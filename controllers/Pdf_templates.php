<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pdf_templates extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_estimation/pdf_templates_model');

        // This is a good global permission check for the entire controller.
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_pdf_templates')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
    }

    /**
     * List all PDF templates
     */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            // This correctly delegates to the server-side processing file for DataTables.
            $this->app->get_table_data(module_views_path(CUSTOM_ESTIMATION_MODULE_NAME, 'admin/pdf_templates/table'));
        }
        $data['title'] = _l('custom_estimation_submenu_pdf_templates');
        $this->load->view('admin/pdf_templates/manage', $data);
    }

    /**
     * Add or edit a PDF template
     * @param string $id template id
     */
    public function template($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($id == '') { // Adding new template
                $new_id = $this->pdf_templates_model->add_template($data);
                if ($new_id) {
                    // Check if $new_id is an array with an error key (if you implement that for slug conflicts in model)
                    if (is_array($new_id) && isset($new_id['error'])) {
                         set_alert('danger', $new_id['error']);
                         // Optionally, repopulate form data via session flashdata if redirecting back to form
                         // $this->session->set_flashdata('form_data', $data);
                         redirect(admin_url('custom_estimation/pdf_templates/template'));
                    } else {
                        set_alert('success', _l('pdf_template_added_successfully'));
                        redirect(admin_url('custom_estimation/pdf_templates/template/' . $new_id));
                    }
                } else {
                    set_alert('danger', _l('pdf_template_add_fail'));
                    // $this->session->set_flashdata('form_data', $data); // Repopulate form
                    redirect(admin_url('custom_estimation/pdf_templates/template'));
                }
            } else { // Updating existing template
                $success = $this->pdf_templates_model->update_template($data, $id);
                // Check if $success is an array with an error key (for slug conflicts)
                if (is_array($success) && isset($success['error'])) {
                    set_alert('danger', $success['error']);
                } elseif ($success) {
                    set_alert('success', _l('pdf_template_updated_successfully'));
                } else {
                    set_alert('warning', _l('pdf_template_update_fail'));
                }
                redirect(admin_url('custom_estimation/pdf_templates/template/' . $id));
            }
        }

        // Prepare data for the add/edit view (GET request or after failed POST if not repopulating)
        if ($id == '') {
            $data['title'] = _l('new_pdf_template');
            // if($this->session->flashdata('form_data')){ // For repopulating form after failed POST
            //     $data = array_merge($data, $this->session->flashdata('form_data'));
            // }
        } else {
            $data['template'] = $this->pdf_templates_model->get_template($id);
            if (!$data['template']) {
                set_alert('danger', _l('pdf_template_not_found'));
                redirect(admin_url('custom_estimation/pdf_templates'));
                return; // Stop execution
            }
            $data['title'] = _l('edit_pdf_template') . ' - ' . $data['template']->name;
        }
        
        $data['bodyclass'] = 'pdf-template-edit';
        $this->load->view('admin/pdf_templates/template_form', $data);
    }

    /**
     * Delete a PDF template
     * @param mixed $id template id
     */
    public function delete_template($id)
    {
        if (!$id || !is_numeric($id)) {
            redirect(admin_url('custom_estimation/pdf_templates'));
        }
        $response = $this->pdf_templates_model->delete_template($id);
        if (is_array($response) && isset($response['error'])) {
             set_alert('warning', $response['error']); // e.g., "Cannot delete default template"
        } elseif ($response == true) {
            set_alert('success', _l('pdf_template_deleted_successfully'));
        } else {
            set_alert('warning', _l('pdf_template_delete_fail'));
        }
        redirect(admin_url('custom_estimation/pdf_templates'));
    }

    /**
     * Set a template as default
     * @param mixed $id template id
     */
    public function set_default($id)
    {
        if (!$id || !is_numeric($id)) {
            redirect(admin_url('custom_estimation/pdf_templates'));
        }
        $success = $this->pdf_templates_model->set_default_template($id);
        if ($success) {
            set_alert('success', _l('pdf_template_set_as_default_successfully'));
        } else {
            // This case might be rare if the ID is valid, unless DB error occurs.
            set_alert('danger', _l('pdf_template_set_as_default_fail'));
        }
        redirect(admin_url('custom_estimation/pdf_templates'));
    }
}