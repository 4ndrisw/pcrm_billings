<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade billing-convert-modal" id="convert_to_billing" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-xxl" role="document">
        <?php echo form_open('admin/billings/convert_to_billing/'.$billing->id,array('id'=>'billing_convert_to_billing_form','class'=>'_transaction_form disable-on-submit')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" onclick="close_modal_manually('#convert_to_billing')" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('billing_convert_to_billing'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php $this->load->view('admin/billings/billing_template'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="close_modal_manually('#convert_to_billing')">
                    <?php echo _l('close'); ?>
                </button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
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
    init_datepicker();
    init_color_pickers();
    init_items_sortable();
    init_tags_inputs();
    validate_billing_form('#billing_convert_to_billing_form');
    <?php if($billing->assigned != 0){ ?>
    $('#convert_to_billing #sale_agent').selectpicker('val',<?php echo $billing->assigned; ?>);
    <?php } ?>
    $('select[name="discount_type"]').selectpicker('val','<?php echo $billing->discount_type; ?>');
    $('input[name="discount_percent"]').val('<?php echo $billing->discount_percent; ?>');
    $('input[name="discount_total"]').val('<?php echo $billing->discount_total; ?>');
    <?php if(is_sale_discount($billing,'fixed')) { ?>
        $('.discount-total-type.discount-type-fixed').click();
    <?php } ?>
    $('input[name="adjustment"]').val('<?php echo $billing->adjustment; ?>');
    $('input[name="show_quantity_as"][value="<?php echo $billing->show_quantity_as; ?>"]').prop('checked',true).change();
    $('#convert_to_billing #client_id').change();
    // Trigger item select width fix
    $('#convert_to_billing').on('shown.bs.modal', function(){
        $('#item_select').trigger('change')
    })

</script>
