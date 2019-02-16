<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\GlobalConfigTable;
use Application\Model\TempMailTable;
use Application\Model\VehiclesTable;
use Application\Model\EventLogTable;

class VehicleServiceTable extends AbstractTableGateway {

    protected $table = 'vehicle_service_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addVehicleServiceDetails($params) {
        $result = "";
		$commonService=new CommonService();
        if (isset($params['vehicle']) && trim($params['vehicle']) != "") {
			
			$workOrderSortNo=$this->checkWorkOrderNo($params['workOrderSortNo']);
			$workOrderNo="BCW".$workOrderSortNo;
            $data = array(
                'vehicle_id' => base64_decode($params['vehicle']),
                'work_order_sort_key' => $workOrderSortNo,
                'work_order_no' => $workOrderNo,
                'garage_name' => $params['garageName'],
                'work_description' => $params['workDescription'],
                'garage_in_kms' => $params['serviceKm'],
                //'bill_no' => $params['billNo'],
                //'bill_amount' => $params['billAmount'],
                //'payment_status' => $params['paymentStatus'],
                //'payment_mode' => base64_decode($params['paymentMode']),
                //'remarks' => $params['remarks'],
                //'next_service_kms' => $params['nextServiceKm'],
                //'insurance_claim_amount' => $params['insClaimAmount'],
                //'insurance_amount_paid' => $params['insAmountPaid'],
                'created_on'=>$commonService->getDateTime()
            );
			
			//if(isset($params['paymentDate']) && trim($params['paymentDate'])!=""){
            //    $data['payment_date']=$commonService->dateFormat($params['paymentDate']);
            //}
			
			if(isset($params['serviceDate']) && trim($params['serviceDate'])!=""){
                $data['garage_in_date']=$commonService->dateFormat($params['serviceDate']);
            }
			
			//if(isset($params['insPaidDate']) && trim($params['insPaidDate'])!=""){
            //    $data['insurance_paid_date']=$commonService->dateFormat($params['insPaidDate']);
            //}
			
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
			if($result>0){
				$dbAdapter = $this->adapter;
				//getVehicleDetails
				$VehicleDb=new VehiclesTable($dbAdapter);
				$vehicleResult=$VehicleDb->getVehicleDetails(base64_decode($params['vehicle']));
				$configDb=new GlobalConfigTable($dbAdapter);
				$workOrderReceiveMail=$configDb->fetchGlobalValue('work_order_receive_mail');
				$to=$workOrderReceiveMail;
				$subject="New Work Order (".$workOrderNo.")";
				
				$message="Hi, <br/><br/>";
				$message.="<table border='1' cellpadding='2' cellspacing='0' style='width:80%;'>";
				
				$message.="<tr>";
				$message.="<td style='width:25%;'>Work Order Number </td>";
				$message.="<td>".$workOrderNo."</td>";
				$message.="</tr>";
				
				$message.="<tr>";
				$message.="<td>Vehicle </td>";
				$message.="<td>".$vehicleResult['vehicle_no']."</td>";
				$message.="</tr>";
				
				$message.="<tr>";
				$message.="<td>Garage Name </td>";
				$message.="<td>".$params['garageName']."</td>";
				$message.="</tr>";
				
				$message.="<tr>";
				$message.="<td>Garage In Date </td>";
				$message.="<td>".$params['serviceDate']."</td>";
				$message.="</tr>";
				
				$message.="<tr>";
				$message.="<td>Garage In Km </td>";
				$message.="<td>".$params['serviceKm']."</td>";
				$message.="</tr>";
				
				$message.="<tr>";
				$message.="<td>Work Description </td>";
				$message.="<td>".$params['workDescription']."</td>";
				$message.="</tr>";
				
				$message.="</table>";
				
				//$message.="<h3>Work Description</h3><br/>";
				//$message.=$params['workDescription'];
				
				$fromName="New Work Order";
				$fromMail="";
				$tempMailDb=new TempMailTable($dbAdapter);
				$tempMailDb->insertTempMailDetails($to,$subject,$message,$fromMail,$fromName);
			}
			
			//event log
			$subject = $lastInsertedId;
			$eventType = 'vehicle service-add';
			$action = 'added a new vehicle service with the work number '.$workOrderNo;
			$resourceName = 'VehicleService';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }
	
	public function updateVehicleServiceDetails($params) {
        $result = "";
		$commonService=new CommonService();
        if (isset($params['vehicle']) && trim($params['vehicle']) != "" && trim($params['serviceId']) != "") {
			$serviceId=base64_decode($params['serviceId']);
            
			$data = array(
                'vehicle_id' => base64_decode($params['vehicle']),
                'garage_name' => $params['garageName'],
                'work_description' => $params['workDescription'],
                'garage_in_kms' => $params['serviceKm'],
				'bill_no' => $params['billNo'],
                'bill_amount' => $params['billAmount'],
                'payment_status' => $params['paymentStatus'],
                'payment_mode' => base64_decode($params['paymentMode']),
                'remarks' => $params['remarks'],
                'next_service_kms' => $params['nextServiceKm'],
                'insurance_claim_amount' => $params['insClaimAmount'],
                'insurance_amount_paid' => $params['insAmountPaid']
            );
			$data['payment_date']=NULL;
            $data['insurance_paid_date']=NULL;
            $data['garage_in_date']=NULL;
			if(isset($params['paymentStatus']) && trim($params['paymentStatus'])!=""){
				$data['work_order_status']="completed";
			}
			if(isset($params['paymentDate']) && trim($params['paymentDate'])!=""){
                $data['payment_date']=$commonService->dateFormat($params['paymentDate']);
            }
			if(isset($params['serviceDate']) && trim($params['serviceDate'])!=""){
                $data['garage_in_date']=$commonService->dateFormat($params['serviceDate']);
            }
			if(isset($params['insPaidDate']) && trim($params['insPaidDate'])!=""){
                $data['insurance_paid_date']=$commonService->dateFormat($params['insPaidDate']);
            }
			$this->update($data, array('service_id' => $serviceId));
			
			$vehicleResult=$this->getVehicleServiceDetails($serviceId);
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vehicle-service") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vehicle-service")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vehicle-service");
            }
			
			//Delete upload file
			if(isset($params['deletedFile']) && trim($params['deletedFile'])!=""){
				$filePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vehicle-service" . DIRECTORY_SEPARATOR . $serviceId. DIRECTORY_SEPARATOR.$params['deletedFile'];
				if(file_exists($filePath)){
					unlink($filePath);
					$this->update(array('file_name'=>NULL), array("service_id" => $serviceId));
				}
			}
			
			//Upload file
			if (isset($_FILES['file']['name']) && trim($_FILES['file']['name'])!= '') {
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vehicle-service" . DIRECTORY_SEPARATOR . $serviceId;
				if (!file_exists($pathname) && !is_dir($pathname)) {
					mkdir($pathname);
				}
			
				if (move_uploaded_file($_FILES["file"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$_FILES['file']['name'])) {
					$imageData = array('file_name' => $_FILES['file']['name']);
					$this->update($imageData, array("service_id" => $serviceId));
				}
            }
			
			//event log
			$subject = $serviceId;
			$eventType = 'vehicle service-update';
			$action = 'updated a vehicle service with the work number '.$vehicleResult['work_order_no'];
			$resourceName = 'VehicleService';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			
			return $serviceId;   
        }
    }
	
	public function fetchAllVehicleService($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('work_order_no','vehicle_no','garage_name',"DATE_FORMAT(garage_in_date,'%d-%b-%Y')",'garage_in_kms','bill_no','bill_amount','payment_status');
        $orderColumns = array('work_order_sort_key','vehicle_no','garage_name','garage_in_date','garage_in_kms','next_service_kms','bill_amount','payment_status');

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
        $sQuery = $sql->select()->from(array('vsd'=>'vehicle_service_details'))
				->join(array('v' => 'vehicle_details'), "v.vehicle_id=vsd.vehicle_id", array('vehicle_no'))
				->join(array('pm' => 'payment_mode'), "pm.type_id=vsd.payment_mode", array('payment_type'),'left')
				->where(array('work_order_status'=>'completed'));
				
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$sQuery->where("(garage_in_date>='".$parameters['startDate']."' AND garage_in_date<='".$parameters['endDate']."')");
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
		$queryContainer->exportQuery = $sQuery;
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select(array('work_order_status'=>'completed'))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
		if ($acl->isAllowed($role, 'Admin\Controller\VehicleService', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\VehicleService', 'view')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
        $commonService=new CommonService();
        foreach ($rResult as $aRow) {
            $row = array();
			$paymentDate="";
			$serviceDate="";
			$download="";
			$edit="";
			$view="";
			
			if(trim($aRow['garage_in_date'])!=""){
				$serviceDate=$commonService->humanDateFormat($aRow['garage_in_date']);	
			}
			
			if(trim($aRow['file_name'])!=""){
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vehicle-service" . DIRECTORY_SEPARATOR . $aRow['service_id'];
				if (file_exists($pathname. DIRECTORY_SEPARATOR.$aRow['file_name'])) {
					$download='<a href="/uploads/vehicle-service'. DIRECTORY_SEPARATOR.$aRow['service_id']. DIRECTORY_SEPARATOR.$aRow['file_name']. '" class="btn btn-success" style="margin-right: 2px;" target="__blank" title="Download Bill"><i class="fa fa-download"></i></a>';
				}
			}
			$row[] = $aRow['work_order_no'];
            $row[] = $aRow['vehicle_no'];
            $row[] = ucwords($aRow['garage_name']);
			$row[] = $serviceDate;
            $row[] = $aRow['garage_in_kms'];
			$row[] = $aRow['next_service_kms'];
			$row[] = $aRow['bill_amount'];
            $row[] = ucfirst($aRow['payment_status']);
			
			if($viewAction){
				$view='<a href="./view/' . base64_encode($aRow['service_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
			}
            if($update){
				$edit='<a href="./edit/' . base64_encode($aRow['service_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
			$row[] = $edit.$download.$view;
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function getVehicleServiceDetails($serviceId) {
        $row = $this->select(array('service_id' => (int) $serviceId))->current();
        return $row;
    }
	
	public function checkWorkOrderNo($workSortNo){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		
		$idSize = strlen($workSortNo."");
		if ($idSize < 4) {
			if ($idSize == 1) {
			$workSortNo = "00" . $workSortNo;
			} else if ($idSize == 2) {
			$workSortNo = "0" . $workSortNo;
			} else {
				$workSortNo;
			}
		}
		
		$query = $sql->select()->from('vehicle_service_details')->where(array('work_order_sort_key'=>$workSortNo));
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $vehicleServiceRes = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($vehicleServiceRes!=""){
			$workSortNo=$vehicleServiceRes['work_order_sort_key']+1;
			return $this->checkWorkOrderNo($workSortNo);
		}else{
			return $workSortNo;
		}
	}
	
	public function fetchNewWorkOrderNo(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from('vehicle_service_details')->order("service_id DESC");
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$vehicleServiceRes = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($vehicleServiceRes!=""){
			$workOrderSortNo=$vehicleServiceRes['work_order_sort_key']+1;
		}else{
			$workOrderSortNo="001";
		}
		$workOrderSortNo=$this->checkWorkOrderNo($workOrderSortNo);
		return $workOrderSortNo;
	}
	
	public function fetchAllPendingWorkOrder($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('work_order_no','vehicle_no','garage_name',"DATE_FORMAT(garage_in_date,'%d-%b-%Y')",'garage_in_kms','work_order_status');
        $orderColumns = array('work_order_sort_key','vehicle_no','garage_name','garage_in_date','garage_in_kms','work_order_status');

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
        $sQuery = $sql->select()->from(array('vsd'=>'vehicle_service_details'))
                ->columns(array('service_id','work_order_no','vehicle_id','garage_name','garage_in_date','garage_in_kms','work_order_status'))
				->join(array('v' => 'vehicle_details'), "v.vehicle_id=vsd.vehicle_id", array('vehicle_no'))
				->where(array('work_order_status'=>'pending'));
				
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
        $iTotal = $this->select(array('work_order_status'=>'pending'))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\VehicleService', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
		
		if ($acl->isAllowed($role, 'Admin\Controller\VehicleService', 'delete')) {
            $deleteService = true;
        } else {
            $deleteService = false;
        }
		
        $commonService=new CommonService();
        foreach ($rResult as $aRow) {
            $row = array();
			$paymentDate="";
			$serviceDate="";
			$edit="";
			$delete="";
			
			if(trim($aRow['garage_in_date'])!=""){
				$serviceDate=$commonService->humanDateFormat($aRow['garage_in_date']);	
			}
            $row[] = $aRow['work_order_no'];
            $row[] = $aRow['vehicle_no'];
            $row[] = ucwords($aRow['garage_name']);
			$row[] = $serviceDate;
            $row[] = $aRow['garage_in_kms'];
            $row[] = ucfirst($aRow['work_order_status']);
			
			if($deleteService){
				$delete='<a href="javascript:void(0);" onclick="deleteVendorPayment(\''.base64_encode($aRow['service_id']).'\')" class="btn btn-danger" style="margin-right: 2px;" title="Delete"><i class="fa fa-trash-o"></i></a>';
			}
			
            if($update){
             $edit = '<a href="./edit/' . base64_encode($aRow['service_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
			$row[]=$edit.$delete;
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchPendingWorkOrderList(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('vsd'=>'vehicle_service_details'))
						->columns(array('service_id','work_order_no','garage_in_date' => new Expression("DATE_FORMAT(garage_in_date,'%d-%b-%Y')")))
						->join(array('v' => 'vehicle_details'), "v.vehicle_id=vsd.vehicle_id", array('vehicle_id','vehicle_no'))
						->where(array('vsd.work_order_status'=>'pending'));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchVehicleServiceDetails($serviceId) {
		if((int) $serviceId>0){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from(array('vsd'=>'vehicle_service_details'))
							->join(array('v' => 'vehicle_details'), "v.vehicle_id=vsd.vehicle_id", array('vehicle_no'))
							->join(array('pm' => 'payment_mode'), "pm.type_id=vsd.payment_mode", array('payment_type'),'left')
							->where(array('vsd.service_id'=>$serviceId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		}
    }
	
	public function deleteVehicleService($serviceId){
		if((int) $serviceId>0){
			$vResult=$this->fetchVehicleServiceDetails($serviceId);
			if($vResult!=""){
				$subject = $serviceId;
				$eventType = 'vehicle servive-delete';
				$action = 'deleted a vehicle service of '.$vResult['work_order_no'];
				$resourceName = 'VehicleService';
				$eventLogDb = new EventLogTable($this->adapter);
				$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
				return $this->delete("service_id=".$serviceId);
			}
		}
	}
}
?>
