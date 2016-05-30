<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;

/**
 * UserModel is model class responsible for manipulation with member accounts.
 * Contains application logic for user accounts and auhentication.
 */
class UserModel extends Nette\Object implements Nette\Security\IAuthenticator {

    const
            TABLE_NAME = 'users',
            COLUMN_ID = 'id',
            COLUMN_NAME = 'username',
            COLUMN_PASSWORD_HASH = 'password';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($username, $password) = $credentials;

        $user = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();
        $member = $this->database->table('members')->where('users_id', $user['id'])->fetch();
        $roles = json_decode($member['roles']);

        if (!$user) {
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (!Passwords::verify($password, $user[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        } elseif (Passwords::needsRehash($user[self::COLUMN_PASSWORD_HASH])) {
            $user->update(array(
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
            ));
        }

        $arr = $user->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($user[self::COLUMN_ID], $roles, $arr);
    }

    /**
     * Creates new account
     * 
     * @param string $username username
     * @param string $password psasword
     * @return int inserted row id
     * @throws DuplicateNameException
     */
    public function create($username, $password) {
        try {
            $row = $this->database->table(self::TABLE_NAME)->insert(array(
                self::COLUMN_NAME => $username,
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password)
            ));

            return $row->id;
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }

    /**
     * Deletes user account
     * 
     * @param int $id user's id
     */
    public function delete($id) {
        $this->database->query("DELETE FROM users WHERE id=?", $id);
    }

    /**
     * Retrieves user acocunt
     * 
     * @param int $id user's id
     * @return AciveRow user row
     */
    public function get($id) {
        return $this->database->table('users')->get($id);
    }

    /**
     * Changes user password
     * 
     * @param type $id user's id
     * @param type $password new password
     */
    public function changePassword($id, $password) {
        $this->database->table('users')->where('id', $id)->update(array('password' => Passwords::hash($password)));
    }

}

class DuplicateNameException extends \Exception {
    
}
