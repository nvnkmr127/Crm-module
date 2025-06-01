<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Categories extends AdminController // Renamed from Custom_estimation_categories
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_estimation/categories_model');

        // Basic permission check - adjust if you have more granular permissions like 'manage_categories'
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
    }

    /**
     * List all product categories - for DataTables
     */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(CUSTOM_ESTIMATION_MODULE_NAME, 'admin/categories/table'));
        }
        $data['title'] = _l('custom_estimation_submenu_categories');
        $this->load->view('admin/categories/manage', $data);
    }

    /**
     * Handles saving (add/edit) a product category from form submission.
     * This method is targeted by the form in the modal.
     */
public function category($id = '')
{
    // --- START AGGRESSIVE DEBUG IN CONTROLLER ---
    // echo "DEBUG: Categories::category() method START. ID: " . htmlspecialchars($id) . "<br>\n"; // Line A

    if ($this->input->post()) {
        // echo "DEBUG: POST request DETECTED.<br>\n"; // Line B
        // echo "DEBUG: POST Data Received by Controller:<br>\n<pre>";
        // print_r($this->input->post(null, true)); // true for XSS clean
        // echo "</pre><br>\n"; // Line C

        // --- UNCOMMENT THE LINE BELOW TO STOP HERE AND CHECK BROWSER OUTPUT ---
        // die("DEBUG: STOPPED IN CONTROLLER AFTER RECEIVING POST. Check browser's Network tab > Response for this output.");

        // Your permission checks (ensure these aren't causing a silent exit if they fail)
        if ($id == '') {
            if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create')) {
                // echo "DEBUG: Create permission FAILED.<br>\n"; die();
                access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
            }
        } else {
            if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit')) {
                // echo "DEBUG: Edit permission FAILED.<br>\n"; die();
                access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
            }
        }

        $data = $this->input->post(null, true); // Get XSS cleaned post data
        $message = '';

        // echo "DEBUG: Controller is about to call model. Data being passed to model:<br>\n<pre>";
        // print_r($data);
        // echo "</pre><br>\n";
        // --- UNCOMMENT THE LINE BELOW TO STOP HERE ---
        // die("DEBUG: STOPPED IN CONTROLLER BEFORE CALLING MODEL. Check browser output.");


        if ($id == '') {
            $new_id = $this->categories_model->add_category($data);
            if ($new_id) {
                // echo "DEBUG: Controller: add_category successful. New ID: " . $new_id . "<br>\n"; // Line D
                set_alert('success', _l('custom_category_added_successfully'));
            } else {
                // echo "DEBUG: Controller: add_category FAILED.<br>\n"; // Line E
                // $db_error_from_model = $this->session->flashdata('db_error'); // If you set it from model
                // if($db_error_from_model) echo "DB Error from Model: " . $db_error_from_model . "<br>\n";
                set_alert('danger', _l('custom_category_add_fail'));
            }
        } else {
            $success = $this->categories_model->update_category($data, $id);
            if ($success) {
                // echo "DEBUG: Controller: update_category successful for ID: " . $id . "<br>\n";
                set_alert('success', _l('custom_category_updated_successfully'));
            } else {
                // echo "DEBUG: Controller: update_category FAILED for ID: " . $id . "<br>\n";
                set_alert('warning', _l('custom_category_update_fail'));
            }
        }
        
        // echo "DEBUG: Controller finished processing, about to redirect.<br>\n";
        // die("DEBUG: STOPPED BEFORE REDIRECT. Check browser output and alerts.");
        redirect(admin_url('custom_estimation/categories'));

    } else {
        // This block handles GET requests (e.g., if someone types the URL directly)
        // Or if the form submission was not a POST for some reason.
        // echo "DEBUG: Categories::category() - NO POST data. Redirecting.<br>\n";
        // die();
        redirect(admin_url('custom_estimation/categories'));
    }
}
    /**
     * Fetches content for the add/edit category modal via AJAX.
     */
    public function category_modal_content()
    {
        // Permission check for viewing/accessing modal content
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) {
            ajax_access_denied(); // Use Perfex helper for AJAX access denial
        }

        $data = [];
        $id = $this->input->get('id'); // Get ID from query string for editing

        if ($id && $id !== '') { // Editing existing category
            if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit')) {
                ajax_access_denied();
            }
            $data['category'] = $this->categories_model->get_category($id);
            if (!$data['category']) {
                // Handle error - category not found, perhaps echo a message or an empty modal part
                echo _l('custom_category_not_found');
                exit; // Stop further execution
            }
            $data['title'] = _l('edit_category');
        } else { // Adding new category
            if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create')) {
                ajax_access_denied();
            }
            $data['title'] = _l('new_category');
        }

        // Load the modal content view
        // Path: modules/custom_estimation/views/admin/categories/category_modal_content.php
        $this->load->view(CUSTOM_ESTIMATION_MODULE_NAME . '/admin/categories/category_modal_content', $data);
    }


    /**
     * Delete a product category
     * @param mixed $id category id
     */
    public function delete_category($id)
    {
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
        if (!$id || !is_numeric($id)) {
            redirect(admin_url('custom_estimation/categories'));
        }
        $response = $this->categories_model->delete_category($id);
        if ($response == true) {
            set_alert('success', _l('custom_category_deleted_successfully'));
        } else {
            // The model might return an array with an error message if deletion is blocked
            if(is_array($response) && isset($response['error'])){
                 set_alert('warning', $response['error']);
            } else {
                 set_alert('warning', _l('custom_category_delete_fail'));
            }
        }
        redirect(admin_url('custom_estimation/categories'));
    }
    
}