<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('billings_settings'); ?>
<div class="horizontal-scrollable-tabs mbot15">
   <div role="tabpanel" class="tab-pane" id="billings">

   <div class="row">
      <div class="col-md-12">
         <?php $iso_logo = get_option('iso_logo'); ?>
         <?php $iso_logo_dark = get_option('iso_logo_dark'); ?>

         <?php if($iso_logo != ''){ ?>
            <div class="row">
               <div class="col-md-9">
                  <img src="<?php echo base_url('uploads/iso/'.$iso_logo); ?>" class="img img-responsive">
               </div>
               <?php if(has_permission('settings','','delete')){ ?>
                  <div class="col-md-3 text-right">
                     <a href="<?php echo admin_url('settings/remove_iso_logo'); ?>" data-toggle="tooltip" title="<?php echo _l('settings_general_iso_remove_logo_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
                  </div>
               <?php } ?>
            </div>
            <div class="clearfix"></div>
         <?php } else { ?>
            <div class="form-group">
               <label for="iso_logo" class="control-label"><?php echo _l('settings_general_iso_logo'); ?></label>
               <input type="file" name="iso_logo" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('settings_general_iso_logo_tooltip'); ?>">
            </div>
         <?php } ?>
         <hr />
         <?php if($iso_logo_dark != ''){ ?>
            <div class="row">
               <div class="col-md-9">
                  <img src="<?php echo base_url('uploads/iso/'.$iso_logo_dark); ?>" class="img img-responsive">
               </div>
               <?php if(has_permission('settings','','delete')){ ?>
                  <div class="col-md-3 text-right">
                     <a href="<?php echo admin_url('settings/remove_iso_logo/dark'); ?>" data-toggle="tooltip" title="<?php echo _l('settings_general_iso_remove_logo_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
                  </div>
               <?php } ?>
            </div>
            <div class="clearfix"></div>
         <?php } else { ?>
            <div class="form-group">
               <label for="iso_logo_dark" class="control-label"><?php echo _l('iso_logo_dark'); ?></label>
               <input type="file" name="iso_logo_dark" class="form-control" value="" data-toggle="tooltip" title="<?php echo _l('settings_general_iso_logo_tooltip'); ?>">
            </div>
         <?php } ?>
         <hr />
      </div>
   </div>

      <div class="form-group">
         <label class="control-label" for="billing_prefix"><?php echo _l('billing_prefix'); ?></label>
         <input type="text" name="settings[billing_prefix]" class="form-control" value="<?php echo get_option('billing_prefix'); ?>">
      </div>
      <hr />
      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('next_billing_number_tooltip'); ?>"></i>
      <?php echo render_input('settings[next_billing_number]','next_billing_number',get_option('next_billing_number'), 'number', ['min'=>1]); ?>
      <hr />
      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('due_after_help'); ?>"></i>
      <?php echo render_input('settings[billing_qrcode_size]', 'billing_qrcode_size', get_option('billing_qrcode_size')); ?>
      <hr />
      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('due_after_help'); ?>"></i>
      <?php echo render_input('settings[billing_due_after]','billing_due_after',get_option('billing_due_after')); ?>
      <hr />
      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('billing_number_of_date_tooltip'); ?>"></i>
      <?php echo render_input('settings[billing_number_of_date]','billing_number_of_date',get_option('billing_number_of_date'), 'number', ['min'=>0]); ?>
      <hr />
      <?php render_yes_no_option('billing_send_telegram_message','billing_send_telegram_message'); ?>
      <hr />
      <?php render_yes_no_option('delete_only_on_last_billing','delete_only_on_last_billing'); ?>
      <hr />
      <?php render_yes_no_option('billing_number_decrement_on_delete','decrement_billing_number_on_delete','decrement_billing_number_on_delete_tooltip'); ?>
      <hr />
      <?php echo render_yes_no_option('allow_staff_view_billings_assigned','allow_staff_view_billings_assigned'); ?>
      <hr />
      <?php render_yes_no_option('view_billing_only_logged_in','require_client_logged_in_to_view_billing'); ?>
      <hr />
      <?php render_yes_no_option('show_assigned_on_billings','show_assigned_on_billings'); ?>
      <hr />
      <?php render_yes_no_option('show_project_on_billing','show_project_on_billing'); ?>
      <hr />

      <?php
      $staff = $this->staff_model->get('', ['active' => 1]);
      $selected = get_option('default_billing_assigned');
      foreach($staff as $member){
       
         if($selected == $member['staffid']) {
           $selected = $member['staffid'];
         
       }
      }
      echo render_select('settings[default_billing_assigned]',$staff,array('staffid',array('firstname','lastname')),'default_billing_assigned_string',$selected);
      ?>
      <hr />
      <?php render_yes_no_option('exclude_billing_from_client_area_with_draft_status','exclude_billing_from_client_area_with_draft_status'); ?>
      <hr />   
      <?php render_yes_no_option('billing_accept_identity_confirmation','billing_accept_identity_confirmation'); ?>
      <hr />
      <?php echo render_input('settings[billing_year]','billing_year',get_option('billing_year'), 'number', ['min'=>2020]); ?>
      <hr />
      
      <div class="form-group">
         <label for="billing_number_format" class="control-label clearfix"><?php echo _l('billing_number_format'); ?></label>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[billing_number_format]" value="1" id="e_number_based" <?php if(get_option('billing_number_format') == '1'){echo 'checked';} ?>>
            <label for="e_number_based"><?php echo _l('billing_number_format_number_based'); ?></label>
         </div>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[billing_number_format]" value="2" id="e_year_based" <?php if(get_option('billing_number_format') == '2'){echo 'checked';} ?>>
            <label for="e_year_based"><?php echo _l('billing_number_format_year_based'); ?> (YYYY.000001)</label>
         </div>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[billing_number_format]" value="3" id="e_short_year_based" <?php if(get_option('billing_number_format') == '3'){echo 'checked';} ?>>
            <label for="e_short_year_based">000001-YY</label>
         </div>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[billing_number_format]" value="4" id="e_year_month_based" <?php if(get_option('billing_number_format') == '4'){echo 'checked';} ?>>
            <label for="e_year_month_based">000001.MM.YYYY</label>
         </div>
         <hr />
      </div>
      <div class="row">
         <div class="col-md-12">
            <?php echo render_input('settings[billings_pipeline_limit]','pipeline_limit_status',get_option('billings_pipeline_limit')); ?>
         </div>
         <div class="col-md-7">
            <label for="default_proposals_pipeline_sort" class="control-label"><?php echo _l('default_pipeline_sort'); ?></label>
            <select name="settings[default_billings_pipeline_sort]" id="default_billings_pipeline_sort" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
               <option value="datecreated" <?php if(get_option('default_billings_pipeline_sort') == 'datecreated'){echo 'selected'; }?>><?php echo _l('billings_sort_datecreated'); ?></option>
               <option value="date" <?php if(get_option('default_billings_pipeline_sort') == 'date'){echo 'selected'; }?>><?php echo _l('billings_sort_billing_date'); ?></option>
               <option value="pipeline_order" <?php if(get_option('default_billings_pipeline_sort') == 'pipeline_order'){echo 'selected'; }?>><?php echo _l('billings_sort_pipeline'); ?></option>
               <option value="expirydate" <?php if(get_option('default_billings_pipeline_sort') == 'expirydate'){echo 'selected'; }?>><?php echo _l('billings_sort_expiry_date'); ?></option>
            </select>
         </div>
         <div class="col-md-5">
            <div class="mtop30 text-right">
               <div class="radio radio-inline radio-primary">
                  <input type="radio" id="k_desc_billing" name="settings[default_billings_pipeline_sort_type]" value="asc" <?php if(get_option('default_billings_pipeline_sort_type') == 'asc'){echo 'checked';} ?>>
                  <label for="k_desc_billing"><?php echo _l('order_ascending'); ?></label>
               </div>
               <div class="radio radio-inline radio-primary">
                  <input type="radio" id="k_asc_billing" name="settings[default_billings_pipeline_sort_type]" value="desc" <?php if(get_option('default_billings_pipeline_sort_type') == 'desc'){echo 'checked';} ?>>
                  <label for="k_asc_billing"><?php echo _l('order_descending'); ?></label>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
      </div>
      <hr  />
      <?php echo render_textarea('settings[predefined_client_note_billing]','predefined_clientnote',get_option('predefined_client_note_billing'),array('rows'=>6)); ?>
      <?php echo render_textarea('settings[predefined_terms_billing]','predefined_terms',get_option('predefined_terms_billing'),array('rows'=>6)); ?>
   </div>
 <?php hooks()->do_action('after_billings_tabs_content'); ?>
</div>
