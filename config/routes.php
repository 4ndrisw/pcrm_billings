<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['billings/billing/(:num)/(:any)'] = 'billing/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['billings/list'] = 'mybilling/list';
$route['billings/show/(:num)/(:any)'] = 'mybilling/show/$1/$2';
$route['billings/office/(:num)/(:any)'] = 'mybilling/office/$1/$2';
$route['billings/pdf/(:num)'] = 'mybilling/pdf/$1';
