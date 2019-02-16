<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;

class ConfigService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function getAllConfig($params){
        $configDb = $this->sm->get('GlobalConfigTable');
        return $configDb->fetchAllConfig($params);
    }
    
    public function getAllGlobalConfig(){
        $globalDb = $this->sm->get('GlobalConfigTable');
        return $globalDb->fetchAllGlobalConfig();        
    }
    
    public function getTargetValue($unitName){
        $db = $this->sm->get('CorporateTable');
        return $db->fetchTargetValue($unitName);
    }
    
    public function updateConfig($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('GlobalConfigTable');
            $result = $db->updateConfigDetails($params);
            $adapter->commit();
            $alertContainer = new Container('alert');
            $alertContainer->alertMsg = 'Config details updated successfully';
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getGlobalValue($globalName){
        $db = $this->sm->get('GlobalConfigTable');
        return $db->fetchGlobalValue($globalName);
    }
}
?>

