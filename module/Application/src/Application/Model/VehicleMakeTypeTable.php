<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;


class VehicleMakeTypeTable extends AbstractTableGateway {

    protected $table = 'vahicle_make_type';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function fetchAllVehicleMake(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('vahicle_make_type')->order("make_type ASC");
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
}
?>
