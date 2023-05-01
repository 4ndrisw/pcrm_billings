<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Billing_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $billing;

    protected $contact;

    public $slug = 'billing-send-to-client';

    public $rel_type = 'billing';

    public function __construct($billing, $contact, $cc = '')
    {
        parent::__construct();

        $this->billing = $billing;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->billings_model->get_attachments($this->billing->id, $attachment);
                $this->add_attachment([
                                'attachment' => get_upload_path_by_type('billing') . $this->billing->id . '/' . $_attachment->file_name,
                                'filename'   => $_attachment->file_name,
                                'type'       => $_attachment->filetype,
                                'read'       => true,
                            ]);
            }
        }

        $this->to($this->contact->email)
        ->set_rel_id($this->billing->id)
        ->set_merge_fields('client_merge_fields', $this->billing->client_id, $this->contact->id)
        ->set_merge_fields('billing_merge_fields', $this->billing->id);
    }
}
