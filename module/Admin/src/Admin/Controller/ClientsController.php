<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class ClientsController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorService = $this->getServiceLocator()->get('ClientService');
            $result = $vendorService->getAllClients($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        $clientService = $this->getServiceLocator()->get('ClientService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $clientService->addClient($params);
            return $this->redirect()->toRoute("clients");
        }
        $companyService = $this->getServiceLocator()->get('CompanyService');
        $companyResult=$companyService->getAllActiveCompany();
        $clientNo=$clientService->getClientCode();
        return new ViewModel(array(
            'company'=>$companyResult,
            'clientNo'=>$clientNo
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
        $clientService = $this->getServiceLocator()->get('ClientService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $clientService->updateClient($params);
            return $this->redirect()->toRoute("clients");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $companyService = $this->getServiceLocator()->get('CompanyService');
            $companyResult=$companyService->getAllActiveCompany();
            $result = $clientService->getClient($id);
            if ($result) {
                return new ViewModel(array(
                    'company'=>$companyResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("clients");
            }
        }
    }
    
    public function getGuestListAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            //$vendorId=base64_decode($params['vendorId']);
            $clientId=$params['clientId'];
            $vendorService = $this->getServiceLocator()->get('ClientService');
            $result=$vendorService->getGuestDetails($clientId);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function getContactListAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $clientId=$params['clientId'];
            $clientService = $this->getServiceLocator()->get('ClientService');
            $result=$clientService->getContactList($clientId);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function getClientByCompanyAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $companyId=$params['company'];
            $clientService = $this->getServiceLocator()->get('ClientService');
            $result=$clientService->getClientByCompany($companyId);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function viewAction() {
        $id = base64_decode($this->params()->fromRoute('id'));
        $clientService = $this->getServiceLocator()->get('ClientService');
        $result = $clientService->fetchClient($id);
        if ($result) {
            return new ViewModel(array(
                'result' => $result
            ));
        } else {
            return $this->redirect()->toRoute("clients");
        }
    }
    
    public function contractExpiryAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $clientService = $this->getServiceLocator()->get('ClientService');
            $result = $clientService->fetchContractExpiryList($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function exportClientDetailsInExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $clientService = $this->getServiceLocator()->get('ClientService');
            $result=$clientService->exportClientDetails($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}

