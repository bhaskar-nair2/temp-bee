<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\BranchTable;
use Application\Model\EventLogTable;
use Application\Model\CompanyDocumentDetailsTable;

class CompanyTable extends AbstractTableGateway {

    protected $table = 'company_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addCompanyDetails($params) {
        $result = "";
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$commonService=new CommonService();
        if (trim($params['companyName']) != "") {
            $data = array(
                'company_name' => $params['companyName'],
                'address' => nl2br($params['address']),
                'city' => $params['city'],
                'pin_code' => $params['pincode'],
                'phone_no' => $params['phoneNo'],
                'pan_no' => $params['panNo'],
                'gst_no' => $params['serviceTaxNo'],
				'sac_no' => $params['sacNo'],
                'status' => $params['status']
            );
            $result = $this->insert($data);
			$lastInsertedId = $this->lastInsertValue;
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company");
            }
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR ."company". DIRECTORY_SEPARATOR.$lastInsertedId) && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company". DIRECTORY_SEPARATOR.$lastInsertedId)) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company". DIRECTORY_SEPARATOR.$lastInsertedId);
            }
			$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "company" . DIRECTORY_SEPARATOR . $lastInsertedId;
			
			$documentDb = new CompanyDocumentDetailsTable($dbAdapter);
			$totalCount=count($params['documentName']);
			for($i=0;$i<$totalCount;$i++){
				if(isset($params['documentName'][$i])&& trim($params['documentName'][$i])!=''&& $params['documentName'][$i]!=null){
					if (isset($_FILES['file']['name'][$i]) && trim($_FILES['file']['name'][$i])!= '') {
						$extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['file']['name'][$i],PATHINFO_EXTENSION));
						$fileName = $commonService->removespecials($params['documentName'][$i])."." . $extension;
						if (move_uploaded_file($_FILES["file"]["tmp_name"][$i], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
							$documentDb->insert(array(
								'document_name' => $params['documentName'][$i],
								'file_name' => $fileName,
								'company_id' => $lastInsertedId
							));
						}
					}
					
				}
			}
			
			//event log
			$subject = $lastInsertedId;
			$eventType = 'company-add';
			$action = 'added a new company with the name '.ucwords($params['companyName']);
			$resourceName = 'Company';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }
	
	public function updateCompanyDetails($params) {
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$commonService=new CommonService();
        if (trim($params['companyId'])!="" && trim($params['companyName']) != "") {
			$companyId=base64_decode($params['companyId']);
            $data = array(
                'company_name' => $params['companyName'],
                'address' => nl2br($params['address']),
				'city' => $params['city'],
                'pin_code' => $params['pincode'],
                'phone_no' => $params['phoneNo'],
                'pan_no' => $params['panNo'],
                'gst_no' => $params['serviceTaxNo'],
                'sac_no' => $params['sacNo'],
                'status' => $params['status']
            );
            $this->update($data, array('company_id' => $companyId));
			$documentDb = new CompanyDocumentDetailsTable($dbAdapter);
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company");
            }
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR ."company". DIRECTORY_SEPARATOR.$companyId) && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company". DIRECTORY_SEPARATOR.$companyId)) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "company". DIRECTORY_SEPARATOR.$companyId);
            }
			$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "company" . DIRECTORY_SEPARATOR . $companyId;
			$totalCount=count($params['documentName']);
			
			if (isset($params['deletedId']) && trim($params['deletedId']) != "") {
				$deletedId = explode(",", $params['deletedId']);
				$totalId = count($deletedId);
				for ($z = 0; $z < $totalId; $z++) {
					$sQuery=$sql->select()->from('company_document_details')->where(array('document_id'=>$deletedId[$z]));
					$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
					$result = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
					if($result!=""){
						unlink($pathname. DIRECTORY_SEPARATOR.$result['file_name']);
					}
					$documentDb->delete("document_id=".$deletedId[$z]);
				}
			}
			
			$totalCount=count($params['documentName']);
			for($i=0;$i<$totalCount;$i++){
				if(isset($params['documentName'][$i])&& trim($params['documentName'][$i])!=''&& $params['documentName'][$i]!=null){
					$data=array(
						'document_name' => $params['documentName'][$i],
						'company_id' => $companyId
					);
					
					if (isset($_FILES['file']['name'][$i]) && trim($_FILES['file']['name'][$i])!= '') {
						$extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['file']['name'][$i],PATHINFO_EXTENSION));
						$fileName = $commonService->removespecials($params['documentName'][$i])."." . $extension;
						if (move_uploaded_file($_FILES["file"]["tmp_name"][$i], $pathname . DIRECTORY_SEPARATOR .$fileName)) {
							$data['file_name']=$fileName;
						}
					}
					
					if(isset($params['documentId'][$i]) && $params['documentId'][$i] !=""){
						$documentDb->update($data,"document_id=".$params['documentId'][$i]);
					}else{
						$documentDb->insert($data);
					}
				}
			}
			
			/*
			$branchDb = new BranchTable($dbAdapter);
			
			if (isset($params['deletedId']) && trim($params['deletedId']) != "") {
				$deletedId = explode(",", $params['deletedId']);
				$totalId = count($deletedId);
				for ($z = 0; $z < $totalId; $z++) {
					$branchDb->delete("branch_id=".$deletedId[$z]);
				}
			}
			
			$totalCount=count($params['city']);
			for($i=0;$i<$totalCount;$i++){
				if(isset($params['city'][$i])&& trim($params['city'][$i])!=''&& $params['city'][$i]!=null){
					$data=array(
					'city' => $params['city'][$i],
					'phone_no' => $params['phoneNo'][$i],
					'alt_phone_no' => $params['altPhoneNo'][$i],
					'address' => nl2br($params['address'][$i]),
					'status' => $params['branchStatus'][$i],
					'company_id' => $companyId
					);
					
					if(isset($params['branchId'][$i]) && $params['branchId'][$i] !=""){
						$branchDb->update($data,"branch_id=".$params['branchId'][$i]);
					}else{
						$branchDb->insert($data);
					}
				}
			}
			*/
			//event log
			$subject = $companyId;
			$eventType = 'company-update';
			$action = 'updated a company with the name '.ucwords($params['companyName']);
			$resourceName = 'Company';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $companyId;
        }
    }
	
    public function fetchAllCompany($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
		
        $aColumns = array('company_name','city','pin_code','phone_no','pan_no','service_tax_no','status');
        $orderColumns = array('company_name','city','pin_code','phone_no','pan_no','service_tax_no','status');

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
		$sQuery = $sql->select()->from(array('c'=>'company_details'))
				//->join(array('b' => 'branches'), "b.company_id=c.company_id", array('cityName' => new Expression("Group_Concat(b.city ORDER BY city SEPARATOR ',')")), "left")
				->group('c.company_id');
        //$sQuery = $sql->select()->from('company_details');
		
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
        $iTotal = $this->select()->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Company', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\Company', 'view')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
        foreach ($rResult as $aRow) {
            $row = array();
			$edit="";
			$view="";
            $row[] = ucwords($aRow['company_name']);
            $row[] = $aRow['city'];
            $row[] = $aRow['pin_code'];
            $row[] = $aRow['phone_no'];
            $row[] = $aRow['pan_no'];
            $row[] = $aRow['service_tax_no'];
            $row[] = ucfirst($aRow['status']);
			if($viewAction){
				$view = '<a href="./view/' . base64_encode($aRow['company_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
			}
            if($update){
				$edit = '<a href="./edit/' . base64_encode($aRow['company_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
			$row[]=$edit.$view;
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getCompanyDetails($companyId) {
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$branchResult="";
        $companyResult = $this->select(array('company_id' => (int) $companyId))->current();
		if($companyResult!=""){
			$query = $sql->select()->from('company_document_details')->where(array('company_id'=>$companyId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$docResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		}
		$result=array('company'=>$companyResult,'document'=>$docResult);
        return $result;
    }
    
	public function fetchAllActiveCompany(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from(array('c'=>'company_details'))
						//->join(array('b' => 'branches'),"b.company_id=c.company_id",array('branch_id','city'))
						->where(array('c.status'=>'active'));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchAllCompanyList(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from(array('c'=>'company_details'))
						//->join(array('b' => 'branches'),"b.company_id=c.company_id",array('branch_id','city'))
						;
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchBeecabsCompany(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from(array('c'=>'company_details'))
						//->join(array('b' => 'branches'),"b.company_id=c.company_id",array('branch_id','city'))
						->where(array('c.company_id'=>1,'c.status'=>'active'));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
}
?>
