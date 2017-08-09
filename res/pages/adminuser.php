<?php if(! WwaUtil::canLoad()) { return; } ?>

<?php if(! WwaUtil::isAdministrator()) { return; } ?>

<div class="wrap wwaplugin_content">

    <h2><?php echo WWA_PLUGIN_NAME.' - '. __('Change Default Admin User');?></h2>



    <p class="clear"></p>

    <div style="clear: both; display: block;">

        <?php  ?>

        <div class="metabox-holder" style="width:99.8%; padding-top: 0;">

            <?php

             ?>

            <div id="cdtp" class="postbox">

                <h3 class="hndle" style="cursor: default;"><span><?php echo __('Default Admin User');?></span></h3>

                <div class="inside">

                    <?php

                    echo WwaUtil::loadTemplate('box-adminuser-change');

                    ?>

                </div>

            </div>

        </div>



    </div>

</div>

