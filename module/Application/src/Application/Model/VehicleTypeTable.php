<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\EventLogTable;

class VehicleTypeTable extends AbstractTableGateway {

    protected $table = 'vehicle_type';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addVehicleTypeDetails($params) {
        $result = "";
        if (trim($params['vehicleType']) != "") {
            $data = array(
                'type_name' => $params['vehicleType']
            );
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            //event log
			$subject = $lastInsertedId;
			$eventType = 'vehicle type-add';
			$action = 'added a new vehicle type with the name '.$params['vehicleType'];
			$resourceName = 'VehicleType';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }
	
	public function updateVehicleTypeDetails($params) {
        if (trim($params['typeId'])!="" && trim($params['vehicleType']) != "") {
			$typeId=base64_decode($params['typeId']);
            $data = array(
                'type_name' => $params['vehicleType']
            );
            $this->update($data, array('type_id' => $typeId));
            //event log
			$subject = $typeId;
			$eventType = 'vehicle type-update';
			$action = 'updated a vehicle type with the name '.$params['vehicleType'];
			$resourceName = 'VehicleType';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $typeId;
        }
    }
	
    public function fetchAllVehicleTypes($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('type_name');

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
        $sQuery = $sql->select()->from('vehicle_type');
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
        if ($acl->isAllowed($role, 'Admin\Controller\VehicleType', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = ucwords($aRow['type_name']);
            if($update){
            $row[] = '<a href="./edit/' . base64_encode($aRow['type_id']) . '" title="Edit"><i class="fa fa-pencil"> </i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getVehicleTypeDetails($typeId) {
        $row = $this->select(array('type_id' => (int) $typeId))->current();
        return $row;
    }
    
	public function fetchAllVehicleType(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('vehicle_type');
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
}
?>
