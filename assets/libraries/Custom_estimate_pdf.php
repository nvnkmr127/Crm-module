// Inside modules/custom_estimation/assets/libraries/Custom_estimate_pdf.php

// ... (other parts of the class) ...

protected function _build_html()
{
    $CI = &get_instance();
    // Ensure the new Twig helper is loaded
    if (!function_exists('custom_estimate_render_twig_template')) {
        $CI->load->helper(CUSTOM_ESTIMATION_MODULE_NAME . '/custom_estimation_twig');
    }

    // Prepare data for Twig context
    // The $this->estimate object should already be populated by the constructor

    // Company Details (example structure, fetch as needed)
    $company_details = [
        'name' => get_option('companyname'),
        'address' => get_option('company_address'),
        'city' => get_option('company_city'),
        'state' => get_option('company_state'),
        'zip' => get_option('company_zip'),
        'country_code' => get_option('company_country_code'),
        'phone' => get_option('company_phone'),
        'vat' => get_option('company_vat'),
        // Add other company details your templates might need
    ];
    if (function_exists('get_company_country_name')) { // Perfex specific function
        $company_details['country_name'] = get_company_country_name(get_option('company_country'));
    }


    // Client/Lead Details (example structure)
    $client_details = [];
    if (isset($this->estimate->lead_id) && $this->estimate->lead_id) {
        if (!class_exists('Leads_model', false)) {
            $CI->load->model('leads_model');
        }
        $lead = $CI->leads_model->get($this->estimate->lead_id);
        if ($lead) {
            $client_details['name'] = $lead->name;
            $client_details['company'] = $lead->company;
            $client_details['address'] = $lead->address;
            $client_details['city'] = $lead->city;
            $client_details['state'] = $lead->state;
            $client_details['zip'] = $lead->zip;
            $client_details['country_name'] = '';
            if (function_exists('get_country_short_name') && $lead->country) {
                 $client_details['country_name'] = get_country_short_name($lead->country);
            }
            $client_details['phonenumber'] = $lead->phonenumber;
            $client_details['email'] = $lead->email;
            $client_details['vat'] = $lead->vat;
            // Add other lead details
        }
    } 
    // else if (isset($this->estimate->client_id) ... ) { /* similar logic for actual clients if applicable */ }


    $base_currency = null;
    if (function_exists('get_base_currency')) {
        $base_currency = get_base_currency();
    }
    if (!$base_currency && function_exists('get_currency_by_id')) { // Fallback for older Perfex or if base not found
        $base_currency = get_currency_by_id(get_option('default_currency'));
    }
     if (!$base_currency) { // Absolute fallback
        $base_currency = (object)['symbol' => get_option('currency_symbol') ?: '$', 'name' => get_option('currency_name') ?: 'USD'];
    }


    // The $this->template_html and $this->template_css are passed via constructor
    // These will be the raw template strings from the database.
    $html_output = custom_estimate_render_twig_template(
        $this->template_html,
        $this->template_css,
        $this->estimate,          // The main estimate object (with items)
        $company_details,
        $client_details,
        $base_currency
        // You can pass $additional_data here if needed
    );

    // Remove the die() statements from your _build_html and prepare methods if they are still there.
    return $html_output;
}

// ... (rest of the class) ...