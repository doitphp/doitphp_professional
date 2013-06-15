<table border="0" cellspacing="0" cellpadding="0" style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">
<caption>Server :</caption>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150" align="left">Server Time :</td>
    <td align="left"><?php echo date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']); ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150" align="left">Server Domain : </td>
    <td align="left"><?php echo $_SERVER['SERVER_NAME']; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150" align="left">Server IP :</td>
    <td align="left"><?php echo $_SERVER['SERVER_ADDR']; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150" align="left">Server OS : </td>
    <td align="left"><?php echo Client::getOs(); ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150" align="left">Server OS Charset : </td>
    <td align="left"><?php echo $_SERVER['HTTP_ACCEPT_LANGUAGE']; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150" align="left">Server Software : </td>
    <td align="left"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150" align="left">Server Web Port : </td>
    <td align="left"><?php echo $_SERVER['SERVER_PORT']; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150" align="left">PHP run mode : </td>
    <td align="left"><?php echo strtoupper(php_sapi_name()); ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td align="left"><span class="blue"><b>WebApp Path</b></span> : </td>
    <td align="left"><?php
	if (is_dir($webappPath)) {
		echo is_writable($webappPath) ? Html::image($this->getAssetUrl('images') . 'check_right.gif') . ' <span class="green">' . $webappPath . '</span> (支持文件写入操作)' :  Html::image($this->getAssetUrl('images') . 'check_error.gif') . ' <span class="red"><b>'. $webappPath . '</b></span>  (注:<span class="red">当前目录没有文件写入权限!</span>)';
	} else {
		echo '<span class="red">对不起,WebApp目录不存在!</span>';
	}
	?>
	</td>
  </tr>
</table>
<table border="0" cellspacing="0" cellpadding="0" style="font-family:Arial, Helvetica, sans-serif; font-size:14px;">
<caption>PHP :</caption>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">PHP Version : </td>
    <td><?php echo PHP_VERSION; if (version_compare(PHP_VERSION,"5.1.0","<")) { echo ' (<span class="red">对不起,当前PHP环境无法满足DoitPHP的运行要求: PHP 5.1.0或更高版本,必须的!</span>)'; } ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">PHPINFO : </td>
    <td><?php echo (stripos('phpinfo', get_cfg_var('disable_functions')) === false) ? '<a href="' . $this->getActionUrl('phpinfo') . '" target="_blank">支持</a>' : '不支持'; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">Safe Mode : </td>
    <td><?php echo get_cfg_var('safe_mode') ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">display_errors : </td>
    <td><?php echo get_cfg_var('display_errors') ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">register_globals : </td>
    <td><?php echo get_cfg_var('register_globals') ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">magic_quotes_gpc : </td>
    <td><?php echo get_cfg_var('magic_quotes_gpc') ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">memory_limit : </td>
    <td><?php echo get_cfg_var('memory_limit'); ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">post_max_size : </td>
    <td><?php echo get_cfg_var('post_max_size'); ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">upload_max_filesize : </td>
    <td><?php if (get_cfg_var('file_uploads')) {echo get_cfg_var('upload_max_filesize'); } else { echo '不允许上传'; } ?></td>
  </tr>
  <tr>
    <td width="10" height="30">&nbsp;</td>
    <td width="150">max_execution_time : </td>
    <td><?php echo get_cfg_var('max_execution_time'), ' (秒)'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>disable_functions : </td>
    <td><?php $disable_functions = get_cfg_var('disable_functions'); if(!$disable_functions) { echo 'No'; } else { echo $disable_functions; }?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>$_SERVER vars:</td>
    <td><?php echo $serverResult; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>Reflection extension : </td>
    <td><?php echo class_exists('Reflection', false) ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>PCRE extension : </td>
    <td><?php echo extension_loaded("pcre") ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>SPL extension : </td>
    <td><?php echo extension_loaded("SPL") ? 'Yes' : '<span class="red">No</span>'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>DOM extension : </td>
    <td><?php echo class_exists("DOMDocument",false) ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>GD extension : </td>
    <td><?php echo $gdResult; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>Memcache extension : </td>
    <td><?php echo (extension_loaded("memcache") || extension_loaded("memcached")) ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>Mcrypt extension : </td>
    <td><?php echo extension_loaded("mcrypt") ? 'Yes' : '<span class="red">No</span>'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>SOAP extension : </td>
    <td><?php echo extension_loaded("soap") ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>Ctype extension : </td>
    <td><?php echo extension_loaded("ctype") ? 'Yes' : '<span class="red">No</span>'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>APC extension : </td>
    <td><?php echo extension_loaded("apc") ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>Xcache extension : </td>
    <td><?php echo extension_loaded("xcache") ? 'Yes' : 'No'; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>Supported databases : </td>
    <td><?php echo $databaseInfo; ?></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;<a href="<?php echo $this->getActionUrl('phpinfo'); ?>" target="_blank">More &gt;&gt;</a></td>
  </tr>
</table>

