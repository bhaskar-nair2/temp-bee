<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class RentalTypeService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function getAllRentalType(){
        $rentalTypeDb = $this->sm->get('RentalTypeTable');
        return $rentalTypeDb->fetchAllRentalType();
    }
   
}
?>

