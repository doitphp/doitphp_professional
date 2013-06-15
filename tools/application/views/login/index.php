<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="none" />
<title>DoitPHP Tools Login</title>
<style type="text/css"> 
<!--
body{margin-left:0;font-family:"Arial Black", Gadget, sans-serif;font-size:14px;line-height:24px;color:#000;margin-top:0;text-align:center;border-style:none;padding:0;}
.total{height:302px;width:402px;text-align:left;border:1px solid #6EBB23;background-color:##FFF;margin:120px auto 0;padding:30px;}
.total .login_box{height:300px;width:400px;margin:0;padding:0;}
.ip{height:24px;width:170px;font-size:14px;line-height:24px;border:1px solid #559ED5;background-color:#FFF;}
-->
</style>
<?php echo Script::add('jquery'); ?>
<?php echo Script::add('form'); ?>
<script type="text/javascript">
function showRequest(){
	var user = $('#user_name').val();
	var pw = $('#user_password').val();
	var code = $('#vd_code').val();	
	if(user==''){
		$('#user_name').css('border', '1px solid #D54E21');
		alert('请填写管理员用户名!');
		$('#user_name').focus();
		return false;
	}
	if(pw==''){
		$('#user_password').css('border', '1px solid #D54E21');
		alert('密码不能为空!');
		$('#user_password').focus();
		return false;
	}
	if(code == ''){
		$('#vd_code').css('border', '1px solid #D54E21');
		alert('验证码不能为空!');
		$('#vd_code').focus();
		return false;
	}
	return true;
}
function showResponse(data){
	if(data.status==true){
		//当有信息提示时
		if(data.msg!='') {
			alert(data.msg);
		}
		//当有网址跳转或页面刷新时
		if(data.data.nexturl!=''){
			if(data.data.nexturl=='refresh'){
				location.reload();
			} else {
				location.href=data.data.nexturl;
			}
		}
	} else {
		if(data.msg!='') {
			alert(data.msg);
		}
	}

	return true;	
}
function refresh_vdcode(){
	$('#vd_image').attr('src','<?php echo $this->getActionUrl('vdcode'); ?>/?time='+Math.round(Math.random()*10));
}
$(document).ready(function(){
	$('#login_box').ajaxForm({
		beforeSubmit:showRequest,
		success:showResponse,
		dataType:'json'
	});
	$('#login_box').resetForm();
});
</script>
</head>
 
<body>
<div class="total">
<div class="login_box">
  <table width="400" border="0" cellspacing="0" cellpadding="0" style="margin-top:5px;">
    <tr>
      <td height="70" colspan="3" align="left"><img src="<?php echo $this->getAssetUrl('images'); ?>logo.jpg" width="350" height="70" border="0" title="doitphp tools logo"></td>
      </tr>
   </table>
   <table width="400" border="0" cellspacing="0" cellpadding="0" style="margin-top:5px;">
   <form action="<?php echo $this->getActionUrl('ajaxlogin'); ?>" method="post" name="login_box" id="login_box">
    <tr>
      <td width="120" height="50" align="center">用户名：</td>
      <td width="10">&nbsp;</td>
      <td width="270" align="left"><input type="text" name="user_name" class="ip" id="user_name" /></td>
    </tr>
    <tr>
      <td width="120" height="50" align="center">密 码：</td>
      <td width="10">&nbsp;</td>
      <td width="270" align="left"><input type="password" name="user_password" class="ip" id="user_password" /></td>
    </tr>
    <tr>
      <td width="120" height="60" align="center">验证码：</td>
      <td width="10">&nbsp;</td>
      <td width="270"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="70" height="60" align="left"><input type="text" name="vd_code" class="ip" id="vd_code" style="width:60px;"/></td>
          <td width="200" align="left"><img src="<?php echo $this->getActionUrl('vdcode'); ?>/?time=<?php echo time(); ?>" style="border:none; cursor:pointer;" id="vd_image" onclick="refresh_vdcode();" title="点击图片更新验证码"/>&nbsp;<a href="javascript:void(0);" onclick="refresh_vdcode();" title="点击刷新验证码" style="color:#333333; font-size:12px;">刷新</a></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td height="50" colspan="3"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="190" height="50" align="right"><input type="reset" name="reset" value="重值" /></td>
          <td width="20">&nbsp;</td>
          <td width="190" align="left"><input type="submit" name="submit" value="登陆" /></td>
        </tr>
      </table></td>
      </tr>
     </form> 
  </table>
</div>
</div>
</body>
</html>