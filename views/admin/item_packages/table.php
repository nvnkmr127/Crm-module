<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Define the columns that will be fetched from the database for the DataTables.
// These correspond to the columns in your 'tblcustom_item_packages' table.
$aColumns = [
    'name',         // The name of the item package
    'description',  // The description of the item package
    // If you add more displayable columns to your 'tblcustom_item_packages' table
    // and want them in the DataTables, add them here.
];

// The primary key column in your 'tblcustom_item_packages' table.
$sIndexColumn = 'id';
// The name of your database table for item packages.
// Ensure CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES is defined in your main module file.
$sTable       = CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES;

// No joins are needed for this simple listing of packages.
// If you were to display, for example, a count of items within each package,
// you might need a JOIN or a subquery here.
$join         = [];

// Additional columns to select, like the ID for creating links.
// It's good practice to alias the ID column for clarity, especially if joining tables later.
$additionalSelect = [
    CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES . '.id as package_id',
];

// Use Perfex CRM's helper function to initialize and process DataTables server-side.
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], $additionalSelect);

$output  = $result['output']; // The output array to be sent back to DataTables.
$rResult = $result['rResult']; // The array of fetched database rows.

// Loop through each fetched row to format it for display in the DataTables.
foreach ($rResult as $aRow) {
    $row = []; // Initialize an array for the current row's data.

    // Format the 'Package Name' column.
    // Make the name a link to the edit page for this package.
    $packageNameOutput = '<a href="' . admin_url('custom_estimation/item_packages/package/' . $aRow['package_id']) . '">' . $aRow['name'] . '</a>';
    $row[] = $packageNameOutput;

    // Format the 'Package Description' column.
    // Display the description or a dash if it's empty.
    // You could also truncate long descriptions here if needed.
    $descriptionOutput = $aRow['description'] ? $aRow['description'] : '-';
    // Example of truncating:
    // $descriptionOutput = strlen($aRow['description']) > 100 ? substr(strip_tags($aRow['description']), 0, 100) . '...' : ($aRow['description'] ? $aRow['description'] : '-');
    $row[] = $descriptionOutput;

    // Format the 'Options' column with action buttons (Edit, Delete).
    $options = '';
    // Check if the user has permission to edit (either 'manage_packages' or general 'edit' for the module).
    if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_packages') || has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit')) {
        $options .= icon_btn('custom_estimation/item_packages/package/' . $aRow['package_id'], 'pencil-square-o', 'btn-default', ['title' => _l('edit')]);
    }
    // Check if the user has permission to delete.
    if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_packages') || has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete')) {
        $options .= icon_btn('custom_estimation/item_packages/delete_package/' . $aRow['package_id'], 'remove', 'btn-danger _delete', ['title' => _l('delete')]);
    }
    $row[] = $options;

    // Add the fully formatted row to the output array.
    $output['aaData'][] = $row;
}

// The 'data_tables_init' function handles sending the JSON response,
// so no explicit 'echo json_encode($output);' is needed here.

