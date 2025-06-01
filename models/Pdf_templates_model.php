<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pdf_templates_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a single PDF template by ID
     * @param  mixed $id template id
     * @return object|null
     */
    public function get_template($id)
    {
        if (!is_numeric($id)) {
            return null;
        }
        $this->db->where('id', $id);
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)->row();
    }

    /**
     * Get a single PDF template by slug
     * @param  string $slug template slug
     * @return object|null
     */
    public function get_template_by_slug($slug)
    {
        if (empty($slug)) {
            return null;
        }
        $this->db->where('slug', $slug);
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)->row();
    }


    /**
     * Get all PDF templates
     * @return array
     */
    public function get_all_templates()
    {
        $this->db->order_by('name', 'asc');
        return $this->db->get(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)->result_array();
    }

    /**
     * Get the default PDF template
     * @return object|null
     */
    public function get_default_template()
    {
        $this->db->where('is_default', 1);
        $template = $this->db->get(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)->row();

        if (!$template) {
            // If no template is explicitly marked as default, fetch the oldest one (by ID)
            // This ensures there's always a fallback if no default is set.
            $this->db->order_by('id', 'asc');
            $this->db->limit(1);
            $template = $this->db->get(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)->row();
        }
        return $template;
    }


    /**
     * Add a new PDF template
     * @param array $data template data
     * @return mixed Insert ID or false
     */
    public function add_template($data)
    {
        // Data from form: name, slug, template_html, template_css, is_default
        $template_data = [
            'name'           => $data['name'],
            'slug'           => isset($data['slug']) && !empty($data['slug']) ? slug_it($data['slug']) : slug_it($data['name']), // Ensure slug is created and sanitized
            'template_html'  => isset($data['template_html']) ? $data['template_html'] : '',
            'template_css'   => isset($data['template_css']) ? $data['template_css'] : '',
            'is_default'     => isset($data['is_default']) && $data['is_default'] == '1' ? 1 : 0,
            // created_at and updated_at are handled by DB defaults/triggers if set up, or manually here
        ];

        if (empty($template_data['name']) || empty($template_data['slug'])) {
            return false; // Name and slug are essential
        }

        // Ensure slug is unique before inserting
        $this->db->where('slug', $template_data['slug']);
        $exists = $this->db->get(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)->row();
        if ($exists) {
            // Handle slug conflict, e.g., append a number or return an error
            // For now, let's assume the controller/validation handles this or we let DB unique constraint catch it.
            // If you want to auto-increment slug, that logic would go here.
        }


        if ($template_data['is_default'] == 1) {
            // Unset any other template as default
            $this->db->update(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES, ['is_default' => 0]);
        }

        $this->db->set('created_at', 'NOW()', false); // Explicitly set created_at
        $this->db->set('updated_at', 'NOW()', false); // Explicitly set updated_at on creation
        $this->db->insert(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES, $template_data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('New PDF Template Added [ID: ' . $insert_id . ', Name: ' . $template_data['name'] . ']');
            return $insert_id;
        }
        return false;
    }

    /**
     * Update an existing PDF template
     * @param  array $data template data
     * @param  mixed $id   template id
     * @return boolean
     */
    public function update_template($data, $id)
    {
        $template_data = [
            'name'           => $data['name'],
            'slug'           => isset($data['slug']) && !empty($data['slug']) ? slug_it($data['slug']) : slug_it($data['name']),
            'template_html'  => isset($data['template_html']) ? $data['template_html'] : '',
            'template_css'   => isset($data['template_css']) ? $data['template_css'] : '',
            'is_default'     => isset($data['is_default']) && $data['is_default'] == '1' ? 1 : 0,
        ];

        if (empty($template_data['name']) || empty($template_data['slug'])) {
            return false;
        }

        // Ensure slug is unique if it's being changed
        $this->db->where('slug', $template_data['slug']);
        $this->db->where('id !=', $id);
        $exists = $this->db->get(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES)->row();
        if ($exists) {
            // Handle slug conflict
            return false; // Or set an error message
        }

        if ($template_data['is_default'] == 1) {
            // Unset other defaults
            $this->db->where('id !=', $id);
            $this->db->update(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES, ['is_default' => 0]);
        }

        $this->db->set('updated_at', 'NOW()', false); // Explicitly set updated_at
        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES, $template_data);

        if ($this->db->affected_rows() > 0) {
            log_activity('PDF Template Updated [ID: ' . $id . ', Name: ' . $template_data['name'] . ']');
            return true;
        }
        // If 'is_default' was changed for other rows but not this one, affected_rows might be 0 for this update.
        // However, if the 'is_default' status of THIS template changed, affected_rows would be > 0.
        // The logic for unsetting other defaults is separate.
        // To correctly report success if only other rows were affected by is_default change:
        // We might need to check the original 'is_default' status of this $id before the update.
        // For simplicity, if any data for *this* template changed, or if its 'is_default' status was part of the $template_data that changed it, it's a success.
        // If only other rows' 'is_default' changed, and this template's data was identical, affected_rows on *this* update is 0.
        // A more robust check might involve querying the 'is_default' status after all operations.
        // However, Perfex typically relies on affected_rows() for the primary update.
        return false;
    }

    /**
     * Delete a PDF template
     * @param  mixed $id template id
     * @return boolean|array Returns true on success, or an array with error message on failure
     */
    public function delete_template($id)
    {
        $template = $this->get_template($id);
        if ($template && $template->is_default == 1) {
            return ['error' => _l('cannot_delete_default_pdf_template')];
        }

        $this->db->where('id', $id);
        $this->db->delete(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES);

        if ($this->db->affected_rows() > 0) {
            log_activity('PDF Template Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * Set a template as default
     * @param  mixed $id template id
     * @return boolean
     */
    public function set_default_template($id)
    {
        // Unset other defaults
        $this->db->update(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES, ['is_default' => 0]);
        $unsetting_affected = $this->db->affected_rows(); // Could be 0 if no other was default

        // Set the new default
        $this->db->where('id', $id);
        $this->db->update(CUSTOM_ESTIMATION_TABLE_PDF_TEMPLATES, ['is_default' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        $setting_affected = $this->db->affected_rows();

        // Return true if the intended template was successfully marked as default.
        return $setting_affected > 0;
    }
}