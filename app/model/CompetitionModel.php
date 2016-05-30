<?php

namespace App\Model;

use Nette,
    CompetitionCategories,
    MemberModel;
use DateTime;

/**
 * CompetitionModel is model class responsible for manipulation with competitions
 * and competitions related tables in DB.
 * Contains application logic for competitions and related entities.
 */
class CompetititionModel extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;

    // Competitor columns used in views.
    const COMPETITOR_COLS = "competitors.id, firstname, surname, gender, birthdate, technical_grade, performance_grade, weight ";

    /**
     * Creates new instance of CompetitionModel.
     * 
     * @param Nette\Database\Context $database DB connection
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    /**
     * Creates competition row in DB.
     * 
     * @param array $values competition values
     */
    public function create($values) {
        $this->database->table('competitions')->insert($values);
    }

    /**
     * Deletes competition from DB
     * 
     * @param int $id competition's id
     */
    public function delete($id) {
        $this->database->table('competitions_participation')->where('competitions_id', $id)->delete();
        $this->database->table('competitions')->where('id', $id)->delete();
    }

    /**
     * Updates competition with specified id
     * 
     * @param array $values new values
     * @param int $id competition's id
     */
    public function update($values, $id) {
        $this->database->table('competitions')->where('id', $id)->update($values);
    }

    /**
     * Retrieves competition from DB.
     * 
     * @param int $id competition's id
     * @return ActiveRow competition row
     */
    public function get($id) {
        return $this->database->table('competitions')->get($id);
    }

    /**
     * Signs up competitor for specified competition.
     * 
     * @param int $competitorId competitor's id
     * @param int $competitionId competition's id
     */
    public function signUp($competitorId, $competitionId) {
        $this->database->query('INSERT IGNORE INTO competitions_participation (competitors_id, competitions_id) VALUES (?, ?)', $competitorId, $competitionId);
    }

    /**
     * Unsigns competitor from specified competition
     * 
     * @param int $competitorId competitor's id
     * @param int $competitionId competition's id
     */
    public function unSign($competitorId, $competitionId) {
        $this->database->table('competitions_participation')->where('competitors_id', $competitorId)->where('competitions_id', $competitionId)->delete();
    }

    /**
     * Retrieves competitors which are signed up for specified competition.
     * 
     * 
     * @param int $id competition's id
     * @return DB rows of participants
     */
    public function getParticipants($id) {
        $competitors = $this->database->query('SELECT ' . self::COMPETITOR_COLS
                        . 'FROM competitors INNER JOIN members ON competitors.members_id = members.id WHERE competitors.id IN '
                        . '(SELECT competitors_id FROM competitions_participation WHERE competitions_id = ?)', $id)->fetchAll();
        return $competitors;
    }

    /**
     * Retrieves ccompetitors which are signed up for specified competition 
     * and are members of specified group.
     * 
     * @param int $groupId competitors group id
     * @param int $competitionId competition's id
     * @return DB rows of participants
     */
    public function getGroupParticipants($groupId, $competitionId) {
        $competitors = $this->database->query('SELECT ' . self::COMPETITOR_COLS
                        . 'FROM competitors INNER JOIN members ON competitors.members_id = members.id WHERE '
                        . 'competitors.id IN (SELECT competitors_id FROM competition_group_members WHERE competition_groups_id = ?) AND '
                        . 'competitors.id IN (SELECT competitors_id FROM competitions_participation WHERE competitions_id = ?)', $groupId, $competitionId)->fetchAll();
        return $competitors;
    }

    /**
     * Retrieves competitors which are NOT signed up for specified competition
     * 
     * @param int $id competition's id
     * @return DB rows of not participating competitors
     */
    public function getNonParticipants($id) {
        $competitors = $this->database->query('SELECT ' . self::COMPETITOR_COLS
                        . 'FROM competitors INNER JOIN members ON competitors.members_id = members.id WHERE competitors.id NOT IN '
                        . '(SELECT competitors_id FROM competitions_participation WHERE competitions_id = ?)', $id)->fetchAll();
        return $competitors;
    }

    /**
     * Retrieves competitors which are NOT signed up for specified competition
     * and are members of specified group
     * 
     * @param int $groupId competitors group id
     * @param int $competitionId competition's id
     * @return DB rows of not participating competitors
     */
    public function getGroupNonParticipants($groupId, $competitionId) {
        $competitors = $this->database->query('SELECT ' . self::COMPETITOR_COLS
                        . 'FROM competitors INNER JOIN members ON competitors.members_id = members.id WHERE '
                        . 'competitors.id IN (SELECT competitors_id FROM competition_group_members WHERE competition_groups_id = ?) AND '
                        . 'competitors.id NOT IN (SELECT competitors_id FROM competitions_participation WHERE competitions_id = ?)', $groupId, $competitionId)->fetchAll();
        return $competitors;
    }

    /**
     * Returns competitor's WKF category
     * 
     * @param array $competitor competitor values
     * @return WKF category string
     */
    public function getCompetitorCategory($competitor) {
        return CompetitionCategories::getCategory($competitor->gender, MemberModel::calculateAge($competitor->birthdate), $competitor->weight);
    }

    /**
     * Retrieves competitions for specified calendar month.
     * 
     * @param type $year calendar year
     * @param type $month calendar month
     * @return type competition DB rows for specified month
     */
    public function getMonthCompetitions($year = null, $month = null) {
        $date = null;
        if (!is_null($year) && !is_null($month)) {
            $date = new DateTime();
            $date->setDate($year, $month, 1);
            $date->setTime(0, 0);
        } else {
            $date = new DateTime('first day of this month');
            $date->setTime(0, 0);
        }
        $competitions = $this->database->table('competitions')->where('start_time >= ? AND start_time <= ?', $date->format('Y-m-d H:i'), $date->setTime(23, 59)->format('Y-m-t H:i'))->order('start_time ASC');
        return $competitions;
    }

    /**
     * Checks whether specified competitor is attending specified competition
     * 
     * @param int $competitorId competitor's id
     * @param int $competitionId competition's id
     * @return boolean true if attending, false otherwise
     */
    public function isCompetitorAttending($competitorId, $competitionId) {
        $row = $this->database->table('competitions_participation')->where('competitors_id', $competitorId)->where('competitions_id', $competitionId)->fetch();
        return empty($row) ? false : true;
    }

}
