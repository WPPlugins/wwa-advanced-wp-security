<?php if(! defined('WWA_PLUGIN_PREFIX')) return;

class WwaFileFolderPermissions

{

    private function __construct(){}

    private function __clone(){}


    final public static function ajaxFileFoldersData($maxEntries = 0)

    {

        $result = array();

        $forceLoad = (bool)$_REQUEST['forceLoad'];

		if(isset($_REQUEST) && count($_REQUEST) > 0)

		{

			$_SESSION['current_dir'] = $_REQUEST['sel_folder'];

			$data = self::list_dirs($_REQUEST['sel_folder']);

		}



		$result['type'] = 'success';

		$result['data'] = $data;

		$result = json_encode($result);

		exit($result);

    }

	

	public function file_ext($path) 

	{

		return end(explode('.', $path));	

	}

	

	public function valid_file($ext)

	{

		$allowed_types = array("php", "htaccess");	

		if(in_array($ext, $allowed_types))

			return true;

		else

			return false;

	}

	

	public function list_dirs($dir_path)

	{

		$data = '';

		$_SESSION['current_dir'] = $dir_path;

		$data .= '<li>'.' <h4>'.$dir_path.'</h4></li>';

		$ffs = scandir($dir_path);

		foreach($ffs as $ff){

			if($ff != '.' && $ff != '..'){

				$path = $dir_path.'/'.$ff;

				if(is_dir($path)){


				}

				else{

					$ext = self::file_ext($path);

					if(self::valid_file($ext))

					{

						$data .= '<li>';

						$data .= '<input type="checkbox" name="chk[]" class="all" value="'.$path.'" />  '.$path." <b>(".substr(sprintf('%o', fileperms($path)), -4).')</b>';

						$data .= '</li>';

					}		

				}

			}

		}

		return $data;

	}

	

}

