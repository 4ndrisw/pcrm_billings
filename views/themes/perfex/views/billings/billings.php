<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s section-heading section-billings">
  <div class="panel-body">
    <h4 class="no-margin section-text"><?php echo _l('billings'); ?></h4>
  </div>
</div>
<div class="panel_s">
  <div class="panel-body">
    <table class="table dt-table table-billings" data-order-col="3" data-order-type="desc">
      <thead>
        <tr>
          <th class="th-billing-number"><?php echo _l('billing') . ' #'; ?></th>
          <th class="th-billing-subject"><?php echo _l('billing_subject'); ?></th>
          <th class="th-billing-total"><?php echo _l('billing_total'); ?></th>
          <th class="th-billing-open-till"><?php echo _l('billing_open_till'); ?></th>
          <th class="th-billing-date"><?php echo _l('billing_date'); ?></th>
          <th class="th-billing-status"><?php echo _l('billing_status'); ?></th>
          <?php
          $custom_fields = get_custom_fields('billing',array('show_on_client_portal'=>1));
          foreach($custom_fields as $field){ ?>
            <th><?php echo $field['name']; ?></th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach($billings as $billing){ ?>
          <tr>
            <td>
              <a href="<?php echo site_url('billing/'.$billing['id'].'/'.$billing['hash']); ?>" class="td-billing-url">
                <?php echo format_billing_number($billing['id']); ?>
                <?php
                if ($billing['invoice_id']) {
                  echo '<br /><span class="text-success billing-invoiced">' . _l('billing_invoiced') . '</span>';
                }
                ?>
              </a>
              <td>
                <a href="<?php echo site_url('billing/'.$billing['id'].'/'.$billing['hash']); ?>" class="td-billing-url-subject">
                  <?php echo $billing['subject']; ?>
                </a>
                <?php
                if ($billing['invoice_id'] != NULL) {
                  $invoice = $this->invoices_model->get($billing['invoice_id']);
                  echo '<br /><a href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '" target="_blank" class="td-billing-invoice-url">' . format_invoice_number($invoice->id) . '</a>';
                } else if ($billing['billing_id'] != NULL) {
                  $billing = $this->billings_model->get($billing['billing_id']);
                  echo '<br /><a href="' . site_url('billing/' . $billing->id . '/' . $billing->hash) . '" target="_blank" class="td-billing-billing-url">' . format_billing_number($billing->id) . '</a>';
                }
                ?>
              </td>
              <td data-order="<?php echo $billing['total']; ?>">
                <?php
                if ($billing['currency'] != 0) {
                 echo app_format_money($billing['total'], get_currency($billing['currency']));
               } else {
                 echo app_format_money($billing['total'], get_base_currency());
               }
               ?>
             </td>
             <td data-order="<?php echo $billing['open_till']; ?>"><?php echo _d($billing['open_till']); ?></td>
             <td data-order="<?php echo $billing['date']; ?>"><?php echo _d($billing['date']); ?></td>
             <td><?php echo format_billing_status($billing['status']); ?></td>
             <?php foreach($custom_fields as $field){ ?>
               <td><?php echo get_custom_field_value($billing['id'],$field['id'],'billing'); ?></td>
             <?php } ?>
           </tr>
         <?php } ?>
       </tbody>
     </table>
   </div>
 </div>
