<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;
use Application\Model\TariffDetailsTable;
use Application\Model\ExtraTariffDetailsTable;
use Application\Model\EventLogTable;

class RentalsTable extends AbstractTableGateway {

    protected $table = 'rental_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function addRentalDetails($params){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		//\Zend\Debug\Debug::dump($params);die;
		if(trim($params['company'])!="" && trim($params['client'])!="" && trim($params['city'])!=""){
			$data = array(
                'company_id' => $params['company'],
                'client_id' => $params['client'],
                'city' => $params['city'],
                'bussiness_unit' => $params['businessUnit']
            );
			$this->insert($data);
			$lastInsertedId = $this->lastInsertValue;
			$tariffDb = new TariffDetailsTable($dbAdapter);
			$extraTariffDb = new ExtraTariffDetailsTable($dbAdapter);
			
			
			//<---- Hatchback start
			$totalSedanCount=count($params['hatchbackTariffHrs']);
			for($i=0;$i<$totalSedanCount;$i++){
				$tariffName="";
				//if(isset($params['hatchbackTariffHrs'][$i])&& trim($params['hatchbackTariffHrs'][$i])!=''&& trim($params['hatchbackTariffKms'][$i])!='' && trim($params['hatchbackTariffAmt'][$i])!=''){
					//Local Use
					$tariffName=$params['hatchbackTariffHrs'][$i]."HRS/".$params['hatchbackTariffKms'][$i]."KMS";
					
					$tariffDb->insert(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['hatchbackTariffHrs'][$i],
						'tariff_kms' => $params['hatchbackTariffKms'][$i],
						'tariff_amt' => $params['hatchbackTariffAmt'][$i],
						'next_tariff_hrs' => $params['hatchbackNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '6',
						'rental_id' => $lastInsertedId
					));
				//}
			}
			
			//if(isset($params['hatchbackOutstationKmsPerDay']) && trim($params['hatchbackOutstationKmsPerDay'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['hatchbackOutstationKmsPerDay']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['hatchbackOutstationKmsPerDay'],
					'tariff_amt' => $params['hatchbackOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '6',
					'rental_id' => $lastInsertedId
				));
			//}
			
			//Airport transfer
			if(isset($params['hatchbackAirportTariffHrs']) && trim($params['hatchbackAirportTariffHrs'])!="" && trim($params['hatchbackAirportTariffKms'])!=""){
				$tariffName=$params['hatchbackAirportTariffHrs']."HRS/".$params['hatchbackAirportTariffKms']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['hatchbackAirportTariffHrs'],
					'tariff_kms' => $params['hatchbackAirportTariffKms'],
					'tariff_amt' => $params['hatchbackAirportTariffAmt'],
					//'next_tariff_hrs' => $params['hatchbackAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '6',
					'rental_id' => $lastInsertedId
				));
			}
			
			//if(isset($params['hatchbackPerHrsPrice']) && trim($params['hatchbackPerHrsPrice'])!=""){
				$extraTariffDb->insert(array(
					'rental_id' => $lastInsertedId,
					'make_type' => 6,
					'extra_per_kms_price' => $params['hatchbackPerKmsPrice'],
					'extra_per_hrs_price' => $params['hatchbackPerHrsPrice'],
					'driver_allowance_day' => $params['hatchbackDriverAllowanceInDay'],
					'driver_allowance_night' => $params['hatchbackDriverAllowanceInNight'],
					'outstation_extra_per_kms_price' => $params['hatchbackOutstationPerKmsPrice']
				));
			//}
			
			//Hatchback end ---->
			
			
			//<---- Sedan start
			$totalSedanCount=count($params['sedanTariffHrs']);
			for($i=0;$i<$totalSedanCount;$i++){
				$tariffName="";
				//if(isset($params['sedanTariffHrs'][$i])&& trim($params['sedanTariffHrs'][$i])!=''&& trim($params['sedanTariffKms'][$i])!='' && trim($params['sedanTariffAmt'][$i])!=''){
					//Local Use
					$tariffName=$params['sedanTariffHrs'][$i]."HRS/".$params['sedanTariffKms'][$i]."KMS";
					
					$tariffDb->insert(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['sedanTariffHrs'][$i],
						'tariff_kms' => $params['sedanTariffKms'][$i],
						'tariff_amt' => $params['sedanTariffAmt'][$i],
						'next_tariff_hrs' => $params['sedanNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '1',
						'rental_id' => $lastInsertedId
					));
				//}
			}
			
			//if(isset($params['sedanOutstationKmsPerDay']) && trim($params['sedanOutstationKmsPerDay'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['sedanOutstationKmsPerDay']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['sedanOutstationKmsPerDay'],
					'tariff_amt' => $params['sedanOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '1',
					'rental_id' => $lastInsertedId
				));
			//}
			
			//Airport transfer
			if(isset($params['sedanAirportTariffHrs']) && trim($params['sedanAirportTariffHrs'])!="" && trim($params['sedanAirportTariffKms'])!=""){
				$tariffName=$params['sedanAirportTariffHrs']."HRS/".$params['sedanAirportTariffKms']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['sedanAirportTariffHrs'],
					'tariff_kms' => $params['sedanAirportTariffKms'],
					'tariff_amt' => $params['sedanAirportTariffAmt'],
					//'next_tariff_hrs' => $params['sedanAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '1',
					'rental_id' => $lastInsertedId
				));
			}
			
			
			
			//if(isset($params['sedanPerHrsPrice']) && trim($params['sedanPerHrsPrice'])!=""){
				$extraTariffDb->insert(array(
					'rental_id' => $lastInsertedId,
					'make_type' => 1,
					'extra_per_kms_price' => $params['sedanPerKmsPrice'],
					'extra_per_hrs_price' => $params['sedanPerHrsPrice'],
					'driver_allowance_day' => $params['sedanDriverAllowanceInDay'],
					'driver_allowance_night' => $params['sedanDriverAllowanceInNight'],
					'outstation_extra_per_kms_price' => $params['sedanOutstationPerKmsPrice']
				));
			//}
			
			//Sedan end ---->
			
			//<---- SUV start
			$totalSuvCount=count($params['suvTariffHrs']);
			for($i=0;$i<$totalSuvCount;$i++){
				$tariffName="";
				//if(isset($params['suvTariffHrs'][$i])&& trim($params['suvTariffHrs'][$i])!=''&& trim($params['suvTariffKms'][$i])!='' && trim($params['suvTariffAmt'][$i])!=''){
					//Local Use
					$tariffName=$params['suvTariffHrs'][$i]."HRS/".$params['suvTariffKms'][$i]."KMS";
					
					$tariffDb->insert(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['suvTariffHrs'][$i],
						'tariff_kms' => $params['suvTariffKms'][$i],
						'tariff_amt' => $params['suvTariffAmt'][$i],
						'next_tariff_hrs' => $params['suvNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '2',
						'rental_id' => $lastInsertedId
					));
				//}
			}
			
			//if(isset($params['suvOutstationKmsPerDay']) && trim($params['suvOutstationKmsPerDay'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['suvOutstationKmsPerDay']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['suvOutstationKmsPerDay'],
					'tariff_amt' => $params['suvOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '2',
					'rental_id' => $lastInsertedId
				));
			//}
			
			//Airport transfer
			if(isset($params['suvAirportTariffHrs']) && trim($params['suvAirportTariffHrs'])!="" && trim($params['suvAirportTariffKms'])!=""){
				$tariffName=$params['suvAirportTariffHrs']."HRS/".$params['suvAirportTariffKms']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['suvAirportTariffHrs'],
					'tariff_kms' => $params['suvAirportTariffKms'],
					'tariff_amt' => $params['suvAirportTariffAmt'],
					//'next_tariff_hrs' => $params['suvAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '2',
					'rental_id' => $lastInsertedId
				));
			}
			
			//if(isset($params['suvPerKmsPrice']) && trim($params['suvPerKmsPrice'])!=""){
				$extraTariffDb->insert(array(
					'rental_id' => $lastInsertedId,
					'make_type' => 2,
					'extra_per_kms_price' => $params['suvPerKmsPrice'],
					'extra_per_hrs_price' => $params['suvPerHrsPrice'],
					'driver_allowance_day' => $params['suvDriverAllowanceInDay'],
					'driver_allowance_night' => $params['suvDriverAllowanceInNight'],
					'outstation_extra_per_kms_price' => $params['suvOutstationPerKmsPrice']
				));
			//}
			
			//SUV end ---->
			
			//<---- Premium start
			$totalPrmiumCount=count($params['premiumTariffHrs']);
			for($i=0;$i<$totalPrmiumCount;$i++){
				$tariffName="";
				//if(isset($params['premiumTariffHrs'][$i])&& trim($params['premiumTariffHrs'][$i])!=''&& trim($params['premiumTariffKms'][$i])!='' && trim($params['premiumTariffAmt'][$i])!=''){
					//Local Use
					$tariffName=$params['premiumTariffHrs'][$i]."HRS/".$params['premiumTariffKms'][$i]."KMS";
					
					$tariffDb->insert(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['premiumTariffHrs'][$i],
						'tariff_kms' => $params['premiumTariffKms'][$i],
						'tariff_amt' => $params['premiumTariffAmt'][$i],
						'next_tariff_hrs' => $params['premiumNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '3',
						'rental_id' => $lastInsertedId
					));
				//}
			}
			
			//if(isset($params['premiumOutstationKmsPerDay']) && trim($params['premiumOutstationKmsPerDay'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['premiumOutstationKmsPerDay']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['premiumOutstationKmsPerDay'],
					'tariff_amt' => $params['premiumOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '3',
					'rental_id' => $lastInsertedId
				));
			//}
			
			//Airport transfer
			if(isset($params['premiumAirportTariffHrs']) && trim($params['premiumAirportTariffHrs'])!="" && trim($params['premiumAirportTariffKms'])!=""){
				$tariffName=$params['premiumAirportTariffHrs']."HRS/".$params['premiumAirportTariffKms']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['premiumAirportTariffHrs'],
					'tariff_kms' => $params['premiumAirportTariffKms'],
					'tariff_amt' => $params['premiumAirportTariffAmt'],
					//'next_tariff_hrs' => $params['premiumAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '3',
					'rental_id' => $lastInsertedId
				));
			}
			
			//if(isset($params['premiumPerHrsPrice']) && trim($params['premiumPerHrsPrice'])!=""){
				$extraTariffDb->insert(array(
					'rental_id' => $lastInsertedId,
					'make_type' => 3,
					'extra_per_kms_price' => $params['premiumPerKmsPrice'],
					'extra_per_hrs_price' => $params['premiumPerHrsPrice'],
					'driver_allowance_day' => $params['premiumDriverAllowanceInDay'],
					'driver_allowance_night' => $params['premiumDriverAllowanceInNight'],
					'outstation_extra_per_kms_price' => $params['premiumOutstationPerKmsPrice']
				));
			//}
			
			//Premium end ---->
			
			//<---- Luxury start
			$totalLuxuryCount=count($params['luxuryTariffHrs']);
			for($i=0;$i<$totalLuxuryCount;$i++){
				$tariffName="";
				//if(isset($params['luxuryTariffHrs'][$i])&& trim($params['luxuryTariffHrs'][$i])!=''&& trim($params['luxuryTariffKms'][$i])!='' && trim($params['luxuryTariffAmt'][$i])!=''){
					//Local Use
					$tariffName=$params['luxuryTariffHrs'][$i]."HRS/".$params['luxuryTariffKms'][$i]."KMS";
					
					$tariffDb->insert(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['luxuryTariffHrs'][$i],
						'tariff_kms' => $params['luxuryTariffKms'][$i],
						'tariff_amt' => $params['luxuryTariffAmt'][$i],
						'next_tariff_hrs' => $params['luxuryNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '4',
						'rental_id' => $lastInsertedId
					));
				//}
			}
			
			//if(isset($params['luxuryOutstationKmsPerDay']) && trim($params['luxuryOutstationKmsPerDay'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['luxuryOutstationKmsPerDay']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['luxuryOutstationKmsPerDay'],
					'tariff_amt' => $params['luxuryOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '4',
					'rental_id' => $lastInsertedId
				));
			//}
			
			//Airport transfer
			if(isset($params['luxuryAirportTariffHrs']) && trim($params['luxuryAirportTariffHrs'])!="" && trim($params['luxuryAirportTariffKms'])!=""){
				$tariffName=$params['luxuryAirportTariffHrs']."HRS/".$params['luxuryAirportTariffKms']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['luxuryAirportTariffHrs'],
					'tariff_kms' => $params['luxuryAirportTariffKms'],
					'tariff_amt' => $params['luxuryAirportTariffAmt'],
					//'next_tariff_hrs' => $params['luxuryAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '4',
					'rental_id' => $lastInsertedId
				));
			}
			
			//if(isset($params['luxuryPerKmsPrice']) && trim($params['luxuryPerKmsPrice'])!=""){
				$extraTariffDb->insert(array(
					'rental_id' => $lastInsertedId,
					'make_type' => 4,
					'extra_per_kms_price' => $params['luxuryPerKmsPrice'],
					'extra_per_hrs_price' => $params['luxuryPerHrsPrice'],
					'driver_allowance_day' => $params['luxuryDriverAllowanceInDay'],
					'driver_allowance_night' => $params['luxuryDriverAllowanceInNight'],
					'outstation_extra_per_kms_price' => $params['luxuryOutstationPerKmsPrice']
				));
			//}
			
			//Luxury end ---->
			
			//<---- Tempo start
			$totalTempoCount=count($params['tempoTariffHrs']);
			for($i=0;$i<$totalTempoCount;$i++){
				$tariffName="";
				//if(isset($params['tempoTariffHrs'][$i])&& trim($params['tempoTariffHrs'][$i])!=''&& trim($params['tempoTariffKms'][$i])!='' && trim($params['tempoTariffAmt'][$i])!=''){
					//Local Use
					$tariffName=$params['tempoTariffHrs'][$i]."HRS/".$params['tempoTariffKms'][$i]."KMS";
					
					$tariffDb->insert(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['tempoTariffHrs'][$i],
						'tariff_kms' => $params['tempoTariffKms'][$i],
						'tariff_amt' => $params['tempoTariffAmt'][$i],
						'next_tariff_hrs' => $params['tempoNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '5',
						'rental_id' => $lastInsertedId
					));
				//}
			}
			
			//if(isset($params['tempoOutstationKmsPerDay']) && trim($params['tempoOutstationKmsPerDay'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['tempoOutstationKmsPerDay']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['tempoOutstationKmsPerDay'],
					'tariff_amt' => $params['tempoOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '5',
					'rental_id' => $lastInsertedId
				));
			//}
			
			//Airport transfer
			if(isset($params['tempoAirportTariffHrs']) && trim($params['tempoAirportTariffHrs'])!="" && trim($params['tempoAirportTariffKms'])!=""){
				$tariffName=$params['tempoAirportTariffHrs']."HRS/".$params['tempoAirportTariffKms']."KMS";
				$tariffDb->insert(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['tempoAirportTariffHrs'],
					'tariff_kms' => $params['tempoAirportTariffKms'],
					'tariff_amt' => $params['tempoAirportTariffAmt'],
					//'next_tariff_hrs' => $params['tempoAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '5',
					'rental_id' => $lastInsertedId
				));
			}
			
			//if(isset($params['tempoPerKmsPrice']) && trim($params['tempoPerKmsPrice'])!=""){
				$extraTariffDb->insert(array(
					'rental_id' => $lastInsertedId,
					'make_type' => 5,
					'extra_per_kms_price' => $params['tempoPerKmsPrice'],
					'extra_per_hrs_price' => $params['tempoPerHrsPrice'],
					'driver_allowance_day' => $params['tempoDriverAllowanceInDay'],
					'driver_allowance_night' => $params['tempoDriverAllowanceInNight'],
					'outstation_extra_per_kms_price' => $params['tempoOutstationPerKmsPrice']
				));
			//}
			
			//Tempo end ---->
			
			//event log
			$subject = $lastInsertedId;
			$eventType = 'rentals-add';
			$action = 'added a new rental ';
			$resourceName = 'Rentals';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $lastInsertedId;
		}
	}
	
	public function updateRentalDetails($params){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		if(trim($params['company'])!="" && trim($params['rentalId'])!=""){
			$rentalId=base64_decode($params['rentalId']);
			$data = array(
                'company_id' => $params['company'],
                'client_id' => $params['client'],
                'city' => $params['city'],
                'bussiness_unit' => $params['businessUnit']
            );
			
			$this->update($data, array('rental_id' => $rentalId));
			
			$tariffDb = new TariffDetailsTable($dbAdapter);
			$extraTariffDb = new ExtraTariffDetailsTable($dbAdapter);
			
			if (isset($params['deletedSedanId']) && trim($params['deletedSedanId']) != "") {
				$deletedId = explode(",", $params['deletedSedanId']);
				$totalId = count($deletedId);
				for ($z = 0; $z < $totalId; $z++) {
					$tariffDb->delete("tariff_id=".$deletedId[$z]);
				}
			}
			
			//<---- Hatchback start
			$totalSedanCount=count($params['hatchbackTariffHrs']);
			for($i=0;$i<$totalSedanCount;$i++){
				$tariffName="";
				if(isset($params['hatchbackTariffHrs'][$i])&& trim($params['hatchbackTariffHrs'][$i])!=''){
					//Local Use
					$tariffName=$params['hatchbackTariffHrs'][$i]."HRS/".$params['hatchbackTariffKms'][$i]."KMS";
					
					$data=array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['hatchbackTariffHrs'][$i],
						'tariff_kms' => $params['hatchbackTariffKms'][$i],
						'tariff_amt' => $params['hatchbackTariffAmt'][$i],
						'next_tariff_hrs' => $params['hatchbackNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '6',
						'rental_id' => $rentalId
					);
					if(isset($params['hatchbackTariffId'][$i]) && trim($params['hatchbackTariffId'][$i])!=""){
						$tariffDb->update($data,array('tariff_id' => base64_decode($params['hatchbackTariffId'][$i])));	
					}else{
						$tariffDb->insert($data);
					}
				}
			}
			
			if(isset($params['hatchbackOutstationTariffId']) && trim($params['hatchbackOutstationTariffId'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['hatchbackOutstationKmsPerDay']."KMS";
				$tariffDb->update(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['hatchbackOutstationKmsPerDay'],
					'tariff_amt' => $params['hatchbackOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '6',
					'rental_id' => $rentalId
				),array('tariff_id' => base64_decode($params['hatchbackOutstationTariffId'])));
			}
			
			//Airport transfer
			if(isset($params['hatchbackAirportTariffHrs']) && trim($params['hatchbackAirportTariffHrs'])!="" && trim($params['hatchbackAirportTariffKms'])!=""){
				$tariffName=$params['hatchbackAirportTariffHrs']."HRS/".$params['hatchbackAirportTariffKms']."KMS";
				$data=array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['hatchbackAirportTariffHrs'],
					'tariff_kms' => $params['hatchbackAirportTariffKms'],
					'tariff_amt' => $params['hatchbackAirportTariffAmt'],
					//'next_tariff_hrs' => $params['sedanAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '6',
					'rental_id' => $rentalId
				);
				if(isset($params['hatchbackAirportTariffId']) && trim($params['hatchbackAirportTariffId'])!=""){
					$tariffDb->update($data,array('tariff_id' => base64_decode($params['hatchbackAirportTariffId'])));
				}else{
					$tariffDb->insert($data);
				}
			}
			
			//if(isset($params['sedanPerHrsPrice']) && trim($params['sedanPerHrsPrice'])!=""){
				$extraTariffResult=$extraTariffDb->checkExtraTariff($rentalId,1);
					$data=array(
						'rental_id' => $rentalId,
						'make_type' => 6,
						'extra_per_kms_price' => $params['hatchbackPerKmsPrice'],
						'extra_per_hrs_price' => $params['hatchbackPerHrsPrice'],
						'driver_allowance_day' => $params['hatchbackDriverAllowanceInDay'],
						'driver_allowance_night' => $params['hatchbackDriverAllowanceInNight'],
						'outstation_extra_per_kms_price' => $params['hatchbackOutstationPerKmsPrice']
					);
				if($extraTariffResult!=""){
					$extraTariffDb->update($data,array('rental_id' => $rentalId,'make_type'=>6));
				}else{
					$extraTariffDb->insert($data);
				}
			//}
			
			//Hatchback end ---->
			
			
			//<---- Sedan start
			$totalSedanCount=count($params['sedanTariffHrs']);
			for($i=0;$i<$totalSedanCount;$i++){
				$tariffName="";
				if(isset($params['sedanTariffHrs'][$i])&& trim($params['sedanTariffHrs'][$i])!=''){
					//Local Use
					$tariffName=$params['sedanTariffHrs'][$i]."HRS/".$params['sedanTariffKms'][$i]."KMS";
					
					$data=array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['sedanTariffHrs'][$i],
						'tariff_kms' => $params['sedanTariffKms'][$i],
						'tariff_amt' => $params['sedanTariffAmt'][$i],
						'next_tariff_hrs' => $params['sedanNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '1',
						'rental_id' => $rentalId
					);
					if(isset($params['sedanTariffId'][$i]) && trim($params['sedanTariffId'][$i])!=""){
						$tariffDb->update($data,array('tariff_id' => base64_decode($params['sedanTariffId'][$i])));	
					}else{
						$tariffDb->insert($data);
					}
				}
			}
			
			if(isset($params['sedanOutstationTariffId']) && trim($params['sedanOutstationTariffId'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['sedanOutstationKmsPerDay']."KMS";
				$tariffDb->update(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['sedanOutstationKmsPerDay'],
					'tariff_amt' => $params['sedanOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '1',
					'rental_id' => $rentalId
				),array('tariff_id' => base64_decode($params['sedanOutstationTariffId'])));
			}
			
			//Airport transfer
			if(isset($params['sedanAirportTariffHrs']) && trim($params['sedanAirportTariffHrs'])!="" && trim($params['sedanAirportTariffKms'])!=""){
				$tariffName=$params['sedanAirportTariffHrs']."HRS/".$params['sedanAirportTariffKms']."KMS";
				$data=array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['sedanAirportTariffHrs'],
					'tariff_kms' => $params['sedanAirportTariffKms'],
					'tariff_amt' => $params['sedanAirportTariffAmt'],
					//'next_tariff_hrs' => $params['sedanAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '1',
					'rental_id' => $rentalId
				);
				if(isset($params['sedanAirportTariffId']) && trim($params['sedanAirportTariffId'])!=""){
					$tariffDb->update($data,array('tariff_id' => base64_decode($params['sedanAirportTariffId'])));
				}else{
					$tariffDb->insert($data);
				}
			}
			
			//if(isset($params['sedanPerHrsPrice']) && trim($params['sedanPerHrsPrice'])!=""){
				$extraTariffResult=$extraTariffDb->checkExtraTariff($rentalId,1);
					$data=array(
						'rental_id' => $rentalId,
						'make_type' => 1,
						'extra_per_kms_price' => $params['sedanPerKmsPrice'],
						'extra_per_hrs_price' => $params['sedanPerHrsPrice'],
						'driver_allowance_day' => $params['sedanDriverAllowanceInDay'],
						'driver_allowance_night' => $params['sedanDriverAllowanceInNight'],
						'outstation_extra_per_kms_price' => $params['sedanOutstationPerKmsPrice']
					);
				if($extraTariffResult!=""){
					$extraTariffDb->update($data,array('rental_id' => $rentalId,'make_type'=>1));
				}else{
					$extraTariffDb->insert($data);
				}
			//}
			
			//Sedan end ---->
			
			//<---- SUV start
			
			if (isset($params['deletedSuvId']) && trim($params['deletedSuvId']) != "") {
				$deletedId = explode(",", $params['deletedSuvId']);
				$totalId = count($deletedId);
				for ($z = 0; $z < $totalId; $z++) {
					$tariffDb->delete("tariff_id=".$deletedId[$z]);
				}
			}
			
			$totalSuvCount=count($params['suvTariffHrs']);
			for($i=0;$i<$totalSuvCount;$i++){
				$tariffName="";
				if(isset($params['suvTariffHrs'][$i])&& trim($params['suvTariffHrs'][$i])!=''){
					//Local Use
					$tariffName=$params['suvTariffHrs'][$i]."HRS/".$params['suvTariffKms'][$i]."KMS";
					$data=array(
							'tariff_name' =>$tariffName,
							'tariff_hrs' => $params['suvTariffHrs'][$i],
							'tariff_kms' => $params['suvTariffKms'][$i],
							'tariff_amt' => $params['suvTariffAmt'][$i],
							'next_tariff_hrs' => $params['suvNextTariffHrs'][$i],
							'rental_type' => '1',
							'make_type' => '2',
							'rental_id' => $rentalId
						);
					if(isset($params['suvTariffId'][$i])&& trim($params['suvTariffId'][$i])!=''){
						$tariffDb->update($data,array('tariff_id' => base64_decode($params['suvTariffId'][$i])));
					}else{
						$tariffDb->insert($data);
					}
				}
			}
			
			if(isset($params['suvOutstationTariffId']) && trim($params['suvOutstationTariffId'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['suvOutstationKmsPerDay']."KMS";
				$tariffDb->update(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['suvOutstationKmsPerDay'],
					'tariff_amt' => $params['suvOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '2',
					'rental_id' => $rentalId
				),array('tariff_id' => base64_decode($params['suvOutstationTariffId'])));
			}
			
			//Airport transfer
			if(isset($params['suvAirportTariffHrs']) && trim($params['suvAirportTariffHrs'])!="" && trim($params['suvAirportTariffKms'])!=""){
				$tariffName=$params['suvAirportTariffHrs']."HRS/".$params['suvAirportTariffKms']."KMS";
				$data=array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['suvAirportTariffHrs'],
					'tariff_kms' => $params['suvAirportTariffKms'],
					'tariff_amt' => $params['suvAirportTariffAmt'],
					//'next_tariff_hrs' => $params['suvAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '2',
					'rental_id' => $rentalId
				);
				if(isset($params['suvAirportTariffId'])&& trim($params['suvAirportTariffId'])!=''){
					$tariffDb->update($data,array('tariff_id' => base64_decode($params['suvAirportTariffId'])));
				}else{
					$tariffDb->insert($data);
				}
			}
			
			//if(isset($params['suvPerKmsPrice']) && trim($params['suvPerKmsPrice'])!=""){
				$extraTariffResult=$extraTariffDb->checkExtraTariff($rentalId,2);
					$data=array(
						'rental_id' => $rentalId,
						'make_type' => 2,
						'extra_per_kms_price' => $params['suvPerKmsPrice'],
						'extra_per_hrs_price' => $params['suvPerHrsPrice'],
						'driver_allowance_day' => $params['suvDriverAllowanceInDay'],
						'driver_allowance_night' => $params['suvDriverAllowanceInNight'],
						'outstation_extra_per_kms_price' => $params['suvOutstationPerKmsPrice']
					);
				if($extraTariffResult!=""){
					$extraTariffDb->update($data,array('rental_id' => $rentalId,'make_type'=>2));
				}else{
					$extraTariffDb->insert($data);
				}
			//}
			
			//SUV end ---->
			
			//<---- Premium start
			
			if (isset($params['deletedPremiumId']) && trim($params['deletedPremiumId']) != "") {
				$deletedId = explode(",", $params['deletedPremiumId']);
				$totalId = count($deletedId);
				for ($z = 0; $z < $totalId; $z++) {
					$tariffDb->delete("tariff_id=".$deletedId[$z]);
				}
			}
			
			$totalPrmiumCount=count($params['premiumTariffHrs']);
			for($i=0;$i<$totalPrmiumCount;$i++){
				$tariffName="";
				if(isset($params['premiumTariffHrs'][$i])&& trim($params['premiumTariffHrs'][$i])!=''){
					//Local Use
					$tariffName=$params['premiumTariffHrs'][$i]."HRS/".$params['premiumTariffKms'][$i]."KMS";
					$data=array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['premiumTariffHrs'][$i],
						'tariff_kms' => $params['premiumTariffKms'][$i],
						'tariff_amt' => $params['premiumTariffAmt'][$i],
						'next_tariff_hrs' => $params['premiumNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '3',
						'rental_id' => $rentalId
					);
					if(isset($params['premiumTariffId'][$i])&& trim($params['premiumTariffId'][$i])!=''){
						$tariffDb->update($data,array('tariff_id' => base64_decode($params['premiumTariffId'][$i])));	
					}else{
						$tariffDb->insert($data);
					}
					
				}
			}
			
			if(isset($params['premiumOutstationTariffId']) && trim($params['premiumOutstationTariffId'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['premiumOutstationKmsPerDay']."KMS";
				$tariffDb->update(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['premiumOutstationKmsPerDay'],
					'tariff_amt' => $params['premiumOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '3',
					'rental_id' => $rentalId
				),array('tariff_id' => base64_decode($params['premiumOutstationTariffId'])));
			}
			
			//Airport transfer
			if(isset($params['premiumAirportTariffHrs']) && trim($params['premiumAirportTariffHrs'])!="" && trim($params['premiumAirportTariffKms'])!=""){
				$tariffName=$params['premiumAirportTariffHrs']."HRS/".$params['premiumAirportTariffKms']."KMS";
				$data=array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['premiumAirportTariffHrs'],
					'tariff_kms' => $params['premiumAirportTariffKms'],
					'tariff_amt' => $params['premiumAirportTariffAmt'],
					//'next_tariff_hrs' => $params['premiumAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '3',
					'rental_id' => $rentalId
				);
				
				if(isset($params['premiumAirportTariffId'])&& trim($params['premiumAirportTariffId'])!=''){
					$tariffDb->update($data,array('tariff_id' => base64_decode($params['premiumAirportTariffId'])));
				}else{
					$tariffDb->insert($data);
				}
				
			}
			
			//if(isset($params['premiumPerHrsPrice']) && trim($params['premiumPerHrsPrice'])!=""){
				$extraTariffResult=$extraTariffDb->checkExtraTariff($rentalId,3);
				$data=array(
					'rental_id' => $rentalId,
					'make_type' => 3,
					'extra_per_kms_price' => $params['premiumPerKmsPrice'],
					'extra_per_hrs_price' => $params['premiumPerHrsPrice'],
					'driver_allowance_day' => $params['premiumDriverAllowanceInDay'],
					'driver_allowance_night' => $params['premiumDriverAllowanceInNight'],
					'outstation_extra_per_kms_price' => $params['premiumOutstationPerKmsPrice']
				);
				if($extraTariffResult!=""){
					$extraTariffDb->update($data,array('rental_id' => $rentalId,'make_type'=>3));
				}else{
					$extraTariffDb->insert($data);
				}
			//}
			
			//Premium end ---->
			
			//<---- Luxury start
			
			if (isset($params['deletedLuxuryId']) && trim($params['deletedLuxuryId']) != "") {
				$deletedId = explode(",", $params['deletedLuxuryId']);
				$totalId = count($deletedId);
				for ($z = 0; $z < $totalId; $z++) {
					$tariffDb->delete("tariff_id=".$deletedId[$z]);
				}
			}
			
			$totalLuxuryCount=count($params['luxuryTariffHrs']);
			for($i=0;$i<$totalLuxuryCount;$i++){
				$tariffName="";
				if(isset($params['luxuryTariffHrs'][$i])&& trim($params['luxuryTariffHrs'][$i])!=''){
					//Local Use
					$tariffName=$params['luxuryTariffHrs'][$i]."HRS/".$params['luxuryTariffKms'][$i]."KMS";
					$data=array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['luxuryTariffHrs'][$i],
						'tariff_kms' => $params['luxuryTariffKms'][$i],
						'tariff_amt' => $params['luxuryTariffAmt'][$i],
						'next_tariff_hrs' => $params['luxuryNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '4',
						'rental_id' => $rentalId
					);
					if(isset($params['luxuryTariffId'][$i])&& trim($params['luxuryTariffId'][$i])!=''){
						$tariffDb->update($data,array('tariff_id' => base64_decode($params['luxuryTariffId'][$i])));	
					}else{
						$tariffDb->insert($data);
					}
				}
			}
			
			if(isset($params['luxuryOutstationTariffId']) && trim($params['luxuryOutstationTariffId'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['luxuryOutstationKmsPerDay']."KMS";
				$tariffDb->update(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['luxuryOutstationKmsPerDay'],
					'tariff_amt' => $params['luxuryOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '4',
					'rental_id' => $rentalId
				),array('tariff_id' => base64_decode($params['luxuryOutstationTariffId'])));
			}
			
			//Airport transfer
			if(isset($params['luxuryAirportTariffHrs']) && trim($params['luxuryAirportTariffHrs'])!="" && trim($params['luxuryAirportTariffKms'])!=""){
				$tariffName=$params['luxuryAirportTariffHrs']."HRS/".$params['luxuryAirportTariffKms']."KMS";
				$data=array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['luxuryAirportTariffHrs'],
					'tariff_kms' => $params['luxuryAirportTariffKms'],
					'tariff_amt' => $params['luxuryAirportTariffAmt'],
					//'next_tariff_hrs' => $params['luxuryAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '4',
					'rental_id' => $rentalId
				);
				if(isset($params['luxuryAirportTariffId'])&& trim($params['luxuryAirportTariffId'])!=''){
					$tariffDb->update($data,array('tariff_id' => base64_decode($params['luxuryAirportTariffId'])));
				}else{
					$tariffDb->insert($data);
				}
			}
			
			//if(isset($params['luxuryPerKmsPrice']) && trim($params['luxuryPerKmsPrice'])!=""){
				$extraTariffResult=$extraTariffDb->checkExtraTariff($rentalId,4);
				$data=array(
					'rental_id' => $rentalId,
					'make_type' => 4,
					'extra_per_kms_price' => $params['luxuryPerKmsPrice'],
					'extra_per_hrs_price' => $params['luxuryPerHrsPrice'],
					'driver_allowance_day' => $params['luxuryDriverAllowanceInDay'],
					'driver_allowance_night' => $params['luxuryDriverAllowanceInNight'],
					'outstation_extra_per_kms_price' => $params['luxuryOutstationPerKmsPrice']
				);
				if($extraTariffResult!=""){
					$extraTariffDb->update($data,array('rental_id' => $rentalId,'make_type'=>4));
				}else{
					$extraTariffDb->insert($data);
				}
			//}
			
			//Luxury end ---->
			
			//<---- Tempo start
			
			if (isset($params['deletedTempoId']) && trim($params['deletedTempoId']) != "") {
				$deletedId = explode(",", $params['deletedTempoId']);
				$totalId = count($deletedId);
				for ($z = 0; $z < $totalId; $z++) {
					$tariffDb->delete("tariff_id=".$deletedId[$z]);
				}
			}
			
			$totalTempoCount=count($params['tempoTariffHrs']);
			for($i=0;$i<$totalTempoCount;$i++){
				$tariffName="";
				if(isset($params['tempoTariffHrs'][$i])&& trim($params['tempoTariffHrs'][$i])!=''){
					//Local Use
					$tariffName=$params['tempoTariffHrs'][$i]."HRS/".$params['tempoTariffKms'][$i]."KMS";
					$data=array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['tempoTariffHrs'][$i],
						'tariff_kms' => $params['tempoTariffKms'][$i],
						'tariff_amt' => $params['tempoTariffAmt'][$i],
						'next_tariff_hrs' => $params['tempoNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '5',
						'rental_id' => $rentalId
					);
					if(isset($params['tempoTariffId'][$i])&& trim($params['tempoTariffId'][$i])!=''){
						$tariffDb->update($data,array('tariff_id' => base64_decode($params['tempoTariffId'][$i])));	
					}else{
						$tariffDb->insert($data);
					}
				}
			}
			
			if(isset($params['tempoOutstationTariffId']) && trim($params['tempoOutstationTariffId'])!=""){
				//Outstation
				$tariffName="1Day(s)/".$params['tempoOutstationKmsPerDay']."KMS";
				$tariffDb->update(array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => 1,
					'tariff_kms' => $params['tempoOutstationKmsPerDay'],
					'tariff_amt' => $params['tempoOutstationAmtPerDay'],
					'rental_type' => '2',
					'make_type' => '5',
					'rental_id' => $rentalId
				),array('tariff_id' => base64_decode($params['tempoOutstationTariffId'])));
			}
			
			//Airport transfer
			if(isset($params['tempoAirportTariffHrs']) && trim($params['tempoAirportTariffHrs'])!="" && trim($params['tempoAirportTariffKms'])!=""){
				$tariffName=$params['tempoAirportTariffHrs']."HRS/".$params['tempoAirportTariffKms']."KMS";
				$data=array(
					'tariff_name' =>$tariffName,
					'tariff_hrs' => $params['tempoAirportTariffHrs'],
					'tariff_kms' => $params['tempoAirportTariffKms'],
					'tariff_amt' => $params['tempoAirportTariffAmt'],
					//'next_tariff_hrs' => $params['tempoAirportNextTariffHrs'],
					'rental_type' => '3',
					'make_type' => '5',
					'rental_id' => $rentalId
				);
				
				if(isset($params['tempoAirportTariffId'])&& trim($params['tempoAirportTariffId'])!=''){
					$tariffDb->update($data,array('tariff_id' => base64_decode($params['tempoAirportTariffId'])));
				}else{
					$tariffDb->insert($data);
				}
			}
			
			//if(isset($params['tempoPerKmsPrice']) && trim($params['tempoPerKmsPrice'])!=""){
				$extraTariffResult=$extraTariffDb->checkExtraTariff($rentalId,5);
				$data=array(
					'rental_id' => $rentalId,
					'make_type' => 5,
					'extra_per_kms_price' => $params['tempoPerKmsPrice'],
					'extra_per_hrs_price' => $params['tempoPerHrsPrice'],
					'driver_allowance_day' => $params['tempoDriverAllowanceInDay'],
					'driver_allowance_night' => $params['tempoDriverAllowanceInNight'],
					'outstation_extra_per_kms_price' => $params['tempoOutstationPerKmsPrice']
				);
				if($extraTariffResult!=""){
					$extraTariffDb->update($data,array('rental_id' => $rentalId,'make_type'=>5));
				}else{
					$extraTariffDb->insert($data);
				}
			//}
			
			//Tempo end ---->
			
			//event log
			$subject = $rentalId;
			$eventType = 'rentals-update';
			$action = 'updated a rental ';
			$resourceName = 'Rentals';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $rentalId;
		}
	}
	
	public function fetchAllRentals($parameters) {
        /* Array of database columns which should be read and sent back to DataTables. Use a space where
         * you want to insert a non-database field (for example a counter or static image)
        */

        $aColumns = array('v.client_name','c.company_name','ci.city_name','bu.unit_name');

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
        $sQuery = $sql->select()->from(array('r'=>'rental_details'))
				->join(array('c' => 'company_details'), "c.company_id=r.company_id", array('company_name'))
				->join(array('v' => 'clients'), "v.client_id=r.client_id", array('client_name'))
				->join(array('ci' => 'city_details'), "ci.city_id=r.city", array('city_name'))
				->join(array('bu' => 'business_units'), "bu.unit_id=r.bussiness_unit", array('unit_name'));
				//->join(array('td' => 'tariff_details'), "td.rental_id=r.rental_id", array('tariffName' => new Expression("Group_Concat(DISTINCT td.tariff_name ORDER BY td.tariff_hrs SEPARATOR ',')")),"left")
				
				
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
        //error_log($sQueryForm);
        $rResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE);

        /* Data set length after filtering */
        $sQuery->reset('limit');
        $sQuery->reset('offset');
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
        
		$update = true;
        
        foreach ($rResult as $aRow) {
            $row = array();
            $row[] = ucwords($aRow['client_name']);
            $row[] = ucwords($aRow['company_name']);
            $row[] = $aRow['city_name'];
            $row[] = $aRow['unit_name'];
            if($update){
            $row[] = '<a href="./edit/' . base64_encode($aRow['rental_id']) . '" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
            $output['aaData'][] = $row;
        }
        return $output;
    }
	
	public function getRentalDetails($rentalId){
		if($rentalId>0){
			$tariff = array();
			$dbAdapter = $this->adapter;
			$sql = new Sql($dbAdapter);
			$query = $sql->select()->from('rental_details')->where(array('rental_id'=>$rentalId));
			$queryStr = $sql->getSqlStringForSqlObject($query);
			$rentResult = $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
			if($rentResult!=""){
				$sQuery = $sql->select()->from('vahicle_make_type');
				$sQueryStr = $sql->getSqlStringForSqlObject($sQuery);
				$makeResult = $dbAdapter->query($sQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
				
				foreach($makeResult as $make){
					$tQuery = $sql->select()->from('tariff_details')->where(array('rental_id'=>$rentalId,'make_type'=>$make['make_id'],'rental_type'=>1));
					$tQueryStr = $sql->getSqlStringForSqlObject($tQuery);
					$tariff[$make['make_type']]['localuse'] = $dbAdapter->query($tQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
					
					$toQuery = $sql->select()->from('tariff_details')->where(array('rental_id'=>$rentalId,'make_type'=>$make['make_id'],'rental_type'=>2));
					$tOutQueryStr = $sql->getSqlStringForSqlObject($toQuery);
					$tariff[$make['make_type']]['outstation'] = $dbAdapter->query($tOutQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
					
					$aQuery = $sql->select()->from('tariff_details')->where(array('rental_id'=>$rentalId,'make_type'=>$make['make_id'],'rental_type'=>3));
					$aQueryStr = $sql->getSqlStringForSqlObject($aQuery);
					$tariff[$make['make_type']]['airport'] = $dbAdapter->query($aQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
					
					$eQuery = $sql->select()->from('extra_tariff_details')->where(array('rental_id'=>$rentalId,'make_type'=>$make['make_id']));
					$eQueryStr = $sql->getSqlStringForSqlObject($eQuery);
					$tariff[$make['make_type']]['extra_tariff'] = $dbAdapter->query($eQueryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
					
				}
			}
			$result=array('rent'=>$rentResult,'tariff'=>$tariff);
			return $result;
		}
	}
	
	public function fetchVehicleTypeByVendor($vendorId,$companyId,$bookingType){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('rd'=>'rental_details'))
						->columns(array('vehicleType' => new Expression("Group_Concat(DISTINCT rd.vehicle_type)")))
						->join(array('vt' => 'vehicle_type'), "vt.type_id=rd.vehicle_type", array('type_id','type_name'))
						->where(array('rd.company_id'=>$companyId,'rd.client_id'=>$vendorId,'rd.booking_type'=>$bookingType))
						->group('rd.rental_id');
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
	
	public function fetchRentalTypeByVehicle($clientId,$companyId,$vehicleType,$bookingType){
		$dbAdapter = $this->adapter;
		$sql = new Sql($dbAdapter);
		$query = $sql->select()->from(array('rd'=>'rental_details'))
						->join(array('rt' => 'rental_type'), "rt.type_id=rd.rental_type", array('type_id','type_name'))
						->where(array('rd.company_id'=>$companyId,'rd.client_id'=>$clientId,'rd.vehicle_type'=>$vehicleType,'rd.booking_type'=>$bookingType));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->toArray();
	}
}
?>
