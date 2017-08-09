<?php if(! defined('WWA_PLUGIN_PREFIX')) return;

class WwaWatch extends WwaPlugin

{



    public static function userPasswordUpdate(){

        add_action('edit_user_profile_update', array(get_class(), '_watchUserInfoUpdated'));

        add_action('personal_options_update', array(get_class(), '_watchUserInfoUpdated'));

    }

    public static function _getUserInfo($userID)

    {

        global $wpdb;



        $t = $wpdb->prefix.'users';

        $username = $wpdb->get_var("SELECT user_login FROM $t WHERE ID=$userID");

        $user = new WP_User( $userID );

        $userRole = (empty($user->roles[0]) ? '' : $user->roles[0]);

        return array(

            'userName' => $username,

            'userRole' => $userRole

        );

    }


    public static function _watchUserInfoUpdated($userID)

    {

        if(! empty($_POST['pass1'])){

            $userInfo = self::_getUserInfo($userID);

            $userName = $userInfo['userName'];

            $userRole = $userInfo['userRole'];

            if($userRole == 'administrator')

            {

                global $wwaPluginAlertsArray;

                $actionName = $wwaPluginAlertsArray['watch_admin_password_update']['name'];

                $alertType = $wwaPluginAlertsArray['watch_admin_password_update']['type'];



                self::alert($actionName, $alertType, WWA_PLUGIN_ALERT_MEDIUM,

                    sprintf(__('Administrator (%s) password updated'), $userName),

                    __('<p>This alert is generated every time an administrator\'s password is updated.</p>'));

            }

        }

    }



}