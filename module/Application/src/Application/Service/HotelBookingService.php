<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;
use PHPExcel;

class HotelBookingService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addHotelBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelBookingsTable');
            $result = $hotelDb->addHotelBookingDetails($params);
            if($result>0){
				$adapter->commit();
				$alertContainer = new Container('alert');
				$alertContainer->alertMsg = 'Booking details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateHotelBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelBookingsTable');
            $result = $hotelDb->updateHotelBookingDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Booking details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
	public function cancelHotelBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelBookingsTable');
            $result = $hotelDb->cancelHotelBookingDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Booking details cancelled successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
	
	public function completeHotelBooking($bookingId,$bookingNo){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelBookingsTable');
            $result = $hotelDb->completeHotelBookingDetails($bookingId,$bookingNo);
            $adapter->commit();
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
	
    public function getAllHotelBookings($parameters){
        $hotelDb = $this->sm->get('HotelBookingsTable');
		$acl = $this->sm->get('AppAcl');
        return $hotelDb->fetchAllHotelBookings($parameters,$acl);
    }
    
    public function generateBookingRef($clientId){
        $db = $this->sm->get('HotelBookingsTable');
        return $db->generateBookingRef($clientId);
    }
    
	public function getHotelBooking($bookingId){
        $hotelDb = $this->sm->get('HotelBookingsTable');
        return $hotelDb->fetchHotelBooking($bookingId);
    }
	
	public function getHotelBookingDetails($bookingId){
        $hotelDb = $this->sm->get('HotelBookingsTable');
        return $hotelDb->fetchHotelBookingDetails($bookingId);
    }
	
	public function getAllHotelBookingReports($parameters){
        $hotelDb = $this->sm->get('HotelBookingsTable');
		$acl = $this->sm->get('AppAcl');
        return $hotelDb->fetchAllHotelBookingReports($parameters,$acl);
    }
	
	public function getLastClosingKms($vehicleId){
		$hotelDb = $this->sm->get('HotelBookingsTable');
        return $hotelDb->fetchLastClosingKms($vehicleId);
	}
	
	public function exportHotelBookingDetails($params)
    {
        try{
            $queryContainer = new Container('query');
            $excel = new PHPExcel();
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            $cacheSettings = array('memoryCacheSize' => '80MB');
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $output = array();
            $sheet = $excel->getActiveSheet();
            $dbAdapter = $this->sm->get('Zend\Db\Adapter\Adapter');
            $sql = new Sql($dbAdapter);
            $searchDate="";
            if(isset($queryContainer->exportQuery)){
				$sQuery=$queryContainer->exportQuery->order("booking_id ASC");
			}
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->exportQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            $commonService=new CommonService();
            
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
                    if(trim($aRow['booking_date_time'])!=""){
						$expBookDate=explode(" ",$aRow['booking_date_time']);
                        $aRow['booking_date_time']=$commonService->humanDateFormat($expBookDate[0]);
                    }
					if(trim($aRow['cancellation_time'])!=""){
						$expBookDate=explode(" ",$aRow['cancellation_time']);
                        $aRow['cancellation_time']=$commonService->humanDateFormat($expBookDate[0])." ".$expBookDate[1];
                    }
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
					
                    $row[] = $aRow['booking_no'];
                    $row[] = $aRow['booking_date_time'];
                    $row[] = ucwords($aRow['guest_name']);
					$row[] = $aRow['room_no'];
					$row[] = $aRow['booking_given_by'];
                    $row[] = ucfirst($aRow['vehicle_type']);
                    $row[] = $aRow['vehicle_no'];
                    $row[] = $aRow['driver_name'];
                    $row[] = $aRow['type_name'];
                    $row[] = $aRow['trip_time'];
                    $row[] = $aRow['end_time'];
                    $row[] = $aRow['total_hrs'];
                    $row[] = $aRow['start_kms'];
                    $row[] = $aRow['close_kms'];
                    $row[] = $aRow['total_kms'];
                    $row[] = $aRow['parking'];
                    $row[] = $aRow['toll'];
                    $row[] = $aRow['permit'];
                    $row[] = ucfirst($aRow['package']);
                    $row[] = $aRow['driver_allowance_day'];
                    $row[] = $aRow['driver_allowance_night'];
                    $row[] = $aRow['total_amount'];
                    $row[] = $aRow['hotel_bill_amount'];
                    $row[] = $aRow['vendor_bill_amount'];
                    $row[] = $aRow['bc_revenue'];
                    $row[] = ucwords($aRow['payment_status']);
                    $row[] = $aRow['reason_for_cancellation'];
                    $row[] = $aRow['cancellation_time'];
                    $row[] = ucwords($aRow['status']);
					
                    
                    $output[] = $row;
               }
            }
            
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                    'size'=>12,
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                )
            );
            
           $borderStyle = array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                )
            );
           
            
            $sheet->setCellValue('A1', html_entity_decode('Hotel Booking List', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            if(isset($params['fromDate']) && trim($params['fromDate'])!='' && trim($params['toDate'])!=""){
				$sheet->setCellValue('A2', html_entity_decode('Date Range : '.$params['fromDate'].' to '.$params['toDate'], ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
             }
			
            $sheet->setCellValue('A4', html_entity_decode('Booking Reference', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Booking Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Guest Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Room Number', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Booking Given By', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Vehicle Type', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Vehicle Number', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Driver Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Booking Type', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
			$sheet->setCellValue('J4', html_entity_decode('Start Time', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('K4', html_entity_decode('End Time', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('L4', html_entity_decode('Total Hrs', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('M4', html_entity_decode('Start Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('N4', html_entity_decode('End Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('O4', html_entity_decode('Total Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('P4', html_entity_decode('Parking', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('Q4', html_entity_decode('Toll', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('R4', html_entity_decode('Permit', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			
            $sheet->setCellValue('S4', html_entity_decode('Package', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('T4', html_entity_decode('Driver Allowance', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('U4', html_entity_decode('Night Allowance', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('V4', html_entity_decode('Total Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);            
			$sheet->setCellValue('W4', html_entity_decode('Hotel Bill Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('X4', html_entity_decode('Vendor Bill Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Y4', html_entity_decode('BC Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Z4', html_entity_decode('Payment Status', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('AA4', html_entity_decode('Reason For Cancellation', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('AB4', html_entity_decode('Cancellation Time', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('AC4', html_entity_decode('Booking Status', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
            $sheet->getStyle('A1:B1')->getFont()->setBold(TRUE)->setSize(16);
            
            
            $sheet->getStyle('A4')->applyFromArray($styleArray);
            $sheet->getStyle('B4')->applyFromArray($styleArray);
            $sheet->getStyle('C4')->applyFromArray($styleArray);
            $sheet->getStyle('D4')->applyFromArray($styleArray);
            $sheet->getStyle('E4')->applyFromArray($styleArray);
            $sheet->getStyle('F4')->applyFromArray($styleArray);
            $sheet->getStyle('G4')->applyFromArray($styleArray);
            $sheet->getStyle('H4')->applyFromArray($styleArray);
            $sheet->getStyle('I4')->applyFromArray($styleArray);
            $sheet->getStyle('J4')->applyFromArray($styleArray);
            $sheet->getStyle('K4')->applyFromArray($styleArray);
            $sheet->getStyle('L4')->applyFromArray($styleArray);
            $sheet->getStyle('M4')->applyFromArray($styleArray);
            $sheet->getStyle('N4')->applyFromArray($styleArray);
            $sheet->getStyle('O4')->applyFromArray($styleArray);
            $sheet->getStyle('P4')->applyFromArray($styleArray);
            $sheet->getStyle('Q4')->applyFromArray($styleArray);
            $sheet->getStyle('R4')->applyFromArray($styleArray);
            $sheet->getStyle('S4')->applyFromArray($styleArray);
            $sheet->getStyle('T4')->applyFromArray($styleArray);
            $sheet->getStyle('U4')->applyFromArray($styleArray);
            $sheet->getStyle('V4')->applyFromArray($styleArray);
            $sheet->getStyle('W4')->applyFromArray($styleArray);
            $sheet->getStyle('X4')->applyFromArray($styleArray);
            $sheet->getStyle('Y4')->applyFromArray($styleArray);
            $sheet->getStyle('Z4')->applyFromArray($styleArray);
            $sheet->getStyle('AA4')->applyFromArray($styleArray);
            $sheet->getStyle('AB4')->applyFromArray($styleArray);
            $sheet->getStyle('AC4')->applyFromArray($styleArray);
           
            
            
            foreach ($output as $rowNo => $rowData) {
                $colNo = 0;
                foreach ($rowData as $field => $value) {
                    if (!isset($value)) {
                        $value = "";
                    }
                    if (is_numeric($value)) {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 5)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 5)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
                    }
                    $rRowCount = $rowNo + 5;
                    $cellName = $sheet->getCellByColumnAndRow($colNo, $rowNo + 5)->getColumn();
                    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                    $sheet->getDefaultRowDimension()->setRowHeight(18);
                    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
                    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 5)->getAlignment()->setWrapText(true);
                    $colNo++;
                }
            }
	    
            $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $filename = 'hotel-booking-details' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-HOTEL-BOOKING-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
	
	public function addFpsHotelBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelBookingsTable');
            $result = $hotelDb->addFpsHotelBookingDetails($params);
            if($result>0){
				$adapter->commit();
				$alertContainer = new Container('alert');
				$alertContainer->alertMsg = 'Booking details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
	
	public function updateFpsHotelBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelBookingsTable');
            $result = $hotelDb->updateFpsHotelBookingDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Booking details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
	
	public function getAllFpsHotelBookings($parameters){
        $hotelDb = $this->sm->get('HotelBookingsTable');
		$acl = $this->sm->get('AppAcl');
        return $hotelDb->fetchAllFpsHotelBookings($parameters,$acl);
    }
	
	public function getAllFpsHotelBookingReports($parameters){
        $hotelDb = $this->sm->get('HotelBookingsTable');
		$acl = $this->sm->get('AppAcl');
        return $hotelDb->fetchAllFpsHotelBookingReports($parameters,$acl);
    }
	
	public function exportFpsHotelBookingDetails($params)
    {
        try{
            $queryContainer = new Container('query');
            $excel = new PHPExcel();
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            $cacheSettings = array('memoryCacheSize' => '80MB');
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $output = array();
            $sheet = $excel->getActiveSheet();
            $dbAdapter = $this->sm->get('Zend\Db\Adapter\Adapter');
            $sql = new Sql($dbAdapter);
            $searchDate="";
            if(isset($queryContainer->exportQuery)){
				$sQuery=$queryContainer->exportQuery->order("booking_id ASC");
			}
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->exportQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            $commonService=new CommonService();
            
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
                    if(trim($aRow['booking_date_time'])!=""){
						$expBookDate=explode(" ",$aRow['booking_date_time']);
                        $aRow['booking_date_time']=$commonService->humanDateFormat($expBookDate[0]);
                    }
					if(trim($aRow['cancellation_time'])!=""){
						$expBookDate=explode(" ",$aRow['cancellation_time']);
                        $aRow['cancellation_time']=$commonService->humanDateFormat($expBookDate[0])." ".$expBookDate[1];
                    }

                    if($aRow['package']=='chargeable'){
						$aRow['package']="Chargeable";
                    }else if($aRow['package']=='complimentary'){
						$aRow['package']="Complimentary";
                    }else if($aRow['package']=='non_revenue'){
						$aRow['package']="Non Revenue";
                    }
                    else if($aRow['package']=='comp'){
						$aRow['package']="Comp";
					}else if($aRow['package']=='fb_odc'){
						$aRow['package']="F&B-ODC";
                    }else if($aRow['package']=='gm'){
						$aRow['package']="GM";
                    }else if($aRow['package']=='owner'){
						$aRow['package']="Owner";
                    }else if($aRow['package']=='staff_drop'){
						$aRow['package']="Staff Drop";
                    }else if($aRow['package']=='fo'){
						$aRow['package']="FO";
                    }else if($aRow['package']=='hk'){
						$aRow['package']="HK";
                    }else if($aRow['package']=='fb'){
						$aRow['package']="F&B";
                    }else if($aRow['package']=='kitchen'){
						$aRow['package']="Kitchen";
                    }else if($aRow['package']=='hr'){
						$aRow['package']="HR";
                    }else if($aRow['package']=='finance'){
						$aRow['package']="Finance";
                    }else if($aRow['package']=='purchase'){
						$aRow['package']="Purchase";
                    }else if($aRow['package']=='it'){
						$aRow['package']="IT";
                    }else if($aRow['package']=='lp'){
						$aRow['package']="LP";
                    }else if($aRow['package']=='sales'){
						$aRow['package']="Sales";
                    }else if($aRow['package']=='engg'){
						$aRow['package']="Engg";
                    }
                    
                    //<---- Booking given by
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
					//-------------- End ---->
					
					
                    
                    $row[] = $aRow['booking_date_time'];
                    $row[] = ucwords($aRow['guest_name']);
					$row[] = $aRow['room_no'];
					$row[] = $aRow['type_name'];
					$row[] = $aRow['booking_given_by'];
                    $row[] = ucfirst($aRow['vehicle_type']);
                    $row[] = $aRow['vehicle_no'];
                    $row[] = $aRow['driver_name'];
                    $row[] = $aRow['pickup_place'];
                    $row[] = $aRow['drop_place'];
					$row[] = $aRow['trip_sheet_no'];
                    $row[] = $aRow['trip_time'];
                    $row[] = $aRow['end_time'];
                    $row[] = $aRow['total_hrs'];
                    $row[] = $aRow['start_kms'];
                    $row[] = $aRow['close_kms'];
                    $row[] = $aRow['total_kms'];
					$row[] = ucfirst($aRow['package']);
					$row[] = $aRow['revenue'];
                    $row[] = $aRow['parking'];
                    $row[] = $aRow['toll'];
                    $row[] = $aRow['permit'];
                    $row[] = $aRow['driver_allowance_day'];
                    
                    $row[] = $aRow['hotel_bill_amount'];
                    $row[] = $aRow['vendor_bill_amount'];
                    $row[] = $aRow['bc_revenue'];
                    $row[] = ucwords($aRow['payment_status']);
                    
                    $output[] = $row;
               }
            }
            
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                    'size'=>12,
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                )
            );
            
           $borderStyle = array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                )
            );
           
            
            $sheet->setCellValue('A1', html_entity_decode('FPS Hotel Booking List', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            if(isset($params['fromDate']) && trim($params['fromDate'])!='' && trim($params['toDate'])!=""){
				$sheet->setCellValue('A2', html_entity_decode('Date Range : '.$params['fromDate'].' to '.$params['toDate'], ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
             }
			
           
            $sheet->setCellValue('A4', html_entity_decode('Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Guest Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Room Number', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('D4', html_entity_decode('Booking Type', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Booking By', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Vehicle Type', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Vehicle Number', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Driver Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Pickup', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('Drop Place', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('K4', html_entity_decode('Trip Sheet No', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('L4', html_entity_decode('Start Time', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('M4', html_entity_decode('End Time', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('N4', html_entity_decode('Total Hrs', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('O4', html_entity_decode('Start Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('P4', html_entity_decode('End Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('Q4', html_entity_decode('Total Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('R4', html_entity_decode('Package', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('S4', html_entity_decode('Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('T4', html_entity_decode('Parking', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('U4', html_entity_decode('Toll', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('V4', html_entity_decode('Permit', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('W4', html_entity_decode('Driver Allowance', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
			$sheet->setCellValue('X4', html_entity_decode('Hotel Bill Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Y4', html_entity_decode('Vendor Bill Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Z4', html_entity_decode('BC Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('AA4', html_entity_decode('Payment Status', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
            $sheet->getStyle('A1:B1')->getFont()->setBold(TRUE)->setSize(16);
            
            
            $sheet->getStyle('A4')->applyFromArray($styleArray);
            $sheet->getStyle('B4')->applyFromArray($styleArray);
            $sheet->getStyle('C4')->applyFromArray($styleArray);
            $sheet->getStyle('D4')->applyFromArray($styleArray);
            $sheet->getStyle('E4')->applyFromArray($styleArray);
            $sheet->getStyle('F4')->applyFromArray($styleArray);
            $sheet->getStyle('G4')->applyFromArray($styleArray);
            $sheet->getStyle('H4')->applyFromArray($styleArray);
            $sheet->getStyle('I4')->applyFromArray($styleArray);
            $sheet->getStyle('J4')->applyFromArray($styleArray);
            $sheet->getStyle('K4')->applyFromArray($styleArray);
            $sheet->getStyle('L4')->applyFromArray($styleArray);
            $sheet->getStyle('M4')->applyFromArray($styleArray);
            $sheet->getStyle('N4')->applyFromArray($styleArray);
            $sheet->getStyle('O4')->applyFromArray($styleArray);
            $sheet->getStyle('P4')->applyFromArray($styleArray);
            $sheet->getStyle('Q4')->applyFromArray($styleArray);
            $sheet->getStyle('R4')->applyFromArray($styleArray);
            $sheet->getStyle('S4')->applyFromArray($styleArray);
            $sheet->getStyle('T4')->applyFromArray($styleArray);
            $sheet->getStyle('U4')->applyFromArray($styleArray);
            $sheet->getStyle('V4')->applyFromArray($styleArray);
            $sheet->getStyle('W4')->applyFromArray($styleArray);
            $sheet->getStyle('X4')->applyFromArray($styleArray);
            $sheet->getStyle('Y4')->applyFromArray($styleArray);
            $sheet->getStyle('Z4')->applyFromArray($styleArray);
            $sheet->getStyle('AA4')->applyFromArray($styleArray);
            
            
            foreach ($output as $rowNo => $rowData) {
                $colNo = 0;
                foreach ($rowData as $field => $value) {
                    if (!isset($value)) {
                        $value = "";
                    }
                    if (is_numeric($value)) {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 5)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 5)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
                    }
                    $rRowCount = $rowNo + 5;
                    $cellName = $sheet->getCellByColumnAndRow($colNo, $rowNo + 5)->getColumn();
                    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                    $sheet->getDefaultRowDimension()->setRowHeight(18);
                    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
                    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 5)->getAlignment()->setWrapText(true);
                    $colNo++;
                }
            }
	    
            $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $filename = 'fps-hotel-booking-details-' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-HOTEL-BOOKING-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function getCymTariffAmt($params){
        $cymTariffDb = $this->sm->get('CymTariffTable');
        return $cymTariffDb->getCymTariffAmtDetails($params);
    }

    public function addCymHotelBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelBookingsTable');
            $result = $hotelDb->addCymHotelBookingDetails($params);
            if($result>0){
				$adapter->commit();
				$alertContainer = new Container('alert');
				$alertContainer->alertMsg = 'Booking details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function getAllCymHotelBookingReports($parameters){
        $hotelDb = $this->sm->get('HotelBookingsTable');
		$acl = $this->sm->get('AppAcl');
        return $hotelDb->fetchAllCymHotelBookingReports($parameters,$acl);
    }

    public function getCymHotelBooking($bookingId){
        $hotelDb = $this->sm->get('HotelBookingsTable');
        return $hotelDb->fetchCymHotelBooking($bookingId);
    }

    public function updateCymHotelBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $hotelDb = $this->sm->get('HotelBookingsTable');
            $result = $hotelDb->updateCymHotelBookingDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Booking details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }

    public function exportCymHotelBookingDetails($params)
    {
        try{
            $queryContainer = new Container('query');
            $excel = new PHPExcel();
            $cacheMethod = \PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
            $cacheSettings = array('memoryCacheSize' => '80MB');
            \PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
            $output = array();
            $sheet = $excel->getActiveSheet();
            $dbAdapter = $this->sm->get('Zend\Db\Adapter\Adapter');
            $sql = new Sql($dbAdapter);
            $searchDate="";
            if(isset($queryContainer->cymExportQuery)){
				$sQuery=$queryContainer->cymExportQuery->order("booking_id ASC");
			}
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->cymExportQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            $commonService=new CommonService();
            
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
                    if(trim($aRow['booking_date_time'])!=""){
						$expBookDate=explode(" ",$aRow['booking_date_time']);
                        $aRow['booking_date_time']=$commonService->humanDateFormat($expBookDate[0]);
                    }

                    if($aRow['package']=='bth'){
						$aRow['package']="BTH";
                    }else if($aRow['package']=='btr'){
						$aRow['package']="BTR";
                    }else if($aRow['package']=='non_revenue'){
						$aRow['package']="Non Revenue";
                    }
                   
                    
                    //<---- Booking given by
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
					//-------------- End ---->
					
                    $row[] = $aRow['booking_no'];
                    $row[] = $aRow['booking_date_time'];
                    $row[] = ucwords($aRow['guest_name']);
					$row[] = $aRow['room_no'];
					$row[] = $aRow['type_name'];
					$row[] = $aRow['booking_given_by'];
                    $row[] = ucfirst($aRow['vehicle_type']);
                    $row[] = $aRow['vehicle_no'];
                    $row[] = $aRow['driver_name'];
                    $row[] = ucfirst($aRow['package']);
                    $row[] = $aRow['trip_time'];
                    $row[] = $aRow['end_time'];
                    $row[] = $aRow['total_hrs'];
                    $row[] = $aRow['start_kms'];
                    $row[] = $aRow['close_kms'];
                    $row[] = $aRow['total_kms'];
                    $row[] = $aRow['tariff_name'];
                    $row[] = $aRow['extra_hrs'];
                    $row[] = $aRow['extra_hrs_amt'];
                    $row[] = $aRow['extra_kms'];
                    $row[] = $aRow['extra_kms_amt'];
                    $row[] = $aRow['tariff_amount'];
                    $row[] = $aRow['parking'];
                    $row[] = $aRow['toll'];
                    $row[] = $aRow['permit'];
                    $row[] = $aRow['driver_allowance_day'];
                    $row[] = $aRow['driver_allowance_night'];
                    $row[] = $aRow['service_tax_amt'];
                    $row[] = $aRow['total_amount'];
                    $row[] = $aRow['bc_revenue'];
                    $row[] = $aRow['vendor_bill_amount'];
                    
                    $output[] = $row;
               }
            }
            
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                    'size'=>12,
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                )
            );
            
           $borderStyle = array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    ),
                )
            );
           
            
            $sheet->setCellValue('A1', html_entity_decode('CYM Hotel Booking List', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            if(isset($params['fromDate']) && trim($params['fromDate'])!='' && trim($params['toDate'])!=""){
				$sheet->setCellValue('A2', html_entity_decode('Date Range : '.$params['fromDate'].' to '.$params['toDate'], ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
             }
			
            $sheet->setCellValue('A4', html_entity_decode('Booking Reference', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Booking Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Guest Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Room Number', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('E4', html_entity_decode('Booking Type', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Booking By', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Vehicle Type', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Vehicle Number', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Driver Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('Package', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('K4', html_entity_decode('Start Time', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('L4', html_entity_decode('End Time', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('M4', html_entity_decode('Total Hrs', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('N4', html_entity_decode('Start Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('O4', html_entity_decode('End Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('P4', html_entity_decode('Total Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Q4', html_entity_decode('Tariff', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('R4', html_entity_decode('Extra Hrs', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('S4', html_entity_decode('Extra Hrs Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('T4', html_entity_decode('Extra Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('U4', html_entity_decode('Extra Kms Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('V4', html_entity_decode('Package Rate', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);

			$sheet->setCellValue('W4', html_entity_decode('Parking', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('X4', html_entity_decode('Toll', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('Y4', html_entity_decode('Permit', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Z4', html_entity_decode('Driver Allowance', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('AA4', html_entity_decode('Driver Allowance In Night', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('AB4', html_entity_decode('Tax Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('AC4', html_entity_decode('Total Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('AD4', html_entity_decode('BC Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('AE4', html_entity_decode('Vendor Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
            $sheet->getStyle('A1:B1')->getFont()->setBold(TRUE)->setSize(16);
            
            
            $sheet->getStyle('A4')->applyFromArray($styleArray);
            $sheet->getStyle('B4')->applyFromArray($styleArray);
            $sheet->getStyle('C4')->applyFromArray($styleArray);
            $sheet->getStyle('D4')->applyFromArray($styleArray);
            $sheet->getStyle('E4')->applyFromArray($styleArray);
            $sheet->getStyle('F4')->applyFromArray($styleArray);
            $sheet->getStyle('G4')->applyFromArray($styleArray);
            $sheet->getStyle('H4')->applyFromArray($styleArray);
            $sheet->getStyle('I4')->applyFromArray($styleArray);
            $sheet->getStyle('J4')->applyFromArray($styleArray);
            $sheet->getStyle('K4')->applyFromArray($styleArray);
            $sheet->getStyle('L4')->applyFromArray($styleArray);
            $sheet->getStyle('M4')->applyFromArray($styleArray);
            $sheet->getStyle('N4')->applyFromArray($styleArray);
            $sheet->getStyle('O4')->applyFromArray($styleArray);
            $sheet->getStyle('P4')->applyFromArray($styleArray);
            $sheet->getStyle('Q4')->applyFromArray($styleArray);
            $sheet->getStyle('R4')->applyFromArray($styleArray);
            $sheet->getStyle('S4')->applyFromArray($styleArray);
            $sheet->getStyle('T4')->applyFromArray($styleArray);
            $sheet->getStyle('U4')->applyFromArray($styleArray);
            $sheet->getStyle('V4')->applyFromArray($styleArray);
            $sheet->getStyle('W4')->applyFromArray($styleArray);
            $sheet->getStyle('X4')->applyFromArray($styleArray);
            $sheet->getStyle('Y4')->applyFromArray($styleArray);
            $sheet->getStyle('Z4')->applyFromArray($styleArray);
            $sheet->getStyle('AA4')->applyFromArray($styleArray);
            $sheet->getStyle('AB4')->applyFromArray($styleArray);
            $sheet->getStyle('AC4')->applyFromArray($styleArray);
            $sheet->getStyle('AD4')->applyFromArray($styleArray);
            $sheet->getStyle('AE4')->applyFromArray($styleArray);

            foreach ($output as $rowNo => $rowData) {
                $colNo = 0;
                foreach ($rowData as $field => $value) {
                    if (!isset($value)) {
                        $value = "";
                    }
                    if (is_numeric($value)) {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 5)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    } else {
                        $sheet->getCellByColumnAndRow($colNo, $rowNo + 5)->setValueExplicit(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
                    }
                    $rRowCount = $rowNo + 5;
                    $cellName = $sheet->getCellByColumnAndRow($colNo, $rowNo + 5)->getColumn();
                    $sheet->getStyle($cellName . $rRowCount)->applyFromArray($borderStyle);
                    $sheet->getDefaultRowDimension()->setRowHeight(18);
                    $sheet->getColumnDimensionByColumn($colNo)->setWidth(20);
                    $sheet->getStyleByColumnAndRow($colNo, $rowNo + 5)->getAlignment()->setWrapText(true);
                    $colNo++;
                }
            }
	    
            $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
            $filename = 'cym-hotel-booking-details-' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-CYM-HOTEL-BOOKING-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
}
?>

