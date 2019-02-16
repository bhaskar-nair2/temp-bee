<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class HotelService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addHotel($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelTable');
            $result = $hotelDb->addHotelDetails($params);
            if($result>0){
				$adapter->commit();
				$alertContainer = new Container('alert');
				$alertContainer->alertMsg = 'Hotel details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateHotel($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelTable');
            $result = $hotelDb->updateHotelDetails($params);
            if($result>0){
			 //$roleDb->mapRolePrivilege($params);
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Hotel details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllHotels($parameters){
        $hotelDb = $this->sm->get('HotelTable');
		$acl = $this->sm->get('AppAcl');
        return $hotelDb->fetchAllHotels($parameters,$acl);
    }
    
    public function getHotel($hotelId){
        $hotelDb = $this->sm->get('HotelTable');
        return $hotelDb->getHotelDetails($hotelId);
    }
    
    public function getAllActiveHotels(){
        $hotelDb = $this->sm->get('HotelTable');
        return $hotelDb->fetchAllActiveHotels();
    }
    
    
}
?>

