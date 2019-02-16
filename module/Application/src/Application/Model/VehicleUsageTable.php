<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\VehiclesTable;
use Application\Model\EventLogTable;

class VehicleUsageTable extends AbstractTableGateway {

    protected $table = 'vehicle_usage_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addVehicleUsageDetails($params) {
        $result = "";
        $usageMonth = "";
        $usageYear = "";
        $usageDate = "";
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        if (trim($params['vehicle']) != "" && trim($params['usageMonth'])!="") {
			if(isset($params['usageMonth']) && trim($params['usageMonth'])!=""){
				$usageDate=$commonService->dateFormat("01-".$params['usageMonth']);
				$expUsageMonth=explode("-",$params['usageMonth']);
				$usageMonth=trim($expUsageMonth[0]);
				$usageYear=trim($expUsageMonth[1]);
			}
            $data = array(
                'vehicle_id' => $params['vehicle'],
                'month' => $usageMonth,
                'year' => $usageYear,
                'usage_date' => $usageDate,
                'odometer_open_km' => $params['odometerOpenKms'],
                'odometer_close_km' => $params['odometerCloseKms'],
                'total_used_km' => $params['totalUsedKms'],
                'gps_used_km' => $params['gpsUsedKms'],
                'total_hotel_revenue_kms' => $params['totalHotelRevenueKms'],
                'total_corp_revenue_kms' => $params['totalCorpRevenueKms'],
                'non_revenue_kms' => $params['nonRevenueKms'],
                'total_hotel_revenue' => $params['totalHotelRevenue'],
                'total_corp_revenue' => $params['totalCorpRevenue'],
                'hotel_revenue_per_km' => $params['hotelRevenuePerKm'],
                'corp_revenue_per_km' => $params['corpRevenuePerKm'],
                'total_fuel' => $params['totalFuel'],
                'fuel_amount' => $params['fuelAmount'],
                'mileage_per_litre' => $params['mileagePerLit'],
                'added_on' => $commonService->getDateTime(),
                'added_by' => $logincontainer->employeeId
            );
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            $vehicleDb = new VehiclesTable($this->adapter);
            $vehicleResult=$vehicleDb->getVehicleDetails($params['vehicle']);
            //event log
			$subject = $lastInsertedId;
			$eventType = 'vehicle usage-add';
			$action = 'added a new vehicle usage with the vehicle number '.$vehicleResult['vehicle_no'];
			$resourceName = 'VehicleUsage';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			
        }
        return $result;
    }
	
	public function updateVehicleUsageDetails($params) {
		$usageMonth = "";
        $usageYear = "";
        $usageDate = "";
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        if (trim($params['usageId'])!="" && trim($params['vehicle']) != "" && trim($params['usageMonth'])!="") {
			$usageId=base64_decode($params['usageId']);
            if(isset($params['usageMonth']) && trim($params['usageMonth'])!=""){
				$usageDate=$commonService->dateFormat("01-".$params['usageMonth']);
				$expUsageMonth=explode("-",$params['usageMonth']);
				$usageMonth=trim($expUsageMonth[0]);
				$usageYear=trim($expUsageMonth[1]);
			}
            $data = array(
                'vehicle_id' => $params['vehicle'],
                'month' => $usageMonth,
                'year' => $usageYear,
                'usage_date' => $usageDate,
                'odometer_open_km' => $params['odometerOpenKms'],
                'odometer_close_km' => $params['odometerCloseKms'],
                'total_used_km' => $params['totalUsedKms'],
                'gps_used_km' => $params['gpsUsedKms'],
                'total_hotel_revenue_kms' => $params['totalHotelRevenueKms'],
                'total_corp_revenue_kms' => $params['totalCorpRevenueKms'],
                'non_revenue_kms' => $params['nonRevenueKms'],
                'total_hotel_revenue' => $params['totalHotelRevenue'],
                'total_corp_revenue' => $params['totalCorpRevenue'],
                'hotel_revenue_per_km' => $params['hotelRevenuePerKm'],
                'corp_revenue_per_km' => $params['corpRevenuePerKm'],
                'total_fuel' => $params['totalFuel'],
                'fuel_amount' => $params['fuelAmount'],
                'mileage_per_litre' => $params['mileagePerLit'],
				'updated_on' => $commonService->getDateTime(),
                'updated_by' => $logincontainer->employeeId
            );
            $this->update($data, array('usage_id' => $usageId));
            
            $vehicleDb = new VehiclesTable($this->adapter);
            $vehicleResult=$vehicleDb->getVehicleDetails($params['vehicle']);
            //event log
			$subject = $usageId;
			$eventType = 'vehicle usage-update';
			$action = 'updated a vehicle usage with the vehicle number '.$vehicleResult['vehicle_no'];
			$resourceName = 'VehicleUsage';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			
			return $usageId;
        }
    }
	
    public function fetchAllVehicleUsages($parameters,$acl) {
		$queryContainer = new Container('query');
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
        $aColumns = array('vehicle_no','type_name','usage_date','total_used_km','non_revenue_kms','hotel_revenue_per_km','corp_revenue_per_km','total_fuel','mileage_per_litre');
        $orderColumns = array('vehicle_no','type_name','usage_date','total_used_km','non_revenue_kms','hotel_revenue_per_km','corp_revenue_per_km','total_fuel','mileage_per_litre');
		
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
        $sQuery = $sql->select()->from(array('vud'=>'vehicle_usage_details'))
								->columns(array('usage_id','month','odometer_open_km','odometer_close_km','total_used_km','gps_used_km','total_hotel_revenue_kms','total_corp_revenue_kms','non_revenue_kms','total_hotel_revenue','total_corp_revenue','hotel_revenue_per_km','corp_revenue_per_km','total_fuel','fuel_amount','mileage_per_litre','usageDate' => new Expression("DATE_FORMAT(usage_date,'%b-%Y')"),'added_on' => new Expression("DATE_FORMAT(added_on,'%d-%b-%Y %H:%i:%s')")))
								->join(array('vd'=>'vehicle_details'),"vd.vehicle_id=vud.vehicle_id",array('vehicle_no'))
								->join(array('vt'=>'vehicle_type'),"vt.type_id=vd.vehicle_type", array('type_name'));
								//->join(array('ed'=>'employee_details'),"ed.employee_id=vud.added_by", array('employee_no','employee_name'))
								
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
	
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=$parameters['startDate'].'-01';
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$sQuery->where("(usage_date>='".$parameters['startDate']."' AND usage_date<='".$parameters['endDate']."')");
		}
		
        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //echo $sQueryStr; die;
		$queryContainer->exportQuery = $sQuery;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);
		
		/* Total data set length */
		$iQuery = $sql->select()->from('vehicle_usage_details');
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$iQuery->where("(usage_date>='".$parameters['startDate']."' AND usage_date<='".$parameters['endDate']."')");
		}
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$iTotal=count($iResult);
        
        //$iTotal = $this->select()->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $commonService=new CommonService();
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\VehicleUsage', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
		if ($acl->isAllowed($role, 'Admin\Controller\VehicleUsage', 'view')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
		
        foreach ($rResult as $aRow) {
            $row = array();
			$edit="";
			$view="";
			//$expDate=explode(" ",$aRow['added_on']);
            $row[] = $aRow['vehicle_no'];
            $row[] = $aRow['type_name'];
            $row[] = $aRow['usageDate'];
            $row[] = $aRow['total_used_km'];
            $row[] = $aRow['non_revenue_kms'];
            $row[] = $aRow['hotel_revenue_per_km'];
            $row[] = $aRow['corp_revenue_per_km'];
            $row[] = $aRow['mileage_per_litre'];
            //$row[] = $aRow['added_on'];
            //$row[] = $commonService->humanDateFormat($expDate[0])." ".$expDate[1];
            
            if($update){
				$edit='<a href="./edit/' . base64_encode($aRow['usage_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
			if($viewAction){
				$view = '<a href="./view/' . base64_encode($aRow['usage_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
            }
			if($update){
				$row[]=$edit.$view;
			}
			
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getVehicleUsageDetails($usageId) {
        $row = $this->select(array('usage_id' => (int) $usageId))->current();
        return $row;
    }
    
	public function fetchVehicleUsageDetails($usageId) {
		if($usageId>0){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from(array('vud'=>'vehicle_usage_details'))
						->join(array('vd'=>'vehicle_details'),"vd.vehicle_id=vud.vehicle_id",array('vehicle_no'))
						->join(array('vt'=>'vehicle_type'),"vt.type_id=vd.vehicle_type", array('type_name'))
						->where(array('vud.usage_id'=>$usageId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		}
    }
}
?>
