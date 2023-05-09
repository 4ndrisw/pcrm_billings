<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id',$billing->id); ?>
<?php echo form_hidden('_attachment_sale_type','billing'); ?>
<div class="panel_s">
   <div class="panel-body">
      <div class="horizontal-scrollable-tabs preview-tabs-top">
         <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
         <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
         <div class="horizontal-tabs">
            <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
               <li role="presentation" class="active">
                  <a href="#tab_billing" aria-controls="tab_billing" role="tab" data-toggle="tab">
                  <?php echo _l('billing'); ?>
                  </a>
               </li>
               <?php if(isset($billing)){ ?>
               <li role="presentation">
                  <a href="#tab_comments" onclick="get_billing_comments(); return false;" aria-controls="tab_comments" role="tab" data-toggle="tab">
                  <?php
                  echo _l('billing_comments');
                  $total_comments = total_rows(db_prefix() . 'billing_comments', [
                      'billingid' => $billing->id,
                    ]
                  );
                  ?>
                      <span class="badge total_comments <?php echo $total_comments === 0 ? 'hide' : ''; ?>"><?php echo $total_comments ?></span>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $billing->id ;?> + '/' + 'billing', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                  <?php echo _l('billing_reminders'); ?>
                  <?php
                     $total_reminders = total_rows(db_prefix().'reminders',
                      array(
                       'isnotified'=>0,
                       'staff'=>get_staff_user_id(),
                       'rel_type'=>'billing',
                       'rel_id'=>$billing->id
                       )
                      );
                     if($total_reminders > 0){
                      echo '<span class="badge">'.$total_reminders.'</span>';
                     }
                     ?>
                  </a>
               </li>
               <?php if(is_admin()) { ?>
               <li role="presentation" class="tab-separator">
                  <a href="#tab_tasks" onclick="init_rel_tasks_table(<?php echo $billing->id; ?>,'billing'); return false;" aria-controls="tab_tasks" role="tab" data-toggle="tab">
                  <?php echo _l('tasks'); ?>
                  </a>
               </li>
               <?php } ?>

               <li role="presentation" class="tab-separator">
                  <a href="#tab_notes" onclick="get_billing_notes(); return false;" aria-controls="tab_notes" role="tab" data-toggle="tab">
                  <?php
                  echo _l('billing_notes');
                  $total_notes = total_rows(db_prefix() . 'billing_notes', [
                      'billingid' => $billing->id,
                    ]
                  );
                  ?>
                      <span class="badge total_notes <?php echo $total_notes === 0 ? 'hide' : ''; ?>"><?php echo $total_notes ?></span>
                  </a>
               </li>
               <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>" class="tab-separator">
                  <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab" data-toggle="tab">
                    <?php if(!is_mobile()){ ?>
                     <i class="fa fa-envelope-open" aria-hidden="true"></i>
                     <?php } else { ?>
                     <?php echo _l('emails_tracking'); ?>
                     <?php } ?>
                  </a>
               </li>
               <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('view_tracking'); ?>" class="tab-separator">
                  <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                    <?php if(!is_mobile()){ ?>
                     <i class="fa fa-eye"></i>
                     <?php } else { ?>
                     <?php echo _l('view_tracking'); ?>
                     <?php } ?>
                  </a>
               </li>
               <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator toggle_view">
                  <a href="#" onclick="small_table_full_view(); return false;">
                  <i class="fa fa-expand"></i></a>
               </li>
               <?php } ?>
            </ul>
         </div>
      </div>
      <div class="row mtop10">
         <div class="col-md-3">
            <span class="billing-status pull-left mright5 mtop5"></span>
         </div>
         <div class="col-md-9 text-right _buttons billing_buttons">
            <?php if(has_permission('billings','','edit')){ ?>
            <a href="<?php echo admin_url('billings/billing/'.$billing->id); ?>" data-placement="left" data-toggle="tooltip" title="<?php echo _l('billing_edit'); ?>" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa fa-pencil-square"></i></a>
            <?php } ?>
            <div class="btn-group">
               <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf"></i><?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span></a>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li class="hidden-xs"><a href="<?php echo site_url('billings/pdf/'.$billing->id.'?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                  <li class="hidden-xs"><a href="<?php echo site_url('billings/taggable_pdf/'.$billing->id.'?output_type=I'); ?>" target="_blank"><?php echo _l('compact_billing'); ?></a></li>
                  <li><a href="<?php echo site_url('billings/pdf/'.$billing->id); ?>"><?php echo _l('download'); ?></a></li>
                  <li>
                     <a href="<?php echo site_url('billings/pdf/'.$billing->id.'?print=true'); ?>" target="_blank">
                     <?php echo _l('print'); ?>
                     </a>
                  </li>
               </ul>
            </div>
            <a href="#" class="btn btn-default btn-with-tooltip" data-target="#billing_send_to_customer" data-toggle="modal"><span data-toggle="tooltip" class="btn-with-tooltip" data-title="<?php echo _l('billing_send_to_email'); ?>" data-placement="bottom"><i class="fa fa-envelope"></i></span></a>
            <div class="btn-group ">
               <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <?php echo _l('more'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <li>
                     <a href="<?php echo site_url('billings/show/'.$billing->id .'/'.$billing->hash); ?>" target="_blank"><?php echo _l('billing_view'); ?></a>
                  </li>
                  <?php hooks()->do_action('after_billing_view_as_client_link', $billing); ?>
                  <?php if(!empty($billing->open_till) && date('Y-m-d') < $billing->open_till && ($billing->status == 4 || $billing->status == 1) && is_billings_expiry_reminders_enabled()) { ?>
                  <li>
                     <a href="<?php echo admin_url('billings/send_expiry_reminder/'.$billing->id); ?>"><?php echo _l('send_expiry_reminder'); ?></a>
                  </li>
                  <?php } ?>
                  <li>
                     <a href="#" data-toggle="modal" data-target="#billings_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                  </li>
                  <?php if(staff_can('edit', 'billings')){
                    foreach($billing_statuses as $status){
                      if($billing->status != $status){ ?>
                        <li>
                           <a href="#" onclick="billing_mark_as(<?php echo $status; ?>, <?php echo $billing->id ?>); return false;">
                           <?php echo _l('billing_mark_as',format_billing_status($status,'',false)); ?></a>
                        </li>
                     <?php }
                    }
                    ?>
                  <?php } ?>
                  <?php if(has_permission('billings','','create')){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'billings/copy/'.$billing->id; ?>"><?php echo _l('billing_copy'); ?></a>
                  </li>
                  <?php } ?>
                  <?php if($billing->id == NULL && $billing->invoice_id == NULL){ ?>
                  <?php foreach($billing_statuses as $status){
                     if(has_permission('billings','','edit')){
                      if($billing->status != $status){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'billings/mark_action_status/'.$status.'/'.$billing->id; ?>"><?php echo _l('billing_mark_as',format_billing_status($status,'',false)); ?></a>
                  </li>
                  <?php
                     } } } ?>
                  <?php } ?>
                  <?php if(!empty($billing->signature) && has_permission('billings','','delete')){ ?>
                  <li>
                     <a href="<?php echo admin_url('billings/clear_signature/'.$billing->id); ?>" class="_delete">
                     <?php echo _l('clear_signature'); ?>
                     </a>
                  </li>
                  <?php } ?>
                  <?php if(has_permission('billings','','delete')){ ?>
                  <li>
                     <a href="<?php echo admin_url() . 'billings/delete/'.$billing->id; ?>" class="text-danger delete-text _delete"><?php echo _l('billing_delete'); ?></a>
                  </li>
                  <?php } ?>
               </ul>
            </div>
            <?php if($billing->id == NULL && $billing->invoice_id == NULL){ ?>
            <?php if(has_permission('billings','','create') || has_permission('invoices','','create')){ ?>
            <div class="btn-group">
               <button type="button" class="btn btn-success dropdown-toggle<?php if($billing->rel_type == 'customer' && total_rows(db_prefix().'clients',array('active'=>0,'userid'=>$billing->rel_id)) > 0){echo ' disabled';} ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <?php echo _l('billing_convert'); ?> <span class="caret"></span>
               </button>
               <ul class="dropdown-menu dropdown-menu-right">
                  <?php
                     $disable_convert = false;
                     $not_related = false;

                     if($billing->rel_type == 'lead'){
                      if(total_rows(db_prefix().'clients',array('leadid'=>$billing->rel_id)) == 0){
                       $disable_convert = true;
                       $help_text = 'billing_convert_to_lead_disabled_help';
                     }
                     } else if(empty($billing->rel_type)){
                     $disable_convert = true;
                     $help_text = 'billing_convert_not_related_help';
                     }
                     ?>
                  <?php if(has_permission('billings','','create')){ ?>
                  <li <?php if($disable_convert){ echo 'data-toggle="tooltip" title="'._l($help_text,_l('billing_convert_billing')).'"';} ?>><a href="#" <?php if($disable_convert){ echo 'style="cursor:not-allowed;" onclick="return false;"';} else {echo 'data-template="billing" onclick="billing_convert_template(this); return false;"';} ?>><?php echo _l('billing_convert_billing'); ?></a></li>
                  <?php } ?>
                  <?php if(has_permission('invoices','','create')){ ?>
                  <li <?php if($disable_convert){ echo 'data-toggle="tooltip" title="'._l($help_text,_l('billing_convert_invoice')).'"';} ?>><a href="#" <?php if($disable_convert){ echo 'style="cursor:not-allowed;" onclick="return false;"';} else {echo 'data-template="invoice" onclick="billing_convert_template(this); return false;"';} ?>><?php echo _l('billing_convert_invoice'); ?></a></li>
                  <?php } ?>
               </ul>
            </div>
            <?php } ?>
            <?php } else {
               if($billing->id != NULL){
                echo '<a href="'.admin_url('billings/list_billings/'.$billing->id).'" class="btn btn-info">'.format_billing_number($billing->id).'</a>';
               } else {
                echo '<a href="'.admin_url('invoices/list_invoices/'.$billing->invoice_id).'" class="btn btn-info">'.format_invoice_number($billing->invoice_id).'</a>';
               }
               } ?>
         </div>
      </div>
      <div class="clearfix"></div>
      <hr class="hr-panel-heading" />
      <div class="row">
         <div class="col-md-12">
            <div class="tab-content">
               <div role="tabpanel" class="tab-pane active" id="tab_billing">
                  <div class="row mtop10">
                     <?php if($billing->status == 3 && !empty($billing->acceptance_firstname) && !empty($billing->acceptance_lastname) && !empty($billing->acceptance_email)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-info">
                           <?php echo _l('accepted_identity_info',array(
                              _l('billing_lowercase'),
                              '<b>'.$billing->acceptance_firstname . ' ' . $billing->acceptance_lastname . '</b> (<a href="mailto:'.$billing->acceptance_email.'">'.$billing->acceptance_email.'</a>)',
                              '<b>'. _dt($billing->acceptance_date).'</b>',
                              '<b>'.$billing->acceptance_ip.'</b>'.(is_admin() ? '&nbsp;<a href="'.admin_url('billings/clear_acceptance_info/'.$billing->id).'" class="_delete text-muted" data-toggle="tooltip" data-title="'._l('clear_this_information').'"><i class="fa fa-remove"></i></a>' : '')
                              )); ?>
                        </div>
                     </div>
                     <?php } ?>
                     <div class="col-md-6">
                        <h4 class="bold">
                           <?php
                              $tags = get_tags_in($billing->id,'billing');
                              if(count($tags) > 0){
                               echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.html_escape(implode(', ',$tags)).'"></i>';
                              }
                              ?>
                           <a href="<?php echo admin_url('billings/billing/'.$billing->id); ?>">
                           <span id="billing-number">
                           <?php echo format_billing_number($billing->id); ?>
                           </span>
                           </a>
                        </h4>
                        <h5 class="bold mbot15 font-medium"><a href="<?php echo site_url('billings/show/'.$billing->id.'/'.$billing->hash); ?>"><?php echo $billing->subject; ?></a>
                        </h5>
                        <h4 class="font-medium mbot15"><?php echo _l('related_to_project',array(
                           _l('billing_lowercase'),
                           _l('project_lowercase'),
                           '<a href="'.admin_url('projects/view/'.$billing->project_id).'" target="_blank">' . get_project_name_by_id($billing->project_id) . '</a>',
                           )); ?>
                        </h4>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-md-6 text-right">
                        <address>
                           <span class="bold"><?php echo _l('billing_to'); ?>:</span><br />
                           <?php                 
                           echo format_billing_info($billing,'admin'); ?>
                        </address>
                     </div>

                     <div class="clearfix"></div>
                     <?php if($billing->reseller && isset($billing->reseller_name)){ ?>
                     <div class="col-md-6">
                        <address>
                           <span class="bold"><?php echo _l('reseller'); ?>:</span><br />
                           <?php                 
                           echo $billing->reseller_name; ?>
                        </address>
                     </div>
                     <?php } ?>
                  </div>

                  <hr class="hr-panel-heading" />
                  <?php
                     if(count($billing->attachments) > 0){ ?>
                  <p class="bold"><?php echo _l('billing_files'); ?></p>
                  <?php foreach($billing->attachments as $attachment){
                     $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                     if(!empty($attachment['external'])){
                        $attachment_url = $attachment['external_link'];
                     }
                     ?>
                  <div class="mbot15 row" data-attachment-id="<?php echo $attachment['id']; ?>">
                     <div class="col-md-8">
                        <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                        <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                        <br />
                        <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                     </div>
                     <div class="col-md-4 text-right">
                        <?php if($attachment['visible_to_customer'] == 0){
                           $icon = 'fa-toggle-off';
                           $tooltip = _l('show_to_customer');
                           } else {
                           $icon = 'fa-toggle-on';
                           $tooltip = _l('hide_from_customer');
                           }
                           ?>
                        <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $billing->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?>" aria-hidden="true"></i></a>
                        <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                        <a href="#" class="text-danger" onclick="delete_billing_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                        <?php } ?>
                     </div>
                  </div>
                  <?php } ?>
                  <?php } ?>
                  <div class="clearfix"></div>

                  <div class="row">
                     <div class="col-md-12">
                        <div class="table-responsive">
                              <?php
                                 $items = get_items_table_data($billing, 'billing', 'html', true);
                                 echo $items->table();
                              ?>
                        </div>
                     </div>
                     <div class="col-md-5 col-md-offset-7">
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
                              <?php if((int)$billing->pph_total != 0){ ?>
                              <tr>
                                 <td>
                                    <span class="bold"><?php echo _l('billing_pph') .' ('. app_format_number($billing->pph,true) . '%)'; ?></span>
                                 </td>
                                 <td class="pph">
                                    <?php echo '-' . app_format_money($billing->pph_total, $billing->currency_name); ?>
                                 </td>
                              </tr>
                              <?php } ?>
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
                                    <?php $billing_total = isset($billing->pph_total) ? $billing->total-$billing->pph_total : $billing->total; ?>
                                    <?php echo app_format_money($billing_total, $billing->currency_name); ?>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                     <?php if(count($billing->attachments) > 0){ ?>
                     <div class="clearfix"></div>
                     <hr />
                     <div class="col-md-12">
                        <p class="bold text-muted"><?php echo _l('billing_files'); ?></p>
                     </div>
                     <?php foreach($billing->attachments as $attachment){
                        $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                        if(!empty($attachment['external'])){
                          $attachment_url = $attachment['external_link'];
                        }
                        ?>
                     <div class="mbot15 row col-md-12" data-attachment-id="<?php echo $attachment['id']; ?>">
                        <div class="col-md-8">
                           <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                           <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                           <br />
                           <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                        </div>
                        <div class="col-md-4 text-right">
                           <?php if($attachment['visible_to_customer'] == 0){
                              $icon = 'fa fa-toggle-off';
                              $tooltip = _l('show_to_customer');
                              } else {
                              $icon = 'fa fa-toggle-on';
                              $tooltip = _l('hide_from_customer');
                              }
                              ?>
                           <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $billing->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="<?php echo $icon; ?>" aria-hidden="true"></i></a>
                           <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                           <a href="#" class="text-danger" onclick="delete_billing_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                           <?php } ?>
                        </div>
                     </div>
                     <?php } ?>
                     <?php } ?>
                     <?php if($billing->client_note != ''){ ?>
                     <div class="col-md-12 mtop15">
                        <p class="bold text-muted"><?php echo _l('billing_note'); ?></p>
                        <p>
                        <?php
                           $notes = explode('--', $billing->client_note);
                           $note_text = '<ul class="unordered-list">';
                           foreach ($notes as $note) {
                              if($note !== ''){
                                 $note_text .='<li>' . $note . '</li>'; 
                              }               }
                           $note_text .= '</ul>';
                           echo($note_text); 
                        ?>
                        </p>
                     </div>
                     <?php } ?>
                     <?php if($billing->terms != ''){ ?>
                     <div class="col-md-12 mtop15">
                        <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
                        <p>
                        <?php
                           $terms = explode('==', $billing->terms);
                           $term_text = '<ol class="ordered-list">';
                           foreach ($terms as $term) {
                              if($term !== ''){
                                 $term_text .='<li>' . $term . '</li>'; 
                              }               }
                           $term_text .= '</ol>';
                           echo($term_text); 
                        ?>
                        </p>
                     </div>
                     <?php } ?>
                  </div>

                      <?php if(!empty($billing->signature)) { ?>
                        <div class="row mtop25">
                           <div class="col-md-6 col-md-offset-6 text-right">
                              <div class="bold">
                                 <p class="no-mbot"><?php echo _l('contract_signed_by') . ": {$billing->acceptance_firstname} {$billing->acceptance_lastname}"?></p>
                                 <p class="no-mbot"><?php echo _l('billing_signed_date') . ': ' . _dt($billing->acceptance_date) ?></p>
                                 <p class="no-mbot"><?php echo _l('billing_signed_ip') . ": {$billing->acceptance_ip}"?></p>
                              </div>
                              <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                                 <?php if(has_permission('billings','','delete')){ ?>
                                 <a href="<?php echo admin_url('billings/clear_signature/'.$billing->id); ?>" data-toggle="tooltip" title="<?php echo _l('clear_signature'); ?>" class="_delete text-danger">
                                 <i class="fa fa-remove"></i>
                                 </a>
                                 <?php } ?>
                              </p>
                              <div class="pull-right">
                                 <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_upload_path_by_type('billing').$billing->id.'/'.$billing->signature)); ?>" class="img-responsive" alt="">
                              </div>
                           </div>
                        </div>
                        <?php } ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_comments">
                  <div class="row billing-comments mtop15">
                     <div class="col-md-12">
                        <div id="billing-comments"></div>
                        <div class="clearfix"></div>
                        <textarea name="content" id="comment" rows="4" class="form-control mtop15 billing-comment"></textarea>
                        <button type="button" class="btn btn-info mtop10 pull-right" onclick="add_billing_comment();"><?php echo _l('billing_add_comment'); ?></button>
                     </div>
                  </div>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_notes">


                  <div class="row billing-notes mtop15">
                     <div class="col-md-12">
                        <div class="clearfix"></div>
                        <textarea name="content" id="note" rows="4" class="form-control mtop15 billing-note"></textarea>
                        <button type="button" class="btn btn-info mtop10 pull-right" onclick="add_billing_note();"><?php echo _l('billing_add_note'); ?></button>
                     </div>
                  </div>

                  <?php //echo form_open(admin_url('billings/add_note/'.$billing->id),array('id'=>'sales-notes','class'=>'billing-notes-form')); ?>
                  <?php //echo render_textarea('description'); ?>
                  <!--
                  <div class="text-right">
                     <button type="submit" class="btn btn-info mtop15 mbot15"><?php //echo _l('billing_add_note'); ?></button>
                  </div>
                  -->
                  <?php //echo form_close(); ?>

                  <hr />
                  <div id="billing-notes"></div>
                  <!-- <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
                  </div>-->
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
                  <?php
                     $this->load->view('admin/includes/emails_tracking',array(
                       'tracked_emails'=>
                       get_tracked_emails($billing->id, 'billing'))
                       );
                     ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_tasks">
                  <?php init_relation_tasks_table(array( 'data-new-rel-id'=>$billing->id,'data-new-rel-type'=>'billing')); ?>
               </div>
               <div role="tabpanel" class="tab-pane" id="tab_reminders">
                  <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-billing-<?php echo $billing->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('billing_set_reminder_title'); ?></a>
                  <hr />
                  <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
                  <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$billing->id,'name'=>'billing','members'=>$members,'reminder_title'=>_l('billing_set_reminder_title'))); ?>
               </div>
               <div role="tabpanel" class="tab-pane ptop10" id="tab_views">
                  <?php
                     $views_activity = get_views_tracking('billing',$billing->id);
                       if(count($views_activity) === 0) {
                     echo '<h4 class="no-margin">'._l('not_viewed_yet',_l('billing_lowercase')).'</h4>';
                     }
                     foreach($views_activity as $activity){ ?>
                  <p class="text-success no-margin">
                     <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
                  </p>
                  <p class="text-muted">
                     <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
                  </p>
                  <hr />
                  <?php } ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div id="modal-wrapper"></div>
<?php // $this->load->view('admin/billings/send_billing_to_email_template'); ?>
<script>
     // defined in manage billings
     billing_id = '<?php echo $billing->id; ?>';
     billing_status = '<?php echo $billing->status; ?>';
     //init_billing_editor();  init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
   init_billings_attach_file();
   init_billing_status(billing_status);
 

/**
 * Format billing status with label or not
 * @param  mixed  $status  billing status id
 * @param  string  $classes additional label classes
 * @param  boolean $label   to include the label or return just translated text
 * @return string
 */
function format_billing_status(status, classes = '', label = true)
{

    id = status;
    if (status == 1) {
        status      = "<?php echo _l('billing_status_draft'); ?>";
        label_class = 'default';
    } else if (status == 2) {
        status      = "<?php echo _l('billing_status_declined'); ?>";
        label_class = 'danger';
    } else if (status == 3) {
        status      = "<?php echo _l('billing_status_accepted'); ?>";
        label_class = 'success';
    } else if (status == 4) {
        status      = "<?php echo _l('billing_status_sent'); ?>";
        label_class = 'info';
    } else if (status == 5) {
        status      = "<?php echo _l('billing_status_expired'); ?>";
        label_class = 'warning';
    } else if (status == 6) {
        status      = "<?php echo _l('billing_status_approved'); ?>";
        label_class = 'success';
    }

    if (label == true) {
        return '<span class="label label-' + label_class + ' ' + classes + ' s-status billing-status-' + id + '">' + status + '</span>';
    }

    return status;

}


</script>
