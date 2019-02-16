<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Zend\Authentication\AuthenticationService; 
use Zend\Authentication\Storage\Session;
use Application\Model\TempMailTable;
use Application\Model\EventLogTable;
use Application\Model\EmployeeTable;

class UserTable extends AbstractTableGateway {

    protected $table = 'users';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addUserDetails($params) {
        $result = "";
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        if (trim($params['employee']) != "" && trim($params['userName'])!="") {
			$config = new \Zend\Config\Reader\Ini();
			$configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
			$password = sha1($params['password'] . $configResult["password"]["salt"]);
            $data = array(
                'employee_id' => $params['employee'],
                'user_name' => $params['userName'],
                'password' => $password,
                'added_on' => $commonService->getDateTime(),
                'added_by' => $logincontainer->employeeId
            );
			if(count($params['client'])>0){
				$client=implode(",",$params['client']);
				$data['monthly_bill_generated_client']=$client;
			}
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
			$empDb = new EmployeeTable($this->adapter);
            $empDb ->update(array('email'=>$params['email']), array("employee_id" => $params['employee']));
			
			$to=$params['email'];
			$subject="Your login details";
			$message="Your user name ".$params['userName'];
			$message.=" and password is ".$params['password'];
			$fromMail="datas@beecabs.in";
			$fromName="Beedatas";
			$tempMailDb=new TempMailTable($this->adapter);
			$tempMailDb->insertTempMailDetails($to,$subject,$message,$fromMail,$fromName);
			
            //event log
			$subject = $lastInsertedId;
			$eventType = 'user-add';
			$action = 'added a new user with the name '.ucwords($params['userName']);
			$resourceName = 'User';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }
	
	public function updateUserDetails($params) {
		
        if (trim($params['userId'])!="" && trim($params['employee']) != "") {
			$commonService=new CommonService();
			$logincontainer = new Container('credo');
			$userId=base64_decode($params['userId']);
            $data = array(
                'employee_id' => $params['employee'],
                'user_name' => $params['userName'],
            );
			$data['monthly_bill_generated_client']=NULL;
			if(count($params['client'])>0){
				$client=implode(",",$params['client']);
				$data['monthly_bill_generated_client']=$client;
			}
			
			if(isset($params['password']) && trim($params['password'])!=""){
				$config = new \Zend\Config\Reader\Ini();
				$configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
                $data['password'] = sha1($params['password'] . $configResult["password"]["salt"]);
            }
            $this->update($data, array('user_id' => $userId));
            
			if(isset($params['email']) && trim($params['email'])!=""){
				$empDb = new EmployeeTable($this->adapter);
				$empDb ->update(array('email'=>$params['email']), array("employee_id" => $params['employee']));
			}
			
            //event log
			$subject = $userId;
			$eventType = 'user-update';
			$action = 'updated a user with the name '.ucwords($params['userName']);
			$resourceName = 'User';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $userId;
        }
    }
	
    public function fetchAlllUsers($parameters) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('employee_name','employee_no','user_name','status');
        $orderColumns = array('employee_name','user_name','status');

        /*
         * Paging
        */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
         * Ordering
        */

        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < intval($parameters['iSortingCols']); $i++) {
                if ($parameters['bSortable_' . intval($parameters['iSortCol_' . $i])] == "true") {
                    $sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = "";
        if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
            $searchArray = explode(" ", $parameters['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);

                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                }
            }
        }

        /*
         * SQL queries
         * Get data to display
         */
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $sQuery = $sql->select()->from(array('u'=>'users'))
					->join(array('e' => 'employee_details'), "e.employee_id=u.employee_id", array('employee_name','employee_no','status'));
		
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //error_log($sQueryForm);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select()->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$update = true;
        $commonService=new CommonService();
        foreach ($rResult as $aRow) {
            $row = array();
            
            $row[] = ucwords($aRow['employee_name'])." - ".$aRow['employee_no'];
            $row[] = $aRow['user_name'];
            $row[] = ucwords($aRow['status']);
            if($update){
            $row[] = '<a href="./edit/' . base64_encode($aRow['user_id']) . '" title="Edit"><i class="fa fa-pencil"> </i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchUser($userId) {
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('u'=>'users'))
						->join(array('e' => 'employee_details'), "e.employee_id=u.employee_id", array('employee_no','email','status'))
						->where(array('user_id'=>(int) $userId));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        //$row = $this->select(array('user_id' => (int) $userId))->current();
        //return $row;
    }
    
	public function loginProcessDetails($params){
        $config = new \Zend\Config\Reader\Ini();
        $configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
        if(isset($params['userId']) && trim($params['userId'])!="" && trim($params['password'])!=""){
            $dbAdapter = $this->adapter;
            $sql = new Sql($dbAdapter);
            $password = sha1($params['password'].$configResult["password"]["salt"]);
            
            $sQuery = $sql->select()->from(array('u' => 'users'))
                            ->join(array('e' => 'employee_details'), "e.employee_id=u.employee_id", array('employee_no','employee_name','business_unit'))
                            ->join(array('r' => 'roles'), "r.role_id=e.role", array('role_id','role_code'))
                            ->where(array('u.user_name' => $params['userId'], 'u.password' => $password,'e.status'=>'working'));
            $sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
            
            $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            $alertContainer = new Container('alert');
            $logincontainer = new Container('credo');
            if($rResult) {
				if(isset($params['remember']) && $params['remember']){
					$authNamespace = new Container(Session::NAMESPACE_DEFAULT); 
					$authNamespace->getManager()->rememberMe(360000);
				}
				
				if(trim($rResult->business_unit)!="" && $rResult->role_id!="1"){
					$logincontainer->businessUnit = $rResult->business_unit;
				}
				
				$string = $rResult->employee_name;
				$words = explode(" ", $string);
				$letters = "";
				foreach ($words as $val) {
					$letters .= substr($val, 0, 1);
				}
				if(isset($rResult->login_type) && trim($rResult->login_type)!=""){
					$logincontainer->clientId=$rResult->client_id;
				}else{
					$logincontainer->clientId=1;
				}
				
                $logincontainer->employeeId = $rResult->employee_id;
                $logincontainer->userName = $rResult->user_name;
                $logincontainer->employeeCode = ucwords($rResult->employee_no);
                $logincontainer->name = ucwords($rResult->employee_name);
                $logincontainer->roleId = $rResult->role_id;
                $logincontainer->roleCode = ucwords($rResult->role_code);
                $logincontainer->empLetters = strtoupper($letters);;
				
				if(isset($rResult->monthly_bill_generated_client) && trim($rResult->monthly_bill_generated_client)!=""){
					$logincontainer->billGenerateClientId = $rResult->monthly_bill_generated_client;
				}
                //event log
				$subject = $logincontainer->employeeId;
				$eventType = 'employee-login';
                $action = "logged in.";
                $resourceName = 'Login';
                $eventLogDb = new EventLogTable($this->adapter);
                $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
				if($rResult->role_id==1){
					return 'admin-home';
				}
				else if($rResult->login_type=='hotel_booking'){
					return 'hotel-booking';
				}
				else{
					return 'home';
				}
                
            }else {
                $alertContainer->alertMsg = 'The user name or password you entered is incorrect';
                return 'login';
            }
        }else {
            $alertContainer->alertMsg = 'The user name or password you entered is incorrect';
            return 'login';
        }
    }
	
	public function updateUserPasswordDetails($params){
		$commonService=new CommonService();
		if(isset($params['emailId']) && trim($params['emailId'])!=""){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
            $query = $sql->select()->from(array('e' => 'employee_details'))
                            ->join(array('u' => 'users'), "u.employee_id=e.employee_id")
                            ->where(array('e.status'=>'active','e.email'=>$params['emailId']));
            $queryStr = $sql->getSqlStringForSqlObject($query);
			$result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($result!=""){
				$password=$commonService->generateRandomString();
				$config = new \Zend\Config\Reader\Ini();
				$configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
                //$ppassword = sha1($password . $configResult["password"]["salt"]);
				$updateRes=$this->update(array('password'=>sha1($password.$configResult["password"]["salt"])), array('user_id' => $result['user_id']));
				$to=$result['email'];
				$subject="Forgot Password";
				$message="Your password is ".$password;
				$fromMail="datas@beecabs.in";
				$fromName="Beedatas";
				$tempMailDb=new TempMailTable($dbAdapter);
				$tempMailDb->insertTempMailDetails($to,$subject,$message,$fromMail,$fromName);
				
				//event log
				$subject = $userId;
				$eventType = 'forgot password - user';
				$action = 'forgot password a user with the name '.ucwords($result['user_id']);
				$resourceName = 'User';
				$eventLogDb = new EventLogTable($this->adapter);
				$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			
				return $updateRes;
			}
		}
		
	}
	
	//This is used for acl
	public function fetchAllActiveUser(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from(array('u'=>'users'))
								->columns(array('user_id','user_name'))
								->join(array('e' => 'employee_details'), "e.employee_id=u.employee_id", array('employee_no','status'))
                                ->where("e.status='active'")
								->order('user_name');
        $queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function updatePasswordDetails($params) {
        $logincontainer = new Container('credo');
        $config = new \Zend\Config\Reader\Ini();
        $configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
        if (trim($params['password']) != "") {
            $password = sha1($params['password'] . $configResult["password"]["salt"]);
            $logincontainer = new Container('credo');
            $data = array('password' => $password);
            return $this->update($data, array('employee_id' => $logincontainer->employeeId));
        }
    }
}
?>
