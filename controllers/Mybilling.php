<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Mybilling extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('billings_model');
        $this->load->model('currencies_model');
        //include_once(module_libs_path(BILLINGS_MODULE_NAME) . 'mails/Billing_mail_template.php');
        //$this->load->library('module_name/library_name'); 
        //$this->load->library('billing_mail_template'); 
        //include_once(module_libs_path(BILLINGS_MODULE_NAME) . 'mails/Billing_send_to_customer.php');
        //$this->load->library('module_name/library_name'); 
        //$this->load->library('billing_send_to_customer'); 


    }

    public function show($id, $hash)
    {
        check_billing_restrictions($id, $hash);
        $billing = $this->billings_model->get($id);

        if ($billing->rel_type == 'customer' && !is_client_logged_in()) {
            load_client_language($billing->rel_id);
        } else if($billing->rel_type == 'lead') {
            load_lead_language($billing->rel_id);
        }

        $identity_confirmation_enabled = get_option('billing_accept_identity_confirmation');
        if ($this->input->post()) {
            $action = $this->input->post('action');
            switch ($action) {
                case 'billing_comment':
                    // comment is blank
                    if (!$this->input->post('content')) {
                        redirect($this->uri->uri_string());
                    }
                    $data               = $this->input->post();
                    $data['billingid'] = $id;
                    $this->billings_model->add_comment($data, true);
                    redirect($this->uri->uri_string() . '?tab=discussion');

                    break;
                case 'accept_billing':
                    $success = $this->billings_model->mark_action_status(3, $id, true);
                    if ($success) {
                        process_digital_signature_image($this->input->post('signature', false), PROPOSAL_ATTACHMENTS_FOLDER . $id);

                        $this->db->where('id', $id);
                        $this->db->update(db_prefix().'billings', get_acceptance_info_array());
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
                case 'decline_billing':
                    $success = $this->billings_model->mark_action_status(2, $id, true);
                    if ($success) {
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
            }
        }

        $number_word_lang_rel_id = 'unknown';
        if ($billing->rel_type == 'customer') {
            $number_word_lang_rel_id = $billing->rel_id;
        }
        $this->load->library('app_number_to_word', [
            'client_id' => $number_word_lang_rel_id,
        ],'numberword');

        $this->disableNavigation();
        $this->disableSubMenu();

        $data['title']     = $billing->subject;
        $data['can_be_accepted']               = false;
        $data['billing']  = hooks()->apply_filters('billing_html_pdf_data', $billing);
        $data['bodyclass'] = 'billing billing-view';

        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }

        $this->app_scripts->theme('sticky-js','assets/plugins/sticky/sticky.js');

        $data['comments'] = $this->billings_model->get_comments($id);
        add_views_tracking('billing', $id);
        hooks()->do_action('billing_html_viewed', $id);
        hooks()->add_action('app_admin_head', 'billings_head_component');
        
        $this->app_css->remove('reset-css','customers-area-default');

        $data                      = hooks()->apply_filters('billing_customers_area_view_data', $data);
        no_index_customers_area();
        $this->data($data);

        $this->view('themes/'. active_clients_theme() .'/views/billings/billing_html');
        
        $this->layout();
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
        $notes = explode('--', $billing->client_note);
        $note = '<ul>';

        foreach($notes as $row){
            if($row !==''){
                $note .= '<li>' . $row .'</li>';
            }
        }
        $note .= '</ul>';
        $billing->note = $note;

        $terms = explode('==', $billing->terms);
        $term = '<ol>';

        foreach($terms as $row){
            if($row !==''){
                $term .= '<li>' . $row .'</li>';
            }
        }
        $term .= '</ol>';
        $billing->term = $term;


        $billing_number = format_billing_number($id);

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

        $pdf->Output(format_billing_number($id).'-'. get_company_name($billing->clientid)  . '.pdf', $type);
    }


    /* Generates billing PDF and senting to email  */
    public function taggable_pdf($id)
    {
        $canView = user_can_view_billing($id);
        if (!$canView) {
            access_denied('Billings');
        } else {
            if (!has_permission('billings', '', 'view') && !has_permission('billings', '', 'view_own') && $canView == false) {
                access_denied('Billings');
            }
        }
        if (!$id) {
            redirect(admin_url('billings'));
        }

        $billing        = $this->billings_model->get($id);
        $project = get_project($billing->project_id);
        $contract = $this->billings_model->get_contract_by_project($project);

        if(count($contract)>0){
            $billing->contract = $contract[0];
        }

        $billing_number = format_billing_number($billing->id);
        $billing->items = $this->billings_model->get_billing_taggable_items($billing->id, $billing->project_id);

        $billing->client_company = $this->clients_model->get($billing->clientid)->company;
        $billing->acceptance_date_string = _dt($billing->acceptance_date);

        try {
            $pdf = billing_tags_pdf($billing);
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

        $fileNameHookData = hooks()->apply_filters('billing_file_name_admin_area', [
                            'file_name' => mb_strtoupper(slug_it($billing_number)) . '.pdf',
                            'billing'  => $billing,
                        ]);


        $pdf->Output(format_billing_number($id).'-'. get_company_name($billing->clientid)  . '.pdf', $type);
    }


}
