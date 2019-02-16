<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
             'admin-home' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin[/:action][/]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'company' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/company[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Company',
                        'action' => 'index',
                    ),
                ),
            ),
            'roles' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/roles[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Roles',
                        'action' => 'index',
                    ),
                ),
            ),
            'vehicle-type' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/vehicle-type[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\VehicleType',
                        'action' => 'index',
                    ),
                ),
            ),
            'vehicles' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/vehicles[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Vehicles',
                        'action' => 'index',
                    ),
                ),
            ),
            'clients' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/clients[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Clients',
                        'action' => 'index',
                    ),
                ),
            ),
            'employee' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/employee[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Employee',
                        'action' => 'index',
                    ),
                ),
            ),
            'rentals' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/rentals[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Rentals',
                        'action' => 'index',
                    ),
                ),
            ),
            'vehicle-service' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/vehicle-service[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\VehicleService',
                        'action' => 'index',
                    ),
                ),
            ),
            'booking' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/booking[/:action][/][:id][/:comingFrom]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Booking',
                        'action' => 'index',
                    ),
                ),
            ),
            'marriott' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/marriott[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Marriott',
                        'action' => 'index',
                    ),
                ),
            ),
            'cyff' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/cyff[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Cyff',
                        'action' => 'index',
                    ),
                ),
            ),
            'retail' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/retail[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Retail',
                        'action' => 'index',
                    ),
                ),
            ),
            'taj' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/taj[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Taj',
                        'action' => 'index',
                    ),
                ),
            ),
            'corporate' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/corporate[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Corporate',
                        'action' => 'index',
                    ),
                ),
            ),
            'vehicle-usage' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/vehicle-usage[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\VehicleUsage',
                        'action' => 'index',
                    ),
                ),
            ),
            'config' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/config[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Config',
                        'action' => 'index',
                    ),
                ),
            ),
            'monthly-billing' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/monthly-billing[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\MonthlyBilling',
                        'action' => 'index',
                    ),
                ),
            ),
            'user' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'index',
                    ),
                ),
            ),
            'vendor' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/vendor[/:action][/][:id][/:fDate][/:tDate]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Vendor',
                        'action' => 'index',
                    ),
                ),
            ),
            'city' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/city[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\City',
                        'action' => 'index',
                    ),
                ),
            ),
            'sms' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/sms[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Sms',
                        'action' => 'index',
                    ),
                ),
            ),
            'hotel-booking' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/hotel-booking[/:action][/][:id][/:comingFrom]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\HotelBooking',
                        'action' => 'index',
                    ),
                ),
            ),
            'hotel' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/hotel[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Hotel',
                        'action' => 'index',
                    ),
                ),
            ),
            'fps-hotel-booking' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/fps-hotel-booking[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\FpsHotelBooking',
                        'action' => 'index',
                    ),
                ),
            ),
            'fuel' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/fuel[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Fuel',
                        'action' => 'index',
                    ),
                ),
            ),
            'cym-hotel-booking' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/cym-hotel-booking[/:action][/][:id]',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\CymHotelBooking',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Company' => 'Admin\Controller\CompanyController',
            'Admin\Controller\Roles' => 'Admin\Controller\RolesController',
            'Admin\Controller\VehicleType' => 'Admin\Controller\VehicleTypeController',
            'Admin\Controller\Vehicles' => 'Admin\Controller\VehiclesController',
            'Admin\Controller\Clients' => 'Admin\Controller\ClientsController',
            'Admin\Controller\Employee' => 'Admin\Controller\EmployeeController',
            'Admin\Controller\Rentals' => 'Admin\Controller\RentalsController',
            'Admin\Controller\VehicleService' => 'Admin\Controller\VehicleServiceController',
            'Admin\Controller\Booking' => 'Admin\Controller\BookingController',
            'Admin\Controller\Marriott' => 'Admin\Controller\MarriottController',
            'Admin\Controller\Cyff' => 'Admin\Controller\CyffController',
            'Admin\Controller\Retail' => 'Admin\Controller\RetailController',
            'Admin\Controller\Taj' => 'Admin\Controller\TajController',
            'Admin\Controller\Corporate' => 'Admin\Controller\CorporateController',
            'Admin\Controller\VehicleUsage' => 'Admin\Controller\VehicleUsageController',
            'Admin\Controller\Config' => 'Admin\Controller\ConfigController',
            'Admin\Controller\MonthlyBilling' => 'Admin\Controller\MonthlyBillingController',
            'Admin\Controller\User' => 'Admin\Controller\UserController',
            'Admin\Controller\Vendor' => 'Admin\Controller\VendorController',
            'Admin\Controller\City' => 'Admin\Controller\CityController',
            'Admin\Controller\Sms' => 'Admin\Controller\SmsController',
            'Admin\Controller\Hotel' => 'Admin\Controller\HotelController',
            'Admin\Controller\HotelBooking' => 'Admin\Controller\HotelBookingController',
            'Admin\Controller\FpsHotelBooking' => 'Admin\Controller\FpsHotelBookingController',
            'Admin\Controller\Fuel' => 'Admin\Controller\FuelController',
            'Admin\Controller\CymHotelBooking' => 'Admin\Controller\CymHotelBookingController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
