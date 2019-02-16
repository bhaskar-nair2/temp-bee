<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\EventLogTable;

class EmployeeTable extends AbstractTableGateway {

    protected $table = 'employee_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    public function addEmployeeDetails($params) {
        $vendor=base64_decode($params['vendor']);
        
        $commonService=new CommonService();
        $employeeCode="BC";
        $lastInsertedId = "";
        if (!file_exists(UPLOAD_PATH) && !is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH);
        }
        if (isset($params['employeeName']) && trim($params['employeeName'])!="" && trim($params['employeeCode']) != "") {
            //$config = new \Zend\Config\Reader\Ini();
            //$configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
            
            $data = array(
                'employee_code' => $employeeCode,
                'employee_sort_key' => $params['employeeSortKey'],
                'employee_no' => $params['employeeCode'],
                'employee_name' => $params['employeeName'],
                'mobile_no' => $params['mobileNo'],
                'email' => $params['email'],
                'gender' => $params['gender'],
                'emergency_contact_no' => $params['emergencyPhoneNo'],
                'address' => $params['address'],
                'role' => base64_decode($params['role']),
                'business_unit' => $vendor,
                'bank_name' => $params['bankName'],
                'account_no' => $params['accountNumber'],
                'insurance_no' => $params['insuranceNumber'],
                'esi_no' => $params['esiNumber'],
                'pf_no' => $params['pfNumber'],
                'blood_group' => $params['bloodGroup'],
                'status' => $params['status'],
                'created_on'=>$commonService->getDateTime()
            );
            
            if(isset($params['dateOfJoin']) && trim($params['dateOfJoin'])!=""){
                $data['date_of_join']=$commonService->dateFormat($params['dateOfJoin']);
            }
            if(isset($params['dateOfReleving']) && trim($params['dateOfReleving'])!=""){
                $data['date_of_releving']=$commonService->dateFormat($params['dateOfReleving']);
            }
            if(isset($params['licenseValidDate']) && trim($params['licenseValidDate'])!=""){
                $data['license_valid_date']=$commonService->dateFormat($params['licenseValidDate']);
            }
            if(isset($params['badgeValidDate']) && trim($params['badgeValidDate'])!=""){
                $data['badge_valid_date']=$commonService->dateFormat($params['badgeValidDate']);
            }
           
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            
            if(trim($_FILES['licenseImg']['name'])!="" || trim($_FILES['idProof']['name'])!="" || trim($_FILES['contractProof']['name'])!="" || trim($_FILES['addressProof']['name'])!="" || trim($_FILES['photo']['name'])!="")
            {
                if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees")) {
                    mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees");
                }
                $pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId;
                if (!file_exists($pathname) && !is_dir($pathname)) {
                    mkdir($pathname);
                }
                if (isset($_FILES['licenseImg']['name']) && trim($_FILES['licenseImg']['name'])!= '') {
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['licenseImg']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if (move_uploaded_file($_FILES["licenseImg"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('license_badge' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
                
                if (isset($_FILES['idProof']['name']) && trim($_FILES['idProof']['name'])!= '') {
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['idProof']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if (move_uploaded_file($_FILES["idProof"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('id_proof' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
            
                if (isset($_FILES['contractProof']['name']) && trim($_FILES['contractProof']['name'])!= '') {
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['contractProof']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if (move_uploaded_file($_FILES["contractProof"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('contract' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
                
                if (isset($_FILES['addressProof']['name']) && trim($_FILES['addressProof']['name'])!= '') {
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['addressProof']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if (move_uploaded_file($_FILES["addressProof"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('address_proof' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
                
                if (isset($_FILES['photo']['name']) && trim($_FILES['photo']['name'])!= '') {
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('photo' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
            }
            
            //event log
			$subject = $lastInsertedId;
			$eventType = 'employee-add';
			$action = 'added a new employee with the name '.ucwords($params['employeeName'])." - ".$params['employeeCode'];
			$resourceName = 'Employee';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
            
        }
        return $lastInsertedId;
    }
    
    public function updateEmployeeDetails($params) {
        $commonService=new CommonService();
        
        $lastInsertedId = "";
        if (!file_exists(UPLOAD_PATH) && !is_dir(UPLOAD_PATH)) {
            mkdir(UPLOAD_PATH);
        }
        if (isset($params['employeeName']) && trim($params['employeeName'])!="" && trim($params['employeeCode']) != "") {
            //$config = new \Zend\Config\Reader\Ini();
            //$configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
            $employeeId=base64_decode($params['employeeId']);
            $vendor=base64_decode($params['vendor']);
            $data = array(
                //'employee_no' => $params['employeeCode'],
                'employee_name' => $params['employeeName'],
                'mobile_no' => $params['mobileNo'],
                'email' => $params['email'],
                'gender' => $params['gender'],
                'emergency_contact_no' => $params['emergencyPhoneNo'],
                'address' => $params['address'],
                'role' => base64_decode($params['role']),
                'business_unit' => $vendor,
                'bank_name' => $params['bankName'],
                'account_no' => $params['accountNumber'],
                'insurance_no' => $params['insuranceNumber'],
                'esi_no' => $params['esiNumber'],
                'pf_no' => $params['pfNumber'],
                'blood_group' => $params['bloodGroup'],
                'status' => $params['status']
            );
            
            $data['date_of_join']=NULL;
            $data['date_of_releving']=NULL;
            $data['license_valid_date']=NULL;
            $data['badge_valid_date']=NULL;
            
            //if(isset($params['password']) && trim($params['password'])!=""){
            //    $data['password'] = sha1($params['password'] . $configResult["password"]["salt"]);
            //}
            if(isset($params['dateOfJoin']) && trim($params['dateOfJoin'])!=""){
                $data['date_of_join']=$commonService->dateFormat($params['dateOfJoin']);
            }
            if(isset($params['dateOfReleving']) && trim($params['dateOfReleving'])!=""){
                $data['date_of_releving']=$commonService->dateFormat($params['dateOfReleving']);
            }
            if(isset($params['licenseValidDate']) && trim($params['licenseValidDate'])!=""){
                $data['license_valid_date']=$commonService->dateFormat($params['licenseValidDate']);
            }
            if(isset($params['badgeValidDate']) && trim($params['badgeValidDate'])!=""){
                $data['badge_valid_date']=$commonService->dateFormat($params['badgeValidDate']);
            }
           
            //$result = $this->insert($data);
            //$lastInsertedId = $this->lastInsertValue;
            $this->update($data,array('employee_id'=>$employeeId));
            $lastInsertedId=$employeeId;
            
            //license image
            if (isset($params['removedLicenseImage']) && $params['removedLicenseImage'] != '') {
                if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedLicenseImage'])) {
                    unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedLicenseImage']);
                    $imageData = array('license_badge' => '');
                    $this->update($imageData, array("employee_id" => $lastInsertedId));
                }
            }
            //id proof
            if (isset($params['removedIdProofImage']) && $params['removedIdProofImage'] != '') {
                if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedIdProofImage'])) {
                    unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedIdProofImage']);
                    $imageData = array('id_proof' => '');
                    $this->update($imageData, array("employee_id" => $lastInsertedId));
                }
            }
            //Contract proof
            if (isset($params['removedContractProofImage']) && $params['removedContractProofImage'] != '') {
                if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedContractProofImage'])) {
                    unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedContractProofImage']);
                    $imageData = array('contract' => '');
                    $this->update($imageData, array("employee_id" => $lastInsertedId));
                }
            }
            //Address proof
            if (isset($params['removedAddressProofImage']) && $params['removedAddressProofImage'] != '') {
                if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedAddressProofImage'])) {
                    unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedAddressProofImage']);
                    $imageData = array('address_proof' => '');
                    $this->update($imageData, array("employee_id" => $lastInsertedId));
                }
            }
            
            //Photo image
            if (isset($params['removedPhotoImage']) && $params['removedPhotoImage'] != '') {
                if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedPhotoImage'])) {
                    unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['removedPhotoImage']);
                    $imageData = array('photo' => '');
                    $this->update($imageData, array("employee_id" => $lastInsertedId));
                }
            }
            
            if(trim($_FILES['licenseImg']['name'])!="" || trim($_FILES['idProof']['name'])!="" || trim($_FILES['contractProof']['name'])!="" || trim($_FILES['addressProof']['name'])!="" || isset($_FILES['photo']['name']) ||trim($_FILES['photo']['name'])!="")
            {
                if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees")) {
                    mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees");
                }
                $pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId;
                if (!file_exists($pathname) && !is_dir($pathname)) {
                    mkdir($pathname);
                }
                if (isset($_FILES['licenseImg']['name']) && trim($_FILES['licenseImg']['name'])!= '') {
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['licenseImg']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if(isset($params['existLicenseImage']) && trim($params['existLicenseImage'])!=""){
                        if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existLicenseImage'])) {
                            unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existLicenseImage']);
                            $imageData = array('license_badge' => '');
                            $this->update($imageData, array("employee_id" => $lastInsertedId));
                        }
                    }
                    if (move_uploaded_file($_FILES["licenseImg"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('license_badge' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
                
                if (isset($_FILES['idProof']['name']) && trim($_FILES['idProof']['name'])!= '') {
                    if(isset($params['existIdProofImage']) && trim($params['existIdProofImage'])!=""){
                        if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existIdProofImage'])) {
                            unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existIdProofImage']);
                            $imageData = array('id_proof' => '');
                            $this->update($imageData, array("employee_id" => $lastInsertedId));
                        }
                    }
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['idProof']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if (move_uploaded_file($_FILES["idProof"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('id_proof' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
            
                if (isset($_FILES['contractProof']['name']) && trim($_FILES['contractProof']['name'])!= '') {
                    if(isset($params['existContractProofImage']) && trim($params['existContractProofImage'])!=""){
                        if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existContractProofImage'])) {
                            unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existContractProofImage']);
                            $imageData = array('contract' => '');
                            $this->update($imageData, array("employee_id" => $lastInsertedId));
                        }
                    }
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['contractProof']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if (move_uploaded_file($_FILES["contractProof"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('contract' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
                
                if (isset($_FILES['addressProof']['name']) && trim($_FILES['addressProof']['name'])!= '') {
                    if(isset($params['existAddressProofImage']) && trim($params['existAddressProofImage'])!=""){
                        if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existAddressProofImage'])) {
                            unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existAddressProofImage']);
                            $imageData = array('address_proof' => '');
                            $this->update($imageData, array("employee_id" => $lastInsertedId));
                        }
                    }
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['addressProof']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if (move_uploaded_file($_FILES["addressProof"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('address_proof' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
                
                if (isset($_FILES['photo']['name']) && trim($_FILES['photo']['name'])!= '') {
                    if(isset($params['existPhotoImage']) && trim($params['existPhotoImage'])!=""){
                        if (file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existPhotoImage'])) {
                            unlink(UPLOAD_PATH . DIRECTORY_SEPARATOR . "employees" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR . $params['existPhotoImage']);
                            $imageData = array('photo' => '');
                            $this->update($imageData, array("employee_id" => $lastInsertedId));
                        }
                    }
                    $extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $fileName = $commonService->generateRandomString(5,'alphanum').$lastInsertedId."." . $extension;
                    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
                        $imageData = array('photo' => $fileName);
                        $this->update($imageData, array("employee_id" => $lastInsertedId));
                    }
                }
            }
            
            //event log
			$subject = $lastInsertedId;
			$eventType = 'employee-update';
			$action = 'updated a employee with the name '.ucwords($params['employeeName']);
			$resourceName = 'Employee';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			
        }
        return $lastInsertedId;
    }
    
    public function fetchAllEmployees($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('e.employee_code','e.employee_name','e.mobile_no','r.role_name','unit_name','bank_name','account_no','blood_group','date_of_join','date_of_releving','e.status');
        $orderColumns = array('e.employee_sort_key','e.employee_name','e.mobile_no','r.role_name','unit_name','bank_name','account_no','blood_group','date_of_join','date_of_releving','e.status');

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
        $sQuery = $sql->select()->from(array('e'=>'employee_details'))
                ->columns(array('employee_id','employee_code','employee_no','employee_name','mobile_no','bank_name','account_no','blood_group','date_of_join','date_of_releving','status'))
				->join(array('r' => 'roles'), "r.role_id=e.role", array('role_name'))
				->join(array('bu' => 'business_units'), "bu.unit_id=e.business_unit", array('unit_name'))
                ->where(array('e.status'=>'working'));
				
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
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select(array('status'=>'working'))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Employee', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = $aRow['employee_no'];
            $row[] = ucwords($aRow['employee_name']);
            $row[] = $aRow['mobile_no'];
			$row[] = ucwords($aRow['role_name']);
			$row[] = ucwords($aRow['unit_name']);
			$row[] = ucwords($aRow['bank_name']);
			$row[] = $aRow['account_no'];
			$row[] = $aRow['blood_group'];
			$row[] = $aRow['date_of_join'];
			$row[] = $aRow['date_of_releving'];
            $row[] = ucfirst($aRow['status']);
            if($update){
            $row[] = '<a href="./edit/' . base64_encode($aRow['employee_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }
    
    public function fetchEmployeeCode(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('employee_details')->order('employee_id DESC');
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if($result!=""){
            $employeeNo=$result['employee_sort_key']+1;
            return $this->checkEmployeeNo($employeeNo);
        }else{
            return $employeeNo='001';
        }
    }
    
    public function checkEmployeeNo($employeeNo){
        $idSize = strlen($employeeNo);
        if ($idSize < 4) {
            if ($idSize == 1) {
            $employeeNo = "00" . $employeeNo;
            } else if ($idSize == 2) {
            $employeeNo = "0" . $employeeNo;
            } else {
            $employeeNo;
            }
        }
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('employee_details')->where(array('employee_sort_key'=>$employeeNo));
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if($result!=""){
            $employeeNo=$result['employee_sort_key']+1;
            return $this->checkEmployeeNo($employeeNo);
        }else{
            return $employeeNo;
        }
    }
    
    public function getEmployeeDetails($employeeId) {
        $result = $this->select(array('employee_id' => (int) $employeeId))->current();
        return $result;
    }
    
    public function fetchAllActiveEmployeesExceptDriver(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('employee_details')
                ->columns(array('employee_id','employee_no','employee_name'))
                ->where(array('status'=>'working','role!=5'))
                ->order('employee_name ASC');
        $queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    
    public function fetchAllActiveEmployeesExceptExistUser(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        //Get Exist user
        $uQuery = $sql->select()->from('users')->columns(array('employee_id'));
        $uQueryStr = $sql->getSqlStringForSqlObject($uQuery);
        $uResult=$dbAdapter->query($uQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $employeeId=array();
        foreach($uResult as $val){
            $employeeId[]=$val['employee_id'];
        }
        $query = $sql->select()->from('employee_details')
                ->columns(array('employee_id','employee_no','employee_name'))
                ->where(array('status'=>'working','role!=5'))
                ->order('employee_name ASC');
        if(count($employeeId)>0){
            $employeeId=implode(",",$employeeId);
            $query->where('employee_id NOT IN ('.$employeeId.')');
        }
        $queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    
    public function fetchEmployeeLicenseExpiryList(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        //Count license renewal
        $lQuery = $sql->select()->from('employee_details')
						->columns(array('employee_id','employee_no'))
						->where(array('status'=>'working'))
						->where("license_valid_date <= (DATE(NOW()) + INTERVAL 15 DAY)");
		$lQueryStr = $sql->getSqlStringForSqlObject($lQuery);
        $lResult = $dbAdapter->query($lQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $licenseCount = count($lResult);
        
        //Count badge renewal
        $bQuery = $sql->select()->from('employee_details')
						->columns(array('employee_id','employee_no'))
						->where(array('status'=>'working'))
						->where("badge_valid_date <= (DATE(NOW()) + INTERVAL 15 DAY)");
		$bQueryStr = $sql->getSqlStringForSqlObject($bQuery);
        $bResult = $dbAdapter->query($bQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $badgeCount = count($bResult);
        
        $result=array('licenseCount'=>$licenseCount,'badgeCount'=>$badgeCount);
        
        return $result;
    }
    
    public function fetchAllActiveDriverList(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('employee_details')
                ->columns(array('employee_id','employee_no','employee_name','mobile_no'))
                ->where(array('status'=>'working','role=5'))
                ->order('employee_name ASC');
        $queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    
    public function fetchAllRelievedEmployees($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('e.employee_code','e.employee_name','e.mobile_no','r.role_name','unit_name','bank_name','account_no','blood_group','date_of_join','date_of_releving','e.status');
        $orderColumns = array('e.employee_sort_key','e.employee_name','e.mobile_no','r.role_name','unit_name','bank_name','account_no','blood_group','date_of_join','date_of_releving','e.status');

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
        $sQuery = $sql->select()->from(array('e'=>'employee_details'))
                ->columns(array('employee_id','employee_code','employee_no','employee_name','mobile_no','bank_name','account_no','blood_group','date_of_join','date_of_releving','status'))
				->join(array('r' => 'roles'), "r.role_id=e.role", array('role_name'))
				->join(array('bu' => 'business_units'), "bu.unit_id=e.business_unit", array('unit_name'))
                ->where(array('e.status'=>'relieved'));
				
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
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select(array('status'=>'relieved'))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Employee', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = $aRow['employee_no'];
            $row[] = ucwords($aRow['employee_name']);
            $row[] = $aRow['mobile_no'];
			$row[] = ucwords($aRow['role_name']);
			$row[] = ucwords($aRow['unit_name']);
			$row[] = ucwords($aRow['bank_name']);
			$row[] = $aRow['account_no'];
			$row[] = $aRow['blood_group'];
			$row[] = $aRow['date_of_join'];
			$row[] = $aRow['date_of_releving'];
            $row[] = ucfirst($aRow['status']);
            if($update){
            $row[] = '<a href="./edit/' . base64_encode($aRow['employee_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }
    
    public function fetchAllActiveBusinessUnitDriverList($businessUnit){
        $logincontainer = new Container('credo');
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('employee_details')
                ->columns(array('employee_id','employee_no','employee_name','mobile_no'))
                ->where(array('status'=>'working','role=5'))
                ->order('employee_name ASC');
        if($businessUnit==""){
            if(isset($logincontainer->businessUnit) && isset($logincontainer->roleId) && $logincontainer->roleId!=1){
                $query=$query->where(array("business_unit"=>$logincontainer->businessUnit));
            }
        }else{
            $query=$query->where(array("business_unit"=>$businessUnit));
        }
        $queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
}
?>
