<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Custom_estimation extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_estimation/estimates_model'); 
    }

    /**
     * Default method for the module.
     * Can be a simple welcome, or redirect to a default section like reports or estimates list.
     */
    public function index()
    {
        // Option 1: Redirect to the new reports page
        redirect(admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/reports'));

        // Option 2: Redirect to the estimates list page
        // redirect(admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/estimates'));
        
        // Option 3: Show a very simple welcome/info page (if you create a view for it)
        // if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) {
        //     access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        // }
        // $data['title'] = _l('custom_estimation_menu_title');
        // $this->load->view('admin/module_home', $data); // You'd need to create module_home.php
    }

    /**
     * Displays the estimation reports/dashboard for the module.
     */
    public function reports()
    {
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
        
        $data['title'] = _l('custom_estimation_submenu_reports'); // Use new lang string

        // Load the widget view content into a variable
        // The widget view 'estimation_summary_widget.php' itself loads the Estimates_model and gets data.
        $data['widget_content'] = $this->load->view(CUSTOM_ESTIMATION_MODULE_NAME . '/widgets/estimation_summary_widget', [], true);

        // Load the module_dashboard view which acts as a container for the widget content
        $this->load->view('admin/module_dashboard', $data);
    }


    /**
     * Example: Settings page for the module (if you implement it)
     */
    // public function settings()
    // {
    //     if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) { 
    //         access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
    //     }

    //     if ($this->input->post()) {
    //         if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit')) { 
    //             access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
    //         }
    //         // $success = $this->settings_model->update($this->input->post()); 
    //         // if ($success) {
    //         //     set_alert('success', _l('settings_updated'));
    //         // }
    //         // redirect(admin_url('custom_estimation/settings'), 'refresh');
    //     }

    //     $data['title'] = _l('custom_estimation_submenu_settings');
    //     // $this->load->view('admin/settings/manage', $data); 
    // }
}