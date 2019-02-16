<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class MonthlyBillingController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $billingService = $this->getServiceLocator()->get('BillingService');
            $result = $billingService->getAllCompletedMonthlyBills($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        $billingService = $this->getServiceLocator()->get('BillingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $billingService->addMonthlyBill($params);
            return $this->redirect()->toRoute('monthly-billing');
        }
        $companyService = $this->getServiceLocator()->get('CompanyService');
        $companyResult=$companyService->getAllCompanyList();
        $commonService = $this->getServiceLocator()->get('CommonService');
        $unitResult=$commonService->getAllActiveBusinessUnits();
        
        $paymentModeResult=$commonService->getAllPaymentMode();
        $configService = $this->getServiceLocator()->get('ConfigService');
        $monthlyInvoiceCode=$configService->getGlobalValue('monthly_invoice_code');
        //$monthlyInvoiceNo=$billingService->getCurrentInvoiceNo();
		$employeeService = $this->getServiceLocator()->get('EmployeeService');
        $employeeResult=$employeeService->getAllActiveEmployeesExceptDriver();
        return new ViewModel(array(
            'company'=>$companyResult,
            'businessUnit'=>$unitResult,
            'paymentMode' => $paymentModeResult,
            'monthlyInvoiceCode' => $monthlyInvoiceCode,
            //'monthlyInvoiceNo' => $monthlyInvoiceNo,
            'employee' => $employeeResult,
        ));
    }

	public function editAction() {
        $request = $this->getRequest();
		$billingService = $this->getServiceLocator()->get('BillingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $billingService->updateMonthlyBill($params);
            return $this->redirect()->toRoute('monthly-billing');
        }else{
			$companyService = $this->getServiceLocator()->get('CompanyService');
			$companyResult=$companyService->getAllCompanyList();
			$commonService = $this->getServiceLocator()->get('CommonService');
			$unitResult=$commonService->getAllActiveBusinessUnits();
			$vendorService = $this->getServiceLocator()->get('ClientService');
			$vendorResult=$vendorService->getAllActiveClients();
			$paymentModeResult=$commonService->getAllPaymentMode();
			$employeeService = $this->getServiceLocator()->get('EmployeeService');
			$employeeResult=$employeeService->getAllActiveEmployeesExceptDriver();
			$configService = $this->getServiceLocator()->get('ConfigService');
			$monthlyInvoiceCode=$configService->getGlobalValue('monthly_invoice_code');
			
			$id = base64_decode($this->params()->fromRoute('id'));
			$result = $billingService->getBillingDetails($id);
			if ($result) {
				return new ViewModel(array(
					'result' => $result,
					'company'=>$companyResult,
					'businessUnit'=>$unitResult,
					'vendor'=>$vendorResult,
					'paymentMode' => $paymentModeResult,
					'monthlyInvoiceCode' => $monthlyInvoiceCode,
					'employee' => $employeeResult
				));
			}else {
                return $this->redirect()->toRoute("monthly-billing");
            }
		}
    }
	
	public function generateBillingAction(){
		$billId = (int) base64_decode($this->params()->fromRoute('id'));
		if($billId>0){
			$billingService = $this->getServiceLocator()->get('BillingService');
			$result = $billingService->getMonthlyBillingDetails($billId);
			if ($result) {
				$viewModel= new ViewModel(array(
					'result' => $result
				));
				$viewModel->setTerminal(true);
				return $viewModel;
			}else {
                return $this->redirect()->toRoute("monthly-billing");
            }
			
		}else{
			return $this->redirect()->toRoute("monthly-billing");
		}
	}
	
	public function getInvoiceNoAction(){
		$request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $company=$params['company'];
            $billingService = $this->getServiceLocator()->get('BillingService');
            $result=$billingService->getInvoiceNoBasedOnCompany($company);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
	}
	
	public function pendingBillAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $billingService = $this->getServiceLocator()->get('BillingService');
            $result = $billingService->getAllPendingMonthlyBills($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
	
	public function deleteAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
			$billId=base64_decode($params['billId']);
            $billingService = $this->getServiceLocator()->get('BillingService');
            $result = $billingService->deleteMonthlyBill($billId);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
	
	public function viewAction() {
		$id = base64_decode($this->params()->fromRoute('id'));
		$billingService = $this->getServiceLocator()->get('BillingService');
		$result = $billingService->getMonthlyBillingDetails($id);
		if ($result) {
			return new ViewModel(array(
				'result' => $result
			));
		}else {
			return $this->redirect()->toRoute("monthly-billing");
		}
    }
	
	public function pendingBillExportToExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $billingService = $this->getServiceLocator()->get('BillingService');
            $result=$billingService->exportPendingMonthlyBill($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
	
	public function completedBillExportToExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $billingService = $this->getServiceLocator()->get('BillingService');
            $result=$billingService->exportCompletedMonthlyBill($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
	
	public function allBillAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $billingService = $this->getServiceLocator()->get('BillingService');
            $result = $billingService->getAllMonthlyBills($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
	
	public function allBillExportToExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $billingService = $this->getServiceLocator()->get('BillingService');
            $result=$billingService->exportAllMonthlyBill($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}

