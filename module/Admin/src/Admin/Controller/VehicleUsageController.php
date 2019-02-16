<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class VehicleUsageController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result = $vehicleService->getAllVehicleUsages($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $vehicleService->addVehicleUsage($params);
            return $this->redirect()->toRoute('vehicle-usage');
        }
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        $vehicleResult=$vehicleService->getAllActiveVehicles();
        return new ViewModel(array(
            'vehicleResult' => $vehicleResult
        ));
    }
    
    public function editAction() {
        $request = $this->getRequest();
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService->updateVehicleUsage($params);
            return $this->redirect()->toRoute('vehicle-usage');
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $vehicleResult=$vehicleService->getAllActiveVehicles();
            $result = $vehicleService->getVehicleUsage($id);
            if ($result) {
                return new ViewModel(array(
                    'vehicleResult' => $vehicleResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute('vehicle-usage');
            }
        }
    }
    
    public function viewAction() {
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        $id = base64_decode($this->params()->fromRoute('id'));
        $result = $vehicleService->fetchVehicleUsage($id);
        if ($result) {
            return new ViewModel(array(
                'result' => $result
            ));
        } else {
            return $this->redirect()->toRoute("vehicle-service");
        }
    }
    
    public function exportToExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result=$vehicleService->exportVehicleUsage($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
   
}

