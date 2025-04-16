<?
#MAF: Este cabecalho deve estar presente em qualquer evento
require_once("../../inc/php/functions.php");
if(!($_SESSION["SESSAO"]["LOGADO"])) die();


//Concatena tipos conforme tipo de pessoa
if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==3 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){
	//$sqlwhere .= " and a.idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].") ";
	
	$sqlwhere .= " AND tt.idtipoteste EXISTS (SELECT r.idtipoteste
												FROM resultado r JOIN amostra a ON a.idamostra = r.idamostra 
											   WHERE r.idtipoteste = tt.idtipoteste
												 AND a.idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")
												 ".getidempresa('a.idempresa','amostra').")";
	
	
}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==4){//contato oficial

	//$sqlwhere .= " and a.idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")";
	//$sqlwhere .= " and r.idsecretaria in(".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"].")";

	$sqlwhere .= " AND tt.idtipoteste EXISTS (SELECT r.idtipoteste
												FROM resultado r JOIN amostra a ON a.idamostra = r.idamostra 
											   WHERE r.idtipoteste = tt.idtipoteste
												 AND a.idpessoa in(".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")
												 AND r.idsecretaria in(".$_SESSION["SESSAO"]["STRCONTATOSECRETARIA"].")
												 ".getidempresa('a.idempresa','amostra').")";


}elseif($_SESSION["SESSAO"]["IDTIPOPESSOA"]==1){//FuncionÃ¡rio
	$sqlwhere .= "";

}else{
	die('[{"0":"Tipo de Usuário não previsto: ['.$_SESSION["SESSAO"]["IDTIPOPESSOA"].']"}]');
}

//Monta o SQL
$sql = "select distinct tt.idtipoteste, tt.idempresa, e.sigla as 'siglaempresa', case 
		when (tt.status='INATIVO')then concat(tt.tipoteste,' (INATIVO)')
		else tt.tipoteste end as tipoteste
		from vwtipoteste tt
		join empresa e on e.idempresa = tt.idempresa

			".$sqlwhere."
		order by tt.tipoteste";


if($_SESSION["SESSAO"]["USUARIO"]=='iagroms'){
	//die($sql);
}

$rsql = mysql_query($sql);

if(!$rsql){
	die("mtotabcol/autocomplete: Erro ao recuperar registros: ".mysql_error());
}
if($_SESSION["SESSAO"]["USUARIO"]=="marcelosouza"){
	//die($sql);
}
//monta o resultado em formato JSON para autocomplete
//$r[0]=primeira coluna select  /  $r[1]=segunda coluna select
echo "[";
while($r = mysql_fetch_array($rsql)){

	echo $virg."{\"".$r['idtipoteste']."\":\"".str_replace("\\","\\\\",$r['siglaempresa'].' - '.$r['tipoteste'])."\"}";

	$virg=",";
}
echo "]";

?>
