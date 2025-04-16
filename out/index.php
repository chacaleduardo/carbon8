<?
//Evitar cache temporariamente: atinge somente a index.php
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
include("../inc/php/appvar.php");
if(_SITEOUT != true){
	header("Location: ../index.php");
}else{
?>
<html>
<head>
<meta http-equiv="refresh" content="120;url=/">
<meta http-equiv="content-type" content="text/html; charset=UTF-8"><title>Em Manuten&ccedil;&atilde;o - Down for maintenance</title>
<style>
* {
    box-sizing: border-box;
}
html{
	margin:0px;
	padding:0px;
}
body{
padding: 10px;
margin:0px;
background: rgba(0,135,181,1);
background: -moz-linear-gradient(left, rgba(0,135,181,1) 0%, rgba(0,114,143,1) 32%, rgba(3,145,81,1) 68%, rgba(185,213,62,1) 100%);
background: -webkit-gradient(left top, right top, color-stop(0%, rgba(0,135,181,1)), color-stop(32%, rgba(0,114,143,1)), color-stop(68%, rgba(3,145,81,1)), color-stop(100%, rgba(185,213,62,1)));
background: -webkit-linear-gradient(left, rgba(0,135,181,1) 0%, rgba(0,114,143,1) 32%, rgba(3,145,81,1) 68%, rgba(185,213,62,1) 100%);
background: -o-linear-gradient(left, rgba(0,135,181,1) 0%, rgba(0,114,143,1) 32%, rgba(3,145,81,1) 68%, rgba(185,213,62,1) 100%);
background: -ms-linear-gradient(left, rgba(0,135,181,1) 0%, rgba(0,114,143,1) 32%, rgba(3,145,81,1) 68%, rgba(185,213,62,1) 100%);
background: linear-gradient(to right, rgba(0,135,181,1) 0%, rgba(0,114,143,1) 32%, rgba(3,145,81,1) 68%, rgba(185,213,62,1) 100%);
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#0087b5', endColorstr='#b9d53e', GradientType=1 );
}
.popup{
display: flex;
flex-direction:column;
align-items: center;
justify-content: center;
background-color:white;
width:100%;
height:100%
}
</style>
</head>
<body style="display:flex;justify-content: center;align-items: center;height: 100vh;">

<div class="popup" style="border: 0px solid silver;">
<div align="center">
<br>&nbsp;
<img src="../inc/img/logo_biofy.png" style="width:25vw;height:auto;">
</div>
<br>
<div align="center">
</div>
<br><br>
<div align="center">
<font color="gray" face="arial" size="2">
O aplicativo Sislaudo est&aacute; em manuten&ccedil;&atilde;o.
<br>This website is currently down for maintenance.
<br>
<br><?=_SITEOUTMSG?>
<br>&nbsp;
<br>&nbsp;

</font>
</div>
</div>
</body></html>
<?
}
?>
