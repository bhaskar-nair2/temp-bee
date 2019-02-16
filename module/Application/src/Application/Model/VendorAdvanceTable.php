<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Model\EventLogTable;
use Application\Service\CommonService;

class VendorAdvanceTable extends AbstractTableGateway {

    protected $table = 'vendor_advance_payments';
	
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function addVendorAdvanceDetails($params) {
		//\Zend\Debug\Debug::dump($params);die;
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        $result = "";
        if (trim($params['vendorName']) != "") {
            $data = array(
                'vendor_id' => $params['vendorName'],
                'advance_month_year' => $params['paymentMonthYear'],
                'advance_date' => trim($params['advanceDate']),
				'advance_amount' => $params['advance'],
				'remarks' => $params['remarks'],
                'added_on' => $commonService->getDateTime(),
                'added_by' => $logincontainer->employeeId
            );
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            //event log
			$subject = $lastInsertedId;
			$eventType = 'add-advance';
			$action = 'added a vendor advance '.ucwords($params['cityName']);
			$resourceName = 'Vendor Advance';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $result;
    }
	
	public function updateCityDetails($params) {
        if (trim($params['cityId'])!="" && trim($params['cityName']) != "") {
			$cityId=base64_decode($params['cityId']);
            $data = array(
                'city_name' => $params['cityName'],
                'status' => $params['status']
            );
            $this->update($data, array('city_id' => $cityId));
            
            //event log
			$subject = $cityId;
			$eventType = 'city-update';
			$action = 'updated a city with the name '.ucwords($params['cityName']);
			$resourceName = 'City';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			
			return $cityId;
        }
    }
	
	
	
}
?>
