<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// --- Table for Product Categories ---
if (!$CI->db->table_exists(CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES)) {
    $sql = "CREATE TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";
    $CI->db->query($sql);
}
// No ALTER TABLE statements for categories for now, assuming structure is stable.

// --- Table for Products ---
if (!$CI->db->table_exists(CUSTOM_ESTIMATION_TABLE_PRODUCTS)) {
    $sql = "CREATE TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `category_id` INT(11) NULL,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `long_description` TEXT NULL,
        `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `unit` VARCHAR(50) NULL,
        `unit_type` VARCHAR(100) NULL, // For more detailed unit classification if needed
        `product_range` VARCHAR(255) NULL,
        `material` VARCHAR(255) NULL,
        `formula` VARCHAR(50) NULL, // e.g., 'sft', 'rft', 'nos'
        `dimensions_length` DECIMAL(10,2) NULL,
        `dimensions_width` DECIMAL(10,2) NULL,
        `dimensions_height` DECIMAL(10,2) NULL,
        `image` VARCHAR(255) NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`category_id`) REFERENCES `" . CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES . "`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";
    $CI->db->query($sql);
} else {
    // Add missing columns to existing products table
    // Ensure correct 'AFTER' clause if column order matters for existing tables
    $after_col = 'unit_price'; // Start after a known existing column
    if (!$CI->db->field_exists('unit', CUSTOM_ESTIMATION_TABLE_PRODUCTS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "` ADD `unit` VARCHAR(50) NULL AFTER `{$after_col}`;"); }
    $after_col = 'unit';
    if (!$CI->db->field_exists('unit_type', CUSTOM_ESTIMATION_TABLE_PRODUCTS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "` ADD `unit_type` VARCHAR(100) NULL AFTER `{$after_col}`;"); }
    $after_col = 'unit_type';
    if (!$CI->db->field_exists('product_range', CUSTOM_ESTIMATION_TABLE_PRODUCTS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "` ADD `product_range` VARCHAR(255) NULL AFTER `{$after_col}`;"); }
    $after_col = 'product_range';
    if (!$CI->db->field_exists('material', CUSTOM_ESTIMATION_TABLE_PRODUCTS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "` ADD `material` VARCHAR(255) NULL AFTER `{$after_col}`;"); }
    $after_col = 'material';
    if (!$CI->db->field_exists('formula', CUSTOM_ESTIMATION_TABLE_PRODUCTS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "` ADD `formula` VARCHAR(50) NULL AFTER `{$after_col}`;"); }
    $after_col = 'formula';
    if (!$CI->db->field_exists('dimensions_length', CUSTOM_ESTIMATION_TABLE_PRODUCTS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "` ADD `dimensions_length` DECIMAL(10,2) NULL AFTER `{$after_col}`;"); }
    $after_col = 'dimensions_length';
    if (!$CI->db->field_exists('dimensions_width', CUSTOM_ESTIMATION_TABLE_PRODUCTS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "` ADD `dimensions_width` DECIMAL(10,2) NULL AFTER `{$after_col}`;"); }
    $after_col = 'dimensions_width';
    if (!$CI->db->field_exists('dimensions_height', CUSTOM_ESTIMATION_TABLE_PRODUCTS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "` ADD `dimensions_height` DECIMAL(10,2) NULL AFTER `{$after_col}`;"); }
}

// --- Table for Estimates ---
if (!$CI->db->table_exists(CUSTOM_ESTIMATION_TABLE_ESTIMATES)) {
    $sql = "CREATE TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `subject` VARCHAR(255) NULL, // Subject field
        `lead_id` INT(11) NULL,
        `client_id` INT(11) NULL,
        `project_id` INT(11) NULL,
        `estimate_number` VARCHAR(50) NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'draft',
        `datecreated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `valid_until` DATE NULL,
        `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `discount_type` VARCHAR(20) DEFAULT 'percentage', // Added discount_type
        `total_discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `total_discount_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        `total_tax` DECIMAL(15,2) NOT NULL DEFAULT 0.00, // Assuming you might add tax later
        `total` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `notes` TEXT NULL,
        `terms_and_conditions` TEXT NULL,
        `admin_notes` TEXT NULL,
        `created_by` INT(11) NOT NULL,
        `approved_by_client_signature` TEXT NULL,
        `approved_by_client_name` VARCHAR(255) NULL,
        `approved_at` DATETIME NULL,
        `hash` VARCHAR(32) NULL,
        `pdf_template_slug` VARCHAR(100) NULL, // For associating with a PDF template
        PRIMARY KEY (`id`),
        INDEX `lead_id` (`lead_id`),
        INDEX `client_id` (`client_id`),
        INDEX `status` (`status`),
        INDEX `pdf_template_slug` (`pdf_template_slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";
    $CI->db->query($sql);
} else {
    // Add missing columns to existing estimates table
    $estimates_after_col = 'subject'; // Start after a known existing column
    if (!$CI->db->field_exists('lead_id', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `lead_id` INT(11) NULL AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'lead_id';
    if (!$CI->db->field_exists('client_id', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `client_id` INT(11) NULL AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'client_id';
    if (!$CI->db->field_exists('project_id', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `project_id` INT(11) NULL AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'project_id';
    if (!$CI->db->field_exists('estimate_number', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `estimate_number` VARCHAR(50) NULL AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'estimate_number';
    if (!$CI->db->field_exists('status', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `status` VARCHAR(50) NOT NULL DEFAULT 'draft' AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'status';
    // datecreated should exist if table exists from previous version
    $estimates_after_col = 'datecreated';
    if (!$CI->db->field_exists('valid_until', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `valid_until` DATE NULL AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'valid_until';
    if (!$CI->db->field_exists('subtotal', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'subtotal';
    if (!$CI->db->field_exists('discount_type', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `discount_type` VARCHAR(20) DEFAULT 'percentage' AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'discount_type';
    if (!$CI->db->field_exists('total_discount_amount', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `total_discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'total_discount_amount';
    if (!$CI->db->field_exists('total_discount_percentage', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `total_discount_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'total_discount_percentage';
    if (!$CI->db->field_exists('total_tax', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `total_tax` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'total_tax';
    if (!$CI->db->field_exists('total', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `total` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'total';
    // notes, terms_and_conditions, admin_notes, created_by should exist
    $estimates_after_col = 'admin_notes';
    if (!$CI->db->field_exists('created_by', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `created_by` INT(11) NOT NULL AFTER `{$estimates_after_col}`;");  } $estimates_after_col = 'created_by';
    if (!$CI->db->field_exists('approved_by_client_signature', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `approved_by_client_signature` TEXT NULL AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'approved_by_client_signature';
    if (!$CI->db->field_exists('approved_by_client_name', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `approved_by_client_name` VARCHAR(255) NULL AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'approved_by_client_name';
    if (!$CI->db->field_exists('approved_at', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `approved_at` DATETIME NULL AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'approved_at';
    if (!$CI->db->field_exists('hash', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `hash` VARCHAR(32) NULL AFTER `{$estimates_after_col}`;"); } $estimates_after_col = 'hash';
    if ($CI->db->field_exists('pdf_template_id', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` DROP COLUMN `pdf_template_id`;"); } // Drop old if exists
    if (!$CI->db->field_exists('pdf_template_slug', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD `pdf_template_slug` VARCHAR(100) NULL AFTER `{$estimates_after_col}`;"); if (!$CI->db->index_exists('pdf_template_slug', CUSTOM_ESTIMATION_TABLE_ESTIMATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "` ADD INDEX `pdf_template_slug` (`pdf_template_slug`);"); } }
}


// --- Table for Estimate Items ---
if (!$CI->db->table_exists(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)) {
    $sql = "CREATE TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `estimate_id` INT(11) NOT NULL,
        `product_id` INT(11) NULL,
        `description` TEXT NOT NULL,
        `long_description` TEXT NULL,
        `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
        `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `item_order` INT(11) DEFAULT 0,
        `discount_amount` DECIMAL(15,2) DEFAULT 0.00, // Per item discount amount (if needed, usually percentage is used)
        `discount_percentage` DECIMAL(5,2) DEFAULT 0.00, // Per item discount percentage
        `is_complimentary` BOOLEAN NOT NULL DEFAULT FALSE,
        `unit` VARCHAR(50) NULL,
        `formula` VARCHAR(50) NULL,
        `custom_dim_length` DECIMAL(10,2) NULL,
        `custom_dim_width` DECIMAL(10,2) NULL,
        `custom_dim_height` DECIMAL(10,2) NULL,
        `material` VARCHAR(255) NULL,
        `range` VARCHAR(255) NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`estimate_id`) REFERENCES `" . CUSTOM_ESTIMATION_TABLE_ESTIMATES . "`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";
    $CI->db->query($sql);
} else {
    // Add missing columns to existing estimate items table
    $after_item_col = 'estimate_id';
    if (!$CI->db->field_exists('product_id', CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . "` ADD `product_id` INT(11) NULL AFTER `{$after_item_col}`;");}
    // Assuming description, long_description, quantity, unit_price, item_order, discount_amount, discount_percentage, is_complimentary exist from previous version
    $after_item_col = 'is_complimentary'; // Start after a known existing column from previous version
    if (!$CI->db->field_exists('unit', CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . "` ADD `unit` VARCHAR(50) NULL AFTER `{$after_item_col}`;"); }
    $after_item_col = 'unit';
    if (!$CI->db->field_exists('formula', CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . "` ADD `formula` VARCHAR(50) NULL AFTER `{$after_item_col}`;"); }
    $after_item_col = 'formula';
    if (!$CI->db->field_exists('custom_dim_length', CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . "` ADD `custom_dim_length` DECIMAL(10,2) NULL AFTER `{$after_item_col}`;"); }
    $after_item_col = 'custom_dim_length';
    if (!$CI->db->field_exists('custom_dim_width', CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . "` ADD `custom_dim_width` DECIMAL(10,2) NULL AFTER `{$after_item_col}`;"); }
    $after_item_col = 'custom_dim_width';
    if (!$CI->db->field_exists('custom_dim_height', CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . "` ADD `custom_dim_height` DECIMAL(10,2) NULL AFTER `{$after_item_col}`;"); }
    $after_item_col = 'custom_dim_height';
    if (!$CI->db->field_exists('material', CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . "` ADD `material` VARCHAR(255) NULL AFTER `{$after_item_col}`;"); }
    $after_item_col = 'material';
    if (!$CI->db->field_exists('range', CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS . "` ADD `range` VARCHAR(255) NULL AFTER `{$after_item_col}`;"); }
}

// --- Table for Item Packages ---
if (!$CI->db->table_exists(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES)) {
    $sql = "CREATE TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";
    $CI->db->query($sql);
}

// --- Table for Item Package Items ---
if (!$CI->db->table_exists(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS)) {
    $sql = "CREATE TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `package_id` INT(11) NOT NULL,
        `product_id` INT(11) NOT NULL,
        `default_quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
        `default_description` TEXT NULL,
        `default_long_description` TEXT NULL,
        `default_unit_price` DECIMAL(15,2) NULL, -- Can be NULL to use product's current price
        `item_order` INT(11) DEFAULT 0,
        `is_complimentary` BOOLEAN NOT NULL DEFAULT FALSE,
        `unit` VARCHAR(50) NULL,
        `unit_type` VARCHAR(100) NULL, // From product, can be overridden
        `package_item_range` VARCHAR(255) NULL, // From product, can be overridden (note: field name slightly different from product's 'product_range' to distinguish)
        `material` VARCHAR(255) NULL, // From product, can be overridden
        `formula` VARCHAR(50) NULL, // From product, can be overridden
        `dimension_l` DECIMAL(10,2) NULL, // From product, can be overridden
        `dimension_w` DECIMAL(10,2) NULL, // From product, can be overridden
        `dimension_h` DECIMAL(10,2) NULL, // From product, can be overridden
        PRIMARY KEY (`id`),
        FOREIGN KEY (`package_id`) REFERENCES `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES . "`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `" . CUSTOM_ESTIMATION_TABLE_PRODUCTS . "`(`id`) ON DELETE CASCADE -- Cascade if product deleted, or SET NULL if package item should remain with no product link
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";
    $CI->db->query($sql);
} else {
    // Add missing columns to existing item package items table
    $after_pkg_item_col = 'is_complimentary'; // Start after a known existing column
    if (!$CI->db->field_exists('unit', CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . "` ADD `unit` VARCHAR(50) NULL AFTER `{$after_pkg_item_col}`;"); }
    $after_pkg_item_col = 'unit';
    if (!$CI->db->field_exists('unit_type', CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . "` ADD `unit_type` VARCHAR(100) NULL AFTER `{$after_pkg_item_col}`;"); }
    $after_pkg_item_col = 'unit_type';
    if (!$CI->db->field_exists('package_item_range', CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . "` ADD `package_item_range` VARCHAR(255) NULL AFTER `{$after_pkg_item_col}`;"); }
    $after_pkg_item_col = 'package_item_range';
    if (!$CI->db->field_exists('material', CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . "` ADD `material` VARCHAR(255) NULL AFTER `{$after_pkg_item_col}`;"); }
    $after_pkg_item_col = 'material';
    if (!$CI->db->field_exists('formula', CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . "` ADD `formula` VARCHAR(50) NULL AFTER `{$after_pkg_item_col}`;"); }
    $after_pkg_item_col = 'formula';
    if (!$CI->db->field_exists('dimension_l', CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . "` ADD `dimension_l` DECIMAL(10,2) NULL AFTER `{$after_pkg_item_col}`;"); }
    $after_pkg_item_col = 'dimension_l';
    if (!$CI->db->field_exists('dimension_w', CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . "` ADD `dimension_w` DECIMAL(10,2) NULL AFTER `{$after_pkg_item_col}`;"); }
    $after_pkg_item_col = 'dimension_w';
    if (!$CI->db->field_exists('dimension_h', CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . "` ADD `dimension_h` DECIMAL(10,2) NULL AFTER `{$after_pkg_item_col}`;"); }
}


// --- Table for PDF Templates ---
if (!$CI->db->table_exists(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)) {
    $sql = "CREATE TABLE `" . CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES . "` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `template_html` LONGTEXT NULL,
        `template_css` LONGTEXT NULL,
        `is_default` BOOLEAN NOT NULL DEFAULT FALSE,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ";";
    $CI->db->query($sql);
} else {
    // Add missing columns to existing PDF templates table
    $after_tpl_col = 'slug';
    if (!$CI->db->field_exists('template_html', CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES . "` ADD `template_html` LONGTEXT NULL AFTER `{$after_tpl_col}`;"); }
    $after_tpl_col = 'template_html';
    if (!$CI->db->field_exists('template_css', CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES . "` ADD `template_css` LONGTEXT NULL AFTER `{$after_tpl_col}`;"); }
    $after_tpl_col = 'template_css';
    if (!$CI->db->field_exists('is_default', CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES . "` ADD `is_default` BOOLEAN NOT NULL DEFAULT FALSE AFTER `{$after_tpl_col}`;"); }
    $after_tpl_col = 'is_default';
    if (!$CI->db->field_exists('created_at', CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES . "` ADD `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `{$after_tpl_col}`;"); }
    $after_tpl_col = 'created_at';
    if (!$CI->db->field_exists('updated_at', CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)) { $CI->db->query("ALTER TABLE `" . CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES . "` ADD `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP AFTER `{$after_tpl_col}`;"); }
  // ... (inside the 'else' block for CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES) ...
    // Check if the 'slug' unique key/index exists
    $table_name_pdf_templates = CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES;
    $key_name_pdf_slug = 'slug'; // This is the name we gave the unique key: UNIQUE KEY `slug` (`slug`)

    $query_show_index = $CI->db->query("SHOW INDEX FROM `" . $table_name_pdf_templates . "` WHERE Key_name = ?", [$key_name_pdf_slug]);
    $slug_index_exists = ($query_show_index->num_rows() > 0);

    if (!$slug_index_exists) {
        // Before adding a unique key, it's crucial to ensure no duplicate slugs currently exist.
        // If duplicates exist, adding a unique key will fail.
        // This check is important if the table was populated without the unique constraint initially.
        $duplicate_check_query = $CI->db->query(
            "SELECT `slug`, COUNT(*) as count 
             FROM `" . $table_name_pdf_templates . "` 
             GROUP BY `slug` 
             HAVING count > 1"
        );

        if ($duplicate_check_query->num_rows() == 0) {
            // No duplicates found, safe to add the unique key
            $CI->db->query("ALTER TABLE `" . $table_name_pdf_templates . "` ADD UNIQUE KEY `slug` (`slug`);");
        } else {
            // Duplicates exist. You'll need to decide how to handle this.
            // Options: log an error, attempt to make slugs unique (e.g., append -1, -2), or notify admin.
            // For now, we'll just skip adding the unique key if duplicates are present to prevent installation errors.
            // You might want to add a log message here for the admin.
            log_message('error', 'Custom Estimation Module: Could not add unique key on `slug` for table `' . $table_name_pdf_templates . '` because duplicate slug values exist. Please resolve manually.');
        }
    }
    // ...
}

// Add any default options for your module
add_option('custom_estimation_default_pdf_template_slug', ''); // Example: store the slug of the default PDF template
add_option('custom_estimation_option_example', 'default_value_if_not_exists'); // Your existing example

?>