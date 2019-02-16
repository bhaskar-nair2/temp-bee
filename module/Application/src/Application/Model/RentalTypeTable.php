<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;


class RentalTypeTable extends AbstractTableGateway {

    protected $table = 'rental_type';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function fetchAllRentalType(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$cQuery = $sql->select()->from('rental_type')->order('type_name ASC');
		$cQueryStr = $sql->getSqlStringForSqlObject($cQuery);
		return $dbAdapter->query($cQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
}
?>
