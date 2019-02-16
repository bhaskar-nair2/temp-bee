<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Zend\Session\Container;
class FpsHotelBookingController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result = $bookingService->getAllFpsHotelBookings($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function addAction() {
        $clientId=61;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $params['clientId']=$clientId;
            $bookingService = $this->getServiceLocator()->get('HotelBookingService');
            $bookingService->addFpsHotelBooking($params);
            return $this->redirect()->toRoute('fps-hotel-booking',array('action'=>'add'));
        }
        $logincontainer = new Container('credo');
        $rentalTypeService = $this->getServiceLocator()->get('RentalTypeService');
        $rentalTypeResult=$rentalTypeService->getAllRentalType();
        $eployeeService = $this->getServiceLocator()->get('EmployeeService');
        //FPS -- 6
        $driverResult=$eployeeService->getAllActiveBusinessUnitDriverList(6);
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        $vehicleResult=$vehicleService->getAllActiveHotelVehicles($clientId);
        //$hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
        //$bookingRef=$hotelBookingService->generateBookingRef($clientId);
        return new ViewModel(array(
            'rentalTypeResult'=>$rentalTypeResult,
            'driverResult'=>$driverResult,
            'vehicleResult'=>$vehicleResult
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $clientId=61;
        $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $hotelBookingService->updateFpsHotelBooking($params);
            return $this->redirect()->toRoute('fps-hotel-booking',array('action'=>'reports'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $hotelBookingService->getHotelBooking($id);
            $eployeeService = $this->getServiceLocator()->get('EmployeeService');
            $driverResult=$eployeeService->getAllActiveBusinessUnitDriverList(6);
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $vehicleResult=$vehicleService->getAllActiveHotelVehicles($clientId); 
            $rentalTypeService = $this->getServiceLocator()->get('RentalTypeService');
            $rentalTypeResult=$rentalTypeService->getAllRentalType();
            if ($result) {
                return new ViewModel(array(
                    'rentalTypeResult'=>$rentalTypeResult,
                    'driverResult'=>$driverResult,
                    'vehicleResult'=>$vehicleResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('fps-hotel-booking',array('action'=>'index'));
            }
        }
    }
    
    public function reportsAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result = $bookingService->getAllFpsHotelBookingReports($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function exportInExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result=$hotelBookingService->exportFpsHotelBookingDetails($params);
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

