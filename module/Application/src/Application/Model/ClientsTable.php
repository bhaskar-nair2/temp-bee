<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\ClientContactTable;
use Application\Model\GuestTable;
use Application\Model\EventLogTable;

class ClientsTable extends AbstractTableGateway {

    protected $table = 'clients';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addClientDetails($params) {
        $result = "";
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$clientCode="CC";
		$commonService=new CommonService();
        if (trim($params['companyName']) != "") {
			$expClientCode=explode($clientCode,$params['clientCode']);
            $data = array(
                'company_id' => base64_decode($params['companyName']),
                //'branch_id' => $params['branchId'],
                'client_name' => $params['vendorName'],
                'client_code' => $clientCode,
                'client_sort_key' => $expClientCode[1],
                'client_no' => $params['clientCode'],
                'client_city' => $params['clientCity'],
                'pin_code' => $params['pincode'],
                'address' => nl2br($params['address']),
				'gst_no' => $params['gstNo'],
                'client_pan_no' => $params['panNo'],
                'service_tax_type' => $params['serviceTaxType'],
				'client_state_code' => $params['stateCode'],
                //'sbc_tax' => $params['sbcTax'],
                //'kkc_tax' => $params['kkcTax'],
                'service_tax_paid_by_client' => $params['serviceTaxPaidByClient'],
                'status' => $params['status']
            );
			if(isset($params['contractExpDate']) && trim($params['contractExpDate'])!=""){
                $data['contract_exp_date']=$commonService->dateFormat($params['contractExpDate']);
            }
			if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='sgst'){
				$data['sgst_tax']=$params['sgstTax'];
				$data['cgst_tax']=$params['cgstTax'];
			}
			else if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='igst'){
				$data['igst_tax']=$params['igstTax'];;
			}
            $this->insert($data);
			$lastInsertedId = $this->lastInsertValue;
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "clients") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "clients")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "clients");
            }
			
			if (isset($_FILES['file']['name']) && trim($_FILES['file']['name'])!= '') {
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "clients" . DIRECTORY_SEPARATOR . $lastInsertedId;
				if (!file_exists($pathname) && !is_dir($pathname)) {
					mkdir($pathname);
				}
			
				if (move_uploaded_file($_FILES["file"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$_FILES['file']['name'])) {
					$imageData = array('file_name' => $_FILES['file']['name']);
					$this->update($imageData, array("client_id" => $lastInsertedId));
				}
            }
				
			$vendorContDb = new ClientContactTable($dbAdapter);
			
			$totalContact=count($params['contactName']);
			for($i=0;$i<$totalContact;$i++){
				if(isset($params['contactName'][$i])&& trim($params['contactName'][$i])!=''&& $params['contactName'][$i]!=null){
					$vendorContDb->insert(array(
					'contact_name' => $params['contactName'][$i],
					'phone_no' => $params['phoneNo'][$i],
					'mobile_no' => $params['mobileNo'][$i],
					'email' => $params['email'][$i],
					'client_id' => $lastInsertedId
					));
				}
			}
			$guestDb = new GuestTable($dbAdapter);
			$totalTraContact=count($params['travellerName']);
			for($i=0;$i<$totalTraContact;$i++){
				if(isset($params['travellerName'][$i])&& trim($params['travellerName'][$i])!=''&& $params['travellerName'][$i]!=null){
					$guestDb->insert(array(
					'guest_name' => $params['travellerName'][$i],
					'mobile_no' => $params['traMobileNo'][$i],
					'email' => $params['traEmail'][$i],
					'client_id' => $lastInsertedId
					));
				}
			}
        }
        //event log
		$subject = $lastInsertedId;
		$eventType = 'client-add';
		$action = 'added a new client with the name '.ucwords($params['vendorName']);
		$resourceName = 'Client';
		$eventLogDb = new EventLogTable($this->adapter);
		$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        return $lastInsertedId;
    }
	
	public function updateClientDetails($params) {
        if (trim($params['vendorId'])!="" && trim($params['companyName']) != "") {
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$commonService=new CommonService();
			$vendorId=base64_decode($params['vendorId']);
            $data = array(
                'company_id' => base64_decode($params['companyName']),
				//'branch_id' => $params['branchId'],
                'client_name' => $params['vendorName'],
                'client_city' => $params['clientCity'],
				//'client_code' => $params['clientCode'],
				'pin_code' => $params['pincode'],
                'address' => nl2br($params['address']),
                'gst_no' => $params['gstNo'],
                'client_pan_no' => $params['panNo'],
                'service_tax_type' => $params['serviceTaxType'],
				'client_state_code' => $params['stateCode'],
				'service_tax_paid_by_client' => $params['serviceTaxPaidByClient'],
                'status' => $params['status']
            );
			
			$data['contract_exp_date']=NULL;
			$data['sgst_tax']=NULL;
			$data['cgst_tax']=NULL;
			$data['igst_tax']=NULL;
			$data['service_tax']=NULL;
			$data['sbc_tax']=NULL;
			$data['kkc_tax']=NULL;
			
			if(isset($params['contractExpDate']) && trim($params['contractExpDate'])!=""){
                $data['contract_exp_date']=$commonService->dateFormat($params['contractExpDate']);
            }
			if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='sgst'){
				$data['sgst_tax']=$params['sgstTax'];
				$data['cgst_tax']=$params['cgstTax'];
			}
			else if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='igst'){
				$data['igst_tax']=$params['igstTax'];;
			}
            $this->update($data, array('client_id' => $vendorId));
			
			//Delete upload file
			if(isset($params['deletedFile']) && trim($params['deletedFile'])!=""){
				$filePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "clients" . DIRECTORY_SEPARATOR . $vendorId. DIRECTORY_SEPARATOR.$params['deletedFile'];
				if(file_exists($filePath)){
					unlink($filePath);
					$this->update(array('file_name'=>NULL), array("client_id" => $vendorId));
				}
			}
			
			//Upload file
			if (isset($_FILES['file']['name']) && trim($_FILES['file']['name'])!= '') {
				
				if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "clients") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "clients")) {
					mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "clients");
				}
				
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "clients" . DIRECTORY_SEPARATOR . $vendorId;
				if (!file_exists($pathname) && !is_dir($pathname)) {
					mkdir($pathname);
				}
				
				if (move_uploaded_file($_FILES["file"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$_FILES['file']['name'])) {
					$imageData = array('file_name' => $_FILES['file']['name']);
					$this->update($imageData, array("client_id" => $vendorId));
				}
            }
			
			$vendorContDb = new ClientContactTable($dbAdapter);
			$guestDb = new GuestTable($dbAdapter);
			if (isset($params['deletedId']) && trim($params['deletedId']) != "") {
				$deletedId = explode(",", $params['deletedId']);
				$totalId = count($deletedId);
				for ($z = 0; $z < $totalId; $z++) {
					$vendorContDb->delete("contact_id=".$deletedId[$z]);
				}
			}
	    
			if (isset($params['deletedTravellerId']) && trim($params['deletedTravellerId']) != "") {
				$deletedId = explode(",", $params['deletedTravellerId']);
				$totalId = count($deletedId);
				for ($k = 0; $k < $totalId; $k++) {
					$guestDb->delete("guest_id=".$deletedId[$k]);
				}
			}
			$totalContact=count($params['contactName']);
			for($i=0;$i<$totalContact;$i++){
				if(isset($params['contactName'][$i])&& trim($params['contactName'][$i])!=''&& $params['contactName'][$i]!=null){
					$data=array(
						'contact_name' => $params['contactName'][$i],
						'phone_no' => $params['phoneNo'][$i],
						'mobile_no' => $params['mobileNo'][$i],
						'email' => $params['email'][$i],
						'client_id' => $vendorId
					);
					if(isset($params['contactId'][$i]) && $params['contactId'][$i] !=""){
						$vendorContDb->update($data,"contact_id=".$params['contactId'][$i]);
					}else{
						$vendorContDb->insert($data);
					}
				}
			}
			
			$totalTraContact=count($params['travellerName']);
			for($i=0;$i<$totalTraContact;$i++){
				if(isset($params['travellerName'][$i])&& trim($params['travellerName'][$i])!=''&& $params['travellerName'][$i]!=null){
					$data=array(
						'guest_name' => $params['travellerName'][$i],
						'mobile_no' => $params['traMobileNo'][$i],
						'email' => $params['traEmail'][$i],
						'client_id' => $vendorId
					);
					
					if(isset($params['passengerId'][$i]) && $params['passengerId'][$i] !=""){
						$guestDb->update($data,"guest_id=".$params['passengerId'][$i]);
					}else{
						$guestDb->insert($data);
					}
				}
			}
			
			//event log
			$subject = $vendorId;
			$eventType = 'client-update';
			$action = 'updated a client with the name '.ucwords($params['vendorName']);
			$resourceName = 'Client';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
		
			return $vendorId;
        }
    }
	
    public function fetchAllClients($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('cd.company_name','v.client_name','v.client_no','v.client_city','v.service_tax','v.sbc_tax','v.kkc_tax','vc.contact_name','v.status');

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
                    $sOrder .= $aColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
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
        $sQuery = $sql->select()->from(array('v'=>'clients'))
				->join(array('cd' => 'company_details'), "cd.company_id=v.company_id", array('company_name'))
				->join(array('vc' => 'client_contacts'), "vc.client_id=v.client_id", array('bookedBy' => new Expression("Group_Concat(vc.contact_name ORDER BY contact_name SEPARATOR ',')")), "left")
				->group('v.client_id');
				
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
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
		$queryContainer->exportQuery = $sQuery;
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
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Clients', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
		if ($acl->isAllowed($role, 'Admin\Controller\Clients', 'view')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
        foreach ($rResult as $aRow) {
            $row = array();
			$edit="";
			$view="";
            $row[] = ucwords($aRow['company_name']);
            $row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['client_no'];
            $row[] = ucwords($aRow['client_city']);
            $row[] = $aRow['service_tax'];
            $row[] = $aRow['sbc_tax'];
            $row[] = $aRow['kkc_tax'];
			$row[] = $aRow['bookedBy'];
            $row[] = ucfirst($aRow['status']);
            if($update){
            $edit = '<a href="./edit/' . base64_encode($aRow['client_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
			if($viewAction){
            $view = '<a href="./view/' . base64_encode($aRow['client_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
            }
			$row[]=$edit.$view;
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getClientDetails($vendorId) {
		$bookedBy="";
		$guestResult="";
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
        $vendorResult = $this->select(array('client_id' => (int) $vendorId))->current();
		if($vendorResult!=""){
			$cQuery = $sql->select()->from('client_contacts')->where(array('client_id'=>$vendorResult['client_id']))->order('contact_name ASC');
			$cQueryStr = $sql->getSqlStringForSqlObject($cQuery);
			$bookedBy = $dbAdapter->query($cQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			$gQuery = $sql->select()->from('guest_details')->where(array('client_id'=>$vendorResult['client_id']))->order('guest_name ASC');
			$gQueryStr = $sql->getSqlStringForSqlObject($gQuery);
			$guestResult = $dbAdapter->query($gQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		}
		$result=array('vendor'=>$vendorResult,'bookedBy'=>$bookedBy,'traveller'=>$guestResult);
        return $result;
    }
    
	public function fetchAllActiveClients(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from('clients')->where(array('status'=>'active'))->order('client_name ASC');
		$sQueryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchAllActiveBeecabClients(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from('clients')->where(array('company_id'=>1,'status'=>'active'))->order('client_name ASC');
		$sQueryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchClientByCompany($companyId){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$sessionLogin = new Container('credo');
		$query = $sql->select()->from('clients')->where(array('company_id'=>(int) $companyId,'status'=>'active'))->order('client_name ASC');
		if($sessionLogin->roleId!=1 && trim($sessionLogin->billGenerateClientId)!=""){
			$query=$query->where("client_id IN($sessionLogin->billGenerateClientId)");
		}
		$sQueryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	
	public function fetchClientCode(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('clients')->order('client_id DESC');
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if($result!=""){
            $clientSortNo=$result['client_sort_key']+1;
            return $this->checkClientNo($clientSortNo);
        }else{
            return $clientSortNo='001';
        }
    }
	
	public function checkClientNo($clientSortNo){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$idSize = strlen($clientSortNo);
		if ($idSize < 4) {
			if ($idSize == 1) {
			$clientSortNo = "00" . $clientSortNo;
			} else if ($idSize == 2) {
			$clientSortNo = "0" . $clientSortNo;
			}else {
			$clientSortNo;
			}
		}
		$query = $sql->select()->from('clients')->where(array('client_sort_key'=>$clientSortNo));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($result!=""){
			$clientSortNo=$result['client_sort_key']+1;
			return $this->checkClientNo($clientSortNo);			
		}else{
			return $clientSortNo;
		}
    }
	
	public function getContractExpiryList(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from('clients')
						->columns(array('client_id','client_name','contract_exp_date' => new Expression("DATE_FORMAT(contract_exp_date,'%d-%b-%Y')")))
						->where(array('status'=>'active'))
						->where("(contract_exp_date <= (DATE(NOW()) + INTERVAL 30 DAY))");
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchClientDetails($clientId) {
		if($clientId>0){
			$bookedBy="";
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from(array('c'=>'clients'))
						->join(array('cd' => 'company_details'), "cd.company_id=c.company_id", array('company_name'))
						->where(array('client_id'=>$clientId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$vendorResult=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($vendorResult!=""){
				$cQuery = $sql->select()->from('client_contacts')->where(array('client_id'=>$vendorResult['client_id']))->order('contact_name ASC');
				$cQueryStr = $sql->getSqlStringForSqlObject($cQuery);
				$bookedBy = $dbAdapter->query($cQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			}
			$result=array('vendor'=>$vendorResult,'bookedBy'=>$bookedBy);
			return $result;
		}
    }
	
	public function fetchContractExpiryList($parameters) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('client_name','contract_exp_date');

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
                    $sOrder .= $aColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
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
		$sQuery = $sql->select()->from('clients')
						->columns(array('client_id','client_name','contract_exp_date' => new Expression("DATE_FORMAT(contract_exp_date,'%d-%b-%Y')")))
						->where(array('status'=>'active'))
						->where("(contract_exp_date <= (DATE(NOW()) + INTERVAL 30 DAY))");
		
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
		$iQuery = $sql->select()->from('clients')
						->columns(array('client_id','client_name','contract_exp_date' => new Expression("DATE_FORMAT(contract_exp_date,'%d-%b-%Y')")))
						->where(array('status'=>'active'))
						->where("(contract_exp_date <= (DATE(NOW()) + INTERVAL 30 DAY))");
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		
        $iTotal = count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['contract_exp_date'];
            
            $output['aaData'][] = $row;
        }
        return $output;
    }
}
?>
