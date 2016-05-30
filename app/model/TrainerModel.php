<?php

namespace App\Model;

use Nette;

/**
 * TrainerModel is model class responsible for manipulation with trainers
 * and trainers related tables in DB.
 * Contains application logic for trainers and related entities.
 */
class TrainerModel extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;

    /**
     * Creates new instance of TrainerModel
     * 
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    /**
     * Creates trainer row in DB for specified member
     * 
     * @param int $memberId member's id
     */
    public function create($memberId) {
        $this->database->table('trainers')->insert(array(members_id => $memberId));
    }

    /**
     * Retrieves trainer specified by it's member id
     * 
     * @param int $id member's id
     * @return ActiveRow trainer row
     */
    public function getByMemberId($id) {
        $trainer = $this->database->table('trainers')->where('members_id', $id)->fetch();
        return $trainer;
    }

    /**
     * Updates trainer specified by member id
     * 
     * @param int $memberId member's id
     * @param array $values new values
     */
    public function update($memberId, $values) {
        $this->database->table('trainers')->where('members_id', $memberId)->update($values);
    }

    /**
     * Retrieves all trainers
     * 
     * @return array trainer rows
     */
    public function getAll() {
        $rows = $this->database->query("SELECT members.id, firstname, midname, surname, gender, birthdate, technical_grade, roles, member_since, trainer_grade, licence_start, licence_end, members_id FROM trainers INNER JOIN members ON trainers.members_id = members.id")->fetchAll();
        return empty($rows) ? array() : $rows;
    }

}
