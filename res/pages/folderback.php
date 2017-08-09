<?php if(! WwaUtil::canLoad()) { return; } ?>

<?php if(! WwaUtil::isAdministrator()) { return; } ?>

<div class="wrap wwaplugin_content">

    <h2><?php echo WWA_PLUGIN_NAME.' - '. __('Folder Backup Tools');?></h2>



    <div style="clear: both; display: block;">

        <?php  ?>

        <div class="metabox-holder" style="overflow: hidden;">



            <?php

             ?>

            <div id="bckdb" style="float:left; width:49%;" class="inner-sidebar1 postbox">

                <h3 class="hndle" style="cursor: default;"><span><?php echo __('Backup Folder');?></span></h3>

                <div class="inside">

                    <?php

                    echo WwaUtil::loadTemplate('box-folder-backup');

                    ?>

                </div>

            </div>



            <?php

            /*

             * DATABASE BACKUPS

             * ================================================================

             */

            ?>

            <div style="float:right;width:49%;" class="inner-sidebar1 postbox">

                <h3 class="hndle" style="cursor: default;"><span><?php echo __('All Folder Backup Files');?></span></h3>

                <div class="inside">

                    <?php

                    echo WwaUtil::loadTemplate('box-available-folder-backups');

                    ?>

                </div>

            </div>

        </div>

	</div>

</div>

