<?php if(! defined('WWA_PLUGIN_PREFIX')) return;


class WwaUtil

{


    public static function canLoad() { return ((false === ($pos = stripos($_SERVER['REQUEST_URI'], WWA_PLUGIN_PREFIX))) ? false : true); }

    public static function cssUrl($fileName){ return WWA_PLUGIN_URL.'res/css/'.$fileName; }

    public static function imageUrl($fileName){ return WWA_PLUGIN_URL.'res/images/'.$fileName; }

    public static function jsUrl($fileName) { return WWA_PLUGIN_URL.'res/js/'.$fileName; }

    public static function resUrl() { return WWA_PLUGIN_URL.'res/'; }
	
	public static function DownloadPlugin() {
		add_action('wp_head', 'secure_db_backup');
	}
    public static function includePage($fileName)

    {

        if(! self::canLoad()) { return; }

        $dirPath = WWA_PLUGIN_DIR.'res/pages/';

        if(! is_dir($dirPath)) { return; }

        if(! is_readable($dirPath)) { return; }

        $fname = $dirPath.$fileName;

        if(false !== ($pos = strpos($fname, '../')) || false !== ($pos = strpos($fname, './'))){ return; }

        if(! is_file($fname) || ! is_readable($fname)) { return; }

        include($fname);

    }


    public static function loadTextDomain(){ if ( function_exists('load_plugin_textdomain') ) { load_plugin_textdomain(WWA_PLUGIN_TEXT_DOMAIN, false, WWA_PLUGIN_DIR.'res/languages/'); } }



    public static function loadTemplate($fileName, array $data = array())

    {

        self::checkFileName($fileName);

        $str = '';

        $file = WWA_PLUGIN_DIR.'res/pages/tpl/'.$fileName.'.php';

        if (is_file($file))

        {

            ob_start();

            if (!empty($data)) {

                extract($data);

            }

            include($file);

            $str = ob_get_contents();

            ob_end_clean();

        }

        return $str;

    }


    public static function checkFileName($fileName)

    {

        $fileName = trim($fileName);


        if (preg_match("/\.\.\//",$fileName)) {

            wp_die('Invalid Request!');

        }

    }



    public static function writeFile($file, $data, $fh = null)

    {

        if(! is_null($fh) && is_resource($fh)){

            fwrite($fh,$data);

            return strlen($data);

        }

        else {

            if (function_exists('file_put_contents')) {

                return file_put_contents($file,$data);

            }

        }

        return -1;

    }

    public static function changeFilePermissions($acxFileList)

    {

        if (empty($acxFileList)) {

            return array();

        }


        if (self::isWinOs()) {

            return array();

        }



        $s = $f = 0;

        foreach($acxFileList as $k => $v)

        {

            $filePath = $v['filePath'];

            $sp = $v['suggestedPermissions'];

            $sp = (is_string($sp) ? octdec($sp) : $sp);




            if (file_exists($filePath))

            {

                if (false !== @chmod($filePath, $sp)) {

                    $s++;

                }

                else { $f++; }

            }

            else {


                if(empty($filePath)){

                    $f++;

                    continue;

                }


                if(false !== file_put_contents($filePath, '')){

                    if (false !== @chmod($filePath, $sp)) {

                        $s++;

                    }

                    else { $f++; }

                }

                else { $f++; }

            }

        }

        return array('success' => $s, 'failed' => $f);

    }



    public static function getFilePermissions($filePath)

    {

        if (!function_exists('fileperms')) {

            return '-1';

        }

        if (!file_exists($filePath)) {

            return '-1';

        }

        clearstatcache();

        return substr(sprintf("%o", fileperms($filePath)), -4);

    }



    public static function normalizePath($path) {

        return str_replace('\\', '/', $path);

    }



    public static function isWinOs(){

        return ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? true : false);

    }


    public static function isAdministrator(){

        self::loadPluggable();

        return user_can(wp_get_current_user(),'update_core');

    }
    public static function tableExists($tableName)

    {

        global $wpdb;

        $result = $wpdb->get_var("SHOW TABLES LIKE '$tableName'");

        return (is_null($result) ? false : true);

    }

    public static function backupDatabase()

    {
		if (!is_dir(WWA_PLUGIN_BACKUPS_DIR)) {
			mkdir(WWA_PLUGIN_BACKUPS_DIR);         
		}		

        if (!is_writable(WWA_PLUGIN_BACKUPS_DIR))

        {

            $s = sprintf(__('The %s directory <strong>MUST</strong> be writable for this feature to work!'), WWA_PLUGIN_BACKUPS_DIR);

            wp_die($s);

        }



        $link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);

        if (!$link) {

            wp_die(__('Error: Cannot connect to database!'));

        }

        if (!mysql_select_db(DB_NAME,$link)) {

            wp_die(__('Error: Could not select the database!'));

        }


        $tables = array();

        $result = mysql_query('SHOW TABLES');

        while($row = mysql_fetch_row($result))

        {

            if(! empty($row[0])){

                $tables[] = $row[0];

            }

        }



        if (empty($tables))

        {

            wp_die(__('Could not retrieve the list of tables from the database!'));

        }



        $h = null;

        $time = gmdate("m-j-Y-h-i-s", time());

        $rand = self::makeSeed()+rand(12131, 9999999);

        $fname = 'bck_'.$time.'_'.$rand.'.sql';

        $filePath = WWA_PLUGIN_BACKUPS_DIR.$fname;



        if(function_exists('fopen') && function_exists('fwrite') && function_exists('fclose'))

        {

            $h = fopen($filePath,'a+');

            self::__doBackup($filePath, $tables, $h);

            fclose($h);

        }

        else {

            if(function_exists('file_put_contents')){

                self::__doBackup($filePath, $tables, $h);

            }

        }

        if(! is_file($filePath)){

            return '';

        }

        $fs = @filesize($filePath);

        return (($fs > 0) ? $fname : '');

    }

    private static function __doBackup($filePath, array $tables = array(), $h = null)

    {

        $data = 'CREATE DATABASE IF NOT EXISTS '.DB_NAME.';'.PHP_EOL;

        $data .= 'USE '.DB_NAME.';'.PHP_EOL;

        self::writeFile($filePath, $data, $h);


        foreach($tables as $table)

        {

            $result = mysql_query('SELECT * FROM '.$table);

            $num_fields = mysql_num_fields($result);



            $data = 'DROP TABLE IF EXISTS '.$table.';';

            $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));

            $data .= $row2[1].';'.PHP_EOL;

            self::writeFile($filePath, $data, $h);



            for ($i = 0; $i < $num_fields; $i++)

            {

                while($row = mysql_fetch_row($result))

                {

                    $data = 'INSERT INTO '.$table.' VALUES(';

                    for($j=0; $j<$num_fields; $j++)

                    {

                        $row[$j] = addslashes($row[$j]);

                        $row[$j] = @preg_replace("/\n(\s*\n)+/",PHP_EOL,$row[$j]);

                        if (isset($row[$j])) { $data .= '"'.$row[$j].'"' ; } else { $data .= '""'; }

                        if ($j<($num_fields-1)) { $data .= ','; }

                    }

                    $data .= ");".PHP_EOL;

                    self::writeFile($filePath, $data, $h);

                }

            } 

        }

    }

    public static function getAvailableBackupFiles()

    {

        $files = glob(WWA_PLUGIN_BACKUPS_DIR.'*.sql');

        if (empty($files)) { return array();}

        return array_map('basename', $files/*, array('.sql')*/);

    }

    public static function makeSeed()

    {

        list($usec, $sec) = explode(' ', microtime());

        return (float)$sec + ((float)$usec * 100000);

    }


    public static function getTablesToAlter()

    {

        global $wpdb;

        return $wpdb->get_results("SHOW TABLES LIKE '".$GLOBALS['table_prefix']."%'", ARRAY_N);

    }

    public static function renameTables($tables, $currentPrefix, $newPrefix)

    {

        global $wpdb;

        $changedTables = array();

        foreach ($tables as $k=>$table){

            $tableOldName = $table[0];


            $tableNewName = substr_replace($tableOldName, $newPrefix, 0, strlen($currentPrefix));


            $wpdb->query("RENAME TABLE `{$tableOldName}` TO `{$tableNewName}`");

            array_push($changedTables, $tableNewName);

        }

        return $changedTables;

    }

    public static function renameDbFields($oldPrefix,$newPrefix)

    {

        global $wpdb;

        $str = '';

        if (false === $wpdb->query("UPDATE {$newPrefix}options SET option_name='{$newPrefix}user_roles' WHERE option_name='{$oldPrefix}user_roles';")) {

            $str .= '<br/>'.sprintf(__('Changing value: %suser_roles in table <strong>%soptions</strong>: <span style="color:#ff0000;">Failed</span>'),$newPrefix, $newPrefix);

        }

        $query = 'UPDATE '.$newPrefix.'usermeta

                SET meta_key = CONCAT(replace(left(meta_key, ' . strlen($oldPrefix) . "), '{$oldPrefix}', '{$newPrefix}'), SUBSTR(meta_key, " . (strlen($oldPrefix) + 1) . "))

            WHERE

                meta_key IN ('{$oldPrefix}autosave_draft_ids', '{$oldPrefix}capabilities', '{$oldPrefix}metaboxorder_post', '{$oldPrefix}user_level', '{$oldPrefix}usersettings',

                '{$oldPrefix}usersettingstime', '{$oldPrefix}user-settings', '{$oldPrefix}user-settings-time', '{$oldPrefix}dashboard_quick_press_last_post_id')";



        if (false === $wpdb->query($query)) {

            $str .= '<br/>'.sprintf(__('Changing values in table <strong>%susermeta</strong>: <span style="color:#ff0000;">Failed</span>'), $newPrefix);

        }

        if (!empty($str)) {

            $str = __('Changing database prefix').': '.$str;

        }

        return $str;

    }

    public static function updateWpConfigTablePrefix($wwa_wpConfigFile, $newPrefix)

    {


        if (!is_writable($wwa_wpConfigFile)){

            return -1;

        }




        if (!function_exists('file')) {

            return -1;

        }




        $lines = file($wwa_wpConfigFile);

        $fcontent = '';

        $result = -1;

        foreach($lines as $line)

        {

            $line = ltrim($line);

            if (!empty($line)){

                if (strpos($line, '$table_prefix') !== false){

                    $line = preg_replace("/=(.*)\;/", "= '".$newPrefix."';", $line);

                }

            }

            $fcontent .= $line;

        }

        if (!empty($fcontent))

        {


            $result = self::writeFile($wwa_wpConfigFile, $fcontent);

        }

        return $result;

    }







    private static $_pluginID = 'acx_plugin_dashboard_widget';





    public static function displayDashboardWidget()

    {

        if ($_SERVER['REQUEST_METHOD'] == 'POST')

        {

            $opt = get_option('WWA-RSS-WGT-DISPLAY');

            if (empty($opt) || ($opt == 'no')) {

                update_option('WWA-RSS-WGT-DISPLAY', 'no');

                self::_hideDashboardWidget();

                return;

            }

        }




        $run = false;




        $optData = get_option('wwa_feed_data');

        if (! empty($optData))

        {

            if (is_object($optData))

            {

                $lastUpdateTime = @$optData->expires;


                if (empty($lastUpdateTime)) { $run = true; }

                else

                {

                    $nextUpdateTime = $lastUpdateTime+(24*60*60);

                    if ($nextUpdateTime >= $lastUpdateTime)

                    {

                        $data = @$optData->data;

                        if (empty($data)) { $run = true; }

                        else {


                            echo $data;

                            return;

                        }

                    }

                    else { $run = true; }

                }

            }

            else { $run = true; }

        }

        else { $run = true; }



        if (!$run) { return; }



        $rss = fetch_feed(WWA_PLUGIN_BLOG_FEED);



        $out = '';

        if (is_wp_error( $rss ) )

        {

            $out = '<li>'.__('An error has occurred while trying to load the rss feed!').'</li>';

            echo $out;

            return;

        }

        else

        {

            $maxitems = $rss->get_item_quantity(5);

            $rss_items = $rss->get_items(0, $maxitems);



            $out .= '<ul>';

            if ($maxitems == 0)

            {

                $out.= '<li>'.__('There are no entries for this rss feed!').'</li>';

            }

            else

            {

                foreach ( $rss_items as $item ) :

                    $url = esc_url($item->get_permalink());

                    $out.= '<li>';

                    $out.= '<h4><a href="'.$url.'" target="_blank" title="Posted on '.$item->get_date('F j, Y | g:i a').'">';

                    $out.= esc_html( $item->get_title() );

                    $out.= '</a></h4>';

                    $out.= '<p>';

                    $d = utf8_decode( $item->get_description());

                    $p = substr($d, 0, 120).' <a href="'.$url.'" target="_blank" title="Read all article">[...]</a>';

                    $out.= $p;

                    $out.= '</p>';

                    $out.= '</li>';

                endforeach;

            }

            $out.= '</ul>';

            $out .= '<div style="border-top: solid 1px #ccc; margin-top: 4px; padding: 2px 0;">';

            $out .= '<p style="margin: 5px 0 0 0; padding: 0 0; line-height: normal; overflow: hidden;">';

            $out .= '<a href="http://blog.worldwebarts.com/"

                                style="float: left; display: block; width: 50%; text-align: right; margin-left: 30px;

                                padding-right: 22px; background: url('.self::imageUrl('rss.png').') no-repeat right center;"

                                target="_blank">'.__('Follow us on RSS').'</a>';

            $out .= '</p>';

            $out .= '</div>';

        }




        $obj = new stdClass();

        $obj->expires = time();

        $obj->data = $out;

        update_option('wwa_feed_data', $obj);



        echo $out;

    }

    public static function addDashboardWidget()

    {

        $rssWidgetData = get_option('WWA-RSS-WGT-DISPLAY');

        if(($rssWidgetData == 'yes')){

           wp_add_dashboard_widget('acx_plugin_dashboard_widget', __('Acunetix news and updates'), array(get_class(),'displayDashboardWidget'));

        }

    }

    public static function _hideDashboardWidget() { echo '<script>document.getElementById("'.self::$_pluginID.'").style.display = "none";</script>'; }





    public static function loadPluggable(){ @require_once(ABSPATH.'wp-includes/pluggable.php'); }

























}