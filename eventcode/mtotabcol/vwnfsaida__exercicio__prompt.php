<?
$year  = ( date("Y"));
$myear  = ( date("Y")-10);
$json = "";
$virg="";
$json.="[";
for ($year; $year >= $myear; $year--){
	$json.=$virg.'{"'.$year.'":"'.$year.'"}';
	$virg=",";
}
$json.="]";
echo($json);