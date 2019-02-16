<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class VehicleTypeController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
            $result = $vehicleTypeService->getAllVehicleTypes($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
            $vehicleTypeService->addVehicleType($params);
            return $this->redirect()->toRoute("vehicle-type");
        }
        return new ViewModel();
    }

    public function editAction() {
        $request = $this->getRequest();
        $vehicleTypeService = $this->getServiceLocator()->get('VehicleTypeService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $vehicleTypeService->updateVehicleType($params);
            return $this->redirect()->toRoute("vehicle-type");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $vehicleTypeService->getVehicleType($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("vehicle-type");
            }
        }
    }


   
}

