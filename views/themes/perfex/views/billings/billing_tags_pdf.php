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
    $biiling_date = getDay($billing->date) .' '.getMonth($billing->date).' '.getYear($billing->date);
    $organization_info .=  '<tr>
                                <td width="25%"><strong>Tanggal</strong></td>
                                <td width="5%">:</td>
                                <td width="70%">' . $biiling_date .'</td>
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
//$table_item = get_items_table_data($billing, 'billing', 'pdf');

//$tblhtml = $table_item->table();

//$pdf->writeHTML($tblhtml, true, false, false, false, '');

//var_dump($billing->items);

$table_item  = '';
$table_item .= '<table style="padding:5,10,5,10" border="1" class="table table-bordered table-billing-items">';
$table_item .=    '<thead>';
$table_item .=        '<tr  style="background-color:#000000;color:#FFFFFF; font-weight:bold">';
$table_item .=            '<th width="50" align="center">No#</th>';
$table_item .=            '<th width="350" align="center">Peralatan</th>';
$table_item .=            '<th width="100" align="center">Item/Lot</th>';
$table_item .=            '<th width="150" align="center">Price</th>';
$table_item .=            '<th width="170" align="center">Total</th>';
$table_item .=        '</tr>';
$table_item .=    '</thead>';
$table_item .=    '<tbody>';
        $i=1;
        foreach($billing->items as $billing_item){
            
$table_item .=            '<tr>';
$table_item .=                '<td width="50" align="right">' .$i.' </td>';
$table_item .=                '<td width="350">' .$billing_item['tags_name']. '</td>';
$table_item .=                '<td width="100" align="center">' .$billing_item['count']. '</td>';
$table_item .=                '<td width="150" align="right">' . app_format_money($billing_item['price'], $billing->currency_name). '</td>';
$table_item .=                '<td width="170" align="right">' .app_format_money($billing_item['count'] * $billing_item['price'], $billing->currency_name). '</td>';
$table_item .=            '</tr>';
            
             $i++; 
         } 
$table_item .=    '</tbody>';
$table_item .= '</table>';

pdf_multi_row($table_item, '', $pdf, ($dimensions['wk'] / 1) - $dimensions['lm']);


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
$total_tax  = 0;
foreach ($billing->items as $tax) {
    $total_tax = $total_tax + $tax['price']*$tax['count']*$tax['taxrate']/100;
}
$tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%">' . app_format_money($total_tax, $billing->currency_name) . '</td>
</tr>';

if ((int)$billing->pph_total != 0) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('billing_pph') .' ('. $billing->pph .'%)</strong></td>
    <td align="right" width="15%">' .'- '. app_format_money($billing->pph_total, $billing->currency_name) . '</td>
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
$numberword = $CI->numberword->convert($billing->total, $billing->currency_name);
if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $numberword, 0, 1, false, true, 'C', true);
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
    $text = format_billing_number($billing->id)  .' - ' . $biiling_date . ' - ' . get_company_name($billing->clientid);
    $assigned_info .= $pdf->write2DBarcode($text, 'QRCODE,L', 37, $pdf->getY(), 40, 40, $style);

    $assigned_info .=  '<br /> <br /> <br /> <br /> <br /> <br /><br />';   
    $assigned_info .= get_staff_full_name($billing->assigned);
}
$assigned_info .= '</div>';

$client_info = '';


$right_info  = $swap == '1' ? $client_info : $assigned_info;
$left_info = $swap == '1' ? $assigned_info : $client_info;
pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

if (!empty($billing->note)) {
    $pdf->Ln(2);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('billing_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $billing->note, 0, 1, false, true, 'L', true);
}


$pdf->AddPage();

$text = '<h1>K W I T A N S I</h1>';
$pdf->writeHTMLCell('', '', '', '', $text, 0, 1, false, true, 'C', true);


$text = '<h2>No. '. format_billing_number($billing->id) .' </h2>';
$pdf->writeHTMLCell('', '', '', '', $text, 0, 1, false, true, 'C', true);

$pdf->Ln(6);

$tblreceipt = '';
$tblreceipt .= '<table cellpadding="6" style="padding: 5px 5px; font-size:' . ($font_size + 4) . 'px">';
$tblreceipt .= '
<tr>
    <td align="left" width="40%" bgcolor="#ddd"><strong>' . _l('billing_client') . '</strong></td>
    <td align="center" width="5%"><strong>' . ':' . '</strong></td>
    <td align="left" width="45%">' . get_company_name($billing->clientid) . '</td>
</tr>';
$tblreceipt .= '
<tr>
    <td></td><td></td><td></td>
</tr>';

$tblreceipt .= '
<tr>
    <td align="left" width="40%" bgcolor="#ddd"><strong>' . _l('billing_subject') . '</strong></td>
    <td align="center" width="5%"><strong>' . ':' . '</strong></td>
    <td align="left" width="45%">' . $billing->subject . '<br />( Perincian Terlampir Pada Invoice )</td>
</tr>';

$tblreceipt .= '
<tr>
    <td></td><td></td><td></td>
</tr>';

$tblreceipt .= '
<tr>
    <td align="left" width="40%" bgcolor="#ddd"><strong>' . _l('billing_amount') . '</strong></td>
    <td align="center" width="5%"><strong>' . ':' . '</strong></td>
    <td align="left" width="45%">' . app_format_money($billing->total, $billing->currency_name) . '</td>
</tr>';

$tblreceipt .= '
<tr>
    <td></td><td></td><td></td>
</tr>';

$tblreceipt .= '
<tr>
    <td align="left" width="40%" bgcolor="#ddd"><strong>' . _l('num_word') . '</strong></td>
    <td align="center" width="5%"><strong>' . ':' . '</strong></td>
    <td align="left" width="45%">' . $numberword . '</td>
</tr>';

$tblreceipt .= '</table>';

$pdf->writeHTML($tblreceipt, true, false, false, false, '');


$assigned_info = '';

$client_info = '<div style="text-align:center;">';
    $client_info .= 'Serang, ' . getDay($billing->date) .' '.getMonth($billing->date).' '.getYear($billing->date) .'<br />';
    $client_info .= get_option('invoice_company_name') . '<br />';
    //$client_info .= $assigned_path . '<br />';

    $text = format_billing_number($billing->id)  .' - ' . $biiling_date . ' - ' . get_company_name($billing->clientid);
    $assigned_info .= $pdf->write2DBarcode($text, 'QRCODE,L', 37, $pdf->getY()+7, 40, 40, $style);

    $client_info .=  '<br /> <br /> <br /> <br /> <br /> <br /><br /><br />';   
    $client_info .= get_staff_full_name($billing->assigned);

$client_info .= '</div>';


$left_info = $swap == '1' ? $client_info : $assigned_info;
$right_info = $swap == '1' ? $assigned_info : $client_info;
pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);