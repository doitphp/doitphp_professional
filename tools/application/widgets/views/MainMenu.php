<!-- 主菜单 -->
<div class="nav">
<ul>
<?php if($controller == 'index' && $action == 'index'){ ?>
<li class="current">首页</li>
<?php } else { ?>
<li><a href="<?php echo $this->createUrl('index/index'); ?>">首页</a></li>
<?php } ?>
<?php if($status == true){?>
<?php if($controller == 'file' && $action == 'index') { ?>
<li class="current">文件管理</li>
<?php } else { ?>
<li><a href="<?php echo $this->createUrl('file/index'); ?>">文件管理</a></li>
<?php } ?>
<?php } else { ?>
<?php if($controller == 'index' && $action == 'webapp'){ ?>
<li class="current">WebApp管理</li>
<?php } else { ?>
<li><a href="<?php echo $this->createUrl('index/webapp'); ?>">WebApp管理</a></li>
<?php } ?>
<?php } ?>
</ul>
</div>
<!-- /主菜单 -->