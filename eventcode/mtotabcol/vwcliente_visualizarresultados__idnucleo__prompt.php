<?
#MAF: Este cabecalho deve estar presente em qualquer evento
require_once("../../inc/php/functions.php");if(!($_SESSION["SESSAO"]["LOGADO"])) die();

//Concatena tipos conforme tipo de pessoa
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==3 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){
	$sqlwhere .= " and a.idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].") ";
}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==4){

	$sqlwhere .= " and a.idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")";
	$sqlwhere .= " and exists (
						select 1 
						from resultado r 
						join amostra a on (r.idamostra = a.idamostra and r.idsecretaria in(".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"]."))
					)";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==1){
	$sqlwhere .= "";
	die('[{"":""}]');

}else{
	die('[{"0":"Tipo de UsuÃ¡rio nÃ£o previsto: ['.$_SESSION["SESSAO"]["IDTIPOPESSOA"].']"}]');
}

//Monta o SQL
$sql = "
		select a.idnucleo, if(ifnull(a.lote,'')='',a.nucleo,concat(a.lote,' - ',a.nucleo))
		from nucleo a
		where 
			situacao = 'ATIVO'
			 ".$sqlwhere." 
		order by a.lote, a.nucleo";

$rsql = mysql_query($sql);

if(!$rsql){
	echo $sql."\n";
	die("mtotabcol/autocomplete: Erro ao recuperar registros: ".mysql_error());
}

//monta o resultado em formato JSON para autocomplete
echo "[";
while($r = mysql_fetch_array($rsql)){
	echo $virg."{\"".$r[0]."\":\"".jsonTrataValor($r[1])."\"}";
	$virg=",";
}
echo "]";

?>
