<?php

namespace App\Model;

use Nette;

/**
 * CoachModel is model class responsible for manipulation with Coaches DB table.
 * Contains application logic for coach entities.
 */
class CoachModel extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;

    /**
     * Constructs new CoachModel
     * 
     * @param Nette\Database\Context $database DB connection
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    /**
     * Creates coach row for member with specified id.
     * 
     * @param type $memberId member's id
     */
    public function create($memberId) {
        $this->database->table('coaches')->insert(array(members_id => $memberId));
    }

    /**
     * Return coach row for member with specified member id
     * 
     * @param type $id member's id
     * @return ActitiveRow from Coaches table
     */
    public function getByMemberId($id) {
        $competitor = $this->database->table('coaches')->where('members_id', $id)->fetch();
        return $competitor;
    }

    /**
     * Updates coach row for member with specified member id
     * 
     * @param type $memberId member's id
     * @param array $values new coach values
     */
    public function update($memberId, $values) {
        $this->database->table('coaches')->where('members_id', $memberId)->update($values);
    }

    /**
     * Returns rows for all coaches in the DB.
     * 
     * @return coaches' rows
     */
    public function getAll() {
        $rows = $this->database->query("SELECT members.id, firstname, midname, surname, gender, birthdate, technical_grade, roles, member_since, coach_grade, specialization, members_id FROM coaches INNER JOIN members ON coaches.members_id = members.id")->fetchAll();
        return empty($rows) ? array() : $rows;
    }

}
