<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .CodeMirror {
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        min-height: 250px; /* Adjust as needed */
    }
    .pdf-template-form-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    .editor-column {
        flex: 1;
        min-width: 300px; /* Ensure editors don't get too squished */
    }
    .preview-column {
        flex: 1;
        min-width: 300px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
    }
    #live_preview_iframe {
        width: 100%;
        height: 500px; /* Adjust as needed */
        border: none;
    }
    .placeholder-inserter {
        margin-bottom: 15px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <?php echo form_open(admin_url('custom_estimation/pdf_templates/template' . (isset($template) ? '/' . $template->id : '')), ['id' => 'pdf-template-form']); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($template) ? $template->name : ''); ?>
                                <?php echo render_input('name', 'pdf_template_name', $value, 'text', ['required' => true]); ?>
                            </div>
                            <div class="col-md-6">
                                <?php $value = (isset($template) ? $template->slug : ''); ?>
                                <?php echo render_input('slug', 'slug', $value, 'text', ['required' => true, 'data-slug-from' => 'name']); ?>
                                <p class="text-muted">
                                    <?php echo _l('pdf_template_slug_info'); ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="checkbox checkbox-primary mtop15">
                            <input type="checkbox" name="is_default" id="is_default" <?php if(isset($template) && $template->is_default == 1){echo 'checked';} ?> value="1">
                            <label for="is_default"><?php echo _l('pdf_template_is_default'); ?></label>
                        </div>
                        <p class="text-muted"><?php echo _l('note_only_one_template_can_be_default'); ?></p>
                        <hr/>

                        <div class="placeholder-inserter form-group">
                            <label for="placeholder_select" class="control-label"><?php echo _l('insert_placeholder'); // Add lang string ?></label>
                            <select id="placeholder_select" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <optgroup label="<?php echo _l('custom_estimate'); // Add lang string: Estimate Details ?>">
                                    <option value="{estimate_id}">Estimate ID</option>
                                    <option value="{estimate_number}">Estimate Number</option>
                                    <option value="{estimate_subject}">Estimate Subject</option>
                                    <option value="{estimate_datecreated}">Date Created</option>
                                    <option value="{estimate_valid_until}">Valid Until</option>
                                    <option value="{estimate_status_name}">Status Name</option>
                                    <option value="{estimate_subtotal}">Subtotal</option>
                                    <option value="{estimate_total_discount_amount}">Discount Amount</option>
                                    <option value="{estimate_total_discount_percentage}">Discount %</option>
                                    <option value="{estimate_total_tax}">Total Tax</option>
                                    <option value="{estimate_total}">Grand Total</option>
                                    <option value="{estimate_notes}">Notes</option>
                                    <option value="{estimate_terms_and_conditions}">Terms & Conditions</option>
                                    <option value="{estimate_public_url}">Public View URL</option>
                                </optgroup>
                                <optgroup label="<?php echo _l('company_details'); // Add lang string ?>">
                                    <option value="{company_name}">Company Name</option>
                                    <option value="{company_address}">Company Address</option>
                                    <option value="{company_city}">Company City</option>
                                    <option value="{company_logo_url}">Company Logo URL</option>
                                    {/* Add more company placeholders */}
                                </optgroup>
                                <optgroup label="<?php echo _l('client_details'); // Add lang string: Client/Lead Details ?>">
                                    <option value="{client_name}">Client/Lead Name</option>
                                    <option value="{client_company}">Client/Lead Company</option>
                                    <option value="{client_address}">Client/Lead Address</option>
                                    {/* Add more client placeholders */}
                                </optgroup>
                                <optgroup label="<?php echo _l('estimate_items'); // Add lang string: Item Loop (for use within loop structure) ?>">
                                    <option value="{item_description}">Item Description</option>
                                    <option value="{item_long_description}">Item Long Description</option>
                                    <option value="{item_quantity}">Item Quantity</option>
                                    <option value="{item_unit}">Item Unit</option>
                                    <option value="{item_unit_price}">Item Unit Price</option>
                                    <option value="{item_total_amount}">Item Total Amount</option>
                                </optgroup>
                                <option value="">START ITEMS LOOP Comment</option>
                                <option value="">END ITEMS LOOP Comment</option>
                            </select>
                            <p class="text-muted">
                                <?php echo _l('pdf_template_placeholders_info'); ?>
                            </p>
                        </div>

                        <div class="pdf-template-form-container">
                            <div class="editor-column">
                                <p class="bold mtop15"><?php echo _l('pdf_template_html_content'); ?></p>
                                <textarea name="template_html" id="template_html_editor" class="form-control codemirror" rows="25" data-mode="htmlmixed">
                                    <?php echo isset($template) ? htmlspecialchars($template->template_html) : ''; ?>
                                </textarea>
                            </div>
                            <div class="editor-column">
                                <p class="bold mtop15"><?php echo _l('pdf_template_css_content'); ?></p>
                                <textarea name="template_css" id="template_css_editor" class="form-control codemirror" rows="25" data-mode="css">
                                    <?php echo isset($template) ? htmlspecialchars($template->template_css) : ''; ?>
                                </textarea>
                            </div>
                        </div>
                        
                        <hr />
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="font-medium"><?php echo _l('live_preview'); // Add lang string ?> (Browser Rendered)</h5>
                                <div class="preview-column">
                                    <iframe id="live_preview_iframe"></iframe>
                                </div>
                            </div>
                        </div>


                        <div class="btn-bottom-toolbar text-right mtop15">
                            <button type="submit" class="btn btn-primary" data-loading-text="<?php echo _l('wait_text'); ?>">
                                <?php echo _l('submit'); ?>
                            </button>
                            <a href="<?php echo admin_url('custom_estimation/pdf_templates'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        var htmlEditor, cssEditor;

        // Initialize CodeMirror for HTML editor
        if(typeof(CodeMirror) !== 'undefined'){
            htmlEditor = CodeMirror.fromTextArea(document.getElementById('template_html_editor'), {
                lineNumbers: true,
                mode: 'htmlmixed',
                theme: 'default', 
                lineWrapping: true,
                autoCloseTags: true,
                matchBrackets: true,
            });
            htmlEditor.on('change', updateLivePreview);

            // Initialize CodeMirror for CSS editor
            cssEditor = CodeMirror.fromTextArea(document.getElementById('template_css_editor'), {
                lineNumbers: true,
                mode: 'css',
                theme: 'default',
                lineWrapping: true,
                autoCloseBrackets: true,
                matchBrackets: true,
            });
            cssEditor.on('change', updateLivePreview);

            // Initial preview update
            updateLivePreview();

        } else {
            console.warn('CodeMirror library not found. Textareas will be plain.');
            // Fallback if CodeMirror not loaded - basic preview update
            $('#template_html_editor, #template_css_editor').on('keyup', updateLivePreview);
            updateLivePreview(); // Initial call
        }

        // Placeholder Inserter
        $('#placeholder_select').on('change', function() {
            var placeholder = $(this).val();
            if (placeholder && htmlEditor) { // Prefer inserting into HTML editor
                htmlEditor.replaceSelection(placeholder);
                htmlEditor.focus();
            } else if (placeholder && cssEditor && cssEditor.hasFocus()){ // Or CSS if it's focused
                 cssEditor.replaceSelection(placeholder);
                 cssEditor.focus();
            }
            $(this).selectpicker('val', ''); // Reset dropdown
        });

        // Live Preview Function
        function updateLivePreview() {
            var html_content = htmlEditor ? htmlEditor.getValue() : $('#template_html_editor').val();
            var css_content = cssEditor ? cssEditor.getValue() : $('#template_css_editor').val();

            // Basic client-side placeholder replacement for preview only
            var preview_html = html_content;
            preview_html = preview_html.replace(/{estimate_subject}/g, 'Sample Estimate Subject');
            preview_html = preview_html.replace(/{client_name}/g, 'John Doe (Sample Client)');
            preview_html = preview_html.replace(/{estimate_total}/g, '<?php echo app_format_money(1250.75, get_base_currency()); ?>');
            // Add more simple replacements as needed for key placeholders

            // Item loop basic replacement for preview - just shows the item template once with sample data
            const itemLoopRegex = /(.*?)/s;
            const itemMatch = preview_html.match(itemLoopRegex);
            if (itemMatch && itemMatch[1]) {
                let itemTemplate = itemMatch[1];
                let sampleItem = itemTemplate.replace(/{item_description}/g, 'Sample Item 1')
                                           .replace(/{item_quantity}/g, '2')
                                           .replace(/{item_unit}/g, 'pcs')
                                           .replace(/{item_unit_price}/g, '<?php echo app_format_money(100, get_base_currency()); ?>')
                                           .replace(/{item_total_amount}/g, '<?php echo app_format_money(200, get_base_currency()); ?>');
                sampleItem += itemTemplate.replace(/{item_description}/g, 'Sample Item 2')
                                           .replace(/{item_quantity}/g, '3')
                                           .replace(/{item_unit}/g, 'hrs')
                                           .replace(/{item_unit_price}/g, '<?php echo app_format_money(50, get_base_currency()); ?>')
                                           .replace(/{item_total_amount}/g, '<?php echo app_format_money(150, get_base_currency()); ?>');
                preview_html = preview_html.replace(itemLoopRegex, sampleItem);
            }


            var iframe_doc = document.getElementById('live_preview_iframe').contentWindow.document;
            iframe_doc.open();
            iframe_doc.write('<html><head><style>' + css_content + '</style></head><body>' + preview_html + '</body></html>');
            iframe_doc.close();
        }


        // Form validation
        appValidateForm($('#pdf-template-form'), {
            name: 'required',
            slug: 'required',
            template_html: 'required'
        });

        // Slug generation from name
        $('input[name="name"]').on('blur', function() {
            var $slug = $('input[name="slug"]');
            if ($slug.val() === '') {
                $slug.val(slugify($(this).val()));
            }
        });
    });
</script>
</body>
</html>