<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class RentalsController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $rentalService = $this->getServiceLocator()->get('RentalService');
            $result = $rentalService->getAllRentals($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $rentalService = $this->getServiceLocator()->get('RentalService');
            $rentalService->addRental($params);
            return $this->redirect()->toRoute("rentals");
        }
        $companyService = $this->getServiceLocator()->get('CompanyService');
        $companyResult=$companyService->getAllActiveCompany();
        $commonService = $this->getServiceLocator()->get('CommonService');
        $cityService = $this->getServiceLocator()->get('CityService');
        $cityResult=$cityService->getAllCities();
        $businessUnitResult=$commonService->getAllActiveBusinessUnits();
        //$clientService = $this->getServiceLocator()->get('ClientService');
        //$clientResult=$clientService->getAllActiveBeecabsClients();
        
        return new ViewModel(array(
            'company'=>$companyResult,
            'cityResult'=>$cityResult,
            'businessUnitResult'=>$businessUnitResult
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $rentalService = $this->getServiceLocator()->get('RentalService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $rentalService->updateRental($params);
            return $this->redirect()->toRoute("rentals");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $rentalService->getRental($id);
            $companyService = $this->getServiceLocator()->get('CompanyService');
            $companyResult=$companyService->getAllActiveCompany();
            $commonService = $this->getServiceLocator()->get('CommonService');
            $cityService = $this->getServiceLocator()->get('CityService');
            $cityResult=$cityService->getAllCities();
            $businessUnitResult=$commonService->getAllActiveBusinessUnits();
            
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    'company'=>$companyResult,
                    'cityResult'=>$cityResult,
                    'businessUnitResult'=>$businessUnitResult
                ));
            } else {
                return $this->redirect()->toRoute("rentals");
            }
        }
    }

    public function getVehicleTypeAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorId=base64_decode($params['vendorId']);
            $branchId=$params['branchId'];
            $bookingType=$params['bookingType'];
            $rentalService = $this->getServiceLocator()->get('RentalService');
            $result=$rentalService->getVehicleTypeByVendor($vendorId,$branchId,$bookingType);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        } 
    }
    
    public function getRentalTypeAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorId=base64_decode($params['vendorId']);
            $vehicleType=base64_decode($params['vehicleType']);
            $branchId=$params['branchId'];
            $bookingType=$params['bookingType'];
            $rentalService = $this->getServiceLocator()->get('RentalService');
            $result=$rentalService->getRentalTypeByVehicle($vendorId,$branchId,$vehicleType,$bookingType);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function getTariffAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $companyId=(int) $params['companyId'];
            $clientId=(int) $params['client'];
            $businessUnit=(int) $params['businessUnit'];
            $city=(int) $params['bookingCity'];
            $vehicleCategory=(int) $params['vehicleCategory'];
            $dutyType=(int) $params['dutyType'];
            $rentalService = $this->getServiceLocator()->get('RentalService');
            $result=$rentalService->getTariffByRentalId($companyId,$clientId,$businessUnit,$city,$vehicleCategory,$dutyType);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function getTariffAmtAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $rentalService = $this->getServiceLocator()->get('RentalService');
            $result=$rentalService->getTariffAmt($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
}

