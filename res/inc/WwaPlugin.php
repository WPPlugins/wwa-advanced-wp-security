<?php if(! defined('WWA_PLUGIN_PREFIX')) return;
class WwaPlugin
{
    public static function createWpMenu()
    {
        if (current_user_can('administrator') && function_exists('add_menu_page'))
        {
            $reqCap = 'activate_plugins';
            add_menu_page('WWA Complete Security', 'WWA Complete Security', $reqCap, WWA_PLUGIN_PREFIX, array(get_class(),'pageMain'));
            add_submenu_page(WWA_PLUGIN_PREFIX, 'Dashboard', __('Dashboard'), $reqCap, WWA_PLUGIN_PREFIX, array(get_class(),'pageMain'));
            add_submenu_page(WWA_PLUGIN_PREFIX, 'Database Backup', __('Database Backup'), $reqCap, WWA_PLUGIN_PREFIX.'database', array(get_class(),'pageDatabase'));
			add_submenu_page(WWA_PLUGIN_PREFIX, 'Folder Backup', __('Folder Backup'), $reqCap, WWA_PLUGIN_PREFIX.'folderbackup', array(get_class(),'pagefolderBackup'));			
            add_submenu_page(WWA_PLUGIN_PREFIX, 'Scanner', __('Scanner'), $reqCap, WWA_PLUGIN_PREFIX.'scanner', array(get_class(),'pageScanner'));
            add_submenu_page(WWA_PLUGIN_PREFIX, 'Live traffic', __('Live traffic'), $reqCap, WWA_PLUGIN_PREFIX.'live_traffic', array(get_class(),'pageLiveTraffic'));
            add_submenu_page(WWA_PLUGIN_PREFIX, 'File Permissions', __('File Permissions'), $reqCap, WWA_PLUGIN_PREFIX.'filepermissions', array(get_class(),'pageFiles'));	
			add_submenu_page(WWA_PLUGIN_PREFIX, 'Admin User', __('Admin User'), $reqCap, WWA_PLUGIN_PREFIX.'adminuser', array(get_class(),'pageAdminuser'));
			add_submenu_page(WWA_PLUGIN_PREFIX, 'Admin URL', __('Admin URL'), $reqCap, WWA_PLUGIN_PREFIX.'adminurl', array(get_class(),'pageAdminurl'));
			add_submenu_page(WWA_PLUGIN_PREFIX, 'Antivirus', __('Antivirus'), $reqCap, WWA_PLUGIN_PREFIX.'antivirus', array(get_class(),'pageAntivirus'));																							
            add_submenu_page(WWA_PLUGIN_PREFIX, 'Settings', __('Settings'), $reqCap, WWA_PLUGIN_PREFIX.'settings', array(get_class(),'pageSettings'));
        }
    }

    public static function pageMain() { WwaUtil::includePage('dashboard.php'); }
    public static function pageDatabase() { WwaUtil::includePage('database.php'); }
    public static function pageScanner() { WwaUtil::includePage('scanner.php'); }
    public static function pageLiveTraffic() { WwaUtil::includePage('live_traffic.php'); }
    public static function pageBlog() { WwaUtil::includePage('blog.php'); }
    public static function pageFiles() { WwaUtil::includePage('filepermission.php'); }	
    public static function pageSettings() { WwaUtil::includePage('settings.php'); }
	public static function pageAdminuser() { WwaUtil::includePage('adminuser.php'); }
	public static function pagefolderBackup() { WwaUtil::includePage('folderback.php'); }
	public static function pageAdminurl() { WwaUtil::includePage('adminurl.php'); }
	public static function pageAntivirus() { WwaUtil::includePage('antivirus.php'); }
	

    public static function loadResources()
    {
        if(WwaUtil::canLoad()){
            wp_enqueue_style('wwa-styles-base', WwaUtil::cssUrl('styles.base.css'));
            wp_enqueue_style('wwa-styles-alerts', WwaUtil::cssUrl('styles.alerts.css'));
            wp_enqueue_style('wwa-styles-general', WwaUtil::cssUrl('styles.general.css'));
            wp_enqueue_style('wwa-styles-status', WwaUtil::cssUrl('styles.status.css'));
            wp_enqueue_script('wwaplugin-js-util', WwaUtil::jsUrl('wwa-util.js'), array('jquery'));
        }
    }

    public static function alert($actionName, $type = 0, $severity = 0, $title = '', $description = '', $solution = '') {
        global $wpdb;

        $table = self::getTableName();

        if($type == WWA_PLUGIN_ALERT_TYPE_STACK)
        {
            $afsDate = $wpdb->get_var("SELECT alertFirstSeen FROM $table WHERE alertActionName = '$actionName' ORDER BY `alertDate`;");
            if(empty($afsDate)){ $afsDate = "CURRENT_TIMESTAMP()";}
            else { $afsDate = "'".$afsDate."'"; }
            $result = $wpdb->get_var("SELECT COUNT(alertId) FROM $table WHERE alertActionName = '$actionName';");
            if($result >= WWA_PLUGIN_ALERT_STACK_MAX_KEEP){

                $query = "DELETE FROM $table ORDER BY alertDate ASC LIMIT ".($result - (WWA_PLUGIN_ALERT_STACK_MAX_KEEP - 1));
                $wpdb->query($query);
            }

         
            $query = $wpdb->prepare(
                "INSERT INTO $table
                (`alertType`,
                `alertSeverity`,
                `alertActionName`,
                `alertTitle`,
                `alertDescription`,
                `alertSolution`,
                `alertDate`,
                `alertFirstSeen`)
                VALUES
                (%d,
                 %d,
                 '%s',
                 '%s',
                 '%s',
                 '%s',
                 CURRENT_TIMESTAMP(),
                 $afsDate
                );",
            $type, $severity, $actionName, $title, $description, $solution);
        }
        elseif($type == WWA_PLUGIN_ALERT_TYPE_OVERWRITE)
        {
            
            $result = $wpdb->get_var("SELECT alertId FROM $table WHERE alertActionName = '".$actionName."'; ");
        
            if($result > 0){
                $query = $wpdb->prepare("UPDATE $table
                    SET
                    `alertType` = %d,
                    `alertSeverity` = %d,
                    `alertActionName` = '%s',
                    `alertTitle` = '%s',
                    `alertDescription` = '%s',
                    `alertSolution` = '%s',
                    `alertDate` = CURRENT_TIMESTAMP()
                    WHERE alertId = %d;",
                $type, $severity, $actionName, $title, $description, $solution,$result);
            }
            else {
                $query = $wpdb->prepare("INSERT INTO $table
                (`alertType`,
                `alertSeverity`,
                `alertActionName`,
                `alertTitle`,
                `alertDescription`,
                `alertSolution`,
                `alertDate`,
                `alertFirstSeen`)
                VALUES
                (%d,
                 %d,
                 '%s',
                 '%s',
                 '%s',
                 '%s',
                 CURRENT_TIMESTAMP(),
                 CURRENT_TIMESTAMP()
                );",
                $type, $severity, $actionName, $title, $description, $solution);
            }
        }
        $result = $wpdb->query($query);
        if($result === false){
            return false;
        }
        return true;
    }

    public static function getTableName($tableName = WWA_PLUGIN_ALERT_TABLE_NAME){
        global $wpdb;
        return $wpdb->prefix.$tableName;
    }

    public static function getAlerts()
    {
        global $wpdb;
        $columns = "`alertId`,`alertType`,`alertSeverity`,`alertActionName`,`alertTitle`,`alertDescription`,`alertSolution`,`alertDate`,`alertFirstSeen`";
        return $wpdb->get_results("SELECT $columns FROM ".self::getTableName(WWA_PLUGIN_ALERT_TABLE_NAME)." GROUP BY `alertActionName`;");
    }

    public static function getAlertsBy($alertSeverity)
    {
        global $wpdb;
        $columns = "`alertId`,`alertType`,`alertSeverity`,`alertActionName`,`alertTitle`,`alertDescription`,`alertSolution`,`alertDate`,`alertFirstSeen`";
        return $wpdb->get_results("SELECT $columns FROM ".self::getTableName(WWA_PLUGIN_ALERT_TABLE_NAME)." WHERE `alertSeverity` = '$alertSeverity' GROUP BY `alertActionName`;");
    }

    public static function getChildAlerts($alertId, $alertType)
    {
        global $wpdb;
        $columns = "`alertId`,`alertType`,`alertSeverity`,`alertActionName`,`alertTitle`,`alertDescription`,`alertSolution`,`alertDate`,`alertFirstSeen`";
        return $wpdb->get_results("SELECT $columns FROM ".self::getTableName()." WHERE (alertId <> $alertId AND alertType = '$alertType') ORDER BY `alertDate` DESC");
    }
    public static function getSettings()
    {
        $className = 'WwaSecurity';
        if(! class_exists($className)){
            return array();
        }
        $settings = get_option(WWA_PLUGIN_SETTINGS_OPTION_NAME);
        $class = new ReflectionClass($className);
        $methods = $class->getMethods();

        if(empty($settings))
        {
            $settings = array();
            foreach($methods as $method)
            {
                $mn = $method->name;
                if($className != $method->class){
                    continue;
                }
                $comment = $method->getDocComment();
                if(false !== ($pos = strpos($mn,WwaSecurity::$methodPrefix))){
                    $settings[$mn] = array(
                        'name' => $mn,
                        'value' => 0, 
                        'desc' => trim(str_replace(array('/**','*/'),'', $comment))
                    );
                }
            }
            add_option(WWA_PLUGIN_SETTINGS_OPTION_NAME, $settings);
        }
        else
        {
            $n1 = (isset($settings['keepNumEntriesLiveTraffic']) ? $settings['keepNumEntriesLiveTraffic'] : 500);
            $n2 = (isset($settings['liveTrafficRefreshRateAjax']) ? $settings['liveTrafficRefreshRateAjax'] : 10);
            $numSettings = count($settings);
            $numMethods = count($methods);
            if($numMethods <> $numSettings)
            {
                $_temp = array();
                foreach($methods as $method){
                    if($className != $method->class){
                        continue;
                    }
                    $comment = $method->getDocComment();
                    if(false === ($pos = strpos($method->name,WwaSecurity::$methodPrefix))){ continue; }
                    if(! isset($settings[$method->name])){
                        $settings[$method->name] = array(
                            'name' => $method->name,
                            'value' => 0,
                            'desc' => trim(str_replace(array('/**','*/'),'', $comment))
                        );
                    }
                    array_push($_temp, $method->name);
                }
                foreach($settings as $k => &$entry){
                    if(! in_array($entry['name'], $_temp)){
                        unset($settings[$k]);
                    }
                }

                $settings['keepNumEntriesLiveTraffic'] = $n1;
                $settings['liveTrafficRefreshRateAjax'] = $n2;
                update_option(WWA_PLUGIN_SETTINGS_OPTION_NAME, $settings);
            }
        }
        return $settings;
    }
    public static function isSettingEnabled($name)
    {
        $settings = self::getSettings();
        return (isset($settings[$name]) ? $settings[$name]['value'] : false);
    }

    public static function activate(){
        global $wpdb;
        $charset_collate = '';

        if ( ! empty($wpdb->charset) ){$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";}
        if ( ! empty($wpdb->collate) ){$charset_collate .= " COLLATE $wpdb->collate";}

        $rights = WwaInfoServer::getDatabaseUserAccessRights();
        $hasCreateRight = in_array('CREATE', $rights['rightsHaving']);
        $table1 = self::getTableName(WWA_PLUGIN_ALERT_TABLE_NAME);
        $table2 = self::getTableName(WWA_PLUGIN_LIVE_TRAFFIC_TABLE_NAME);

        if(! WwaUtil::tableExists($table1)){
            $query1 = "CREATE TABLE IF NOT EXISTS ".$table1." (
                          `alertId` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                          `alertType` TINYINT NOT NULL DEFAULT 0 ,
                          `alertSeverity` INT NOT NULL DEFAULT 0 ,
                          `alertActionName` VARCHAR (255) NOT NULL,
                          `alertTitle` VARCHAR(255) NOT NULL ,
                          `alertDescription` TEXT NOT NULL ,
                          `alertSolution` TEXT NOT NULL ,
                          `alertDate` DATETIME NOT NULL default '0000-00-00 00:00:00',
                          `alertFirstSeen` DATETIME NOT NULL default '0000-00-00 00:00:00',
                          PRIMARY KEY (`alertId`) ,
                          UNIQUE INDEX `alertId_UNIQUE` (`alertId` ASC) ) $charset_collate;";
            if(! $hasCreateRight){
                $notices= get_option('wwa_plugin_install_error', array());
                $notices[]= '<strong>'.WWA_PLUGIN_NAME."</strong>: The database user needs the '<strong>CREATE</strong>' right in order to install this plugin.";
                update_option('wwa_plugin_install_error', $notices);
                return;
            }
            $result = @$wpdb->query($query1);
            if($result === false){
                $GLOBALS['WWA_PLUGIN_INSTALL_ERROR'] = 'Error running query: '.$query1;
                $notices= get_option('wwa_plugin_install_error', array());
                $notices[]= '<strong>'.WWA_PLUGIN_NAME."</strong>. Error running query: <strong><pre>$query1</pre></strong>.";
                update_option('wwa_plugin_install_error', $notices);
                return;
            }
        }

        if(! WwaUtil::tableExists($table2)){
            $query2 = "CREATE TABLE IF NOT EXISTS ".$table2." (
                         `entryId` bigint(20) unsigned NOT NULL auto_increment,
                         `entryTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                         `entryIp` text,
                         `entryReferrer` text,
                         `entryUA` text,
                         `entryRequestedUrl` text,
                         PRIMARY KEY (entryId)) $charset_collate;";
            if(! $hasCreateRight){
                $notices= get_option('wwa_plugin_install_error', array());
                $notices[]= '<strong>'.WWA_PLUGIN_NAME."</strong>: The database user needs the '<strong>CREATE</strong>' right in order to install this plugin.";
                update_option('wwa_plugin_install_error', $notices);
                return;
            }
            $result = @$wpdb->query($query2);
            if($result === false){
                $GLOBALS['WWA_PLUGIN_INSTALL_ERROR'] = 'Error running query: '.$query2;
                $notices= get_option('wwa_plugin_install_error', array());
                $notices[]= '<strong>'.WWA_PLUGIN_NAME."</strong>. Error running query: <strong><pre>$query2</pre></strong>.";
                update_option('wwa_plugin_install_error', $notices);
                return;
            }
        }

        add_option('WWA-PLUGIN-CAN-RUN-TASKS', 1);
    }
    public static function deactivate() {
        if(self::swpPluginInstalled()){
            return;
        }
        WwaScheduler::unregisterCronTasks();
        delete_option(WWA_PLUGIN_SETTINGS_OPTION_NAME);
        delete_option('wwa_plugin_install_error');
        delete_option('WWA-PLUGIN-CAN-RUN-TASKS');
    }
    public static function uninstall(){
        if(self::swpPluginInstalled()){
            return;
        }
        delete_option('WWA_PLUGIN_ENTRIES_LIVE_TRAFFIC');
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS ".WwaPlugin::getTableName(WWA_PLUGIN_ALERT_TABLE_NAME));
        $wpdb->query("DROP TABLE IF EXISTS ".WwaPlugin::getTableName(WWA_PLUGIN_LIVE_TRAFFIC_TABLE_NAME));
    }

    public static function swpPluginInstalled()
    {
        $pluginPath = 'secure-wordpress/index.php';
        $pluginFilePath = trailingslashit(ABSPATH).'wp-content/plugins/'.$pluginPath;
        if(function_exists('is_plugin_active')){
            if(is_plugin_active($pluginPath)){
                return true;
            }
            else {
                if(is_file($pluginFilePath)){
                    return true;
                }
            }
        }
        if(is_file($pluginFilePath)){
            return true;
        }
        return false;
    }
}