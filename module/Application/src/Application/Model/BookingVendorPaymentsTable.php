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

class BookingVendorPaymentsTable extends AbstractTableGateway {

    protected $table = 'booking_vendor_payments';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addBookingVendorPaymentDetails($params) {
		
        $result = "";
		$dbAdapter = $this->adapter;
        //$sql = new Sql($dbAdapter);
		$logincontainer = new Container('credo');
		$commonService=new CommonService();
        if (isset($params['bookingId']) && trim($params['bookingId']) != "") {
			$bookingId=base64_decode($params['bookingId']);
			
            $data = array(
                'booking_id' => $bookingId,
                'basic_amount' => $params['vendorBasicAmount'],
                'extra_hrs_amount' => $params['extraHrsAmt'],
                'extra_kms_amount' => $params['vendorExtKmsAmt'],
                'parking_toll' => $params['parkingToll'],
				'permit' => $params['permit'],
				'driver_allowance' => $params['driverAllowance'],
				'service_tax' => $params['serviceTax'],
				'service_tax_amount' => $params['serviceTaxAmt'],
				'total_payable' => $params['totalPayable'],
				'revenue' => $params['bcRevenue'],
                'added_on' => $commonService->getDateTime(),
                'added_by' => $logincontainer->employeeId
            );
			
			if(isset($params['paymentStatus']) && trim($params['paymentStatus'])!=""){
				$data['vendor_payment_status']=$params['paymentStatus'];
			}
			
			if(isset($params['paidAmount']) && trim($params['paidAmount'])!=""){
				$data['paid_amount']=$params['paidAmount'];
			}
			
			if(isset($params['paymentMode']) && trim($params['paymentMode'])!=""){
				$data['vendor_payment_mode']=base64_decode($params['paymentMode']);
			}
			
			if(isset($params['paidDate']) && trim($params['paidDate'])!=""){
				$data['paid_date']=$commonService->dateFormat($params['paidDate']);
			}
			if(isset($params['balance']) && trim($params['balance'])!=""){
				$data['vendor_balance']=$params['balance'];
			}
			
            $this->insert($data);
			$lastInsertedId = $this->lastInsertValue;
			
			//event log
			$subject = $lastInsertedId;
			$eventType = 'booking-vendor-payment';
			$action = 'added a booking vendor payment';
			$resourceName = 'Booking Vendor Payment';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $lastInsertedId;
		}
    }
	
	public function updateBookingVendorPaymentDetails($params) {
		
        $result = "";
		$dbAdapter = $this->adapter;
        //$sql = new Sql($dbAdapter);
		$logincontainer = new Container('credo');
		$commonService=new CommonService();
        if (isset($params['bookingId']) && trim($params['bookingId']) != "") {
			$bookingId=base64_decode($params['bookingId']);
			$vendorPaymentId=base64_decode($params['vendorPaymentId']);
			
            $data = array(
                'booking_id' => $bookingId,
                'basic_amount' => $params['vendorBasicAmount'],
                'extra_hrs_amount' => $params['extraHrsAmt'],
                'extra_kms_amount' => $params['vendorExtKmsAmt'],
                'parking_toll' => $params['parkingToll'],
                'permit' => $params['permit'],
				'driver_allowance' => $params['driverAllowance'],
				'service_tax' => $params['serviceTax'],
				'service_tax_amount' => $params['serviceTaxAmt'],
				'total_payable' => $params['totalPayable'],
				'revenue' => $params['bcRevenue'],
                'added_by' => $logincontainer->employeeId
            );
			
			$data['vendor_payment_status']=NULL;
			$data['paid_amount']=NULL;
			$data['vendor_payment_mode']=NULL;
			$data['paid_date']=NULL;
			$data['vendor_balance']=NULL;
			
			if(isset($params['paymentStatus']) && trim($params['paymentStatus'])!=""){
				$data['vendor_payment_status']=$params['paymentStatus'];
			}
			
			if(isset($params['paidAmount']) && trim($params['paidAmount'])!=""){
				$data['paid_amount']=$params['paidAmount'];
			}
			
			if(isset($params['paymentMode']) && trim($params['paymentMode'])!=""){
				$data['vendor_payment_mode']=base64_decode($params['paymentMode']);
			}
			
			if(isset($params['paidDate']) && trim($params['paidDate'])!=""){
				$data['paid_date']=$commonService->dateFormat($params['paidDate']);
			}
			if(isset($params['balance']) && trim($params['balance'])!=""){
				$data['vendor_balance']=$params['balance'];
			}
			
			$this->update($data, array('vendor_payment_id' => $vendorPaymentId));
            
			
			//event log
			$subject = $vendorPaymentId;
			$eventType = 'booking-vendor-payment';
			$action = 'update a booking vendor payment';
			$resourceName = 'Booking Vendor Payment';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $vendorPaymentId;
		}
    }
	
	function getBookingVendorPaymentDetails($bookingId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from('booking_vendor_payments')->where(array('booking_id'=> (int) $bookingId));;
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
	}

}
?>
