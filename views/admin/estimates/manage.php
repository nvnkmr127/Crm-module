<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <?php if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create')) { ?>
                    <a href="<?php echo admin_url('custom_estimation/estimates/estimate'); ?>" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_custom_estimate'); ?>
                    </a>
                    <?php } ?>
                </div>

                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        // UI Columns: 0:Subject, 1:Lead, 2:Status, 3:Total, 4:Date, 5:Valid Until, 6:Options
                        $table_data = [
                            _l('custom_estimate_subject'),      // UI Col 0
                            _l('custom_estimate_lead'),         // UI Col 1
                            _l('custom_estimate_status'),       // UI Col 2
                            _l('estimate_total'),               // UI Col 3
                            _l('custom_estimate_date'),         // UI Col 4
                            _l('custom_estimate_valid_until'),  // UI Col 5
                            ['name' => _l('options'), 'th_attrs' => ['class' => 'text-right']] // UI Col 6
                        ];
                        render_datatable($table_data, 'custom-estimates', [], [
                            'data-last-order-identifier' => 'custom-estimates',
                            'data-default-order'         => get_table_last_order('custom-estimates'), 
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
        // UI Columns: 0:Subject, 1:Lead, 2:Status, 3:Total, 4:Date, 5:Valid Until, 6:Options
        
        // For extreme simplification test:
        // Only UI Column 0 (Subject) and UI Column 4 (Date Created) will be sortable/searchable from server.
        // All others are marked as non-sortable and non-searchable.
        var notSortableColumns = [1, 2, 3, 5, 6]; 
        var notSearchableColumns = [1, 2, 3, 5, 6]; 

        initDataTable('.table-custom-estimates', 
            admin_url + 'custom_estimation/estimates', 
            notSortableColumns, 
            notSearchableColumns, 
            undefined,          
            [4, 'desc']         // Default sort: UI column 4 (Date Created) descending.
                                // This should map to $aColumns[4] in table.php
        ); 
    });
</script>
</body>
</html>
