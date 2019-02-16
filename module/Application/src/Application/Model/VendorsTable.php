<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\VendorVehicleMapTable;
use Application\Model\VehiclesTable;
use Application\Model\EventLogTable;

class VendorsTable extends AbstractTableGateway {

    protected $table = 'vendors';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addVendorDetails($params) {
        $result = "";
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$vendorCode="VC";
		$commonService=new CommonService();
        if (trim($params['vendorName']) != "") {
			$expClientCode=explode($vendorCode,$params['vendorCode']);
            $data = array(
                'vendor_name' => $params['vendorName'],
                'vendor_code' => $vendorCode,
                'vendor_sort_key' => $expClientCode[1],
                'vendor_no' => $params['vendorCode'],
                'vendor_type' => $params['vendorType'],
                'pan_no' => $params['panNumber'],
                'vendor_address' => nl2br($params['address']),
                'gst_no' => $params['gstNo'],
                'bank_account' => $params['accountNo'],
                'vendor_city' => implode(",",$params['vendorCity']),
                'contact_no' => $params['contactNo'],
                'alt_contact_no' => $params['altContactNo'],
                'status' => $params['status']
            );
			
            $this->insert($data);
			$lastInsertedId = $this->lastInsertValue;
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendors") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendors")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendors");
            }
			
			$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendors" . DIRECTORY_SEPARATOR . $lastInsertedId;
			if (!file_exists($pathname) && !is_dir($pathname)) {
				mkdir($pathname);
			}
			
			if (isset($_FILES['vendorAgreement']['name']) && trim($_FILES['vendorAgreement']['name'])!= '') {
				if (move_uploaded_file($_FILES["vendorAgreement"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$_FILES['vendorAgreement']['name'])) {
					$imageData = array('agreement_attachment' => $_FILES['vendorAgreement']['name']);
					$this->update($imageData, array("vendor_id" => $lastInsertedId));
				}
            }
				
			$vendorVehicleMapDb = new VendorVehicleMapTable($dbAdapter);
			$vehicleDb = new VehiclesTable($dbAdapter);
			
			$totalContact=count($params['vehicleNo']);
			if($totalContact>0 && trim($params['vendorType'])=='attached'){
				$pathname = UPLOAD_PATH. DIRECTORY_SEPARATOR."vendors". DIRECTORY_SEPARATOR.$lastInsertedId. DIRECTORY_SEPARATOR."vehicle";
				if (!file_exists($pathname) && !is_dir($pathname)) {
					mkdir($pathname);
				}
				
				//add vehicle details
				for($i=0;$i<$totalContact;$i++){
					$rcAttachmentName=NULL;
					
					if(isset($params['vehicleNo'][$i])&& trim($params['vehicleNo'][$i])!=''&& $params['vehicleNo'][$i]!=null){
						
						$vehicleDb->insert(array(
									'vehice_category' => $params['vehicleCategory'][$i],
									'vehicle_type' => $params['vehicleType'][$i],
									'vehicle_no' => $params['vehicleNo'][$i],
									'vehicle_mode' => 2, //Attached
									'vehicle_status' => 'active',
									'coming_from' => 'vendor',
									'tax_renewal_date' => $commonService->dateFormat($params['taxExpiry'][$i]),
									'insurance_renewal_date' => $commonService->dateFormat($params['insuranceExpiry'][$i])
								));
						$vehicleId = $vehicleDb->lastInsertValue;
						if (isset($_FILES['rcAttachment']['name'][$i]) && trim($_FILES['rcAttachment']['name'][$i])!= '') {
							$extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['rcAttachment']['name'][$i],PATHINFO_EXTENSION));
							$rcAttachmentName = $commonService->removespecials($_FILES['rcAttachment']['name'][$i])."." . $extension;
							if (move_uploaded_file($_FILES["rcAttachment"]["tmp_name"][$i],$pathname . DIRECTORY_SEPARATOR .$rcAttachmentName)) {
								
							}
						}
						
						$vendorVehicleMapDb->insert(array(
							'vehicle_id' => $vehicleId,
							//'tax_expiry' => $commonService->dateFormat($params['taxExpity'][$i]),
							'rc_attachment' => $rcAttachmentName,
							//'ins_exp_attachment' => $insExpAttachmentName,
							'vendor_id' => $lastInsertedId
						));
					}
				}
			}
			
        }
        //event log
		$subject = $lastInsertedId;
		$eventType = 'vendor-add';
		$action = 'added a new vendor with the name '.ucwords($params['vendorName']);
		$resourceName = 'Vendor';
		$eventLogDb = new EventLogTable($this->adapter);
		$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        return $lastInsertedId;
    }
	
	public function updateVendorDetails($params) {
        $result = "";
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		
		$commonService=new CommonService();
        if (trim($params['vendorName']) != "" && trim($params['vendorId'])!="") {
			$vendorId=base64_decode($params['vendorId']);
            $data = array(
                'vendor_name' => $params['vendorName'],
                'vendor_type' => $params['vendorType'],
                'pan_no' => $params['panNumber'],
                'vendor_address' => nl2br($params['address']),
                'gst_no' => $params['gstNo'],
                'bank_account' => $params['accountNo'],
                'vendor_city' => implode(",",$params['vendorCity']),
                'contact_no' => $params['contactNo'],
                'status' => $params['status']
            );
			
			$this->update($data, array('vendor_id' => $vendorId));
			$lastInsertedId = $vendorId;
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendors") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendors")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendors");
            }
			
			$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendors" . DIRECTORY_SEPARATOR . $lastInsertedId;
			if (!file_exists($pathname) && !is_dir($pathname)) {
				mkdir($pathname);
			}
			
			//Delete upload file
			if(isset($params['deletedAgreementFile']) && trim($params['deletedAgreementFile'])!=""){
				$filePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendors" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR.$params['deletedAgreementFile'];
				if(file_exists($filePath)){
					unlink($filePath);
					$this->update(array('agreement_attachment'=>NULL), array("vendor_id" => $lastInsertedId));
				}
			}
			
			if (isset($_FILES['vendorAgreement']['name']) && trim($_FILES['vendorAgreement']['name'])!= '') {
				if (move_uploaded_file($_FILES["vendorAgreement"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$_FILES['vendorAgreement']['name'])) {
					$imageData = array('agreement_attachment' => $_FILES['vendorAgreement']['name']);
					$this->update($imageData, array("vendor_id" => $lastInsertedId));
				}
            }
			
			$vendorVehicleMapDb = new VendorVehicleMapTable($dbAdapter);
			$vehicleDb = new VehiclesTable($dbAdapter);
			$pathname = UPLOAD_PATH. DIRECTORY_SEPARATOR."vendors". DIRECTORY_SEPARATOR.$lastInsertedId. DIRECTORY_SEPARATOR."vehicle";
			$totalContact=count($params['vehicleNo']);
			if($totalContact>0 && trim($params['vendorType'])=='attached'){
				if (isset($params['deletedId']) && trim($params['deletedId']) != "") {
					$deletedId = explode(",", $params['deletedId']);
					$totalId = count($deletedId);
					for ($z = 0; $z < $totalId; $z++) {
						$query = $sql->select()->from('vendor_vehicle_map')->where(array('map_id'=>$deletedId[$z]));
						$queryStr = $sql->getSqlStringForSqlObject($query);
						$result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
						if($result!=""){
							$rcFilePath =$pathname. DIRECTORY_SEPARATOR.$result['rc_attachment'];
							//$insFilePath =$pathname. DIRECTORY_SEPARATOR.$result['ins_exp_attachment'];
							if(file_exists($rcFilePath)){
								unlink($rcFilePath);
							}
						}
						$vendorVehicleMapDb->delete("map_id=".$deletedId[$z]);
					}
				}
			
				
				if($totalContact>0){
					$pathname = UPLOAD_PATH. DIRECTORY_SEPARATOR."vendors". DIRECTORY_SEPARATOR.$lastInsertedId. DIRECTORY_SEPARATOR."vehicle";
					if (!file_exists($pathname) && !is_dir($pathname)) {
						mkdir($pathname);
					}
					
					for($i=0;$i<$totalContact;$i++){
						$rcAttachmentName=NULL;
						$insExpAttachmentName=NULL;
						if(isset($params['vehicleNo'][$i])&& trim($params['vehicleNo'][$i])!=''&& $params['vehicleNo'][$i]!=null){
							
							if(isset($params['mapId'][$i]) && trim($params['mapId'][$i]) !=""){
								$data=array(
									'vehicle_id' => $params['vehicleId'][$i],
									'vendor_id' => $lastInsertedId
								);
								
								if (isset($_FILES['rcAttachment']['name'][$i]) && trim($_FILES['rcAttachment']['name'][$i])!= '') {
									
									$query = $sql->select()->from('vendor_vehicle_map')->where(array('map_id'=>$params['mapId'][$i]));
									$queryStr = $sql->getSqlStringForSqlObject($query);
									$result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
									if($result!=""){
										$rcFilePath =$pathname. DIRECTORY_SEPARATOR.$result['rc_attachment'];
										if(file_exists($rcFilePath)){
											unlink($rcFilePath);
										}
									}
									
									$extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['rcAttachment']['name'][$i],PATHINFO_EXTENSION));
									$rcAttachmentName = $commonService->removespecials($_FILES['rcAttachment']['name'][$i])."." . $extension;
									if (move_uploaded_file($_FILES["rcAttachment"]["tmp_name"][$i],$pathname . DIRECTORY_SEPARATOR .$rcAttachmentName)) {
										$data['rc_attachment']=$rcAttachmentName;
									}
								}
								$vendorVehicleMapDb->update($data,"map_id=".$params['mapId'][$i]);
								$vehicleData=array(
									'vehice_category' => $params['vehicleCategory'][$i],
									'vehicle_type' => $params['vehicleType'][$i],
									'vehicle_no' => $params['vehicleNo'][$i],
									'vehicle_mode' => 2, //Attached
									'vehicle_status' => $params['vehicleStatus'][$i],
									'tax_renewal_date' => $commonService->dateFormat($params['taxExpity'][$i]),
									'insurance_renewal_date' => $commonService->dateFormat($params['insuranceExpiry'][$i])
								);
								//\Zend\Debug\Debug::dump($vehicleData);
								//die;
								$vehicleDb->update($vehicleData,"vehicle_id=".$params['vehicleId'][$i]);
								
							}else{
								$vehicleDb->insert(array(
										'vehice_category' => $params['vehicleCategory'][$i],
										'vehicle_type' => $params['vehicleType'][$i],
										'vehicle_no' => $params['vehicleNo'][$i],
										'vehicle_mode' => 2, //Attached
										'vehicle_status' => $params['vehicleStatus'][$i],
										'coming_from' => 'vendor',
										'tax_renewal_date' => $commonService->dateFormat($params['taxExpity'][$i]),
										'insurance_renewal_date' => $commonService->dateFormat($params['insuranceExpiry'][$i])
									));
								$vehicleId = $vehicleDb->lastInsertValue;
								$data=array(
									'vehicle_id' => $vehicleId,
									'vendor_id' => $lastInsertedId
								);
								if(isset($_FILES['rcAttachment']['name'][$i]) && trim($_FILES['rcAttachment']['name'][$i])!= '') {
									$extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['rcAttachment']['name'][$i],PATHINFO_EXTENSION));
									$rcAttachmentName = $commonService->removespecials($_FILES['rcAttachment']['name'][$i])."." . $extension;
									if (move_uploaded_file($_FILES["rcAttachment"]["tmp_name"][$i],$pathname . DIRECTORY_SEPARATOR .$rcAttachmentName)) {
										$data['rc_attachment']=$rcAttachmentName;
									}
								}
								
								$vendorVehicleMapDb->insert($data);
							}
							
						}
						
					}
				}
			
			}
		}
        //event log
		$subject = $lastInsertedId;
		$eventType = 'vendor-update';
		$action = 'updates a vendor with the name '.ucwords($params['vendorName']);
		$resourceName = 'Vendor';
		$eventLogDb = new EventLogTable($this->adapter);
		$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        return $lastInsertedId;
    }
	
    public function fetchAllVendors($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','v.vendor_city','v.contact_no','v.status');
		
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
        $sQuery = $sql->select()->from(array('v'=>'vendors'));
				//->join(array('vvm' => 'vendor_vehicle_map'), "vvm.vendor_id=v.vendor_id", array('map_id'))
				//->join(array('cd' => 'city_details'), "cd.city_id IN(v.vendor_city)", array('city_name' => new Expression("Group_Concat(city_name ORDER BY city_name SEPARATOR ',')")))
				//->group('v.vendor_id');
				
				
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
        if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'view')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
		
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'add-rental')) {
            $addRentalAction = true;
        } else {
            $addRentalAction = false;
        }
		
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'edit-rental')) {
            $editRentalAction = true;
        } else {
            $editRentalAction = false;
        }
		
        foreach ($rResult as $aRow) {
			$cityName="";
			/*
			$query = $sql->select()->from('vendor_rentals')->where(array('vendor_id'=>$aRow['vendor_id']));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$vResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			$countVendorRentals=count($vResult);
			*/
			$query = $sql->select()->from('city_details')
							->columns(array('city_name' => new Expression("Group_Concat(city_name ORDER BY city_name SEPARATOR ',')")))
							->where("city_id IN (".$aRow['vendor_city'].")");
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$vResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			if(count($vResult)>0){
				$cityName=$vResult[0]['city_name'];
			}
			//\Zend\Debug\Debug::dump($vResult[0]['city_name']);die;
            $row = array();
			$edit="";
			$view="";
			$addRental="";
            $row[] = ucwords($aRow['vendor_name']);
            $row[] = $aRow['vendor_no'];
            $row[] = ucwords($aRow['vendor_type']);
            $row[] = $cityName;
            $row[] = $aRow['contact_no'];
			//$row[] = '';
            $row[] = ucfirst($aRow['status']);
            if($update){
            $edit = '<a href="./edit/' . base64_encode($aRow['vendor_id']) . '" title="Edit"><i class="fa fa-pencil"> </i></a>';
            }
			
			/*
			if($addRentalAction){
				if($countVendorRentals==0){
				$addRental = '<a href="./add-rental/' . base64_encode($aRow['vendor_id']) . '" class="btn btn-primary" style="margin-right: 2px;" title="Add Rental"><i class="fa fa-plus"> Add Rental</i></a>';
				}
			}
			
			if($editRentalAction){
				if($countVendorRentals>0){
				$addRental = '<a href="./edit-rental/' . base64_encode($aRow['vendor_id']) . '" class="btn btn-primary" style="margin-right: 2px;" title="Edit Rendal"><i class="fa fa-pencil"> Edit Rental</i></a>';
				}
			}*/
			
			if($viewAction){
            $view = '<a href="./view/' . base64_encode($aRow['vendor_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
            }
			$row[]=$edit.$view.$addRental;
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchVendorCode(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('vendors')->order('vendor_id DESC');
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if($result!=""){
            $vendorSortNo=$result['vendor_sort_key']+1;
            return $this->checkVendorNo($vendorSortNo);
        }else{
            return $vendorSortNo='001';
        }
    }
	
	public function checkVendorNo($vendorSortNo){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$idSize = strlen($vendorSortNo);
		if ($idSize < 4) {
			if ($idSize == 1) {
			$vendorSortNo = "00" . $vendorSortNo;
			} else if ($idSize == 2) {
			$vendorSortNo = "0" . $vendorSortNo;
			}else {
			$vendorSortNo;
			}
		}
		$query = $sql->select()->from('vendors')->where(array('vendor_sort_key'=>$vendorSortNo));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($result!=""){
			$vendorSortNo=$result['vendor_sort_key']+1;
			return $this->checkVendorNo($vendorSortNo);			
		}else{
			return $vendorSortNo;
		}
    }
	
	public function getVendorDetails($vendorId) {
		$bookedBy="";
		$guestResult="";
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
        $vendorResult = $this->select(array('vendor_id' => (int) $vendorId))->current();
		if($vendorResult!=""){
			$cQuery = $sql->select()->from('vendor_vehicle_map')->where(array('vendor_id'=>$vendorResult['vendor_id']));
			$cQueryStr = $sql->getSqlStringForSqlObject($cQuery);
			$vehicleMap = $dbAdapter->query($cQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			
			foreach($vehicleMap as $key=>$veh){
				$vQuery = $sql->select()->from('vehicle_details')
								->columns(array('vehicle_no','vehice_category','vehicle_type','insurance_renewal_date','tax_renewal_date','vehicle_status'))
								->where(array('vehicle_id'=>$veh['vehicle_id']));
				$vQueryStr = $sql->getSqlStringForSqlObject($vQuery);
				$vehicleReult = $dbAdapter->query($vQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
				if($vehicleReult!=""){
					$vehicleMap[$key]['vehicle_type']=$vehicleReult['vehicle_type'];
					$vehicleMap[$key]['vehicle_no']=$vehicleReult['vehicle_no'];
					$vehicleMap[$key]['vehice_category']=$vehicleReult['vehice_category'];
					$vehicleMap[$key]['insurance_renewal_date']=$vehicleReult['insurance_renewal_date'];
					$vehicleMap[$key]['tax_renewal_date']=$vehicleReult['tax_renewal_date'];
					$vehicleMap[$key]['vehicle_status']=$vehicleReult['vehicle_status'];
				}
			}
		}
		$result=array('vendor'=>$vendorResult,'vehicleMap'=>$vehicleMap);
        return $result;
    }
	
	public function fetchAllActiveVendor(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from('vendors')->order('vendor_name ASC');
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchVendorInfo($vendorId) {
		if($vendorId>0){
			return $this->select(array('vendor_id' => (int) $vendorId))->current();	
		}
	}
	
	public function getVendorRentalDetails($vendorId){
		if($vendorId>0){
			$tariff = array();
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from('vendors')->where(array('vendor_id'=>$vendorId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$vendorResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($vendorResult!=""){
				$sQuery = $sql->select()->from('vahicle_make_type');
				$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
				$makeResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
				
				foreach($makeResult as $make){
					$tQuery = $sql->select()->from('vendor_tariff_details')->where(array('vendor_id'=>$vendorId,'make_type'=>$make['make_id'],'rental_type'=>1));
					$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
					$tariff[$make['make_type']]['localuse'] = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
					
					$toQuery = $sql->select()->from('vendor_tariff_details')->where(array('vendor_id'=>$vendorId,'make_type'=>$make['make_id'],'rental_type'=>2));
					$tOutQueryStr = $sql->getSqlStringForSqlObject($toQuery);
					$tariff[$make['make_type']]['outstation'] = $dbAdapter->query($tOutQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
					
					$eQuery = $sql->select()->from('vendor_rentals')->where(array('vendor_id'=>$vendorId,'make_type'=>$make['make_id']));
					$eQueryStr = $sql->getSqlStringForSqlObject($eQuery);
					$tariff[$make['make_type']]['extra_tariff'] = $dbAdapter->query($eQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
				}
			}
			$result=array('vendor'=>$vendorResult,'tariff'=>$tariff);
			return $result;
		}
	}
	
	public function fetchAllActiveVendorBasedCity($city){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		//$query = $sql->select()->from('vendors')->where("vendor_city IN($city)");
		//$queryStr = $sql->getSqlStringForSqlObject($query);
		$queryStr="SELECT * FROM vendors WHERE FIND_IN_SET('".$city."',vendor_city) > 0 order by vendor_name ASC";
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchVehicleNoByVendor($vendorId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('v'=>'vendors'))
					->columns(array('vendor_id'))
					->join(array('vvm' => 'vendor_vehicle_map'), "vvm.vendor_id=v.vendor_id", array('map_id'))
					->join(array('vd' => 'vehicle_details'), "vd.vehicle_id=vvm.vehicle_id", array('vehicle_id','vehicle_no'))
					->where(array('v.vendor_id' => (int) $vendorId));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function addVendorCurrentBalance($vendorId,$amount){
		$vendorResult = $this->select(array('vendor_id' => (int) $vendorId))->current();
		if($vendorResult!=""){
			$balance=$vendorResult['current_balance']+$amount;
			$data = array(
                'current_balance' => $balance
            );
			$this->update($data, array('vendor_id' => $vendorId));
		}
	}
	
	public function updateVendorCurrentBalance($vendorId,$paidAmt){
		$vendorResult = $this->select(array('vendor_id' => (int) $vendorId))->current();
		if($vendorResult!=""){
			$balance=$vendorResult['current_balance']-$paidAmt;
			$data = array(
                'current_balance' => $balance
            );
			$this->update($data, array('vendor_id' => $vendorId));
		}
	}
	
	public function updateAllVendorCurrentBalance(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('v'=>'vendors'))
					->columns(array('vendor_id','current_balance'))
					//->where(array('vendor_id'=>25))
					;
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		foreach($result as $res){
			$totalPayment=0;
			$pQuery=$sql->select()->from('vendor_payments')
						->where(array('payment_status'=>'pending','vendor_id' =>  (int) $res['vendor_id']));
			$pQueryStr = $sql->getSqlStringForSqlObject($pQuery);
			$pResult=$dbAdapter->query($pQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			if(count($pResult)>0){
				
				$paidAmt="";
				foreach($pResult as $pRes){
					$totalPayment+=$pRes['net_balance'];
					/*
					$sQuery=$sql->select()->from('vendor_paid_details')
							->where(array('payment_id' => (int) $pRes['payment_id']));
					$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
					$sResult=$dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
					
					if(count($sResult)>0){
						foreach($sResult as $val){
							$paidAmt+=$val['paid_amount'];
						}
					}*/
				}
				//$balanceAmt=$totalPayment-$paidAmt;
			}
			$balanceAmt=$totalPayment;
			$data = array(
				'current_balance' => $balanceAmt
			);
			$this->update($data, array('vendor_id' => $res['vendor_id']));
		}
	}
	
	public function fetchVendorPendingPayments(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from('vendors')->where(array('current_balance>0'))->order("current_balance DESC");
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $vendorResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
}
?>
