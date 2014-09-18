<!-- calendar begin -->
<table style="width:100%;border:1px solid #9EC9FE;font-size:12px;line-height:20px;color:#222;height:auto;">
	<tr align="center">
		<td colspan="7"><?php echo $data['year']; ?> 年  <?php echo $data['month']; ?> 月</td>
	</tr>
	<tr align="center" style="background-color:#9EC9FE;color:#FFF;">
		<td>日</td><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td>
	</tr>
<?php foreach ($data['content'] as $lines) {?>
	<tr align="center">
<?php foreach ($lines as $value) {?>
<?php if($value['status'] == false) {?>
		<td>&nbsp;</td>
<?php } else {?>
<?php if($value['status'] === 'used') {?>
		<td><?php if(isset($value['ext']) && is_array($value['ext'])) {?><a href="<?php echo $value['ext']['url']; ?>" <?php if(isset($value['ext']['target'])) {?>target="<?php echo $value['ext']['target']; ?>"<?php } ?> style="background-color:#A5A5A5;color:#FFF;display:block;" onmouseover='javascript:this.style.background="#58A0EC";' onmouseout='javascript:this.style.background="#A5A5A5";'><?php echo $value['date']; ?></a><?php } else {echo $value['date']; }?></td>
<?php } elseif ($value['status'] === 'today') {?>
		<td style="color:#FFF;background-color:#58A0EC;"><?php echo $value['date']; ?></td>
<?php } else {?>
		<td><?php echo $value['date']; ?></td>
<?php
		}
	}
}
?>
	</tr>
<?php } ?>
</table>