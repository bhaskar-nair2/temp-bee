<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;


class GuestTable extends AbstractTableGateway {

    protected $table = 'guest_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    public function fetchGuestDetails($clientId){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
        $query = $sql->select()->from('guest_details')->where(array('client_id'=>$clientId))->order('guest_name ASC');
		$queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function getGuestIdByName($clientId,$guestName,$mobile,$guestId=null){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		if($guestId>0){
			$data=array(
				'guest_name' => $guestName,
				'mobile_no' => $mobile
			);
			$this->update($data,array('guest_id' => $guestId));
			return $guestId;
		}else{
			$query = $sql->select()->from('guest_details')->where(array('client_id'=>$clientId,'guest_name'=>$guestName));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($result!=""){
				if($mobile!=""){
					$data=array(
						'mobile_no' => $mobile
					);
					$this->update($data,array('guest_id' => $result['guest_id']));
				}
				$guestId=$result['guest_id'];
			}else{
				$data = array(
					'guest_name' => $guestName,
					'mobile_no' => $mobile,
					'client_id' => $clientId
				);
				
				$this->insert($data);
				$guestId = $this->lastInsertValue;
			}
		
			return $guestId;
		}
	}
}
?>
