<?php
defined('BASEPATH') or exit('No direct script access allowed');

# Module Details
$lang['custom_estimation_module_name'] = 'Custom Estimations';

# Menu
$lang['custom_estimation_menu_title'] = 'Custom Estimations';
$lang['custom_estimation_submenu_estimates'] = 'Manage Estimates';
$lang['custom_estimation_submenu_products'] = 'Products';
$lang['custom_estimation_submenu_categories'] = 'Product Categories';
$lang['custom_estimation_submenu_item_packages'] = 'Item Packages';
$lang['custom_estimation_submenu_pdf_templates'] = 'PDF Templates'; 
$lang['custom_estimation_submenu_reports'] = 'Reports';
$lang['custom_estimation_submenu_settings'] = 'Settings';

# Permissions
$lang['custom_estimation_permission_edit_product_values'] = 'Edit Product Values';
$lang['custom_estimation_permission_approve'] = 'Approve Estimates';
$lang['custom_estimation_permission_manage_packages'] = 'Manage Item Packages';
$lang['custom_estimation_permission_manage_pdf_templates'] = 'Manage PDF Templates'; 

# General & Estimates
$lang['custom_estimate'] = 'Custom Estimate';
$lang['custom_estimates'] = 'Custom Estimates';
$lang['new_custom_estimate'] = 'New Custom Estimate';
$lang['edit_custom_estimate'] = 'Edit Custom Estimate';
$lang['save_custom_estimate'] = 'Save Custom Estimate';
$lang['custom_estimate_lead'] = 'Lead';
$lang['custom_estimate_client'] = 'Client';
$lang['custom_estimate_project'] = 'Project';
$lang['custom_estimate_date'] = 'Date';
$lang['custom_estimate_valid_until'] = 'Valid Until';
$lang['custom_estimate_status'] = 'Status';
$lang['custom_estimate_created_by'] = 'Created By';
$lang['custom_estimate_notes'] = 'Notes';
$lang['custom_estimate_terms_and_conditions'] = 'Terms & Conditions';
$lang['view_estimate_url'] = 'View Estimate';
$lang['custom_estimate_public_view'] = 'Public View';
$lang['custom_estimate_online_view_title'] = 'Estimate Details'; 
$lang['custom_estimate_items_heading'] = 'Estimate Items';
$lang['no_items_in_custom_estimate'] = 'There are no items in this custom estimate.';
$lang['add_item_select_product_label'] = 'Select Product to Add';
$lang['download_pdf'] = 'Download PDF'; 

$lang['custom_estimate_added_successfully'] = 'Custom estimate added successfully.';
$lang['custom_estimate_add_fail'] = 'Failed to add custom estimate.';
$lang['custom_estimate_updated_successfully'] = 'Custom estimate updated successfully.';
$lang['custom_estimate_update_fail'] = 'Failed to update custom estimate.';
$lang['custom_estimate_deleted_successfully'] = 'Custom estimate deleted successfully.';
$lang['custom_estimate_delete_fail'] = 'Failed to delete custom estimate.';
$lang['custom_estimate_not_found'] = 'Custom estimate not found.';
$lang['invalid_estimate_hash'] = 'Invalid estimate link.'; 
$lang['custom_estimate_template_not_found'] = 'Estimate template not found or not applicable.';

# Lead Profile Tab
$lang['lead_profile_custom_estimates_tab'] = 'Custom Estimates'; 
$lang['no_estimates_found'] = 'No custom estimates found.'; // Corrected for general use
$lang['no_custom_estimates_found_for_this_lead'] = 'No custom estimates found for this lead.'; // Kept for lead-specific context
$lang['lead_info_not_available'] = 'Lead information not available.'; 
$lang['duplicate_estimate_tip'] = 'Duplicate Estimate';


# Estimate Item Packages / Templates
$lang['select_item_package_label'] = 'Select Item Package';
$lang['load_package_items_button'] = 'Load Package Items';
$lang['package_items_loaded_info'] = 'Items from the selected package have been added. You can now adjust them as needed.';
$lang['new_item_package'] = 'New Item Package';
$lang['edit_item_package'] = 'Edit Item Package';
$lang['item_package_name'] = 'Package Name';
$lang['item_package_description'] = 'Package Description';
$lang['item_package_added_successfully'] = 'Item package added successfully.';
$lang['item_package_add_fail'] = 'Failed to add item package.';
$lang['item_package_updated_successfully'] = 'Item package updated successfully.';
$lang['item_package_update_fail'] = 'Failed to update item package.';
$lang['item_package_deleted_successfully'] = 'Item package deleted successfully.';
$lang['item_package_delete_fail'] = 'Failed to delete item package.';
$lang['item_package_not_found'] = 'Item package not found.';
$lang['confirm_load_package'] = 'Are you sure you want to load items from this package? Existing items will not be removed, but new items will be added.';
$lang['package_items_heading'] = 'Package Items'; 
$lang['package_item_default_description'] = 'Default Description'; 
$lang['package_item_default_quantity'] = 'Default Quantity'; 
$lang['package_item_default_unit_price'] = 'Default Unit Price'; 
$lang['leave_blank_for_product_price'] = 'Leave blank to use current product price';
$lang['no_items_in_package'] = 'No items have been added to this package yet.';
$lang['please_select_a_product'] = 'Please select a product.';
$lang['please_enter_valid_quantity'] = 'Please enter a valid quantity.';
$lang['product_already_in_package'] = 'This product is already in the package.';
$lang['package_item_default_description_ph'] = 'Enter default description for this item in the package';
$lang['please_select_an_item_package'] = 'Please select an item package first.';
$lang['package_item_unit'] = 'Unit (e.g., pcs, sqm, rft)';
$lang['package_item_range'] = 'Range';
$lang['package_item_material'] = 'Material';
$lang['package_item_formula'] = 'Formula';
$lang['package_item_formula_sft'] = 'SFT (Area)';
$lang['package_item_formula_rft'] = 'RFT (Running Ft)';
$lang['package_item_formula_nos'] = 'NOS (Numbers)';
$lang['package_item_dimensions'] = 'Dimensions (L x W x H)'; 
$lang['package_item_length'] = 'Length (ft)'; 
$lang['package_item_width'] = 'Width (ft)'; 
$lang['package_item_height'] = 'Height (ft)'; 

# PDF Templates
$lang['pdf_template_name'] = 'Template Name';
$lang['pdf_template_content'] = 'Template Content (HTML/JSON)';
$lang['pdf_template_is_default'] = 'Is Default Template?';
$lang['new_pdf_template'] = 'New PDF Template';
$lang['edit_pdf_template'] = 'Edit PDF Template';
$lang['pdf_template_added_successfully'] = 'PDF template added successfully.';
$lang['pdf_template_add_fail'] = 'Failed to add PDF template.';
$lang['pdf_template_updated_successfully'] = 'PDF template updated successfully.';
$lang['pdf_template_update_fail'] = 'Failed to update PDF template.';
$lang['pdf_template_deleted_successfully'] = 'PDF template deleted successfully.';
$lang['pdf_template_delete_fail'] = 'Failed to delete PDF template.';
$lang['pdf_template_not_found'] = 'PDF template not found.';
$lang['pdf_template_set_as_default_successfully'] = 'PDF template set as default successfully.';
$lang['pdf_template_set_as_default_fail'] = 'Failed to set PDF template as default.';
$lang['no_pdf_templates_found'] = 'No PDF templates found.';
$lang['pdf_template_editor_heading'] = 'PDF Template Editor';
$lang['pdf_template_info'] = 'Define the HTML structure for your PDF. You can use placeholders like {estimate_total}, {company_name}, {client_name}, etc. Item details can be looped through using ... {item_description} ... .';
$lang['pdf_template_content_placeholder'] = 'Enter your HTML template here. Example: <h1>Estimate: {estimate_number}</h1>...';
$lang['pdf_template_slug_info'] = 'Unique identifier for the template (lowercase, no spaces). Used in code. Will be auto-generated from name if left blank.';
$lang['pdf_template_html_content'] = 'Template HTML Content';
$lang['pdf_template_css_content'] = 'Template Custom CSS';
$lang['pdf_template_placeholders_info'] = 'Available placeholders: {estimate_number}, {client_name}, {company_name}, {estimate_total}, etc. For items, use: &lt;!-- START_ITEMS_LOOP --&gt; ... {item_description} ... &lt;!-- END_ITEMS_LOOP --&gt;';
$lang['insert_placeholder'] = 'Insert Placeholder';
$lang['live_preview'] = 'Live Preview';
$lang['note_only_one_template_can_be_default'] = 'Note: Only one template can be set as the default.';
$lang['cannot_delete_default_pdf_template'] = 'Cannot delete the default PDF template. Set another template as default first.';
$lang['set_as_default'] = 'Set as Default';


# Estimate Statuses
$lang['estimate_status_draft'] = 'Draft';
$lang['estimate_status_sent'] = 'Sent';
$lang['estimate_status_approved'] = 'Approved';
$lang['estimate_status_declined'] = 'Declined';
$lang['estimate_status_expired'] = 'Expired';

# Products & Categories
$lang['custom_product_name'] = 'Product Name';
$lang['custom_product_category'] = 'Category';
$lang['custom_product_category_lowercase'] = 'category';
$lang['custom_product_description'] = 'Description';
$lang['custom_product_long_description'] = 'Long Description';
$lang['custom_product_unit_price'] = 'Unit Price';
$lang['custom_product_unit'] = 'Unit (e.g., pcs, sqm, rft)'; 
$lang['custom_product_unit_type'] = 'Unit Type'; 
$lang['custom_product_range'] = 'Range'; 
$lang['custom_product_material'] = 'Material'; 
$lang['custom_product_formula'] = 'Calculation Formula'; 
$lang['custom_product_formula_sft'] = 'SFT (Area - L x W)';
$lang['custom_product_formula_rft'] = 'RFT (Running Feet - L)';
$lang['custom_product_formula_nos'] = 'NOS (Numbers/Pieces)';
$lang['custom_product_dimensions_ft'] = 'Dimensions (ft)';
$lang['custom_product_length_ft'] = 'Length (ft)';
$lang['custom_product_width_ft'] = 'Width (ft)';
$lang['custom_product_height_ft'] = 'Height (ft)';
$lang['custom_product_image'] = 'Product Image';
$lang['new_product'] = 'New Product';
$lang['edit_product'] = 'Edit Product';
$lang['custom_product_added_successfully'] = 'Product added successfully.';
$lang['custom_product_add_fail'] = 'Failed to add product.';
$lang['custom_product_updated_successfully'] = 'Product updated successfully.';
$lang['custom_product_update_fail'] = 'Failed to update product.';
$lang['custom_product_deleted_successfully'] = 'Product deleted successfully.';
$lang['custom_product_delete_fail'] = 'Failed to delete product.';
$lang['custom_product_not_found'] = 'Product not found.';
$lang['custom_product_used_in_estimates_cannot_delete'] = 'This product is used in one or more estimates and cannot be deleted.';
$lang['remove_image'] = 'Remove Current Image';


$lang['new_category'] = 'New Category';
$lang['edit_category'] = 'Edit Category';
$lang['category_name'] = 'Category Name';
$lang['category_name_required'] = 'Category name is required.';
$lang['custom_category_added_successfully'] = 'Category added successfully.';
$lang['custom_category_add_fail'] = 'Failed to add category.';
$lang['custom_category_updated_successfully'] = 'Category updated successfully.';
$lang['custom_category_update_fail'] = 'Failed to update category.';
$lang['custom_category_deleted_successfully'] = 'Category deleted successfully.';
$lang['custom_category_delete_fail'] = 'Failed to delete category. Make sure it is not used by any products.';
$lang['custom_category_not_found'] = 'Category not found.';
$lang['confirm_delete_category'] = 'Are you sure you want to delete this category?';
$lang['no_categories_found'] = 'No product categories found.';
$lang['add_new_category_button'] = 'Add New Category';

# Estimate Items Table Headings
$lang['estimate_table_item_heading'] = 'Item';
$lang['estimate_table_item_description'] = 'Description';
$lang['estimate_table_quantity_heading'] = 'Qty';
$lang['estimate_table_rate_heading'] = 'Rate';
$lang['estimate_table_tax_heading'] = 'Tax';
$lang['estimate_table_amount_heading'] = 'Amount';
$lang['estimate_table_discount_heading'] = 'Discount';


# Estimate Items
$lang['complimentary'] = 'Complimentary';
$lang['add_item'] = 'Add Item';
$lang['remove_item'] = 'Remove Item';

# Totals & Discounts
$lang['estimate_subtotal'] = 'Subtotal';
$lang['estimate_discount'] = 'Discount';
$lang['estimate_total_discount_amount'] = 'Discount Amount';
$lang['estimate_total_discount_percentage'] = 'Discount Percentage';
$lang['estimate_total'] = 'Total';
$lang['apply_discount'] = 'Apply Discount';
$lang['discount_type_fixed'] = 'Fixed Amount'; 
$lang['discount_type_percentage'] = 'Percentage';
$lang['discount_fixed_amount'] = 'Fixed Amount';


# Approval & Signature
$lang['estimate_approve_sign'] = 'Approve & Sign';
$lang['estimate_signature_clear'] = 'Clear Signature';
$lang['estimate_signed_by'] = 'Signed By';
$lang['estimate_signed_date'] = 'Signed Date';
$lang['estimate_mark_as_approved'] = 'Mark as Approved';

# Sharing
$lang['share_estimate_email'] = 'Share via Email';
$lang['share_estimate_whatsapp'] = 'Share via WhatsApp';

# Reports
$lang['estimation_reports_dashboard_title'] = 'Estimation Reports';
$lang['dashboard'] = 'Dashboard'; 
$lang['reports'] = 'Reports'; 
$lang['total_estimates'] = 'Total Estimates';
$lang['approved_estimates'] = 'Approved Estimates';
$lang['pending_estimates'] = 'Pending Estimates';
$lang['declined_estimates'] = 'Declined Estimates';
$lang['total_value'] = 'Total Value';
$lang['view_all_custom_estimates'] = 'View All Custom Estimates';


$lang['custom_estimation_setting_option_1'] = 'Example Setting 1';
$lang['wait_text'] = 'Please wait...';
$lang['dropdown_non_selected_tex'] = 'Nothing selected';

$lang['custom_estimates_for_lead_deleted_successfully'] = 'Successfully deleted %s custom estimates for the lead.';
$lang['failed_to_delete_all_custom_estimates_for_lead'] = 'Failed to delete all custom estimates for the lead.';
$lang['no_custom_estimates_found_for_this_lead_to_delete'] = 'No custom estimates found for this lead to delete.';
$lang['invalid_lead_id'] = 'Invalid Lead ID provided.';


?>
