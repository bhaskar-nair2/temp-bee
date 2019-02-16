<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Zend\Config\Writer\PhpArray;
use Application\Model\EventLogTable;
use Application\Service\CommonService;

class HotelBookingsTable extends AbstractTableGateway {

    protected $table = 'hotel_bookings';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addHotelBookingDetails($params) {
		//\Zend\Debug\Debug::dump($params);die;
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        $result = "";
        if (trim($params['bookingRef']) != "") {
			$expBook=explode($params['bookingCode'],$params['bookingRef']);
			$sortKey=$expBook[1];
            $data = array(
                'client_id' => $logincontainer->clientId,
                'booking_code' => $params['bookingCode'],
                'sort_key' => $sortKey,
                'booking_no' => $params['bookingRef'],
                'booking_date_time' => $commonService->dateFormat($params['bookingDate'])." ".$params['bookingTime'],
                'guest_name' => $params['guestName'],
                'room_no' => $params['roomNo'],
                'booking_given_by' => $params['bookingGivenBy'],
                'booking_type' => $params['dutyType'],
                'vehicle_type' => $params['vehicleType'],
                'vehicle_no' => $params['vehicle'],
                'driver_name' => $params['driverName'],
				
				'trip_time' => $params['bookingTime'],
                'end_time' => $params['endTime'],
                'total_hrs' => $params['totalHrs'],
                'start_kms' => $params['startKms'],
                'close_kms' => $params['endKms'],
                'total_kms' => $params['totalKms'],
                'parking' => $params['parking'],
                'toll' => $params['toll'],
                'permit' => $params['permit'],
                'package' => $params['package'],
                'driver_allowance_day' => $params['driverAllowance'],
                'driver_allowance_night' => $params['nightAllowance'],
                'total_amount' => $params['totalAmount'],
				'status' => 'completed',
                'created_by' => $logincontainer->employeeId,
                'created_on' => $commonService->getDateTime()
            );
			if(isset($params['vehicleId']) && trim($params['vehicleId'])>0){
				$data['vehicle_id']=$params['vehicleId'];
			}
			if(isset($params['driverId']) && trim($params['driverId'])>0){
				$data['driver_id']=$params['driverId'];
			}
			if(trim($params['vehicleType'])=="Vendor"){
				if(isset($params['hotelBillAmount']) && trim($params['hotelBillAmount'])>0){
					$data['hotel_bill_amount']=$params['hotelBillAmount'];
				}
				if(isset($params['vendorBillAmount']) && trim($params['vendorBillAmount'])>0){
					$data['vendor_bill_amount']=$params['vendorBillAmount'];
				}
				if(isset($params['bcRevenue']) && trim($params['bcRevenue'])!=''){
					$data['bc_revenue']=$params['bcRevenue'];
				}
				if(isset($params['paymentStatus']) && trim($params['paymentStatus'])!=''){
					$data['payment_status']=$params['paymentStatus'];
				}
			}
			
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            //event log
			$subject = $lastInsertedId;
			$eventType = 'add-hotel-booking';
			$action = 'added a new hotel booking with the ref '.ucwords($params['bookingRef']);
			$resourceName = 'Hotel-Booking';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $lastInsertedId;
    }
	
	public function updateHotelBookingDetails($params) {
		$commonService=new CommonService();
        if (trim($params['bookingId'])!="" && trim($params['bookingRef']) != "") {
			$bookingId=base64_decode($params['bookingId']);
			
			$data = array(
                'booking_date_time' => $commonService->dateFormat($params['bookingDate'])." ".$params['bookingTime'],
                'guest_name' => $params['guestName'],
                'room_no' => $params['roomNo'],
                'booking_given_by' => $params['bookingGivenBy'],
                'booking_type' => $params['dutyType'],
                'vehicle_type' => $params['vehicleType'],
                'vehicle_no' => $params['vehicle'],
                'driver_name' => $params['driverName'],
				'trip_time' => $params['bookingTime'],
                'end_time' => $params['endTime'],
                'total_hrs' => $params['totalHrs'],
                'start_kms' => $params['startKms'],
                'close_kms' => $params['endKms'],
                'total_kms' => $params['totalKms'],
                'parking' => $params['parking'],
                'toll' => $params['toll'],
                'permit' => $params['permit'],
                'package' => $params['package'],
                'driver_allowance_day' => $params['driverAllowance'],
                'driver_allowance_night' => $params['nightAllowance'],
                'total_amount' => $params['totalAmount']
            );
			$data['vehicle_id']=NULL;
			$data['driver_id']=NULL;
			$data['hotel_bill_amount']=NULL;
			$data['vendor_bill_amount']=NULL;
			$data['bc_revenue']=NULL;
			$data['payment_status']=NULL;
			
			if(isset($params['vehicleId']) && trim($params['vehicleId'])>0){
				$data['vehicle_id']=$params['vehicleId'];
			}
			if(isset($params['driverId']) && trim($params['driverId'])>0){
				$data['driver_id']=$params['driverId'];
			}
			if(trim($params['vehicleType'])=="Vendor"){
				if(isset($params['hotelBillAmount']) && trim($params['hotelBillAmount'])>0){
					$data['hotel_bill_amount']=$params['hotelBillAmount'];
				}
				if(isset($params['vendorBillAmount']) && trim($params['vendorBillAmount'])>0){
					$data['vendor_bill_amount']=$params['vendorBillAmount'];
				}
				if(isset($params['bcRevenue']) && trim($params['bcRevenue'])!=''){
					$data['bc_revenue']=$params['bcRevenue'];
				}
				if(isset($params['paymentStatus']) && trim($params['paymentStatus'])!=''){
					$data['payment_status']=$params['paymentStatus'];
				}
			}
            $this->update($data, array('booking_id' => $bookingId));
            
            //event log
			$subject = $bookingId;
			$eventType = 'hotel-booking-update';
			$action = 'updated a hotel booking with the ref '.ucwords($params['bookingRef']);
			$resourceName = 'Hotel-Booking';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $bookingId;
        }
    }
	
	public function cancelHotelBookingDetails($params) {
		$commonService=new CommonService();
        if (trim($params['bookingId'])!="") {
			$bookingId=base64_decode($params['bookingId']);
			
			$data = array(
                'cancelled_by' => $params['cancelledBy'],
                'reason_for_cancellation' => $params['reasonForCancellation'],
                'status' => 'cancelled',
				'cancellation_time' => $commonService->getDateTime()
            );
        
            $this->update($data, array('booking_id' => $bookingId));
            
            //event log
			$subject = $bookingId;
			$eventType = 'hotel-booking-cancel';
			$action = 'cancelled a hotel booking with the ref '.ucwords($params['bookingRef']);
			$resourceName = 'Hotel-Booking';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $bookingId;
        }
    }
	
	public function completeHotelBookingDetails($bookingId,$bookingNo) {
		if($bookingId>0){
			$data = array(
                'status' => 'completed'
            );
			$this->update($data, array('booking_id' => $bookingId));
			
            //event log
			$subject = $bookingId;
			$eventType = 'hotel-booking-completed';
			$action = 'completed a hotel booking with the ref '.ucwords($bookingNo);
			$resourceName = 'Hotel-Booking';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $bookingId;
        }
    }
	
    public function fetchAllHotelBookings($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
		$commonService=new CommonService();
        $aColumns = array('booking_no','DATE_FORMAT(booking_date_time,"%d-%b-%Y")','trip_time','guest_name','room_no','type_name','vehicle_no','driver_name');
        $orderColumns = array('booking_no','booking_date_time','trip_time','guest_name','room_no','type_name','vehicle_no','driver_name');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
         * Ordering
         */

        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < intval($parameters['iSortingCols']); $i++) {
                if ($parameters['bSortable_' . intval($parameters['iSortCol_' . $i])] == "true") {
					if($orderColumns[intval($parameters['iSortCol_' . $i])]=='trip_from_date'){
						$sOrder = array('trip_from_date '.$parameters['sSortDir_' . $i]. ",",'trip_start_time '.$parameters['sSortDir_' . $i]. ",");
					}else{
						$sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
					}
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = "";
        if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
            $searchArray = explode(" ", $parameters['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);

                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                }
            }
        }

        /*
         * SQL queries
         * Get data to display
         */
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$sQuery = $sql->select()->from(array('hb'=>'hotel_bookings'))
					->join(array('rt' => 'rental_type'), "rt.type_id=hb.booking_type", array('type_name'))
					->where(array('client_id'=>'1','status'=>'pending'));
				
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select(array('client_id'=>'1','status'=>'pending'))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\HotelBooking', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        if ($acl->isAllowed($role, 'Admin\Controller\HotelBooking', 'complete')) {
            $completeAction = true;
        } else {
            $completeAction = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\HotelBooking', 'cancel')) {
            $cancelAction = true;
        } else {
            $cancelAction = false;
        }
        foreach ($rResult as $aRow) {
			$expDate=explode(" ",$aRow['booking_date_time']);
			$edit="";
			$complete="";
			$cancel="";
			$status="";
			if(trim($aRow['start_kms'])!="" && trim($aRow['close_kms'])!=""){
				$status='completed';
			}
            $row = array();
            $row[] = $aRow['booking_no'];
            $row[] = $commonService->humanDateFormat($expDate[0]);
            $row[] = substr($expDate[1],0,-3);
            $row[] = ucwords($aRow['guest_name']);
            $row[] = $aRow['room_no'];
            $row[] = $aRow['type_name'];
            $row[] = $aRow['vehicle_no'];
            $row[] = ucfirst($aRow['driver_name']);
            //$row[] = '<a href="javascript:void(0);" onclick="generateTripSheet(\''.base64_encode($aRow['booking_id']).'\')" class="" title="Generate Trip Sheet"><i class="fa fa-file-code-o"></i></a>';
			if($update){
				$row[] = '<a href="./edit/' . base64_encode($aRow['booking_id']) . '" class=" " title="Edit"><i class="fa fa-pencil-square-o"></i></a>';
			}
            if($completeAction){
				$row[] ='<a href="javascript:void(0);" class="" title="Complete Booking" onclick="completeBooking('.$aRow['booking_id'].',\''.$aRow['booking_no'].'\',\''.$status.'\')"><i class="fa fa-check-square-o"></i></a>';;
				//$row[] = '<a href="./complete/' . base64_encode($aRow['booking_id']) . '" class="" title="Complete Booking"><i class="fa fa-check-square-o"> </i></a>';
			}
			if($cancelAction){
				//$row[] = '<a href="./cancel/' . base64_encode($aRow['booking_id']) . '" class="" title="Cancel Booking"><i class="fa fa-times"> </i></a>';	
			}
            
            
            
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchHotelBooking($bookingId) {
        $row = $this->select(array('booking_id' => (int) $bookingId))->current();
        return $row;
    }
    
	public function generateBookingRef($clientId){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from('clients')->where(array('client_id'=>$clientId,'status'=>'active'));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($result!=""){
			$bookCode=$result['hotel_booking_code'];
			$query = $sql->select()->from('hotel_bookings')->where(array('client_id'=>$clientId,'booking_code'=>$bookCode))
						->order('sort_key DESC')->limit(1);
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$bResult=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			
			if($result!=""){
				$bookSortNo=$bResult['sort_key']+1;
				$bookNo=$this->checkBookingNo($bookCode,$bookSortNo,$clientId);
			}else{
				$bookSortNo='001';
				$bookNo=$this->checkBookingNo($bookCode,$bookSortNo,$clientId);
			}
			return array('bookCode'=>$bookCode,'bookSortNo'=>$bookNo);
		}
	}
	
	public function checkBookingNo($bookCode,$bookSortNo,$clientId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$bookSortNo = sprintf('%03d',$bookSortNo);
		$bookNo=$bookCode.$bookSortNo;
		$query = $sql->select()->from('hotel_bookings')->where(array('client_id'=>$clientId,'booking_no'=>$bookNo));
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $chkBookRes = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($chkBookRes!=""){
			$bookSortNo=$chkBookRes['sort_key']+1;
			return $this->checkBookingNo($bookCode,$bookSortNo,$clientId);
		}else{
			return $bookSortNo;
		}
	}
	
	public function fetchHotelBookingDetails($bookingId) {
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('hb'=>'hotel_bookings'))
					->join(array('rt' => 'rental_type'), "rt.type_id=hb.booking_type", array('type_name'))
					->join(array('c' => 'clients'), "c.client_id=hb.client_id", array('client_name'),'left')
					->where(array('booking_id'=>$bookingId));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
	}
	
	public function fetchAllHotelBookingReports($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
		$queryContainer = new Container('query');
		$commonService=new CommonService();
        $aColumns = array('DATE_FORMAT(booking_date_time,"%d-%b-%Y")','booking_no', 'guest_name','room_no','vehicle_no','driver_name','type_name','package');
        $orderColumns = array('booking_date_time','sort_key', 'guest_name','type_name','driver_name','vehicle_no','package');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
         * Ordering
         */

        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < intval($parameters['iSortingCols']); $i++) {
                if ($parameters['bSortable_' . intval($parameters['iSortCol_' . $i])] == "true") {
					if($orderColumns[intval($parameters['iSortCol_' . $i])]=='trip_from_date'){
						$sOrder = array('trip_from_date '.$parameters['sSortDir_' . $i]. ",",'trip_start_time '.$parameters['sSortDir_' . $i]. ",");
					}else{
						$sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
					}
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = "";
        if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
            $searchArray = explode(" ", $parameters['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);

                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                }
            }
        }

        /*
         * SQL queries
         * Get data to display
         */
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$sQuery = $sql->select()->from(array('hb'=>'hotel_bookings'))
					->join(array('rt' => 'rental_type'), "rt.type_id=hb.booking_type", array('type_name'))
					->where(array('client_id'=>'1',"hb.status!='pending'"));
					
		if(isset($parameters['fromDate']) && trim($parameters['fromDate'])!="" && trim($parameters['toDate'])!=""){
			$startDate=$commonService->dateFormat(trim($parameters['fromDate']));
			$endDate=$commonService->dateFormat(trim($parameters['toDate']));
			$sQuery->where("(DATE(booking_date_time)>='".$startDate."' AND DATE(booking_date_time)<='".$endDate."')");
		}
		if(isset($parameters['vehicleType']) && trim($parameters['vehicleType'])!=""){
			$sQuery->where(array('hb.vehicle_type'=>$parameters['vehicleType']));
		}
		if(isset($parameters['bookingStatus']) && trim($parameters['bookingStatus'])!=""){
			$sQuery->where(array('hb.status'=>$parameters['bookingStatus']));
		}
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $sQuery->reset('order');
		$queryContainer->exportQuery = $sQuery;
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select(array('client_id'=>'1',"status!='pending'"))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\HotelBooking', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        foreach ($rResult as $aRow) {
			$expDate=explode(" ",$aRow['booking_date_time']);
			$edit="";
			$complete="";
			$cancel="";
			if($aRow['package']=='non_revenue'){
				$aRow['package']="Non Revenue";
			}
            $row = array();
            $row[] = $commonService->humanDateFormat($expDate[0]);
			$row[] = $aRow['booking_no'];
            $row[] = ucwords($aRow['guest_name']);
			$row[] = $aRow['type_name'];
			$row[] = $aRow['driver_name'];
            $row[] = $aRow['vehicle_no'];
            $row[] = ucfirst($aRow['package']);
            $row[] = ucfirst($aRow['status']);
            if($update){
				$row[] = '<a href="../edit/' . base64_encode($aRow['booking_id']) . '/43045s" class=" " title="Edit"><i class="fa fa-pencil-square-o"></i></a>';
			}
            
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchLastClosingKms($vehicleId){
		if($vehicleId>0){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$sQuery = $sql->select()->from(array('hb'=>'hotel_bookings'))
						->columns(array('booking_id','close_kms'))
						->where(array("hb.vehicle_id"=>$vehicleId))
						->order("booking_id Desc");
			$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
			$result = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($result!=""){
				return $result['close_kms'];
			}
		}
	}
	
	public function addFpsHotelBookingDetails($params) {
		//\Zend\Debug\Debug::dump($params);die;
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        $result = "";
        if (trim($params['tripSheetNo']) != "") {
            $data = array(
                'client_id' => $params['clientId'],
                'booking_date_time' => $commonService->dateFormat($params['bookingDate'])." ".$params['bookingTime'],
                'guest_name' => $params['guestName'],
                'pickup_place' => $params['pickupPlace'],
                'drop_place' => $params['dropPlace'],
                'room_no' => $params['roomNo'],
                'booking_given_by' => $params['bookingGivenBy'],
                'booking_type' => $params['dutyType'],
                'vehicle_type' => $params['vehicleType'],
                'vehicle_mode' => $params['vehicleMode'],
                'vehicle_no' => $params['vehicle'],
                'driver_name' => $params['driverName'],
                'trip_sheet_no' => $params['tripSheetNo'],
				'trip_time' => $params['bookingTime'],
                'end_time' => $params['endTime'],
                'total_hrs' => $params['totalHrs'],
                'start_kms' => $params['startKms'],
                'close_kms' => $params['endKms'],
                'total_kms' => $params['totalKms'],
                'parking' => $params['parking'],
                'toll' => $params['toll'],
                'permit' => $params['permit'],
                'package' => $params['package'],
                'revenue' => $params['revenue'],
                'driver_allowance_day' => $params['driverAllowance'],
				'status'=>'completed',
                'created_by' => $logincontainer->employeeId,
                'created_on' => $commonService->getDateTime()
            );
			if(isset($params['vehicleId']) && trim($params['vehicleId'])>0){
				$data['vehicle_id']=$params['vehicleId'];
			}
			if(isset($params['driverId']) && trim($params['driverId'])>0){
				$data['driver_id']=$params['driverId'];
			}
			if(trim($params['vehicleType'])=="Vendor"){
				if(isset($params['hotelBillAmount']) && trim($params['hotelBillAmount'])>0){
					$data['hotel_bill_amount']=$params['hotelBillAmount'];
				}
				if(isset($params['vendorBillAmount']) && trim($params['vendorBillAmount'])>0){
					$data['vendor_bill_amount']=$params['vendorBillAmount'];
				}
				if(isset($params['bcRevenue']) && trim($params['bcRevenue'])!=''){
					$data['bc_revenue']=$params['bcRevenue'];
				}
				if(isset($params['paymentStatus']) && trim($params['paymentStatus'])!=''){
					$data['payment_status']=$params['paymentStatus'];
				}
			}
			
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            //event log
			$subject = $lastInsertedId;
			$eventType = 'add-fps-hotel-booking';
			$action = 'added a new hotel booking with the trip sheet ref '.ucwords($params['tripSheetNo']);
			$resourceName = 'Fps-Hotel-Booking';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $lastInsertedId;
    }
	
	public function updateFpsHotelBookingDetails($params) {
		$commonService=new CommonService();
        if (trim($params['bookingId'])!="" && trim($params['tripSheetNo']) != "") {
			$bookingId=base64_decode($params['bookingId']);
			
			$data = array(
                'booking_date_time' => $commonService->dateFormat($params['bookingDate'])." ".$params['bookingTime'],
                'guest_name' => $params['guestName'],
				//'guest_mobile_no' => $params['guestMobileNo'],
                'pickup_place' => $params['pickupPlace'],
                'drop_place' => $params['dropPlace'],
                'room_no' => $params['roomNo'],
                'booking_given_by' => $params['bookingGivenBy'],
                'booking_type' => $params['dutyType'],
                'vehicle_type' => $params['vehicleType'],
				'vehicle_mode' => $params['vehicleMode'],
                'vehicle_no' => $params['vehicle'],
                'driver_name' => $params['driverName'],
				'trip_sheet_no' => $params['tripSheetNo'],
				'trip_time' => $params['bookingTime'],
                'end_time' => $params['endTime'],
                'total_hrs' => $params['totalHrs'],
                'start_kms' => $params['startKms'],
                'close_kms' => $params['endKms'],
                'total_kms' => $params['totalKms'],
                'parking' => $params['parking'],
                'toll' => $params['toll'],
                'permit' => $params['permit'],
                'package' => $params['package'],
				'revenue' => $params['revenue'],
                'driver_allowance_day' => $params['driverAllowance']
            );
			$data['vehicle_id']=NULL;
			$data['driver_id']=NULL;
			$data['hotel_bill_amount']=NULL;
			$data['vendor_bill_amount']=NULL;
			$data['bc_revenue']=NULL;
			$data['payment_status']=NULL;
			
			if(isset($params['vehicleId']) && trim($params['vehicleId'])>0){
				$data['vehicle_id']=$params['vehicleId'];
			}
			if(isset($params['driverId']) && trim($params['driverId'])>0){
				$data['driver_id']=$params['driverId'];
			}
			if(trim($params['vehicleType'])=="Vendor"){
				if(isset($params['hotelBillAmount']) && trim($params['hotelBillAmount'])>0){
					$data['hotel_bill_amount']=$params['hotelBillAmount'];
				}
				if(isset($params['vendorBillAmount']) && trim($params['vendorBillAmount'])>0){
					$data['vendor_bill_amount']=$params['vendorBillAmount'];
				}
				if(isset($params['bcRevenue']) && trim($params['bcRevenue'])!=''){
					$data['bc_revenue']=$params['bcRevenue'];
				}
				if(isset($params['paymentStatus']) && trim($params['paymentStatus'])!=''){
					$data['payment_status']=$params['paymentStatus'];
				}
			}
            $this->update($data, array('booking_id' => $bookingId));
            
            //event log
			$subject = $bookingId;
			$eventType = 'fps-hotel-booking-update';
			$action = 'updated a hotel booking with the trip sheet ref '.ucwords($params['tripSheetNo']);
			$resourceName = 'Fps-Hotel-Booking';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $bookingId;
        }
    }
	
	public function fetchAllFpsHotelBookings($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
		$commonService=new CommonService();
        $aColumns = array('booking_no','DATE_FORMAT(booking_date_time,"%d-%b-%Y")','trip_time','guest_name','room_no','type_name','vehicle_no','driver_name');
        $orderColumns = array('booking_no','booking_date_time','trip_time','guest_name','room_no','type_name','vehicle_no','driver_name');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
         * Ordering
         */

        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < intval($parameters['iSortingCols']); $i++) {
                if ($parameters['bSortable_' . intval($parameters['iSortCol_' . $i])] == "true") {
					if($orderColumns[intval($parameters['iSortCol_' . $i])]=='trip_from_date'){
						$sOrder = array('trip_from_date '.$parameters['sSortDir_' . $i]. ",",'trip_start_time '.$parameters['sSortDir_' . $i]. ",");
					}else{
						$sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
					}
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = "";
        if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
            $searchArray = explode(" ", $parameters['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);

                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                }
            }
        }

        /*
         * SQL queries
         * Get data to display
         */
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$sQuery = $sql->select()->from(array('hb'=>'hotel_bookings'))
					->join(array('rt' => 'rental_type'), "rt.type_id=hb.booking_type", array('type_name'))
					->where(array('client_id'=>'61','status'=>'pending'));
				
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select(array('client_id'=>'61','status'=>'pending'))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\FpsHotelBooking', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        if ($acl->isAllowed($role, 'Admin\Controller\FpsHotelBooking', 'complete')) {
            $completeAction = true;
        } else {
            $completeAction = false;
        }
		
        foreach ($rResult as $aRow) {
			$expDate=explode(" ",$aRow['booking_date_time']);
			$edit="";
			$complete="";
			$cancel="";
			$status="";
			if(trim($aRow['start_kms'])!="" && trim($aRow['close_kms'])!=""){
				$status='completed';
			}
            $row = array();
            $row[] = $aRow['booking_no'];
            $row[] = $commonService->humanDateFormat($expDate[0]);
            $row[] = substr($expDate[1],0,-3);
            $row[] = ucwords($aRow['guest_name']);
            $row[] = $aRow['room_no'];
            $row[] = $aRow['type_name'];
            $row[] = $aRow['vehicle_no'];
            $row[] = ucfirst($aRow['driver_name']);
            
			if($update){
				$row[] = '<a href="./edit/' . base64_encode($aRow['booking_id']) . '" class=" " title="Edit"><i class="fa fa-pencil-square-o"></i></a>';
			}
            if($completeAction){
				$row[] ='<a href="javascript:void(0);" class="" title="Complete Booking" onclick="completeBooking('.$aRow['booking_id'].',\''.$aRow['booking_no'].'\',\''.$status.'\')"><i class="fa fa-check-square-o"></i></a>';;
			}
            
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchAllFpsHotelBookingReports($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
		$queryContainer = new Container('query');
		$commonService=new CommonService();
        $aColumns = array('DATE_FORMAT(booking_date_time,"%d-%b-%Y")','trip_sheet_no','guest_name','room_no','vehicle_no','driver_name','type_name','package');
        $orderColumns = array('booking_date_time','trip_sheet_no', 'guest_name','type_name','driver_name','vehicle_no','package');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
         * Ordering
         */

        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < intval($parameters['iSortingCols']); $i++) {
                if ($parameters['bSortable_' . intval($parameters['iSortCol_' . $i])] == "true") {
					if($orderColumns[intval($parameters['iSortCol_' . $i])]=='trip_from_date'){
						$sOrder = array('trip_from_date '.$parameters['sSortDir_' . $i]. ",",'trip_start_time '.$parameters['sSortDir_' . $i]. ",");
					}else{
						$sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
					}
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = "";
        if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
            $searchArray = explode(" ", $parameters['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);

                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                }
            }
        }

        /*
         * SQL queries
         * Get data to display
         */
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$sQuery = $sql->select()->from(array('hb'=>'hotel_bookings'))
					->join(array('rt' => 'rental_type'), "rt.type_id=hb.booking_type", array('type_name'))
					->where(array('client_id'=>'61',"hb.status!='pending'"));
					
		if(isset($parameters['fromDate']) && trim($parameters['fromDate'])!="" && trim($parameters['toDate'])!=""){
			$startDate=$commonService->dateFormat(trim($parameters['fromDate']));
			$endDate=$commonService->dateFormat(trim($parameters['toDate']));
			$sQuery->where("(DATE(booking_date_time)>='".$startDate."' AND DATE(booking_date_time)<='".$endDate."')");
		}
		if(isset($parameters['vehicleType']) && trim($parameters['vehicleType'])!=""){
			$sQuery->where(array('hb.vehicle_type'=>$parameters['vehicleType']));
		}
		
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $sQuery->reset('order');
		$queryContainer->exportQuery = $sQuery;
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select(array('client_id'=>'61',"status!='pending'"))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\FpsHotelBooking', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        foreach ($rResult as $aRow) {
			$expDate=explode(" ",$aRow['booking_date_time']);
			$edit="";
			$complete="";
			$cancel="";
			if($aRow['package']=='non_revenue'){
				$aRow['package']="Non Revenue";
			}
			if($aRow['booking_given_by']=='ays'){
				$aRow['booking_given_by']='AYS';
			}
			else if($aRow['booking_given_by']=='front_office'){
				$aRow['booking_given_by']='Front Office';
			}else if($aRow['booking_given_by']=='hotel'){
				$aRow['booking_given_by']='Hotel';
			}
			else if($aRow['booking_given_by']=='self'){
				$aRow['booking_given_by']='Self';
			}
			else if($aRow['booking_given_by']=='travel_desk'){
				$aRow['booking_given_by']='Travel Desk';
			}
            $row = array();
            $row[] = $commonService->humanDateFormat($expDate[0]);
			$row[] = $aRow['trip_sheet_no'];
            $row[] = ucwords($aRow['guest_name']);
			$row[] = $aRow['type_name'];
			$row[] = ucfirst($aRow['booking_given_by']);
			$row[] = $aRow['driver_name'];
            $row[] = $aRow['vehicle_no'];
            $row[] = ucfirst($aRow['package']);
            
            if($update){
				$row[] = '<a href="../edit/' . base64_encode($aRow['booking_id']).'" class=" " title="Edit"><i class="fa fa-pencil-square-o"></i></a>';
			}
            
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function addCymHotelBookingDetails($params) {
		//\Zend\Debug\Debug::dump($params);die;
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        $result = "";
        if (trim($params['bookingRef']) != "") {
			$expBook=explode($params['bookingCode'],$params['bookingRef']);
			$sortKey=$expBook[1];
            $data = array(
                'client_id' => '6',
                'booking_code' => $params['bookingCode'],
                'sort_key' => $sortKey,
                'booking_no' => $params['bookingRef'],
                'booking_date_time' => $commonService->dateFormat($params['bookingDate'])." ".$params['bookingTime'],
                'guest_name' => $params['guestName'],
                'room_no' => $params['roomNo'],
                'booking_given_by' => $params['bookingGivenBy'],
                'booking_type' => $params['dutyType'],
                'vehicle_type' => $params['vehicleType'],
                'vehicle_mode' => $params['vehicleMode'],
                'vehicle_no' => $params['vehicle'],
                'driver_name' => $params['driverName'],
				'trip_time' => $params['bookingTime'],
                'end_time' => $params['endTime'],
                'total_hrs' => $params['totalHrs'],
                'start_kms' => $params['startKms'],
                'close_kms' => $params['endKms'],
                'total_kms' => $params['totalKms'],
                'parking' => $params['parking'],
                'toll' => $params['toll'],
                'permit' => $params['permit'],
                'package' => $params['package'],
                'driver_allowance_day' => $params['driverAllowancePerDay'],
                'driver_allowance_night' => $params['driverAllowancePerNight'],
                'total_amount' => $params['totalAmount'],
                'tariff_name' => $params['tariff'],
                'status' => 'completed',
                'sevice_tax_type' => 'cgst',
                'sgst_tax' => '2.5',
                'cgst_tax' => '2.5',
                'service_tax_amt' => $params['serviceTaxAmt'],
                'created_by' => $logincontainer->employeeId,
                'created_on' => $commonService->getDateTime()
            );

			if(isset($params['vehicleId']) && trim($params['vehicleId'])>0){
				$data['vehicle_id']=$params['vehicleId'];
			}
			if(isset($params['driverId']) && trim($params['driverId'])>0){
				$data['driver_id']=$params['driverId'];
            }
            
            if(isset($params['bcRevenue']) && trim($params['bcRevenue'])!=''){
                $data['bc_revenue']=$params['bcRevenue'];
            }

            if(isset($params['vendorRevenue']) && trim($params['vendorRevenue'])>0){
                $data['vendor_bill_amount']=$params['vendorRevenue'];
            }
            
            if(isset($params['tariff']) && trim($params['tariff'])!=""){
                $data['tariff_name']=$params['tariff'];
            }
            if(isset($params['tariffAmount']) && trim($params['tariffAmount'])!=""){
                $data['tariff_amount']=$params['tariffAmount'];
            }
            if(isset($params['extraKms']) && trim($params['extraKms'])!=""){
                $data['extra_kms']=$params['extraKms'];
            }
            if(isset($params['extraKmsAmt']) && trim($params['extraKmsAmt'])!=""){
                $data['extra_kms_amt']=$params['extraKmsAmt'];
            }
            if(isset($params['extraHrs']) && trim($params['extraHrs'])!=""){
                $data['extra_hrs']=$params['extraHrs'];
            }
            if(isset($params['extraHrsAmt']) && trim($params['extraHrsAmt'])!=""){
                $data['extra_hrs_amt']=$params['extraHrsAmt'];
            }
            if(isset($params['extAmtPerHrs']) && trim($params['extAmtPerHrs'])!=""){
                $data['ext_amt_per_hrs']=$params['extAmtPerHrs'];
            }
            if(isset($params['extAmtPerKms']) && trim($params['extAmtPerKms'])!=""){
                $data['ext_amt_per_kms']=$params['extAmtPerKms'];
            }
            //\Zend\Debug\Debug::dump($data);die;
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            //event log
			$subject = $lastInsertedId;
			$eventType = 'add-cym-hotel-booking';
			$action = 'added a new cym hotel booking with the ref '.ucwords($params['bookingRef']);
			$resourceName = 'Cym-Hotel-Booking';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
        }
        return $lastInsertedId;
    }

    public function updateCymHotelBookingDetails($params) {
        //\Zend\Debug\Debug::dump($params);die;
        if (trim($params['bookingId'])!="" && trim($params['bookingRef']) != "") {
            $bookingId=base64_decode($params['bookingId']);
            $commonService=new CommonService();
            $logincontainer = new Container('credo');
            if (trim($params['bookingRef']) != "") {
                $data = array(
                    //'client_id' => '6',
                    //'booking_no' => $params['bookingRef'],
                    'booking_date_time' => $commonService->dateFormat($params['bookingDate'])." ".$params['bookingTime'],
                    'guest_name' => $params['guestName'],
                    'room_no' => $params['roomNo'],
                    'booking_given_by' => $params['bookingGivenBy'],
                    'booking_type' => $params['dutyType'],
                    'vehicle_type' => $params['vehicleType'],
                    'vehicle_mode' => $params['vehicleMode'],
                    'vehicle_no' => $params['vehicle'],
                    'driver_name' => $params['driverName'],
                    'trip_time' => $params['bookingTime'],
                    'end_time' => $params['endTime'],
                    'total_hrs' => $params['totalHrs'],
                    'start_kms' => $params['startKms'],
                    'close_kms' => $params['endKms'],
                    'total_kms' => $params['totalKms'],
                    'parking' => $params['parking'],
                    'toll' => $params['toll'],
                    'permit' => $params['permit'],
                    'package' => $params['package'],
                    'driver_allowance_day' => $params['driverAllowancePerDay'],
                    'driver_allowance_night' => $params['driverAllowancePerNight'],
                    'total_amount' => $params['totalAmount'],
                    'tariff_name' => $params['tariff'],
                    'status' => 'completed',
                    'sevice_tax_type' => 'cgst',
                    'sgst_tax' => '2.5',
                    'cgst_tax' => '2.5',
                    'service_tax_amt' => $params['serviceTaxAmt'],
                    'created_by' => $logincontainer->employeeId,
                    //'created_on' => $commonService->getDateTime()
                );

                $data['ext_amt_per_kms']=NULL;
                $data['vehicle_id']=NULL;
			    $data['driver_id']=NULL;
			    $data['tariff_amount']=NULL;
			    $data['vendor_bill_amount']=NULL;
			    $data['bc_revenue']=NULL;
                $data['extra_hrs']=NULL;
                $data['extra_hrs_amt']=NULL;
                $data['ext_amt_per_hrs']=NULL;
                $data['ext_amt_per_kms']=NULL;
                $data['extra_kms']=NULL;
                $data['extra_kms_amt']=NULL;
                $data['tariff_name']=NULL;
            
                if(isset($params['vehicleId']) && trim($params['vehicleId'])>0){
                    $data['vehicle_id']=$params['vehicleId'];
                }
                if(isset($params['driverId']) && trim($params['driverId'])>0){
                    $data['driver_id']=$params['driverId'];
                }
                if(isset($params['bcRevenue']) && trim($params['bcRevenue'])!=''){
                    $data['bc_revenue']=$params['bcRevenue'];
                }
                if(isset($params['vendorRevenue']) && trim($params['vendorRevenue'])>0){
                    $data['vendor_bill_amount']=$params['vendorRevenue'];
                }
                
                if(isset($params['tariff']) && trim($params['tariff'])!=""){
                    $data['tariff_name']=$params['tariff'];
                }
                if(isset($params['tariffAmount']) && trim($params['tariffAmount'])!=""){
                    $data['tariff_amount']=$params['tariffAmount'];
                }
                if(isset($params['extraKms']) && trim($params['extraKms'])!=""){
                    $data['extra_kms']=$params['extraKms'];
                }
                if(isset($params['extraKmsAmt']) && trim($params['extraKmsAmt'])!=""){
                    $data['extra_kms_amt']=$params['extraKmsAmt'];
                }
                if(isset($params['extraHrs']) && trim($params['extraHrs'])!=""){
                    $data['extra_hrs']=$params['extraHrs'];
                }
                if(isset($params['extraHrsAmt']) && trim($params['extraHrsAmt'])!=""){
                    $data['extra_hrs_amt']=$params['extraHrsAmt'];
                }
                if(isset($params['extAmtPerHrs']) && trim($params['extAmtPerHrs'])!=""){
                    $data['ext_amt_per_hrs']=$params['extAmtPerHrs'];
                }
                if(isset($params['extAmtPerKms']) && trim($params['extAmtPerKms'])!=""){
                    $data['ext_amt_per_kms']=$params['extAmtPerKms'];
                }
                //\Zend\Debug\Debug::dump($data);die;
                $this->update($data, array('booking_id' => $bookingId));
                
                //event log
                $subject = $bookingId;
                $eventType = 'update-cym-hotel-booking';
                $action = 'updated a new cym hotel booking with the ref '.ucwords($params['bookingRef']);
                $resourceName = 'Cym-Hotel-Booking';
                $eventLogDb = new EventLogTable($this->adapter);
                $eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
                return $bookingId;
            }
        }
    }

    public function fetchAllCymHotelBookingReports($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
         */
		$queryContainer = new Container('query');
		$commonService=new CommonService();
        $aColumns = array('DATE_FORMAT(booking_date_time,"%d-%b-%Y")','booking_no', 'guest_name','room_no','vehicle_no','driver_name','type_name','package');
        $orderColumns = array('booking_date_time','sort_key', 'guest_name','type_name','driver_name','vehicle_no','package');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($parameters['iDisplayStart']) && $parameters['iDisplayLength'] != '-1') {
            $sOffset = $parameters['iDisplayStart'];
            $sLimit = $parameters['iDisplayLength'];
        }

        /*
         * Ordering
         */

        $sOrder = "";
        if (isset($parameters['iSortCol_0'])) {
            for ($i = 0; $i < intval($parameters['iSortingCols']); $i++) {
                if ($parameters['bSortable_' . intval($parameters['iSortCol_' . $i])] == "true") {
					if($orderColumns[intval($parameters['iSortCol_' . $i])]=='trip_from_date'){
						$sOrder = array('trip_from_date '.$parameters['sSortDir_' . $i]. ",",'trip_start_time '.$parameters['sSortDir_' . $i]. ",");
					}else{
						$sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
					}
                }
            }
            $sOrder = substr_replace($sOrder, "", -1);
        }

        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */

        $sWhere = "";
        if (isset($parameters['sSearch']) && $parameters['sSearch'] != "") {
            $searchArray = explode(" ", $parameters['sSearch']);
            $sWhereSub = "";
            foreach ($searchArray as $search) {
                if ($sWhereSub == "") {
                    $sWhereSub .= "(";
                } else {
                    $sWhereSub .= " AND (";
                }
                $colSize = count($aColumns);

                for ($i = 0; $i < $colSize; $i++) {
                    if ($i < $colSize - 1) {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' OR ";
                    } else {
                        $sWhereSub .= $aColumns[$i] . " LIKE '%" . ($search ) . "%' ";
                    }
                }
                $sWhereSub .= ")";
            }
            $sWhere .= $sWhereSub;
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($parameters['bSearchable_' . $i]) && $parameters['bSearchable_' . $i] == "true" && $parameters['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere .= $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                } else {
                    $sWhere .= " AND " . $aColumns[$i] . " LIKE '%" . ($parameters['sSearch_' . $i]) . "%' ";
                }
            }
        }

        /*
         * SQL queries
         * Get data to display
         */
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$sQuery = $sql->select()->from(array('hb'=>'hotel_bookings'))
					->join(array('rt' => 'rental_type'), "rt.type_id=hb.booking_type", array('type_name'))
					->where(array('client_id'=>'6'));
					
		if(isset($parameters['fromDate']) && trim($parameters['fromDate'])!="" && trim($parameters['toDate'])!=""){
			$startDate=$commonService->dateFormat(trim($parameters['fromDate']));
			$endDate=$commonService->dateFormat(trim($parameters['toDate']));
			$sQuery->where("(DATE(booking_date_time)>='".$startDate."' AND DATE(booking_date_time)<='".$endDate."')");
		}
		if(isset($parameters['vehicleType']) && trim($parameters['vehicleType'])!=""){
			$sQuery->where(array('hb.vehicle_type'=>$parameters['vehicleType']));
		}
		
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }

        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //echo $sQueryStr;die;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $sQuery->reset('order');
		$queryContainer->cymExportQuery = $sQuery;
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select(array('client_id'=>'6'))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\FpsHotelBooking', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
        foreach ($rResult as $aRow) {
			$expDate=explode(" ",$aRow['booking_date_time']);
			$edit="";
			$complete="";
			$cancel="";
			if($aRow['package']=='non_revenue'){
				$aRow['package']="Non Revenue";
			}
			
            $row = array();
            $row[] = $commonService->humanDateFormat($expDate[0]);
			$row[] = $aRow['booking_no'];
            $row[] = ucwords($aRow['guest_name']);
			$row[] = $aRow['type_name'];
			$row[] = ucfirst($aRow['driver_name']);
			$row[] = $aRow['vehicle_no'];
            $row[] = ucfirst($aRow['package']);
            
            if($update){
				$row[] = '<a href="./edit/' . base64_encode($aRow['booking_id']).'" class=" " title="Edit"><i class="fa fa-pencil-square-o"></i></a>';
			}
            
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function fetchCymHotelBooking($bookingId) {
        $row = $this->select(array('booking_id' => (int) $bookingId))->current();
        return $row;
    }
}
?>
