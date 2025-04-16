<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
   require_once("../inc/php/cbpost.php");
}

$unidadepadrao = getUnidadePadraoModulo($_GET["_modulo"], $_idempresa);

//Parâmetros mandatórios para o carbon
$pagvaltabela = "resultado";
$pagvalcampos = array(
   "idresultado" => "pk"
);


//RECEBER O NOME DO ALERTA SETADO NA PRODSERV

$sql = "SELECT p.alertarotulo, p.alertarotuloy, p.alertarotulon  FROM resultado r join prodserv p on r.idtipoteste = p.idprodserv WHERE idresultado = '" . $_GET['idresultado'] . "';";

$res = mysql_query($sql) or die("A Consulta da prodserv falhou : " . mysql_error() . "<p>SQL: $sql");
$row = mysql_fetch_array($res);
$nomealerta      = $row["alertarotulo"];
$alertarotuloy   = $row["alertarotuloy"];
$alertarotulon   = $row["alertarotulon"];
$idprodserv 	 = $row["idprodserv"];

//RECEBER OS SERVIÇOS VINCULADOS AO ALERTA SETADO NA PRODSERV

$sql = "SELECT pv.idobjeto  
		FROM resultado r
		JOIN prodservvinculo pv on pv.idprodserv = r.idtipoteste
		WHERE tipoobjeto='prodserv' 
		and pv.alerta='Y' 
		and idresultado=" . $_GET['idresultado'] . ";";
	echo '<!-- vinculados: ' . $sql .'-->';
$res = mysql_query($sql) or die("A Consulta da prodserv falhou : " . mysql_error() . "<p>SQL: $sql");
$vinculados = array();
while($row = mysql_fetch_array($res)) {
	array_push($vinculados,$row['idobjeto']);
}
$vinculados = json_encode($vinculados);
echo '<!-- vinculados: ' . $vinculados .'-->';

function criaCamposTabelaAntigo($campo)
{
   $fields = "";

   switch ($campo->tipo) {
	  case 'numerico':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="number">';
		 break;
	  case 'input':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="text">';
		 break;
	  case 'data':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="date">';
		 break;
	  case 'hora':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="time">';
		 break;
	  case 'selecionavel':
		 $compl = '';
		 foreach ($campo->options as $k) {
			$compl .= '<option value="' . $k->nome . '">' . $k->nome . '</option>';
		 }
		 // $teste = str_replace('"',"'",fillselect($str, 'Sim'));
		 $fields .= '<select name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"><option value=""></option>' . $compl . '</select>';
		 break;
	  case 'fixo':
		 $compl = '';
		 foreach ($campo->options as $k) {
			$compl .= '<option value="' . $k->nome . '">' . $k->nome . '</option>';
		 }
		 // $teste = str_replace('"',"'",fillselect($str, 'Sim'));
		 $fields .= '<select name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"><option value=""></option>' . $compl . '</select>';
		 break;
	  case 'checkbox':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" type="checkbox" >';
		 break;
	  case 'textarea':
		 $fields .= '<textarea name="campo_' . $campo->indice . '" data-indice="" data-tipo="editorhtml" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" class="hide"></textarea>
						<div name="campoeditor_' . $campo->indice . '" data-tipo="divhtml" class="diveditor" onkeypress="pageStateChanged=true;" style="text-align: left;width:auto; height:66px; margin-right: 8px; border: none;"></div>';
		 break;
   }

   return ($fields);
}

function criaCamposTabela($campo)
{
   // echo"<script>console.log(".$campo.")</script>";
   $fields = "";
   if ($campo->calculo ==  "SIM") {
	  $calculo = 'data-calculo="SIM"';
   } else {
	  $calculo = 'data-calculo="NAO"';
   }
   switch ($campo->tipo) {
	  case 'numerico':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="number" ' . $calculo . '>';
		 break;
	  case 'identificador':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" value="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="text" ' . $calculo . '>';
		 break;
	  case 'input':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="text" ' . $calculo . '>';
		 break;
	  case 'data':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="date" ' . $calculo . '>';
		 break;
	  case 'hora':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="time" ' . $calculo . '>';
		 break;
	  case 'selecionavel':
		 $compl = '';
		 foreach ($campo->options as $k) {
			$compl .= '<option calcula="' . $k->calculo . '" value="' . $k->nome . '" class="aplicar' . $k->indice . '">' . $k->nome . '</option>';
		 }
		 // $teste = str_replace('"',"'",fillselect($str, 'Sim'));
		 $fields .= '<select name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '" ' . $calculo . ' ><option value="" class="aplicarvazio' . $campo->indice . '"></option>' . $compl . '</select>';
		 break;
	  case 'fixo':
		 $compl = '';
		 foreach ($campo->options as $k) {
			$compl .= '<option value="' . $k->nome . '" class="aplicar' . $k->indice . '">' . $k->nome . '</option>';
		 }
		 // $teste = str_replace('"',"'",fillselect($str, 'Sim'));
		 $fields .= '<select name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  ' . $calculo . ' ><option value="" class="aplicarvazio' . $campo->indice . '"></option>' . $compl . '</select>';
		 break;
	  case 'checkbox':
		 $fields .= '<input name="campo_' . $campo->indice . '" data-indice="" data-tipo="' . $campo->tipo . '" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  type="checkbox" ' . $calculo . ' >';
		 break;
	  case 'textarea':
		 $fields .= '<textarea name="campo_' . $campo->indice . '" data-indice="" data-tipo="editorhtml" data-titulo="' . $campo->titulo . '" data-vinculo="' . $campo->vinculo . '"  class="hide" ' . $calculo . ' ></textarea>
		 <div name="campoeditor_' . $campo->indice . '" data-tipo="divhtml" class="diveditor" onkeypress="pageStateChanged=true;" style="text-align: left;width:auto; height:66px; margin-right: 8px; border: none;"></div>';
		 break;
   }

   return ($fields);
}

function listaIdentificadores()
{
   $sqlIdentificador = "select
   id.identificacao,
   id.ididentificador
   from amostra as a 
   join identificador id on (id.idobjeto = a.idamostra)
   where a.idamostra = " . $_1_u_resultado_idamostra;
   //echo "<pre>".$sqlUnidadeEspecie."<pre>";
   $result = mysql_query($sqlIdentificador);

   while ($rowId = mysql_fetch_assoc($result)) {
	  $identificacao = $rowId['identificacao'];
   }
}

function listaTestes()
{

   global $_1_u_resultado_idamostra, $_1_u_resultado_idresultado, $_1_u_resultado_idprodserv;

   $sqlt = "SELECT
			r.idresultado,
			r.idtipoteste,
			t.tipoteste,
			t.sigla,
			r.quantidade quant,
			r.status,
			t.tipogmt,
			r.criadopor,
			dmahms(r.criadoem),
			r.alteradopor,
			if(s.dia is null,'',concat(' - D',s.dia)) as rotulo,
			dmahms(r.alteradoem) alteradoem,
			se.nome as secretaria,
			a.idpessoa,
			pv.idprodserv,
			p.codprodserv,
			case 
				when (select count(1) from prodservvinculo pv where pv.idobjeto =  t.idtipoteste) > 0 then 'VINCULADO'
				when (select count(1) from prodservvinculo pv where pv.idprodserv =  t.idtipoteste) > 0 then 'VINCULO'
				else ''
			end as 'vinculo',
   			ifnull(pv.idprodserv,t.idtipoteste) as ordem
		FROM
			resultado r join vwtipoteste t on (r.idtipoteste = t.idtipoteste)
			left join servicoensaio s on (r.idservicoensaio=s.idservicoensaio)
			left join pessoa se on (se.idpessoa = r.idsecretaria)
			join amostra a on a.idamostra = r.idamostra
			left join prodservvinculo pv on pv.idobjeto =  t.idtipoteste and pv.tipoobjeto='prodserv'
			left join prodserv p on p.idprodserv = pv.idprodserv
		WHERE
			r.idamostra = " . $_1_u_resultado_idamostra . "
			and r.status !='OFFLINE'
			group by r.idresultado
			order by ordem desc
			";

   //die($sqlt);
   $rest = d::b()->query($sqlt);
   echo '<div id="tbTestes">';
   if (!$rest) die("Falha consultando testes: " . mysqli_error(d::b()) . "<p>SQL: " . $sqlt);

   while ($r = mysqli_fetch_assoc($rest)) {
	  $secretaria = $r["idpessoa"];
	  $oficial = empty($r["secretaria"]) ? "naooficial" : "oficial";
	  $testeativo = ($_1_u_resultado_idresultado == $r["idresultado"]) ? "ativo shadowRightGray" : "inativo";
?>
	  <div class="oTeste <?= $testeativo ?>" cbstatus="<?= $r["status"] ?>" onclick="CB.go('idresultado=<?= $r["idresultado"] ?>')">
		 <table>
			<tr>
			   <td class="sigla"><?= $r["sigla"] ?></td>
			   <td class="quant"><span><?= $r["quant"] ?></span></td>
			</tr>
			<tr class="testerotulo">
			   <td class="tipoteste"><?= $r["tipoteste"] . $r["rotulo"] ?></td>
			   <td><span class="<?= $oficial ?>"><i class="fa fa-user-secret"></i></span></td>
			</tr>
		 </table>
	  </div>
	  <div class="webui-popover-content">
		 <table>
			<tr>
			   <td>Teste:</td>
			   <td class="nowrap"><?= $r["tipoteste"] ?></td>
			</tr>
			<tr>
			   <td>Quant.:</td>
			   <td><?= $r["quant"] ?></td>
			</tr>
			<?
			if ($r["secretaria"]) {
			?>
			   <tr>
				  <td class="nowrap"><i class="fa fa-user-secret"></i>&nbsp;Oficial:</td>
				  <td class="nowrap"><?= $r["secretaria"] ?></td>
			   </tr>
			<?
			}
			?>
		 </table>
		 <?
		 //LTM (12/02021): Alterado a Busca, retirando da _auditoria, para não pesar nesta tabela
		 $sqla = "SELECT upper(s.rotulo) AS valor, 
					   fh.criadopor, 
					   fh.criadoem, 
					   p.assinateste 
				 FROM fluxostatushist fh JOIN fluxostatus fs ON fh.idfluxostatus = fs.idfluxostatus
				 JOIN " . _DBCARBON . "._status s ON fs.idstatus = s.idstatus
				 JOIN pessoa p ON fh.criadopor = p.usuario
				   WHERE modulo = '" . $_GET["_modulo"] . "' AND idmodulo =" . $r["idresultado"] . "
			 ORDER BY fh.criadoem ASC";
		 $resa = d::b()->query($sqla);
		 $qtda = mysqli_num_rows($resa);
		 if ($qtda > 0) {

		 ?>
			<HR>
			<table style="font-size: 10px">
			   <tr>
				  <td>STATUS</td>
				  <td>ALTERADO POR</td>
				  <td>ALTERADO EM</td>
			   </tr>
			   <?
			   while ($ra = mysqli_fetch_assoc($resa)) {
				  if (($ra['valor'] == 'ASSINADO' and $ra['assinateste'] == 'Y') or $ra['valor'] != 'ASSINADO') {
			   ?>
					 <tr>
						<td><?= $ra['valor'] ?></td>
						<td>
						   <? //Alterado até colocar a assinatura digital 
						   ?>
						   <? if ($ra['criadopor'] == 'leandrocardoso' || $ra['criadopor'] == 'danielhenrique' || $ra['criadopor'] == ' 	ana_fernandes 	' || $ra['criadopor'] == 'lidianemelo') {
							  echo 'edison';
						   } else { ?>
							  <?= $ra['criadopor'] ?>
						   <? } ?>
						</td>
						<td><?= dmahms($ra['criadoem']) ?></td>
					 </tr>
			   <?
				  }
			   }
			   ?>
			</table>
		 <?
		 }
		 ?>
	  </div>
   <?
   }
   echo '</div>';
   ?>
   <i class="fa fa-plus-circle fa-2x verde pointer" onclick="novoTeste()" alt="Inserir novo teste"></i>
   <button id="novoTestesalvar" type="button" class="btn btn-danger btn-xs" onclick="adicionarTestes();" title="Adicionar Teste(s)" style="display:none">
	  <i class="fa fa-circle"></i>Adicionar Teste(s)
   </button>
   <div id="modeloNovoTeste" class="hidden">
	  <div class="oTeste novo">
		 <table>
			<tr>
			   <td class="tipoteste" colspan="2"><input class="formTmp" type="hidden" name="#nameidamostra" value="<?= $_1_u_resultado_idamostra ?>">
				  <input class="formTmp" type="hidden" name="#namestatus" value="ABERTO">
				  <input class="formTmp idprodserv" type="text" name="#nameidtipoteste" cbvalue placeholder="INFORME O TESTE" vnulo style="font-size:10px">
			   </td>
			</tr>
			<tr class="testerotulo">
			   <td class="quant"><span><input id="qtdteste" type="text" class="formTmp" name="#namequantidade" style="width:50px;font-size:10px" placeholder="QTD." vnulo vnumero></span></td>
			   <td>
				  <select class="formTmp" name="#nameidsecretaria" style="font-size:10px;" placeholder="Secretaria">
					 <option value="0"></option>
					 <?
					 $strs = "select pp.idpessoa,pp.nome
					 from pessoa p,pessoa pp
					 where pp.idpessoa = p.idsecretaria
						" . getidempresa('pp.idempresa', 'pessoa') . "
						and p.idpessoa =" . $secretaria;

					 fillselect($strs, $r["idsecretaria"]);
					 ?>
				  </select>
			   </td>
			</tr>
		 </table>
	  </div>
   </div>
<?
}


function confPressionamentoTeclas()
{

   $sqlkp = "select * from gmtkeypress";

   $reskp = d::b()->query($sqlkp) or die("A Consulta de configuração de teclas falhou : " . mysql_error() . "<p>SQL: $sql");
   $row = mysqli_fetch_assoc($reskp);

   echo "arrKeyConf[" . $row["x1"] . "] = 1;\n";
   echo "arrKeyConf[" . $row["x2"] . "] = 2;\n";
   echo "arrKeyConf[" . $row["x3"] . "] = 3;\n";
   echo "arrKeyConf[" . $row["x4"] . "] = 4;\n";
   echo "arrKeyConf[" . $row["x5"] . "] = 5;\n";
   echo "arrKeyConf[" . $row["x6"] . "] = 6;\n";
   echo "arrKeyConf[" . $row["x7"] . "] = 7;\n";
   echo "arrKeyConf[" . $row["x8"] . "] = 8;\n";
   echo "arrKeyConf[" . $row["x9"] . "] = 9;\n";
   echo "arrKeyConf[" . $row["x10"] . "] = 10;\n";
   echo "arrKeyConf[" . $row["x11"] . "] = 11;\n";
   echo "arrKeyConf[" . $row["x12"] . "] = 12;\n";
   echo "arrKeyConf[" . $row["x13"] . "] = 13;\n";
}

/*
   * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
   */
$pagsql = "SELECT
   		l.idresultado,
		 l.idempresa,
		   l.versao,
   		l.alerta,
   		l.tipoalerta,
   		l.idamostra,
   		l.idtipoteste,
   		l.quantidade,
   		l.status,
   		l.criadopor,
   		l.criadoem,
   		l.alteradopor,
   		l.alteradoem,
   		l.descritivo,
   		l.q1,
   		l.q2,
   		l.q3,
   		l.q4,
   		l.q5,
   		l.q6,
   		l.q7,
   		l.q8,
   		l.q9,
   		l.q10,
   		l.q11,
   		l.q12,
   		l.q13,
   		l.idt,
   		l.gmt,
   		l.padrao,
   		l.var,
   		t.tipoteste,
   		t.sigla,
   		t.tipogmt,
   		t.tipobact,
   		l.positividade,
   		t.tipoespecial,
   		t.tiporelatorio,
   		l.idtecnico,
		l.jsonresultado,
		l.jsonconfig
   	FROM
   		resultado l
	JOIN
		vwtipoteste t ON l.idtipoteste = t.idtipoteste
   	WHERE
   		-- l.idempresa = " . $_SESSION["SESSAO"]["IDEMPRESA"] . " AND
   		 l.idresultado = #pkid
   	ORDER BY
   		t.tipoteste";

/*
   * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
   */
require_once("../inc/php/controlevariaveisgetpost.php");


$sam = "select
   		a.idamostra,
		 a.idempresa,
   		a.idregistro,
   		a.idunidade,
   		a.dataamostra,
   		ifnull(p.nomecurto, p.nome) as nome,
   		p.idpessoa,
   		sta.subtipoamostra,
   		a.idade,
   		a.tipoidade,
   		a.exercicio
   	from
   		amostra a
	left join
   		pessoa p on a.idpessoa = p.idpessoa
   	left join subtipoamostra sta on  a.idsubtipoamostra = sta.idsubtipoamostra
   	where
   		a.idamostra = " . $_1_u_resultado_idamostra;

$resam = d::b()->query($sam) or die("Falha consultando amostra: " . mysqli_error() . "<p>SQL: " . $sam);
$ram = mysqli_fetch_assoc($resam);

$modamostra = getModuloAmostraPadrao($ram["idunidade"]);




?>
<style>
   .ui-autocomplete {
	  width: 500px !important;
	  font-size: 9px;
   }
   .striped>.row:nth-of-type(odd) {
		background-color: white;
	}
</style>
<div class="col-md-12">
   <div class="panel panel-default">
	  <div class="panel-body">
		 <table id="amostra" style="width: 100%;">
			<tr>
			   <td><strong>Registro:</strong></td>
			   <td id="cabRegistro" cbidamostra="<?= $ram["idamostra"] ?>">
				  <label class='alert-warning'><?= $ram["idregistro"] ?>
					 <a title="Abrir Amostra" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=<?= $modamostra ?>&idamostra=<?= $ram["idamostra"] ?>&_idempresa=<?= $ram['idempresa'] ?>" target="_blank"></a>
				  </label>
			   </td>
			   <td><strong>ID Teste:</strong></td>
			   <td>
				  <label class='alert-warning'>
					 <?= $_1_u_resultado_idresultado ?>.<?= $_1_u_resultado_versao ?>
				  </label>
			   </td>
			   <td style="width: 30px;">Cliente:</td>
			   <td class="inputreadonly" nowrap style="padding:6px;"><?= $ram["nome"] ?></td>
			   <td>Amostra:</td>
			   <td class="inputreadonly" style="padding:6px;"><?= $ram["subtipoamostra"] ?></td>
			   <td>
				  <span>
					 <? $rotulo = getStatusFluxo($pagvaltabela, 'idresultado', $_1_u_resultado_idresultado) ?>
					 <label title="<?= $rotulo['status'] ?>" class="alert-warning" id="statusButton"><?= mb_strtoupper($rotulo['rotulo'], 'UTF-8') ?></label>
				  </span>
			   </td>
			   <td>
				  <? //Alterado o link para igual da Amostra "De: report/impclienteamostra.php?acao=u&idamostra - Para o atual" - Lidiane (10-06-2020) - sislaudo.laudolab.com.br/?_modulo=evento&_acao=u&idevento=321965
				  ?>
				  <a title="Imprimir Cliente Amostra." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/amostra.php?acao=i&idamostra=<?= $ram["idamostra"] ?>')"></a>
			   </td>
			   <?
			   $sqlemail = "SELECT 
							  m.idmailfila
						   FROM
							  mailfila m
								 JOIN
							  comunicacaoextitem c ON (c.idcomunicacaoext = m.idobjeto)
						   WHERE
							  m.tipoobjeto = 'comunicacaoext'
								 AND c.idobjeto = " . $_1_u_resultado_idresultado . "
								 AND c.tipoobjeto = 'resultado'
								 " . getidempresa('m.idempresa', 'envioemail') . "
						   ORDER BY
							  idmailfila DESC LIMIT 1";
			   $resemail = d::b()->query($sqlemail) or die("Falha na consulta do email: " . mysqli_error() . "<p>SQL: " . $sqlemail);
			   $rowemail = mysqli_fetch_assoc($resemail);
			   $numemail = mysqli_num_rows($resemail);
			   if ($numemail > 0) { ?>
				  <td>
					 <a class="pull-right" title="Ver emails enviados" onclick="janelamodal('?_modulo=envioemail&_acao=u&idmailfila=<?= $rowemail['idmailfila'] ?>')"><i class="fa fa-envelope-o cinza pointer"></i><i style="z-index: 2300;margin-left:-5px;margin-top:-7px;" class="fa fa-search cinza cinza pointer"></i></a>
				  </td>
			   <? } ?>
			</tr>
		 </table>
	  </div>
   </div>
</div>
<div class="col-md-2">
   <?
   listaTestes();
   ?>
</div>
<div class="col-md-10">
   <div class="panel panel-default">
	  <div class="panel-body">
		 <style>
			.diveditor {
			   border: 1px solid gray;
			   background-color: white;
			   color: black;
			   font-family: Arial, Verdana, sans-serif;
			   font-size: 10pt;
			   font-weight: normal;
			   width: 800px;
			   height: 256px;
			   word-wrap: break-word;
			   overflow: auto;
			   padding: 5px;
			}
		 </style>
		 <?
		 //die($_1_u_resultado_idresultado);
		 if (!empty($_1_u_resultado_idresultado)) {
			$sql = "SELECT
				l.idresultado,
				l.versao,
				l.alerta,
				l.tipoalerta,
				l.idamostra,
				l.idtipoteste,
				l.quantidade,
				l.status,
				l.criadopor,
				dmahms(l.criadoem) criadoem,
				l.alteradopor,
				dmahms(l.alteradoem) alteradoem,
				l.descritivo,
			 l.observacao,
				l.q1,
				l.q2,
				l.q3,
				l.q4,
				l.q5,
				l.q6,
				l.q7,
				l.q8,
				l.q9,
				l.q10,
				l.q11,
				l.q12,
				l.q13,
				l.idt,
				l.gmt,
				l.padrao,
				l.var,
				t.tipoteste,
				t.sigla,
				t.tipogmt,
				t.tipobact,
				l.positividade,
				t.tipoespecial,
				t.tiporelatorio,
				l.idtecnico,
				l.conformidade,
				l.resultadocertanalise,
				l.idservicoensaio,
				p.modelo,
				p.modo,
				p.geraagente,
				p.tipogmt,
				l.tipokit,
				l.jsonresultado,
				l.jsonconfig
						
					FROM
				resultado l
					JOIN
				vwtipoteste t ON l.idtipoteste = t.idtipoteste
					LEFT JOIN
				prodserv p ON l.idtipoteste = p.idprodserv
						
					WHERE
						-- l.idempresa = " . $_SESSION["SESSAO"]["IDEMPRESA"] . " AND
						 l.idresultado = " . $_1_u_resultado_idresultado . "
					ORDER BY
						t.tipoteste";
			//echo $sql;

			$res = mysql_query($sql) or die("A Consulta falhou : " . mysql_error() . "<p>SQL: $sql");
			$row = mysql_fetch_array($res);
			$_1_u_resultado_idresultado   = $row["idresultado"];
			$_1_u_resultado_versao      = $row["versao"];
			$_1_u_resultado_alerta      = $row["alerta"];
			$_1_u_resultado_tipoalerta   = $row["tipoalerta"];
			$_1_u_resultado_descritivo   = $row["descritivo"];
			$_1_u_resultado_observacao = $row['observacao'];
			$_1_u_resultado_q1      = $row["q1"];
			$_1_u_resultado_q2      = $row["q2"];
			$_1_u_resultado_q3      = $row["q3"];
			$_1_u_resultado_q4      = $row["q4"];
			$_1_u_resultado_q5      = $row["q5"];
			$_1_u_resultado_q6      = $row["q6"];
			$_1_u_resultado_q7      = $row["q7"];
			$_1_u_resultado_q8      = $row["q8"];
			$_1_u_resultado_q9      = $row["q9"];
			$_1_u_resultado_q10      = $row["q10"];
			$_1_u_resultado_q11      = $row["q11"];
			$_1_u_resultado_q12      = $row["q12"];
			$_1_u_resultado_q13      = $row["q13"];
			$_1_u_resultado_gmt      = $row["gmt"];
			$_1_u_resultado_idt      = $row["idt"];
			$_1_u_resultado_var      = $row["var"];
			$_1_u_resultado_tipoteste   = $row["tipoteste"];
			$_1_u_resultado_tipogmt      = $row["tipogmt"];
			$_1_u_resultado_tipobact   = $row["tipobact"];
			$_1_u_resultado_criadopor   = $row["criadopor"];
			$_1_u_resultado_criadoem   = $row["criadoem"];
			$_1_u_resultado_alteradopor   = $row["alteradopor"];
			$_1_u_resultado_alteradoem   = $row["alteradoem"];
			$_1_u_resultado_positividade   = $row["positividade"];
			$_1_u_resultado_tipoespecial   = $row["tipoespecial"];
			$_1_u_resultado_quantidade   = $row["quantidade"];
			$_1_u_resultado_tiporelatorio   = $row["tiporelatorio"];
			$_1_u_resultado_idtipoteste   = $row["idtipoteste"];
			$_1_u_resultado_idtecnico   = $row["idtecnico"];
			$_1_u_resultado_conformidade   = $row["conformidade"];
			$_1_u_resultado_resultadocertanalise = $row["resultadocertanalise"];
			$_1_u_resultado_idservicoensaio   = $row["idservicoensaio"];
			$_1_u_resultado_modelo      = $row["modelo"];
			$_1_u_resultado_modo      = $row["modo"];
			$_1_u_resultado_tipogmt      = $row["tipogmt"];
			$_1_u_resultado_tipokit            = $row["tipokit"];
			$tipogmt                     = $row["titulo"];
			$qtx                        = $row["quantidade"];
			$_1_u_resultado_jsonresultado      = $row["jsonresultado"];
			$_1_u_resultado_jsonconfig         = $row["jsonconfig"];
			$geraagente = $row["geraagente"];

			$sql = "SELECT (valor*1) as valor FROM prodservtipoopcao where -- idempresa =" . $_SESSION["SESSAO"]["IDEMPRESA"] . " and 
						 idprodserv = '" . $_1_u_resultado_idtipoteste . "' order by valor*1";

			$res = mysql_query($sql) or die("A Consulta falhou : " . mysql_error() . "<p>SQL: $sql");

			$i = 1;
			while ($row = mysql_fetch_assoc($res)) {
			   $x[$i] = $row["valor"];
			   $i++;
			}

			$somaor = $_1_u_resultado_q1 + $_1_u_resultado_q2 + $_1_u_resultado_q3 + $_1_u_resultado_q4 + $_1_u_resultado_q5 + $_1_u_resultado_q6 + $_1_u_resultado_q7 + $_1_u_resultado_q8 + $_1_u_resultado_q9 + $_1_u_resultado_q10 + $_1_u_resultado_q11 + $_1_u_resultado_q12 + $_1_u_resultado_q13;


			/*****************************************/
			/*SE MODO FOR INDIVIDUAL [INICIO]
		/*****************************************/
			if ($_1_u_resultado_modo == "IND"  && $_1_u_resultado_modelo != 'DINAMICO') {

			   $sqlind = "select * from resultadoindividual i where i.idresultado=" . $_1_u_resultado_idresultado;
			   $resind = mysql_query($sqlind) or die(" Erro ao buscar od resultados individuais:" . mysql_error . " <p>SQL" . $sqlind);
			   $qtdind = mysql_num_rows($resind);
			   if ($qtdind == 0 and $_1_u_resultado_quantidade > 0) { //inserir quando não tiver orificio para aquele resultado

				  //se for de um teste do bioterio verifica se existe numeração
				  if (!empty($_1_u_resultado_idservicoensaio)) {
					 $sqlindx = "select i.* from identificador i,servicoensaio s where  i.tipoobjeto='bioensaio' and i.idobjeto=s.idobjeto and s.tipoobjeto='bioensaio' and s.idservicoensaio=" . $_1_u_resultado_idservicoensaio . " order by identificacao";

					 $resindx = mysql_query($sqlindx) or die(" Erro ao buscar identificacao:" . mysql_error . " <p>SQL" . $sqlindx);
					 $qtdindx = mysql_num_rows($resindx);
				  } else {
					 //$qtdindx=0;
					 $sqlindx = "select i.* from identificador i where i.idobjeto=" . $_1_u_resultado_idamostra . " and i.tipoobjeto='amostra' order by ididentificador";
					 $resindx = mysql_query($sqlindx) or die(" Erro ao buscar identificacao:" . mysql_error . " <p>SQL" . $sqlindx);
					 $qtdindx = mysql_num_rows($resindx);
				  }
				  if ($qtdindx > 0) {
					 while ($rowindx = mysql_fetch_assoc($resindx)) {
						$sqlin = "insert into resultadoindividual (
										idempresa,
										idresultado,
										identificacao,												
										criadopor,
										criadoem,
										alteradopor,
										alteradoem)
										values(
										" . $_SESSION["SESSAO"]["IDEMPRESA"] . "
										," . $_1_u_resultado_idresultado . "
										,'" . $rowindx['identificacao'] . "'												
										,'" . $_SESSION["SESSAO"]["USUARIO"] . "'
										,now()
										,'" . $_SESSION["SESSAO"]["USUARIO"] . "'
										,now()
										)";
						mysql_query($sqlin) or die("A insercão dos individuos no resultado falhou : " . mysql_error() . "<p>SQL:" . $sqlin);
					 }
				  } else {

					 for ($z = 1; $z <= $_1_u_resultado_quantidade; $z++) {
						$sqlin = "insert into resultadoindividual (
										idempresa,
										idresultado,								
										criadopor,
										criadoem,
										alteradopor,
										alteradoem) 
										values(
										" . $_SESSION["SESSAO"]["IDEMPRESA"] . "
										," . $_1_u_resultado_idresultado . "								
										,'" . $_SESSION["SESSAO"]["USUARIO"] . "' 
										,now()
										,'" . $_SESSION["SESSAO"]["USUARIO"] . "' 
										,now()
										)";
						mysql_query($sqlin) or die("A insercão dos individuos no resultado falhou : " . mysql_error() . "<p>SQL:" . $sqlin);
					 }
				  }
			   } elseif ($qtdind > $_1_u_resultado_quantidade) { // excluir quando a quantidade de orificios for maior que a quantidade do resultado

				  $dif = $qtdind - $_1_u_resultado_quantidade;
				  for ($d = 1; $d <= $dif; $d++) {
					 $sqld = "delete from resultadoindividual 
				where idresultado=" . $_1_u_resultado_idresultado . " order by idresultadoindividual desc limit 1";
					 mysql_query($sqld) or die("Erro ao deletar orificios a mais: " . mysql_error() . "<p>SQL:" . $sqld);
				  }
			   } elseif ($qtdind < $_1_u_resultado_quantidade) { //inserir quando a quantidade de orificios for inferior a quantidade do resultado

				  $dif = $_1_u_resultado_quantidade - $qtdind;
				  for ($d = 1; $d <= $dif; $d++) {
					 $sqlin1 = "insert into resultadoindividual (
									idempresa,
									idresultado,
									criadopor,
									criadoem,
									alteradopor,
									alteradoem)
									values(
									" . $_SESSION["SESSAO"]["IDEMPRESA"] . "
									," . $_1_u_resultado_idresultado . "
									,'" . $_SESSION["SESSAO"]["USUARIO"] . "'
									,now()
									,'" . $_SESSION["SESSAO"]["USUARIO"] . "'
									,now()
									)";
					 mysql_query($sqlin1) or die("A insercão dos individuos faltantes no resultado falhou : " . mysql_error() . "<p>SQL:" . $sqlin1);
				  }
			   }
			}

			/*****************************************/
			/*SE MODO FOR INDIVIDUAL [FIM]
		/*****************************************/

		 ?>
			<h4 class='nowrap'><span class="cinza">Resultado para </span>
			   <span class="negrito"><?= $_1_u_resultado_tipoteste ?>
				  	<a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&_acao=u&idprodserv=<?= $_1_u_resultado_idtipoteste ?>" target="_blank"></a>
				  	<a style="float:right" title="Visualizar Impressão" class="fa fa-print fade pointer hoverazul" href="/report/emissaoresultado.php?idresultado=<?= $_1_u_resultado_idresultado ?>" target="_blank"></a>
				  	<?if($_1_u_resultado_jsonresultado =="" && $_1_u_resultado_modelo == "DINÂMICO"){ ?>
						<i class="fa fa-exclamation-triangle fa-1x laranja pointer" title="Resultados ainda não foram salvos, favor salvar a página para que os dados apareçam no relatório."></i>
					<? } ?>	
			   </span>
			</h4>
			<hr>
			<input type="hidden" name="_1_u_resultado_idresultado" value="<?= $_1_u_resultado_idresultado ?>" id="idresultado">

			<?

			/*****************************************/
			/*SE MODO FOR SELETIVO AGRUPADO [INICIO]
		/*****************************************/
			if ($_1_u_resultado_modelo == "SELETIVO" && $_1_u_resultado_modo == "AGRUP") {
			   //Se for tipo GMT
			   $keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
			?>
			   <br>
			   <div class="row row-eq-height">
				  <div class="col-md-2">
					 <div class="row" style="margin: 3px;border: 1px solid #ccc;font-size: 10px;  background-color: #ccc;">
						<div class="col-md-12">
						   <font class="graybold bold">Selecionar Ação:</font>
						</div>
						<div class="col-md-12">
						   <font class="graybold"><input type="radio" value="+" name="xoper" class="tablehidden" onclick="setoper('+');" checked> Adicionar</font>
						</div>
						<div class="col-md-12">
						   <font class="graybold"><input type="radio" value="-" name="xoper" class="tablehidden" onclick="setoper('-');"> Subtrair</font>
						</div>
					 </div>
				  </div>
				  <div class="col-md-5">
					 <div id="tbOrificios">
						<div class="row">
						   <? if (!empty($x[1]) or $x[1] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q1" value="<?= $_1_u_resultado_q1 ?>" size="3" id="k_1" <?= $keyreadonly ?>> x <span><?= $x[1] ?></span>
								 </div>
							  </div>
						   <? } ?>
						   <? if (!empty($x[6]) or $x[6] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q6" value="<?= $_1_u_resultado_q6 ?>" size="3" id="k_6" <?= $keyreadonly ?>> x <span><?= $x[6] ?></span>
								 </div>
							  </div>
						   <? } ?>
						   <? if (!empty($x[11]) or $x[11] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q11" value="<?= $_1_u_resultado_q11 ?>" size="3" id="k_11" <?= $keyreadonly ?>> x <span><?= $x[11] ?></span>
								 </div>
							  </div>
						   <? } ?>
						</div>


						<div class="row">
						   <? if (!empty($x[2]) or $x[2] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q2" value="<?= $_1_u_resultado_q2 ?>" size="3" id="k_2" <?= $keyreadonly ?>> x <span><?= $x[2] ?></span>
								 </div>
							  </div>
						   <? } ?>
						   <? if (!empty($x[7]) or $x[7] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q7" value="<?= $_1_u_resultado_q7 ?>" size="3" id="k_7" <?= $keyreadonly ?>> x <span><?= $x[7] ?></span>
								 </div>
							  </div>
						   <? } ?>
						   <? if (!empty($x[12]) or $x[12] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q12" value="<?= $_1_u_resultado_q12 ?>" size="3" id="k_12" <?= $keyreadonly ?>> x <span><?= $x[12] ?></span>
								 </div>
							  </div>
						   <? } ?>
						</div>
						<div class="row">

						   <? if (!empty($x[3]) or $x[3] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q3" value="<?= $_1_u_resultado_q3 ?>" size="3" id="k_3" <?= $keyreadonly ?>> x <span><?= $x[3] ?></span>
								 </div>
							  </div>
						   <? } ?>
						   <? if (!empty($x[8]) or $x[8] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q8" value="<?= $_1_u_resultado_q8 ?>" size="3" id="k_8" <?= $keyreadonly ?>> x <span><?= $x[8] ?></span>
								 </div>
							  </div>
						   <? } ?>
						   <? if (!empty($x[13]) or $x[13] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q13" value="<?= $_1_u_resultado_q13 ?>" size="3" id="k_13" <?= $keyreadonly ?>> x <span><?= $x[13] ?></span>
								 </div>
							  </div>
						   <? } ?>
						</div>
						<div class="row">

						   <? if (!empty($x[4]) or $x[4] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q4" value="<?= $_1_u_resultado_q4 ?>" size="3" id="k_4" <?= $keyreadonly ?>> x <span><?= $x[4] ?></span>
								 </div>
							  </div>
						   <? } ?>
						   <? if (!empty($x[9]) or $x[9] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q9" value="<?= $_1_u_resultado_q9 ?>" size="3" id="k_9" <?= $keyreadonly ?>> x <span><?= $x[9] ?></span>
								 </div>
							  </div>
						   <? } ?>
						</div>
						<div class="row">

						   <? if (!empty($x[5]) or $x[5] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q5" value="<?= $_1_u_resultado_q5 ?>" size="3" id="k_5" <?= $keyreadonly ?>> x <span><?= $x[5] ?></span>
								 </div>
							  </div>
						   <? } ?>
						   <? if (!empty($x[10]) or $x[10] === '0') { ?>
							  <div class="col-sm-4">
								 <div>
									<input type="text" name="_1_u_resultado_q10" value="<?= $_1_u_resultado_q10 ?>" size="3" id="k_10" <?= $keyreadonly ?>> x <span><?= $x[10] ?></span>
								 </div>
							  </div>
						   <? } ?>

						   <div class="col-md-12">
							  <div style="padding:8px !important; background: #faebcc">
								 Total: <span style="font-size: 12pt; color: red;" id="somaorificios"><?= $somaor ?></span>
							  </div>
						   </div>
						</div>
						<div class="row">
						   <div class="col-sm-4">
							  <div style="padding:8px !important; background-color: #709ABE; ">
								 GTM: <span style="line-height: 20px;"><?= $_1_u_resultado_gmt ?></span>
							  </div>
						   </div>
						   <div class="col-sm-4">
							  <div style="padding:8px !important;background-color: #709ABE">
								 IDT: <span style="line-height: 20px;"><?= $_1_u_resultado_idt ?></span>
							  </div>
						   </div>
						   <div class="col-sm-4">
							  <div style="padding:8px !important; background-color: #709ABE">
								 CV: <span style="line-height: 20px;"><?= $_1_u_resultado_var ?></span>
							  </div>
						   </div>

						</div>


					 </div>
				  </div>


				  <div class="col-sm-4">
					 <div style="background-color: #ccc;padding: 4px;">
						<div class="row">
						   <div class="col-sm-12">
							  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
							  <div class="col-sm-8"><input name="_1_u_resultado_positividade" id="comp_pos" type="number" size="2" value="<?= $_1_u_resultado_positividade ?>" style="float:left; width: 80px"></div>
						   </div>
						   <div class="col-sm-12">
							  <div class="col-sm-4"><span class="col-md-12" style="float:left;line-height: 16px;text-align: left;"><?= $nomealerta; ?>:</span>
								 <small class="col-md-12" style="font-size:8px;text-align:left;"><i>(marcado - <?= $alertarotuloy; ?>)</i></small>
								 <small class="col-md-12" style="font-size:8px;text-align:left;"><i>(desmarcado - <?= $alertarotulon; ?>)</i></small>
							  </div>
							  <div class="col-sm-8"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="alertateste(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

						   </div>

						   <div class="col-sm-12">
							  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Alerta:</span></div>
							  <div class="col-sm-8">
								 <div id="dTipoAlerta" style=" display: <?= $divdisplay ?>;">
									<? //altera a busca pelo tipo do alerta hermesp 03-09-2020
									$sqal = "select tipoalerta,tipoalerta
											from prodservtipoalerta 
											where idprodserv= " . $_1_u_resultado_idtipoteste . " 
											order by tipoalerta";
									$resal = d::b()->query($sqal) or die("Falhou a buscar por configuração do alerta :" . mysqli_error() . "<br>Sql:" . $sqal);
									$qtdal = mysqli_num_rows($resal);
									if ($qtdal < 1) {
									   $sqal = "select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','HI POSITIVO-1,4[5],12:i:-'";
									}
									?>

									<select name="_1_u_resultado_tipoalerta" onchange="alertateste(<?= $_1_u_resultado_idresultado ?>);">
									   <option value=""></option>
									   <? fillselect($sqal, $_1_u_resultado_tipoalerta); ?>
									   <? //fillselect("select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','1,4[5],12:i:-'",$_1_u_resultado_tipoalerta);
									   ?>
									</select>
								 </div>
							  </div>
						   </div>
						   <div class="col-sm-12" style="display:<?= $divdisplayins ?>">
							  <div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Mostra Insumo:</span></div>
							  <div class="col-sm-4"><input type="checkbox" id="chMostraInsumo" <?= $strmostrainsumo ?> onClick="mostraInsumo(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

						   </div>
						</div>
					 </div>
				  </div>


			   </div>

			<?
			   /*****************************************/
			   /*SE MODO FOR SELETIVO AGRUPADO [FIM]
	/*****************************************/
			   /*****************************************/
			   /*SE MODO FOR SELETIVO INDIVIDUAL [INICIO]
	/*****************************************/
			} elseif ($_1_u_resultado_modelo == "SELETIVO" and $_1_u_resultado_modo == "IND") { //Se for tipo GMT
			   $keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
			   /*	if($_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"){
   		$pesagem="Y";
   	}else{
   		$pesagem="N";
   	}	 
	*/
			?>
			   <br>
			   <div align="center">
				  <?


				  $sqlind = "select * from resultadoindividual i where i.idresultado=" . $_1_u_resultado_idresultado;
				  $resind = mysql_query($sqlind) or die(" Erro ao buscar od resultados individuais 2:" . mysql_error . " <p>SQL" . $sqlind);
				  $x = 0;
				  $i = 2;
				  $tipoespecial = substr($_1_u_resultado_tipoespecial, 0, -4);
				  $tab = 0;
				  while ($rowind = mysql_fetch_assoc($resind)) {
					 $i = $i + 1;
					 $tab = $tab + 2;

					 if (($x % 7) == 0) {
				  ?>
						<div class="col-sm-4">
						   <div class="interna3">
							  <div class="row">
								 <div class="col-sm-4 text-right">
									ID
								 </div>

								 <div class="col-sm-4">
									Orif.
								 </div>
								 <div class="col-sm-4">
									Valor
								 </div>

							  </div>
						   </div>
						<?
					 }
					 $x = $x + 1;
						?>
						<div class="row divdescritivo">
						   <div class="col-sm-12">
							  <div class="interna2 ">
								 <div class="col-sm-1">
									<span style="line-height: 30px;"><?= $x ?></span>
								 </div>
								 <div class="col-sm-4">
									<div class="interna">
									   <input type="hidden" name="_<?= $i ?>_u_resultadoindividual_idresultadoindividual" value="<?= $rowind['idresultadoindividual'] ?>" size="3">
									   <input tabindex="<?= $tab ?>" type="text" style="width:100% !important; font-size:11px" title="Identificação" placeholder="ID" name="_<?= $i ?>_u_resultadoindividual_identificacao" value="<?= $rowind['identificacao'] ?>" size="10">
									</div>
								 </div>
								 <!--JLAL - 20/10/01 /*Alteração do evento change para onkeyup, para atualizar o valor sem precisar clicar fora ou em outro campo*/-->
								 <div class="col-sm-2">
									<div class="interna">
									   <input style="width:100% !important; font-size:11px" tabindex="<?= $tab + 1 ?>" type="text" title="tecla" id="tecla<?= $i ?>" onkeyup="setresultadoind(<?= $i ?>);" name="_<?= $i ?>_u_resultadoindividual_valor" value="<?= $rowind['valor'] ?>" size="1">
									   <input type="hidden" title="tecla" id="resultado<?= $i ?>" name="_<?= $i ?>_u_resultadoindividual_resultado" value="<?= $rowind['resultado'] ?>" size="1">
									   <input type="hidden" name="tipoespecial" value="<?= $_1_u_resultado_tipoespecial ?>" size="1">
									   <input type="hidden" name="tipoteste" value="<?= $tipoespecial ?>" size="1">
									</div>
								 </div>
								 <div class="col-sm-3">
									<div class="interna">
									   <select class="seltit" id="rotulo<?= $i ?>" title="Resultado" <?= $disabled2 ?> name="resultado" vnulo disabled="disabled" style="background: #ddd;width:100% !important; font-size:11px">
										  <option value=""></option>
										  <? fillselect(
											 "SELECT @i:=@i+1 AS num, (valor*1) as valor FROM prodservtipoopcao, (SELECT @i:=0) AS foo 
								where idprodserv = '" . $_1_u_resultado_idtipoteste . "' order by valor*1",
											 $rowind['resultado']
										  ); ?>
									   </select>
									</div>
								 </div>


							  </div>
						   </div>
						</div>
						<?
						if (($x % 7) == 0) {
						?>
						</div>
					 <?
						}
					 }
					 if (($x % 7) != 0) {
					 ?>
			   </div>
	  </div>
   <?
					 }
   ?>
   <?
			   $stralerta = "";
			   if ($_1_u_resultado_alerta == "Y") {
				  $stralerta = "checked";
				  $divdisplay = "block";
			   } else {
				  $divdisplay = "none";
			   }
   ?>

   <?
			   $strmostrainsumo = "";
			   if ($_1_u_resultado_mostraformulains == "Y") {
				  $strmostrainsumo = "checked";
				  $divdisplayins = "block";
			   } else {
				  $divdisplayins = "none";
			   }
   ?>
   <div class="col-sm-4">
	  <? if ($_1_u_resultado_tipogmt != "N/A") { ?>
		 <div class="interna3" style="background-color:#709ABE;height: 45px;">
			<div class="row">
			   <div class="col-sm-12">
				  <div style="padding-left: 6px;padding-right: 6px;">
					 GMT <span style="float:right"><?= $_1_u_resultado_gmt ?></span>
				  </div>
			   </div>
			</div>
		 </div>
	  <? } ?>
	  <div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
		 <div class="row">
			<div class="col-sm-12">
			   <div style="padding-left: 6px;padding-right: 6px;line-height: 30px">
				  Alerta <span style="float:right"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="alertateste(<?= $_1_u_resultado_idresultado ?>);"></span>
			   </div>
			</div>
		 </div>
	  </div>
   </div>
   </div>
<?
/*****************************************/
/*SE MODO FOR SELETIVO INDIVIDUAL [FIM]
/*****************************************/
/*****************************************/
/*SE MODO FOR DINAMICO [INICIO]
/*****************************************/
} elseif ($_1_u_resultado_modelo == "DINÂMICO") {
if (!empty($_1_u_resultado_idtipoteste) and empty($_1_u_resultado_descritivo)) {
	$sqlt = "select textoinclusaores from prodserv where idprodserv =" . $_1_u_resultado_idtipoteste;
	$rest = mysql_query($sqlt);
	$rowt = mysql_fetch_assoc($rest);
	$_1_u_resultado_descritivo = $rowt['textoinclusaores'];
}

if (!empty($_1_u_resultado_idtipoteste)) {

	$jsonresultado = json_decode($_1_u_resultado_jsonresultado);

	$sqlj = "select jsonconfig from prodserv where idprodserv = " . $_1_u_resultado_idtipoteste;
	$resj = mysql_query($sqlj);
	// $rowj=mysql_fetch_assoc($resj);


	while ($rowind = mysql_fetch_assoc($resj)) {
		$jsonconfig = $rowind['jsonconfig'];
	}

	if (empty($_1_u_resultado_jsonconfig) or (count($jsonresultado->INDIVIDUAL) == 0)) {
		$_1_u_resultado_jsonconfig = $jsonconfig;
	}

	$input = ($_1_u_resultado_jsonconfig);
	$jsonconfig = json_decode($input);


	$dataagrup = '<div>';
	$qtdcampos = 1;
	/*JLAL - 20/10/01 
	Alterações: Validar identificadores cadastrados na amostra; Validar campo indentificador cadastrado na prodserv;
		Validar unidade especifica para buscar a configuração na prodserv; Validar campos tipo fixo para só aparecer quando não tiver identificador e nem campo identificador na prodserv;
		Função de aplicar para todos, apenas em campos tipo selecionavel e fixo quando tem campo identificador na prodeserv e identificador na amostra;
		Retrocompatibilidade com as funcionaldiades antigas, para que nenhum resultado perca a configuração;   
	*/
	//sql para trazer a unidade especifica cadastrada na amostra
	$sqlUnidadeEspecie = "select
							tef.idespeciefinalidade,
							id.identificacao,
							id.ididentificador
							, p.idplantel
							, p.plantel as especie
							, a.idespeciefinalidade
							from especiefinalidade tef left join plantel p on(p.idplantel=tef.idplantel)
							join amostra as a on (tef.idespeciefinalidade=a.idespeciefinalidade)
							left join identificador id on (id.idobjeto = a.idamostra)
							where  tef.status='A'
							and a.idamostra = " . $_1_u_resultado_idamostra;
	//echo "<pre>".$sqlUnidadeEspecie."<pre>";
	$rest = mysql_query($sqlUnidadeEspecie);
	$rowt = mysql_fetch_assoc($rest);
	$idplantel = $rowt['idplantel'];
	$identificacao = $rowt['identificacao'];
	$unidade = !empty($idplantel) ? $idplantel : 'todas';
	//Validação para verificar se é a prodserv antiga
	if ($jsonconfig->personalizados) {
		foreach ($jsonconfig->personalizados as $key) {
		if ($key->vinculo == 'INDIVIDUAL') {
			$headind .= '<div class="col-sm-1 text-center">';
			$headind .= $key->titulo;
			$headind .= '</div>';
			$qtdcampos++;
		}

		if ($key->vinculo == 'AGRUPADO') {
			$dataagrup .= '<div class="col-sm-4"><label style="color:#333 !important;font-weight:normal !important;">' . $key->titulo . '</label>';
			$dataagrup .= criaCamposTabelaAntigo($key);
			$dataagrup .= '</div>';
		} else {
			if ($key->tipo == 'textarea') {
				$n = 1;
			} else {
				$n = 1;
			}
			$dataind .= '<div class="col-sm-' . $n . '">';
			$dataind .= criaCamposTabelaAntigo($key);
			$dataind .= '</div>';
		}
		}
	} else {
		//Nova configuração implementada para atender a nova prodserv
		foreach ($jsonconfig->unidadeBloco as $unidadeNeg) {
		$unidadePadrao = $unidadeNeg->unidade == "" ? "todas" : $unidadeNeg->unidade;
		//Validação para verificar se existe uma configuração na prodserv que bate com o tipo de unidade de negocio,
		//se não existir ele pega a configuração com o tipo todas
		if ($unidadePadrao == $unidade) {
			$teste = 1;
			break;
		} else {
			$teste = 0;
		}
		}
		foreach ($jsonconfig->unidadeBloco as $unidadeNeg) {
		$unidadePadrao = $unidadeNeg->unidade == "" ? "todas" : $unidadeNeg->unidade;
		//Validação para verificar se existe uma configuração na prodserv que bate com o tipo de unidade de negocio,

		foreach ($unidadeNeg->personalizados as $key) {
			//Valida se existe campo identificador cadastrado na configuração da prodserv
			if ($unidadePadrao == $unidade && $teste == 1) {
				if ($key->tipo == 'identificador') {
					$tipoIdent = 1;
					break;
				} else {
					$tipoIdent = 0;
				}
				if ($key->tipo == 'selecionavel' || $key->tipo == 'fixo') {
					$inputRemover = 1;
				}
			} else if ($unidadePadrao == "todas" && $teste != 1) {
				if ($key->tipo == 'identificador') {
					$tipoIdent = 1;
					break;
				} else {
					$tipoIdent = 0;
				}
				if ($key->tipo == 'selecionavel' || $key->tipo == 'fixo') {
					$inputRemover = 1;
				}
			}
		}
		}
		foreach ($jsonconfig->unidadeBloco as $unidadeNeg) {
		$unidadePadrao = $unidadeNeg->unidade == "" ? "todas" : $unidadeNeg->unidade;

		//@521338 - ERRO ORDEM TABELA DINÂMICA SERVIÇOS
		//ordenando campos dinamicos conforme configuração da prodserv
		$camposOrdenados = [];
		foreach ($unidadeNeg->personalizados as $campos)
			$camposOrdenados[$campos->ordem] = $campos;
		ksort($camposOrdenados);

		if ($unidadePadrao == $unidade && $teste == 1) {
			foreach ($camposOrdenados as $key) {
				if ($unidadeNeg->index == $key->index) {
					if ($key->vinculo == 'INDIVIDUAL') {
					//Verifica se é campo do tipo selecionavel ou campo tipo fixo que tenha um campo identificador na config da prodserv
					//para poder implementar a funcionalidade de aplicar para todos
					if (($key->tipo == 'selecionavel') || ($key->tipo == "fixo" && !empty($identificacao &&  $tipoIdent == 1))) {
						if ($key->tipo == 'selecionavel') {
							$tipo = 1;
						} else if ($tipoIdent != 0 && !empty($identificacao) && $key->tipo == "fixo") {
							$tipo = 2;
						}
						$headind .= '<div class="col-sm-1 text-center">';
						$headind .= $key->titulo;
						$headind .= '<img src="./inc/img/iconApplyAll.png" width="16px" height="16px" title="Aplicar à todos" alt="Aplicar à todos" style="margin-left:5px" onclick="aplicarTodos(' . $tipo . ',' . $key->indice . ',' . $teste . ');">';
						$headind .= '<div class="configModal' . $key->indice . '"></div>';
						$headind .= '</div>';

						$qtdcampos++;
					} else {
						$headind .= '<div class="col-sm-1 text-center">';
						$headind .= $key->titulo;
						$headind .= '</div>';

						$qtdcampos++;
					}
					}
					if ($key->vinculo == 'AGRUPADO') {
					$dataagrup .= '<div class="col-sm-4"><label style="color:#333 !important;font-weight:normal !important;">' . $key->titulo . '</label>';
					$dataagrup .= criaCamposTabela($key);
					$dataagrup .= '</div>';
					} else {
					if ($key->tipo == 'textarea') {
						$n = 1;
					} else {
						$n = 1;
					}
					$dataind .= '<div class="col-sm-' . $n . '">';
					$dataind .= criaCamposTabela($key);
					$dataind .= '</div>';
					}
				}
			}
		} else if ($unidadePadrao == "todas" && $teste != 1) {
			foreach ($camposOrdenados as $key) {
				if ($unidadeNeg->index == $key->index) {
					if ($key->vinculo == 'INDIVIDUAL') {
					if (($key->tipo == 'selecionavel') || ($key->tipo == "fixo" && !empty($identificacao &&  $tipoIdent == 1))) {
						if ($key->tipo == 'selecionavel') {
							$tipo = 1;
						} else if ($tipoIdent != 0 && !empty($identificacao) && $key->tipo == "fixo") {
							$tipo = 2;
						}
						$headind .= '<div class="col-sm-1 text-center">';
						$headind .= $key->titulo;
						$headind .= '<img src="./inc/img/iconApplyAll.png" width="16px" height="16px" title="Aplicar à todos" alt="Aplicar à todos" style="margin-left:5px" onclick="aplicarTodos(' . $tipo . ',' . $key->indice . ',' . $teste . ');">';
						$headind .= '<div class="configModal' . $key->indice . '"></div>';
						$headind .= '</div>';

						$qtdcampos++;
					} else {
						$headind .= '<div class="col-sm-1 text-center">';
						$headind .= $key->titulo;
						$headind .= '</div>';

						$qtdcampos++;
					}
					}
					if ($key->vinculo == 'AGRUPADO') {
					$dataagrup .= '<div class="col-sm-4"><label style="color:#333 !important;font-weight:normal !important;">' . $key->titulo . '</label>';
					$dataagrup .= criaCamposTabela($key);
					$dataagrup .= '</div>';
					} else {
					if ($key->tipo == 'textarea') {
						$n = 1;
					} else {
						$n = 1;
					}
					$dataind .= '<div class="col-sm-' . $n . '">';
					$dataind .= criaCamposTabela($key);
					$dataind .= '</div>';
					}
				}
			}
		}
		}
	}

	$dataagrup .= '</div>';
	$c = 1;
	$cabecalho .= ' <div class="row">
			<div class="col-sm-12">
				<div class="interna3" style="margin:0px;font-size:10px;">
					<div class="row">
					<div class="col-sm-1">         
						<img src="./inc/img/svg/checked.svg" style="display:none;margin-left:5px;" id="selectChecked" width="16px" height="16px" title="Selecionar todos os itens" alt="Selecionar todos os itens" style="margin-left:5px" onclick="uncheckedAll();">
						<img src="./inc/img/svg/unchecked.svg" style="margin-left:5px;"  id="unchecked" width="16px" height="16px" title="Selecionar todos os itens" alt="Selecionar todos os itens"  onclick="checkedAll();">
						<img src="./inc/img/removeAll.png" style="display:none;" id="removeChecked" width="16px" height="16px" title="Remover itens selecionados" alt="Remover itens selecionados" style="margin-left:5px" onclick="delSelecionados();">
						</div>
					
					' . $headind;
	$cabecalho .= '         </div>
				</div>
			</div>
		</div>';
	$fixo = 0;
	$fixoindice = '';
	$qtdfixo = 0;
	//Valida se é configuração da prodserv antiga, para trazer os campos certos
	if ($jsonconfig->personalizados) {
		foreach ($jsonconfig->personalizados as $key) {
		if ($key->tipo == 'fixo') {
			$fixoindice = $key->indice;
			foreach ($key->options as $k) {
				$qtdfixo++;
				//echo $k->nome;
				$fixo = 1;
				$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="addCampo(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
		<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="delCampo(this)" style="position:absolute;z-index:999;right:-40px;"></span>
					<div class="col-sm-12">
					<div class="interna2 " style="height:auto;">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input remove" name="remove">
						</div>
					<div class="row">';
				$gridind .= '        <div class="col-sm-1 text-center">' . $c . '</div>';
				$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
				$gridind .= '     </div> </div>
					</div>
				</div>';
				$c++;
			}
		}
		}
	} else {
		//Traz os campos com a nova configuração buscada na prodsev
		foreach ($jsonconfig->unidadeBloco as $unidadeNeg) {
		$unidadePadrao = $unidadeNeg->unidade == "" ? "todas" : $unidadeNeg->unidade;
		if ($unidadePadrao == $unidade) {
			$teste = 1;
		} else {
			$teste = 0;
		}
		if ($unidadePadrao == $unidade && $teste == 1) {

			foreach ($unidadeNeg->personalizados as $key) {
				if ($unidadeNeg->index == $key->index && ($jsonresultado->QTDINDIVIDUAL == 0 || empty($jsonresultado))) {
					if ($key->tipo == 'fixo') {
					$fixoindice = $key->indice;
					foreach ($key->options as $k) {

						if ($tipoIdent == 0 || empty($identificacao)) {
							$qtdfixo++;
							//echo $k->nome;


							$fixo = 1;
							$gridind .= '<div class="row  global">
					<span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="addCampo(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
					<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="delCampo(this)" style="position:absolute;z-index:999;right:-40px;"></span>
					<div class="col-sm-12">
					<div class="interna2" style="height:auto;">
					
						<div class="custom-control custom-checkbox">
							<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
						</div>
						<div class="row">';
							$gridind .= '     <div class="col-sm-1 text-center">' . $c . '</div>';
							$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
							$gridind .= '     </div> 
						</div>
					</div>
				</div>';
							$c++;
						}
					}
					}
				}
			}
		} else if ($unidadePadrao == "todas" && $teste != 1) {
			foreach ($unidadeNeg->personalizados as $key) {
				if ($unidadeNeg->index == $key->index && ($jsonresultado->QTDINDIVIDUAL == 0 || empty($jsonresultado))) {
					if ($key->tipo == 'fixo') {
					$fixoindice = $key->indice;
					foreach ($key->options as $k) {

						if ($tipoIdent == 0 || empty($identificacao)) {
							$qtdfixo++;
							//echo $k->nome;
							$fixo = 1;
							$gridind .= '<div class="row  global">
					<span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="addCampo(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
					<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="delCampo(this)" style="position:absolute;z-index:999;right:-40px;"></span>
					<div class="col-sm-12">
					<div class="interna2" style="height:auto;">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
						</div>
						<div class="row">
						';
							$gridind .= '     <div class="col-sm-1 text-center">' . $c . '</div>';
							$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
							$gridind .= '     </div> 
						</div>
					</div>
				</div>';
							$c++;
						}
					}
					}
				}
			}
		}
		}
	}

	$c = 1;
	//Traz a configuração gravada no jsonresultado se existir campo fixo
	if ($qtdfixo > 0 and count($jsonresultado->INDIVIDUAL) > 0) {
		//print_r($jsonresultado->INDIVIDUAL);
		$gridind = '';
		$vIndice = '';
		foreach ($jsonresultado->INDIVIDUAL as $k) {
		if ($vIndice != $k->indice) {
			$vIndice = $k->indice;
			$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="addCampo(this,' . $vIndice . ')" style="position:absolute;z-index:999;right:-15px;"></span>
		<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="delCampo(this)" style="position:absolute;z-index:999;right:-40px;"></span>
			<div class="col-sm-12">
			<div class="interna2 " style="height:auto;">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
				</div>
					<div class="row">';
			$gridind .= '        <div class="col-sm-1 text-center">' . $vIndice . '</div>';
			$gridind .= str_replace('data-indice=""', 'data-indice="' . $vIndice . '"', str_replace('name="camp', 'name="' . $vIndice . '_camp', $dataind));
			$gridind .= '     </div> </div>
					</div>
				</div>';
			$c++;
		} else if ($c <= $jsonresultado->QTDINDIVIDUAL && empty($k->indice)) {
			$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="addCampo(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
		<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="delCampo(this)" style="position:absolute;z-index:999;right:-40px;"></span>
			<div class="col-sm-12">
			<div class="interna2 " style="height:auto;">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
				</div>
					<div class="row">';
			$gridind .= '        <div class="col-sm-1 text-center">' . $c . '</div>';
			$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
			$gridind .= '     </div> </div>
					</div>
				</div>';
			$c++;
		}
		}
		//Traz a config salva pelo usuário
	} else if (count($jsonresultado->INDIVIDUAL) > 0) {
		$gridind = '';
		$vIndice = '';
		foreach ($jsonresultado->INDIVIDUAL as $k) {
		if ($vIndice != $k->indice) {
			$vIndice = $k->indice;
			$onclick = ($geraagente == 'Y') ? 'delCampo(this,' . $vIndice . ')' : 'delCampo(this)';

			$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="addCampo(this,' . $vIndice . ')" style="position:absolute;z-index:999;right:-15px;"></span>
		<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="' . $onclick . '" style="position:absolute;z-index:999;right:-40px;"></span>
			<div class="col-sm-12">
			<div class="interna2 " style="height:auto;">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
				</div>
					<div class="row">
					';
			$gridind .= '        <div class="col-sm-1 text-center">' . $vIndice . '</div>';
			$gridind .= str_replace('data-indice=""', 'data-indice="' . $vIndice . '"', str_replace('name="camp', 'name="' . $vIndice . '_camp', $dataind));
			$gridind .= '     </div> </div>
					</div>
				</div>';
			$c++;
		} else if ($c <= $jsonresultado->QTDINDIVIDUAL && empty($k->indice)) {
			$gridind .= '<div class="row  global" ><span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="addCampo(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
		<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="delCampo(this)" style="position:absolute;z-index:999;right:-40px;"></span>
			<div class="col-sm-12">
			<div class="interna2 " style="height:auto;">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
				</div>
					<div class="row">';
			$gridind .= '        <div class="col-sm-1 text-center">' . $c . '</div>';
			$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
			$gridind .= '     </div> </div>
					</div>
				</div>';
			$c++;
		}
		}
	}
	//Valida se existe campo do tipo fixo, se não foi salvo ainda e se existe campo identificador ou identificações da amostra
	if ($fixo != 1 && (empty($identificacao) || $tipoIdent == 0) && $jsonresultado->QTDINDIVIDUAL == 0) {
		while ($c <= $_1_u_resultado_quantidade) {
		$gridind .= '<div class="row  global">
					<span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="addCampo(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
					<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="delCampo(this)" style="position:absolute;z-index:999;right:-40px;"></span>
					<div class="col-sm-12">
					<div class="interna2" style="height:auto;">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
						</div>
						<div class="row">';
		$gridind .= '     <div class="col-sm-1 text-center">' . $c . '</div>';
		$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace('name="camp', 'name="' . $c . '_camp', $dataind));
		$gridind .= '     </div> 
						</div>
					</div>
				</div>';
		$c++;
		}
		//Valida se nao for do tipo fixo e se existir identificação e campo identificador e nao tiver sido salvo ainda
	} else if ($fixo != 1 && (!empty($identificacao) && $tipoIdent == 1) && $jsonresultado->QTDINDIVIDUAL == 0) {
		$sqlIdentificador = "select
		id.identificacao,
		id.ididentificador
		from amostra as a 
		join identificador id on (id.idobjeto = a.idamostra)
		where a.idamostra = " . $_1_u_resultado_idamostra;
		//echo "<pre>".$sqlUnidadeEspecie."<pre>";
		$result = mysql_query($sqlIdentificador);

		while ($c <= $rowId = mysql_fetch_assoc($result)) {
		$identificacao = $rowId['identificacao'];
		$gridind .= '<div class="row global">
					<span class="fa fa-plus-circle fa-2x cinzaclaro hovercinza verde pointer" onclick="addCampo(this,' . $c . ')" style="position:absolute;z-index:999;right:-15px;"></span>
					<span class="fa fa-minus-circle fa-2x cinzaclaro hovercinza vermelho pointer" onclick="delCampo(this)" style="position:absolute;z-index:999;right:-40px;"></span>
					<div class="col-sm-12">
					<div class="interna2" style="height:auto;">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" style="display:none;" class="custom-control-input remove" name="remove">
						</div>
						<div class="row">';
		$gridind .= '     <div class="col-sm-1 text-center">' . $c . '</div>';
		$gridind .= str_replace('data-indice=""', 'data-indice="' . $c . '"', str_replace(
			'name="camp',
			'name="' . $c . '_camp',
			str_replace('value=""', 'value="' . $identificacao . '"', $dataind)
		));
		$gridind .= '     </div> 
						</div>
					</div>
				</div>';
		$c++;
		}
	}
}

echo $cabecalho . $gridind;
//Valida para atualizar a quantidade de testes, caso chegue a zero ele pega da sessão o ultimo valor cadastrado para aquele tipo de resultado
if ($_1_u_resultado_quantidade == 0) {
	echo '<input type="hidden" id="qtTeste" value="' . $_SESSION['auxqt'] . '" name="_1_u_resultado_quantidade">';
	unset($_SESSION['auxqt']);
} else {
	if ($_SESSION['auxqt']) {
		echo '<input type="hidden" id="qtTeste" value="' . $_1_u_resultado_quantidade . '" name="_1_u_resultado_quantidade">';
	} else {
		$_SESSION['auxqt'] = $_1_u_resultado_quantidade;
		echo '<input type="hidden" id="qtTeste" value="' . $_1_u_resultado_quantidade . '" name="_1_u_resultado_quantidade">';
	}
}

$stralerta = "";
if ($_1_u_resultado_alerta == "Y") {
	$stralerta = "checked";
	$divdisplay = "block";
} else {
	$divdisplay = "none";
}

$strmostrainsumo = "";
if ($_1_u_resultado_mostraformulains == "Y") {
	$strmostrainsumo = "checked";
	$divdisplayins = "block";
} else {
	$divdisplayins = "none";
}
?>
   <div class="row">
	  <div class="col-sm-9">
		 <div style="background-color: #ccc;padding: 8px;">
			<div class="row">
			   <div class="col-sm-12">

				  <?
				  echo $dataagrup;
				  ?>
				  <input type="hidden" id="jsonresultado" name="_1_u_resultado_jsonresultado" value='<?= $_1_u_resultado_jsonresultado ?>'>
				  <input type="hidden" id="jsonconfig" name="_1_u_resultado_jsonconfig" value='<?= $_1_u_resultado_jsonconfig ?>'>
			   </div>

			</div>
		 </div>
		 <?

			   //Valida se existe campo de calculo marcado na config da prodserv, se existe calculo marcado de GMT ou ART
			   if ($_1_u_resultado_tipogmt != "N/A") {
				  if ($_1_u_resultado_tipogmt == "GMT") {
					 echo '<div class="row">
		<div class="col-sm-2">
		   <div style="margin-left: 5px;padding:8px !important;background-color: #709ABE;">
			  GMT: <span style="line-height: 10px;">' . $_1_u_resultado_gmt . '</span>
		   </div>
		</div>
		</div>';
				  }
				  if ($_1_u_resultado_tipogmt == "ART") {
					 echo '<div class="row">
		<div class="col-sm-2">
		   <div style="margin-left: 5px;padding:8px !important;background-color: #709ABE; ">
			  ART: <span style="line-height: 10px;">' . number_format(tratanumero($_1_u_resultado_gmt), 2, '.', '') . '</span>
		   </div>
		</div>
		</div>';
				  }
				  if ($_1_u_resultado_tipogmt == "SOMA") {
					 echo '<div class="row">
		<div class="col-sm-2">
		   <div style="margin-left: 5px;padding:8px !important;background-color: #709ABE; ">
			  SOMA: <span style="line-height: 10px;">' . number_format(tratanumero($_1_u_resultado_gmt), 2, '.', '') . '</span>
		   </div>
		</div>
		</div>';
				  }
				  if ($_1_u_resultado_tipogmt == "PERC") {
					 echo '<div class="row">
		<div class="col-sm-2">
		   <div style="margin-left: 5px;padding:8px !important;background-color: #709ABE; ">
			  PERC: <span style="line-height: 10px;">' . number_format(tratanumero($_1_u_resultado_gmt), 2, '.', '') . '%</span>
		   </div>
		</div>
		</div>';
				  }
			   }
		 ?>
	  </div>

	  <div class="col-sm-3">
		 <div style="background-color: #ccc;padding: 8px;">
			<div class="row">
			   <div class="col-sm-12">
				  <div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
				  <div class="col-sm-4"><input name="_1_u_resultado_positividade" id="comp_pos" type="number" size="2" value="<?= $_1_u_resultado_positividade ?>" style="float:left; width: 40px"></div>
			   </div>
			   <div class="col-sm-12">
				  <div class="col-sm-8"><span class="col-md-12" style="float:left;line-height: 16px;text-align: left;"><?= $nomealerta; ?>:</span>
					 <small class="col-md-12" style="font-size:8px;text-align:left;"><i>(marcado - <?= $alertarotuloy; ?>)</i></small>
					 <small class="col-md-12" style="font-size:8px;text-align:left;"><i>(desmarcado - <?= $alertarotulon; ?>)</i></small>

				  </div>
				  <div class="col-sm-4"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="alertateste(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>
				  <?
				  $stralerta = "";
				  if ($_1_u_resultado_alerta == "Y") {
					 $stralerta = "checked";
					 $divdisplay = "block";
				  } else {
					 $divdisplay = "none";
				  }
				  ?>
				  <?
				  $strmostrainsumo = "";
				  if ($_1_u_resultado_mostraformulains == "Y") {
					 $strmostrainsumo = "checked";
					 $divdisplayins = "block";
				  } else {
					 $divdisplayins = "none";
				  }
				  ?>
			   </div>

			   <div class="col-sm-12">
				  <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Alerta:</span></div>
				  <div class="col-sm-8">
					 <div id="dTipoAlerta" style=" display: <?= $divdisplay ?>;">
						<? //altera a busca pelo tipo do alerta hermesp 03-09-2020
						$sqal = "select tipoalerta,tipoalerta
											from prodservtipoalerta 
											where idprodserv= " . $_1_u_resultado_idtipoteste . " 
											order by tipoalerta";
						$resal = d::b()->query($sqal) or die("Falhou a buscar por configuração do alerta :" . mysqli_error() . "<br>Sql:" . $sqal);
						$qtdal = mysqli_num_rows($resal);
						if ($qtdal < 1) {
						   $sqal = "select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','HI POSITIVO-1,4[5],12:i:-'";
						}
						?>

						<select name="_1_u_resultado_tipoalerta" onchange="alertateste(<?= $_1_u_resultado_idresultado ?>);">
						   <option value=""></option>
						   <? fillselect($sqal, $_1_u_resultado_tipoalerta); ?>
						   <? //fillselect("select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','1,4[5],12:i:-'",$_1_u_resultado_tipoalerta);
						   ?>
						</select>
						<input type="hidden" name="_1_<?=$_acao?>_resultado_alerta" value="<?=$_1_u_resultado_alerta ?>">
					 </div>
				  </div>
			   </div>
			   <div class="col-sm-12" style="display:<?= $divdisplayins ?>">
				  <div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Mostra Insumo:</span></div>
				  <div class="col-sm-4"><input type="checkbox" id="chMostraInsumo" <?= $strmostrainsumo ?> onClick="mostraInsumo(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

			   </div>
			</div>
		 </div>
		 <?
			   if ($modamostra == 'amostracqd' or $modamostra == 'amostraprod') { //Controle de qualidade
		 ?>
			<div style="background-color: #709ABE;padding: 4px;margin-top: 4px;">
			   <div class="row">
				  <div class="col-sm-12">
					 <div class="col-sm-12"><span style="float:left;line-height: 30px;text-align: left;">Resultado:</span></div>
					 <div class="col-sm-12"><input class="size15" name="_1_u_resultado_resultadocertanalise" type="text" class="" value="<?= $_1_u_resultado_resultadocertanalise ?>"></div>
				  </div>
				  <div class="col-sm-12">
					 <div class="col-sm-12"><span style="float:left;line-height: 30px;text-align: left;">Conclusão:</span></div>
					 <div class="col-sm-12">
						<select name="_1_u_resultado_conformidade">
						   <option value=""></option>
						   <? fillselect(array(
							  'CONFORME' => 'Conforme', 'NAO CONFORME' => 'Não Conforme', 'NAO SE APLICA' => 'Não se Aplica'
						   ), $_1_u_resultado_conformidade); ?>
						</select>
					 </div>
				  </div>
			   </div>
			</div>
		 <?
			   }
		 ?>
	  </div>
   </div>
   <style>
	  .interna2 .col-sm-1,
	  .interna3 .col-sm-1 {
		 width: <?= (100 / $qtdcampos); ?>%;
	  }
   </style>
<?

/*****************************************/
/*SE MODO FOR DINAMICO [FIM]
/*****************************************/
/*****************************************/
/*SE MODO FOR DESCRITIVO AGRUPADO [INICIO]
/*****************************************/
} elseif ($_1_u_resultado_modelo == "DESCRITIVO" and $_1_u_resultado_modo == "AGRUP") {

if (!empty($_1_u_resultado_idtipoteste) and empty($_1_u_resultado_descritivo)) {
$sqlt = "select textoinclusaores from prodserv where idprodserv =" . $_1_u_resultado_idtipoteste;
$rest = mysql_query($sqlt);
$rowt = mysql_fetch_assoc($rest);
$_1_u_resultado_descritivo = $rowt['textoinclusaores'];
}


$stralerta = "";
if ($_1_u_resultado_alerta == "Y") {
	$stralerta = "checked";
	$divdisplay = "block";
} else {
	$divdisplay = "none";
}

$strmostrainsumo = "";
if ($_1_u_resultado_mostraformulains == "Y") {
	$strmostrainsumo = "checked";
	$divdisplayins = "block";
} else {
	$divdisplayins = "none";
}
   ?>
<div align="center" style="padding: 0px 18px;">
	<div class="row">
		<div class="col-sm-9">
		<div style="background-color: #ccc;padding: 4px;">
			<div class="row">
				<div class="col-sm-12">
					<label id="lbaviso" class="idbox" style="display: none;"></label>
					<div id="diveditor" class="diveditor" onkeypress="pageStateChanged=true;" style="text-align: left"><?= $_1_u_resultado_descritivo ?></div>
					<textarea style="display: none; text-align: left" name="_1_u_resultado_descritivo"><?= $_1_u_resultado_descritivo ?></textarea>
				</div>
			</div>
		</div>
		</div>
		<div class="col-sm-3">
		<div style="background-color: #ccc;padding: 4px;">
			<div class="row">
				<div class="col-sm-12">
					<div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
					<div class="col-sm-4"><input name="_1_u_resultado_positividade" id="comp_pos" type="number" size="2" value="<?= $_1_u_resultado_positividade ?>" style="float:left; width: 40px"></div>
				</div>
				<div class="col-sm-12">
					<div class="col-sm-8"><span class="col-md-12" style="float:left;line-height: 16px;text-align: left;"><?= $nomealerta; ?>:</span>
					<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(marcado - <?= $alertarotuloy; ?>)</i></small>
					<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(desmarcado - <?= $alertarotulon; ?>)</i></small>
					</div>
					<div class="col-sm-4"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="alertateste(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>
					<?
					$stralerta = "";
					if ($_1_u_resultado_alerta == "Y") {
					$stralerta = "checked";
					$divdisplay = "block";
					} else {
					$divdisplay = "none";
					}
					?>
					<?
					$strmostrainsumo = "";
					if ($_1_u_resultado_mostraformulains == "Y") {
					$strmostrainsumo = "checked";
					$divdisplayins = "block";
					} else {
					$divdisplayins = "none";
					}
					?>
				</div>

				<div class="col-sm-12">
					<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Alerta:</span></div>
					<div class="col-sm-8">
					<div id="dTipoAlerta" style=" display: <?= $divdisplay ?>;">
						<? //altera a busca pelo tipo do alerta hermesp 03-09-2020
						$sqal = "select tipoalerta,tipoalerta
										from prodservtipoalerta 
										where idprodserv= " . $_1_u_resultado_idtipoteste . " 
										order by tipoalerta";
						$resal = d::b()->query($sqal) or die("Falhou a buscar por configuração do alerta :" . mysqli_error() . "<br>Sql:" . $sqal);
						$qtdal = mysqli_num_rows($resal);
						if ($qtdal < 1) {
							$sqal = "select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','HI POSITIVO-1,4[5],12:i:-'";
						}
						?>

						<select name="_1_u_resultado_tipoalerta" onchange="alertateste(<?= $_1_u_resultado_idresultado ?>);">
							<option value=""></option>
							<? fillselect($sqal, $_1_u_resultado_tipoalerta); ?>
							<? //fillselect("select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','1,4[5],12:i:-'",$_1_u_resultado_tipoalerta);
							?>
						</select>
					</div>
					</div>
				</div>
				<div class="col-sm-12" style="display:<?= $divdisplayins ?>">
					<div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Mostra Insumo:</span></div>
					<div class="col-sm-4"><input type="checkbox" id="chMostraInsumo" <?= $strmostrainsumo ?> onClick="mostraInsumo(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

				</div>
			</div>
		</div>
		<?
			if ($modamostra == 'amostracqd' or $modamostra == 'amostraprod') { //Controle de qualidade
		?>
			<div style="background-color: #709ABE;padding: 4px;margin-top: 4px;">
				<div class="row">
					<div class="col-sm-12">
					<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Resultado:</span></div>
					<div class="col-sm-8"><input class="size15" name="_1_u_resultado_resultadocertanalise" type="text" class="" value="<?= $_1_u_resultado_resultadocertanalise ?>"></div>
					</div>
					<div class="col-sm-12">
					<div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Conclusão:</span></div>
					<div class="col-sm-8">
						<select name="_1_u_resultado_conformidade">
							<option value=""></option>
							<? fillselect(array(
								'CONFORME' => 'Conforme', 'NAO CONFORME' => 'Não Conforme', 'NAO SE APLICA' => 'Não se Aplica'
							), $_1_u_resultado_conformidade); ?>
						</select>
					</div>
					</div>
				</div>
			</div>
		<?
			}
		?>
		</div>
	</div>
</div>
<?
/*****************************************/
/*SE MODO FOR DESCRITIVO AGRUPADO [FIM]
/*****************************************/
/*****************************************/
/*SE MODO FOR DESCRITIVO INDIVIDUAL [INICIO]
/*****************************************/
} elseif (
$_1_u_resultado_modelo == "DESCRITIVO" and $_1_u_resultado_modo == "IND"

//$_1_u_resultado_tipoespecial=="BRONQUITE IND" or $_1_u_resultado_tipoespecial=="NEWCASTLE IND" or $_1_u_resultado_tipoespecial=="GUMBORO IND" 
//		or $_1_u_resultado_tipoespecial=="REOVIRUS IND" or $_1_u_resultado_tipoespecial=="PNEUMOVIRUS IND" or $_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"
//		or $_1_u_resultado_tipoespecial=="DESCRITIVO IND"
) { //Se for tipo GMT
$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
/*	if($_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"){
$pesagem="Y";
}else{
$pesagem="N";
}	 
*/
?>
   <div align="center">
	  <?


			   $sqlind = "select * from resultadoindividual i where i.idresultado=" . $_1_u_resultado_idresultado;
			   $resind = mysql_query($sqlind) or die(" Erro ao buscar od resultados individuais 2:" . mysql_error . " <p>SQL" . $sqlind);
			   $x = 0;
			   $i = 2;
			   $tipoespecial = substr($_1_u_resultado_tipoespecial, 0, -4);
			   $tab = 0;
			   while ($rowind = mysql_fetch_assoc($resind)) {
				  $i = $i + 1;
				  $tab = $tab + 2;

				  if (($x % 7) == 0) {
	  ?>
			<div class="col-sm-4" style="padding: 0px;">
			   <div class="interna3">
				  <div class="row">
					 <div class="col-sm-4 text-right">
						ID
					 </div>

					 <div class="col-sm-7">
						<?= (($_1_u_resultado_tipogmt == "N/A") ? "RESULTADO" : "VALOR") ?>
					 </div>

				  </div>
			   </div>
			<?
				  }
				  $x = $x + 1;
			?>
			<div class="row divdescritivo">
			   <div class="col-sm-12">
				  <div class="interna2 ">
					 <div class="col-sm-1">
						<span style="line-height: 30px;"><?= $x ?></span>
					 </div>
					 <div class="col-sm-4">
						<div class="interna">
						   <input type="hidden" name="_<?= $i ?>_u_resultadoindividual_idresultadoindividual" value="<?= $rowind['idresultadoindividual'] ?>" size="3">
						   <input tabindex="<?= $tab ?>" style="width:100% !important; font-size:11px" type="text" title="Identificação" placeholder="ID" name="_<?= $i ?>_u_resultadoindividual_identificacao" value="<?= $rowind['identificacao'] ?>" size="10">
						</div>
					 </div>

					 <div class="col-sm-7">
						<div class="interna">
						   <input type="hidden" name="tipoespecial" value="<?= $_1_u_resultado_tipoespecial ?>" size="1">
						   <input style="width: 100% !important;font-size:11px" type="text" title="tecla" id="resultado<?= $i ?>" name="_<?= $i ?>_u_resultadoindividual_resultado" value="<?= $rowind['resultado'] ?>" size="20" <?= (($_1_u_resultado_tipogmt == "N/A") ? " class='text-left' " : "vdecimal") ?>>
						</div>
					 </div>

				  </div>
			   </div>
			</div>
			<?
				  if (($x % 7) == 0) {
			?>
			</div>
		 <?
				  }
			   }
			   if (($x % 7) != 0) {
		 ?>
   </div>
</div>
<?
}

$stralerta = "";
if ($_1_u_resultado_alerta == "Y") {
	$stralerta = "checked";
	$divdisplay = "block";
} else {
	$divdisplay = "none";
}

$strmostrainsumo = "";
if ($_1_u_resultado_mostraformulains == "Y") {
	$strmostrainsumo = "checked";
	$divdisplayins = "block";
} else {
	$divdisplayins = "none";
}
?>
<div class="col-sm-4" style="padding:0px;">
   <? if ($_1_u_resultado_tipogmt != "N/A") { ?>
	  <div class="interna3" style="background-color:#709ABE;height: 45px;">
		 <div class="row">
			<div class="col-sm-12">
			   <div style="padding-left: 6px;padding-right: 6px;text-align: left;line-height: 35px;">
				  GMT <span style="float:right"><?= $_1_u_resultado_gmt ?></span>
			   </div>
			</div>
		 </div>
	  </div>
   <? } ?>
   <div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
	  <div class="row">
		 <div class="col-sm-12">
			<div style="padding-left: 6px;padding-right: 6px;text-align: left;line-height: 35px;">
			   Alerta <span style="float:right"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="alertateste(<?= $_1_u_resultado_idresultado ?>);"></span>
			</div>
		 </div>
	  </div>
   </div>
</div>
</div>
<?
/*****************************************/
/*SE MODO FOR DESCRITIVO INDIVIDUAL [FIM]
/*****************************************/
/*****************************************/
/*SE MODO FOR DROP AGRUPADO [INICIO]
/*****************************************/
} elseif ($_1_u_resultado_modelo == "DROP" and $_1_u_resultado_modo == "AGRUP") {

?>
   <div align="center" style="padding: 0px 16px;">
	  <div class="row">
		 <div class="col-sm-8">
			<div style="background-color: #ccc;padding: 4px;">
			   <div class="row">
				  <div class="col-sm-12">
					 <div class="interna">

						<div class="row">
						   <div class="col-sm-2">

							  Resultado:


						   </div>
						   <div class="col-sm-10">
							  <select class="seltit" id="rotulo<?= $i ?>" title="Resultado" <?= $disabled2 ?> name="_1_u_resultado_descritivo" style="width:94% !important">
								 <option value=""></option>
								 <? fillselect(
									"SELECT valor AS num, valor as valor FROM prodservtipoopcao where  idprodserv = '" . $_1_u_resultado_idtipoteste . "' order by 1*valor",
									$_1_u_resultado_descritivo
								 ); ?>
							  </select>
						   </div>
						</div>
					 </div>
				  </div>

			   </div>
			</div>
		 </div>
		 <?
			   $stralerta = "";
			   if ($_1_u_resultado_alerta == "Y") {
				  $stralerta = "checked";
				  $divdisplay = "block";
			   } else {
				  $divdisplay = "none";
			   }
		 ?>
		 <?
			   $strmostrainsumo = "";
			   if ($_1_u_resultado_mostraformulains == "Y") {
				  $strmostrainsumo = "checked";
				  $divdisplayins = "block";
			   } else {
				  $divdisplayins = "none";
			   }
		 ?>
		 <div class="col-sm-4">
			<div style="background-color: #ccc;padding: 4px;">
			   <div class="row">
				  <div class="col-sm-12">
					 <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Qtd. Positivos:</span></div>
					 <div class="col-sm-8"><input name="_1_u_resultado_positividade" id="comp_pos" type="number" size="2" value="<?= $_1_u_resultado_positividade ?>" style="float:left; width: 80px"></div>
				  </div>
				  <div class="col-sm-12">
					 <div class="col-sm-4"><span class="col-md-12" style="float:left;line-height: 16px;text-align: left;"><?= $nomealerta; ?>:</span>
						<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(marcado - <?= $alertarotuloy; ?>)</i></small>
						<small class="col-md-12" style="font-size:8px;text-align:left;"><i>(desmarcado - <?= $alertarotulon; ?>)</i></small>
					 </div>
					 <div class="col-sm-8"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="alertateste(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

				  </div>

				  <div class="col-sm-12">
					 <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;display: <?= $divdisplay ?>;">Tipo Alerta:</span></div>
					 <div class="col-sm-8">
						<div id="dTipoAlerta" style=" display: <?= $divdisplay ?>;">

						   <? //altera a busca pelo tipo do alerta hermesp 03-09-2020
						   $sqal = "select tipoalerta,tipoalerta
											from prodservtipoalerta 
											where idprodserv= " . $_1_u_resultado_idtipoteste . " 
											order by tipoalerta";
						   $resal = d::b()->query($sqal) or die("Falhou a buscar por configuração do alerta :" . mysqli_error() . "<br>Sql:" . $sqal);
						   $qtdal = mysqli_num_rows($resal);
						   if ($qtdal < 1) {
							  $sqal = "select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','HI POSITIVO-1,4[5],12:i:-'";
						   }
						   ?>

						   <select name="_1_u_resultado_tipoalerta" onchange="alertateste(<?= $_1_u_resultado_idresultado ?>);">
							  <option value=""></option>
							  <? fillselect($sqal, $_1_u_resultado_tipoalerta); ?>
							  <? //fillselect("select 'MG','MG' union select 'MS','MS' union select 'SE','SE' union select 'SG','SG' union select 'SP','SP' union select 'ST','ST' union select 'SPP','SPP'  union select 'HI POSITIVO','1,4[5],12:i:-'",$_1_u_resultado_tipoalerta);
							  ?>
						   </select>

						</div>
					 </div>
				  </div>
				  <div class="col-sm-12" style="display:<?= $divdisplayins ?>">
					 <div class="col-sm-8"><span style="float:left;line-height: 30px;text-align: left;">Mostra Insumo:</span></div>
					 <div class="col-sm-4"><input type="checkbox" id="chMostraInsumo" <?= $strmostrainsumo ?> onClick="mostraInsumo(<?= $_1_u_resultado_idresultado ?>);" style="float:left; "></div>

				  </div>
			   </div>
			</div>
		 </div>
		 <?
			   if ($modamostra == 'amostracqd' or $modamostra == 'amostraprod') { //Controle de qualidade
		 ?>
			<div style="background-color: #709ABE;padding: 4px;margin-top: 4px;">
			   <div class="row">
				  <div class="col-sm-12">
					 <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Resultado:</span></div>
					 <div class="col-sm-8"><input class="size15" name="_1_u_resultado_resultadocertanalise" type="text" class="" value="<?= $_1_u_resultado_resultadocertanalise ?>"></div>
				  </div>
				  <div class="col-sm-12">
					 <div class="col-sm-4"><span style="float:left;line-height: 30px;text-align: left;">Conclusão:</span></div>
					 <div class="col-sm-8">
						<select name="_1_u_resultado_conformidade">
						   <option value=""></option>
						   <? fillselect(array(
							  'CONFORME' => 'Conforme', 'NAO CONFORME' => 'Não Conforme', 'NAO SE APLICA' => 'Não se Aplica'
						   ), $_1_u_resultado_conformidade); ?>
						</select>
					 </div>
				  </div>
			   </div>
			</div>
		 <?
			   }
		 ?>
	  </div>
   </div>
<?
/*****************************************/
/*SE MODO FOR DROP AGRUPADO [FIM]
/*****************************************/
/*****************************************/
/*SE MODO FOR DROP INDIVIDUAL [INICIO]
/*****************************************/
} elseif (
	$_1_u_resultado_modelo == "DROP" and $_1_u_resultado_modo == "IND"

	//$_1_u_resultado_tipoespecial=="BRONQUITE IND" or $_1_u_resultado_tipoespecial=="NEWCASTLE IND" or $_1_u_resultado_tipoespecial=="GUMBORO IND" 
	//		or $_1_u_resultado_tipoespecial=="REOVIRUS IND" or $_1_u_resultado_tipoespecial=="PNEUMOVIRUS IND" or $_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"
	//		or $_1_u_resultado_tipoespecial=="DESCRITIVO IND"
) { //Se for tipo GMT
	$keyreadonly = "readonly"; //Esta variavel deixa as caixas de digitacao somente leitura. Para desabilitar, colocar valor ''
	/*	if($_1_u_resultado_tipoespecial=="PESAGEM" or $_1_u_resultado_tipoespecial=="ALFA"){
	$pesagem="Y";
}else{
	$pesagem="N";
}	 
*/
?>
   <br>
   <div align="center">
	  <?


			   $sqlind = "select * from resultadoindividual i where i.idresultado=" . $_1_u_resultado_idresultado;
			   $resind = mysql_query($sqlind) or die(" Erro ao buscar od resultados individuais 2:" . mysql_error . " <p>SQL" . $sqlind);
			   $x = 0;
			   $i = 2;
			   $tipoespecial = substr($_1_u_resultado_tipoespecial, 0, -4);
			   $tab = 0;
			   while ($rowind = mysql_fetch_assoc($resind)) {
				  $i = $i + 1;
				  $tab = $tab + 2;

				  if (($x % 7) == 0) {
	  ?>
			<div class="col-sm-4">
			   <div class="interna3">
				  <div class="row">
					 <div class="col-sm-4 text-right">
						ID
					 </div>

					 <div class="col-sm-7">
						<?= (($_1_u_resultado_tipogmt == "N/A") ? "RESULTADO" : "VALOR") ?>
					 </div>

				  </div>
			   </div>
			<?
				  }
				  $x = $x + 1;
			?>
			<div class="row divdescritivo">
			   <div class="col-sm-12">
				  <div class="interna2 ">
					 <div class="col-sm-1">
						<span style="line-height: 30px;"><?= $x ?></span>
					 </div>
					 <div class="col-sm-4">
						<div class="interna">
						   <input type="hidden" name="_<?= $i ?>_u_resultadoindividual_idresultadoindividual" value="<?= $rowind['idresultadoindividual'] ?>" size="3">
						   <input tabindex="<?= $tab ?>" style="width:100% !important; font-size:11px" type="text" title="Identificação" placeholder="ID" name="_<?= $i ?>_u_resultadoindividual_identificacao" value="<?= $rowind['identificacao'] ?>" size="10">
						</div>
					 </div>

					 <div class="col-sm-7">
						<div class="interna">
						   <select class="seltit" id="rotulo<?= $i ?>" title="Resultado" <?= $disabled2 ?> name="_<?= $i ?>_u_resultadoindividual_resultado" style="width:100% !important">
							  <option value=""></option>
							  <? fillselect(
								 "SELECT valor AS num, valor FROM prodservtipoopcao  where idprodserv = '" . $_1_u_resultado_idtipoteste . "' order by valor*1",
								 $rowind['resultado']
							  ); ?>
						   </select>




						</div>
					 </div>


				  </div>
			   </div>
			</div>
			<?
				  if (($x % 7) == 0) {
			?>
			</div>
		 <?
				  }
			   }
			   if (($x % 7) != 0) {
		 ?>
   </div>
   </div>
<?
}

$stralerta = "";
if ($_1_u_resultado_alerta == "Y") {
	$stralerta = "checked";
	$divdisplay = "block";
} else {
	$divdisplay = "none";
}

$strmostrainsumo = "";
if ($_1_u_resultado_mostraformulains == "Y") {
	$strmostrainsumo = "checked";
	$divdisplayins = "block";
} else {
	$divdisplayins = "none";
}
?>
<div class="col-sm-4">
   <? if ($_1_u_resultado_tipogmt != "N/A") { ?>
	  <div class="interna3" style="background-color:#709ABE;height: 45px;">
		 <div class="row">
			<div class="col-sm-12">
			   <div style="padding-left: 6px;padding-right: 6px; text-align: left">
				  GMT <span style="float:right"><?= $_1_u_resultado_gmt ?></span>
			   </div>
			</div>
		 </div>
	  </div>
   <? } ?>
   <div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
	  <div class="row">
		 <div class="col-sm-12">
			<div style="padding-left: 6px;padding-right: 6px;line-height: 30px; text-align: left">
			   Alerta <span style="float:right"><input type="checkbox" id="chAlerta" <?= $stralerta ?> onClick="alertateste(<?= $_1_u_resultado_idresultado ?>);"></span>
			</div>
		 </div>
	  </div>
   </div>
</div>
</div>
<?
   /*****************************************/
   /*SE MODO FOR DROP INDIVIDUAL [FIM]
	/*****************************************/
   /*****************************************/
   /*SE MODO FOR UPLOAD [INICIO]
	/*****************************************/
} elseif ($_1_u_resultado_modelo == "UPLOAD") {
	$strsql = "SELECT * FROM resultadoelisa where idresultado = " . $_1_u_resultado_idresultado . "  and status = 'A' order by idresultadoelisa";
	$result = d::b()->query($strsql) or die("A Consulta  dos resultados elisa falhou : " . mysqli_error() . "<p>SQL: $strsql");
	$iresult  = mysqli_num_rows($result);
	if ($iresult > 0) {
?>

	<div class="row" style="background: #bbb;margin: 0;">
		<div class="col-xs-1 text-right">
			&nbsp;
		</div>
		<div class="col-xs-2 text-right">
			Wells
		</div>
		<div class="col-xs-1 text-right">
			O.D.
		</div>
		<div class="col-xs-2 text-right">
			I.E.
		</div>
		<div class="col-xs-1 text-right">
			S/P
		</div>
		<div class="col-xs-1 text-right">
			S/N
		</div>
		<div class="col-xs-1 text-right">
			Titer
		</div>
		<div class="col-xs-1 text-right">
			Group
		</div>
		<div class="col-xs-2 text-center">
			Result
		</div>
		</div>
		 <div class="striped">
		 <? while($row = mysqli_fetch_assoc($result)) {?>
			<div class="row" style="margin: 0;">
				  <div class="col-xs-1 text-right">
					 <?= $row['nome'] ?>
				  </div>
				  <div class="col-xs-2 text-right">
					 <?= $row['well'] ?>
				  </div>
				  <div class="col-xs-1 text-right">
					 <?= $row['OD'] ?>
				  </div>
				  <div class="col-xs-2 text-right">
					 <?= $row['IE'] ?>
				  </div>
				  <div class="col-xs-1 text-right">
					 <?= $row['SP'] ?>
				  </div>
				  <div class="col-xs-1 text-right">
					 <?= $row['SN'] ?>
				  </div>
				  <div class="col-xs-1 text-right">
					 <?= $row['titer'] ?>
				  </div>
				  <div class="col-xs-1 text-right">
					 <?= $row['grupo'] ?>
				  </div>
				  <div class="col-xs-2 text-center">
					 <?= $row['result'] ?>
				  </div>
			</div>
		 <? } ?>
		 </div>

   <br>
<?
	}
	$sql1 = "select concat(a.idregistro,p.codprodserv) as nomearqui,concat(a.idregistro,p.codprodserv) as nomearquivortf
			from resultado r,amostra a,prodserv p
			where p.idprodserv = r.idtipoteste
			and  a.idamostra = r.idamostra
			and r.idresultado =" . $_1_u_resultado_idresultado;
		$res1 = d::b()->query($sql1) or die("Erro ao buscar o nome do arquivo");
		$row1 = mysqli_fetch_assoc($res1);
?>
<div class="row" style="margin: 0;display: flex;align-items: center;">
	<div class="col-xs-12 col-sm-6 col-md-6" style="margin: 0;">
	<div class="row" style="margin: 0;">
	  <div class="col-md-4">Selecione o kit:</div>
	  <div class="col-md-8"><select id="tipokit" name="tipokit" onchange="settipokit(this,<?= $_1_u_resultado_idresultado ?>)">
			<? fillselect("select 'IDEXX-LAUDO','IDEXX-LAUDO' union select 'IDEXX','IDEXX' union select 'AFFINITECK','AFFINITECK' union select 'BIOCHEK','BIOCHEK'", $_1_u_resultado_tipokit); ?> </select>
	  </div>
	</div>
	<div class="row" style="margin: 0;display: flex;align-items: center;">
	  <div class="col-md-4">Nome Arquivo Padrão:</div>
	  <div class="col-md-8"> 
		 <span id="nome_arquivo_txt">
			 <label class="alert-warning"><a class="copy" onclick="copyToClipboard(this)"><?= $row1['nomearquivortf'] ?>.txt <i class="fa fa-clipboard"></i></a></label> (AFFINITECK / BIOCHEK)
		</span>
		<span id="nome_arquivo_rtf">
			<label class="alert-warning"><a class="copy" onclick="copyToClipboard(this)"><?= $row1['nomearquivortf'] ?>.RTF <i class="fa fa-clipboard"></i></a></label> (IDEXX / IDEXX-LAUDO)
		</span>
	  </div>
	</div>
   </div>
   
   <script>
	function copyToClipboard(element) {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val($(element).text()).select();
		document.execCommand("copy");
		$temp.remove();
		alertAzul('Copiado para a área de trabalho &nbsp&nbsp');
	}
	function change_arquivo(){
		if($('#tipokit').val()=='IDEXX-LAUDO' || $('#tipokit').val()=='IDEXX'){
			$('#nome_arquivo_txt').hide();
			$('#nome_arquivo_rtf').show();
		}else{
			$('#nome_arquivo_txt').show();
			$('#nome_arquivo_rtf').hide();
		}
	}
	$('#tipokit').on('change', ()=>{
		change_arquivo();
	});
	change_arquivo();
   </script>

   <div class="col-xs-12 col-sm-6 col-md-6">
	  <div class="cbupload" id="resultadoelisa" title="Clique ou arraste o arquivo Elisa para cá." style="width:100%;height:100%;">
		 <i class="fa fa-cloud-upload fonte18"></i>
	  </div>
   </div>
</div>
<?
}
	/*****************************************/
	/*SE MODO FOR UPLOAD [FIM]
	/*****************************************/

   if (($modamostra == 'amostratra' or $modamostra == 'amostraautogenas') and !empty($_1_u_resultado_idresultado)) { //diag autogenas
?>

   <div class="col-sm-4">
	  <div class="interna3" style="background-color:#ccc;height: 45px;">
		 <div class="row">
			<div class="col-sm-12">
			   <div style="padding-left: 6px;padding-right: 6px;">
				  <?
				  if ($geraagente == "Y") {
				  ?>
					 Agente:<i class="fa fa-plus-circle fa-1x verde btn-lg pointer" title="Criar um agente" onclick="inovolote(<?= $_1_u_resultado_idresultado ?>)"></i>
				  <?
				  } else {
				  ?>
					 Agente:<i class="fa fa-exclamation-triangle vermelho fa-1x btn-lg pointer" title="Este teste não esta configurado para gerar sementes. Configurar a opção no cadastro de produtos e serviços."></i>
				  <?


				  }
				  //buscar agentes gerados
				  $sqll = "select p.descr,o.idobjeto,l.idlote,l.exercicio,l.partida,p.descr,l.criadopor,l.criadoem,l.tipificacao
	  		from lote l join prodserv p 
				left join unidadeobjeto o on(o.tipoobjeto='modulo' and o.idobjeto like ('lote%') and o.idunidade = l.idunidade)
	  		where p.idprodserv = l.idprodserv 
	  		and l.tipoobjetosolipor='resultado' 
	  		and l.idobjetosolipor=" . $_1_u_resultado_idresultado . " group by l.idlote";
				  //die($sqll);
				  $resl = d::b()->query($sqll) or die(mysqli_error(d::b()));
				  $qtdrowl = mysqli_num_rows($resl);
				  if ($qtdrowl > 0) {
				  ?>
					 <div id="resultadoagente<?= $_1_u_resultado_idresultado ?>" style="display: none">
						<div id="cbModuloResultados" class="col-md-12 zeroauto panel panel-default">
						   <table class="table table-hover table-striped table-condensed">
							  <tr>
								 <td>Lote</td>
								 <td>Produto</td>
								 <td>Criado por</td>
								 <td>Criado em</td>
							  </tr>
							  <?
							  $k = 0;
							  $arrAgentes = array();
							  while ($rl = mysqli_fetch_assoc($resl)) {
								 $arrAgentes[$k]["agente"] = $rl['partida'];
								 $arrAgentes[$k]["idlote"] = $rl['idlote'];
								 $arrAgentes[$k]["tipificacao"] = $rl['tipificacao'];
								 $arrAgentes[$k]["exercicio"] = $rl['exercicio'];
								 $k++;
							  ?>
								 <tr onclick="janelamodal('?_modulo=semente&_acao=u&idlote=<?= $rl['idlote'] ?>');">
									<td><?= $rl['partida'] ?></td>
									<td><?= $rl['descr'] ?></td>
									<td><?= $rl['criadopor'] ?></td>
									<td><?= dmahms($rl['criadoem']) ?></td>
								 </tr>
							  <? } ?>
						   </table>
						</div>
					 </div>
					 <i class="fa fa-cubes fa-1x azul btn-lg pointer" title="Agente(s) isolados" onclick="listalote(<?= $_1_u_resultado_idresultado ?>)"></i>
				  <? } else {
					 $arrAgentes = 0;
				  }

				  if (is_array($arrAgentes)) {
					 $arrAgentes = (count($arrAgentes) == 0) ? json_encode([]) : json_encode($arrAgentes);
				  } else {
					 $arrAgentes = 0;
				  }
				  ?>
			   </div>
			</div>
		 </div>
	  </div>
   </div>

<?
}
?>
<div class="panel-body">
   <br>
   <?

	$sqlf1 = "select *
	from prodservformula
	where  idprodserv=" . $_1_u_resultado_idtipoteste . "
	order by ordem,idprodservformula asc";

	$resf1 = d::b()->query($sqlf1) or die("E ao buscas as fazes do servico \n" . mysqli_error(d::b()) . "\n" . $sqlf1);

   ?>
   <style>
	  .itemestoque {
		 Xwidth: 100%;
		 width: auto;
		 display: inline-block;
		 text-align: right;
	  }
   </style>
   <?

	//Pega a Unidade que está setada no ProdServ - Lidiane (09-04-2020)
	$pagvalmodulo = $_GET['_modulo'];
	$sqlunobj = "SELECT idunidade FROM " . _DBCARBON . "._modulo m INNER JOIN  laudo.unidadeobjeto o ON m.modulo = o.idobjeto WHERE modulo = '" . $pagvalmodulo . "' AND tipoobjeto = 'modulo';";
	$respunobj = d::b()->query($sqlunobj) or die("Erro ao buscar resultado unidadeobjeto  do teste:" . mysqli_error(d::b()) . "sql=" . $sqlunobj);
	while ($rowunobj = mysqli_fetch_assoc($respunobj)) {
		$unidadeobjeto .= $rowunobj['idunidade'];
	}
	// campo de Informações Adicionais.
	if (in_array($_1_u_resultado_modelo, ['DROP', 'UPLOAD', 'SELETIVO'])) { ?>
	<div class="row" style="margin: 0;">
	  <div class="col-sm-12">
		 <b>Informações Adicionais:</b>
		 <textarea style="min-height: 150px;" name="_1_u_resultado_observacao"><?= $_1_u_resultado_observacao ?></textarea>
	  </div>
   </div>
	<? }

	$l = $_1_u_resultado_idresultado;
	while ($rowf1 = mysqli_fetch_assoc($resf1)) {
			   if ($_1_u_resultado_status == 'ASSINADO' or $_1_u_resultado_status == 'FECHADO') {
				  $sqlf = "select  p.descr,c.qtdd as qtdi, c.qtdd_exp as qtdi_exp,p.idprodserv
						   from lote l 
							  join lotecons c on (c.idlote= l.idlote and c.tipoobjeto ='resultado' )                                
							  join lotefracao f on(f.idlote=l.idlote and f.idlotefracao=c.idlotefracao)
							  JOIN unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade)
							  JOIN  " . _DBCARBON . "._modulo m on m.modulo = o.idobjeto and m.modulotipo = 'lote'
							  join prodserv p on(p.idprodserv=l.idprodserv)
						   where  c.idobjeto=" . $_1_u_resultado_idresultado . " 
							  and c.qtdd>0 
						   group by p.idprodserv";
			   } else {
				  $sqlf = "select p.descr,i.qtdi,qtdi_exp,i.idprodserv
					 from prodserv p,prodservformula f, prodservformulains i 
					 where p.idprodserv = i.idprodserv
					 and f.idprodserv = " . $_1_u_resultado_idtipoteste . "
					 and i.idprodservformula = f.idprodservformula
					 and f.idprodservformula=" . $rowf1["idprodservformula"] . "
					 and f.status='ATIVO'
					 and i.status='ATIVO'
					 order by i.ord asc";
			   }

			   $resf =  d::b()->query($sqlf) or die("Erro ao buscar produtos  do teste:" . mysqli_error(d::b()) . "sql=" . $sqlf);

			   $qtdf = mysqli_num_rows($resf);

			   if ($qtdf > 0) {

				  $sqlpf = "select * from resultadoprodservformula where idresultado = " . $_1_u_resultado_idresultado;
				  $respf = d::b()->query($sqlpf) or die("Erro 1 ao buscar resultado prodservformula  do teste:" . mysqli_error(d::b()) . "sql=" . $sqlpf);
				  $qtdmostra1 = mysqli_num_rows($respf);

				  if ($qtdmostra1 < 1 and ($_1_u_resultado_status != 'ASSINADO' or $_1_u_resultado_status != 'FECHADO')) {
					 $sqlin = " INSERT INTO resultadoprodservformula
						(idempresa,idresultado,idprodservformula,criadopor,criadoem)
						VALUES
						(" . $_SESSION["SESSAO"]["IDEMPRESA"] . "," . $_1_u_resultado_idresultado . "," . $rowf1["idprodservformula"] . ",'" . $_SESSION["SESSAO"]["USUARIO"] . "',sysdate())";

					 $resin =  d::b()->query($sqlin) or die("Erro ao a primeira fase do teste:" . mysqli_error(d::b()) . "sql=" . $sqlin);
				  }
	  ?>
		 <div class="row">
			<div class="col-md-12">
			   <div class="panel panel-default">
				  <div class="panel-heading">Insumos do teste Fase:<?= $rowf1["ordem"] ?>
					 <?
					 $sqlpf = "select * from resultadoprodservformula where idresultado = " . $_1_u_resultado_idresultado . " and status ='ATIVO' and idprodservformula =" . $rowf1["idprodservformula"];
					 $respf = d::b()->query($sqlpf) or die("Erro 2 ao buscar resultado prodservformula  do teste:" . mysqli_error(d::b()) . "sql=" . $sqlpf);
					 $qtdmostra = mysqli_num_rows($respf);

					 if ($qtdmostra > 0) {
						$rowresprod = mysqli_fetch_assoc($respf);
					 ?>
						<input title="Retirar fase" checked="checked" type="checkbox" name="retirarfase" onclick="dfase(<?= $rowresprod["idresultadoprodservformula"] ?>);">
					 <?
					 } else {
					 ?>
						<input title="Inserir fase" type="checkbox" name="inserirfase" onclick="ifase(<?= $_1_u_resultado_idresultado ?>,<?= $rowf1["idprodservformula"] ?>);">
					 <?
					 }
					 ?>
				  </div>
				  <div class="panel-body">
					 <?
					 if ($qtdmostra > 0) {
					 ?>
						<table class="table table-striped planilha">
						   <tr>
							  <? if ($_1_u_resultado_status != 'ASSINADO' and $_1_u_resultado_status != 'FECHADO') { ?>
								 <th>Utilizar</th>
							  <? } ?>
							  <th>Produto</th>

							  <th>Lotes</th>
							  <th>Utilizando</th>
							  <? if ($_1_u_resultado_status != 'ASSINADO') { ?>
								 <th>Restante</th>
								 <th></th>
							  <? } ?>
						   </tr>
						   <?
						   while ($rowf = mysqli_fetch_assoc($resf)) {
							  if ($_1_u_resultado_status == 'ASSINADO' or $_1_u_resultado_status == 'FECHADO') {

								 $sqlca = "select l.partida,o.idobjeto,f.idlotefracao,l.exercicio,l.idlote,f.qtd as qtddisp,f.qtd_exp as qtddisp_exp,c.idlotecons,c.qtdd,c.qtdd_exp,l.status
							from lote l join lotecons c on (c.idlote= l.idlote and c.tipoobjeto ='resultado' and c.idobjeto=" . $_1_u_resultado_idresultado . " and c.qtdd>0)
								join lotefracao f on(f.idlote=l.idlote and f.idunidade=" . $unidadepadrao . ")
								 JOIN unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade)
									JOIN " . _DBCARBON . "._modulo m on m.modulo = o.idobjeto and m.modulotipo = 'lote'
							where l.idprodserv =" . $rowf["idprodserv"];


								 $resca =  d::b()->query($sqlca) or die("Erro ao buscar atribuicoes dos lotes no resultado assinado:" . mysqli_error(d::b()) . "sql=" . $sqlca);
								 $qtdca = mysqli_num_rows($resca);
								 $qtdimput = $rowf['qtdi'] * $_1_u_resultado_quantidade;
						   ?>
								 <tr>

									<td class='nowrap'><?= $rowf['descr'] ?> <a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&_acao=u&idprodserv=<?= $rowf['idprodserv'] ?>" target="_blank"></a></td>
									<? if ($qtdca < 1) { ?>
									   <td>Não foi encontrado lote disponivel!!!</td>

									<? } else { ?>
									   <td>
										  <?
										  $utilizando = 0;
										  while ($rowca = mysqli_fetch_assoc($resca)) {
											 $utilizando = $rowca['qtdd'] + $utilizando;
										  ?>
											 <span class="label label-primary fonte10 itemestoque" qtddisp="<?= tratanumero($rowca['qtddisp']) ?>" qtddispexp="" idlote="<?= $rowca['idlote'] ?>" data-toggle="tooltip" title="" data-original-title="<?= $rowca['partida'] ?>">
												<a class="branco hoverbranco" href="?_modulo=<?= $rowca['idobjeto'] ?>&_acao=u&idlote=<?= $rowca['idlote'] ?>" target="_blank"><?= $rowca['partida'] ?>/<?= $rowca['exercicio'] ?></a>
												<span class="badge pointer screen" idlote="<?= $rowca['idlote'] ?>" onclick="janelamodal('?_modulo=<?= $rowca['idobjeto'] ?>&_acao=u&idlote=<?= $rowca['idlote'] ?>')"><?= tratanumero($rowca['qtddisp']) ?></span>
												<? if ($rowca['status'] != 'ESGOTADO') { ?>
												   <a title="Esgotar Lote." class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="esgotarlote(<?= $rowca['idlotefracao'] ?>)"></a>
												<? } ?>
												<input type="text" name="<?= $act ?>qtdd" value="<?= $rowca['qtdd'] ?>" class="reset screen" cbqtddispexp="" style="width: 80px !important; background-color: white;" onkeyup="mostraConsumo(this)" readonly="readonly">
											 </span>
										  <?
										  } //
										  ?>
									   </td>
									   <td align="right"><span class="badge"> <?= tratanumero($utilizando) ?></span></td>

									<? } //if($qtdca<1){       

								 } else { // if($_1_u_resultado_status=='ASSINADO'){

									$sqlc = "select l.partida,f.idlotefracao,l.exercicio,o.idobjeto,l.idlote,f.qtd as qtddisp,f.qtd_exp as qtddisp_exp,c.idlotecons,c.qtdd,c.qtdd_exp 
							from lote l  join lotefracao f on(f.idlote = l.idlote  and f.idunidade=" . $unidadepadrao . ")
							left join lotecons c on (c.idlote= l.idlote and c.tipoobjeto ='resultado' and c.idobjeto=" . $_1_u_resultado_idresultado . ")
								join unidade u on(f.idunidade=u.idunidade )
								
								JOIN unidadeobjeto o FORCE INDEX (TIPOOBJETOUNIDADE) ON (o.tipoobjeto = 'modulo' AND f.idunidade = o.idunidade)
								JOIN " . _DBCARBON . "._modulo m on m.modulo = o.idobjeto and m.modulotipo = 'lote'
							where l.idprodserv =" . $rowf["idprodserv"] . " 
							   
							and l.status ='APROVADO'
							and ((f.status ='DISPONIVEL') or exists (select 1 from lotecons cc where cc.idlote = l.idlote AND cc.idlotefracao=f.idlotefracao and c.tipoobjeto ='resultado' and c.idobjeto=" . $_1_u_resultado_idresultado . ") and c.qtdd>0)";
									// echo($sqlc);

									$resc =  d::b()->query($sqlc) or die("Erro ao buscar atribuicoes dos lotes:" . mysqli_error(d::b()) . "sql=" . $sqlc);
									$qtdc = mysqli_num_rows($resc);
									$qtdutilizar = $rowf['qtdi'] * $_1_u_resultado_quantidade;
									$qtdimput = $rowf['qtdi'] * $_1_u_resultado_quantidade;
									?>
								 <tr class="trInsumo">
									<td align="right"><span class="badge sQtdpadrao"><?= tratanumero($qtdutilizar) ?></span></td>
									<td class='nowrap'><?= $rowf['descr'] ?> <a title="Abrir cadastro produto" class="fa fa-bars fade pointer hoverazul" href="?_acao=u&_modulo=prodserv&_acao=u&idprodserv=<?= $rowf['idprodserv'] ?>" target="_blank"></a> </td>
									<? if ($qtdc < 1) { ?>
									   <td>Não foi encontrado lote disponivel!!!</td>

									<? } else { ?>
									   <td id="insumos<?= $rowf["idprodserv"] ?>">
										  <?

										  $qtdusando = 0;
										  while ($rowc = mysqli_fetch_assoc($resc)) {
											 $l = $l + 1;
											 $act = $l;
											 $novo = 'Y';
											 if (!empty($rowc['idlotecons'])) {
												$act = '_cons' . $l . '_u_lotecons_';
												$qtdimput = $qtdimput - $rowc['qtddisp'];
												$novo = 'N';
											 } elseif ($rowc['qtddisp'] > 0 and $qtdimput > 0 and empty($rowc['qtdd'])) {
												if ($rowc['qtddisp'] < $qtdimput) {
												   $rowc['qtdd'] = $rowc['qtddisp'];
												   $qtdimput = $qtdimput - $rowc['qtddisp'];
												   $act = '_cons' . $l . '_i_lotecons_';
												} else {
												   $rowc['qtdd'] = $qtdimput;
												   $qtdimput = 0;
												   $act = '_cons' . $l . '_i_lotecons_';
												}
												$novo = 'N';
											 }
											 if ($rowc['qtddisp'] <= 0 and $rowc['qtdd'] <= 0) {
												$readonlyzest = "readonly='readonly'";
											 } else {
												$readonlyzest = "";
											 }
											 $qtdusando = $rowc['qtdd'] + $qtdusando;
										  ?>



											 <span class="label label-primary fonte10 itemestoque" qtddisp="<?= tratanumero($rowc['qtddisp']) ?>" qtddispexp="" idlote="<?= $rowc['idlote'] ?>" data-toggle="tooltip" title="" data-original-title="<?= $rowc['partida'] ?>">
												<a class="branco hoverbranco" href="?_modulo=<?= $rowc["idobjeto"] ?>&_acao=u&idlote=<?= $rowc['idlote'] ?>" target="_blank"><?= $rowc['partida'] ?>/<?= $rowc['exercicio'] ?></a>
												<span class="badge pointer screen" idlote="<?= $rowc['idlote'] ?>" onclick="janelamodal('?_modulo=<?= $rowc['idobjeto'] ?>&_acao=u&idlote=<?= $rowc['idlote'] ?>')"><?= tratanumero($rowc['qtddisp']) ?></span>
												<? if ($rowca['status'] != 'ESGOTADO') { ?>
												   <a title="Esgotar Lote." class="fa fa-minus-circle pointer branco hoververmelho fa-1x" onclick="esgotarlote(<?= $rowc['idlotefracao'] ?>)"></a>
												<? } ?>
												<input <?= $readonlyzest ?> type="text" name="<?= $act ?>qtdd" value="<?= $rowc['qtdd'] ?>" class="reset screen" cbqtddispexp="" style="width: 80px !important; background-color: white;" onkeyup="mostraConsumo(this)" <? if ($novo == 'Y') { ?> onchange="atualizainput(<?= $l ?>)" <? } ?>>
												<input type="hidden" name="<?= $act ?>idlotecons" value="<?= $rowc['idlotecons'] ?>">
												<input type="hidden" name="<?= $act ?>tipoobjeto" value="resultado">
												<input type="hidden" name="<?= $act ?>idobjeto" value="<?= $_1_u_resultado_idresultado ?>">
												<input type="hidden" name="<?= $act ?>idlote" value="<?= $rowc['idlote'] ?>">
												<input type="hidden" name="<?= $act ?>idlotefracao" value="<?= $rowc['idlotefracao'] ?>">
											 </span>


										  <?
										  } //
										  $restante = $qtdutilizar - $qtdusando;
										  if ($restante > 0) {
											 $fundo = "fundolaranja";
										  } else {
											 $fundo = "fundoverde";
										  }
										  ?>
									   </td>
									   <td>
										  <span class="badge  sUtilizando <?= $fundo ?>"><?= $qtdusando ?></span>
									   </td>
									   <td>
										  <span class="badge sRestante <?= $fundo ?>"><?= $restante ?></span>
									   </td>
								 <?  } //if($qtdc<1){
								 } //if($_1_u_resultado_status=='ASSINADO'){   
								 ?>

								 </tr>
							  <?
						   } //while($rowf= mysqli_fetch_assoc($resf)){
							  ?>
						</table>
					 <?
					 } //if($qtdmostra>0){
					 ?>
				  </div>

			   </div>


			</div>

		 </div>
   <?
			   } //if($qtdf>0){
			} //while($rowf1=mysqli_fetch_assoc($resf1)){
   ?>
   <br>
</div>
<br>
<input name="_1_u_resultado_status" type="hidden" style="width: 10px;" value="<?= $_1_u_resultado_status ?>">
<!--div class="panel-body">
<div align="center" style="background: #ccc; height: 50px; line-height: 44px;">
   <table style="margin: 0px; padding: 0px;">
	   <tr><td>** Alterar Status: </td>
		 <td>
			
			<select style="dispdlay: none;" name="_1_u_resultado_status">
			   <option value="ABERTO">ABERTO</option>
			   <option value="PROCESSANDO">PROCESSANDO</option>
			   <option value="FECHADO" selected>FECHADO</option>
			</select>
			<!--
			   <select style="dispdlay: none;" name="_1_u_resultado_status">
			   <? //fillselect(array("ABERTO"=>"Aberto","PROCESSANDO"=>"Processando","FECHADO"=>"Fechado"),$_1_u_resultado_status)
			   ?>
			   </select>
			   -->
<!--/td>
	  </tr>
   </table>
</div>
</div-->
</fieldset>
<?
		 }
?>
</tr>
</table>
</div>
</div>
</div>
<p>
   <!--div  class="panel-body">
   <div class="col-md-2"></div>
   <div class="col-md-10">
	  <div class="cbupload" id="arquivoresultado" title="Clique ou arraste arquivos para cá" style="width:50%;height:100%;">
		 <i class="fa fa-cloud-upload fonte18"></i>
	  </div>
   </div>
</div-->
<p>
   <?
   if (!empty($_1_u_resultado_idresultado)) { // trocar p/ cada tela a tabela e o id da tabela
	  $_idModuloParaAssinatura = $_1_u_resultado_idresultado; // trocar p/ cada tela o id da tabela
	  require 'viewAssinaturas.php';
   }
   $tabaud = "resultado"; //pegar a tabela do criado/alterado em antigo
   $idRefDefaultDropzone = "arquivoresultado";
   require 'viewCriadoAlterado.php';
   ?>
<div id="novolote" style="display: none">
   <div class="interna2" style="background-color:#ccc; margin-top: 6px !important; margin: 3px;">
	  <div class="row">
		 <div class="col-md-12">
			<div class="panel panel-default">
			   <div class="panel-heading">
				  <table>
					 <tr>
						<td align="right"><strong>Agente:</strong></td>
						<td>
						   <?
						   $sqlag = "select * from objetovinculo where idobjeto = " . $_1_u_resultado_idtipoteste;
						   $resc =  d::b()->query($sqlag) or die("Erro ao buscar agentes vinculados:" . mysqli_error(d::b()) . "sql=" . $sqlag);
						   $qtdc = mysqli_num_rows($resc);
						   if ($qtdc > 0) {
							  $select = "select
						p.idprodserv,ifnull(p.descrcurta,p.descr) as descr
						from objetovinculo o join prodserv p on(p.idprodserv = o.idobjetovinc)
						where o.idobjeto=" . $_1_u_resultado_idtipoteste . " and o.tipoobjeto='prodserv'";
						   } else {
							  $select = "select p.idprodserv,p.descr 
						from prodserv p join  unidadeobjeto u on( u.idunidade = 9 and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
						where p.tipo = 'PRODUTO'
						and p.status = 'ATIVO' 
						and p.especial='Y'
						 order by p.descr";
						   }
						   ?>
						   <select class="size30" id="idprodservlote" name="">
							  <option></option>
							  <? fillselect($select); ?>
						   </select>
						   <input id="idlotelote" name="" type="hidden" value="">
						   <input id="statuslote" name="" type="hidden" value="ABERTO">
						   <input id="idunidadegplote" name="" type="hidden" value="2">
						   <input id="exerciciolote" name="" type="hidden" value="<?= date("Y") ?>">
						   <input id="tipoobjetolote" name="" type="hidden" value="resultado">
						   <input id="idobjetolote" name="" type="hidden" value="">
						</td>
						<td>Qtd:</td>
						<td>
						   <input class="size5" id="qtdprod" name="" type="number" value="5" vnulo>
						</td>
					 </tr>
					 <tr>
						<td align="right"><strong>Orgão:</strong></td>
						<td>
						   <input id="orgao" name="" type="text" value="">
						</td>
					 </tr>
				  </table>
			   </div>
			</div>
		 </div>
	  </div>
   </div>
</div>
<?

function jsonServicos()
{
   global $jsonServicos, $unidadepadrao;

   $sql = "select idprodserv,trim(concat(codprodserv,' - ',descr)) as descr
			from prodserv p join unidadeobjeto u on( u.idunidade = " . $unidadepadrao . " and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
			where  p.status = 'ATIVO'
				and p.tipo='SERVICO'			
			order by trim(concat(codprodserv,' - ',descr))";

   $res = d::b()->query($sql);

   $arrtmp = array();
   $i = 0;
   while ($r = mysqli_fetch_assoc($res)) {
	  $arrtmp[$i]["value"] = $r["idprodserv"];
	  $arrtmp[$i]["label"] = $r["descr"];
	  $i++;
   }

   $jsonServicos = json_encode($arrtmp);
}
jsonServicos();

?>
<script>
   jsonServicos = <?= $jsonServicos ?>;
   criaAutocompletesTestes();

   function novoTeste() {

	  oTbTestes = $("#tbTestes");
	  iNovoTeste = (oTbTestes.find("input.idprodserv").length + 1);
	  htmlTrModelo = $("#modeloNovoTeste").html();

	  htmlTrModelo = htmlTrModelo.replace("#nameidamostra", "" + iNovoTeste + "#idamostra");
	  htmlTrModelo = htmlTrModelo.replace("#namestatus", "" + iNovoTeste + "#status");
	  htmlTrModelo = htmlTrModelo.replace("#nameidtipoteste", "" + iNovoTeste + "#idtipoteste");
	  htmlTrModelo = htmlTrModelo.replace("#namequantidade", "" + iNovoTeste + "#quantidade");
	  htmlTrModelo = htmlTrModelo.replace("#nameidsecretaria", "" + iNovoTeste + "#idsecretaria");

	  htmlTrModelo = htmlTrModelo.replace(/#irow/g, iNovoTeste);

	  novoTr = "<div>" + htmlTrModelo + "</div>";
	  oTbTestes.append(novoTr);
	  criaAutocompletesTestes();
	  $("#novoTestesalvar").css("display", "inline-block");

   }
   //Autocomplete de Servicos (testes)
   function criaAutocompletesTestes() {
	  $("#tbTestes .idprodserv").autocomplete({
		 source: jsonServicos,
		 delay: 0
	  });

   }


   function adicionarTestes() {
	  //LTM - 07-04-2021 - Retorna o idFluxoStatus Selecionado
	  var idfluxostatus = getIdFluxoStatus('<?=$pagvalmodulo?>', 'ABERTO');

	  //Daí use o $.each com 2 parâmetros: ([coleção de objetos] + [function])
	  jPost = {}; //Vou usar json ao invés de string. Fica mais bonito, elegante, sincero
	  $.each($("#tbTestes .formTmp"), function(i, o) {
		 $o = $(o); //Transforma o elemento HTML em obj Jquery
		 var aName = $o.attr("name").split("#"); //Transforma o name em um array
		 console.log(aName);

		 // console.log($o.hasAttribute("cbvalue"));
		 if ($o.attr('cbvalue')) {
			jPost["_" + aName[0] + "_i_resultado_" + aName[1]] = $o.attr('cbvalue'); //Monta o obj key/value com os valores dos elementos
		 } else {
			jPost["_" + aName[0] + "_i_resultado_" + aName[1]] = $o.val(); //Monta o obj key/value com os valores dos elementos
		 }

		 jPost["_" + aName[0] + "_i_resultado_idfluxostatus"] = idfluxostatus; //Monta o obj key/value com os valores dos elementos

		 var idempresa = (getUrlParameter("_idempresa")) ? getUrlParameter("_idempresa") : '';

		 if (idempresa) {
			jPost["_" + aName[0] + "_i_resultado_idempresa"] = idempresa; //Monta o obj key/value com os valores dos elementos
		 }
		 console.log($o.val());
		 // jPost["_"+aName[0]+"_i_resultado_"+aName[1]]=$o.val();//Monta o obj key/value com os valores dos elementos
	  });

	  CB.post({
		 objetos: jPost,
		 parcial: true,
		 msgSalvo: "Teste(s) adicionado(s)"
	  });
   }

   var idResultado = Number(<?= $_1_u_resultado_idresultado ?>);
   var pageStateChanged = false; //Teste se a pagina sofreu alteracoes
   var vModelo = "<?= $_1_u_resultado_modelo ?>";
   var vModo = "<?= $_1_u_resultado_modo ?>";

   //Variáveis para soro
   var arrKeyConf = new Array();
   var arrKeyConf = new Array();

   var qtx = parseInt(<?= $qtx ?>); //A soma dos orificios deve ser <= a este valor
   var xoper = "+";
   let vinculados = <?= $vinculados?$vinculados:'[]'; ?>;
 
   vinculados = jQuery.map(vinculados, function(o, id) {
		return {
			"idobjeto": o
		}
	});
	
   //Colocar alerta nos testes
   function alertateste(inIdresultado, inChk) {

	  sTipoalerta = $("[name=_1_u_resultado_tipoalerta]").val();
	  let idfluxostatus = getIdFluxoStatus('<?=$pagvalmodulo?>', 'ABERTO');

	  if ($("#chAlerta").is(':checked')) {
		CB.post({
			"objetos": "_x_u_resultado_idresultado=" + inIdresultado + "&_x_u_resultado_alerta=Y&_x_u_resultado_tipoalerta=" + sTipoalerta,
			parcial: true,
			posPost: ()=>{
				if(vinculados.length){
					alert(""+vinculados.length+" serviço(s) vínculado(s) a esse resultado positivo serão incluídos.");
					array = [];
					vinculados.map((obj,i)=>{
						i=i+1;
						array["_"+i+"_i_resultado_idamostra"] = <?=$_1_u_resultado_idamostra?>;
						array["_"+i+"_i_resultado_idtipoteste"] = obj.idobjeto;
						array["_"+i+"_i_resultado_quantidade"] = 1;
						array["_"+i+"_i_resultado_idempresa"] = <?=cb::idempresa()?>;
						array["_"+i+"_i_resultado_idfluxostatus"] = idfluxostatus;
						array["_"+i+"_i_resultado_status"] = 'ABERTO';
						array["_"+i+"_i_resultado_idsecretaria"] = 0;
						array["_"+i+"_i_resultado_jsonresultado"] = '{}';
					})
					console.log(array);
						CB.post({
						"objetos": $.extend({}, array),
						parcial: true
					});
				}	
			}
		});
	  } else {
		 CB.post({
			"objetos": "_x_u_resultado_idresultado=" + inIdresultado + "&_x_u_resultado_alerta=N&_x_u_resultado_tipoalerta=" + sTipoalerta,
			parcial: true
		 });
	  }
   }
   //Mostra Insumo no Resultado
   function mostraInsumo(inIdresultado, inChk) {

	  if ($("#chMostraInsumo").is(':checked')) {
		 CB.post({
			"objetos": "_x_u_resultado_idresultado=" + inIdresultado + "&_x_u_resultado_mostraformulains=Y",
			parcial: true
		 });
	  } else {
		 CB.post({
			"objetos": "_x_u_resultado_idresultado=" + inIdresultado + "&_x_u_resultado_mostraformulains=N",
			parcial: true
		 });
	  }
   }



   function retsomax() {

	  //soma as quantidades dos inputs (orificios)
	  var total = 0;
	  $("#tbOrificios input[id^=k_]").each(function() {
		 total += Number($(this).val());
	  });

	  //Verifica se é maior que a quantidade de testes estipulada
	  if ((total + 1) <= qtx) {
		 $("#somaorificios").html(total + 1);
		 return true;
	  } else {
		 return false;
	  }
   }

   function capkey(e) {

	  teclaPressionada = retkey(e);

	  iInput = arrKeyConf[teclaPressionada];

	  if (iInput && (document.activeElement.name === undefined || document.activeElement.name == 'xoper')) {
		 pageStateChanged = true;

		 //alert("tecla: "+teclaPressionada+"\n codigo:" + i);
		 $objx = $("#k_" + iInput);

		 if ($objx.length == 0) {
			console.log('Objeto [' + idobjx + '] não encontrado');
			return;
		 }

		 if (xoper == "+" && $("#qtdteste").is(":focus") == false) {
			if (retsomax()) { //Verifica se a quant maxima foi atingida
			   $objx.val(parseInt($objx.val()) + 1);
			   return false;
			} else {
			   alertAtencao("A Quantidade total de [" + qtx + "] testes  foi atingida!", null, "3000");
			   return false;
			}
		 } else if (xoper == "-" && $("#qtdteste").is(":focus") == false) {
			if (parseInt($objx.val()) > 0) { //Verifica se a quant maxima foi atingida
			   $objx.val(parseInt($objx.val()) - 1);
			   $("#somaorificios").html(Number($("#somaorificios").html()) - 1);
			   return false;
			} else {
			   window.status = "Limite inferior [0] atingido...";
			   return false;
			}
		 } else if ($("#qtdteste").is(":focus") == false) {
			alert("Valor para Operação (+ ou -) não ajustado!\n Impossível calcular orifícios.");
		 }
	  }
   }

   function setoper(inoper) {
	  xoper = inoper;
	  console.log("Operação de cálculo alterada para [" + inoper + "]");
   }

   //Conforme o tipo do teste prepara a tela para reagir a funções/comandos específicos
   if (vModelo == "DESCRITIVO") {
	  sSeletor = '#diveditor';
	  oDescritivo = $("[name=_1_" + CB.acao + "_resultado_descritivo]");

	  //Atribuir MCE somente após método loadUrl
	  //CB.posLoadUrl = function(){
	  //Inicializa Editor
	  if (tinyMCE.editors["diveditor"]) {
		 tinyMCE.editors["diveditor"].remove();
	  }
	  tinyMCE.init({
		 selector: sSeletor
			/*,height : 300
			,min_height: 300 */
			,
		 inline: true /* não usar iframe */ ,
		 toolbar: ' italic | subscript superscript | bullist numlist | table',
		 menubar: false,
		 plugins: ['table', 'autoresize'],
		 setup: function(editor) {
			editor.on('init', function(e) {
			   this.setContent(oDescritivo.val());
			});
		 },
		 entity_encoding: 'raw'
	  });

	  //}

	  //Antes de salvar atualiza o textarea
	  CB.prePost = function() {
		 if (tinyMCE.get('diveditor')) {
			//falha nbsp: oDescritivo.val( tinyMCE.get('diveditor').getContent({format : 'raw'}).toUpperCase());
			oDescritivo.val(
			   //maf180919: caracteres especiais estavam sendo transformados para maiusculas. o upper case foi colocado dentro de um replace seletivo
			   //tinyMCE.get('diveditor').getContent().toUpperCase()
			   tinyMCE.get('diveditor').getContent().replace(/[a-z]/gi, function(char) {
				  return char;
			   })
			);
		 }
	  }
   } else if (vModelo == "DINÂMICO") {
	  var vFixo = '<?= $fixo; ?>';
	  var vFixoindice = '<?= $fixoindice; ?>';
	  var vQtdfixo = '<?= $qtdfixo; ?>';
	  var vGridind = `<?= preg_replace("/<br>|\n/", "", $gridind); ?>`;
	  //	console.log(vGridind);
	  /*JLAL - 20/10/01
	  Criação da função que cria o modal trazendo as configurações do jsonconfig da prodserv nova
   */
	  function criaModal(tipo, indice, teste) {
		 if (tipo == 1) {
			tipo = 'selecionavel';
		 } else if (tipo == 2) {
			tipo = 'fixo';
		 }

		 let novoCampo = '<div class="modOption' + indice + '" data-campo="' + indice + '">\
			<div id="modalID' + indice + '" class="modal"  tabindex="-' + indice + '" role="dialog">\
			   <div class="modal-dialog" role="document">\
				  <div class="modal-content">\
					 <div class="modal-header">\
						<h5 class="modal-title">Aplicar à todos</h5>\
					 </div>\
					 <div class="modal-body">\
						<p>Selecione abaixo a opção para que seja preenchida nos outros itens selecionáveis</p>\
						<div class="row">\
						   <div class="col-lg">';
		 var config = JSON.parse('<?= $_1_u_resultado_jsonconfig; ?>');
		 if (typeof(config) == 'object') {
			var stop = false;
			var idplantel = '<?= $idplantel; ?>';
			var unidade = idplantel != undefined ? idplantel : 'todas';

			config.unidadeBloco.forEach(function(bloco) {

			   if (bloco.unidade == unidade && teste == 1) {
				  bloco.personalizados.forEach(function(key) {
					 if (bloco.index == key.index) {
						if (key.tipo == tipo && key.indice == indice) {
						   novoCampo += '<div class="input-group mb-3">\
									   <div class="input-group-prepend">\
										  <label class="input-group-text" for="select' + key.indice + '">Opções</label>\
									   </div>\
									   <select class="custom-select" id="select' + key.indice + '">\
										  <option selected value="vazio"></option>';
						   key.options.forEach(function(option) {
							  novoCampo += '<option value="' + option.indice + '">' + option.nome + '</option>\
											 ';
						   });
						   novoCampo += '</select>\
									</div>\
									</div>\
								 </div>\
							  </div>\
							  <div class="modal-footer">\
								 <span id="aplicarOpcao" class="btn btn-success" data-dismiss="modal" onclick="aplicarOpcao(' + key.indice + ');">Aplicar</span>\
								 <button id="cancelarOption" type="button"\
									class="btn btn-danger"\
									data-dismiss="modal">Cancelar</button>\
							  </div>\
						   </div>\
						</div>\
					 </div>\
				  <\div>';
						   //stop = true;
						}

					 }
				  });
			   } else if (bloco.unidade == "todas" && teste != 1) {
				  bloco.personalizados.forEach(function(key) {
					 if (bloco.index == key.index) {
						if (key.tipo == tipo && key.indice == indice) {
						   novoCampo += '<div class="input-group mb-3">\
									   <div class="input-group-prepend">\
										  <label class="input-group-text" for="select' + key.indice + '">Opções</label>\
									   </div>\
									   <select class="custom-select" id="select' + key.indice + '">\
										  <option selected value="vazio"></option>';
						   key.options.forEach(function(option) {
							  novoCampo += '<option value="' + option.indice + '">' + option.nome + '</option>\
											 ';
						   });
						   novoCampo += '</select>\
									</div>\
									</div>\
						</div>\
					 </div>\
					 <div class="modal-footer">\
						<span id="aplicarOpcao" class="btn btn-success" data-dismiss="modal" onclick="aplicarOpcao(' + key.indice + ');">Aplicar</span>\
						<button id="cancelarOption" type="button"\
						   class="btn btn-danger"\
						   data-dismiss="modal">Cancelar</button>\
					 </div>\
				  </div>\
			   </div>\
			</div>\
		 <\div>';
						   //stop = true;
						}

					 }
				  });
			   }

			});
		 }
		 return novoCampo;
	  }
	  /*JLAL - 20/10/01
	  Criação da função vai replicar o valor selecionado para os outros campos select
   */
	  function aplicarOpcao(index) {
		 var qt = '<?= $qtdfixo ?>' == 0 ? '<?= $_1_u_resultado_quantidade ?>' : '<?= $qtdfixo ?>';

		 var valor = $('#select' + index).val();
		 for (var i = 0; i < qt; i++) {
			if (valor == "vazio") {
			   $('.aplicar' + valor + index + ' ').prop('selected', true);
			} else {
			   $('.aplicar' + valor + ' ').prop('selected', true);
			}
		 }
	  }
	  /*JLAL - 20/10/01
		 Criação da função que cria o modal para que o usuário consiga escolher qual valor vai replicar para os outros campos select
	  */
	  function aplicarTodos(tipo, indice, teste) {
		 let novaConfig = criaModal(tipo, indice, teste);
		 $('.configModal' + indice).append($(novaConfig));

		 $('#modalID' + indice).modal('show');
		 $(".modal-backdrop").remove();

	  }

	  function addCampo(valor, indice) {

		 console.log(valor);
		 var ind = $('.interna2').length--;
		 var nIndice = 0;
		 $('[data-indice]').each(function(index) {

			var input = $(this);
			if (Number(input.attr('data-indice')) > nIndice) {

			   //  console.log(input.attr('data-indice') + '>' + nIndice);
			   nIndice = input.attr('data-indice');
			}


		 });
		 nIndice++;
		 $(valor).closest(".row").html()

		 var grid = $(valor).closest(".row").html();
		 grid = grid.split(">" + indice + "<").join(">" + nIndice + "<");
		 grid = grid.split(indice + "_campo").join(nIndice + "_campo");
		 grid = grid.split('data-indice="' + indice + '"').join('data-indice="' + nIndice + '"');

		 $(valor).closest(".row").after(grid);
		 /*JLAL - 20/10/01 
			Atualiza a quantidade de testes a cada acrescimo, além de salvar automatico para atualizar o indice, evitando que ocorra erro         
		 */
		 document.getElementById('qtTeste').value = ind;

		 CB.post();
	  }

	  function delCampo(valor, indice = null) {
		 console.log(valor);
		 var ind = $('.interna2').length - 1;

		 var resultado = $(`[data-titulo="RESULTADO"][data-indice="${indice}"]`).val();
		 var semente = $(`[data-titulo="SEMENTE"][data-indice="${indice}"]`).val();

		 if (indice && resultado == "POSITIVO" && semente != "") {
			if (confirm("Deseja realmente retirar essa semente?")) {
			   var grid = $(valor).closest(".row").remove();
			   ind--;
			   var qt = document.getElementById('qtTeste').value = ind;
			   /*JLAL - 20/10/01 
				  Atualiza a quantidade de testes a cada decrescimo,caso a quatidade chegue a zero, altera o valor pegando o ultimo valor cadastrado para aquele tipo de resultado
				  , além de salvar automatico para atualizar o indice, evitando que ocorra erro         
			   */
			   if (qt == 0) {
				  qt = document.getElementById('qtTeste').value = "<?= $_SESSION['auxqt'] ?>";
			   }

			   let lote = jArrAgentes.filter((o, i) => {
				  return o.agente + "/" + o.exercicio == semente
			   });

			   if (lote.length > 0) {
				  if ($('div[data-tipo="divhtml"]').attr('name')) {
					 namedoobjeto = $('div[data-tipo="divhtml"]').attr('name');
					 $('div[data-tipo="divhtml"]').each(function(index, element) {
						var nome = $(this).attr('name').replace('editor', '');
						$('textarea[name="' + nome + '"]').val(element.innerHTML);
					 });
				  }
				  InputsToJsonField();
				  CB.post({
					 objetos: {
						"_x_u_lote_idlote": lote[0].idlote,
						"_x_u_lote_orgao": ""
					 },
				  });
			   } else {
				  CB.post();
			   }
			}

		 } else {
			var grid = $(valor).closest(".row").remove();
			ind--;
			var qt = document.getElementById('qtTeste').value = ind;
			/*JLAL - 20/10/01 
			   Atualiza a quantidade de testes a cada decrescimo,caso a quatidade chegue a zero, altera o valor pegando o ultimo valor cadastrado para aquele tipo de resultado
			   , além de salvar automatico para atualizar o indice, evitando que ocorra erro         
			*/
			if (qt == 0) {
			   qt = document.getElementById('qtTeste').value = "<?= $_SESSION['auxqt'] ?>";
			}
			CB.post();
		 }

	  }


	  function checkedAll() {
		 $("input[name='remove']").each(function() {
			$(".remove").prop('checked', true);
		 });
		 $('#removeChecked').show();
		 $('#selectChecked').show();
		 $('.remove').show();
		 $('#unchecked').hide();
	  }

	  function uncheckedAll() {
		 $("input[name='remove']").each(function() {
			$(".remove").prop('checked', false);
		 });
		 $('#removeChecked').show();
		 $('#selectChecked').hide();
		 $('#unchecked').show();
	  }

	  function delSelecionados() {
		 var verificaMsg = $("[data-titulo='SEMENTE']").filter((i, o) => {
			if (o.value &&
			   $(`[data-titulo='RESULTADO'][data-indice="${$(o).attr('data-indice')}"]`).val() == "POSITIVO" &&
			   $(`[data-titulo='TIPO DE AMOSTRA'][data-indice="${$(o).attr('data-indice')}"]`).val() != "" &&
			   $(o).closest(".global").find("[name='remove']").is(":checked")) {
			   return true;
			} else {
			   return false;
			}
		 })

		 let Nverifica = true;
		 if (verificaMsg.length > 0) {
			Nverifica = confirm("Existem sementes associadas ao teste, deseja realmente excluí-las?");
		 }

		 if (Nverifica) {

			var ind = $('.interna2').length - 1;
			$("input[name='remove']:checked").each(function(i, o) {

			   var resultado = $(this).closest('.global').find(`[data-titulo="RESULTADO"]`).val()
			   var semente = $(this).closest('.global').find(`[data-titulo="SEMENTE"]`).val()

			   if (resultado == "POSITIVO" && semente != "") {
				  <? if (($modamostra == 'amostratra' or $modamostra == 'amostraautogenas') and !empty($_1_u_resultado_idresultado)) { ?>
					 let lote = jArrAgentes.filter((o, i) => {
						return o.agente + "/" + o.exercicio == semente
					 });
					 if (lote.length > 0) {
						$("#cbModuloForm").append(`
						   <input type="hidden" name="_upd${i}_u_lote_idlote" value=${lote[0].idlote}>
						   <input type="hidden" name="_upd${i}_u_lote_orgao" value="">
						`);
					 }
				  <? } ?>
			   }

			   $(this).closest('.global').remove();
			   ind--;
			   var qt = document.getElementById('qtTeste').value = ind;
			   /*JLAL - 20/10/01 
				  Atualiza a quantidade de testes a cada decrescimo,caso a quatidade chegue a zero, altera o valor pegando o ultimo valor cadastrado para aquele tipo de resultado
				  , além de salvar automatico para atualizar o indice, evitando que ocorra erro         
			   */
			   if (qt == 0) {
				  qt = document.getElementById('qtTeste').value = "<?= $_SESSION['auxqt'] ?>";

				  CB.post();
			   } else {
				  //CB.post();
			   }
			});

		 }

	  }

	  //FUNÇÃO CRIADA PARA CONVERTER TODOS OS INPUTS DINÂMICOS EM JSON E ARMAZENAR NA VARIÁVEL CB
	  function InputsToJsonField() {
		 //TIPO DINÂMICO INDIVIDUAL
		 var arrObj = [];
		 var individual = '';
		 var qtdindividual = $('.interna2').length - 1;
		 $('[data-vinculo="INDIVIDUAL"]').each(function(index) {

			var input = $(this);
			//Validação para os campos tipo select para pdoer trazer o valor selecionado
			if ($(input).is("select")) {
			   var valor = input.val();
			   if (input.children("option:selected").attr('calcula') == "Y") {
				  var calculoperc = "Y";
			   } else {
				  var calculoperc = "N";
			   }
			}
			if (input.attr('type') == 'checkbox') {
			   var valor = input.is(':checked');
			} else {
			   var valor = input.val().replace(/"/g, '&quot;');
			}
			//Alteração para gravar se o campo é de calculo ou não
			arrObj.push({
			   type: input.attr("type"),
			   indice: input.attr("data-indice"),
			   titulo: input.attr("data-titulo"),
			   calculo: input.attr("data-calculo"),
			   name: input.attr("name"),
			   value: valor,
			   calculoop: calculoperc
			});
		 });

		 var jsonObj = new Object();
		 jsonObj["INDIVIDUAL"] = arrObj;
		 jsonObj["QTDINDIVIDUAL"] = qtdindividual;
		 var arrObj = [];

		 //TIPO DINÂMICO AGRUPADO
		 $('[data-vinculo="AGRUPADO"]').each(

			function(index) {
			   var input = $(this);
			   if (input.attr('type') == 'checkbox') {
				  var valor = input.is(':checked');
			   } else {
				  var valor = input.val().replace(/"/g, '&quot;');
			   }

			   arrObj.push({
				  type: input.attr("type"),
				  indice: input.attr("data-indice"),
				  titulo: input.attr("data-titulo"),
				  name: input.attr("name"),
				  value: valor
			   });

			}
		 );

		 jsonObj["AGRUPADO"] = arrObj;

		 //JUNTAR AS ARRAYS E TRANSFORMAR EM JSON
		 myJsonString = JSON.stringify(jsonObj);
		 console.log(myJsonString);
		 //ATRIBUIR AO CAMPO HIDDEN DO INPUT CB
		 $("[name=_1_" + CB.acao + "_resultado_jsonresultado]").val(myJsonString);
	  }


	  //INCLUSÃO DINÂMICA DO TINYMCE NOS CAMPOS DINÂMICOS DO TIPO TEXTAREA
	  sSeletor = '.diveditor';

	  if (tinyMCE.editors["diveditor"]) {
		 tinyMCE.editors["diveditor"].remove();
	  }


	  tinyMCE.init({
		 selector: sSeletor,
		 inline: true /* não usar iframe */ ,
		 toolbar: 'bold | subscript superscript | bullist numlist | table',
		 menubar: false,
		 plugins: ['table', 'autoresize'],
		 setup: function(editor) {
			editor.on('init', function(e) {
			   $(":text [name='" + this.bodyElement.getAttribute('name') + "']").val(this.bodyElement.attributes.contenteditable.ownerElement.innerHTML);
			}).on('change', function(e) {
			   $(":text [name='" + this.bodyElement.getAttribute('name') + "']").val(this.bodyElement.attributes.contenteditable.ownerElement.innerHTML);
			});
		 },
		 entity_encoding: 'raw'
	  });

	  //PREENCHIMENTO DE TODOS OS CAMPOS DINÂMICOS COM SEUS RESPECTIVOS VALORES
	  //DE ACORDO COM AS INFORMAÇÕES ARMAZENADAS NO JSON
	  var objJsonReturn = new Object;
	  if ('<?= $_1_u_resultado_jsonresultado; ?>') {
		 objJsonReturn = JSON.parse('<?= $_1_u_resultado_jsonresultado; ?>');

		 $.each(objJsonReturn, function(index, value) {

			$.each(value, function(i, v) {
			   name = v['name'];
			   //SE TIPO INPUT
			   if ($('input').is('[name="' + name + '"]')) {
				  //SE TIPO TEXT
				  if (v['type'] == 'text') {
					 $('input[name="' + name + '"]').attr('value', v['value']);
					 //SE TIPO CHECKBOX
				  } else if (v['type'] == 'checkbox') {
					 console.log(v['value']);
					 if (v['value'] == true) {
						$('input[name="' + name + '"]').prop('checked', true);
					 }
					 //SE TIPO OUTROS
				  } else {
					 $('input[name="' + name + '"]').val(v['value']);

				  }
				  //SE TIPO SELECT
			   } else if ($('select').is('[name="' + name + '"]')) {
				  $('select[name="' + name + '" ] option[value="' + v['value'] + '"]').attr("selected", "selected");
				  //SE TIPO TEXTAREA
			   } else if ($('textarea').is('[name="' + name + '"]')) {
				  //ATUALIZA TEXTAREA          
				  $('textarea[name="' + name + '"]').html(v['value']);
				  namediv = name.replace('campo', 'campoeditor');
				  //ATUALIZA DIV TYNEMCE
				  $('div[name="' + namediv + '"]').html(v['value']);
			   }
			});
		 });
	  }
	  //Antes de salvar atualiza o textarea
	  CB.prePost = function() {
		 if ($('div[data-tipo="divhtml"]').attr('name')) {
			namedoobjeto = $('div[data-tipo="divhtml"]').attr('name');
			$('div[data-tipo="divhtml"]').each(function(index, element) {
			   var nome = $(this).attr('name').replace('editor', '');
			   $('textarea[name="' + nome + '"]').val(element.innerHTML);
			});

		 }
		 InputsToJsonField();
	  }

	  var c = 1;

	  if (vFixo == '1') {
		 //console.log(<?= $fixo; ?>);
		 while (c <= vQtdfixo) {
			$('[name=' + c + '_campo_' + vFixoindice + '] option').eq(c).attr('selected', true);
			c = c + 1;
		 }
	  }

   } else if (vModelo == "SELETIVO" && vModo == "AGRUP") {
	  document.onkeypress = capkey;
	  <?
	  confPressionamentoTeclas();
	  ?>
   } else if (vModelo == "UPLOAD") {
	  $("#resultadoelisa").dropzone({
		 idObjeto: idResultado,
		 tipoObjeto: 'resultado',
		 tipoArquivo: 'RESULTADOELISA',
		 tipoKit: $("[name=tipokit]").val(),
		 sending: function(file, xhr, formData) {
			//Ajusta parametros antes de enviar via post
			formData.append("tipokit", this.options.tipoKit);
		 }
	  });

   }

   CB.preLoadUrl = function() {
	  //Como o carregamento é via ajax, os popups ficavam aparecendo após o load
	  $(".webui-popover").remove();
   }

   $(".oTeste").webuiPopover({
	  trigger: "hover",
	  placement: "right",
	  delay: {
		 show: 300,
		 hide: 0
	  }
   });



   window.onbeforeunload = testPageState;

   function testPageState() {
	  if ((typeof(pageStateChanged) != "undefined") && (pageStateChanged)) {
		 mess = "***********************************************************\n\nAS INFORMAÇÕES NÃO FORAM SALVAS AINDA!\n DESEJA REALMENTE SAIR SEM SALVAR?\n\n***********************************************************";
		 return mess;
	  }
   }



   function inovolote(inidresultado) {
	  var strCabecalho = "</strong>NOVO AGENTE <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='criaragente();'><i class='fa fa-circle'></i>Salvar</button></strong>";
	  //$("#cbModalTitulo").html((strCabecalho));

	  var htmloriginal = $("#novolote").html();
	  var objfrm = $(htmloriginal);

	  objfrm.find("#idlotelote").attr("name", "_x_i_lote_idlote");
	  objfrm.find("#idprodservlote").attr("name", "_x_i_lote_idprodserv");

	  objfrm.find("#exerciciolote").attr("name", "_x_i_lote_exercicio");
	  objfrm.find("#statuslote").attr("name", "_x_i_lote_status");
	  objfrm.find("#qtdprod").attr("name", "_x_i_lote_qtdprod");
	  objfrm.find("#idunidadegplote").attr("name", "_x_i_lote_idunidade");

	  let tipoAmostra = $(`[data-titulo="TIPO DE AMOSTRA"]`);
	  if (tipoAmostra.length > 0) {
		 var arrTipoAmostra = false;
		 var $opt = "";
		 tipoAmostra.each((i, o) => {
			if (o.value != "" && $(`[data-titulo="RESULTADO"][data-indice="${$(o).attr('data-indice')}"]`).val() == "POSITIVO" && $(`[data-titulo="SEMENTE"][data-indice="${$(o).attr('data-indice')}"]`).val() == "") {
			   arrTipoAmostra = true;
			   let indice = $(o).attr('data-indice')
			   $opt += `<option indice=${indice} value="${o.value}">${o.value}</option>`;
			}
		 });
		 let content = `<select id="orgao" name="_x_i_lote_orgao"><option></option>${$opt}</select>`;

		 if (arrTipoAmostra) {
			$(content).insertAfter(objfrm.find("#orgao"));
			objfrm.find("input#orgao").remove();
		 } else {
			objfrm.find("#orgao").attr("name", "_x_i_lote_orgao");
		 }

	  } else {
		 objfrm.find("#orgao").attr("name", "_x_i_lote_orgao");
	  }

	  objfrm.find("#idobjetolote").attr("name", "_x_i_lote_idobjetosolipor");
	  objfrm.find("#idobjetolote").attr("value", inidresultado);

	  objfrm.find("#tipoobjetolote").attr("name", "_x_i_lote_tipoobjetosolipor");

	  CB.modal({
		 titulo: strCabecalho,
		 corpo: [objfrm],
	  });

   }

   function listalote(inidresultado) {
	  var strCabecalho = "</strong>AGENTE(S)</strong>";
	  $("#cbModalTitulo").html((strCabecalho));

	  var htmloriginal = $("#resultadoagente" + inidresultado).html();
	  var objfrm = $(htmloriginal);
	  $("#cbModalCorpo").html(objfrm.html());
	  $('#cbModal').modal('show');

   }

   function criaragente() {

	  //LTM - 05-05-2021 - Retorna o idFluxoStatus Selecionado
	  var idfluxostatus = getIdFluxoStatus('semente', 'ABERTO');
	  var str = "_x_i_lote_idprodserv=" + $("[name=_x_i_lote_idprodserv]").val() +
		 "&_x_i_lote_status=ABERTO" +
		 "&_x_i_lote_idempresa=<?= cb::idempresa() ?>" +
		 "&_x_i_lote_idfluxostatus=" + idfluxostatus +
		 "&_x_i_lote_exercicio=" + $("[name=_x_i_lote_exercicio]").val() +
		 "&_x_i_lote_idunidade=" + $("[name=_x_i_lote_idunidade]").val() +
		 "&_x_i_lote_orgao=" + $("[name=_x_i_lote_orgao]").val() +
		 "&_x_i_lote_qtdprod=" + $("[name=_x_i_lote_qtdprod]").val() +
		 "&_x_i_lote_qtdpedida=" + $("[name=_x_i_lote_qtdprod]").val() +
		 "&_x_i_lote_tipoobjetosolipor=" + $("[name=_x_i_lote_tipoobjetosolipor]").val() +
		 "&_x_i_lote_idobjetosolipor=" + $("[name=_x_i_lote_idobjetosolipor]").val();

	  var orgao = $("[name=_x_i_lote_orgao]");
	  var indice = orgao.children(`[value="${orgao.val()}"]`).attr('indice')
	  CB.post({
		 objetos: str,
		 parcial: true,
		 posPost: function(resp, status, ajax) {
			if (status = "success") {
			   var a = jArrAgentes.filter((o, i) => {
				  return o.idlote == ajax.getResponseHeader('x-cb-pkid')
			   });


			   $("#cbModalCorpo").html("");
			   $('#cbModal').modal('hide');
			   if (indice && a[0]) {
				  $(`[data-titulo="SEMENTE"][data-indice="${indice}"]`).val(a[0].agente + "/" + a[0].exercicio);
				  CB.post();
			   }
			} else {
			   alert(resp);
			}
		 }
	  });
   }

   if ($("[name=_1_u_resultado_idresultado]").val()) {
	  $("#arquivoresultado").dropzone({
		 idObjeto: $("[name=_1_u_resultado_idresultado]").val(),
		 tipoObjeto: 'resultado'
	  });
   }


   function setresultadoind(vid) {

	  var tecla = parseInt($('#tecla' + vid).val());

	  var valor = tecla + 1;

	  document.getElementById('resultado' + vid).value = valor;
	  document.getElementById('rotulo' + vid).value = valor;
   }

   function atualizainput(inlinha) {

	  $("[name=" + inlinha + "idlote]").attr('name', '_' + inlinha + '_i_lotecons_idlote');
	  $("[name=" + inlinha + "idlotefracao]").attr('name', '_' + inlinha + '_i_lotecons_idlotefracao');
	  $("[name=" + inlinha + "tipoobjeto]").attr('name', '_' + inlinha + '_i_lotecons_tipoobjeto');
	  $("[name=" + inlinha + "idobjeto]").attr('name', '_' + inlinha + '_i_lotecons_idobjeto');
	  $("[name=" + inlinha + "qtdd]").attr('name', '_' + inlinha + '_i_lotecons_qtdd');

   }

   function esgotarlote(inIdlotefracao) {

	  if (confirm("Deseja realmente esgotar o lote?")) {
		 CB.post({
			"objetos": "_x_u_lotefracao_idlotefracao=" + inIdlotefracao + "&_x_u_lotefracao_status=ESGOTADO&_x_u_lotefracao_qtd=0&&_x_u_lotefracao_qtd_exp=0",
			parcial: true
		 });
	  }
   }

   function mostraConsumo(inOConsumo) {

	  $oc = $(inOConsumo);

	  $tbInsumo = $oc.closest("table");
	  //$oajustecalc=$tbInsumo.find("[class=ajuste_calc]");

	  somaUtilizacao = 0;
	  $trInsumo = $oc.closest("tr.trInsumo");
	  $sRestante = $trInsumo.find(".sRestante");
	  $sQtdpadrao = $trInsumo.find(".sQtdpadrao");
	  $oConsumos = $trInsumo.find("[name*=_qtdd]");
	  $sUtilizando = $trInsumo.find(".sUtilizando");

	  $.each($oConsumos, function(isc, osc) {

		 var $o = $(osc);

		 if ($o.val()) {

			if ($o.attr("cbqtddispexp") != "" && ($o.val().toLowerCase().indexOf("e") <= 0 && $o.val().toLowerCase().indexOf("d") <= 0)) {
			   alertAtencao("Valor inválido. <br> Inserir e ou d.");
			   return false;
			}

			valor = $o.val().replace(/,/g, '.');
			valor = normalizaQtd(valor);

			somaUtilizacao += valor;
		 }

	  });

	  qtdPadrao = normalizaQtd($sQtdpadrao.html());

	  //somaUtilizacao=recuperaExpoente(somaUtilizacao,qtdPadrao);

	  if (somaUtilizacao >= qtdPadrao) {
		 sclass = "fundoverde";
	  } else {
		 sclass = "fundolaranja";
	  }

	  if (somaUtilizacao > 0) {
		 //Formata o badge de 'utilizando'
		 $sUtilizando
			.html(somaUtilizacao)
			.removeClass("fundoverde")
			.removeClass("fundolaranja")
			.addClass(sclass)
			.attr("title", (somaUtilizacao / qtdPadrao) * 100 + "%");
	  } else { //zero ou vazio
		 //Formata o badge de 'utilizando'
		 $sUtilizando
			.html(somaUtilizacao)
			.removeClass("fundoverde")
			.removeClass("fundolaranja")
			.attr("title", (somaUtilizacao / qtdPadrao) * 100 + "%");
	  }

	  $sRestante
		 //.html(((qtdPadrao-somaUtilizacao)/$vajustecalc))
		 .html(qtdPadrao - somaUtilizacao)
		 .removeClass("fundoverde")
		 .removeClass("fundolaranja")
		 .addClass(sclass);

   }

   function normalizaQtd(inValor) {

	  var sVlr = "" + inValor;
	  var $arrExp;
	  var fVlr;

	  if (sVlr.toLowerCase().indexOf("d") > -1) {
		 $arrExp = sVlr.toLowerCase().split('d');
		 fVlr = (parseFloat($arrExp[0]) * parseFloat($arrExp[1])).toFixed(2);
		 fVlr = parseFloat(fVlr);
	  } else if (sVlr.toLowerCase().indexOf("e") > -1) {
		 $arrExp = sVlr.toLowerCase().split('e');
		 fVlr = $arrExp[0] * Math.pow(10, $arrExp[1]);
	  } else {
		 fVlr = parseFloat(sVlr).toFixed(2);
	  }

	  return parseFloat(fVlr);
   }

   function settipokit(vthis, inidres) {
	  CB.post({
		 objetos: `_x_u_resultado_idresultado=` + inidres + `&_x_u_resultado_tipokit=` + $(vthis).val(),
		 parcial: true
	  });
   }

   function dfase(inidresultadoprodservformula) {
	  CB.post({
		 objetos: `_x_u_resultadoprodservformula_idresultadoprodservformula=` + inidresultadoprodservformula + `&_x_u_resultadoprodservformula_status=INATIVO`,
		 parcial: true
	  });
   }

   function ifase(inidresultado, inidprodservformula) {
	  CB.post({
		 objetos: `_x_i_resultadoprodservformula_idresultado=` + inidresultado + `&_x_i_resultadoprodservformula_idprodservformula=` + inidprodservformula,
		 parcial: true
	  });
   }

   <? if (($modamostra == 'amostratra' or $modamostra == 'amostraautogenas') and !empty($_1_u_resultado_idresultado)) { ?>
	  jArrAgentes = <?= $arrAgentes ?>;

	  if (jArrAgentes != 0) {
		 // jArrAgentes == Array
		 if (jArrAgentes.length > 0) {
			// Monta input:select

			$(`[data-titulo="SEMENTE"]`).each((i, o) => {
			   let name = $(o).attr('name');
			   let indice = $(o).attr('data-indice');
			   let tipo = $(o).attr('data-tipo');
			   let titulo = $(o).attr('data-titulo');
			   let vinculo = $(o).attr('data-vinculo');
			   let calculo = $(o).attr('data-calculo');
			   let type = $(o).attr('type');
			   let value = $(o).val();
			   let selected;

			   if (value == "" && $(`[data-titulo="RESULTADO"][data-indice="${indice}"]`).val() == "POSITIVO" && $(`[data-titulo="TIPO DE AMOSTRA"][data-indice="${indice}"]`).val() != "") {
				  $opt = `<option value = ""></option>`;
				  var orgao = $(`[data-titulo="TIPO DE AMOSTRA"][data-indice="${indice}"]`).val()
				  for (let a of jArrAgentes) {

					 $opt += `<option tipificacao="${a.tipificacao}" idlote="${a.idlote}" value = "${a.agente}/${a.exercicio}">${a.agente}/${a.exercicio}</option>`;

				  }

				  let $oContent = $(`<select name = "${name}" data-indice = "${indice}" type = "${type}" data-tipo = "${tipo}" data-titulo = "${titulo}" data-vinculo = "${vinculo}" data-calculo = "${calculo}">
					 ${$opt}
				  </select>`).on('change', function() {
					 if (this.value != "") {
						let option = $(this).children(`[value="${this.value}"]`);
						if ($(`[data-titulo="TIPIFICAÇÃO"][data-indice="${indice}"]`)) {

						   $(`[data-titulo="TIPIFICAÇÃO"][data-indice="${indice}"]`).val($(this).children(`[value="${this.value}"]`).attr('tipificacao'));

						}

						if ($('div[data-tipo="divhtml"]').attr('name')) {
						   namedoobjeto = $('div[data-tipo="divhtml"]').attr('name');
						   $('div[data-tipo="divhtml"]').each(function(index, element) {
							  var nome = $(this).attr('name').replace('editor', '');
							  $('textarea[name="' + nome + '"]').val(element.innerHTML);
						   });
						}
						InputsToJsonField();
						CB.post({
						   objetos: {
							  "_x_u_lote_idlote": option.attr('idlote'),
							  "_x_u_lote_orgao": orgao
						   },
						});
					 }
				  });
				  $(o).parent().append($oContent);

				  $(o).remove();
			   } else if (value != "" && $(`[data-titulo="RESULTADO"][data-indice="${indice}"]`).val() == "POSITIVO") {
				  $(o).on('keyup', {
					 lote: value
				  }, function(e) {
					 if (this.value == "") {
						if (confirm("Deseja realmente retirar essa semente?")) {
						   let lote = jArrAgentes.filter((o, i) => {
							  return o.agente + "/" + o.exercicio == e.data.lote
						   });

						   if (lote.length > 0) {
							  if ($('div[data-tipo="divhtml"]').attr('name')) {
								 namedoobjeto = $('div[data-tipo="divhtml"]').attr('name');
								 $('div[data-tipo="divhtml"]').each(function(index, element) {
									var nome = $(this).attr('name').replace('editor', '');
									$('textarea[name="' + nome + '"]').val(element.innerHTML);
								 });

							  }
							  if ($(`[data-titulo="TIPIFICAÇÃO"][data-indice="${indice}"]`)) {

								 $(`[data-titulo="TIPIFICAÇÃO"][data-indice="${indice}"]`).val('');

							  }
							  InputsToJsonField();
							  CB.post({
								 objetos: {
									"_x_u_lote_idlote": lote[0].idlote,
									"_x_u_lote_orgao": ""
								 },
							  });
						   }
						} else {
						   this.value = e.data.lote;
						}
					 }
				  })
			   }

			});
		 }
	  }
   <? } ?>

   <? if($_acao!='i'){?>
	CB.on("posPost",function(data){
		if(data.jqXHR.getResponseHeader('positivos'))
		{
			let numeroResultadosPositivos = JSON.parse(data.jqXHR.getResponseHeader('positivos'));
			console.log(numeroResultadosPositivos);
			
			if(numeroResultadosPositivos > 0){
				alertAtencao(`Existem ${numeroResultadosPositivos} resultado(s) positivo(s). Favor marcar o Flag de Alerta`);
			}			
		}
	});
	<? } ?>

   //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape
</script>