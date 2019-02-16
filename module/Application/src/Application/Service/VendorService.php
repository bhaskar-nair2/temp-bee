<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;
use PHPExcel;

class VendorService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addVendor($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vendorDb = $this->sm->get('VendorsTable');
            $result = $vendorDb->addVendorDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Vendor details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateVendor($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vendorDb = $this->sm->get('VendorsTable');
            $result = $vendorDb->updateVendorDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Vendor details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function addVendorPayment($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
            $result = $vendorPaymentsDb->addVendorPaymentDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Vendor payment details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateVendorPayment($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
            $result = $vendorPaymentsDb->updateVendorPaymentDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Vendor payment details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllVendors($parameters){
        $vendorDb = $this->sm->get('VendorsTable');
        $acl = $this->sm->get('AppAcl');
        return $vendorDb->fetchAllVendors($parameters,$acl);
    }
    
    public function getAllVendorPendingPayment($parameters){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        $acl = $this->sm->get('AppAcl');
        return $vendorPaymentsDb->fetchAllVendorPendingPayment($parameters,$acl);
    }
    
    public function getAllVendorCompletedPayment($parameters){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        $acl = $this->sm->get('AppAcl');
        return $vendorPaymentsDb->fetchAllVendorCompletedPayment($parameters,$acl);
    }
    
    public function getVendor($vendorId){
        $vendorDb = $this->sm->get('VendorsTable');
        return $vendorDb->getVendorDetails($vendorId);
    }
    
    public function getVendorCode(){
        $vendorDb = $this->sm->get('VendorsTable');
        return $vendorDb->fetchVendorCode();
    }
    
    public function getAllActiveVendor(){
        $vendorDb = $this->sm->get('VendorsTable');
        return $vendorDb->fetchAllActiveVendor();
    }
    
    public function getVendorPayment($paymentId){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        return $vendorPaymentsDb->getVendorPaymentDetails($paymentId);
    }
    
    public function fetchVendorPayment($paymentId){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        return $vendorPaymentsDb->fetchVendorPaymentDetails($paymentId);
    }
    
    public function getVendorPaymentCode(){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        return $vendorPaymentsDb->fetchVendorPaymentCode();
    }
    
    public function exportVendorPayments($params)
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
            //$commonService=new CommonService();
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
                    
                    $row[] = ucwords($aRow['vendor_name']);
                    $row[] = $aRow['vendor_no'];
                    $row[] = ucwords($aRow['vendor_type']);
                    $row[] = $aRow['paymentDate'];
                    $row[] = $aRow['hotel_revenue'];
                    $row[] = $aRow['corporate_revenue'];
                    $row[] = $aRow['retail_revenue'];
                    $row[] = $aRow['total_revenue'];
                    $row[] = $aRow['fuel_amount'];
                    $row[] = $aRow['fuel_surcharge'];
                    $row[] = $aRow['advance'];
                    $row[] = $aRow['other_deductions'];
                    $row[] = $aRow['total_deductions'];
                    $row[] = $aRow['hotel_payment'];
                    $row[] = $aRow['corp_payment'];
                    $row[] = $aRow['retail_payment'];
                    $row[] = $aRow['parking_amount'];
                    $row[] = ucwords($aRow['service_tax_applicable']);
                    $row[] = $aRow['service_tax_amount'];
                    $row[] = $aRow['total_payment'];
                    $row[] = ucwords($aRow['tds_applicable']);
                    $row[] = $aRow['tds_amount'];
                    $row[] = $aRow['net_amount'];
                    $row[] = $aRow['company_revenue'];
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
           
            $sheet->setCellValue('A1', html_entity_decode('Vendor Payment Details ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($searchDate, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
           
            $sheet->setCellValue('A4', html_entity_decode('Vendor Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Vendor Number', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Vendor Type', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Payment Month & Year', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Hotel Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Corporate Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Retail Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Total Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Fuel Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('Fuel Surcharge', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('K4', html_entity_decode('Advance', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('L4', html_entity_decode('Other Deductions', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('M4', html_entity_decode('Total Deductions', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('N4', html_entity_decode('Hotel Payment', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('O4', html_entity_decode('Corporate Payment', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('P4', html_entity_decode('Retail Payment', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Q4', html_entity_decode('Parking Amount ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('R4', html_entity_decode('Service Tax Applicable ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('S4', html_entity_decode('Service Tax Amount ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('T4', html_entity_decode('Total Amount ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('U4', html_entity_decode('TDS Applicable', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('V4', html_entity_decode('TDS Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('W4', html_entity_decode('Net Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('X4', html_entity_decode('Net Company Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Y4', html_entity_decode('Status', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
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
            $filename = 'vendor-payment-report' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-VENDOR-PAYMENT-REPORT-EXCEL-" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function makeVendorPayment($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vendorPaidDb = $this->sm->get('VendorPaidDetailsTable');
            $result = $vendorPaidDb->makeVendorPaymentDetails($params);
            if($result>0){
             $adapter->commit();
             return $result;
             //$alertContainer = new Container('alert');
             //$alertContainer->alertMsg = 'Paid vendor payment successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getVendorPaidDetails($paymentId){
        $vendorPaymentsDb = $this->sm->get('VendorPaidDetailsTable');
        return $vendorPaymentsDb->fetchVendorPaidDetails($paymentId);
    }
    
    public function exportCompleteVendorPayments($params)
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
            //$commonService=new CommonService();
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
                    
                    $row[] = ucwords($aRow['vendor_name']);
                    $row[] = $aRow['vendor_no'];
                    $row[] = ucwords($aRow['vendor_type']);
                    $row[] = $aRow['paymentDate'];
                    $row[] = $aRow['hotel_revenue'];
                    $row[] = $aRow['corporate_revenue'];
                    $row[] = $aRow['retail_revenue'];
                    $row[] = $aRow['total_revenue'];
                    $row[] = $aRow['fuel_amount'];
                    $row[] = $aRow['fuel_surcharge'];
                    $row[] = $aRow['advance'];
                    $row[] = $aRow['other_deductions'];
                    $row[] = $aRow['total_deductions'];
                    $row[] = $aRow['hotel_payment'];
                    $row[] = $aRow['corp_payment'];
                    $row[] = $aRow['retail_payment'];
                    $row[] = $aRow['parking_amount'];
                    $row[] = ucwords($aRow['service_tax_applicable']);
                    $row[] = $aRow['service_tax_amount'];
                    $row[] = $aRow['total_payment'];
                    $row[] = ucwords($aRow['tds_applicable']);
                    $row[] = $aRow['tds_amount'];
                    $row[] = $aRow['net_amount'];
                    $row[] = $aRow['company_revenue'];
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
           
            $sheet->setCellValue('A1', html_entity_decode('Vendor Payment Details ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($searchDate, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
           
            $sheet->setCellValue('A4', html_entity_decode('Vendor Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Vendor Number', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Vendor Type', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Payment Month & Year', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Hotel Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Corporate Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Retail Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Total Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Fuel Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('Fuel Surcharge', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('K4', html_entity_decode('Advance', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('L4', html_entity_decode('Other Deductions', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('M4', html_entity_decode('Total Deductions', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('N4', html_entity_decode('Hotel Payment', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('O4', html_entity_decode('Corporate Payment', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('P4', html_entity_decode('Retail Payment', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Q4', html_entity_decode('Parking Amount ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('R4', html_entity_decode('Service Tax Applicable ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('S4', html_entity_decode('Service Tax Amount ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('T4', html_entity_decode('Total Amount ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('U4', html_entity_decode('TDS Applicable', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('V4', html_entity_decode('TDS Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('W4', html_entity_decode('Net Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('X4', html_entity_decode('Net Company Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('Y4', html_entity_decode('Status', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
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
            $filename = 'vendor-payment-report' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-VENDOR-PAYMENT-REPORT-EXCEL-" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function deleteVendorPayment($paymentId){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        return $vendorPaymentsDb->deleteVendorPayment($paymentId);
    }
    
    public function getVendorInfo($vendorId){
        $vendorDb = $this->sm->get('VendorsTable');
        return $vendorDb->fetchVendorInfo($vendorId);
    }
    
    public function addVendorRental($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vendorRentalsDb = $this->sm->get('VendorRentalsTable');
            $result = $vendorRentalsDb->addVendorRentalDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Vendor rental details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateVendorRental($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vendorRentalsDb = $this->sm->get('VendorRentalsTable');
            $result = $vendorRentalsDb->updateVendorRentalDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Vendor rental details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getVendorRentals($vendorId){
        $vendorDb = $this->sm->get('VendorsTable');
        return $vendorDb->getVendorRentalDetails($vendorId);
    }
    
    public function getAllVendorPayments($parameters){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        $acl = $this->sm->get('AppAcl');
        return $vendorPaymentsDb->fetchAllVendorPayments($parameters,$acl);
    }
    
    public function getAllActiveVendorBasedCity($city){
        $vendorDb = $this->sm->get('VendorsTable');
        return $vendorDb->fetchAllActiveVendorBasedCity($city);
    }
    
    public function getVehicleNoByVendor($vendor){
        $vendorDb = $this->sm->get('VendorsTable');
        return $vendorDb->fetchVehicleNoByVendor($vendor);
    }
    
    public function getAllVendorLedgers($parameters){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        $acl = $this->sm->get('AppAcl');
        return $vendorPaymentsDb->fetchAllVendorLedgerPayments($parameters,$acl);
    }
    
    public function getVendorPaymentList($parameters){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        $acl = $this->sm->get('AppAcl');
        return $vendorPaymentsDb->fetchVendorPaymentList($parameters,$acl);
    }
    
    public function getVendorPaymentReports($vendorId,$fDate,$tDate){
        $vendorPaymentsDb = $this->sm->get('VendorPaymentsTable');
        return $vendorPaymentsDb->fetchVendorPaymentReports($vendorId,$fDate,$tDate);
    }
    
    public function addVendorAdvance($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $vendorAdvanceDb = $this->sm->get('VendorAdvanceTable');
            $result = $vendorAdvanceDb->addVendorAdvanceDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Vendor advance details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getVendorPendingPayments(){
        $vendorPaymentsDb = $this->sm->get('VendorsTable');
        return $vendorPaymentsDb->fetchVendorPendingPayments();
    }
}
?>

