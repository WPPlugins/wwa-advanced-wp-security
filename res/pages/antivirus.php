<?php if(! WwaUtil::canLoad()) { return; } ?>

<?php if(! WwaUtil::isAdministrator()) { return; } ?>



<?php


if ( ! empty($_POST) ) {


	check_admin_referer('antivirus');




	$options = array(

		'cronjob_enable' => (int)(!empty($_POST['av_cronjob_enable'])),

		'notify_email'	 => is_email(@$_POST['av_notify_email']),

		'safe_browsing'  => (int)(!empty($_POST['av_safe_browsing']))

	);




	if ( empty($options['cronjob_enable']) ) {

		$options['notify_email'] = '';

		$options['safe_browsing'] = 0;

	}




	if ( $options['cronjob_enable'] && ! get_option('cronjob_enable') ) {

		AntiVirus::_add_scheduled_hook();

	} else if ( ! $options['cronjob_enable'] && get_option('cronjob_enable') ) {

		AntiVirus::_clear_scheduled_hook();

	}




	AntiVirus::_update_options($options); ?>



	<div id="message" class="updated fade">

		<p>

			<strong>

				<?php _e('Settings saved.') ?>

			</strong>

		</p>

	</div>

<?php 

} 

AntiVirus::add_enqueue_script();

AntiVirus::add_enqueue_style();

?>

<script type='text/javascript' src='js/script.min.js'></script>

<div class="wrap wwaplugin_content">

    <h2>

        AntiVirus

    </h2>



    <table class="form-table">

        <tr valign="top">

            <th scope="row">

                <?php esc_html_e('Manual malware scan', 'antivirus') ?>

            </th>

            <td>

                <div class="inside" id="av_manual_scan">

                    <p>

                        <a href="#" class="button button-primary">

                            <?php esc_html_e('Scan the theme templates now', 'antivirus') ?>

                        </a>

                        <span class="alert"></span>

                    </p>

                    <div class="output"></div>

                </div>

            </td>

        </tr>

    </table>





    <form method="post" action="">

        <?php wp_nonce_field('antivirus') ?>



        <table class="form-table">

            <tr valign="top">

                <th scope="row">

                    <?php esc_html_e('Weekly malware scan', 'antivirus') ?>

                </th>

                <td>

                    <fieldset>

                        <label for="av_cronjob_enable">

                            <input type="checkbox" name="av_cronjob_enable" id="av_cronjob_enable" value="1" <?php checked(AntiVirus::_get_option('cronjob_enable'), 1) ?> />

                            <?php esc_html_e('Check the theme templates for malware', 'antivirus') ?>

                        </label>



                        <p class="description">

                            <?php if ( $timestamp = wp_next_scheduled('antivirus_weekly_cronjob') ) {

                                echo sprintf(

                                    '%s: %s',

                                    esc_html__('Next Run', 'antivirus'),

                                    date_i18n('d.m.Y H:i:s', $timestamp + get_option('gmt_offset') * 3600 * 7)

                                );

                            } ?>

                        </p>





                        <br />





                        <label for="av_safe_browsing">

                            <input type="checkbox" name="av_safe_browsing" id="av_safe_browsing" value="1" <?php checked(AntiVirus::_get_option('safe_browsing'), 1) ?> />

                            <?php esc_html_e('Malware detection by Google Safe Browsing', 'antivirus') ?>

                        </label>



                        <p class="description">

                            <?php esc_html_e('Diagnosis and notification in suspicion case', 'antivirus') ?>

                        </p>





                        <br />





                        <label for="av_notify_email">

                            <input type="text" name="av_notify_email" id="av_notify_email" value="<?php esc_attr_e(AntiVirus::_get_option('notify_email')) ?>" class="regular-text" placeholder="<?php esc_attr_e('Email address for notifications', 'antivirus') ?>" />

                        </label>



                        <p class="description">

                            <?php esc_html_e('If the field is empty, the blog admin will be notified', 'antivirus') ?>

                        </p>

                    </fieldset>

                </td>

            </tr>



            <tr valign="top">

                <th scope="row">

                    <input type="submit" class="button button-primary" value="<?php _e('Save Changes') ?>" />

                </th>

            </tr>

        </table>

    </form>

</div>