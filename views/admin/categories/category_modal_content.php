<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="categoryModalLabel"><?php echo $title; ?></h4>
</div>
<?php echo form_open(admin_url('custom_estimation/categories/category/' . (isset($category) ? $category->id : '')), ['id' => 'category-form']); ?>
<div class="modal-body">
    <div class="alert alert-danger BOLD_ERROR_PLACEHOLDER hide"></div> {/* Placeholder for AJAX errors */}
    <div class="row">
        <div class="col-md-12">
            <?php
            $value = (isset($category) ? $category->name : '');
            echo render_input('name', 'category_name', $value, 'text', ['required' => true]); 
            ?>
            <?php
            $value = (isset($category) ? $category->description : '');
            echo render_textarea('description', 'custom_product_description', $value); 
            ?>
            <?php if (isset($category)) { ?>
                <input type="hidden" name="id" value="<?php echo $category->id; ?>">
            <?php } ?>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <button type="submit" class="btn btn-primary" data-loading-text="<?php echo _l('wait_text'); ?>"><?php echo _l('submit'); ?></button>
</div>
<?php echo form_close(); ?>

<script>
    $(function() {
        appValidateForm($('#category-form'), {
            name: 'required'
        }, manage_category_form_handler_ajax); // Changed handler name

        function manage_category_form_handler_ajax(form) {
            var $form = $(form);
            var $submitButton = $form.find('button[type="submit"]');
            var $errorPlaceholder = $form.find('.BOLD_ERROR_PLACEHOLDER');

            // Disable button and show loading text
            $submitButton.prop('disabled', true).html($submitButton.data('loading-text'));
            $errorPlaceholder.addClass('hide').html(''); // Clear previous errors

            var formData = $form.serialize(); // Get form data
            var actionUrl = $form.attr('action');

            $.ajax({
                type: 'POST',
                url: actionUrl,
                data: formData,
                dataType: 'json', // Expect JSON response from controller
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        $('#category_modal').modal('hide');
                        // Assuming you have a DataTable for categories that needs refreshing
                        if (typeof $('.table-custom-estimation-categories').DataTable === 'function') {
                            $('.table-custom-estimation-categories').DataTable().ajax.reload();
                        } else {
                            // Fallback if DataTable not initialized or different table class
                            window.location.reload(); // Simple reload if table refresh is complex
                        }
                    } else {
                        // Display error message from server response
                        $errorPlaceholder.removeClass('hide').html(response.message || '<?php echo _l("custom_category_add_fail"); ?>');
                        $submitButton.prop('disabled', false).html('<?php echo _l("submit"); ?>');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Handle AJAX errors (e.g., 500 Internal Server Error)
                    var errorMsg = '<?php echo _l("custom_category_add_fail"); ?>';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMsg = jqXHR.responseJSON.message;
                    } else if (jqXHR.responseText) {
                        // Try to show a snippet of the server error if it's HTML (like a PHP error page)
                        // This is for debugging, might not be user-friendly
                        // errorMsg += '<br><small>Server response: ' + jqXHR.responseText.substring(0, 200) + '...</small>';
                         errorMsg = 'A server error occurred (Code: ' + jqXHR.status + '). Please check server logs or contact support.';
                    }
                    $errorPlaceholder.removeClass('hide').html(errorMsg);
                    $submitButton.prop('disabled', false).html('<?php echo _l("submit"); ?>');
                }
            });

            return false; // Prevent traditional form submission
        }
    });
</script>
```
**Key Changes in the Canvas:**

1.  **Error Placeholder:** Added `<div class="alert alert-danger BOLD_ERROR_PLACEHOLDER hide"></div>` inside the modal body to display any error messages.
2.  **JavaScript Handler Changed:**
    * The `appValidateForm` callback is now `manage_category_form_handler_ajax`.
    * **AJAX Submission:** This new handler function now performs an AJAX `POST` request to the form's action URL.
    * **Button State:** It disables the submit button and shows its `data-loading-text` at the start of the AJAX request.
    * **Success Callback:**
        * If `response.success` is true (you'll need to ensure your controller's `category()` method returns JSON like `echo json_encode(['success' => true, 'message' => 'Saved!']);`), it shows a success message using `alert_float`, hides the modal, and attempts to reload the DataTables for categories (assuming your categories table has the class `table-custom-estimation-categories`). If DataTables isn't available, it falls back to a full page reload.
    * **Error Callback:**
        * If `response.success` is false, or if the AJAX request itself fails (e.g., a 500 error from the server), it displays an error message in the `BOLD_ERROR_PLACEHOLDER` div.
        * It re-enables the submit button.
    * **`return false;`**: This is crucial at the end of `manage_category_form_handler_ajax` to prevent the default traditional form submission, as we are now handling it via AJAX.

**Important Next Steps for You:**

1.  **Controller Modification (`Categories.php` - Canvas ID `perfex_custom_estimation_categories_controller_v2`):**
    * Your `category($id = '')` method in `Categories.php` currently ends with `redirect(...)`. **This needs to change.**
    * Since the form is now submitted via AJAX and expects a JSON response, your controller method should `echo json_encode(...)` and then `die()` or `exit()`, instead of redirecting.
    * **Example of controller response:**
        ```php
        // In Categories.php -> category() method, after processing:
        if ($new_id) { // Or $success for update
            $message = _l('custom_category_added_successfully'); // Or updated
            echo json_encode(['success' => true, 'message' => $message, 'id' => $new_id]);
        } else {
            $message = _l('custom_category_add_fail'); // Or update fail
            echo json_encode(['success' => false, 'message' => $message]);
        }
        die(); // Important to stop further execution
        ```

2.  **Find and Fix the Server-Side PHP Error:**
    * This JavaScript change makes the UI more responsive to errors, but **it does not fix the underlying reason why saving is failing.**
    * You still need to find the PHP error message from your server logs (PHP error log or CodeIgniter logs in `application/logs/`) that occurs when the AJAX POST request is made to `admin/custom_estimation/categories/category/...`. This error message will tell you what's crashing in your `Categories.php` controller or `Categories_model.php`.

By implementing these changes, the modal should no longer get "stuck." If a server error occurs, the JavaScript will now attempt to display an error message and re-enable the save button. This will make it easier to see that a server-side issue is happening, which you can then diagnose using your server lo