<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                         <h4 class="no-margin">
                            <?php echo $title; ?>
                        </h4>
                        <hr class="hr-panel-heading" />
                        <?php
                        // Output the widget content that was loaded by the controller
                        if (isset($widget_content)) {
                            echo $widget_content;
                        } else {
                            echo '<p>No report data to display.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
