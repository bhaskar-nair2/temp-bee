<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\ClientContactTable;
use Application\Model\GuestTable;
use Application\Model\TripDriverMapTable;
use Application\Model\TempMailTable;
use Application\Model\BookingVendorPaymentsTable;

class BookingsTable extends AbstractTableGateway {

    protected $table = 'bookings';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addBookingDetails($params) {
		//\Zend\Debug\Debug::dump($params);die;
        $result = "";
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
        if (trim($params['company']) != "") {
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$clientContDb = new ClientContactTable($dbAdapter);
			$guestDb = new GuestTable($dbAdapter);
			
			
            $data = array(
				'booking_no'=>$params['bookingRef'],
                'company_id' => $params['company'],
                'booking_type' => $params['businessUnit'],
                'city' => $params['city'],
                'make_type' => $params['vehicleCategory'],
                'vehicle_type' => base64_decode($params['vehicleType']),
                'duty_type' => $params['dutyType'],
                //'tariff' => base64_decode($params['tariff']),
                'booking_status' => 'pending',
                //'booker_id' => $bookerId,
                'booker_name' => $params['bookerName'],
                'booker_mobile_no' => $params['bookerMobileNo'],
                'booker_email' => $params['bookerMailId'],
                //'guest_id' => $guestId,
                'guest_title' => $params['guestTitle'],
                'guest_name' => $params['customerName'],
                'guest_mobile_no' => $params['customerMobileNo'],
                'trip_start_time' => $params['tripTime'],
                'spec_ins' => $params['specialIns'],
                //'service_tax_type' => $params['serviceTaxType'],
                //'sgst_tax' => $params['sgstTax'],
                //'cgst_tax' => $params['cgstTax'],
                //'igst_tax' => $params['igstTax'],
                'pickup_area' => $params['pickupArea'],
                'pickup_address' => $params['pickupAddress'],
                'mop' => base64_decode($params['paymentMode']),
                'payment_status' => $params['paymentStatus'],
                'booking_date'=>$commonService->getDate(),
                'created_on'=>$commonService->getDateTime(),
                'created_by' => $logincontainer->employeeId
            );
			
			if(isset($params['client']) && trim($params['client'])!="" && $params['businessUnit']=='2'){
				$clientId=$params['client'];
				$guestId=(int) $params['customerId'];
				$bookerId=(int) $params['bookerId'];
				if($guestId>0){
					$guestId=$guestDb->getGuestIdByName($clientId,trim($params['customerName']),trim($params['customerMobileNo']),$guestId);	
				}else{
					$guestId=$guestDb->getGuestIdByName($clientId,trim($params['customerName']),trim($params['customerMobileNo']));
				}
				if($bookerId>0){
					$bookerId=$clientContDb->getClientContactIdByName($clientId,trim($params['bookerName']),trim($params['bookerMobileNo']),trim($params['bookerMailId']),$bookerId);
				}else{
					$bookerId=$clientContDb->getClientContactIdByName($clientId,trim($params['bookerName']),trim($params['bookerMobileNo']),trim($params['bookerMailId']));
				}
			
				$data['booker_id']=$bookerId;
				$data['guest_id']=$guestId;
				$data['client_id']=$params['client'];
			}
			
			if(isset($params['tripDate']) && trim($params['tripDate'])!=""){
				$data['trip_from_date']=$commonService->dateFormat($params['tripDate']);
			}
			if($params['businessUnit']==2){
				if(isset($params['bookingCode']) && trim($params['bookingCode'])!=""){
					$data['booking_code']=trim($params['bookingCode']);
				}
				if(isset($params['bookingSortNo']) && trim($params['bookingSortNo'])!=""){
					$data['booking_sort_key']=trim($params['bookingSortNo']);
				}	
			}
			
			if(isset($params['paidDate']) && trim($params['paidDate'])!=""){
				$data['paid_date']=$commonService->dateFormat($params['paidDate']);
			}
			
            $result = $this->insert($data);
            $bookingId = $this->lastInsertValue;
			if(isset($params['sendEmail'])){
				$this->sendConfimationBookingEmail($bookingId);
			}
        }
        return $result;
    }
	
	public function updateBookingDetails($params) {
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$clientContDb = new ClientContactTable($dbAdapter);
		$guestDb = new GuestTable($dbAdapter);
		$clientId=$params['client'];
		
        if (trim($params['bookingId'])!="" && trim($params['company']) != "") {
			$bookingId=base64_decode($params['bookingId']);
            $data = array(
				'booking_no'=>$params['bookingRef'],
                'company_id' => $params['company'],
                //'client_id' => $params['client'],
                'booking_type' => $params['businessUnit'],
                'city' => $params['city'],
                'make_type' => $params['vehicleCategory'],
				'vehicle_type' => base64_decode($params['vehicleType']),
				'duty_type' => $params['dutyType'],
				//'tariff' => base64_decode($params['tariff']),
                'booking_status' => 'pending',
				//'booker_id' => $bookerId,
                'booker_name' => $params['bookerName'],
                'booker_mobile_no' => $params['bookerMobileNo'],
                'booker_email' => $params['bookerMailId'],
                //'guest_id' => $guestId,
				'guest_title' => $params['guestTitle'],
				'guest_name' => $params['customerName'],
                'guest_mobile_no' => $params['customerMobileNo'],
                'trip_start_time' => $params['tripTime'],
                'spec_ins' => $params['specialIns'],
				//'service_tax_type' => $params['serviceTaxType'],
                //'sgst_tax' => $params['sgstTax'],
                //'cgst_tax' => $params['cgstTax'],
                //'igst_tax' => $params['igstTax'],
				'pickup_area' => $params['pickupArea'],
                'pickup_address' => $params['pickupAddress'],
                'mop' => base64_decode($params['paymentMode']),
                'payment_status' => $params['paymentStatus']
            );
			
			$data['trip_from_date']=NULL;
            $data['booking_code']=NULL;
            $data['booking_sort_key']=NULL;
            $data['guest_id']=NULL;
            $data['booker_id']=NULL;
            $data['client_id']=NULL;
            $data['paid_date']=NULL;
			
			if(isset($params['client']) && trim($params['client'])!="" && $params['businessUnit']=='2'){
				$guestId=(int) $params['customerId'];
				$bookerId=(int) $params['bookerId'];
				if($guestId>0){
					$guestId=$guestDb->getGuestIdByName($clientId,trim($params['customerName']),trim($params['customerMobileNo']),$guestId);	
				}else{
					$guestId=$guestDb->getGuestIdByName($clientId,trim($params['customerName']),trim($params['customerMobileNo']));
				}
				if($bookerId>0){
					$bookerId=$clientContDb->getClientContactIdByName($clientId,trim($params['bookerName']),trim($params['bookerMobileNo']),trim($params['bookerMailId']),$bookerId);
				}else{
					$bookerId=$clientContDb->getClientContactIdByName($clientId,trim($params['bookerName']),trim($params['bookerMobileNo']),trim($params['bookerMailId']));
				}
				
				$data['booker_id']=$bookerId;
				$data['guest_id']=$guestId;
				$data['client_id']=$params['client'];
			}
			
			
			if(isset($params['tripDate']) && trim($params['tripDate'])!=""){
				$data['trip_from_date']=$commonService->dateFormat($params['tripDate']);
			}
			if(isset($params['bookingCode']) && trim($params['bookingCode'])!=""){
				$data['booking_code']=trim($params['bookingCode']);
			}
			if(isset($params['bookingSortNo']) && trim($params['bookingSortNo'])!=""){
				$data['booking_sort_key']=trim($params['bookingSortNo']);
			}
			if(isset($params['paidDate']) && trim($params['paidDate'])!=""){
				$data['paid_date']=$commonService->dateFormat($params['paidDate']);
			}
			//\Zend\Debug\Debug::dump($data);die;
            $this->update($data, array('booking_id' => $bookingId));
            if(isset($params['sendEmail'])){
			$this->sendConfimationBookingEmail($bookingId);
			}
			return $bookingId;
        }
    }
	
	public function executeBookingDetails($params) {
		//\Zend\Debug\Debug::dump($params);die;
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		
        if (trim($params['bookingId'])!="") {
			$bookingId=base64_decode($params['bookingId']);
            $data = array(
                //'make_type' => $params['vehicleCategory'],
                'spec_ins' => $params['specialIns'],
                'vendor_id' => $params['vendor'],
                'booking_status' => 'assigned'
            );
			
			$tripDriverMapDb = new TripDriverMapTable($dbAdapter);
			
			$tripData = array(
                'booking_id' => $bookingId,
                'driver_name' => $params['driverName'],
                'driver_mobile_no' => $params['driverMobileNo'],
				'provided_make_type' => $params['vehicleCategory'],
                'vehicle_no' => $params['vehicleNo'],
                'vehicle_type' => base64_decode($params['vehicleType'])
            );
			//Check trip already assigned
			$tQuery=$sql->select()->from('trip_driver_map')->where(array('booking_id'=>$bookingId));
			$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
			$result = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($result!=""){
				$tripDriverMapDb->update($tripData, array('booking_id' => $bookingId));
			}else{
				$tripDriverMapDb->insert($tripData);
			}
			
            $this->update($data, array('booking_id' => $bookingId));
			
			if(isset($params['sendSms'])){
				//$this->sendAssignedBookingEmail($bookingId);
				$this->sendBookingSms($bookingId);
			}
			return $bookingId;
        }
    }
	
	public function closeBookingDetails($params) {
		//\Zend\Debug\Debug::dump($params);die;
		$commonService=new CommonService();
		$logincontainer = new Container('credo');
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		
        if (trim($params['bookingId'])!="" && trim($params['startDate'])!="") {
			$bookingId=base64_decode($params['bookingId']);
            $data = array(
                'make_type' => $params['vehicleCategory'],
                'duty_type' => $params['dutyType'],
                'tariff' => $params['tariff'],
                'tariff_text' => $params['tariffText'],
                'trip_start_time' => $params['startTime'],
                'trip_close_time' => $params['endTime'],
                'total_hrs' => $params['totalHrs'],
                'total_days' => $params['totalDays'],
                'extra_hrs' => $params['extraHrs'],
                'start_kms' => $params['startKms'],
                'close_kms' => $params['closeKms'],
                'total_kms' => $params['totalKms'],
                'extra_kms' => $params['extraKms'],
                'ext_kms_amount' => $params['extraKmsAmt'],
                'ext_hrs_amount' => $params['extraHrsAmt'],
                'ext_amt_per_hrs' => $params['extAmtPerHrs'],
                'ext_amt_per_km' => $params['extAmtPerKms'],
                'trip_from_date' => $commonService->dateFormat($params['startDate']),
                'trip_to_date' => $commonService->dateFormat($params['endDate']),
                'tariff_amount' => $params['tariffAmount'],
                'day_time_driver_allowance' => $params['dayTimeDriverAllowance'],
                'night_time_driver_allowance' => $params['nightTimeDriverAllowance'],
                'driver_allowance_per_day' => $params['driverAllowancePerDay'],
                'driver_allowance_per_night' => $params['driverAllowancePerNight'],
                'driver_allowance' => $params['driverAllowance'],
                'parking_toll' => $params['parkingToll'],
                'permit' => $params['permitAmt'],
                //'service_tax_amt' => $params['serviceTaxAmt'],
                'sub_total_amount' => $params['subTotalAmount'],
                'total_amount' => $params['totalAmount'],
                'advance' => $params['advance'],
                'balance' => $params['balance'],
                'payment_status' => $params['paymentStatus'],
                'booking_status' => 'close',
                'closed_by' => $logincontainer->employeeId,
            );
			
			$data['paid_date']=NULL;
			$data['sgst_tax']=NULL;
			$data['cgst_tax']=NULL;
			$data['igst_tax']=NULL;
			$data['service_tax_paid_by_client']=NULL;
			$data['service_tax_type']=NULL;
			$data['service_tax_amt']=NULL;
			
			if(isset($params['serviceTaxPaidByClient']) && $params['serviceTaxPaidByClient']=='yes'){
				$data['service_tax_paid_by_client']=$params['serviceTaxPaidByClient'];
				$data['service_tax_type']=$params['serviceTaxType'];
				$data['service_tax_amt']=$params['serviceTaxAmt'];
				if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='sgst'){
					$data['sgst_tax']=$params['sgstTax'];
					$data['cgst_tax']=$params['cgstTax'];
				}
				else if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='igst'){
					$data['igst_tax']=$params['igstTax'];;
				}
			}
			
			if(isset($params['paidDate']) && trim($params['paidDate'])!=""){
				$data['paid_date']=$commonService->dateFormat($params['paidDate']);
			}
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "booking") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "booking")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "booking");
            }
			
			//Delete upload file
			if(isset($params['deletedTripSheetFile']) && trim($params['deletedTripSheetFile'])!=""){
				$filePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "booking" . DIRECTORY_SEPARATOR . $bookingId. DIRECTORY_SEPARATOR.$params['deletedTripSheetFile'];
				if(file_exists($filePath)){
					unlink($filePath);
					$this->update(array('trip_sheet'=>NULL), array("booking_id" => $bookingId));
				}
			}
			
			if (isset($_FILES['tripSheet']['name']) && trim($_FILES['tripSheet']['name'])!= '') {
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "booking" . DIRECTORY_SEPARATOR . $bookingId;
				if (!file_exists($pathname) && !is_dir($pathname)) {
					mkdir($pathname);
				}
				
				if (move_uploaded_file($_FILES["tripSheet"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$_FILES['tripSheet']['name'])) {
					$fileData = array('trip_sheet' => $_FILES['tripSheet']['name']);
					$this->update($fileData, array("booking_id" => $bookingId));
				}
            }
			
            $this->update($data, array('booking_id' => $bookingId));
			return $bookingId;
        }
    }
	
    public function fetchAllPendingBookings($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$commonService=new CommonService();
        $aColumns = array('booking_no','DATE_FORMAT(trip_from_date,"%d-%b-%Y")','trip_start_time','type_name','city_name','client_name','vmt.make_type','guest_name','pickup_address');
        $orderColumns = array('booking_no','trip_from_date','trip_start_time','type_name','city_name','client_name','vmt.make_type','guest_name','pickup_address');

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
        $sQuery = $sql->select()->from(array('b'=>'bookings'))
						->columns(array('booking_id','booking_no','trip_from_date','trip_start_time','booking_status','guest_name','pickup_address'))
						->join(array('ci' => 'city_details'), "ci.city_id=b.city", array('city_name'))
						->join(array('cd' => 'company_details'), "cd.company_id=b.company_id", array('company_name'))
						->join(array('c' => 'clients'), "c.client_id=b.client_id", array('client_name'),'left')
						//->join(array('gd' => 'guest_details'),"gd.guest_id=b.guest_id", array('guest_name'))
						->join(array('vmt' => 'vahicle_make_type'),"vmt.make_id=b.make_type", array('makeType'=>'make_type'))
						->join(array('rt' => 'rental_type'),"rt.type_id=b.duty_type", array('dutyType'=>'type_name'))
						->where(array('booking_status'=>'pending'));
					
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		$iQuery = $sql->select()->from('bookings')->where(array('booking_status'=>'pending'));
		
		if(isset($parameters['fromTripDate']) && trim($parameters['fromTripDate'])!="" && trim($parameters['closeTripDate'])!=""){
			$startDate=$commonService->dateFormat(trim($parameters['fromTripDate']));
			$endDate=$commonService->dateFormat(trim($parameters['closeTripDate']));
			$sQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
			$iQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
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
        //$iTotal = $this->select(array('booking_status'=>'pending'))->count();
		
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$iTotal=count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $sessionLogin = new Container('credo');
		$role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
		
		if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'execute')) {
            $executeAction = true;
        } else {
            $executeAction = false;
        }
		
		if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'cancel')) {
            $cancelAction = true;
        } else {
            $cancelAction = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'delete')) {
            $deleteAction = true;
        } else {
            $deleteAction = false;
        }
        
        foreach ($rResult as $aRow) {
			$edit="";
			$execute="";
			$cancelBooking="";
			$deleteBooking="";
            $row = array();
            $row[] = $aRow['booking_no'];
            $row[] = $commonService->humanDateFormat($aRow['trip_from_date']);
            $row[] = $aRow['trip_start_time'];
            $row[] = ucwords($aRow['dutyType']);
            $row[] = ucwords($aRow['city_name']);
			$row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['makeType'];
            $row[] = ucwords($aRow['guest_name']);
            $row[] = $aRow['pickup_address'];
            if($update){
            $edit = '<a href="../edit/' . base64_encode($aRow['booking_id']) . '" title="Edit"><i class="fa fa-pencil"> </i></a>';
            }
			if($executeAction){
			$execute = '<a href="../execute/' . base64_encode($aRow['booking_id']) . '" title="Execute Booking"><i class="fa fa-book"> </i></a>';	
			}
			if($cancelAction){
				$cancelBooking = '<a href="javascript:void(0)" onClick="showModal(\'../cancel/'.base64_encode($aRow['booking_id']). '\',\'600\',\'400\')" title="Cancel Booking"><i class="fa fa-times"> </i></a>';
			}
			if($deleteAction){
			$deleteBooking = '<a href="javascript:void(0)" onClick="deleteBooking(\''.base64_encode($aRow['booking_id']).'\')" title="Delete Booking"><i class="fa fa-trash-o"></i></a>';
			}
			$row[]=$edit.$execute.$cancelBooking.$deleteBooking;
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function checkBookingCode($bookCode){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from('bookings')->where(array('booking_code'=>$bookCode))->order("booking_id DESC");
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($result!=""){
			$bookSortNo=$result['booking_sort_key']+1;
			$bookNo=$this->checkBookingNo($bookCode,$bookSortNo);
		}else{
			$bookSortNo='001';
			$bookNo=$this->checkBookingNo($bookCode,$bookSortNo);
		}
		return $bookNo;
	}
	
	public function checkBookingNo($bookCode,$bookSortNo){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		
		$idSize = strlen($bookSortNo."");
		if ($idSize < 4) {
			if ($idSize == 1) {
			$bookSortNo = "00" . $bookSortNo;
			} else if ($idSize == 2) {
			$bookSortNo = "0" . $bookSortNo;
			} else {
				$bookSortNo;
			}
		}
		$bookNo=$bookCode.$bookSortNo;
		$query = $sql->select()->from('bookings')->where(array('booking_no'=>$bookNo));
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $chkBookRes = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($chkBookRes!=""){
			$bookSortNo=$chkBookRes['booking_sort_key']+1;
			return $this->checkBookingNo($bookCode,$bookSortNo);
		}else{
			return $bookSortNo;
		}
	}
    
	public function getBookingDetails($bookingId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('b'=>'bookings'))
                ->columns(array('booking_id','booking_sort_key','booking_code','booking_no','company_id','client_id','booking_type','booking_date','trip_from_date','make_type','vehicle_type','city','duty_type','tariff','booking_status','guest_id','guest_name','guest_mobile_no','booker_id','booker_name','booker_mobile_no','booker_email','trip_start_time','spec_ins','pickup_area','pickup_address','mop','service_tax_type','sgst_tax','cgst_tax','igst_tax','paid_date','payment_status'))
				//->join(array('gd' => 'guest_details'), "gd.guest_id=b.guest_id", array('guest_name','guestMobile'=>'mobile_no','email'),'left')
				//->join(array('vc' => 'vendor_contacts'), "vc.contact_id=b.vendor_contact_id", array('contact_name','vendorContactMobile'=>'mobile_no'),'left')
				->where(array('booking_id'=> (int) $bookingId));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
	}
	
	public function fetchCorporateBookingNo(){
		$currentYear=date('y');
		$bookCode="BK".$currentYear;
		$bookSortNo=$this->checkBookingCode($bookCode);
		//$bookNo=$bookCode.$bookSortNo;
		return array('bookingCode'=>$bookCode,'bookingSortNo'=>$bookSortNo);
	}
	
	public function fetchAllExecutedBookings($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$commonService=new CommonService();
        $aColumns = array('booking_no','DATE_FORMAT(trip_from_date,"%d-%b-%Y")','trip_start_time','type_name','city_name','client_name','vmt.make_type','guest_name','pickup_address','driver_name','driver_mobile_no','vehicle_no','vendor_name');
        $orderColumns = array('booking_no','trip_from_date','trip_start_time','type_name','city_name','client_name','vmt.make_type','guest_name','pickup_address','driver_name','driver_mobile_no','vehicle_no','vendor_name');

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
        $sQuery = $sql->select()->from(array('b'=>'bookings'))
						->columns(array('booking_id','booking_no','trip_from_date','trip_start_time','guest_title','guest_name','pickup_address','booking_status'))
						->join(array('ci' => 'city_details'), "ci.city_id=b.city", array('city_name'))
						->join(array('cd' => 'company_details'), "cd.company_id=b.company_id", array('company_name'))
						->join(array('c' => 'clients'), "c.client_id=b.client_id", array('client_name'),'left')
						//->join(array('gd' => 'guest_details'),"gd.guest_id=b.guest_id", array('guest_name'))
						->join(array('vmt' => 'vahicle_make_type'),"vmt.make_id=b.make_type", array('makeType'=>'make_type'))
						->join(array('rt' => 'rental_type'),"rt.type_id=b.duty_type", array('dutyType'=>'type_name'))
						->join(array('tdm' => 'trip_driver_map'),"tdm.booking_id=b.booking_id", array('map_id','driver_name','driver_mobile_no','vehicle_no'))
						//->join(array('emp' => 'employee_details'),"emp.employee_id=tdm.driver_id", array('employee_name','mobile_no'))
						//->join(array('vd' => 'vehicle_details'),"vd.vehicle_id=tdm.vehicle_id", array('vehicle_no'))
						->join(array('v' => 'vendors'),"v.vendor_id=b.vendor_id", array('vendor_name','vendor_no'),"left")
						->where(array('booking_status'=>'assigned'));
					
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		$iQuery = $sql->select()->from('bookings')->where(array('booking_status'=>'assigned'));
		
		if(isset($parameters['fromTripDate']) && trim($parameters['fromTripDate'])!="" && trim($parameters['closeTripDate'])!=""){
			$startDate=$commonService->dateFormat(trim($parameters['fromTripDate']));
			$endDate=$commonService->dateFormat(trim($parameters['closeTripDate']));
			$sQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
			$iQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
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
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$iTotal=count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $sessionLogin = new Container('credo');
		$role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'close-trip')) {
            $update = true;
        } else {
            $update = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'execute')) {
            $executeAction = true;
        } else {
            $executeAction = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'cancel')) {
            $cancelAction = true;
        } else {
            $cancelAction = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'delete')) {
            $deleteAction = true;
        } else {
            $deleteAction = false;
        }
        if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'generate-trip-sheet')) {
            $tripSheetAction = true;
        } else {
            $tripSheetAction = false;
        }
		
        foreach ($rResult as $aRow) {
			$edit="";
			$closeTrip="";
			$cancelBooking="";
			$deleteBooking="";
			$generateTripSheet="";
            $row = array();
            $row[] = $aRow['booking_no'];
            $row[] = $commonService->humanDateFormat($aRow['trip_from_date']);
            $row[] = $aRow['trip_start_time'];
            $row[] = ucwords($aRow['dutyType']);
            $row[] = ucwords($aRow['city_name']);
			$row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['makeType'];
            $row[] = ucwords($aRow['guest_title'])." ".ucwords($aRow['guest_name']);
            $row[] = $aRow['pickup_address'];
            $row[] = ucwords($aRow['driver_name']);
            $row[] = $aRow['driver_mobile_no'];
            $row[] = $aRow['vehicle_no'];
            $row[] = ucwords($aRow['vendor_name']);
            
			if($executeAction){
				$edit = '<a href="../edit-execute/' . base64_encode($aRow['booking_id']) . '" title="Edit Execute Booking"><i class="fa fa-book"></i></a>';
			}
			
			if($update){
				$closeTrip = '<a href="../close-trip/' . base64_encode($aRow['booking_id']) . '" title="Close Trip"><i class="fa fa-taxi"> </i></a>';
            }
			if($cancelAction){
				$cancelBooking = '<a href="javascript:void(0)" onClick="showModal(\'../cancel/'.base64_encode($aRow['booking_id']). '\',\'600\',\'400\')" title="Cancel Booking"><i class="fa fa-times"> </i></a>';
			}
			if($deleteAction){
				$deleteBooking = '<a href="javascript:void(0)" onClick="deleteBooking(\''.base64_encode($aRow['booking_id']).'\')" title="Delete Booking"><i class="fa fa-trash-o"></i></a>';
			}
			if($tripSheetAction){
				$generateTripSheet = '<a href="javascript:void(0);" onclick="generateTripSheet(\''.base64_encode($aRow['booking_id']).'\')" title="Generate Trip Sheet"><i class="fa fa-file-code-o"></i></a>';
			}
			$row[]=$edit.$closeTrip.$cancelBooking.$deleteBooking.$generateTripSheet;
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function getTripAssignBookingDetails($bookingId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('b'=>'bookings'))
                ->columns(array('booking_id','vendor_id','make_type','booking_status','city','spec_ins'))
				->join(array('tdm' => 'trip_driver_map'),"tdm.booking_id=b.booking_id",array('driver_name','driver_mobile_no','vehicle_no','provided_make_type','vehicle_type'))
				//->join(array('emp' => 'employee_details'),"emp.employee_id=tdm.driver_id", array('mobile_no'))
				->where(array('b.booking_id'=> (int) $bookingId));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
	}
	
	public function getExecutedBookingDetails($bookingId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$vehicleResult="";
		$tariff="";
		$query = $sql->select()->from(array('b'=>'bookings'))
                ->columns(array('booking_id','booking_no','vendor_id','company_id','client_id','booking_type','make_type','city','duty_type','guest_name','guest_mobile_no','tariff','tariff_text','trip_from_date','trip_to_date','trip_start_time','trip_close_time','total_hrs','extra_hrs','ext_hrs_amount','ext_amt_per_hrs','ext_amt_per_km','start_kms','close_kms','total_kms','extra_kms','ext_kms_amount','total_days','mop','tariff_amount','driver_allowance','parking_toll','sub_total_amount','total_amount','service_tax_amt','advance','balance','trip_sheet','payment_status','paid_date','booking_status'))
				->join(array('tdm' => 'trip_driver_map'),"tdm.booking_id=b.booking_id",array('driver_name','driver_mobile_no','vehicle_no'))
				//->join(array('gd' => 'guest_details'), "gd.guest_id=b.guest_id", array('guest_name','guestMobile'=>'mobile_no','email'),'left')
				//->join(array('vd' => 'vehicle_details'),"vd.vehicle_id=tdm.vehicle_id",array('vehicle_no','vehicle_type'))
				//->join(array('vt' => 'vehicle_type'),"vt.type_id=vd.vehicle_type",array('vehicleType'=>'type_name'))
				->join(array('c' => 'clients'),"c.client_id=b.client_id",array('client_name','service_tax_type','igst_tax','sgst_tax','cgst_tax','service_tax_paid_by_client'),'left')
				->join(array('cd' => 'city_details'),"cd.city_id=b.city",array('city_name'))
				->join(array('bu' => 'business_units'),"bu.unit_id=b.booking_type",array('unit_name'))
				//->join(array('vc' => 'vendor_contacts'), "vc.contact_id=b.vendor_contact_id", array('contact_name','vendorContactMobile'=>'mobile_no'),'left')
				->where(array('b.booking_id'=> (int) $bookingId));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        $bookResult= $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($bookResult!=""){
			//Get tariff
			$rQuery = $sql->select()->from('rental_details')->where(array('company_id'=>$bookResult['company_id'],'client_id'=>$bookResult['client_id'],'city'=>$bookResult['city'],'bussiness_unit'=>$bookResult['booking_type']));
			$rQueryStr = $sql->getSqlStringForSqlObject($rQuery);
			$rResult=$dbAdapter->query($rQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($rResult!=""){
				$tQuery = $sql->select()->from('tariff_details')->where(array('rental_id'=>$rResult['rental_id'],'make_type'=>$bookResult['make_type'],'rental_type'=>$bookResult['duty_type']));
				$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
				$tariff=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			}
		}
		
		$result=array('booking'=>$bookResult,'tariff'=>$tariff);
		return $result;
	}
	
	public function fetchAllClosedBooking($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$commonService=new CommonService();
        $aColumns = array('booking_no','DATE_FORMAT(trip_from_date,"%d-%b-%Y")','trip_start_time','city_name','client_name','vmt.make_type','guest_name','pickup_address','vehicle_no','vendor_name','payment_status');
        $orderColumns = array('booking_no','trip_from_date','trip_start_time','city_name','client_name','vmt.make_type','guest_name','pickup_address','vehicle_no','vendor_name','payment_status');
		
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
        $sQuery = $sql->select()->from(array('b'=>'bookings'))
						->columns(array('booking_id','booking_no','trip_from_date','trip_start_time','payment_status','pickup_address'))
						->join(array('ci' => 'city_details'), "ci.city_id=b.city", array('city_name'))
						->join(array('cd' => 'company_details'), "cd.company_id=b.company_id", array('company_name'))
						->join(array('c' => 'clients'), "c.client_id=b.client_id", array('client_name'))
						->join(array('gd' => 'guest_details'),"gd.guest_id=b.guest_id", array('guest_name'))
						->join(array('vmt' => 'vahicle_make_type'),"vmt.make_id=b.make_type", array('makeType'=>'make_type'))
						//->join(array('rt' => 'rental_type'),"rt.type_id=b.duty_type", array('dutyType'=>'type_name'))
						->join(array('tdm' => 'trip_driver_map'),"tdm.booking_id=b.booking_id", array('map_id','driver_name','driver_mobile_no','vehicle_no'))
						->join(array('v' => 'vendors'),"v.vendor_id=b.vendor_id", array('vendor_name','vendor_no'),"left")
						->where(array('booking_status'=>'close'));
					
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		$iQuery = $sql->select()->from('bookings')->where(array('booking_status'=>'close'));
		
		if(isset($parameters['fromTripDate']) && trim($parameters['fromTripDate'])!="" && trim($parameters['closeTripDate'])!=""){
			$startDate=$commonService->dateFormat(trim($parameters['fromTripDate']));
			$endDate=$commonService->dateFormat(trim($parameters['closeTripDate']));
			$sQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
			$iQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
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
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$iTotal=count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $sessionLogin = new Container('credo');
		$role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'close-trip')) {
            $update = true;
        } else {
            $update = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'add-vendor-payment')) {
            $addVendorPayment = true;
        } else {
            $addVendorPayment = false;
        }
        
        foreach ($rResult as $aRow) {
			$edit="";
			$vendorPayment="";
			$generateBill="";
            $row = array();
            $row[] = $aRow['booking_no'];
            $row[] = $commonService->humanDateFormat($aRow['trip_from_date']);
            $row[] = $aRow['trip_start_time'];
            $row[] = ucwords($aRow['city_name']);
			$row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['makeType'];
            $row[] = $aRow['guest_name'];
            $row[] = $aRow['pickup_address'];
            $row[] = $aRow['vehicle_no'];
            $row[] = ucwords($aRow['vendor_name']);
            $row[] = ucwords($aRow['payment_status']);
            
			if($update){
				$edit = '<a href="../close-trip/' . base64_encode($aRow['booking_id']) . '/cl0s3" title="Edit Close Trip"><i class="fa fa-taxi"></i></a>';
			}
			if($addVendorPayment){
				$vendorPayment = '<a href="../add-vendor-payment/' . base64_encode($aRow['booking_id']) . '/cl0s3" title="Add Vendor Payment"><i class="fa fa-plus"> Vendor Payment</i></a>';
			}
			
				$generateBill = '<a href="javascript:void(0);" onclick="generateBill(\''.base64_encode($aRow['booking_id']).'\')" title="Generate Invoice"><i class="fa fa-file-code-o"></i></a>';
			
			$row[]=$edit.$vendorPayment.$generateBill;
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchAllVendorPendingBooking($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$commonService=new CommonService();
        $aColumns = array('booking_no','DATE_FORMAT(trip_from_date,"%d-%b-%Y")','trip_start_time','city_name','vmt.make_type','guest_name','vehicle_no','vendor_name','payment_status');
        $orderColumns = array('booking_no','trip_from_date','trip_start_time','city_name','vmt.make_type','guest_name','vehicle_no','vendor_name','payment_status');
		
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
                    $sOrder .= $orderColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
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
        $sQuery = $sql->select()->from(array('b'=>'bookings'))
						->columns(array('booking_id','booking_no','trip_from_date','parking_toll','tariff_amount','driver_allowance','service_tax','total_amount','payment_status'))
						->join(array('ci' => 'city_details'), "ci.city_id=b.city", array('city_name'))
						->join(array('cd' => 'company_details'), "cd.company_id=b.company_id", array('company_name'))
						->join(array('rt' => 'rental_type'),"rt.type_id=b.duty_type", array('dutyType'=>'type_name'))
						->join(array('bu' => 'business_units'),"bu.unit_id=b.booking_type",array('unit_name'))
						->join(array('pm' => 'payment_mode'),"pm.type_id=b.mop",array('payment_type'))
						->join(array('v' => 'vendors'),"v.vendor_id=b.vendor_id", array('vendor_name','vendor_no'),"left")
						->where(array('booking_status'=>'add_vendor_payment'));
					
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		$iQuery = $sql->select()->from('bookings')->where(array('booking_status'=>'add_vendor_payment'));
		
		if(isset($parameters['searchDate']) && trim($parameters['searchDate'])!=""){
			$expSearchDate=explode("to",$parameters['searchDate']);
			$startDate=$commonService->dateFormat(trim($expSearchDate[0]));
			$endDate=$commonService->dateFormat(trim($expSearchDate[1]));
			$sQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
			$iQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
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
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$iTotal=count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $sessionLogin = new Container('credo');
		$role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'edit-vendor-payment')) {
            $update = true;
        } else {
            $update = false;
        }
		
        
        foreach ($rResult as $aRow) {
			$editVendorPayment="";
			$closeTrip="";
            $row = array();
            $row[] = $aRow['booking_no'];
            $row[] = $commonService->humanDateFormat($aRow['trip_from_date']);
            $row[] = $aRow['unit_name'];
            $row[] = ucwords($aRow['city_name']);
			//$row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['dutyType'];
            $row[] = $aRow['tariff_amount'];
            $row[] = $aRow['parking_toll'];
            $row[] = $aRow['driver_allowance'];
            $row[] = $aRow['service_tax'];
            $row[] = $aRow['total_amount'];
            $row[] = $aRow['payment_type'];
            $row[] = ucwords($aRow['vendor_name']);
            $row[] = ucwords($aRow['payment_status']);
            
			if($update){
				$editVendorPayment = '<a href="../edit-vendor-payment/' . base64_encode($aRow['booking_id']) . '/cl0s3" title="Edit Vendor Payment"><i class="fa fa-pencil"> Edit Vendor Payment</i></a>';
			}
			
			
			$row[]=$editVendorPayment;
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchCloseBooking($bookingId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('b'=>'bookings'))
                ->columns(array('booking_id','booking_no','vendor_id','booking_type','duty_type','tariff','tariff_text','total_hrs','extra_hrs','ext_hrs_amount','start_kms','close_kms','total_kms','extra_kms','ext_kms_amount','total_days','mop','tariff_amount','driver_allowance','parking_toll','permit','total_amount','service_tax_amt','advance','balance','payment_status','paid_date','booking_status'))
				->join(array('v' => 'vendors'),"v.vendor_id=b.vendor_id", array('vendor_name','vendor_no','vendor_type'),"left")
				->where(array('b.booking_id'=> (int) $bookingId));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
	}
	
	public function cancelBookingDetails($params){
		if(isset($params['bookingId']) && trim($params['bookingId'])!=""){
			$bookingId=base64_decode($params['bookingId']);
            $data = array(
				'reason_for_cancellation'=>$params['reasonForCancellation'],
				'booking_status'=>'cancel'
            );
            $this->update($data, array('booking_id' => $bookingId));
			return $bookingId;
		}
	}
	
	public function fetchAllCancelBookings($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$commonService=new CommonService();
        $aColumns = array('booking_no','DATE_FORMAT(trip_from_date,"%d-%b-%Y")','trip_start_time','type_name','city_name','client_name','vmt.make_type','guest_name');
        $orderColumns = array('booking_no','trip_from_date','trip_start_time','type_name','city_name','client_name','vmt.make_type','guest_name');

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
        $sQuery = $sql->select()->from(array('b'=>'bookings'))
						->columns(array('booking_id','booking_no','trip_from_date','trip_start_time','booking_status'))
						->join(array('ci' => 'city_details'), "ci.city_id=b.city", array('city_name'))
						->join(array('cd' => 'company_details'), "cd.company_id=b.company_id", array('company_name'))
						->join(array('c' => 'clients'), "c.client_id=b.client_id", array('client_name'))
						->join(array('gd' => 'guest_details'),"gd.guest_id=b.guest_id", array('guest_name'))
						->join(array('vmt' => 'vahicle_make_type'),"vmt.make_id=b.make_type", array('makeType'=>'make_type'))
						->join(array('rt' => 'rental_type'),"rt.type_id=b.duty_type", array('dutyType'=>'type_name'))
						->where(array('booking_status'=>'cancel'));
					
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		$iQuery = $sql->select()->from('bookings')->where(array('booking_status'=>'cancel'));
		
		if(isset($parameters['fromTripDate']) && trim($parameters['fromTripDate'])!="" && trim($parameters['closeTripDate'])!=""){
			$startDate=$commonService->dateFormat(trim($parameters['fromTripDate']));
			$endDate=$commonService->dateFormat(trim($parameters['closeTripDate']));
			$sQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
			$iQuery->where("(trip_from_date>='".$startDate."' AND trip_from_date<='".$endDate."')");
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
        //$iTotal = $this->select(array('booking_status'=>'cancel'))->count();
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		$iTotal=count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        $sessionLogin = new Container('credo');
		$role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
		
		if ($acl->isAllowed($role, 'Admin\Controller\Booking', 'delete')) {
            $deleteAction = true;
        } else {
            $deleteAction = false;
        }
		
        
        foreach ($rResult as $aRow) {
			$edit="";
			$deleteBooking="";
			
            $row = array();
            $row[] = $aRow['booking_no'];
            $row[] = $commonService->humanDateFormat($aRow['trip_from_date']);
            $row[] = $aRow['trip_start_time'];
            $row[] = ucwords($aRow['dutyType']);
            $row[] = ucwords($aRow['city_name']);
			$row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['makeType'];
            $row[] = $aRow['guest_name'];
            if($update){
				//$edit = '<a href="../edit/' . base64_encode($aRow['booking_id']) . '" class="btn btn-default" style="margin-right: 2px;" title="Edit"><i class="fa fa-pencil"> Edit</i></a>';
            }
			if($deleteAction){
			$deleteBooking = '<a href="javascript:void(0)" onClick="deleteBooking(\''.base64_encode($aRow['booking_id']).'\')" title="Delete Booking"><i class="fa fa-trash-o"></i></a>';
			}
			$row[]=$deleteBooking;
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchBookingDetails($bookingId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('b'=>'bookings'))
                ->columns(array('booking_id','booking_no','company_id','booking_date','trip_from_date','city','duty_type','tariff','booking_status','guest_id','guest_title','guest_name','guest_mobile_no','booker_id','booker_name','booker_mobile_no','booker_email','trip_start_time','spec_ins','pickup_area','pickup_address','mop','tariff_text','tariff_amount','total_hrs','total_kms','extra_kms','ext_kms_amount','extra_hrs','ext_hrs_amount','ext_amt_per_km','ext_amt_per_hrs','parking_toll','permit','driver_allowance','day_time_driver_allowance','night_time_driver_allowance','driver_allowance_per_day','driver_allowance_per_night','service_tax_type','sgst_tax','cgst_tax','igst_tax','sub_total_amount','total_amount','balance','payment_status'))
				->join(array('bvt' => 'vehicle_type'), "bvt.type_id=b.vehicle_type", array('reqVehicleMake'=>'type_name'),'left')
				->join(array('c' => 'company_details'), "c.company_id=b.company_id", array('company_name'),'left')
				->join(array('bu' => 'business_units'), "bu.unit_id=b.booking_type", array('unit_name'),'left')
				->join(array('cl' => 'clients'), "cl.client_id=b.client_id", array('client_name','clientAddress'=>'address','clientGstNo'=>'gst_no'),'left')
				->join(array('cd' => 'city_details'), "cd.city_id=b.city", array('city_name'),'left')
				->join(array('vmt' => 'vahicle_make_type'), "vmt.make_id=b.make_type", array('make_id','make_type'),'left')
				->join(array('rt' => 'rental_type'), "rt.type_id=b.duty_type", array('dutyType'=>'type_name'),'left')
				->join(array('tdm' => 'trip_driver_map'), "tdm.booking_id=b.booking_id", array('driver_name','driver_mobile_no','vehicle_no','provided_make_type'),'left')
				->join(array('tvmt' => 'vahicle_make_type'), "tvmt.make_id=tdm.provided_make_type", array('provideMakeType'=>'make_type'),'left')
				->join(array('vt' => 'vehicle_type'), "vt.type_id=tdm.vehicle_type", array('vehicleMake'=>'type_name'),'left')
				->join(array('pm' => 'payment_mode'),"pm.type_id=b.mop",array('payment_type'))
				->where(array('b.booking_id'=> (int) $bookingId));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
	}
	
	public function sendConfimationBookingEmail($bookingId){
		//Send booking details to booker email
		$dbAdapter = $this->adapter;
		$bookingResult=$this->fetchBookingDetails($bookingId);
		$commonService=new CommonService();
		$configDb=new GlobalConfigTable($dbAdapter);
		//Send email
		if(isset($bookingResult['booker_email']) && trim($bookingResult['booker_email'])!=""){
			$to=$bookingResult['booker_email'];
			
			$newBookingReceiveMail=$configDb->fetchGlobalValue('new_booking_receive_mail');
			
			if($newBookingReceiveMail!=""){
				if(trim($to)!=""){
				$to=$to.','.$newBookingReceiveMail;
				}
			}
			
			$subject=ucwords($bookingResult['company_name'])." Booking Confirmation";
			$message="Dear ".ucwords($bookingResult['guest_name']).", <br/><br/>";
			$message.="Greetings From ".ucwords($bookingResult['company_name'])." !!! <br/><br/>";
			$message.="This is to inform you that your booking is confirmed.<br/><br/>";
			
			$message.="<table border='1' cellpadding='2' cellspacing='0' style='width:310px;'>";
			
			$message.="<tr>";
			$message.="<td colspan='2' style='text-align:center;background-color:#4f81bd;color:#fff;font-weight:bold;''>Booking Details </td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Booking Reference No. </td>";
			$message.="<td style='width:20%;'>".$bookingResult['booking_no']."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Booking Date</td>";
			$message.="<td>".$commonService->humanDateFormat($bookingResult['booking_date'])."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Name of Company </td>";
			$message.="<td>".ucwords($bookingResult['client_name'])."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td style='width:25%;'>Guest Name </td>";
			$message.="<td>".$bookingResult['guest_title']." ".ucwords($bookingResult['guest_name'])."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Guest Contact Number </td>";
			$message.="<td>".$bookingResult['guest_mobile_no']."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Booking City </td>";
			$message.="<td>".ucwords($bookingResult['city_name'])."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Car Type </td>";
			$message.="<td>".ucwords($bookingResult['make_type'])."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Travel Date & Time</td>";
			$message.="<td>".$commonService->humanDateFormat($bookingResult['trip_from_date'])." / ".$bookingResult['trip_start_time']."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Pickup Address</td>";
			$message.="<td>".$bookingResult['pickup_address']."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Trip Type </td>";
			$message.="<td>".$bookingResult['dutyType']."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Mode of Payment</td>";
			$message.="<td>".$bookingResult['payment_type']."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Booking given by</td>";
			$message.="<td>".ucwords($bookingResult['booker_name'])."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Booker Contact Number</td>";
			$message.="<td>".$bookingResult['booker_mobile_no']."</td>";
			$message.="</tr>";
			
			if(isset($bookingResult['spec_ins']) && trim($bookingResult['spec_ins'])!=""){
			$message.="<tr>";
			$message.="<td>Remarks</td>";
			$message.="<td>".$bookingResult['spec_ins']."</td>";
			$message.="</tr>";
			}
			$message.="</table>";
			$message.="<br/> Wish you a Safe Journey.For Assistance call at 9087218885 ";
			
			$fromName=ucwords($bookingResult['company_name'])." Booking Confirmation";
			if($bookingResult['company_id']=="1"){
				$fromMail="reservation@beecabs.in";	
			}else{
				$fromMail="booking@aspalogistics.co.in";	
			}
			$tempMailDb=new TempMailTable($dbAdapter);
			$tempMailDb->insertTempMailDetails($to,$subject,$message,$fromMail,$fromName);
			
		}
		
	}
	
	public function updateBookingStatus($bookingId,$status){
		if($bookingId>0){
			$this->update(array('booking_status'=>$status), array('booking_id' => $bookingId));
		}
	}
	
	public function deleteBookingDetails($bookingId){
		if($bookingId>0){
			$dbAdapter = $this->adapter;
			$tripDriverMapDb = new TripDriverMapTable($dbAdapter);
			$bookingVendorPaymentDb = new BookingVendorPaymentsTable($dbAdapter);
			$tripDriverMapDb->delete(array('booking_id'=>$bookingId));
			$bookingVendorPaymentDb->delete(array('booking_id'=>$bookingId));
			return $this->delete(array('booking_id'=>$bookingId));
		}
	}
	
	public function sendBookingSms($bookingId){
		$dbAdapter = $this->adapter;
		$bookingResult=$this->fetchBookingDetails($bookingId);
		$commonService=new CommonService();
		$configDb=new GlobalConfigTable($dbAdapter);
		$newBookingReceiveSMS=$configDb->fetchGlobalValue('new_booking_receive_guest_sms');
		$config = new \Zend\Config\Reader\Ini();
		$configResult = $config->fromFile(CONFIG_PATH . '/custom.config.ini');
		$smsUserId = $configResult["sms"]["user"];
		$smsPassword = $configResult["sms"]["password"];
		
		//Send SMS
		if($bookingResult['company_id']=="1"){
			if(isset($bookingResult['guest_mobile_no']) && trim($bookingResult['guest_mobile_no'])!=""){
				$expGuestMobile=explode(",",$bookingResult['guest_mobile_no']);
				if(trim($newBookingReceiveSMS)!=""){
					$configMobile=explode(",",$newBookingReceiveSMS);
					$expGuestMobile = array_merge($expGuestMobile,$configMobile); 
				}
				/*
				Greetings from Beecabs! (XXXXXXXX) details for (XXXXXXXX) @ (XXXXXXXX)
				Driver Name : (XXXXX)
				Mobile No : (XXXXXXXXX)
				Cab No : (XXXXXX)
				For Assistance : (XXXXXXX)
				*/
				foreach($expGuestMobile as $guestMobileNo){
					if(trim($guestMobileNo)!=""){
						//SMS to Guest & Booker
						$msg="Greetings from Beecabs! Cab Details for  ".$bookingResult['guest_title']." ".$bookingResult['guest_name']." @ ".$bookingResult['pickup_area']."`Pickup Date & Time : ".$commonService->humanDateFormat($bookingResult['trip_from_date'])." / ".$bookingResult['trip_start_time']."`Driver Name : ".$bookingResult['driver_name']."`Mobile No : ".$bookingResult['driver_mobile_no']."`Cab Type : ".$bookingResult['vehicleMake']."`Cab No : ".$bookingResult['vehicle_no']."`For Assistance : Kindly call us 9087218885";
						//$msg="Greetings from Beecabs! ".$bookingResult['vehicleMake']." details for ".$bookingResult['guest_title']." ".$bookingResult['guest_name']." @ ".$bookingResult['city_name']."`Driver Name : ".$bookingResult['driver_name']."`Mobile No : ".$bookingResult['driver_mobile_no']."`Cab No : ".$bookingResult['vehicle_no']."`For Assistance : Kindly call us 9087218885";
						$msg=urlencode($msg);
						
						//SMS send to guest
						$ch = curl_init();
						//curl_setopt($ch,CURLOPT_URL,  "http://www.meru.co.in/wip/sendsms");
						curl_setopt($ch,CURLOPT_URL,  "http://www.wizhcomm.co.in/wems/sendsms");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$smsUserId&password=$smsPassword&to=".$guestMobileNo."&message=".$msg."&from=".$configResult["sms"]["beecabSenderId"]);
						$buffer = curl_exec($ch);
						if(empty ($buffer))
						{ echo " buffer is empty "; }
						else
						{ echo $buffer; } 
						curl_close($ch);
					}
				}
			}
			
			if(isset($bookingResult['booker_mobile_no']) && trim($bookingResult['booker_mobile_no'])!=""){
				$expBookerMobile=explode(",",$bookingResult['booker_mobile_no']);
				foreach($expBookerMobile as $bookerMobileNo){
					if(trim($bookerMobileNo)!=""){
						//SMS send to booker
						$ch = curl_init();
						curl_setopt($ch,CURLOPT_URL,  "http://www.wizhcomm.co.in/wems/sendsms");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$smsUserId&password=$smsPassword&to=".$bookerMobileNo."&message=".$msg."&from=".$configResult["sms"]["beecabSenderId"]);
						$buffer = curl_exec($ch);
						if(empty ($buffer))
						{ echo " buffer is empty "; }
						else
						{ echo $buffer; } 
						curl_close($ch);
					}
				}
			}
			
		
			if(isset($bookingResult['driver_mobile_no']) && trim($bookingResult['driver_mobile_no'])!=""){
				//SMS to Driver template
				/*
				Greetings from Beecabs! Guest Details Below
				Guest Name : (XXXXX)
				Mobile No : (XXXXXX)
				Pickup Address : (XXXXX)
				Pickup Date & Time : (XXXXXX)
				Duty Type : (XXXXX)
				For Assistance : (XXXXXXX)
				*/
				//SMS to Driver
				if(trim($bookingResult['spec_ins'])==""){
					$bookingResult['spec_ins']="";
				}else{
					$bookingResult['spec_ins']=" - ".trim($bookingResult['spec_ins']);
				}
				$msg="Greetings from Beecabs! Guest Details Below`Guest Name : ".$bookingResult['guest_title']." ".$bookingResult['guest_name']."`Mobile No : ".$bookingResult['guest_mobile_no']."`Pickup Address : ".$bookingResult['pickup_address']."`Pickup Date & Time : ".$commonService->humanDateFormat($bookingResult['trip_from_date'])." / ".$bookingResult['trip_start_time']."`Duty Type : ".$bookingResult['dutyType'].$bookingResult['spec_ins']."`For Assistance : Kindly call us 9087218885";
				$msg=urlencode($msg);
				//echo $msg;die;
				$driverMobile=explode(",",$bookingResult['driver_mobile_no']);
				foreach($driverMobile as $driverMobileNo){
					if(trim($driverMobileNo)!=""){
						$ch = curl_init();
						curl_setopt($ch,CURLOPT_URL,  "http://www.wizhcomm.co.in/wems/sendsms");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$smsUserId&password=$smsPassword&to=".$driverMobileNo."&message=".$msg."&from=".$configResult["sms"]["beecabSenderId"]);
						$buffer = curl_exec($ch);
						if(empty ($buffer))
						{ echo " buffer is empty "; }
						else
						{ echo $buffer; } 
						curl_close($ch);
					}
				}
			}
		}else{
			if(isset($bookingResult['guest_mobile_no']) && trim($bookingResult['guest_mobile_no'])!=""){
				/*
				Greetings from Aspalogistics! (XXXXXXXX) details for (XXXXXXXX) @ (XXXXXXXX)
				Driver Name : (XXXXX)
				Mobile No : (XXXXXXXXX)
				Cab No : (XXXXXX)
				For Assistance : (XXXXXXX)
				
				Greetings from Aspalogistics! Cab Details for (Guest Name) @ (PickupArea)
				Pickup Date & Time:  (XXXXX)
				Driver Name : (XXXXX)
				Mobile No : (XXXXXXXXX)
				Cab Type: (XXXXX)
				Cab No : (XXXXXX)
				For Assistance : (XXXXXXX)
				*/
				//SMS to Guest & Booker
				$expGuestMobile=explode(",",$bookingResult['guest_mobile_no']);
				if(trim($newBookingReceiveSMS)!=""){
					$configMobile=explode(",",$newBookingReceiveSMS);
					$expGuestMobile = array_merge($expGuestMobile,$configMobile); 
				}
				foreach($expGuestMobile as $guestMobileNo){
					if(trim($guestMobileNo)!=""){
						$msg="Greetings from Aspalogistics! Cab Details for  ".$bookingResult['guest_title']." ".$bookingResult['guest_name']." @ ".$bookingResult['pickup_area']."`Pickup Date & Time : ".$commonService->humanDateFormat($bookingResult['trip_from_date'])." / ".$bookingResult['trip_start_time']."`Driver Name : ".$bookingResult['driver_name']."`Mobile No : ".$bookingResult['driver_mobile_no']."`Cab Type : ".$bookingResult['vehicleMake']."`Cab No : ".$bookingResult['vehicle_no']."`For Assistance : Kindly call us 9087218881";
						//$msg="Greetings from Aspalogistics! ".$bookingResult['vehicleMake']." details for ".$bookingResult['guest_title']." ".$bookingResult['guest_name']." @ ".$bookingResult['city_name']."`Driver Name : ".$bookingResult['driver_name']."`Mobile No : ".$bookingResult['driver_mobile_no']."`Cab No : ".$bookingResult['vehicle_no']."`For Assistance : Kindly call us 9087218881";
						$msg=urlencode($msg);
						//SMS send to guest
						$ch = curl_init();
						curl_setopt($ch,CURLOPT_URL,  "http://www.wizhcomm.co.in/wems/sendsms");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$smsUserId&password=$smsPassword&to=".$guestMobileNo."&message=".$msg."&from=".$configResult["sms"]["aspaSenderId"]);
						$buffer = curl_exec($ch);
						if(empty ($buffer))
						{ echo " buffer is empty "; }
						else
						{ echo $buffer; } 
						curl_close($ch);
					}
				}
			}
			
			if(isset($bookingResult['booker_mobile_no']) && trim($bookingResult['booker_mobile_no'])!=""){
				$expBookerMobile=explode(",",$bookingResult['booker_mobile_no']);
				foreach($expBookerMobile as $bookerMobileNo){
					if(trim($bookerMobileNo)!=""){
						//SMS send to booker
						$ch = curl_init();
						curl_setopt($ch,CURLOPT_URL,  "http://www.wizhcomm.co.in/wems/sendsms");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$smsUserId&password=$smsPassword&to=".$bookerMobileNo."&message=".$msg."&from=".$configResult["sms"]["aspaSenderId"]);
						$buffer = curl_exec($ch);
						if(empty ($buffer))
						{ echo " buffer is empty "; }
						else
						{ echo $buffer; } 
						curl_close($ch);
					}
				}
			}
		
			if(isset($bookingResult['driver_mobile_no']) && trim($bookingResult['driver_mobile_no'])!=""){
				//SMS to Driver template
				/*
				Greetings from Aspalogistics! Guest Details Below
				Guest Name : (XXXXX)
				Mobile No : (XXXXXX)
				Pickup Address : (XXXXX)
				Pickup Date & Time : (XXXXXX)
				Duty Type : (XXXXX)
				For Assistance : (XXXXXXX)
				*/
				//SMS to Driver
				if(trim($bookingResult['spec_ins'])==""){
					$bookingResult['spec_ins']="";
				}else{
					$bookingResult['spec_ins']=" - ".trim($bookingResult['spec_ins']);
				}
				$msg="Greetings from Aspalogistics! Guest Details Below`Guest Name : ".$bookingResult['guest_title']." ".$bookingResult['guest_name']."`Mobile No : ".$bookingResult['guest_mobile_no']."`Pickup Address : ".$bookingResult['pickup_address']."`Pickup Date & Time : ".$commonService->humanDateFormat($bookingResult['trip_from_date'])." / ".$bookingResult['trip_start_time']."`Duty Type : ".$bookingResult['dutyType'].$bookingResult['spec_ins']."`For Assistance : Kindly call us 9087218881";
				$msg=urlencode($msg);
				
				$driverMobile=explode(",",$bookingResult['driver_mobile_no']);
				foreach($driverMobile as $driverMobileNo){
					if(trim($driverMobileNo)!=""){
						$ch = curl_init();
						curl_setopt($ch,CURLOPT_URL,  "http://www.wizhcomm.co.in/wems/sendsms");
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, "username=$smsUserId&password=$smsPassword&to=".$driverMobileNo."&message=".$msg."&from=".$configResult["sms"]["aspaSenderId"]);
						$buffer = curl_exec($ch);
						if(empty ($buffer))
						{ echo " buffer is empty "; }
						else
						{ echo $buffer; } 
						curl_close($ch);
					}
				}
			}
		}
	}
	
	public function sendAssignedBookingEmail($bookingId){
		//Send booking details to booker email
		$dbAdapter = $this->adapter;
		$bookingResult=$this->fetchBookingDetails($bookingId);
		$commonService=new CommonService();
		//Send email
		if(isset($bookingResult['booker_email']) && trim($bookingResult['booker_email'])!=""){
			$to=$bookingResult['booker_email'];
			$subject=ucwords($bookingResult['company_name'])." Booking Confirmation";
			$message="Dear ".ucwords($bookingResult['guest_name']).", <br/><br/>";
			$message.="Greetings From ".ucwords($bookingResult['company_name'])." !!! <br/><br/>";
			$message.="This is to inform you that your booking is confirmed.<br/><br/>";
			
			$message.="<table border='1' cellpadding='2' cellspacing='0' style='width:80%;'>";
			
			$message.="<tr>";
			$message.="<td colspan='2' style='width:25%;text-align:center;background-color:#4f81bd;color:#fff;font-weight:bold;''>Booking Details </td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td style='width:25%;'>Booking Reference No. </td>";
			$message.="<td>".$bookingResult['booking_no']."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td style='width:25%;'>Guest Name </td>";
			$message.="<td>".$bookingResult['guest_title']." ".ucwords($bookingResult['guest_name'])."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Guest Contact Number </td>";
			$message.="<td>".$bookingResult['guest_mobile_no']."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Driver Name</td>";
			$message.="<td>".$bookingResult['driver_name']."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Driver Contact Number</td>";
			$message.="<td>".$bookingResult['driver_mobile_no']."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Booking City </td>";
			$message.="<td>".ucwords($bookingResult['city_name'])."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Car Type </td>";
			$message.="<td>".ucwords($bookingResult['make_type'])."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Trip Details </td>";
			$message.="<td>".$bookingResult['dutyType']."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Booking Date</td>";
			$message.="<td>".$commonService->humanDateFormat($bookingResult['booking_date'])."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Trip Date & Time</td>";
			$message.="<td>".$commonService->humanDateFormat($bookingResult['trip_from_date'])." / ".$bookingResult['trip_start_time']."</td>";
			$message.="</tr>";
			
			$message.="<tr>";
			$message.="<td>Pickup Address</td>";
			$message.="<td>".$bookingResult['pickup_address']."</td>";
			$message.="</tr>";
			
			$message.="<tr style='background:#d3dfee;'>";
			$message.="<td>Payment Status</td>";
			$message.="<td>".ucwords($bookingResult['payment_status'])."</td>";
			$message.="</tr>";
			
			$message.="</table>";
			if($bookingResult['company_id']=="1"){
				$message.="<br/> Wish you a Safe Journey.For Assistance call at 9087218885 ";
			}else{
				$message.="<br/> Wish you a Safe Journey.For Assistance call at 9087218881 ";
			}
			
			
			$fromName=ucwords($bookingResult['company_name'])." Booking Confirmation";
			$fromMail="reservation@beecabs.in";
			$tempMailDb=new TempMailTable($dbAdapter);
			$tempMailDb->insertTempMailDetails($to,$subject,$message,$fromMail,$fromName);
			
		}
	}
	
	public function fetchTodayCurrentBookingCount(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$currentDate= date('Y-m-d');
		$query = $sql->select()->from(array('b'=>'bookings'))
				->where(array('b.booking_date'=> $currentDate));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        $result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		return count($result);
	}
	
	public function fetchTodayExecutedBookingCount(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$currentDate= date('Y-m-d');
		$query = $sql->select()->from('bookings')
				->where(array('booking_status'=>'assigned','booking_date'=> $currentDate));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        $result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		return count($result);
	}
	
	public function fetchTodayCancelledBookingCount(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$currentDate= date('Y-m-d');
		$query = $sql->select()->from('bookings')
				->where(array('booking_status'=>'cancel','booking_date'=> $currentDate));
		$queryStr = $sql->getSqlStringForSqlObject($query);
        $result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		return count($result);
	}
}
?>
