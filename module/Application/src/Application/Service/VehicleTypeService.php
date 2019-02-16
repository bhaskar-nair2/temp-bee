<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class VehicleTypeService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addVehicleType($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vehicleTypeDb = $this->sm->get('VehicleTypeTable');
            $result = $vehicleTypeDb->addVehicleTypeDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Vehicle type added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateVehicleType($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vehicleTypeDb = $this->sm->get('VehicleTypeTable');
            $result = $vehicleTypeDb->updateVehicleTypeDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Vehicle type updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllVehicleTypes($parameters){
        $vehicleTypeDb = $this->sm->get('VehicleTypeTable');
        $acl = $this->sm->get('AppAcl');
        return $vehicleTypeDb->fetchAllVehicleTypes($parameters,$acl);
    }
    
    public function getVehicleType($typeId){
        $vehicleTypeDb = $this->sm->get('VehicleTypeTable');
        return $vehicleTypeDb->getVehicleTypeDetails($typeId);
    }
    
    public function getAllVehicleType(){
        $vehicleTypeDb = $this->sm->get('VehicleTypeTable');
        return $vehicleTypeDb->fetchAllVehicleType();
    }
    
    public function getAllVehicleMode(){
        $vehicleModeDb = $this->sm->get('VehicleModeTable');
        return $vehicleModeDb->fetchAllVehicleMode();
    }
}
?>

