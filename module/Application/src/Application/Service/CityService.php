<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class CityService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addCity($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $cityDb = $this->sm->get('CityTable');
            $result = $cityDb->addCityDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'City details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateCity($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $cityDb = $this->sm->get('CityTable');
            $result = $cityDb->updateCityDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'City details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllCity($parameters){
        $cityDb = $this->sm->get('CityTable');
        $acl = $this->sm->get('AppAcl');
        return $cityDb->fetchAllCity($parameters,$acl);
    }
    
    public function getCity($cityId){
        $cityDb = $this->sm->get('CityTable');
        return $cityDb->getCityDetails($cityId);
    }
    
    public function getAllCities(){
        $cityDb = $this->sm->get('CityTable');
        return $cityDb->fetchAllCities();
    }
}
?>

