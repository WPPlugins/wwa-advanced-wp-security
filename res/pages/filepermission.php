<?php if(! WwaUtil::canLoad()) { return; } ?>

<?php if(! WwaUtil::isAdministrator()) { return; } ?>

<div class="wrap wwaplugin_content">

    <h2><?php echo WWA_PLUGIN_NAME.' - '. __('File & Folder Permissions');?></h2>



    <p class="clear"></p>

    <div style="clear: both; display: block;">

        <?php  ?>

        <div class="metabox-holder" style="overflow: hidden;">



            <?php

 
            ?>

            <div id="bckdb" style="float:left; width:99%;" class="inner-sidebar1 postbox">

                <h3 class="hndle" style="cursor: default;"><span><?php echo __('All Files & Folders');?></span></h3>

                <div class="inside">

                

                    <?php

                    echo WwaUtil::loadTemplate('box-file-folder-permissions');

                    ?>

                </div>

            </div>

    </div>

</div>

