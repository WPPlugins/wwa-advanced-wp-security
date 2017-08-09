<?php if(! WwaUtil::canLoad()) { return; } ?>

<?php


	$showPage = false;          

	$isWPConfigWritable = false; 

	$cdtpIsPostBack = false;    

    $acxInfoMessage = $wwa_Message = '';

	$new_prefix = '';

    $canAlter = false;  



?>

<?php


	 if ($_SERVER['REQUEST_METHOD'] == 'POST')

	 {

		 if (!empty($_POST['newadminuser']) && isset($_POST['changeAdminUser']))

		 {

			$cdtpIsPostBack = true;



			if (function_exists('wp_nonce_field')) {

				check_admin_referer('change-admin_user');

			}



             $wpdb = $GLOBALS['wpdb'];

             if (empty($wpdb))

             {

                 wp_die(__('An internal error has occurred (empty $wpdb). Please inform the plug-in author about this error. Thank you!'));

             }



             $new_user = $_POST['newadminuser'];




             if(! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', $new_user)){

                 $acxInfoMessage .=  sprintf('Invalid table name prefix: %s', htmlentities($new_user));

                 $new_prefix = 'admin';

             }

             else

             {



                 if (strlen($new_user) < strlen($_POST['newadminuser'])){

                     $acxInfoMessage .= __('You used some characters disallowed in Table names. The sanitized prefix will be used instead').': '.$new_user;

                 }

                 if ($new_prefix == 'admin') {

                     if (!empty($acxInfoMessage)) { $acxInfoMessage .= '<br/>'; }

                     $acxInfoMessage .= __('No change! Please select a different table prefix value.');

                 }

                 else

                 {

                     $result = WwaAdminuser::updateAdminUsername($new_user);

					 if ($result == 'updated')



						 $acxInfoMessage .= '<br/><span class="acx-notice-success acx-icon-alert-success">'.__('The <strong>admin</strong> username has been successfully updated!').'</span>';



					 else

						 $acxInfoMessage .= '<br/>'.__('There are some errors occured');

                 }

             }

		}

	}

    else { $new_prefix = $old_prefix; }



if(empty($new_prefix)){

    $new_prefix = $old_prefix;

}

?>



<?php


?>

<div class="acx-section-box">

	

    <?php

    $adminuser = WwaAdminuser::adminUsername();

	?>

	<?php

    

	if($adminuser == 'admin')

	{

	?>

    <form action="#cdtp" method="post" name="adminuserchange">

        <?php if (function_exists('wp_nonce_field')) { wp_nonce_field('change-admin_user'); } ?>

        <p><?php echo sprintf(__('Change the current Admin Username - <input type="text" name="newadminuser" value="%s" size="20" maxlength="15"/> .'), $adminuser); ?></p>

        <p><?php echo __('Allowed characters: all latin alphanumeric as well as the <strong>_</strong> (underscore).');?></p>

        <input type="submit" class="button-primary" name="changeAdminUser" value="<?php echo __('Update');?>" />

    </form>

    <?php

	}

	else

	{

	?>

    <p><?php echo sprintf(__('Current Admin User is different than admin.')); ?></p>

    <?php	

	}

	?>

</div>

<div id="cdtp">

    <?php


        if ($cdtpIsPostBack){

            if (!empty($acxInfoMessage)){ echo '<p class="acx-info-box">',$acxInfoMessage,'</p>'; }

            if (!empty($wwa_Message)) { echo '<p class="acx-info-box">',$wwa_Message,'</p>'; }

        }

        else {

            if (!empty($wwa_Message)) { echo '<p class="acx-info-box">',$wwa_Message,'</p>'; }

        }

    ?>

</div>

