<?php

defined('BASEPATH') or exit('No direct script access allowed');


require_once('install/billings.php');
require_once('install/billing_activity.php');
require_once('install/billing_comments.php');
require_once('install/billing_notes.php');

$CI->db->query("
INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('billing', 'billing-send-to-client', 'english', 'Send billing to Customer', 'billing # {billing_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached billing <strong># {billing_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>billing status:</strong> {billing_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-already-send', 'english', 'billing Already Sent to Customer', 'billing # {billing_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your billing request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-declined-to-staff', 'english', 'billing Declined (Sent to Staff)', 'Customer Declined billing', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined billing with number <strong># {billing_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-accepted-to-staff', 'english', 'billing Accepted (Sent to Staff)', 'Customer Accepted billing', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted billing with number <strong># {billing_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-thank-you-to-customer', 'english', 'Thank You Email (Sent to Customer After Accept)', 'Thank for you accepting billing', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank for for accepting the billing.</span><br /> <br /><span style=\"font-size: 12pt;\">We look forward to doing business with you.</span><br /> <br /><span style=\"font-size: 12pt;\">We will contact you as soon as possible.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-expiry-reminder', 'english', 'billing Expiration Reminder', 'billing Expiration Reminder', '<p><span style=\"font-size: 12pt;\">Hello {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The billing with <strong># {billing_number}</strong> will expire on <strong>{billing_expirydate}</strong></span><br /><br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span></p>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-send-to-client', 'english', 'Send billing to Customer', 'billing # {billing_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached billing <strong># {billing_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>billing status:</strong> {billing_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-already-send', 'english', 'billing Already Sent to Customer', 'billing # {billing_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your billing request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-declined-to-staff', 'english', 'billing Declined (Sent to Staff)', 'Customer Declined billing', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined billing with number <strong># {billing_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-accepted-to-staff', 'english', 'billing Accepted (Sent to Staff)', 'Customer Accepted billing', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted billing with number <strong># {billing_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'staff-added-as-project-member', 'english', 'Staff Added as Project Member', 'New project assigned to you', '<p>Hi <br /><br />New billing has been assigned to you.<br /><br />You can view the billing on the following link <a href=\"{billing_link}\">billing__number</a><br /><br />{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('billing', 'billing-accepted-to-staff', 'english', 'billing Accepted (Sent to Staff)', 'Customer Accepted billing', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted billing with number <strong># {billing_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the billing on the following link: <a href=\"{billing_link}\">{billing_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0);
");
/*
 *
 */

// Add options for billings
add_option('delete_only_on_last_billing', 1);
add_option('billing_prefix', 'INV-');
add_option('receipt_prefix', 'KWT-');
add_option('next_billing_number', 1);
add_option('default_billing_assigned', 9);
add_option('billing_number_decrement_on_delete', 0);
add_option('billing_number_format', 4);
add_option('billing_year', date('Y'));
add_option('exclude_billing_from_client_area_with_draft_status', 1);



add_option('predefined_client_note_billing', '
--Nama Rekening : NAMA_AKUN,
--Nomor Rekening : NOMOR_REKENING,
--Nama Bank : Bank Syariah Indonesia (BSI) - Kota Serang,
    ');

add_option(

    ');

add_option('billing_due_after', 1);
add_option('allow_staff_view_billings_assigned', 1);
add_option('show_assigned_on_billings', 1);
add_option('require_client_logged_in_to_view_billing', 1);

add_option('show_project_on_billing', 1);
add_option('billings_pipeline_limit', 1);
add_option('default_billings_pipeline_sort', 1);
add_option('billing_accept_identity_confirmation', 1);
add_option('billing_qrcode_size', '160');
add_option('billing_send_telegram_message', 0);

add_option('next_billing_number',1);
add_option('billing_number_format',4);
