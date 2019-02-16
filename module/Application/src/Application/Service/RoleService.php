<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class RoleService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addRole($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $roleDb = $this->sm->get('RolesTable');
            $result = $roleDb->addRoleDetails($params);
            if($result>0){
				//$roleDb->mapRolePrivilege($params);
				$adapter->commit();
				$alertContainer = new Container('alert');
				$alertContainer->alertMsg = 'Role details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateRole($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $roleDb = $this->sm->get('RolesTable');
            $result = $roleDb->updateRoleDetails($params);
            if($result>0){
			 //$roleDb->mapRolePrivilege($params);
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Role details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllRoles($parameters){
        $roleDb = $this->sm->get('RolesTable');
		$acl = $this->sm->get('AppAcl');
        return $roleDb->fetchAllRoles($parameters,$acl);
    }
    
    public function getRole($roleId){
        $roleDb = $this->sm->get('RolesTable');
        return $roleDb->getRoleDetails($roleId);
    }
    
    public function getAllActiveRoles(){
        $roleDb = $this->sm->get('RolesTable');
        return $roleDb->fetchAllActiveRoles();
    }
    
    public function getAllResource(){
        $roleDb = $this->sm->get('RolesTable');
        return $roleDb->fetchAllResource();
    }
}
?>

