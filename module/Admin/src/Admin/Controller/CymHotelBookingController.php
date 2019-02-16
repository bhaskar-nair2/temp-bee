<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class CymHotelBookingController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result = $bookingService->getAllCymHotelBookingReports($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('HotelBookingService');
            $bookingService->addCymHotelBooking($params);
            return $this->redirect()->toRoute('cym-hotel-booking',array('action'=>'add'));
        }
        $rentalTypeService = $this->getServiceLocator()->get('RentalTypeService');
        $rentalTypeResult=$rentalTypeService->getAllRentalType();
        $eployeeService = $this->getServiceLocator()->get('EmployeeService');
        $driverResult=$eployeeService->getAllActiveBusinessUnitDriverList();
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        $vehicleResult=$vehicleService->getAllActiveHotelVehicles();
        $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
        $clientId=6;
        $bookingRef=$hotelBookingService->generateBookingRef($clientId);
        return new ViewModel(array(
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
            $hotelBookingService->updateCymHotelBooking($params);
            return $this->redirect()->toRoute("cym-hotel-booking");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result=$hotelBookingService->getCymHotelBooking($id);
            $rentalTypeService = $this->getServiceLocator()->get('RentalTypeService');
            $rentalTypeResult=$rentalTypeService->getAllRentalType();
            $eployeeService = $this->getServiceLocator()->get('EmployeeService');
            $driverResult=$eployeeService->getAllActiveBusinessUnitDriverList();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $vehicleResult=$vehicleService->getAllActiveHotelVehicles();
            $clientId=6;
           // $bookingRef=$hotelBookingService->generateBookingRef($clientId);
            

            if ($result) {
                return new ViewModel(array(
                    'rentalTypeResult'=>$rentalTypeResult,
                    'driverResult'=>$driverResult,
                    'vehicleResult'=>$vehicleResult,
                    'result'=>$result,
                ));
            } else {
                return $this->redirect()->toRoute("cym-hotel-booking");
            }
        }
    }

    public function getTariffAmtAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $bookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result=$bookingService->getCymTariffAmt($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function rentalsAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $cymRentalService = $this->getServiceLocator()->get('CymRentalService');
            $result = $cymRentalService->getAllCymRentals($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function editRentalsAction(){
        $request = $this->getRequest();
        $cymRentalService = $this->getServiceLocator()->get('CymRentalService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $cymRentalService->updateRental($params);
            return $this->redirect()->toRoute('cym-hotel-booking',array('action'=>'rentals'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $cymRentalService->getCymRental($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('cym-hotel-booking',array('action'=>'rentals'));
            }
        }
    }

    public function exportInExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $hotelBookingService = $this->getServiceLocator()->get('HotelBookingService');
            $result=$hotelBookingService->exportCymHotelBookingDetails($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}

