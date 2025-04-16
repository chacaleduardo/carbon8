<?
#MAF: Este cabecalho deve estar presente em qualquer evento
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();

$term = $_GET["term"];

//concatena clausulas WHERE
if(!empty($term)){
	$sqlwhere .= " and tipoamostra like '%".$term."%' ";
}else{
	$sqlwhere .= " and tipoamostra <> '' "; 
}


//Concatena tipos conforme tipo de pessoa
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==3 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){
	$sqlwhere .= " and exists (
					select 1 
					from amostra a2 
					where a2.idtipoamostra = t.idtipoamostra
						and a2.idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")
				) ";
}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==4){//contato oficial

	$sqlwhere .= " and exists (
					select 1 
					from amostra a2
					where a2.idtipoamostra = t.idtipoamostra
						and a2.idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")
						and exists (select 1 from resultado r where r.idamostra = a2.idamostra and r.idsecretaria in(".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"]."))
				) ";

}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==1){//Funcionário
	$sqlwhere .= "";

}else{
	die('[{"0":"Tipo de Usuário não previsto: ['.$_SESSION["SESSAO"]["IDTIPOPESSOA"].']"}]');
}


//Monta o SQL
$sql = "select idtipoamostra, tipoamostra 
		from tipoamostra t
		where status = 'ATIVO'
			".$sqlwhere."
		order by tipoamostra";

$rsql = mysql_query($sql);

if(!$rsql){
	die("mtotabcol/autocomplete: Erro ao recuperar registros: ".mysql_error());
}

//monta o resultado em formato JSON para autocomplete
echo "[";
while($r = mysql_fetch_array($rsql)){
	echo $virg."{\"".$r[0]."\":\"".$r[1]."\"}";
	$virg=",";
}
echo "]";

?>
