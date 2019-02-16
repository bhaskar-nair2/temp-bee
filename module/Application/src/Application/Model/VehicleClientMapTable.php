<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\EventLogTable;

class VehicleClientMapTable extends AbstractTableGateway {

    protected $table = 'vehicle_client_map';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function fetchVehicleClientMap($vehicleId){
		if($vehicleId!=""){
			$clientArray=array();
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$sQuery = $sql->select()->from('vehicle_client_map')->where(array('vehicle_id'=>$vehicleId));
			$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
			$sResult=$dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			if(count($sResult)>0){
				foreach($sResult as $val){
					$clientArray[]=$val['client_id'];
				}
				return $clientArray;
			}
		}
	}
	
}
?>
