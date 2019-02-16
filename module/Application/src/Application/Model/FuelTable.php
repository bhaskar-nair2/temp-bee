<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Zend\Config\Writer\PhpArray;
use Application\Model\EventLogTable;
use Application\Model\PetrolPumpTable;
use Application\Service\CommonService;

class FuelTable extends AbstractTableGateway {

    protected $table = 'fuel_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addFuelDetails($params) {
		//\Zend\Debug\Debug::dump($params);die;
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        $result = "";
        if (trim($params['fuelFillDate']) != "") {
			$petrolPumpDb = new PetrolPumpTable($this->adapter);
            $data = array(
                'fuel_date' => $commonService->dateFormat($params['fuelFillDate']),
                'vehicle_no' => $params['vehicle'],
                'vehicle_make' => $params['vehicleType'],
                'vehicle_mode' => $params['vehicleMode'],
                'driver_name' => $params['driverName'],
                'last_fuel_kms' => $params['lastFuelKms'],
                'current_kms' => $params['currentKms'],
                'total_kms' => $params['totalKms'],
                'quantity' => $params['quantity'],
                'amount_per_ltr' => $params['amtPerLtr'],
                'total_amount' => $params['totalAmount'],
                'mileage_per_litre' => $params['mileage'],
                'payment_mode' => base64_decode($params['paymentMode']),
                'staff_name' => base64_decode($params['employee']),
                'created_by' => $logincontainer->employeeId,
                'created_on' => $commonService->getDateTime()
            );
			
			if(isset($params['petrolPumpId']) && trim($params['petrolPumpId'])>0){
				$data['pump_id']=$params['petrolPumpId'];
			}else{
				$pumpId=$petrolPumpDb->checkPetrolPumpName($params['petrolPump']);
				$data['pump_id']=$pumpId;
			}
			
			if(isset($params['vehicleId']) && trim($params['vehicleId'])>0){
				$data['vehicle_id']=$params['vehicleId'];
			}
			if(isset($params['driverId']) && trim($params['driverId'])>0){
				$data['driver_id']=$params['driverId'];
			}
			if(isset($params['lastFuelDate']) && trim($params['lastFuelDate'])!=""){
				$data['last_fuel_date']=$commonService->dateFormat($params['lastFuelDate']);
			}
			
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            //event log
			$subject = $lastInsertedId;
			$eventType = 'add-fuel';
			$action = 'added a new fuel '.$params['fuelFillDate'];
			$resourceName = 'Fuel';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $lastInsertedId;
    }
	
	public function updateFuelDetails($params) {
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
		$petrolPumpDb = new PetrolPumpTable($this->adapter);
        if (trim($params['fuelId'])!="" && trim($params['fuelFillDate']) != "") {
			$fuelId=base64_decode($params['fuelId']);
			
			$data = array(
                'fuel_date' => $commonService->dateFormat($params['fuelFillDate']),
                'vehicle_no' => $params['vehicle'],
                'vehicle_make' => $params['vehicleType'],
                'vehicle_mode' => $params['vehicleMode'],
                'driver_name' => $params['driverName'],
                'last_fuel_kms' => $params['lastFuelKms'],
                'current_kms' => $params['currentKms'],
                'total_kms' => $params['totalKms'],
                'quantity' => $params['quantity'],
                'amount_per_ltr' => $params['amtPerLtr'],
				'mileage_per_litre' => $params['mileage'],
                'total_amount' => $params['totalAmount'],
                'payment_mode' => base64_decode($params['paymentMode']),
                'staff_name' => base64_decode($params['employee']),
                'created_by' => $logincontainer->employeeId,
                'created_on' => $commonService->getDateTime()
            );
			$data['vehicle_id']=NULL;
			$data['driver_id']=NULL;
			$data['last_fuel_date']=NULL;
			
			if(isset($params['petrolPumpId']) && trim($params['petrolPumpId'])>0){
				$data['pump_id']=$params['petrolPumpId'];
			}else{
				$pumpId=$petrolPumpDb->checkPetrolPumpName($params['petrolPump']);
				$data['pump_id']=$pumpId;
			}
			
			if(isset($params['vehicleId']) && trim($params['vehicleId'])>0){
				$data['vehicle_id']=$params['vehicleId'];
			}
			if(isset($params['driverId']) && trim($params['driverId'])>0){
				$data['driver_id']=$params['driverId'];
			}
			if(isset($params['lastFuelDate']) && trim($params['lastFuelDate'])!=""){
				$data['last_fuel_date']=$commonService->dateFormat($params['lastFuelDate']);
			}
            $this->update($data, array('fuel_id' => $fuelId));
            
            //event log
			$subject = $fuelId;
			$eventType = 'fuel-update';
			$action = 'updated a fuel with the date '.$params['fuelFillDate'];
			$resourceName = 'Fuel';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $fuelId;
        }
    }
	
    public function fetchAllFuelReports($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
		$queryContainer = new Container('query');
		$commonService=new CommonService();
        $aColumns = array('DATE_FORMAT(fuel_date,"%d-%b-%Y")','vehicle_no','vehicle_mode','pump_name','quantity','total_amount','mileage_per_litre','driver_name','payment_type');
        $orderColumns = array('fuel_date','vehicle_no','vehicle_mode','pump_name','quantity','total_amount','mileage_per_litre','driver_name','payment_type');

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
					if($orderColumns[intval($parameters['iSortCol_' . $i])]=='trip_from_date'){
						$sOrder = array('trip_from_date '.$parameters['sSortDir_' . $i]. ",",'trip_start_time '.$parameters['sSortDir_' . $i]. ",");
					}else{
						$sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
					}
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
		$sQuery = $sql->select()->from(array('f'=>'fuel_details'))
					->join(array('pm' => 'payment_mode'), "pm.type_id=f.payment_mode", array('payment_type'))
					->join(array('p' => 'petrol_pumps'), "p.pump_id=f.pump_id", array('pump_name'));
		
		
		if(isset($parameters['fromDate']) && trim($parameters['fromDate'])!="" && trim($parameters['toDate'])!=""){
			$startDate=$commonService->dateFormat(trim($parameters['fromDate']));
			$endDate=$commonService->dateFormat(trim($parameters['toDate']));
			$sQuery->where("(fuel_date>='".$startDate."' AND fuel_date<='".$endDate."')");
		}
		if(isset($parameters['vehicleType']) && trim($parameters['vehicleType'])!=""){
			$sQuery->where(array('f.vehicle_mode'=>$parameters['vehicleType']));
		}
		if(isset($parameters['petrolPump']) && trim($parameters['petrolPump'])!=""){
			$sQuery->where(array('f.pump_id'=>$parameters['petrolPump']));
		}
		
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
        if ($acl->isAllowed($role, 'Admin\Controller\Fuel', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
       
        foreach ($rResult as $aRow) {
			$edit="";
			
            $row = array();
            $row[] = $commonService->humanDateFormat($aRow['fuel_date']);
			$row[] = $aRow['vehicle_no'];
			$row[] = $aRow['vehicle_mode'];
            $row[] = ucwords($aRow['pump_name']);
            $row[] = $aRow['quantity'];
            $row[] = $aRow['total_amount'];
            $row[] = $aRow['mileage_per_litre'];
            $row[] = ucfirst($aRow['driver_name']);
            $row[] = ucfirst($aRow['payment_type']);
            
			if($update){
				$row[] = '<a href="../edit/' . base64_encode($aRow['fuel_id']) . '" class=" " title="Edit"><i class="fa fa-pencil-square-o"></i></a>';
			}
            
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchFuelDetails($fuelId) {
        $row = $this->select(array('fuel_id' => (int) $fuelId))->current();
        return $row;
    }
	
    public function fetchLastFuelInfo($vehicleId){
		if($vehicleId>0){
			$result = array();
			$commonService=new CommonService();
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from(array('f'=>'fuel_details'))->where(array('vehicle_id'=>$vehicleId))->order("fuel_date DESC");
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$fuelResult=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($fuelResult){
				$result= array('lastFuelKms'=>$fuelResult['current_kms'],'lastFuelDate'=>$commonService->humanDateFormat($fuelResult['fuel_date']));
			}
			return $result;
		}
	}
}
?>
