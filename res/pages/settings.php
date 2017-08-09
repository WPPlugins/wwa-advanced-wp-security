<?php if(! WwaUtil::canLoad()) { return; } ?>
<?php if(! WwaUtil::isAdministrator()) { return; } ?>
<?php
$rm = strtoupper($_SERVER['REQUEST_METHOD']);
$settings = WwaPlugin::getSettings();

$rssWidgetData = get_option('WWA-RSS-WGT-DISPLAY');
$rssWidgetChecked = false;
if(!empty($rssWidgetData) && ($rssWidgetData == 'yes')){
    $rssWidgetChecked = true;
}

if('POST' == $rm)
{
    if(isset($_POST['wwaplugin_update_settings_field'])){
        if(!wp_verify_nonce($_POST['wwaplugin_update_settings_field'],'wwaplugin_update_settings')){
            wp_die(__('Invalid request.'));
        }
    }
    else {wp_die(__('Invalid request.'));}

    if(isset($_POST['updateSettingsButton']))
    {
        $postData = $_POST['chk_options'];
        parse_str($postData, $checkboxes);

        if(empty($checkboxes)){
            foreach($settings as &$entry){
                $entry['value'] = 0;
            }
        }
        else {
            foreach($checkboxes as $method => $value){
                $settings[$method]['value'] = $value;
            }
        }

        if(empty($_POST['chk_rss_wgt_display'])){
            $rssWidgetChecked = false;
            update_option('WWA-RSS-WGT-DISPLAY', 'no');
        }
        else {
            $rssWidgetChecked = true;
            update_option('WWA-RSS-WGT-DISPLAY', 'yes');
        }

        update_option(WWA_PLUGIN_SETTINGS_OPTION_NAME, $settings);
        $settings = get_option(WWA_PLUGIN_SETTINGS_OPTION_NAME);
    }
    elseif(isset($_POST['deleteRssDataButton']))
    {
        delete_option('wwa_feed_data');
    }
}
?>
<div class="wrap wwaplugin_content">
    <h2><?php echo WWA_PLUGIN_NAME.' - '. __('Settings');?></h2>

    <p class="clear"></p>
    <div style="clear: both; display: block;">
        <div class="metabox-holder">
            <div class="inner-sidebar1 postbox">
                <h3 class="hndle" style="cursor: default;"><span><?php echo __('Settings');?></span></h3>
                <div class="inside acx-section-box" style="padding-left:0;">
                    <form method="post">
                        <?php wp_nonce_field('wwaplugin_update_settings','wwaplugin_update_settings_field'); ?>
                        <?php
                            $i = 0;
                            foreach($settings as $k => $entry){
                                if(is_array($entry))
                                {
									$title = '';
									if($entry['name'] == 'fix_hideWpVersion')
										$title = 'Hide WordPress version for all users but administrators';
									if($entry['name'] == 'fix_removeWpMetaGeneratorsFrontend')
										$title = 'Remove various meta tags generators from the blog&rsquo;s head tag for non-administrators.';
									if($entry['name'] == 'fix_removeReallySimpleDiscovery')
										$title = 'Remove Really Simple Discovery meta tags from front-end';
									if($entry['name'] == 'fix_removeWindowsLiveWriter')
										$title = 'Remove Windows Live Writer meta tags from front-end';
									if($entry['name'] == 'fix_disableErrorReporting')
										$title = 'Disable error reporting (php + db) for all but administrators';
									if($entry['name'] == 'fix_removeCoreUpdateNotification')
										$title = 'Remove core update notifications from back-end for all but administrators';
									if($entry['name'] == 'fix_removePluginUpdateNotifications')
										$title = 'Remove plug-ins update notifications from back-end';
									if($entry['name'] == 'fix_removeThemeUpdateNotifications')
										$title = 'Remove themes update notifications from back-end';
									if($entry['name'] == 'fix_removeLoginErrorNotificationsFrontEnd')
										$title = 'Remove login error notifications from front-end';
									if($entry['name'] == 'fix_hideAdminNotifications')
										$title = 'Hide admin notifications for non admins.';
									if($entry['name'] == 'fix_preventDirectoryListing')
										$title = 'Try to create the index.php file in the wp-content, wp-content/plugins, wp-content/themes and wp-content/uploads directories to prevent directory listing';
									if($entry['name'] == 'fix_removeWpVersionFromLinks')
										$title = 'Remove the version parameter from urls';
									if($entry['name'] == 'fix_emptyReadmeFileFromRoot')
										$title = 'Empty the content of the readme.html file from the root directory.';

                                    $chkID = "chk-$i";
                                    echo '<div class="acx-section-box wwaplugin-overflow">';
                                    echo '<label for="'.$chkID.'" class="wwaplugin-overflow">';
                                    echo '<span class="chk-settings wwaplugin_checkbox'.($entry['value'] ? ' wwaplugin_checkbox-active' : '').'" id="'.$chkID.'" data-bind="'.$entry['name'].'"><a>&nbsp;</a></span>';
                                    echo '<span>'.$title.'</span>';
                                    echo '</label>';
                                    echo '</div>';
                                    $i++;
                                }
                            }
                        ?>
                        <?php
                        echo '<div class="acx-section-box wwaplugin-overflow">';
                            echo '<label for="wwa_feed_data" class="wwaplugin-overflow">';
                            echo '<span class="chk-extra wwaplugin_checkbox'.($rssWidgetChecked ? ' wwaplugin_checkbox-active' : '').'" id="wwa_feed_data"><a>&nbsp;</a></span>';
                            echo '<span>'.__('Show the RSS widget in the dashboard').'</span>';
                            echo '</label>';
                        echo '</div>';
                        ?>

                        <input type="hidden" name="chk_options" id="chk_options" />
                        <input type="hidden" name="chk_rss_wgt_display" id="chk_rss_wgt_display" />
                        <div class="acx-section-box wwaplugin-overflow">
                            <input type="button" id="_resetButton" class="button button-secondary" style="width: 70px;"/>
                            <input type="submit" value="Update settings" class="button button-primary" name="updateSettingsButton"/>
                            <input type="submit" value="Delete rss data" class="button button-primary" name="deleteRssDataButton"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($){
        var resetButton = $('#_resetButton');
        var oneChecked = false;
        var checkboxes = $('.wwaplugin_checkbox');
        var entriesLiveTrafficInput = $('#max_number_live_traffic');

   
        $.each(checkboxes, function(i,v){
            var self = $(v);
            if(self.hasClass('wwaplugin_checkbox-active')){
                oneChecked = true;
            }
            self.parent('label').on('click', function(){
                if(self.hasClass('wwaplugin_checkbox-active')){
                    self.removeClass('wwaplugin_checkbox-active');
                }
                else { self.addClass('wwaplugin_checkbox-active'); }
            });
        });


        if(oneChecked){ resetButton.val('Clear all'); }
        else { resetButton.val('Select all'); }

        resetButton.click(function(){
            $(this).text(function(i, text){
                if($(this).val() == 'Clear all'){
                    $.each(checkboxes,function(i,v){
                        $(v).removeClass('wwaplugin_checkbox-active');
                    });
                    $(this).val('Select all');
                }
                else {
                    $.each(checkboxes,function(i,v){
                        $(v).addClass('wwaplugin_checkbox-active',true);
                    });
                    $(this).val('Clear all');
                }
            });
        });

        $('form').submit(function(){
            $('#chk_options').val('');
            var data = $('.chk-settings').map(function(){
                var self = $(this);
                return {name: self.attr('data-bind'), value: self.hasClass('wwaplugin_checkbox-active')?1:0};
            }).get();
            $('#chk_options').val($.param(data));
            $('#chk_rss_wgt_display').val($('.chk-extra').hasClass('wwaplugin_checkbox-active')?1:0);
        });
    });
</script>
