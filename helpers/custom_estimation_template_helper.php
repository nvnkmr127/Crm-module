<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Custom Estimation Template Helper
 */

// --- Basic Fallback Formatting Functions (if core helpers fail to load) ---
if (!function_exists('local_format_quantity')) {
    function local_format_quantity($qty) {
        return number_format((float)$qty, 2, '.', '');
    }
}
if (!function_exists('local_app_format_money')) {
    function local_app_format_money($amount, $currency_symbol = '$') {
        $symbol_to_use = '$';
        if (is_object($currency_symbol) && isset($currency_symbol->symbol)) {
            $symbol_to_use = $currency_symbol->symbol;
        } elseif (is_string($currency_symbol) && !empty($currency_symbol)) {
            $symbol_to_use = $currency_symbol;
        }
        return $symbol_to_use . number_format((float)$amount, 2, '.', ',');
    }
}
// --- End Basic Fallback ---


if (!function_exists('parse_custom_estimate_template')) {
    /**
     * Parses the estimate template HTML and CSS with estimate data.
     *
     * @param string $template_html The HTML content of the template.
     * @param string $template_css The CSS content of the template.
     * @param object $estimate The estimate data object.
     * @param array $additional_data Additional data to pass for parsing.
     * @return string The parsed HTML content.
     */
    function parse_custom_estimate_template($template_html, $template_css, $estimate, $additional_data = [])
    {
        $CI = &get_instance();
        $base_currency = null;

        // --- Load Core Helpers & Check Functions ---
        $helpers_to_load = ['format', 'date', 'currency', 'url']; // 'html' could be added if needed
        foreach($helpers_to_load as $helper_name){
            if (!is_loaded($helper_name.'_helper')) { // Check if helper is already loaded
                 @$CI->load->helper($helper_name);
            }
        }

        // Define function aliases that point to core functions or local fallbacks
        $_format_quantity_fn = function_exists('format_quantity') ? 'format_quantity' : 'local_format_quantity';
        $_app_format_money_fn = function_exists('app_format_money') ? 'app_format_money' : 'local_app_format_money';
        $_d_fn = function_exists('_d') ? '_d' : function($date){ if(empty($date) || $date == '0000-00-00') return ''; return date('Y-m-d', strtotime($date)); };
        $_dt_fn = function_exists('_dt') ? '_dt' : function($datetime){ if(empty($datetime) || $datetime == '0000-00-00 00:00:00') return ''; return date('Y-m-d H:i:s', strtotime($datetime)); };
        $_l_fn = function_exists('_l') ? '_l' : function($s, ...$args){ $s = ucfirst(str_replace('_',' ',$s)); if(!empty($args)) $s = vsprintf($s, $args); return $s; };
        $_site_url_fn = function_exists('site_url') ? 'site_url' : function($uri = ''){ if(function_exists('base_url')) return base_url($uri); return $uri;};
        $_get_option_fn = function_exists('get_option') ? 'get_option' : function($name){ return "Option:{$name}"; }; // Basic fallback
        $_pdf_logo_url_fn = function_exists('pdf_logo_url') ? 'pdf_logo_url' : function(){ return ""; }; // Basic fallback
        $_get_country_fn = function_exists('get_country') ? 'get_country' : function($id){ if($id) return (object)['short_name' => "CountryID:{$id}"]; return null; };


        if (!function_exists('get_base_currency') || !($base_currency = get_base_currency())) { // Check if function exists and returns valid object
            $base_currency = (object)['symbol' => '$', 'name' => 'USD', 'id' => 0]; // Add id for consistency if needed elsewhere
        }
        if (is_null($base_currency) || !is_object($base_currency) || !isset($base_currency->symbol)) {
             $base_currency = (object)['symbol' => '$', 'name' => 'USD', 'id' => 0];
        }

        $data_for_parsing = [];
        $data_for_parsing['estimate'] = $estimate; // Raw estimate object
        $data_for_parsing['estimate_id_raw'] = $estimate->id ?? '';
        $data_for_parsing['estimate_id'] = isset($estimate->id) && function_exists('format_custom_estimate_number') ? format_custom_estimate_number($estimate->id) : ($estimate->id ?? '');
        $data_for_parsing['estimate_number'] = isset($estimate->estimate_number) && !empty($estimate->estimate_number) ? htmlspecialchars($estimate->estimate_number) : $data_for_parsing['estimate_id'];
        $data_for_parsing['estimate_title'] = $_l_fn('custom_estimate') . ' #' . $data_for_parsing['estimate_number'];
        
        $status_info = isset($estimate->status) && function_exists('get_custom_estimate_status_by_id') ? get_custom_estimate_status_by_id($estimate->status) : null;
        $data_for_parsing['estimate_status_name'] = $status_info ? htmlspecialchars($status_info['name']) : (isset($estimate->status) ? ucfirst(htmlspecialchars($estimate->status)) : '');
        $data_for_parsing['estimate_status_slug'] = isset($estimate->status) ? htmlspecialchars($estimate->status) : 'unknown';

        $data_for_parsing['estimate_datecreated_short'] = isset($estimate->datecreated) ? $_d_fn($estimate->datecreated) : '';
        $data_for_parsing['estimate_datecreated_long'] = isset($estimate->datecreated) ? $_dt_fn($estimate->datecreated) : '';
        $data_for_parsing['estimate_valid_until'] = isset($estimate->valid_until) && !empty($estimate->valid_until) ? $_d_fn($estimate->valid_until) : '-';
        
        $data_for_parsing['estimate_subtotal'] = isset($estimate->subtotal) ? $_app_format_money_fn($estimate->subtotal, $base_currency) : $_app_format_money_fn(0, $base_currency);

        $discount_value = 0;
        $discount_label_suffix = '';
        if(isset($estimate->total_discount_percentage) && $estimate->total_discount_percentage > 0 && isset($estimate->subtotal) && $estimate->subtotal > 0){
             $discount_value = ($estimate->subtotal * $estimate->total_discount_percentage) / 100;
             $discount_label_suffix = ' (' . round($estimate->total_discount_percentage, 2) . '%)';
        } elseif (isset($estimate->total_discount_amount) && $estimate->total_discount_amount > 0) {
             $discount_value = $estimate->total_discount_amount;
        }
        $data_for_parsing['estimate_discount_value_formatted'] = $_app_format_money_fn($discount_value, $base_currency);
        $data_for_parsing['estimate_discount_label'] = $_l_fn('estimate_discount') . $discount_label_suffix;

        $data_for_parsing['estimate_total_tax'] = isset($estimate->total_tax) ? $_app_format_money_fn($estimate->total_tax, $base_currency) : $_app_format_money_fn(0, $base_currency);
        $data_for_parsing['estimate_total'] = isset($estimate->total) ? $_app_format_money_fn($estimate->total, $base_currency) : $_app_format_money_fn(0, $base_currency);
        
        $data_for_parsing['estimate_notes'] = isset($estimate->notes) ? nl2br(htmlspecialchars($estimate->notes)) : '';
        $data_for_parsing['estimate_terms_and_conditions'] = isset($estimate->terms_and_conditions) ? nl2br(htmlspecialchars($estimate->terms_and_conditions)) : '';
        
        $estimate_id_for_url = $estimate->id ?? '';
        $estimate_hash_for_url = $estimate->hash ?? '';
        $data_for_parsing['estimate_public_url'] = ($estimate_id_for_url && $estimate_hash_for_url) ? $_site_url_fn('custom_estimation/custom_estimate_public/view/' . $estimate_id_for_url . '/' . $estimate_hash_for_url) : '';
        $data_for_parsing['estimate_public_pdf_url'] = ($estimate_id_for_url && $estimate_hash_for_url) ? $_site_url_fn('custom_estimation/custom_estimate_public/pdf/' . $estimate_id_for_url . '/' . $estimate_hash_for_url) : '';


        // Placeholders for acceptance/declination status and actions
        $acceptable_statuses = ['draft', 'sent']; // Define which statuses can be actioned, adjust as needed
        $data_for_parsing['can_be_actioned'] = isset($estimate->status) && in_array($estimate->status, $acceptable_statuses);
        $data_for_parsing['is_accepted'] = isset($estimate->status) && $estimate->status == 'approved'; // Use your 'approved' status ID
        $data_for_parsing['is_declined'] = isset($estimate->status) && $estimate->status == 'declined'; // Use your 'declined' status ID
        $data_for_parsing['estimate_accepted_by_name'] = isset($estimate->approved_by_client_name) ? htmlspecialchars($estimate->approved_by_client_name) : '';
        $data_for_parsing['estimate_accepted_date'] = isset($estimate->approved_at) ? $_dt_fn($estimate->approved_at) : '';
        // Signature image URL placeholder would need logic if you save signatures as files
        // $data_for_parsing['estimate_signature_image_url'] = isset($estimate->signature_file_path) ? base_url('uploads/custom_estimate_signatures/' . $estimate->signature_file_path) : '';


        // Company Details
        $data_for_parsing['company_name'] = htmlspecialchars($_get_option_fn('companyname'));
        $data_for_parsing['company_address'] = nl2br(htmlspecialchars($_get_option_fn('company_address')));
        $company_city = $_get_option_fn('company_city'); $company_state = $_get_option_fn('company_state'); $company_zip = $_get_option_fn('company_zip');
        $data_for_parsing['company_city_state_zip'] = trim( ($company_city ? htmlspecialchars($company_city) . ', ' : '') . htmlspecialchars($company_state) . ' ' . htmlspecialchars($company_zip) );
        $country_obj = $_get_country_fn($_get_option_fn('company_country'));
        $data_for_parsing['company_country'] = $country_obj ? htmlspecialchars($country_obj->short_name) : '';
        $data_for_parsing['company_phone'] = htmlspecialchars($_get_option_fn('company_phone'));
        $data_for_parsing['company_email'] = htmlspecialchars($_get_option_fn('company_email'));
        $data_for_parsing['company_website'] = htmlspecialchars($_get_option_fn('company_website'));
        $data_for_parsing['company_vat_number'] = htmlspecialchars($_get_option_fn('company_vat'));
        $data_for_parsing['company_logo_url'] = $_pdf_logo_url_fn();

        // Client/Lead Details (assuming $estimate->lead_id is populated)
        $client_data_keys = ['client_name', 'client_company', 'client_address', 'client_city_state_zip', 'client_country', 'client_phone', 'client_email', 'client_vat_number'];
        foreach($client_data_keys as $key) { $data_for_parsing[$key] = ''; } // Initialize
        if (isset($estimate->lead_id) && $estimate->lead_id) {
            if (!class_exists('Leads_model', false)) { $CI->load->model('leads_model'); } // Ensure model is loaded
            $lead = isset($CI->leads_model) ? $CI->leads_model->get($estimate->lead_id) : null;
            if ($lead) {
                $data_for_parsing['client_name'] = htmlspecialchars($lead->name);
                $data_for_parsing['client_company'] = htmlspecialchars($lead->company ?? '');
                $data_for_parsing['client_address'] = nl2br(htmlspecialchars($lead->address ?? ''));
                $client_city = $lead->city ?? ''; $client_state = $lead->state ?? ''; $client_zip = $lead->zip ?? '';
                $data_for_parsing['client_city_state_zip'] = trim( ($client_city ? htmlspecialchars($client_city) . ', ' : '') . htmlspecialchars($client_state) . ' ' . htmlspecialchars($client_zip) );
                $lead_country_obj = $_get_country_fn($lead->country ?? null); // Pass null if country not set
                $data_for_parsing['client_country'] = $lead_country_obj ? htmlspecialchars($lead_country_obj->short_name) : '';
                $data_for_parsing['client_phone'] = htmlspecialchars($lead->phonenumber ?? '');
                $data_for_parsing['client_email'] = htmlspecialchars($lead->email ?? '');
                $data_for_parsing['client_vat_number'] = htmlspecialchars($lead->vat ?? '');
            }
        }
        
        // Merge any additional data passed to the function
        $data_for_parsing = array_merge($data_for_parsing, $additional_data);

        // Current Date
        $data_for_parsing['current_date_short'] = $_d_fn(date('Y-m-d'));
        $data_for_parsing['current_date_long'] = $_dt_fn(date('Y-m-d H:i:s'));

        // --- Ensure all values are strings or arrays/objects for parsing ---
        foreach ($data_for_parsing as $key => $value) {
            if (is_null($value)) { $data_for_parsing[$key] = ''; }
            elseif (is_bool($value)) { $data_for_parsing[$key] = $value ? '1' : '0'; } // For conditional blocks
            elseif (!is_array($value) && !is_object($value)) { $data_for_parsing[$key] = (string) $value; }
        }

        // --- HTML and CSS Handling ---
        $final_html_content = $template_html ?? '';
        $page_title = $data_for_parsing['estimate_title'] ?? $_l_fn('custom_estimate');
        $is_full_html_doc = stripos($final_html_content, '<html') !== false && stripos($final_html_content, '<head') !== false;

        if (!empty($template_css)) {
            $style_block = "\n<style type=\"text/css\">\n" . $template_css . "\n</style>\n";
            if (preg_match('/<head[^>]*>/i', $final_html_content, $matches_head)) {
                $final_html_content = str_replace($matches_head[0], $matches_head[0] . $style_block, $final_html_content);
            } elseif ($is_full_html_doc && preg_match('/<html[^>]*>/i', $final_html_content, $matches_html)) { // If <html> but no <head>
                $final_html_content = str_replace($matches_html[0], $matches_html[0] . "<head><meta charset=\"utf-8\"><title>" . htmlspecialchars($page_title) . "</title>" . $style_block . "</head>", $final_html_content);
            } else { // No <html> or <head>, wrap it
                 $final_html_content = "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"utf-8\"><title>" . htmlspecialchars($page_title) . "</title>" . $style_block . "</head><body>" . $final_html_content . "</body></html>";
                 $is_full_html_doc = true; // We just made it one
            }
        }
        
        // Ensure basic HTML structure if not already a full document
        if (!$is_full_html_doc) {
            $final_html_content = "<!DOCTYPE html><html lang=\"en\"><head><meta charset=\"utf-8\"><title>" . htmlspecialchars($page_title) . "</title></head><body>" . $final_html_content . "</body></html>";
        }
        // Ensure charset and title are in head if it exists
        if (stripos($final_html_content, '<head') !== false) {
            if (stripos($final_html_content, '<meta charset="utf-8">') === false && stripos($final_html_content, '<meta charset="UTF-8">') === false) { // Check for both common charsets
                 $final_html_content = preg_replace('/(<head[^>]*>)/i', '$1<meta charset="utf-8">', $final_html_content, 1);
            }
            if (stripos($final_html_content, '<title>') === false && !empty($page_title)) {
                $final_html_content = preg_replace('/(<head[^>]*>)/i', '$1<title>' . htmlspecialchars($page_title) . '</title>', $final_html_content, 1);
            }
        }


        // --- Define Tags for Loops and Conditionals ---
        $item_loop_start_tag = '{START_ESTIMATE_ITEMS_LOOP}';
        $item_loop_end_tag   = '{END_ESTIMATE_ITEMS_LOOP}';
        
        $conditional_tags = [
            'IF_HAS_ITEMS'    => (isset($estimate->items) && (is_array($estimate->items) || is_object($estimate->items)) && count((array)$estimate->items) > 0),
            'IF_NO_ITEMS'     => !(isset($estimate->items) && (is_array($estimate->items) || is_object($estimate->items)) && count((array)$estimate->items) > 0),
            'IF_HAS_NOTES'    => !empty(trim(strip_tags($data_for_parsing['estimate_notes']))),
            'IF_HAS_TERMS_AND_CONDITIONS' => !empty(trim(strip_tags($data_for_parsing['estimate_terms_and_conditions']))),
            'IF_HAS_DISCOUNT' => ($discount_value > 0),
            // New conditional tags based on estimate status and actionability
            'IF_ESTIMATE_CAN_BE_ACTIONED' => $data_for_parsing['can_be_actioned'] == '1',
            'IF_ESTIMATE_IS_ACCEPTED'     => $data_for_parsing['is_accepted'] == '1',
            'IF_ESTIMATE_IS_DECLINED'     => $data_for_parsing['is_declined'] == '1',
        ];
        
        $item_conditional_tags_pattern = [
            'IF_ITEM_COMPLIMENTARY' => 'is_complimentary_item', // Key in item data to check (boolean)
            'IF_NOT_ITEM_COMPLIMENTARY' => '!is_complimentary_item', // Key in item data to check (boolean, negated)
        ];


        // --- Process Item Loop ---
        $item_number = 0;
        $item_loop_regex = '/' . preg_quote($item_loop_start_tag, '/') . '(.*?)' . preg_quote($item_loop_end_tag, '/') . '/s';

        if (preg_match($item_loop_regex, $final_html_content, $matches)) {
            $full_loop_block = $matches[0];
            $item_row_template = $matches[1];
            $all_items_rendered_html = '';

            if (isset($estimate->items) && (is_array($estimate->items) || is_object($estimate->items)) && count((array)$estimate->items) > 0) {
                foreach ($estimate->items as $item_obj) {
                    $item_number++; 
                    $item = (array)$item_obj; // Work with array
                    $single_item_rendered_html = $item_row_template;

                    $qty = $item['quantity'] ?? 0;
                    $price = $item['unit_price'] ?? 0;
                    $item_subtotal_calc = $qty * $price;
                    $item_discount_value = 0;
                    if (isset($item['discount_percentage']) && (float)$item['discount_percentage'] > 0) {
                        $item_discount_value = ($item_subtotal_calc * (float)$item['discount_percentage'] / 100);
                    } elseif (isset($item['discount_amount']) && (float)$item['discount_amount'] > 0) { // Older structure?
                        $item_discount_value = (float)$item['discount_amount'];
                    }
                    $item_total_after_discount = $item_subtotal_calc - $item_discount_value;
                    $is_complimentary_item = (isset($item['is_complimentary']) && $item['is_complimentary'] == 1);

                    $item_data_for_parsing = [ // Data specific to this item for parsing its template row
                        'is_complimentary_item' => $is_complimentary_item, // For item-level conditionals
                    ];
                    
                    // Process item-level conditional blocks first
                    foreach($item_conditional_tags_pattern as $tag_name => $condition_key){
                        $negate = strpos($condition_key, '!') === 0;
                        if($negate) $condition_key = substr($condition_key, 1);

                        $condition_met = isset($item_data_for_parsing[$condition_key]) && $item_data_for_parsing[$condition_key];
                        if($negate) $condition_met = !$condition_met;
                        
                        $start_tag = '{' . $tag_name . '}';
                        $end_tag = '{END_' . $tag_name . '}';
                        $regex = '/' . preg_quote($start_tag, '/') . '(.*?)' . preg_quote($end_tag, '/') . '/s';
                        
                        if(preg_match($regex, $single_item_rendered_html, $cond_matches)){
                            if($condition_met){
                                $single_item_rendered_html = str_replace($cond_matches[0], $cond_matches[1], $single_item_rendered_html);
                            } else {
                                $single_item_rendered_html = str_replace($cond_matches[0], '', $single_item_rendered_html);
                            }
                        }
                    }


                    $item_replacements = [
                        '{item_number}' => $item_number,
                        '{item_description}' => htmlspecialchars($item['description'] ?? ''),
                        '{item_long_description}' => nl2br(htmlspecialchars($item['long_description'] ?? '')),
                        '{item_quantity}' => $_format_quantity_fn($qty),
                        '{item_unit}' => htmlspecialchars($item['unit'] ?? ''),
                        '{item_unit_price}' => $_app_format_money_fn($price, $base_currency),
                        '{item_discount_percentage}' => round((float)($item['discount_percentage'] ?? 0), 2),
                        '{item_discount_amount_formatted}' => $_app_format_money_fn($item_discount_value, $base_currency),
                        '{item_discount_display}' => $item_discount_value > 0 ? ('-' . $_app_format_money_fn($item_discount_value, $base_currency)) : '',
                        '{item_total_amount}' => $_app_format_money_fn($item_total_after_discount, $base_currency),
                        '{item_custom_dim_length}' => htmlspecialchars($item['custom_dim_length'] ?? ''),
                        '{item_custom_dim_width}' => htmlspecialchars($item['custom_dim_width'] ?? ''),
                        '{item_custom_dim_height}' => htmlspecialchars($item['custom_dim_height'] ?? ''),
                        '{item_formula}' => htmlspecialchars($item['formula'] ?? ''),
                        '{item_material}' => htmlspecialchars($item['material'] ?? ''),
                        '{item_range}' => htmlspecialchars($item['range'] ?? ''),
                        '{item_is_complimentary_text}' => $is_complimentary_item ? $_l_fn('yes') : $_l_fn('no'),
                    ];
                    foreach($item_replacements as $placeholder => $value){ 
                        $single_item_rendered_html = str_replace($placeholder, (string)$value, $single_item_rendered_html); 
                    }
                    
                    $all_items_rendered_html .= $single_item_rendered_html;
                }
            }
            $final_html_content = str_replace($full_loop_block, $all_items_rendered_html, $final_html_content);
        }

        // --- Process Overall Conditional Blocks ---
        foreach($conditional_tags as $tag_name => $condition_met){
            $start_tag = '{' . $tag_name . '}';
            $end_tag = '{END_' . $tag_name . '}';
            // Regex to find the block: {TAG_NAME} content {END_TAG_NAME}
            $regex = '/' . preg_quote($start_tag, '/') . '(.*?)' . preg_quote($end_tag, '/') . '/s';
            
            // Using preg_replace_callback to handle multiple occurrences if any
            $final_html_content = preg_replace_callback($regex, function($matches) use ($condition_met) {
                if ($condition_met) {
                    return $matches[1]; // Return the content between tags
                } else {
                    return ''; // Remove the block entirely
                }
            }, $final_html_content);
        }
        

        // --- Replace all remaining simple placeholders ---
        foreach ($data_for_parsing as $key => $value) {
            // Only replace if value is a string or number (booleans were handled for conditionals)
            if (is_string($value) || is_numeric($value)) {
                $final_html_content = str_replace('{' . $key . '}', (string)$value, $final_html_content);
            }
        }
        
        // Optional: Clean up any unmatched {placeholders} that might remain if data was missing
        // $final_html_content = preg_replace('/\{[a-zA-Z0-9_]+\}/', '', $final_html_content);

        return $final_html_content;
    }
}


if (!function_exists('format_custom_estimate_number')) {
    /**
     * Formats the custom estimate number with a prefix.
     *
     * @param int $id The estimate ID.
     * @return string Formatted estimate number.
     */
    function format_custom_estimate_number($id) {
        $CI = &get_instance(); // Ensure CI instance is available
        // Get prefix from settings if available, otherwise use a default.
        $prefix = (function_exists('get_option') ? get_option('custom_estimate_prefix') : null); 
        $prefix = $prefix ? $prefix : 'EST-'; // Default prefix
        // Get padding from settings if available, otherwise use a default.
        $padding = (function_exists('get_option') ? (int)get_option('number_padding_custom_estimates') : null);
        $padding = $padding ?: 5; // Default padding
        
        return $prefix . str_pad($id, $padding, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('get_custom_estimate_status_by_id')) {
    /**
     * Gets custom estimate status details by status ID.
     *
     * @param string $status_id The status ID (slug).
     * @return array Status details (name, color, order).
     */
    function get_custom_estimate_status_by_id($status_id) {
        $CI = &get_instance();
        $model_instance = null;

        // Try to get the loaded instance of the model
        if (isset($CI->estimates_model) && $CI->estimates_model instanceof Estimates_model) {
            $model_instance = $CI->estimates_model;
        } 
        // If not loaded as 'estimates_model', try the module specific name if loaded elsewhere
        elseif (isset($CI->custom_estimation_estimates_model) && $CI->custom_estimation_estimates_model instanceof Estimates_model) {
            $model_instance = $CI->custom_estimation_estimates_model;
        }
        // If still not found, try to load it
        elseif (defined('CUSTOM_ESTIMATION_MODULE_NAME')) {
            $model_path = CUSTOM_ESTIMATION_MODULE_NAME . '/estimates_model';
            if (!class_exists('Estimates_model', false)) { // Check if class is already declared
                $full_model_path = module_dir_path(CUSTOM_ESTIMATION_MODULE_NAME) . 'models/Estimates_model.php';
                if(file_exists($full_model_path)){
                    require_once($full_model_path); // Require it if not autoloaded
                }
            }
            // Now try to load or get instance
            if (class_exists('Estimates_model')) {
                 $CI->load->model($model_path, 'temp_custom_estimates_model_instance'); // Load with a temporary alias
                 $model_instance = $CI->temp_custom_estimates_model_instance;
            }
        }

        if ($model_instance && method_exists($model_instance, 'get_estimate_statuses')) {
            $statuses = $model_instance->get_estimate_statuses();
            foreach ($statuses as $status) {
                if (isset($status['id']) && $status['id'] == $status_id) {
                    return $status;
                }
            }
        }
        // Fallback if model or status not found
        return ['id' => $status_id, 'name' => ucfirst(str_replace('_', ' ', $status_id)), 'color' => 'default', 'order' => 0];
    }
}

?>