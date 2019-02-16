<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Zend\Config\Writer\PhpArray;
use Application\Model\EventLogTable;

class HotelTable extends AbstractTableGateway {

    protected $table = 'hotel_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addHotelDetails($params) {
        $result = "";
        if (trim($params['hotelName']) != "") {
            $data = array(
                'hotel_name' => $params['hotelName'],
                'hotel_code' => $params['hotelCode'],
                'city' => $params['city']
            );
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            //event log
			$subject = $lastInsertedId;
			$eventType = 'hotel-add';
			$action = 'added a new hotel with the name '.ucwords($params['hotelName']);
			$resourceName = 'Hotel';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $lastInsertedId;
    }
	
	public function updateHotelDetails($params) {
        if (trim($params['hotelId'])!="" && trim($params['hotelName']) != "") {
			$hotelId=base64_decode($params['hotelId']);
            $data = array(
                'hotel_name' => $params['hotelName'],
				'hotel_code' => $params['hotelCode'],
                'city' => $params['city'],
                'status' => $params['status']
            );
            $this->update($data, array('hotel_id' => $hotelId));
            
            //event log
			$subject = $hotelId;
			$eventType = 'hotel-update';
			$action = 'updated a hotel with the name '.ucwords($params['hotelName']);
			$resourceName = 'Hotel';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $hotelId;
        }
    }
	
    public function fetchAllHotels($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('hotel_name','city_name', 'status');

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
		$sQuery = $sql->select()->from(array('h'=>'hotel_details'))
				->join(array('c' => 'city_details'), "c.city_id=h.city", array('city_name'));
				
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
        if ($acl->isAllowed($role, 'Admin\Controller\Hotel', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = ucwords($aRow['hotel_name']);
            $row[] = $aRow['city_name'];
            $row[] = ucfirst($aRow['status']);
            if($update){
            $row[] = '<a href="./edit/' . base64_encode($aRow['hotel_id']) . '" class="" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getHotelDetails($hotelId) {
        $row = $this->select(array('hotel_id' => (int) $hotelId))->current();
        return $row;
    }
    
	public function fetchAllActiveHotels(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from('hotel_details')->where(array('status'=>'active'))->order('hotel_name ASC');
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
   
}
?>
