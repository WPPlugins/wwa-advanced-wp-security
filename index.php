<?php
/**
Plugin Name: Advanced Wordpress Security
Plugin URI: http://www.worldwebarts.com
Description: The Advanced Wordpress Security plugin Developed by World Web Arts to protect your wordpress website from virus and malicious attack by permission set, change wp url, setting updates, file scan, database backup, folder backup, traffic check etc.
Version: 1.0
Author: R K Sahoo
Author URI: http://www.worldwebarts.com
*/

define('WWA_PLUGIN_PREFIX', 'wwa_');
define('WWA_PLUGIN_NAME', 'WWA Wordpress Security Guide');
define('WWA_PLUGIN_URL', trailingslashit(plugins_url('', __FILE__)));
define('WWA_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__)));
define('WWA_PLUGIN_BASE_NAME', basename(__DIR__));


require('wwa-settings.php');
require('res/inc/alerts.php');
require('res/inc/WwaUtil.php');
require('res/inc/WwaPlugin.php');
require('res/inc/WwaInfo.php');

require('res/inc/WwaSecurity.php');

require('res/inc/WwaCheck.php');

require('res/inc/WwaScheduler.php');

require('res/inc/WwaWatch.php');

require('res/inc/WwaAdminuser.php');

require('res/inc/WwaAdminurl.php');

require('res/inc/WwaFolderbackup.php');

require('res/inc/WwaLiveTraffic.php');

require('res/inc/WwaFileFolderPermissions.php');

require('res/inc/wwa-functions.php');

require('res/inc/Wwaantivirus.php');






add_action('admin_init', array('WwaUtil','loadPluggable'));

register_activation_hook( __FILE__, array('WwaPlugin', 'activate') );

register_deactivation_hook( __FILE__, array('WwaPlugin', 'deactivate') );

register_uninstall_hook( __FILE__, array('WwaPlugin', 'uninstall') );







add_action(	'plugins_loaded', array('AntiVirus', 'instance'	), 99);




register_activation_hook(__FILE__, array('AntiVirus','install'));

register_deactivation_hook(__FILE__, array(	'AntiVirus', 'deactivation'));

register_uninstall_hook(__FILE__, array('AntiVirus','uninstall'));


if(false !== get_option('WWA-PLUGIN-CAN-RUN-TASKS',false))

{

    WwaScheduler::registerTask(array('WwaPlugin','loadResources'), 'init');

    WwaScheduler::registerTask(array('WwaPlugin','createWpMenu'), 'admin_menu');

    WwaScheduler::registerTask(array('WwaLiveTraffic','registerHit'), 'init');

    WwaScheduler::registerTask(array('WwaLiveTraffic','ajaxGetTrafficData'), 'wp_ajax_ajaxGetTrafficData');

    WwaScheduler::registerTask(array('WwaLiveTraffic','ajaxGetTrafficData'), 'wp_ajax_nopriv_ajaxGetTrafficData');

    WwaScheduler::registerTask(array('WwaUtil','addDashboardWidget'), 'wp_dashboard_setup');

    WwaScheduler::registerTask(array('WwaFileFolderPermissions','ajaxFileFoldersData'), 'wp_ajax_ajaxFileFoldersData');




    WwaScheduler::registerCronTask('wwa_check_user_admin', array('WwaCheck','adminUsername'), '8h');




    WwaScheduler::registerCronTask('wwa_cleanup_live_traffic', array('WwaLiveTraffic','clearEvents'), 'hourly');



    WwaScheduler::registerTask(array('WwaWatch','userPasswordUpdate'));




    WwaScheduler::registerClassTasks('WwaSecurity','fix_');




    WwaScheduler::registerClassTasks('WwaCheck','check_');

}





$antispam_send_spam_comment_to_admin = false; 



$antispam_allow_trackbacks = false;


if ( ! function_exists( 'antispam_scripts_styles_init' ) ) :

	function antispam_scripts_styles_init() {

		if ( !is_admin() ) { 


			wp_enqueue_script( 'anti-spam-script', plugins_url( '/js/anti-spam.js', __FILE__ ), array( 'jquery' ) );

		}

	}

	add_action('init', 'antispam_scripts_styles_init');

endif; 





if ( ! function_exists( 'antispam_form_part' ) ) :

	function antispam_form_part() {

		if ( ! is_user_logged_in() ) { 

			$antispam_form_part = '

	<p class="comment-form-ant-spm" style="clear:both;">

		<strong>Current <span style="display:none;">day</span> <span style="display:none;">month</span> <span style="display:inline;">ye@r</span></strong> <span class="required">*</span>

		<input type="hidden" name="ant-spm-a" id="ant-spm-a" value="'.date('Y').'" />

		<input type="text" name="ant-spm-q" id="ant-spm-q" size="30" value="20" />

	</p>

	'; 

			$antispam_form_part .= '

	<p class="comment-form-ant-spm-2" style="display:none;">

		<strong>Leave this field empty</strong> <span class="required">*</span>

		<input type="text" name="ant-spm-e-email-url" id="ant-spm-e-email-url" size="30" value=""/>

	</p>

	'; 

			echo $antispam_form_part;

		}

	}

	add_action( 'comment_form', 'antispam_form_part' ); 

endif; 





if ( ! function_exists( 'antispam_check_comment' ) ) :

	function antispam_check_comment( $commentdata ) {

		global $antispam_send_spam_comment_to_admin, $antispam_allow_trackbacks;

		extract( $commentdata );



		$antispam_pre_error_message = '<p><strong><a href="javascript:window.history.back()">Go back</a></strong> and try again.</p>';

		$antispam_error_message = '';



		if ( $antispam_send_spam_comment_to_admin ) { // if sending email to admin is enabled

			$antispam_admin_email = get_option('admin_email');  // admin email



			$post = get_post( $comment->comment_post_ID );

			$antispam_message_spam_info  = 'Spam for post: "'.$post->post_title.'"' . "\r\n";

			$antispam_message_spam_info .= get_permalink( $comment->comment_post_ID ) . "\r\n\r\n";



			$antispam_message_spam_info .= 'IP : ' . $_SERVER['REMOTE_ADDR'] . "\r\n";

			$antispam_message_spam_info .= 'User agent : ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";

			$antispam_message_spam_info .= 'Referer : ' . $_SERVER['HTTP_REFERER'] . "\r\n\r\n";

			$antispam_message_spam_info .= 'Comment data:'."\r\n"; 

			foreach ( $commentdata as $key => $value ) {

				$antispam_message_spam_info .= '$commentdata['.$key. '] = '.$value."\r\n"; 

			}

			$antispam_message_spam_info .= "\r\n\r\n";



			$antispam_message_spam_info .= 'Post vars:'."\r\n"; 

			foreach ( $_POST as $key => $value ) {

				$antispam_message_spam_info .= '$_POST['.$key. '] = '.$value."\r\n"; 

			}

			$antispam_message_spam_info .= "\r\n\r\n";



			$antispam_message_spam_info .= 'Cookie vars:'."\r\n"; 

			foreach ( $_COOKIE as $key => $value ) {

				$antispam_message_spam_info .= '$_COOKIE['.$key. '] = '.$value."\r\n"; 

			}

			$antispam_message_spam_info .= "\r\n\r\n";



			$antispam_message_append = '-----------------------------'."\r\n";

			$antispam_message_append .= 'This is spam comment rejected plugin.' . "\r\n";

			$antispam_message_append .= 'You may edit "anti-spam.php" file and disable this notification.' . "\r\n";

			$antispam_message_append .= 'You should find "$antispam_send_spam_comment_to_admin" and make it equal to "false".' . "\r\n";

		}



		if ( ! is_user_logged_in() && $comment_type != 'pingback' && $comment_type != 'trackback' ) {

			$spam_flag = false;



			if ( trim( $_POST['ant-spm-q'] ) != date('Y') ) { 

				$spam_flag = true;

				if ( empty( $_POST['ant-spm-q'] ) ) { 

					$antispam_error_message .= 'Error: empty answer. ['.$_POST['ant-spm-q'].']<br> ';

				} else {

					$antispam_error_message .= 'Error: answer is wrong. ['.$_POST['ant-spm-q'].']<br> ';

				}

			}

			if ( ! empty( $_POST['ant-spm-e-email-url'] ) ) { 

				$spam_flag = true;

				$antispam_error_message .= 'Error: field should be empty. ['.$_POST['ant-spm-e-email-url'].']<br> ';

			}

			if ( $spam_flag ) { 

				if ( $antispam_send_spam_comment_to_admin ) { 



					$antispam_subject = 'Spam comment on site ['.get_bloginfo( 'name' ).']'; 

					$antispam_message = '';



					$antispam_message .= $antispam_error_message . "\r\n\r\n";



					$antispam_message .= $antispam_message_spam_info;



					$antispam_message .= $antispam_message_append;



					@wp_mail( $antispam_admin_email, $antispam_subject, $antispam_message );

				}

				wp_die( $antispam_pre_error_message . $antispam_error_message ); 

			}

		}



		if ( ! $antispam_allow_trackbacks ) {

			if ( $comment_type == 'trackback' ) {

				$antispam_error_message .= 'Error: trackbacks are disabled.<br> ';

				if ( $antispam_send_spam_comment_to_admin ) { 

					$antispam_subject = 'Spam trackback on site ['.get_bloginfo( 'name' ).']'; 



					$antispam_message = '';



					$antispam_message .= $antispam_error_message . "\r\n\r\n";



					$antispam_message .= $antispam_message_spam_info; 



					$antispam_message .= $antispam_message_append;



					@wp_mail( $antispam_admin_email, $antispam_subject, $antispam_message ); 

				}

				wp_die( $antispam_pre_error_message . $antispam_error_message ); 

			}

		}



		return $commentdata; 

	}



	if ( ! is_admin() ) {

		add_filter( 'preprocess_comment', 'antispam_check_comment', 1 );

	}

endif; 

