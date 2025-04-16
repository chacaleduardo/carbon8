<?
require_once("../inc/php/functions.php");
require_once(__DIR__."/../form/controllers/complote_controller.php");


if ($_GET['inspecionarsql']) {
	$inspecionasql=true;
}else {
	$inspecionasql=false;
}

//$inspecionanucleo=" and n.idnucleo=10466";
/* */
//Clientes do contato
$respd = ComparativoController::buscarLotesParaComparativo(getidempresa('p.idempresa','prodserv'),getidempresa('a.idempresa',''));
$npd = count($respd);

if($inspecionasql)echo("<pre>Número de resultados: ".$npd."</pre>");

$arrProd=array();
$arrProdNucleos=array();
$arrNucleos=array();
$arrTestes=array();
$arrIdades=array();

$arrVacinas=array();
$arrTmp=array();

//Loop nos clientes
foreach($respd as $k => $rc){
	//Armazena
	$arrProd["produtos"][$rc["idlote"]]["descr"]=$rc["descr"];
	
	$resnuc = ComparativoController::buscarNucleosBioterio(getidempresa('a.idempresa',''),$rc['idlote']);
	$nnuc = count($resnuc);
	if($inspecionasql)echo("<pre>Número de resultados: ".$nnuc." - ".$rc['descr']."</pre>");

	if ($nnuc > 0) {
		foreach($resnuc as $k1 => $rn){
			
			if(!empty($rn["idservicoensaio"])){
			//$rn["idade"]=traduzid("servicoensaio", "idservicoensaio", "dia", $rn["idservicoensaio"]);
			$dia = traduzid('servicoensaio','idservicoensaio','dia',$rn["idservicoensaio"],false);
			$rn["idade"]=$dia;
			}    
			$rotNucleo=acentos2ent($rn["lote"]." - ".$rn["nucleo"]." (".$rn["ano"].")");
			//Monta os dados com todos os testes encontrados para cada Núcleo/Teste
			$arrProdNucleos["produtos"][$rc["idlote"]][$rn["idnucleo"]]["nucleo"]=$rotNucleo;
			$arrProdNucleos["produtos"][$rc["idlote"]][$rn["idnucleo"]]["situacao"]=$rn["situacao"];
			$arrProdNucleos["produtos"][$rc["idlote"]][$rn["idnucleo"]]["testes"][$rn["idprodserv"]]="";
			
			//Monta os dados agrupados por idade, para facilitar o loop no javascript para o gráfico
			$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["idres"] = $rn["idresultado"];
	
			if(!empty($rn["gmt"])){
				$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["gmt"] = $rn["gmt"];
			}else if(!empty($rn["vacina"])){
				$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["gmt"] = "0";
			}else{
				$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["gmt"] = "";
			}
	
			$arrIdades["idades"][$rn["idade"]][$rn["idnucleo"]][$rn["idprodserv"]]["vacina"] = (!empty($rn["vacina"])) ? "<br>".$rn["vacina"] : ""; // info das vacinas vem aqui// info das vacinas vem aqui
	
			$arrTmp[] = $rn["idnucleo"];
			//Monta Array com os tipos de teste encontrados
			$arrTestes["tiposteste"][$rn["idprodserv"]]["descr"] = acentos2ent($rn["descr"]);
			
			//Monta Array com os Núcleos
			$arrNucleos["nucleos"][$rn["idnucleo"]]=$rotNucleo;
			
		}
	}
	
}

$arrTmp = array_unique($arrTmp, SORT_STRING);
//var_dump($arrTmp);die;
foreach ($arrTmp as $key => $value) {
	$arrVacinas = ComparativoController::buscarNucleoVacina($value);
}



//Ordena pela idade
ksort($arrIdades["idades"]);

#echo json_encode($arrRet);
#print_r($arrProd);
#print_r($arrTestes);
#print_r($arrClienteNucleos);

$JProd = json_encode($arrProd,JSON_UNESCAPED_UNICODE);
$jTestes = json_encode($arrTestes,JSON_UNESCAPED_UNICODE);
$jProdNucleos = json_encode($arrProdNucleos,JSON_UNESCAPED_UNICODE);
$jNucleos = json_encode($arrNucleos,JSON_UNESCAPED_UNICODE);
$jIdades = json_encode($arrIdades,JSON_UNESCAPED_UNICODE);
$jVacinas = json_encode($arrVacinas,JSON_UNESCAPED_UNICODE);

?>
<!--
var JProd = <?=$JProd?>;

var jTestes = <?=$jTestes?>;

var jProdNucleos = <?=$jProdNucleos?>;

var jIdades = <?=$jIdades?>;

var jNucleos = <?=$jNucleos?>;

var jVacinas = <?=$jVacinas?>;

//# sourceURL=comploteDados.php-->