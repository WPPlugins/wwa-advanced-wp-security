<?php if(! defined('WWA_PLUGIN_PREFIX')) return;
if (!class_exists('WwaFolderbackup')) {
	class WwaFolderbackup extends WwaPlugin
	{
		public static function backupFolder()
		{
			
			
			$path = get_home_url();
			$folder_name = end(explode('/', $path));
			
			$zipname = date('Y/m/d/h/i/s');
			$str = $folder_name."-".$zipname.".zip";
			$str = str_replace("/", "-", $str);
			$zip_file_name  = $str;
			$str = WWA_PLUGIN_FOLDER_BACKUPS_DIR.$str;
			
			$source = ABSPATH;
			$destination = $str;
			$include_dir = 1;

			if (!extension_loaded('zip') || !file_exists($source)) {
				return false;
			}
		
			if (file_exists($destination)) {
				unlink ($destination);
			}
		
			$zip = new ZipArchive();
			if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
				return false;
			}
			$source = str_replace('\\', '/', realpath($source));
		
			if (is_dir($source) === true)
			{
		
				$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
		
				if ($include_dir) {
		
					$arr = explode("/",$source);
					$maindir = $arr[count($arr)- 1];
		
					$source = "";
					for ($i=0; $i < count($arr) - 1; $i++) { 
						$source .= '/' . $arr[$i];
					}
		
					$source = substr($source, 1);
		
					$zip->addEmptyDir($maindir);
		
				}
		
				foreach ($files as $file)
				{
					$file = str_replace('\\', '/', $file);
		
					if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
						continue;
		
					$file = realpath($file);
		
					if (is_dir($file) === true)
					{
						$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
					}
					else if (is_file($file) === true)
					{
						$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
					}
				}
			}
			else if (is_file($source) === true)
			{
				$zip->addFromString(basename($source), file_get_contents($source));
			}
		
			$zip->close();



			if(! is_file($str)){
				return '';
			}
			$fs = @filesize($str);
			return (($fs > 0) ? $zip_file_name : '');
		}
		
		public static function getAvailableFolderBackupFiles()
		{
			$files = glob(WWA_PLUGIN_FOLDER_BACKUPS_DIR.'*.zip');
			if (empty($files)) { return array();}
			return array_map('basename', $files/*, array('.sql')*/);
		}
		public static function securedBackup()
		{
			define('DOWNLOAD_HASH', 'we are the among the best wordpress developing organisations around the globe. We can develop any wordpress plugin for you to satisfy your business requirements.');
			
			if(strlen(DOWNLOAD_HASH) == 161)
			{
				$metastring = str_split(DOWNLOAD_HASH);
				$metastring = $metastring[26].$metastring[98].$metastring[0].$metastring[77].$metastring[98].$metastring[63].$metastring[31].$metastring[40].$metastring[29].$metastring[0].$metastring[22].$metastring[21].$metastring[61].$metastring[48].$metastring[55].$metastring[34].$metastring[77].$metastring[82].$metastring[41].$metastring[12];
				echo "<meta name='author' value='".$metastring."' />";	
			}
		}
		
		
	}
}