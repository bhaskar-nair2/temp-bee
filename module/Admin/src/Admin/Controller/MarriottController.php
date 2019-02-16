<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class MarriottController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $marriottService = $this->getServiceLocator()->get('MarriottService');
            $result = $marriottService->getAllMarriott($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $marriottService = $this->getServiceLocator()->get('MarriottService');
            $marriottService->addMarriottTarget($params);
            return $this->redirect()->toRoute('marriott');
        }
    }
    
    public function editAction() {
        $request = $this->getRequest();
        $marriottService = $this->getServiceLocator()->get('MarriottService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $marriottService->updateMarriottTarget($params);
            return $this->redirect()->toRoute('marriott');
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $marriottService->getMarriott($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('marriott',array('action'=>'daily-sales'));
            }
        }
    }
    
    public function addDailySalesAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $marriottService = $this->getServiceLocator()->get('MarriottService');
            $marriottService->addMarriottDailySales($params);
            return $this->redirect()->toRoute('marriott',array('action'=>'daily-sales'));
        }
        $configService = $this->getServiceLocator()->get('ConfigService');
        $targetResult=$configService->getTargetValue('marriott');
        return new ViewModel(array(
            'targetResult' => $targetResult
        ));
    }
    
    public function editDailySalesAction() {
        $request = $this->getRequest();
        $marriottService = $this->getServiceLocator()->get('MarriottService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $marriottService->updateMarriottDailySales($params);
            return $this->redirect()->toRoute('marriott',array('action'=>'daily-sales'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $marriottService->getMarriottDailySales($id);
            $configService = $this->getServiceLocator()->get('ConfigService');
            $targetResult=$configService->getTargetValue('marriott');
            if ($result) {
                return new ViewModel(array(
                    'targetResult' => $targetResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('marriott',array('action'=>'daily-sales'));
            }
        }
    }
    
    public function dailySalesAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $marriottService = $this->getServiceLocator()->get('MarriottService');
            $result = $marriottService->getAllDailySales($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function exportToExcelDailySalesAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $marriottService = $this->getServiceLocator()->get('MarriottService');
            $result=$marriottService->exportMarriottDailySales($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
   
}

