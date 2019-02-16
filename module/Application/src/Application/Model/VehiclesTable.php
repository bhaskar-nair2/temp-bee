<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\EventLogTable;
use Application\Model\VehicleClientMapTable;
use Zend\Json\Json;

class VehiclesTable extends AbstractTableGateway {

    protected $table = 'vehicle_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addVehicleDetails($params) {
        $result = "";
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$vehicleClientMapDb = new VehicleClientMapTable($dbAdapter);
		$commonService=new CommonService();
        if (trim($params['vehicleType']) != "" && trim($params['vehicleNo']) != "") {
            $data = array(
                'vehicle_type' => base64_decode($params['vehicleType']),
                'vehicle_no' => $params['vehicleNo'],
                'vehicle_mode' => base64_decode($params['vehicleMode']),
                'vehice_category' => $params['vehicleCategory'],
                'no_of_seating' => $params['noOfSeating'],
                'vehicle_registration_year' => $params['vehRegistrationYear'],
                'hypothecation' => $params['hypothecation'],
                'loan_amount' => $params['loanAmount'],
				'vehicle_status'=> 'active'
            );
			
			if(isset($params['insuranceRenewalDate']) && trim($params['insuranceRenewalDate'])!=""){
				$data['insurance_renewal_date']=$commonService->dateFormat($params['insuranceRenewalDate']);
			}
			if(isset($params['taxRenewalDate']) && trim($params['taxRenewalDate'])!=""){
				$data['tax_renewal_date']=$commonService->dateFormat($params['taxRenewalDate']);
			}
			if(isset($params['fcRenewalDate']) && trim($params['fcRenewalDate'])!=""){
				$data['fc_renewal_date']=$commonService->dateFormat($params['fcRenewalDate']);
			}
			if(isset($params['permitRenewalDate']) && trim($params['permitRenewalDate'])!=""){
				$data['permit_renewal_date']=$commonService->dateFormat($params['permitRenewalDate']);
			}
			if(isset($params['loanClosingDate']) && trim($params['loanClosingDate'])!=""){
				$data['loan_closing_date']=$commonService->dateFormat($params['loanClosingDate']);
			}
			if(isset($params['pollutionRenewalDate']) && trim($params['pollutionRenewalDate'])!=""){
				$data['pollution_renewal_date']=$commonService->dateFormat($params['pollutionRenewalDate']);
			}
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
			
			//Vehicle client map
			if(isset($params['clientId']) && trim($params['clientId'])!=""){
				$vehicleClientMapDb->insert(array(
					'vehicle_id' => $lastInsertedId,
					'client_id' => $params['clientId']
				));
			}
			
            //event log
			$subject = $lastInsertedId;
			$eventType = 'vehicle -add';
			$action = 'added a new vehicle with the number '.$params['vehicleNo'];
			$resourceName = 'Vehicle';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }
	
	public function updateVehicleDetails($params) {
		$commonService=new CommonService();
		$vehicleClientMapDb = new VehicleClientMapTable($this->adapter);
        if (trim($params['vehicleId'])!="" && trim($params['vehicleNo']) != "") {
			$vehicleId=base64_decode($params['vehicleId']);
			$insRenewalDate=NULL;
			$taxRenewalDate=NULL;
			$fcRenewalDate=NULL;
			$permitRenewalDate=NULL;
			$loanClosingDate=NULL;
			$pollutionRenewalDate=NULL;
			
			if(isset($params['insuranceRenewalDate']) && trim($params['insuranceRenewalDate'])!=""){
				$insRenewalDate=$commonService->dateFormat($params['insuranceRenewalDate']);
			}
			if(isset($params['taxRenewalDate']) && trim($params['taxRenewalDate'])!=""){
				$taxRenewalDate=$commonService->dateFormat($params['taxRenewalDate']);
			}
			if(isset($params['fcRenewalDate']) && trim($params['fcRenewalDate'])!=""){
				$fcRenewalDate=$commonService->dateFormat($params['fcRenewalDate']);
			}
			
			if(isset($params['permitRenewalDate']) && trim($params['permitRenewalDate'])!=""){
				$permitRenewalDate=$commonService->dateFormat($params['permitRenewalDate']);
			}
			
			if(isset($params['loanClosingDate']) && trim($params['loanClosingDate'])!=""){
				$loanClosingDate=$commonService->dateFormat($params['loanClosingDate']);
			}
			if(isset($params['pollutionRenewalDate']) && trim($params['pollutionRenewalDate'])!=""){
				$pollutionRenewalDate=$commonService->dateFormat($params['pollutionRenewalDate']);
			}
            $data = array(
                'vehicle_type' => base64_decode($params['vehicleType']),
                'vehicle_no' => $params['vehicleNo'],
                'vehicle_mode' => base64_decode($params['vehicleMode']),
				'vehice_category' => $params['vehicleCategory'],
                'no_of_seating' => $params['noOfSeating'],
				'vehicle_registration_year' => $params['vehRegistrationYear'],
                'insurance_renewal_date' => $insRenewalDate,
                'tax_renewal_date' => $taxRenewalDate,
                'fc_renewal_date' => $fcRenewalDate,
                'permit_renewal_date' => $permitRenewalDate,
                'pollution_renewal_date' => $pollutionRenewalDate,
				'hypothecation' => $params['hypothecation'],
                'loan_amount' => $params['loanAmount'],
                'loan_closing_date' => $loanClosingDate,
				'vehicle_status'=> $params['status']
            );
			
            $this->update($data, array('vehicle_id' => $vehicleId));
            
			//Vehicle client map
			if(isset($params['clientId']) && trim($params['clientId'])!=""){
				$vehicleClientMapDb->delete("vehicle_id=".$vehicleId);
				$vehicleClientMapDb->insert(array(
					'vehicle_id' => $vehicleId,
					'client_id' => $params['clientId']
				));
			}
			
            //event log
			$subject = $vehicleId;
			$eventType = 'vehicle-update';
			$action = 'updated a vehicle with the number '.$params['vehicleNo'];
			$resourceName = 'Vehicle';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $vehicleId;
        }
    }
	
    public function fetchAllVehicles($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('type_name','vehicle_no','mode_name','DATE_FORMAT(tax_renewal_date,"%d-%b-%Y")','DATE_FORMAT(insurance_renewal_date,"%d-%b-%Y")','DATE_FORMAT(fc_renewal_date,"%d-%b-%Y")','DATE_FORMAT(permit_renewal_date,"%d-%b-%Y")','DATE_FORMAT(pollution_renewal_date,"%d-%b-%Y")','vehicle_status');
        $orderColumns = array('type_name','vd.vehicle_no','mode_name','vd.insurance_renewal_date','vd.fc_renewal_date','vd.permit_renewal_date','vd.tax_renewal_date','vd.pollution_renewal_date','vd.vehicle_status');

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
        $sQuery = $sql->select()->from(array('vd'=>'vehicle_details'))
						->columns(array('vehicle_id', 'vehicle_no','no_of_seating','vehicle_registration_year','insurance_renewal_date' => new \Zend\Db\Sql\Expression("DATE_FORMAT(insurance_renewal_date,'%d-%b-%Y')"),'tax_renewal_date' => new Expression("DATE_FORMAT(tax_renewal_date,'%d-%b-%Y')"),'fc_renewal_date' => new Expression("DATE_FORMAT(fc_renewal_date,'%d-%b-%Y')"),'permit_renewal_date' => new Expression("DATE_FORMAT(permit_renewal_date,'%d-%b-%Y')"),'pollution_renewal_date' => new Expression("DATE_FORMAT(pollution_renewal_date,'%d-%b-%Y')"),'loan_closing_date' => new Expression("DATE_FORMAT(loan_closing_date,'%d-%b-%Y')"),'hypothecation','loan_amount','vehicle_status'))
						->join(array('vt' => 'vehicle_type'), "vt.type_id=vd.vehicle_type", array('type_name'))
						->join(array('vm' => 'vehicle_mode'), "vm.mode_id=vd.vehicle_mode", array('mode_name'));
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$sQuery->where("((vd.insurance_renewal_date>='".$parameters['startDate']."' AND vd.insurance_renewal_date<='".$parameters['endDate']."') OR (vd.tax_renewal_date>='".$parameters['startDate']."' AND vd.tax_renewal_date<='".$parameters['endDate']."') OR (vd.fc_renewal_date>='".$parameters['startDate']."' AND vd.fc_renewal_date<='".$parameters['endDate']."') OR (vd.permit_renewal_date>='".$parameters['startDate']."' AND vd.permit_renewal_date<='".$parameters['endDate']."') OR (vd.pollution_renewal_date>='".$parameters['startDate']."' AND vd.pollution_renewal_date<='".$parameters['endDate']."'))");
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
		$queryContainer->exportQuery = $sQuery;
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
        $commonService=new CommonService();
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Vehicles', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
		if ($acl->isAllowed($role, 'Admin\Controller\Vehicles', 'view')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
		
        foreach ($rResult as $aRow) {
            $row = array();
            $edit="";
            $view="";
            $row[] = ucwords($aRow['type_name']);
            $row[] = $aRow['vehicle_no'];
            $row[] = ucwords($aRow['mode_name']);
            $row[] = $aRow['insurance_renewal_date'];
            $row[] = $aRow['fc_renewal_date'];
            $row[] = $aRow['permit_renewal_date'];
			$row[] = $aRow['tax_renewal_date'];
			$row[] = $aRow['pollution_renewal_date'];
            $row[] = ucwords($aRow['vehicle_status']);
			if($viewAction){
				$view = '<a href="./view/' . base64_encode($aRow['vehicle_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
			}
            if($update){
				$edit = '<a href="./edit/' . base64_encode($aRow['vehicle_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
			$row[]=$edit.$view;
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getVehicleDetails($id) {
        $row = $this->select(array('vehicle_id' => (int) $id))->current();
        return $row;
    }
	
	public function fetchAllActiveOwnVehicles(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$sQuery = $sql->select()->from('vehicle_details')->where(array('vehicle_mode'=>1,'vehicle_status'=>'active'));
		$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
		return $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchAllActiveVehicles(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$sQuery = $sql->select()->from(array('vd'=>'vehicle_details'))
						->columns(array('vehicle_id','vehicle_no'))
						->join(array('vt'=>'vehicle_type'),"vt.type_id=vd.vehicle_type", array('type_name'))
						->join(array('vm'=>'vehicle_mode'),"vm.mode_id=vd.vehicle_mode", array('mode_name'))
						->where(array('vehicle_status'=>'active'));
		$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
		return $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
    
	public function fetchAllInsuranceExpiry($parameters) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        $aColumns = array('vd.vehicle_no','type_name','mode_name','DATE_FORMAT(insurance_renewal_date,"%d-%b-%Y")','DATE_FORMAT(tax_renewal_date,"%d-%b-%Y")','DATE_FORMAT(fc_renewal_date,"%d-%b-%Y")','DATE_FORMAT(permit_renewal_date,"%d-%b-%Y")','DATE_FORMAT(pollution_renewal_date,"%d-%b-%Y")');
        $orderColumns = array('','vd.vehicle_no','type_name','mode_name','vd.insurance_renewal_date','vd.tax_renewal_date','vd.fc_renewal_date','vd.permit_renewal_date','vd.pollution_renewal_date');

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
        $sQuery = $sql->select()->from(array('vd'=>'vehicle_details'))
						->columns(array('vehicle_id', 'vehicle_no', 'vehicle_registration_year','insurance_renewal_date' => new \Zend\Db\Sql\Expression("DATE_FORMAT(insurance_renewal_date,'%d-%b-%Y')"),'tax_renewal_date' => new Expression("DATE_FORMAT(tax_renewal_date,'%d-%b-%Y')"),'fc_renewal_date' => new Expression("DATE_FORMAT(fc_renewal_date,'%d-%b-%Y')"),'permit_renewal_date' => new Expression("DATE_FORMAT(permit_renewal_date,'%d-%b-%Y')"),'pollution_renewal_date' => new Expression("DATE_FORMAT(pollution_renewal_date,'%d-%b-%Y')"),'vehicle_status'))
						->join(array('vt' => 'vehicle_type'), "vt.type_id=vd.vehicle_type", array('type_name'))
						->join(array('vm' => 'vehicle_mode'), "vm.mode_id=vd.vehicle_mode", array('mode_name'))
						->where(array('vehicle_status'=>'active'))
						->where("(insurance_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY) OR tax_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY) OR fc_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY) OR permit_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY) OR pollution_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY))");
						
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
		$tQuery = $sql->select()->from(array('vd'=>'vehicle_details'))
						->where(array('vehicle_status'=>'active'))
						->where("(insurance_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY) OR tax_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY) OR fc_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY) OR permit_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY) OR pollution_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY))");
		$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
		$tResult = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		
		//Count insurance renewal
		$iQuery = $sql->select()->from(array('vd'=>'vehicle_details'))
						->where(array('vehicle_status'=>'active'))
						->where("insurance_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$insCount = count($iResult);
		
		//Count tax renewal
		$taxQuery = $sql->select()->from(array('vd'=>'vehicle_details'))
						->where(array('vehicle_status'=>'active'))
						->where("tax_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$taxQueryStr = $sql->getSqlStringForSqlObject($taxQuery);
		$taxResult = $dbAdapter->query($taxQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$taxCount = count($taxResult);
		
		//Count fc renewal
		$fcQuery = $sql->select()->from(array('vd'=>'vehicle_details'))
						->where(array('vehicle_status'=>'active'))
						->where("fc_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$fcQueryStr = $sql->getSqlStringForSqlObject($fcQuery);
		$fcResult = $dbAdapter->query($fcQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$fcCount = count($fcResult);
		
		//Count permit renewal
		$pQuery = $sql->select()->from(array('vd'=>'vehicle_details'))
						->where(array('vehicle_status'=>'active'))
						->where("permit_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$pQueryStr = $sql->getSqlStringForSqlObject($pQuery);
		$pResult = $dbAdapter->query($pQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$permitCount = count($pResult);
		
		//Count pollution renewal
		$polQuery = $sql->select()->from(array('vd'=>'vehicle_details'))
						->where(array('vehicle_status'=>'active'))
						->where("pollution_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$polQueryStr = $sql->getSqlStringForSqlObject($polQuery);
		$pollResult = $dbAdapter->query($polQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$pollutionCount = count($pollResult);
		
		
        $iTotal = count($tResult);
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[]=$insCount."#".$taxCount."#".$fcCount."#".$permitCount."#".$pollutionCount;
            $row[] = '<a href="/vehicles/view/' . base64_encode($aRow['vehicle_id']) . '" title="View">'.$aRow['vehicle_no'].'</a>';
			$row[] = ucwords($aRow['type_name']);
            $row[] = ucwords($aRow['mode_name']);
            $row[] = $aRow['insurance_renewal_date'];
            $row[] = $aRow['tax_renewal_date'];
            $row[] = $aRow['fc_renewal_date'];
            $row[] = $aRow['permit_renewal_date'];
            $row[] = $aRow['pollution_renewal_date'];
           
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchVehicleDetails($id) {
		if($id>0){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from(array('vd'=>'vehicle_details'))
							->join(array('vt' => 'vehicle_type'), "vt.type_id=vd.vehicle_type", array('type_name'))
							->join(array('vm' => 'vehicle_mode'), "vm.mode_id=vd.vehicle_mode", array('mode_name'))
							->where(array('vehicle_id'=>(int) $id));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			return $result;
		}
    }
	
	public function fetchAllVehiclesBasedOnCategory($vehicleCategory) {
		if($vehicleCategory>0){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from(array('vd'=>'vehicle_details'))
							->columns(array('vehicle_id','vehicle_no'))
							->join(array('vt' => 'vehicle_type'), "vt.type_id=vd.vehicle_type", array('type_name'))
							//->join(array('vm' => 'vehicle_mode'), "vm.mode_id=vd.vehicle_mode", array('mode_name'))
							->where(array('vehice_category'=>(int) $vehicleCategory,'vehicle_status'=>'active'));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			return $result;
		}
    }
	
	public function fetchVehicleRenewalResult(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		
		//Count insurance renewal
		$iQuery = $sql->select()->from('vehicle_details')
						->columns(array('vehicle_id'))
						->where(array('vehicle_status'=>'active'))
						->where("insurance_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$insCount = count($iResult);
		
		//Count tax renewal
		$taxQuery = $sql->select()->from('vehicle_details')
						->columns(array('vehicle_id'))
						->where(array('vehicle_status'=>'active'))
						->where("tax_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$taxQueryStr = $sql->getSqlStringForSqlObject($taxQuery);
		$taxResult = $dbAdapter->query($taxQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$taxCount = count($taxResult);
		
		//Count fc renewal
		$fcQuery = $sql->select()->from('vehicle_details')
						->columns(array('vehicle_id'))
						->where(array('vehicle_status'=>'active'))
						->where("fc_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$fcQueryStr = $sql->getSqlStringForSqlObject($fcQuery);
		$fcResult = $dbAdapter->query($fcQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$fcCount = count($fcResult);
		
		//Count permit renewal
		$pQuery = $sql->select()->from('vehicle_details')
						->columns(array('vehicle_id'))
						->where(array('vehicle_status'=>'active'))
						->where("permit_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$pQueryStr = $sql->getSqlStringForSqlObject($pQuery);
		$pResult = $dbAdapter->query($pQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$permitCount = count($pResult);
		
		//Count pollution renewal
		$polQuery = $sql->select()->from('vehicle_details')
						->columns(array('vehicle_id'))
						->where(array('vehicle_status'=>'active'))
						->where("pollution_renewal_date <= (DATE(NOW()) + INTERVAL 30 DAY)");
		$polQueryStr = $sql->getSqlStringForSqlObject($polQuery);
		$pollResult = $dbAdapter->query($polQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$pollutionCount = count($pollResult);
		
		$renewal['Insurance Renewal']=$insCount;
		$renewal['FC Renewal']=$fcCount;
		$renewal['Tax Renewal']=$taxCount;
		$renewal['Permit Renewal']=$permitCount;
		$renewal['Pollution Renewal']=$pollutionCount;
		//return $result=Json::encode($renewal);
		return $renewal;
	}
	
	public function fetchAllActiveHotelVehicles($clientId=NULL){
		$logincontainer = new Container('credo');
		$logincontainer->clientId;
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$sQuery = $sql->select()->from(array('vcm'=>'vehicle_client_map'))
				->join(array('vd' => 'vehicle_details'), "vd.vehicle_id=vcm.vehicle_id", array('vehicle_no'))
				->join(array('vt'=>'vehicle_type'),"vt.type_id=vd.vehicle_type", array('type_name'))
				->join(array('vm'=>'vehicle_mode'),"vm.mode_id=vd.vehicle_mode", array('mode_name'))
				->where(array('vd.vehicle_status'=>'active'));
		if(trim($clientId)==""){
			$sQuery=$sQuery->where(array('vcm.client_id'=>$logincontainer->clientId));
		}else{
			$sQuery=$sQuery->where(array('vcm.client_id'=>$clientId));
		}
		$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
		return $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
}
?>
