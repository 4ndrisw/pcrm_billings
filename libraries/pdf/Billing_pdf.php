<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Billing_pdf extends App_pdf
{
    protected $billing;

    private $billing_number;

    public function __construct($billing, $tag = '')
    {
        if ($billing->rel_id != null && $billing->rel_type == 'customer') {
            $this->load_language($billing->rel_id);
        } else if ($billing->rel_id != null && $billing->rel_type == 'lead') {
            $CI = &get_instance();

            $this->load_language($billing->rel_id);
            $CI->db->select('default_language')->where('id', $billing->rel_id);
            $language = $CI->db->get('leads')->row()->default_language;

            load_pdf_language($language);
        }

        $billing                = hooks()->apply_filters('billing_html_pdf_data', $billing);
        $GLOBALS['billing_pdf'] = $billing;

        parent::__construct();

        $this->tag      = $tag;
        $this->billing = $billing;


        # Don't remove these lines - important for the PDF layout
        $this->billing->content = $this->fix_editor_html($this->billing->content);
        $this->billing_status_color = billing_status_color_pdf($this->billing->status);
        $this->billing_status = format_billing_status($this->billing->status);

        $this->billing_number = format_billing_number($this->billing->id);

        $this->SetTitle($this->billing_number .'-'. get_company_name($this->billing->clientid));
        $this->SetDisplayMode('default', 'OneColumn');
    }

    //Page header
    public function Header() {

        if(get_option('print_billing_header_footer') == 0){
            return;    
        }

        $dimensions = $this->getPageDimensions();

        $billing                = hooks()->apply_filters('billing_html_pdf_data', $this->billing);
        if(isset($billing)){
            $billing_pdf = $billing;
        }

        $right = pdf_right_logo_url();
        
        // Add logo
        $left = pdf_logo_url();
        $this->ln(5);

        $page_start = $this->getPage();
        $y_start    = $this->GetY();
        $left_width = 40;
        // Write top left logo and right column info/text

        // write the left cell
        $this->MultiCell($left_width, 0, $left, 0, 'L', 0, 2, '', '', true, 0, true);

        $page_end_1 = $this->getPage();
        $y_end_1    = $this->GetY();

        $this->setPage($page_start);

        // write the right cell
        $this->MultiCell(185, 0, $right, 0, 'R', 0, 1, 0, $y_start, true, 0, true);

        //pdf_multi_row($info_right_column, '', $this, ($dimensions['wk'] / 1) - $dimensions['lm']);
        //pdf_multi_row($info_left_column, $info_right_column, $this, ($dimensions['wk'] / 1) - $dimensions['lm']);

        //$this->ln(5);
    }

    public function prepare()
    {
        $number_word_lang_rel_id = 'unknown';

        if ($this->billing->rel_type == 'customer') {
            $number_word_lang_rel_id = $this->billing->rel_id;
        }

        $this->with_number_to_word($number_word_lang_rel_id);

        $total = '';
        if ($this->billing->total != 0) {
            $total = app_format_money($this->billing->total, get_currency($this->billing->currency));
            $total = _l('billing_total') . ': ' . $total;
        }

        $this->set_view_vars([
            'number'       => $this->billing_number,
            'billing'     => $this->billing,
            'total'        => $total,
            'billing_url' => site_url('billing/' . $this->billing->id . '/' . $this->billing->hash),
        ]);

        return $this->build();
    }

    // Page footer
    public function Footer() {
        
        if(get_option('print_billing_header_footer') == 0){
            return;    
        }

        // Position at 15 mm from bottom
        $this->SetY(-25);
        // Set font
        $this->SetFont('helvetica', 'B', 10);
        

        $tbl = <<<EOD
        <table cellspacing="0" cellpadding="5" border="0">
            <tr>
                <td width ="75%" align="center" style="line-height: 200%; vertical-align:middle; background-color:#00008B;color:#FFF;">
                    Jl. Raya Taktakan Ruko Golden Paradise No.7, Lontarbaru Serang, Banten<BR />
                    Web : www.ciptamasjaya.co.id - Email : info@ciptamasjaya.co.id 
                </td>
                <td width ="25%"  align="center" style="font-size:20px; line-height: 100%; vertical-align:middle; background-color:#FF0000; color:#FFF;">TAMASYA <BR />TOTAL SOLUTION FOR SAFETY</td>
            </tr>
        </table>
        EOD;

        $this->writeHTML($tbl, true, false, false, false, '');

    }

    protected function type()
    {
        return 'billing';
    }

    protected function file_path()
    {
        $filePath = 'my_billingpdf.php';
        $customPath = module_views_path('billings','themes/' . active_clients_theme() . '/views/billings/' . $filePath);
        $actualPath = module_views_path('billings','themes/' . active_clients_theme() . '/views/billings/billingpdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
