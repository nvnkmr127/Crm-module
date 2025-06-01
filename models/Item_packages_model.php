<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Item_packages_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a single item package by ID
     * @param  mixed $id package id
     * @return object|null
     */
    public function get_item_package($id)
    {
        if (!is_numeric($id)) {
            return null;
        }
        $this->db->where('id', $id);
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES)->row();
    }

    /**
     * Get all item packages
     * @return array
     */
    public function get_all_item_packages()
    {
        $this->db->order_by('name', 'asc');
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES)->result_array();
    }

    /**
     * Get all items for a specific package, formatted for loading into an estimate.
     * @param  int $package_id
     * @return array
     */
    public function get_items_for_package($package_id)
    {
        if (!is_numeric($package_id)) {
            return [];
        }

        $this->db->select(
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.product_id, ' .
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.default_quantity as quantity, ' .
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.default_description, ' .
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.default_long_description, ' .
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.default_unit_price, ' .
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.is_complimentary, ' .
            // New fields from tblcustom_item_package_items
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.unit, ' .
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.formula, ' .
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.dimension_l, ' .
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.dimension_w, ' .
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.dimension_h, ' .
            // Fields from tblcustom_products for fallback
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.name as product_name, ' .
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.description as product_master_description, ' .
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.long_description as product_master_long_description, ' .
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.unit_price as product_master_unit_price, ' .
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.unit as product_master_unit, ' .
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.formula as product_master_formula, ' .
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.dimensions_length as product_master_dim_l, ' .
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.dimensions_width as product_master_dim_w, ' .
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.dimensions_height as product_master_dim_h'
        );
        $this->db->from(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS);
        $this->db->join(CUSTOM_ESTIMATION_TABLE_PRODUCTS, CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.id = ' . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.product_id', 'left');
        $this->db->where(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.package_id', $package_id);
        $this->db->order_by(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.item_order', 'asc');
        
        $package_items_raw = $this->db->get()->result_array();
        
        $items_for_estimate = [];
        foreach($package_items_raw as $pkg_item) {
            $items_for_estimate[] = [
                'product_id'          => $pkg_item['product_id'],
                'description'         => !empty($pkg_item['default_description']) ? $pkg_item['default_description'] : $pkg_item['product_name'],
                'long_description'    => !empty($pkg_item['default_long_description']) ? $pkg_item['default_long_description'] : $pkg_item['product_master_long_description'],
                'quantity'            => $pkg_item['quantity'], // Already aliased from default_quantity
                'unit_price'          => isset($pkg_item['default_unit_price']) && $pkg_item['default_unit_price'] !== null ? $pkg_item['default_unit_price'] : $pkg_item['product_master_unit_price'],
                'is_complimentary'    => $pkg_item['is_complimentary'],
                // Populate new fields, falling back to master product if package item's field is NULL or not set
                'unit'                => isset($pkg_item['unit']) && !empty($pkg_item['unit']) ? $pkg_item['unit'] : $pkg_item['product_master_unit'],
                'formula'             => isset($pkg_item['formula']) && !empty($pkg_item['formula']) ? $pkg_item['formula'] : $pkg_item['product_master_formula'],
                'dimension_l'         => isset($pkg_item['dimension_l']) && $pkg_item['dimension_l'] !== null ? $pkg_item['dimension_l'] : $pkg_item['product_master_dim_l'],
                'dimension_w'         => isset($pkg_item['dimension_w']) && $pkg_item['dimension_w'] !== null ? $pkg_item['dimension_w'] : $pkg_item['product_master_dim_w'],
                'dimension_h'         => isset($pkg_item['dimension_h']) && $pkg_item['dimension_h'] !== null ? $pkg_item['dimension_h'] : $pkg_item['product_master_dim_h'],
            ];
        }
        return $items_for_estimate;
    }

    /**
     * Get all items for a specific package, formatted for editing the package itself.
     * @param  int $package_id
     * @return array
     */
    public function get_package_items_for_editing($package_id)
    {
        if (!is_numeric($package_id)) {
            return [];
        }
        $this->db->select(
            CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.*, ' . 
            CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.name as product_name' 
        );
        $this->db->from(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS);
        $this->db->join(CUSTOM_ESTIMATION_TABLE_PRODUCTS, CUSTOM_ESTIMATION_TABLE_PRODUCTS . '.id = ' . CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.product_id', 'left');
        $this->db->where(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.package_id', $package_id);
        $this->db->order_by(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS . '.item_order', 'asc');
        
        $results = $this->db->get()->result_array();
        $formatted_results = [];
        foreach($results as $row){
            $formatted_row = $row;
            // For the form, we use the direct 'default_description', 'default_quantity', 'default_unit_price'
            // and the new fields like 'unit', 'formula', 'dimension_l' etc.
            $formatted_row['description'] = $row['default_description']; // Keep as is, view can handle placeholder or show product_name
            $formatted_row['quantity'] = $row['default_quantity'];
            $formatted_row['unit_price'] = $row['default_unit_price']; 
            // New fields are already selected by '.*' from CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS
            $formatted_results[] = $formatted_row;
        }
        return $formatted_results;
    }

    /**
     * Add a new item package
     * @param array $data package data, including $data['items']
     * @return mixed Insert ID or false
     */
    public function add_package($data)
    {
        $package_main_data = [
            'name'        => $data['name'], 
            'description' => isset($data['description']) ? $data['description'] : null,
        ];

        if (empty($package_main_data['name'])) {
            return false;
        }

        $this->db->insert(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES, $package_main_data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item_order => $item) {
                    $item_to_add = [
                        'product_id'               => $item['product_id'],
                        'default_quantity'         => $item['default_quantity'],
                        'default_description'      => isset($item['default_description']) ? $item['default_description'] : null,
                        'default_long_description' => isset($item['default_long_description']) ? $item['default_long_description'] : null,
                        'default_unit_price'       => (isset($item['default_unit_price']) && $item['default_unit_price'] !== '') ? $item['default_unit_price'] : null,
                        'item_order'               => $item_order, 
                        'is_complimentary'         => (isset($item['is_complimentary']) && $item['is_complimentary'] == '1') ? 1 : 0,
                        // New fields for package items
                        'unit'                     => isset($item['unit']) ? $item['unit'] : null,
                        'unit_type'                => isset($item['unit_type']) ? $item['unit_type'] : null, // Assuming this comes from form
                        'package_item_range'       => isset($item['package_item_range']) ? $item['package_item_range'] : null, // Assuming this comes from form
                        'material'                 => isset($item['material']) ? $item['material'] : null, // Assuming this comes from form
                        'formula'                  => isset($item['formula']) ? $item['formula'] : null,
                        'dimension_l'              => (isset($item['dimension_l']) && $item['dimension_l'] !== '') ? $item['dimension_l'] : null,
                        'dimension_w'              => (isset($item['dimension_w']) && $item['dimension_w'] !== '') ? $item['dimension_w'] : null,
                        'dimension_h'              => (isset($item['dimension_h']) && $item['dimension_h'] !== '') ? $item['dimension_h'] : null,
                    ];
                    $this->add_item_to_package($insert_id, $item_to_add);
                }
            }
            log_activity('New Item Package Added [ID: ' . $insert_id . ', Name: ' . $package_main_data['name'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update an existing item package
     * @param  array $data package data, including $data['items']
     * @param  mixed $id   package id
     * @return boolean
     */
    public function update_package($data, $id)
    {
        $package_main_data = [
            'name'        => $data['name'],
            'description' => isset($data['description']) ? $data['description'] : null,
        ];

        if (empty($package_main_data['name'])) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES, $package_main_data);
        $affected_rows = $this->db->affected_rows();

        $this->db->where('package_id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS);

        $items_changed = false;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item_order => $item) {
                 $item_to_add = [
                    'product_id'               => $item['product_id'],
                    'default_quantity'         => $item['default_quantity'],
                    'default_description'      => isset($item['default_description']) ? $item['default_description'] : null,
                    'default_long_description' => isset($item['default_long_description']) ? $item['default_long_description'] : null,
                    'default_unit_price'       => (isset($item['default_unit_price']) && $item['default_unit_price'] !== '') ? $item['default_unit_price'] : null,
                    'item_order'               => $item_order,
                    'is_complimentary'         => (isset($item['is_complimentary']) && $item['is_complimentary'] == '1') ? 1 : 0,
                    // New fields
                    'unit'                     => isset($item['unit']) ? $item['unit'] : null,
                    'unit_type'                => isset($item['unit_type']) ? $item['unit_type'] : null,
                    'package_item_range'       => isset($item['package_item_range']) ? $item['package_item_range'] : null,
                    'material'                 => isset($item['material']) ? $item['material'] : null,
                    'formula'                  => isset($item['formula']) ? $item['formula'] : null,
                    'dimension_l'              => (isset($item['dimension_l']) && $item['dimension_l'] !== '') ? $item['dimension_l'] : null,
                    'dimension_w'              => (isset($item['dimension_w']) && $item['dimension_w'] !== '') ? $item['dimension_w'] : null,
                    'dimension_h'              => (isset($item['dimension_h']) && $item['dimension_h'] !== '') ? $item['dimension_h'] : null,
                ];
                if ($this->add_item_to_package($id, $item_to_add)) {
                    $items_changed = true;
                }
            }
        }
        
        if ($affected_rows > 0 || $items_changed) {
            log_activity('Item Package Updated [ID: ' . $id . ', Name: ' . $package_main_data['name'] . ']');
            return true;
        }
        return false;
    }

    public function delete_package($id)
    {
        $this->db->where('package_id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS);

        $this->db->where('id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGES);

        if ($this->db->affected_rows() > 0) {
            log_activity('Item Package Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * Add an item to a specific package
     * @param int $package_id
     * @param array $item_data (expects keys like product_id, default_quantity etc. including new fields)
     * @return mixed Insert ID or false
     */
    public function add_item_to_package($package_id, $item_data)
    {
        if (!isset($item_data['product_id']) || !isset($item_data['default_quantity'])) {
            return false; 
        }

        $data_to_insert = [
            'package_id'               => $package_id,
            'product_id'               => $item_data['product_id'],
            'default_quantity'         => $item_data['default_quantity'],
            'default_description'      => isset($item_data['default_description']) ? $item_data['default_description'] : null,
            'default_long_description' => isset($item_data['default_long_description']) ? $item_data['default_long_description'] : null,
            'default_unit_price'       => (isset($item_data['default_unit_price']) && $item_data['default_unit_price'] !== '') ? $item_data['default_unit_price'] : null,
            'item_order'               => isset($item_data['item_order']) ? (int)$item_data['item_order'] : 0,
            'is_complimentary'         => (isset($item_data['is_complimentary']) && $item_data['is_complimentary'] == '1') ? 1 : 0,
            // New fields to save
            'unit'                     => isset($item_data['unit']) ? $item_data['unit'] : null,
            'unit_type'                => isset($item_data['unit_type']) ? $item_data['unit_type'] : null,
            'package_item_range'       => isset($item_data['package_item_range']) ? $item_data['package_item_range'] : null,
            'material'                 => isset($item_data['material']) ? $item_data['material'] : null,
            'formula'                  => isset($item_data['formula']) ? $item_data['formula'] : null,
            'dimension_l'              => (isset($item_data['dimension_l']) && $item_data['dimension_l'] !== '') ? $item_data['dimension_l'] : null,
            'dimension_w'              => (isset($item_data['dimension_w']) && $item_data['dimension_w'] !== '') ? $item_data['dimension_w'] : null,
            'dimension_h'              => (isset($item_data['dimension_h']) && $item_data['dimension_h'] !== '') ? $item_data['dimension_h'] : null,
        ];

        $this->db->insert(CUSTOM_ESTIMATION_TABLE_ITEM_PACKAGE_ITEMS, $data_to_insert);
        return $this->db->insert_id();
    }
}
