<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content accounting-template billing">
      <div class="row">
      
          <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body">
                  <div class="row">

	  <?php echo form_open_multipart('billings/settings');?>



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
	                     <a href="<?php echo admin_url('billings/remove_iso_logo'); ?>" data-toggle="tooltip" title="<?php echo _l('settings_general_iso_remove_logo_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
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
	                     <a href="<?php echo admin_url('billings/remove_iso_logo/dark'); ?>" data-toggle="tooltip" title="<?php echo _l('settings_general_iso_remove_logo_tooltip'); ?>" class="_delete text-danger"><i class="fa fa-remove"></i></a>
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

      <div class="form-group">
         <label class="control-label" for="billing_prefix"><?php echo _l('billing_prefix'); ?></label>
         <input type="text" name="settings[billing_prefix]" class="form-control" value="<?php echo get_option('billing_prefix'); ?>">
      </div>
      <hr />
	      
	      <input type="submit" value="upload" />


            <div class="clearfix"></div>
        
        <?php echo form_close(); ?>

	    </div>
	    </div>
	    </div>
	    </div>
	    </div>

      </div>
   </div>
</div>