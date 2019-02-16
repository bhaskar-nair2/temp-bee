<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class UserService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addUser($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('UserTable');
            $result = $db->addUserDetails($params);
            if($result>0){
                $roleDb = $this->sm->get('RolesTable');
                $roleDb->mapRolePrivilege($params);
                $adapter->commit();
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'User details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateUser($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('UserTable');
            $result = $db->updateUserDetails($params);
            if($result>0){
                $roleDb = $this->sm->get('RolesTable');
                $roleDb->mapRolePrivilege($params);
                $adapter->commit();
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'User details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllUsers($parameters){
        $db = $this->sm->get('UserTable');
        return $db->fetchAlllUsers($parameters);
    }
    
    public function getUser($userId){
        $db = $this->sm->get('UserTable');
        return $db->fetchUser($userId);
    }
   
    public function loginProcess($params) {
        $userDb = $this->sm->get('UserTable');
        return $userDb->loginProcessDetails($params);
    }
    
    public function updatePassword($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $userDb = $this->sm->get('UserTable');
            $result = $userDb->updateUserPasswordDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Please check email ';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
}
?>

