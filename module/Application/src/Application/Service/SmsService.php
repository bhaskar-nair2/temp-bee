<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class SmsService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function updateSmsTemplate($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('SmsTemplateTable');
            $result = $db->updateSmsTemplateDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Sms template details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllSmsTemplate($parameters){
        $db = $this->sm->get('SmsTemplateTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllSmsTemplate($parameters,$acl);
    }
    
    public function getSmsTemplate($templateId){
        $db = $this->sm->get('SmsTemplateTable');
        return $db->getSmsTemplateDetails($templateId);
    }
}
?>

