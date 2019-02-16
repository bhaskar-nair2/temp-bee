<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CronController extends AbstractActionController{

    public function indexAction(){
       
    }

    public function sendMailAction(){
      $temp = $this->getServiceLocator()->get('CommonService');
      $temp->sendTempMail();
    }
    
    public function updateVendorCurrentBalanceAction(){
        $temp = $this->getServiceLocator()->get('CommonService');
        $temp->updateAllVendorCurrentBalance();
    }
}

