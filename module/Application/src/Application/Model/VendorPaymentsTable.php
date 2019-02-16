<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\EventLogTable;
use Application\Model\VendorsTable;
use Application\Model\VendorPaidDetailsTable;

class VendorPaymentsTable extends AbstractTableGateway {

    protected $table = 'vendor_payments';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
	public function addVendorPaymentDetails($params) {
        $result = "";
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$logincontainer = new Container('credo');
		$commonService=new CommonService();
		$vendorsDb=new VendorsTable($dbAdapter);
        if (trim($params['vendor']) != "" && trim($params['paymentMonth'])!="") {
			$vendor=base64_decode($params['vendor']);
			if(isset($params['paymentMonth']) && trim($params['paymentMonth'])!=""){
				$paymentDate=$commonService->dateFormat("01-".$params['paymentMonth']);
				$expPaymentMonth=explode("-",$params['paymentMonth']);
				$paymentMonth=trim($expPaymentMonth[0]);
				$paymentYear=trim($expPaymentMonth[1]);
			}
            $data = array(
                'vendor_id' => $vendor,
                'payment_month' => $paymentMonth,
                'payment_year' => $paymentYear,
                'payment_date' => $paymentDate,
                'hotel_revenue' => $params['hotelRevenue'],
                'corporate_revenue' => $params['coporateRevenue'],
                'retail_revenue' => $params['retailRevenue'],
                'total_revenue' => $params['totalRevenue'],
                'fuel_amount' => $params['fuelAmount'],
                'fuel_surcharge' => $params['fuelSurcharge'],
                'advance' => $params['advance'],
                'other_deductions' => $params['otherDeduction'],
                'total_deductions' => $params['totalDeduction'],
                'hotel_payment' => $params['hotelPayment'],
                'corp_payment' => $params['corpPayment'],
                'retail_payment' => $params['retailPayment'],
                'parking_amount' => $params['parkingAmount'],
                'service_tax_applicable' => $params['serviceTaxApp'],
                //'service_tax_amount' => $params['stAmount'],
                'total_payment' => $params['totalAmount'],
                'tds_applicable' => $params['tdsApplicable'],
				'net_amount' => $params['netAmount'],
				'net_balance' => $params['netAmount'],
				'company_revenue' => $params['companyRevenue'],
                'payment_status' => 'pending',
                'added_on' => $commonService->getDateTime(),
                'added_by' => $logincontainer->employeeId
            );
			
			
			if(isset($params['serviceTaxApp']) && trim($params['serviceTaxApp'])=='yes'){
				$data['service_tax']=$params['serviceTax'];
				$data['service_tax_amount']=$params['stAmount'];
			}
			
			if(isset($params['tdsApplicable']) && trim($params['tdsApplicable'])=='yes'){
				$data['tds']=$params['tds'];
				$data['tds_amount']=$params['tdsAmount'];
			}
			
            $this->insert($data);
			$lastInsertedId = $this->lastInsertValue;
			
			$vendorsDb->addVendorCurrentBalance($vendor,$params['netAmount']);
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment");
            }
			
			if (isset($_FILES['file']['name']) && trim($_FILES['file']['name'])!= '') {
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment" . DIRECTORY_SEPARATOR . $lastInsertedId;
				if (!file_exists($pathname) && !is_dir($pathname)) {
					mkdir($pathname);
				}
				
				$extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['file']['name'],PATHINFO_EXTENSION));
				$attachmentFileName = $commonService->removespecials($_FILES['file']['name'])."." . $extension;
				if (move_uploaded_file($_FILES["file"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$attachmentFileName)) {
					$imageData = array('attach_file_name' => $attachmentFileName);
					$this->update($imageData, array("payment_id" => $lastInsertedId));
				}
            }
			//Get vendor details
			$vendorRes=$vendorsDb->fetchVendorInfo($vendor);
			
			//event log
			$subject = $lastInsertedId;
			$eventType = 'vendor-add-payment';
			$action = 'added a new vendor payment with the name '.ucwords($vendorRes['vendor_name'])."-".$params['paymentMonth'];
			$resourceName = 'Vendor Payment';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $lastInsertedId;
		}
    }
	
	public function updateVendorPaymentDetails($params) {
        $result = "";
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$logincontainer = new Container('credo');
		$commonService=new CommonService();
		$vendorsDb=new VendorsTable($dbAdapter);
        if (trim($params['vendor']) != "" && trim($params['paymentMonth'])!="" && trim($params['paymentId'])!="") {
			$vendor=base64_decode($params['vendor']);
			$paymentId=base64_decode($params['paymentId']);
			
			//Get vendor payment details
			$query = $sql->select()->from('vendor_payments')
					->where(array('payment_id'=>$paymentId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$vendorPaymentResult= $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($vendorPaymentResult!=""){
				$vendorsDb->updateVendorCurrentBalance($vendor,$vendorPaymentResult['net_balance']);
			}
			
			if(isset($params['paymentMonth']) && trim($params['paymentMonth'])!=""){
				$paymentDate=$commonService->dateFormat("01-".$params['paymentMonth']);
				$expPaymentMonth=explode("-",$params['paymentMonth']);
				$paymentMonth=trim($expPaymentMonth[0]);
				$paymentYear=trim($expPaymentMonth[1]);
			}
            $data = array(
                'vendor_id' => $vendor,
                'payment_month' => $paymentMonth,
                'payment_year' => $paymentYear,
                'payment_date' => $paymentDate,
                'hotel_revenue' => $params['hotelRevenue'],
                'corporate_revenue' => $params['coporateRevenue'],
                'retail_revenue' => $params['retailRevenue'],
                'total_revenue' => $params['totalRevenue'],
                'fuel_amount' => $params['fuelAmount'],
                'fuel_surcharge' => $params['fuelSurcharge'],
                'advance' => $params['advance'],
                'other_deductions' => $params['otherDeduction'],
                'total_deductions' => $params['totalDeduction'],
                'hotel_payment' => $params['hotelPayment'],
                'corp_payment' => $params['corpPayment'],
                'retail_payment' => $params['retailPayment'],
                'parking_amount' => $params['parkingAmount'],
                'service_tax_applicable' => $params['serviceTaxApp'],
                'total_payment' => $params['totalAmount'],
                'tds_applicable' => $params['tdsApplicable'],
				'net_amount' => $params['netAmount'],
				'net_balance' => $params['netAmount'],
				'company_revenue' => $params['companyRevenue']
            );
			$data['service_tax_amount']=NULL;
			$data['service_tax']=NULL;
			if(isset($params['serviceTaxApp']) && trim($params['serviceTaxApp'])=='yes'){
				$data['service_tax']=$params['serviceTax'];
				$data['service_tax_amount']=$params['stAmount'];
			}
			$data['tds_amount']=NULL;
			$data['tds']=NULL;
			if(isset($params['tdsApplicable']) && trim($params['tdsApplicable'])=='yes'){
				$data['tds']=$params['tds'];
				$data['tds_amount']=$params['tdsAmount'];
			}
			//Add vendor payments
			$vendorsDb->addVendorCurrentBalance($vendor,$params['netAmount']);
			
			$this->update($data, array('payment_id' => $paymentId));
			$lastInsertedId = $paymentId;
			
			
			if (!file_exists(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment") && !is_dir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment")) {
                mkdir(UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment");
            }
			
			//Delete upload file
			if(isset($params['deletedFile']) && trim($params['deletedFile'])!=""){
				$filePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment" . DIRECTORY_SEPARATOR . $lastInsertedId. DIRECTORY_SEPARATOR.$params['deletedFile'];
				if(file_exists($filePath)){
					unlink($filePath);
					$this->update(array('attach_file_name'=>NULL), array("payment_id" => $lastInsertedId));
				}
			}
			
			if (isset($_FILES['file']['name']) && trim($_FILES['file']['name'])!= '') {
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment" . DIRECTORY_SEPARATOR . $lastInsertedId;
				if (!file_exists($pathname) && !is_dir($pathname)) {
					mkdir($pathname);
				}
			
				$extension = strtolower(pathinfo($pathname . DIRECTORY_SEPARATOR . $_FILES['file']['name'],PATHINFO_EXTENSION));
				$expStr=explode(".",$_FILES['file']['name']);
				$attachmentFileName = $commonService->removespecials($expStr[0])."." . $extension;
				if (move_uploaded_file($_FILES["file"]["tmp_name"], $pathname . DIRECTORY_SEPARATOR .$attachmentFileName)) {
					$imageData = array('attach_file_name' => $attachmentFileName);
					$this->update($imageData, array("payment_id" => $lastInsertedId));
				}
            }
			
			//Get vendor details
			$vendorRes=$vendorsDb->fetchVendorInfo($vendor);
			
			//event log
			$subject = $lastInsertedId;
			$eventType = 'vendor-update-payment';
			$action = 'updated a vendor payment with the name '.ucwords($vendorRes['vendor_name']."-".$params['paymentMonth']);
			$resourceName = 'Vendor Payment';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $lastInsertedId;
		}
    }
	
    public function fetchAllVendorPendingPayment($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','vp.payment_month','vp.net_amount','vp.company_revenue','vp.net_balance');
        $orderColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','vp.payment_month','vp.net_amount','vp.company_revenue','vp.net_balance');
		
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
        $sQuery = $sql->select()->from(array('vp'=>'vendor_payments'))
				->columns(array('payment_id','payment_month','payment_year','hotel_revenue','corporate_revenue','retail_revenue','total_revenue','fuel_amount','fuel_surcharge','advance','other_deductions','total_deductions','hotel_payment','corp_payment','retail_payment','parking_amount','service_tax_applicable','service_tax_amount','total_payment','tds_applicable','tds_amount','net_amount','paymentDate' => new Expression("DATE_FORMAT(payment_date,'%b-%Y')"),'company_revenue','net_balance','attach_file_name','payment_status'))
				->join(array('v' => 'vendors'), "v.vendor_id=vp.vendor_id", array('vendor_name','vendor_no','vendor_type'))
				->where(array('payment_status'=>'pending'));
				
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=$parameters['startDate'].'-01';
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$sQuery->where("(payment_date>='".$parameters['startDate']."' AND payment_date<='".$parameters['endDate']."')");
		}
		if(isset($parameters['vendorName']) && trim($parameters['vendorName'])!=""){
			$sQuery->where(array('vp.vendor_id'=>base64_decode($parameters['vendorName'])));
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
		$queryContainer->exportQuery = $sQuery;
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select((array('payment_status'=>'pending')))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'edit-payment')) {
            $update = true;
        } else {
            $update = false;
        }
        
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'view-payment')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'delete-payment')) {
            $deletePayment = true;
        } else {
            $deletePayment = false;
        }
		
        foreach ($rResult as $aRow) {
            $row = array();
			$edit="";
			$view="";
			$download="";
			$delete="";
            $row[] = ucwords($aRow['vendor_name']);
            $row[] = $aRow['vendor_no'];
            $row[] = ucwords($aRow['vendor_type']);
            $row[] = $aRow['paymentDate'];
            $row[] = $aRow['net_amount'];
            $row[] = $aRow['company_revenue'];
            $row[] = $aRow['net_balance'];
			
			if(trim($aRow['attach_file_name'])!=""){
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment" . DIRECTORY_SEPARATOR . $aRow['payment_id'];
				if (file_exists($pathname. DIRECTORY_SEPARATOR.$aRow['attach_file_name'])) {
					$download='<a href="/uploads/vendor-payment'. DIRECTORY_SEPARATOR.$aRow['payment_id']. DIRECTORY_SEPARATOR.$aRow['attach_file_name']. '" target="__blank" title="Download Attachment"><i class="fa fa-download"></i></a>';
				}
			}
			
            if($update){
				if($aRow['net_amount']==$aRow['net_balance']){
				$edit = '<a href="/vendor/edit-payment/' . base64_encode($aRow['payment_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
				}
            }
			if($viewAction){
				$view = '<a href="/vendor/view-payment/' . base64_encode($aRow['payment_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
            }
			
			if($deletePayment){
				$delete='<a href="javascript:void(0);" onclick="deleteVendorPayment(\''.base64_encode($aRow['payment_id']).'\')" title="Delete"><i class="fa fa-trash-o"></i></a>';
			}
			$makePayment = '<a href="javascript:void(0)" onClick="showModal(\'/vendor/make-payment/'.base64_encode($aRow['payment_id']). '\',\'700\',\'400\')"  title="Make Payment"><i class="fa fa-money"> </i></a>';
			
			if($update || $deletePayment || $viewAction || trim($download)!=""){
				$row[]=$view.$edit.$download.$makePayment.$delete;
			}
			$output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function getVendorPaymentDetails($paymentId){
		if($paymentId>0){
			return $this->select(array('payment_id' => (int) $paymentId))->current();	
		}
	}
	
	public function fetchVendorPaymentDetails($paymentId){
		if($paymentId>0){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from(array('vp'=>'vendor_payments'))
					->join(array('v' => 'vendors'), "v.vendor_id=vp.vendor_id", array('vendor_name','vendor_no','vendor_type'))
					->where(array('payment_id'=>$paymentId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		}
	}
	
	public function fetchVendorPaymentCode(){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $query = $sql->select()->from('vendor_paid_details')->order('paid_id DESC');
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        if($result!=""){
            $paymentSortNo=$result['payment_sort_key']+1;
            return $this->checkVendorPaymentCode($paymentSortNo);
        }else{
            return $paymentSortNo='001';
        }
    }
	
	public function checkVendorPaymentCode($paymentSortNo){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$idSize = strlen($paymentSortNo);
		if ($idSize < 4) {
			if ($idSize == 1) {
			$paymentSortNo = "00" . $paymentSortNo;
			} else if ($idSize == 2) {
			$paymentSortNo = "0" . $paymentSortNo;
			}else {
			$paymentSortNo;
			}
		}
		$query = $sql->select()->from('vendor_paid_details')->where(array('payment_sort_key'=>$paymentSortNo));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($result!=""){
			$paymentSortNo=$result['payment_sort_key']+1;
			return $this->checkVendorPaymentCode($paymentSortNo);			
		}else{
			return $paymentSortNo;
		}
    }
	
	public function fetchAllVendorCompletedPayment($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','vp.payment_month','vp.net_amount','vp.company_revenue','vp.net_balance');
        $orderColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','vp.payment_month','vp.net_amount','vp.company_revenue','vp.net_balance');
		
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
        $sQuery = $sql->select()->from(array('vp'=>'vendor_payments'))
				->columns(array('payment_id','payment_month','payment_year','hotel_revenue','corporate_revenue','retail_revenue','total_revenue','fuel_amount','fuel_surcharge','advance','other_deductions','total_deductions','hotel_payment','corp_payment','retail_payment','parking_amount','service_tax_applicable','service_tax_amount','total_payment','tds_applicable','tds_amount','net_amount','paymentDate' => new Expression("DATE_FORMAT(payment_date,'%b-%Y')"),'company_revenue','net_balance','attach_file_name','payment_status'))
				->join(array('v' => 'vendors'), "v.vendor_id=vp.vendor_id", array('vendor_name','vendor_no','vendor_type'))
				->where(array('payment_status'=>'completed'));
				
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=$parameters['startDate'].'-01';
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$sQuery->where("(payment_date>='".$parameters['startDate']."' AND payment_date<='".$parameters['endDate']."')");
		}
		if(isset($parameters['vendorName']) && trim($parameters['vendorName'])!=""){
			$sQuery->where(array('vp.vendor_id'=>base64_decode($parameters['vendorName'])));
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
		$queryContainer->exportQuery = $sQuery;
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select((array('payment_status'=>'completed')))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'edit-payment')) {
            $update = true;
        } else {
            $update = false;
        }
        
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'view-payment')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
        foreach ($rResult as $aRow) {
            $row = array();
			
			$view="";
			$download="";
            $row[] = ucwords($aRow['vendor_name']);
            $row[] = $aRow['vendor_no'];
            $row[] = ucwords($aRow['vendor_type']);
            $row[] = $aRow['paymentDate'];
            $row[] = $aRow['net_amount'];
            $row[] = $aRow['company_revenue'];
            $row[] = $aRow['net_balance'];
			
			if(trim($aRow['attach_file_name'])!=""){
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment" . DIRECTORY_SEPARATOR . $aRow['payment_id'];
				if (file_exists($pathname. DIRECTORY_SEPARATOR.$aRow['attach_file_name'])) {
					$download='<a href="/uploads/vendor-payment'. DIRECTORY_SEPARATOR.$aRow['payment_id']. DIRECTORY_SEPARATOR.$aRow['attach_file_name']. '" target="__blank" title="Download Attachment"><i class="fa fa-download"></i></a>';
				}
			}
			
			if($viewAction){
            $view = '<a href="/vendor/view-payment/' . base64_encode($aRow['payment_id']) . '" title="View"><i class="fa fa-eye"> View</i></a>';
            }
			
			$row[]=$view.$download;
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function deleteVendorPayment($paymentId){
		if($paymentId>0){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$logincontainer = new Container('credo');
			$vendorsDb=new VendorsTable($dbAdapter);
			$query = $sql->select()->from(array('vp'=>'vendor_payments'))
					->columns(array('payment_id','payment_month','payment_year','net_balance','attach_file_name'))
					->join(array('v' => 'vendors'), "v.vendor_id=vp.vendor_id", array('vendor_id','vendor_name','vendor_no'))
					->where(array('payment_id'=>$paymentId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($result!=""){
				//Update vendor current balance
				$vendorsDb->updateVendorCurrentBalance($result['vendor_id'],$result['net_balance']);
				
				if(trim($result['attach_file_name'])!=""){
					$filePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment" . DIRECTORY_SEPARATOR . $result['payment_id']. DIRECTORY_SEPARATOR.$result['attach_file_name'];
					if(file_exists($filePath)){
						unlink($filePath);
					}
				}
			
				//event log
				$subject = $paymentId;
				$eventType = 'vendor payment-delete';
				$action = 'deleted a vendor payment of '.$result['payment_month'].'-'.$result['payment_year'].' with the vendor number '.$result['vendor_no']." ";
				$resourceName = 'Vendor';
				$eventLogDb = new EventLogTable($this->adapter);
				$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
				
				$vendorsPaidDb=new VendorPaidDetailsTable($dbAdapter);
				$vendorsPaidDb->delete("payment_id=".$paymentId);
				return $this->delete("payment_id=".$paymentId);
			}
		}
	}
	
	public function fetchAllVendorPayments($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','vp.payment_month','vp.net_amount','vp.company_revenue','vp.net_balance');
        $orderColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','vp.payment_month','vp.net_amount','vp.company_revenue','vp.net_balance');
		
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
        $sQuery = $sql->select()->from(array('vp'=>'vendor_payments'))
				->columns(array('payment_id','payment_month','payment_year','hotel_revenue','corporate_revenue','retail_revenue','total_revenue','fuel_amount','fuel_surcharge','advance','other_deductions','total_deductions','hotel_payment','corp_payment','retail_payment','parking_amount','service_tax_applicable','service_tax_amount','total_payment','tds_applicable','tds_amount','net_amount','paymentDate' => new Expression("DATE_FORMAT(payment_date,'%b-%Y')"),'company_revenue','net_balance','attach_file_name','payment_status'))
				->join(array('v' => 'vendors'), "v.vendor_id=vp.vendor_id", array('vendor_name','vendor_no','vendor_type'));
				
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=$parameters['startDate'].'-01';
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$sQuery->where("(payment_date>='".$parameters['startDate']."' AND payment_date<='".$parameters['endDate']."')");
		}
		if(isset($parameters['vendorName']) && trim($parameters['vendorName'])!=""){
			$sQuery->where(array('vp.vendor_id'=>base64_decode($parameters['vendorName'])));
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
		$queryContainer->exportQuery = $sQuery;
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select()->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'edit-payment')) {
            $update = true;
        } else {
            $update = false;
        }
        
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'view-payment')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'delete-payment')) {
            $deletePayment = true;
        } else {
            $deletePayment = false;
        }
		
        foreach ($rResult as $aRow) {
            $row = array();
			$edit="";
			$view="";
			$download="";
			$delete="";
            $row[] = ucwords($aRow['vendor_name']);
            $row[] = $aRow['vendor_no'];
            $row[] = ucwords($aRow['vendor_type']);
            $row[] = $aRow['paymentDate'];
            $row[] = $aRow['net_amount'];
            $row[] = $aRow['company_revenue'];
            $row[] = $aRow['net_balance'];
			
			if(trim($aRow['attach_file_name'])!=""){
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment" . DIRECTORY_SEPARATOR . $aRow['payment_id'];
				if (file_exists($pathname. DIRECTORY_SEPARATOR.$aRow['attach_file_name'])) {
					$download='<a href="/uploads/vendor-payment'. DIRECTORY_SEPARATOR.$aRow['payment_id']. DIRECTORY_SEPARATOR.$aRow['attach_file_name']. '" target="__blank" title="Download Attachment"><i class="fa fa-download"></i></a>';
				}
			}
			
            if($update){
				if($aRow['net_amount']==$aRow['net_balance']){
				$edit = '<a href="/vendor/edit-payment/' . base64_encode($aRow['payment_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
				}
            }
			if($viewAction){
				$view = '<a href="/vendor/view-payment/' . base64_encode($aRow['payment_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
            }
			
			if($deletePayment){
				$delete='<a href="javascript:void(0);" onclick="deleteVendorPayment(\''.base64_encode($aRow['payment_id']).'\')" title="Delete"><i class="fa fa-trash-o"></i></a>';
			}
			$makePayment = '<a href="javascript:void(0)" onClick="showModal(\'/vendor/make-payment/'.base64_encode($aRow['payment_id']). '\',\'700\',\'400\')" title="Make Payment"><i class="fa fa-money"> Pay</i></a>';
			
			if($update || $deletePayment || $viewAction || trim($download)!=""){
				$row[]=$view.$edit.$download.$makePayment.$delete;
			}
			$output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchAllVendorLedgerPayments($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','v.current_balance');
        $orderColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','v.current_balance');
		
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
        $sQuery = $sql->select()->from(array('vp'=>'vendor_payments'))
				->columns(array('payment_id'))
				->join(array('v' => 'vendors'), "v.vendor_id=vp.vendor_id", array('vendor_id','vendor_name','vendor_no','vendor_type','current_balance'))
				->group('v.vendor_id');
		
		$pQuery = $sql->select()->from(array('vp'=>'vendor_payments'))
				->columns(array('payment_id','payment_month','payment_year','hotel_revenue','corporate_revenue','retail_revenue','total_revenue','fuel_amount','fuel_surcharge','advance','other_deductions','total_deductions','hotel_payment','corp_payment','retail_payment','parking_amount','service_tax_applicable','service_tax_amount','total_payment','tds_applicable','tds_amount','net_amount','paymentDate' => new Expression("DATE_FORMAT(payment_date,'%b-%Y')"),'company_revenue','net_balance','attach_file_name','payment_status'))
				->join(array('v' => 'vendors'), "v.vendor_id=vp.vendor_id", array('vendor_name','vendor_no','vendor_type'));
				
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=date('Y-m-d',strtotime($parameters['startDate'].'-01'));
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$sQuery->where("(payment_date>='".$parameters['startDate']."' AND payment_date<='".$parameters['endDate']."')");
			$pQuery->where("(payment_date>='".$parameters['startDate']."' AND payment_date<='".$parameters['endDate']."')");
		}
		
		if(isset($parameters['vendorName']) && trim($parameters['vendorName'])!=""){
			$sQuery->where(array('vp.vendor_id'=>base64_decode($parameters['vendorName'])));
			$pQuery->where(array('vp.vendor_id'=>base64_decode($parameters['vendorName'])));
		}
		
		if(isset($parameters['paymentStatus']) && trim($parameters['paymentStatus'])!=""){
			$sQuery->where(array('vp.payment_status'=>$parameters['paymentStatus']));
			$pQuery->where(array('vp.payment_status'=>$parameters['paymentStatus']));
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
		$queryContainer->exportQuery = $pQuery;
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
		$iQuery = $sql->select()->from(array('vp'=>'vendor_payments'))
				->columns(array('payment_id','net_balance'))
				->join(array('v' => 'vendors'), "v.vendor_id=vp.vendor_id", array('vendor_name'))
				->group('v.vendor_id');
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
		
        $iTotal = count($iResult);
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
		
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = ucwords($aRow['vendor_name']);
            $row[] = $aRow['vendor_no'];
            $row[] = ucwords($aRow['vendor_type']);
            $row[] = $aRow['current_balance'];
			$view = '<a href="/vendor/view-ledger/' . base64_encode($aRow['vendor_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
			$row[]=$view;
			
			$output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchVendorPaymentList($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('v.vendor_name','v.vendor_no','v.vendor_type','vp.payment_month','vp.net_amount','vp.company_revenue','vp.net_balance');
        $orderColumns = array('','v.vendor_name','v.vendor_no','v.vendor_type','vp.payment_date','vp.net_amount','vp.company_revenue','vp.net_balance');
		
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
        $sQuery = $sql->select()->from(array('vp'=>'vendor_payments'))
				->columns(array('payment_id','payment_month','payment_year','hotel_revenue','corporate_revenue','retail_revenue','total_revenue','fuel_amount','fuel_surcharge','advance','other_deductions','total_deductions','hotel_payment','corp_payment','retail_payment','parking_amount','service_tax_applicable','service_tax_amount','total_payment','tds_applicable','tds_amount','net_amount','paymentDate' => new Expression("DATE_FORMAT(payment_date,'%b-%Y')"),'company_revenue','net_balance','attach_file_name','payment_status'))
				->join(array('v' => 'vendors'), "v.vendor_id=vp.vendor_id", array('vendor_name','vendor_no','vendor_type'));
				//->where(array('payment_status'=>'pending'))
				
        if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=date('Y-m-d',strtotime($parameters['startDate'].'-01'));
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$sQuery->where("(payment_date>='".$parameters['startDate']."' AND payment_date<='".$parameters['endDate']."')");
		}
		if(isset($parameters['vendorName']) && trim($parameters['vendorName'])!=""){
			$sQuery->where(array('vp.vendor_id'=>$parameters['vendorName']));
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
		$queryContainer->exportQuery = $sQuery;
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        $iTotal = $this->select((array('vendor_id'=>$parameters['vendorName'])))->count();
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$sessionLogin = new Container('credo');
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'edit-payment')) {
            $update = true;
        } else {
            $update = false;
        }
        
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'view-payment')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\Vendor', 'delete-payment')) {
            $deletePayment = true;
        } else {
            $deletePayment = false;
        }
		
        foreach ($rResult as $aRow) {
            $row = array();
			$edit="";
			$view="";
			$download="";
			$delete="";
			$row[] = '<img src="/assets/img/details_open.png" alt="Show" title="Show" style="margin:0 !important;padding:0 !important;cursor:pointer;"  onClick="insRow(\'vendorPaymentDataTable\',' . $aRow['payment_id'] . ',this)" />';
            $row[] = ucwords($aRow['vendor_name']);
            $row[] = $aRow['vendor_no'];
            $row[] = ucwords($aRow['vendor_type']);
            $row[] = $aRow['paymentDate'];
            $row[] = $aRow['net_amount'];
            $row[] = $aRow['company_revenue'];
            $row[] = $aRow['net_balance'];
			
			if(trim($aRow['attach_file_name'])!=""){
				$pathname = UPLOAD_PATH . DIRECTORY_SEPARATOR . "vendor-payment" . DIRECTORY_SEPARATOR . $aRow['payment_id'];
				if (file_exists($pathname. DIRECTORY_SEPARATOR.$aRow['attach_file_name'])) {
					$download='<a href="/uploads/vendor-payment'. DIRECTORY_SEPARATOR.$aRow['payment_id']. DIRECTORY_SEPARATOR.$aRow['attach_file_name']. '" target="__blank" title="Download Attachment"><i class="fa fa-download"></i></a>';
				}
			}
			
            if($update){
				if($aRow['net_amount']==$aRow['net_balance']){
				$edit = '<a href="/vendor/edit-payment/' . base64_encode($aRow['payment_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
				}
            }
			if($viewAction){
				$view = '<a href="/vendor/view-payment/' . base64_encode($aRow['payment_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
            }
			
			if($deletePayment){
				$delete='<a href="javascript:void(0);" onclick="deleteVendorPayment(\''.base64_encode($aRow['payment_id']).'\')" title="Delete"><i class="fa fa-trash-o"></i></a>';
			}
			$makePayment = '<a href="javascript:void(0)" onClick="showModal(\'/vendor/make-payment/'.base64_encode($aRow['payment_id']). '\',\'700\',\'400\')" title="Make Payment"><i class="fa fa-money"> </i></a>';
			
			if($update || $deletePayment || $viewAction || trim($download)!=""){
				$row[]=$view.$edit.$download.$makePayment.$delete;
			}
			$output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchVendorPaymentReports($vendorId,$fDate,$tDate){
		if($vendorId!="" && $fDate!="" && $tDate!=""){
			$pResult="";
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from('vendors')
                        ->columns(array('vendor_name','vendor_no','vendor_type'))
                        ->where(array('vendor_id'=>$vendorId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
            $vendorResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($vendorResult!=""){
				$pQuery = $sql->select()->from('vendor_payments')->where(array('vendor_id'=>$vendorId))
								->where("(payment_date>='".$fDate."' AND payment_date<='".$tDate."')")
								->order("payment_date DESC");
				$pQueryStr = $sql->getSqlStringForSqlObject($pQuery);
                $pResult = $dbAdapter->query($pQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
				foreach($pResult as $key=>$res){
					$sQuery = $sql->select()->from('vendor_paid_details')->where(array('payment_id'=>$res['payment_id']));
					$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
					$pResult[$key]['paidResult']=$dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
				}
			}
			return $result=array('vendor'=>$vendorResult,'paymentDetails'=>$pResult);
		}
	}
}
?>
