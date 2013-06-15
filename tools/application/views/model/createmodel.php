<script type="text/javascript" src="<?php echo $assetUrl; ?>jquery/jquery.cookies.min.js?version=2.2.0"></script>
<script type="text/javascript" src="<?php echo $assetUrl; ?>fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="<?php echo $assetUrl; ?>fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $assetUrl; ?>fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript">
$(function(){
	$(".fancybox").fancybox();
	$('#model_name_box').cookieBind();
	$('#model_tabname_status_box').cookieBind();
	$('#model_table_name_select').cookieBind();
	$('#note_description_box').cookieBind();
	$('#note_author_box').cookieBind();
	$('#note_copyright_box').cookieBind();
	$('#note_license_box').cookieBind();
	$('#note_link_box').cookieBind();
	$('#model_tabname_status_box').click(function(){
		if($(this).attr('checked')=='checked'){
			$('#model_table_name_box').show();
		}else{
			$('#model_table_name_box').hide();
		}
	});
});
function ajaxResponse(data){
	alert(data.msg);
	if(data.status==true){
		location.reload();
	}
}
function addMethodList(){
	var methodName=$('#add_method_name_box').val();
	var methodDesc=$('#add_method_desc_box').val();
	var methodAccess=$('#add_method_access_box').val();
	var methodReturn=$('#add_method_return_box').val();
	if(methodName==''){
		alert('对不起，Method名称不能为空！');
		$('#add_method_name_box').css('border-color', '#C00').focus();
		return false;
	}
	$.post('<?php echo $this->getActionUrl('ajaxaddmethod'); ?>', {name:methodName, desc:methodDesc, access:methodAccess, returnType:methodReturn},ajaxResponse, 'json');
}
function editMethodList(){
	var methodId=$('#edit_method_id_box').val();
	var methodName=$('#edit_method_name_box').val();
	var methodDesc=$('#edit_method_desc_box').val();
	var methodAccess=$('#edit_method_access_box').val();
	var methodReturn=$('#edit_method_return_box').val();
	if(methodId==''){
		return false;
	}
	if(methodName==''){
		alert('对不起，Method名称不能为空！');
		$('#edit_method_name_box').css('border-color', '#C00').focus();
		return false;
	}
	$.post('<?php echo $this->getActionUrl('ajaxeditmethod'); ?>', {id:methodId, name:methodName, desc:methodDesc, access:methodAccess, returnType:methodReturn}, ajaxResponse, 'json');
}
function removeMethodList(id){
	if(!confirm('你确认要删除该Method数据吗？')){
		return false;
	}
	$.post('<?php echo $this->getActionUrl('ajaxdeletemethod'); ?>', {id:id}, ajaxResponse, 'json');
}
function addMethodParams(id) {
	var name=$('#add_method_params_name_box').val();
	var desc=$('#add_method_params_desc_box').val();
	var type=$('#add_method_params_type_box').val();
	var defaultVal=$('#add_method_params_default_box').val();
	if(name==''){
		alert('对不起，参数名称不能为空！');
		$('#add_method_params_name_box').css('border-color', '#C00').focus();
		return false;
	}
	$.post('<?php echo $this->getActionUrl('ajaxaddmethodparams'); ?>', {id:id, name:name, desc:desc, type:type, defaultVal:defaultVal}, ajaxResponse, 'json');
}
function editMethodParams(mid, key){
	var name=$('#edit_method_params_name_box').val();
	var desc=$('#edit_method_params_desc_box').val();
	var type=$('#edit_method_params_type_box').val();
	var defaultVal=$('#edit_method_params_default_box').val();
	if(name==''){
		alert('对不起，参数名称不能为空！');
		$('#edit_method_params_name_box').css('border-color', '#C00').focus();
		return false;
	}
	$.post('<?php echo $this->getActionUrl('ajaxeditmethodparams'); ?>', {id:mid, key:key, name:name, desc:desc, type:type, defaultVal:defaultVal}, ajaxResponse, 'json');
}
function deleteMethodParams(mid, key){
	if(!confirm('你确认要删除该Params数据吗？')){
		return false;
	}
	$.post('<?php echo $this->getActionUrl('ajaxdeletemethodparams'); ?>', {id:mid, key:key}, ajaxResponse, 'json');
}
function ajaxFormRequest(){
	var modelName=$('#model_name_box').val();
	if(modelName==''){
		alert('对不起，Model Name不能为空！');		
		$('#model_name_box').css('border-color', '#C00').focus();
		return false;
	}
}
function ajaxFormResponse(data){
	alert(data.msg);
	if(data.status==true){
		$.cookies.del('model_name_box');
		$.cookies.del('model_tabname_status_box');
		$.cookies.del('model_table_name_box');
		$.cookies.del('note_description_box');
		$('#create_model_form_box').resetForm();
		location.reload();
	}
}
</script>
<?php echo Script::ajaxFormSubmit('#create_model_form_box', 'ajaxFormRequest', 'ajaxFormResponse', 'json'); ?>
<fieldset>
<legend>创建Model文件：</legend>
<?php $this->widget('ReturnDir'); ?>
<form action="<?php echo $this->getActionUrl('ajaxcreatemodel'); ?>" method="post" name="create_model_form_box" id="create_model_form_box">
<table border="0" cellspacing="0" cellpadding="0" style="margin-top:10px;">
<caption>基本信息</caption>
  <tr>
    <td width="300" align="left"><label>Model Name:</label>&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="text" class="text" name="model_name_box" id="model_name_box" style="width:180px;"/>
	</td>
	<td width="170" align="center"><input name="model_tabname_status_box" type="checkbox" id="model_tabname_status_box" <?php if($bindTableStatus==true){echo 'checked="checked"';} ?>>
	绑定数据表( 仅限MYSQL )</td>
	<td align="left"><span id="model_table_name_box" <?php if($bindTableStatus==false){ echo 'class="hide"'; } ?>><label>Table Name:</label>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $tableNameHtml; ?></span></td>
  </tr>
</table>
<!-- method -->
<table border="0" cellspacing="0" cellpadding="0">
<caption>Method</caption>
  <tr>
    <td align="right"><a href="<?php echo $this->getActionUrl('addmethod'); ?>/time/<?php echo $timeNow; ?>" class="fancybox">添加Method</a></td>
  </tr>
  <tr>
    <td align="left" valign="top">
<?php if($methodList) { $methodId = 1; foreach ($methodList as $methodKey=>$lines) { ?>
<table border="0" cellpadding="0" cellspacing="1" bgcolor="#CCCCCC">
  <tr>
    <td width="20" align="center" bgcolor="#FFFFFF"><strong><?php echo $methodId; ?></strong>.</td>
    <td width="100" align="center" bgcolor="#FFFFFF">MethodName：</td>
    <td width="180" align="center" bgcolor="#FFFFFF"><span class="blue"><?php echo $lines['name']; ?></span></td>
    <td width="100" align="center" bgcolor="#FFFFFF">描述：</td>
    <td colspan="2" align="center" bgcolor="#FFFFFF"><?php echo $lines['desc']; ?></td>
  </tr>
  <tr>
    <td colspan="2" align="center" bgcolor="#FFFFFF">访问权限：</td>
    <td width="180" align="center" bgcolor="#FFFFFF"><?php echo $lines['access']; ?></td>
    <td width="100" align="center" bgcolor="#FFFFFF">返回数据类型：</td>
    <td width="90" align="center" bgcolor="#FFFFFF"><?php echo $lines['return']; ?></td>
    <td align="center" bgcolor="#FFFFFF"><a href="<?php echo $this->getActionUrl('addmethodparams'); ?>/id/<?php echo $methodKey; ?>/time/<?php echo $timeNow; ?>" class="fancybox">添加参数</a> &nbsp; <a href="<?php echo $this->getActionUrl('editmethod'); ?>/id/<?php echo $methodKey; ?>/time/<?php echo $timeNow; ?>" class="fancybox">编辑Method</a> &nbsp; <a href="javascript:void(0);" onclick="removeMethodList(<?php echo $methodKey; ?>);">删除Method</a></td>
  </tr>
  <?php if(isset($lines['params']) && $lines['params']) { ?>
  <tr>
    <td colspan="6" align="left" valign="top" bgcolor="#FFFFFF">
<!--method list-->
	 <table border="0" cellpadding="0" cellspacing="1" bgcolor="#FFFFFF">
		  <tr>
			<td width="150" align="center" bgcolor="#F5F5F5">参数名</td>
			<td width="120" align="center" bgcolor="#F5F5F5">数据类型</td>
			<td align="center" bgcolor="#F5F5F5">描述</td>
			<td width="100" align="center" bgcolor="#F5F5F5">默认值</td>
			<td width="100" align="center" bgcolor="#F5F5F5">操作</td>
		  </tr>
		  <?php foreach($lines['params'] as $key=>$value) {?>
		  <tr>
			<td align="center"><?php echo $value['name']; ?></td>
			<td align="center"><?php echo $value['type']; ?></td>
			<td align="center"><?php echo $value['desc']; ?></td>
			<td align="center"><?php echo $value['default']; ?></td>
			<td align="center"><a href="<?php echo $this->getActionUrl('editmethodparams'); ?>/id/<?php echo $methodKey; ?>/key/<?php echo $key; ?>/time/<?php echo $timeNow; ?>" class="fancybox">编辑</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="deleteMethodParams(<?php echo $methodKey; ?>, <?php echo $key; ?>);">删除</a></td>
		  </tr>
		  <?php } ?>
	 </table>
<!--/method list-->	</td>
  </tr>
  <?php } ?>
</table>
<?php $methodId ++; } } ?>
	</td>
  </tr>
</table>
<!-- /method -->
<?php $this->widget('ExtNote'); ?>
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="40" align="center"><input type="submit" style="width:130px; height:24px; text-align:center; border:1px solid #333; cursor:pointer" value="创建Model文件" name="create_model_submit_button"/><input type="hidden" name="path_box" value="<?php echo $path; ?>" /></td>
  </tr>
</table>
</form>
</fieldset>