<?
/* 
 *** Variáveis disponíveis no escopo:
 *	$_idobjeto
 *	$_tipoobjeto
 *	$_tipoarquivo
 *	$_caminho
 *  $_FILES
*/

/*
 * ####################################### Excluir arquivos de AVATAR anteriores
 */
$sd = "SELECT idarquivo, caminho
	FROM arquivo 
	WHERE tipoobjeto='pessoa'
		AND tipoarquivo='AVATAR' 
		AND idobjeto = ".$_idobjeto;

$qd = d::b()->query($sd) or die(getNomeArquivo(__FILE__)." #1: ".mysqli_error(d::b()));

while($r= mysqli_fetch_assoc($qd)){
	unlink($r["caminho"]);
	$sd="DELETE FROM arquivo WHERE idarquivo=".$r["idarquivo"]." LIMIT 1";
	d::b()->query($sd) or die(getNomeArquivo(__FILE__)." #2: ".mysqli_error(d::b()));
}

/*
 * ####################################### Redimensionar Imagem
 */
//ini_set("display_errors", true);//error_reporting(E_ALL);
//Observação: Por causa nos includes aninhados, remover a declaração "namespace claviska;" dentro do arquivo SimpleImage.php
require "../inc/php/img/SimpleImage.php";

try {
	$image = new SimpleImage();
	//Redimensiona
	$image
	  ->fromFile($_FILES["file"]["tmp_name"])
	  ->resize(250) // Redimensionar para 500 pixels, calculando automaticamente a proporção de height X weight
	  ->toFile($_FILES["file"]["tmp_name"],null,65);

} catch(Exception $err) {
  echo $err->getMessage();
}

?>