<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Columns to be fetched from the database for DataTables.
$aColumns = [
    'name',       // PDF Template Name
    'is_default', // Is it the default template?
];

$sIndexColumn = 'id';
$sTable       = CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES;

$join = []; 

$additionalSelect = [
    'id as template_id', 
    // 'created_at', // If you want to display it
    // 'updated_at',
];

$where = []; 

$result = data_tables_init(
    $aColumns,
    $sIndexColumn,
    $sTable,
    is_array($join) ? $join : [],
    is_array($where) ? $where : [],
    is_array($additionalSelect) ? $additionalSelect : [],
    '',  // sGroupBy
    []   // searchAs 
);

$output  = $result['output']; 
$rResult = $result['rResult']; 

foreach ($rResult as $aRow) {
    $row = []; 

    // Column 0: Template Name
    $nameOutput = '<a href="' . admin_url('custom_estimation/pdf_templates/template/' . $aRow['template_id']) . '">' . $aRow['name'] . '</a>';
    $row[] = $nameOutput;

    // Column 1: Is Default
    $is_default_output = '<span class="label label-';
    if ($aRow['is_default'] == 1) {
        $is_default_output .= 'success">';
        $is_default_output .= _l('yes');
    } else {
        $is_default_output .= 'default">';
        $is_default_output .= _l('no');
        // Add a link to set as default if not already default and user has permission
        if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit') || has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_pdf_templates')) {
            $is_default_output .= ' <a href="' . admin_url('custom_estimation/pdf_templates/set_default/' . $aRow['template_id']) . '" class="text-success">' . _l('set_as_default') . '</a>';
        }
    }
    $is_default_output .= '</span>';
    $row[] = $is_default_output;

    // Column 2: Options (Edit, Delete)
    $options = '';
    if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit') || has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_pdf_templates')) {
        $options .= icon_btn('custom_estimation/pdf_templates/template/' . $aRow['template_id'], 'pencil-square-o', 'btn-default', ['title' => _l('edit')]);
    }
    // Prevent deleting the default template directly from the list for safety
    if ($aRow['is_default'] == 0 && (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete') || has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_pdf_templates'))) {
        $options .= icon_btn('custom_estimation/pdf_templates/delete_template/' . $aRow['template_id'], 'remove', 'btn-danger _delete', ['title' => _l('delete')]);
    }
    $row[] = $options;

    $output['aaData'][] = $row;
}