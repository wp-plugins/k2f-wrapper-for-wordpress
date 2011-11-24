<?php

	class K2FU {
		/**
		 * @return boolean True if K2F is currently installed, false otherwise.
		 */
		public static function is_installed(){
			return file_exists(K2FB.'boot.php');
		}
		/**
		 * @return boolean True if the currently installed version is outdated.
		 */
		public static function has_updates(){
			$old = preg_replace('/[^0-9\.]/', '', self::get_this_version());
			$new = preg_replace('/[^0-9\.]/', '', self::get_next_version());
			return version_compare($old, $new)<0;
		}
		/**
		 * @return string The currently installed version.
		 */
		public static function get_this_version(){
			if(($data=file_get_contents(K2FB.'boot.php'))!='')
				if(preg_match("/(define\\(\\'K2F\\',\\')([\\w\\.]+)(\\'\\);)/", $data, $matches))
					return trim($matches[2],' \'"');
			return '';
		}
		/**
		 * @return string The version string of the current up-to-date K2F.
		 */
		public static function get_next_version(){
			static $version = null;
			if(!$version)
				$version = file_get_contents('http://update.k-2-f.org/version.php');
			return $version;
		}
		/**
		 * Downloads a K2F zip package and extracts to current directly.
		 * @param string $version The new version to update to.
		 * @param callable $progress Function called for each status update. It takes two arguments;
		 *                           $percent, $message
		 */
		public static function do_update($version, $progress){
			$zip = self::_file_temp('','','.zip');
			self::_file_download(
				//'http://update.k-2-f.org/updt.php?mtd=zipfile&p[]='.urlencode($version).'&p[]=1',
				'http://update.k-2-f.org/data/devel-min.zip', // TODO use the real API here
				$zip,
				function($download_size, $downloaded, $upload_size, $uploaded)use($progress){
					$percent = $downloaded / $download_size * 50;
					$progress($percent, 'Download '.number_format($percent,2).'%');
				}
			);
			self::_file_unzip(
				K2FB,
				$zip,
				function($total, $index, $name)use($progress){
					$percent = $index / $total * 50;
					$progress(50+$percent, 'Extracting '.$name.'...');
				}
			);
		}
		/**
		 * Creates a system-specific temporary file.
		 * @return string File name of the created temporary file.
		 */
		public static function _file_temp($root=null,$prefix='',$suffix=''){
			if(!$root)$root=sys_get_temp_dir();
			while(!($file=@fopen($name=$root.$prefix.round(microtime(true)*10000).'-'.mt_rand().$suffix,'xb')));
			@fclose($file);
			register_shutdown_function(function()use($name){
				@unlink($name);
			});
			return $name;
		}
		/**
		 * Download a file to somewhere locally.
		 * @param string $url Location of remote file.
		 * @param string $tmp Location of local file.
		 * @param callable $progress Progression callback. It takes 4 parameters;
		 *                           $download_size, $downloaded, $upload_size, $uploaded
		 */
		private static function _file_download($url, $tmp, $progress){
			$ch=curl_init();
			curl_setopt($ch,CURLOPT_URL,$url);
			if(substr($url,0,8)=='https://'){
				curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_ANY);
				curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
			}
			curl_setopt($ch,CURLOPT_NOPROGRESS,false);
			curl_setopt($ch,CURLOPT_PROGRESSFUNCTION,$progress);
			curl_setopt($ch,CURLOPT_HEADER,false);
			curl_setopt($ch,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
			curl_setopt($ch,CURLOPT_MAXREDIRS,50);
			$ofp=fopen($tmp, 'wb');
			curl_setopt($ch,CURLOPT_FILE,$ofp);
			curl_exec($ch);
			curl_close($ch);
			fclose($ofp);
			if(is_resource($ofp))fclose($ofp);
		}
		/**
		 * Extract a zipfile's contents.
		 * @param string $base Base path of where extracted files will go to.
		 * @param string $zip Location of zip file.
		 * @param callable $progress Progression callback. It takes 3 parameters;
		 *                           $total, $index, $entry
		 */
		private static function _file_unzip($base, $zip, $progress){
			if(substr($base,-1,1)!=DIRECTORY_SEPARATOR)$base.=DIRECTORY_SEPARATOR;
			// count entries
			$file = zip_open($zip);
			$total = 0;
			while(zip_read($file)!==false)$total++;
			zip_close($file);
			// extract zip
			$file = zip_open($zip);
			$index = 0; $chunkSize = 1048576; // 1kb
			while(($entry=zip_read($file))!=false){
				$index++;
				if(zip_entry_open($file, $entry)){
					$name = str_replace('root/Dropbox/K2F-DEV/', '', zip_entry_name($entry));
					$progress($total, $index, $name);
					// extract item
					$size = zip_entry_filesize($entry);
					mkdir(dirname($base.$name),0775,true);
					$unzipped = fopen($base.$name,'wb');
					while($size > 0){
						$chunk = zip_entry_read($entry, $chunkSize);
						if($chunk!==false){
							$size -= $chunkSize;
							fwrite($unzipped, $chunk);
						}else break;
					}
					fclose($unzipped);
					zip_entry_close($entry);
					usleep(50000);
				}
			}
			zip_close($file);
		}
		public static function update(){
			function K2F_Progress($percent, $message){
				echo '<script type="text/javascript">parent.k2f_status('.(int)$percent.','.json_encode($message).');</script>'.chr(13).chr(10);
			}
			// initialize stuff
			while(ob_get_level())ob_end_clean();
			ob_implicit_flush(true);
			set_time_limit(0);
			// webkit hotfix
			echo '<!DOCTYPE html><html><head><title></title></head><body>';
			// begin update process
			K2F_Progress(0, 'Loading...');
			K2FU::do_update(self::get_next_version(), 'K2F_Progress');
			K2F_Progress(100, 'Finished!');
			// webkit hotfix
			echo '</body></html>';
			die;
		}
		public static function render(){
			if(!K2FU::is_installed()){
				?><input id="k2fu-bt" type="button" onclick="k2f_update()" value="Install"/><?php
			}elseif(K2FU::has_updates()){
				?><input id="k2fu-bt" type="button" onclick="k2f_update()" value="Update"/> (from <?php echo K2FU::get_this_version(); ?> to <?php echo K2FU::get_next_version(); ?>)<?php
			}else{
				?>K2F is already up to date (<?php echo K2FU::get_this_version(); ?>)<?php
			}
			?><iframe id="k2fu-if" src="about:blank" width="1" height="1" frameborder="0" scrolling="no"></iframe>

			<div id="k2fu-pw" style="display:none; margin:5px 2px; vertical-align:top; position:relative; width:160px; overflow:hidden; padding:1px; border:1px solid #BBB; border-radius:4px; background:#FFF;">
				<div id="k2fu-pb" style="width:0%; height:12px; border-radius:2px; background:#5D5;"></div>
				<div id="k2fu-pm" style="position:absolute; left:0; right:0; top:0; bottom:0; text-align:center; color:#555; font-size:10px;"></div>
			</div>
			
			<script type="text/javascript">
				function k2f_update(){
					document.getElementById('k2fu-pw').style.display = 'inline-block';
					document.getElementById('k2fu-if').src = location.origin+location.pathname+
						location.search+(location.search=='' ? '?' : '&')+'k2fdoupdate=1'+location.hash;
					document.getElementById('k2fu-bt').disabled = true;
				}
				function k2f_status(percent, message){
					document.getElementById('k2fu-pb').style.width = percent+'%';
					document.getElementById('k2fu-pm').innerHTML = message;
				}
			</script><?php
		}
	}

	if(isset($_REQUEST['k2fdoupdate']))K2FU::update();
	
?>