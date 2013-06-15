<table border="0" cellpadding="0" cellspacing="10">
<caption>添加Method参数</caption>
  <tr>
    <td width="90" height="30" align="right"><label>参数名称：</label></td>
    <td width="310"><input type="text" name="add_method_params_name_box" id="add_method_params_name_box" class="text"/></td>
  </tr>
  <tr>
    <td width="90" height="30" align="right"><label>数据类型：</label></td>
    <td>
<select id="add_method_params_type_box" name="add_method_params_type_box">
	<option value="integer">integer</option>
	<option value="string">string</option>
	<option value="array">array</option>
	<option value="boolean">boolean</option>
	<option value="object">object</option>
	<option value="void">void</option>
	<option value="mixed">mixed</option>
	<option selected="selected" value="unknown">unknown</option>
</select>
	</td>
  </tr>
  <tr>
    <td width="90" height="30" align="right"><label>描述：</label></td>
    <td><input type="text" name="add_method_params_desc_box" id="add_method_params_desc_box" class="text"/></td>
  </tr>
  <tr>
    <td width="90" height="30" align="right"><label>默认值：</label></td>
    <td><input type="text" name="add_method_params_default_box" id="add_method_params_default_box" class="text"/></td>
  </tr>
  <tr>
    <td width="90" height="30">&nbsp;</td>
    <td><input type="button" name="add_method_params_submit_button" value="添加" onclick="addMethodParams(<?php echo $id; ?>);"/></td>
  </tr>
</table>