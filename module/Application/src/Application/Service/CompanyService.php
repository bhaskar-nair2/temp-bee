<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class CompanyService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addCompany($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $companyDb = $this->sm->get('CompanyTable');
            $result = $companyDb->addCompanyDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Company details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateCompany($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $companyDb = $this->sm->get('CompanyTable');
            $result = $companyDb->updateCompanyDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Company details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllCompany($parameters){
        $companyDb = $this->sm->get('CompanyTable');
        $acl = $this->sm->get('AppAcl');
        return $companyDb->fetchAllCompany($parameters,$acl);
    }
    
    public function getCompany($companyId){
        $companyDb = $this->sm->get('CompanyTable');
        return $companyDb->getCompanyDetails($companyId);
    }
    
    public function getAllActiveCompany(){
        $companyDb = $this->sm->get('CompanyTable');
        return $companyDb->fetchAllActiveCompany();
    }
    
    public function getAllCompanyList(){
        $companyDb = $this->sm->get('CompanyTable');
        return $companyDb->fetchAllCompanyList();
    }
    
    public function getBeecabsCompany(){
        $companyDb = $this->sm->get('CompanyTable');
        return $companyDb->fetchBeecabsCompany();
    }
}
?>

