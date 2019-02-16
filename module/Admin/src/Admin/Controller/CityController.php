<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class CityController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $cityService = $this->getServiceLocator()->get('CityService');
            $result = $cityService->getAllCity($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
		$cityService = $this->getServiceLocator()->get('CityService');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $cityService->addCity($params);
            return $this->redirect()->toRoute("city");
        }
    }

    public function editAction() {
        $request = $this->getRequest();
        $cityService = $this->getServiceLocator()->get('CityService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $cityService->updateCity($params);
            return $this->redirect()->toRoute("city");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $cityService->getCity($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("city");
            }
        }
    }
   
}

