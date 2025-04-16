<link href="../inc/css/8rep.css" media="all" rel="stylesheet" type="text/css" />
<style type="text/css" media="print">
@media print {
  body {-webkit-print-color-adjust: exact;}
  a[href]:after {
    content: none !important;
  }
}
@page {
    size:A4;
    margin-left: 0px;
    margin-right: 0px;
    margin-top: 0px;
    margin-bottom: 0px;
    margin: 0;
    -webkit-print-color-adjust: exact;
}
</style>
<style>
#inter span{
	font-size:8px !important;
	font-weight:normal;
}

#inter u{
	font-size:8px !important;
	font-weight:normal;
}
.tabtitulos td{
	height:10px;
}
.MsoTableGrid td{
	border-bottom: 1px solid silver !important;
	border-top: 1px solid silver !important;
	border-left: 1px solid silver !important;
	border-right: 1px solid silver !important;
}
strong{
font-weight: normal !important;
}	
	
span{
	font-size:6px !important;
}
.relisa{

	width:11% !important;
	display:inline-block;
}
.MsoTableGrid{
	width:100%;
}
table{
	width:100%;
}
.MsoTableGrid{
	border-color:#eee !important;
}
.trelisa{
	height:10px;
}

.trpos{

	background-color: #FFC0C0;
}
	

	
	
@font-face {
  font-family: 'Roboto';
  src: url("../inc/css/fonts/Roboto-Regular.woff2?v=2.137") format("woff2"), url("../inc/css/fonts/Roboto-Regular.woff?v=2.137") format("woff");
  font-weight: 400;
  font-style: normal; }
  @font-face {
  font-family: 'Roboto';
  src: url("../inc/css/fonts/Roboto-Regular.woff2?v=2.137") format("woff2"), url("../inc/css/fonts/Roboto-Regular.woff2?v=2.137") format("woff");
  font-weight: normal;
  font-style: normal; }
/* BEGIN Bold */
@font-face {
  font-family: 'Roboto';
  src: url("../inc/css/fonts/Roboto-Bold.woff2?v=2.137") format("woff2"), url("../inc/css/fonts/Roboto-Bold.woff?v=2.137") format("woff");
  font-weight: 700;
  font-style: normal; }
@font-face {
  font-family: 'Roboto';
  src: url("../inc/css/fonts/Roboto-Bold.woff2?v=2.137") format("woff2"), url("../inc/css/fonts/Roboto-Bold.woff?v=2.137") format("woff");
  font-weight: bold;
  font-style: normal; }
/* END Bold */

  
table{
	
	padding:0px;
	margin:0px;
	border-spacing: 0px;
	font-size:7px;
	text-transform:uppercase;
	font-family:Verdana, Geneva, sans-serif !important;
	font-weight:normal !important;
	color: #333 !important;
	
}
.tdrot{
	width:80px !important;
	font-size:7px !important;

	color:#333 !important;
	height:12px;
	text-align:left !important;
	background-color:#fff;
	font-family:Verdana, Geneva, sans-serif !important;

}
.tdval{
	font-size:8px !important;
	font-family:Verdana, Geneva, sans-serif !important;
text-transform:uppercase;
	color:#333 !important;
	background-color:#fff;
}

.tablegenda tr td{
padding-left:4px !important;
}

.resdesc table td{
	padding-left:4px !important;
	padding:1px !important;
	height:10px !important;
	border:1px solid #e1e1e1;
}
.resdesc table tr{
	height:10px !important;
	
}
.resdesc table{
	border:none !important;
}

.resdesc2 p, .resdesc2 strong, .resdesc2 div {
	font-size:6px !important;
	line-height:8px !important;
}

.resdesc2 span {
	font-size:6px !important
	line-height:8px !important;
}

.resdesc2 td{
	border: none !important;
}

#resm span{
	font-size:8px !important;
}
</style>
<style type="text/css">
   table { page-break-inside:auto; width:100% }
    tr    { page-break-inside:avoid; page-break-after:auto;}
	  
  
    thead { display:table-header-group }
    tfoot { display:table-footer-group }
	
	@media print
{    


	
    .no-print, .no-print *
    {
        display: none !important;
    }
	 
}

</style>

	<?

include_once("../inc/php/jpgraph/grafelisa.php");
include_once("../inc/php/jpgraph/grafelisagmt.php");

function teaser( $html ) {
    $html = str_replace( '&nbsp;', ' ', $html );
    do {
        $tmp = $html;
        $html = preg_replace(
            '#<([^ >]+)[^>]*>[[:space:]]*</\1>#', '', $html );
    } while ( $html !== $tmp );

    return $html;
}
/*
 *maf09082011: escreve no cabecalho as informacoes de controle da impressao
 */
function imppaginisup(){
	//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
	global $ipage;
	global $pb;
	global $codepress;
	global $irestotal;
	global $row;
	global $rowp;
	global $mostracabecalho;

	$ipage++;
	$paginaatual = $ipage;

/*
 * maf060211: mostrar cabecalhos a usuarios que nao sao do tipo funcionario 
 *  
 */

if($_SESSION["SESSAO"]["IDTIPOPESSOA"]!=1 or ( $rowp["visualizares"]=='Y' AND $mostracabecalho!="N" )){
	
	// imagem de https://www.base64-image.de/
	//../img/Cabecalho Resultado.png
	?>
	<table style="<?=$pb?>;width:700px !important; margin:auto;  ">
	<thead>
	<tr  style="position:relative;">
	<td><div style="position:fixed; right:0px; top:4px;z-index:9999999">
	<a href="#" class="no-print" onclick="window.print();return false;"><div onmouseover="this.style.opacity=1;" onmouseout="this.style.opacity=0.6;" style="opacity:0.6;float:left;   top: 10%;  width: 8em;
    margin-top: -2.5em;
    background: #666;
    height: 32px;
    font-size: 12px !important;
    text-align: center;
    line-height: 20px;    margin: 0px 2px;
    color: #fff;"><img src="../inc/img/print_white_192x192.png" style="height:20px;position:relative;top:5px;">Imprimir</div></a>
	<? 
	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$full_url = $protocol."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

?>
	<a href="<?=$full_url?>&csv=1" class="no-print" ><div onmouseover="this.style.opacity=1;" onmouseout="this.style.opacity=0.6;" style="opacity:0.6;float:left;top: 10%;  width: 12em;
    margin-top: -2.5em;
    background: #666;
    height: 32px;
    font-size: 12px !important;
    text-align: center;
    line-height: 20px; margin: 0px 2px;
    color: #fff;"><img src="../inc/img/csv-icon.png" style="height:20px;position:relative;top:5px;">DOWNLOAD CSV</div></a>
<a href="<?=$full_url?>&gerapdf=Y" class="no-print" ><div  onmouseover="this.style.opacity=1;" onmouseout="this.style.opacity=0.6;"style="opacity:0.6;float:left;top: 10%; width: 12em;
	display:none
    margin-top: -2.5em;
    background: #666;
    height: 32px;
    font-size: 12px !important;
    text-align: center;
    line-height: 20px; margin: 0px 2px;
    color: #fff;"><img src="../inc/img/pdf-icon.png" style="height:20px;position:relative;top:5px;">DOWNLOAD PDF</div></a>	
	</div>
	<div style=" width:700px ;">
		<table class="cabtxt" style="width:100%;position:relative; z-index:2; background-color:#fff; margin-bottom:0px; valign:bottom; 
			background-position: left; background-size: cover; border: 1px solid #eee; border-radius: 7px 0px 0px 0px; border-right:none;background-repeat:no-repeat;	">
		
		<tr>
			<td style="width:500px; height:127px;	" >
				<img src="../inc/img/cabecalho-relatorio-de-ensaio.png" alt="">
			</td>
			<td style="border: none; border-radius: 0px 7px 0px 0px;  border-left:none; text-align:right;vertical-align:bottom">
			<?
		if($row["logoinmetro"]=='Y'){
?>	
					<img src="../inc/img/selo-inmetron.png" border="0" style="height: 116px">
<?
		}
?>
			</td>
			<!-- <td style="width:171px;"  align="right">Rod. BR 365, KM 615 - B. Conj Alvorada<br>CEP 38407-180 - Uberl&acirc;ndia - MG<br>laudolab@laudolab.com.br<br>www.laudolab.com.br<br>Fone/Fax: (34) 3222-5700</td>-->
		</tr>
		
		</table>
	</div>
	</td>
	</tr>
	</thead> 
	
<?
	$pb="";
}else{
?>	
	
	<table style="<?=$pb?>;width:700px !important; margin:auto;  ">
	<thead>
	<tr style="position:relative;">
	<td>
	<a href="#" class="no-print" onclick="window.print();return false;"><div>Imprimir</div></a>
	<div style=" width:700px ;">
		<table class="cabtxt" style="width:100%;position:relative; z-index:2; background-color:#fff; margin-bottom:0px;">
		
		
		<tr>
			<td style="width:498px; height:141px; " >
			&nbsp;
			</td>
			<td style="border: none; border-radius: 0px 7px 0px 0px;  border-left:none; text-align:left; ">
<?
		if($row["logoinmetro"]=='Y'){
?>	
					<img src="../inc/img/selo-inmetron.png" border="0" style="height: 116px">
<?
		}
?>
			</td>
			<!-- <td style="width:171px;"  align="right">Rod. BR 365, KM 615 - B. Conj Alvorada<br>CEP 38407-180 - Uberl&acirc;ndia - MG<br>laudolab@laudolab.com.br<br>www.laudolab.com.br<br>Fone/Fax: (34) 3222-5700</td>-->
		</tr>
		
		</table>
	</div>
	</td>
	</tr>
	
	
	
<? }	
?>
<tr><td>
	<div class="" style="width:700px;position:relative;z-index:111111; margin:auto; left: 0;
  right: 0; 
  margin: 0 auto;
    text-align: right;">
	<div style="width: 700px;position:absolute; top:-3px; border-bottom:none;    border-top: 1px solid gray;
    border-top-style: dotted;margin: auto; text-transform:uppercase ">Imp.: <?=$codepress?>; Início pg. p/ [<?=$row["sigla"] ?>] reg.: [<?=$row["idregistro"]?>];</div></div><!-- Controle Impressao -->
	</td></tr></thead> 
	<?
	$pb="page-break-before: always;";
}


/*
 *maf09082011: finaliza no rodape o controle da impressao
 */
function imppagrodape($inisubpage){
	//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
	global $codepress;
	global $ipage;
	global $irestotal;
	global $row;

	
	/*if(!empty($inisubpage)){//Continua numeração normal das páginas
		//Numera as subpáginas
		$paginaatual = $ipage.".".$inisubpage;
	}else{*/
		$paginaatual = $ipage;
	//}

	?>

	<!-- INI: Rodape -->
	


	
	<tfoot>
	<tr>
	<td>
	<table style="width: 100%; top:-10px; position:relative;">		
<tr><td>
		<table class="tsep" style="width:100%;">
		<tr><td>
		<table class="tsep" style="width:100%;">
		<tr><td style="width:100%">
		<table style="width:100%" >
		<tr>
		<td > 
	<div class="nimptbot" style="width:100%; text-align:center;font-size:6px;">Imp: <?=$codepress?>; Fim pg. p/ [<?=$row["sigla"] ?>] reg.: [<?=$row["idregistro"]?>];</div>
	
	<div style="width:100%; text-align:center; background:#f7f7f7; padding-top:7px; padding-bottom:7px; line-height:12px;font-size:6px">
	<? echo ('<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;">Laudo Laboratório Avícola Uberlândia Ltda. CNPJ: 23.259.427/0001-04 - I.E.: 7023871770001.</span>
	<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;">Rodovia BR 365, S/N°. Alvorada. CEP: 38407-180 - Uberlândia-MG. (34) 3222-5700 - resultados@laudolab.com.br</span>'); ?>
	
	</div>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	</td>
	</tr>
	</table> 
	</td>
	</tr>
	</tfoot>
	</table>
	
	<!--<br><div class="nimptbot2">Este relatório atende aos requisitos de acreditação da Cgcre, que avaliou a competência do laboratório</div>
	<br><div class="nimptbot2">As opiniões e interpretações expressas acima não fazem parte do escopo da acreditação deste laboratório</div>
	-->
	
	<!-- FIM: Rodape -->

	<?
}

function cabecalhores(){

	//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
	global $row, $rowbio, $rowend;
	
	//verificar se usuario pode assinar o teste;
	$sqlass = "select assinateste from pessoa where assinateste = 'Y' and idpessoa =".$_SESSION["SESSAO"]["IDPESSOA"];
	$resass = mysql_query($sqlass) or die("Erro ao verificar se usuario assina testes: ".mysql_error());
	$qtdrowd= mysql_num_rows($resass);	
	
?>	
<tbody>

<tr>
   <td style="width: 100%">
   <div style="width:700px;">
      <table style="width: 100%">
         <tr>
            <td>
               <table class="tsep" style="width:100%; margin-top:6px;">
                  <tr>
                     <td   style="text-align:center; font-size:13px !important;">RELAT&Oacute;RIO DE ENSAIO - <?=$row["tipoteste"]?> <?=$rowbio['rotulo']?></td>
                  </tr>
                  <tr  >
                     <td  >
                        <table class="tsep" style="width:100%;">
                           <tr>
                              <td>
                                 <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                    <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
                                       <td colspan="2" style="font-size:11px;">EMPRESA
                                       </td >
                                    </tr>
                                    <tr>
                                       <td class="tdrot grrot" >Cliente:</td>
                                       <td class="tdval grval"  ><?=($row["nome"])?></td>
                                    </tr>
                                    <tr>
                                       <td class="tdrot grrot" >R. Social:</td>
                                       <td class="tdval grval"  ><?=($row["razaosocial"])?></td>
                                    </tr>
                                    <?
                                       $qtdend=count($rowend);
                                       if($qtdend>0){
                                       ?>			
                                    <tr>
                                       <td class="tdrot grrot" >Endere&ccedil;o:</td>
                                       <td class="tdval grval"> <?=($rowend["logradouro"])?> <?=($rowend["endereco"])?> 
                                          <?if($rowend['numero']){echo("N&ordm;:".$rowend['numero']);}?>
                                          <?if($rowend['complemento']){echo(", ".($rowend["complemento"]));}?>
                                          <?if($rowend['bairro']){echo(", BAIRRO: ". ($rowend['bairro']));}?>
                                          <?if($rowend['cep']){echo(", CEP:". formatarCEP($rowend['cep'],true) );}?>
                                          <?if($rowend['cidade']){echo(", ".($rowend['cidade']));}?>
                                          <?if($rowend['uf']){echo("-".$rowend['uf']);}?>
                                       </td>
                                    </tr>
                                    <?
                                       }
                                       ?>			
                                 </table>
                              </td>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
         <tr>
            <td>
               <table class="tsep" style="width:100%">
                  <tr>
                     <td>
                        <table class="tsep" style="width:100%; margin-top:0px; background-color:#fff;">
                           <!-- Cabecalho Superior -->
                           <tr>
                              <td	style="<?
                                 if(($row["granja"]) 
                                 	or ($row["tipoaves"])
                                 	or ($row["especiefinalidade"])
                                 	or ($row["nucleoamostra"])
                                 	or ($row["lote"])
                                 	or ($row["idade"]) or ($row['idservicoensaio'])){ echo 'width:50%;';}else{echo 'width:100%;';}
                                 ?> vertical-align:top;">
                                 <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;  ">
                                    <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
                                       <td colspan="2" style="font-size:11px;"><spam style="line-height:16px;">DADOS DA AMOSTRA</spam><span style="font-size:6px !important; width: 64%; text-align: right;">&nbsp; (As informa&ccedil;&otilde;es abaixo foram fornecidas pelo cliente) </span>
                                       </td >
                                    </tr>
                                    <tr>
                                       <td class="tdrot grrot">N&ordm; Registro:</td>
                                       <td class="tdval grval" >
					<b>
					<?
					//se usuario puder assinar
					if($qtdrowd > 0){
                                            $sqlm="select o.idobjeto
													from unidadeobjeto o 
													join "._DBCARBON."._modulo m on(m.modulo=o.idobjeto and m.modulotipo='resultado')
													where tipoobjeto = 'modulo' 
													and idobjeto like('result%') 
													and idunidade==".$row["idunidade"];
                                            $resm=mysql_query($sqlm) or die("Erro ao buscar unidade do modulo: ".mysql_error());
                                            $rowm=mysql_fetch_assoc($resm);
                                            
					?>
                                            <a style="text-decoration:none" title="Resultado" href="javascript:janelamodal('../?_modulo=<?=$rowm['idobjeto']?>&_acao=u&idresultado=<?=$row["idresultado"]?>')"><font style="color:#333 !important; font-weight:bold;"><?if($row["idunidade"]==4){?>B<?}?><?=$row["idregistro"]?></font></a>
					<?}else{?>
						<?=$row["idregistro"]?>
					<?}?>
					</b>                                       
					</td>
                                    </tr>
                                    <?if($row["dataamostraformatada"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Data Registro:</td>
                                       <td class="tdval grval"><?=$row["dataamostraformatada"]?></td>
                                    </tr>
                                    <?}?>
                                    <tr>
                                       <td  class="tdrot grrot" >Nº Amostra(s):</td>
                                       <td  class="tdval grval"><?=($row["nroamostra"])?></td>
                                    </tr>
									 <?if($row["subtipoamostra"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Subtipo:</td>
                                       <td class="tdval grval"><?=($row["subtipoamostra"])?></td>
                                    </tr>
									<?}?>
                                    <?if($row["estexterno"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Estudo:</td>
                                       <td class="tdval grval"><?=($row["estexterno"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["descricao"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Descri&ccedil;&atilde;o:</td>
                                       <td class="tdval grval"><?=($row["descricao"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["datacoleta"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Data Coleta:</td>
                                       <td class="tdval grval"><?=$row["datacoleta"]?></td>
                                    </tr>
                                    <?}elseif($row["normativa"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Data Coleta:</td>
                                       <td class="tdinf">Não Informado</td>
                                    </tr>
                                    <?}?>
                                    <?if($row["lacre"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Lacre:</td>
                                       <td class="tdval grval"><?=($row["lacre"])?></td>
                                    </tr>
                                    <?}elseif($row["normativa"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Lacre:</td>
                                       <td class="tdinf">Não Informado</td>
                                    </tr>
                                    <?}?>
                                    <?if($row["galpao"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Galp&atilde;o/Avi&aacute;rio:</td>
                                       <td class="tdval grval"><?=($row["galpao"])?></td>
                                    </tr>
                                    <?}elseif($row["normativa"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Galp&atilde;o/Avi&aacute;rio:</td>
                                       <td class="tdinf">Não Informado</td>
                                    </tr>
                                    <?}?>
                                    <?if($row["linha"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Linha:</td>
                                       <td class="tdval grval"><?=($row["linha"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["localcoleta"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Local Coleta:</td>
                                       <td class="tdval grval"><?=($row["localcoleta"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["tc"]){?>
                                    <tr>
                                       <td class="tdrot grrot">TC:</td>
                                       <td class="tdval grval"><?=($row["tc"])?></td>
                                    </tr>
                                    <?}elseif($row["normativa"]){?>
                                    <tr>
                                       <td class="tdrot grrot">TC:</td>
                                       <td class="tdinf">Não Informado</td>
                                    </tr>
                                    <?}?>
                                    <?if($row["tipo"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Tipo:</td>
                                       <td class="tdval grval"><?=($row["tipo"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["datafabricacao"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Data Fabrica&ccedil;&atilde;o:</td>
                                       <td class="tdval grval"><?=$row["datafabricacao"]?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["responsavel"]){?>
                                    <tr>
                                       <td class="tdrot grrot" >Respons&aacute;vel:</td>
                                       <td class="tdval grval"><?=($row["responsavel"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["responsavelof"]){?>
                                    <tr>
                                       <td class="tdrot grrot" >Resp. Oficial:</td>
                                       <td class="tdval grval"><?=($row["responsavelof"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["sexo"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Sexo:</td>
                                       <td class="tdval grval"><?=($row["sexo"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["nrodoses"]){?>
                                    <tr>
                                       <td class="tdrot grrot">N&ordm; Doses:</td>
                                       <td class="tdval grval"><?=($row["nrodoses"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["partida"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Partida:</td>
                                       <td class="tdval grval"><?=($row["partida"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["especificacao"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Especifica&ccedil;&otilde;es:</td>
                                       <td class="tdval grval"><?=($row["especificacao"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["fornecedor"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Fornecedor:</td>
                                       <td class="tdval grval"><?=($row["fornecedor"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["notafiscal"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Nota Fiscal:</td>
                                       <td class="tdval grval"><?=$row["notafiscal"]?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["vencimento"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Vencimento:</td>
                                       <td class="tdval grval"><?=$row["vencimento"]?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["semana"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Semana:</td>
                                       <td class="tdval grval"><?=($row["semana"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["identificacaochip"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Identifica&ccedil;&atilde;o/Chip:</td>
                                       <td class="tdval grval"><?=($row["identificacaochip"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["diluicoes"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Dilui&ccedil;&otilde;es:</td>
                                       <td class="tdval grval"><?=($row["diluicoes"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["nroplacas"]){?>
                                    <tr>
                                       <td class="tdrot grrot">N&ordm; Placas:</td>
                                       <td class="tdval grval"><?=($row["nroplacas"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["fabricante"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Fabricante:</td>
                                       <td class="tdval grval"><?=($row["fabricante"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["sexadores"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Sexadores:</td>
                                       <td class="tdval grval"><?=($row["sexadores"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["localexp"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Local Espec&iacute;fico:</td>
                                       <td class="tdval grval"><?=($row["localexp"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["pedido"]){?>
                                    <tr>
                                       <td class="tdrot grrot">N&ordm; Clifor/Pedido:</td>
                                       <td class="tdval grval"><?=($row["pedido"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if($row["nsvo"]){?>
                                    <tr>
                                       <td class="tdrot grrot">N&ordm; SVO:</td>
                                       <td class="tdval grval"><?=($row["nsvo"])?></td>
                                    </tr>
                                    <?}elseif($row["normativa"]){?>
                                    <tr>
                                       <td class="tdrot grrot">N&ordm; SVO:</td>
                                       <td class="tdinf">Não Informado</td>
                                    </tr>
                                    <?}?>					    
                                    <?if($row["cpfcnpjprod"]){?>
                                    <tr>
                                       <td class="tdrot grrot">CPF/CNPJ:</td>
                                       <td class="tdval grval"><?=formatarCPF_CNPJ($row["cpfcnpjprod"],true); ?></td>
                                    </tr>
                                    <?}elseif($row["normativa"]){?>
                                    <tr>
                                       <td class="tdrot grrot">CPF/CNPJ:</td>
                                       <td class="tdinf">Não Informado</td>
                                    </tr>
                                    <?}?>
                                    <?if($row["cidade"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Cidade:</td>
                                       <td class="tdval grval"><?=($row["cidade"])?>-<?=$row["uf"]?></td>
                                    </tr>
                                    <?}elseif($row["normativa"]){?>
                                    <tr>
                                       <td class="tdrot grrot">Cidade:</td>
                                       <td class="tdinf">Não Informado</td>
                                    </tr>
                                    <?}?>
                                    <?if(!empty($rowbio["volume"]) and !empty($row['idservicoensaio'])){?>
                                    <tr>
                                       <td class="tdrot grrot">Vol. Aplicado:</td>
                                       <td class="tdval grval"><?=($rowbio["volume"])?></td>
                                    </tr>
                                    <?}?>				
                                    <?if(!empty($rowbio["doses"]) and !empty($row['idservicoensaio'])){?>
                                    <tr>
                                       <td class="tdrot grrot">Nº Doses:</td>
                                       <td class="tdval grval"><?=($rowbio["doses"])?></td>
                                    </tr>
                                    <?}?>
                                    <?if(!empty($rowbio["via"]) and !empty($row['idservicoensaio'])){?>
                                    <tr>
                                       <td class="tdrot grrot">Via de Inoculação:</td>
                                       <td class="tdval grval"><?=($rowbio["via"])?></td>
                                    </tr>
                                    <?}?>				
                                    <?if(!empty($rowbio["coranilha"]) and !empty($row['idservicoensaio'])){?>
                                    <tr>
                                       <td class="tdrot grrot">Cor da Anilha:</td>
                                       <td class="tdval grval"><?=($rowbio["coranilha"])?></td>
                                    </tr>
                                    <?}?>
                                 </table>
                              </td>
                              <?
                                 if(($row["granja"]) 
                                 	or ($row["tipoaves"])
                                 	or ($row["especiefinalidade"])
                                 	or ($row["nucleoamostra"])
                                 	or ($row["lote"])
                                 	or ($row["idade"]) or ($row['idservicoensaio'])){
                                 ?>
                              <td class="td" style="width:50%; vertical-align:top;background-color:#fff" align="right">
                                 <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                    <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
                                       <td colspan="2" style="font-size:11px;font-family:Verdana, Geneva, sans-serif !important;">
                                          ORIGEM
                                          
                                       </td >
                                    </tr>
                                    <?//buscar registro do bioterio
                                       if(!empty($rowbio['idregistro']) and !empty($row['idservicoensaio'])){
                                       ?>
                                    <tr><td class="tdrot grrot">Registro Biot&eacute;rio:</td>				
                                    <td class="tdval grval">B<?=($rowbio['idregistro'])?></td>
                                    </tr>
                                    <?
                                       }
                                       ?>
                                    <?if($row["granja"]){?><tr><td class="tdrot grrot">Granja:</td><td class="tdval grval"><?=($row["granja"])?></td></tr><?}elseif($row["normativa"]){?><tr><td class="tdrot grrot">Granja:</td><td class="tdinf">Não Informado</td></tr><?}?>
                                    <?if($row["tipoaves"]){?><tr><td class="tdrot grrot">Tipo Aves:</td><td class="tdval grval"><?=($row["tipoaves"])?></td></tr><?}?>
                                    <?if($row["especiefinalidade"]){?><tr><td class="tdrot grrot">Espécie:</td><td class="tdval grval"><?=($row["especiefinalidade"])?></td></tr><?}?>
                                    <?if($row["nucleo"]){?><tr><td class="tdrot grrot">N&uacute;cleo:</td><td class="tdval grval"><?=($row["nucleo"])?></td></tr><?}?>
                                    <?if($row["nucleoorigem"]){?><tr><td class="tdrot grrot">N&uacute;cleo Origem:</td><td class="tdval grval"><?=($row["nucleoorigem"])?></td></tr><?}elseif($row["normativa"]){?><tr><td class="tdrot grrot">N&uacute;cleo Origem:</td><td class="tdinf">Não Informado</td></tr><?}?>
                                    <?if($row["clienteterceiro"]){?><tr><td class="tdrot grrot">Cliente Terceiro:</td><td class="tdval grval"><?=($row["clienteterceiro"])?></td></tr><?}?>
                                    <?
                                       if($row["regoficial"]){?><tr><td class="tdrot grrot">N&ordm; Reg. Oficial:</td><td class="tdval grval"><?=($row["regoficial"])?></td></tr>
                                    <?	if($row["idsecretaria"]){?><tr><td class="tdrot grrot">Org&atilde;o Oficial:</td><td class="tdval grval"><?=(traduzid("pessoa","idpessoa","nome",$row["idsecretaria"]))?></td></tr><?}
                                       }
                                       ?>
                                    <?if($row["lote"]){?><tr><td class="tdrot grrot">Lote:</td><td class="tdval grval"><?=($row["lote"])?></td></tr><?}?>
                                    <?if($row["idade"]){?><tr><td class="tdrot grrot">Idade:</td><td class="tdval grval"><?=($row["idade"])?>&nbsp;<?=$row["tipoidade"]?></td></tr><?}elseif($row["normativa"]){?><tr><td class="tdrot grrot">Idade:</td><td class="tdinf">Não Informado</td></tr><?}?>
                                    <?//mostrar numero de aves do bioterio
                                       if(!empty($rowbio['qtd']) and !empty($row['idservicoensaio']) and !empty($row["quantidadeteste"])){
                                       ?>
                                    <tr><td class="tdrot grrot">N&ordm; de Animais:</td>				
                                    <td class="tdval grval"><?=($row["quantidadeteste"])?></td>
                                    </tr>
                                    <?
                                       }
                                       ?>
                                    <?if(!empty($rowbio['rot']) and !empty($rowbio['gaiola'])){?><tr><td class="tdrot grrot">Local:</td><td class="tdval grval"><?=($rowbio['rot'])?> - GAIOLA <?=$rowbio['gaiola']?></td></tr><?}?>
                                    <?if(!empty($rowbio["coranilha"]) and !empty($row['idservicoensaio'])){?><tr><td class="tdrot grrot">Cor da Anilha:</td><td class="tdval grval"><?=($rowbio["coranilha"])?></td></tr><?}?>
                                 </table>
                              </td>
                              <?
                                 }else{
                                 ?>
                           
                              <?
                                 }
                                 ?>
                           </tr>
                        </table>
                     </td>
                  </tr>
               </table>
               <?
                  //$varobs = str_replace(chr(13),"<br>",$row["observacao"]);
                  $varobs = nl2br($row["observacao"]);
                  if(str_replace(chr(13),"",str_replace(chr(10),"",$row["observacao"]))!=""){
                  ?>
            </td>
         </tr>
         <tr>
			 <td>		
				 <table class="tsep" style="width:100%">
					 <tr>
						 <td>
							 <table class="tsep" style="width:100%; margin-top:0px"><!-- Cabecalho Superior -->
								 <tr>
									 <td class="td" style="width:100%">
										 <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
											 <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
												 <td style="font-size:11px;">Observa&ccedil;&otilde;es
												 </td >
											 </tr>
											 <tr>
												<td class="tdval grval"><?=($varobs)?>
											 </td >
											 </tr>
										 </table>
									 </td>
								 </tr>
							 </table>
						 </td>
					 </tr>
				 </table>
			 </td>
         </tr>
         <?
            }//if(str_replace(chr(13),"",str_replace(chr(10),"",$row["observacao"]))!=""){
            ?>
         
         
      </table>
	  </div>
   </td>
</tr>		 	
	
<?	 
}

function assinaturarodape($inidresultado){
		global $arrassinat;
        $qtdrowss= count($arrassinat);
		//se não foi assinado nao imprime assinatura
        if($qtdrowss > 0){
			?>
			<tr>
			<td style="width: 100%">
			<div style="width:700px;">
	<table style="width: 100%; top:-7px; position:relative;" id="assinatura">		
<tr><td>
		<table class="tsep" style="width:100%;">
		<tr><td>
		<table class="tsep" style="width:100%;">
		<tr><td class="td" style="width:100%">
		<table style="width:100%">
		<tr>
		<td class="tdval grval" style="background-color:#fff;"> 
			
			
			<?
	        echo "<div align='center' style='margin:0px;padding:0px;border:none; width:100%'> \n";
	        
	        echo "<table class='tabass' style='width:100%'> \n";
	        echo "<tr> \n";
			foreach($arrassinat as $i => $rowass) {

					$nomresp="";
					$crmvresp="";
					
					//troca dados do responsavel via hardcode
					switch ($rowass["idpessoa"]) {
						case 782://edison
							$nomresp = "EDISON ROSSI";
							$crmvresp = "CRMV - MG N&ordm; 1626";
						;
						break;
						case 1484://edison
						//Alteração realizada pois as pessoas abaixo ainda não possuem assinatura digital - LTM (30-07-2020)
						case 8209://Daniel Henrique
						case 7188://Leandro Cardoso
						case 99141://Ana Reativa
							$nomresp = "EDISON ROSSI";
							$crmvresp = "CRMV - MG N&ordm; 1626";
						;
						break;
						case 797://marcio
							$nomresp = "MARCIO BOTREL";
							$crmvresp = "CRMV - MG N&ordm; 1454";
						;
						break;
						case 1483://marcio
							$nomresp = "MARCIO BOTREL";
							$crmvresp = "CRMV - MG N&ordm; 1454";
						;
						break;
						default:
						null;
						break;
					}
	
					if($nomresp){
						
						$arrAss = assinaturaDigitalA1($rowass["idresultado"].$rowass["criadoem"].$rowass["idpessoa"], $inUsuarioSislaudo);
						
						echo "<td align='center'>";
						echo "<img src='../inc/img/sig".strtolower(trim($rowass["idpessoa"])).".gif' height='48px'> \n";
						echo "<p><label class='lbresp' style='font-weight:bold'>".($nomresp)."</label></p>";
						echo "<p><label class='lb6'>Respons&aacute;vel T&eacute;cnico: ".($crmvresp)."</label> </p>";
						echo "<p> </p>";
						echo "<p><label class='lb6'>Assinatura Digital: ".$arrAss["assinatura"]."</label> | <label class='lb6'>Data Assinatura:".$rowass["criadoem"]."</label> </p>";
						echo "<p><label class='lb6'><img width='6px;' src='../inc/img/secure15.png'> Autorização Certificado Digital Serpro: ".$arrAss["autorizacaoserpro"]."</label> \n";
						echo "</td>";
					}
	        }
	       
	        echo "</tr> \n";
	        echo "</table> \n";
	       
	        echo "</div> \n";
			?>
			
			
				</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	</td>
	</tr>
	</table>
	</div> 
		</td>
		</tr>
		</tbody>

	
	<?
        } 
}

/*
 * Monta relatorio descritivo, geado a partir do RTE
 */
 function remove_empty_tags_recursive ($str, $repto = NULL)
{
    //** Return if string not given or empty.
    if (!is_string ($str)
        || trim ($str) == '')
            return $str;

    //** Recursive empty HTML tags.
    return preg_replace (

        //** Pattern written by Junaid Atari.
        '/<([^<\/>]*)>([\s]*?|(?R))<\/\1>/imsU',

        //** Replace with nothing if string empty.
        !is_string ($repto) ? '' : $repto,

        //** Source string
        $str
    );
}

function relresultado($mostraass, $ocultar){

	
		
	
	
	
	//invoca para dentro deste contexto desta funcao as variaveis ja existentes fora dela. Isto permite que os valores da query do relatorio sejam utilizados aqui
	global $echosql, $row, $rtitulos, $arrgrafgmt, $modelo, $modo, $grafico, $tipogmt, $arrprodservtipoopcao, $arrprodservtipoopcaoespecie, $arrlotecons, $templateinterpretacao;
	global $ins_nomepartida, $ins_fabricante, $ins_partidaext, $ins_fabricacao, $ins_vencimento; 

	//$echosql = true;
	
	$prodservcongelada = true;
	
		if ($prodservcongelada == true){
				  
			foreach ($arrlotecons as $i => $linhai) {
				
				$ins_nomepartida[$i]			= $linhai['descr'];
				$ins_fabricante[$i]				= $linhai['fabricante'];
				$ins_partidaext[$i]				= $linhai['partidaext'];
				$ins_fabricacao[$i]				= $linhai['fabricacao'];
				$ins_vencimento[$i]				= $linhai['vencimento'];
			
			}
		}else{
		$sqli = "
			select 
				c.qtdd, 
				c.qtdd_exp,
				pl.descr,
				l.spartida,
				l.partidaext,
				DATE_FORMAT(l.vencimento, '%d/%m/%Y') as vencimento,
				DATE_FORMAT(l.fabricacao, '%d/%m/%Y') as fabricacao,
				l.fabricante
			FROM lotecons c
			JOIN lote l ON c.idlote=l.idlote
			JOIN prodservformulains i ON i.idprodserv=l.idprodserv
			JOIN prodservformula p ON p.idprodservformula = i.idprodservformula
			JOIN prodserv pl ON pl.idprodserv = l.idprodserv
			WHERE 
			c.tipoobjeto ='resultado' 
			and c.idobjeto ='".$row['idresultado']."'
			and p.idprodserv = '".$row['idtipoteste']."'
			and c.qtdd>0
		   	and i.listares='Y';";
			$resi=mysql_query($sqli) or die("Erro ao buscar as pesagens para listagem sql 1".$sqlind);
		$x = 1;
		while($linhai=mysql_fetch_assoc($resi)){
			$ins_nomepartida[$x]			= $linhai['descr'];
			$ins_fabricante[$x]				= $linhai['fabricante'];
			$ins_partidaext[$x]				= $linhai['partidaext'];
			$ins_fabricacao[$x]				= $linhai['fabricacao'];
			$ins_vencimento[$x]				= $linhai['vencimento'];
			$x++;
		}
	}
	//echo count($arrprodservtipoopcaoespecie);
	//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
	if ($prodservcongelada == true){
		$modelo 				= $row['modelo'];
		$modo 					= $row['modo'];
		$tipogmt 				= $row['tipogmt'];
		$comparativodelotes 	= $row['comparativodelotes'];
		
	}else{
	//mcc - 28/11/2018 - pegar a configuração direto da prodserv
	$sqlc =
	"SELECT
		modelo,
		modo,
		tipogmt,
		comparativodelotes
	FROM
		prodserv
	WHERE
		idprodserv = '".$row['idtipoteste']."';";
		
	$resc=mysql_query($sqlc) or die("Erro ao buscar as pesagens para listagem sql 1".$sqlind);
		$y=0;
		while($linha=mysql_fetch_assoc($resc)){
			$modelo 				= $linha['modelo'];
			$modo 					= $linha['modo'];
			$tipogmt 				= $linha['tipogmt'];
			$comparativodelotes 	= $linha['comparativodelotes'];
		}
	}
	
	//Recupera os rotulos de orificios para os valores GMT
		$rot = array();
		
		$rot = $rtitulos;
		
if (!empty($row['idespeciefinalidade']) ){	

	if ($prodservcongelada == true){
		//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
		$c = 0;
		foreach ($arrprodservtipoopcaoespecie as $i => $linhad) {
			 
				$idprodservtipoopcaoinicio[$c] 		= $linhad['valorinicio'];
				$idprodservtipoopcaofim[$c] 		= $linhad['valorfim'];
				$cor[$c] 							= $linhad['cor'];
				$msg[$c] 							= $linhad['msg'];
				switch($cor[$c]){
					case 'azul':
						$cor[$c] = '#00ffff';
						break;
					case 'amarelo':
						$cor[$c] = '#ffff00';
						break;
					case 'vermelho':
						$cor[$c] = '#ff0000';
						break;
					case 'verde':
						$cor[$c] = '#00ff00';
						break;
				}
				 
				$c++;
			}
			
	}else{
		//mcc - 28/11/2018 - pegar a configuração direto da prodserv
	  $sqld = "
		SELECT
			idespeciefinalidade,
			idprodservtipoopcaoinicio,
			idprodservtipoopcaofim,
			cor,
			ptoI.valor as valorinicio,
			ptoF.valor as valorfim,
			msg
		FROM
			prodservtipoopcaoespecie ptoe
		LEFT JOIN
			prodservtipoopcao ptoI on ptoI.idprodservtipoopcao = ptoe.idprodservtipoopcaoinicio
		LEFT JOIN
			prodservtipoopcao ptoF on ptoF.idprodservtipoopcao = ptoe.idprodservtipoopcaofim
		WHERE
			ptoe.idprodserv = '".$row['idtipoteste']."' AND
			status = 'ATIVO' AND
			idadeinicio <= '".$row['idade']."' AND
			idadefim >= '".$row['idade']."' AND
			idespeciefinalidade = '".$row['idespeciefinalidade']."'
			order by
			idprodservtipoopcaoinicio;";
		
			$resd=mysql_query($sqld) or die("Erro ao montar configuração de gráfico ".$sqlind);
			
			$c = 0;
			while($linhad=mysql_fetch_assoc($resd)){
				$idprodservtipoopcaoinicio[$c] 		= $linhad['valorinicio'];
				$idprodservtipoopcaofim[$c] 		= $linhad['valorfim'];
				$cor[$c] 							= $linhad['cor'];
				$msg[$c] 							= $linhad['msg'];
				switch($cor[$c]){
					case 'azul':
						$cor[$c] = '#00ffff';
						break;
					case 'amarelo':
						$cor[$c] = '#ffff00';
						break;
					case 'vermelho':
						$cor[$c] = '#ff0000';
						break;
					case 'verde':
						$cor[$c] = '#00ff00';
						break;
				}
				 
				$c++;
			}
	}
			
		//Verifica se e 'Semanas', nao gerar grafico GMT
		$boolsem = strpos(strtoupper($row["tipoidade"]), "SEM");
		if ($boolsem === false) { //Atenção para utilização do '===': $boolsem retorna como encontrado na posição 0 a string procurada. 0=False, portanto, com === se força a tratar somente booleanos
			$boolsem = false;
		} else {
			$boolsem = true;
		}

		
		if($echosql){echo "<!-- Rótulos Orifícios: \n";print_r($rot);echo "\n -->";}

		if(empty($rot)){
			echo "<!-- Erro recuperando Titulos dos orificios GMT. O Array veio vazio. Provavelmente os Titulos nao estao cadastrados -->";
		}

		$arrrotulo   = array();//guarda os valores do orificio
		$arrorificio = array();//guarda os orifio
		$arrfrasecab = array();	//guarda a frase padrao
		$arrcolorbar = array(); //guarda a cor do grafio	
		
		$y=0;


		


		//die($tipogmt.' '.$modo);
		if ($tipogmt == "GMT"){	

			if ($modo == "AGRUP"){

				for ($i = 1; $i <= 13; $i++) {//roda nos 13 orificios

					//se o oficio foi marcado alguma vez
					if($row["q".$i] > 0){
						$arrorificio[$y] = $row["q".$i];//guarda quantas aves no array
						$qtdorificio = $qtdorificio + $row["q".$i];//soma a quantidade de aves no orifcio
						$arrrotulo[$y] = $rot[$i]; //guarda o valor do titulo 
					
						if($row["q".$i]>1){
							$arrfrasecab[$y] = $row["q".$i]." Amostras apresentaram título ".$rot[$i];
						}else{
							$arrfrasecab[$y] = $row["q".$i]." Amostra apresentou título ".$rot[$i];
						}
			
						$c = 0;
						
						while ($c < count($idprodservtipoopcaoinicio)){
							
							if ($rot[$i] >= $idprodservtipoopcaoinicio[$c] and $rot[$i] <= $idprodservtipoopcaofim[$c]){
							
								$qtd[$c] = $qtd[$c] + $row["q".$i];
								$arrcolorbar[$y] = $cor[$c];
								 
							}
							
							$c++;						
						}
						$y++;      	
					}
				}

				$c = 0;
				while ($c < count($idprodservtipoopcaoinicio)){
					$perc[$c] =round((($qtd[$c] * 100)/ $qtdorificio),2);			
					$c++;
				}
				
			 	$urlimg = graftitulo($arrrotulo,$arrorificio,$arrcolorbar);
					
					 
			}elseif ($modo == "IND"){
				
				if ($prodservcongelada == true){
					//mcc - 28/11/2018 - pegar a configuração da prodserv congelada 
					$c = 1;
					
					foreach ($arrprodservtipoopcao as $i => $linhad) {
						
						if ( $linhad['valor'] == '0.0'){
							 $linhad['valor'] = 0;
						}
						$strind[$c] = $linhad['valor'];
						$c++;
					}
				}else{
					$sqli = "SELECT
									valor
								FROM
									prodservtipoopcao
								WHERE
									idprodserv = '".$row['idtipoteste']."'
								ORDER BY
									valor*1, valor";
					$resi=mysql_query($sqli) or die("Erro ao buscar orifícios".$sqlind);
					$y = 1;
					while($rowi=mysql_fetch_assoc($resi)){	
					if ( $rowi['valor'] == '0.0'){
							 $rowi['valor'] = 0;
						}
						
						$strind[$y] = $rowi['valor'];
						$y++;
					}
				}

				// o resultado individual não possui quantidade de orificios predefinida por este motivo  e gerada uma nova tabela resultadoindividual
				
				  $sqlind="
							select 
								count(*) as qtdorificio,
								-- r.identificacao,
								r.resultado
							from 
								resultadoindividual r
							where 
								r.resultado is not null
								and r.idresultado = '".$row['idresultado']." '
							group by 
								resultado 
							order by 
								r.resultado*1, r.resultado";
				
				$resind=mysql_query($sqlind) or die("Erro ao buscar resultados dos orificios do teste do bioensaio sql".$sqlind);
				$y = 0;
				while($rowind=mysql_fetch_assoc($resind)){
					$arrorificio[$y] = $rowind['qtdorificio'];//guarda quantas aves no array
					$qtdorificio 	 = $qtdorificio + $rowind['qtdorificio'];//soma a quantidade de aves no orifcio
					$arrrotulo[$y] 	 = $strind[$rowind['resultado']]; //guarda o valor do titulo
					
					$c = 0;
						
					while ($c < count($idprodservtipoopcaoinicio)){
						if ($arrrotulo[$y] >= $idprodservtipoopcaoinicio[$c] and $arrrotulo[$y] <= $idprodservtipoopcaofim[$c]){
							$arrcolorbar[$y] = $cor[$c];
						}
						$c++;						
					}
					$y++;
				}
				
				
				$urlimg = graftitulo($arrrotulo,$arrorificio,$arrcolorbar);
			}


				$arrrgmt            = array();
				$arridade           = array();
				

				$x=0;
				
				if ($comparativodelotes){
					foreach ($arrgrafgmt as $i => $idadegmt) {
						//maf040511: solicitação de Daniel: retirar a condicao de 'gtm > 0' porque os registros 5977 e 5984 de 2011 não estavam mostrando os resultados  de GMTs zerados
						//if($arrgrafgmt["gmt"] > 0){
							$arrrgmt[$x]= $idadegmt["gmt"];
							$arridade[$x]= $idadegmt["idade"];
							$x++;
						//}	
					}
					//se o array não estiver vazio e se for semanas mostra o grafico		
					if(!empty($arrrgmt) and !empty($arridade) and $boolsem==true){
						$urlimggmt = grafgmt($arrrgmt,$arridade,$maiorgmt);
					}
				}
																	
		}else if ($tipogmt == "ART"){	
		
		 $sqlind="
							select 
								count(*) as qtdorificio,
								r.identificacao,
								r.resultado
							from 
								resultadoindividual r
							where 
								r.resultado is not null
								and r.idresultado = '".$row['idresultado']." '
							group by 
								resultado 
							order by 
								r.resultado";
				
				$resind=mysql_query($sqlind) or die("Erro ao buscar resultados dos orificios do teste do bioensaio sql".$sqlind);
				$y = 0;
				while($rowind=mysql_fetch_assoc($resind)){
					$arrorificio[$y] = $rowind['qtdorificio'];//guarda quantas aves no array
					$qtdorificio 	 = $qtdorificio + $rowind['qtdorificio'];//soma a quantidade de aves no orifcio
					$arrrotulo[$y] 	 = $rowind['resultado']; //guarda o valor do titulo
					
					$c = 0;
						
					while ($c < count($idprodservtipoopcaoinicio)){
						if ($arrrotulo[$y] >= $idprodservtipoopcaoinicio[$c] and $arrrotulo[$y] <= $idprodservtipoopcaofim[$c]){
							$arrcolorbar[$y] = $cor[$c];
						}
						$c++;						
					}
					$y++;
				}

				$urlimg = graftitulo($arrrotulo,$arrorificio,$arrcolorbar);
				
		}
}
	

	
	
?>
<tr>
<td style="width: 100%">
<div style="width:700px;">
      <table style="width: 100%;position:relative;">

<tr>
 <td style="width: 100%">		
	 <table class="tsep" style="width:100%;">
		 <tr>
			 <td>
				 <table class="tsep" style="width:100%; margin-top:0px;"><!-- Cabecalho Superior -->
					 <tr>
						 <td style="width:100%">
							 <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
								 <tr style="background-color:#f7f7f7; font-size:11x; text-transform:uppercase;	height:20px;">
									<td style="font-size:11px;width:50%">Resultado <span style="font-size:6px !important;">(<?=$row["quantidadeteste"];?> teste(s) realizado(s))</span> </td >
								 <?if($row["versao"]<1){$versao=1;}else{$versao=$row["versao"];}?>
									<td style="vertical-align:top;text-align:right !important; width:50%; font-size:8px; line-height: 16px; padding-top: 1px" align="right" >
									 <?
									if($versao>1){
										$verant=$versao-1; 
											echo("<span style='font-size:6px !important;'>Este relatório substitui o de n° ".$row["idresultado"].".".$verant."</span>");                                
									}                                
									?> 
									ID Teste:<font style='font-weight:bold'><?=$row["idresultado"]?><?if($versao>0){?>.<?echo $versao;}?></font>
								
									</td>
								 </tr>

							 </table>
						 
                     
							 
                        <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                           <tr>
                              <td class="tdval grval">
                                 <br>
                                 <table style="width:100%">
								 
									  <?
										  if ($modelo =="UPLOAD"){//Se for elisa, quebrar a tabela em partes iguais para nao gerar paginas 'soltas' na impressao
												relelisa($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"],$row["idespeciefinalidade"],$mostraass, 1, $row["textointerpretacao"], $row["textopadrao"]);
											}else{
											?>
                                     <tr >
									<td style="vertical-align:top;width:64%">
									

	
	
                                          <fieldset class="fset" style="border:none;">
                                             <? // <legend><font class="ftitulo">&nbsp;Resultado *&nbsp;</font></legend> //?>
											  <div class="resdesc" id="resm" style="vertical-align:top">
											  
											
                                             <?
											  
                                                	$sqla="select caminho from arquivo where idobjeto = '".$row['idresultado']."' and tipoobjeto = 'resultado'";
                                                		$resa=mysql_query($sqla);
                                                		$rowa=mysql_fetch_assoc($resa);
                                                		
                                                		$row["caminho"] = $rowa['caminho'];
														
														
												if (file_exists($row["caminho"])) {		
													echo '<a href="'.$row["caminho"].'" target="_blank"><img src="../inc/img/pdf-icon2.png"  style="position: absolute;right: 8px;top: 28px;"></a>';
                                                }
												
                                                //hermes: Na tela de assinatura, colocar o texto de inclusao resultado no teste caso ele esteja aberto e com o descritivo vazio
                                                if(empty($row["descritivo"]) and $row['status']=='ABERTO' and !empty($row['idtipoteste'])){
                                                	$sqlt="select textoinclusaores from prodserv where idprodserv =".$row['idtipoteste'];
                                                		$rest=mysql_query($sqlt);
                                                		$rowt=mysql_fetch_assoc($rest);
                                                		
                                                		$row["descritivo"] = $rowt['textoinclusaores'];
														
														
														
														
                                                }
												$row["descritivo"] = str_replace("&nbsp;", "", $row["descritivo"]);
												$row["descritivo"] = preg_replace('/<P[^>]*>\s*?<\/P[^>]*>/', '', $row["descritivo"]);
                                                $row["descritivo"] = preg_replace('/(<[^>]+) style=".*?"/i', '$1',  $row["descritivo"]);
												$row["descritivo"] = preg_replace('/(<[^>]+) align=".*?"/i', '$1',  $row["descritivo"]);
                                                //Escreve diretamente na tela o resultado descritivo gerado pelo RTE
                                               echo(($row["descritivo"]));
											    
												//echo preg_replace('/\n(\s*\n){2,}/', "\n\n", $row["descritivo"]);
                                                //Escreve rodape padrao
                                                
                                                //print_r($_SERVER);
                                                
													
													
													
													if ($modo == 'IND'){
														
														
														
														 $sqlind="
															select 
																ri.identificacao,
																ri.resultado
															from 
																resultadoindividual ri
															join
																resultado r on r.idresultado = ri.idresultado
															where 
																ri.idresultado = ".$row['idresultado']." 
															order 
																by ri.idresultadoindividual";
													
														$resind=mysql_query($sqlind) or die("Erro ao buscar identificação e resultados dos orificios do teste do bioensaio sql".$sqlind);
														$y=1;
														
														$total = mysql_num_rows($resind);
														while($rowind=mysql_fetch_assoc($resind)){
															if ($y > ($total/2)){
																echo '</ul>';
																$y = 1;
															}
															if ($y == 1){
																echo '<ul style="width:40%;vertical-align:top; margin-bottom:0px; padding-left:12px; float:left; margin-top:0px;">';
															}
															
															if(!empty($rowind['identificacao'])){
																
																if($tipogmt == 'GMT'){
																	echo "<li>Ave ".$rowind['identificacao']." apresentou título ".$strind[$rowind['resultado']]."</li>";
																}elseif($tipogmt == 'ART' ){
																	echo "<li>Ave ".$rowind['identificacao']." pesou ".$rowind['resultado']." (GR)</li>";
																}else{
																	echo "<li>Amostra ".$rowind['identificacao']." apresentou resultado ".$rowind['resultado']."</li>";
																	
																}
																
																
															}else{
																echo "<li>Amostra ".$rowind['identificacao']." apresentou resultado ".$rowind['resultado']."</li>";
															}															
															$y++;														
														}
														if($tipogmt == "GMT"){
															echo "<li>Média Geométrica dos títulos: ".$row["gmt"]."</li>";
														}else if($tipogmt == "ART"){
															echo "<li>Média Aritmética das pesagens: ".$row["gmt"]."</li>";
														}

														if ($y > 0){
															echo '</ul>';
														}
													}else if ($modo == 'AGRUP'){
														
														for ($i = 1; $i <= 13; $i++) {//roda nos 13 orificios
													
															//se o oficio foi marcado alguma vez
															if($row["q".$i] > 0){
																
													
																if($row["q".$i]>1){
																	echo "<li>".$row["q".$i]." Amostras apresentaram título ".$rot[$i];
																	
																}else{
																	echo "<li>".$row["q".$i]." Amostra apresentou título ".$rot[$i];
																}
													
															
																$y++;      	
															}
														}
														if($tipogmt == "GMT"){
															echo "<li>Média Geométrica dos títulos: ".$row["gmt"]."</li>";
														}else if($tipogmt == "ART"){
															echo "<li>Média Aritmética das pesagens: ".$row["gmt"]."</li>";
														}
													}

                                                ?>
                                                   <?
                                                   
                                                   if(($z % 50  ) == 0 and $z > 0){
                                                   echo '</ul><div style="page-break-before: always; "></div><ul style="width:100%; margin-top:0px; margin-bottom:0px;padding-left:12px;">';
                                                   ?>
                                                <?
                                                   }
												   
                                                   ?>
												   
                                                <?
                                                   if(!empty($frasepronta) and !empty($rot["msgmin"]) and $row["geralegenda"]=='Y'){
                                                   ?>			
                                                <li>
                                                   
                                                     <?=($frasepronta)?>
                                                 </li> 
                                                <?
                                                   }
                                                   //INCLUIDO A FRASE PARA AVES QUE ESTÃO COM TITULO ACIMA DO MAXIMO
                                                   if(!empty($frasepronta2) and !empty($rot["msgmax"]) and $row["geralegenda"]=='Y'){
                                                   ?>			
                                                <li>
                                                      <?=($frasepronta2)?>
                                                 </li>
                                                <?
                                                   }
                                                   
                                                   ?>
                                             </ul>
											
                                             </div>
										   </fieldset>
										   
										  
										  
											  
											  
											  <?				
											if(!empty($row["textointerpretacao"]) or trim($row["textointerpretacao"])!= "" or !empty($row["interfrase"]) or trim($row["interfrase"])!= ""){
										 ?>		
 
										   <BR>
											 <fieldset class="fset">
                                             <legend><font class="ftitulo">&nbsp;Interpretação *&nbsp;</font></legend>
											 
											 <? echo $templateinterpretacao; ?>
											  <div class="resdesc" id="inter" style="font-size:8px !important ;">
                                         
                                             <div id="fraseedicao" class="divfrase" style="width:100%"><?=($row["interfrase"])?><Br><?=($row["textointerpretacao"])?> 
                                                <input id='idfrasedit' type="hidden" value="<?=strip_tags($row['interfrase']);?>">
                                             </div>
											 

                                         
											<?	if(!empty($row["idade"]) and !empty($row["tipoidade"])){?>
                                          
                                          <table class="tablegenda"style="width:100%">
                                             <tr>
                                                <td>* Para inserção da interpretação não foram considerados registros posteriores a <?=($row["idade"])?> <?=($row["tipoidade"])?></td>
                                             </tr>
                                          </table>
                                          <?
												}
												
												?>
												  </div>
										  </fieldset>
										<?
                                             }elseif($mostraass==false){
                                             ?>	
 
										   <BR> 											 
                                          <fieldset class="fset">
                                             <legend><font class="ftitulo">&nbsp;Interpretação *&nbsp;</font></legend>
											 
											 <? echo $templateinterpretacao; ?>
											  <div class="resdesc">
                                             <div id="fraseedicao" class="divfrase" style="width:100%"><textarea  rows='5' cols='40' id='idfrasedit' tabindex="1"><?=($row["interfrase"])?></textarea>
											 <br>
											 <?=($row["textointerpretacao"]);?></div>
                                          
                                          <?	if(!empty($row["idade"]) and !empty($row["tipoidade"])){?>
                                          <table class="tablegenda" style="width:100%">
                                             <tr>
                                                <td>* Para inserção da interpretação não foram considerados registros posteriores a <?=($row["idade"])?> <?=($row["tipoidade"])?></td>
                                             </tr>
                                          </table>
										  <?
												}
												
												?>
										  	  </div>
										  </fieldset>
                                          <?
                                             }
                                             ?>	

	
									
											
										   <?
										   
										  if(trim($row["textopadrao"])!=="" and $ocultar != 0){ ?>
										    <BR>
										   <fieldset class="fset">
                                             <legend><font class="ftitulo">&nbsp;Considerações *&nbsp;</font></legend>
											 
											  <div class="resdesc resdesc2" >			
													<?
													$x =1;
													
													
													if (count($ins_partidaext) > 0){
														echo '<table><tr><td>';
													
													
													while ($x <= count($ins_partidaext)){
														if($x % 2 == 0){
															 $bor = 'border-right:1px dashed #eee;';
														} else {
															 $bor = ''; 
														}
														
														if ($x > 1){
															$bot = 'border-top:1px dashed #eee;';
														}else{
															$bot = '';
														}
														
														
														
														echo '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
														echo '<li>PARTIDA DE '.$ins_nomepartida[$x].'</li>';
														echo '<li>FABRICANTE: '.$ins_fabricante[$x].'</li>';
														echo '<li>PARTIDA: '.$ins_partidaext[$x].'</li>';
														echo '<li>FABRICAÇÃO: '.$ins_fabricacao[$x].'</li>';
														echo '<li>VENCIMENTO: '.$ins_vencimento[$x].'</li>';
														echo '</ul>';
														
														$x++;
														
														}
														
														if($x % 2 != 0){
															 if($x % 2 == 0){
															 $bor = 'border-right:1px dashed #eee;';
														} else {
															 $bor = ''; 
														}
														
														if ($x > 1){
															$bot = 'border-top:1px dashed #eee;';
														}else{
															$bot = '';
														}
														
														
														
														echo '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
														echo '<li></li>';
														
														echo '</ul>';
														}
														echo '</td></tr></table>';
													} 
													?>
											  
													<?=preg_replace('/<(\w+) [^>]+>/', '<$1>', $row["textopadrao"]);
													?> 
										   </div>
										  </fieldset>
										  <?    }
                                           ?>
										  
                                       </td>
									   <?
									    if((!empty($urlimg) or !empty($urlimggmt)) and $row["idade"] != ''){
											?>
									   <td style="width:36%;vertical-align: top;" > 
									   <?
									    if(!empty($urlimg)){
											?>
									   <fieldset class="fset">
									    <legend><font class="ftitulo">&nbsp;gráfico *&nbsp;</font></legend>
										<img src="<?=$urlimg?>" border="0" alt="Gráfico GMT" style="height: 120px;">
										<? $c = 0;
										while ($c < count($cor)){
											echo '<div style="font-size:6px !important; float:left; width:100%"><div style="width:8px; height:8px;float:left;background-color:'.$cor[$c].'">&nbsp;</div>&nbsp;'.$perc[$c].'% - '.$msg[$c].' (entre '.$idprodservtipoopcaoinicio[$c].' e '.$idprodservtipoopcaofim[$c].')</div>';
											$c++;
										}
                                       ?>
                                        
										</fieldset>
										<? } ?>
										<br>
										  <?
									    if(!empty($urlimggmt) and $comparativodelotes == 'Y'){
											?>
										 <fieldset class="fset">
									    <legend><font class="ftitulo">&nbsp;Histórico *&nbsp;</font></legend>
										
										<img src="<?=$urlimggmt?>" border="0" alt="Gráfico GMT" style="height: 120px;">
                                      
                                       
                                        
										</fieldset>
											<? } ?>
									   </td>                                         
										<? }
											?>
                                    </tr>
											<? } ?>
											<tr><td colspan="2" style="text-align:center">
										<span style="font-size:6px; text-align:center">Os resultados desse relatório se restrigem às amostras ensaiadas. Esse relatório só pode ser reproduzido em sua totalidade. <br>Data conclusão ensaio: <?=$row["dataconclusao"];?> </span>
											</td></tr>
                                 </table>
                            
                              </td>
                           </tr>
                        </table>
						 
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
      </table>
   </td>
</tr>
</table>
</div> 
</td>
         </tr>
		 


<?

	if($mostraass==true and $ocultar != 0){
		
		assinaturarodape($row["idresultado"]);
		//Apos assinatura colocar quebra de linha para o rodape nao misturar com a assinatura
	}
?>
	
<?
}



/*
 * Monta o grafico de titulos
 */
function graftitulo($arrrotulo,$arrorificio,$arrcolorbar){
include_once("../inc/php/jpgraph/jpgraph.php");
include_once("../inc/php/jpgraph/jpgraph_bar.php");

//maf: verifica se  no minimo 1 orificio foi preenchido. caso contrario emite msg erro. Isto ocorre por exemplo em casos de amostras contaminadas onde nao foi possivel efetuar o teste
if(empty($arrorificio)){
	echo "<font color='red'><li>Nenhum T&iacute;tulo foi informado</li></font>";
	return "";
}

// Create the graph. These two calls are always required
$graph = new Graph(350,185);	
$graph->SetScale("textlin");
$graph->SetMarginColor('white');
$graph->SetFrame(true,'silver',1);
$graph->xaxis->title->Set("Títulos GMT");
$graph->yaxis->title->Set("N. Amostras");
$graph->yaxis->scale->SetGrace(10);
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,7);
$graph->yaxis->SetLabelMargin(2);
$graph->yaxis->SetTitleMargin(20);
// Adjust the margin a bit to make more room for titles
$graph->img->SetMargin(35,10,5,25);

// Create a bar pot
$bplot = new BarPlot($arrorificio);
$bplot->SetFillColor($arrcolorbar);

$bplot->value->Show();
$bplot->value->SetFont(FF_VERDANA,FS_NORMAL,7);
$bplot->value->SetFormat('%d');
$bplot->value->SetAngle(0);
$bplot->SetWeight(0);//sem borda
$bplot->value->SetColor("black","darkred");// Black color for positive values and darkred for negative values
$bplot->SetColor("black");

$graph->Add($bplot);


$graph->xaxis->SetTickLabels($arrrotulo);

// Setup the titles
$graph->title->Set("Resultado Atual");
// $graph->xaxis->title->Set("X-fghjkl");
// $graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_VERDANA,FS_BOLD,8);
$graph->title->SetColor('darkgray');
$graph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$graph->yaxis->title->SetColor('darkgray');
$graph->yaxis->HideLine(true);
$graph->yaxis->HideTicks(true);

$graph->xaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$graph->xaxis->title->SetColor('darkgray');
$graph->xaxis->HideTicks(true);
$graph->xaxis->HideLine(true);

$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

$graph->Stroke($urlimg);

return $urlimg;

	
}

/*
 * Grafico GMT
 */
function grafgmt($arrrgmt,$arridade){
include_once("../jpgraph/jpgraph.php");
include_once("../jpgraph/jpgraph_bar.php");

//echo "\n<!-- =====================================================\n";
//print_r($arrrgmt);print_r($arridade);
//echo "\n -->\n";

// Create the graph. These two calls are always required
$graph = new Graph(350,185);	
$graph->SetScale("textlin");
$graph->SetMarginColor('white');
$graph->SetFrame(true,'silver',1);
$graph->xaxis->title->Set("Semanas");
$graph->yaxis->title->Set("Título(GMT)");
$graph->yaxis->SetTitleMargin(30);
$graph->yaxis->scale->SetGrace(10);
$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,7);
$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,7);
$graph->yaxis->SetLabelMargin(3); 
// Adjust the margin a bit to make more room for titles
$graph->img->SetMargin(50,10,5,25);

// Create a bar pot
$bplot = new BarPlot($arrrgmt);
$bplot->SetFillColor('#ffff00');

$bplot->value->Show();
$bplot->SetWeight(0);//sem borda
// Must use TTF fonts if we want text at an arbitrary angle
$bplot->value->SetFont(FF_VERDANA,FS_NORMAL,7);
$bplot->value->SetFormat('%d');
$bplot->value->SetAngle(0);
// Black color for positive values and darkred for negative values
$bplot->value->SetColor("black","darkred");

$graph->Add($bplot);


$graph->xaxis->SetTickLabels($arridade);

// Setup the titles
$graph->title->Set("Histórico");
// $graph->xaxis->title->Set("X-fghjkl");
// $graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_VERDANA,FS_BOLD,8);
$graph->title->SetColor('darkgray');
$graph->yaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$graph->yaxis->title->SetColor('darkgray');
$graph->yaxis->HideLine(true);
$graph->yaxis->HideTicks(true);

$graph->xaxis->title->SetFont(FF_VERDANA,FS_NORMAL,8);
$graph->xaxis->title->SetColor('darkgray');
$graph->xaxis->HideLine(true);
$graph->xaxis->HideTicks(true);

$urlimg = "../tmp/graph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

$graph->Stroke($urlimg);

return $urlimg;
}

/*
 * Grafico HISTóRICO
 */
function grafhistorico($arrtit,$arrsem,$arrpadrao){
include_once("../jpgraph/jpgraph.php");
include_once("../jpgraph/jpgraph_line.php");
include_once("../jpgraph/jpgraph_bar.php");

//print_r($arrpadrao);
$data1y=$arrtit[1];
$data2y=$arrtit[2];
$data3y=$arrtit[3];
$data4y=$arrtit[4];
$data5y=$arrtit[4];
$data6y=$arrtit[6];
$data7y=$arrtit[7];
$data8y=$arrtit[8];
$data9y=$arrtit[9];
$data10y=$arrtit[10];
$data11y=$arrtit[11];
$data12y=$arrtit[12];
$data13y=$arrtit[13];
$data14y=$arrtit[14];
$data15y=$arrtit[15];
$data16y=$arrtit[16];
$data17y=$arrtit[17];
$data18y=$arrtit[18];
$data19y=$arrtit[19];
$data20y=$arrtit[20];
$data21y=$arrtit[21];
$data22y=$arrtit[22];
$data23y=$arrtit[23];
$data24y=$arrtit[24];
$data25y=$arrtit[25];
$data26y=$arrtit[26];
$data27y=$arrtit[27];
$data28y=$arrtit[28];
//line1
$ydata=$arrpadrao;

// Create the graph. These two calls are always required
$graph = new Graph(1400,620);
$graph->SetScale("textlin");
$graph->SetMarginColor('white');
$graph->SetFrame(true,'silver',1);
$graph->xaxis->title->Set("Semanas");
$graph->yaxis->title->Set("Título");
$graph->yaxis->SetTitleMargin(30);
$graph->yaxis->scale->SetGrace(10);
$graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,7);
$graph->yaxis->SetLabelMargin(3); 
// Adjust the margin a bit to make more room for titles
$graph->img->SetMargin(50,10,5,25);

$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels($arrsem);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false,false);

if(!empty($arrpadrao)){
// Create the linear plot 
$lineplot=new LinePlot($ydata); 
//$lineplot->mark->SetType(MARK_FILLEDCIRCLE);
//$lineplot->mark->SetWidth(5);
//$lineplot->mark->SetColor('red');
//$lineplot->mark->SetFillColor('red');
$lineplot->SetColor("red");
$lineplot->SetWeight(3);
$lineplot->SetBarCenter();

//$lineplot->value->Show();
//$lineplot->value->SetColor("black");
//$lineplot->value->SetFont(FF_FONT1,FS_BOLD);
$lineplot->SetCSIMTargets($targ,$alt);
}

// Create the bar plots and colors
$b1plot = new BarPlot($data1y);$b1plot->SetFillColor('#ffff00'); //$b1plot->SetColor('#ffff00');
$b2plot = new BarPlot($data2y);$b2plot->SetFillColor('#ffff00'); //$b2plot->SetColor('#ffff00');
$b3plot = new BarPlot($data3y);$b3plot->SetFillColor('#ffff00'); //$b3plot->SetColor('#ffff00');
$b4plot = new BarPlot($data4y);$b4plot->SetFillColor('#ffff00'); //$b4plot->SetColor('#ffff00');
$b5plot = new BarPlot($data5y);$b5plot->SetFillColor('#ffff00'); //$b5plot->SetColor('#ffff00');
$b6plot = new BarPlot($data6y);$b6plot->SetFillColor('#ffff00'); //$b6plot->SetColor('#ffff00');
$b7plot = new BarPlot($data7y);$b7plot->SetFillColor('#ffff00'); //$b7plot->SetColor('#ffff00');
$b8plot = new BarPlot($data8y);$b8plot->SetFillColor('#ffff00'); //$b8plot->SetColor('#ffff00');
$b9plot = new BarPlot($data9y);$b9plot->SetFillColor('#ffff00'); //$b9plot->SetColor('#ffff00');
$b10plot = new BarPlot($data10y);$b10plot->SetFillColor('#ffff00'); //$b10plot->SetColor('#ffff00');
$b11plot = new BarPlot($data11y);$b11plot->SetFillColor('#ffff00'); //$b11plot->SetColor('#ffff00');
$b12plot = new BarPlot($data12y);$b12plot->SetFillColor('#ffff00'); //$b12plot->SetColor('#ffff00');
$b13plot = new BarPlot($data13y);$b13plot->SetFillColor('#ffff00'); //$b13plot->SetColor('#ffff00');
$b14plot = new BarPlot($data14y);$b14plot->SetFillColor('#ffff00'); //$b14plot->SetColor('#ffff00');
$b15plot = new BarPlot($data15y);$b15plot->SetFillColor('#ffff00'); //$b15plot->SetColor('#ffff00');
$b16plot = new BarPlot($data16y);$b16plot->SetFillColor('#ffff00'); //$b16plot->SetColor('#ffff00');
$b17plot = new BarPlot($data17y);$b17plot->SetFillColor('#ffff00'); //$b17plot->SetColor('#ffff00');
$b18plot = new BarPlot($data18y);$b18plot->SetFillColor('#ffff00'); //$b18plot->SetColor('#ffff00');
$b19plot = new BarPlot($data19y);$b19plot->SetFillColor('#ffff00'); //$b19plot->SetColor('#ffff00');
$b20plot = new BarPlot($data20y);$b20plot->SetFillColor('#ffff00'); //$b20plot->SetColor('#ffff00');
$b21plot = new BarPlot($data21y);$b21plot->SetFillColor('#ffff00'); //$b21plot->SetColor('#ffff00');
$b22plot = new BarPlot($data22y);$b22plot->SetFillColor('#ffff00'); //$b22plot->SetColor('#ffff00');
$b23plot = new BarPlot($data23y);$b23plot->SetFillColor('#ffff00'); //$b23plot->SetColor('#ffff00');
$b24plot = new BarPlot($data24y);$b24plot->SetFillColor('#ffff00'); //$b24plot->SetColor('#ffff00');
$b25plot = new BarPlot($data25y);$b25plot->SetFillColor('#ffff00'); //$b25plot->SetColor('#ffff00');
$b26plot = new BarPlot($data26y);$b26plot->SetFillColor('#ffff00'); //$b26plot->SetColor('#ffff00');
$b27plot = new BarPlot($data27y);$b27plot->SetFillColor('#ffff00'); //$b27plot->SetColor('#ffff00');
$b28plot = new BarPlot($data28y);$b28plot->SetFillColor('#ffff00'); //$b28plot->SetColor('#ffff00');


// Create the grouped bar plot
$gbplot = new GroupBarPlot(array($b1plot,$b2plot,$b3plot,$b4plot,$b5plot,$b6plot,$b7plot,$b8plot,$b9plot,$b10plot,$b11plot,$b12plot,$b13plot,$b14plot,$b15plot,$b16plot,$b17plot,$b18plot,$b19plot,$b20plot,$b21plot,$b22plot,$b23plot,$b24plot,$b25plot,$b26plot,$b27plot,$b28plot));
$gbplot->SetWidth(0.9);
// ...and add it to the graPH
$graph->Add($gbplot);
//line
if(!empty($arrpadrao)){
$graph->Add($lineplot); 
}
$graph->title->Set("Histórico");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$urlimg = "../tmp/jpgraph/" .  session_id() . "_" . md5(uniqid(time())) . ".png";

$graph->Stroke($urlimg);

return $urlimg;
}


function relespecialanterior(){
	null;
}

/*
 * gera a tabela e graficos para o elisa
 */
function relelisa($idresultado, $idnucleo, $idpessoa, $idtipoteste,$tipoidade,$idespeciefinalidade,$mostraass,$ocultar, $textointerpretacao, $textopadrao){


	//Invoca variáveis do escopo superior
	global $irestotal;
	global $boopb;
	global $arrelisa;
	
	//Quantidade de linhas do Elisa por pagina
	$qtlinhaselisa = 35;
	$quebratab = 0;
	$paginaquebra = 1;
	$iresultv = count($arrelisa);

	//echo $strsqlv; die("[".$iresultv."]");

	if ($iresultv > 0){
		$arrelisav = array();
		$in=0;
		foreach ($arrelisa as $i => $rowv) {
				
			//se for resultado da tabela de dados, armazenar em um array com um nivel a mais
			$in++;
			if($rowv["local"]=="C"){

				//Se o numero de linhas alcancar o limite, aumenta o grupo e reseta o numero de linhas atual
				if($quebratab==$qtlinhaselisa){

					$paginaquebra++;
					$quebratab=0;
				}
				//Somente incrementa o numero de linhas atual
				if($quebratab<$qtlinhaselisa){
					$quebratab++;
				}

				$arrelisav[$rowv["local"]][$paginaquebra][$in] = $rowv;

			}else{
				$arrelisav[$rowv["local"]][$in] = $rowv;
			}
		}
	}else{
		echo ("\nTeste de Elisa sem dados: [".$idresultado."]\n");
	}


	$irestotal = count($arrelisav["C"]);

	while (list($key, $tabelisa) = each($arrelisav["C"])) {
		//echo "$key => $val\n";
		/*echo"<pre>";
		print_r($tabelisa);die;
		echo"<pre>";*/
		relelisacorpo($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade,$idespeciefinalidade,$mostraass,$tabelisa,$arrelisav["R"],$textopadrao, $ocultar, $textointerpretacao);
		
		$boopb = true;//Indica inicio da quebra de paginas na segunda folha
	}

}

/*
 * gera a tabela e graficos para o elisa
 */

function relelisacorpo($idresultado, $idnucleo, $idpessoa, $idtipoteste, $tipoidade, $idespeciefinalidade, $mostraass, $arrtabelisa, $arrtabresumo, $intextopadrao = false, $ocultar, $textointerpretacao)
{
	global $arrelisa, $arrelisagr1, $arrelisagr2, $templatecsv;
	global $ins_nomepartida, $ins_fabricante, $ins_partidaext, $ins_fabricacao, $ins_vencimento; 
	// verifica se trata-se de uma amostra de DIAS. Caso positivo, nao mostrar segundo grafico. A pedido de Andre 271009.
	$booldia = strpos(strtoupper($tipoidade) , "DIA");
	if ($booldia === false) { //Atenção para utilização do '===': $boolsem retorna como encontrado na posição 0 a string procurada. 0=False, portanto, com === se força a tratar somente booleanos
		$booldia = false;
	}
	else {
		$booldia = true;
	}
	if (empty($idresultado) or empty($idpessoa) or empty($idtipoteste)) {
		echo "--> Parâmetros para gr&aacute;fico Elisa est&atilde;o incompletos (Page Source). <br />A amostra n&atilde;o possui informa&ccedil;&atilde;o de [Cliente] ou [Teste]";
		echo "<!-- ";
		print_r(func_get_args());
		echo " -->";
	}
	else {
		// ######################################################Dados para a tabela
		$iresult = count($arrelisa);
		if ($iresult > 0) {
			$tabelisa = array();
			// print_r($row );
			foreach($arrelisa as $i => $row) {
				$tabelisa[$row["local"]][$row["nome"]] = $row;
			}
			$tabelisa["C"] = $arrtabelisa;
			$tabelisa["R"] = $arrtabresumo;
			$arrgraf1 = array();
			$linha = array();
			if ($idtipoteste == 670 or $idtipoteste == 3305 or $idtipoteste == 3512 or $idtipoteste == 4160 or $idtipoteste == 8710) {
				// print_r($arrelisa);
				foreach($arrelisa as $i => $row) {
					if (is_numeric($row['nome']) and $row['SP'] != '') {
						$number = str_replace(',', '.', $row['SP']);
						$arredondado = floor($number * 100) / 100;
						// echo $number.'*'.round($number,2).'*'.(floor($number * 100) / 100).'<br />';
						if ($arredondado >= 0.00 and $arredondado <= 0.09) {
							$linha[0]++;
						}
						if ($arredondado > 0.09 and $arredondado <= 0.19) {
							$linha[1]++;
						}
						if ($arredondado > 0.19 and $arredondado <= 0.29) {
							$linha[2]++;
						}
						if ($arredondado > 0.29 and $arredondado <= 0.39) {
							$linha[3]++;
						}
						if ($arredondado > 0.39 and $arredondado <= 0.49) {
							$linha[4]++;
						}
						if ($arredondado > 0.49 and $arredondado <= 0.59) {
							$linha[5]++;
						}
						if ($arredondado > 0.59 and $arredondado <= 0.69) {
							$linha[6]++;
						}
						if ($arredondado > 0.69 and $arredondado <= 0.79) {
							$linha[7]++;
						}
						if ($arredondado > 0.79 and $arredondado <= 0.89) {
							$linha[8]++;
						}
						if ($arredondado > 0.89 and $arredondado <= 0.99) {
							$linha[9]++;
						}
						if ($arredondado > 0.99 and $arredondado <= 1.09) {
							$linha[10]++;
						}
						if ($arredondado > 1.09 and $arredondado <= 1.19) {
							$linha[11]++;
						}
						if ($arredondado > 1.19 and $arredondado <= 1.29) {
							$linha[12]++;
						}
						if ($arredondado > 1.29 and $arredondado <= 1.39) {
							$linha[13]++;
						}
						if ($arredondado > 1.39 and $arredondado <= 1.49) {
							$linha[14]++;
						}
						if ($arredondado > 1.49 and $arredondado <= 1.59) {
							$linha[15]++;
						}
						if ($arredondado > 1.59 and $arredondado <= 1000) {
							$linha[16]++;
						}
					}
					//	echo round('0.699',2).'<br />';
					//	echo $number =  round(str_replace(',','.',$row['SP']),2).'<br />';
				}
				for ($c = 0; $c <= 16; $c++) {
					// echo $c.'-'.$linha[$c].'<br />' ;
					$arrgraf1[$c] = $linha[$c];
				}
			}
			else if ($idtipoteste == 1556) {
				foreach($arrelisa as $i => $row) {
					if ($row['result'] == 'Pos!') {
						$linha[0]++;
					}
					elseif ($row['result'] == 'Neg') {
						$linha[1]++;
					}
				}
				for ($c = 0; $c <= 1; $c++) {
					// echo $c.'-'.$linha[$c].'<br />' ;
					$arrgraf1[$c] = $linha[$c];
				}
			}
			else if ($idtipoteste == 3484) {
				foreach($arrelisa as $i => $row) {
					if ($row['result'] == 'Pos!') {
						$linha[0]++;
					}
					elseif ($row['result'] == 'Neg') {
						$linha[1]++;
					}
					elseif ($row['result'] == 'Sus*') {
						$linha[2]++;
					}
				}
				for ($c = 0; $c <= 2; $c++) {
					// echo $c.'-'.$linha[$c].'<br />' ;
					$arrgraf1[$c] = $linha[$c];
				}
			}
			else {
				foreach($arrelisagr1 as $i => $row) {
					if ($row["grupo"] == '0') {
						$arrgraf1[(int)$row["grupo"]] = $row["quant"];
					}
					else {
						$arrgraf1[$row["grupo"]] = $row["quant"];
					}
				}
			}
			// print_r($arrgraf1);
			// #######################################################Dados para o segundo gráfico
			if (!empty($idnucleo) and !empty($idpessoa) and !empty($idtipoteste)) {
				$arrgraf2 = array();
				foreach($arrelisagr2 as $i => $row) {
					$arrgraf2[$row["idade"]] = $row["gmt"] + 1;
				}
			}
			// print_r($tabelisa["C"]); echo "<br />"	;
			// echo count($tabelisa["C"]);die;
			$templateelisa = '	<tr>
									<td style="vertical-align: top; width:64%" valign="top">
										<fieldset class="fset" style="border:none;">
											<div class="resdesc" style="text-align:center;">
												<div style="width:100%; background-color:#f7f7f7; height:14px; vertical-align:middle" class="trelisa ' . $corback . '">
													<div class="relisa">&nbsp;</div>
													<div class="relisa">Well</div>
													<div class="relisa">O.D.</div>
													<div class="relisa">S/P</div>
													<div class="relisa">S/N</div>
													<div class="relisa">Titer</div>
													<div class="relisa">Group</div>
													<div class="relisa">Result</div>
												</div>
';
			while (list($chave, $vlr) = each($tabelisa["C"])) {
				// die('aas');
				// while ($row = mysql_fetch_array($result)){
				if (strtoupper($vlr["result"]) == "POS!") {
					$corback = "trpos";
				}
				else {
					$corback = "trnormal";
				}
				// maf110314: A pedido de Andre, SPs com zero devem ser mostrados
				$vc1 = (!empty($vlr['SP']) or $vlr['SP'] == 0) ? 1 : 0;
				$vc2 = (!empty($vlr['SN'])) ? 1 : 0;
				$vc3 = (!empty($vlr['titer'])) ? 1 : 0;
				$vc4 = (!empty($vlr['grupo'])) ? 1 : 0;
				$vc5 = (!empty($vlr['result'])) ? 1 : 0;
				$vcr = $vc1 + $vc2 + $vc3 + $vc4 + $vc5; //quantidade de colunas preenchidas. Isto evita mostrar lixo de RTF
				if (($vcr >= 2 and $vlr['nome'] != "Well" and $vlr['well'] != "O.D.") or (strtoupper($vlr['nome']) == "NEG" or strtoupper($vlr['nome']) == "POS")) { //Nao mostrar lixo
					$templateelisa.= '
												<div style="width:100%;" class="trelisa ' . $corback . '"> 
													<div class="relisa">' . $vlr['nome'] . '</div>  
													<div class="relisa">' . $vlr['well'] . '</div>
													<div class="relisa">' . $vlr['OD'] . '</div>
													<div class="relisa">';
					if ($vlr['local'] == 'C' and (empty($vlr['SP']) and $vlr['SP'] != 0)) {
						$templateelisa.= '-';
						$_sp = '-';
					}
					else {
						$templateelisa.= $vlr['SP'];
						$_sp = $vlr['SP'];
					}
					$templateelisa.= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and empty($vlr['SN'])) {
						$templateelisa.= '-';
						$_SN = '-';
					}
					else {
						$templateelisa.= $vlr['SN'];
						$_SN = $vlr['SN'];
					}
					$templateelisa.= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and empty($vlr['titer'])) {
						$templateelisa.= '-';
						$_titer = '-';
					}
					else {
						$templateelisa.= $vlr['titer'];
						$_titer = $vlr['titer'];
					}
					$templateelisa.= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and !strlen($vlr['grupo'])) {
						$templateelisa.= '-';
						$_grupo = '-';
					}
					else {
						$templateelisa.= $vlr['grupo'];
						$_grupo = $vlr['grupo'];
					}
					$templateelisa.= '				</div><div class="relisa">';
					if ($vlr['local'] == 'C' and empty($vlr['result'])) {
						$templateelisa.= '-';
						$_result = '-';
					}
					else {
						$templateelisa.= $vlr['result'];
						$_result = $vlr['result'];
					}
					$templateelisa.= '				</div>
												</div>';
					$templatecsv.= '"Nome: ' . $vlr['nome'] . ' " "Well: ' . $vlr['well'] . '" "O.D.: ' . $vlr['OD'] . ' " "S/P: ' . $_sp . ' " "S/N: ' . $_sn . '"  "TITER: ' . $_titer . '"\n "GROUP: ' . $_grupo . '" "RESULT: ' . $_result . '", ';
				}
			}
			$templateelisa.= '
												<br />
												<table style="width:100%; padding:0px;margin:auto;" class="tabelisa" >
													<tr class="hdr">
														<td colspan="3" class="tdrot grrot"style="text-align:center !important" >Resumo</td>
													</tr>
													<tr class="hdr">
														<td></td>
														<td align="center" class="tdrot grrot" style="text-align:center !important;">';
			if ($idtipoteste == 81) {
				$templateelisa.= "S/N";
			}
			else {
				$templateelisa.= "S/P";
			}
			$templateelisa.= "						</tr>";
			while (list($chave, $vlr) = each($tabelisa["R"])) {
				if (strtoupper($vlr["result"]) == "POS!") {
					$corback = "trpos";
				}
				else {
					$corback = "trnormal";
				}
				$templateelisa.= '
													<tr class="' . $corback . '">
														<td align="center" class="tdval grval">' . ($vlr['nome']) . '</td>
														<td align="center" class="tdval grval">' . ($vlr['SP']) . '</td>
														<td align="center" class="tdval grval">' . ($vlr['titer']) . '</td>
													</tr>';
				$templatecsv.=	'"Nome: ' . $vlr['nome'] . ' " "S/P: ' . $vlr['SP'] . '" "TITER: ' . $vlr['titer'];				
			}
			$templateelisa.= '
												</table>
											</div>
										</fieldset>';
			if (!empty($textointerpretacao) and $textointerpretacao != " ") {
				$templateelisa.= '
										<br />
										 <fieldset class="fset">
											<legend><font class="ftitulo">&nbsp;Interpretação *&nbsp;</font></legend>
											<div class="resdesc">
												<div id="fraseedicao" class="divfrase" style="width:100%">' . $textointerpretacao . '
													<input id="idfrasedit" type="hidden" value="' . $textointerpretacao . '">
												</div>';
				if (!empty($row["idade"]) and !empty($row["tipoidade"])) {
					$templateelisa.= '
												<table class="tablegenda"style="width:100%">
													<tr>
														<td>* Para inserção da interpretação não foram considerados registros posteriores a ' . ($row["idade"]) . ' ' . ($row["tipoidade"]) . '</td>
													</tr>
												</table>';
				}
				$templateelisa.= '
											</div>
										</fieldset>';
			}
			elseif ($mostraass == false) {
				$templateelisa.= '
 
										<br /> 											 
										<fieldset class="fset">
											<legend><font class="ftitulo">&nbsp;Interpretação *&nbsp;</font></legend>
											<div class="resdesc">
												<div id="fraseedicao" class="divfrase" style="width:100%"><textarea  rows="5" cols="40" id="idfrasedit" tabindex="1">' . ($textointerpretacao) . '</textarea></div>
												<table class="tablegenda" style="width:100%">
													<tr>
														<td>* Para inserção da interpretação não foram considerados registros posteriores a ' . ($row["idade"]) . ' ' . ($row["tipoidade"]) . '</td>
													</tr>
												</table>
											</div>
										</fieldset>';
			}
			if (trim($intextopadrao) !== "" and $ocultar != 0) {
				$templateelisa.= '  	<br />
										<fieldset class="fset" style="text-align:left">
											<legend><font class="ftitulo">&nbsp;Considerações *&nbsp;</font></legend>';
											
													if (($_SESSION["SESSAO"]["IDPESSOA"] == '6494')){ 
														echo count($ins_partidaext);
													}
													$x =1;
													
													
													if (count($ins_partidaext) > 0){
														$templateelisa .= '<table><tr><td>';
													
													
													while ($x <= count($ins_partidaext)){
														if($x % 2 == 0){
															 $bor = 'border-right:1px dashed #eee;';
														} else {
															 $bor = ''; 
														}
														
														if ($x > 1){
															$bot = 'border-top:1px dashed #eee;';
														}else{
															$bot = '';
														}
														
														
														
														$templateelisa .= '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
														$templateelisa .= '<li>PARTIDA DE '.$ins_nomepartida[$x].'</li>';
														$templateelisa .= '<li>FABRICANTE: '.$ins_fabricante[$x].'</li>';
														$templateelisa .= '<li>PARTIDA: '.$ins_partidaext[$x].'</li>';
														$templateelisa .= '<li>FABRICAÇÃO: '.$ins_fabricacao[$x].'</li>';
														$templateelisa .= '<li>VENCIMENTO: '.$ins_vencimento[$x].'</li>';
														$templateelisa .= '</ul>';
														
														$x++;
														
														}
														
														if($x % 2 != 0){
															 if($x % 2 == 0){
															 $bor = 'border-right:1px dashed #eee;';
														} else {
															 $bor = ''; 
														}
														
														if ($x > 1){
															$bot = 'border-top:1px dashed #eee;';
														}else{
															$bot = '';
														}
														
														
														
														$templateelisa .= '<ul style="padding-left:4px !important; '.$bot.' '.$bor.'  padding:4px;  float:left;list-style: none; width:47%; min-width:30px;vertical-align:top; margin-bottom:0px; padding-left:0px; float:left; margin-top:0px;font-size:6px !important;">';
														$templateelisa .= '<li></li>';
														
														$templateelisa .= '</ul>';
														}
														$templateelisa .= '</td></tr></table>';
													} 
													
													
													
													
											$templateelisa .='
											<div class="resdesc">' . preg_replace('/<(\w+) [^>]+>/', '<$1>', $intextopadrao). '</div>
										</fieldset>';
			}
			$templateelisa.= '	</td>';
			if ($idtipoteste == 670 or $idtipoteste == 3305 or $idtipoteste == 3512 or $idtipoteste == 4160 or $idtipoteste == 8710) {
				$urlimg = geragrafelisaSP($arrgraf1);
			}
			elseif ($idtipoteste == 636 or $idtipoteste == 1455) {
				$urlimg = geragrafelisa4($arrgraf1);
			}
			elseif ($idtipoteste == 1556) {
				$urlimg = geragrafelisaRESULT($arrgraf1);
			}
			elseif ($idtipoteste == 3484) {
				$urlimg = geragrafelisaRESULTSUS($arrgraf1);
			}
			else {
				$urlimg = geragrafelisa($arrgraf1);
			}
			$urlimg2 = geragrafelisagmt($arrgraf2);
			if (!empty($urlimg) or !empty($urlimg2)) {
				$templateelisa.= '
								<td valign="top" style="width:36%;  vertical-align:top">';
				if (!empty($urlimg)) {
					$templateelisa.= '
									<fieldset class="fset">
										<legend><font class="ftitulo">&nbsp;Gráfico *&nbsp;</font></legend> 
										<div class="resdesc" style="text-align:center;">
											<img src="' . $urlimg . '" style="padding-bottom:5px; height: 120px;"  >
										</div>
									</fieldset>	';
				}
				if ($comparativodelotes == 'Y') {
					$templateelisa.= '
									<br />
									<fieldset class="fset">
										<legend><font class="ftitulo">&nbsp;Histórico *&nbsp;</font></legend> 
										<div class="resdesc" style="text-align:center;">';
					if (!empty($urlimg2)) {
						$templateelisa.= '
											<img src="' . $urlimg2 . '" style="padding-bottom:5px;height: 120px;">';
					}
					$templateelisa.= '
										</div>
									</fieldset>	';
				}
				$templateelisa.= '
									</td>';
			}
			$templateelisa.= '
								</tr>';
		}
	}
	if (empty($_REQUEST['csv'])) {
		echo $templateelisa;
	}
}
//controlar impressão por NF
function controleimpressao($innumerorps, $inoficial){
	
	
	
	$vqtd=0;
	
	$sqlqtd="select idcontroleimpressao,via from controleimpressao where numerorps =".$innumerorps." and oficial= '".$inoficial."'";
	$resqtd = mysql_query($sqlqtd) or die("A consulta da quantidade de vias falhou (1): " . mysql_error() . "<p>SQL: $sqlqtd");
	$vqtd = mysql_num_rows($resqtd);
	
	if($vqtd==0){	

		//inicializa o controle de impressao. como existe CHAVE UNICA composta, somente o erro 1062 sera ignorado
		$sqli="insert into controleimpressao (idempresa,numerorps,oficial,status,via,criadopor,criadoem) 
						values (
							".$_SESSION["SESSAO"]["IDEMPRESA"]."
							,".$innumerorps."
							,'".$inoficial."','ATIVO',1,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
	
		mysql_query($sqli) or die("Error ao inserir na controleimpressao [".mysql_error()."] sql = ".$sqli );	
		
		$idcontroleimpressao = mysql_insert_id();
		
	}else{
		
		$row=mysql_fetch_assoc($resqtd);
		$idcontroleimpressao = $row["idcontroleimpressao"];
		$via = $row["via"]+1;
		$sqlu="update controleimpressao set via=".$via.",status='ATIVO' where numerorps =".$innumerorps." and oficial= '".$inoficial."'";
		
		$res = mysql_query($sqlu) or die("Error2 ao alterar via".$sqlu);
		
	}	
	
}
//controlar impressão por resultado executado pela impressão por NF
function controleimpressaoitem($innumerorps, $inoficial, $inidresultado){
	
		
	
	$vqtd=0;
	
	$sqlqtd="select idcontroleimpressao,via from controleimpressao where numerorps =".$innumerorps." and oficial= '".$inoficial."'";
	$resqtd = mysql_query($sqlqtd) or die("A consulta da quantidade de vias falhou (2) : " . mysql_error() . "<p>SQL: $sqlqtd");
	$vqtd = mysql_num_rows($resqtd);
	$row=mysql_fetch_assoc($resqtd);
	
	if($vqtd==0){	

		echo("erro ao buscar versão da impressão");
		die;
		
	}
		
	//insere o resultado para o controle
	$sqlit="insert into controleimpressaoitem (idempresa,idcontroleimpressao,idresultado,status,via,oficial,criadopor,criadoem) 
				values (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$row['idcontroleimpressao'].",".$inidresultado.",'ATIVO',".$row['via'].",'".$inoficial."','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
	mysql_query($sqlit) or die("Error ao inserir na controleimpressaoitem [".mysql_error()."] sql = ".$sqlit );	
	
	
}

//controlar impressão executado pela impressão oficial
function controleimpressaooficial($inidregistro,$inexercicio){



	$vqtd=0;

	$sqlqtd="select idcontroleimpressao,via from controleimpressao where idregistro =".$inidregistro." and exercicio =".$inexercicio." and oficial= 'S'";
	$resqtd = mysql_query($sqlqtd) or die("controleimpressaooficial: A consulta da quantidade de vias oficial falhou : " . mysql_error() . "<p>SQL: $sqlqtd");
	$vqtd = mysql_num_rows($resqtd);

	if($vqtd==0){

		//inicializa o controle de impressao. como existe CHAVE UNICA composta, somente o erro 1062 sera ignorado
		$sqli="insert into controleimpressao (idempresa,idregistro,exercicio,oficial,status,via,criadopor,criadoem)
						values (
							".$_SESSION["SESSAO"]["IDEMPRESA"]."
							,".$inidregistro."
							,".$inexercicio."
							,'S','ATIVO',1,'".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";

		mysql_query($sqli) or die("controleimpressaooficial: Error ao inserir na controleimpressao [".mysql_error()."] sql = ".$sqli );

		$idcontroleimpressao = mysql_insert_id();

	}else{

		$row=mysql_fetch_assoc($resqtd);
		$idcontroleimpressao = $row["idcontroleimpressao"];
		$via = $row["via"]+1;
		$sqlu="update controleimpressao set via=".$via.",status='ATIVO' where idregistro =".$inidregistro." and oficial= 'S'";

		$res = mysql_query($sqlu) or die("controleimpressaooficial: Erro2 ao alterar via".$sqlu);

	}

}
//controlar impressão por resultado executado pela impressão dos oficiais
function controleimpressaoitemoficial($inidregistro, $inidresultado, $inexercicio){

	
	
	$vqtd=0;

	$sqlqtd="select idcontroleimpressao,via from controleimpressao where idregistro =".$inidregistro."  and exercicio =".$inexercicio." and oficial= 'S'";
	$resqtd = mysql_query($sqlqtd) or die("controleimpressaoitemoficial: A consulta da quantidade de vias falhou : " . mysql_error() . "<p>SQL: $sqlqtd");
	$vqtd = mysql_num_rows($resqtd);
	$row=mysql_fetch_assoc($resqtd);

	if($vqtd==0){

		echo("erro ao buscar versão da impressão");
		die;

	}

	//insere o resultado para o controle
	$sqlit="insert into controleimpressaoitem (idempresa,idcontroleimpressao,idresultado,status,via,oficial,criadopor,criadoem)
				values (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$row['idcontroleimpressao'].",".$inidresultado.",'ATIVO',".$row['via'].",'S','".$_SESSION["SESSAO"]["USUARIO"]."',sysdate())";
	mysql_query($sqlit) or die("controleimpressaoitemoficial: Error ao inserir na controleimpressaoitem [".mysql_error()."] sql = ".$sqlit );


}


?>
