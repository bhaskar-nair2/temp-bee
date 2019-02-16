<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class UserController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $userService = $this->getServiceLocator()->get('userService');
            $result = $userService->getAllUsers($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $userService = $this->getServiceLocator()->get('userService');
            $userService->addUser($params);
            return $this->redirect()->toRoute("user");
        }
        $employeeService = $this->getServiceLocator()->get('EmployeeService');
        $employeeResult=$employeeService->getAllActiveEmployeesExceptExistUser();
        $roleService = $this->getServiceLocator()->get('RoleService');
        $resourceResult = $roleService->getAllResource();
		$clientService = $this->getServiceLocator()->get('ClientService');
		$clientResult=$clientService->getAllActiveClients();
        return new ViewModel(array(
            'employee'=>$employeeResult,
            'resourceResult' => $resourceResult,
            'clientResult' => $clientResult,
        ));
    }

    public function editAction() {
        $request = $this->getRequest();
        $userService = $this->getServiceLocator()->get('userService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $userService->updateUser($params);
            return $this->redirect()->toRoute("user");
        } else {
            $configFile = CONFIG_PATH . DIRECTORY_SEPARATOR . "acl.config.php";
            $config = \Zend\Config\Factory::fromFile($configFile, true);
            
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $userService->getUser($id);
            $employeeService = $this->getServiceLocator()->get('EmployeeService');
            $employeeResult=$employeeService->getAllActiveEmployeesExceptDriver();
            $roleService = $this->getServiceLocator()->get('RoleService');
            $resourceResult = $roleService->getAllResource();
            $clientService = $this->getServiceLocator()->get('ClientService');
			$clientResult=$clientService->getAllActiveClients();
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    'employee'=>$employeeResult,
                    'resourceResult' => $resourceResult,
					'resourcePrivilegeMap' => $config,
					'clientResult' => $clientResult,
                ));
            } else {
                return $this->redirect()->toRoute("user");
            }
        }
    }
   
}

