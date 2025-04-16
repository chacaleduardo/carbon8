<?
$year  = ( date("Y"));
$myear  = ( date("Y")-12);
$virg="";
$json.="[";
for ($year; $year >= $myear; $year--){
	$json.=$virg.'{"'.$year.'":"'.$year.'"}';
	$virg=",";
}
$json.="]";
echo($json);