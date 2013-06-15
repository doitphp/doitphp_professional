<table border="0" cellspacing="10" cellpadding="0">
<caption>编辑Method</caption>
  <tr>
    <td width="90" height="25" align="right"><label>Method 名称：</label></td>
    <td width="310" align="left"><input type="text" name="edit_method_name_box" id="edit_method_name_box" class="text" value="<?php echo $methodInfo['name']; ?>"/></td>
  </tr>
  <tr>
    <td width="90" height="25" align="right"><label>描述：</label></td>
    <td align="left"><input type="text" name="edit_method_desc_box" id="edit_method_desc_box" class="text" value="<?php echo $methodInfo['desc']; ?>"/></td>
  </tr>
  <tr>
    <td width="90" height="25" align="right"><label>访问权限：</label></td>
    <td align="left">
<select id="edit_method_access_box" name="edit_method_access_box">
	<option <?php echo ($methodInfo['access'] == 'protected') ? 'selected="selected"' : ''; ?> value="protected">protected</option>
	<option <?php echo ($methodInfo['access'] == 'private') ? 'selected="selected"' : ''; ?> value="private">private</option>
</select>
</td>
  </tr>
  <tr>
    <td width="90" height="25" align="right"><label>返回数据类型：</label></td>
    <td align="left">
<select id="edit_method_return_box" name="edit_method_return_box">
	<option <?php echo ($methodInfo['return'] == 'integer') ? 'selected="selected"' : ''; ?> value="integer">integer</option>
	<option <?php echo ($methodInfo['return'] == 'string') ? 'selected="selected"' : ''; ?> value="string">string</option>
	<option <?php echo ($methodInfo['return'] == 'array') ? 'selected="selected"' : ''; ?> value="array">array</option>
	<option <?php echo ($methodInfo['return'] == 'boolean') ? 'selected="selected"' : ''; ?> value="boolean">boolean</option>
	<option <?php echo ($methodInfo['return'] == 'object') ? 'selected="selected"' : ''; ?> value="object">object</option>
	<option <?php echo ($methodInfo['return'] == 'void') ? 'selected="selected"' : ''; ?> value="void">void</option>
	<option <?php echo ($methodInfo['return'] == 'mixed') ? 'selected="selected"' : ''; ?> value="mixed">mixed</option>
	<option <?php echo ($methodInfo['return'] == 'unknown') ? 'selected="selected"' : ''; ?> value="unknown">unknown</option>
</select>
	</td>
  </tr>
  <tr>
    <td width="90" height="25"><input type="hidden" value="<?php echo $id; ?>" name="edit_method_method_id" id="edit_method_id_box"/></td>
    <td align="right"><input type="button" name="edit_method_button" value="编辑" onclick="editMethodList();"/></td>
  </tr>
</table>