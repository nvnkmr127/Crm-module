<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'name',
    'description',
    // _l('options') is generated, not a DB column
];

$sIndexColumn = 'id';
$sTable       = CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES;

// Initialize $join as an empty array if no specific joins are needed for this table.
// This was the source of the "Undefined variable $join" warning and subsequent "implode(): null given" error.
$join         = []; 

// Example of how you might add a join if needed in the future:
// $join = [
//     'LEFT JOIN ' . db_prefix() . 'some_other_table ON ' . db_prefix() . 'some_other_table.category_id = ' . CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES . '.id',
// ];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES . '.id as category_id']); // Aliasing id for clarity

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    // Category Name - linked to the modal edit
    $categoryNameOutput = $aRow['name'];
    // If you want to make it a link that opens the modal (requires JS in manage.php to handle this if not already)
    // $categoryNameOutput = '<a href="#" onclick="edit_category_modal(' . $aRow['category_id'] . '); return false;">' . $aRow['name'] . '</a>';
    $row[] = $categoryNameOutput;

    // Category Description
    $row[] = $aRow['description'] ? $aRow['description'] : '-';

    // Options - Edit and Delete (triggers modal or direct action)
    $options = '';
    if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit')) {
        $options .= icon_btn('#', 'pencil-square-o', 'btn-default', [
            'onclick' => 'edit_category_modal(' . $aRow['category_id'] . '); return false;', // Matches your manage.php JS
            'title' => _l('edit')
        ]);
    }
    if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete')) {
        $options .= icon_btn('custom_estimation/categories/delete_category/' . $aRow['category_id'], 'remove', 'btn-danger _delete', ['title' => _l('delete')]);
    }
    $row[] = $options;

    $output['aaData'][] = $row;
}