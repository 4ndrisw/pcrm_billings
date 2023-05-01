<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if ($billing['status'] == $status) { ?>
<li data-billing-id="<?php echo $billing['id']; ?>" class="<?php if($billing['invoice_id'] != NULL || $billing['billing_id'] != NULL){echo 'not-sortable';} ?>">
   <div class="panel-body">
      <div class="row">
         <div class="col-md-12">
            <h4 class="bold pipeline-heading">
               <a href="<?php echo admin_url('billings/list_billings/'.$billing['id']); ?>" data-toggle="tooltip" data-title="<?php echo $billing['subject']; ?>" onclick="billing_pipeline_open(<?php echo $billing['id']; ?>); return false;"><?php echo format_billing_number($billing['id']); ?></a>
               <?php if(has_permission('billings','','edit')){ ?>
               <a href="<?php echo admin_url('billings/billing/'.$billing['id']); ?>" target="_blank" class="pull-right"><small><i class="fa fa-pencil-square-o" aria-hidden="true"></i></small></a>
               <?php } ?>
            </h4>
            <span class="mbot10 inline-block full-width">
            <?php
               if($billing['rel_type'] == 'lead'){
                 echo '<a href="'.admin_url('leads/index/'.$billing['rel_id']).'" onclick="init_lead('.$billing['rel_id'].'); return false;" data-toggle="tooltip" data-title="'._l('lead').'">' .$billing['billing_to'].'</a><br />';
               } else if($billing['rel_type'] == 'customer'){
                 echo '<a href="'.admin_url('clients/client/'.$billing['rel_id']).'" data-toggle="tooltip" data-title="'._l('client').'">' .$billing['billing_to'].'</a><br />';
               }
               ?>
            </span>
         </div>
         <div class="col-md-12">
            <div class="row">
               <div class="col-md-8">
                  <?php if($billing['total'] != 0){
                     ?>
                  <span class="bold"><?php echo _l('billing_total'); ?>:
                     <?php echo app_format_money($billing['total'], get_currency($billing['currency'])); ?>
                  </span>
                  <br />
                  <?php } ?>
                  <?php echo _l('billing_date'); ?>: <?php echo _d($billing['date']); ?>
                  <?php if(is_date($billing['open_till'])){ ?>
                  <br />
                  <?php echo _l('billing_open_till'); ?>: <?php echo _d($billing['open_till']); ?>
                  <?php } ?>
                  <br />
               </div>
               <div class="col-md-4 text-right">
                  <small><i class="fa fa-comments" aria-hidden="true"></i> <?php echo _l('billing_comments'); ?>: <?php echo total_rows(db_prefix().'billing_comments', array(
                     'billingid' => $billing['id']
                     )); ?></small>
               </div>
               <?php $tags = get_tags_in($billing['id'],'billing');
                  if(count($tags) > 0){ ?>
               <div class="col-md-12">
                  <div class="mtop5 kanban-tags">
                     <?php echo render_tags($tags); ?>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</li>
<?php } ?>
