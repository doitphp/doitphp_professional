<table border="0" cellspacing="10" cellpadding="0">
<caption>添加Method</caption>
  <tr>
    <td width="90" height="25" align="right"><label>Method 名称：</label></td>
    <td width="310" align="left"><input type="text" name="add_method_name_box" id="add_method_name_box" class="text"/></td>
  </tr>
  <tr>
    <td width="90" height="25" align="right"><label>描述：</label></td>
    <td align="left"><input type="text" name="add_method_desc_box" id="add_method_desc_box" class="text"/></td>
  </tr>
  <tr>
    <td width="90" height="25" align="right"><label>访问权限：</label></td>
    <td align="left">
<select id="add_method_access_box" name="add_method_access_box">
	<option selected="selected" value="public">public</option>
	<option value="protected">protected</option>
	<option value="private">private</option>
</select>
</td>
  </tr>
  <tr>
    <td width="90" height="25" align="right"><label>返回数据类型：</label></td>
    <td align="left">
<select id="add_method_return_box" name="add_method_return_box">
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
    <td width="90" height="25">&nbsp;</td>
    <td align="right"><input type="button" name="add_method_button" value="添加" onclick="addMethodList();"/></td>
  </tr>
</table>