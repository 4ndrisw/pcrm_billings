<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop15 preview-top-wrapper">
   <div class="row">
      <div class="col-md-3">
         <div class="mbot30">
            <div class="billing-html-logo">
               <?php echo get_dark_company_logo(); ?>
            </div>
         </div>
      </div>
      <div class="clearfix"></div>
   </div>
   <div class="top" data-sticky data-sticky-class="preview-sticky-header">
      <div class="container preview-sticky-container">
         <div class="row">
            <div class="col-md-12">
               <div class="col-md-3">
                  <h3 class="bold no-mtop billing-html-number no-mbot">
                     <span class="sticky-visible hide">
                     <?php echo format_billing_number($billing->id); ?>
                     </span>
                  </h3>
                  <h4 class="billing-html-status mtop7">
                     <?php echo format_billing_status($billing->status,'',true); ?>
                  </h4>
               </div>
               <div class="col-md-9">         
                  <?php
                     // Is not accepted, declined and expired
                     if ($billing->status != 4 && $billing->status != 3 && $billing->status != 5) {
                       $can_be_accepted = true;
                       if($identity_confirmation_enabled == '0'){
                         echo form_open($this->uri->uri_string(), array('class'=>'pull-right mtop7 action-button'));
                         echo form_hidden('billing_action', 4);
                         echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_billing').'</button>';
                         echo form_close();
                       } else {
                         echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_billing').'</button>';
                       }
                     } else if($billing->status == 3){
                       if (($billing->open_till >= date('Y-m-d') || !$billing->open_till) && $billing->status != 5) {
                         $can_be_accepted = true;
                         if($identity_confirmation_enabled == '0'){
                           echo form_open($this->uri->uri_string(),array('class'=>'pull-right mtop7 action-button'));
                           echo form_hidden('billing_action', 4);
                           echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_billing').'</button>';
                           echo form_close();
                         } else {
                           echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_billing').'</button>';
                         }
                       }
                     }
                     // Is not accepted, declined and expired
                     if ($billing->status != 4 && $billing->status != 3 && $billing->status != 5) {
                       echo form_open($this->uri->uri_string(), array('class'=>'pull-right action-button mright5 mtop7'));
                       echo form_hidden('billing_action', 3);
                       echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-default action-button accept"><i class="fa fa-remove"></i> '._l('clients_decline_billing').'</button>';
                       echo form_close();
                     }
                     ?>
                  <?php echo form_open(site_url('billings/pdf/'.$billing->id), array('class'=>'pull-right action-button')); ?>
                  <button type="submit" name="billingpdf" class="btn btn-default action-button download mright5 mtop7" value="billingpdf">
                  <i class="fa fa-file-pdf-o"></i>
                  <?php echo _l('clients_invoice_html_btn_download'); ?>
                  </button>
                  <?php echo form_close(); ?>
                  <?php if((is_client_logged_in() && has_contact_permission('billings'))  || is_staff_member()){ ?>
                     <?php 
                        $clients = 'clients';
                        if(is_staff_member()){
                           $clients = 'admin';
                        }
                       ?>
                  <a href="<?php echo site_url($clients.'/billings/'); ?>" class="btn btn-default pull-right mright5 mtop7 action-button go-to-portal">
                  <?php echo _l('client_go_to_dashboard'); ?>
                  </a>
                  <?php } ?>
               </div>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>

   </div>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
   <div class="panel-body">
      <div class="col-md-10 col-md-offset-1">
         <div class="row mtop20">
            <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
               <h4 class="bold billing-html-number"><?php echo format_billing_number($billing->id); ?></h4>
               <address class="billing-html-company-info">
                  <?php echo format_organization_info(); ?>
               </address>
            </div>
            <div class="col-sm-6 text-right transaction-html-info-col-right">
               <span class="bold billing_to"><?php echo _l('billing_to'); ?>:</span>
               
                  <address class="no-margin billing-html-info">
                     <?php echo format_billing_info($billing, 'html'); ?>
                  </address>
               <p class="no-mbot billing-html-date">
                  <span class="bold">
                  <?php echo _l('billing_data_date'); ?>:
                  </span>
                  <?php echo _d($billing->date); ?>
               </p>
               <?php if(!empty($billing->open_till)){ ?>
               <p class="no-mbot billing-html-expiry-date">
                  <span class="bold"><?php echo _l('billing_data_expiry_date'); ?></span>:
                  <?php echo _d($billing->open_till); ?>
               </p>
               <?php } ?>
               <?php if(!empty($billing->reference_no)){ ?>
               <p class="no-mbot billing-html-reference-no">
                  <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                  <?php echo $billing->reference_no; ?>
               </p>
               <?php } ?>

               <?php $pdf_custom_fields = get_custom_fields('billing',array('show_on_pdf'=>1,'show_on_client_portal'=>1));
                  foreach($pdf_custom_fields as $field){
                    $value = get_custom_field_value($billing->id,$field['id'],'billing');
                    if($value == ''){continue;} ?>
               <p class="no-mbot">
                  <span class="bold"><?php echo $field['name']; ?>: </span>
                  <?php echo $value; ?>
               </p>
               <?php } ?>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12">
               <div class="table-responsive">
                  <?php
                     $items = get_items_table_data($billing, 'billing');
                     echo $items->table();
                     ?>
               </div>
            </div>
            <div class="col-md-6 col-md-offset-6">
               <table class="table text-right">
                  <tbody>
                     <tr id="subtotal">
                        <td><span class="bold"><?php echo _l('billing_subtotal'); ?></span>
                        </td>
                        <td class="subtotal">
                           <?php echo app_format_money($billing->subtotal, $billing->currency_name); ?>
                        </td>
                     </tr>
                     <?php if(is_sale_discount_applied($billing)){ ?>
                     <tr>
                        <td>
                           <span class="bold"><?php echo _l('billing_discount'); ?>
                           <?php if(is_sale_discount($billing,'percent')){ ?>
                           (<?php echo app_format_number($billing->discount_percent,true); ?>%)
                           <?php } ?></span>
                        </td>
                        <td class="discount">
                           <?php echo '-' . app_format_money($billing->discount_total, $billing->currency_name); ?>
                        </td>
                     </tr>
                     <?php } ?>
                     <?php
                        foreach($items->taxes() as $tax){
                         echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('.app_format_number($tax['taxrate']).'%)</td><td>'.app_format_money($tax['total_tax'], $billing->currency_name).'</td></tr>';
                        }
                        ?>
                     <?php if((int)$billing->adjustment != 0){ ?>
                     <tr>
                        <td>
                           <span class="bold"><?php echo _l('billing_adjustment'); ?></span>
                        </td>
                        <td class="adjustment">
                           <?php echo app_format_money($billing->adjustment, $billing->currency_name); ?>
                        </td>
                     </tr>
                     <?php } ?>
                     <tr>
                        <td><span class="bold"><?php echo _l('billing_total'); ?></span>
                        </td>
                        <td class="total">
                           <?php echo app_format_money($billing->total, $billing->currency_name); ?>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
            <?php
               if(get_option('total_to_words_enabled') == 1){ ?>
            <div class="col-md-12 text-center billing-html-total-to-words">
               <p class="bold"><?php echo  _l('num_word').': '.$this->numberword->convert($billing->total,$billing->currency_name); ?></p>
            </div>
            <?php } ?>
            <?php if(count($billing->attachments) > 0 && $billing->visible_attachments_to_customer_found == true){ ?>
            <div class="clearfix"></div>
            <div class="billing-html-files">
               <div class="col-md-12">
                  <hr />
                  <p class="bold mbot15 font-medium"><?php echo _l('billing_files'); ?></p>
               </div>
               <?php foreach($billing->attachments as $attachment){
                  // Do not show hidden attachments to customer
                  if($attachment['visible_to_customer'] == 0){continue;}
                  $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                  if(!empty($attachment['external'])){
                  $attachment_url = $attachment['external_link'];
                  }
                  ?>
               <div class="col-md-12 mbot15">
                  <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                  <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>
               </div>
               <?php } ?>
            </div>
            <?php } ?>
            <?php if(!empty($billing->client_note)){ ?>
            <div class="col-md-12 billing-html-note">
               <b><?php echo _l('billing_note'); ?></b><br />
               <?php
                  $notes = explode('--', $billing->client_note);
                  $note_text = '<ul>';
                  foreach ($notes as $note) {
                     if($note !== ''){
                        $note_text .='<li>' . $note . '</li>'; 
                     }               }
                  $note_text .= '</ul>';
                  echo($note_text); 
               ?>
            </div>
            <?php } ?>
            <?php if(!empty($billing->terms)){ ?>
            <div class="col-md-12 billing-html-terms-and-conditions">
               <hr />
               <b><?php echo _l('terms_and_conditions'); ?>:</b><br />
               <?php
                  $terms = explode('==', $billing->terms);
                  $term_text = '<ol>';
                  foreach ($terms as $term) {
                     if($term !== ''){
                        $term_text .='<li>' . $term . '</li>'; 
                     }               }
                  $term_text .= '</ol>';
                  echo($term_text); 
               ?>
            </div>
            <?php } ?>
         </div>
      </div>
   </div>
</div>
<?php
   if($identity_confirmation_enabled == '1' && $can_be_accepted){
    get_template_part('identity_confirmation_form',array('formData'=>form_hidden('billing_action',4)));
   }
   ?>
<script>
   $(function(){
     new Sticky('[data-sticky]');
   })
</script>
