<?php
namespace Application\Model;

use Zend\Config\Factory;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Resource\GenericResource;
use Zend\Permissions\Acl\Role\GenericRole;

/**
 * Description of Acl
 *
 * @author ilahir
 */
class Acl extends ZendAcl {
    public function __construct($resourceList,$userList) {
        foreach ($resourceList as $res) {
            if (!$this->hasResource($res['resource_id'])) {
                $this->addResource(new GenericResource($res['resource_id']));
            }
        }
        
        foreach ($userList as $user) {
            if (!$this->hasRole($user['employee_no'])) {
                $this->addRole(new GenericRole($user['employee_no']));
            }
        }

        $config = Factory::fromFile(CONFIG_PATH . DIRECTORY_SEPARATOR . "acl.config.php");
        
        foreach ($config as $employeeNo => $resource) {
            if (!$this->hasRole($employeeNo)) {
                $this->addRole(new GenericRole($employeeNo));
            }
            
            foreach ($resource as $resource => $permission) {
                foreach ($permission as $privilege => $permission) {
                    $this->$permission($employeeNo,$resource,$privilege);
                }
            }
        }
    }

}
