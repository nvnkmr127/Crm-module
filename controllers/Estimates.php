<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Estimates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_estimation/estimates_model');
        $this->load->model('custom_estimation/products_model'); 
        $this->load->model('custom_estimation/item_packages_model'); 
        $this->load->model('custom_estimation/pdf_templates_model'); 
        $this->load->model('leads_model'); 
        $this->load->model('taxes_model'); // Perfex Core Taxes Model for tax rates
        $this->load->library('form_validation');

        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(CUSTOM_ESTIMATION_MODULE_NAME, 'admin/estimates/table'));
        }
        $data['title'] = _l('custom_estimation_submenu_estimates');
        $this->load->view('admin/estimates/manage', $data);
    }

    public function estimate($id = '') 
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            
            $this->form_validation->set_rules('lead_id', _l('custom_estimate_lead'), 'required|integer');
            $this->form_validation->set_rules('valid_until', _l('custom_estimate_valid_until'), 'required');
            $this->form_validation->set_rules('status', _l('custom_estimate_status'), 'required');
            // Add validation for new fields if necessary (e.g., adjustment is numeric)
            if (isset($data['adjustment'])) {
                $this->form_validation->set_rules('adjustment', _l('estimate_adjustment'), 'numeric');
            }


            if ($this->form_validation->run() == FALSE) {
                $error_message = validation_errors('<p class="text-danger">', '</p>');
                set_alert('danger', $error_message);
                $this->session->set_flashdata('form_data', $data); // Repopulate form
                if ($id == '') {
                    redirect(admin_url('custom_estimation/estimates/estimate'));
                } else {
                    redirect(admin_url('custom_estimation/estimates/estimate/' . $id));
                }
                return; 
            }


            if ($id == '') {
                if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create')) {
                    access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
                }
                $new_id = $this->estimates_model->add_estimate($data);
                if ($new_id) {
                    set_alert('success', _l('custom_estimate_added_successfully'));
                    redirect(admin_url('custom_estimation/estimates/estimate/' . $new_id));
                } else {
                    set_alert('danger', _l('custom_estimate_add_fail'));
                    $this->session->set_flashdata('form_data', $data);
                    redirect(admin_url('custom_estimation/estimates/estimate')); 
                }
            } else {
                if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit')) {
                    access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
                }
                $success = $this->estimates_model->update_estimate($data, $id);
                if ($success) {
                    set_alert('success', _l('custom_estimate_updated_successfully'));
                } else {
                    set_alert('warning', _l('custom_estimate_update_fail'));
                }
                redirect(admin_url('custom_estimation/estimates/estimate/' . $id));
            }
        }

        // Prepare data for the view (GET request or after failed POST if repopulating)
        $view_data = []; 
        $view_data['id'] = $id; // Pass ID to view for form action

        if ($id == '') {
            $view_data['title'] = _l('new_custom_estimate');
            if($this->session->flashdata('form_data')){
                // If form data exists from a failed POST, use it to repopulate
                $estimate_from_flash = (object) $this->session->flashdata('form_data');
                if(isset($estimate_from_flash->items) && is_array($estimate_from_flash->items)){
                    $repopulated_items = [];
                    foreach($estimate_from_flash->items as $item_array){
                        $repopulated_items[] = (object) $item_array;
                    }
                    $estimate_from_flash->items = $repopulated_items;
                }
                $view_data['estimate'] = $estimate_from_flash;
            }
        } else {
            $estimate_data = $this->estimates_model->get_estimate($id, true); 
            if (!$estimate_data) {
                set_alert('danger', _l('custom_estimate_not_found'));
                redirect(admin_url('custom_estimation/estimates'));
                return; 
            }
            $view_data['estimate'] = $estimate_data;
            $title_estimate_number = function_exists('format_custom_estimate_number') ? format_custom_estimate_number($estimate_data->id) : $estimate_data->id;
            $view_data['title'] = _l('edit_custom_estimate') . ' - ' . $title_estimate_number;
        }

        // Load products for item selection
        $all_products_raw = $this->products_model->get_all_products();
        $view_data['products'] = [];
        if ($all_products_raw) {
            foreach($all_products_raw as $product) {
                $view_data['products'][] = [
                    'id'                  => $product['id'] ?? null,
                    'name'                => $product['name'] ?? 'Unnamed Product',
                    'description'         => $product['description'] ?? '',
                    'long_description'    => $product['long_description'] ?? '',
                    'unit_price'          => $product['unit_price'] ?? '0.00',
                    'unit'                => $product['unit'] ?? '',
                    'formula'             => $product['formula'] ?? 'nos',
                    'dimensions_length'   => $product['dimensions_length'] ?? '',
                    'dimensions_width'    => $product['dimensions_width'] ?? '',
                    'dimensions_height'   => $product['dimensions_height'] ?? '',
                    'material'            => $product['material'] ?? '', 
                    'product_range'       => $product['product_range'] ?? '', 
                ];
            }
        }

        // Load leads for selection
        $view_data['leads'] = $this->leads_model->get(); 
        // Load custom estimate statuses
        $view_data['statuses'] = $this->estimates_model->get_estimate_statuses();
        // Load item packages
        $view_data['item_packages'] = $this->item_packages_model->get_all_item_packages();
        // Load PDF templates
        $view_data['pdf_templates'] = $this->pdf_templates_model->get_all_templates();
        
        // **** NEW: Load Taxes for dropdowns ****
        $view_data['taxes'] = $this->taxes_model->get(); // Fetches all available tax rates

        $view_data['bodyclass'] = 'estimate-edit accounting-transaction';

        $this->load->view('admin/estimates/estimate', $view_data);
    }

   public function delete_estimate($id)
    {
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
        if (!$id) {
            redirect(admin_url('custom_estimation/estimates'));
        }

        $response = $this->estimates_model->delete_estimate($id);
        if ($response == true) {
            set_alert('success', _l('custom_estimate_deleted_successfully'));
        } else {
            set_alert('warning', _l('custom_estimate_delete_fail'));
        }
        redirect(admin_url('custom_estimation/estimates'));
    }

    public function pdf($id)
    {
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }

        if (!$id) {
            redirect(admin_url('custom_estimation/estimates'));
        }

        $estimate = $this->estimates_model->get_estimate($id, true); 

        if (!$estimate) {
            set_alert('danger', _l('custom_estimate_not_found'));
            redirect(admin_url('custom_estimation/estimates'));
            return;
        }

        $pdf_template_to_use = null;
        if (!empty($estimate->pdf_template_slug)) { 
            $pdf_template_to_use = $this->pdf_templates_model->get_template_by_slug($estimate->pdf_template_slug);
        }
        if (!$pdf_template_to_use) { 
            $pdf_template_to_use = $this->pdf_templates_model->get_default_template();
        }

        try {
            $this->load->library('custom_estimation/custom_estimate_pdf', [
                'estimate_data' => $estimate,
                'template_content' => $pdf_template_to_use ? $pdf_template_to_use->template_html : null,
                'template_css' => $pdf_template_to_use ? $pdf_template_to_use->template_css : '' 
            ], 'custom_estimate_pdf_lib');
            
            $this->custom_estimate_pdf_lib->prepare();
            $this->custom_estimate_pdf_lib->Output(slug_it('custom-estimate-' . (function_exists('format_custom_estimate_number') ? format_custom_estimate_number($estimate->id) : $estimate->id) ) . '.pdf', 'I'); 
        } catch (Exception $e) {
            log_message('error', 'Error generating PDF for custom estimate ' . $id . ': ' . $e->getMessage());
            set_alert('danger', 'Could not generate PDF: ' . $e->getMessage());
            redirect(admin_url('custom_estimation/estimates/estimate/' . $id));
        }
    }

    public function get_package_items_ajax($package_id)
    {
        if (!$this->input->is_ajax_request()) {
             header('HTTP/1.0 403 Forbidden');
             echo 'Direct access not allowed.';
             exit;
        }

        $items = [];
        $success = false;

        if (!is_numeric($package_id) || $package_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid package ID.']);
            die();
        }
        
        $items = $this->item_packages_model->get_items_for_package($package_id);
        
        if ($items !== false) { 
            $success = true; 
        } else {
            $success = false; 
            $items = []; 
        }

        if ($success) {
            echo json_encode(['success' => true, 'items' => $items]);
        } else {
            echo json_encode(['success' => false, 'message' => _l('custom_estimate_add_fail')]); 
        }
        die(); 
    }
}
