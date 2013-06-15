<script type="text/javascript">
$(function(){
	$('#webserver_name_box').change(function(){
		var serverId=$(this).val();
		var htaccessChecked=$('#htaccess_state_box').attr('checked');
		if(serverId!='apache' && htaccessChecked=='checked'){
			$('#htaccess_state_box').attr('checked', false);
		}
	});
});
function ResponseCallback(data){
	alert(data.msg);
	if(data.status==true) {
		location.href='<?php echo $this->createUrl('file/index'); ?>';
	}
}
</script>
<?php echo Script::ajaxFormSubmit('#create_webapp_form', null, 'ResponseCallback', 'json');?>
<!-- create webapp -->
<?php if($webappStatus == false) { ?>
<div class="alert">对不起，您还没有创建所要开发的项目目录，请点击“创建WebApp目录”按钮创建项目目录。</div>
<?php } else { ?>
<fieldset>
<legend>创建WebApp目录:</legend>
<form action="<?php echo $this->getActionUrl('ajaxwebapp'); ?>" method="post" id="create_webapp_form">
<label>Server Software:</label>
&nbsp;&nbsp;<select id="webserver_name_box" class="text" style="width:auto;" name="webserver_name"><option <?php if ($isApache == true) {echo 'selected="selected"';}?>value="apache">Apache</option><option value="other">Other</option></select>
<?php if($isApache == true) {?>&nbsp;&nbsp;<input id="htaccess_state_box" type="checkbox" name="htaccess_state"> .htaccess文件（仅限apache）<?php } ?>&nbsp;&nbsp;<input id="ext_state_box" type="checkbox" name="ext_state">扩展目录&nbsp;&nbsp;<input id="module_state_box" type="checkbox" name="module_state">模块目录&nbsp;&nbsp;<input id="lang_state_box" type="checkbox" name="lang_state">多语言&nbsp;&nbsp;&nbsp;&nbsp;<input id="create_app_dir_button" type="submit" style="width:110px; height:24px; text-align:center; border:1px solid #333; cursor:pointer" value="创建WebApp目录" name="create_app_dir_button"/>
</fieldset>
<?php } ?>
</form>
<!-- /create webapp -->