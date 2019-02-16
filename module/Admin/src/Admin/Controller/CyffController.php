<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class CyffController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $cyffService = $this->getServiceLocator()->get('CyffService');
            $result = $cyffService->getAllCyffDailySales($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $cyffService = $this->getServiceLocator()->get('CyffService');
            $cyffService->addCyffDailySales($params);
            return $this->redirect()->toRoute("cyff");
        }

        return new ViewModel();
    }

    public function editAction() {
        $request = $this->getRequest();
        $cyffService = $this->getServiceLocator()->get('CyffService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $cyffService->updateCyffDailySales($params);
            return $this->redirect()->toRoute("cyff");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $cyffService->getCyffDailySales($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("cyff");
            }
        }
    }

    public function exportToExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $cyffService = $this->getServiceLocator()->get('CyffService');
            $result=$cyffService->exportDailySales($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
   
}

