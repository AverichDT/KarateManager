<?php

namespace App\Model;

use Nette\Security\Permission;
use Nette;

/**
 * Class respnsoible for authorizing members and controlling access
 * to application resources.
 */
class MyAuthorizator extends Nette\Object implements Nette\Security\IAuthorizator {

    private $acl;

    public function create() {
        $acl = new Permission();

        // User roles
        $acl->addRole('guest');
        $acl->addRole('member');
        $acl->addRole('competitor', 'member');
        $acl->addRole('trainer', 'member');
        $acl->addRole('coach', 'trainer');
        $acl->addRole('admin', 'coach');

        // Application resources
        $acl->addResource('Homepage');
        $acl->addResource('Competition');
        $acl->addResource('Training');
        $acl->addResource('Profile');
        $acl->addResource('Settings');
        $acl->addResource('UserManagement');

        // Guest rights
        $acl->allow('guest', 'Homepage');
        
        // Member rights
        $acl->allow('member', 'Homepage');
        $acl->allow('member', 'Profile', array('view', 'manage'));
        $acl->allow('member', 'Settings', array('view', 'manage'));
        $acl->allow('member', 'Training', 'view');
        
        // Competitor rights [inherits member rights]
        $acl->allow('competitor', 'Competition', 'view');
        
        // Trainer rights [inherits member rights]
        $acl->allow('trainer', 'Training', 'manage');
        
        // Coach rights [inherits trainer rights]
        $acl->allow('coach', 'Competition', array('view', 'manage'));
        
        // Admin rights
        $acl->allow('admin', 'UserManagement', array('view', 'manage'));

        return $acl;
    }

    public function __construct() {
        $this->acl = $this->create();
    }

    function isAllowed($role, $resource, $privilege) {

        return $this->acl->isAllowed($role, $resource, $privilege);
    }

}
