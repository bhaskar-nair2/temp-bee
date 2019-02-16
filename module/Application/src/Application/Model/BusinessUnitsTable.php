<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;


class BusinessUnitsTable extends AbstractTableGateway {

    protected $table = 'business_units';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function fetchAllActiveBusinessUnits(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
        $query = $sql->select()->from('business_units')->where(array('status'=>'active'))->order('unit_name ASC');
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
}
?>
