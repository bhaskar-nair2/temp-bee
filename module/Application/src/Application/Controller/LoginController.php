<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class LoginController extends AbstractActionController{

    public function indexAction(){
        $logincontainer = new Container('credo');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $userService = $this->getServiceLocator()->get('UserService');
            $route = $userService->loginProcess($params);
            return $this->redirect()->toRoute($route);
        }
        if (isset($logincontainer->employeeId) && $logincontainer->employeeId != "") {
            if (isset($logincontainer->roleId) && $logincontainer->roleId=="1") {
                return $this->redirect()->toRoute("admin-home");
            }else{
                return $this->redirect()->toRoute("home");
            }
            
        } else {
            $viewModel = new ViewModel();
            $viewModel->setTerminal(true);
            return $viewModel;
        }
    }

    public function logoutAction()
    {
        $logincontainer = new Container('credo');
        $logincontainer->getManager()->getStorage()->clear('credo');
        return $this->redirect()->toRoute("login");
    }
    
    public function forgotPasswordAction(){
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $userService = $this->getServiceLocator()->get('UserService');
            $result = $userService->updatePassword($params);
            return $this->redirect()->toRoute("login");
        }
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function checkLoginEmailAction(){
        $result = "";
        $request = $this->getRequest();
        if ($request->isPost()) {
            $params = $request->getPost();
            $emailId=$params['email'];
            $common = $this->getServiceLocator()->get('CommonService');
            $result = $common->checkLoginEmailValidations($emailId);
        }
        $viewModel = new ViewModel();
        $viewModel->setVariables(array('result' => $result))
                    ->setTerminal(true);
        return $viewModel;
    }
}

