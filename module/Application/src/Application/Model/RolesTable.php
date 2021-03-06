<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Zend\Config\Writer\PhpArray;
use Application\Model\EventLogTable;

class RolesTable extends AbstractTableGateway {

    protected $table = 'roles';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addRoleDetails($params) {
        $result = "";
        if (trim($params['roleName']) != "") {
            $data = array(
                'role_name' => $params['roleName'],
                'role_code' => $params['roleCode'],
                'status' => $params['status']
            );
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            //event log
			$subject = $lastInsertedId;
			$eventType = 'roles-add';
			$action = 'added a new role with the name '.ucwords($params['roleName']);
			$resourceName = 'Roles';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }
	
	public function updateRoleDetails($params) {
        if (trim($params['roleId'])!="" && trim($params['roleName']) != "") {
			$roleId=base64_decode($params['roleId']);
            $data = array(
                'role_name' => $params['roleName'],
                'role_code' => $params['roleCode'],
                'status' => $params['status']
            );
            $this->update($data, array('role_id' => $roleId));
            
            //event log
			$subject = $roleId;
			$eventType = 'roles-update';
			$action = 'updated a role with the name '.ucwords($params['roleName']);
			$resourceName = 'Roles';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			
			return $roleId;
        }
    }
	
    public function fetchAllRoles($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */

        $aColumns = array('role_name','role_code', 'status');

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
        $sQuery = $sql->select()->from('roles');
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
        if ($acl->isAllowed($role, 'Admin\Controller\Roles', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = ucwords($aRow['role_name']);
            $row[] = $aRow['role_code'];
            $row[] = ucfirst($aRow['status']);
            if($update){
            $row[] = '<a href="./edit/' . base64_encode($aRow['role_id']) . '" class="" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getRoleDetails($roleId) {
        $row = $this->select(array('role_id' => (int) $roleId))->current();
        return $row;
    }
    
	public function fetchAllActiveRoles(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from('roles')->where(array('status'=>'active'))->order('role_name ASC');
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchRoles() {
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $roleQuery = $sql->select()->from('roles')->order('role_name')
                                 ->where("role_code != 'daemon' AND status = 'active'");
        $roleQueryStr = $sql->getSqlStringForSqlObject($roleQuery);
        return $dbAdapter->query($roleQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
    }
    
    public function fetchAllResource(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $resourceQuery = $sql->select()->from('resources')->order('display_name');
        $resourceQueryStr = $sql->getSqlStringForSqlObject($resourceQuery);
        $resourceResult = $dbAdapter->query($resourceQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $count = count($resourceResult);
        for ($i = 0; $i < $count; $i++) {
            $privilageQuery = $sql->select()->from('privileges')
                                            ->where(array('resource_id' => $resourceResult[$i]['resource_id']))
                                            ->order('display_name');
            $privilageQueryStr = $sql->getSqlStringForSqlObject($privilageQuery);
            $resourceResult[$i]['privilege'] = $dbAdapter->query($privilageQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        }
        return $resourceResult;
    }
    
    public function mapRolePrivilege($params) {    
        try {
			$roleCode=$params['employeeCode'];
			$configFile = CONFIG_PATH . DIRECTORY_SEPARATOR . "acl.config.php";
			$config = new \Zend\Config\Config(include($configFile), true);
			$config->$roleCode = array();
	
			foreach ($params['resource'] as $resourceName => $privilege) {
				$config->$roleCode->$resourceName = $privilege;
			}
			$writer = new PhpArray();
			$writer->toFile($configFile, $config);
            
		} catch (Exception $exc) {
			error_log($exc->getMessage());
			error_log($exc->getTraceAsString());
		}
    }
}
?>
