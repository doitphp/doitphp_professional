<script type="text/javascript" src="<?php echo $assetUrl; ?>jquery/jquery.cookies.min.js?version=2.2.0"></script>
<script type="text/javascript">
$(function(){
	$('#ext_name_box').cookieBind();
	$('#note_description_box').cookieBind();
	$('#note_author_box').cookieBind();
	$('#note_copyright_box').cookieBind();
	$('#note_license_box').cookieBind();
	$('#note_link_box').cookieBind();
});
function ajaxFormRequest(){
	var extName=$('#ext_name_box').val();
	if(extName==''){
		alert('对不起，扩展模块名不能为空！');
		$('#ext_name_box').css('border-color', '#C00').focus();
		return false;
	}
}
function ajaxFormResponse(data){
	alert(data.msg);	
	if(data.status==true){
		$.cookies.del('ext_name_box');
		$.cookies.del('note_description_box');
		$('#create_ext_form_box').resetForm();
		location.reload();		
	}
}
</script>
<?php echo Script::ajaxFormSubmit('#create_ext_form_box', 'ajaxFormRequest', 'ajaxFormResponse', 'json'); ?>
<fieldset>
<legend>创建扩展模块：</legend>
<?php $this->widget('ReturnDir'); ?>
<form action="<?php echo $this->getActionUrl('ajaxcreateext'); ?>" method="post" name="create_ext_form_box" id="create_ext_form_box">
<table border="0" cellspacing="0" cellpadding="0" style="margin-top:10px;">
<caption>基本信息</caption>
  <tr>
    <td width="120" align="center"><label>扩展模块名:</label></td>
    <td align="left">
	<input type="text" class="text" name="ext_name_box" id="ext_name_box" style="width:180px;"/>
	</td>
  </tr>
</table>
<?php $this->widget('ExtNote'); ?>
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="40" align="center"><input type="submit" style="width:140px; height:24px; text-align:center; border:1px solid #333; cursor:pointer" value="创建扩展模块" name="create_ext_submit_button"/><input type="hidden" name="path_box" value="<?php echo $path; ?>" /></td>
  </tr>
</table>
</form>
</fieldset>