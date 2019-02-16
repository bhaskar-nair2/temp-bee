<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class HotelController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $hotelService = $this->getServiceLocator()->get('HotelService');
            $result = $hotelService->getAllHotels($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
    
    public function addAction() {
		$hotelService = $this->getServiceLocator()->get('HotelService');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $hotelService->addHotel($params);
            return $this->redirect()->toRoute("hotel");
        }
        $cityService = $this->getServiceLocator()->get('CityService');
        $cityResult=$cityService->getAllCities();
        return new ViewModel(array(
            'cityResult' => $cityResult
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $hotelService = $this->getServiceLocator()->get('HotelService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $hotelService->updateHotel($params);
            return $this->redirect()->toRoute("hotel");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $cityService = $this->getServiceLocator()->get('CityService');
            $cityResult=$cityService->getAllCities();
            $result = $hotelService->getHotel($id);
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    'cityResult' => $cityResult
                ));
            } else {
                return $this->redirect()->toRoute("hotel");
            }
        }
    }

    
}

