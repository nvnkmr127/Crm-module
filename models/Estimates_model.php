<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Estimates_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
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
                // Ensure all expected fields are present, even if null from DB
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
        
        if (isset($data['removed_items'])) { unset($data['removed_items']);} // Not used in add

        $estimate_main_data = [
            // 'subject' was removed as per user request
            'lead_id'                   => (isset($data['lead_id']) && !empty($data['lead_id'])) ? $data['lead_id'] : null,
            'client_id'                 => isset($data['client_id']) && !empty($data['client_id']) ? $data['client_id'] : null, 
            'project_id'                => isset($data['project_id']) && !empty($data['project_id']) ? $data['project_id'] : null, 
            'estimate_number'           => isset($data['estimate_number']) ? $data['estimate_number'] : null, 
            'status'                    => isset($data['status']) ? $data['status'] : 'draft',
            'datecreated'               => date('Y-m-d H:i:s'),
            'valid_until'               => (isset($data['valid_until']) && !empty($data['valid_until'])) ? to_sql_date($data['valid_until']) : null,
            // Subtotal, discount, tax, total will be calculated by update_estimate_totals
            'notes'                     => isset($data['notes']) ? $data['notes'] : null,
            'terms_and_conditions'      => isset($data['terms_and_conditions']) ? $data['terms_and_conditions'] : null,
            'admin_notes'               => isset($data['admin_notes']) ? $data['admin_notes'] : null,
            'created_by'                => get_staff_user_id(),
            'hash'                      => app_generate_hash(),
            'pdf_template_slug'         => isset($data['pdf_template_slug']) ? $data['pdf_template_slug'] : null, 
        ];


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
            $this->update_estimate_totals($insert_id, $data); 

            log_activity('New Custom Estimate Added [ID: ' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update an existing estimate
     * @param  array $data estimate data (should include 'items' array and optionally 'removed_items' array)
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
            // 'subject' removed
            'lead_id'                   => (isset($data['lead_id']) && !empty($data['lead_id'])) ? $data['lead_id'] : null,
            'client_id'                 => isset($data['client_id']) && !empty($data['client_id']) ? $data['client_id'] : null,
            'project_id'                => isset($data['project_id']) && !empty($data['project_id']) ? $data['project_id'] : null,
            'estimate_number'           => isset($data['estimate_number']) ? $data['estimate_number'] : null,
            'status'                    => isset($data['status']) ? $data['status'] : 'draft',
            'valid_until'               => (isset($data['valid_until']) && !empty($data['valid_until'])) ? to_sql_date($data['valid_until']) : null,
            'notes'                     => isset($data['notes']) ? $data['notes'] : null,
            'terms_and_conditions'      => isset($data['terms_and_conditions']) ? $data['terms_and_conditions'] : null,
            'admin_notes'               => isset($data['admin_notes']) ? $data['admin_notes'] : null,
            'pdf_template_slug'         => isset($data['pdf_template_slug']) ? $data['pdf_template_slug'] : null, 
        ];
         // Unset fields that will be calculated and saved by update_estimate_totals
        unset($estimate_main_data['subtotal']);
        unset($estimate_main_data['total_discount_amount']);
        unset($estimate_main_data['total_discount_percentage']);
        unset($estimate_main_data['total_tax']);
        unset($estimate_main_data['total']);


        if (count($removed_items) > 0) {
            $this->db->where('estimate_id', $id);
            $this->db->where_in('id', $removed_items);
            $this->db->delete(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS);
        }

        // Delete existing items for this estimate before re-adding/updating
        $this->db->where('estimate_id', $id);
        if (count($removed_items) > 0) { 
            $this->db->where_not_in('id', $removed_items);
        }
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS);


        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_ESTIMATES, $estimate_main_data);
        $affected_rows = $this->db->affected_rows();
        
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
                $items_changed_or_added = true;
            }
        }
        
        $this->update_estimate_totals($id, $data); 

        if ($affected_rows > 0 || $items_changed_or_added || count($removed_items) > 0) {
            log_activity('Custom Estimate Updated [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
    
    public function delete_estimate($id)
    {
        log_activity('Attempting to delete Custom Estimate [ID: ' . $id . ']');

        // Delete associated items first
        $this->db->where('estimate_id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_ESTIMATE_ITEMS);
        $items_deleted_count = $this->db->affected_rows();
        log_activity('Deleted ' . $items_deleted_count . ' items for Custom Estimate [ID: ' . $id . ']');

        // Then delete the main estimate
        $this->db->where('id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_ESTIMATES);
        $estimate_deleted_count = $this->db->affected_rows();

        if ($estimate_deleted_count > 0) {
            log_activity('Custom Estimate Deleted Successfully [ID: ' . $id . ']');
            return true;
        } else {
            log_activity('Failed to delete Custom Estimate or Estimate not found [ID: ' . $id . ']. Main estimate affected rows: ' . $estimate_deleted_count);
            return false;
        }
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
                $quantity = $item['quantity'] ?? 0;
                $unit_price = $item['unit_price'] ?? 0;
                $discount_percentage = $item['discount_percentage'] ?? 0;
                
                if ($is_complimentary == 0) { 
                    $item_total_before_discount = $quantity * $unit_price;
                    $item_discount_val = 0;
                    if ($discount_percentage > 0) {
                        $item_discount_val = ($item_total_before_discount * $discount_percentage) / 100;
                    } 
                    $subtotal += ($item_total_before_discount - $item_discount_val);
                }
            }
        }

        $discount_type = $main_estimate_data['discount_type'] ?? ($estimate->discount_type ?? 'percentage'); 
        $overall_discount_percentage = 0;
        $overall_discount_amount = 0;

        if ($discount_type === 'percentage') {
            $overall_discount_percentage = $main_estimate_data['total_discount_percentage'] ?? ($estimate->total_discount_percentage ?? 0);
        } else { 
            $overall_discount_amount = $main_estimate_data['total_discount_amount'] ?? ($estimate->total_discount_amount ?? 0);
        }

        $total_discount_calculated = 0;
        if ($discount_type === 'percentage' && $overall_discount_percentage > 0) {
            $total_discount_calculated = ($subtotal * $overall_discount_percentage) / 100;
            $overall_discount_amount = $total_discount_calculated; 
        } elseif ($discount_type === 'fixed_amount' && $overall_discount_amount > 0) {
            $total_discount_calculated = $overall_discount_amount;
            if ($subtotal > 0) { 
                $overall_discount_percentage = ($overall_discount_amount / $subtotal) * 100;
            } else {
                $overall_discount_percentage = 0;
            }
        }
        
        $total = $subtotal - $total_discount_calculated; 
        
        $update_data = [
            'subtotal'                  => $subtotal,
            'total_discount_amount'     => $overall_discount_amount,
            'total_discount_percentage' => $overall_discount_percentage,
            'total'                     => $total,
        ];

        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_ESTIMATES, $update_data);
        return $this->db->affected_rows() > 0;
    }

    public function get_estimate_statuses() { 
        return hooks()->apply_filters('custom_estimate_statuses', [
            ['id' => 'draft', 'name' => _l('estimate_status_draft'), 'color' => 'text-muted', 'order' => 1],
            ['id' => 'sent', 'name' => _l('estimate_status_sent'), 'color' => 'text-info', 'order' => 2],
            ['id' => 'approved', 'name' => _l('estimate_status_approved'), 'color' => 'text-success', 'order' => 3],
            ['id' => 'declined', 'name' => _l('estimate_status_declined'), 'color' => 'text-danger', 'order' => 4],
            ['id' => 'expired', 'name' => _l('estimate_status_expired'), 'color' => 'text-warning', 'order' => 5],
        ]);
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

    public function count_total_estimates() { /* ... */ }
    public function count_estimates_by_status($status) { /* ... */ }
    public function get_total_value_by_status($status) { /* ... */ }
    public function get_total_value_all_estimates() { /* ... */ }
    public function get_dashboard_summary() { /* ... */ }
}
