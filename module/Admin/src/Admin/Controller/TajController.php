<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class TajController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $tajService = $this->getServiceLocator()->get('TajService');
            $result = $tajService->getAllTajTargetDetails($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $tajService = $this->getServiceLocator()->get('TajService');
            $tajService->addTajTarget($params);
            return $this->redirect()->toRoute('taj');
        }
    }

    public function editAction() {
        $request = $this->getRequest();
        $tajService = $this->getServiceLocator()->get('TajService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $tajService->updateTajTarget($params);
            return $this->redirect()->toRoute('taj');
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $tajService->getTajTarget($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('taj');
            }
        }
    }
    
    public function dailySalesAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $tajService = $this->getServiceLocator()->get('TajService');
            $result = $tajService->getAllTajDailySales($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function addDailySalesAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $tajService = $this->getServiceLocator()->get('TajService');
            $tajService->addTajDailySales($params);
            return $this->redirect()->toRoute('taj',array('action'=>'daily-sales'));
        }
        $configService = $this->getServiceLocator()->get('ConfigService');
        $targetResult=$configService->getTargetValue('taj');
        return new ViewModel(array(
            'targetResult' => $targetResult
        ));
    }
    
    public function editDailySalesAction() {
        $request = $this->getRequest();
        $tajService = $this->getServiceLocator()->get('TajService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $tajService->updateTajDailySales($params);
            return $this->redirect()->toRoute('taj',array('action'=>'daily-sales'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $tajService->getTajDailySales($id);
            $configService = $this->getServiceLocator()->get('ConfigService');
            $targetResult=$configService->getTargetValue('taj');
            if ($result) {
                return new ViewModel(array(
                    'targetResult' => $targetResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('taj',array('action'=>'daily-sales'));
            }
        }
    }
    
    public function exportToExcelDailySalesAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $tajService = $this->getServiceLocator()->get('TajService');
            $result=$tajService->exportTajDailySales($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}

