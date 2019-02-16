<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;


class VehicleModeTable extends AbstractTableGateway {

    protected $table = 'vehicle_mode';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function fetchAllVehicleMode(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('vehicle_mode');
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE);
	}
}
?>
