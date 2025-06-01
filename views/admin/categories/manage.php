<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <?php if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create')) { ?>
                    <a href="#" onclick="new_category_modal(); return false;" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_category'); ?>
                    </a>
                    <?php } ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            _l('category_name'),
                            _l('custom_product_description'),
                            _l('options')
                        ];
                        render_datatable($table_data, 'custom-estimation-categories', [], [
                            'data-last-order-identifier' => 'categories',
                            'data-default-order'         => get_table_last_order('categories'),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="category_modal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php // Content will be loaded here by AJAX ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function() {
        // Initialize the server-side datatable
        // Corrected default sorting to column 0 (category name) ascending
        initDataTable('.table-custom-estimation-categories', admin_url + 'custom_estimation/categories', undefined, undefined, undefined, [0, 'asc']);
    });

    // Function to open the modal for a new category
    function new_category_modal() {
        $('#category_modal .modal-content').load(admin_url + 'custom_estimation/categories/category_modal_content', function() {
            $('#category_modal').modal('show');
            // appValidateForm($('#category-form'), {name: 'required'}); // Validation is in the modal content view
        });
    }

    // Function to open the modal for editing an existing category
    function edit_category_modal(category_id) {
        $('#category_modal .modal-content').load(admin_url + 'custom_estimation/categories/category_modal_content?id=' + category_id, function() {
            $('#category_modal').modal('show');
            // appValidateForm($('#category-form'), {name: 'required'}); // Validation is in the modal content view
        });
    }
</script>
</body>
</html>