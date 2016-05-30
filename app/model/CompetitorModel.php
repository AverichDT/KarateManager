<?php

namespace App\Model;

use Nette;
use DateTime;

/**
 * CompetitorModel is model class responsible for manipulation with competitors
 * and competitors related tables in DB.
 * Contains application logic for competitors and related entities.
 */
class CompetitorModel extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;
    private $memberModel;

    /**
     * Creates new instance of CompetitorModel
     * 
     * @param Nette\Database\Context $database DB connection
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
        $this->memberModel = new MemberModel($database);
    }

    /**
     * Creates competitor row in DB for specified member
     * 
     * @param int $memberId member's id
     */
    public function create($memberId) {
        $this->database->table('competitors')->insert(array(members_id => $memberId));
    }

    /**
     * Retrieves competitor row from DB for specified member
     * 
     * @param int $id member's id
     * @return ActiveRow competitor row
     */
    public function getByMemberId($id) {
        $competitor = $this->database->table('competitors')->where('members_id', $id)->fetch();
        return $competitor;
    }

    /**
     * Retrieves competitor row from DB for specified user
     * 
     * @param type $id users's id
     * @return ActiveRow competitor row
     */
    public function getByUserId($id) {
        $member = $this->memberModel->getByUserId($id);
        return $this->getByMemberId($member->id);
    }

    /**
     * Updates competitor values for specified member.
     *  
     * @param type $memberId member's id
     * @param type $values new values
     */
    public function update($memberId, $values) {
        $this->database->table('competitors')->where('members_id', $memberId)->update($values);
    }

    /**
     * Retrieves all competitors from DB. 
     * 
     * @return competitor rows
     */
    public function getAll() {
        $rows = $this->database->query("SELECT members.id, firstname, midname, surname, gender, birthdate, technical_grade, roles, member_since, height, weight, cuma_stamp, cka_stamp, performance_grade, specialization, members_id FROM competitors INNER JOIN members ON competitors.members_id = members.id")->fetchAll();
        return empty($rows) ? array() : $rows;
    }

    /**
     * Returns competitions which is specified competitor attending.
     * 
     * @param type $id competitors's id
     * @param type $year calendar year
     * @param type $month calendar month
     * @return type training rows
     */
    public function getMonthCompetitions($id, $year, $month) {
        $date = new DateTime();
        $date->setDate($year, $month, 1);
        $date->setTime(0, 0);
        $competitions = $this->database->table('competitions')->where('start_time >= ? AND start_time <= ?', $date->format('Y-m-d H:i'), $date->setTime(23, 59)->format('Y-m-t H:i'))->where('id IN (SELECT competitions_id FROM competitions_participation WHERE competitors_id = ?)', $id)->order('start_time ASC');
        return $competitions;
    }

    /**
     * Returns competitors WKF category string
     * 
     * @param type $competitor competitor values
     * @return type WKF category string
     */
    public function getCompetitorCategory($competitor) {
        return \CompetitionCategories::getCategory($competitor->gender, \App\Model\MemberModel::calculateAge($competitor->birthdate), $competitor->weight);
    }

}
