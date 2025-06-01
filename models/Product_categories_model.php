<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_categories_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a single category by ID
     * @param  mixed $id category id
     * @return object|null
     */
    public function get_category($id)
    {
        if (!is_numeric($id)) {
            return null;
        }
        $this->db->where('id', $id);
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES)->row();
    }

    /**
     * Get all product categories
     * @return array
     */
    public function get_all_categories()
    {
        $this->db->order_by('name', 'asc');
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES)->result_array();
    }

    /**
     * Add a new product category
     * @param array $data category data
     * @return mixed Insert ID or false
     */
    public function add_category($data)
    {
        $category_data = [
            'name'        => $data['name'],
            'description' => isset($data['description']) ? $data['description'] : null,
            // created_at is handled by DB default
        ];

        if (empty($category_data['name'])) {
            return false; // Name is required
        }

        $this->db->insert(CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES, $category_data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New Product Category Added [ID: ' . $insert_id . ', Name: ' . $category_data['name'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update an existing product category
     * @param  array $data category data
     * @param  mixed $id   category id
     * @return boolean
     */
    public function update_category($data, $id)
    {
        $category_data = [
            'name'        => $data['name'],
            'description' => isset($data['description']) ? $data['description'] : null,
        ];

        if (empty($category_data['name'])) {
            return false; // Name is required
        }

        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES, $category_data);

        if ($this->db->affected_rows() > 0) {
            log_activity('Product Category Updated [ID: ' . $id . ', Name: ' . $category_data['name'] . ']');
            return true;
        }
        return false; // Return false if no rows affected (e.g., data was the same or error)
    }

    /**
     * Delete a product category
     * @param  mixed $id category id
     * @return boolean
     */
    public function delete_category($id)
    {
        // Check if category is used by any products
        $this->db->where('category_id', $id);
        $count = $this->db->count_all_results(CUSTOM_ESTIMATION_TABLE_PRODUCTS);

        if ($count > 0) {
            // Category is in use, prevent deletion
            return ['error' => _l('custom_category_delete_fail')]; 
        }

        $this->db->where('id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_PRODUCT_CATEGORIES);

        if ($this->db->affected_rows() > 0) {
            log_activity('Product Category Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
}
