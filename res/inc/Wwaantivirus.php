<?php
if ( ! class_exists('WP') ) {

	die();

}
class AntiVirus {
	public static $base;
	public static function instance()

	{

		new self();

	}
	public function __construct()

	{

		if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) OR ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) ) {

			return;

		}
		self::$base = plugin_basename(__FILE__);

		if ( defined('DOING_CRON') ) {

			add_action(

				'antivirus_weekly_cronjob',

				array(

					__CLASS__,

					'do_weekly_cronjob'

				)

			);


		} elseif ( is_admin() ) {


			if ( defined('DOING_AJAX') ) {

				add_action(

					'wp_ajax_get_ajax_response',

					array(

						__CLASS__,

						'get_ajax_response'

					)

				);


			} else {


				add_action(

					'init',

					array(

						__CLASS__,

						'load_plugin_lang'

					)

				);

				add_action(

					'admin_menu',

					array(

						__CLASS__,

						'add_sidebar_menu'

					)

				);

				add_action(

					'admin_notices',

					array(

						__CLASS__,

						'show_dashboard_notice'

					)

				);

				add_action(

					'deactivate_' .self::$base,

					array(

						__CLASS__,

						'clear_scheduled_hook'

					)

				);

				add_filter(

					'plugin_action_links_' .self::$base,

					array(

						__CLASS__,

						'init_action_links'

					)

				);

			}

		}

	}
	public static function load_plugin_lang()

	{

		load_plugin_textdomain(

			'antivirus',

			false,

			'antivirus/lang'

		);

	}


	public static function init_action_links($data)

	{


		if ( ! current_user_can('manage_options') ) {

			return $data;

		}



		return array_merge(

			$data,

			array(

				sprintf(

					'<a href="%s">%s</a>',

					add_query_arg(

						array(

							'page' => 'antivirus'

						),

						admin_url('options-general.php')

					),

					__('Settings')

				)

			)

		);

	}
	public static function init_row_meta($data, $page)

	{

		if ( $page != self::$base ) {

			return $data;

		}



		return array_merge(

			$data,

			array(

				'<a href="http://worldwebarts.com/" target="_blank">World Web Arts</a>',

				' '

			)

		);

	}


	public static function install()

	{


		add_option(

			'antivirus',

			array(),

			'',

			'no'

		);


		if ( self::_get_option('cronjob_enable') ) {

			self::_add_scheduled_hook();

		}

	}

	public static function deactivation()

	{

		self::clear_scheduled_hook();

	}



	public static function uninstall()

	{

		delete_option('antivirus');

	}

	public static function _get_option($field)

	{

		$options = wp_parse_args(

			get_option('antivirus'),

			array(

				'cronjob_enable' => 0,

				'cronjob_alert'  => 0,

				'safe_browsing'  => 0,

				'notify_email'   => '',

				'white_list'     => ''

			)

		);



		return ( empty($options[$field]) ? '' : $options[$field] );

	}

	public static function _update_option($field, $value)

	{

		self::_update_options(

			array(

				$field => $value

			)

		);

	}


	public static function _update_options($data)

	{

		update_option(

			'antivirus',

			array_merge(

				(array)get_option('antivirus'),

				$data

			)

		);

	}

	public static function _add_scheduled_hook()

	{

		if ( ! wp_next_scheduled('antivirus_weekly_cronjob') ) {

			wp_schedule_event(

				time(),

				'weekly',

				'antivirus_weekly_cronjob'

			);

		}

	}
	public static function clear_scheduled_hook()

	{

		if ( wp_next_scheduled('antivirus_weekly_cronjob') ) {

			wp_clear_scheduled_hook('antivirus_weekly_cronjob');

		}

	}

	public static function do_weekly_cronjob()

	{


		if ( ! self::_get_option('cronjob_enable') ) {

			return;

		}


		self::load_plugin_lang();

		self::_check_safe_browsing();

		self::_check_blog_internals();

	}

	public static function _check_safe_browsing()

	{


		if ( ! self::_get_option('safe_browsing') ) {

			return;

		}

		$response = wp_remote_get(

			sprintf(

				'https://sb-ssl.google.com/safebrowsing/api/lookup?client=wpantivirus&apikey=%s&appver=0.1&pver=3.0&url=%s',

				'ABQIAAAAsu9cf81zMEioUOLBi7TrhhTJnIkNNG4BG3awC5RGoTZgJ-xX-A',

				urlencode( get_bloginfo('url') )

			),

			array(

				'sslverify' => false

			)

		);




		if ( is_wp_error($response) ) {

			return;

		}




		if ( wp_remote_retrieve_response_code($response) == 204 ) {

			return;

		}




		self::_send_warning_notification(

			esc_html__('Safe Browsing Alert', 'antivirus'),

			sprintf(

				"%s\r\nhttp://www.google.com/safebrowsing/diagnostic?site=%s&hl=%s",

				esc_html__('Please check the Google Safe Browsing diagnostic page:', 'antivirus'),

				urlencode( get_bloginfo('url') ),

				substr(get_locale(), 0, 2)

			)

		);

	}

	public static function _check_blog_internals()

	{

		if ( ! self::_check_theme_files() && ! self::_check_permalink_structure() ) {

			return;

		}



		self::_send_warning_notification(

			esc_html__('Virus suspected', 'antivirus'),

			sprintf(

				"%s\r\n%s",

				esc_html__('The weekly antivirus scan of your blog suggests alarm.', 'antivirus'),

				get_bloginfo('url')

			)

		);




		self::_update_option(

			'cronjob_alert',

			1

		);

	}




	public static function _send_warning_notification($subject, $body)

	{


		if ( $email = self::_get_option('notify_email') ) {

			$email = sanitize_email($email);

		}	else {

			$email = get_bloginfo('admin_email');

		}




		wp_mail(

			$email,

			sprintf(

				'[%s] %s',

				get_bloginfo('name'),

				$subject

			),

			sprintf(

				"%s\r\n\r\n\r\n%s\r\n%s\r\n",

				$body,

				esc_html__('Notify message by AntiVirus for WordPress', 'antivirus'),

				esc_html__('http://worldwebarts.com', 'antivirus')

			)

		);

	}


	public static function add_sidebar_menu()

	{

		add_action(

			'admin_print_styles-' . $page,

			array(

				__CLASS__,

				'add_enqueue_style'

			)

		);

		add_action(

			'admin_print_scripts-' . $page,

			array(

				__CLASS__,

				'add_enqueue_script'

			)

		);

	}

	public static function add_enqueue_script()

	{


		$data = get_plugin_data(__FILE__);




		wp_register_script(

			'av_script',

			plugins_url('js/script.min.js', __FILE__),

			array('jquery'),

			$data['Version']

		);




		wp_enqueue_script('av_script');




		wp_localize_script(

			'av_script',

			'av_settings',

			array(

				'nonce' => wp_create_nonce('av_ajax_nonce'),

				'theme'	=> urlencode(self::_get_theme_name()),

				'msg_1'	=> esc_html__('There is no virus', 'antivirus'),

				'msg_2' => esc_html__('View line', 'antivirus'),

				'msg_3' => esc_html__('Scan finished', 'antivirus')

			)

		);

	}



	public static function add_enqueue_style()

	{


		$data = get_plugin_data(__FILE__);



		wp_register_style(

			'av_css',

			plugins_url('css/style.min.css', __FILE__),

			array(),

			$data['Version']

		);




		wp_enqueue_style('av_css');

	}
	public static function _get_current_theme()

	{


		if ( function_exists('wp_get_theme') ) {


			$theme = wp_get_theme();

			$name = $theme->get('Name');

			$slug = $theme->get_stylesheet();

			$files = self::get_all_files('php', 4);




			if ( empty($name) OR empty($files) ) {

				return false;

			}




			return array(

				'Name' => $name,

				'Slug' => $slug,

				'Template Files' => $files

			);


		} else {

			if ( $themes = get_themes() ) {


				if ( $theme = get_current_theme() ) {

					if ( array_key_exists((string)$theme, $themes) ) {

						return $themes[$theme];

					}

				}

			}



		}



		return false;

	}

	public static function _get_theme_files()

	{


		if ( ! $theme = self::_get_current_theme() ) {

			return false;

		}




		if ( empty($theme['Template Files']) ) {

			return false;

		}




		return array_unique(

			array_map(

				create_function(

					'$v',

					'return str_replace(array(ABSPATH, ""), "", $v);'

				),

				$theme['Template Files']

			)

		);

	}


	public static function _get_theme_name()

	{

		if ( $theme = self::_get_current_theme() ) {

			if ( ! empty($theme['Slug']) ) {

				return $theme['Slug'];

			}

			if ( ! empty($theme['Name']) ) {

				return $theme['Name'];

			}

		}



		return false;

	}



	public static function _get_white_list()

	{

		return explode(

			':',

			self::_get_option('white_list')

		);

	}


	public static function get_ajax_response()

	{


		check_ajax_referer('av_ajax_nonce');




		if ( empty($_POST['_action_request']) ) {

			exit();

		}




		$values = array();

		$output = '';




		switch ($_POST['_action_request']) {

			case 'get_theme_files':

				self::_update_option(

					'cronjob_alert',

					0

				);



				$values = self::_get_theme_files();

			break;



			case 'check_theme_file':

				if ( ! empty($_POST['_theme_file']) && $lines = self::_check_theme_file($_POST['_theme_file']) ) {

					foreach( $lines as $num => $line ) {

						foreach( $line as $string ) {

							$values[] = $num;

							$values[] = htmlentities($string, ENT_QUOTES);

							$values[] = md5($num . $string);

						}

					}

				}

			break;



			case 'update_white_list':

				if ( ! empty($_POST['_file_md5']) ) {

					self::_update_option(

						'white_list',

						implode(

							':',

							array_unique(

								array_merge(

									self::_get_white_list(),

									array($_POST['_file_md5'])

								)

							)

						)

					);



					$values = array($_POST['_file_md5']);

				}

				break;



			default:

				break;

		}




		if ( $values ) {

			$output = sprintf(

				"['%s']",

				implode("', '", $values)

			);




			header('Content-Type: plain/text');




			echo sprintf(

				'{data:%s, nonce:"%s"}',

				$output,

				$_POST['_ajax_nonce']

			);

		}




		exit();

	}

	public static function _get_file_content($file)

	{

		return file(ABSPATH . $file);

	}




	public static function get_all_files( $type = null, $depth = 0, $search_parent = false ) {

		$files = (array) self::scandir( ABSPATH, $type, $depth );



		if ( $search_parent && $this->parent() )

			$files += (array) self::scandir( $this->get_template_directory(), $type, $depth );



		return $files;

	}

	private static function scandir( $path, $extensions = null, $depth = 0, $relative_path = '' ) {

		if ( ! is_dir( $path ) )

			return false;



		if ( $extensions ) {

			$extensions = (array) $extensions;

			$_extensions = implode( '|', $extensions );

		}



		$relative_path = trailingslashit( $relative_path );

		if ( '/' == $relative_path )

			$relative_path = '';



		$results = scandir( $path );

		$files = array();



		foreach ( $results as $result ) {

			if ( '.' == $result[0] )

				continue;

			if ( is_dir( $path . '/' . $result ) ) {

				if ( ! $depth || 'CVS' == $result )

					continue;

				$found = self::scandir( $path . '/' . $result, $extensions, $depth - 1 , $relative_path . $result );

				$files = array_merge_recursive( $files, $found );

			} elseif ( ! $extensions || preg_match( '~\.(' . $_extensions . ')$~', $result ) ) {

				$files[ $relative_path . $result ] = $path . '/' . $result;

			}

		}



		return $files;

	}



	public static function _get_dotted_line($line, $tag, $max = 100)

	{


		if ( ! $line OR ! $tag ) {

			return false;

		}




		if ( strlen($tag) > $max ) {

			return $tag;

		}




		$left = round( ($max - strlen($tag)) / 2 );




		$tag = preg_quote($tag);




		$output = preg_replace(

			'/(' .$tag. ')(.{' .$left. '}).{0,}$/',

			'$1$2 ...',

			$line

		);

		$output = preg_replace(

			'/^.{0,}(.{' .$left. ',})(' .$tag. ')/',

			'... $1$2',

			$output

		);



		return $output;

	}




	public static function _php_match_pattern()

	{

		return '/(assert|file_get_contents|curl_exec|popen|proc_open|unserialize|eval|base64_encode|base64_decode|create_function|exec|shell_exec|system|passthru|ob_get_contents|file|curl_init|readfile|fopen|fsockopen|pfsockopen|fclose|fread|file_put_contents)\s*?\(/';

	}


	public static function _check_file_line($line = '', $num)

	{

		$line = trim((string)$line);


		if ( ! $line OR ! isset($num) ) {

			return false;

		}

		$results = array();

		$output = array();

		preg_match_all(

			self::_php_match_pattern(),

			$line,

			$matches

		);


		if ( $matches[1] ) {

			$results = $matches[1];

		}


		preg_match_all(

			'/[\'\"\$\\ \/]*?([a-zA-Z0-9]{' .strlen(base64_encode('sergej + swetlana = love.')). ',})/', 
			$line,

			$matches

		);


		if ( $matches[1] ) {

			$results = array_merge($results, $matches[1]);

		}

		preg_match_all(

			'/<\s*?(i?frame)/',

			$line,

			$matches

		);


		if ( $matches[1] ) {

			$results = array_merge($results, $matches[1]);

		}


		preg_match(

			'/get_option\s*\(\s*[\'"](.*?)[\'"]\s*\)/',

			$line,

			$matches

		);


		if ( $matches && $matches[1] && self::_check_file_line(get_option($matches[1]), $num) ) {

			array_push($results, 'get_option');

		}


		if ( $results ) {


			$results = array_unique($results);


			$md5 = self::_get_white_list();


			foreach( $results as $tag ) {

				$string = str_replace(

					$tag,

					'@span@' .$tag. '@/span@',

					self::_get_dotted_line($line, $tag)

				);


				if ( ! in_array(md5($num . $string), $md5) ) {

					$output[] = $string;

				}

			}



			return $output;

		}



		return false;

	}


	public static function _check_theme_files()

	{

		if ( ! $files = self::_get_theme_files() ) {

			return false;

		}


		$results = array();

		foreach( $files as $file ) {

			if ( $result = self::_check_theme_file($file) ) {

				$results[$file] = $result;

			}

		}


		if ( ! empty($results) ) {

			return $results;

		}



		return false;

	}


	public static function _check_theme_file($file)

	{

		if ( ! $file ) {

			return false;

		}
		if ( ! $content = self::_get_file_content($file) ) {

			return false;

		}


		$results = array();


		foreach( $content as $num => $line ) {

			if ( $result = self::_check_file_line($line, $num) ) {

				$results[$num] = $result;

			}

		}


		if ( ! empty($results) ) {

			return $results;

		}



		return false;

	}



	public static function _check_permalink_structure()

	{

		if ( $structure = get_option('permalink_structure') ) {


			preg_match_all(

				self::_php_match_pattern(),

				$structure,

				$matches

			);


			if ( $matches[1] ) {

				return $matches[1];

			}

		}



		return false;

	}

	public static function show_dashboard_notice() {

		if ( ! self::_get_option('cronjob_alert') ) {

			return;

		}


		echo sprintf(

			'<div class="error"><p><strong>%1$s:</strong> %2$s <a href="%3$s">%4$s &rarr;</a></p></div>',

			esc_html__('Virus suspected', 'antivirus'),

			esc_html__('The weekly antivirus scan of your blog suggests alarm.', 'antivirus'),

			add_query_arg(

				array(

					'page' => 'antivirus'

				),

				admin_url('admin.php')

			),

			esc_html__('Manual malware scan', 'antivirus')

		);

	}

}