<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;
use PHPExcel;

class BillingService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addMonthlyBill($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('MonthlyBillingTable');
            $result = $db->addMonthlyBillDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Monthly bill details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
	
	public function updateMonthlyBill($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('MonthlyBillingTable');
            $result = $db->updateMonthlyBillDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Monthly bill details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
	public function getAllCompletedMonthlyBills($parameters){
        $db = $this->sm->get('MonthlyBillingTable');
		$acl = $this->sm->get('AppAcl');
        return $db->fetchAllCompletedMonthlyBills($parameters,$acl);
    }
	
	public function getBillingDetails($billingId){
		$db = $this->sm->get('MonthlyBillingTable');
        return $db->fetchBillingDetails($billingId);
	}
	
	public function getMonthlyBillingDetails($billingId){
		$db = $this->sm->get('MonthlyBillingTable');
        return $db->fetchMonthlyBillingDetails($billingId);
	}
	
	public function getInvoiceNoBasedOnCompany($companyId){
		$db = $this->sm->get('MonthlyBillingTable');
        return $db->fetchInvoiceNoBasedOnCompany($companyId);
	}
	
	public function getAllPendingMonthlyBills($parameters){
        $db = $this->sm->get('MonthlyBillingTable');
		$acl = $this->sm->get('AppAcl');
        return $db->fetchAllPendingMonthlyBills($parameters,$acl);
    }
	
	public function deleteMonthlyBill($billId){
		$db = $this->sm->get('MonthlyBillingTable');
        return $db->deleteMonthlyBillDetails($billId);
	}
	
	public function getPendingPaymentList(){
		$db = $this->sm->get('MonthlyBillingTable');
        return $db->fetchPendingPaymentList();
	}
	
	public function exportPendingMonthlyBill($params)
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
            
            if (isset($params['startDate']) && trim($params['startDate'])!= "" && isset($params['endDate']) && trim($params['endDate'])!="") {
                $searchDate="Date : ".$params['startDate']." to ".$params['endDate'];
            }
            
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->exportQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            $commonService=new CommonService();
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
                    
					$otherAmount=0;
					$query = $sql->select()->from('other_amount_details')->where(array('monthly_bill_id'=>$aRow['month_bill_id']));
					$queryStr = $sql->getSqlStringForSqlObject($query);
					$rResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
					if(count($rResult) > 0) {
					foreach($rResult as $val) {
						$otherAmount+=$val['other_amount'];
					}
					}
					
                    $row[] = $aRow['invoice_no'];
                    $row[] = $aRow['invoice_month_year'];
                    $row[] = $commonService->humanDateFormat($aRow['invoice_date']);
                    $row[] = $aRow['company_name'];
                    $row[] = $aRow['client_name'];
                    $row[] = $aRow['unit_name'];
					$row[] = $aRow['basic_amount'];
                    $row[] = $aRow['parking_fee'];
                    $row[] = $aRow['driver_retention'];
                    $row[] = $otherAmount;
					if(trim($aRow['service_tax'])!=""){
						$service_tax=($aRow['service_tax']/100);
						$seerviceTaxAmount=round(($aRow['total_amount']*$service_tax),2);
						$row[] = $seerviceTaxAmount;
					}else{
						$row[] = '';	
					}
					
					if(trim($aRow['sbc_tax'])!=""){
						$sbc_tax=($aRow['sbc_tax']/100);
						$sbcAmount=round(($aRow['total_amount']*$sbc_tax),2);
						$row[] = $sbcAmount;
					}else{
						$row[] = '';	
					}
					
					if(trim($aRow['kkc_tax'])!=""){
						$kkc_tax=($aRow['kkc_tax']/100);
						$kkcTaxAmount=round(($aRow['total_amount']*$kkc_tax),2);
						$row[] = $kkcTaxAmount;
					}else{
						$row[] = '';	
					}
					
                    $row[] = round($aRow['net_amount']);
					
                    $row[] = $commonService->humanDateFormat($aRow['payment_due_date']);
                    
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
           
            $sheet->setCellValue('A1', html_entity_decode('Pending Monthly Bill ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($searchDate, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
           
            $sheet->setCellValue('A4', html_entity_decode('Invoice No', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Invoice Month & Year', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Invoice Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Company Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Client', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Business Unit', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Basic Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Toll/Parking', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Driver Retention', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('Other Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('K4', html_entity_decode('Service Tax', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('L4', html_entity_decode('SBC Tax', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('M4', html_entity_decode('KKC Tax', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('N4', html_entity_decode('Total Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->setCellValue('O4', html_entity_decode('Payment Due Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
            $sheet->getStyle('A1:B1')->getFont()->setBold(TRUE)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(TRUE)->setSize(13);
            
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
            $filename = 'pending-monthly-bill-report' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-PENDING-MONTHLY-BILL-REPORT-EXCEL-" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
	
	public function exportCompletedMonthlyBill($params)
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
            
            if (isset($params['startDate']) && trim($params['startDate'])!= "" && isset($params['endDate']) && trim($params['endDate'])!="") {
                $searchDate="Date : ".$params['startDate']." to ".$params['endDate'];
            }
            
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->exportQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            $commonService=new CommonService();
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
					$otherAmount=0;
					$query = $sql->select()->from('other_amount_details')->where(array('monthly_bill_id'=>$aRow['month_bill_id']));
					$queryStr = $sql->getSqlStringForSqlObject($query);
					$rResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
					if(count($rResult) > 0) {
					foreach($rResult as $val) {
						$otherAmount+=$val['other_amount'];
					}
					}
                    $row[] = $aRow['invoice_no'];
					$row[] = $aRow['invoice_month_year'];
                    $row[] = $commonService->humanDateFormat($aRow['invoice_date']);
                    $row[] = $aRow['company_name'];
                    $row[] = $aRow['client_name'];
                    $row[] = $aRow['unit_name'];
                    $row[] = $aRow['basic_amount'];
                    $row[] = $aRow['parking_fee'];
                    $row[] = $aRow['driver_retention'];
                    $row[] = $otherAmount;
					if(trim($aRow['service_tax'])!=""){
						$service_tax=($aRow['service_tax']/100);
						$seerviceTaxAmount=round(($aRow['total_amount']*$service_tax),2);
						$row[] = $seerviceTaxAmount;
					}else{
						$row[] = '';	
					}
					
					if(trim($aRow['sbc_tax'])!=""){
						$sbc_tax=($aRow['sbc_tax']/100);
						$sbcAmount=round(($aRow['total_amount']*$sbc_tax),2);
						$row[] = $sbcAmount;
					}else{
						$row[] = '';	
					}
					
					if(trim($aRow['kkc_tax'])!=""){
						$kkc_tax=($aRow['kkc_tax']/100);
						$kkcTaxAmount=round(($aRow['total_amount']*$kkc_tax),2);
						$row[] = $kkcTaxAmount;
					}else{
						$row[] = '';	
					}
					
                    $row[] = round($aRow['net_amount']);
                    $row[] = $aRow['tds'];
                    $row[] = $aRow['discount'];
                    $row[] = $aRow['received_amount'];
                    $row[] = $aRow['payment_type'];
                    $row[] = $aRow['remarks'];
                    $row[] = $aRow['employee_name'];
					$row[] = $commonService->humanDateFormat($aRow['payment_received_date']);
                    $row[] = ucwords($aRow['payment_status']);
					$row[] = $aRow['balance'];
                    
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
           
            $sheet->setCellValue('A1', html_entity_decode('Completed Monthly Bill ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($searchDate, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			
			$sheet->getCellByColumnAndRow(0, 4)->setValueExplicit(html_entity_decode('Invoice No', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(1, 4)->setValueExplicit(html_entity_decode('Invoice Month & Year', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(2, 4)->setValueExplicit(html_entity_decode('Invoice Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(3, 4)->setValueExplicit(html_entity_decode('Company Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(4, 4)->setValueExplicit(html_entity_decode('Client', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(5, 4)->setValueExplicit(html_entity_decode('Business Unit', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(6, 4)->setValueExplicit(html_entity_decode('Basic Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(7, 4)->setValueExplicit(html_entity_decode('Toll/Parking', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(8, 4)->setValueExplicit(html_entity_decode('Driver Retention', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(9, 4)->setValueExplicit(html_entity_decode('Other Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(10, 4)->setValueExplicit(html_entity_decode('Service Tax', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(11, 4)->setValueExplicit(html_entity_decode('SBC Tax', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(12, 4)->setValueExplicit(html_entity_decode('KKC Tax', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(13, 4)->setValueExplicit(html_entity_decode('Total Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(14, 4)->setValueExplicit(html_entity_decode('TDS', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(15, 4)->setValueExplicit(html_entity_decode('Discount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(16, 4)->setValueExplicit(html_entity_decode('Received Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(17, 4)->setValueExplicit(html_entity_decode('Payment Mode', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(18, 4)->setValueExplicit(html_entity_decode('Remarks', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(19, 4)->setValueExplicit(html_entity_decode('Invoice Prepared By', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(20, 4)->setValueExplicit(html_entity_decode('Received Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(21, 4)->setValueExplicit(html_entity_decode('Status', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(22, 4)->setValueExplicit(html_entity_decode('Balance Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			
            $sheet->getStyle('A1:B1')->getFont()->setBold(TRUE)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(TRUE)->setSize(13);
            
			$sheet->getStyleByColumnAndRow(0, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(1, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(2, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(3, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(4, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(5, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(6, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(7, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(8, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(9, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(10, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(11, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(12, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(13, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(14, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(15, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(16, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(17, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(18, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(19, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(20, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(21, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(22, 4)->applyFromArray($styleArray);
			
            
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
            $filename = 'completed-monthly-bill-report' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-COMPLETED-MONTHLY-BILL-REPORT-EXCEL-" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
	
	public function getAllMonthlyBills($parameters){
        $db = $this->sm->get('MonthlyBillingTable');
		$acl = $this->sm->get('AppAcl');
        return $db->fetchAllMonthlyBills($parameters,$acl);
    }
	
	public function exportAllMonthlyBill($params)
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
            
            if (isset($params['startDate']) && trim($params['startDate'])!= "" && isset($params['endDate']) && trim($params['endDate'])!="") {
                $searchDate="Date : ".$params['startDate']." to ".$params['endDate'];
            }
            
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->exportAllBillQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            $commonService=new CommonService();
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
					$otherAmount=0;
					$query = $sql->select()->from('other_amount_details')->where(array('monthly_bill_id'=>$aRow['month_bill_id']));
					$queryStr = $sql->getSqlStringForSqlObject($query);
					$rResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
					if(count($rResult) > 0) {
					foreach($rResult as $val) {
						$otherAmount+=$val['other_amount'];
					}
					}
                    $row[] = $aRow['invoice_no'];
					$row[] = $aRow['invoice_month_year'];
                    $row[] = $commonService->humanDateFormat($aRow['invoice_date']);
                    $row[] = $aRow['company_name'];
                    $row[] = $aRow['client_name'];
                    $row[] = $aRow['unit_name'];
                    $row[] = $aRow['basic_amount'];
                    $row[] = $aRow['parking_fee'];
                    $row[] = $aRow['driver_retention'];
                    $row[] = $otherAmount;
					if(trim($aRow['service_tax'])!=""){
						$service_tax=($aRow['service_tax']/100);
						$seerviceTaxAmount=round(($aRow['total_amount']*$service_tax),2);
						$row[] = $seerviceTaxAmount;
					}else{
						$row[] = '';	
					}
					
					if(trim($aRow['sbc_tax'])!=""){
						$sbc_tax=($aRow['sbc_tax']/100);
						$sbcAmount=round(($aRow['total_amount']*$sbc_tax),2);
						$row[] = $sbcAmount;
					}else{
						$row[] = '';	
					}
					
					if(trim($aRow['kkc_tax'])!=""){
						$kkc_tax=($aRow['kkc_tax']/100);
						$kkcTaxAmount=round(($aRow['total_amount']*$kkc_tax),2);
						$row[] = $kkcTaxAmount;
					}else{
						$row[] = '';	
					}
					
                    $row[] = round($aRow['net_amount']);
                    $row[] = $aRow['tds'];
                    $row[] = $aRow['discount'];
                    $row[] = $aRow['received_amount'];
                    $row[] = $aRow['payment_type'];
                    $row[] = $aRow['remarks'];
                    $row[] = $aRow['employee_name'];
					$row[] = $commonService->humanDateFormat($aRow['payment_received_date']);
                    $row[] = ucwords($aRow['payment_status']);
					$row[] = $aRow['balance'];
                    
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
           
            $sheet->setCellValue('A1', html_entity_decode('All Monthly Bill ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($searchDate, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			
			$sheet->getCellByColumnAndRow(0, 4)->setValueExplicit(html_entity_decode('Invoice No', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(1, 4)->setValueExplicit(html_entity_decode('Invoice Month & Year', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(2, 4)->setValueExplicit(html_entity_decode('Invoice Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(3, 4)->setValueExplicit(html_entity_decode('Company Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(4, 4)->setValueExplicit(html_entity_decode('Client', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(5, 4)->setValueExplicit(html_entity_decode('Business Unit', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(6, 4)->setValueExplicit(html_entity_decode('Basic Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(7, 4)->setValueExplicit(html_entity_decode('Toll/Parking', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(8, 4)->setValueExplicit(html_entity_decode('Driver Retention', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(9, 4)->setValueExplicit(html_entity_decode('Other Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(10, 4)->setValueExplicit(html_entity_decode('Service Tax', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(11, 4)->setValueExplicit(html_entity_decode('SBC Tax', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(12, 4)->setValueExplicit(html_entity_decode('KKC Tax', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(13, 4)->setValueExplicit(html_entity_decode('Total Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(14, 4)->setValueExplicit(html_entity_decode('TDS', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(15, 4)->setValueExplicit(html_entity_decode('Discount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(16, 4)->setValueExplicit(html_entity_decode('Received Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(17, 4)->setValueExplicit(html_entity_decode('Payment Mode', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(18, 4)->setValueExplicit(html_entity_decode('Remarks', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(19, 4)->setValueExplicit(html_entity_decode('Invoice Prepared By', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(20, 4)->setValueExplicit(html_entity_decode('Received Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(21, 4)->setValueExplicit(html_entity_decode('Status', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			$sheet->getCellByColumnAndRow(22, 4)->setValueExplicit(html_entity_decode('Balance Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
			
            $sheet->getStyle('A1:B1')->getFont()->setBold(TRUE)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(TRUE)->setSize(13);
            
			$sheet->getStyleByColumnAndRow(0, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(1, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(2, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(3, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(4, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(5, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(6, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(7, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(8, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(9, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(10, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(11, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(12, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(13, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(14, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(15, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(16, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(17, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(18, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(19, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(20, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(21, 4)->applyFromArray($styleArray);
			$sheet->getStyleByColumnAndRow(22, 4)->applyFromArray($styleArray);
			
            
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
            $filename = 'all-monthly-bill-report' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-COMPLETED-MONTHLY-BILL-REPORT-EXCEL-" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
}
?>

