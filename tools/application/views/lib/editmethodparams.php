<table border="0" cellpadding="0" cellspacing="10">
<caption>编辑Method参数</caption>
  <tr>
    <td width="90" height="30" align="right"><label>参数名称：</label></td>
    <td width="310"><input type="text" name="edit_method_params_name_box" id="edit_method_params_name_box" class="text" value="<?php echo $paramsInfo['name']; ?>"/></td>
  </tr>
  <tr>
    <td width="90" height="30" align="right"><label>数据类型：</label></td>
    <td>
<select id="edit_method_params_type_box" name="edit_method_params_type_box">
	<option <?php echo ($paramsInfo['type'] == 'integer') ? 'selected="selected"' : ''; ?> value="integer">integer</option>
	<option <?php echo ($paramsInfo['type'] == 'string') ? 'selected="selected"' : ''; ?> value="string">string</option>
	<option <?php echo ($paramsInfo['type'] == 'array') ? 'selected="selected"' : ''; ?> value="array">array</option>
	<option <?php echo ($paramsInfo['type'] == 'boolean') ? 'selected="selected"' : ''; ?> value="boolean">boolean</option>
	<option <?php echo ($paramsInfo['type'] == 'object') ? 'selected="selected"' : ''; ?> value="object">object</option>
	<option <?php echo ($paramsInfo['type'] == 'void') ? 'selected="selected"' : ''; ?> value="void">void</option>
	<option <?php echo ($paramsInfo['type'] == 'mixed') ? 'selected="selected"' : ''; ?> value="mixed">mixed</option>
	<option <?php echo ($paramsInfo['type'] == 'unknown') ? 'selected="selected"' : ''; ?> value="unknown">unknown</option>
</select>
	</td>
  </tr>
  <tr>
    <td width="90" height="30" align="right"><label>描述：</label></td>
    <td><input type="text" name="edit_method_params_desc_box" id="edit_method_params_desc_box" class="text" value="<?php echo $paramsInfo['desc']; ?>"/></td>
  </tr>
  <tr>
    <td width="90" height="30" align="right"><label>默认值：</label></td>
    <td><input type="text" name="edit_method_params_default_box" id="edit_method_params_default_box" class="text" value="<?php echo $paramsInfo['default']; ?>"/></td>
  </tr>
  <tr>
    <td width="90" height="30">&nbsp;</td>
    <td><input type="button" name="edit_method_params_submit_button" value="编辑" onclick="editMethodParams(<?php echo $id; ?>, <?php echo $key; ?>);"/></td>
  </tr>
</table>