<?
error_reporting(E_ALL);
ini_set("display_errors","1");

$xml =file_get_contents ($arq_final);
//	echo($lines);
		
if (!empty($xml)) {

	$xml=str_replace("'","\"",$xml);

	$sql="UPDATE nf
		set envionfe='CONCLUIDA', xmlret = '".$xml."'
		where idnf = ".$_idobjeto;
	$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);

	//  header('Content-type: text/xml; charset=UTF-8');
	echo("XML atualizado com sucesso.");

}else{
	d::b()->query("DELETE FROM arquivo WHERE idarquivo = ".$insertidArq);
	echo "Problema ao abrir arquivo!";
}//$handle