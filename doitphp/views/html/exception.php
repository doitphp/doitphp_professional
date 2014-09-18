<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Exception</title>
<style type="text/css">
/*<![CDATA[*/
body {font-family:"Verdana";font-weight:normal;color:black;background-color:white;}
h1 { font-family:"Verdana";font-weight:normal;font-size:18pt;color:red }
h3 {font-family:"Verdana";font-weight:bold;font-size:11pt}
p {font-family:"Verdana";font-size:9pt;}
.message {color: maroon;}
/*]]>*/
</style>
</head>

<body>
<h1>Exception</h1>

<h3>Description</h3>
<p class="message">
<?php echo $message; ?>
</p>
<?php if (defined('DOIT_DEBUG') && DOIT_DEBUG === true && $level != 'Normal') { ?>
<h3>Source File</h3>
<p>
<?php echo $sourceFile; ?>
</p>

<h3>Stack Trace</h3>
<div class="callstack">
<pre>
<?php echo $traceString; ?>
</pre>
</div>
<?php } ?>
</body>
</html>