<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Zend\Session\Container;
class FuelController extends AbstractActionController {

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $fuelService = $this->getServiceLocator()->get('FuelService');
            $fuelService->addFuel($params);
            return $this->redirect()->toRoute('fuel',array('action'=>'add'));
        }
        $commonService = $this->getServiceLocator()->get('CommonService');
        $employeeService = $this->getServiceLocator()->get('EmployeeService');
        $employeeResult=$employeeService->getAllActiveEmployeesExceptDriver();
        $driverResult=$employeeService->getAllActiveDriverList();
        $paymentModeResult=$commonService->getAllPaymentMode();
        $petrolPumpResult=$commonService->getAllPetrolPump();
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        $vehicleResult=$vehicleService->getAllActiveVehicles();
        return new ViewModel(array(
            'employee' => $employeeResult,
            'driverResult' => $driverResult,
            'vehicleResult' => $vehicleResult,
            'paymentMode' => $paymentModeResult,
            'petrolPumpResult' => $petrolPumpResult,
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $fuelService = $this->getServiceLocator()->get('FuelService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $fuelService->updateFuel($params);
            return $this->redirect()->toRoute('fuel',array('action'=>'reports'));
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $fuelService->getFuelDetails($id);
            
            $commonService = $this->getServiceLocator()->get('CommonService');
            $employeeService = $this->getServiceLocator()->get('EmployeeService');
            $employeeResult=$employeeService->getAllActiveEmployeesExceptDriver();
            $driverResult=$employeeService->getAllActiveDriverList();
            $paymentModeResult=$commonService->getAllPaymentMode();
            $petrolPumpResult=$commonService->getAllPetrolPump();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $vehicleResult=$vehicleService->getAllActiveVehicles();
            if ($result) {
                return new ViewModel(array(
                    'employee' => $employeeResult,
                    'driverResult' => $driverResult,
                    'vehicleResult' => $vehicleResult,
                    'paymentMode' => $paymentModeResult,
                    'petrolPumpResult' => $petrolPumpResult,
                    'result' => $result,
                ));
            } else {
                return $this->redirect()->toRoute('fuel',array('action'=>'reports'));
            }
        }
    }
    
    public function reportsAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $fuelService = $this->getServiceLocator()->get('FuelService');
            $result = $fuelService->getAllFuelReports($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
        $commonService = $this->getServiceLocator()->get('CommonService');
        $petrolPumpResult=$commonService->getAllPetrolPump();
        return new ViewModel(array(
            'petrolPumpResult' => $petrolPumpResult,
        ));
    }
    
    public function getLastFuelInfoAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $fuelService = $this->getServiceLocator()->get('FuelService');
            $result=$fuelService->getLastFuelInfo($params['vehicle_id']);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function exportInExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $fuelService = $this->getServiceLocator()->get('FuelService');
            $result=$fuelService->exportFuelDetails($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
}

