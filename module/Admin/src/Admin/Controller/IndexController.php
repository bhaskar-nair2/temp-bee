<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class IndexController extends AbstractActionController{

    public function indexAction(){
        $sessionLogin = new Container('credo');
        if(isset($sessionLogin->roleId) && trim($sessionLogin->roleId)!=1){
            return $this->redirect()->toRoute("home");
        }
        $corporateService = $this->getServiceLocator()->get('CorporateService');
        $corCurrentTargetCount=$corporateService->getCurrentTarget('corporate');
        $marriottCurrentTargetCount=$corporateService->getCurrentTarget('marriott');
        $retailCurrentTargetCount=$corporateService->getCurrentTarget('retail');
        $tajCurrentTargetCount=$corporateService->getCurrentTarget('taj');
       
        $clientService = $this->getServiceLocator()->get('ClientService');
        $clientContractExpiryResult=$clientService->getContractExpiryList();
       
        $empService = $this->getServiceLocator()->get('EmployeeService');
        $empLicenseExpiryResult=$empService->getEmployeeLicenseExpiryList();
       
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        $workOrderResult=$vehicleService->getPendingWorkOrderList();
       
        //Monthly bill
        $billService = $this->getServiceLocator()->get('BillingService');
        $pendingPaymentResult=$billService->getPendingPaymentList();
       
       return new ViewModel(array(
            'corporateCount' => $corCurrentTargetCount,
            'marriottCount' => $marriottCurrentTargetCount,
            'retailCount' => $retailCurrentTargetCount,
            'tajCount' => $tajCurrentTargetCount,
            'clientContractExpiryResult' => $clientContractExpiryResult,
            'employeeExpiryResult' => $empLicenseExpiryResult,
            'workOrderResult' => $workOrderResult,
            'monthlyPendingPayment' => $pendingPaymentResult
        ));
    }

	public function operationAction(){
		$vehicleService = $this->getServiceLocator()->get('VehicleService');
        $renewalResult=$vehicleService->getVehicleRenewalResult();
		$empService = $this->getServiceLocator()->get('EmployeeService');
        $empLicenseExpiryResult=$empService->getEmployeeLicenseExpiryList();
		$bookingService = $this->getServiceLocator()->get('BookingService');
		$todayCurrentBooking=$bookingService->getTodayCurrentBookingCount();
        $todayExecutedBooking=$bookingService->getTodayExecutedBookingCount();
        $todayCanceledBooking=$bookingService->getTodayCancelledBookingCount();
		return new ViewModel(array(
			'renewalResult' => $renewalResult,
			'employeeExpiryResult' => $empLicenseExpiryResult,
			'todayCurrentBooking'=>$todayCurrentBooking,
            'todayExecutedBooking'=>$todayExecutedBooking,
            'todayCanceledBooking'=>$todayCanceledBooking
		));
	}
	
	public function financialAction(){
		$vndorService = $this->getServiceLocator()->get('VendorService');
        $vendorPaymentResult=$vndorService->getVendorPendingPayments();
		$clientService = $this->getServiceLocator()->get('ClientService');
        $clientPaymentResult=$clientService->getClientPendingPayments();
		
		$bookingService = $this->getServiceLocator()->get('BookingService');
		$todayCurrentBooking=$bookingService->getTodayCurrentBookingCount();
        $todayExecutedBooking=$bookingService->getTodayExecutedBookingCount();
        $todayCanceledBooking=$bookingService->getTodayCancelledBookingCount();
		
		//\Zend\Debug\Debug::dump($renewalResult);
		return new ViewModel(array(
			'vendorPaymentResult' => $vendorPaymentResult,
			'clientPaymentResult' => $clientPaymentResult,
			'todayCurrentBooking'=>$todayCurrentBooking,
            'todayExecutedBooking'=>$todayExecutedBooking,
            'todayCanceledBooking'=>$todayCanceledBooking
		));
	}
}

