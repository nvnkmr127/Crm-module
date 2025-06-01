<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <?php if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create')) { ?>
                    <a href="<?php echo admin_url('custom_estimation/products/product'); ?>" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_product'); ?>
                    </a>
                    <?php } ?>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        // UI Columns: 0:Image, 1:Name, 2:Category, 3:Description, 4:Price, 5:Options
                        $table_data = [
                            [ 
                                'name' => _l('custom_product_image'),
                                'th_attrs' => ['class' => 'toggleable', 'id' => 'th-image'],
                                'as_sortable' => false 
                            ], // UI Col 0
                            _l('custom_product_name'),       // UI Col 1
                            _l('custom_product_category'),    // UI Col 2
                            _l('custom_product_description'), // UI Col 3
                            [ 
                                'name' => _l('custom_product_unit_price'),
                                'th_attrs' => ['class' => 'toggleable text-right', 'id' => 'th-price']
                            ], // UI Col 4
                            ['name' => _l('options'), 'th_attrs' => ['class' => 'text-right']] // UI Col 5
                        ];

                        render_datatable($table_data, 'custom-estimation-products', [], [
                            'data-last-order-identifier' => 'products',
                            'data-default-order'         => get_table_last_order('products'),
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
    // UI Columns: 0:Image, 1:Name, 2:Category, 3:Description, 4:Price, 5:Options
    var notSortableDbColumns = [0, 5]; 
    var notSearchableDbColumns = [0, 5]; 

    initDataTable('.table-custom-estimation-products', 
        admin_url + 'custom_estimation/products', 
        notSortableDbColumns, 
        notSearchableDbColumns, 
        undefined,          
        [1, 'asc']          // Default sort: UI column 1 (Product Name)
    ); 
});
</script>
</body>
</html>
