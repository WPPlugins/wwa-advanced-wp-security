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

	WwaAdminurl::admin_init();

	echo '<span class="acx-icon-alert-success">'.__('The admin url has been successfully updated!').'</span>';

}

?>



<?php
?>

<div class="acx-section-box">

	<form action="" method="post" name="changeadminurl">

		<?php 

        echo '<p><input id="rwl-admin-input" type="checkbox" name="rwl_admin" value="1" ' . checked( get_option( 'rwl_admin' ), true, false ) . '> Enabling this option will redirect any admin requests to the new login page if not logged in, but beware that this will reveal the location of it.</p>';

        echo '<p><code>' . home_url() . '/</code> <input id="rwl-page-input" type="text" name="rwl_page" value="' . get_option( 'rwl_page' ) . '"> <code>/</code></p>';

        ?>    

        <input type="submit" class="button-primary" name="changeAdminurl" value="<?php echo __('Update');?>" />

    </form>

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

