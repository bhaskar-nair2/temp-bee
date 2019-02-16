<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;


class ClientContactTable extends AbstractTableGateway {

    protected $table = 'client_contacts';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function fetchContactList($clientId){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
        $query = $sql->select()->from('client_contacts')->where(array('client_id'=>$clientId))->order('contact_name ASC');
		$queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function getClientContactIdByName($clientId,$contactName,$mobile,$email=NULL,$contactId=NULL){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		if($contactId>0){
			$data=array(
				'contact_name' => $contactName,
				'mobile_no' => $mobile
			);
			if(trim($email)!=""){
				$data['email']=$email;
			}
			$this->update($data,array('contact_id' => $contactId));
			return $contactId;
		}else{
			$query = $sql->select()->from('client_contacts')->where(array('client_id'=>$clientId,'contact_name'=>$contactName));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($result!=""){
				if($mobile!=""){
					$data=array(
						'contact_name' => $contactName,
						'mobile_no' => $mobile
					);
					if(trim($email)!=""){
						$data['email']=$email;
					}
					$this->update($data,array('contact_id' => $result['contact_id']));
				}
				$contactId=$result['contact_id'];
			}else{
				$data = array(
					'contact_name' => $contactName,
					'mobile_no' => $mobile,
					'client_id' => $clientId
				);
				if(trim($email)!=""){
					$data['email']=$email;
				}
				$this->insert($data);
				$contactId = $this->lastInsertValue;
			}
			
			return $contactId;
		}
	}
}
?>
