<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;
use PHPExcel;

class RetailService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addRetailTarget($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('RetailTable');
            $result = $db->addRetailTargetDetails($params);
            if($result>0){
                $adapter->commit();
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'Retail target details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateRetailTarget($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('RetailTable');
            $result = $db->updateRetailTargetDetails($params);
            if($result>0){
                $adapter->commit();
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'Retail target details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllRetailTargetDetails($parameters){
        $db = $this->sm->get('RetailTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllRetailTargetDetails($parameters,$acl);
    }
    
    public function addRetailDailySales($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('RetailDailySalesTable');
            $result = $db->addRetailDailySalesDetails($params);
            if($result>0){
                $adapter->commit();
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'Retail daily sales details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateRetailDailySales($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('RetailDailySalesTable');
            $result = $db->updateRetailDailySalesDetails($params);
            if($result>0){
                $adapter->commit();
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'Retail daily sales details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllRetailDailySales($parameters){
        $db = $this->sm->get('RetailDailySalesTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllRetailDailySales($parameters,$acl);
    }
    
    public function getRetailDailySales($id){
        $db = $this->sm->get('RetailDailySalesTable');
        return $db->fetchRetailDailySales($id);
    }
    
    public function getRetailTarget($id){
        $db = $this->sm->get('RetailTable');
        return $db->fetchRetailTarget($id);
    }
    
    public function exportRetailDailySales($params)
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
                    
                    $row[] = $commonService->humanDateFormat($aRow['sales_date']);
                    $row[] = $aRow['total_trip'];
                    $row[] = $aRow['total_revenue'];
                    $row[] = $aRow['daily_target'];
                    $row[] = $aRow['monthly_target'];
                    
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
           
            $sheet->setCellValue('A1', html_entity_decode('Retail Daily Sales ', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('A2', html_entity_decode($searchDate, ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
           
            $sheet->setCellValue('A4', html_entity_decode('Sales Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Total Trip', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Total Revenue', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Daily Target', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Monthly Target', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
            $sheet->getStyle('A1:B1')->getFont()->setBold(TRUE)->setSize(16);
            $sheet->getStyle('A2:B2')->getFont()->setBold(TRUE)->setSize(13);
            
            $sheet->getStyle('A4')->applyFromArray($styleArray);
            $sheet->getStyle('B4')->applyFromArray($styleArray);
            $sheet->getStyle('C4')->applyFromArray($styleArray);
            $sheet->getStyle('D4')->applyFromArray($styleArray);
            $sheet->getStyle('E4')->applyFromArray($styleArray);
            
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
            $filename = 'retail-daily-sales-report' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-RETAIL-DAILY-SALES-REPORT-EXCEL-" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
}
?>

