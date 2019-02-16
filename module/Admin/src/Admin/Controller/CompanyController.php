<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class CompanyController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $companyService = $this->getServiceLocator()->get('CompanyService');
            $result = $companyService->getAllCompany($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $companyService = $this->getServiceLocator()->get('CompanyService');
            $companyService->addCompany($params);
            return $this->redirect()->toRoute("company");
        }

        return new ViewModel();
    }

    public function editAction() {
        $request = $this->getRequest();
        $companyService = $this->getServiceLocator()->get('CompanyService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $companyService->updateCompany($params);
            return $this->redirect()->toRoute("company");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $companyService->getCompany($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("company");
            }
        }
    }

    public function viewAction() {
        $request = $this->getRequest();
        $companyService = $this->getServiceLocator()->get('CompanyService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $companyService->updateCompany($params);
            return $this->redirect()->toRoute("company");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $companyService->getCompany($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("company");
            }
        }
    }
   
}

