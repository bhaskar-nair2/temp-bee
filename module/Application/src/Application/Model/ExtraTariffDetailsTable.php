<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;


class ExtraTariffDetailsTable extends AbstractTableGateway {

    protected $table = 'extra_tariff_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function checkExtraTariff($rentalId,$makeType){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from('extra_tariff_details')->where(array('rental_id'=>$rentalId,'make_type'=>$makeType));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
	}
}
?>
