ALTER TABLE `monthly_billing_details` ADD `converted_gst` VARCHAR(100) NULL DEFAULT 'yes' AFTER `payment_status`;
ALTER TABLE `vendors` ADD `gst_no` VARCHAR(255) NULL DEFAULT NULL AFTER `st_no`;


INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Vendor', 'ledger', 'Manage Ledger');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Vendor', 'view-ledger', 'View Ledger');

INSERT INTO `global_config` (`global_id`, `global_name`, `global_value`, `display_name`) VALUES (NULL, 'vendor_tax', '5', 'Vendor Tax');

ALTER TABLE `vendors` ADD `current_balance` VARCHAR(255) NULL DEFAULT NULL AFTER `agreement_attachment`;

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Vendor', 'download-pdf-report', 'Download Pdf Report');

ALTER TABLE `company_details` ADD `sac_no` VARCHAR(255) NULL DEFAULT NULL AFTER `service_tax_no`;

--23-Aug-2017
CREATE TABLE `vendor_advance_payments` (
  `advance_id` INT NOT NULL AUTO_INCREMENT,
  `vendor_id` INT NOT NULL,
  `advance_month` VARCHAR(255) NULL DEFAULT NULL,
  `advance_year` VARCHAR(255) NULL DEFAULT NULL,
  `advance_date` DATE NULL DEFAULT NULL,
  `advance_amount` VARCHAR(255) NULL DEFAULT NULL,
  `Remarks` MEDIUMTEXT NULL DEFAULT NULL,
  `added_on` DATETIME NULL DEFAULT NULL,
  `added_by` INT NOT NULL,
  PRIMARY KEY (`advance_id`),
  UNIQUE KEY (`vendor_id`, `advance_date`)
);
ALTER TABLE `vendor_advance_payments` ADD FOREIGN KEY (vendor_id) REFERENCES `vendors` (`vendor_id`);
ALTER TABLE `vendor_advance_payments` ADD FOREIGN KEY (added_by) REFERENCES `employee_details` (`employee_id`);

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Vendor', 'add-advance', 'Add Advance');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Index', 'financial', 'Financial Dashboard');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Booking', 'generate-trip-sheet', 'Generate Trip Sheet');

--27-Dec-2017
ALTER TABLE `trip_driver_map` ADD `provided_vehicle_category` INT(11) NULL DEFAULT NULL AFTER `driver_mobile_no`;
ALTER TABLE `trip_driver_map` CHANGE `provided_vehicle_category` `provided_make_type` INT(11) NULL DEFAULT NULL;
ALTER TABLE `bookings` ADD `vehicle_type` INT(11) NULL DEFAULT NULL AFTER `make_type`;

--10-Jan-2018
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Booking', 'generate-bill', 'Generate Invoice');

ALTER TABLE `bookings` ADD `ext_amt_per_hrs` INT(11) NULL DEFAULT NULL AFTER `ext_hrs_amount`;
ALTER TABLE `bookings` ADD `ext_amt_per_km` INT(11) NULL DEFAULT NULL AFTER `ext_amt_per_hrs`;

ALTER TABLE `bookings` ADD `driver_allowance_per_day` VARCHAR(255) NULL DEFAULT NULL AFTER `driver_allowance`, ADD `driver_allowance_per_night` VARCHAR(255) NULL DEFAULT NULL AFTER `driver_allowance_per_day`;
ALTER TABLE `bookings` ADD `service_tax_type` VARCHAR(100) NULL DEFAULT NULL AFTER `permit`;
ALTER TABLE `bookings` ADD `sgst_tax` VARCHAR(100) NULL DEFAULT NULL AFTER `service_tax_type`;
ALTER TABLE `bookings` ADD `cgst_tax` VARCHAR(100) NULL DEFAULT NULL AFTER `sgst_tax`, ADD `igst_tax` VARCHAR(100) NULL DEFAULT NULL AFTER `cgst_tax`;
ALTER TABLE `bookings` ADD `service_tax_paid_by_client` VARCHAR(100) NULL DEFAULT NULL AFTER `permit`;
ALTER TABLE `bookings` ADD `sub_total_amount` VARCHAR(255) NULL DEFAULT NULL AFTER `permit`;

--09-Mar-2018
INSERT INTO `global_config` (`global_id`, `global_name`, `global_value`, `display_name`) VALUES (NULL, 'new_booking_receive_guest_sms', NULL, 'New Booking Receive Guest SMS');

--05-Apr-2018
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Admin\\Controller\\Hotel', 'Manage Hotel');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Hotel', 'index', 'Access'), ('Admin\\Controller\\Hotel', 'add', 'Add');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Hotel', 'edit', 'Edit');
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Admin\\Controller\\HotelBooking', 'Manage Hotel Booking');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\HotelBooking', 'index', 'Access'), ('Admin\\Controller\\HotelBooking', 'edit', 'Edit');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\HotelBooking', 'add', 'Add');

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\HotelBooking', 'complete', 'Complete');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\HotelBooking', 'cancel', 'Cancel');
ALTER TABLE `hotel_bookings` ADD `cancelled_by` VARCHAR(255) NULL DEFAULT NULL AFTER `status`;
ALTER TABLE `hotel_bookings` ADD `reason_for_cancellation` VARCHAR(255) NULL DEFAULT NULL AFTER `cancelled_by`;
ALTER TABLE `hotel_bookings` ADD `cancellation_time` VARCHAR(255) NULL DEFAULT NULL AFTER `reason_for_cancellation`;
ALTER TABLE `clients` ADD `hotel_booking_code` VARCHAR(100) NULL DEFAULT NULL AFTER `file_name`;

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\HotelBooking', 'reports', 'Reports');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\HotelBooking', 'generate-trip-sheet', 'Generate Trip Sheet');
ALTER TABLE `users` ADD `login_type` VARCHAR(255) NULL DEFAULT NULL AFTER `monthly_bill_generated_client`;
ALTER TABLE `users` ADD `client_id` INT NULL DEFAULT NULL AFTER `login_type`;

--ilahir 06-May-2018
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Admin\\Controller\\FpsHotelBooking', 'Manage FPS Hotel Booking');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\FpsHotelBooking', 'index', 'Access'), ('Admin\\Controller\\FpsHotelBooking', 'add', 'Add');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\FpsHotelBooking', 'edit', 'Edit');

ALTER TABLE `hotel_bookings` ADD `vehicle_mode` VARCHAR(255) NULL DEFAULT NULL AFTER `vehicle_type`;
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\FpsHotelBooking', 'complete', 'Complete')
ALTER TABLE `hotel_bookings` ADD `trip_sheet_no` VARCHAR(255) NULL DEFAULT NULL AFTER `booking_no`;
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\FpsHotelBooking', 'reports', 'Reports');
ALTER TABLE `hotel_bookings` ADD `revenue` VARCHAR(255) NULL DEFAULT NULL AFTER `payment_status`;

--18-may-2018
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Admin\\Controller\\Fuel', 'Fuel Management');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Fuel', 'add', 'Add'), ('Admin\\Controller\\Fuel', 'edit', 'Edit');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\Fuel', 'reports', 'Reports');


CREATE TABLE `petrol_pumps` (
  `pump_id` int(11) NOT NULL,
  `pump_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `petrol_pumps`
  ADD PRIMARY KEY (`pump_id`);

ALTER TABLE `petrol_pumps`
  MODIFY `pump_id` int(11) NOT NULL AUTO_INCREMENT;

--ilair 05-Jun-2018
INSERT INTO `resources` (`resource_id`, `display_name`) VALUES ('Admin\\Controller\\CymHotelBooking', 'Manage CYM Bookings');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\CymHotelBooking', 'add', 'Add');

INSERT INTO `rental_type` (`type_id`, `type_name`) VALUES (NULL, 'Consulate Drop'), (NULL, 'Short Transfer');

ALTER TABLE `fuel_details` ADD `total_kms` VARCHAR(255) NULL DEFAULT NULL AFTER `current_kms`;

--ilahir 28-jun-2018
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\CymHotelBooking', 'rentals', 'Rental List');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\CymHotelBooking', 'edit-rentals', 'Edit Rental');

ALTER TABLE `hotel_bookings` ADD `sevice_tax_type` VARCHAR(100) NULL DEFAULT NULL AFTER `payment_status`, ADD `sgst_tax` VARCHAR(50) NULL DEFAULT NULL AFTER `sevice_tax_type`, ADD `cgst_tax` VARCHAR(50) NULL DEFAULT NULL AFTER `sgst_tax`;
ALTER TABLE `hotel_bookings` ADD `igst_tax` VARCHAR(50) NULL DEFAULT NULL AFTER `cgst_tax`, ADD `service_tax_amt` VARCHAR(255) NULL DEFAULT NULL AFTER `igst_tax`;

INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\CymHotelBooking', 'index', 'Reports');
INSERT INTO `privileges` (`resource_id`, `privilege_name`, `display_name`) VALUES ('Admin\\Controller\\CymHotelBooking', 'edit', 'Edit');

