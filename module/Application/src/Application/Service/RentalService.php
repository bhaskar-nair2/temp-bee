<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class RentalService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addRental($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $rentalsDb = $this->sm->get('RentalsTable');
            $result = $rentalsDb->addRentalDetails($params);
            if($result>0){
                $adapter->commit();
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'Rental details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateRental($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $rentalsDb = $this->sm->get('RentalsTable');
            $result = $rentalsDb->updateRentalDetails($params);
            if($result>0){
                $adapter->commit();
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'Rental details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllRentals($parameters){
        $rentalsDb = $this->sm->get('RentalsTable');
        return $rentalsDb->fetchAllRentals($parameters);
    }
    
    public function getRental($id){
        $rentalsDb = $this->sm->get('RentalsTable');
        return $rentalsDb->getRentalDetails($id);
    }
    
    public function getVehicleTypeByVendor($vendorId,$companyId,$bookingType){
        $rentalsDb = $this->sm->get('RentalsTable');
        return $rentalsDb->fetchVehicleTypeByVendor($vendorId,$companyId,$bookingType);
    }
    
    public function getRentalTypeByVehicle($vendorId,$companyId,$vehicleType,$bookingType){
        $rentalsDb = $this->sm->get('RentalsTable');
        return $rentalsDb->fetchRentalTypeByVehicle($vendorId,$companyId,$vehicleType,$bookingType);
    }
    
    public function getTariffByRentalId($companyId,$clientId,$businessUnit,$city,$vehicleCategory,$dutyType){
        $tariffDb = $this->sm->get('TariffDetailsTable');
        return $tariffDb->fetchTariffByRentalId($companyId,$clientId,$businessUnit,$city,$vehicleCategory,$dutyType);
    }
    
    public function getTariffAmt($params){
        $tariffDb = $this->sm->get('TariffDetailsTable');
        return $tariffDb->getTariffAmtDetails($params);
    }
}
?>

