<table border="0" cellspacing="0" cellpadding="0">
<caption>附加信息 ( 注释 )</caption>
  <tr>
    <td width="100" align="right">文件描述:</td>
    <td width="10"></td>
    <td><input type="text" name="note_description_box" id="note_description_box" class="text"/></td>
  </tr>
  <tr>
    <td width="100" align="right">作者:</td>
    <td width="10"></td>
    <td><input type="text" name="note_author_box" id="note_author_box" class="text" value="<?php echo $noteInfo['author']; ?>"/></td>
  </tr>
  <tr>
    <td width="100" align="right">版权信息:</td>
    <td width="10"></td>
    <td><input type="text" name="note_copyright_box" id="note_copyright_box" class="text" value="<?php echo $noteInfo['copyright']; ?>"/></td>
  </tr>
    <tr>
    <td align="right">发行协议:</td>
    <td></td>
    <td><input type="text" name="note_license_box" id="note_license_box" class="text" value="<?php echo $noteInfo['lisence']; ?>"/></td>
  </tr>
  <tr>
    <td align="right">相关链接:</td>
    <td></td>
    <td><input type="text" name="note_link_box" id="note_link_box" class="text" value="<?php echo $noteInfo['link']; ?>"/></td>
  </tr>
</table>
