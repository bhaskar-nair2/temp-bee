<?php
namespace Application\Service;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use \Application\Service\CommonService;


class BookingService {

    public $sm = null;

    public function __construct($sm) {
        $this->sm = $sm;
    }

    public function getServiceManager() {
        return $this->sm;
    }
    
    public function addBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('BookingsTable');
            $result = $db->addBookingDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Booking details added successfully - '.$params['bookingRef'];
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function updateBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('BookingsTable');
            $result = $db->updateBookingDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Booking details updated successfully - '.$params['bookingRef'];
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function executeBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('BookingsTable');
            $result = $db->executeBookingDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Assigned driver successfully';
             $generateTripSheet = new Container('tripSheet');
             $generateTripSheet->bookingId = $result;
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllPendingBookings($parameters){
        $db = $this->sm->get('BookingsTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllPendingBookings($parameters,$acl);
    }
    
    public function getBooking($bookingId){
        $db = $this->sm->get('BookingsTable');
        return $db->getBookingDetails($bookingId);
    }
    
    public function getCorporateBookingNo(){
        $db = $this->sm->get('BookingsTable');
        return $db->fetchCorporateBookingNo();
    }
    
    public function getAllExecutedBookings($parameters){
        $db = $this->sm->get('BookingsTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllExecutedBookings($parameters,$acl);
    }
    
    public function getTripAssignBooking($bookingId){
        $db = $this->sm->get('BookingsTable');
        return $db->getTripAssignBookingDetails($bookingId);
    }
    
    public function getExecutedBooking($bookingId){
        $db = $this->sm->get('BookingsTable');
        return $db->getExecutedBookingDetails($bookingId);
    }
    
    public function closeBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('BookingsTable');
            $result = $db->closeBookingDetails($params);
            if($result>0){
             $adapter->commit();
             $alertContainer = new Container('alert');
             $alertContainer->alertMsg = 'Booking closed successfully';
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllClosedBooking($parameters){
        $db = $this->sm->get('BookingsTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllClosedBooking($parameters,$acl);
    }
    
    public function getAllVendorPendingBooking($parameters){
        $db = $this->sm->get('BookingsTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllVendorPendingBooking($parameters,$acl);
    }
    
    public function getCloseBooking($bookingId){
        $db = $this->sm->get('BookingsTable');
        return $db->fetchCloseBooking($bookingId);
    }
    
    public function cancelBooking($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('BookingsTable');
            $result = $db->cancelBookingDetails($params);
            if($result>0){
             $adapter->commit();
             return $result;
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getAllCancelBookings($parameters){
        $db = $this->sm->get('BookingsTable');
        $acl = $this->sm->get('AppAcl');
        return $db->fetchAllCancelBookings($parameters,$acl);
    }
    
    public function addBookingVendorPayment($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $bookingDb = $this->sm->get('BookingsTable');
            $db = $this->sm->get('BookingVendorPaymentsTable');
            $result = $db->addBookingVendorPaymentDetails($params);
            if($result>0){
                $bookingId=base64_decode($params['bookingId']);
                $bookingDb->updateBookingStatus($bookingId,'add_vendor_payment');
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'Added vendor payment successfully';
                $adapter->commit();
                return $result;
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function deleteBooking($bookingId){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            $db = $this->sm->get('BookingsTable');
            $result = $db->deleteBookingDetails($bookingId);
            if($result>0){
                $adapter->commit();
                return $result;
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function fetchBooking($bookingId){
        $db = $this->sm->get('BookingsTable');
        return $db->fetchBookingDetails($bookingId);
    }
    
    public function getBookingVendorPayment($bookingId){
        $db = $this->sm->get('BookingVendorPaymentsTable');
        return $db->getBookingVendorPaymentDetails($bookingId);
    }
    
    public function updateBookingVendorPayment($params){
        $adapter = $this->sm->get('Zend\Db\Adapter\Adapter')->getDriver()->getConnection();
        $adapter->beginTransaction();
        try {
            //$bookingDb = $this->sm->get('BookingsTable');
            $db = $this->sm->get('BookingVendorPaymentsTable');
            $result = $db->updateBookingVendorPaymentDetails($params);
            if($result>0){
                $bookingId=base64_decode($params['bookingId']);
                //$bookingDb->updateBookingStatus($bookingId,'add_vendor_payment');
                $alertContainer = new Container('alert');
                $alertContainer->alertMsg = 'Updated vendor payment successfully';
                $adapter->commit();
                return $result;
            }
        }
        catch (Exception $exc) {
            $adapter->rollBack();
            error_log($exc->getMessage());
            error_log($exc->getTraceAsString());
        }
    }
    
    public function getTodayCurrentBookingCount(){
        $db = $this->sm->get('BookingsTable');
        return $db->fetchTodayCurrentBookingCount();
    }
    
    public function getTodayExecutedBookingCount(){
        $db = $this->sm->get('BookingsTable');
        return $db->fetchTodayExecutedBookingCount();
    }
    
    public function getTodayCancelledBookingCount(){
        $db = $this->sm->get('BookingsTable');
        return $db->fetchTodayCancelledBookingCount();
    }
}
?>

