<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class EmployeeService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    
    public function addEmployee($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $employeeDb = $this->sm->get('EmployeeTable');
            $result = $employeeDb->addEmployeeDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Employee details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateEmployee($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $employeeDb = $this->sm->get('EmployeeTable');
            $result = $employeeDb->updateEmployeeDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Employee details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllEmployees($parameters){
        $employeeDb = $this->sm->get('EmployeeTable');
        $acl = $this->sm->get('AppAcl');
        return $employeeDb->fetchAllEmployees($parameters,$acl);
    }
    
    public function getEmployeeCode(){
        $employeeDb = $this->sm->get('EmployeeTable');
        return $employeeDb->fetchEmployeeCode();
    }
    
    public function getEmployee($employeeId){
        $employeeDb = $this->sm->get('EmployeeTable');
        return $employeeDb->getEmployeeDetails($employeeId);
    }
    
    public function updatePassword($params) {
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $userDb = $this->sm->get('UserTable');
            $result = $userDb->updatePasswordDetails($params);
            $adapter->commit();
            $container = new Container('alert');
            $container->alertMsg = 'Password details updated successfully';
            return $result;
            
        } catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllActiveEmployeesExceptDriver(){
        $employeeDb = $this->sm->get('EmployeeTable');
        return $employeeDb->fetchAllActiveEmployeesExceptDriver();
    }
    
    public function getAllActiveEmployeesExceptExistUser(){
        $employeeDb = $this->sm->get('EmployeeTable');
        return $employeeDb->fetchAllActiveEmployeesExceptExistUser();
    }
    
    public function getEmployeeLicenseExpiryList(){
        $employeeDb = $this->sm->get('EmployeeTable');
        return $employeeDb->fetchEmployeeLicenseExpiryList();
    }
    
    public function getAllActiveDriverList(){
        $employeeDb = $this->sm->get('EmployeeTable');
        return $employeeDb->fetchAllActiveDriverList();
    }
    
    public function getAllRelievedEmployees($parameters){
        $employeeDb = $this->sm->get('EmployeeTable');
        $acl = $this->sm->get('AppAcl');
        return $employeeDb->fetchAllRelievedEmployees($parameters,$acl);
    }
    
    public function getAllActiveBusinessUnitDriverList($businessUnit=null){
        $employeeDb = $this->sm->get('EmployeeTable');
        return $employeeDb->fetchAllActiveBusinessUnitDriverList($businessUnit);
    }
}
?>

