<?php if(! WwaUtil::canLoad()) { return; } ?>





<div class="">

    <blockquote>

        <p><?php echo __('You can change the permission of any file and folders in the server.'); ?></p>

    </blockquote>

</div>

<?php

function file_ext($path) 

{

	return end(explode('.', $path));	

}



function valid_file($ext)

{

	$allowed_types = array("php", "htaccess");	

	if(in_array($ext, $allowed_types))

		return true;

	else

		return false;

}



?>



<style type="text/css">

#folder_select ul{padding-left:10px;}

</style>

<?php

$plugin_loc = plugin_dir_url( __FILE__ );

$root_folder = substr(ABSPATH, 0, strlen(ABSPATH)-1);



$type = $_GET['type'];

$cur_per = isset($_REQUEST['cur_per'])?$_REQUEST['cur_per']:'';

if($cur_per != '')

	$current_dir = isset($_SESSION['current_dir'])?$_SESSION['current_dir']:$root_folder;

else

	$current_dir = $root_folder;



If(count($_POST) > 0){

	$all = $_POST['main_chk'];

	$check_file = $_POST['chk'];

	$per = $_POST['per'];

	if(count($check_file)){

		foreach($_POST['chk'] as $key){


			switch($per)

			{

				case '0444':

					chmod($key, 0444);

				break;

				case '0644':

					chmod($key, 0644);

				break;

			}

		}

	}

	echo "Permission changed successfully to $per.";

}



function list_dirs($dir_path)

{

	$cur_per = isset($_REQUEST['cur_per'])?$_REQUEST['cur_per']:'';

	if($cur_per != '')

		$current_dir = isset($_SESSION['current_dir'])?$_SESSION['current_dir']:$root_folder;

	else

		$current_dir = $root_folder;

    $ffs = scandir($dir_path);

    foreach($ffs as $ff){

		$selected = '';

        if($ff != '.' && $ff != '..'){

			$path = $dir_path.'/'.$ff;

            if(is_dir($path)){ 

				if($path == $current_dir)

					$selected = ' selected = "selected"';

				echo '<option value="'.$path.'"'.$selected.'>'.$path.'</option>';

				list_dirs($path);

			}

			else

			{


			}			

        }

    }

}



function listFolderFiles($dir){

    $ffs = scandir($dir);


    echo '<ul>';

	echo '<li>'.' <h4>'.$dir.'</h4></li>';

	$counter = 0;


    foreach($ffs as $ff){

		echo "<li><ul>";

        if($ff != '.' && $ff != '..'){

			$path = $dir.'/'.$ff;

            if(is_dir($path)){ 		


			}else{

				$ext = file_ext($path);

				if(valid_file($ext))

				{

					$counter++;

	            	echo '<li>'.'<input type="checkbox" name="chk[]" class="all" value="'.$path.'" />  '.$path." <b>(".substr(sprintf('%o', fileperms($path)), -4).')</b>';

            		echo '</li>';

				}

			}

        }

		echo "</ul></li>";

    }

    echo '</ul>';

}





?>

<script type="text/javascript">

function select_all()

{

check_all = document.getElementById('check_all');

    var nodes = document.getElementsByClassName("all");

    if(check_all.checked==true){

		for (var idxnode in nodes){

			var node = nodes[idxnode];

			node.checked=true;

	   }

 	}else{

		for (var idxnode in nodes){

			var node = nodes[idxnode];

			node.checked=false;

	   }

	}

}

function validate(){

	var obj = document.frm;

	var error = "";

	var flag = true;

	var nodes = document.getElementsByClassName("all");

	var rkids = "";

	var i = 0;

	for (var idxnode in nodes){

		var node = nodes[idxnode];

		if (node.checked == true){

			i++;

		}

	}

	if(i == 0){

		flag = false;

		error += "Please check any one  \n";

	}

	if(flag == false){

		alert(error);

		return false;

	}

	else if(obj.per.value == 0)

	{

		alert("Please select the permission  \n");

		return false;

	}

	else

	{

		obj.submit();

		return true;

	}

}

</script>





<script type="text/javascript">

    <?php if($forceLoad) echo 'var wwaAjaxForceLoad = 1;'; else echo 'var wwaAjaxForceLoad = 0;'?>

    (function($){

        $(document).ready( function()

        {

            function _createLoader($){

                var imgPath = "<?php echo WwaUtil::imageUrl('ajax-loader.gif') ?>";

                var text = "<?php echo __('Loading data...');?>";

                return $('<span id="ajaxLoaderRemove"><img src="'+imgPath+'" title="'+text+'" alt="'+text+'"/><span>'+text+'</span></span>');

            }

            function _showLoader($parentElement, $loader){ $parentElement.append($loader); }

            function _hideLoader(stringId) { $('#'+stringId).remove(); }



            var loader = _createLoader($);

				var $table = $("#wwaTrafficScanTable"),

                nonce = $table.attr("data-nonce"),

                $tbody = $('#the-list', $table)

                ,lastID = $table.attr("data-lastid")

                ,loaderWrapper = $('#loaderWrapper');

            function wwapluginLoadData()

            {

                _showLoader(loaderWrapper, loader);

                $.ajax({

                    type : "post",

                    dataType : "json",

                    cache: false,

                    url : "<?php echo admin_url( 'admin-ajax.php' );?>",

                    data: $('#frm').serialize(),

                    success: function(response) {

                        _hideLoader('ajaxLoaderRemove');

                        if(response.type == "success") {

                            if(response.data.length > 20){ $('#folder_select').html(response.data); }

                        }

                        else { alert("An error occurred while trying to load data. Please try again in a few seconds."); }

                    }

                });

                wwaAjaxForceLoad = 0;

            }

			$('#sel_folder').change(function(){

            	wwapluginLoadData();

			});



          

            var settingsLegend = $('#settingsLegend');

            var settingsContent = $('#settingsContent');

            settingsLegend.toggle(

                function(){

                    settingsContent.hide();

                    settingsLegend.parent().removeClass('wwaPluginFieldsetSettingsExpanded').addClass('wwaPluginFieldsetSettingsCollapsed');

                },

                function(){

                    settingsLegend.parent().removeClass('wwaPluginFieldsetSettingsCollapsed').addClass('wwaPluginFieldsetSettingsExpanded');

                    settingsContent.show();

                }

            );

       });

    })(jQuery);

</script>



<p id="loaderWrapper"></p>

<form name="frm" id="frm" method="post" action="">

<input type="hidden" name="action" value="ajaxFileFoldersData" />

<div style="float:left;"><h4><input type="checkbox" name="main_chk" id="check_all" value="1" onclick="select_all();" /> <label for="check_all">Check All</label></h4></div>

<div style="float:left; margin-top:14px; padding-left:20px;"><strong>Set Permission</strong>

<select name="per" id="per">

<option value="0">Select Permission</option>

<option value="0644"<?php echo ($cur_per == '0644')?' selected = "selected"':''?>>0644</option>

<option value="0444"<?php echo ($cur_per == '0444')?' selected = "selected"':''?>>0444</option>

</select></div>



<div style="float:left; margin-top:14px; padding-left:20px;">



<?php

	echo '<select name="sel_folder" id="sel_folder">';

	echo '<option value="'.$root_folder.'">Select Folder</option>';

    list_dirs($root_folder);

	echo '</select>';



?>

</div>

<br /><br /><br />



<ul id="folder_select">

<?php

listFolderFiles($current_dir);

?>

</ul>



<input type="button" name="button" value="Send" onclick="return validate();"/>

</form>