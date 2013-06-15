<table border="0" cellspacing="10" cellpadding="0">
<caption>编辑Action</caption>
  <tr>
    <td width="90" height="25" align="right"><label>Action 名称：</label></td>
    <td width="310" align="left"><input type="text" name="action_name" id="edit_action_name_box" class="text" value="<?php echo $actionInfo['name']; ?>"/></td>
  </tr>
  <tr>
    <td width="90" height="25" align="right"><label>描述：</label></td>
    <td align="left"><input type="text" name="action_desc" id="edit_action_desc_box" class="text" value="<?php echo $actionInfo['desc']; ?>"/></td>
  </tr>
  <tr>
    <td width="90" height="25">&nbsp;</td>
    <td align="right"><input type="button" name="edit_action_button" value="编辑" onclick="editActionList(<?php echo $id; ?>);"/></td>
  </tr>
</table>