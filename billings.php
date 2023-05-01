<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Billings
Description: Default module for defining billings
Version: 1.0.1
Requires at least: 2.3.*
*/

define('BILLINGS_MODULE_NAME', 'billings');
define('BILLING_ATTACHMENTS_FOLDER', FCPATH . 'uploads/billings/');

//hooks()->add_filter('before_billing_updated', '_format_data_billing_feature');
//hooks()->add_filter('before_billing_added', '_format_data_billing_feature');

hooks()->add_action('after_cron_run', 'billings_notification');
hooks()->add_action('admin_init', 'billings_module_init_menu_items');
hooks()->add_action('admin_init', 'billings_permissions');
hooks()->add_action('admin_init', 'billings_settings_tab');
hooks()->add_action('clients_init', 'billings_clients_area_menu_items');
//hooks()->add_action('app_admin_head', 'billings_head_component');
//hooks()->add_action('app_admin_footer', 'billings_footer_js_component');

hooks()->add_action('staff_member_deleted', 'billings_staff_member_deleted');

hooks()->add_filter('migration_tables_to_replace_old_links', 'billings_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'billings_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'billings_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'billings_add_dashboard_widget');
hooks()->add_filter('module_billings_action_links', 'module_billings_action_links');


function billings_add_dashboard_widget($widgets)
{
    /*
    $widgets[] = [
        'path'      => 'billings/widgets/billing_this_week',
        'container' => 'left-8',
    ];
    $widgets[] = [
        'path'      => 'billings/widgets/project_not_billingd',
        'container' => 'left-8',
    ];
    */

    return $widgets;
}


function billings_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'billings', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function billings_global_search_result_output($output, $data)
{
    if ($data['type'] == 'billings') {
        $output = '<a href="' . admin_url('billings/billing/' . $data['result']['id']) . '">' . format_billing_number($data['result']['id']) . '</a>';
    }

    return $output;
}

function billings_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('billings', '', 'view')) {

        // billings
        $CI->db->select()
           ->from(db_prefix() . 'billings')
           ->like(db_prefix() . 'billings.formatted_number', $q)->limit($limit);
        
        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'billings',
                'search_heading' => _l('billings'),
            ];
        
        if(isset($result[0]['result'][0]['id'])){
            return $result;
        }

        // billings
        $CI->db->select()->from(db_prefix() . 'billings')->like(db_prefix() . 'clients.company', $q)->or_like(db_prefix() . 'billings.formatted_number', $q)->limit($limit);
        $CI->db->join(db_prefix() . 'clients',db_prefix() . 'billings.client_id='.db_prefix() .'clients.userid', 'left');
        $CI->db->order_by(db_prefix() . 'clients.company', 'ASC');

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'billings',
                'search_heading' => _l('billings'),
            ];
    }

    return $result;
}

function billings_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
                'table' => db_prefix() . 'billings',
                'field' => 'description',
            ];

    return $tables;
}

function billings_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('billings', $capabilities, _l('billings'));
}


/**
* Register activation module hook
*/
register_activation_hook(BILLINGS_MODULE_NAME, 'billings_module_activation_hook');

function billings_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
    //ALTER TABLE `tblitemable` ADD `task_id` INT NULL DEFAULT NULL 
      $CI->db->query('ALTER TABLE `' . db_prefix() . 'itemable` ADD `task_id` INT NULL DEFAULT NULL');
}

/**
* Register deactivation module hook
*/
register_deactivation_hook(BILLINGS_MODULE_NAME, 'billings_module_deactivation_hook');

function billings_module_deactivation_hook()
{

    $CI = &get_instance();

    if ($CI->db->table_exists(db_prefix() . 'billings')) {
      $CI->db->query('DROP TABLE `' . db_prefix() . 'billings`');
    }

    if ($CI->db->table_exists(db_prefix() . 'billing_activity')) {
      $CI->db->query('DROP TABLE `' . db_prefix() . 'billing_activity`');
    }

    if ($CI->db->table_exists(db_prefix() . 'billing_comments')) {
      $CI->db->query('DROP TABLE `' . db_prefix() . 'billing_comments`');
    }

    if ($CI->db->table_exists(db_prefix() . 'billing_notes')) {
      $CI->db->query('DROP TABLE `' . db_prefix() . 'billing_notes`');
    }

   $CI->db->query('DELETE FROM `' . db_prefix() . 'options` WHERE `name` LIKE "%billing%"');
   $CI->db->query('DELETE FROM `' . db_prefix() . 'itemable` WHERE `rel_type` LIKE "%billing%"');
   $CI->db->query('DELETE FROM `' . db_prefix() . 'item_tax` WHERE `rel_type` LIKE "%billing%"');
   $CI->db->query('DELETE FROM `' . db_prefix() . 'reminders` WHERE `rel_type` LIKE "%billing%"');
   $CI->db->query('DELETE FROM `' . db_prefix() . 'emailtemplates` WHERE `name` LIKE "%billing%"');

    // billings
    
    $CI->load->model('billings/billings_model');
    $billings = $CI->db->select('id')
                    ->from(db_prefix() . 'files')
                    ->where('rel_type', 'billing')
                    ->get()->result();
    foreach($billings as $billing){
        $CI->billings_model->delete_attachment($billing->id);
    }
}

//hooks()->add_action('deactivate_' . $module . '_module', $function);

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(BILLINGS_MODULE_NAME, [BILLINGS_MODULE_NAME]);

/**
 * Init billings module menu items in setup in admin_init hook
 * @return null
 */
function billings_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
            'name'       => _l('billing'),
            'url'        => 'billings',
            'permission' => 'billings',
            'position'   => 57,
            ]);

    if (has_permission('billings', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('billings', [
                'slug'     => 'billings-tracking',
                'name'     => _l('billings'),
                'icon'     => 'fa fa-calendar',
                'href'     => admin_url('billings'),
                'position' => 12,
        ]);
    }
}

function module_billings_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=billings') . '">' . _l('settings') . '</a>';

    return $actions;
}

function billings_clients_area_menu_items()
{   
    // Show menu item only if client is logged in
    if (is_client_logged_in()) {
        add_theme_menu_item('billings', [
                    'name'     => _l('billings'),
                    'href'     => site_url('billings/list'),
                    'position' => 15,
        ]);
    }
}

/**
 * [perfex_dark_theme_settings_tab net menu item in setup->settings]
 * @return void
 */
function billings_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('billings', [
        'name'     => _l('settings_group_billings'),
        //'view'     => module_views_path(BILLINGS_MODULE_NAME, 'admin/settings/includes/billings'),
        'view'     => 'billings/billings_settings',
        'position' => 51,
    ]);
}

$CI = &get_instance();
$CI->load->helper(BILLINGS_MODULE_NAME . '/billings');

if(($CI->uri->segment(1)=='admin' && $CI->uri->segment(2)=='billings') || $CI->uri->segment(1)=='billings'){
    $CI->app_css->add(BILLINGS_MODULE_NAME.'-css', base_url('modules/'.BILLINGS_MODULE_NAME.'/assets/css/'.BILLINGS_MODULE_NAME.'.css'));
    $CI->app_scripts->add(BILLINGS_MODULE_NAME.'-js', base_url('modules/'.BILLINGS_MODULE_NAME.'/assets/js/'.BILLINGS_MODULE_NAME.'.js'));
}

