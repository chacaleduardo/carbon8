<?include_once("../../inc/php/functions.php");
$year  = ( date("Y")+2);
$myear  = ( date("Y")-10);
$virg="";
$json="[";
for ($year; $year >= $myear; $year--){
	$json.=$virg.'{"'.$year.'":"'.$year.'"}';
	$virg=",";
}
$json.="]";
echo($json);
?>