<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;


class PaymentModeTable extends AbstractTableGateway {

    protected $table = 'payment_mode';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function fetchAllPaymentModeDetails(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
        $query = $sql->select()->from('payment_mode')->order('payment_type ASC');
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
}
?>
