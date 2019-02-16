<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class RetailController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $retailService = $this->getServiceLocator()->get('RetailService');
            $result = $retailService->getAllRetailTargetDetails($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $retailService = $this->getServiceLocator()->get('RetailService');
            $retailService->addRetailTarget($params);
            return $this->redirect()->toRoute('retail');
        }
    }

    public function editAction() {
        $request = $this->getRequest();
        $retailService = $this->getServiceLocator()->get('RetailService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $retailService->updateRetailTarget($params);
            return $this->redirect()->toRoute('retail');
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $retailService->getRetailTarget($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('retail');
            }
        }
    }

    public function dailySalesAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $retailService = $this->getServiceLocator()->get('RetailService');
            $result = $retailService->getAllRetailDailySales($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function addDailySalesAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $retailService = $this->getServiceLocator()->get('RetailService');
            $retailService->addRetailDailySales($params);
            return $this->redirect()->toRoute('retail',array('action'=>'daily-sales'));
        }
        $configService = $this->getServiceLocator()->get('ConfigService');
        $targetResult=$configService->getTargetValue('retail');
        return new ViewModel(array(
            'targetResult' => $targetResult
        ));
    }
    
    public function editDailySalesAction() {
        $request = $this->getRequest();
        $retailService = $this->getServiceLocator()->get('RetailService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $retailService->updateRetailDailySales($params);
            return $this->redirect()->toRoute('retail',array('action'=>'daily-sales'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $retailService->getRetailDailySales($id);
            $configService = $this->getServiceLocator()->get('ConfigService');
            $targetResult=$configService->getTargetValue('retail');
            if ($result) {
                return new ViewModel(array(
                    'targetResult' => $targetResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('retail',array('action'=>'daily-sales'));
            }
        }
    }
    
    public function exportToExcelDailySalesAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $retailService = $this->getServiceLocator()->get('RetailService');
            $result=$retailService->exportRetailDailySales($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}

