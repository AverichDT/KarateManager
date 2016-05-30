<?php

namespace App\Model;

use Nette;
use Nette\Utils\DateTime;

/**
 * MemberModel is model class responsible for manipulation with members
 * and members related tables in DB.
 * Contains application logic for members and related entities.
 */
class MemberModel extends Nette\Object {

    /** @var Nette\Database\Context */
    private $database;

    /** @var App\Model\UserModel */
    private $userModel;

    /**
     * Creates new instance of MemberModel
     * 
     * @param Nette\Database\Context $database DB connection
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
        $this->userModel = new UserModel($database);
    }

    /**
     * Creates new member and inserts it into DB
     * 
     * @param type $values member values
     */
    public function createMember($values) {
        // Insert related rows into DB
        $userId = $this->userModel->create($values->username, $values->password);
        // Insert new member row
        $member = $this->database->table('members')->insert(array(
            'firstname' => $values->firstname,
            'midname' => $values->midname,
            'surname' => $values->surname,
            'gender' => $values->gender,
            'birthdate' => $values->birthdate,
            'nid' => $values->nid,
            'mail' => $values->mail,
            'phone' => $values->phone,
            'city' => $values->city,
            'address' => $values->address,
            'zipcode' => $values->zipcode,
            'technical_grade' => $values->technical_grade,
            'roles' => json_encode($values->roles),
            'member_since' => (isset($values->member_since) && $values->member_since != "0000-00-00") ? $values->member_since : date("Y-m-d"),
            'users_id' => $userId,
        ));
        $this->createMemberRoles($member->id, $values->roles);
    }

    /**
     * Creates appropriate entities specified by roles and inserts them into DB.
     * 
     * @param type $id member's id
     * @param type $roles member's roles
     */
    private function createMemberRoles($id, $roles) {
        if (in_array('competitor', $roles)) {
            $this->database->query('INSERT INTO competitors (members_id) VALUES (?)', $id);
        }
        if (in_array('trainer', $roles)) {
            $this->database->query('INSERT INTO trainers (members_id) VALUES (?)', $id);
        }
        if (in_array('coach', $roles)) {
            $this->database->query('INSERT INTO coaches (members_id) VALUES (?)', $id);
        }
    }

    /**
     * Deletes appopriate rows specified by roles from DB
     * 
     * @param type $id member's id
     * @param type $roles member's roles to be deleted
     */
    private function deleteMemberRoles($id, $roles) {
        if (in_array('competitor', $roles)) {
            $competitorRow = $this->database->query('SELECT id FROM competitors WHERE members_id = ?', $member->id)->fetch();
            $competitorId = $competitorRow['id'];
            $this->deleteCompetitor($competitorId);
        }
        if (in_array('trainer', $roles)) {
            $this->database->query('DELETE FROM trainers WHERE members_id=?', $id);
        }
        if (in_array('coach', $roles)) {
            $this->database->query('DELETE FROM coaches WHERE members_id=?', $id);
        }
    }

    /**
     * Categorizes members by their specified roles.
     * 
     * @param type $allMembers [input] all members
     * @param type $members [output] all members with role member
     * @param type $competitors [output] all members with role competitor
     * @param type $trainers [output] all members with role trainer
     * @param type $coaches [output] all members with role coach
     */
    public function categorizeMembers($allMembers, &$members, &$competitors, &$trainers, &$coaches) {
        foreach ($allMembers as $member) {
            $roles = json_decode($member->roles);
            if (in_array("member", $roles)) {
                $members[] = $member;
            }
            if (in_array("competitor", $roles)) {
                $competitors[] = $member;
            }
            if (in_array("trainer", $roles)) {
                $trainers[] = $member;
            }
            if (in_array("coach", $roles)) {
                $coaches[] = $member;
            }
        }
    }

    /**
     * Retrieves all members from DB
     * 
     * @return array members rows
     */
    public function getAllMembers() {
        return $this->database->table('members')->order('technical_grade ASC')->fetchAll();
    }

    /**
     * Retrieves specified member from DB
     * 
     * @param int $id member's id
     * @return ActiveRow member row
     */
    public function get($id) {
        $member = $this->database->table('members')->get($id);
        return $member;
    }

    /**
     * Retrieves specified member's roles from DB
     * 
     * @param int $id member's id
     * @return array member's roles
     */
    public function getMemberRoles($id) {
        $member = $this->get($id);
        return json_decode($member->roles);
    }

    /**
     * Updates specified member
     * 
     * @param int $id member's id
     * @param array $values new values
     */
    public function update($id, $values) {
        if (isset($values['roles'])) {
            $oldRoles = json_decode($this->get($id)->roles);
            $rolesToDelete = array_diff($oldRoles, $values['roles']);
            $rolesToCreate = array_diff($values['roles'], $oldRoles);

            $this->deleteMemberRoles($id, $rolesToDelete);
            $this->createMemberRoles($id, $rolesToCreate);
            $values['roles'] = json_encode($values['roles']);
        }
        $this->database->table('members')->where('id', $id)->update($values);
    }

    /**
     * Retrieves member's acount (USER) from DB
     * 
     * @param int $id user's id
     * @return ActiveRow user row
     */
    public function getUser($id) {
        return $this->userModel->get($id);
    }

    /**
     * Retrieves member by user's id
     * 
     * @param type $id user's id
     * @return row member row
     */
    public function getByUserId($id) {
        return $this->database->table('members')->where('users_id', $id)->fetch();
    }

    /**
     * Retrieves member's account (USER) from DB specified by member's id
     * 
     * @param int $id member's id
     * @return ActiveRow user row
     */
    public function getUserByMemberId($id) {
        $member = $this->database->table('members')->get($id);
        $user = $member->ref('users', 'users_id');
        return $user;
    }

    /**
     * Deletes specified member
     * 
     * @param int $id member's id
     */
    public function delete($id) {
        $member = $this->database->table('members')->get($id);
        $userId = $member->users_id;
        $competitorRow = $this->database->query('SELECT id FROM competitors WHERE members_id = ?', $member->id)->fetch();
        $competitorId = $competitorRow['id'];
        $this->deleteCompetitor($competitorId);
        $this->database->query("DELETE FROM trainers WHERE members_id=?", $id);
        $this->database->query("DELETE FROM coaches WHERE members_id=?", $id);
        $this->deleteMember($member->id);
        $this->userModel->delete($userId);
    }

    private function deleteMember($memberId) {
        $this->database->table('training_group_members')->where('members_id', $memberId)->delete();
        $this->database->table('trainings_attendance')->where('members_id', $memberId)->delete();
        $this->database->table('members')->where('id', $memberId)->delete();
    }

    private function deleteCompetitor($competitorId) {
        $this->database->table('competition_group_members')->where('competitors_id', $competitorId)->delete();
        $this->database->table('competitions_participation')->where('competitors_id', $competitorId)->delete();
        $this->database->table('competitors')->where('id', $competitorId)->delete();
    }

    /**
     * Returns trainings which is specified member attending.
     * 
     * @param type $id member's id
     * @param type $year calendar year
     * @param type $month calendar month
     * @return type training rows
     */
    public function getMonthTrainings($id, $year, $month) {
        $date = new DateTime();
        $date->setDate($year, $month, 1);
        $date->setTime(0, 0);
        $trainings = $this->database->table('trainings')->where('start_time >= ? AND start_time <= ?', $date->format('Y-m-d H:i'), $date->setTime(23, 59)->format('Y-m-t H:i'))->where('id IN (SELECT trainings_id FROM trainings_attendance WHERE members_id = ?)', $id)->order('start_time ASC');
        return $trainings;
    }

    /**
     * Calculate's age from birth date
     * 
     * @param string $birthDate birth date
     * @return int age
     */
    public static function calculateAge($birthDate) {
        $date = new DateTime($birthDate);
        $now = new DateTime();
        $interval = $now->diff($date);
        return $interval->y;
    }

    /**
     * Resolves technical grade enumeration into technical grade text
     * 
     * @param int $technicalGrade technical grade number [ENUM]
     * @return string technical grade text
     */
    public function getTechnicalGradeText($technicalGrade) {
        $text = "";
        if ($technicalGrade < 8) {
            $text = (8 - $technicalGrade) . ". kyu";
        } else {
            $text = ($technicalGrade - 7) . ". dan";
        }
        return $text;
    }

}
