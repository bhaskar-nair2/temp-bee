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
use Application\Model\CymExtraTariffDetailsTable;

class CymTariffTable extends AbstractTableGateway {

    protected $table = 'cym_tariff_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    public function getCymTariffAmtDetails($params){
        //\Zend\Debug\Debug::dump($params);die;
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $package=$params['package'];
        $dutyType=$params['dutyType'];
        $travHrs=$params["travHrs"];
		$travMins=$params["travMins"];
        $travKms=$params['totalKms'];
        $dayTimeDriverAllowance=0;
        $nightTimeDriverAllowance=0;
        $extKms=0;
		$extHrs=0;
        $exTQuery = $sql->select()->from('cym_extra_tariff_details')->where(array('package'=>$package,'rental_type'=>$dutyType));
		$exTQueryStr = $sql->getSqlStringForSqlObject($exTQuery);
		$exTResult=$dbAdapter->query($exTQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($exTResult!=""){
            $extAmtPerKms=$exTResult["extra_per_kms_price"];
            $extAmtPerHrs=$exTResult["extra_per_hrs_price"];
            $otExtAmtPerKms=$exTResult["extra_per_kms_price"];
            $driverAllowanceDay=$exTResult["driver_allowance_day"];
            $driverAllowanceNight=$exTResult["driver_allowance_night"];
        }

        //Localuse calculation
        if($dutyType!=2){
            $tQuery = $sql->select()->from('cym_tariff_details')->where(array('package'=>$package,'rental_type'=>$dutyType));
			$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
			$tResult=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
            foreach($tResult as $val){
                $nextTariffHrs=0;
                $nextTariffMins=0;
                if(trim($val["next_tariff_hrs"])!=""){
                    $expHrs=explode(".",$val["next_tariff_hrs"]);
                    $nextTariffHrs=$expHrs[0];
                    if(isset($expHrs[1]) && $expHrs[1]>0){
                        $nextTariffMins=$expHrs[1];
                    }
                }
                
                if($travHrs<$nextTariffHrs){
                    //echo $travHrs;die;
                    if($travHrs<=$val["tariff_hrs"]){
                        $chkExtHrs=$val["tariff_hrs"]-$travHrs;
                        if($chkExtHrs<=$extHrs){
                            $extHrs=0;
                        }
                        if($extHrs==0){
                            $extHrs=0;
                            $tariffId=$val["tariff_id"];
                            $tariffKms=$val["tariff_kms"];
                            $tariffAmt=$val["tariff_amt"];
                            break;
                        }
                    }
                    elseif($travHrs>$val["tariff_hrs"]){
                        $extHrs=$travHrs-$val["tariff_hrs"];
                        $tariffKms=$val["tariff_kms"];
                        $tariffId=$val["tariff_id"];
                        $tariffAmt=$val["tariff_amt"];
                    }
                }
                else if($travHrs==$nextTariffHrs && $travMins<=$nextTariffMins){
                    if($travHrs<=$val["tariff_hrs"]){
                        $chkExtHrs=$val["tariff_hrs"]-$travHrs;
                        if($chkExtHrs<=$extHrs){
                            $extHrs=0;
                        }
                        if($extHrs==0){
                            $extHrs=0;
                            $tariffId=$val["tariff_id"];
                            $tariffKms=$val["tariff_kms"];
                            $tariffAmt=$val["tariff_amt"];
                            break;
                        }
                    }
                    elseif($travHrs>$val["tariff_hrs"]){
                        $extHrs=$travHrs-$val["tariff_hrs"];
                        $tariffKms=$val["tariff_kms"];
                        $tariffId=$val["tariff_id"];
                        $tariffAmt=$val["tariff_amt"];
                    }
                }
                elseif(trim($val["next_tariff_hrs"])=="" && $travHrs<=$val["tariff_hrs"]){
                    //echo $travHrs;die;
                    if($travHrs<=$val["tariff_hrs"]){
                        $chkExtHrs=$val["tariff_hrs"]-$travHrs;
                        if($chkExtHrs<=$extHrs){
                            $extHrs=0;
                        }else{
                        
                        }
                        if($extHrs==0){
                            $extHrs=0;
                            $tariffId=$val["tariff_id"];
                            $tariffKms=$val["tariff_kms"];
                            $tariffAmt=$val["tariff_amt"];
                            break;
                        }
                    }
                }
                elseif($travHrs>$val["tariff_hrs"]){
                    $extHrs=$travHrs-$val["tariff_hrs"];
                    $tariffKms=$val["tariff_kms"];
                    $tariffId=$val["tariff_id"];
                    $tariffAmt=$val["tariff_amt"];
                }
            }
            
            if($travKms<=$tariffKms){
                $extKms=0;
            }elseif($travKms>$tariffKms){
                $extKms=$travKms-$tariffKms;
            }
            $tQuery = $sql->select()->from('cym_tariff_details')->where(array('tariff_id'=>$tariffId));
            $tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
            $tariffRes=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            
            $extraAmtInHrs=$extAmtPerHrs*$extHrs;
            $extraAmtInKms=$extAmtPerKms*$extKms;
            $res=array('extraHrs'=>$extHrs,
                    'extraKms'=>$extKms,
                    'tariff'=>$tariffId,
                    'tariffName'=>$tariffRes['tariff_name'],
                    'extraAmtInHrs'=>$extraAmtInHrs,
                    'extraAmtInKms'=>$extraAmtInKms,
                    'tariffAmt'=>$tariffAmt,
                    'extAmtPerHrs'=>$extAmtPerHrs,
                    'extAmtPerKms'=>$extAmtPerKms
                );
			return json_encode($res);
        }else if($dutyType==2){
            $tQuery = $sql->select()->from('cym_tariff_details')->where(array('package'=>$package,'rental_type'=>$dutyType));
			$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
            $tResult=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            
            $extHrs=0;
            $extKms=0;
            $extraAmtInHrs=0;
            $extraAmtInKms=0;
            $driverAllowance=0;
            $tariffAmt=0;
            $travDay=1;
            $startTime=$params["startTime"];
            $closeTime=$params["endTime"];
            $startTimeCal=false;
            $firstAllowanceDay=false;
            
            for($t=1;$t<=$travDay;$t++){
                if($t==$travDay){
                    if($driverAllowance==0){
                        if(strtotime($startTime)<=strtotime('22:00')){
                            $dayTimeDriverAllowance+=$driverAllowanceDay;
                            $driverAllowance+=$driverAllowanceDay;
                        }
                        if(strtotime($closeTime)>strtotime('22:00')){
                            $nightTimeDriverAllowance+=$driverAllowanceNight;
                            $driverAllowance+=$driverAllowanceNight;
                        }
                    }else{
                        //last day trip
                        if($firstAllowanceDay){
                            $nightTimeDriverAllowance+=$driverAllowanceNight;
                            $driverAllowance+=$driverAllowanceNight;
                            $firstAllowanceDay=false;
                        }
                        //Check before 10pm
                        if(strtotime($closeTime)<=strtotime('22:00')){
                            $dayTimeDriverAllowance+=$driverAllowanceDay;
                            $driverAllowance+=$driverAllowanceDay;
                        }else{
                            $dayTimeDriverAllowance+=$driverAllowanceDay;
                            $nightTimeDriverAllowance+=$driverAllowanceNight;
                            $driverAllowance+=($driverAllowanceDay+$driverAllowanceNight);
                        }
                    }
                }
            }
            
            if($tResult!=""){
                $tariffId=$tResult["tariff_id"];
                $tariffKms=$travDay*$tResult["tariff_kms"];
                $tariffAmt=$travDay*$tResult["tariff_amt"];
                
                if($travKms<=$tariffKms){
                    $extKms=0;
                }elseif($travKms>$tariffKms){
                    $extKms=$travKms-$tariffKms;
                }
                
                $extraAmtInKms=$otExtAmtPerKms*$extKms;
                
                $tQuery = $sql->select()->from('cym_tariff_details')->where(array('tariff_id'=>$tariffId));
                $tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
                $tariffRes=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
                
                if($travDay>1){
                    $tariffRes['tariff_name']=$travDay."Day(s)/".$tariffKms."KMS";
                }
                $res=array('extraHrs'=>$extHrs,
                    'extraKms'=>$extKms,
                    'tariff'=>$tariffId,
                    'tariffName'=>$tariffRes['tariff_name'],
                    'extraAmtInHrs'=>$extraAmtInHrs,
                    'extraAmtInKms'=>$extraAmtInKms,
                    'noOfDay'=>$travDay,
                    'tariffKms'=>$tariffKms,
                    'dayTimeDriverAllowance'=>$dayTimeDriverAllowance,
                    'nightTimeDriverAllowance'=>$nightTimeDriverAllowance,
                    'driverAllowance'=>$driverAllowance,
                    'tariffAmt'=>$tariffAmt,
                    'extAmtPerHrs'=>'0',
                    'extAmtPerKms'=>$otExtAmtPerKms,
                    'driverAllowancePerDay'=>$driverAllowanceDay,
                    'driverAllowancePerNight'=>$driverAllowanceNight
                );
                return json_encode($res);
            }
        }else if($dutyType==3){
            //Airport transfer
            $tQuery = $sql->select()->from('cym_tariff_details')->where(array('package'=>$package,'rental_type'=>$dutyType));
            $tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
            $tResult=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
            if($tResult!=""){
                if($travHrs<=$tResult["tariff_hrs"]){
                    $extHrs=0;
                    $tariffId=$tResult["tariff_id"];
                    $tariffKms=$tResult["tariff_kms"];
                    $tariffAmt=$tResult["tariff_amt"];
                    
                }elseif($travHrs>$tResult["tariff_hrs"]){
                    $extHrs=$travHrs-$tResult["tariff_hrs"];
                    $tariffKms=$tResult["tariff_kms"];
                    $tariffId=$tResult["tariff_id"];
                    $tariffAmt=$tResult["tariff_amt"];
                }
                
                if($travKms<=$tariffKms){
                    $extKms=0;
                }elseif($travKms>$tariffKms){
                    $extKms=$travKms-$tariffKms;
                }
                
                $tQuery = $sql->select()->from('cym_tariff_details')->where(array('tariff_id'=>$tariffId));
                $tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
                $tariffRes=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
                
                $extraAmtInHrs=$extAmtPerHrs*$extHrs;
                $extraAmtInKms=$extAmtPerKms*$extKms;
                $res=array('extraHrs'=>$extHrs,
                       'extraKms'=>$extKms,
                       'tariff'=>$tariffId,
                       'tariffName'=>$tariffRes['tariff_name'],
                       'extraAmtInHrs'=>$extraAmtInHrs,
                       'extraAmtInKms'=>$extraAmtInKms,
                       'tariffAmt'=>$tariffAmt,
                       'extAmtPerHrs'=>$extAmtPerHrs,
                       'extAmtPerKms'=>$extAmtPerKms
                );
                return json_encode($res);
            }
        }
    }
    
    public function fetchAllCymRentals($parameters) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('package','rental_type','tariff_name');

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
                    $sOrder .= $aColumns[intval($parameters['iSortCol_' . $i])] . " " . ( $parameters['sSortDir_' . $i] ) . ",";
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
        $sQuery = $sql->select()->from(array('ctd'=>'cym_tariff_details'))
                ->columns(array('tariff_id','package','rental_type','tariffName' => new Expression("Group_Concat(DISTINCT ctd.tariff_name ORDER BY ctd.tariff_hrs SEPARATOR ',')")))
                ->join(array('rt' => 'rental_type'), "rt.type_id=ctd.rental_type", array('type_name'))
                ->group("rental_type")
                ->group("package");
		
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
        $iQuery = $sql->select()->from(array('ctd'=>'cym_tariff_details'))
                    ->group("rental_type")->group("package");
        $iQueryStr = $sql->getSqlStringForSqlObject($iQuery);
        $iResult = $dbAdapter->query($iQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);
        $iTotal = count($iResult);
        $output = array(
            "sEcho" => intval($parameters['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );
        
		$update = true;
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = strtoupper($aRow['package']);
            $row[] = ucwords($aRow['type_name']);
            $row[] = $aRow['tariffName'];
            
            if($update){
                $row[] = '<a href="../edit-rentals/' . base64_encode($aRow['rental_type']."#".$aRow['package']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }

    public function getCymRentalDetails($id){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $expId=explode("#",$id);
        $rentalType=$expId[0];
        $package=$expId[1];
        
        $query = $sql->select()->from(array('ctd'=>'cym_tariff_details'))
                    ->join(array('rt' => 'rental_type'), "rt.type_id=ctd.rental_type", array('type_name'))
                    ->where(array('rental_type'=>$rentalType,'package'=>$package));
        $queryStr = $sql->getSqlStringForSqlObject($query);
        $result = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
        if(count($result)>0){
            $cQuery = $sql->select()->from(array('ctd'=>'cym_extra_tariff_details'))
                    ->where(array('rental_type'=>$rentalType,'package'=>$package));
            $cQueryStr = $sql->getSqlStringForSqlObject($cQuery);
            $cResult = $dbAdapter->query($cQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
        }
        return array('tariff'=>$result,'extra-tariff'=>$cResult);
    }

    public function updateRentalDetails($params){
        $dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
        $cymExtraDb = new CymExtraTariffDetailsTable($dbAdapter);
        //\Zend\Debug\Debug::dump($params);die;
        if(isset($params['rentalId']) && trim($params['rentalId'])!="" && trim($params['package'])!=""){
            $params['package']=strtolower($params['package']);
            $this->delete(array('rental_type'=>$params['rentalId'],'package'=>$params['package']));
            
            $tCount=count($params['tariffHrs']);
            for($i=0;$i<$tCount;$i++){
				if(isset($params['tariffHrs'][$i])&& trim($params['tariffHrs'][$i])!=''&& $params['tariffHrs'][$i]!=null){
                    if($params['rentalId']!='2'){
                        $tariffName=$params['tariffHrs'][$i]."HRS/".$params['tariffKms'][$i]."KMS";
                    }
                    else if($params['rentalId']=='2'){
                        $tariffName=$params['tariffHrs'][$i]."Day(s)/".$params['tariffKms'][$i]."KMS";
                    }
                    

                    $data=array(
						'package' => $params['package'],
						'rental_type' => $params['rentalId'],
						'tariff_name' => $tariffName,
                        'tariff_hrs' => $params['tariffHrs'][$i],
                        'tariff_kms' => $params['tariffKms'][$i],
                        'tariff_amt' => $params['tariffAmt'][$i],
                        'next_tariff_hrs' => $params['nextTariffHrs'][$i]
                    );
                    $this->insert($data);
                    $lastInsertedId = $this->lastInsertValue;
				}
            }
            $cData=array();
            if(isset($params['perHrsPrice'])){
                $cData['extra_per_hrs_price']=$params['perHrsPrice'];
            }
            if(isset($params['perKmsPrice'])){
                $cData['extra_per_kms_price']=$params['perKmsPrice'];
            }
            if(isset($params['driverAllowanceInDay'])){
                $cData['driver_allowance_day']=$params['driverAllowanceInDay'];
            }
            if(isset($params['driverAllowanceInNight'])){
                $cData['driver_allowance_night']=$params['driverAllowanceInNight'];
            }
            
            if(count($cData)>0){
                if(trim($params['extraId'])!=""){
                    $cymExtraDb->update($cData,"extra_id=".$params['extraId']);
                }else{
                    $cData['rental_type']=$params['rentalId'];
                    $cData['package']=$params['package'];
                    $cymExtraDb->insert($cData);
                }   
            }
            return $lastInsertedId;
        }
    }
}
?>
