<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class VehicleServiceController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result = $vehicleService->getAllVehicleService($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService->addVehicleService($params);
            return $this->redirect()->toRoute("vehicle-service");
        }
        
        //$commonService = $this->getServiceLocator()->get('CommonService');
        //$paymentModeResult=$commonService->getAllPaymentMode();
        $ownVehicleResult=$vehicleService->getAllActiveOwnVehicles();
        $workOrderNo=$vehicleService->getNewWorkOrderNo();
        return new ViewModel(array(
            'ownVehicle' => $ownVehicleResult,
            'workOrderNo' => $workOrderNo
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService->updateVehicleService($params);
            return $this->redirect()->toRoute("vehicle-service");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $vehicleService->getVehicleService($id);
            if ($result) {
                $commonService = $this->getServiceLocator()->get('CommonService');
                $paymentModeResult=$commonService->getAllPaymentMode();
                $ownVehicleResult=$vehicleService->getAllActiveOwnVehicles();
                return new ViewModel(array(
                    'ownVehicle' => $ownVehicleResult,
                    'paymentMode' => $paymentModeResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("vehicle-service");
            }
        }
    }

    public function pendingWorkOrderAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result = $vehicleService->getAllPendingWorkOrder($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function viewAction() {
        $vehicleService = $this->getServiceLocator()->get('VehicleService');
        $id = base64_decode($this->params()->fromRoute('id'));
        $result = $vehicleService->fetchVehicleService($id);
        if ($result) {
            return new ViewModel(array(
                'result' => $result
            ));
        } else {
            return $this->redirect()->toRoute("vehicle-service");
        }
    }
    
    public function exportCompleteWorkOrderToExcelAction(){
        $request = $this->getRequest();
        if($request->isPost())
        {
            $params = $request->getPost();
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result=$vehicleService->exportCompleteWorkOrder($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' =>$result));
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }
    
    public function deleteAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
			$serviceId=base64_decode($params['serviceId']);
            $vehicleService = $this->getServiceLocator()->get('VehicleService');
            $result = $vehicleService->deleteVehicleService($serviceId);
            $viewModel = new ViewModel();
            $viewModel->setVariables(array('result' => $result))
                        ->setTerminal(true);
            return $viewModel;
        }
    }
}

