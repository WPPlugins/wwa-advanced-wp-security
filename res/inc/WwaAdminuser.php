<?php if(! defined('WWA_PLUGIN_PREFIX')) return;

class WwaAdminuser extends WwaPlugin

{

    public static function adminUsername()

    {

        global $wpdb, $wwaPluginAlertsArray;

        $actionName = $wwaPluginAlertsArray['check_username_admin']['name'];

        $alertType = $wwaPluginAlertsArray['check_username_admin']['type'];



        $u = $wpdb->get_var("SELECT `ID` FROM $wpdb->users WHERE user_login='admin';");

        if(empty($u)){

            self::alert($actionName, $alertType, WWA_PLUGIN_ALERT_INFO,

                sprintf(__('User <strong>"%s"</strong> (with administrative rights) was not found'), 'admin'),

                sprintf(__('<p>One well known and dangerous WordPress security vulnerability is User Enumeration, in which a

                            malicious user is able to enumerate a valid WordPress user account to launch a brute force attack against it.

                            In order to protect from this type of attack, it is important not to have the default <a href="%s" target="%s">WordPress administrator</a>

                            username enabled on your blog.</p>'), 'http://www.worldwebarts.com/', '_blank')

            );

        }

        else {

           

            $userRole = $wpdb->get_var("SELECT meta_value FROM ".$wpdb->usermeta. " WHERE user_id = $u AND meta_key = '".$wpdb->prefix."user_level'");

            if(! empty($userRole)){

                $userRole = intval($userRole);

                if(in_array($userRole, array(8,9,10))){

                    

                    return 'admin';

                }

            }

        }

		

	}

	

	

  

    public static function updateAdminUsername($newuser = '')

    {

        global $wpdb, $wwaPluginAlertsArray;

        $actionName = $wwaPluginAlertsArray['check_username_admin']['name'];

        $alertType = $wwaPluginAlertsArray['check_username_admin']['type'];



        $u = $wpdb->get_var("SELECT `ID` FROM $wpdb->users WHERE user_login = 'admin';");

        if(empty($u)){

            self::alert($actionName, $alertType, WWA_PLUGIN_ALERT_INFO,

                sprintf(__('No user exists with username admin</p>'))

            );

        }

        else {

			$uj = $wpdb->get_var("SELECT `ID` FROM $wpdb->users WHERE user_login = '".$newuser."';");

			if(empty($uj))

			{			

				$qry = "UPDATE ".$wpdb->users. " SET user_login='".$newuser."' WHERE ID = $u";

				$userRole = $wpdb->query($qry);				

				return 'updated';

			}

			else

			{

				self::alert($actionName, $alertType, WWA_PLUGIN_ALERT_INFO,

					sprintf(__('User already exists with username</p>'))

				);				

				return false;	

			}

        }

		

	}

	

}