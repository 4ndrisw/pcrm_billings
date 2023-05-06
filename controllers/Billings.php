<?php
defined('BASEPATH') or exit('No direct script access allowed');

use modules\billings\services\billings\BillingsPipeline;


class Billings extends AdminController
{
    
    public function __construct()
    {
        parent::__construct();
        $this->load->model('billings_model');
        $this->load->model('currencies_model');
        include_once(module_libs_path('billings') . 'mails/Billing_mail_template.php');
        //$this->load->library('module_name/library_name'); 
        $this->load->library('billing_mail_template'); 
        //include_once(module_libs_path(BILLINGS_MODULE_NAME) . 'mails/Billing_send_to_customer.php');
        //$this->load->library('module_name/library_name'); 
        //$this->load->library('billing_send_to_customer'); 


    }

    public function index($billing_id = '')
    {
        $this->list_billings($billing_id);
    }

    public function list_billings($billing_id = '')
    {
        close_setup_menu();

        if (!has_permission('billings', '', 'view') && !has_permission('billings', '', 'view_own') && get_option('allow_staff_view_billings_assigned') == 0) {
            access_denied('billings');
        }
        
        log_activity($billing_id);

        $isPipeline = $this->session->userdata('billings_pipeline') == 'true';

        if ($isPipeline && !$this->input->get('status')) {
            $data['title']           = _l('billings_pipeline');
            $data['bodyclass']       = 'billings-pipeline';
            $data['switch_pipeline'] = false;
            // Direct access
            if (is_numeric($billing_id)) {
                $data['billingid'] = $billing_id;
            } else {
                $data['billingid'] = $this->session->flashdata('billingid');
            }

            $this->load->view('admin/billings/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') && $isPipeline) {
                $this->pipeline(0, true);
            }

            $data['billing_id']           = $billing_id;
            $data['switch_pipeline']       = true;
            $data['title']                 = _l('billings');
            $data['statuses']              = $this->billings_model->get_statuses();
            $data['billings_sale_agents'] = $this->billings_model->get_sale_agents();
            $data['years']                 = $this->billings_model->get_billings_years();
            
            log_activity(json_encode($data));
            /*
            if($billing_id){
                $this->load->view('admin/billings/manage_small_table', $data);
            }else{
                $this->load->view('admin/billings/manage_table', $data);
            }
            */
                $this->load->view('admin/billings/manage_table', $data);
        }
    }

    public function table()
    {
        if (
            !has_permission('billings', '', 'view')
            && !has_permission('billings', '', 'view_own')
            && get_option('allow_staff_view_billings_assigned') == 0
        ) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('billings', 'tables/billings'));
        
    }
    /*
    public function small_table()
    {
        if (
            !has_permission('billings', '', 'view')
            && !has_permission('billings', '', 'view_own')
            && get_option('allow_staff_view_billings_assigned') == 0
        ) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('billings', 'tables/billings_small_table'));
        
    }
    */

    public function billing_relations($rel_id, $rel_type)
    {
        $this->app->get_table_data(module_views_path('billings', 'tables/billings_relations', [
            'rel_id'   => $rel_id,
            'rel_type' => $rel_type,
        ]));
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->billings_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function clear_signature($id)
    {
        if (has_permission('billings', '', 'delete')) {
            $this->billings_model->clear_signature($id);
        }

        redirect(admin_url('billings/list_billings/' . $id .'#' . $id .'#' . $id));
    }

    public function sync_data()
    {
        if (has_permission('billings', '', 'create') || has_permission('billings', '', 'edit')) {
            $has_permission_view = has_permission('billings', '', 'view');

            $this->db->where('rel_id', $this->input->post('rel_id'));
            $this->db->where('rel_type', $this->input->post('rel_type'));

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $address = trim($this->input->post('address'));
            $address = nl2br($address);
            $this->db->update(db_prefix() . 'billings', [
                'phone'   => $this->input->post('phone'),
                'zip'     => $this->input->post('zip'),
                'country' => $this->input->post('country'),
                'state'   => $this->input->post('state'),
                'address' => $address,
                'city'    => $this->input->post('city'),
            ]);

            if ($this->db->affected_rows() > 0) {
                echo json_encode([
                    'message' => _l('all_data_synced_successfully'),
                ]);
            } else {
                echo json_encode([
                    'message' => _l('sync_billings_up_to_date'),
                ]);
            }
        }
    }

    public function billing($id = '')
    {
        if ($this->input->post()) {
            $billing_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('billings', '', 'create')) {
                    access_denied('billings');
                }
                $id = $this->billings_model->add($billing_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('billing')));
                    if ($this->set_billing_pipeline_autoload($id)) {
                        redirect(admin_url('billings'));
                    } else {
                        redirect(admin_url('billings/list_billings/' . $id .'#' . $id));
                    }
                }
            } else {
                if (!has_permission('billings', '', 'edit')) {
                    access_denied('billings');
                }
                $success = $this->billings_model->update($billing_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('billing')));
                }
                if ($this->set_billing_pipeline_autoload($id)) {
                    redirect(admin_url('billings'));
                } else {
                    redirect(admin_url('billings/list_billings/' . $id .'#' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('billing_lowercase'));
        } else {
            $data['billing'] = $this->billings_model->get($id);

            if (!$data['billing'] || !user_can_view_billing($id)) {
                blank_page(_l('billing_not_found'));
            }

            $data['billing']    = $data['billing'];
            $data['is_billing'] = true;
            $title               = _l('edit', _l('billing_lowercase'));
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['statuses']      = $this->billings_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = $title;
        $this->load->view('admin/billings/billing', $data);
    }

    public function get_template()
    {
        $name = $this->input->get('name');
        echo $this->load->view('admin/billings/templates/' . $name, [], true);
    }

    public function send_expiry_reminder($id)
    {
        $canView = user_can_view_billing($id);
        if (!$canView) {
            access_denied('billings');
        } else {
            if (!has_permission('billings', '', 'view') && !has_permission('billings', '', 'view_own') && $canView == false) {
                access_denied('billings');
            }
        }

        $success = $this->billings_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_billing_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('billings/list_billings/' . $id .'#' . $id));
        }
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'billings', get_acceptance_info_array(true));
        }

        redirect(admin_url('billings/list_billings/' . $id .'#' . $id));
    }

    public function pdf($id)
    {
        if (!$id) {
            redirect(admin_url('billings'));
        }

        $canView = user_can_view_billing($id);
        if (!$canView) {
            access_denied('billings');
        } else {
            if (!has_permission('billings', '', 'view') && !has_permission('billings', '', 'view_own') && $canView == false) {
                access_denied('billings');
            }
        }

        $billing = $this->billings_model->get($id);

        try {
            $pdf = billing_pdf($billing);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $billing_number = format_billing_number($id);
        $pdf->Output($billing_number . '.pdf', $type);
    }

    public function get_billing_data_ajax($id, $to_return = false)
    {
        if (!has_permission('billings', '', 'view') && !has_permission('billings', '', 'view_own') && get_option('allow_staff_view_billings_assigned') == 0) {
            echo _l('access_denied');
            die;
        }

        $billing = $this->billings_model->get($id, [], true);

        if (!$billing || !user_can_view_billing($id)) {
            echo _l('billing_not_found');
            die;
        }

        
        //$this->billings_mail_template->set_rel_id($billing->id);
        include_once(module_libs_path(BILLINGS_MODULE_NAME) . 'mails/Billing_send_to_customer.php');

        //$data = billing_prepare_mail_preview_data('billing_send_to_customer', $billing->email);

        $merge_fields = [];

        $merge_fields[] = [
            [
                'name' => 'Items Table',
                'key'  => '{billing_items}',
            ],
        ];

        $merge_fields = array_merge($merge_fields, $this->app_merge_fields->get_flat('billings', 'other', '{email_signature}'));

        $data['statuses'] = $this->billings_model->get_statuses();
        $data['billings_sale_agents'] = $this->billings_model->get_sale_agents();
        $data['billing_statuses']     = $this->billings_model->get_statuses();
        $data['members']               = $this->staff_model->get('', ['active' => 1]);
        $data['billing_merge_fields'] = $merge_fields;
        $data['billing']              = $billing;
        $data['totalNotes']            = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'billing']);

        if ($to_return == false) {
            $this->load->view('admin/billings/billings_preview_template', $data);
        } else {
            return $this->load->view('admin/billings/billings_preview_template', $data, true);
        }
    }

/*
    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_billing($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'billing', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_billing($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'billing');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }
    public function convert_to_billing($id)
    {
        if (!has_permission('billings', '', 'create')) {
            access_denied('billings');
        }
        if ($this->input->post()) {
            $this->load->model('billings_model');
            $billing_id = $this->billings_model->add($this->input->post());
            if ($billing_id) {
                set_alert('success', _l('billing_converted_to_billing_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'billings', [
                    'billing_id' => $billing_id,
                    'status'      => 3,
                ]);
                log_activity('Billing Converted to Estimate [EstimateID: ' . $billing_id . ', BillingID: ' . $id . ']');

                hooks()->do_action('billing_converted_to_billing', ['billing_id' => $id, 'billing_id' => $billing_id]);

                redirect(admin_url('billings/billing/' . $billing_id));
            } else {
                set_alert('danger', _l('billing_converted_to_billing_fail'));
            }
            if ($this->set_billing_pipeline_autoload($id)) {
                redirect(admin_url('billings'));
            } else {
                redirect(admin_url('billings/list_billings/' . $id .'#' . $id));
            }
        }
    }
*/
    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if ($this->input->post()) {
            $this->load->model('invoices_model');
            $invoice_id = $this->invoices_model->add($this->input->post());
            if ($invoice_id) {
                set_alert('success', _l('billing_converted_to_invoice_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'billings', [
                    'invoice_id' => $invoice_id,
                    'status'     => 3,
                ]);
                log_activity('Billing Converted to Invoice [InvoiceID: ' . $invoice_id . ', BillingID: ' . $id . ']');
                hooks()->do_action('billing_converted_to_invoice', ['billing_id' => $id, 'invoice_id' => $invoice_id]);
                redirect(admin_url('invoices/invoice/' . $invoice_id));
            } else {
                set_alert('danger', _l('billing_converted_to_invoice_fail'));
            }
            if ($this->set_billing_pipeline_autoload($id)) {
                redirect(admin_url('billings'));
            } else {
                redirect(admin_url('billings/list_billings/' . $id .'#' . $id));
            }
        }
    }

    public function get_invoice_convert_data($id)
    {
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $this->load->model('taxes_model');
        $data['taxes']         = $this->taxes_model->get();
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff']          = $this->staff_model->get('', ['active' => 1]);
        $data['billing']       = $this->billings_model->get($id);
        $data['billable_tasks'] = [];
        $data['add_items']      = $this->_parse_items($data['billing']);

        if ($data['billing']->rel_type == 'lead') {
            $this->db->where('leadid', $data['billing']->rel_id);
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['billing']->rel_id;
        }
        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'billing',
            'rel_id'     => $id,
        ];
        $this->load->view('admin/billings/invoice_convert_template', $data);
    }

    public function get_billing_convert_data($id)
    {
        $this->load->model('taxes_model');
        $data['taxes']         = $this->taxes_model->get();
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff']     = $this->staff_model->get('', ['active' => 1]);
        $data['billing']  = $this->billings_model->get($id);
        $data['add_items'] = $this->_parse_items($data['billing']);

        $this->load->model('billings_model');
        $data['billing_statuses'] = $this->billings_model->get_statuses();
        if ($data['billing']->rel_type == 'lead') {
            $this->db->where('leadid', $data['billing']->rel_id);
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['billing']->rel_id;
        }

        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'billing',
            'rel_id'     => $id,
        ];

        $this->load->view('admin/billings/billing_convert_template', $data);
    }

    private function _parse_items($billing)
    {
        $items = [];
        foreach ($billing->items as $item) {
            $taxnames = [];
            $taxes    = get_billing_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                array_push($taxnames, $tax['taxname']);
            }
            $item['taxname']        = $taxnames;
            $item['parent_item_id'] = $item['id'];
            $item['id']             = 0;
            $items[]                = $item;
        }

        return $items;
    }

    /* Send billing to email */
    public function send_to_email($id)
    {
        $canView = user_can_view_billing($id);
        if (!$canView) {
            access_denied('billings');
        } else {
            if (!has_permission('billings', '', 'view') && !has_permission('billings', '', 'view_own') && $canView == false) {
                access_denied('billings');
            }
        }

        if ($this->input->post()) {
            try {
                $success = $this->billings_model->send_billing_to_email(
                    $id,
                    $this->input->post('attach_pdf'),
                    $this->input->post('cc')
                );
            } catch (Exception $e) {
                $message = $e->getMessage();
                echo $message;
                if (strpos($message, 'Unable to get the size of the image') !== false) {
                    show_pdf_unable_to_get_image_size_error();
                }
                die;
            }

            if ($success) {
                set_alert('success', _l('billing_sent_to_email_success'));
            } else {
                set_alert('danger', _l('billing_sent_to_email_fail'));
            }

            if ($this->set_billing_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('billings/list_billings/' . $id .'#' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('billings', '', 'create')) {
            access_denied('billings');
        }
        $new_id = $this->billings_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('billing_copy_success'));
            $this->set_billing_pipeline_autoload($new_id);
            redirect(admin_url('billings/billing/' . $new_id));
        } else {
            set_alert('success', _l('billing_copy_fail'));
        }
        if ($this->set_billing_pipeline_autoload($id)) {
            redirect(admin_url('billings'));
        } else {
            redirect(admin_url('billings/list_billings/' . $id .'#' . $id));
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('billings', '', 'edit')) {
            access_denied('billings');
        }
        $success = $this->billings_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('billing_status_changed_success'));
        } else {
            set_alert('danger', _l('billing_status_changed_fail'));
        }
        if ($this->set_billing_pipeline_autoload($id)) {
            redirect(admin_url('billings'));
        } else {
            redirect(admin_url('billings/list_billings/' . $id .'#' . $id));
        }
    }

    public function delete($id)
    {
        if (!has_permission('billings', '', 'delete')) {
            access_denied('billings');
        }
        $response = $this->billings_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('billing')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('billing_lowercase')));
        }
        redirect(admin_url('billings'));
    }

    public function get_relation_data_values($rel_id, $rel_type)
    {
        echo json_encode($this->billings_model->get_relation_data_values($rel_id, $rel_type));
    }

    public function add_billing_comment()
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->billings_model->add_comment($this->input->post()),
            ]);
        }
    }
     
    public function edit_comment($id)
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->billings_model->edit_comment($this->input->post(), $id),
                'message' => _l('comment_updated_successfully'),
            ]);
        }
    }

    public function get_billing_comments($id)
    {
        $data['comments'] = $this->billings_model->get_comments($id);
        $this->load->view('admin/billings/comments_template', $data);
    }

    public function remove_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . 'billing_comments')->row();
        if ($comment) {
            if ($comment->staffid != get_staff_user_id() && !is_admin()) {
                echo json_encode([
                    'success' => false,
                ]);
                die;
            }
            echo json_encode([
                'success' => $this->billings_model->remove_comment($id),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function add_billing_note()
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->billings_model->add_note($this->input->post()),
            ]);
        }
    }

    public function edit_note($id)
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->billings_model->edit_note($this->input->post(), $id),
                'message' => _l('note_updated_successfully'),
            ]);
        }
    }

    public function get_billing_notes($id)
    {
        $data['notes'] = $this->billings_model->get_notes($id);
        $this->load->view('admin/billings/notes_template', $data);
    }

    public function remove_note($id)
    {
        $this->db->where('id', $id);
        $note = $this->db->get(db_prefix() . 'billing_notes')->row();
        if ($note) {
            if ($note->staffid != get_staff_user_id() && !is_admin()) {
                echo json_encode([
                    'success' => false,
                ]);
                die;
            }
            echo json_encode([
                'success' => $this->billings_model->remove_note($id),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }


    public function save_billing_data()
    {
        if (!has_permission('billings', '', 'edit') && !has_permission('billings', '', 'create')) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }
        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('billing_id'));
        $this->db->update(db_prefix() . 'billings', [
            'content' => html_purify($this->input->post('content', false)),
        ]);

        $success = $this->db->affected_rows() > 0;
        $message = _l('updated_successfully', _l('billing'));

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    // Pipeline
    public function pipeline($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'billings_pipeline' => $set,
        ]);
        if ($manual == false) {
            redirect(admin_url('billings'));
        }
    }

    public function pipeline_open($id)
    {
        if (has_permission('billings', '', 'view') || has_permission('billings', '', 'view_own') || get_option('allow_staff_view_billings_assigned') == 1) {
            $data['billing']      = $this->get_billing_data_ajax($id, true);
            $data['billing_data'] = $this->billings_model->get($id);
            $this->load->view('admin/billings/pipeline/billing', $data);
        }
    }

    public function update_pipeline()
    {
        if (has_permission('billings', '', 'edit')) {
            $this->billings_model->update_pipeline($this->input->post());
        }
    }

    public function get_pipeline()
    {
        if (has_permission('billings', '', 'view') || has_permission('billings', '', 'view_own') || get_option('allow_staff_view_billings_assigned') == 1) {
            $data['statuses'] = $this->billings_model->get_statuses();
            $this->load->view('admin/billings/pipeline/pipeline', $data);
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $billings = (new BillingsPipeline($status))
        ->search($this->input->get('search'))
        ->sortBy(
            $this->input->get('sort_by'),
            $this->input->get('sort')
        )
        ->page($page)->get();

        foreach ($billings as $billing) {
            $this->load->view('admin/billings/pipeline/_kanban_card', [
                'billing' => $billing,
                'status'   => $status,
            ]);
        }
    }

    public function set_billing_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('billings_pipeline') && $this->session->userdata('billings_pipeline') == 'true') {
            $this->session->set_flashdata('billingid', $id);

            return true;
        }

        return false;
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('billing_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('billing_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }


    /* View all settings */
    public function settings()
    {
        if (!has_permission('settings', '', 'view')) {
            access_denied('settings');
        }
        if ($this->input->post()) {
            if (!has_permission('settings', '', 'edit')) {
                access_denied('settings');
            }

                //$config['upload_path']          = './uploads/';
                //$config['allowed_types']        = 'gif|jpg|png';
                $config['max_size']             = 100;
                $config['max_width']            = 1024;
                $config['max_height']           = 768;

                $this->load->library('upload', $config);
        
            $logo_uploaded     = (handle_iso_logo_upload() ? true : false);

        }

        $data['title'] = 'Billing Settings';
        $this->load->view('admin/billings/settings', $data);
    }

    /* Remove iso logo from settings / ajax */
    public function remove_iso_logo($type = '')
    {
        hooks()->do_action('before_remove_iso_logo');

        if (!has_permission('settings', '', 'delete')) {
            access_denied('settings');
        }

        $logoName = get_option('iso_logo');
        if ($type == 'dark') {
            $logoName = get_option('iso_logo_dark');
        }

        //$path = get_upload_path_by_type('iso') . '/' . $logoName;
        $path = 'uploads/iso' . '/' . $logoName;

        if (file_exists($path)) {
            unlink($path);
        }

        update_option('iso_logo' . ($type == 'dark' ? '_dark' : ''), '');
        redirect($_SERVER['HTTP_REFERER']);
    }
                
    /* Used in kanban when dragging and mark as */
    public function update_billing_status()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $this->billings_model->update_billing_status($this->input->post());
        }
    }
    
    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('billings', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'billings', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('billing'));
            }
        }

        echo json_encode($response);
        die;
    }
}
