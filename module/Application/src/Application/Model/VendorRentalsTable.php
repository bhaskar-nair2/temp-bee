<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Model\VendorTariffDetailsTable;
use Application\Model\EventLogTable;

class VendorRentalsTable extends AbstractTableGateway {

    protected $table = 'vendor_rentals';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
	
	public function addVendorRentalDetails($params){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		//\Zend\Debug\Debug::dump($params);
		//die;
		if(trim($params['vendorId'])!=""){
			$vendorId=base64_decode($params['vendorId']);
			
			$tariffDb = new VendorTariffDetailsTable($dbAdapter);
			
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
						//'next_tariff_hrs' => $params['sedanNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '1',
						'vendor_id' => $vendorId
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
					'vendor_id' => $vendorId
				));
			//}
			
			//if(isset($params['sedanPerHrsPrice']) && trim($params['sedanPerHrsPrice'])!=""){
				$this->insert(array(
					'vendor_id' => $vendorId,
					'make_type' => 1,
					'extra_per_kms_price' => $params['sedanPerKmsPrice'],
					'extra_per_hrs_price' => $params['sedanPerHrsPrice'],
					'driver_allowance_day' => $params['sedanDriverAllowanceInDay'],
					'driver_allowance_night' => $params['sedanDriverAllowanceInNight']
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
						//'next_tariff_hrs' => $params['suvNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '2',
						'vendor_id' => $vendorId
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
					'vendor_id' => $vendorId
				));
			//}
			
			//if(isset($params['suvPerKmsPrice']) && trim($params['suvPerKmsPrice'])!=""){
				$this->insert(array(
					'vendor_id' => $vendorId,
					'make_type' => 2,
					'extra_per_kms_price' => $params['suvPerKmsPrice'],
					'extra_per_hrs_price' => $params['suvPerHrsPrice'],
					'driver_allowance_day' => $params['suvDriverAllowanceInDay'],
					'driver_allowance_night' => $params['suvDriverAllowanceInNight']
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
						//'next_tariff_hrs' => $params['premiumNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '3',
						'vendor_id' => $vendorId
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
					'vendor_id' => $vendorId
				));
			//}
			
			//if(isset($params['premiumPerHrsPrice']) && trim($params['premiumPerHrsPrice'])!=""){
				$this->insert(array(
					'vendor_id' => $vendorId,
					'make_type' => 3,
					'extra_per_kms_price' => $params['premiumPerKmsPrice'],
					'extra_per_hrs_price' => $params['premiumPerHrsPrice'],
					'driver_allowance_day' => $params['premiumDriverAllowanceInDay'],
					'driver_allowance_night' => $params['premiumDriverAllowanceInNight']
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
						//'next_tariff_hrs' => $params['luxuryNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '4',
						'vendor_id' => $vendorId
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
					'vendor_id' => $vendorId
				));
			//}
			
			//if(isset($params['luxuryPerKmsPrice']) && trim($params['luxuryPerKmsPrice'])!=""){
				$this->insert(array(
					'vendor_id' => $vendorId,
					'make_type' => 4,
					'extra_per_kms_price' => $params['luxuryPerKmsPrice'],
					'extra_per_hrs_price' => $params['luxuryPerHrsPrice'],
					'driver_allowance_day' => $params['luxuryDriverAllowanceInDay'],
					'driver_allowance_night' => $params['luxuryDriverAllowanceInNight']
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
						//'next_tariff_hrs' => $params['tempoNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '5',
						'vendor_id' => $vendorId
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
					'vendor_id' => $vendorId
				));
			//}
			
			//if(isset($params['tempoPerKmsPrice']) && trim($params['tempoPerKmsPrice'])!=""){
				$this->insert(array(
					'vendor_id' => $vendorId,
					'make_type' => 5,
					'extra_per_kms_price' => $params['tempoPerKmsPrice'],
					'extra_per_hrs_price' => $params['tempoPerHrsPrice'],
					'driver_allowance_day' => $params['tempoDriverAllowanceInDay'],
					'driver_allowance_night' => $params['tempoDriverAllowanceInNight']
				));
			//}
			
			//Tempo end ---->
			
			//event log
			$subject = $vendorId;
			$eventType = 'add-vendor-rentals';
			$action = 'added a new vendor rental ';
			$resourceName = 'VendorRentals';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $vendorId;
		}
	}
	
	public function updateVendorRentalDetails($params){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		if(trim($params['vendorId'])!=""){
			$vendorId=base64_decode($params['vendorId']);
			$tariffDb = new VendorTariffDetailsTable($dbAdapter);
			
			//<---- Sedan start
			$totalSedanCount=count($params['sedanTariffHrs']);
			for($i=0;$i<$totalSedanCount;$i++){
				$tariffName="";
				if(isset($params['sedanTariffId'][$i])&& trim($params['sedanTariffId'][$i])!=''){
					//Local Use
					$tariffName=$params['sedanTariffHrs'][$i]."HRS/".$params['sedanTariffKms'][$i]."KMS";
					
					$tariffDb->update(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['sedanTariffHrs'][$i],
						'tariff_kms' => $params['sedanTariffKms'][$i],
						'tariff_amt' => $params['sedanTariffAmt'][$i],
						//'next_tariff_hrs' => $params['sedanNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '1',
						//'vendor_id' => $vendorId
					),array('vendor_tariff_id' => base64_decode($params['sedanTariffId'][$i])));
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
					//'vendor_id' => $vendorId
				),array('vendor_tariff_id' => base64_decode($params['sedanOutstationTariffId'])));
			}
			
			//if(isset($params['sedanPerHrsPrice']) && trim($params['sedanPerHrsPrice'])!=""){
				$extraTariffResult=$this->checkVendorRental($vendorId,1);
					$data=array(
						'vendor_id' => $vendorId,
						'make_type' => 1,
						'extra_per_kms_price' => $params['sedanPerKmsPrice'],
						'extra_per_hrs_price' => $params['sedanPerHrsPrice'],
						'driver_allowance_day' => $params['sedanDriverAllowanceInDay'],
						'driver_allowance_night' => $params['sedanDriverAllowanceInNight']
					);
				if($extraTariffResult!=""){
					$this->update($data,array('vendor_id' => $vendorId,'make_type'=>1));
				}else{
					$this->insert($data);
				}
			//}
			
			//Sedan end ---->
			
			//<---- SUV start
			$totalSuvCount=count($params['suvTariffHrs']);
			for($i=0;$i<$totalSuvCount;$i++){
				$tariffName="";
				if(isset($params['suvTariffId'][$i])&& trim($params['suvTariffId'][$i])!=''){
					//Local Use
					$tariffName=$params['suvTariffHrs'][$i]."HRS/".$params['suvTariffKms'][$i]."KMS";
					
					$tariffDb->update(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['suvTariffHrs'][$i],
						'tariff_kms' => $params['suvTariffKms'][$i],
						'tariff_amt' => $params['suvTariffAmt'][$i],
						//'next_tariff_hrs' => $params['suvNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '2',
						//'vendor_id' => $vendorId
					),array('vendor_tariff_id' => base64_decode($params['suvTariffId'][$i])));
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
					//'rental_id' => $rentalId
				),array('vendor_tariff_id' => base64_decode($params['suvOutstationTariffId'])));
			}
			
			//if(isset($params['suvPerKmsPrice']) && trim($params['suvPerKmsPrice'])!=""){
				$extraTariffResult=$this->checkVendorRental($vendorId,2);
					$data=array(
						'vendor_id' => $vendorId,
						'make_type' => 2,
						'extra_per_kms_price' => $params['suvPerKmsPrice'],
						'extra_per_hrs_price' => $params['suvPerHrsPrice'],
						'driver_allowance_day' => $params['suvDriverAllowanceInDay'],
						'driver_allowance_night' => $params['suvDriverAllowanceInNight']
					);
				if($extraTariffResult!=""){
					$this->update($data,array('vendor_id' => $vendorId,'make_type'=>2));
				}else{
					$this->insert($data);
				}
			//}
			
			//SUV end ---->
			
			//<---- Premium start
			$totalPrmiumCount=count($params['premiumTariffHrs']);
			for($i=0;$i<$totalPrmiumCount;$i++){
				$tariffName="";
				if(isset($params['premiumTariffId'][$i])&& trim($params['premiumTariffId'][$i])!=''){
					//Local Use
					$tariffName=$params['premiumTariffHrs'][$i]."HRS/".$params['premiumTariffKms'][$i]."KMS";
					
					$tariffDb->update(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['premiumTariffHrs'][$i],
						'tariff_kms' => $params['premiumTariffKms'][$i],
						'tariff_amt' => $params['premiumTariffAmt'][$i],
						//'next_tariff_hrs' => $params['premiumNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '3',
						//'rental_id' => $rentalId
					),array('vendor_tariff_id' => base64_decode($params['premiumTariffId'][$i])));
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
					//'rental_id' => $rentalId
				),array('vendor_tariff_id' => base64_decode($params['premiumOutstationTariffId'])));
			}
			
			//if(isset($params['premiumPerHrsPrice']) && trim($params['premiumPerHrsPrice'])!=""){
				$extraTariffResult=$this->checkVendorRental($vendorId,3);
				$data=array(
					'vendor_id' => $vendorId,
					'make_type' => 3,
					'extra_per_kms_price' => $params['premiumPerKmsPrice'],
					'extra_per_hrs_price' => $params['premiumPerHrsPrice'],
					'driver_allowance_day' => $params['premiumDriverAllowanceInDay'],
					'driver_allowance_night' => $params['premiumDriverAllowanceInNight']
				);
				if($extraTariffResult!=""){
					$this->update($data,array('vendor_id' => $vendorId,'make_type'=>3));
				}else{
					$this->insert($data);
				}
			//}
			
			//Premium end ---->
			
			//<---- Luxury start
			$totalLuxuryCount=count($params['luxuryTariffHrs']);
			for($i=0;$i<$totalLuxuryCount;$i++){
				$tariffName="";
				if(isset($params['luxuryTariffId'][$i])&& trim($params['luxuryTariffId'][$i])!=''){
					//Local Use
					$tariffName=$params['luxuryTariffHrs'][$i]."HRS/".$params['luxuryTariffKms'][$i]."KMS";
					
					$tariffDb->update(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['luxuryTariffHrs'][$i],
						'tariff_kms' => $params['luxuryTariffKms'][$i],
						'tariff_amt' => $params['luxuryTariffAmt'][$i],
						//'next_tariff_hrs' => $params['luxuryNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '4',
						//'rental_id' => $rentalId
					),array('vendor_tariff_id' => base64_decode($params['luxuryTariffId'][$i])));
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
					//'rental_id' => $rentalId
				),array('vendor_tariff_id' => base64_decode($params['luxuryOutstationTariffId'])));
			}
			
			//if(isset($params['luxuryPerKmsPrice']) && trim($params['luxuryPerKmsPrice'])!=""){
				$extraTariffResult=$this->checkVendorRental($vendorId,4);
				$data=array(
					'vendor_id' => $vendorId,
					'make_type' => 4,
					'extra_per_kms_price' => $params['luxuryPerKmsPrice'],
					'extra_per_hrs_price' => $params['luxuryPerHrsPrice'],
					'driver_allowance_day' => $params['luxuryDriverAllowanceInDay'],
					'driver_allowance_night' => $params['luxuryDriverAllowanceInNight']
				);
				if($extraTariffResult!=""){
					$this->update($data,array('vendor_id' => $vendorId,'make_type'=>4));
				}else{
					$this->insert($data);
				}
			//}
			
			//Luxury end ---->
			
			//<---- Tempo start
			$totalTempoCount=count($params['tempoTariffHrs']);
			for($i=0;$i<$totalTempoCount;$i++){
				$tariffName="";
				if(isset($params['tempoTariffId'][$i])&& trim($params['tempoTariffId'][$i])!=''){
					//Local Use
					$tariffName=$params['tempoTariffHrs'][$i]."HRS/".$params['tempoTariffKms'][$i]."KMS";
					
					$tariffDb->update(array(
						'tariff_name' =>$tariffName,
						'tariff_hrs' => $params['tempoTariffHrs'][$i],
						'tariff_kms' => $params['tempoTariffKms'][$i],
						'tariff_amt' => $params['tempoTariffAmt'][$i],
						//'next_tariff_hrs' => $params['tempoNextTariffHrs'][$i],
						'rental_type' => '1',
						'make_type' => '5',
						//'rental_id' => $rentalId
					),array('vendor_tariff_id' => base64_decode($params['tempoTariffId'][$i])));
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
					//'rental_id' => $rentalId
				),array('vendor_tariff_id' => base64_decode($params['tempoOutstationTariffId'])));
			}
			
			//if(isset($params['tempoPerKmsPrice']) && trim($params['tempoPerKmsPrice'])!=""){
				$extraTariffResult=$this->checkVendorRental($vendorId,5);
				$data=array(
					'vendor_id' => $vendorId,
					'make_type' => 5,
					'extra_per_kms_price' => $params['tempoPerKmsPrice'],
					'extra_per_hrs_price' => $params['tempoPerHrsPrice'],
					'driver_allowance_day' => $params['tempoDriverAllowanceInDay'],
					'driver_allowance_night' => $params['tempoDriverAllowanceInNight']
				);
				if($extraTariffResult!=""){
					$this->update($data,array('vendor_id' => $vendorId,'make_type'=>5));
				}else{
					$this->insert($data);
				}
			//}
			
			//Tempo end ---->
			
			//event log
			$subject = $vendorId;
			$eventType = 'update-vendor-rentals';
			$action = 'updated a vendor rental ';
			$resourceName = 'VendorRentals';
			$eventLogDb = new EventLogTable($this->adapter);
			$eventLogDb->addEventLog($subject, $eventType, $action, $resourceName);
			return $vendorId;
		}
	}
	
	public function checkVendorRental($vendorId,$makeType){
		$dbAdapter = $this->adapter;
        $sql = new Sql($dbAdapter);
		$query = $sql->select()->from('vendor_rentals')->where(array('vendor_id'=>$vendorId,'make_type'=>$makeType));
		$queryStr = $sql->getSqlStringForSqlObject($query);
		return $dbAdapter->query($queryStr, $dbAdapter::QUERY_MODE_EXECUTE)->current();
	}
}
?>
