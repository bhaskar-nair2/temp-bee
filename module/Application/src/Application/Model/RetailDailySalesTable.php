<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\RetailTable;
use Application\Model\CorporateTable;
use Application\Model\EventLogTable;

class RetailDailySalesTable extends AbstractTableGateway {

    protected $table = 'retail_daily_sales';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addRetailDailySalesDetails($params) {
        $result = "";
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
        if (trim($params['salesDate']) != "") {
			$salesDate=$commonService->dateFormat($params['salesDate']);
            $data = array(
                'sales_date' => $salesDate,
                'total_trip' => $params['totalTrip'],
                'total_revenue' => $params['totalRevenue'],
                'daily_target' => $params['dailyTarget'],
                'monthly_target' => $params['monthlyTarget'],
                'added_on' => $commonService->getDateTime(),
                'added_by' => $logincontainer->employeeId
            );
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
			$corporateId=base64_decode($params['corporateId']);
			if($corporateId>0){
				//Update current target
				$dbAdapter = $this->adapter;
				$retailDb=new RetailTable($dbAdapter);
				$retailDb->updateCurrentTarget($corporateId,$params['monthlyTarget']);
			}
			
			//update monthly sales
			$globalDailyTarget=$params['globalDailyTarget'];
			$expSalesDate=explode("-",$salesDate);
			
			$cQuery = $sql->select()->from('retail_daily_sales')
							->where(array("YEAR(sales_date)='".$expSalesDate[0]."'"))
							->where(array("MONTH(sales_date)='".$expSalesDate[1]."'"))
							->order("sales_date asc");
			$cQueryStr = $sql->getSqlStringForSqlObject($cQuery);
			
			$cResult = $dbAdapter->query($cQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			$previousTarget=0;
			$currentTarget=0;
			
			foreach($cResult as $val){
				$totalSales=$val['total_trip']+$val['total_revenue'];
				$todayTarget=$globalDailyTarget-$totalSales;
				if($todayTarget>0){
					$todayTarget=$todayTarget*-1;
				}else{
					$todayTarget=$todayTarget*-1;
				}
				$previousTarget=$currentTarget;
				$currentTarget=$currentTarget+$todayTarget;
				
				$this->updateRetailDailySalesCurrentTarget($val['sales_id'],$todayTarget,$currentTarget,$previousTarget,$corporateId);
				
			}
        }
        //event log
		$subject = $lastInsertedId;
		$eventType = 'retail daily sales-add';
		$action = 'added a new retail daily sales with the date '.$params['salesDate'];
		$resourceName = 'Retail';
		$eventLogDb = new EventLogTable($this->adapter);
		$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        return $result;
    }
	
	public function updateRetailDailySalesDetails($params) {
        if (trim($params['salesId'])!="" && trim($params['salesDate']) != "") {
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$commonService=new CommonService();
			$logincontainer = new Container('credo');
			$retailId=base64_decode($params['salesId']);
			$salesDate=$commonService->dateFormat($params['salesDate']);
            $data = array(
                'sales_date' => $salesDate,
                'total_trip' => $params['totalTrip'],
                'total_revenue' => $params['totalRevenue'],
                'daily_target' => $params['dailyTarget'],
                'monthly_target' => $params['monthlyTarget'],
				'updated_on' => $commonService->getDateTime(),
                'updated_by' => $logincontainer->employeeId
            );
			
            $this->update($data, array('sales_id' => $retailId));
			$corporateId=base64_decode($params['corporateId']);
			//update monthly sales
			$globalDailyTarget=$params['globalDailyTarget'];
			$expSalesDate=explode("-",$salesDate);
			
			$cQuery = $sql->select()->from('retail_daily_sales')
							->where(array("YEAR(sales_date)='".$expSalesDate[0]."'"))
							->where(array("MONTH(sales_date)='".$expSalesDate[1]."'"))
							->order("sales_date asc");
			$cQueryStr = $sql->getSqlStringForSqlObject($cQuery);
			
			$cResult = $dbAdapter->query($cQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			$previousTarget=0;
			$currentTarget=0;
			
			foreach($cResult as $val){
				$totalSales=$val['total_trip']+$val['total_revenue'];
				$todayTarget=$globalDailyTarget-$totalSales;
				if($todayTarget>0){
					$todayTarget=$todayTarget*-1;
				}else{
					$todayTarget=$todayTarget*-1;
				}
				$previousTarget=$currentTarget;
				$currentTarget=$currentTarget+$todayTarget;
				
				$this->updateRetailDailySalesCurrentTarget($val['sales_id'],$todayTarget,$currentTarget,$previousTarget,$corporateId);
			}
			//event log
			$subject = $retailId;
			$eventType = 'retail daily sales-update';
			$action = 'updated a retail daily sales with the date '.$params['salesDate'];
			$resourceName = 'Retail';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $retailId;
        }
    }
	
	public function updateRetailDailySalesCurrentTarget($salesId,$todayTarget,$currentTarget,$previousTarget,$corporateId){
		$data = array(
			'daily_target' => $todayTarget,
			'monthly_target' => $currentTarget,
			'previous_target' => $previousTarget
		);
		$result=$this->update($data, array('sales_id' => $salesId));
		if($corporateId>0){
			//Update current target
			$dbAdapter = $this->adapter;
			$retailDb=new RetailTable($dbAdapter);
			$retailDb->updateCurrentTarget($corporateId,$currentTarget);
		}
	}
	
    public function fetchAllRetailDailySales($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('DATE_FORMAT(sales_date,"%d-%b-%Y")','total_trip','total_revenue','daily_target','monthly_target');
        $orderColumns = array('sales_date','total_trip','total_revenue','daily_target','monthly_target');

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
        $sQuery = $sql->select()->from('retail_daily_sales');
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
		$iQuery = $sql->select()->from('retail_daily_sales');
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
        if ($acl->isAllowed($role, 'Admin\Controller\Retail', 'edit-daily-sales')) {
            $update = true;
        } else {
            $update = false;
        }
        $commonService=new CommonService();
		$corporateDb=new CorporateTable($dbAdapter);
		$currentTargetResult=$corporateDb->fetchTargetValue('retail');
		$startDate=date('Y-m-d',strtotime($currentTargetResult['startTargetDate']));
		$endDate=date('Y-m-d',strtotime($currentTargetResult['endTargetDate']));
        foreach ($rResult as $aRow) {
            $row = array();
			$checkDate=date('Y-m-d',strtotime($aRow['sales_date']));
            $row[] = $commonService->humanDateFormat($aRow['sales_date']);
            $row[] = $aRow['total_trip'];
            $row[] = $aRow['total_revenue'];
            $row[] = $aRow['daily_target'];
            $row[] = $aRow['monthly_target'];
            if($update){
				if($checkDate>=$startDate && $checkDate<=$endDate){
				$row[] = '<a href="/retail/edit-daily-sales/' . base64_encode($aRow['sales_id']) . '" title="Edit"><i class="fa fa-pencil"> </i></a>';
				}else{
				$row[]='';	
				}
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchRetailDailySales($salesId) {
        $row = $this->select(array('sales_id' => (int) $salesId))->current();
        return $row;
    }
    
}
?>
