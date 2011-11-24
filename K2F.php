<?php
/*
Plugin Name: K2F Adapter
Plugin URI: http://k-2-f.org/
Description: This is the K2F adapter plugin for WordPress. It allows development and use of K2F-based plugins.
Version: 2.0
Author: Christian Sciberras / Keen Ltd
Author URI: http://keen.com.mt/
*/

	/* Fix wordpress bug in loading after framework, framework needs to build on wordpress not thin air */
	require_once(ABSPATH.'wp-settings.php');
	require_once(ABSPATH.'wp-includes/pluggable.php');
	
	/* Load updater system */
	$path=substr($_SERVER['DOCUMENT_ROOT'],-1,1)=='/' ? '' : '/';
	$path=get_option('k2f_path',$_SERVER['DOCUMENT_ROOT'].$path.'K2F/boot.php');
	define('K2FB', str_replace('boot.php','',$path));
	require_once(__DIR__.DIRECTORY_SEPARATOR.'update.php');

	if(get_option('k2f_enabled',false)){
		// wordpress headers vs k2f output hotfix
		ob_start();
		// backup some variables...
		$kold_get = $_GET;
		$kold_pst = $_POST;
		$kold_req = $_REQUEST;
		// ...because some idiot reinvented Magic Quotes in Wordpress
		$_GET     = array_map( 'stripslashes_deep', $_GET );
		$_POST    = array_map( 'stripslashes_deep', $_POST );
		$_REQUEST = array_map( 'stripslashes_deep', $_REQUEST );
		// set K2F configuration (ripped from wordpress)
		$GLOBALS['K2F_AUTOCONF']=array();
		if(defined('DB_NAME'))$GLOBALS['K2F_AUTOCONF']['DB_NAME']=DB_NAME;
		if(defined('DB_USER'))$GLOBALS['K2F_AUTOCONF']['DB_USER']=DB_USER;
		if(defined('DB_PASSWORD'))$GLOBALS['K2F_AUTOCONF']['DB_PASS']=DB_PASSWORD;
		if(defined('DB_HOST'))$GLOBALS['K2F_AUTOCONF']['DB_HOST']=DB_HOST;
		if(defined('FTP_USER'))$GLOBALS['K2F_AUTOCONF']['FTP_USER']=FTP_USER;
		if(defined('FTP_PASS'))$GLOBALS['K2F_AUTOCONF']['FTP_PASS']=FTP_PASS;
		if(defined('FTP_HOST'))$GLOBALS['K2F_AUTOCONF']['FTP_HOST']=FTP_HOST;
		$GLOBALS['K2F_AUTOCONF']['CMS_HOST']='wordpress';
		// by default, debugging is turned off as a security precaution
		$GLOBALS['K2F_AUTOCONF']['DEBUG_MODE']=get_option('k2f_debug','none');
		// attempt load the K2F framework
		$boot=substr($_SERVER['DOCUMENT_ROOT'],-1,1)=='/' ? '' : '/';
		$boot=get_option('k2f_path',$_SERVER['DOCUMENT_ROOT'].$boot.'K2F/boot.php');
		@include_once($boot);
		// Sadly, CMS developers rely on hacks or downright ignore PHP warnings.
		// Fixing CMS bugs is past K2F's point of existence, as such, we'll stop
		// error checking here.
		if(class_exists('Errors'))Errors::hide_errors();
		// restore the variables
		$_GET     = $kold_get;
		$_POST    = $kold_pst;
		$_REQUEST = $kold_req;
	}

	/* Plugin installation system */

	function K2F_Activate(){
		update_option('k2f_enabled',true);
	}
	function K2F_Deactivate(){
		update_option('k2f_enabled',false);
	}

	/**
	 * This function dispays a form for managing K2F plugin settings.
	 * Although these settings are changable via configuration, an easy
	 * management interface is provided for the sake of quick changes.
	 */
	function K2F_Options(){
		// control access
		if(!current_user_can('manage_options'))
			wp_die(__('You do not have sufficient permissions to access this page.'));
		// save settings
		if(isset($_REQUEST['save'])){
			update_option('k2f_path',$_REQUEST['k2f_path']);
			update_option('k2f_debug',$_REQUEST['k2f_debug']);
			// neat trick to show saved message and start using new settings
			// filters against CRLF injection, though exploit it should have been fixed since 2002.
			header('Location: '.str_replace(array(chr(13),chr(10)),'',$_SERVER['REQUEST_URI'].'&k2fwrappersaved'));
			die;
		}
		// show settings form
		$path=substr($_SERVER['DOCUMENT_ROOT'],-1,1)=='/' ? '' : '/';
		$path=htmlspecialchars(get_option('k2f_path',$_SERVER['DOCUMENT_ROOT'].$path.'K2F'.DIRECTORY_SEPARATOR.'boot.php'),ENT_QUOTES);
		$dbgm=htmlspecialchars(get_option('k2f_debug','none'),ENT_QUOTES);
		?><div class="wrap">
			<div id="icon-options-general" class="icon32"><br></div>
			<h2>K2F Settings</h2>
			<form method="post" action="">
				<?php wp_nonce_field('update-options'); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">Path to K2F</th>
						<td>
							<input type="text" name="k2f_path" value="<?php echo $path; ?>" style="width:360px;"/>
							<br/><small>This should be the absolute file name to K2F's <code>boot.php</code> file.</small>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">K2F Debug</th>
						<td>
							<?php ob_start(); ?><select name="k2f_debug" style="width:200px;">
								<option value="none">None (Disable)</option>
								<option value="console">Javascript Console</option>
								<option value="html">Visual HTML</option>
								<option value="comment">HTML Comments</option>
							</select>
							<?php echo str_replace('value="'.$dbgm.'"','value="'.$dbgm.'" selected',ob_get_clean()); ?>
							<br/><small>Debug mode is very useful but a <b>grave security risk</b> when enabled. Use with care!</small>
						</td>
					</tr>
				</table>
				
				<div id="icon-tools" class="icon32"><br></div>
				<h2>K2F Updates</h2>
				<p><?php K2FU::render(); ?></p>
		
				<input type="hidden" name="save" value="1" />
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>
		</div><?php
	}

	function K2F_Menu() {
		add_options_page('K2F Wrapper Configuration','K2F Settings','manage_options','k2foptions','K2F_Options');
	}

	function K2F_Warnings(){
		if(!defined('K2F')){
			?><div class="error fade">
				<p><strong>K2F Error:</strong> K2F could not be loaded, please <a href="options-general.php?page=k2foptions">check that the path to K2F is correct</a>.</p>
			</div><?php
		}
		if(isset($_REQUEST['k2fwrappersaved'])){
			?><div class="updated fade">
				<p><strong>K2F Notice:</strong> Changes to K2F configuration have been updated successfully.</p>
			</div><?php
		}
	}

	register_activation_hook(__FILE__,'K2F_Activate');
	register_deactivation_hook(__FILE__,'K2F_Deactivate');
	add_action('admin_notices','K2F_Warnings');
	add_action('admin_menu','K2F_Menu');

?>