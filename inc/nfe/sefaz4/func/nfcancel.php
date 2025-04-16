<?php
require_once("../../../../inc/php/validaacesso.php");

require_once("../../../../api/nf/index.php");
require_once("../../../../form/controllers/pedido_controller.php");


//ini_set("display_errors","1");

//error_reporting(E_ALL);

////BIBLIOTECA PARA NFS4.0
//use NFePHP\NFe\Tools;
//use NFePHP\Common\Certificate;
//use NFePHP\Common\Soap\SoapCurl;
//require_once "../vendor/autoload.php";


use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
require_once "../vendor/autoload.php";


function getParcelaItens($idnotafiscal){
	/*
	* verifica se existe alguma parcela item quitada. se existir, nao alterar nada.
	*/
   $rowverifitem = PedidoController::verificarSeExisteParcelaQuitada($idnotafiscal);
   return  $rowverifitem;
}

function recuperaParcelas($inidobj,$instatus,$intipoobjeto){
    /*
     * verifica se existe alguma parcela quitada. se existir, nao alterar nada.
     */
    $rowverif=PedidoController::buscarQuantidadeParcelasPorStatusTipoObjeto($instatus,$intipoobjeto,$inidobj);

    return  $rowverif;
}

function deletaParcelasExistentes($idnotafiscal){
	/*
	* deleta as parcelas existentes.
	*/
	//comissão
	PedidoController::deletaParcelaComissaoPendentePorIdobjeto($idnotafiscal,'nf');
	// contapagaritem
	


	// GVT - 23/07/2021 - @471938 remover assinatura de contas a pagar que serão apagadas
	$resCarrimbo = PedidoController::buscarFaturaSemComissaoPorIdobjeto($idnotafiscal,'nf');

	if(count($resCarrimbo) > 0){
		foreach($resCarrimbo as $rCarrimbo ){   
			//deletar carimbo pendente    
			PedidoController::deletarPorIdObjetoTipoObjetoEIdPessoa($rCarrimbo["id"],'contapagar',798);
		}
	}

	

	PedidoController::deletaParcelaImpostoPendentePorIdobjeto($idnotafiscal,'nf');
	
	$resdev = PedidoController::buscarCategoriaCancelado($idnotafiscal);

	$qtddev=count($resdev);
	// echo("qtd ".$Vqtdnfitem);
	if($qtddev>0){
		//PedidoController::deletaParcelaPendentePorIdobjeto($idnotafiscal,'nf');
		PedidoController::AtualizaParcelaPendentePorIdobjeto($idnotafiscal,'nf','CANCELADO');
		foreach($resdev as $row){  
			PedidoController::atualizarCategoriaSubcategoriaNfItem($idnotafiscal,$row['idcontaitem'],$row['idtipoprodserv']);
		
		}
	}else{
		 // contapagaritem
		 PedidoController::deletaParcelaPendentePorIdobjeto($idnotafiscal,'nf');
		 PedidoController::deletaFaturaSemComissaoPorIdobjeto($idnotafiscal,'nf');   
	}
	

	//PedidoController::deletaFaturaSemComissaoPorIdobjeto($idnotafiscal,'nf');   
}


$qr = "SELECT 
			case n.idempresa 
				when 8 then 1
				else n.idempresa
			end as idempresa,n.idempresafat,o.natoptipo
	from nf n 
	join natop o on(o.idnatop=n.idnatop)
	where n.idnf = ".$_GET["idnotafiscal"];
$rs = d::b()->query($qr);
$rs = mysqli_fetch_assoc($rs);

// _idempresa = isset($rs["idempresa"])?$rs["idempresa"]:$_GET["_idempresa"];
if(!empty($rs["idempresafat"]) and $rs["natoptipo"]!='transferencia'){
	$_idempresa=$rs["idempresafat"];
}else{
	$_idempresa=$rs["idempresa"];
}

$idnotafiscal = $_GET["idnotafiscal"]; //Id da nota fiscal que vem da acao de enviar

$_sql = "SELECT *,str_to_date(nfatualizacao,'%d/%m/%Y %h:%i:%s') as nfatt FROM empresa WHERE idempresa = ".$_idempresa;
$_res=d::b()->query($_sql) or die(mysqli_error(d::b())." erro ao buscar o empresa na tabela empresa ".$_sql);
$_row=mysqli_fetch_assoc($_res);

    $config = [
    "atualizacao" => $_row["nfatt"],
    "tpAmb" => 1, // Se deixar o tpAmb como 2 você emitirá a nota em ambiente de homologação(teste) e as notas fiscais aqui não tem valor fiscal
    "razaosocial" => $_row["nfrazaosocial"],
    "siglaUF" => $_row["nfsiglaUF"],
    "cnpj" => $_row["nfcnpj"],
    "schemes" => $_row["nfschemes"], 
    "versao" => $_row["nfversao"],
    "tokenIBPT" => $_row["nftokenIBPT"]
    ];

    $configJson = json_encode($config);

    $certificadoDigital = file_get_contents($_row["certificado"]);

    // $tools = new Tools($configJson, Certificate::readPfx($certificadoDigital, '010787'));


$sql="select SUBSTRING(idnfe,4) as idnfe,protocolonfe,infcpl from nf where idnf=".$idnotafiscal;
$res=d::b()->query($sql) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
$row=mysqli_fetch_assoc($res);

if(empty($row['idnfe']) or empty($row['protocolonfe'])){
	die("Não foi encontrado o protocolo da notafiscal para cancelar a NF");
}

//$nfe = new ToolsNFePHP;
//$chNFe = "31130323259427000104550010000005021110385551";
//$nProt = "135110002251645";
$chNFe=$row['idnfe'];
$nProt=$row['protocolonfe'];

if(empty($row['infcpl'])){
	$xJust = "A NF foi cancelada pois foi emitida com informações incorretas";
}else{
	$xJust =$row['infcpl'];
}

$tpAmb = '1';
$modSOAP = '2';

try {
	//Verifica se tem remessa. Se tiver, solicita que retire primeiro antes do Cancelamento
	//sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=319465 (Lidiane - 18/05/2020)
	$sqlPagarItem = "select count(*) AS contadoritem
					   from contapagaritem i join remessaitem r on(r.idcontapagar=i.idcontapagar) 
					  where i.idobjetoorigem = ".$idnotafiscal." and i.tipoobjetoorigem='nf'";
	$resPagarItem = d::b()->query($sqlPagarItem) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
	$rowPagarItem = mysqli_fetch_assoc($resPagarItem);
	
	$sqlPagar = "select count(*) AS contador
					  from contapagar c join remessaitem r on(r.idcontapagar=c.idcontapagar)
					  where c.idobjeto = ".$idnotafiscal." and c.tipoobjeto ='nf'";
	$resPagar = d::b()->query($sqlPagar) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
	$rowPagar = mysqli_fetch_assoc($resPagar);

	if($rowPagarItem['contadoritem'] == 0 && $rowPagar['contador'] == 0)
	{
		$tools = new NFePHP\NFe\Tools($configJson, NFePHP\Common\Certificate::readPfx($certificadoDigital, $_row["senha"]));
		// $certificate = Certificate::readPfx($content, 'senha');
		//$tools = new Tools($configJson, $certificate);
		$tools->model('55');

		$chave = $chNFe;
		//$xJust = 'Erro de digitação nos dados dos produtos';
		$nProt = $nProt;
		
	   
		
	     $response = $tools->sefazCancela($chave, $xJust, $nProt);

		//você pode padronizar os dados de retorno atraves da classe abaixo
		//de forma a facilitar a extração dos dados do XML
		//NOTA: mas lembre-se que esse XML muitas vezes seré necessário, 
		//      quando houver a necessidade de protocolos
		$stdCl = new Standardize($response);
		//nesse caso $std irá conter uma representação em stdClass do XML
		$std = $stdCl->toStd();
		//nesse caso o $arr irá conter uma representação em array do XML
		//$arr = $stdCl->toArray();
		//nesse caso o $json irá conter uma representação em JSON do XML
		//$json = $stdCl->toJson();
	
			$cStat = $std->retEvento->infEvento->cStat;
			if ($cStat == '101' || $cStat == '155' || $cStat == '135' || $cStat == '573') {
				//SUCESSO PROTOCOLAR A SOLICITAÇÃO ANTES DE GUARDAR
				// $xml = Complements::toAuthorize($tools->lastRequest, $response);
				//grave o XML protocolado 

					
			$sql="update nf n JOIN fluxo f ON f.modulo = 'pedido' AND f.status = 'ATIVO' JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
			JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus AND s.statustipo = 'CANCELADO'
					set n.envionfe='CANCELADA', n.status= 'CANCELADO', n.idfluxostatus = fs.idfluxostatus	   			
					where n.idnf = ".$idnotafiscal;
			$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);
			
			/*$sqlFluxo = "SELECT fs.idfluxostatus, f.idfluxo,
								(SELECT idfluxostatushist FROM fluxostatushist fh WHERE fh.idmodulo = n.idnf AND fh.modulo = f.modulo 
							   ORDER BY idfluxostatushist DESC LIMIT 1) AS idfluxostatushist
						  FROM nf n
						  JOIN fluxo f ON f.modulo = 'pedido' AND f.status = 'ATIVO'
						  JOIN fluxostatus fs ON f.idfluxo = fs.idfluxo
						  JOIN "._DBCARBON."._status s ON fs.idstatus = s.idstatus AND s.statustipo = 'CANCELADO'
						  WHERE n.idnf = ".$idnotafiscal;
			$resFluxo = d::b()->query($sqlFluxo) or die(mysqli_error(d::b())." erro ao buscar o numero do recibo ".$sql);
			$rowFluxo = mysqli_fetch_assoc($resFluxo);*/
			//Atualiza o Fluxo
			
			$sql="delete l.* 
			from nfitem i,lotecons l 
			where l.idobjeto = i.idnfitem 
			and l.tipoobjeto='nfitem' 
			and i.idnf=".$idnotafiscal;
			$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);

			$sql="delete l.* 
			from nfitem i,lotereserva l 
			where l.idobjeto = i.idnfitem 
			and l.tipoobjeto='nfitem' 
			and i.idnf=".$idnotafiscal;
			$retx = d::b()->query($sql) or die("Erro ao atualizar nf sql:".$sql);


			$arrParcItens=getParcelaItens($idnotafiscal);
			$qtParcelasitem = $arrParcItens['quant'];
			$arrParcelas=recuperaParcelas($idnotafiscal,'QUITADO','nf');//Contapagar Quitado
			$qtParcelas  = $arrParcelas['quant'];
			
			if($qtParcelasitem==0 and $qtParcelas==0){
				deletaParcelasExistentes($idnotafiscal);		
				
					$resf=PedidoController::buscarNfitemPorIdobjetoTipoobjeto($idnotafiscal,'nf');
					$Vqtdnfitem=count($resf);
				   // echo("qtd ".$Vqtdnfitem);
					if($Vqtdnfitem>0){
						foreach($resf as $row){                    
							PedidoController:: deletarNfitemPorId($row['idnfitem']);						               
						}        
					} 
				
			}

			 echo $cStat.": Cancelado com Sucesso.";

			} else {

				$xmotivo = $std->retEvento->infEvento->xMotivo;
				//houve alguma falha no evento 
				//TRATAR			
			
				echo 'Codigo'.$cStat.' : ';
				echo $xmotivo;
				//echo htmlspecialchars($nfe->soapDebug);
				
			}
	  
	} else {
		die("Por favor, retirar os arquivos de Remessa, antes de cancelar a Nota Fiscal");
	}
} catch (\Exception $e) {
    echo $e->getMessage();
}
