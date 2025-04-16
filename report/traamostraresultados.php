<style>

</style>
<?
ini_set('memory_limit', '-1');
   require_once("../inc/php/validaacesso.php");
   require_once("reltesteinct2bkp_11102018.php");
   if(empty($_GET["idamostratra"])){
       die("Amostra n�o enviada");
   }
   
   
   function buscaamostras(){
	global $_1_u_amostra_idamostra,$_1_u_amostra_idpessoa;
	
	$sql="select distinct tt.idtipoteste, tipoteste
		from amostra a
		join resultado r on a.idamostra = r.idamostra
		join resultadojson rj ON rj.idresultado=r.idresultado
		JOIN vwtipoteste tt ON tt.idtipoteste = r.idtipoteste
		join prodserv p on p.idprodserv = tt.idtipoteste
		LEFT JOIN `subtipoamostra` `sb` ON `sb`.`idsubtipoamostra` = `a`.`idsubtipoamostra`
		where a.idamostratra = ".$_GET["idamostratra"]." order by idprodserv";
	$res= d::b()->query($sql) or die("Erro ao buscar amostras : " . mysql_error() . "<p>SQL:".$sql);
	$qtd= mysqli_num_rows($res);

	return ($res);
	
   }
   
   $sql ="Select DATE_FORMAT(dataamostra, '%d/%m/%Y')as dataamostra, idregistro from amostra a where a.idamostra = '".$_GET["idamostratra"]."'";

$res = mysql_query($sql) or die("Falha no Relat�rio de Testes: " . mysql_error() . "<p>SQL: $sql");


while($linha = mysql_fetch_array($res)){
	$dataamostra = $linha['dataamostra'];
	$idregistro = 	$linha['idregistro'];
}
	

	//Abre um $row com os dados da coluna jresultado
	
   
   $oAm=getAmostra($_GET["idamostratra"]);
   //print_r($oAm);
   //$idtipoamostra=$oAm["idtipoamostra"];
   $idsubtipoamostra=$oAm["idsubtipoamostra"];
   
   $oRes=getResultados($_GET["idamostratra"]);
   
   $oAAm=getAgenteAmostra($_GET["idamostratra"]);
   
   function getAgenteAmostra($inidamostra){
       $sqla="select r.idresultado,l.partida,l.exercicio,l.idlote,l.status,p.descr
                       from lote l,resultado r,prodserv p,amostra a
                       where l.tipoobjetosolipor='resultado' 
                       and l.idobjetosolipor=r.idresultado
                       and p.idprodserv = l.idprodserv
                       and r.idamostra=a.idamostra
                       and a.idamostratra = ".$inidamostra." order by r.ord";
       $resa=d::b()->query($sqla) or die("Erro ao buscar agentes da amostra : " . mysql_error() . "<p>SQL:".$sqla);
   
       $arrColunas = mysqli_fetch_fields($resa);
       $i=0;
       $arrret=array();
       while($r = mysqli_fetch_assoc($resa)){
           $i=$i+1;
           //para cada coluna resultante do select cria-se um item no array
           foreach ($arrColunas as $col) {
               //$arrret[$i][$col->name]=$robj[$col->name];
               $arrret[$r["idresultado"]][$r["idlote"]][$col->name]=$r[$col->name];//alterado para agrupar pelo resultado, para facilitar os loops
           }
       }
       return $arrret;
   }
   ?>
<html>
   <head>
      <link href="../inc/css/emissaoresultadopdf.css" rel="stylesheet" type="text/css" />
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
         font-size:7px !important;
         }
         .relisa{
         width:12% !important;
         display:inline-block;
         }
         .MsoTableGrid{
         width:100%;
         }
         table{
         width:100%;
         bor:color
         }
         .MsoTableGrid{
         border-color:#fff !important;
         }
         .trelisa{
         height:12px;
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
         font-family:'Roboto'!important;
         font-weight:normal !important;
         color: #333 !important;
         }
         .tdrot{
         width:80px !important;
         font-size:7px !important;
         color:#333 !important;
         height:14px;
         text-align:left !important;
         background-color:#fff;
         font-family:'Roboto'!important;
         }
         .tdval{
         font-size:7px !important;
         font-family:'Roboto'!important;
         text-transform:uppercase;
         color:#333 !important;
         background-color:#fff;
         }
 
		 .mostraresultado .tdval .grval, .mostraresultado .tbgr .grval, .mostraresultado .tbgr{
			border: none !important; 
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
      <link href="../inc/css/mtorep.css" media="all" rel="stylesheet" type="text/css">
      <title>Resumo Diagn�stico</title>
   </head>
   <body>
      <table style="width:700px; margin:auto; ">
         <thead>
            <tr>
               <td>
                  <div style="; width:700px;">
                     <table class="cabtxt" style="width:100%;position:relative; z-index:2; background-color:#fff; margin-bottom:0px;">
                        
                           <tr>
                              <td style="background:url('../inc/img/cabecalho-relatorio-de-ensaio-LAUDO-INATA.jpg'); width:573px; height:90px;
                                 background-position: left; background-size: cover; border: 1px solid #fff; border-radius: 7px 0px 0px 0px; border-right:none;background-repeat:no-repeat;		">
                                 &nbsp;
                              </td>
                              <td style="border: 1px solid #fff; border-radius: 0px 7px 0px 0px;  border-left:none; text-align:right"></td>
                              <!-- <td style="width:171px;"  align="right">Rod. BR 365, KM 615 - B. Conj Alvorada<br>CEP 38407-180 - Uberl&acirc;ndia - MG<br>laudolab@laudolab.com.br<br>www.laudolab.com.br<br>Fone/Fax: (34) 3222-5700</td>-->
                           </tr>
                        
                     </table>
                  </div>
               </td>
            </tr>
         </thead>
         <!-- Controle Impressao -->
         <tbody>
            <tr>
               <td style="width: 100%">
                  <table style="width: 100%">
                     
                        <tr>
                           <td>
                              <table class="tsep" style="width:100%; margin-top:6px;">
                                 
                                    <tr>
                                       <td style="text-align:center; font-size:13px;">RELAT�RIO TRA RESULTADO</td>
                                    </tr>
									
									<tr>
                                       <td>
                                          <table class="tsep" style="width:100%;">
                                            
											 
											 <tr>
                                                   <td>
                                                      <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                         
                                                            <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
                                                               <td colspan="6" style="font-size:11px;" >DADOS DO TEA/TRA
                                                               </td>
                                                            </tr>
                                                            <tr>
                                                               <td style="width:12% !important;" class="tdrot grrot">TEA/TRA:</td>
                                                               <td  style="width:38% !important;" class="tdval grval" ><?=$idregistro;?></td>
                                                            
                                                               <td  style="width:12% !important;" class="tdrot grrot">Data Registro:</td>
                                                               <td  style="width:38% !important;" class="tdval grval" ><?=$dataamostra;?></td>
                                                            </tr>
                                                            
															
                                                    
                                                      </table>
                                                   </td>
                                                </tr>
												
												
                                             
                                          </table>
                                       </td>
                                    </tr>
									
									
                                    <tr>
                                       <td>
                                          <table class="tsep" style="width:100%;">
                                            
											
												
												
                                                <tr>
                                                   <td>
                                                      <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                     
                                                            <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
                                                               <td colspan="6" style="font-size:11px;" >DADOS DO CLIENTE
                                                               </td>
                                                            </tr>
                                                            <tr>
                                                               <td class="tdrot grrot" width="12%">Cliente:</td>
                                                               <td class="tdval grval" colspan="5" width="88%"><?=$oAm["razaosocial"]?></td>
                                                            </tr>
                                                            <tr>
                                                               <td class="tdrot grrot">Propriedade/Granja:</td>
                                                               <td class="tdval grval" colspan="5"><?=$oAm["nome"]?></td>
                                                            </tr>
                                                            <tr>
                                                               <td class="tdrot grrot">Endere�o:</td>
                                                               <td class="tdval grval" colspan="5"> <?
                  if(empty($oAm["enderecosacado"])){
                  ?>
               <div class="alert alert-warning">
                  <span class="notProducao"><i class="fa fa-exclamation-triangle"></i>&nbsp;Favor preencher o endere�o da propriedade no cadastro do cliente!</span>
               </div>
               <?
                  }else{
                      echo($oAm["enderecosacado"]);
                  }
                  ?>                                  -MG                                       
                                                               </td>
                                                            </tr>
															 <tr>
                                                               <td class="tdrot grrot" >Cnpj:</td>
                                                               <td class="tdval grval" colspan="3"><?=formatarCPF_CNPJ($oAm["cpfcnpj"])?></td>
                                                           
                                                               <td class="tdrot grrot">Inscr. Estadual:</td>
                                                               <td class="tdval grval"  ><?=$oAm["inscrest"]?></td>
                                                            </tr>
															 <tr>
                                                               <td class="tdrot grrot">Esp�cie/Finalidade:</td>
                                                               <td class="tdval grval" colspan="5"><?=$oAm["especietipofinalidade"]?></td>
                                                            </tr>
															 <tr>
                                                               <td class="tdrot grrot">Material colhido:</td>
                                                               <td class="tdval grval"><?=$oAm["subtipoamostra"]?></td>
															   <td class="tdrot grrot">Quantidade:</td>
                                                               <td class="tdval grval"><?=$oAm["nroamostra"]?></td>
															   <td class="tdrot grrot">Data Coleta:</td>
                                                               <td class="tdval grval"><?=dma($oAm["datacoleta"])?></td>
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
$_listaAmostras = buscaamostras();
$teste = '';
while($row=mysqli_fetch_assoc($_listaAmostras)){
	
	if ($teste != $row['tipoteste']){
	
	
?>  
                        <tr>
                           <td>
                              <table class="tsep" style="width:100%">
                                
                                    <tr>
                                       <td>
                                          <table class="tsep" style="width:100%; margin-top:0px">
                                             <!-- Cabecalho Superior -->
                                             
                                                <tr>
                                                   <td class="td" style="width:100%">
                                                      <table class="tbgr" style="width:100%; border:1px solid #f7f7f7;">
                                                         
                                                            <tr style="background-color:#f7f7f7; font-size:13x; text-transform:uppercase;	height:20px;">
                                                               <td colspan="2" style="font-size:11px;"><?=$row['tipoteste'];?>
                                                               </td>
                                                            </tr>
															
															
															
<?	}					
?>
<? 

 $sql ="select 
		r.idresultado
		,rj.jresultado
	from 
		
				resultado r 
				JOIN resultadojson rj ON rj.idresultado=r.idresultado 
				JOIN amostra a ON r.idamostra = a.idamostra and a.idamostratra = ".$_GET["idamostratra"]."
				JOIN pessoa p ON p.idpessoa = a.idpessoa
				JOIN  subtipoamostra st  ON st.idsubtipoamostra = a.idsubtipoamostra
				JOIN vwtipoteste tt ON tt.idtipoteste = r.idtipoteste and tt.idtipoteste = ".$row['idtipoteste']."
				left join nucleo n on n.idnucleo = a.idnucleo
				left join especiefinalidade ef on ef.idespeciefinalidade=a.idespeciefinalidade
		order by a.idpessoa, a.exercicio, a.idregistro desc , tt.tipoteste";

if($echosql){echo "<!-- " . $sql . "  -->";}


$res = mysql_query($sql) or die("Falha no Relat�rio de Testes: " . mysql_error() . "<p>SQL: $sql");

$i = 0;
while($resultado = mysql_fetch_array($res)){
$i++;
	//Recupera os dados congelados no resultado, para serem apresentados na tela juntamente � vers�o gerada ap�s STATUS=ASSINADO
	$rc= unserialize(base64_decode($resultado["jresultado"]));

	if(empty($rc)){
		echo "Teste n�o possui informa��o de resultado: [".$resultado["idresultado"]."]";
	}
	

	//Abre um $row com os dados da coluna jresultado
	$row["idempresa"]		=$rc["amostra"]["res"]["idempresa"];
	$row["idunidade"]		=$rc["amostra"]["res"]["idunidade"];
	$row["idregistro"]		=$rc["amostra"]["res"]["idregistro"];
	$row["idamostra"]		=$rc["amostra"]["res"]["idamostra"];
	$row["exercicio"]		=$rc["amostra"]["res"]["exercicio"];
	$row["idpessoa"]		=$rc["amostra"]["res"]["idpessoa"];
	$row["idtipoamostra"]	=$rc["amostra"]["res"]["idtipoamostra"];
	$row["idsubtipoamostra"]=$rc["amostra"]["res"]["idsubtipoamostra"];
	$row["datacoleta"]		=dma($rc["amostra"]["res"]["datacoleta"]);
	$row["nroamostra"]		=$rc["amostra"]["res"]["nroamostra"];
	$row["origem"]			=$rc["amostra"]["res"]["origem"];
	$row["lote"]			=$rc["amostra"]["res"]["lote"];
	$row["idade"]			=$rc["amostra"]["res"]["idade"];
	$row["tipoidade"]		=$rc["amostra"]["res"]["tipoidade"];
	$row["observacao"]		=$rc["amostra"]["res"]["observacao"];
	$row["descricao"]		=$rc["amostra"]["res"]["descricao"];
	$row["lacre"]			=$rc["amostra"]["res"]["lacre"];
	$row["galpao"]			=$rc["amostra"]["res"]["galpao"];
	$row["linha"]			=$rc["amostra"]["res"]["linha"];
	$row["responsavelof"]	=$rc["amostra"]["res"]["responsavelof"];
	$row["responsavel"]		=$rc["amostra"]["res"]["responsavel"];
	$row["tipo"]			=$rc["amostra"]["res"]["tipo"];
	$row["nroplacas"]		=$rc["amostra"]["res"]["nroplacas"];
	$row["diluicoes"]		=$rc["amostra"]["res"]["diluicoes"];
	$row["tipoaves"]		=$rc["amostra"]["res"]["tipoaves"];
	$row["identificacaochip"]=$rc["amostra"]["res"]["identificacaochip"];
	$row["sexo"]			=$rc["amostra"]["res"]["sexo"];
	$row["datafabricacao"]	=$rc["amostra"]["res"]["datafabricacao"];
	$row["partida"]			=$rc["amostra"]["res"]["partida"];
	$row["nrodoses"]		=$rc["amostra"]["res"]["nrodoses"];
	$row["especificacao"]	=$rc["amostra"]["res"]["especificacao"];
	$row["fornecedor"]		=$rc["amostra"]["res"]["fornecedor"];
	$row["localcoleta"]		=$rc["amostra"]["res"]["localcoleta"];
	$row["localexp"]		=$rc["amostra"]["res"]["localexp"];
	$row["tc"]				=$rc["amostra"]["res"]["tc"];
	$row["semana"]			=$rc["amostra"]["res"]["semana"];
	$row["notafiscal"]		=$rc["amostra"]["res"]["notafiscal"];
	$row["vencimento"]		=$rc["amostra"]["res"]["vencimento"];
	$row["fabricante"]		=$rc["amostra"]["res"]["fabricante"];
	$row["sexadores"]		=$rc["amostra"]["res"]["sexadores"];
	$row["cpfcnpjprod"]		=$rc["amostra"]["res"]["cpfcnpjprod"];
	$row["uf"]				=$rc["amostra"]["res"]["uf"];
	$row["cidade"]			=$rc["amostra"]["res"]["cidade"];
	$row["pedido"]			=$rc["amostra"]["res"]["pedido"];
	$row["criadopor"]		=$rc["amostra"]["res"]["criadopor"];
	$row["criadoem"]		=$rc["amostra"]["res"]["criadoem"];
	$row["alteradopor"]		=$rc["amostra"]["res"]["alteradopor"];
	$row["alteradoem"]		=$rc["amostra"]["res"]["alteradoem"];
	$row["estexterno"]		=$rc["amostra"]["res"]["estexterno"];
	$row["clienteterceiro"]	=$rc["amostra"]["res"]["clienteterceiro"];
	$row["nucleoorigem"]	=$rc["amostra"]["res"]["nucleoorigem"];
	$row["idwfxprocativ"]	=$rc["amostra"]["res"]["idwfxprocativ"];
	$row["dataamostra"]		=$rc["amostra"]["res"]["dataamostra"];
	$row["dataamostraformatada"]=dma($rc["amostra"]["res"]["dataamostra"]);
	$row["granja"]			=$rc["amostra"]["res"]["granja"];
	$row["nsvo"]			=$rc["amostra"]["res"]["nsvo"];
	$row["nucleoamostra"]	=$rc["amostra"]["res"]["nucleoamostra"];
	$row["idespeciefinalidade"]=$rc["especiefinalidade"]["res"]["idespeciefinalidade"];
	//$row["especiefinalidade"]=$rc["especiefinalidade"]["res"]["especie"]."-".$rc["especiefinalidade"]["res"]["tipoespecie"]."-".$rc["especiefinalidade"]["res"]["finalidade"];
	$row["especiefinalidade"]=$rc["especiefinalidade"]["res"]["especiefinalidade"];
	$row["finalidade"]		=$rc["especiefinalidade"]["res"]["finalidade"];
	$row["idnucleo"]		=$rc["nucleo"]["res"]["idnucleo"];
	$row["nucleo"]			=$rc["nucleo"]["res"]["nucleo"];
	$row["regoficial"]		=$rc["nucleo"]["res"]["regoficial"];
	$row["quantidadeteste"]      =$rc["resultado"]["res"]["quantidade"];
	$row["nome"]			=$rc["pessoa"]["res"]["nome"];
	$row["razaosocial"]		=$rc["pessoa"]["res"]["razaosocial"];
	$row["idservicoensaio"]	=$rc["resultado"]["res"]["idservicoensaio"];
	$row["gmt"]				=$rc["resultado"]["res"]["gmt"];
	$row["padrao"]			=$rc["resultado"]["res"]["padrao"];
	$row["descritivo"]		=$rc["resultado"]["res"]["descritivo"];
	$row["gmt"]				=$rc["resultado"]["res"]["gmt"];
	$row["idresultado"]		=$rc["resultado"]["res"]["idresultado"];
	$row["idt"]				=$rc["resultado"]["res"]["idt"];
	$row["idtipoteste"]		=$rc["resultado"]["res"]["idtipoteste"];
	$row["padrao"]			=$rc["resultado"]["res"]["padrao"];
	$row["q1"]				=$rc["resultado"]["res"]["q1"];
	$row["q10"]				=$rc["resultado"]["res"]["q10"];
	$row["q11"]				=$rc["resultado"]["res"]["q11"];
	$row["q12"]				=$rc["resultado"]["res"]["q12"];
	$row["q13"]				=$rc["resultado"]["res"]["q13"];
	$row["q2"]				=$rc["resultado"]["res"]["q2"];
	$row["q3"]				=$rc["resultado"]["res"]["q3"];
	$row["q4"]				=$rc["resultado"]["res"]["q4"];
	$row["q5"]				=$rc["resultado"]["res"]["q5"];
	$row["q6"]				=$rc["resultado"]["res"]["q6"];
	$row["q7"]				=$rc["resultado"]["res"]["q7"];
	$row["q8"]				=$rc["resultado"]["res"]["q8"];
	$row["q9"]				=$rc["resultado"]["res"]["q9"];
	$row["status"]			=$rc["resultado"]["res"]["status"];
	$row["var"]				=$rc["resultado"]["res"]["var"];
	$row["idsecretaria"]	=$rc["resultado"]["res"]["idsecretaria"];
	$row["interfrase"]		=$rc["resultado"]["res"]["interfrase"];
        $row["versao"]                  =$rc["resultado"]["res"]["versao"];
	$row["subtipoamostra"]	=$rc["subtipoamostra"]["res"]["subtipoamostra"];
	$row["normativa"]		=$rc["subtipoamostra"]["res"]["normativa"];
	$row["tipoamostra"]		=$rc["tipoamostra"]["res"]["tipoamostra"];
	$row["tipoamostraformatado"]=($row["tipoamostra"]==$row["subtipoamostra"])?$row["tipoamostra"]:$row["tipoamostra"]." Subtipo:".$row["subtipoamostra"];
	$row["tipoteste"]		=$rc["prodserv"]["res"]["tipoteste"];
	$row["sigla"]			=$rc["prodserv"]["res"]["sigla"];
	$row["tipogmt"]			=$rc["prodserv"]["res"]["tipogmt"];
	$row["tipoespecial"]	=$rc["prodserv"]["res"]["tipoespecial"];
	$row["geralegenda"]		=$rc["prodserv"]["res"]["geralegenda"];
	$row["geragraf"]		=$rc["prodserv"]["res"]["geragraf"];
	$row["geracalc"]		=$rc["prodserv"]["res"]["geracalc"];
	$row["textopadrao"]		=$rc["prodserv"]["res"]["textopadrao"];
	$row["tipobact"]		=$rc["prodserv"]["res"]["tipobact"];
	$row["logoinmetro"]		=$rc["prodserv"]["res"]["logoinmetro"];

	$rowbio		=$rc["bioensaio"]["res"];
	$rowend		=$rc["endereco"]["res"];
	$rtitulos	=$rc["titulos"]["res"];
	$arrgrafgmt	=$rc["hist_gmt"]["res"];
	$arrelisa	=$rc["resultadoelisa"]["res"];
	$arrelisagr1=$rc["resultadoelisa_graf1"]["res"];
	$arrelisagr2=$rc["resultadoelisa_graf2"]["res"];
	$arrassinat	=$rc["resultadoassinatura"]["res"];
	$mostraass=true;

	?>
															<tr>
															   <td  class="tdval grval" colspan="2" >
															   <br>
															   <table class="tbgr" style="width:100%; border:2px solid #f7f7f7;">
															   <tr>
															    <td width="12%" style="width:12% !important;" class="tdrot grrot" >Amostra:</td>
															   <td width="38%" class="tdval grval"><?=($row["nroamostra"])?> <font class="ft8cinza"></font> <?=($row["subtipoamostra"])?></td>
															   <td width="12%"  class="tdrot grrot" >N&ordm; Registro:</td>
															   <td width="38%" class="tdval grval"><?=($row["idamostra"])?></td>
																
															  
																</tr>
																<tr>
															   <td  class="tdrot grrot" style="vertical-align: top; padding-top:10px" >Resultado:</td>
															   <td colspan="3" class="tdval grval"><br><table class='mostraresultado'>
															 
															 
															<?
	//Se for impressao oficial for funcionario e n�o vizualizar resultado controla a impress�o
	if($impoficial=="Y" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N'){
		controleimpressaooficial($row['idregistro'],$row['exercicio']);
	}

	//Se for uma amostar com idunidade = 4 trata-se de uma amostra do bioterio
	/*
	if($row['idunidade']==4 and !empty($row['idservicoensaio'])){
		//Buscar a idade para o bioterio
		$sqlb="SELECT (DATEDIFF(s.data,ss.data)) AS idade,if(s.dia is null,'',concat('D',s.dia)) as rotulo,dma(s.data) as dmadata
			from servicoensaio ss,servicoensaio s
			where ss.servico = 'TRANSFERENCIA'
			and	ss.idbioensaio = s.idbioensaio
			and s.idservicoensaio =".$row['idservicoensaio'];
		$resb=mysql_query($sqlb) or die("Erro ao buscar a idade para o biot�rio sql".$sqlb);
		$rowb=mysql_fetch_assoc($resb);
		if(empty($rowb['rotulo'])){
			$row['idade']=$rowb['idade'];
			$row['dataamostraformatada']=$rowb['dmadata'];
		}else{
			$row['idade']=$rowb['rotulo'];
			$row["tipoidade"]="";
			$row['dataamostraformatada']=$rowb['dmadata'];
		}
		//$row["nroamostra"]=$row['quantidadeteste'];
		//echo($sqlb);
	}
	*/
	// inserir informa��es do lote no resultado
	if(!empty($row['idwfxprocativ'])){		
		$row['partida']=		$rc["vwpartidaamostra"]["res"]["partidaext"];
		$row['nucleoamostra']=	$rc["vwpartidaamostra"]["res"]["partida"];
		$row['lote']=			$rc["vwpartidaamostra"]["res"]["partidaext"];
	}

	$ipage = 0; //controla o numero dap agina atual
	$irestotal = 1;

	/*
	 * As condicoes abaixo invocam as funcoes para montagem das emissoes conforme o tipo especial
	 */
	if($row["tipoespecial"]=="DESCRITIVO"){ 
		//imppaginisup();//inicia o controle da impressao: paginacao e controle encriptado
		//cabecalhores();//monta o cabe�alho
		reldescritivo($mostraass, 0);
		//imppagrodape(1);//finaliza a pagina
	}

	if($row["tipoespecial"]== "BRONQUITE" or $row["tipoespecial"]=="NEWCASTLE" or $row["tipoespecial"]=="GUMBORO" or $row["tipoespecial"]=="REOVIRUS" or $row["tipoespecial"]=="PNEUMOVIRUS"			
or $row["tipoespecial"]=='GUMBORO IND' or $row["tipoespecial"]=='BRONQUITE IND' or $row["tipoespecial"]=='NEWCASTLE IND' or $row["tipoespecial"]=='PNEUMOVIRUS IND' or $row["tipoespecial"]=='REOVIRUS IND'
or $row["tipoespecial"]=="PESAGEM" or $row["tipoespecial"]=="ALFA" or $row["tipoespecial"]=="DESCRITIVO IND"){//Se for GMT
		//imppaginisup();//inicia o controle da impressao: paginacao e controle encriptado
		//cabecalhores();//monta o cabe�alho
		relespecial($mostraass, 0);
		//imppagrodape();//finaliza a pagina
	}//se GMT

	//Se o tipo especial for ELISA ou ELISASGMT (Elisa S/ GMT) adicionado a pedido do Daniel
	if($row["tipoespecial"]=="ELISA" or $row["tipoespecial"]=="ELISASGMT"){//Se for elisa, quebrar a tabela em partes iguais para nao gerar paginas 'soltas' na impressao


		relelisa($row["idresultado"], $row["idnucleo"], $row["idpessoa"], $row["idtipoteste"], $row["tipoidade"],$row["idespeciefinalidade"],$mostraass, 0);


	}
	
	//se for funcionario e n�o visualizar resultado e a solicita��o n�o vier da impress�o oficial controla a impress�o
	if($rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N' and empty($impoficial)){
		controleimpressaoitem($controle,$chkoficial,$row["idresultado"]);
	}
	//se vier da impress�o dos oficiais for funcionario e n�o visualizar resultado controla a impress�o
	if($impoficial=="Y" and $rowp["idtipopessoa"]==1 and $rowp["visualizares"]=='N'){
		controleimpressaoitemoficial($row['idregistro'],$row["idresultado"],$row["exercicio"]);
	}
	
	
	/* * INDICACAO DE VISUALIZACAO DO RESULTADO */ 				
	$sqlvis = "insert into resultadovisualizacao (idresultado, idpessoa,criadoem,email) 
	values (".$row["idresultado"].",".$_SESSION["SESSAO"]["IDPESSOA"].",now(),'".$_SESSION["SESSAO"]["EMAIL"]."')"; 
	$resvis = mysql_query($sqlvis); 
	if(!$resvis){
		if($echosql){
			echo "\n<!-- Resultado Visualiza��o: ".mysqli_error(d::b())."\nSql:".$sqlvis." -->";
		} 
	} 
?>
</table></td></tr></table></td></tr>
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
					
<?						
} ?>						
                      
                     
                     </tbody>
					 <tfoot>
	<tr>
	<td><br>
	<table style="width: 100%; top:-10px; position:relative;">		
<tr><td>
		<table class="tsep" style="width:100%;">
		<tr><td>
		<table class="tsep" style="width:100%;">
		<tr><td style="width:100%">
		<table style="width:100%" >
		<tr>
		<td > 
	
	
	<div style="width:100%; text-align:center; background:#f7f7f7; padding-top:7px; padding-bottom:7px; line-height:12px;font-size:6px">
	<? echo ('<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;">Laudo Laborat�rio Av�cola Uberl�ndia Ltda. CNPJ: 23.259.427/0001-04 - I.E.: 7023871770001.</span>
	<span style="display:block; height: 10px; line-height: 10px;font-size:6px !important;">Rodovia BR 365, S/N�. Alvorada. CEP: 38407-180 - Uberl�ndia-MG. (34) 3222-5700 - resultados@laudolab.com.br</span>'); ?>
	
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
               </td>
            </tr>
           
           
        
         <!-- INI: Rodape -->
        
         <!--<br><div class="nimptbot2">Este relat�rio atende aos requisitos de acredita��o da Cgcre, que avaliou a compet�ncia do laborat�rio</div><br><div class="nimptbot2">As opini�es e interpreta��es expressas acima n�o fazem parte do escopo da acredita��o deste laborat�rio</div>
            --><!-- FIM: Rodape -->
      </table>
  
   </body>
</html>

<?
die();
	$html = ob_get_contents();
	//limpar o codigo html
	$html = preg_replace('/>\s+</', "><", $html);

	ob_end_clean();
	define("DOMPDF_DEFAULT_MEDIA_TYPE", "print");
	define("DOMPDF_ENABLE_HTML5PARSER", true);
	define("DOMPDF_ENABLE_FONTSUBSETTING", true);
	define("DOMPDF_UNICODE_ENABLED", true);
	
	define("DOMPDF_DPI", 86);
	define("DOMPDF_ENABLE_REMOTE", true);
	define("DOMPDF_DEFAULT_PAPER_SIZE", "A4");

	


	// Inclu�mos a biblioteca DOMPDF
require_once("../inc/dompdf/dompdf_config.inc.php");
	
//	 require_once "../inc/dompdf3/vendor/autoload.php";
//	use Dompdf\Dompdf;

	// Instanciamos a classe
	$dompdf = new DOMPDF();
	 $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'ISO-8859-1');
	// Passamos o conte�do que ser� convertido para PDF
	 $dompdf->load_html($html,'ISO-8859-1'); 

	// Definimos o tamanho do papel e
	// sua orienta��o (retrato ou paisagem)
	$dompdf->set_paper('A4','portrait');
	 
	// O arquivo � convertido
	 $dompdf->render();
	
	if($gravaarquivo=='Y'){
	// Salvo no diret�rio  do sistema
	    $output = $dompdf->output();
	    file_put_contents("/var/www/carbon8/tmp/resultadopdf/resultado".$nomearq.".pdf",$output);
	    echo($newidcomunicacao);
	}else{   
	// Exibido para o usu�rio
		$dompdf->stream("resultado_".$nomearq.".pdf");
	}	
?>