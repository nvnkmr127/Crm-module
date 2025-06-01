<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Estimates_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        // It's good practice to load models you'll frequently use here,
        // or ensure they are loaded before methods that need them.
        // $this->load->model('taxes_model'); // Loaded on-demand in update_estimate_totals
    }

    /**
     * Get a single estimate by ID
     * @param  mixed $id estimate id
     * @param  boolean $include_items Whether to include estimate items
     * @return object|null
     */
    public function get_estimate($id, $include_items = true)
    {
        if (!is_numeric($id)) {
            return null;
        }
        $this->db->where('id', $id);
        $estimate = $this->db->get(CUSTOM_ESTIMATION_TABLE_ESTIMATES)->row();

        if ($estimate && $include_items) {
            $this->db->where('estimate_id', $id);
            $this->db->order_by('item_order', 'asc');
            $estimate_items_raw = $this->db->get(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS)->result_array();
            
            $estimate->items = [];
            foreach($estimate_items_raw as $item_raw){
                $item_obj = (object) $item_raw; 
                $item_obj->unit = $item_raw['unit'] ?? '';
                $item_obj->formula = $item_raw['formula'] ?? 'nos';
                $item_obj->custom_dim_length = $item_raw['custom_dim_length'] ?? '';
                $item_obj->custom_dim_width = $item_raw['custom_dim_width'] ?? '';
                $item_obj->custom_dim_height = $item_raw['custom_dim_height'] ?? '';
                $item_obj->material = $item_raw['material'] ?? '';
                $item_obj->range = $item_raw['range'] ?? '';
                $estimate->items[] = $item_obj;
            }
        }
        return $estimate;
    }

    /**
     * Add a new estimate
     * @param array $data estimate data (should include 'items' array)
     * @return mixed Insert ID or false
     */
    public function add_estimate($data)
    {
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }
        
        if (isset($data['removed_items'])) { unset($data['removed_items']);}

        $estimate_main_data = [
            'lead_id'                   => (isset($data['lead_id']) && !empty($data['lead_id'])) ? $data['lead_id'] : null,
            'client_id'                 => null, // Explicitly null as per "Leads only"
            'project_id'                => null, // Explicitly null
            'estimate_number'           => $data['estimate_number'] ?? null, 
            'status'                    => $data['status'] ?? 'draft',
            'datecreated'               => date('Y-m-d H:i:s'),
            'valid_until'               => (isset($data['valid_until']) && !empty($data['valid_until'])) ? to_sql_date($data['valid_until']) : null,
            'notes'                     => $data['notes'] ?? null,
            'terms_and_conditions'      => $data['terms_and_conditions'] ?? null,
            'admin_notes'               => $data['admin_notes'] ?? null,
            'created_by'                => get_staff_user_id(),
            'hash'                      => app_generate_hash(),
            'pdf_template_slug'         => $data['pdf_template_slug'] ?? null,
            // New fields for totals section
            'adjustment'                => $data['adjustment'] ?? 0.00,
            'tax_id_1'                  => (isset($data['tax_id_1']) && !empty($data['tax_id_1'])) ? $data['tax_id_1'] : null,
            'tax_id_2'                  => (isset($data['tax_id_2']) && !empty($data['tax_id_2'])) ? $data['tax_id_2'] : null,
            'discount_type'             => $data['discount_type'] ?? 'percentage', // Ensure discount_type is saved
            'total_discount_percentage' => $data['total_discount_percentage'] ?? 0.00,
            'total_discount_amount'     => $data['total_discount_amount'] ?? 0.00,
        ];
        // Subtotal, total_tax, total will be calculated by update_estimate_totals

        $this->db->insert(CUSTOM_ESTIMATION_TABLE_ESTIMATES, $estimate_main_data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            if (count($items) > 0) {
                foreach ($items as $item_order => $item) {
                    $item_data = [
                        'estimate_id'         => $insert_id,
                        'product_id'          => (isset($item['product_id']) && !empty($item['product_id'])) ? $item['product_id'] : null,
                        'description'         => $item['description'] ?? '',
                        'long_description'    => $item['long_description'] ?? null,
                        'quantity'            => $item['quantity'] ?? 0,
                        'unit_price'          => $item['unit_price'] ?? 0.00,
                        'item_order'          => $item_order,
                        // 'discount_amount' is usually not set per item if overall discount is used, but kept for flexibility
                        'discount_amount'     => $item['discount_amount'] ?? 0.00, 
                        'discount_percentage' => $item['discount_percentage'] ?? 0.00,
                        'is_complimentary'    => (isset($item['is_complimentary']) && $item['is_complimentary'] == '1') ? 1 : 0,
                        'unit'                => $item['unit'] ?? null,
                        'formula'             => $item['formula'] ?? 'nos',
                        'custom_dim_length'   => (isset($item['custom_dim_length']) && $item['custom_dim_length'] !== '') ? $item['custom_dim_length'] : null,
                        'custom_dim_width'    => (isset($item['custom_dim_width']) && $item['custom_dim_width'] !== '') ? $item['custom_dim_width'] : null,
                        'custom_dim_height'   => (isset($item['custom_dim_height']) && $item['custom_dim_height'] !== '') ? $item['custom_dim_height'] : null,
                        'material'            => $item['material'] ?? null, 
                        'range'               => $item['range'] ?? null,    
                    ];
                    $this->db->insert(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS, $item_data);
                }
            }
            // Pass the full $data array which includes discount and new tax/adjustment fields from the form
            $this->update_estimate_totals($insert_id, $data); 

            log_activity('New Custom Estimate Added [ID: ' . $insert_id . ']');
            hooks()->do_action('after_custom_estimate_added', $insert_id);
            return $insert_id;
        }
        return false;
    }

    /**
     * Update an existing estimate
     * @param  array $data estimate data
     * @param  mixed $id   estimate id
     * @return boolean
     */
    public function update_estimate($data, $id)
    {
        $items = [];
        if (isset($data['items']) && is_array($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }
        $removed_items = [];
        if (isset($data['removed_items']) && is_array($data['removed_items'])) {
            $removed_items = $data['removed_items'];
            unset($data['removed_items']);
        }
        
        $estimate_main_data = [
            'lead_id'                   => (isset($data['lead_id']) && !empty($data['lead_id'])) ? $data['lead_id'] : null,
            'client_id'                 => null, // Explicitly null
            'project_id'                => null, // Explicitly null
            'estimate_number'           => $data['estimate_number'] ?? null,
            'status'                    => $data['status'] ?? 'draft',
            'valid_until'               => (isset($data['valid_until']) && !empty($data['valid_until'])) ? to_sql_date($data['valid_until']) : null,
            'notes'                     => $data['notes'] ?? null,
            'terms_and_conditions'      => $data['terms_and_conditions'] ?? null,
            'admin_notes'               => $data['admin_notes'] ?? null,
            'pdf_template_slug'         => $data['pdf_template_slug'] ?? null,
            // New fields for totals section
            'adjustment'                => $data['adjustment'] ?? 0.00,
            'tax_id_1'                  => (isset($data['tax_id_1']) && !empty($data['tax_id_1'])) ? $data['tax_id_1'] : null,
            'tax_id_2'                  => (isset($data['tax_id_2']) && !empty($data['tax_id_2'])) ? $data['tax_id_2'] : null,
            'discount_type'             => $data['discount_type'] ?? 'percentage',
            'total_discount_percentage' => $data['total_discount_percentage'] ?? 0.00,
            'total_discount_amount'     => $data['total_discount_amount'] ?? 0.00,
        ];
        // Subtotal, total_tax, total will be calculated by update_estimate_totals

        $affected_rows = 0;

        // Delete explicitly removed items
        if (count($removed_items) > 0) {
            $this->db->where('estimate_id', $id);
            $this->db->where_in('id', $removed_items);
            $this->db->delete(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS);
            $affected_rows += $this->db->affected_rows();
        }

        // Update or Add items
        // A common pattern is to delete all existing items (not in removed_items list) and re-add them
        // This simplifies handling order changes and updates vs inserts.
        $this->db->where('estimate_id', $id);
        if (count($removed_items) > 0) { 
            $this->db->where_not_in('id', $removed_items); // Don't delete already removed ones again
        }
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS);
        // $affected_rows += $this->db->affected_rows(); // Not strictly needed for this count

        $items_changed_or_added = false;
        if (count($items) > 0) {
            foreach ($items as $item_order => $item) {
                 $item_data = [
                    'estimate_id'         => $id,
                    'product_id'          => (isset($item['product_id']) && !empty($item['product_id'])) ? $item['product_id'] : null,
                    'description'         => $item['description'] ?? '',
                    'long_description'    => $item['long_description'] ?? null,
                    'quantity'            => $item['quantity'] ?? 0,
                    'unit_price'          => $item['unit_price'] ?? 0.00,
                    'item_order'          => $item_order,
                    'discount_percentage' => $item['discount_percentage'] ?? 0.00,
                    'is_complimentary'    => (isset($item['is_complimentary']) && $item['is_complimentary'] == '1') ? 1 : 0,
                    'unit'                => $item['unit'] ?? null,
                    'formula'             => $item['formula'] ?? 'nos',
                    'custom_dim_length'   => (isset($item['custom_dim_length']) && $item['custom_dim_length'] !== '') ? $item['custom_dim_length'] : null,
                    'custom_dim_width'    => (isset($item['custom_dim_width']) && $item['custom_dim_width'] !== '') ? $item['custom_dim_width'] : null,
                    'custom_dim_height'   => (isset($item['custom_dim_height']) && $item['custom_dim_height'] !== '') ? $item['custom_dim_height'] : null,
                    'material'            => $item['material'] ?? null, 
                    'range'               => $item['range'] ?? null,    
                ];
                $this->db->insert(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS, $item_data);
                if($this->db->insert_id()){
                    $items_changed_or_added = true;
                }
            }
        }
        
        // Update main estimate data
        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_ESTIMATES, $estimate_main_data);
        $affected_rows += $this->db->affected_rows();
        
        // Pass the full $data array which includes discount and new tax/adjustment fields from the form
        $totals_updated = $this->update_estimate_totals($id, $data); 

        if ($affected_rows > 0 || $items_changed_or_added || $totals_updated) {
            log_activity('Custom Estimate Updated [ID: ' . $id . ']');
            hooks()->do_action('after_custom_estimate_updated', $id);
            return true;
        }
        return false;
    }
    
    public function update_estimate_totals($id, $main_estimate_data = []) 
    {
        $estimate = $this->get_estimate($id, true); 
        if (!$estimate) { return false; }

        $subtotal = 0;
        $items = $estimate->items; 

        if (is_array($items) || is_object($items)) {
            foreach ($items as $item_obj) { 
                $item = (array) $item_obj; 
                $is_complimentary = $item['is_complimentary'] ?? 0;
                $quantity = (float)($item['quantity'] ?? 0);
                $unit_price = (float)($item['unit_price'] ?? 0);
                $discount_percentage = (float)($item['discount_percentage'] ?? 0);
                
                if ($is_complimentary == 0) { 
                    $item_total_before_discount = $quantity * $unit_price;
                    $item_discount_val = 0;
                    if ($discount_percentage > 0 && $discount_percentage <= 100) {
                        $item_discount_val = ($item_total_before_discount * $discount_percentage) / 100;
                    } 
                    $subtotal += ($item_total_before_discount - $item_discount_val);
                }
            }
        }

        // Overall Discount
        // Values for discount_type, total_discount_percentage, total_discount_amount should come from $main_estimate_data (form post)
        // or fallback to existing $estimate values if not in $main_estimate_data (e.g. when totals are recalculated internally)
        $discount_type = $main_estimate_data['discount_type'] ?? $estimate->discount_type;
        $overall_discount_percentage_input = (float)($main_estimate_data['total_discount_percentage'] ?? $estimate->total_discount_percentage);
        $overall_discount_amount_input = (float)($main_estimate_data['total_discount_amount'] ?? $estimate->total_discount_amount);

        $total_discount_calculated_value = 0; // The actual monetary value of the discount
        $final_overall_discount_percentage = 0;
        $final_overall_discount_amount = 0;

        if ($discount_type === 'percentage') {
            if ($overall_discount_percentage_input > 0 && $overall_discount_percentage_input <= 100) {
                $total_discount_calculated_value = ($subtotal * $overall_discount_percentage_input) / 100;
            }
            $final_overall_discount_percentage = $overall_discount_percentage_input;
            $final_overall_discount_amount = $total_discount_calculated_value; // Store the calculated amount
        } elseif ($discount_type === 'fixed_amount') {
            $total_discount_calculated_value = $overall_discount_amount_input;
            $final_overall_discount_amount = $overall_discount_amount_input;
            if ($subtotal > 0) { // Avoid division by zero
                $final_overall_discount_percentage = ($total_discount_calculated_value / $subtotal) * 100;
            } else {
                $final_overall_discount_percentage = 0;
            }
        }
        
        $subtotal_after_discount = $subtotal - $total_discount_calculated_value;

        // Calculate Taxes
        if(!class_exists('taxes_model')) { // Ensure model is loaded
            $this->load->model('taxes_model');
        }
        $total_tax_amount = 0;
        
        $tax_id_1_input = $main_estimate_data['tax_id_1'] ?? $estimate->tax_id_1;
        $tax_id_2_input = $main_estimate_data['tax_id_2'] ?? $estimate->tax_id_2;

        if ($tax_id_1_input && !empty($tax_id_1_input)) {
            $tax1 = $this->taxes_model->get($tax_id_1_input);
            if ($tax1) {
                $tax_amount = ($subtotal_after_discount * (float)$tax1->taxrate) / 100;
                $total_tax_amount += $tax_amount;
            }
        }
        if ($tax_id_2_input && !empty($tax_id_2_input)) {
            $tax2 = $this->taxes_model->get($tax_id_2_input);
            if ($tax2) {
                $tax_amount = ($subtotal_after_discount * (float)$tax2->taxrate) / 100;
                $total_tax_amount += $tax_amount;
            }
        }
        
        // Adjustment
        $adjustment_input = (float)($main_estimate_data['adjustment'] ?? ($estimate->adjustment ?? 0.00));
        
        $final_total = $subtotal_after_discount + $total_tax_amount + $adjustment_input; 
        
        $update_totals_data_for_db = [
            'subtotal'                  => $subtotal,
            'discount_type'             => $discount_type,
            'total_discount_amount'     => $final_overall_discount_amount,
            'total_discount_percentage' => $final_overall_discount_percentage,
            'adjustment'                => $adjustment_input,
            'tax_id_1'                  => $tax_id_1_input ?: null, // Store NULL if empty
            'tax_id_2'                  => $tax_id_2_input ?: null, // Store NULL if empty
            'total_tax'                 => $total_tax_amount,
            'total'                     => $final_total,
        ];
        
        // Apply filter before saving
        $update_totals_data_for_db = hooks()->apply_filters('before_custom_estimate_total_calculation', $update_totals_data_for_db, $id);

        // If the filter modified any of the core components, we might need to recalculate the final total
        // For simplicity, we assume the filter might change amounts but the structure for total calculation remains.
        // Or, the filter is responsible for ensuring 'total' is correct if it modifies components.
        // A more robust filter handling would re-calculate total from filtered components if needed.
        // For now, let's assume the filter either doesn't change components affecting the sum, or updates 'total' itself.


        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_ESTIMATES, $update_totals_data_for_db);
        $affected_rows = $this->db->affected_rows();
        
        if($affected_rows > 0) {
            hooks()->do_action('after_custom_estimate_totals_updated', $id);
            return true;
        }
        // Return true even if no rows affected, if the data to be set was identical
        // This indicates the totals are "correct" even if no DB write happened.
        // However, for now, stick to affected_rows to indicate a change was persisted.
        return false; 
    }

    public function delete_estimate($id)
    {
        // Hook before deletion
        hooks()->do_action('before_delete_custom_estimate', $id);

        $this->db->where('estimate_id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS);
        
        $this->db->where('id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_ESTIMATES);
        
        if ($this->db->affected_rows() > 0) {
            log_activity('Custom Estimate Deleted [ID: ' . $id . ']');
            hooks()->do_action('after_delete_custom_estimate', $id);
            return true;
        }
        return false;
    }

    public function get_estimate_statuses() { 
        $statuses = [
            ['id' => 'draft', 'name' => _l('estimate_status_draft'), 'color' => 'text-muted label-default', 'order' => 1],
            ['id' => 'sent', 'name' => _l('estimate_status_sent'), 'color' => 'text-info label-info', 'order' => 2],
            ['id' => 'approved', 'name' => _l('estimate_status_approved'), 'color' => 'text-success label-success', 'order' => 3],
            ['id' => 'declined', 'name' => _l('estimate_status_declined'), 'color' => 'text-danger label-danger', 'order' => 4],
            ['id' => 'expired', 'name' => _l('estimate_status_expired'), 'color' => 'text-warning label-warning', 'order' => 5],
        ];
        return hooks()->apply_filters('custom_estimate_statuses', $statuses);
     }
     
    public function get_estimates_for_lead($lead_id)
    {
        if (!is_numeric($lead_id)) {
            return [];
        }
        $this->db->select(CUSTOM_ESTIMATION_TABLE_ESTIMATES . '.*'); 
        $this->db->where(CUSTOM_ESTIMATION_TABLE_ESTIMATES . '.lead_id', $lead_id);
        $this->db->order_by(CUSTOM_ESTIMATION_TABLE_ESTIMATES . '.datecreated', 'desc');
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_ESTIMATES)->result_array();
    }

    // Method for public accept/decline actions
    public function update_estimate_status_and_approval($id, $data)
    {
        $old_data = $this->get_estimate($id, false); // Get current status
        $old_status = $old_data ? $old_data->status : null;

        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_ESTIMATES, $data); // $data includes new status and approval fields

        if ($this->db->affected_rows() > 0) {
            $new_status = $data['status'];
            log_activity('Custom Estimate Public Action [ID: ' . $id . ', Status Changed To: ' . $new_status . ']');
            
            // Specific hooks for accept/decline could be here based on $new_status
            if ($new_status == 'approved' && $old_status != 'approved') {
                 hooks()->do_action('custom_estimate_accepted', ['estimate_id' => $id, 'acceptance_data' => $data]);
            } elseif ($new_status == 'declined' && $old_status != 'declined') {
                 hooks()->do_action('custom_estimate_declined', ['estimate_id' => $id]);
            }

            // General status change hook
            if ($old_status !== $new_status) {
                hooks()->do_action('custom_estimate_status_changed', [
                    'estimate_id' => $id, 
                    'old_status' => $old_status, 
                    'new_status' => $new_status,
                    'admin_action' => false // Assuming public actions are not admin_actions
                ]);
            }
            return true;
        }
        return false;
    }
    
    // Example method to be called from Admin Controller for status changes by staff
    public function update_status($id, $status) {
        $old_data = $this->get_estimate($id, false);
        $old_status = $old_data ? $old_data->status : null;

        if ($old_status === $status) {
            return false; // No change
        }

        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_ESTIMATES, ['status' => $status]);

        if ($this->db->affected_rows() > 0) {
            log_activity('Custom Estimate Status Changed by Admin [ID: ' . $id . ', From: ' . $old_status . ', To: ' . $status . ']');
            hooks()->do_action('custom_estimate_status_changed', [
                'estimate_id' => $id, 
                'old_status' => $old_status, 
                'new_status' => $status,
                'admin_action' => true
            ]);
            return true;
        }
        return false;
    }

    // --- Dashboard Related Methods (Placeholders - Implement actual logic as needed) ---
    public function get_dashboard_summary() {
        $statuses = $this->get_estimate_statuses();
        $summary = [];
        foreach ($statuses as $status) {
            $this->db->where('status', $status['id']);
            $total = $this->db->count_all_results(CUSTOM_ESTIMATION_TABLE_ESTIMATES);
            $summary[] = [
                'status_id' => $status['id'],
                'name' => $status['name'],
                'color' => $status['color'], // Ensure your status array includes a CSS class or color code
                'total' => $total,
            ];
        }
        return $summary;
    }

}

