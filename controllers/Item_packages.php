<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Item_packages extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_estimation/item_packages_model');
        $this->load->model('custom_estimation/products_model'); // For selecting products to add to a package

        // Permission check for managing packages
        // Users need 'manage_packages' or at least 'view' for the index page.
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view') && !has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_packages')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
    }

    /**
     * List all item packages
     */
    public function index()
    {
        // Further check if user can only view but not manage, they should still see the list.
        // More granular checks are done in specific methods like package() or delete_package().
        if ($this->input->is_ajax_request()) {
            // This will call 'table.php' view for item packages
            $this->app->get_table_data(module_views_path(CUSTOM_ESTIMATION_MODULE_NAME, 'admin/item_packages/table'));
        }
        $data['title'] = _l('custom_estimation_submenu_item_packages');
        // You will create this view: modules/custom_estimation/views/admin/item_packages/manage.php
        $this->load->view('admin/item_packages/manage', $data);
    }

    /**
     * Add or edit an item package
     * @param string $id package id
     */
    public function package($id = '')
    {
        // Strict check for manage_packages permission for add/edit operations
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_packages')) {
             access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            
            if ($id == '') { // Adding new package
                // Although covered by the initial check, good to be explicit for 'create' capability
                if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create') && !has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_packages')) {
                    access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
                }
                $new_id = $this->item_packages_model->add_package($data);
                if ($new_id) {
                    set_alert('success', _l('item_package_added_successfully'));
                    redirect(admin_url('custom_estimation/item_packages/package/' . $new_id));
                } else {
                    set_alert('danger', _l('item_package_add_fail'));
                    // It's better to repopulate the form with submitted data on failure
                    // For now, redirecting to a blank form.
                    redirect(admin_url('custom_estimation/item_packages/package'));
                }
            } else { // Updating existing package
                 if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit') && !has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_packages')) {
                    access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
                }
                $success = $this->item_packages_model->update_package($data, $id);
                if ($success) {
                    set_alert('success', _l('item_package_updated_successfully'));
                } else {
                    set_alert('warning', _l('item_package_update_fail'));
                }
                redirect(admin_url('custom_estimation/item_packages/package/' . $id));
            }
        }

        // Prepare data for the add/edit view
        if ($id == '') {
            $data['title'] = _l('new_item_package');
        } else {
            $data['package'] = $this->item_packages_model->get_item_package($id);
            if (!$data['package']) {
                set_alert('danger', _l('item_package_not_found'));
                redirect(admin_url('custom_estimation/item_packages'));
            }
            // Get items associated with this package for the edit form
            $data['package_items'] = $this->item_packages_model->get_package_items_for_editing($id);
            $data['title'] = _l('edit_item_package') . ' - ' . $data['package']->name;
        }

        // Fetch all products to populate the "select product to add" dropdown in the package form
        $all_products = $this->products_model->get_all_products();
        $data['products'] = [];
        if ($all_products) {
            foreach ($all_products as $product) {
                $data['products'][] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'description' => isset($product['description']) ? $product['description'] : '',
                    'long_description' => isset($product['long_description']) ? $product['long_description'] : '',
                    'unit_price' => isset($product['unit_price']) ? $product['unit_price'] : '0.00',
                ];
            }
        }
        
        // You will create this view file: modules/custom_estimation/views/admin/item_packages/package_form.php
        $this->load->view('admin/item_packages/package_form', $data);
    }

    /**
     * Delete an item package
     * @param mixed $id package id
     */
    public function delete_package($id)
    {
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete') && !has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_packages')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
        if (!$id) {
            redirect(admin_url('custom_estimation/item_packages'));
        }

        $response = $this->item_packages_model->delete_package($id);
        if ($response == true) {
            set_alert('success', _l('item_package_deleted_successfully'));
        } else {
            set_alert('warning', _l('item_package_delete_fail'));
        }
        redirect(admin_url('custom_estimation/item_packages'));
    }
}
