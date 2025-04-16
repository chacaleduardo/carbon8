<?
//Criado para Listar o Calculo das Horas Extras dos Funcionários
//Lidiane (12/06/2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=325249
header("Content-Type: text/html;  charset=UTF-8",true);

require_once("../inc/php/validaacesso.php");
require_once("../model/recursoshumanos.php");
// CONTROLLERS
require_once(__DIR__."/../form/controllers/menurelatorio_controller.php");
require_once(__DIR__."/../form/controllers/empresa_controller.php");

// QUEYRS
require_once(__DIR__."/../form/querys/_iquery.php");
require_once(__DIR__."/../form/querys/sgdepartamento_query.php");
baseToGet($_GET["_filtros"]);

//Alterado para between a pedido de Nessi
$idpessoa			= $_GET["idpessoa"];
$idempresa			= $_GET["idempresa"];
$idsgdepartamento	= $_GET["idsgdepartamento"];
$nome				= $_GET["_fts"];	

$_idrep = $_GET["_idrep"];

if ($_GET["relatorio"]){
	$_idrep = $_GET["relatorio"];
}
?>

<html>
<head>
	<title><?= $_header ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />

	<link href="..\inc\css\carbon.css" rel="stylesheet">
	<link href="..\inc\css\bootstrap\css\bootstrap.css" rel="stylesheet">
	<link href="..\inc\css\fontawesome\font-awesome.min.css" rel="stylesheet">
	<script src="../inc/js/jquery/jquery-1.11.2.min.js"></script>
	<script src="../inc/css/bootstrap/js/bootstrap.min.js"></script>
	<script src="../inc/js/carbon.js"></script>
	<script src="../inc/js/functions.js"></script>
	<script src="../inc/js/notifications/smart.js"></script>
	<link href="../inc/js/notifications/smart.css" media="all" rel="stylesheet" type="text/css" />
	<style type="text/css">
		table {
			page-break-inside: auto
		}

		tr {
			page-break-inside: avoid;
			page-break-after: auto
		}

		thead {
			display: table-header-group
		}

		tfoot {
			display: table-footer-group
		}

		@media print {
			.noprint {
				display: none;
			}

			body {
				background: #fff;
			}

			a[href]:after {
				content: none !important;
			}
		}
	</style>
</head>

<body>

<?


if ($_REQUEST['_fds'])
{
	$data = explode('-',$_REQUEST['_fds']);
	$dataevento_1 = $data[0];
	$data1 = validadate($dataevento_1);
}
// CONSIDERAR DIA ATUAL PARA LISTAGEM
$data1 =date("Y-m-d");

$strin='';
if(!empty($idpessoa)){
	$strin=" and po.idpessoa in(".$idpessoa.") ";
}

$lps=getModsUsr("LPS");

// RETRINGIR CONSULTA A HIERARQUIA ORGANOGRAMA QUANDO MARCADO NA LPREP-------------------------------------------------------------
	$lpRep = MenuRelatorioController::buscarLpRepPorIdRepEIdLps(222, $lps, true);
	$_sqlinp='';
	if($lpRep)
	{ 
		foreach($lpRep as $r)
		{
			if($r['flgidpessoa']=='Y'){
				$strin .= getOrganogramaRep('po.idpessoa');
				break;
			}
		}
	} elseif(array_key_exists("STRCONTATOCLIENTE", $_SESSION["SESSAO"])){
		$strin .= " and po.idpessoa in( ".$_SESSION["SESSAO"]["STRCONTATOCLIENTE"].",".$_SESSION["SESSAO"]["IDPESSOA"].") ";
	}


if(!empty($idsgdepartamento))
{
	$sql = SQL::mount(SgDepartamentoQuery::buscarGroupConcatPessoasVinculadasAoSgDepartamentoPorIdSgDepartamentoEClausula(), [
		'idsgdepartamento' => $idsgdepartamento,
		'clausula' => $strin
	]);

}elseif(!empty($strin)){
	$sql = SQL::mount(PessoaQuery::buscarGroupConcatIdPessoasPorClausula(), [
		'clausula' => $strin
	]);
}else{
	die("Não encontrado departamento ou os funcionários para listagem");
}
echo("<!-- sqldepartamento:".$sql." -->");
$res = SQL::ini($sql)::exec(); 

if(!$res->data || !$res->data[0]['idpessoa'])
{
	die("Falha ao listar os funcionarios: ".mysqli_error(d::b())."\n".$sql);
}

foreach($res->data as $row)
{
	$res1 = PessoaController::buscarPessoasPorIdPessoa($row['idpessoa']);
	
	if(!$res1) die("Falha ao listar os funcionarios da lista");

	// while($rowp=mysqli_fetch_assoc($res1))
	foreach($res1 as $rowp)
	{
		$rct = PessoaController::buscarPessoaPorIdPessoa($rowp['idpessoa']);	
		$timestamp2 = strtotime($data1); 
		$mesfim= date('m', $timestamp2);
	   

		$histData1 = date("Y-m-d", mktime(0, 0, 0, $mesfim - 6, 1, date('Y', $timestamp2)));
		$histData2 = date("Y-m-d", mktime(23, 59, 59, $mesfim , date('d') - date('j'), date('Y', $timestamp2)));
		
		if(strtotime($rct['contratacao']) > strtotime($histData1)){
		   $histData1 = $rct['contratacao'];
		}
		$arrHist =array();
		for ($i = 0;; $i++) 
		{
			$s = "SELECT DATE_ADD('" . $histData1 . "', INTERVAL " . $i . " DAY) as diabusca,
			DATE_FORMAT( DATE_ADD('" . $histData1 . "', INTERVAL " . $i . " DAY),'%W') as semana,
				case  when DATE_ADD('" . $histData1 . "', INTERVAL " . $i . " DAY) > '" . $histData2 . "' then 'Y' 
				else 'N' end  as maior";
			$rw = SQL::ini($s)::exec()->data;


			if (!$rw || $rw[0]['maior'] == 'Y') break;

			$arrHist[$rct['idpessoa']][$rct['nome']][$rw[0]['diabusca']][]['semana'] = $rw[0]['semana'];
		}

		foreach ($arrHist as $idpessoa => $arrf) 
		{
			$totalh = 0;
			$totalhn = 0;
			$totalp = 0;
			$thextra = 0;
			$thextradin = 0;
			$dinhoraextra = 0;
			$tdiastrab = 0;

			foreach ($arrf as $nome => $arrdata) {
				foreach ($arrdata as $data => $arraponto) {
					$timestampdt = strtotime($data);
					$d = date("d", $timestampdt);
					$m = date("m", $timestampdt);
					$a = date("Y", $timestampdt);
					$arrmes[] = $m . "/" . $a;
				}
			}
		}

?>

<br>

                        <table class="normal" style="text-align: center; margin-bottom: 20px;">
						
						
                            <?
							$hecpx = MenuRelatorioController::buscarHorasExtrasPendentesAnteriores($rowp['idpessoa'], $data1);
                           
                            $sinal="";
                            if ($hecpx['valor'] < 0) 
							{
                                $sinal = "-";
                            }

							$alertcor='';

                            if($hecpx['valor'] !=0){
                                $alertcor='color:red;';
                            }
                            ?>
                            <tr style="height:20px " class="header">                                
                                <td  class="header" colspan="4" style=" text-align: left; ">                              
									<?=$rowp['nome']?>
								</td>
                            </tr>
                            <tr style="height:20px " class="header">                              
                                <td class="header" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Mês</td>
                                <td class="header" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Realizadas (+)</td>                               
                                <td class="header" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Compensadas (-)</td>
								<td class="header" style="color: rgb(75,75,75); text-align: center; background-color: rgb(222,222,222);">Acumuladas</td>
                            </tr>
							<tr >
                              
							  <td>Anterior<?//=$hecpx['periodo']?></td>
							  <td> -- </td>                               
							  <td> -- </td>
							  <td><?=$sinal?><?=convertHoras(abs($hecpx['valor']))?></td>
						  </tr>

                        <?                       
                        
                        $armes = array_unique($arrmes);
                        $vhecp=$hecpx['valor'];
                        $vhec=0;
                        $vhece=0;
                            foreach ($armes as $key => $value) {
                                $ms = explode("/", $value);
                     

                                 $mesQuit=$ms['1']."-".$ms['0'];
                                
								$hecp = MenuRelatorioController::buscarHorasPendentesPorDataEventoEIdPessoaEStatus($rowp['idpessoa'], $mesQuit,"'PENDENTE'");
								$hec = MenuRelatorioController::buscarHorasPendentesPorDataEventoEIdPessoaEStatus($rowp['idpessoa'], $mesQuit,"'QUITADO TRANSFERENCIA','QUITADO'");

								$hece = MenuRelatorioController::buscarHorasExtras($rowp['idpessoa'], $mesQuit);

								if(empty($hece['valor'])){$hece['valor']=0;}
								if(empty($hecp['valor'])){$hecp['valor']=0;}
								if(empty($hec['valor'])){$hec['valor']=0;}
								
								$vhecp=$vhecp+$hecp['valor'];
								$vhec= $vhec+$hec['valor'];
								$vhece=$vhece+$hece['valor'];


								$snce="";
								if ($hece['valor'] < 0) {
									$vvhece = $hece['valor'] * -1;
									$snce = "-";
								}else{
									$vvhece = $hece['valor'];
								}

								$sncp="";
								if ($hecp['valor'] < 0) {
									$vvhecp = $hecp['valor'] * -1;
									$sncp = "-";
								}else{
									$vvhecp = $hecp['valor'];
								}

								$snc="";
								if ($hec['valor'] < 0) {
									$vvhec = $hec['valor'] * -1;
									$snc = "-";
								}else{
									$vvhec = $hec['valor'];
								}
                                ?>
                                <tr >
                                    <td  class="tbl"><?= $value ?></td>
                                    <td  class="tbl"> <?= $snce.convertHoras($vvhece) ?> </td>                                    
                                    <td class="tbl" ><?= $snc.convertHoras($vvhec) ?></td>
									<td  class="tbl"> <?= $sncp.convertHoras($vvhecp) ?> </td>
                                </tr>
                            
                            <? } 
                            
                            $sne="";
                            if ($vhece < 0) {
                                $vhece = $vhece * -1;
                                $sne = "-";
                            }

                            $sn="";
                            if ($vhec < 0) {
                                $vhec = $vhec * -1;
                                $sn = "-";
                            }
                            
                            $snp="";
                            if ($vhecp < 0) {
                                $vhecp = $vhecp * -1;
                                $snp = "-";
                            }

                            

                            ?>
                                <tr style="height:20px " class="header">
                                   
                                    <td  class="header" style="text-align: center;"><strong>Total</strong></td>
                                    <td  class="header"><strong> <?= $sne.convertHoras($vhece) ?> </strong></td>                                   
                                    <td  class="header"><strong> <?= $sn.convertHoras($vhec) ?> </strong></td>
									<td  class="header"><strong> <?= $snp.convertHoras($vhecp) ?> </strong></td>
                                </tr>
                        </table>
						<p>
						<br>    
<?
	}
}
?>
</body>
</html>