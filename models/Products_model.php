<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a single product by ID
     * @param  mixed $id product id
     * @return object|null
     */
    public function get_product($id)
    {
        if (!is_numeric($id)) {
            return null;
        }
        $this->db->where('id', $id);
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_PRODUCTS)->row();
    }

    /**
     * Get all products, optionally filtered by category
     * @param  mixed $category_id (optional)
     * @return array
     */
    public function get_all_products($category_id = null)
    {
        if ($category_id && is_numeric($category_id)) {
            $this->db->where('category_id', $category_id);
        }
        $this->db->order_by('name', 'asc');
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_PRODUCTS)->result_array();
    }

    /**
     * Add a new product
     * @param array $data product data
     * @return mixed Insert ID or false
     */
    public function add_product($data)
    {
        $product_data = [
            'name'                => $data['name'],
            'category_id'         => (isset($data['category_id']) && !empty($data['category_id'])) ? $data['category_id'] : null,
            'description'         => isset($data['description']) ? $data['description'] : null,
            'long_description'    => isset($data['long_description']) ? $data['long_description'] : null,
            'unit_price'          => isset($data['unit_price']) ? $data['unit_price'] : 0.00,
            'unit'                => isset($data['unit']) ? $data['unit'] : null,
            'unit_type'           => isset($data['unit_type']) ? $data['unit_type'] : null,
            'product_range'       => isset($data['product_range']) ? $data['product_range'] : null,
            'material'            => isset($data['material']) ? $data['material'] : null,
            'formula'             => isset($data['formula']) ? $data['formula'] : 'nos',
            'dimensions_length'   => (isset($data['dimensions_length']) && $data['dimensions_length'] !== '') ? $data['dimensions_length'] : null,
            'dimensions_width'    => (isset($data['dimensions_width']) && $data['dimensions_width'] !== '') ? $data['dimensions_width'] : null,
            'dimensions_height'   => (isset($data['dimensions_height']) && $data['dimensions_height'] !== '') ? $data['dimensions_height'] : null,
            'image'               => isset($data['image']) ? $data['image'] : null,
            // created_at is default
        ];

        if (empty($product_data['name'])) {
            return false;
        }

        $this->db->insert(CUSTOM_ESTIMATION_TABLE_PRODUCTS, $product_data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Product Added [ID: ' . $insert_id . ', Name: ' . $product_data['name'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update an existing product
     * @param  array $data product data
     * @param  mixed $id   product id
     * @return boolean
     */
    public function update_product($data, $id)
    {
        $product_data = [
            'name'                => $data['name'],
            'category_id'         => (isset($data['category_id']) && !empty($data['category_id'])) ? $data['category_id'] : null,
            'description'         => isset($data['description']) ? $data['description'] : null,
            'long_description'    => isset($data['long_description']) ? $data['long_description'] : null,
            'unit_price'          => isset($data['unit_price']) ? $data['unit_price'] : 0.00,
            'unit'                => isset($data['unit']) ? $data['unit'] : null,
            'unit_type'           => isset($data['unit_type']) ? $data['unit_type'] : null,
            'product_range'       => isset($data['product_range']) ? $data['product_range'] : null,
            'material'            => isset($data['material']) ? $data['material'] : null,
            'formula'             => isset($data['formula']) ? $data['formula'] : 'nos',
            'dimensions_length'   => (isset($data['dimensions_length']) && $data['dimensions_length'] !== '') ? $data['dimensions_length'] : null,
            'dimensions_width'    => (isset($data['dimensions_width']) && $data['dimensions_width'] !== '') ? $data['dimensions_width'] : null,
            'dimensions_height'   => (isset($data['dimensions_height']) && $data['dimensions_height'] !== '') ? $data['dimensions_height'] : null,
        ];
        // Handle image separately - only update if a new image is provided or if 'remove_image' is set
        if (isset($data['image'])) { // This would be set by the upload handler
            $product_data['image'] = $data['image'];
        } elseif (isset($data['remove_image']) && $data['remove_image'] == '1') {
            $product_data['image'] = null;
        }


        if (empty($product_data['name'])) {
            return false;
        }
        // updated_at is handled by DB on update
        
        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_PRODUCTS, $product_data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Product Updated [ID: ' . $id . ', Name: ' . $product_data['name'] . ']');
            return true;
        }
        return false; 
    }

    /**
     * Delete a product
     * @param  mixed $id product id
     * @return boolean
     */
    public function delete_product($id)
    {
        // First, check if the product is used in any estimate items or package items
        $this->db->where('product_id', $id);
        $count_estimate_items = $this->db->count_all_results(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS);

        $this->db->where('product_id', $id);
        $count_package_items = $this->db->count_all_results(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS);

        if ($count_estimate_items > 0 || $count_package_items > 0) {
            return ['error' => _l('custom_product_used_in_estimates_cannot_delete')]; 
        }

        // If not used, proceed with deletion
        $product = $this->get_product($id);
        if ($product && !empty($product->image)) {
            $image_path = FCPATH . 'uploads/custom_estimation_module/products/' . $product->image;
            if (file_exists($image_path)) {
                @unlink($image_path);
            }
        }

        $this->db->where('id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_PRODUCTS);

        if ($this->db->affected_rows() > 0) {
            log_activity('Product Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
}