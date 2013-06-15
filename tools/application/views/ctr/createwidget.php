<script type="text/javascript" src="<?php echo $assetUrl; ?>jquery/jquery.cookies.min.js?version=2.2.0"></script>
<script type="text/javascript">
$(function(){
	$('#widget_name_box').cookieBind();
	$('#widget_view_file_box').cookieBind();
	$("input[name='widget_view_file_ext']").cookieBind();
	$('#note_description_box').cookieBind();
	$('#note_author_box').cookieBind();
	$('#note_copyright_box').cookieBind();
	$('#note_license_box').cookieBind();
	$('#note_link_box').cookieBind();
});
function ajaxFormRequest(){
	var widgetName=$('#widget_name_box').val();
	if(widgetName==''){
		alert('对不起，Widget Name不能为空！');
		$('#widget_name_box').css('border-color', '#C00').focus();
		return false;
	}
}
function ajaxFormResponse(data){
	alert(data.msg);
	if(data.status==true){
		$.cookies.del('widget_name_box');
		$.cookies.del('note_description_box');
		$('#create_widget_form_box').resetForm();
		location.reload();
	}
}
</script>
<?php echo Script::ajaxFormSubmit('#create_widget_form_box', 'ajaxFormRequest', 'ajaxFormResponse', 'json'); ?>
<fieldset>
<legend>创建Widget文件：</legend>
<?php $this->widget('ReturnDir'); ?>
<form action="<?php echo $this->getActionUrl('ajaxcreatewidget'); ?>" method="post" name="create_widget_form_box" id="create_widget_form_box">
<table border="0" cellspacing="0" cellpadding="0" style="margin-top:10px;">
<caption>基本信息</caption>
  <tr>
    <td width="120" align="center"><label>Widget Name:</label></td>
    <td align="left">
	<input type="text" class="text" name="widget_name_box" id="widget_name_box" style="width:180px;"/>&nbsp;&nbsp;&nbsp;&nbsp;<input name="widget_view_file_state" type="checkbox" id="widget_view_file_box" <?php if($viewFileStatus==true) {echo 'checked="checked"'; } ?>/>视图文件 &nbsp;( 视图文件格式: <input name="widget_view_file_ext" type="radio" value="php" <?php if($viewFileExt != 'html') {echo 'checked="checked"';} ?>/> PHP <input type="radio" name="widget_view_file_ext" value="html" <?php if($viewFileExt == 'html') {echo 'checked="checked"';} ?>/>HTML )
	</td>
  </tr>
</table>
<?php $this->widget('ExtNote'); ?>
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="40" align="center"><input type="submit" style="width:140px; height:24px; text-align:center; border:1px solid #333; cursor:pointer" value="创建Widget文件" name="create_widget_submit_button"/><input type="hidden" name="path_box" value="<?php echo $path; ?>" /></td>
  </tr>
</table>
</form>
</fieldset>