<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php if (isset($lead)) { ?>
    <h4 class="customer-profile-group-heading"><?php echo _l('lead_profile_custom_estimates_tab'); ?></h4>
    
    <?php if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create')) { ?>
        <a href="<?php echo admin_url('custom_estimation/estimates/estimate?lead_id=' . $lead->id); ?>" class="btn btn-primary mbot15">
            <i class="fa fa-plus"></i> <?php echo _l('new_custom_estimate'); ?>
        </a>
    <?php } ?>

    <?php
    // We need to fetch the estimates related to this lead.
    // This logic would typically be in the controller that loads this view,
    // or this view could call a model method directly (less ideal for MVC).
    // For this example, assuming $related_estimates is passed to this view.
    // In a real scenario, your main module file's hook that adds this tab
    // might need to ensure the controller loading the lead profile prepares this data.
    // Or, use an AJAX call to a dedicated controller method to load this table content.

    // For now, let's prepare for a simple table.
    // You'll need to load estimates for $lead->id using your Estimates_model.
    // $related_estimates = $this->ci->estimates_model->get_estimates_for_lead($lead->id); 
    // (Assuming $this->ci is available or use &get_instance())
    
    // Let's use a placeholder for now and build out the AJAX loading table later if needed,
    // or ensure the main lead controller passes this data.
    // For simplicity, we can call the model method here IF the model is loaded.

    $CI = &get_instance();
    if (!class_exists('Estimates_model', false)) {
        $CI->load->model('custom_estimation/estimates_model');
    }
    $related_estimates = $CI->estimates_model->get_estimates_for_lead($lead->id);

    if (count($related_estimates) > 0) { ?>
        <table class="table dt-table" data-order-col="2" data-order-type="desc">
            <thead>
                <tr>
                    <th><?php echo _l('custom_estimate') . ' #'; ?></th>
                    <th><?php echo _l('custom_estimate_subject'); ?></th> <th><?php echo _l('estimate_total'); ?></th>
                    <th><?php echo _l('custom_estimate_date'); ?></th>
                    <th><?php echo _l('custom_estimate_valid_until'); ?></th>
                    <th><?php echo _l('custom_estimate_status'); ?></th>
                    <th><?php echo _l('options'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($related_estimates as $estimate_item) { ?>
                    <tr>
                        <td>
                            <a href="<?php echo admin_url('custom_estimation/estimates/estimate/' . $estimate_item['id']); ?>">
                                <?php echo format_custom_estimate_number($estimate_item['id']); ?>
                            </a>
                        </td>
                        <td><?php echo $estimate_item['subject'] ?? format_custom_estimate_number($estimate_item['id']); // Fallback if subject removed ?></td>
                        <td><?php echo app_format_money($estimate_item['total'], get_base_currency()); ?></td>
                        <td><?php echo _d($estimate_item['datecreated']); ?></td>
                        <td><?php echo $estimate_item['valid_until'] ? _d($estimate_item['valid_until']) : '-'; ?></td>
                        <td>
                            <?php 
                            $status_info = get_custom_estimate_status_by_id($estimate_item['status']);
                            echo '<span class="label label-'.($status_info['color'] ?? 'default').'">' . ($status_info['name'] ?? ucfirst($estimate_item['status'])) . '</span>';
                            ?>
                        </td>
                        <td>
                            <div class="tw-flex tw-items-center tw-space-x-2">
                                <a href="<?php echo admin_url('custom_estimation/estimates/estimate/' . $estimate_item['id']); ?>" class="btn btn-default btn-icon" title="<?php echo _l('edit'); ?>"><i class="fa fa-pencil-square-o"></i></a>
                                <a href="<?php echo admin_url('custom_estimation/estimates/pdf/' . $estimate_item['id']); ?>" target="_blank" class="btn btn-default btn-icon" title="<?php echo _l('download_pdf'); ?>"><i class="fa fa-file-pdf-o"></i></a>
                                <?php if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'create')) { // Permission for duplicate might be 'create' or a new one ?>
                                    <a href="<?php echo admin_url('custom_estimation/estimates/duplicate/' . $estimate_item['id']); ?>" class="btn btn-default btn-icon" title="<?php echo _l('duplicate_estimate_tip'); // Add lang string ?>"><i class="fa fa-clone"></i></a>
                                <?php } ?>
                                <?php if (has_permission(CUSTOM_ESTIMATION_MODULE_NAME, '', 'delete')) { ?>
                                    <a href="<?php echo admin_url('custom_estimation/estimates/delete_estimate/' . $estimate_item['id']); ?>" class="btn btn-danger btn-icon _delete" title="<?php echo _l('delete'); ?>"><i class="fa fa-remove"></i></a>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p class="no-margin"><?php echo _l('no_estimates_found'); ?></p>
    <?php } ?>
<?php } else { ?>
    <p class="no-margin"><?php echo _l('lead_info_not_available'); ?></p>
<?php } ?>