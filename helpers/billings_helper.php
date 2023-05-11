<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Injects theme CSS
 * @return null
 */
function billings_head_component()
{
        echo '<link rel="stylesheet" type="text/css" id="billings-css" href="'. base_url('modules/billings/assets/css/billings.css').'">';
    $CI = &get_instance();
    if (($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'billings') ||
        $CI->uri->segment(1) == 'billings'){
    }
}


/**
 * Injects theme CSS
 * @return null
 */
function billings_footer_js_component()
{
        echo '<script src="' . base_url('modules/billings/assets/js/billings.js') . '"></script>';
    $CI = &get_instance();
    if (($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'billings') ||
        ($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'list_billings') ||
        $CI->uri->segment(1) == 'billings'){
    }
}


/**
 * Prepare general billing pdf
 * @since  Version 1.0.2
 * @param  object $billing billing as object with all necessary fields
 * @param  string $tag tag for bulk pdf exporter
 * @return mixed object
 */
function billing_pdf($billing, $tag = '')
{
    return app_pdf('billing',  module_libs_path(BILLINGS_MODULE_NAME) . 'pdf/Billing_pdf', $billing, $tag);
}


/**
 * Get billing short_url
 * @since  Version 2.7.3
 * @param  object $billing
 * @return string Url
 */
function get_billing_shortlink($billing)
{
    $long_url = site_url("billing/{$billing->id}/{$billing->hash}");
    if (!get_option('bitly_access_token')) {
        return $long_url;
    }

    // Check if billing has short link, if yes return short link
    if (!empty($billing->short_link)) {
        return $billing->short_link;
    }

    // Create short link and return the newly created short link
    $short_link = app_generate_short_link([
        'long_url' => $long_url,
        'title'    => format_billing_number($billing->id),
    ]);

    if ($short_link) {
        $CI = &get_instance();
        $CI->db->where('id', $billing->id);
        $CI->db->update(db_prefix() . 'billings', [
            'short_link' => $short_link,
        ]);

        return $short_link;
    }

    return $long_url;
}

/**
 * Check if billing hash is equal
 * @param  mixed $id   billing id
 * @param  string $hash billing hash
 * @return void
 */
function check_billing_restrictions($id, $hash)
{
    $CI = &get_instance();
    $CI->load->model('billings_model');
    if (!$hash || !$id) {
        show_404();
    }
    $billing = $CI->billings_model->get($id);
    if (!$billing || ($billing->hash != $hash)) {
        show_404();
    }
}

/**
 * Check if billing email template for expiry reminders is enabled
 * @return boolean
 */
function is_billings_email_expiry_reminder_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'billing-expiry-reminder', 'active' => 1]) > 0;
}

/**
 * Check if there are sources for sending billing expiry reminders
 * Will be either email or SMS
 * @return boolean
 */
function is_billings_expiry_reminders_enabled()
{
    return is_billings_email_expiry_reminder_enabled() || is_sms_trigger_active(SMS_TRIGGER_PROPOSAL_EXP_REMINDER);
}

/**
 * Return billing status color class based on twitter bootstrap
 * @param  mixed  $id
 * @param  boolean $replace_default_by_muted
 * @return string
 */
function billing_status_color_class($id, $replace_default_by_muted = false)
{
    if ($id == 1) {
        $class = 'default';
    } elseif ($id == 2) {
        $class = 'danger';
    } elseif ($id == 3) {
        $class = 'success';
    } elseif ($id == 4 || $id == 5) {
        // status sent and revised
        $class = 'info';
    } elseif ($id == 6) {
        $class = 'default';
    }
    if ($class == 'default') {
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    }

    return $class;
}
/**
 * Format billing status with label or not
 * @param  mixed  $status  billing status id
 * @param  string  $classes additional label classes
 * @param  boolean $label   to include the label or return just translated text
 * @return string
 */
function format_billing_status($status, $classes = '', $label = true)
{
    $id = $status;
    if ($status == 1) {
        $status      = _l('billing_status_draft');
        $label_class = 'default';
    } elseif ($status == 2) {
        $status      = _l('billing_status_declined');
        $label_class = 'danger';
    } elseif ($status == 3) {
        $status      = _l('billing_status_accepted');
        $label_class = 'success';
    } elseif ($status == 4) {
        $status      = _l('billing_status_sent');
        $label_class = 'info';
    } elseif ($status == 5) {
        $status      = _l('billing_status_expired');
        $label_class = 'warning';
    } elseif ($status == 6) {
        $status      = _l('billing_status_approved');
        $label_class = 'success';
    }

    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status billing-status-' . $id . '">' . $status . '</span>';
    }

    return $status;
}

/**
 * Function that format billing number based on the prefix option and the billing id
 * @param  mixed $id billing id
 * @return string
 */
/*
function format_billing_number($id)
{
    $format = get_option('billing_prefix') . str_pad($id, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);

    return hooks()->apply_filters('billing_number_format', $format, $id);
}
*/


/**
 * Format billing number based on description
 * @param  mixed $id
 * @return string
 */
function format_billing_number($id)
{
    $CI = &get_instance();
    $CI->db->select('date,number,prefix,number_format')->from(db_prefix() . 'billings')->where('id', $id);
    $billing = $CI->db->get()->row();

    if (!$billing) {
        return '';
    }

    $number = billing_number_format($billing->number, $billing->number_format, $billing->prefix, $billing->date);

    return hooks()->apply_filters('format_billing_number', $number, [
        'id'       => $id,
        'billing' => $billing,
    ]);
}


function billing_number_format($number, $format, $applied_prefix, $date)
{
    $originalNumber = $number;
    $prefixPadding  = get_option('number_padding_prefixes');
    if ($format == 1) {
        // Number based
        $number = $applied_prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT);
    } elseif ($format == 2) {
        // Year based
        $number = $applied_prefix . date('Y', strtotime($date)) . '.' . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT);
    } elseif ($format == 3) {
        // Number-yy based
        $number = $applied_prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT) . '-' . date('y', strtotime($date));
    } elseif ($format == 4) {
        // Number-mm-yyyy based
        $number = $applied_prefix . str_pad($number, $prefixPadding, '0', STR_PAD_LEFT) . '.' . date('m', strtotime($date)) . '.' . date('Y', strtotime($date));
    }

    return hooks()->apply_filters('billing_number_format', $number, [
        'format'         => $format,
        'date'           => $date,
        'number'         => $originalNumber,
        'prefix_padding' => $prefixPadding,
    ]);
}


/**
 * Function that return billing item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_billing_item_taxes($itemid)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'billing');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}


/**
 * Calculate billing percent by status
 * @param  mixed $status          billing status
 * @param  mixed $total_billings in case the total is calculated in other place
 * @return array
 */
function get_billings_percent_by_status($status, $total_billings = '')
{
    $has_permission_view                 = has_permission('billings', '', 'view');
    $has_permission_view_own             = has_permission('billings', '', 'view_own');
    $allow_staff_view_billings_assigned = get_option('allow_staff_view_billings_assigned');
    $staffId                             = get_staff_user_id();

    $whereUser = '';
    if (!$has_permission_view) {
        if ($has_permission_view_own) {
            $whereUser = '(addedfrom=' . $staffId;
            if ($allow_staff_view_billings_assigned == 1) {
                $whereUser .= ' OR assigned=' . $staffId;
            }
            $whereUser .= ')';
        } else {
            $whereUser .= 'assigned=' . $staffId;
        }
    }

    if (!is_numeric($total_billings)) {
        $total_billings = total_rows(db_prefix() . 'billings', $whereUser);
    }

    $data            = [];
    $total_by_status = 0;
    $where           = 'status=' . get_instance()->db->escape_str($status);
    if (!$has_permission_view) {
        $where .= ' AND (' . $whereUser . ')';
    }

    $total_by_status = total_rows(db_prefix() . 'billings', $where);
    $percent         = ($total_billings > 0 ? number_format(($total_by_status * 100) / $total_billings, 2) : 0);

    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_billings;

    return $data;
}

/**
 * Function that will search possible billing templates in applicaion/views/admin/billing/templates
 * Will return any found files and user will be able to add new template
 * @return array
 */
function get_billing_templates()
{
    $billing_templates = [];
    if (is_dir(VIEWPATH . 'admin/billings/templates')) {
        foreach (list_files(VIEWPATH . 'admin/billings/templates') as $template) {
            $billing_templates[] = $template;
        }
    }

    return $billing_templates;
}
/**
 * Check if staff member can view billing
 * @param  mixed $id billing id
 * @param  mixed $staff_id
 * @return boolean
 */
function user_can_view_billing($id, $staff_id = false)
{
    $CI = &get_instance();

    $staff_id = $staff_id ? $staff_id : get_staff_user_id();

    if (has_permission('billings', $staff_id, 'view')) {
        return true;
    }

    $CI->db->select('id, addedfrom, assigned');
    $CI->db->from(db_prefix() . 'billings');
    $CI->db->where('id', $id);
    $billing = $CI->db->get()->row();

    if ((has_permission('billings', $staff_id, 'view_own') && $billing->addedfrom == $staff_id)
        || ($billing->assigned == $staff_id && get_option('allow_staff_view_billings_assigned') == 1)
    ) {
        return true;
    }

    return false;
}
function parse_billing_content_merge_fields($billing)
{
    $id = is_array($billing) ? $billing['id'] : $billing->id;
    $CI = &get_instance();

    $CI->load->library('merge_fields/billings_merge_fields');
    $CI->load->library('merge_fields/other_merge_fields');

    $merge_fields = [];
    $merge_fields = array_merge($merge_fields, $CI->billings_merge_fields->format($id));
    $merge_fields = array_merge($merge_fields, $CI->other_merge_fields->format());
    foreach ($merge_fields as $key => $val) {
        $content = is_array($billing) ? $billing['content'] : $billing->content;

        if (stripos($content, $key) !== false) {
            if (is_array($billing)) {
                $billing['content'] = str_ireplace($key, $val, $content);
            } else {
                $billing->content = str_ireplace($key, $val, $content);
            }
        } else {
            if (is_array($billing)) {
                $billing['content'] = str_ireplace($key, '', $content);
            } else {
                $billing->content = str_ireplace($key, '', $content);
            }
        }
    }

    return $billing;
}

/**
 * Check if staff member have assigned billings / added as sale agent
 * @param  mixed $staff_id staff id to check
 * @return boolean
 */
function staff_has_assigned_billings($staff_id = '')
{
    $CI       = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-billings-' . $staff_id);
    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'billings', ['assigned' => $staff_id]);
        $CI->app_object_cache->add('staff-total-assigned-billings-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}

function get_billings_sql_where_staff($staff_id)
{
    $has_permission_view_own            = has_permission('billings', '', 'view_own');
    $allow_staff_view_invoices_assigned = get_option('allow_staff_view_billings_assigned');
    $CI                                 = &get_instance();

    $whereUser = '';
    if ($has_permission_view_own) {
        $whereUser = '((' . db_prefix() . 'billings.addedfrom=' . $CI->db->escape_str($staff_id) . ' AND ' . db_prefix() . 'billings.addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "billings" AND capability="view_own"))';
        if ($allow_staff_view_invoices_assigned == 1) {
            $whereUser .= ' OR assigned=' . $CI->db->escape_str($staff_id);
        }
        $whereUser .= ')';
    } else {
        $whereUser .= 'assigned=' . $CI->db->escape_str($staff_id);
    }

    return $whereUser;
}



if (!function_exists('format_billing_info')) {
    /**
     * Format billing info format
     * @param  object $billing billing from database
     * @param  string $for      where this info will be used? Admin area, HTML preview?
     * @return string
     */
    function format_billing_info($billing, $for = '')
    {
        $format = get_option('customer_info_format');

        $countryCode = '';
        $countryName = '';

        if ($country = get_country($billing->billing_country)) {
            $countryCode = $country->iso2;
            $countryName = $country->short_name;
        }
        $billingTo = '<b>' . get_company_name($billing->clientid) . '</b>';
        $phone      = $billing->phone;
        $email      = $billing->email;

        if ($for == 'admin') {
            $hrefAttrs = '';
            if ($billing->rel_type == 'lead') {
                $hrefAttrs = ' href="#" onclick="init_lead(' . $billing->rel_id . '); return false;" data-toggle="tooltip" data-title="' . _l('lead') . '"';
            } else {
                $hrefAttrs = ' href="' . admin_url('clients/client/' . $billing->rel_id) . '" data-toggle="tooltip" data-title="' . _l('client') . '"';
            }
            $billingTo = '<a' . $hrefAttrs . '>' . $billingTo . '</a>';
        }

        if ($for == 'html' || $for == 'admin') {
            $phone = '<a href="tel:' . $billing->phone . '">' . $billing->phone . '</a>';
            $email = '<a href="mailto:' . $billing->email . '">' . $billing->email . '</a>';
        }
        
        $billingStreet = $billing->billing_street;
        $billingCity = $billing->billing_city;
        $billingState = $billing->billing_state;
        $billingZip = $billing->billing_zip;

        if($billing->include_shipping && $billing->show_shipping_on_billing){
            $billingStreet = $billing->shipping_street;
            $billingCity = $billing->shipping_city;
            $billingState = $billing->shipping_state;
            $billingZip = $billing->shipping_zip;
        }

        $format = _info_format_replace('company_name', $billingTo, $format);
        $format = _info_format_replace('street', $billingStreet, $format);
        $format = _info_format_replace('city', $billingCity, $format);
        $format = _info_format_replace('state', $billingState, $format);

        $format = _info_format_replace('country_code', $countryCode, $format);
        $format = _info_format_replace('country_name', $countryName, $format);

        $format = _info_format_replace('zip_code', $billingZip, $format);
        $format = _info_format_replace('phone', $phone, $format);
        $format = _info_format_replace('email', $email, $format);

        $whereCF = [];
        if (is_custom_fields_for_customers_portal()) {
            $whereCF['show_on_client_portal'] = 1;
        }

        // If no custom fields found replace all custom fields merge fields to empty
        $format = _maybe_remove_first_and_last_br_tag($format);

        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return hooks()->apply_filters('billing_info_text', $format, ['billing' => $billing, 'for' => $for]);
    }
}


/**
 * Prepares email template preview $data for the view
 * @param  string $template    template class name
 * @param  mixed $customer_id_or_email customer ID to fetch the primary contact email or email
 * @return array
 */
function billing_prepare_mail_preview_data($template, $customer_id_or_email, $mailClassParams = [])
{
    $CI = &get_instance();

    if (is_numeric($customer_id_or_email)) {
        $contact = $CI->clients_model->get_contact(get_primary_contact_user_id($customer_id_or_email));
        $email   = $contact ? $contact->email : '';
    } else {
        $email = $customer_id_or_email;
    }

    $CI->load->model('emails_model');

    $data['template'] = $CI->billing_mail_template->prepare($email, $template);
    $slug             = $CI->billing_mail_template->get_default_property_value('slug', $template, $mailClassParams);

    $data['template_name'] = $slug;

    $template_result = $CI->emails_model->get(['slug' => $slug, 'language' => 'english'], 'row');

    $data['template_system_name'] = $template_result->name;
    $data['template_id']          = $template_result->emailtemplateid;

    $data['template_disabled'] = $template_result->active == 0;

    return $data;
}


function billing_get_mail_template_path($class, &$params)
{
    //log_activity('params get_mail_template_path 1 : ' .time() .' ' . json_encode($params));
    $CI  = &get_instance();

    $dir = module_libs_path(BILLINGS_MODULE_NAME, 'mails/');

    // Check if second parameter is module and is activated so we can get the class from the module path
    // Also check if the first value is not equal to '/' e.q. when import is performed we set
    // for some values which are blank to "/"
    if (isset($params[0]) && is_string($params[0]) && $params[0] !== '/' && is_dir(module_dir_path($params[0]))) {
        $module = $CI->app_modules->get($params[0]);

        if ($module['activated'] === 1) {
            $dir = module_libs_path($params[0]) . 'mails/';
        }

        unset($params[0]);
        $params = array_values($params);
        //log_activity('params get_mail_template_path 2 : ' .time() .' ' . json_encode($params));
        //log_activity('params get_mail_template_path 3 : ' .time() .' ' . json_encode($dir));
    }

    return $dir . ucfirst($class) . '.php';
}


/**
 * Return RGBa billing status color for PDF documents
 * @param  mixed $status_id current billing status
 * @return string
 */
function billing_status_color_pdf($status_id)
{
    if ($status_id == 1) {
        $statusColor = '119, 119, 119';
    } elseif ($status_id == 2) {
        // Sent
        $statusColor = '3, 169, 244';
    } elseif ($status_id == 3) {
        //Declines
        $statusColor = '252, 45, 66';
    } elseif ($status_id == 4) {
        //Accepted
        $statusColor = '0, 191, 54';
    } else {
        // Expired
        $statusColor = '255, 111, 0';
    }

    return hooks()->apply_filters('billing_status_pdf_color', $statusColor, $status_id);
}

            
function is_sale_pph_applied($data)
{
    return $data->pph_total > 0;
}

function is_sale_pph($data, $is)
{
    if ($data->pph_percent == 0 && $data->pph_total == 0) {
        return false;
    }

    $pph_type = 'fixed';
    if ($data->pph_percent != 0) {
        $pph_type = 'percent';
    }

    return $pph_type == $is;
}


/**
 * Prepare general billing pdf
 * @since  Version 1.0.2
 * @param  object $billing billing as object with all necessary fields
 * @param  string $tag tag for bulk pdf exporter
 * @return mixed object
 */
function billing_tags_pdf($billing, $tag = '')
{
    return app_pdf('billing',  module_libs_path(BILLINGS_MODULE_NAME) . 'pdf/Billing_tags_pdf', $billing, $tag);
}

