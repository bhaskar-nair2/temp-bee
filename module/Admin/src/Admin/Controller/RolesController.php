<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class RolesController extends AbstractActionController {

    public function indexAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $roleService = $this->getServiceLocator()->get('RoleService');
            $result = $roleService->getAllRoles($params);
            return $this->getResponse()->setContent(Json::encode($result));
        }
    }

    public function addAction() {
		$roleService = $this->getServiceLocator()->get('RoleService');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $roleService->addRole($params);
            return $this->redirect()->toRoute("roles");
        }
//		else {
//            $resourceResult = $roleService->getAllResource();
//            return new ViewModel(array(
//                'resourceResult' => $resourceResult,
//            ));
//        }
    }

    public function editAction() {
        $request = $this->getRequest();
        $roleService = $this->getServiceLocator()->get('RoleService');
        if ($request->isPost()) {
            $params = $request->getPost();
            $roleService->updateRole($params);
            return $this->redirect()->toRoute("roles");
        } else {
			//$configFile = CONFIG_PATH . DIRECTORY_SEPARATOR . "acl.config.php";
            //$config = \Zend\Config\Factory::fromFile($configFile, true);
            $id = base64_decode($this->params()->fromRoute('id'));
            $result = $roleService->getRole($id);
            //$resourceResult = $roleService->getAllResource();
            if ($result) {
                return new ViewModel(array(
                    'result' => $result,
                    //'resourceResult' => $resourceResult,
					//'resourcePrivilegeMap' => $config
                ));
            } else {
                return $this->redirect()->toRoute("roles");
            }
        }
    }


   
}

