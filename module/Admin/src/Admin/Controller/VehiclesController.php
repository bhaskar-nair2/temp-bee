<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class VehiclesController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result = $vehicleService->getAllVehicles($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $vehicleService->addVehicle($params);
            return $this->redirect()->toRoute("vehicles");
        }
        $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
        $vehicleTypeResult=$vehicleTypeService->getAllVehicleType();
        $vehicleModeResult=$vehicleTypeService->getAllVehicleMode();
        $commonService = $this->getServiceLocator()->get('CommonService');
        $vehicleMakeResult=$commonService->getAllVehicleMake();
        $clientService = $this->getServiceLocator()->get('ClientService');
        $clientResult=$clientService->getAllActiveBeecabsClients();
        return new ViewModel(array(
            'vehicleType'=>$vehicleTypeResult,
            'vehicleMode'=>$vehicleModeResult,
            'vehicleCategoryResult'=>$vehicleMakeResult,
            'clientResult'=>$clientResult
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService->updateVehicle($params);
            return $this->redirect()->toRoute("vehicles");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
            $vehicleTypeResult=$vehicleTypeService->getAllVehicleType();
            $vehicleModeResult=$vehicleTypeService->getAllVehicleMode();
            $commonService = $this->getServiceLocator()->get('CommonService');
            $clientService = $this->getServiceLocator()->get('ClientService');
            $clientResult=$clientService->getAllActiveBeecabsClients();
            $vehicleMakeResult=$commonService->getAllVehicleMake();
            $result = $vehicleService->getVehicle($id);
            $vehicleClientMapResult = $vehicleService->getVehicleClientMap($id);
            if ($result) {
                return new ViewModel(array(
                    'vehicleType'=>$vehicleTypeResult,
                    'vehicleMode'=>$vehicleModeResult,
                    'vehicleCategoryResult'=>$vehicleMakeResult,
                    'clientResult'=>$clientResult,
                    'vehicleClientMapResult'=>$vehicleClientMapResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("vehicles");
            }
        }
    }
    
    public function getInsuranceExpiryAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result = $vehicleService->getAllInsuranceExpiry($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function viewAction(){
        $id = base64_decode($this->params()->fromRoute('id'));
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        $result = $vehicleService->fetchVehicle($id);
        if ($result) {
            return new ViewModel(array(
                'result' => $result
            ));
        } else {
            return $this->redirect()->toRoute("vehicles");
        }
    }
    
    public function exportToExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result=$vehicleService->exportVehicleList($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function getVehicleListAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleCategory=(int) $params['vehicleCategory'];
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result=$vehicleService->getAllVehiclesBasedOnCategory($vehicleCategory);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
}

