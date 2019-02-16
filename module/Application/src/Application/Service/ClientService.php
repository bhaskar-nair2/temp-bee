<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;
use PHPExcel;

class ClientService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addClient($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $clientDb = $this->sm->get('ClientsTable');
            $result = $clientDb->addClientDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Client details added successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateClient($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $clientDb = $this->sm->get('ClientsTable');
            $result = $clientDb->updateClientDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Client details updated successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllClients($parameters){
        $vendorDb = $this->sm->get('ClientsTable');
        $acl = $this->sm->get('AppAcl');
        return $vendorDb->fetchAllClients($parameters,$acl);
    }
    
    public function getClient($clientId){
        $clientDb = $this->sm->get('ClientsTable');
        return $clientDb->getClientDetails($clientId);
    }
    
    public function getAllActiveClients(){
        $clientDb = $this->sm->get('ClientsTable');
        return $clientDb->fetchAllActiveClients();
    }
    
    public function getContactList($clientId){
        $contactDb = $this->sm->get('ClientContactTable');
        return $contactDb->fetchContactList($clientId);
    }
    
    public function getGuestDetails($clientId){
        $guestDb = $this->sm->get('GuestTable');
        return $guestDb->fetchGuestDetails($clientId);
    }
    
    public function getAllActiveBeecabsClients(){
        $clientDb = $this->sm->get('ClientsTable');
        return $clientDb->fetchAllActiveBeecabClients();
    }
    
    public function getClientByCompany($companyId){
        $clientDb = $this->sm->get('ClientsTable');
        return $clientDb->fetchClientByCompany($companyId);
    }
    
    public function getClientCode(){
        $clientDb = $this->sm->get('ClientsTable');
        return $clientDb->fetchClientCode();
    }
    
    public function getContractExpiryList(){
        $clientDb = $this->sm->get('ClientsTable');
        return $clientDb->getContractExpiryList();
    }
    
    public function fetchClient($clientId){
        $clientDb = $this->sm->get('ClientsTable');
        return $clientDb->fetchClientDetails($clientId);
    }
    
    public function fetchContractExpiryList($parameters){
        $clientDb = $this->sm->get('ClientsTable');
        return $clientDb->fetchContractExpiryList($parameters);
    }
    
    public function getClientPendingPayments(){
        $db = $this->sm->get('MonthlyBillingTable');
        return $db->fetchClientPendingPayments();
    }
    
    public function exportClientDetails($params)
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
            
            
            $sQueryStr = $sql->getSqlStringForSqlObject($queryContainer->exportQuery);
            $sResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            $commonService=new CommonService();
            
            if(count($sResult) > 0) {
                foreach($sResult as $aRow) {
                    $row = array();
                    if(trim($aRow['contract_exp_date'])!=""){
                        $aRow['contract_exp_date']=$commonService->humanDateFormat($aRow['contract_exp_date']);	
                    }
                    
                    $row[] = $aRow['client_no'];
                    $row[] = ucwords($aRow['client_name']);
                    $row[] = ucwords($aRow['client_city']);
                    $row[] = $aRow['pin_code'];
                    $row[] = $aRow['address'];
                    $row[] = $aRow['gst_no'];
                    $row[] = $aRow['client_pan_no'];
                    $row[] = $aRow['contract_exp_date'];
                    $row[] = ucwords($aRow['bookedBy']);
                    
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
           
            
            $sheet->setCellValue('A1', html_entity_decode('Client List', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
            $sheet->setCellValue('A4', html_entity_decode('Client No', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('B4', html_entity_decode('Client Name', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('C4', html_entity_decode('City', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('D4', html_entity_decode('Pin Code', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('E4', html_entity_decode('Address', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('F4', html_entity_decode('GST No', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('G4', html_entity_decode('PAN No', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('H4', html_entity_decode('Contract Expiry Date', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValue('I4', html_entity_decode('Contact Person', ENT_QUOTES, 'UTF-8'), \PHPExcel_Cell_DataType::TYPE_STRING);
            
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
            $filename = 'client-list-' . date('d-M-Y-H-i-s') . '.xls';
            $writer->save(TEMP_UPLOAD_PATH . DIRECTORY_SEPARATOR . $filename);
            
            return $filename;
        }
        catch (Exception $exc) {
            return "";
            error_log("GENERATE-VEHICLE-SERVICE-EXCEL--" . $exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
}
?>

