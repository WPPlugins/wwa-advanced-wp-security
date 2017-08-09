<?php 
if(! defined('WWA_PLUGIN_PREFIX')) 
exit;

define('WWA_PLUGIN_ALERT_INFO', 0);
define('WWA_PLUGIN_ALERT_LOW', 1);
define('WWA_PLUGIN_ALERT_MEDIUM', 2);
define('WWA_PLUGIN_ALERT_CRITICAL', 3);
define('WWA_PLUGIN_ALERT_TYPE_OVERWRITE', 0);
define('WWA_PLUGIN_ALERT_TYPE_STACK', 1);
define('WWA_PLUGIN_ALERT_STACK_MAX_KEEP', 10);
define('WWA_PLUGIN_ALERT_TABLE_NAME', '_wwa_plugin_alerts');
define('WWA_PLUGIN_LIVE_TRAFFIC_TABLE_NAME', '_wwa_plugin_live_traffic');
define('WWA_PLUGIN_BACKUPS_DIR', WP_CONTENT_DIR.'/backups/');
define('WWA_BACKUPS_DIR', WWA_PLUGIN_URL.'../../backups/');
define('WWA_PLUGIN_FOLDER_BACKUPS_DIR', WP_CONTENT_DIR.'/backups/');
define('WWA_PLUGIN_TEXT_DOMAIN', 'WWAWP_SECURITY');
define('WWA_PLUGIN_SETTINGS_OPTION_NAME', 'wwaplugin_settings');
define('WWA_PLUGIN_BLOG_FEED','');

$_wwaplugin_base_path  = trailingslashit(ABSPATH);
$_wwaplugin_wpAdmin    = $_wwaplugin_base_path.'wp-admin';
$_wwaplugin_wpContent  = $_wwaplugin_base_path.'wp-content';
$_wwaplugin_wpIncludes = $_wwaplugin_base_path.'wp-includes';
$_wwapluginWpConfigPath ='';
if(! is_file($_wwapluginWpConfigPath)){
    $_tmpPath = realpath($_wwaplugin_base_path.'../wp-config.php');
    if(is_file($_tmpPath)){
        $_wwapluginWpConfigPath = $_tmpPath;
    }
    else { $_wwapluginWpConfigPath = ''; }
}

$acxFileList = array(
    'root directory' => array( 'filePath' => $_wwaplugin_base_path, 'suggestedPermissions' => '0755'),
    'wp-admin' => array( 'filePath' => $_wwaplugin_wpAdmin, 'suggestedPermissions' => '0755'),
    'wp-content' => array( 'filePath' => $_wwaplugin_wpContent, 'suggestedPermissions' => '0755'),
    'wp-includes' => array( 'filePath' => $_wwaplugin_wpIncludes, 'suggestedPermissions' => '0755'),
    '.htaccess' => array( 'filePath' => $_wwaplugin_base_path.'.htaccess', 'suggestedPermissions' => '0644'),
    'readme.html' => array( 'filePath' => $_wwaplugin_base_path.'readme.html', 'suggestedPermissions' => '0400'),
    'wp-config.php' => array( 'filePath' => $_wwapluginWpConfigPath, 'suggestedPermissions' => '0644'),
    'wp-admin/index.php' => array( 'filePath' => $_wwaplugin_wpAdmin.'/index.php', 'suggestedPermissions' => '0644'),
    'wp-admin/.htaccess' => array( 'filePath' => $_wwaplugin_wpAdmin.'/.htaccess', 'suggestedPermissions' => '0644'),
);