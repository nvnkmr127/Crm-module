<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Ensure all debugging echos/var_dumps/die are commented out for normal operation
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$CI = &get_instance();

$sTable = CUSTOM_ESTIMATION_TABLE_PRODUCTS;
$categoriesTable = CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES;

// $aColumns: Define for sortable/searchable database columns.
// Match the number of UI columns from products/manage.php (6 columns: Image, Name, Category, Desc, Price, Options)
// For UI columns not intended for DB sorting (like Image or Options),
// provide a simple, valid column from the primary table (like the ID column) or a literal.
// The client-side DataTables config will mark them as non-sortable.
$aColumns = [
    0 => $sTable . '.id',               // UI Col 0 (Image) - Use 'id' as a placeholder DB column. Marked non-sortable on client.
    1 => $sTable . '.name',             // UI Col 1 (Name)
    2 => $categoriesTable . '.name',    // UI Col 2 (Category Name)
    3 => $sTable . '.description',      // UI Col 3 (Description)
    4 => $sTable . '.unit_price',       // UI Col 4 (Price)
    5 => $sTable . '.id'                // UI Col 5 (Options) - Use 'id' as a placeholder. Marked non-sortable on client.
];

$sIndexColumn = $sTable . '.id'; 

$join = [
    'LEFT JOIN ' . $categoriesTable . ' ON ' . $categoriesTable . '.id = ' . $sTable . '.category_id',
];

// $additionalSelect: Explicitly select ALL columns needed for display in $aRow, using distinct aliases.
$additionalSelect = [
    $sTable . '.id as product_id',
    $sTable . '.name as product_name_for_display', 
    $sTable . '.description as product_description_for_display', 
    $sTable . '.unit_price as product_unit_price_for_display', 
    $sTable . '.image as product_image_for_display', 
    $categoriesTable . '.name as category_name_for_display', 
];

$where = []; 

// --- DEBUG BEFORE data_tables_init ---
// echo "DEBUG: products/table.php - Before data_tables_init():<br>\n<pre>";
// echo "aColumns: "; print_r($aColumns);
// echo "sIndexColumn: "; print_r($sIndexColumn);
// echo "sTable: "; print_r($sTable);
// echo "join: "; print_r($join);
// echo "additionalSelect: "; print_r($additionalSelect);
// echo "_POST data: "; print_r($_POST);
// echo "</pre>\n";
// die("DEBUG: Stopped before data_tables_init call in products/table.php.");
// --- END DEBUG ---

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

// --- DEBUG AFTER data_tables_init ---
// echo "DEBUG: products/table.php - data_tables_init() COMPLETED.<br>\n";
// echo "DEBUG: Result from data_tables_init():<br>\n<pre>";
// var_dump($result); 
// echo "</pre><br>\n";
// if (!$result || !isset($result['rResult']) || !isset($result['output'])) {
//     echo "DEBUG: ERROR - data_tables_init() did not return expected structure.<br>\n";
//     die("DEBUG: STOPPED after data_tables_init() due to unexpected result structure.");
// }
// die("DEBUG: STOPPED after data_tables_init() in products/table.php.");
// --- END DEBUG ---

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = []; // This array must match the number of <th> in products/manage.php (6 columns)

    // Column 0: Image
    $imageOutput = '-';
    if (!empty($aRow['product_image_for_display'])) {
        $image_url = base_url('uploads/custom_estimation_module/products/' . $aRow['product_image_for_display']);
        $imageOutput = '<img src="' . $image_url . '" class="img img-responsive img-thumbnail" style="max-width: 50px; max-height: 50px;" alt="' . htmlspecialchars($aRow['product_name_for_display']) . '">';
    }
    $row[] = $imageOutput;

    // Column 1: Product Name
    $productNameOutput = '<a href="' . admin_url('custom_estimation/products/product/' . $aRow['product_id']) . '">' . $aRow['product_name_for_display'] . '</a>';
    $row[] = $productNameOutput;

    // Column 2: Category Name
    $row[] = $aRow['category_name_for_display'] ? $aRow['category_name_for_display'] : '-';

    // Column 3: Product Description
    $description = $aRow['product_description_for_display'];
    if (strlen($description) > 100) {
        $description = substr(strip_tags($description), 0, 100) . '...';
    }
    $row[] = $description ? $description : '-';

    // Column 4: Unit Price
    $row[] = app_format_money($aRow['product_unit_price_for_display'], get_base_currency());

    // Column 5: Options
    $options = '';
    if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit')) {
        $options .= icon_btn('custom_estimation/products/product/' . $aRow['product_id'], 'pencil-square-o', 'btn-default', ['title' => _l('edit')]);
    }
    if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete')) {
        $options .= icon_btn('custom_estimation/products/delete_product/' . $aRow['product_id'], 'remove', 'btn-danger _delete', ['title' => _l('delete')]);
    }
    $row[] = $options;

    $output['aaData'][] = $row;
}
