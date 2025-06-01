<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('custom_estimation_submenu_reports'); ?>">
    <div class="panel_s widget-holder">
        <div class="panel-body">
            <div class="widget-dragger"></div>
            <h4 class="quick-stats-leads TodaysFollowUps_widget_headingg">
                <span class="font-medium pull-left"><?php echo _l('custom_estimation_submenu_reports'); // Or a more specific widget title, e.g., _l('custom_estimates_summary_widget_title') ?></span>
                <span class="pull-right">
                    <a href="<?php echo admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/estimates'); ?>" class="font-medium-xs"><?php echo _l('view_all_custom_estimates'); ?></a>
                </span>
                <div class="clearfix"></div>
            </h4>
            <hr class="hr-panel-heading" />
            <div class="row">
                <?php
                // The controller that loads this widget (usually Dashboard.php) should load the model
                // and pass $summary_data to this view.
                // For direct use or if not passed, we can try to load it here.
                // This ensures the widget can function even if not explicitly prepared by the main dashboard controller.
                if (!isset($summary_data)) {
                    $CI = &get_instance(); // Get CodeIgniter instance
                    if(!class_exists('Estimates_model', false)){ // Check if model is already loaded
                         $CI->load->model('custom_estimation/estimates_model');
                    }
                    // It's better if the controller loading the widget prepares this data.
                    // For robustness, we fetch it here if not provided.
                    if(isset($CI->estimates_model)){
                        $summary_data = $CI->estimates_model->get_dashboard_summary(); 
                    } else {
                        $summary_data = []; // Default to empty if model couldn't be loaded
                         echo "<p class='text-danger'>Error: Estimates_model could not be loaded for widget.</p>";
                    }
                }
                
                if (is_array($summary_data) && count($summary_data) > 0) {
                    $total_estimates = 0;
                    foreach ($summary_data as $status_summary_item) { // Renamed loop variable
                        // Ensure $status_summary_item is an array and 'total' key exists
                        if (is_array($status_summary_item) && isset($status_summary_item['total'])) {
                            $total_estimates += (int)$status_summary_item['total'];
                        }
                    }
                ?>
                    <div class="col-md-12">
                        <h5 class="text-muted"><?php echo _l('total_estimates') . ': ' . $total_estimates; ?></h5>
                    </div>

                    <?php
                    foreach ($summary_data as $status_summary_item) { // Renamed loop variable
                        // Robust checks before accessing array elements
                        $status_name = isset($status_summary_item['name']) ? htmlspecialchars($status_summary_item['name']) : _l('unknown');
                        $status_total = isset($status_summary_item['total']) ? (int)$status_summary_item['total'] : 0;
                        $status_color = isset($status_summary_item['color']) ? htmlspecialchars($status_summary_item['color']) : 'text-default'; // Default color class
                        $status_id = isset($status_summary_item['status_id']) ? htmlspecialchars($status_summary_item['status_id']) : 'unknown_status';
                        
                        // Calculate percentage
                        $percent = ($total_estimates > 0 ? number_format(($status_total * 100) / $total_estimates, 2) : 0);
                    ?>
                        <div class="col-md-12 mtop5">
                            <div class="row">
                                <div class="col-md-7">
                                    <a href="<?php echo admin_url(CUSTOM_ESTIMATION_MODULE_NAME . '/estimates?status=' . $status_id); ?>" class="<?php echo $status_color; ?>">
                                        <?php echo $status_name; ?>
                                    </a>
                                </div>
                                <div class="col-md-5 text-right <?php echo $status_color; ?>">
                                    <?php echo $status_total; ?> / <?php echo $total_estimates; ?>
                                </div>
                                <div class="col-md-12">
                                    <div class="progress no-margin">
                                        <div class="progress-bar progress-bar-<?php 
                                            // Basic mapping for progress bar class based on text color, can be improved
                                            // Perfex uses text-success, text-info, text-warning, text-danger for statuses
                                            $progress_class = 'default'; // Default bootstrap class
                                            if(strpos($status_color, 'success') !== false) $progress_class = 'success';
                                            else if(strpos($status_color, 'info') !== false) $progress_class = 'info';
                                            else if(strpos($status_color, 'warning') !== false) $progress_class = 'warning';
                                            else if(strpos($status_color, 'danger') !== false) $progress_class = 'danger';
                                            else if(strpos($status_color, 'muted') !== false) $progress_class = 'default'; // Or 'muted' if your theme supports it
                                            echo $progress_class; 
                                            ?>" 
                                             role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%;">
                                            <?php echo $percent; ?>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } // End foreach 
                    ?>
                <?php } else { ?>
                    <div class="col-md-12">
                        <p class="text-muted"><?php echo _l('no_estimates_found'); // Or a more specific message for the widget ?></p>
                    </div>
                <?php } // End if count($summary_data) 
                ?>
            </div>
        </div>
    </div>
</div>