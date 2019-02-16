<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class BookingController extends AbstractActionController {

    public function indexAction() {
        /*$request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('BookingService');
            $result = $bookingService->getAllBookings($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }*/
    }
    
    public function pendingAction() {
        $bookingService = $this->getServiceLocator()->get('BookingService');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $result = $bookingService->getAllPendingBookings($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $todayCurrentBooking=$bookingService->getTodayCurrentBookingCount();
        $todayExecutedBooking=$bookingService->getTodayExecutedBookingCount();
        $todayCanceledBooking=$bookingService->getTodayCancelledBookingCount();
        return new ViewModel(array(
            'todayCurrentBooking'=>$todayCurrentBooking,
            'todayExecutedBooking'=>$todayExecutedBooking,
            'todayCanceledBooking'=>$todayCanceledBooking
        ));
    }
    
    public function addAction() {
        $this->layout('layout/modal.phtml');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('BookingService');
            $bookingService->addBooking($params);
            return $this->redirect()->toRoute('booking',array('action'=>'pending'));
        }

        $companyService = $this->getServiceLocator()->get('CompanyService');
        $companyResult=$companyService->getAllActiveCompany();
        $commonService = $this->getServiceLocator()->get('CommonService');
        $businessUnitResult=$commonService->getAllActiveBusinessUnits();
        $paymentModeResult=$commonService->getAllPaymentMode();
        $cityService = $this->getServiceLocator()->get('CityService');
        $cityResult=$cityService->getAllCities();
        $vehicleMakeResult=$commonService->getAllVehicleMake();
        $rentalTypeService = $this->getServiceLocator()->get('RentalTypeService');
        $rentalTypeResult=$rentalTypeService->getAllRentalType();
        $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
        $vehicleTypeResult=$vehicleTypeService->getAllVehicleType();
        return new ViewModel(array(
            'company'=>$companyResult,
            'paymentMode' => $paymentModeResult,
            'businessUnitResult'=>$businessUnitResult,
            'cityResult'=>$cityResult,
            'vehicleMakeTypeResult'=>$vehicleMakeResult,
            'rentalTypeResult'=>$rentalTypeResult,
            'vehicleType'=>$vehicleTypeResult
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $bookingService = $this->getServiceLocator()->get('BookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService->updateBooking($params);
            return $this->redirect()->toRoute('booking',array('action'=>'pending'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $bookingService->getBooking($id);
            
            $companyService = $this->getServiceLocator()->get('CompanyService');
            $companyResult=$companyService->getAllActiveCompany();
            $commonService = $this->getServiceLocator()->get('CommonService');
            $businessUnitResult=$commonService->getAllActiveBusinessUnits();
            $paymentModeResult=$commonService->getAllPaymentMode();
            $cityService = $this->getServiceLocator()->get('CityService');
            $cityResult=$cityService->getAllCities();
            $vehicleMakeResult=$commonService->getAllVehicleMake();
            $rentalTypeService = $this->getServiceLocator()->get('RentalTypeService');
            $rentalTypeResult=$rentalTypeService->getAllRentalType();
            $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
            $vehicleTypeResult=$vehicleTypeService->getAllVehicleType();
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    'company'=>$companyResult,
                    'paymentMode' => $paymentModeResult,
                    'businessUnitResult'=>$businessUnitResult,
                    'cityResult'=>$cityResult,
                    'vehicleMakeTypeResult'=>$vehicleMakeResult,
                    'rentalTypeResult'=>$rentalTypeResult,
                    'vehicleType'=>$vehicleTypeResult
                ));
            } else {
                return $this->redirect()->toRoute('booking',array('action'=>'pending'));
            }
        }
    }

    public function getCorporateBookingNoAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            //$businessUnit=$params['businessUnit'];
            $bookingService = $this->getServiceLocator()->get('BookingService');
            $result=$bookingService->getCorporateBookingNo();
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        } 
    }
    
    public function executeAction() {
        $request = $this->getRequest();
        $bookingService = $this->getServiceLocator()->get('BookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService->executeBooking($params);
            return $this->redirect()->toRoute('booking',array('action'=>'pending'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $bookingService->fetchBooking($id);
            $commonService = $this->getServiceLocator()->get('CommonService');
            //$eployeeService = $this->getServiceLocator()->get('EmployeeService');
            //$driverResult=$eployeeService->getAllActiveDriverList();
            $vehicleCategoryResult=$commonService->getAllVehicleMake();
            $vendorService = $this->getServiceLocator()->get('VendorService');
            $vendorResult=$vendorService->getAllActiveVendorBasedCity($result['city']);
            $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
            $vehicleTypeResult=$vehicleTypeService->getAllVehicleType();
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    'vehicleType'=>$vehicleTypeResult,
                    'vehicleCategoryResult'=>$vehicleCategoryResult,
                    'vendorResult'=>$vendorResult,
                ));
            } else {
                return $this->redirect()->toRoute('booking',array('action'=>'pending'));
            }
        }
    }
    
    public function editExecuteAction() {
        $request = $this->getRequest();
        $bookingService = $this->getServiceLocator()->get('BookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService->executeBooking($params);
            return $this->redirect()->toRoute('booking',array('action'=>'executed-list'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $bookingDetails = $bookingService->fetchBooking($id);
            $result = $bookingService->getTripAssignBooking($id);
            $commonService = $this->getServiceLocator()->get('CommonService');
            
            $vehicleCategoryResult=$commonService->getAllVehicleMake();
            $vendorService = $this->getServiceLocator()->get('VendorService');
            $vendorResult=$vendorService->getAllActiveVendorBasedCity($result['city']);
            $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
            $vehicleTypeResult=$vehicleTypeService->getAllVehicleType();
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    'bookingDetails' => $bookingDetails,
                    'vehicleType'=>$vehicleTypeResult,
                    'vehicleCategoryResult'=>$vehicleCategoryResult,
                    'vendorResult'=>$vendorResult,
                ));
            } else {
                return $this->redirect()->toRoute('booking',array('action'=>'pending'));
            }
        }
    }
    
    public function executedListAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('BookingService');
            $result = $bookingService->getAllExecutedBookings($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function closeTripAction() {
        $request = $this->getRequest();
        $bookingService = $this->getServiceLocator()->get('BookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService->closeBooking($params);
            if(isset($params['comingFrom']) && trim($params['comingFrom'])=='cl0s3'){
                return $this->redirect()->toRoute('booking',array('action'=>'closed-list'));    
            }else{
                return $this->redirect()->toRoute('booking',array('action'=>'executed-list'));    
            }
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $comingFrom = $this->params()->fromRoute('comingFrom');
            $result = $bookingService->getExecutedBooking($id);
            $commonService = $this->getServiceLocator()->get('CommonService');
            $rentalTypeService = $this->getServiceLocator()->get('RentalTypeService');
            $rentalTypeResult=$rentalTypeService->getAllRentalType();
            $vehicleCategory=$commonService->getAllVehicleMake();
            $paymentModeResult=$commonService->getAllPaymentMode();
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    'comingFrom' => $comingFrom,
                    'vehicleCategory'=>$vehicleCategory,
                    'rentalTypeResult'=>$rentalTypeResult,
                    'paymentMode' => $paymentModeResult,
                ));
            } else {
                return $this->redirect()->toRoute('booking',array('action'=>'executed-list'));
            }
        }
    }
    
    public function closedListAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('BookingService');
            $result = $bookingService->getAllClosedBooking($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function addVendorPaymentAction() {
        $request = $this->getRequest();
        $bookingService = $this->getServiceLocator()->get('BookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService->addBookingVendorPayment($params);
            return $this->redirect()->toRoute('booking',array('action'=>'closed-list'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $bookingService->getCloseBooking($id);
            $commonService = $this->getServiceLocator()->get('CommonService');
            $paymentModeResult=$commonService->getAllPaymentMode();
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    'paymentMode' => $paymentModeResult,
                ));
            } else {
                return $this->redirect()->toRoute('booking',array('action'=>'closed-list'));
            }
        }
    }
    
    public function vendorPendingAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('BookingService');
            $result = $bookingService->getAllVendorPendingBooking($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function cancelAction(){
        $request = $this->getRequest();
        $bookingService = $this->getServiceLocator()->get('BookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $addedResult=$bookingService->cancelBooking($params);
            return new ViewModel(array(
                'cancelBooking'=>$addedResult
            ));
        }else{
            $this->layout('layout/modal.phtml');
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $bookingService->getBooking($id);
            return new ViewModel(array(
                'result'=>$result
            ));
        }
    }
    
    public function cancelListAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('BookingService');
            $result = $bookingService->getAllCancelBookings($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function deleteAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $bookingId=base64_decode($params['bookingId']);
            $bookingService = $this->getServiceLocator()->get('BookingService');
            $result=$bookingService->deleteBooking($bookingId);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function editVendorPaymentAction() {
        $request = $this->getRequest();
        $bookingService = $this->getServiceLocator()->get('BookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService->updateBookingVendorPayment($params);
            return $this->redirect()->toRoute('booking',array('action'=>'vendor-pending'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $bookingService->getCloseBooking($id);
            $vendorPaymentResult = $bookingService->getBookingVendorPayment($id);
            $commonService = $this->getServiceLocator()->get('CommonService');
            $paymentModeResult=$commonService->getAllPaymentMode();
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    'paymentMode' => $paymentModeResult,
                    'vendorPaymentResult' => $vendorPaymentResult,
                ));
            } else {
                return $this->redirect()->toRoute('booking',array('action'=>'vendor-pending'));
            }
        }
    }
    
    public function generateTripSheetAction(){
        $id = base64_decode($this->params()->fromRoute('id'));
        $bookingService = $this->getServiceLocator()->get('BookingService');
        $result = $bookingService->fetchBooking($id);
        if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
        }else{
            return $this->redirect()->toRoute('booking',array('action'=>'pending'));
        }
        //\Zend\Debug\Debug::dump($result);die;
    }
    
    public function generateBillAction(){
        $id = base64_decode($this->params()->fromRoute('id'));
        $bookingService = $this->getServiceLocator()->get('BookingService');
        $result = $bookingService->fetchBooking($id);
        if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
        }else{
            return $this->redirect()->toRoute('booking',array('action'=>'pending'));
        }
    }
}

