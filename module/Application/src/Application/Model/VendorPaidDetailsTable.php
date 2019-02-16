<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\EventLogTable;
use Application\Model\VendorPaymentsTable;

class VendorPaidDetailsTable extends AbstractTableGateway {

    protected $table = 'vendor_paid_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function makeVendorPaymentDetails($params) {
        $result = "";
		$dbAdapter = $this->adapter;
        //$sql = new Sql($dbAdapter);
		$logincontainer = new Container('credo');
		$commonService=new CommonService();
		$vendorPaymentsDb=new VendorPaymentsTable($dbAdapter);
		$vendorsDb=new VendorsTable($dbAdapter);
        if (trim($params['paymentId']) != "" && trim($params['payDate'])!="") {
			$paymentId=base64_decode($params['paymentId']);
			$vendorId=base64_decode($params['vendorId']);
			$expPaymentCode=explode("PC",$params['paymentCode']);
            $data = array(
                'payment_id' => $paymentId,
                'payment_sort_key' => $expPaymentCode[1],
                'payment_code' => $params['paymentCode'],
                'paid_date' => $commonService->dateFormat($params['payDate']),
                'paid_amount' => $params['payAmount'],
                'balance_amount' => $params['balanceAmount'],
				'remarks' => $params['remarks'],
                'added_on' => $commonService->getDateTime(),
                'added_by' => $logincontainer->employeeId
            );
			
			if(isset($params['balanceAmount']) && trim($params['balanceAmount'])==0){
				$vendorPaymentsDb->update(array('net_balance'=>$params['balanceAmount'],'payment_status'=>'completed'),array("payment_id" => $paymentId));
			}else{
				$vendorPaymentsDb->update(array('net_balance'=>$params['balanceAmount']),array("payment_id" => $paymentId));
			}
			//Update vendor current balance
			$vendorsDb->updateVendorCurrentBalance($vendorId,$params['payAmount']);
			
            $this->insert($data);
			$lastInsertedId = $this->lastInsertValue;
			
			//event log
			$subject = $lastInsertedId;
			$eventType = 'vendor-make-payment';
			$action = 'added a make vendor payment with the payment code '.$params['paymentCode']."-".$params['payDate'];
			$resourceName = 'Vendor Payment';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $lastInsertedId;
		}
    }
	
	public function fetchVendorPaidDetails($paymentId){
		if($paymentId>0){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from('vendor_paid_details')->where(array('payment_id'=>$paymentId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		}
	}
}
?>
