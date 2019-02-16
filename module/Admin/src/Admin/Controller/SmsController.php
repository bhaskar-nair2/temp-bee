<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class SmsController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $smsService = $this->getServiceLocator()->get('SmsService');
            $result = $smsService->getAllSmsTemplate($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function editAction() {
        $request = $this->getRequest();
        $smsService = $this->getServiceLocator()->get('SmsService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $smsService->updateSmsTemplate($params);
            return $this->redirect()->toRoute("sms");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $smsService->getSmsTemplate($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("sms");
            }
        }
    }
   
}

