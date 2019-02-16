<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\EventLogTable;

class CyffDailySalesTable extends AbstractTableGateway {

    protected $table = 'cyff_daily_sales';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addCyffDailySalesDetails($params) {
        $result = "";
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        if (trim($params['salesDate']) != "") {
            $data = array(
                'sales_date' => $commonService->dateFormat($params['salesDate']),
                'total_trip' => $params['totalTrip'],
                'total_km_driven' => $params['totalKms'],
                'driver_deployed' => $params['driverDeployed'],
                'added_on' => $commonService->getDateTime(),
                'added_by' => $logincontainer->employeeId
            );
            $result = $this->insert($data);
			$lastInsertedId = $this->lastInsertValue;
            //event log
			$subject = $lastInsertedId;
			$eventType = 'cyff-add';
			$action = 'added a new cyff daily sales with the date '.$params['salesDate'];
			$resourceName = 'Cyff';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }
	
	public function updateCyffDailySalesDetails($params) {
        if (trim($params['cyffId'])!="" && trim($params['salesDate']) != "") {
			$commonService=new CommonService();
			$logincontainer = new Container('credo');
			$cyffId=base64_decode($params['cyffId']);
            $data = array(
                'sales_date' => $commonService->dateFormat($params['salesDate']),
                'total_trip' => $params['totalTrip'],
                'total_km_driven' => $params['totalKms'],
                'driver_deployed' => $params['driverDeployed'],
				'updated_on' => $commonService->getDateTime(),
                'updated_by' => $logincontainer->employeeId
            );
            $this->update($data, array('sales_id' => $cyffId));
            
            //event log
			$subject = $cyffId;
			$eventType = 'cyff-update';
			$action = 'updated a cyff daily sales with the date '.$params['salesDate'];
			$resourceName = 'Cyff';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			
			return $cyffId;
        }
    }
	
    public function fetchAllCyffDailySales($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('DATE_FORMAT(sales_date,"%d-%b-%Y")','total_trip','total_km_driven','driver_deployed');
        $orderColumns = array('sales_date','total_trip','total_km_driven','driver_deployed');

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
        $sQuery = $sql->select()->from('cyff_daily_sales');
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$sQuery->where("(sales_date>='".$parameters['startDate']."' AND sales_date<='".$parameters['endDate']."')");
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
		$queryContainer->exportQuery = $sQuery;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        //$iTotal = $this->select()->count();
		$iQuery = $sql->select()->from('cyff_daily_sales');
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$iQuery->where("(sales_date>='".$parameters['startDate']."' AND sales_date<='".$parameters['endDate']."')");
		}
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$iTotal=count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Cyff', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        $commonService=new CommonService();
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = $commonService->humanDateFormat($aRow['sales_date']);
            $row[] = $aRow['total_trip'];
            $row[] = $aRow['total_km_driven'];
            $row[] = $aRow['driver_deployed'];
            if($update){
            $row[] = '<a href="/cyff/edit/' . base64_encode($aRow['sales_id']) . '" title="Edit"><i class="fa fa-pencil"> </i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchCyffDailySales($salesId) {
        $row = $this->select(array('sales_id' => (int) $salesId))->current();
        return $row;
    }
    
}
?>
