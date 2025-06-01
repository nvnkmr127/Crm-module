<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Custom_estimate_public extends App_Controller 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_estimation/estimates_model');
        $this->load->model('custom_estimation/pdf_templates_model'); 
        
        // Ensure the template helper is loaded.
        // If not autoloaded by the main module file, load it here.
        if (!function_exists('parse_custom_estimate_template')) {
            $this->load->helper(CUSTOM_ESTIMATION_MODULE_NAME . '/custom_estimation_template');
        }
        $this->load->library('session'); 
    }

    public function view($id = null, $hash = null)
    {
        if (!$id || !$hash || !is_numeric($id)) {
            show_404();
        }

        $estimate = $this->estimates_model->get_estimate($id, true); 

        if (!$estimate || $estimate->hash !== $hash) {
            set_alert('danger', _l('invalid_estimate_hash'));
            show_404(); 
            return;
        }

        $pdf_template_to_use = null;
        if (!empty($estimate->pdf_template_slug)) {
            $pdf_template_to_use = $this->pdf_templates_model->get_template_by_slug($estimate->pdf_template_slug);
        }
        if (!$pdf_template_to_use) { 
            $pdf_template_to_use = $this->pdf_templates_model->get_default_template();
        }

        $template_html = '<h1>{estimate_subject}</h1><p>'._l('custom_estimate_template_not_found').'</p>'; // Fallback HTML
        $template_css = ''; // Default empty CSS

        if ($pdf_template_to_use && isset($pdf_template_to_use->template_html)) {
            $template_html = $pdf_template_to_use->template_html;
            $template_css = isset($pdf_template_to_use->template_css) ? $pdf_template_to_use->template_css : '';
        } elseif (ENVIRONMENT !== 'production') {
            log_message('error', "No PDF template found for estimate ID {$id} (slug: {$estimate->pdf_template_slug}) or no default template set for public view.");
        }

        $additional_data = []; // Prepare any additional data needed by the template parser that's not directly on $estimate
        
        $data['parsed_estimate_html'] = parse_custom_estimate_template($template_html, $template_css, $estimate, $additional_data);
        $data['title'] = _l('custom_estimate_online_view_title') . ' - #' . format_custom_estimate_number($estimate->id);
        $data['estimate'] = $estimate; 
        $data['bodyclass'] = 'viewcustomestimate public-view';

        $this->load->view('custom_estimation/public/estimate_online_view', $data);
    }

    public function pdf($id = null, $hash = null)
    {
        if (!$id || !$hash || !is_numeric($id)) {
            show_404();
        }

        $estimate = $this->estimates_model->get_estimate($id, true); 

        if (!$estimate || $estimate->hash !== $hash) {
            log_message('error', 'Invalid hash or ID for public PDF access. Estimate ID: ' . $id);
            show_404();
            return;
        }

        $pdf_template_to_use = null;
        if (!empty($estimate->pdf_template_slug)) {
            $pdf_template_to_use = $this->pdf_templates_model->get_template_by_slug($estimate->pdf_template_slug);
        }
        if (!$pdf_template_to_use) { 
            $pdf_template_to_use = $this->pdf_templates_model->get_default_template();
        }
        
        $template_html_content = $pdf_template_to_use ? $pdf_template_to_use->template_html : null;
        $template_css_content = $pdf_template_to_use ? $pdf_template_to_use->template_css : '';

        try {
            // Ensure the custom PDF library is loaded from your module.
            // The alias 'custom_estimate_pdf_lib_public' is used to avoid conflicts if the library
            // was already loaded with a different alias (e.g., by an admin controller).
            $this->load->library(CUSTOM_ESTIMATION_MODULE_NAME . '/custom_estimate_pdf', [
                'estimate_data'    => $estimate,
                'template_content' => $template_html_content, 
                'template_css'     => $template_css_content 
            ], 'custom_estimate_pdf_lib_public'); // Unique alias for this instance
            
            $this->custom_estimate_pdf_lib_public->prepare();
            $this->custom_estimate_pdf_lib_public->Output(slug_it(_l('custom_estimate') . '-' . format_custom_estimate_number($estimate->id)) . '.pdf', 'I'); 
        } catch (Exception $e) {
            log_message('error', 'Error generating public PDF for custom estimate ' . $id . ': ' . $e->getMessage());
            show_error('Could not generate PDF: ' . $e->getMessage());
        }
    }
}
