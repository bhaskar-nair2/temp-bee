<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class EmployeeController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $employeeService = $this->getServiceLocator()->get('EmployeeService');
            $result = $employeeService->getAllEmployees($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        $employeeService = $this->getServiceLocator()->get('EmployeeService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $employeeService->addEmployee($params);
            return $this->redirect()->toRoute("employee");
        }
        $roleService = $this->getServiceLocator()->get('RoleService');
        $roleResult=$roleService->getAllActiveRoles();
        $commonService = $this->getServiceLocator()->get('CommonService');
        $unitResult=$commonService->getAllActiveBusinessUnits();
        $employeeNo=$employeeService->getEmployeeCode();
        return new ViewModel(array(
            'role'=>$roleResult,
            'businessUnit'=>$unitResult,
            'employeeNo'=>$employeeNo
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $employeeService = $this->getServiceLocator()->get('EmployeeService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $employeeService->updateEmployee($params);
            return $this->redirect()->toRoute("employee");
        } else {
            $id = base64_decode($this->params()->fromRoute('id'));
            $roleService = $this->getServiceLocator()->get('RoleService');
            $roleResult=$roleService->getAllActiveRoles();
            $commonService = $this->getServiceLocator()->get('CommonService');
            $unitResult=$commonService->getAllActiveBusinessUnits();
            $result = $employeeService->getEmployee($id);
            if ($result) {
                return new ViewModel(array(
                    'role'=>$roleResult,
                    'businessUnit'=>$unitResult,
                    'result' => $result
                ));
            } else {
                return $this->redirect()->toRoute("employee");
            }
        }
    }

    public function changePasswordAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $employeeService = $this->getServiceLocator()->get('EmployeeService');
            $route = $employeeService->updatePassword($params);
            return $this->redirect()->toRoute("admin-home");
        }
        return new ViewModel();
    }
    
    public function relievedAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $employeeService = $this->getServiceLocator()->get('EmployeeService');
            $result = $employeeService->getAllRelievedEmployees($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }
   
}

