<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_estimation/products_model');
        $this->load->model('custom_estimation/product_categories_model');
        // Ensure permissions are checked for product management
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'view')) { // Or a more specific 'manage_products' permission
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path(CUSTOM_ESTIMATION_MODULE_NAME, 'admin/products/table'));
        }
        $data['title'] = _l('custom_estimation_submenu_products');
        $this->load->view('admin/products/manage', $data);
    }

    public function product($id = '')
    {
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create') && $id == '') {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'edit') && $id != '') {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }

        if ($this->input->post()) {
            $data = $this->input->post();
            
            // Handle image upload
            if (isset($_FILES['product_image']['name']) && $_FILES['product_image']['name'] != '') {
                $upload_path = FCPATH . 'uploads/custom_estimation_module/products/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                $config['upload_path']   = $upload_path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png';
                $config['max_size']      = '2048'; // 2MB
                $config['encrypt_name']  = TRUE;

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('product_image')) {
                    $upload_data = $this->upload->data();
                    $data['image'] = $upload_data['file_name'];

                    // If updating, remove old image
                    if ($id != '' && isset($data['old_image']) && !empty($data['old_image'])) {
                        $old_image_path = $upload_path . $data['old_image'];
                        if (file_exists($old_image_path)) {
                            @unlink($old_image_path);
                        }
                    }
                } else {
                    set_alert('warning', $this->upload->display_errors());
                    // Don't necessarily redirect, allow user to fix other fields
                }
            }
            unset($data['old_image']); // Remove helper field

            if (isset($data['remove_image']) && $data['remove_image'] == '1' && $id != '') {
                $product_to_edit = $this->products_model->get_product($id);
                if ($product_to_edit && !empty($product_to_edit->image)) {
                    $image_path = FCPATH . 'uploads/custom_estimation_module/products/' . $product_to_edit->image;
                     if (file_exists($image_path)) {
                        @unlink($image_path);
                    }
                    $data['image'] = null; // Explicitly set to null for update
                }
            }
            unset($data['remove_image']);


            if ($id == '') {
                $new_id = $this->products_model->add_product($data);
                if ($new_id) {
                    set_alert('success', _l('custom_product_added_successfully'));
                    redirect(admin_url('custom_estimation/products/product/' . $new_id));
                } else {
                    set_alert('danger', _l('custom_product_add_fail'));
                    redirect(admin_url('custom_estimation/products/product'));
                }
            } else {
                $success = $this->products_model->update_product($data, $id);
                if ($success) {
                    set_alert('success', _l('custom_product_updated_successfully'));
                } else {
                    set_alert('warning', _l('custom_product_update_fail'));
                }
                redirect(admin_url('custom_estimation/products/product/' . $id));
            }
        }

        if ($id == '') {
            $data['title'] = _l('new_product');
        } else {
            $data['product'] = $this->products_model->get_product($id);
            if (!$data['product']) {
                set_alert('danger', _l('custom_product_not_found'));
                redirect(admin_url('custom_estimation/products'));
            }
            $data['title'] = _l('edit_product') . ' - ' . $data['product']->name;
        }
        
        $data['categories'] = $this->product_categories_model->get_all_categories();
        $data['bodyclass'] = 'product-edit';
        $this->load->view('admin/products/product', $data);
    }

    public function delete_product($id)
    {
        if (!has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete')) {
            access_denied(CUSTOM_ESTIMATION_MODULE_NAME);
        }
        if (!$id || !is_numeric($id)) {
            redirect(admin_url('custom_estimation/products'));
        }
        $response = $this->products_model->delete_product($id);

        if (is_array($response) && isset($response['error'])) {
             set_alert('warning', $response['error']);
        } elseif ($response == true) {
            set_alert('success', _l('custom_product_deleted_successfully'));
        } else {
            set_alert('warning', _l('custom_product_delete_fail'));
        }
        redirect(admin_url('custom_estimation/products'));
    }
}