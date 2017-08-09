<?php if(! WwaUtil::canLoad()) { return; } ?>
<?php if(! WwaUtil::isAdministrator()) { return; } ?>
<div class="wrap wwaplugin_content">
    <h2><?php echo WWA_PLUGIN_NAME.' - '. __('WordPress Scanner');?></h2>

    <p class="clear"></p>
    <p style="margin-left: 15px;">
        <?php echo __("This page displays various information after scanning your WordPress website"); ?>:
    </p>

    <div class="metabox-holder">

        <div style="width:49.8%; float: left;" class="postbox">
            <h3 class="hndle" style="cursor: default;"><span><?php echo __('Server Report');?></span></h3>
            <div class="inside acx-section-box">
                <?php
                echo WwaUtil::loadTemplate('box-server-results');
                ?>
            </div>
        </div>
        <div style="width:49.8%; float: right;" class="postbox">
            <h3 class="hndle" style="cursor: default;"><span><?php echo __('WordPress Scan Report');?></span></h3>
            <div class="inside acx-section-box">
                <?php
                echo WwaUtil::loadTemplate('box-scan-results-wp');
                ?>
            </div>
        </div>

        <div style="width:99.8%; clear: both;" class="inner-sidebar1 postbox">
            <h3 class="hndle" style="cursor: default;"><span><?php echo __('File Scan Report');?></span></h3>
            <div class="inside">
                <?php
                echo WwaUtil::loadTemplate('box-scan-results-file');
                ?>
            </div>
        </div>
    </div>
</div>