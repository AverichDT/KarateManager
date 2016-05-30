<?php

namespace App\Model;

use Nette;
use DateTime;

/**
 * TrainingModel is model class responsible for manipulation with trainings
 * and training related tables in DB.
 * Contains application logic for trainings and related entities.
 */
class TrainingModel extends Nette\Object {

    private $database;

    /**
     * Creates new instance of TrainingModel
     * 
     * @param Nette\Database\Context $database DB connection
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    /**
     * Inserts new training(s) into DB
     * 
     * @param type $values training values
     */
    public function create($values) {
        if ($values['repeating']) {
            // setup dates for incrementing on repeating trainings
            $start_time = new DateTime($values['start_time']);
            $end_time = new DateTime($values['end_time']);
            $repeating_end = new DateTime($values['repeating_end']);
            $modifyingString = '+' . $values['repeating_interval'] . " days";

            // setup series_id
            $series_id = $this->getMaxSeriesId();
            if (is_null($series_id)) {
                $values['series_id'] = 0;
            } else {
                $values['series_id'] = $series_id + 1;
            }

            // while start_time is before repeating_end do:
            while ($start_time < $repeating_end) {
                // insert training
                $this->insertTrainingToDB($values);
                // modify values for next training
                $start_time->modify($modifyingString);
                $end_time->modify($modifyingString);
                $values['start_time'] = $start_time;
                $values['end_time'] = $end_time;
            }
        } else {
            $values['series_id'] = null;
            $this->insertTrainingToDB($values);
        }
    }

    private function insertTrainingToDB($values) {
        $this->database->table('trainings')->insert(array(
            'title' => $values['title'],
            'description' => $values['description'],
            'place' => $values['place'],
            'start_time' => $values['start_time'],
            'end_time' => $values['end_time'],
            'max_attendance' => $values['max_attendance'],
            'min_technical_grade' => $values['min_technical_grade'],
            'series_id' => $values['series_id']
        ));
    }

    private function getMaxSeriesId() {
        return $this->database->table('trainings')->max('series_id');
    }

    /**
     * Retrieves specified training from DB
     * 
     * @param type $id training's id
     * @return ActiveRow training row
     */
    public function get($id) {
        return $this->database->table('trainings')->get($id);
    }

    /**
     * Retrieves all trainings from DB
     * 
     * @return array training rows
     */
    public function getAll() {
        return $this->database->table('trainings')->order('start_time ASC');
    }

    /**
     * Deletes training from DB
     * 
     * @param int $id training's id
     */
    public function delete($id, $series_id = null) {
        if ($series_id == null) {
            $this->database->table('trainings_attendance')->where('trainings_id', $id)->delete();
            $this->database->table('trainings')->where('id', $id)->delete();
        } else {
            $trainings = $this->getSeriesTrainings($series_id);
            foreach ($trainings as $training) {
                $this->delete($training->id);
            }
        }
    }

    /**
     * Signs up member for specified training.
     * 
     * @param int $memberId member's id
     * @param int $trainingId training's id
     * @param int $series_id training series id
     */
    public function signUp($memberId, $trainingId, $series_id = null) {
        if ($series_id == null) {
            $this->database->query('INSERT IGNORE INTO trainings_attendance (members_id, trainings_id) VALUES (?, ?)', $memberId, $trainingId);
        } else {
            $trainings = $this->getSeriesTrainings($series_id);
            foreach ($trainings as $training) {
                $this->signUp($memberId, $training->id);
            }
        }
    }

    /**
     * Unsigns member from specified training
     * 
     * @param int $memberId member's id
     * @param int $trainingId trainings's id
     * @param int $series_id training series id
     */
    public function unSign($memberId, $trainingId, $series_id = null) {
        if ($series_id == null) {
            $this->database->table('trainings_attendance')->where('members_id', $memberId)->where('trainings_id', $trainingId)->delete();
        } else {
            $trainings = $this->getSeriesTrainings($series_id);
            foreach ($trainings as $training) {
                $this->unSign($memberId, $training->id);
            }
        }
    }

    public function getSeriesTrainings($id) {
        return $this->database->table('trainings')->where('series_id', $id);
    }

    /**
     * Checks whether specified member is attending specified training
     * 
     * @param int $memberId member's id
     * @param int $trainingId training's id
     * @return boolean true if attending, false otherwise
     */
    public function isMemberAttending($memberId, $trainingId) {
        $row = $this->database->table('trainings_attendance')->where('members_id', $memberId)->where('trainings_id', $trainingId)->fetch();
        return empty($row) ? false : true;
    }

    /**
     * Retrieves trainings for specified calendar month.
     * 
     * @param type $year calendar year
     * @param type $month calendar month
     * @return type trainings DB rows for specified month
     */
    public function getMonthTrainings($year = null, $month = null) {
        $date = null;
        if (!is_null($year) && !is_null($month)) {
            $date = new DateTime();
            $date->setDate($year, $month, 1);
            $date->setTime(0, 0);
        } else {
            $date = new DateTime('first day of this month');
            $date->setTime(0, 0);
        }
        $trainings = $this->database->table('trainings')->where('start_time >= ? AND start_time <= ?', $date->format('Y-m-d H:i'), $date->setTime(23, 59)->format('Y-m-t H:i'))->order('start_time ASC');
        return $trainings;
    }

    /**
     * Counts number of members attending specified training
     * 
     * @param int $id training's id
     * @return int attendants count
     */
    public function getCountOfAttendingMembers($id) {
        $count = $this->database->table('trainings_attendance')->where('trainings_id', $id)->count();
        return $count;
    }

    /**
     * Retrieves members which are signed up for specified training.
     * 
     * @param int $id training's id
     * @return DB rows of participants
     */
    public function getAttendants($id) {
        $members = $this->database->query('SELECT * FROM members WHERE id IN '
                        . '(SELECT members_id FROM trainings_attendance WHERE trainings_id = ?)', $id)->fetchAll();
        return $members;
    }

    /**
     * Retrieves members which are signed up for specified training 
     * and are members of specified group.
     * 
     * @param int $groupId training group id
     * @param int $trainingId training's id
     * @return DB rows of attendants
     */
    public function getGroupAttendants($groupId, $trainingId) {
        $members = $this->database->query('SELECT * FROM members  WHERE '
                        . 'id IN (SELECT members_id FROM training_group_members WHERE training_groups_id = ?) AND '
                        . 'id IN (SELECT members_id FROM trainings_attendance WHERE trainings_id = ?)', $groupId, $trainingId)->fetchAll();
        return $members;
    }

    /**
     * Retrieves members which are NOT signed up for specified training
     * 
     * @param int $id training's id
     * @return DB rows of not attedning members
     */
    public function getNonAttendants($id) {
        $members = $this->database->query('SELECT * FROM members WHERE id NOT IN '
                        . '(SELECT members_id FROM trainings_attendance WHERE trainings_id = ?)', $id)->fetchAll();
        return $members;
    }

    /**
     * Retrieves members which are NOT signed up for specified training
     * and are members of specified group
     * 
     * @param int $groupId members group id
     * @param int $trainingId training's id
     * @return DB rows of not attending members
     */
    public function getGroupNonAttendants($groupId, $trainingId) {
        $members = $this->database->query('SELECT * FROM members  WHERE '
                        . 'id IN (SELECT members_id FROM training_group_members WHERE training_groups_id = ?) AND '
                        . 'id NOT IN (SELECT members_id FROM trainings_attendance WHERE trainings_id = ?)', $groupId, $trainingId)->fetchAll();
        return $members;
    }

}
