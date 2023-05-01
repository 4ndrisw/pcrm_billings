<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrency = get_base_currency();

$aColumns = [
    db_prefix() . 'billings.id',
    'subject',
    'billing_to',
    'total',
    'total_tax',
    'date',
    'datecreated',
    'status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'billings';

$where  = [];
$filter = [];

if ($this->ci->input->post('leads_related')) {
    array_push($filter, 'OR rel_type="lead"');
}
if ($this->ci->input->post('customers_related')) {
    array_push($filter, 'OR rel_type="customer"');
}
if ($this->ci->input->post('expired')) {
    array_push($filter, 'OR open_till IS NOT NULL AND open_till <"' . date('Y-m-d') . '" AND status NOT IN(2,3)');
}

$statuses  = $this->ci->billings_model->get_statuses();
$statusIds = [];

foreach ($statuses as $status) {
    if ($this->ci->input->post('billings_' . $status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND status IN (' . implode(', ', $statusIds) . ')');
}

$agents    = $this->ci->billings_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND assigned IN (' . implode(', ', $agentsIds) . ')');
}

$years      = $this->ci->billings_model->get_billings_years();
$yearsArray = [];
foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($yearsArray, $year['year']);
    }
}
if (count($yearsArray) > 0) {
    array_push($filter, 'AND YEAR(date) IN (' . implode(', ', $yearsArray) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('billings', '', 'view')) {
    array_push($where, 'AND ' . get_billings_sql_where_staff(get_staff_user_id()));
}

$join          = [];
$custom_fields = get_table_custom_fields('billing');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'billings.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$aColumns = hooks()->apply_filters('billings_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'currency',
    'clientid',
    'currency',
    'invoice_id',
    'hash',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    //$numberOutput = '<a href="' . admin_url('billings/list_billings/' . $aRow[db_prefix() . 'billings.id']. '#' . $aRow[db_prefix() . 'billings.id']) . '" onclick="init_billing(' . $aRow[db_prefix() . 'billings.id'] . '); return false;">' . format_billing_number($aRow[db_prefix() . 'billings.id']) . '</a>';
    //$numberOutput = '<a href="' . admin_url('billings#' . $aRow[db_prefix() . 'billings.id']) . '" target="_blank">' . format_billing_number($aRow[db_prefix() . 'billings.id']) . ' AA</a>';
    //$numberOutput = '<a href="' . admin_url('billings/list_billings/' . $aRow[db_prefix() . 'billings.id']. '#' . $aRow[db_prefix() . 'billings.id']) . '" target="_blank">' . format_billing_number($aRow[db_prefix() . 'billings.id']) . '</a>';
    //$numberOutput = '<a href="' . admin_url('billings/list_billings/' . $aRow[db_prefix() . 'billings.id']. '#' . $aRow[db_prefix() . 'billings.id']) . '">' . format_billing_number($aRow[db_prefix() . 'billings.id']) . '</a>';



    // If is from client area table
    $numberOutput = '<a href="' . admin_url('billings/list_billings/' . $aRow[db_prefix() . 'billings.id']. '#' . $aRow[db_prefix() . 'billings.id']) . '" onclick="init_billing(' . $aRow[db_prefix() . 'billings.id'] . '); return false;">' . format_billing_number($aRow[db_prefix() . 'billings.id']) . '</a>';

    $numberOutput .= '<div class="row-options">';

    $numberOutput .= '<a href="' . site_url('billings/show/' . $aRow[db_prefix() . 'billings.id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('billings', '', 'edit')) {
        $numberOutput .= ' | <a href="' . admin_url('billings/billing/' . $aRow[db_prefix() . 'billings.id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = '<a href="' . admin_url('billings/list_billings/' . $aRow[db_prefix() . 'billings.id']) . '" onclick="init_billing(' . $aRow[db_prefix() . 'billings.id'] . '); return false;">' . $aRow['subject'] . ' bb</a>';
    
    $toOutput = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank" data-toggle="tooltip" data-title="' . _l('client') . '">' . get_company_name($aRow['clientid']) . '</a>';
    $row[] = $toOutput;

    $amount = app_format_money($aRow['total'], ($aRow['currency'] != 0 ? get_currency($aRow['currency']) : $baseCurrency));

    if ($aRow['invoice_id']) {
        $amount .= '<br /> <span class="hide"> - </span><span class="text-success">' . _l('billing_invoiced') . '</span>';
    }

    $row[] = $amount;

    $row[] = app_format_money($aRow['total_tax'], $aRow['currency']);

    $row[] = _d($aRow['date']);


    $row[] = _d($aRow['datecreated']);

            $span = '';
                //if (!$locked) {
                    $span .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
                    $span .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow[db_prefix() . 'billings.id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $span .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
                    $span .= '</a>';

                    $span .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow[db_prefix() . 'billings.id'] . '">';
                    foreach ($statuses as $billingChangeStatus) {
                        if ($aRow['status'] != $billingChangeStatus) {
                            $span .= '<li>
                          <a href="#" onclick="billing_mark_as(' . $billingChangeStatus . ',' . $aRow[db_prefix() . 'billings.id'] . '); return false;">
                             ' . format_billing_status($billingChangeStatus) . '
                          </a>
                       </li>';
                        }
                    }
                    $span .= '</ul>';
                    $span .= '</div>';
                //}
                $span .= '</span>';

            $outputStatus = '<span class="label label-danger inline-block">' . _l('billing_status_draft') . $span;

            if ($aRow['status'] == 1) {
                $outputStatus = '<span class="label label-default inline-block">' . _l('billing_status_draft') . $span;
            } elseif ($aRow['status'] == 2) {
                $outputStatus = '<span class="label label-danger inline-block">' . _l('billing_status_declined') . $span;
            } elseif ($aRow['status'] == 3) {
                $outputStatus = '<span class="label label-success inline-block">' . _l('billing_status_accepted') . $span;
            } elseif ($aRow['status'] == 4) {
                $outputStatus = '<span class="label label-info inline-block">' . _l('billing_status_sent') . $span;
            } elseif ($aRow['status'] == 5) {
                $outputStatus = '<span class="label label-warning inline-block">' . _l('billing_status_expired') . $span;
            } elseif ($aRow['status'] == 6) {
                $outputStatus = '<span class="label label-success inline-block">' . _l('billing_status_approved') . '</span>';
            }

            $_data = $outputStatus;

    $row[] = $outputStatus;
    //$row[] = format_billing_status($aRow['status']);

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('billings_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
