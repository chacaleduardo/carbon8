<?
require_once("../inc/php/functions.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pk = $_GET['pk'];
	$modulo = $_GET['modulo'];
	removeBloqueioTela($pk,$modulo);
	return json_encode(array(
		"stauts" => "Sessão removida."
	));
}