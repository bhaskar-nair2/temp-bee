<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class CorporateController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $corporateService = $this->getServiceLocator()->get('CorporateService');
            $result = $corporateService->getAllCorporateTargetDetails($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $corporateService = $this->getServiceLocator()->get('CorporateService');
            $corporateService->addCorporateTarget($params);
            return $this->redirect()->toRoute('corporate');
        }
    }

    public function editAction() {
        $request = $this->getRequest();
        $corporateService = $this->getServiceLocator()->get('CorporateService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $corporateService->updateCorporateTarget($params);
            return $this->redirect()->toRoute('corporate');
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $corporateService->getCorporateTarget($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('corporate');
            }
        }
    }
    
    public function dailySalesAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $corporateService = $this->getServiceLocator()->get('CorporateService');
            $result = $corporateService->getAllCorporateDailySales($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function addDailySalesAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $corporateService = $this->getServiceLocator()->get('CorporateService');
            $corporateService->addCorporateDailySales($params);
            return $this->redirect()->toRoute('corporate',array('action'=>'daily-sales'));
        }
        $configService = $this->getServiceLocator()->get('ConfigService');
        $targetResult=$configService->getTargetValue('corporate');
        return new ViewModel(array(
            'targetResult' => $targetResult
        ));
    }
    
    public function editDailySalesAction() {
        $request = $this->getRequest();
        $corporateService = $this->getServiceLocator()->get('CorporateService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $corporateService->updateCorporateDailySales($params);
            return $this->redirect()->toRoute('corporate',array('action'=>'daily-sales'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $corporateService->getCorporateDailySales($id);
            $configService = $this->getServiceLocator()->get('ConfigService');
            $targetResult=$configService->getTargetValue('corporate');
        
            if ($result) {
                return new ViewModel(array(
                    'targetResult' => $targetResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('corporate',array('action'=>'daily-sales'));
            }
        }
    }
    
    public function exportToExcelDailySalesAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $corporateService = $this->getServiceLocator()->get('CorporateService');
            $result=$corporateService->exportCorporateDailySales($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}

