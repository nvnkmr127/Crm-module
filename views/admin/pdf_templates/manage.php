<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-mb-2 sm:tw-mb-4">
                    <a href="<?php echo admin_url('custom_estimation/pdf_templates/template'); ?>" class="btn btn-primary">
                        <i class="fa-regular fa-plus tw-mr-1"></i>
                        <?php echo _l('new_pdf_template'); ?>
                    </a>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php
                        $table_data = [
                            _l('pdf_template_name'),
                            _l('pdf_template_is_default'),
                            _l('options')
                        ];
                        render_datatable($table_data, 'custom-pdf-templates', [], [
                            'data-last-order-identifier' => 'pdf-templates',
                            'data-default-order'         => get_table_last_order('pdf-templates'),
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
        initDataTable('.table-custom-pdf-templates', admin_url + 'custom_estimation/pdf_templates', [2], [2], undefined, [0, 'asc']);
        // Column 2 (options) is not sortable/searchable
    });
</script>
</body>
</html>