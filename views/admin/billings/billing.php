<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content accounting-template billing">
      <div class="row">
         <?php
         if(isset($billing)){
             echo form_hidden('isedit',$billing->id);
            }
         $rel_type = '';
            $rel_id = '';
            if(isset($billing) || ($this->input->get('rel_id') && $this->input->get('rel_type'))){
             if($this->input->get('rel_id')){
               $rel_id = $this->input->get('rel_id');
               $rel_type = $this->input->get('rel_type');
             } else {
               $rel_id = $billing->rel_id;
               $rel_type = $billing->rel_type;
             }
            }
            ?>
         <?php
         echo form_open($this->uri->uri_string(),array('id'=>'billing-form','class'=>'_transaction_form billing-form'));

         ?>

          <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="row">
                     <?php if(isset($billing)){ ?>
                     <div class="col-md-12">
                        <?php echo format_billing_status($billing->status); ?>
                     </div>
                     <div class="clearfix"></div>
                     <hr />
                     <?php } ?>
                     <div class="col-md-6 border-right">
                        <?php $value = (isset($billing) ? $billing->subject : ''); ?>
                        <?php $attrs = (isset($billing) ? array() : array('autofocus'=>true)); ?>
                        <?php echo render_input('subject','billing_subject',$value,'text',$attrs); ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="f_client_id">
                                    <div class="form-group select-placeholder">
                                        <label for="clientid"
                                            class="control-label"><?php echo _l('billing_select_customer'); ?></label>
                                        <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search<?php if (isset($billing) && empty($billing->clientid)) {
                                                echo ' customer-removed';
                                            } ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                <?php $selected = (isset($billing) ? $billing->clientid : '');
                                                     if ($selected == '') {
                                                         $selected = (isset($customer_id) ? $customer_id: '');
                                                     }
                                                     if ($selected != '') {
                                                         $rel_data = get_relation_data('customer', $selected);
                                                         $rel_val  = get_relation_values($rel_data, 'customer');
                                                         echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                                     }
                                                ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group select-placeholder projects-wrapper<?php if ((!isset($billing)) || (isset($billing) && !customer_has_projects($billing->clientid))) {
                                     echo (isset($customer_id) && (!isset($project_id) || !$project_id)) ? ' hide' : '';
                                 } ?>">
                                    <label for="project_id"><?php echo _l('project'); ?></label>
                                    <div id="project_ajax_search_wrapper">
                                        <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true"
                                            data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                            <?php
                                             if (!isset($project_id)) {
                                                 $project_id = '';
                                             }
                                              if (isset($billing) && $billing->project_id != 0) {
                                                  $project_id = $billing->project_id;
                                              }
                                              if ($project_id) {
                                                  echo '<option value="' . $project_id . '" selected>' . get_project_name_by_id($project_id) . '</option>';
                                              }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                    <div class="col-md-12">
                                        <a href="#" class="edit_shipping_billing_info" data-toggle="modal"
                                            data-target="#billing_and_shipping_details"><i class="fa-regular fa-pen-to-square"></i></a>
                                        <?php include_once(module_views_path('billings','admin/billings/billing_and_shipping_template.php')); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="bold"><?php echo _l('invoice_bill_to'); ?></p>
                                        <address>
                                            <span class="billing_street">
                                                <?php $billing_street = (isset($billing) ? $billing->billing_street : '--'); ?>
                                                <?php $billing_street = ($billing_street == '' ? '--' :$billing_street); ?>
                                                <?php echo $billing_street; ?></span><br>
                                            <span class="billing_city">
                                                <?php $billing_city = (isset($billing) ? $billing->billing_city : '--'); ?>
                                                <?php $billing_city = ($billing_city == '' ? '--' :$billing_city); ?>
                                                <?php echo $billing_city; ?></span>,
                                            <span class="billing_state">
                                                <?php $billing_state = (isset($billing) ? $billing->billing_state : '--'); ?>
                                                <?php $billing_state = ($billing_state == '' ? '--' :$billing_state); ?>
                                                <?php echo $billing_state; ?></span>
                                            <br />
                                            <span class="billing_country">
                                                <?php $billing_country = (isset($billing) ? get_country_short_name($billing->billing_country) : '--'); ?>
                                                <?php $billing_country = ($billing_country == '' ? '--' :$billing_country); ?>
                                                <?php echo $billing_country; ?></span>,
                                            <span class="billing_zip">
                                                <?php $billing_zip = (isset($billing) ? $billing->billing_zip : '--'); ?>
                                                <?php $billing_zip = ($billing_zip == '' ? '--' :$billing_zip); ?>
                                                <?php echo $billing_zip; ?></span>
                                        </address>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="bold"><?php echo _l('ship_to'); ?></p>
                                        <address>
                                            <span class="shipping_street">
                                                <?php $shipping_street = (isset($billing) ? $billing->shipping_street : '--'); ?>
                                                <?php $shipping_street = ($shipping_street == '' ? '--' :$shipping_street); ?>
                                                <?php echo $shipping_street; ?></span><br>
                                            <span class="shipping_city">
                                                <?php $shipping_city = (isset($billing) ? $billing->shipping_city : '--'); ?>
                                                <?php $shipping_city = ($shipping_city == '' ? '--' :$shipping_city); ?>
                                                <?php echo $shipping_city; ?></span>,
                                            <span class="shipping_state">
                                                <?php $shipping_state = (isset($billing) ? $billing->shipping_state : '--'); ?>
                                                <?php $shipping_state = ($shipping_state == '' ? '--' :$shipping_state); ?>
                                                <?php echo $shipping_state; ?></span>
                                            <br />
                                            <span class="shipping_country">
                                                <?php $shipping_country = (isset($billing) ? get_country_short_name($billing->shipping_country) : '--'); ?>
                                                <?php $shipping_country = ($shipping_country == '' ? '--' :$shipping_country); ?>
                                                <?php echo $shipping_country; ?></span>,
                                            <span class="shipping_zip">
                                                <?php $shipping_zip = (isset($billing) ? $billing->shipping_zip : '--'); ?>
                                                <?php $shipping_zip = ($shipping_zip == '' ? '--' :$shipping_zip); ?>
                                                <?php echo $shipping_zip; ?></span>
                                        </address>
                                    </div>
                            </div>
                        </div>

                        <div class="row">
                          <div class="col-md-12">

                              <?php
                                 $next_billing_number = get_option('next_billing_number');
                                 $format = get_option('billing_number_format');
                                 
                                  if(isset($billing)){
                                    $format = $billing->number_format;
                                  }

                                 $prefix = get_option('billing_prefix');

                                 if ($format == 1) {
                                   $__number = $next_billing_number;
                                   if(isset($billing)){
                                     $__number = $billing->number;
                                     $prefix = '<span id="prefix">' . $billing->prefix . '</span>';
                                   }
                                 } else if($format == 2) {
                                   if(isset($billing)){
                                     $__number = $billing->number;
                                     $prefix = $billing->prefix;
                                     $prefix = '<span id="prefix">'. $prefix . '</span><span id="prefix_year">' . date('Y',strtotime($billing->date)).'</span>/';
                                   } else {
                                     $__number = $next_billing_number;
                                     $prefix = $prefix.'<span id="prefix_year">'.date('Y').'</span>/';
                                   }
                                 } else if($format == 3) {
                                    if(isset($billing)){
                                     $yy = date('y',strtotime($billing->date));
                                     $__number = $billing->number;
                                     $prefix = '<span id="prefix">'. $billing->prefix . '</span>';
                                   } else {
                                    $yy = date('y');
                                    $__number = $next_billing_number;
                                  }
                                 } else if($format == 4) {
                                    if(isset($billing)){
                                     $yyyy = date('Y',strtotime($billing->date));
                                     $mm = date('m',strtotime($billing->date));
                                     $__number = $billing->number;
                                     $prefix = '<span id="prefix">'. $billing->prefix . '</span>';
                                   } else {
                                    $yyyy = date('Y');
                                    $mm = date('m');
                                    $__number = $next_billing_number;
                                  }
                                 }
                                 
                                 $_billing_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
                                 $isedit = isset($billing) ? 'true' : 'false';
                                 $data_original_number = isset($billing) ? $billing->number : 'false';
                                 ?>

                                 <div class="form-group">
                                    <label for="number"><?php echo _l('billing_add_edit_number'); ?></label>
                                    <div class="input-group">
                                       <span class="input-group-addon">
                                       <?php if(isset($billing)){ ?>
                                       <a href="#" onclick="return false;" data-toggle="popover" data-container='._transaction_form' data-html="true" data-content="<label class='control-label'><?php echo _l('settings_sales_billing_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $billing->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('billings/update_number_settings/'.$billing->id); ?>' class='btn btn-info btn-block mtop15'><?php echo _l('submit'); ?></button>"><i class="fa fa-cog"></i></a>
                                        <?php }
                                         echo $prefix;
                                       ?>
                                       </span>
                                       <input type="text" name="number" class="form-control" value="<?php echo $_billing_number; ?>" data-isedit="<?php echo $isedit; ?>" data-original-number="<?php echo $data_original_number; ?>">
                                       <?php if($format == 3) { ?>
                                       <span class="input-group-addon">
                                          <span id="prefix_year" class="format-n-yy"><?php echo $yy; ?></span>
                                       </span>
                                       <?php } else if($format == 4) { ?>
                                        <span class="input-group-addon">
                                          <span id="prefix_month" class="format-mm-yyyy"><?php echo $mm; ?></span>
                                          .
                                          <span id="prefix_year" class="format-mm-yyyy"><?php echo $yyyy; ?></span>
                                       </span>
                                       <?php } ?>
                                    </div>
                                 </div>
                           </div>
                        </div>

                        <div class="row">
                          <div class="col-md-6">
                              <?php $value = (isset($billing) ? _d($billing->date) : _d(date('Y-m-d'))) ?>
                              <?php echo render_date_input('date','billing_date',$value); ?>
                          </div>
                          <div class="col-md-6">
                            <?php
                        $value = '';
                        if(isset($billing)){
                          $value = _d($billing->open_till);
                        } else {
                          if(get_option('billing_due_after') != 0){
                              $value = _d(date('Y-m-d',strtotime('+'.get_option('billing_due_after').' DAY',strtotime(date('Y-m-d')))));
                          }
                        }
                        echo render_date_input('open_till','billing_open_till',$value); ?>
                          </div>
                        </div>

                     </div>
                     <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group no-mbot">
                                   <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                                   <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($billing) ? prep_tags_input(get_tags_in($billing->id,'billing')) : ''); ?>" data-role="tagsinput">
                                </div>
                                <div class="col-md-6 form-group mtop10 no-mbot">
                                    <p><?php echo _l('billing_allow_comments'); ?></p>
                                    <div class="onoffswitch">
                                      <input type="checkbox" id="allow_comments" class="onoffswitch-checkbox" <?php if((isset($billing) && $billing->allow_comments == 1) || !isset($billing)){echo 'checked';}; ?> value="on" name="allow_comments">
                                      <label class="onoffswitch-label" for="allow_comments" data-toggle="tooltip" title="<?php echo _l('billing_allow_comments_help'); ?>"></label>
                                    </div>
                                </div>
                            </div>
                            <?php
                                $selected = '';
                                $currency_attr = array('data-show-subtext'=>true);
                                foreach($currencies as $currency){

                                    if($currency['isdefault'] == 1){
                                      $currency_attr['data-base'] = $currency['id'];
                                    }
                                    if(isset($billing)){
                                      if($currency['id'] == $billing->currency){
                                        $selected = $currency['id'];
                                      }
                                      if($billing->rel_type == 'customer'){
                                        $currency_attr['disabled'] = true;
                                      }
                                    } else {
                                        if($rel_type == 'customer'){
                                            $customer_currency = $this->clients_model->get_customer_default_currency($rel_id);
                                            if($customer_currency != 0){
                                              $selected = $customer_currency;
                                            } else {
                                                if($currency['isdefault'] == 1){
                                                    $selected = $currency['id'];
                                                }
                                            }
                                            $currency_attr['disabled'] = true;
                                        } else {
                                           if($currency['isdefault'] == 1){
                                                $selected = $currency['id'];
                                            }
                                        }
                                   }
                                }
                               $currency_attr = apply_filters_deprecated('billing_currency_disabled', [$currency_attr], '2.3.0', 'billing_currency_attributes');
                               $currency_attr = hooks()->apply_filters('billing_currency_attributes', $currency_attr);
                            ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-6">
                                      <?php
                                      echo render_select('currency', $currencies, array('id','name','symbol'), 'billing_currency', $selected, $currency_attr);
                                      ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label for="discount_type" class="control-label"><?php echo _l('discount_type'); ?></label>
                                            <select name="discount_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                 <option value="" selected><?php echo _l('no_discount'); ?></option>
                                                <option value="before_tax" <?php
                                                if(isset($billing)){ if($billing->discount_type == 'before_tax'){ echo 'selected'; }}?>><?php echo _l('discount_type_before_tax'); ?></option>
                                                <option value="after_tax" <?php if(isset($billing)){if($billing->discount_type == 'after_tax'){echo 'selected';}} ?>><?php echo _l('discount_type_after_tax'); ?></option>
                                            </select>
                                        </div>
                                    </div>                                    
                                </div>
                            </div>


                            <div class="col-md-6">
                              <div class="form-group select-placeholder">
                                 <label for="status" class="control-label"><?php echo _l('billing_status'); ?></label>
                                 <?php
                                    $disabled = '';
                                    if(isset($billing)){
                                     if($billing->id != NULL || $billing->invoice_id != NULL){
                                       $disabled = 'disabled';
                                     }
                                    }
                                    ?>
                                 <select name="status" class="selectpicker" data-width="100%" <?php echo $disabled; ?> data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <?php foreach($statuses as $status){ ?>
                                    <option value="<?php echo $status; ?>" <?php if((isset($billing) && $billing->status == $status) || (!isset($billing) && $status == 0)){echo 'selected';} ?>><?php echo format_billing_status($status,'',false); ?></option>
                                    <?php } ?>
                                 </select>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <?php
                                 $i = 0;
                                 $selected = get_option('default_billing_assigned');
                                 foreach($staff as $member){
                                  if(isset($billing)){
                                    if($billing->assigned == $member['staffid']) {
                                      $selected = $member['staffid'];
                                    }
                                  }
                                  $i++;
                                 }
                                 echo render_select('assigned',$staff,array('staffid',array('firstname','lastname')),'billing_assigned',$selected);
                                 ?>
                           </div>

                           <div class="col-md-12">
                               <?php $attributes = array(
                                            'id'    => 'reseller_wrapper',
                                            'class' => 'reseller_info'
                                    );

                                    echo form_fieldset(_l('reseller_billing'), $attributes);
                                    ?>                                
                                    <div class="col-md-3">
                                        <p><?php echo _l('use_reseller'); ?></p>
                                        <div class="onoffswitch">
                                          <input type="checkbox" id="reseller" class="onoffswitch-checkbox" <?php if((isset($billing) && $billing->reseller == 1) || !isset($billing)){echo 'checked';}; ?> value="on" name="reseller">
                                          <label class="onoffswitch-label" for="reseller" data-toggle="tooltip" title="<?php echo _l('billing_reseller_help'); ?>"></label>
                                        </div>
                                    </div>
                                    <?php
                                    echo '<div class="col-md-9">';
                                        echo render_input('reseller_name',_l('reseller_name'),isset($billing->reseller_name) ? $billing->reseller_name : '');
                                    echo '</div>';
                                    echo form_fieldset_close();
                               ?>

                           </div>


                        </div>
                     </div>
                  </div>
                  <div class="btn-bottom-toolbar bottom-transaction text-right">
                  <p class="no-mbot pull-left mtop5 btn-toolbar-notice"><?php echo _l('include_billing_items_merge_field_help','<b>{billing_items}</b>'); ?></p>
                    <?php
                        $cancel = admin_url('billings');
                      if(isset($billing->id)){
                        $cancel = admin_url('billings/#' .$billing->id);
                      }
                     ?>
                    <a class="btn btn-sm btn-default" href="<?php echo $cancel; ?>"><?php echo _l('cancel'); ?></a>
                    <button type="button" class="btn btn-info mleft10 billing-form-submit save-and-send transaction-submit">
                        <?php echo _l('save_and_send'); ?>
                    </button>
                    <button class="btn btn-info mleft5 billing-form-submit transaction-submit" type="button">
                      <?php echo _l('save'); ?>
                    </button>
               </div>
               </div>
            </div>
         </div>
         <div class="col-md-12">
            <div class="panel_s">
               <?php $this->load->view('admin/billings/_add_edit_items'); ?>
            </div>
         </div>


          <div class="col-md-12 mtop15">
             <div class="panel-body bottom-transaction">
               <?php $value = (isset($billing) ? $billing->client_note : get_option('predefined_client_note_billing')); ?>
               <?php echo render_textarea('client_note','billing_add_edit_client_note',$value,array(),array(),'mtop15'); ?>
               <?php $value = (isset($billing) ? $billing->terms : get_option('predefined_terms_billing')); ?>
               <?php echo render_textarea('terms','terms_and_conditions',$value,array(),array(),'mtop15'); ?>
             </div>
          </div>


         <?php echo form_close(); ?>
         <?php $this->load->view('admin/invoice_items/item'); ?>
      </div>
      <div class="btn-bottom-pusher"></div>
   </div>
</div>
<?php init_tail(); ?>
<script>
   var _rel_id = $('#rel_id'),
   _rel_type = $('#rel_type'),
   _rel_id_wrapper = $('#rel_id_wrapper'),
   data = {};

    init_currency();
    // Maybe items ajax search
    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
    validate_billing_form();

    // Project ajax search
    init_ajax_project_search_by_customer_id();
    
    calculate_total_billing_with_pph();

   function validate_billing_form(){
      appValidateForm($('#billing-form'), {
        subject : 'required',
        billing_to : 'required',
        rel_type: 'required',
        //rel_id : 'required',
        date : 'required',
        email: {
         email:true,
         required:true
       },
       currency : 'required',
     });
   }
</script>

</body>
</html>
