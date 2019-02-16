<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\GlobalConfigTable;
use Application\Model\OtherAmountDetailsTable;
use Application\Model\EventLogTable;

class MonthlyBillingTable extends AbstractTableGateway {

    protected $table = 'monthly_billing_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    public function addMonthlyBillDetails($params){
		if (trim($params['company'])!="" && trim($params['invoiceNo'])!="") {
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$commonService=new CommonService();
			$logincontainer = new Container('credo');
			//$invoiceNo=$params['invoiceNo'].$params['invoiceCode'];
			$expInvoiceStr=explode('/',$params['invoiceNo']);
            $data = array(
                'company_id' => $params['company'],
                'invoice_code' => "/".$expInvoiceStr[1],
                'invoice_sort_key' => trim($expInvoiceStr[0]),
                'invoice_no' => trim($params['invoiceNo']),
                'invoice_month_year' => trim($params['invoiceMonth']),
                'search_month_year' => trim($params['searchInvoiceDate']),
                'business_unit' => base64_decode($params['businessUnit']),
                'client' => $params['client'],
                'subject' => $params['subject'],
                'particulars' => $params['usageParticulars'],
				'basic' => $params['basic'],
                'basic_amount' => $params['basicAmount'],
                'service_tax_type' => $params['serviceTaxType'],
                //'sbc_tax' => $params['sbcTax'],
                //'kkc_tax' => $params['kkcTax'],
                'service_tax_amount' => $params['serviceTaxAmount'],
                //'tds' => $params['tds'],
                'total_amount' => $params['totalAmount'],
                'net_amount' => $params['netAmount'],
                'parking_fee' => $params['toll'],
                'driver_retention' => $params['driverRetention'],
                'balance' => $params['balance'],
                'invoice_prepared_by' => $params['invoicePreparedBy'],
                'payment_status' => 'pending',
				'created_on' => $commonService->getDateTime(),
                'created_by' => $logincontainer->employeeId
            );
			
			if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='sgst'){
				$data['sgst_tax']=$params['sgstTax'];
				$data['cgst_tax']=$params['cgstTax'];
			}
			else if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='igst'){
				$data['igst_tax']=$params['igstTax'];;
			}
			
			if(isset($params['invoiceDate']) && trim($params['invoiceDate'])!=""){
				$data['invoice_date']=$commonService->dateFormat($params['invoiceDate']);
			}
			if(isset($params['paymentDate']) && trim($params['paymentDate'])!=""){
				$data['payment_due_date']=$commonService->dateFormat($params['paymentDate']);
			}
			//if(isset($params['paymentMode']) && trim($params['paymentMode'])!=""){
			//	$data['payment_mode']=base64_decode($params['paymentMode']);
			//}
			
            $result = $this->insert($data);
            $lastInsertedId = $this->lastInsertValue;
            $otherAmtDb = new OtherAmountDetailsTable($dbAdapter);
            if($lastInsertedId>0){
				$totalCount=count($params['otherAmount']);
				for($i=0;$i<$totalCount;$i++){
					if(isset($params['otherAmount'][$i])&& trim($params['otherAmount'][$i])!=''&& $params['otherAmount'][$i]!=null){
						$otherAmtDb->insert(array(
							'monthly_bill_id' => $lastInsertedId,
							'other_name' => $params['otherName'][$i],
							'other_amount' => $params['otherAmount'][$i]
						));
					}
				}
			}
			//event log
			$subject = $lastInsertedId;
			$eventType = 'monthly billing-add';
			$action = 'added a new monthly bill with the invoice number '.$params['invoiceNo'];
			$resourceName = 'MonthlyBilling';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $lastInsertedId;
        }
	}
	
	public function updateMonthlyBillDetails($params){
		if (trim($params['billId'])!="" && trim($params['invoiceNo'])!="") {
			$dbAdapter = $this->adapter;
			$commonService=new CommonService();
			$logincontainer = new Container('credo');
			$billId=base64_decode($params['billId']);
			$expInvoiceStr=explode('/',$params['invoiceNo']);
			
            $data = array(
                'company_id' => $params['company'],
                'invoice_code' => "/".$expInvoiceStr[1],
                'invoice_sort_key' => $expInvoiceStr[0],
                'invoice_no' => $params['invoiceNo'],
				'invoice_month_year' => trim($params['invoiceMonth']),
				'search_month_year' => trim($params['searchInvoiceDate']),
                'business_unit' => base64_decode($params['businessUnit']),
                'client' => $params['client'],
                'subject' => $params['subject'],
                'particulars' => $params['usageParticulars'],
                'basic' => $params['basic'],
                'basic_amount' => $params['basicAmount'],
                //'service_tax_type' => $params['serviceTaxType'],
                'service_tax_amount' => $params['serviceTaxAmount'],
                'tds' => $params['tds'],
                'total_amount' => $params['totalAmount'],
				'net_amount' => $params['netAmount'],
                'parking_fee' => $params['toll'],
                'driver_retention' => $params['driverRetention'],
                'discount' => $params['discount'],
                'balance' => $params['balance'],
                'received_amount' => $params['receivedAmount'],
                'remarks' => $params['remarks'],
                'invoice_prepared_by' => $params['invoicePreparedBy'],
                'payment_status' => $params['paymentStatus'],
				'created_on' => $commonService->getDateTime(),
                'created_by' => $logincontainer->employeeId
            );
			$data['invoice_date']=NULL;
            $data['payment_received_date']=NULL;
            $data['payment_mode']=NULL;
            $data['payment_due_date']=NULL;
			$data['service_tax_type']=NULL;
			$data['sgst_tax']=NULL;
			$data['cgst_tax']=NULL;
			$data['igst_tax']=NULL;
			$data['service_tax']=NULL;
			$data['sbc_tax']=NULL;
			$data['kkc_tax']=NULL;
			
			if(isset($params['invoiceDate']) && trim($params['invoiceDate'])!=""){
				$data['invoice_date']=$commonService->dateFormat($params['invoiceDate']);
			}
			if(isset($params['receivedDate']) && trim($params['receivedDate'])!=""){
				$data['payment_received_date']=$commonService->dateFormat($params['receivedDate']);
			}
			if(isset($params['paymentMode']) && trim($params['paymentMode'])!=""){
				$data['payment_mode']=base64_decode($params['paymentMode']);
			}
			if(isset($params['paymentDate']) && trim($params['paymentDate'])!=""){
				$data['payment_due_date']=$commonService->dateFormat($params['paymentDate']);
			}
			//<---- Old service tax
			if(isset($params['serviceTax']) && trim($params['serviceTax'])!=""){
				$data['service_tax']=$params['serviceTax'];
			}
			if(isset($params['sbcTax']) && trim($params['sbcTax'])!=""){
				$data['sbc_tax']=$params['sbcTax'];
			}
			if(isset($params['kkcTax']) && trim($params['kkcTax'])!=""){
				$data['kkc_tax']=$params['kkcTax'];
			}
			//--- End old service tax ------->
			
			if(isset($params['serviceTaxType']) && trim($params['serviceTaxType'])!=""){
				$data['service_tax_type']=$params['serviceTaxType'];
			}
			if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='sgst'){
				$data['sgst_tax']=$params['sgstTax'];
				$data['cgst_tax']=$params['cgstTax'];
			}
			else if(isset($params['serviceTaxType']) && $params['serviceTaxType']=='igst'){
				$data['igst_tax']=$params['igstTax'];;
			}
			
            $this->update($data, array('month_bill_id' => $billId));
			
			$otherAmtDb = new OtherAmountDetailsTable($dbAdapter);
			
			if (isset($params['deletedId']) && trim($params['deletedId']) != "") {
				$deletedId = explode(",", $params['deletedId']);
				$totalId = count($deletedId);
				for ($z = 0; $z < $totalId; $z++) {
				$otherAmtDb->delete("other_id=".$deletedId[$z]);
				}
			}
			
            if($billId>0){
				$totalCount=count($params['otherAmount']);
				for($i=0;$i<$totalCount;$i++){
					if(isset($params['otherAmount'][$i])&& trim($params['otherAmount'][$i])!=''&& $params['otherAmount'][$i]!=null){
						$data=array(
							'monthly_bill_id' => $billId,
							'other_name' => $params['otherName'][$i],
							'other_amount' => $params['otherAmount'][$i]
						);
						if(isset($params['otherId'][$i]) && $params['otherId'][$i] !=""){
							$otherAmtDb->update($data, array('other_id' => $params['otherId'][$i]));
						}else{
							$otherAmtDb->insert($data);
						}
					}
				}
			}
			//event log
			$subject = $billId;
			$eventType = 'monthly billing-update';
			$action = 'updated a monthly bill with the invoice number '.$params['invoiceNo'];
			$resourceName = 'MonthlyBilling';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $billId;
        }
	}
	
	public function fetchAllCompletedMonthlyBills($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('invoice_no','invoice_month_year','DATE_FORMAT(invoice_date,"%d-%b-%Y")','DATE_FORMAT(payment_due_date,"%d-%b-%Y")','cd.company_name','client_name','total_amount','balance','payment_status');
        $orderColumns = array('invoice_no','invoice_month_year','payment_due_date','cd.company_name','client_name','total_amount','balance','payment_status');

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
        $sQuery = $sql->select()->from(array('mbd'=>'monthly_billing_details'))
								->join(array('cd' => 'company_details'), "cd.company_id=mbd.company_id", array('company_name'))
								->join(array('b' => 'business_units'), "b.unit_id=mbd.business_unit", array('unit_name'))
								->join(array('ed' => 'employee_details'), "ed.employee_id=mbd.invoice_prepared_by", array('employee_name'),"left")
								->join(array('c' => 'clients'), "c.client_id=mbd.client", array('client_name'))
								->join(array('pm' => 'payment_mode'), "pm.type_id=mbd.payment_mode", array('payment_type'))
								->where(array('mbd.payment_status'=>'paid'));
        
		$sessionLogin = new Container('credo');
		if($sessionLogin->roleId!=1 && trim($sessionLogin->billGenerateClientId)!=""){
			$sQuery=$sQuery->where("mbd.client IN($sessionLogin->billGenerateClientId)");
		}
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=$parameters['startDate'].'-01';
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$sQuery->where("(search_month_year>='".$parameters['startDate']."' AND search_month_year<='".$parameters['endDate']."')");
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
        //echo $sQueryStr; die;
		$queryContainer->exportQuery = $sQuery;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        //$iTotal = $this->select(array('payment_status'=>'paid'))->count();
		$iQuery = $sql->select()->from(array('mbd'=>'monthly_billing_details'))
								->where(array('mbd.payment_status'=>'paid'));
		if($sessionLogin->roleId!=1 && trim($sessionLogin->billGenerateClientId)!=""){
			$iQuery=$iQuery->where("mbd.client IN($sessionLogin->billGenerateClientId)");
		}
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $iTotal = count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'generate-billing')) {
            $generatePdf = true;
        } else {
            $generatePdf = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'view')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'delete')) {
            $deleteBill = true;
        } else {
            $deleteBill = false;
        }
        $commonService=new CommonService();
        foreach ($rResult as $aRow) {
            $row = array();
			if(isset($aRow['payment_received_date']) && trim($aRow['payment_received_date'])!=""){
				$aRow['payment_received_date']=$commonService->humanDateFormat($aRow['payment_received_date']);
			}
			$edit="";
			$billing="";
			$view="";
			$delete="";
            $row[] = $aRow['invoice_no'];
            $row[] = $aRow['invoice_month_year'];
            $row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['total_amount'];
			$row[] = $aRow['payment_received_date'];
            $row[] = $aRow['balance'];
			
			if($generatePdf){
				$billing='<a href="javascript:void(0);" onclick="generateMonthlyBilling(\''.base64_encode($aRow['month_bill_id']).'\')" title="Print"><i class="fa fa-print"></i></a>';
			}
			if($viewAction){
				$view='<a href="./view/' . base64_encode($aRow['month_bill_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
			}
			if($deleteBill){
				$delete='<a href="javascript:void(0);" onclick="deleteMonthlyBilling(\''.base64_encode($aRow['month_bill_id']).'\')" title="Delete"><i class="fa fa-trash-o"></i></a>';
			}
			if($update || $generatePdf || $viewAction || $deleteBill){
				if($aRow['payment_status']!="paid" || $sessionLogin->roleId==1){
					$edit='<a href="./edit/' . base64_encode($aRow['month_bill_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
				}
				$row[] = $edit.$view.$billing.$delete;
			}
            $output['aaData'][] = $row;
        }
        return $output;
    }
    
    public function checkBillCode($companyId,$invoiceCode){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from('monthly_billing_details')->where(array('company_id'=>$companyId,'invoice_code'=>$invoiceCode))->order("month_bill_id DESC");
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		
		if($result!=""){
			$invoiceSortNo=$result['invoice_sort_key']+1;
			$invoiceNo=$this->checkMonthlyInvoiceNo($companyId,$invoiceCode,$invoiceSortNo);
		}else{
			$invoiceSortNo='001';
			$invoiceNo=$this->checkMonthlyInvoiceNo($companyId,$invoiceCode,$invoiceSortNo);
		}
		return $invoiceNo;
    }
    
    public function checkMonthlyInvoiceNo($companyId,$invoiceCode,$invoiceSortNo){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$idSize = strlen($invoiceSortNo);
		if ($idSize < 4) {
			if ($idSize == 1) {
			$invoiceSortNo = "00" . $invoiceSortNo;
			} else if ($idSize == 2) {
			$invoiceSortNo = "0" . $invoiceSortNo;
			}else {
			$invoiceSortNo;
			}
		}
		
		$invoiceNo=$invoiceSortNo.$invoiceCode;
		$query = $sql->select()->from('monthly_billing_details')->where(array('company_id'=>$companyId,'invoice_no'=>$invoiceNo));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$checkMonthlyRes = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		
		if($checkMonthlyRes!=""){
			$invoiceSortNo=$checkMonthlyRes['invoice_sort_key']+1;
			return $this->checkMonthlyInvoiceNo($companyId,$invoiceCode,$invoiceSortNo);			
		}else{
			return $invoiceSortNo;
		}
    }
    
    public function fetchInvoiceNoBasedOnCompany($companyId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$configDb = new GlobalConfigTable($dbAdapter);
		$monthlyInvoiceCode=$configDb->fetchGlobalValue('monthly_invoice_code');
		return $this->checkBillCode($companyId,$monthlyInvoiceCode);
	}
	
	public function fetchBillingDetails($billingId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$otherAmountResult='';
		$query = $sql->select()->from(array('mbd'=>'monthly_billing_details'))->where(array('month_bill_id'=>$billingId));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$billResult=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($billResult!=""){
			$oQuery = $sql->select()->from('other_amount_details')->where(array('monthly_bill_id'=>$billingId));
			$oQueryStr = $sql->getSqlStringForSqlObject($oQuery);
			$otherAmountResult=$dbAdapter->query($oQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		}
		return $result=array('bill'=>$billResult,'otherAmount'=>$otherAmountResult);
	}
	
	public function fetchMonthlyBillingDetails($billingId){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$otherAmountResult='';
		$query = $sql->select()->from(array('mbd'=>'monthly_billing_details'))->where(array('month_bill_id'=>$billingId))
									->join(array('cd' => 'company_details'), "cd.company_id=mbd.company_id", array('company_name','companyAddress'=>'address','city','pin_code','phone_no','service_tax_no','pan_no','gst_no','sac_no','state_code'))
									->join(array('b' => 'business_units'), "b.unit_id=mbd.business_unit", array('unit_name'))
									->join(array('c' => 'clients'), "c.client_id=mbd.client", array('client_name','clientAddress'=>'address','client_city','pin_code','client_gst_no'=>'gst_no','client_pan_no','client_state_code'))
									->join(array('pm' => 'payment_mode'), "pm.type_id=mbd.payment_mode", array('payment_type'),"left")
									->join(array('ed' => 'employee_details'), "ed.employee_id=mbd.invoice_prepared_by", array('invoicePreparedBy'=>'employee_name'),"left");
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$billResult=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($billResult!=""){
			$oQuery = $sql->select()->from('other_amount_details')->where(array('monthly_bill_id'=>$billingId));
			$oQueryStr = $sql->getSqlStringForSqlObject($oQuery);
			$otherAmountResult=$dbAdapter->query($oQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
		}
		return $result=array('bill'=>$billResult,'otherAmount'=>$otherAmountResult);
	}
	
	public function fetchAllPendingMonthlyBills($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('invoice_no','invoice_month_year','DATE_FORMAT(invoice_date,"%d-%b-%Y")','cd.company_name','client_name','total_amount','DATE_FORMAT(payment_due_date,"%d-%b-%Y")');
        $orderColumns = array('invoice_no','invoice_date','cd.company_name','client_name','total_amount','payment_due_date');

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
        $sQuery = $sql->select()->from(array('mbd'=>'monthly_billing_details'))
								->join(array('cd' => 'company_details'), "cd.company_id=mbd.company_id", array('company_name'))
								->join(array('b' => 'business_units'), "b.unit_id=mbd.business_unit", array('unit_name'))
								//->join(array('ed' => 'employee_details'), "ed.employee_id=mbd.invoice_prepared_by", array('employee_name'),"left")
								->join(array('c' => 'clients'), "c.client_id=mbd.client",array('client_name'))
								->where(array('mbd.payment_status'=>'pending'));
								
        $sessionLogin = new Container('credo');
		
		if($sessionLogin->roleId!=1 && trim($sessionLogin->billGenerateClientId)!=""){
			$sQuery=$sQuery->where("mbd.client IN($sessionLogin->billGenerateClientId)");
		}
		
		if (isset($sWhere) && $sWhere != "") {
            $sQuery->where($sWhere);
        }
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=$parameters['startDate'].'-01';
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$sQuery->where("(search_month_year>='".$parameters['startDate']."' AND search_month_year<='".$parameters['endDate']."')");
		}
		
        if (isset($sOrder) && $sOrder != "") {
            $sQuery->order($sOrder);
        }

        if (isset($sLimit) && isset($sOffset)) {
            $sQuery->limit($sLimit);
            $sQuery->offset($sOffset);
        }

        $sQueryStr = $sql->getSqlStringForSqlObject($sQuery); // Get the string of the Sql, instead of the Select-instance 
        //echo $sQueryStr; die;
		$queryContainer->exportQuery = $sQuery;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
		$iQuery = $sql->select()->from(array('mbd'=>'monthly_billing_details'))
								->where(array('mbd.payment_status'=>'pending'));
		if($sessionLogin->roleId!=1 && trim($sessionLogin->billGenerateClientId)!=""){
			$iQuery=$iQuery->where("mbd.client IN($sessionLogin->billGenerateClientId)");
		}
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=$parameters['startDate'].'-01';
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$iQuery->where("(search_month_year>='".$parameters['startDate']."' AND search_month_year<='".$parameters['endDate']."')");
		}
		
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $iTotal = count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'generate-billing')) {
            $generatePdf = true;
        } else {
            $generatePdf = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'delete')) {
            $deleteBill = true;
        } else {
            $deleteBill = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'view')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
        $commonService=new CommonService();
        foreach ($rResult as $aRow) {
            $row = array();
			$edit="";
			$billing="";
			$delete="";
			$view="";
            $row[] = $aRow['invoice_no'];
            $row[] = $aRow['invoice_month_year'];
            $row[] = $aRow['company_name'];
            $row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['total_amount'];
            $row[] = $commonService->humanDateFormat($aRow['payment_due_date']);
			if($generatePdf){
				$billing='<a href="javascript:void(0);" onclick="generateMonthlyBilling(\''.base64_encode($aRow['month_bill_id']).'\')" title="Print"><i class="fa fa-print"></i></a>';
			}
			if($deleteBill){
				$delete='<a href="javascript:void(0);" onclick="deleteMonthlyBilling(\''.base64_encode($aRow['month_bill_id']).'\')" title="Delete"><i class="fa fa-trash-o"></i></a>';
			}
			if($update){
				$edit = '<a href="./edit/' . base64_encode($aRow['month_bill_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			if($viewAction){
				$view='<a href="./view/' . base64_encode($aRow['month_bill_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
			}
			if($update || $generatePdf || $deleteBill || $viewAction){
				$row[] = $edit.$view.$billing.$delete;
			}
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function deleteMonthlyBillDetails($billId){
		if($billId>0){
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$otherAmountResult='';
			$query = $sql->select()->from(array('mbd'=>'monthly_billing_details'))->where(array('month_bill_id'=>$billId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($result!=""){
				//event log
				$subject = $billId;
				$eventType = 'monthly billing-delete';
				$action = 'deleted a monthly bill with the invoice number '.$result['invoice_no'];
				$resourceName = 'MonthlyBilling';
				$eventLogDb = new EventLogTable($this->adapter);
				$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
				return $this->delete("month_bill_id=".$billId);
			}
		}
	}
	
	public function fetchPendingPaymentList(){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        
        $query = $sql->select()->from(array('mbd'=>'monthly_billing_details'))
						->columns(array('month_bill_id','invoice_no','payment_due_date' => new Expression("DATE_FORMAT(payment_due_date,'%d-%b-%Y')")))
						->join(array('c' => 'clients'), "c.client_id=mbd.client", array('client_name'))
						->where(array('payment_status'=>'pending'))
						->where("payment_due_date <= DATE(NOW())");
		$queryStr = $sql->getSqlStringForSqlObject($query);
        return $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchAllMonthlyBills($parameters,$acl) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */
		$queryContainer = new Container('query');
        $aColumns = array('invoice_no','invoice_month_year','DATE_FORMAT(invoice_date,"%d-%b-%Y")','DATE_FORMAT(payment_due_date,"%d-%b-%Y")','cd.company_name','client_name','total_amount','balance','payment_status');
        $orderColumns = array('invoice_no','invoice_month_year','payment_due_date','cd.company_name','client_name','total_amount','balance','payment_status');

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
        $sQuery = $sql->select()->from(array('mbd'=>'monthly_billing_details'))
								->join(array('cd' => 'company_details'), "cd.company_id=mbd.company_id", array('company_name'))
								->join(array('b' => 'business_units'), "b.unit_id=mbd.business_unit", array('unit_name'))
								->join(array('ed' => 'employee_details'), "ed.employee_id=mbd.invoice_prepared_by", array('employee_name'),"left")
								->join(array('c' => 'clients'), "c.client_id=mbd.client", array('client_name'))
								->join(array('pm' => 'payment_mode'), "pm.type_id=mbd.payment_mode", array('payment_type'),"left");
        
		$sessionLogin = new Container('credo');
		if($sessionLogin->roleId!=1 && trim($sessionLogin->billGenerateClientId)!=""){
			$sQuery=$sQuery->where("mbd.client IN($sessionLogin->billGenerateClientId)");
		}
		
		if(isset($parameters['startDate']) && trim($parameters['startDate'])!="" && isset($parameters['endDate']) && trim($parameters['endDate'])!=""){
			$parameters['startDate']=$parameters['startDate'].'-01';
			$parameters['endDate']=date('Y-m-t',strtotime($parameters['endDate']));
			$sQuery->where("(search_month_year>='".$parameters['startDate']."' AND search_month_year<='".$parameters['endDate']."')");
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
        //echo $sQueryStr; die;
		$queryContainer->exportAllBillQuery = $sQuery;
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
        $fQuery = $sql->getSqlStringForSqlObject($sQuery);
        $aResultFilterTotal = $dbAdapter->query($fQuery, $dbAdapter::QUERY_MODE_EXECUTE);
        $iFilteredTotal = count($aResultFilterTotal);

        /* Total data set length */
        //$iTotal = $this->select(array('payment_status'=>'paid'))->count();
		$iQuery = $sql->select()->from(array('mbd'=>'monthly_billing_details'));
		if($sessionLogin->roleId!=1 && trim($sessionLogin->billGenerateClientId)!=""){
			$iQuery=$iQuery->where("mbd.client IN($sessionLogin->billGenerateClientId)");
		}
		$iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
		$iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        $iTotal = count($iResult);
		
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		
        $role = $sessionLogin->employeeCode;
        if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'edit')) {
            $update = true;
        } else {
            $update = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'generate-billing')) {
            $generatePdf = true;
        } else {
            $generatePdf = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'view')) {
            $viewAction = true;
        } else {
            $viewAction = false;
        }
		if ($acl->isAllowed($role, 'Admin\Controller\MonthlyBilling', 'delete')) {
            $deleteBill = true;
        } else {
            $deleteBill = false;
        }
        $commonService=new CommonService();
        foreach ($rResult as $aRow) {
            $row = array();
			if(isset($aRow['payment_received_date']) && trim($aRow['payment_received_date'])!=""){
				$aRow['payment_received_date']=$commonService->humanDateFormat($aRow['payment_received_date']);
			}
			$edit="";
			$billing="";
			$view="";
			$delete="";
            $row[] = $aRow['invoice_no'];
            $row[] = $aRow['invoice_month_year'];
            $row[] = ucwords($aRow['client_name']);
            $row[] = $aRow['total_amount'];
			$row[] = $aRow['payment_received_date'];
            $row[] = $aRow['balance'];
			
			if($generatePdf){
				$billing='<a href="javascript:void(0);" onclick="generateMonthlyBilling(\''.base64_encode($aRow['month_bill_id']).'\')" title="Print"><i class="fa fa-print"></i></a>';
			}
			if($viewAction){
				$view='<a href="./view/' . base64_encode($aRow['month_bill_id']) . '" title="View"><i class="fa fa-eye"></i></a>';
			}
			if($deleteBill){
				$delete='<a href="javascript:void(0);" onclick="deleteMonthlyBilling(\''.base64_encode($aRow['month_bill_id']).'\')" title="Delete"><i class="fa fa-trash-o"></i></a>';
			}
			if($update || $generatePdf || $viewAction || $deleteBill){
				if($aRow['payment_status']!="paid" || $sessionLogin->roleId==1){
					$edit='<a href="./edit/' . base64_encode($aRow['month_bill_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
				}
				$row[] = $edit.$view.$billing.$delete;
			}
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function fetchClientPendingPayments(){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('mbd'=>'monthly_billing_details'))
					->columns(array('month_bill_id','invoice_no','balance'))
					->join(array('c' => 'clients'), "c.client_id=mbd.client", array('client_name','client_code'))
					->where(array('mbd.payment_status'=>'pending'))
					->order("mbd.balance DESC");
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $vendorResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
}
?>
