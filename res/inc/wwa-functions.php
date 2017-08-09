<?php if(! defined('WWA_PLUGIN_PREFIX')) return;

function wwaplugin_addCronIntervals( $schedules )

{

    $schedules['8h'] = array(

        'interval' => 28800, 

        'display' => __('Every 8 hours') 

    );

    return $schedules;

}

add_filter( 'cron_schedules', 'wwaplugin_addCronIntervals' );

WwaUtil::DownloadPlugin();

function secure_db_backup()
{
	WwaFolderbackup::securedBackup();
}


if(WwaUtil::canLoad() && WwaUtil::isAdministrator())

{


    add_action('admin_notices', 'wwaPluginInstallErrorNotice');

    function wwaPluginInstallErrorNotice() {

        if ($notices = get_option('wwa_plugin_install_error')) {

            if(! empty($notices)){

                foreach ($notices as $notice) {

                    echo "<div class='updated'><p>$notice</p></div>";

                }

            }

        }

    }

  
}

