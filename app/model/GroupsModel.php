<?php

namespace App\Model;

use Nette;
use App\Model\CompetititionModel;

/**
 * CompetitionGroupModel is model class responsible for manipulation with competition
 * groups (competitor groups).
 * Contains application logic for competition (competitor) groups.
 *
 * @author Petr
 */
class CompetitionGroupModel extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;
    private $competitionModel;

    /**
     * Creates new instance of CompetitionGroupModel
     * 
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
        $this->competitionModel = new CompetititionModel($database);
    }

    /**
     * Retrieves competition group from DB
     * 
     * @param int $id group's id
     * @return ActiveRow competition group row
     */
    public function get($id) {
        return $this->database->table('competition_groups')->get($id);
    }

    /**
     * Retrieves all competitition groups from DB
     * 
     * @return array competition group rows
     */
    public function getAll() {
        $rows = $this->database->table('competition_groups')->fetchAll();
        return empty($rows) ? false : $rows;
    }

    /**
     * Creates competition group row in DB.
     * 
     * @param array $values competition group values
     */
    public function create($values) {
        $this->database->table('competition_groups')->insert($values);
    }

    /**
     * Updates competition group.
     * 
     * @param int $id group's id
     * @param array $values new values
     */
    public function update($id, $values) {
        $this->database->table('competition_groups')->where('id', $id)->update($values);
    }

    /**
     * Deletes competition group.
     * 
     * @param int $id group's id
     */
    public function delete($id) {
        $this->database->table('competition_group_members')->where('competition_groups_id', $id)->delete();
        $this->database->table('competition_groups')->where('id', $id)->delete();
    }

    /**
     * Adds specified competitor to group
     * 
     * @param int $competitorId competitor's id
     * @param int $groupId group's id
     */
    public function addCompetitorToGroup($competitorId, $groupId) {
        $this->database->table('competition_group_members')->insert(array(
            'competition_groups_id' => $groupId,
            'competitors_id' => $competitorId
        ));
    }

    /**
     * Removes specified competitor form group
     * 
     * @param int $competitorId competitor's id 
     * @param int $groupId group's id
     */
    public function removeCompetitorFromGroup($competitorId, $groupId) {
        $this->database->table('competition_group_members')->where('competitors_id', $competitorId)->where('competition_groups_id', $groupId)->delete();
    }

    /**
     * Retrieves ids of all competitors from specified group
     * 
     * @param type $id group's id
     * @return array array of competitors ids
     */
    public function getGroupCompetitorsIds($id) {
        return $this->database->table('competition_group_members')->where('competition_groups_id', $id)->select('competitors_id');
    }

    /**
     * Retrieves all members of specified group
     * 
     * @param int $id group's id
     * @return array members of group
     */
    public function getMembers($id) {
        $competitors = $this->database->query('SELECT ' . CompetititionModel::COMPETITOR_COLS
                        . 'FROM competitors INNER JOIN members ON competitors.members_id = members.id WHERE competitors.id IN '
                        . '(SELECT competitors_id FROM competition_group_members WHERE competition_groups_id = ?)', $id)->fetchAll();
        return $competitors;
    }

    /**
     * Retrieves all competitors, who are NOT members of specified group
     * 
     * @param type $id group's id
     * @return array non members of group
     */
    public function getNonMembers($id) {
        $competitors = $this->database->query('SELECT ' . CompetititionModel::COMPETITOR_COLS
                        . 'FROM competitors INNER JOIN members ON competitors.members_id = members.id WHERE competitors.id NOT IN '
                        . '(SELECT competitors_id FROM competition_group_members WHERE competition_groups_id = ?)', $id)->fetchAll();
        return $competitors;
    }

    /**
     * Signs up all competitors who are members of specified group for competition.
     * 
     * @param type $groupId group's id
     * @param type $competitionId competition's id
     */
    public function competitionSignUp($groupId, $competitionId) {
        foreach ($this->getGroupCompetitorsIds($groupId) as $row) {
            $this->competitionModel->signUp($row->competitors_id, $competitionId);
        }
    }

    /**
     * Unsigns all competitors who are members of specified group from competition
     * 
     * @param type $groupId group's id
     * @param type $competitionId competition's id
     */
    public function competitionUnSign($groupId, $competitionId) {
        foreach ($this->getGroupCompetitorsIds($groupId) as $row) {
            $this->competitionModel->unSign($row->competitors_id, $competitionId);
        }
    }

}

/**
 * Description of TrainingGroup
 * 
 * @author Petr
 */
class TrainingGroupModel extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;
    private $trainingModel;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
        $this->trainingModel = new TrainingModel($database);
    }

    /**
     * Retrieves training group from DB
     * 
     * @param int $id group's id
     * @return ActiveRow training group row
     */
    public function get($id) {
        return $this->database->table('training_groups')->get($id);
    }

    /**
     * Retrieves all training groups from DB
     * 
     * @return array training group rows
     */
    public function getAll() {
        $rows = $this->database->table('training_groups')->fetchAll();
        return empty($rows) ? false : $rows;
    }

    /**
     * Creates trainig group row in DB.
     * 
     * @param array $values training group values
     */
    public function create($values) {
        $this->database->table('training_groups')->insert($values);
    }

    /**
     * Updates training group.
     * 
     * @param int $id group's id
     * @param array $values new values
     */
    public function update($id, $values) {
        $this->database->table('training_groups')->where('id', $id)->update($values);
    }

    /**
     * Deletes training group.
     * 
     * @param int $id group's id
     */
    public function delete($id) {
        $this->database->table('training_group_members')->where('training_groups_id', $id)->delete();
        $this->database->table('training_groups')->where('id', $id)->delete();
    }

    /**
     * Adds specified member to group
     * 
     * @param int $memberId member's id
     * @param int $groupId group's id
     */
    public function addMemberToGroup($memberId, $groupId) {
        $this->database->table('training_group_members')->insert(array(
            'training_groups_id' => $groupId,
            'members_id' => $memberId
        ));
    }

    /**
     * Removes specified member from group
     * 
     * @param int $memberId member's id 
     * @param int $groupId group's id
     */
    public function removeMemberFromGroup($memberId, $groupId) {
        $this->database->table('training_group_members')->where('members_id', $memberId)->where('training_groups_id', $groupId)->delete();
    }

    /**
     * Retrieves all members of specified group
     * 
     * @param int $id group's id
     * @return array members of group
     */
    public function getMembers($id) {
        $members = $this->database->query('SELECT * FROM members WHERE members.id IN '
                        . '(SELECT members_id FROM training_group_members WHERE training_groups_id = ?)', $id)->fetchAll();
        return $members;
    }

    /**
     * Retrieves all competitors, who are NOT members of specified group
     * 
     * @param type $id group's id
     * @return array non members of group
     */
    public function getNonMembers($id) {
        $members = $this->database->query('SELECT * FROM members WHERE members.id NOT IN '
                        . '(SELECT members_id FROM training_group_members WHERE training_groups_id = ?)', $id)->fetchAll();
        return $members;
    }

    /**
     * Signs up all mbebers who are members of specified group for training.
     * 
     * @param type $groupId group's id
     * @param type $trainingId training's id
     * @param type $series_id training series id
     */
    public function trainingSignUp($groupId, $trainingId, $series_id) {
        foreach ($this->getGroupMembersIds($groupId) as $row) {
            $this->trainingModel->signUp($row->members_id, $trainingId, $series_id);
        }
    }

    /**
     * Unsigns all members who are members of specified group from training
     * 
     * @param type $groupId group's id
     * @param type $trainingId training's id
     * @param type $series_id training series id
     */
    public function trainingUnSign($groupId, $trainingId, $series_id) {
        foreach ($this->getGroupMembersIds($groupId) as $row) {
            $this->trainingModel->unSign($row->members_id, $trainingId, $series_id);
        }
    }

    /**
     * Retrieves ids of all members from specified group
     * 
     * @param type $id group's id
     * @return array array of members ids
     */
    public function getGroupMembersIds($id) {
        return $this->database->table('training_group_members')->where('training_groups_id', $id)->select('members_id');
    }

}
