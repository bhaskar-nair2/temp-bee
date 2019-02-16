<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\EventLogTable;

class CorporateTable extends AbstractTableGateway {

    protected $table = 'corporate';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function addCorporateTargetDetails($params){
		if (trim($params['targetMonth'])!="") {
			$commonService=new CommonService();
			$logincontainer = new Container('credo');
			$targetMonth="";
			$targetYear="";
			if(isset($params['targetMonth']) && trim($params['targetMonth'])!=""){
				$targetDate=$commonService->dateFormat("01-".$params['targetMonth']);
				$expUsageMonth=explode("-",$params['targetMonth']);
				$targetMonth=trim($expUsageMonth[0]);
				$targetYear=trim($expUsageMonth[1]);
			}
			$this->update(array('target_status'=>'inactive'));
            $data = array(
                'target_date' => $targetDate,
                'target_month' => $targetMonth,
                'target_year' => $targetYear,
                'daily_target' => $params['dailyTarget'],
                'monthly_target' => $params['monthlyTarget'],
				'added_on' => $commonService->getDateTime(),
                'added_by' => $logincontainer->employeeId
            );
            $result = $this->insert($data);
            
            //event log
			$subject = $result;
			$eventType = 'corporate target-add';
			$action = 'added a new corporate target with the date '.$targetDate;
			$resourceName = 'Corporate';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $result;
        }
	}
	
	public function updateCorporateTargetDetails($params) {
        if (trim($params['corporateId'])!="" && trim($params['targetMonth'])!="") {
			$commonService=new CommonService();
			$logincontainer = new Container('credo');
			$corporateId=base64_decode($params['corporateId']);
			$targetMonth="";
			$targetYear="";
			if(isset($params['targetMonth']) && trim($params['targetMonth'])!=""){
				$targetDate=$commonService->dateFormat("01-".$params['targetMonth']);
				$expUsageMonth=explode("-",$params['targetMonth']);
				$targetMonth=trim($expUsageMonth[0]);
				$targetYear=trim($expUsageMonth[1]);
			}
			if(isset($params['status']) && trim($params['status'])=='active'){
			$this->update(array('target_status'=>'inactive'));
			}
            $data = array(
				'target_date' => $targetDate,
				'target_month' => $targetMonth,
                'target_year' => $targetYear,
                'daily_target' => $params['dailyTarget'],
                'monthly_target' => $params['monthlyTarget'],
				'target_status' => $params['status'],
				'updated_on' => $commonService->getDateTime(),
                'updated_by' => $logincontainer->employeeId
            );
            $this->update($data, array('corporate_id' => $corporateId));
            //event log
			$subject = $corporateId;
			$eventType = 'corporate target-update';
			$action = 'updated a corporate target with the date '.$targetDate;
			$resourceName = 'Corporate';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $corporateId;
        }
    }
	
    public function fetchAllCorporateTargetDetails($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('target_date','daily_target','monthly_target','target_status');
        $orderColumns = array('target_date','daily_target','monthly_target','target_status');

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
        $sQuery = $sql->select()->from('corporate');
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
        $iTotal = $this->select()->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Corporate', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = $aRow['target_month']." - ".$aRow['target_year'];
            $row[] = $aRow['daily_target'];
            $row[] = $aRow['monthly_target'];
            $row[] = ucwords($aRow['target_status']);
			if($update){
				$row[] = '<a href="./edit/' . base64_encode($aRow['corporate_id']) . '" title="Edit"><i class="fa fa-pencil"> </i></a>';
			}
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchCorporateTarget($targetId) {
        $row = $this->select(array('corporate_id' => (int) $targetId))->current();
        return $row;
    }
    
    public function fetchTargetValue($unitName) {
		if(trim($unitName)!=""){
			$result="";
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$commonService=new CommonService();
			$sQuery = $sql->select()->from($unitName)->where(array('target_status' => 'active'));
			$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
			$configValues = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($configValues){
				$startTargetDate=$commonService->humanDateFormat($configValues['target_date']);
				$endTargetDate=$commonService->humanDateFormat(date("Y-m-t",strtotime($startTargetDate)));
				$result=array('corporateId'=>$configValues[$unitName.'_id'],'dailyTarget'=>$configValues['daily_target'],'monthlyTarget'=>$configValues['monthly_target'],'currentTarget'=>$configValues['current_target'],'startTargetDate'=>$startTargetDate,'endTargetDate'=>$endTargetDate);
			}
			return $result;
		}
    }
	
	public function updateCurrentTarget($corporateId,$currentTarget){
		$this->update(array('current_target'=>$currentTarget), array('corporate_id' => $corporateId));
	}
	
	public function fetchCurrentTarget($unitName){
		$result=0;
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$sQuery = $sql->select()->from($unitName)->where(array('target_status' => 'active'));
		$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
		$targetResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($targetResult){
			if(trim($targetResult['current_target'])!=""){
				$result=$targetResult['current_target'];	
			}
		}
		return $result;
	}
}
?>
