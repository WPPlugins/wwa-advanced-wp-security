<?php if(! WwaUtil::canLoad()) { return; } ?>



<?php

$wwa_bckDirPath = WWA_PLUGIN_BACKUPS_DIR;

if (is_dir($wwa_bckDirPath) && is_writable($wwa_bckDirPath)) :

?>





<?php

    if ($_SERVER['REQUEST_METHOD'] == 'POST')

    {

        if (isset($_POST['wwa_folder_backup']))

        {

			if (!is_dir(WWA_PLUGIN_BACKUPS_DIR)) {
				mkdir(WWA_PLUGIN_BACKUPS_DIR);         
			}		


            if ('' <> ($fname = WwaFolderbackup::backupFolder())) {

                echo '<p class="acx-info-box">';

					echo '<span>',__('Folder successfully backed up!'),'</span>';

					echo '<br/><span>',__('Download backup file'),': </span>';

					echo '<a href="'.WWA_PLUGIN_BACKUPS_DIR.$fname.'" title="'.__('Click to download').'">'.$fname.'</a>';

                echo '</p>';

            }

            else {

                echo '<p class="acx-info-box">';

					echo __('The folder could not be backed up!');

					echo '<br/>',__("A possible error might be that you didn't set up writing permissions for the backups directory!");

                echo '</p>';

            }

        }

    }

?>

<div class="acx-section-box">

    <form action="#bckdb" method="post">

        <input type="hidden" name="wwa_folder_backup"/>

        <input type="submit" class="button-primary" name="backupFolderButton" value="<?php echo __('Backup now!');?>"/>

    </form>

</div>



<?php else : 

            $s = sprintf(__('The %s directory <strong>MUST</strong> be writable for this feature to work!'), WWA_PLUGIN_BACKUPS_DIR);

            wp_die($s);
	

endif; ?>

