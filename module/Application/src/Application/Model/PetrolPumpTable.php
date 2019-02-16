<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Model\EventLogTable;

class PetrolPumpTable extends AbstractTableGateway {

    protected $table = 'petrol_pumps';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function checkPetrolPumpName($pumpName){
		if($pumpName!=""){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from('petrol_pumps')->where(array('pump_name'=>trim($pumpName)));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$pumpResult=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($pumpResult){
				$lastInsertedId=$pumpResult['pump_id'];
			}else{
				$data = array(
					'pump_name' => trim($pumpName)
				);
				$result = $this->insert($data);
				$lastInsertedId = $this->lastInsertValue;
			}
			return $lastInsertedId;
		}
	}
	
	public function fetchAllPetrolPump(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
        $query = $sql->select()->from('petrol_pumps')->order('pump_name ASC');
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
}
?>
