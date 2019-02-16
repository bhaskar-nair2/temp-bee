<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;
use PHPExcel;

class FuelService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addFuel($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $fuelDb = $this->sm->get('FuelTable');
            $result = $fuelDb->addFuelDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Fuel details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateFuel($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $fuelDb = $this->sm->get('FuelTable');
            $result = $fuelDb->updateFuelDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Fuel details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllFuelReports($parameters){
        $fuelDb = $this->sm->get('FuelTable');
        $acl = $this->sm->get('AppAcl');
        return $fuelDb->fetchAllFuelReports($parameters,$acl);
    }
    
    public function getFuelDetails($fuelId){
        $fuelDb = $this->sm->get('FuelTable');
        return $fuelDb->fetchFuelDetails($fuelId);
    }
    
    public function getLastFuelInfo($vehicleId){
        $fuelDb = $this->sm->get('FuelTable');
        return $fuelDb->fetchLastFuelInfo($vehicleId);
    }
    
    public function exportFuelDetails($params)
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
				$sQuery=$queryContainer->exportQuery->order("fuel_id ASC");
			}
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->exportQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            $commonService=new CommonService();
            
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
                    if(trim($aRow['fuel_date'])!=""){
                        $aRow['fuel_date']=$commonService->humanDateFormat($aRow['fuel_date']);
                    }
                    if(trim($aRow['last_fuel_date'])!=""){
                        $aRow['last_fuel_date']=$commonService->humanDateFormat($aRow['last_fuel_date']);
                    }
					
					if(trim($aRow['total_kms'])==""){
						if($aRow['current_kms']>$aRow['last_fuel_kms']){
								$aRow['total_kms']=$aRow['current_kms']-$aRow['last_fuel_kms'];
						}
					}
					
                    $row[] = $aRow['fuel_date'];
                    $row[] = $aRow['vehicle_no'];
					$row[] = $aRow['vehicle_mode'];
                    $row[] = ucwords($aRow['pump_name']);
                    $row[] = ucwords($aRow['driver_name']);
					$row[] = $aRow['last_fuel_kms'];
					$row[] = $aRow['last_fuel_date'];
                    $row[] = $aRow['current_kms'];
					$row[] = $aRow['total_kms'];                    
					$row[] = $aRow['quantity'];
					$row[] = $aRow['amount_per_ltr'];
					$row[] = $aRow['mileage_per_litre'];
                    $row[] = ucwords($aRow['total_amount']);
					$row[] = ucwords($aRow['payment_type']);
                    
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
           
            
            $sheet->setCellValue('A1', html_entity_decode('Fuel List', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            if(isset($params['fromDate']) && trim($params['fromDate'])!='' && trim($params['toDate'])!=""){
				$sheet->setCellValue('A2', html_entity_decode('Date Range : '.$params['fromDate'].' to '.$params['toDate'], ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
             }
			
           
            $sheet->setCellValue('A4', html_entity_decode('Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Vehicle Number', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('Vehicle Type', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Petrol Pump', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Driver Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('Last Fuel Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('Last Fuel Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Current Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Total Kms', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('J4', html_entity_decode('Quantity', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('K4', html_entity_decode('Amount Per Litre', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('L4', html_entity_decode('Mileage', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('M4', html_entity_decode('Total Amount', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('N4', html_entity_decode('Payment Mode', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
            
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
            $filename = 'fuel-details-' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-HOTEL-BOOKING-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
}
?>

