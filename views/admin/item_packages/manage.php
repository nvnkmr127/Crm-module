<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <?php
                    // Show 'New Item Package' button if user has 'manage_packages' permission
                    // or the general 'create' permission for this module.
                    if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'manage_packages') || has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create')) { ?>
                    <a href="<?php echo admin_url('custom_estimation/item_packages/package'); ?>" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_item_package'); ?>
                    </a>
                    <?php } ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        // Define table headers for item packages
                        $table_data = [
                            ['name' => _l('item_package_name'), 'th_attrs' => ['class' => 'toggleable', 'id' => 'th-package-name']],
                            ['name' => _l('item_package_description'), 'th_attrs' => ['class' => 'toggleable', 'id' => 'th-package-description']],
                            // Example: You might later add a column to show the number of items in each package.
                            // ['name' => _l('package_item_count'), 'th_attrs' => ['class' => 'toggleable', 'id' => 'th-package-item-count'], 'as_sortable' => false],
                            ['name' => _l('options'), 'th_attrs' => ['class' => 'text-right']]
                        ];
                        render_datatable($table_data, 'custom-item-packages', [], [
                            'data-last-order-identifier' => 'item-packages', // Unique identifier for table state saving
                            'data-default-order'         => get_table_last_order('item-packages'),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        // Initialize the server-side datatable for item packages
        initDataTable('.table-custom-item-packages', admin_url + 'custom_estimation/item_packages', undefined, undefined, undefined, [0, 'asc']); // Sort by package name (column 0) by default
    });
</script>
</body>
</html>
