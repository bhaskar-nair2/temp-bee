<?php

namespace Admin;

use Application\Model\EmployeeTable;
use Application\Model\CompanyTable;
use Application\Model\RolesTable;
use Application\Model\VehicleTypeTable;
use Application\Model\VehicleModeTable;
use Application\Model\VehiclesTable;
use Application\Model\ClientsTable;
use Application\Model\GuestTable;
use Application\Model\ClientContactTable;
use Application\Model\RentalTypeTable;
use Application\Model\RentalsTable;
use Application\Model\TariffDetailsTable;
use Application\Model\PaymentModeTable;
use Application\Model\VehicleServiceTable;
use Application\Model\BranchTable;
use Application\Model\BookingsTable;
use Application\Model\MarriottDailySalesTable;
use Application\Model\CyffDailySalesTable;
use Application\Model\RetailDailySalesTable;
use Application\Model\TajDailySalesTable;
use Application\Model\TajTable;
use Application\Model\CorporateDailySalesTable;
use Application\Model\MarriottTable;
use Application\Model\BusinessUnitsTable;
use Application\Model\VehicleUsageTable;
use Application\Model\RetailTable;
use Application\Model\CorporateTable;
use Application\Model\ResourcesTable;
use Application\Model\GlobalConfigTable;
use Application\Model\MonthlyBillingTable;
use Application\Model\UserTable;
use Application\Model\OtherAmountDetailsTable;
use Application\Model\TempMailTable;
use Application\Model\EventLogTable;
use Application\Model\CompanyDocumentDetailsTable;
use Application\Model\VendorsTable;
use Application\Model\VendorVehicleMapTable;
use Application\Model\VendorPaymentsTable;
use Application\Model\VendorPaidDetailsTable;
use Application\Model\CityTable;
use Application\Model\ExtraTariffDetailsTable;
use Application\Model\VendorTariffDetailsTable;
use Application\Model\VendorRentalsTable;
use Application\Model\VehicleMakeTypeTable;
use Application\Model\TripDriverMapTable;
use Application\Model\BookingVendorPaymentsTable;
use Application\Model\SmsTemplateTable;
use Application\Model\VendorAdvanceTable;
use Application\Model\HotelTable;
use Application\Model\HotelBookingsTable;
use Application\Model\VehicleClientMapTable;
use Application\Model\PetrolPumpTable;
use Application\Model\FuelTable;
use Application\Model\CymTariffTable;
use Application\Model\CymExtraTariffDetailsTable;

use Application\Service\CommonService;
use Application\Service\EmployeeService;
use Application\Service\CompanyService;
use Application\Service\RoleService;
use Application\Service\VehicleTypeService;
use Application\Service\VehicleService;
use Application\Service\ClientService;
use Application\Service\RentalTypeService;
use Application\Service\RentalService;
use Application\Service\BookingService;
use Application\Service\MarriottService;
use Application\Service\CyffService;
use Application\Service\RetailService;
use Application\Service\TajService;
use Application\Service\CorporateService;
use Application\Service\ConfigService;
use Application\Service\BillingService;
use Application\Service\UserService;
use Application\Service\VendorService;
use Application\Service\CityService;
use Application\Service\SmsService;
use Application\Service\HotelService;
use Application\Service\HotelBookingService;
use Application\Service\FuelService;
use Application\Service\CymRentalService;

class Module {

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                
                 'EmployeeTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new EmployeeTable($dbAdapter);
                    return $table;
                },
                'CompanyTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CompanyTable($dbAdapter);
                    return $table;
                },
                'RolesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new RolesTable($dbAdapter);
                    return $table;
                },
                'VehicleTypeTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VehicleTypeTable($dbAdapter);
                    return $table;
                },
                'VehicleModeTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VehicleModeTable($dbAdapter);
                    return $table;
                },
                'VehiclesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VehiclesTable($dbAdapter);
                    return $table;
                },
                'ClientsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ClientsTable($dbAdapter);
                    return $table;
                },
                'GuestTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GuestTable($dbAdapter);
                    return $table;
                },
                'ClientContactTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ClientContactTable($dbAdapter);
                    return $table;
                },
                'RentalTypeTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new RentalTypeTable($dbAdapter);
                    return $table;
                },
                'RentalsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new RentalsTable($dbAdapter);
                    return $table;
                },
                'TariffDetailsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new TariffDetailsTable($dbAdapter);
                    return $table;
                },
                'PaymentModeTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new PaymentModeTable($dbAdapter);
                    return $table;
                },
                'VehicleServiceTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VehicleServiceTable($dbAdapter);
                    return $table;
                },
                'BranchTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new BranchTable($dbAdapter);
                    return $table;
                },
                'BookingsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new BookingsTable($dbAdapter);
                    return $table;
                },
                'MarriottDailySalesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new MarriottDailySalesTable($dbAdapter);
                    return $table;
                },
                'CyffDailySalesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CyffDailySalesTable($dbAdapter);
                    return $table;
                },
                'RetailDailySalesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new RetailDailySalesTable($dbAdapter);
                    return $table;
                },
                'TajDailySalesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new TajDailySalesTable($dbAdapter);
                    return $table;
                },
                'CorporateDailySalesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CorporateDailySalesTable($dbAdapter);
                    return $table;
                },
                'MarriottTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new MarriottTable($dbAdapter);
                    return $table;
                },
                'TajTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new TajTable($dbAdapter);
                    return $table;
                },
                'BusinessUnitsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new BusinessUnitsTable($dbAdapter);
                    return $table;
                },
                'VehicleUsageTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VehicleUsageTable($dbAdapter);
                    return $table;
                },
                'RetailTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new RetailTable($dbAdapter);
                    return $table;
                },
                'CorporateTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CorporateTable($dbAdapter);
                    return $table;
                },
                'ResourcesTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ResourcesTable($dbAdapter);
                    return $table;
                },
                'GlobalConfigTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GlobalConfigTable($dbAdapter);
                    return $table;
                },
                'MonthlyBillingTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new MonthlyBillingTable($dbAdapter);
                    return $table;
                },
                'UserTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserTable($dbAdapter);
                    return $table;
                },
                'OtherAmountDetailsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new OtherAmountDetailsTable($dbAdapter);
                    return $table;
                },
                'TempMailTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new TempMailTable($dbAdapter);
                    return $table;
                },
                'EventLogTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new EventLogTable($dbAdapter);
                    return $table;
                },
                'CompanyDocumentDetailsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CompanyDocumentDetailsTable($dbAdapter);
                    return $table;
                },
                'VendorsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VendorsTable($dbAdapter);
                    return $table;
                },
                'VendorVehicleMapTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VendorVehicleMapTable($dbAdapter);
                    return $table;
                },
                'VendorPaymentsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VendorPaymentsTable($dbAdapter);
                    return $table;
                },
                'VendorPaidDetailsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VendorPaidDetailsTable($dbAdapter);
                    return $table;
                },
                'CityTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CityTable($dbAdapter);
                    return $table;
                },
                'ExtraTariffDetailsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ExtraTariffDetailsTable($dbAdapter);
                    return $table;
                },
                'VendorTariffDetailsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VendorTariffDetailsTable($dbAdapter);
                    return $table;
                },
                'VendorRentalsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VendorRentalsTable($dbAdapter);
                    return $table;
                },
                'VehicleMakeTypeTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VehicleMakeTypeTable($dbAdapter);
                    return $table;
                },
                'TripDriverMapTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new TripDriverMapTable($dbAdapter);
                    return $table;
                },
                'BookingVendorPaymentsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new BookingVendorPaymentsTable($dbAdapter);
                    return $table;
                },
                'VendorAdvanceTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VendorAdvanceTable($dbAdapter);
                    return $table;
                },
                'SmsTemplateTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new SmsTemplateTable($dbAdapter);
                    return $table;
                },
                'HotelTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new HotelTable($dbAdapter);
                    return $table;
                },
                'HotelBookingsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new HotelBookingsTable($dbAdapter);
                    return $table;
                },
                'VehicleClientMapTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new VehicleClientMapTable($dbAdapter);
                    return $table;
                },
                'PetrolPumpTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new PetrolPumpTable($dbAdapter);
                    return $table;
                },
                'FuelTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new FuelTable($dbAdapter);
                    return $table;
                },
                'CymTariffTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CymTariffTable($dbAdapter);
                    return $table;
                },
                'CymExtraTariffDetailsTable' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new CymExtraTariffDetailsTable($dbAdapter);
                    return $table;
                },

                'CommonService' => function($sm) {
                    return new CommonService($sm);
                },
                'EmployeeService' => function($sm) {
                    return new EmployeeService($sm);
                },
                'CompanyService' => function($sm) {
                    return new CompanyService($sm);
                },
                'RoleService' => function($sm) {
                    return new RoleService($sm);
                },
                'VehicleTypeService' => function($sm) {
                    return new VehicleTypeService($sm);
                },
                'VehicleService' => function($sm) {
                    return new VehicleService($sm);
                },
                'ClientService' => function($sm) {
                    return new ClientService($sm);
                },
                'RentalTypeService' => function($sm) {
                    return new RentalTypeService($sm);
                },
                'RentalService' => function($sm) {
                    return new RentalService($sm);
                },
                'BookingService' => function($sm) {
                    return new BookingService($sm);
                },
                'MarriottService' => function($sm) {
                    return new MarriottService($sm);
                },
                'CyffService' => function($sm) {
                    return new CyffService($sm);
                },
                'RetailService' => function($sm) {
                    return new RetailService($sm);
                },
                'TajService' => function($sm) {
                    return new TajService($sm);
                },
                'CorporateService' => function($sm) {
                    return new CorporateService($sm);
                },
                'ConfigService' => function($sm) {
                    return new ConfigService($sm);
                },
                'BillingService' => function($sm) {
                    return new BillingService($sm);
                },
                'UserService' => function($sm) {
                    return new UserService($sm);
                },
                'VendorService' => function($sm) {
                    return new VendorService($sm);
                },
                'CityService' => function($sm) {
                    return new CityService($sm);
                },
                'SmsService' => function($sm) {
                    return new SmsService($sm);
                },
                'HotelService' => function($sm) {
                    return new HotelService($sm);
                },
                'HotelBookingService' => function($sm) {
                    return new HotelBookingService($sm);
                },
                'FuelService' => function($sm) {
                    return new FuelService($sm);
                },
                'CymRentalService' => function($sm) {
                    return new CymRentalService($sm);
                },
                
            ),
        );
    }

}
