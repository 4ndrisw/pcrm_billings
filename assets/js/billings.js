// Init single billing
function init_billing(id) {
    load_small_table_item(id, '#billing', 'billing_id', 'billings/get_billing_data_ajax', '.table-billings');
}

/*
if ($("body").hasClass('billings-pipeline')) {
    var billing_id = $('input[name="billingid"]').val();
    billing_pipeline_open(billing_id);
}
*/


function add_billing_comment() {
    var comment = $('#comment').val();
    if (comment == '') {
        return;
    }
    var data = {};
    data.content = comment;
    data.billingid = billing_id;
    $('body').append('<div class="dt-loader"></div>');
    $.post(admin_url + 'billings/add_billing_comment', data).done(function (response) {
        response = JSON.parse(response);
        $('body').find('.dt-loader').remove();
        if (response.success == true) {
            $('#comment').val('');
            get_billing_comments();
        }
    });
}

function get_billing_comments() {
    if (typeof (billing_id) == 'undefined') {
        return;
    }
    requestGet('billings/get_billing_comments/' + billing_id).done(function (response) {
        $('body').find('#billing-comments').html(response);
        update_comments_count('billing')
    });
}

function remove_billing_comment(commentid) {
    if (confirm_delete()) {
        requestGetJSON('billings/remove_comment/' + commentid).done(function (response) {
            if (response.success == true) {
                $('[data-commentid="' + commentid + '"]').remove();
                update_comments_count('billing')
            }
        });
    }
}

function edit_billing_comment(id) {
    var content = $('body').find('[data-billing-comment-edit-textarea="' + id + '"] textarea').val();
    if (content != '') {
        $.post(admin_url + 'billings/edit_comment/' + id, {
            content: content
        }).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                $('body').find('[data-billing-comment="' + id + '"]').html(nl2br(content));
            }
        });
        toggle_billing_comment_edit(id);
    }
}

function toggle_billing_comment_edit(id) {
    $('body').find('[data-billing-comment="' + id + '"]').toggleClass('hide');
    $('body').find('[data-billing-comment-edit-textarea="' + id + '"]').toggleClass('hide');
}


function add_billing_note() {
    var note = $('#note').val();
    if (note == '') {
        return;
    }
    var data = {};
    data.content = note;
    data.billingid = billing_id;
    $('body').append('<div class="dt-loader"></div>');
    $.post(admin_url + 'billings/add_billing_note', data).done(function (response) {
        response = JSON.parse(response);
        $('body').find('.dt-loader').remove();
        if (response.success == true) {
            $('#note').val('');
            get_billing_notes();
        }
    });
}


    
function get_billing_notes() {
    if (typeof (billing_id) == 'undefined') {
        return;
    }
    requestGet('billings/get_billing_notes/' + billing_id).done(function (response) {
        $('body').find('#billing-notes').html(response);
        update_notes_count('billing')
    });
}

function remove_billing_note(noteid) {
    if (confirm_delete()) {
        requestGetJSON('billings/remove_note/' + noteid).done(function (response) {
            if (response.success == true) {
                $('[data-noteid="' + noteid + '"]').remove();
                update_notes_count('billing')
            }
        });
    }
}

function edit_billing_note(id) {
    var content = $('body').find('[data-billing-note-edit-textarea="' + id + '"] textarea').val();
    if (content != '') {
        $.post(admin_url + 'billings/edit_note/' + id, {
            content: content
        }).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
                $('body').find('[data-billing-note="' + id + '"]').html(nl2br(content));
            }
        });
        toggle_billing_note_edit(id);
    }
}

function toggle_billing_note_edit(id) {
    $('body').find('[data-billing-note="' + id + '"]').toggleClass('hide');
    $('body').find('[data-billing-note-edit-textarea="' + id + '"]').toggleClass('hide');
}

function update_notes_count() {
  var count = $(".note-item").length;
  $(".total_notes").text(count);
  if (count === 0) {
    $(".total_notes").addClass("hide");
  } else {
    $(".total_notes").removeClass("hide");
  }
}

function billing_convert_template(invoker) {
    var template = $(invoker).data('template');
    var html_helper_selector;
    if (template == 'billing') {
        html_helper_selector = 'billing';
    } else if (template == 'invoice') {
        html_helper_selector = 'invoice';
    } else {
        return false;
    }

    requestGet('billings/get_' + html_helper_selector + '_convert_data/' + billing_id).done(function (data) {
        if ($('.billing-pipeline-modal').is(':visible')) {
            $('.billing-pipeline-modal').modal('hide');
        }
        $('#convert_helper').html(data);
        $('#convert_to_' + html_helper_selector).modal({
            show: true,
            backdrop: 'static'
        });
        reorder_items();
    });

}

function save_billing_content(manual) {
    var editor = tinyMCE.activeEditor;
    var data = {};
    data.billing_id = billing_id;
    data.content = editor.getContent();
    $.post(admin_url + 'billings/save_billing_data', data).done(function (response) {
        response = JSON.parse(response);
        if (typeof (manual) != 'undefined') {
            // Show some message to the user if saved via CTRL + S
            alert_float('success', response.message);
        }
        // Invokes to set dirty to false
        editor.save();
    }).fail(function (error) {
        var response = JSON.parse(error.responseText);
        alert_float('danger', response.message);
    });
}

// Proposal sync data in case eq mail is changed, shown for lead and customers.
function sync_billings_data(rel_id, rel_type) {
    var data = {};
    var modal_sync = $('#sync_data_billing_data');
    data.country = modal_sync.find('select[name="country"]').val();
    data.zip = modal_sync.find('input[name="zip"]').val();
    data.state = modal_sync.find('input[name="state"]').val();
    data.city = modal_sync.find('input[name="city"]').val();
    data.address = modal_sync.find('textarea[name="address"]').val();
    data.phone = modal_sync.find('input[name="phone"]').val();
    data.rel_id = rel_id;
    data.rel_type = rel_type;
    $.post(admin_url + 'billings/sync_data', data).done(function (response) {
        response = JSON.parse(response);
        alert_float('success', response.message);
        modal_sync.modal('hide');
    });
}


// Delete billing attachment
function delete_billing_attachment(id) {
    if (confirm_delete()) {
        requestGet('billings/delete_attachment/' + id).done(function (success) {
            if (success == 1) {
                var rel_id = $("body").find('input[name="_attachment_sale_id"]').val();
                $("body").find('[data-attachment-id="' + id + '"]').remove();
                $("body").hasClass('billings-pipeline') ? billing_pipeline_open(rel_id) : init_billing(rel_id);
            }
        }).fail(function (error) {
            alert_float('danger', error.responseText);
        });
    }
}

// Used when billing is updated from pipeline. eq changed order or moved to another status
function billings_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {
            billingid: $(ui.item).attr('data-billing-id'),
            status: $(ui.item.parent()[0]).attr('data-status-id'),
            order: [],
        };

        $.each($(ui.item).parents('.pipeline-status').find('li'), function (idx, el) {
            var id = $(el).attr('data-billing-id');
            if(id){
                data.order.push([id, idx+1]);
            }
        });

        check_kanban_empty_col('[data-billing-id]');

        setTimeout(function () {
             $.post(admin_url + 'billings/update_pipeline', data).done(function (response) {
                update_kan_ban_total_when_moving(ui,data.status);
                billing_pipeline();
            });
        }, 200);
    }
}

// Used when billing is updated from pipeline. eq changed order or moved to another status
function billings_pipeline_update(ui, object) {
    if (object === ui.item.parent()[0]) {
        var data = {
            order: [],
            status: $(ui.item.parent()[0]).attr('data-status-id'),
            billingid: $(ui.item).attr('data-billing-id'),
        };

        $.each($(ui.item).parents('.pipeline-status').find('li'), function (idx, el) {
            var id = $(el).attr('data-billing-id');
            if(id){
                data.order.push([id, idx+1]);
            }
        });

        check_kanban_empty_col('[data-billing-id]');

        setTimeout(function () {
            $.post(admin_url + 'billings/update_pipeline', data).done(function (response) {
                update_kan_ban_total_when_moving(ui,data.status);
                billings_pipeline();
            });
        }, 200);
    }
}

// Init billings pipeline
function billings_pipeline() {
    init_kanban('billings/get_pipeline', billings_pipeline_update, '.pipeline-status', 347, 360);
}

// Open single billing in pipeline
function billing_pipeline_open(id) {
    if (id === '') {
        return;
    }
    requestGet('billings/pipeline_open/' + id).done(function (response) {
        var visible = $('.billing-pipeline-modal:visible').length > 0;
        $('#billing').html(response);
        if (!visible) {
            $('.billing-pipeline-modal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
        } else {
            $('#billing').find('.modal.billing-pipeline-modal')
                .removeClass('fade')
                .addClass('in')
                .css('display', 'block');
        }
    });
}

// Sort billings in the pipeline view / switching sort type by click
function billing_pipeline_sort(type) {
    kan_ban_sort(type, billings_pipeline);
}

// Validates billing add/edit form
function validate_billing_form(selector) {

    selector = typeof (selector) == 'undefined' ? '#billing-form' : selector;

    appValidateForm($(selector), {
        rel_id: {
            required: {
                depends: function () {
                    var customerRemoved = $('select#rel_type').hasClass('customer-removed');
                    return !customerRemoved;
                }
            }
        },
        date: 'required',
        number: {
            required: true
        }
    });

    $("body").find('input[name="number"]').rules('add', {
        remote: {
            url: admin_url + "billings/validate_billing_number",
            type: 'post',
            data: {
                number: function () {
                    return $('input[name="number"]').val();
                },
                isedit: function () {
                    return $('input[name="number"]').data('isedit');
                },
                original_number: function () {
                    return $('input[name="number"]').data('original-number');
                },
                date: function () {
                    return $('body').find('.billing input[name="date"]').val();
                },
            }
        },
        messages: {
            remote: app.lang.billing_number_exists,
        }
    });

}


// Get the preview main values
function get_billing_item_preview_values() {
    var response = {};
    response.description = $('.main textarea[name="description"]').val();
    response.long_description = $('.main textarea[name="long_description"]').val();
    response.qty = $('.main input[name="quantity"]').val();
    return response;
}

// Append the added items to the preview to the table as items
function add_billing_item_to_table(data, itemid){

  // If not custom data passed get from the preview
  data = typeof (data) == 'undefined' || data == 'undefined' ? get_billing_item_preview_values() : data;
  if (data.description === "" && data.long_description === "") {
     return;
  }

  var table_row = '';
  var item_key = lastAddedItemKey ? lastAddedItemKey += 1 : $("body").find('tbody .item').length + 1;
  lastAddedItemKey = item_key;

  table_row += '<tr class="sortable item">';

  table_row += '<td class="dragger">';

  // Check if quantity is number
  if (isNaN(data.qty)) {
     data.qty = 1;
  }

  $("body").append('<div class="dt-loader"></div>');
  var regex = /<br[^>]*>/gi;

     table_row += '<input type="hidden" class="order" name="newitems[' + item_key + '][order]">';

     table_row += '</td>';

     table_row += '<td class="bold description"><textarea name="newitems[' + item_key + '][description]" class="form-control" rows="5">' + data.description + '</textarea></td>';

     table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description.replace(regex, "\n") + '</textarea></td>';
   //table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description + '</textarea></td>';


     table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="newitems[' + item_key + '][qty]" value="' + data.qty + '" class="form-control">';

     if (!data.unit || typeof (data.unit) == 'undefined') {
        data.unit = '';
     }

     table_row += '<input type="text" placeholder="' + app.lang.unit + '" name="newitems[' + item_key + '][unit]" class="form-control input-transparent text-right" value="' + data.unit + '">';

     table_row += '</td>';


     table_row += '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' + itemid + '); return false;"><i class="fa fa-trash"></i></a></td>';

     table_row += '</tr>';

     $('table.items tbody').append(table_row);

     $(document).trigger({
        type: "item-added-to-table",
        data: data,
        row: table_row
     });


     clear_item_preview_values();
     reorder_items();

     $('body').find('#items-warning').remove();
     $("body").find('.dt-loader').remove();

  return false;
}


// From billing table mark as
function billing_mark_as(status_id, billing_id) {
    var data = {};
    data.status = status_id;
    data.billingid = billing_id;
    $.post(admin_url + 'billings/update_billing_status', data).done(function (response) {
        reload_billings_tables();
    });
    init_billing_status(status_id);
}

// Reload all billings possible table where the table data needs to be refreshed after an action is performed on task.
function reload_billings_tables() {
    var av_billings_tables = ['.table-billings', '.table-rel-billings'];
    $.each(av_billings_tables, function (i, selector) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().ajax.reload(null, false);
        }
    });
}

function init_billings_attach_file(){

  $("#billings_attach_file").on("hidden.bs.modal", function (e) {
    $("#sales_uploaded_files_preview").empty();
    $(".dz-file-preview").empty();
  });

  if (typeof Dropbox != "undefined") {
    if ($("#dropbox-chooser-sales").length > 0) {
      document.getElementById("dropbox-chooser-sales").appendChild(
        Dropbox.createChooseButton({
          success: function (files) {
            salesExtenalFileUpload(files, "dropbox");
          },
          linkType: "preview",
          extensions: app.options.allowed_files.split(","),
        })
      );
    }
  }
  /*
  if ($("#sales-upload").length > 0) {
    new Dropzone(
      "#sales-upload",
      appCreateDropzoneOptions({
        sending: function (file, xhr, formData) {
          formData.append(
            "rel_id",
            $("body").find('input[name="_attachment_sale_id"]').val()
          );
          formData.append(
            "type",
            $("body").find('input[name="_attachment_sale_type"]').val()
          );
        },
        success: function (files, response) {
          response = JSON.parse(response);
          var type = $("body")
            .find('input[name="_attachment_sale_type"]')
            .val();
          var dl_url, delete_function;
          dl_url = "download/file/sales_attachment/";
          delete_function = "delete_" + type + "_attachment";
          if (type == "estimate") {
            $("body").hasClass("estimates-pipeline")
              ? estimate_pipeline_open(response.rel_id)
              : init_estimate(response.rel_id);
          } else if (type == "proposal") {
            $("body").hasClass("proposals-pipeline")
              ? proposal_pipeline_open(response.rel_id)
              : init_proposal(response.rel_id);
          } else {
            if (typeof window["init_" + type] == "function") {
              window["init_" + type](response.rel_id);
            }
          }
          var data = "";
          if (response.success === true || response.success == "true") {
            data +=
              '<div class="display-block sales-attach-file-preview" data-attachment-id="' +
              response.attachment_id +
              '">';
            data += '<div class="col-md-10">';
            data +=
              '<div class="pull-left"><i class="attachment-icon-preview fa-regular fa-file"></i></div>';
            data +=
              '<a href="' +
              site_url +
              dl_url +
              response.key +
              '" target="_blank">' +
              response.file_name +
              "</a>";
            data += '<p class="text-muted">' + response.filetype + "</p>";
            data += "</div>";
            data += '<div class="col-md-2 text-right">';
            data +=
              '<a href="#" class="text-danger" onclick="' +
              delete_function +
              "(" +
              response.attachment_id +
              '); return false;"><i class="fa fa-times"></i></a>';
            data += "</div>";
            data += '<div class="clearfix"></div><hr/>';
            data += "</div>";
            $("#sales_uploaded_files_preview").append(data);
          }
        },
      })
    );
  }
  */
}

function calculate_total_billing_with_pph(){

  // Recaulciate total on these changes
  $("body").on("change", 'input[name="pph"],select.tax', function () {
    calculate_total_with_pph();
  });

}


// Calculate invoice total - NOT RECOMENDING EDIT THIS FUNCTION BECUASE IS VERY SENSITIVE
function calculate_total_with_pph() {
  if ($("body").hasClass("no-calculate-total")) {
    return false;
  }

  var calculated_tax,
    taxrate,
    item_taxes,
    row,
    _amount,
    _tax_name,
    taxes = {},
    taxes_rows = [],
    subtotal = 0,
    total = 0,
    quantity = 1,
    total_discount_calculated = 0,
    rows = $(".table.has-calculations tbody tr.item"),
    discount_area = $("#discount_area"),
    adjustment = $('input[name="adjustment"]').val(),
    discount_percent = $('input[name="discount_percent"]').val(),
    discount_fixed = $('input[name="discount_total"]').val(),
    discount_total_type = $(".discount-total-type.selected"),
    discount_type = $('select[name="discount_type"]').val();

  $(".tax-area").remove();

  $.each(rows, function () {
    quantity = $(this).find("[data-quantity]").val();
    if (quantity === "") {
      quantity = 1;
      $(this).find("[data-quantity]").val(1);
    }

    _amount = accounting.toFixed(
      $(this).find("td.rate input").val() * quantity,
      app.options.decimal_places
    );
    _amount = parseFloat(_amount);

    $(this).find("td.amount").html(format_money(_amount, true));
    subtotal += _amount;
    row = $(this);
    item_taxes = $(this).find("select.tax").selectpicker("val");

    if (item_taxes) {
      $.each(item_taxes, function (i, taxname) {
        taxrate = row
          .find('select.tax [value="' + taxname + '"]')
          .data("taxrate");
        calculated_tax = (_amount / 100) * taxrate;
        if (!taxes.hasOwnProperty(taxname)) {
          if (taxrate != 0) {
            _tax_name = taxname.split("|");
            tax_row =
              '<tr class="tax-area"><td>' +
              _tax_name[0] +
              "(" +
              taxrate +
              '%)</td><td id="tax_id_' +
              slugify(taxname) +
              '"></td></tr>';
            $(discount_area).after(tax_row);
            taxes[taxname] = calculated_tax;
          }
        } else {
          // Increment total from this tax
          taxes[taxname] = taxes[taxname] += calculated_tax;
        }
      });
    }
  });

  // Discount by percent
  if (
    discount_percent !== "" &&
    discount_percent != 0 &&
    discount_type == "before_tax" &&
    discount_total_type.hasClass("discount-type-percent")
  ) {
    total_discount_calculated = (subtotal * discount_percent) / 100;
  } else if (
    discount_fixed !== "" &&
    discount_fixed != 0 &&
    discount_type == "before_tax" &&
    discount_total_type.hasClass("discount-type-fixed")
  ) {
    total_discount_calculated = discount_fixed;
  }

  $.each(taxes, function (taxname, total_tax) {
    if (
      discount_percent !== "" &&
      discount_percent != 0 &&
      discount_type == "before_tax" &&
      discount_total_type.hasClass("discount-type-percent")
    ) {
      total_tax_calculated = (total_tax * discount_percent) / 100;
      total_tax = total_tax - total_tax_calculated;
    } else if (
      discount_fixed !== "" &&
      discount_fixed != 0 &&
      discount_type == "before_tax" &&
      discount_total_type.hasClass("discount-type-fixed")
    ) {
      var t = (discount_fixed / subtotal) * 100;
      total_tax = total_tax - (total_tax * t) / 100;
    }

    total += total_tax;
    total_tax = format_money(total_tax);
    $("#tax_id_" + slugify(taxname)).html(total_tax);
  });

  total = total + subtotal;

  // Discount by percent
  if (
    discount_percent !== "" &&
    discount_percent != 0 &&
    discount_type == "after_tax" &&
    discount_total_type.hasClass("discount-type-percent")
  ) {
    total_discount_calculated = (total * discount_percent) / 100;
  } else if (
    discount_fixed !== "" &&
    discount_fixed != 0 &&
    discount_type == "after_tax" &&
    discount_total_type.hasClass("discount-type-fixed")
  ) {
    total_discount_calculated = discount_fixed;
  }

  total = total - total_discount_calculated;

  var  pph = $('input[name="pph"]').val();
  
  pre_pph = total;
  pph_calculated = 0;

  if (
    pph !== "" &&
    pph != 0
  ) {
      pph_calculated = (total * pph) / 100;
      total = total - pph_calculated;
  }else{
      pph_calculated = 0;
      total = pre_pph;
  }


  adjustment = parseFloat(adjustment);
  console.log('--1-- ' + total);
  // Check if adjustment not empty
  if (!isNaN(adjustment)) {
    total = total + adjustment;
  }

  console.log('--2-- ' + total);

  var discount_html = "-" + format_money(total_discount_calculated);
  $('input[name="discount_total"]').val(
    accounting.toFixed(total_discount_calculated, app.options.decimal_places)
  );

  $(".pph-total").html(
    format_money(pph_calculated) +
      hidden_input(
        "pph_total",
        accounting.toFixed(pph_calculated, app.options.decimal_places)
      )
  );

  // Append, format to html and display
  $(".discount-total").html(discount_html);
  $(".adjustment").html(format_money(adjustment));
  $(".subtotal").html(
    format_money(subtotal) +
      hidden_input(
        "subtotal",
        accounting.toFixed(subtotal, app.options.decimal_places)
      )
  );

  console.log('--3-- ' + total);

  $(".total").html(
    format_money(total) +
      hidden_input(
        "total",
        accounting.toFixed(total, app.options.decimal_places)
      )
  );

  $(document).trigger("sales-total-calculated");
}

function init_billing_status(billing_status){
  $(".billing-status").html(format_billing_status(billing_status));
}
