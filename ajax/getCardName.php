<?
require_once("../inc/php/functions.php");

$iddashcard = $_GET['iddashcard'];

if (!empty($iddashcard)) {
    $sql = "SELECT 
				cardtitle
			FROM 
				dashcard
			WHERE status='ATIVO'
			and	iddashcard=".$iddashcard;

	$res = d::b()->query($sql);

	$row = mysqli_fetch_array($res);
	
	if(!empty($row["cardtitle"])){
		echo $row["cardtitle"];
	}
}

?>