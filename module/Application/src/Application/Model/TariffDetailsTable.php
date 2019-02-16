<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;


class TariffDetailsTable extends AbstractTableGateway {

    protected $table = 'tariff_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function fetchTariffByRentalId($companyId,$clientId,$businessUnit,$city,$vehicleCategory,$dutyType){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		if($companyId>0 && $clientId>0 && $businessUnit>0 && $city>0 && $vehicleCategory>0 && $dutyType>0){
			$query = $sql->select()->from('rental_details')->where(array('company_id'=>$companyId,'client_id'=>$clientId,'city'=>$city,'bussiness_unit'=>$businessUnit));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($result!=""){
				$tQuery = $sql->select()->from('tariff_details')->where(array('rental_id'=>$result['rental_id'],'make_type'=>$vehicleCategory,'rental_type'=>$dutyType));
				$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
				return $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
			}
		}
	}
	
	public function getTariffAmtDetails($params){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$travDays=$params["travDays"];
		$travHrs=$params["travHrs"];
		$travMins=$params["travMins"];
		$travKms=$params["travKms"];
		$startDate=$params["startDate"];
		$closeDate=$params["closeDate"];
		
		//\Zend\Debug\Debug::dump($params);die;
		
		$query = $sql->select()->from('rental_details')->where(array('company_id'=>$params['companyId'],'client_id'=>$params['client'],'city'=>$params['bookingCity'],'bussiness_unit'=>$params['businessUnit']));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		$result=$dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
		if($result!=""){
			//Calculation in local use
			$extKms=0;
			$extHrs=0;
			$tariffKms=0;
			$tariffId=0;
			$tariffAmt=0;
			$dayTimeDriverAllowance=0;
			$nightTimeDriverAllowance=0;
			
			$exTQuery = $sql->select()->from('extra_tariff_details')->where(array('rental_id'=>$result['rental_id'],'make_type'=>$params['vehicleCategory']));
			$exTQueryStr = $sql->getSqlStringForSqlObject($exTQuery);
			$exTResult=$dbAdapter->query($exTQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			
			if($exTResult!=""){
				$extAmtPerKms=$exTResult["extra_per_kms_price"];
				$extAmtPerHrs=$exTResult["extra_per_hrs_price"];
				$otExtAmtPerKms=$exTResult["outstation_extra_per_kms_price"];
				$driverAllowanceDay=$exTResult["driver_allowance_day"];
				$driverAllowanceNight=$exTResult["driver_allowance_night"];
			}
			//Localuse calculation
			if($params['dutyType']==1){
				$tQuery = $sql->select()->from('tariff_details')->where(array('rental_id'=>$result['rental_id'],'make_type'=>$params['vehicleCategory'],'rental_type'=>$params['dutyType']));
				$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
				$tResult=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
				foreach($tResult as $val){
					if($travHrs<=$val["next_tariff_hrs"]){
						
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
						elseif($travHrs>$val["tariff_hrs"]){
							$extHrs=$travHrs-$val["tariff_hrs"];
							$tariffKms=$val["tariff_kms"];
							$tariffId=$val["tariff_id"];
							$tariffAmt=$val["tariff_amt"];
						}
						/*
						elseif($travHrs<=$val["tariff_hrs"]){
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
						*/
					}
					elseif(trim($val["next_tariff_hrs"])=="" && $travHrs<=$val["tariff_hrs"]){
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
				
				$tQuery = $sql->select()->from('tariff_details')->where(array('tariff_id'=>$tariffId));
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
			}else if($params['dutyType']==2){
				$tQuery = $sql->select()->from('tariff_details')->where(array('rental_id'=>$result['rental_id'],'make_type'=>$params['vehicleCategory'],'rental_type'=>$params['dutyType']));
				$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
				$tResult=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
				
				$extHrs=0;
				$extKms=0;
				$extraAmtInHrs=0;
				$extraAmtInKms=0;
				$driverAllowance=0;
				$tariffAmt=0;
				$travDay=($closeDate-$startDate)+1;
				$startTime=$params["startTime"];
				$closeTime=$params["closeTime"];
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
					}else{
						if(!$startTimeCal){
							//Calculate starting time in first day
							if(strtotime($startTime)<=strtotime('22:00')){
								$dayTimeDriverAllowance+=$driverAllowanceDay;
								$driverAllowance+=$driverAllowanceDay;
								$firstAllowanceDay=true;
							}else{
								$nightTimeDriverAllowance+=$driverAllowanceNight;
								$driverAllowance+=$driverAllowanceNight;
								
							}
							$startTimeCal=true;
						}else{
							if($firstAllowanceDay){
								$nightTimeDriverAllowance+=$driverAllowanceNight;
								$driverAllowance+=$driverAllowanceNight;
								$firstAllowanceDay=false;
							}
							$dayTimeDriverAllowance+=$driverAllowanceDay;
							$nightTimeDriverAllowance+=$driverAllowanceNight;
							$driverAllowance+=$driverAllowanceDay+$driverAllowanceNight;
						}
					}
				}
				/*
				if(strtotime($startTime)<=strtotime('22:00')){
					$driverAllowance+=$driverAllowanceDay;
				}else{
					$driverAllowance+=$driverAllowanceNight;
				}
				
				if(strtotime($closeTime)<=strtotime('22:00')){
					$driverAllowance+=$driverAllowanceDay;
				}else{
					$driverAllowance+=$driverAllowanceNight;
				}
				*/
				//echo $driverAllowance;
				//die;
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
					
					$tQuery = $sql->select()->from('tariff_details')->where(array('tariff_id'=>$tariffId));
					$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
					$tariffRes=$dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
					
					//changeText=data.noOfDay+"Day(s)/"+data.tariffKms+"KMS";
					if($travDay>1){
						$tariffRes['tariff_name']=$travDay."Day(s)/".$tariffKms."KMS";
					}
					$res=array('extraHrs'=>$extHrs,
						'extraKms'=>$extKms,
						'tariff'=>$tariffId,
						'tariffName'=>$tariffRes['tariff_name'],
						'extraAmtInHrs'=>$extraAmtInHrs,
						'extraAmtInKms'=>$extraAmtInKms,
						//'driverAllowanceDay'=>$driverAllowanceDay,
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
			}else if($params['dutyType']==3){
				//Airport transfer
				$tQuery = $sql->select()->from('tariff_details')->where(array('rental_id'=>$result['rental_id'],'make_type'=>$params['vehicleCategory'],'rental_type'=>$params['dutyType']));
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
					
					$tQuery = $sql->select()->from('tariff_details')->where(array('tariff_id'=>$tariffId));
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
		}else{
			return json_encode(array());
		}
	}
}
?>
