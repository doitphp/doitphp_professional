<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DoitPHP Tools</title>
<link href="<?php echo $this->getAssetUrl('images'); ?>screen.css" rel="stylesheet" type="text/css">
<!--[if lt IE 8]><link rel="stylesheet" href="<?php echo $this->getAssetUrl('images'); ?>ie.css" type="text/css"><![endif]-->
<?php echo Script::add('jquery'); ?>
<?php echo Script::add('form'); ?>
</head>

<body style="background:#F5F5F5;">
<!-- total-->
<div class="container" style="margin-top:20px; padding:30px; background:#FFFFFF; width:900px;">
<!-- top -->
<div class="header">
<div><a href="<?php echo $this->getBaseUrl(); ?>"><img src="<?php echo $this->getAssetUrl('images'); ?>logo.jpg" width="350" height="70" border="0" title="doitphp tools logo"></a></div>
<div class="text-align-right" style="padding-right:15px;">欢迎使用: <span class="blue">DoitPHP Tools</span> ( 标准版 )&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo $this->createUrl('login/logout'); ?>">退出</a></div>
<!-- main menu-->
<?php $this->widget('MainMenu'); ?>
<!-- /main menu-->
</div>
<hr class="space"/>
<!-- /top -->

<!-- content -->
<div class="content">
<!-- Conter content beging-->
<?php echo $viewContent; ?>
<!-- Conter content end-->
</div>
<!-- /content -->
<!-- footer-->
<div class="footer">CopyRight <a href="http://www.doitphp.com" target="_blank">www.doitphp.com</a> 2010 - 2013</div>
<!-- /footer-->
</div>
<!-- /total-->
</body>
</html>