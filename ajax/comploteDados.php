<?
require_once("../inc/php/functions.php");

require_once(__DIR__."/../form/controllers/complote_controller.php");



$inspecionasql=false;//$inspecionanucleo=" and n.idnucleo=10466";
$req = $_GET['acao'];
$idpessoa = $_GET['idpessoa'];

//Clientes do contato


if($req == "buscarunidades"){

	if($_SESSION["SESSAO"]["STRCONTATOCLIENTE"]){

		$clausulacontato = " and p.idpessoa in (".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].")";
	}else{
		$clausulacontato = "";
	}

	$rescli = ComparativoController::buscarUnidadesClientes(cb::idempresa(),$clausulacontato);

	echo json_encode($rescli,JSON_UNESCAPED_UNICODE);
}


if($req == "buscarnucleo" && !empty($idpessoa)){

	
	$arrIdades=array();
	$arrTmp=array();
	$arrClienteNucleos=array();
	$arrNucleos=array();
	$arrTestes=array();
	$arrVacinas=array();

	$idpessoa = (empty($idpessoa)?$_SESSION['SESSAO']["IDPESSOA"]:$idpessoa);

	$resnuc = ComparativoController::buscarNucleos(cb::idempresa(),$idpessoa);
	foreach($resnuc as $k => $rn){

		if(!empty($rn["idservicoensaio"])){
			$rn["idade"]=traduzid("servicoensaio", "idservicoensaio", "dia", $rn["idservicoensaio"]);		
		}
		$rotNucleo=acentos2ent($rn["lote"]." - ".$rn["nucleo"]." (".$rn["ano"].")");
		//Monta os dados com todos os testes encontrados para cada Núcleo/Teste
		$arrClienteNucleos["clientes"][$rn["idpessoa"]][$rn["idnucleo"]]["nucleo"]=$rotNucleo;
		$arrClienteNucleos["clientes"][$rn["idpessoa"]][$rn["idnucleo"]]["situacao"]=$rn["situacao"];
		$arrClienteNucleos["clientes"][$rn["idpessoa"]][$rn["idnucleo"]]["testes"][$rn["idprodserv"]]="";
		
		//Monta os dados agrupados por idade, para facilitar o loop no javascript para o gráfico
		$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["idres"] = $rn["idresultado"];

		if(!empty($rn["gmt"])){
			$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["gmt"] = $rn["gmt"];
		}else if(!empty($rn["vacina"])){
			$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["gmt"] = "0";
		}else{
			$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["gmt"] = "0";
		}

		$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["vacina"] = (!empty($rn["vacina"])) ? "<br>".$rn["vacina"] : ""; // info das vacinas vem aqui// info das vacinas vem aqui

		$arrTmp[] = $rn["idnucleo"];
		//Monta Array com os tipos de teste encontrados
		$arrTestes["tiposteste"][$rn["idprodserv"]]["descr"] = acentos2ent($rn["descr"]);
		
		//Monta Array com os Núcleos
		$arrNucleos["nucleos"][$rn["idnucleo"]]=$rotNucleo;
	}
	
	$arrTmp = array_unique($arrTmp, SORT_STRING);
	//var_dump($arrTmp);die;
	foreach ($arrTmp as $key => $value) {
		$arrVacinas = ComparativoController::buscarNucleoVacina($value);
	}

	//Ordena pela idade
	ksort($arrIdades["idades"]);
	$arrayJson = array(
						"arrIdades" => $arrIdades,
						"arrTmp" => $arrTmp,
						"arrClienteNucleos" => $arrClienteNucleos,
						"arrNucleos" => $arrNucleos,
						"arrTestes" => $arrTestes,
						"arrVacinas" => $arrVacinas,
					);


	echo json_encode($arrayJson,JSON_UNESCAPED_UNICODE);
}




// //Loop nos clientes
// while($rc = mysql_fetch_assoc($rescli)){
// 	//Armazena
	
	
	
// 	$sqlnuc = "SELECT n.idnucleo
// 		,n.nucleo
// 		,n.lote
// 		,YEAR(n.alojamento) as ano
// 		,n.situacao
// 		,n.tipoaves
// 		,a.idunidade
// 		,CAST(a.idade as UNSIGNED) as idade
// 		,r.idservicoensaio
// 		,ps.idprodserv
// 		,ps.tipoespecial
// 		,ps.descr
// 		,r.idresultado
// 		,CASE 
// 			WHEN tipoespecial = 'ELISA' THEN (select re.titer from resultadoelisa re where re.idresultado=r.idresultado and re.nome = 'GMN' and re.status='A')
//             ELSE r.gmt
// 		END as gmt
// 		,(select group_concat(nv.vacina SEPARATOR ' / ') from nucleovacina nv where n.idnucleo = nv.idnucleo AND nv.datavacina = CAST(a.idade as UNSIGNED)) as vacina
// 	from amostra a FORCE INDEX(pessoa_nucleo)
// 		join nucleo n FORCE INDEX(PRIMARY) on (n.idnucleo = a.idnucleo)
// 		join resultado r FORCE INDEX(idamostra) on (r.idamostra = a.idamostra)
// 		join prodserv ps FORCE INDEX(PRIMARY) on (
// 			ps.idprodserv = r.idtipoteste 
// 			AND ps.tipo='SERVICO' 
// 			AND ps.tipoespecial in ('PESAGEM','GUMBORO','BRONQUITE','NEWCASTLE','PNEUMOVIRUS','REOVIRUS','ELISA','GUMBORO IND','BRONQUITE IND','NEWCASTLE IND','PNEUMOVIRUS IND','REOVIRUS IND')
// 		)
// 	where a.idempresa = ".cb::idempresa()."
// 		and a.idpessoa = ".$rc["idpessoa"]."
// 		and CAST(a.idade as UNSIGNED) > ''
// 		".$inspecionanucleo." 
// 		-- and n.situacao='ATIVO' and n.idpessoa = 661
// 	order by ano desc,n.nucleo";

// 	if($_SESSION["SESSAO"]["USUARIO"]=="laudolab")die($sqlnuc);
// 	if($inspecionasql)echo("<pre>SQL: ".$sqlnuc."</pre>");
// 	$resnuc = mysql_query($sqlnuc) or die("Erro pesquisando nucleos: ".mysql_error());
// 	$nnuc = mysqli_num_rows($resnuc);
// 	if($inspecionasql)echo("<pre>Número de resultados: ".$nnuc."</pre>");
// 	//Loop nos ClienteNucleos
// /*	while($rn = mysql_fetch_assoc($resnuc)){
// 	    if(!empty($rn["idservicoensaio"])){
// 		$sqli="select ifnull(dia,'0') as campo from servicoensaio where idservicoensaio=".$rn["idservicoensaio"];
// 		$resi = mysql_query($sqli) or die("Erro dia 0 na servicoensaio: ".mysql_error());
// 		$rowi= mysql_fetch_assoc($resi);
// 		$rn["idade"]=$rowi['campo'];
// 		//$rn["idade"]=traduzid("servicoensaio", "idservicoensaio", "dia", $rn["idservicoensaio"]);		
// 	    }
// 	*/
// 	while($rn = mysql_fetch_assoc($resnuc)){
// 	    if(!empty($rn["idservicoensaio"])){
// 		$rn["idade"]=traduzid("servicoensaio", "idservicoensaio", "dia", $rn["idservicoensaio"]);		
// 	    }    
// 		$rotNucleo=acentos2ent($rn["lote"]." - ".$rn["nucleo"]." (".$rn["ano"].")");
// 		//Monta os dados com todos os testes encontrados para cada Núcleo/Teste
// 		$arrClienteNucleos["clientes"][$rc["idpessoa"]][$rn["idnucleo"]]["nucleo"]=$rotNucleo;
// 		$arrClienteNucleos["clientes"][$rc["idpessoa"]][$rn["idnucleo"]]["situacao"]=$rn["situacao"];
// 		$arrClienteNucleos["clientes"][$rc["idpessoa"]][$rn["idnucleo"]]["testes"][$rn["idprodserv"]]="";
		
// 		//Monta os dados agrupados por idade, para facilitar o loop no javascript para o gráfico
// 		$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["idres"] = $rn["idresultado"];

// 		if(!empty($rn["gmt"])){
// 			$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["gmt"] = $rn["gmt"];
// 		}else if(!empty($rn["vacina"])){
// 			$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["gmt"] = "0";
// 		}else{
// 			$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["gmt"] = "";
// 		}

// 		$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["vacina"] = (!empty($rn["vacina"])) ? "<br>".$rn["vacina"] : ""; // info das vacinas vem aqui// info das vacinas vem aqui

// 		$arrTmp[] = $rn["idnucleo"];
// 		//Monta Array com os tipos de teste encontrados
// 		$arrTestes["tiposteste"][$rn["idprodserv"]]["descr"] = acentos2ent($rn["descr"]);
		
// 		//Monta Array com os Núcleos
// 		$arrNucleos["nucleos"][$rn["idnucleo"]]=$rotNucleo;
		
// 	}
	
// }

// $arrTmp = array_unique($arrTmp, SORT_STRING);
// //var_dump($arrTmp);die;
// foreach ($arrTmp as $key => $value) {
	
// 	$sqlvac = "SELECT group_concat(nv.vacina SEPARATOR ' / ') as vacinas,nv.datavacina,nv.idnucleo
// 				FROM nucleovacina nv 
// 				WHERE nv.idnucleo = ".$value." AND 
// 				NOT EXISTS (
// 					SELECT 1 
// 					FROM amostra a 
// 					WHERE a.idnucleo = nv.idnucleo AND 
// 					nv.datavacina = CAST(a.idade AS UNSIGNED))
// 				GROUP BY nv.datavacina";

// 	if($inspecionasql)echo("<pre>SQL: ".$sqlvac."</pre>");

// 	$resvac = mysql_query($sqlvac) or die("Erro pesquisando núcleo vacina: ".mysql_error());
// 	$nvac = mysqli_num_rows($resvac);

// 	if($inspecionasql)echo("<pre>Número de resultados: ".$nvac."</pre>");

// 	if($nvac > 0){
// 		$i = 0;
// 		while($rvac = mysqli_fetch_assoc($resvac)){
// 			$arrVacinas["nucleos"][$rvac["idnucleo"]][$i]["gmt"] = 0;
// 			$arrVacinas["nucleos"][$rvac["idnucleo"]][$i]["idres"] = 0;
// 			$arrVacinas["nucleos"][$rvac["idnucleo"]][$i]["vacinas"] = "<br>".$rvac["vacinas"];
// 			$arrVacinas["nucleos"][$rvac["idnucleo"]][$i]["idade"] = $rvac["datavacina"];
// 			$i++;
// 		}
// 	}
// }

/*
 * Ordena as Idades em cada nucleo. Sem este loop seria necessário executar novamente a consulta principal acima para cada nucleo
 *
foreach ($arrClienteNucleos["nucleos"] as $idnucleo => $arrinfo) {

	foreach ($arrinfo["testes"] as $teste => $idades) {

		foreach ($idades["idades"] as $idade => $gmt) {
			echo $k;
		}
		//Ordena as idades do array conforme as chaves
		ksort($arrClienteNucleos["nucleos"][$idnucleo]["testes"][$teste]["idades"]);
		
		//Recupera as menores e maiores idades existentes conforme a chave (idade)
		$arrClienteNucleos["nucleos"][$idnucleo]["testes"][$teste]["idademin"] = min( array_keys($arrClienteNucleos["nucleos"][$idnucleo]["testes"][$teste]["idades"]));
		$arrClienteNucleos["nucleos"][$idnucleo]["testes"][$teste]["idademax"] = max( array_keys($arrClienteNucleos["nucleos"][$idnucleo]["testes"][$teste]["idades"]));

	}
}*/

//Ordena pela idade
// ksort($arrIdades["idades"]);

#echo json_encode($arrRet);
#print_r($arrClientes);
#print_r($arrTestes);
#print_r($arrClienteNucleos);

// $jClientes = json_encode($arrClientes,JSON_UNESCAPED_UNICODE);
// $jTestes = json_encode($arrTestes,JSON_UNESCAPED_UNICODE);
// $jClienteNucleos = json_encode($arrClienteNucleos,JSON_UNESCAPED_UNICODE);
// $jNucleos = json_encode($arrNucleos,JSON_UNESCAPED_UNICODE);
// $jIdades = json_encode($arrIdades,JSON_UNESCAPED_UNICODE);
// $jVacinas = json_encode($arrVacinas,JSON_UNESCAPED_UNICODE);

?>