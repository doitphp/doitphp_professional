<script type="text/javascript">
function showUpload(){
	$('#upload_form_box').show(200);
}
function removeUpload(){
	$('#upload_form_box').hide(200);
}
function uploadRequest(){
	var file_box = $('#upload_files_box').val();
	if(file_box==''){
		alert('上传文件不能为空');
		$('#upload_files_box').focus();
		return false;
	}
}
function uploadResponse(data){
	alert(data.msg);
	if(data.status==true){
		$('#upload_file_form_box').resetForm();
		location.reload();
	}
}
function deleteFile(file, isdir){
	if(!confirm('你确认要进行删除操作!')){
		return false;
	}
	$.post('<?php echo $this->getActionUrl('ajaxdeletefile'); ?>', {dir_name:'<?php echo $path; ?>', file_name:file}, function(data){
		alert(data.msg);
		if(data.status==true) {
			location.reload();
		}
	}, 'json');
}
</script>
<?php echo Script::ajaxFormSubmit('#upload_file_form_box', 'uploadRequest', 'uploadResponse', 'json'); ?>
<!-- webapp note-->
<div style="margin-left:20px; height:30px;"><span class="blue"><b>WebApp Path</b></span>:&nbsp;&nbsp;<?php if(is_dir($webAppPath)) {echo '<span class="green">' . $webAppPath . '</span>'; echo is_writable($webAppPath) ? ' ( Writable )' : ' ( <span class="red">unwriteable</span> )';} else { echo '<span class="red"><b>' . $webAppPath . '</b></span> 注：<span class="red">当前目录不存在!</span>'; } ?> </div>
<!-- /webapp note-->

<!-- file list -->
<fieldset>
<legend>文件列表:</legend>
<table border="0" cellspacing="0" cellpadding="0">
<caption>
<?php if($dir == true) {?>
<img src="<?php echo $this->getAssetUrl('images'); ?>file_topdir.gif"  />
<a href="<?php echo $returnUrl; ?>">返回上级目录</a>&nbsp;&nbsp;&nbsp;&nbsp;
<?php } ?>
<img src="<?php echo $this->getAssetUrl('images'); ?>tree_folderopen.gif"  /> 当前目录: <?php echo $path; ?>
</caption>
  
<tr>
    <td height="50" colspan="5" align="left">&nbsp;&nbsp;[<a href="<?php echo $this->getSelfUrl(); ?>" target="_self">根目录</a>]
	<!-- file upload-->
<?php if($isSystem == false) { ?>
	&nbsp;[<a href="javascript:void(0);" onclick="showUpload();">上传文件</a>]	
	<!-- file upload-->
	<!--controller manage-->
	<?php if($isController == true) { ?>
	&nbsp;[<a href="<?php echo $this->createUrl('ctr/createcontroller'); ?>/?path=<?php echo $dir; ?>">创建Controller文件</a>]
	<?php } ?>
	<!--/controller manage-->
	<!-- widget manage-->
	<?php if($isWidget == true) { ?>
	&nbsp;[<a href="<?php echo $this->createUrl('ctr/createwidget'); ?>/?path=<?php echo $dir; ?>">创建Widget文件</a>]
	<?php } ?>
	<!-- /widget manage-->
	<!--model manage-->
	<?php if($isModel == true) { ?>
	&nbsp;[<a href="<?php echo $this->createUrl('model/createmodel'); ?>/?path=<?php echo $dir; ?>">创建Model文件</a>]
	<?php } ?>
	<!--/model manage-->
	<!--module manage-->
	<?php if($isModule == true) { ?>
	&nbsp;[<a href="<?php echo $this->createUrl('module/createmodule'); ?>/?path=<?php echo $dir; ?>">创建Module目录</a>]
	<?php } ?>
	<!--/module manage-->
	<!--library manage-->
	<?php if($isLibrary == true) { ?>
	&nbsp;[<a href="<?php echo $this->createUrl('lib/createclass'); ?>/?path=<?php echo $dir; ?>">创建Class文件</a>]
	<?php } ?>
	<!--/library manage-->
	<!-- extension manage -->
	<?php if($isExtensionDir == true) { ?>
	&nbsp;[<a href="<?php echo $this->createUrl('ctr/createext'); ?>/?path=<?php echo $dir; ?>">创建扩展模块</a>]
	<?php } ?>
	<!--/extension manage -->
<?php } ?>
	</td>
  </tr>
  <tr id="upload_form_box" class="even" style="display:none;">
  <td height="40" colspan="5" align="left"><form action="<?php echo $this->getActionUrl('ajaxfileupload'); ?>" method="post" enctype="multipart/form-data" name="upload_file_form_box" id="upload_file_form_box">文件上传:&nbsp;&nbsp;
    <input type="hidden" name="uploadDirName" id="upload_dir" value="<?php echo $path; ?>" /><input type="file" name="upload_file" id="upload_files_box"/>&nbsp;<input type="submit" value="上传" name="upload_button"/>&nbsp;&nbsp;<a href="javascript:void(0);" onclick="removeUpload();">取消上传</a></form></td>
  </tr>
  <tr>
    <th width="320" align="center">文件名称</th>
    <th width="120" align="center">大小</th>
    <th width="150" align="center">修改时间</th>
    <th width="60" align="center">权限</th>
    <th align="center">操作</th>
  </tr>
 <?php if($fileList == true) {
 foreach($fileList as $key=>$lines) {
 if($lines['isdir'] == 1) {
 ?>
  <tr <?php echo ($key%2 ==1) ? 'class="even"' : ''; ?>>
    <td width="320" align="left"><img src="<?php echo $this->getAssetUrl('images'); ?>tree_folder.gif"  /> <a href="<?php echo $this->getSelfUrl(); ?>/?path=<?php echo $dir, '/', $lines['name']; ?>" target="_self"><?php echo $lines['name']; ?></a></td>
    <td width="120" align="center">&nbsp;</td>
    <td width="150" align="center"><?php echo $lines['time']; ?></td>
    <td width="60" align="center"><?php echo $lines['mod']; ?></td>
    <td align="center">&nbsp;</td>
  </tr>
  <?php } else {?>
  <tr <?php echo ($key%2 ==1) ? 'class="even"' : ''; ?>>
    <td width="320" align="left"><?php if($lines['ico'] == true) { ?><img src="<?php echo $this->getAssetUrl('images'), $lines['ico']; ?>"  /> <?php } echo $lines['name']; ?></td>
    <td width="120" align="center"><?php echo $lines['size']; ?></td>
    <td width="150" align="center"><?php echo $lines['time']; ?></td>
    <td width="60" align="center"><?php echo $lines['mod']; ?></td>
    <td align="center">	<?php if($isSystem == false) { ?><a href="javascript:void(0);" onclick="deleteFile('<?php echo $lines['name']; ?>', 0);">删除</a><?php } ?></td>
  </tr>
<?php }}} else { ?>
<tr>
    <td colspan="5" align="center" style="font-size:14px;">亲, 暂时没有找到所要显示的文件哦!</td>
  </tr>
<?php } ?>
</table>
</fieldset>
<!-- /file list -->