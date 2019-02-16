<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class CymRentalService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function updateRental($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $rentalsDb = $this->sm->get('CymTariffTable ');
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
    
    public function getAllCymRentals($parameters){
        $rentalsDb = $this->sm->get('CymTariffTable');
        return $rentalsDb->fetchAllCymRentals($parameters);
    }
    
    public function getCymRental($id){
        $rentalsDb = $this->sm->get('CymTariffTable');
        return $rentalsDb->getCymRentalDetails($id);
    }
}
?>

