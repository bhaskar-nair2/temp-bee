<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Zend\Session\Container;
class HotelBookingController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result = $bookingService->getAllHotelBookings($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('HotelBookingService');
            $bookingService->addHotelBooking($params);
            return $this->redirect()->toRoute('hotel-booking',array('action'=>'add'));
        }
        $logincontainer = new Container('credo');
        //$hotelService = $this->getServiceLocator()->get('HotelService');
        //$hotelResult=$hotelService->getAllActiveHotels();
        $rentalTypeService = $this->getServiceLocator()->get('RentalTypeService');
        $rentalTypeResult=$rentalTypeService->getAllRentalType();
        $eployeeService = $this->getServiceLocator()->get('EmployeeService');
        $driverResult=$eployeeService->getAllActiveBusinessUnitDriverList();
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        $vehicleResult=$vehicleService->getAllActiveHotelVehicles();
        $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
        //$clientId=$logincontainer->clientId;
        $clientId=1;
        $bookingRef=$hotelBookingService->generateBookingRef($clientId);
        return new ViewModel(array(
            //'hotelResult'=>$hotelResult,
            'rentalTypeResult'=>$rentalTypeResult,
            'driverResult'=>$driverResult,
            'vehicleResult'=>$vehicleResult,
            'bookingRef'=>$bookingRef,
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $hotelBookingService->updateHotelBooking($params);
            return $this->redirect()->toRoute('hotel-booking',array('action'=>'reports'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $comingFrom = $this->params()->fromRoute('comingFrom');
            $result = $hotelBookingService->getHotelBooking($id);
            
            $hotelService = $this->getServiceLocator()->get('HotelService');
            $hotelResult=$hotelService->getAllActiveHotels();
            $rentalTypeService = $this->getServiceLocator()->get('RentalTypeService');
            $rentalTypeResult=$rentalTypeService->getAllRentalType();
            $eployeeService = $this->getServiceLocator()->get('EmployeeService');
            $driverResult=$eployeeService->getAllActiveDriverList();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $vehicleResult=$vehicleService->getAllActiveHotelVehicles();
            if ($result) {
                return new ViewModel(array(
                    'hotelResult'=>$hotelResult,
                    'rentalTypeResult'=>$rentalTypeResult,
                    'driverResult'=>$driverResult,
                    'vehicleResult'=>$vehicleResult,
                    'result' => $result,
                    'comingFrom' => $comingFrom,
                ));
            } else {
                return $this->redirect()->toRoute('hotel-booking',array('action'=>'index'));
            }
        }
    }
    
    public function completeAction(){
        
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result=$hotelBookingService->completeHotelBooking($params['bookingId'],$params['bookingNo']);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function cancelAction() {
        $request = $this->getRequest();
        $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $hotelBookingService->cancelHotelBooking($params);
            return $this->redirect()->toRoute('hotel-booking',array('action'=>'index'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $hotelBookingService->getHotelBookingDetails($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('hotel-booking',array('action'=>'index'));
            }
        }
    }
    
    public function generateTripSheetAction(){
        $id = base64_decode($this->params()->fromRoute('id'));
        $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
        $result = $hotelBookingService->getHotelBookingDetails($id);
        if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
        }else{
            return $this->redirect()->toRoute('hotel-booking',array('action'=>'index'));
        }
    }
    
    public function reportsAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result = $bookingService->getAllHotelBookingReports($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function exportInExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result=$hotelBookingService->exportHotelBookingDetails($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function getLastClosingKmsAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result=$hotelBookingService->getLastClosingKms($params['vehicle_id']);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}

