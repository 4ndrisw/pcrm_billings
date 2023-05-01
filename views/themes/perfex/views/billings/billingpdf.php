<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$pdf->ln(25);

// Estimate to
$customer_info = '<b>' . _l('billing_to') . '</b>';
$customer_info .= '<div style="color:#424242;">';
$customer_info .= format_billing_info($billing, 'billing');
$customer_info .= '</div>';

if (!empty($billing->reference_no)) {
    $customer_info .= _l('reference_no') . ': ' . $billing->reference_no . '<br />';
}

$organization_info = '<div style="color:#424242;">';
    //$organization_info .= format_organization_info();
//    $organization_info .= '<span style = "width:300px;">Nomor</span><span>:</span> </span>' .format_billing_number($billing->id) . '</div>';
//    $organization_info .= '<span >Nomor</span><span>:</span> </span>' ._d($billing->date) . '</div>';


    $organization_info .=  '<table width=100%>';
    $organization_info .=  '<tr>
                                <td width="25%"><strong>Nomor</strong></td>
                                <td width="5%">:</td>
                                <td width="70%">' .format_billing_number($billing->id) . '</td>
                            </tr>';
    $organization_info .=  '<tr>
                                <td width="25%"><strong>Tanggal</strong></td>
                                <td width="5%">:</td>
                                <td width="70%">' .getDay($billing->date) .' '.getMonth($billing->date).' '.getYear($billing->date) .'</td>
                            </tr>';
    $organization_info .=  '<tr>
                                <td width="25%"><strong>Perihal</strong></td>
                                <td width="5%">:</td>
                                <td width="70%">' . $billing->subject . '</td>
                            </tr>';

    if (!empty($billing->reference_no)) {
        $customer_info .= _l('reference_no') . ': ' . $billing->reference_no . '<br />';
        $organization_info .=  '<tr>
                                <td width="25%">'._l('reference_no') .'</td>
                                <td width="5%">:</td>
                                <td width="70%">' . $billing->reference_no . '</td>
                            </tr>';

    }

    $organization_info .=  '</table>';


$organization_info .= '</div>';

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT - 5, PDF_MARGIN_TOP + 10, PDF_MARGIN_RIGHT - 5);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER + 30);

$right_info  = $swap == '1' ? $customer_info : $organization_info;
$left_info = $swap == '1' ? $organization_info : $customer_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->Ln(6);
$text = '<h1>I N V O I C E</h1>';

$pdf->writeHTMLCell('', '', '', '', $text, 0, 1, false, true, 'C', true);

// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 4));

// The items table
$items = get_items_table_data($billing, 'billing', 'pdf');

$tblhtml = $items->table();

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(2);
$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('billing_subtotal') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($billing->subtotal, $billing->currency_name) . '</td>
</tr>';

if (is_sale_discount_applied($billing)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('billing_discount');
    if (is_sale_discount($billing, 'percent')) {
        $tbltotal .= ' (' . app_format_number($billing->discount_percent, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . app_format_money($billing->discount_total, $billing->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%">' . app_format_money($tax['total_tax'], $billing->currency_name) . '</td>
</tr>';
}

if ((int)$billing->adjustment != 0) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('billing_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($billing->adjustment, $billing->currency_name) . '</td>
</tr>';
}

$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('billing_total') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($billing->total, $billing->currency_name) . '</td>
</tr>';

$tbltotal .= '</table>';

$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $CI->numberword->convert($billing->total, $billing->currency_name), 0, 1, false, true, 'C', true);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
}

$pdf->ln(6);

/*
$assigned_path = <<<EOF
        <img width="150" height="150" src="$billing->assigned_path">
    EOF;    
*/
$assigned_info = '<div style="text-align:center;">';
    $assigned_info .= get_option('invoice_company_name') . '<br />';
    //$assigned_info .= $assigned_path . '<br />';

if ($billing->assigned != 0 && get_option('show_assigned_on_billings') == 1) {
    $style = array(
        'border' => 0,
        'vpadding' => 'auto',
        'hpadding' => 'auto',
        'fgcolor' => array(0, 0, 0),
        'bgcolor' => false, //array(255,255,255)
        'module_width' => 1, // width of a single module in points
        'module_height' => 1 // height of a single module in points
     );
    $text = format_billing_number($billing->id)  .' - ' . get_company_name($billing->clientid);
    $assigned_info .= $pdf->write2DBarcode($text, 'QRCODE,L', 37, $pdf->getY(), 40, 40, $style);

    $assigned_info .=  '<br /> <br /> <br /> <br /> <br /> <br /><br />';   
    $assigned_info .= get_staff_full_name($billing->assigned);
}
$assigned_info .= '</div>';

$client_info = '';


$left_info  = $swap == '1' ? $client_info : $assigned_info;
$right_info = $swap == '1' ? $assigned_info : $client_info;
pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

if (!empty($billing->note)) {
    $pdf->Ln(2);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('billing_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $billing->note, 0, 1, false, true, 'L', true);
}

$text = 'Dokumen ini diterbitkan melalui aplikasi `CRM` PT. Cipta Mas Jaya tidak memerlukan tanda tangan basah.';
$pdf->Ln(2);
$pdf->SetY('266');
$pdf->writeHTMLCell('', '', '', '', $text, 0, 1, false, true, 'C', true);

