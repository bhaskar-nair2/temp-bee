<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class VendorController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorService = $this->getServiceLocator()->get('VendorService');
            $result = $vendorService->getAllVendors($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorService->addVendor($params);
            return $this->redirect()->toRoute("vendor");
        }
        $vendorNo=$vendorService->getVendorCode();
        $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
        $vehicleTypeResult=$vehicleTypeService->getAllVehicleType();
        $commonService = $this->getServiceLocator()->get('CommonService');
        $vehicleCategoryResult=$commonService->getAllVehicleMake();
        $cityService = $this->getServiceLocator()->get('CityService');
        $cityResult=$cityService->getAllCities();
        return new ViewModel(array(
            'vendorNo'=>$vendorNo,
            'vehicleTypeResult'=>$vehicleTypeResult,
            'vehicleCategoryResult'=>$vehicleCategoryResult,
            'cityResult'=>$cityResult
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorService->updateVendor($params);
            return $this->redirect()->toRoute("vendor");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $vendorService->getVendor($id);
            $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
            $vehicleTypeResult=$vehicleTypeService->getAllVehicleType();
            $commonService = $this->getServiceLocator()->get('CommonService');
            $vehicleCategoryResult=$commonService->getAllVehicleMake();
            $cityService = $this->getServiceLocator()->get('CityService');
            $cityResult=$cityService->getAllCities();
            if ($result) {
                return new ViewModel(array(
                    'vehicleTypeResult'=>$vehicleTypeResult,
                    'vehicleCategoryResult'=>$vehicleCategoryResult,
                    'cityResult'=>$cityResult,
                    'result'=>$result
                ));
            } else {
                return $this->redirect()->toRoute("vendor");
            }
        }
    }
    
    public function pendingPaymentAction(){
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $vendorService->getAllVendorPendingPayment($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $vendorResult=$vendorService->getAllActiveVendor();
        return new ViewModel(array(
            'vendorList'=>$vendorResult
        ));
    }
    
    public function addPaymentAction(){
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        $configService = $this->getServiceLocator()->get('ConfigService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorService->addVendorPayment($params);
            return $this->redirect()->toRoute('vendor',array('action'=>'view-ledger','id'=>$params['vendor']));
        }
        $vendorTax=$configService->getGlobalValue('vendor_tax');
        $vendorResult=$vendorService->getAllActiveVendor();
        return new ViewModel(array(
            'vendorList'=>$vendorResult,
            'vendorTax'=>$vendorTax
        ));
    }
    
    public function editPaymentAction(){
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        $configService = $this->getServiceLocator()->get('ConfigService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorService->updateVendorPayment($params);
            return $this->redirect()->toRoute('vendor',array('action'=>'view-ledger','id'=>$params['vendor']));
        }else{
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $vendorService->getVendorPayment($id);
            $vendorTax=$configService->getGlobalValue('vendor_tax');
            $vendorResult=$vendorService->getAllActiveVendor();
            return new ViewModel(array(
                'vendorList'=>$vendorResult,
                'vendorTax'=>$vendorTax,
                'result'=>$result
            ));
        }
    }
    
    public function viewPaymentAction(){
        $vendorService = $this->getServiceLocator()->get('VendorService');
        $id = base64_decode($this->params()->fromRoute('id'));
        $result = $vendorService->fetchVendorPayment($id);
        return new ViewModel(array(
            'result'=>$result
        ));
        
    }
    
    public function exportToExcelVendorPaymentAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $vendorService = $this->getServiceLocator()->get('VendorService');
            $result=$vendorService->exportVendorPayments($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function makePaymentAction(){
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $addedMakePayment=$vendorService->makeVendorPayment($params);
            return new ViewModel(array(
                'makePayment'=>$addedMakePayment
            ));
        }else{
            $this->layout('layout/modal.phtml');
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $vendorService->fetchVendorPayment($id);
            $paidResult = $vendorService->getVendorPaidDetails($id);
            $paymentNo=$vendorService->getVendorPaymentCode();
            return new ViewModel(array(
                'result'=>$result,
                'paidResult'=>$paidResult,
                'paymentNo'=>$paymentNo
            ));
        }
    }
    
    public function completedPaymentAction(){
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $vendorService->getAllVendorCompletedPayment($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $vendorResult=$vendorService->getAllActiveVendor();
        return new ViewModel(array(
            'vendorList'=>$vendorResult
        ));
    }
    
    public function exportCompleteVendorPaymentToExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $vendorService = $this->getServiceLocator()->get('VendorService');
            $result=$vendorService->exportCompleteVendorPayments($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function deletePaymentAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
			$paymentId=base64_decode($params['paymentId']);
            $vendorService = $this->getServiceLocator()->get('VendorService');
            $result = $vendorService->deleteVendorPayment($paymentId);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function addRentalAction() {
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorService->addVendorRental($params);
            return $this->redirect()->toRoute("vendor");
        }
        else{
            $id = base64_decode($this->params()->fromRoute('id'));
            $vendorResult = $vendorService->getVendorInfo($id);
            if($vendorResult!=""){
                return new ViewModel(array(
                    'vendor'=>$vendorResult
                ));
            }else{
                return $this->redirect()->toRoute("vendor");   
            }
        }
    }
    
    public function editRentalAction() {
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendorService->updateVendorRental($params);
            return $this->redirect()->toRoute("vendor");
        }
        else{
            $id = base64_decode($this->params()->fromRoute('id'));
            $vendorResult = $vendorService->getVendorRentals($id);
            if($vendorResult!=""){
                return new ViewModel(array(
                    'result'=>$vendorResult
                ));
            }else{
                return $this->redirect()->toRoute("vendor");   
            }
        }
    }
    
    public function allPaymentAction(){
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $vendorService->getAllVendorPayments($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $vendorResult=$vendorService->getAllActiveVendor();
        return new ViewModel(array(
            'vendorList'=>$vendorResult
        ));
    }
    
    public function getVehicleNoAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vendor=$params['vendor'];
            $vendorService = $this->getServiceLocator()->get('VendorService');
            $result=$vendorService->getVehicleNoByVendor($vendor);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function ledgerAction() {
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $vendorService->getAllVendorLedgers($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        //$commonService = $this->getServiceLocator()->get('CommonService');
        //$commonService->updateAllVendorCurrentBalance();
       
        $vendorResult=$vendorService->getAllActiveVendor();
        return new ViewModel(array(
            'vendorList'=>$vendorResult
        ));
    }
    
    public function viewLedgerAction() {
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $vendorService->getVendorPaymentList($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $vendorId = base64_decode($this->params()->fromRoute('id'));
        return new ViewModel(array(
            'vendorId'=>$vendorId
        ));
    }
    
    public function paidListAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $paymentId=$params['paymentId'];
            $vendorService = $this->getServiceLocator()->get('VendorService');
            $result = $vendorService->getVendorPaidDetails($paymentId);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function downloadPdfReportAction() {
        $vendorId=base64_decode($this->params()->fromRoute('id'));
        $fDate=$this->params()->fromRoute('fDate');
        $tDate=$this->params()->fromRoute('tDate');
        if(trim($fDate)!=""){
            $fDate=date('Y-m-d',strtotime($fDate.'-01'));
        }
        if(trim($tDate)!=""){
            $tDate=date('Y-m-t',strtotime($tDate));
        }
        $vendorService = $this->getServiceLocator()->get('VendorService');
        $result = $vendorService->getVendorPaymentReports($vendorId,$fDate,$tDate);
        if ($result) {
            $viewModel= new ViewModel(array(
                'result' => $result
            ));
			$viewModel->setTerminal(true);
            return $viewModel;
        }else {
            return $this->redirect()->toRoute('vendor',array('action'=>'ledger'));
        }
    }
    
    public function addAdvanceAction(){
        $request = $this->getRequest();
        $vendorService = $this->getServiceLocator()->get('VendorService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $vendorService->addVendorAdvance($params);
            //return $this->getResponse()->setContent(Json::encode($result));
        }
        $vendorResult=$vendorService->getAllActiveVendor();
        return new ViewModel(array(
            'vendorList'=>$vendorResult
        ));
    }
}

