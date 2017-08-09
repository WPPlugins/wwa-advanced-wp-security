<?php if(! WwaUtil::canLoad()) { return; } ?>

<?php if(! WwaUtil::isAdministrator()) { return; } ?>

<div class="wrap wwaplugin_content">

    <h2><?php echo WWA_PLUGIN_NAME.' - '. __('Database Tools');?></h2>



    <div style="clear: both; display: block;">

        <?php  ?>

        <div class="metabox-holder" style="overflow: hidden;">



            <?php
             ?>

            <div id="bckdb" style="float:left; width:49%;" class="inner-sidebar1 postbox">

                <h3 class="hndle" style="cursor: default;"><span><?php echo __('Backup Database');?></span></h3>

                <div class="inside">

                    <?php

                    echo WwaUtil::loadTemplate('box-database-backup');

                    ?>

                </div>

            </div>



            <?php
 
            ?>

            <div style="float:right;width:49%;" class="inner-sidebar1 postbox">

                <h3 class="hndle" style="cursor: default;"><span><?php echo __('Database Backup Files');?></span></h3>

                <div class="inside">

                    <?php

                    echo WwaUtil::loadTemplate('box-available-backups');

                    ?>

                </div>

            </div>

        </div>

	</div>

    <p class="clear"></p>

    <div style="clear: both; display: block;">

        <div class="metabox-holder" style="width:99.8%; padding-top: 0;">

            <?php
 
            ?>

            <div id="cdtp" class="postbox">

                <h3 class="hndle" style="cursor: default;"><span><?php echo __('Change Database Prefix');?></span></h3>

                <div class="inside">

                    <?php

                    echo WwaUtil::loadTemplate('box-database-change-prefix');

                    ?>

                </div>

            </div>

        </div>



    </div>

</div>

