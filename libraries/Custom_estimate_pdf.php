<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!class_exists('App_pdf')) {
    $app_pdf_path = APPPATH . 'libraries/pdf/App_pdf.php';
    if (file_exists($app_pdf_path)) {
        require_once($app_pdf_path);
    } else {
        $log_message = "Core PDF library App_pdf.php not found at expected path: " . $app_pdf_path;
        log_message('error', $log_message);
        // Consider throwing an exception or a more user-friendly error
    }
}

class Custom_estimate_pdf extends App_pdf
{
    protected $estimate;
    protected $template_html;
    protected $template_css;

    public function __construct($params)
    {
        if (!class_exists('App_pdf')) {
            throw new Exception("Parent class App_pdf not found. PDF generation cannot proceed.");
        }
        parent::__construct();

        if (!isset($params['estimate_data']) || !is_object($params['estimate_data'])) {
            throw new Exception("Estimate data must be an object and provided in params['estimate_data'].");
        }
        $this->estimate = $params['estimate_data'];
        $this->template_html = isset($params['template_content']) ? $params['template_content'] : null;
        $this->template_css = isset($params['template_css']) ? $params['template_css'] : '';

        if (defined('CUSTOM_ESTIMATION_MODULE_NAME')) {
            get_instance()->load->helper(CUSTOM_ESTIMATION_MODULE_NAME . '/custom_estimation_template');
        } else {
            log_message('error', 'Custom_estimate_pdf: CUSTOM_ESTIMATION_MODULE_NAME constant not defined. Cannot load template helper.');
            // Optionally throw an exception
        }

        $this->SetTitle(_l('custom_estimate') . ' #' . format_custom_estimate_number($this->estimate->id));
        $this->setAuthor(get_option('companyname'));
        $this->setCreator(get_option('companyname'));
        $this->setSubject(_l('custom_estimate'));

        $this->setCellPadding(1);
        $this->setCellHeightRatio(1.5);
        $this->setFont(get_option('pdf_font'), '', get_option('pdf_font_size'));
    }

    protected function type() {
        return 'custom_estimate';
    }

    protected function file_path() {
        $dir = get_upload_path_by_type('custom_estimate');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file_name = slug_it(_l('custom_estimate') . '-' . format_custom_estimate_number($this->estimate->id)) . '.pdf';
        return $dir . $file_name;
    }

    public function prepare()
    {
        $this->AddPage();
        $html = $this->_build_html();

        if (empty(trim($html))) {
            log_message('error', 'Custom_estimate_pdf::prepare() - HTML content from _build_html() is empty. PDF might be blank. Estimate ID: ' . $this->estimate->id);
            // Fallback HTML to ensure PDF is not entirely blank and shows an error
            $html = "<h1>Error: Could not generate PDF content for Custom Estimate #" . format_custom_estimate_number($this->estimate->id) . ". Please check logs.</h1>";
        }
        
        // Clean any accidental output buffer before TCPDF tries to write.
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            ob_clean();
        }

        $this->writeHTML($html, true, false, true, false, '');
    }

    protected function _build_html()
    {
        $CI = &get_instance();
        $additional_data_for_parsing = [];

        if ($this->template_html) {
            // log_message('debug', "Custom_estimate_pdf: _build_html() - Using template_html from database for Estimate ID: " . $this->estimate->id);

            if (!function_exists('parse_custom_estimate_template')) {
                log_message('error', 'Custom_estimate_pdf: Helper function parse_custom_estimate_template not found.');
                return "<h1>Error: PDF Template parsing function not available.</h1>";
            }

            $parsed_html = parse_custom_estimate_template(
                $this->template_html,
                $this->template_css,
                $this->estimate,
                $additional_data_for_parsing
            );
            // log_message('debug', "Custom_estimate_pdf: _build_html() - Parsed HTML length: " . strlen(trim($parsed_html)));
            return $parsed_html;

        } else {
            log_message('info', 'Custom_estimate_pdf: No custom template_html. Falling back to default view for Estimate ID: ' . $this->estimate->id);

            $data_for_fallback_view['estimate'] = $this->estimate;
            $data_for_fallback_view['title'] = _l('custom_estimate') . ' #' . format_custom_estimate_number($this->estimate->id);

            if (!function_exists('get_custom_estimate_status_by_id')) {
                log_message('error', 'Custom_estimate_pdf: Helper get_custom_estimate_status_by_id not found for fallback.');
                $data_for_fallback_view['status_name'] = ucfirst($this->estimate->status);
            } else {
                $status_info = get_custom_estimate_status_by_id($this->estimate->status);
                $data_for_fallback_view['status_name'] = $status_info ? $status_info['name'] : ucfirst($this->estimate->status);
            }

            $module_name = defined('CUSTOM_ESTIMATION_MODULE_NAME') ? CUSTOM_ESTIMATION_MODULE_NAME : 'custom_estimation';
            $fallback_view_path_relative = $module_name . '/admin/estimates/pdf_template';
            $fallback_view_file_full_path = FCPATH . 'modules/' . $module_name . '/views/admin/estimates/pdf_template.php';


            if (file_exists($fallback_view_file_full_path)) {
                // log_message('debug', "Custom_estimate_pdf: _build_html() - Loading fallback view: " . $fallback_view_path_relative);
                $fallback_html = $CI->load->view($fallback_view_path_relative, $data_for_fallback_view, true);
                return $fallback_html;
            } else {
                log_message('error', 'Custom_estimate_pdf: Fallback PDF template view file missing: ' . $fallback_view_file_full_path);
                return "<h1>Error: Fallback PDF template (pdf_template.php) is missing.</h1>";
            }
        }
    }
}