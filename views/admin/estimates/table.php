<?php
// --- START AGGRESSIVE DEBUGGING ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
// echo "DEBUG: table.php (Manual Query Debug) START<br>\n";
// --- END AGGRESSIVE DEBUGGING ---

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Manually construct a very simple database query, bypassing data_tables_init()

$sTable = CUSTOM_ESTIMATION_TABLE_ESTIMATES;
$leadsTable = db_prefix() . 'leads';

// Simple query to get a few records
$CI->db->select([
    $sTable . '.id as estimate_id',
    $sTable . '.subject as estimate_subject',
    $sTable . '.lead_id',
    $leadsTable . '.name as estimate_lead_name', // Ensure this alias is unique if used elsewhere
    $sTable . '.status as estimate_status',
    $sTable . '.total as estimate_total',
    $sTable . '.datecreated as estimate_datecreated',
    $sTable . '.valid_until as estimate_valid_until',
]);
$CI->db->from($sTable);
$CI->db->join($leadsTable, $leadsTable . '.id = ' . $sTable . '.lead_id', 'left');
$CI->db->order_by($sTable . '.id', 'desc');
$CI->db->limit(10); // Get a small number of records for testing

$rResult = $CI->db->get()->result_array();
$db_error = $CI->db->error();

if ($db_error && isset($db_error['code']) && $db_error['code'] != 0) {
    // If there's a database error with our manual query, output it
    header('HTTP/1.1 500 Internal Server Error');
    echo "MANUAL DB QUERY ERROR:<br><pre>";
    print_r($db_error);
    echo "</pre>";
    die();
}

// echo "DEBUG: Manual query executed. Number of rows: " . count($rResult) . "<br><pre>";
// print_r($rResult);
// echo "</pre>";
// die("DEBUG: Stopped after manual query execution.");


// Manually construct the output array in the format DataTables expects
$output = [
    "draw"            => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
    "recordsTotal"    => count($rResult), // For this test, total and filtered are the same
    "recordsFiltered" => count($rResult),
    "aaData"          => [],
];

if (!class_exists('Estimates_model', false) && isset($CI->estimates_model)) {
    // Model might have been loaded by controller, or load it here if needed for statuses
} else if (!isset($CI->estimates_model)) {
    $CI->load->model('custom_estimation/estimates_model');
}
$estimate_statuses = isset($CI->estimates_model) ? $CI->estimates_model->get_estimate_statuses() : [];
if (!is_array($estimate_statuses)) $estimate_statuses = [];


foreach ($rResult as $aRow) {
    $row = []; // This array must match the number of <th> in manage.php

    // Column 0: Subject
    $subjectOutput = (isset($aRow['estimate_subject']) && isset($aRow['estimate_id']))
        ? '<a href="' . admin_url('custom_estimation/estimates/estimate/' . $aRow['estimate_id']) . '">' . $aRow['estimate_subject'] . '</a>'
        : 'N/A';
    $row[] = $subjectOutput;

    // Column 1: Lead
    $leadClientOutput = '-';
    if (isset($aRow['estimate_lead_id_for_display']) && isset($aRow['estimate_lead_name']) && !empty($aRow['estimate_lead_name'])) {
        $leadClientOutput = '<a href="' . admin_url('leads/index/' . $aRow['estimate_lead_id_for_display']) . '">' . $aRow['estimate_lead_name'] . '</a>';
    } else if (isset($aRow['estimate_lead_name']) && !empty($aRow['estimate_lead_name'])) { // Fallback if lead_id isn't in this specific select
         $leadClientOutput = $aRow['estimate_lead_name'];
    }
    $row[] = $leadClientOutput;


    // Column 2: Status
    $statusDisplayValue = isset($aRow['estimate_status']) ? $aRow['estimate_status'] : 'N/A';
    $statusOutput = ucfirst($statusDisplayValue);
    if (is_array($estimate_statuses)) {
        foreach ($estimate_statuses as $status_info) {
            if (isset($status_info['id']) && $statusDisplayValue == $status_info['id']) {
                $statusOutput = '<span class="label label-inline ' . (isset($status_info['color']) ? $status_info['color'] : 'label-default') . ' s-status estimate-status-' . $status_info['id'] . '">' . (isset($status_info['name']) ? $status_info['name'] : ucfirst($statusDisplayValue)) . '</span>';
                break;
            }
        }
    }
    $row[] = $statusOutput;
    
    // Column 3: Total
    $row[] = isset($aRow['estimate_total']) ? app_format_money($aRow['estimate_total'], get_base_currency()) : 'N/A';
    
    // Column 4: Date Created
    $row[] = isset($aRow['estimate_datecreated']) ? _dt($aRow['estimate_datecreated']) : 'N/A';
    
    // Column 5: Valid Until
    $row[] = isset($aRow['estimate_valid_until']) && $aRow['estimate_valid_until'] ? _d($aRow['estimate_valid_until']) : 'N/A';

    // Column 6: Options
    $options = '';
    if (isset($aRow['estimate_id'])) {
        if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit')) {
            $options .= icon_btn('custom_estimation/estimates/estimate/' . $aRow['estimate_id'], 'pencil-square-o', 'btn-default', ['title' => _l('edit')]);
        }
        $options .= icon_btn('custom_estimation/estimates/pdf/' . $aRow['estimate_id'], 'file-pdf-o', 'btn-default', ['title' => _l('download_pdf'), 'target' => '_blank']);
        if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete')) {
            $options .= icon_btn('custom_estimation/estimates/delete_estimate/' . $aRow['estimate_id'], 'remove', 'btn-danger _delete', ['title' => _l('delete')]);
        }
    }
    $row[] = $options;

    $output['aaData'][] = $row;
}

// Set content type to JSON and echo the output
header('Content-Type: application/json');
echo json_encode($output);
die(); // Ensure no other output interferes

/*
// Original data_tables_init() call - commented out for this test
$result = data_tables_init(
    $aColumns,
    $sIndexColumn, 
    $sTable,
    is_array($join) ? $join : [],
    is_array($where) ? $where : [],
    is_array($additionalSelect) ? $additionalSelect : [],
    '',  
    []   
);
// ... rest of original processing ...
*/
