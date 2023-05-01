<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade billing-convert-modal" id="convert_to_invoice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xxl" role="document">
        <?php echo form_open('admin/billings/convert_to_invoice/'.$billing->id,array('id'=>'billing_convert_to_invoice_form','class'=>'_transaction_form invoice-form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="close_modal_manually('#convert_to_invoice')" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('billing_convert_to_invoice'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php $this->load->view('admin/invoices/invoice_template'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default invoice-form-submit save-as-draft transaction-submit">
                    <?php echo _l('save_as_draft'); ?>
                </button>
                <button class="btn btn-info invoice-form-submit transaction-submit">
                    <?php echo _l('submit'); ?>
                </button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php $this->load->view('admin/invoice_items/item'); ?>
<script>
    init_ajax_search('customer','#client_id.ajax-search');
    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
    custom_fields_hyperlink();
    init_selectpicker();
    init_tags_inputs();
    init_datepicker();
    init_color_pickers();
    init_items_sortable();
    validate_invoice_form('#billing_convert_to_invoice_form');
    <?php if($billing->assigned != 0){ ?>
     $('#convert_to_invoice #sale_agent').selectpicker('val',<?php echo $billing->assigned; ?>);
    <?php } ?>
    $('select[name="discount_type"]').selectpicker('val','<?php echo $billing->discount_type; ?>');
    $('input[name="discount_percent"]').val('<?php echo $billing->discount_percent; ?>');
    $('input[name="discount_total"]').val('<?php echo $billing->discount_total; ?>');
    <?php if(is_sale_discount($billing,'fixed')) { ?>
        $('.discount-total-type.discount-type-fixed').click();
    <?php } ?>
    $('input[name="adjustment"]').val('<?php echo $billing->adjustment; ?>');
    $('input[name="show_quantity_as"][value="<?php echo $billing->show_quantity_as; ?>"]').prop('checked',true).change();
    $('#convert_to_invoice #client_id').change();
    // Trigger item select width fix
    $('#convert_to_invoice').on('shown.bs.modal', function(){
        $('#item_select').trigger('change')
    })
</script>
