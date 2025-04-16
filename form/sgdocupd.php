<?
require_once("../inc/php/functions.php");
include_once("../inc/php/validaacesso.php");


//Parà¢metros mandatà³rios para o carbon
$pagvaltabela = "sgdocupd";
$pagvalcampos = array(
	"idsgdocupd" => "pk"
);



/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select u.idsgdocupd, s.idempresa, u.idsgdoc, u.idregistro, u.idunidade, u.titulo, u.idsgtipodoc, u.idsgdoctipo, u.idsgdoctipodocumento, u.idpessoa, u.versao, u.revisao, u.status, u.tipoacesso, u.responsavel, u.conteudo, u.inicio, u.fim, u.nota, u.observacao, u.resultado, u.conteudo2, u.regalteracao, u.acompversao, u.criadopor, u.criadoem, u.alteradopor, u.alteradoem, u.iddocumentoorigem from sgdocupd u JOIN sgdoc s on s.idsgdoc = u.idsgdoc where u.idsgdocupd = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");



?>
<html>

<head>
<title>Versão de Documento</title>


<style>
table{
border:0px;
}
</style>


<link href="../inc/css/mtorep.css?1" media="all" rel="stylesheet" type="text/css" />
<script language="JavaScript" src="../inc/js/functions.js"></script>

<style>
.roxopalidobold{
	font-family: Arial, Serif, SansSerif;
	font-size: 9pt;
	font-weight: bold;
	weight: bold;
	font-color: #8C91AD;
	color: #8C91AD;
}
.node {
	text-decoration: none; 
	font-weight: bold;
	weight: bold;
	margin-left: 30px;
}
.leaf {
	text-decoration: none; 
	color: blue;
	font-weight: bold;
	weight: bold;
	margin-left: 30px;
}
.btold{
	border: 1px solid gray;
	background-color: silver;
	font-size: 16px;
	height: 18px;
	width: 18px;
	font-weight: bold;
	font-family: sans-serif;
	text-align: center;
	cursor: pointer;
}
.btold{
	cursor: pointer;
}
.iframe{
	background-color: white;
}

.ifeditor{
	background-color: white;
	border: 0px;
}

.fword{
	border: 0px;
	border-collapse: collapse;
}
.fword .bescura{
	background-color: black;
	height: 1px;width: 1px;
}
.fword .bclara{
	background-color: white; height: 1px;width: 1px;
}
fieldset .normal
,div .normal{
	/* border: 1px solid #CCCCCC; */
	margin: 5px;
	border-collapse: collapse;
}

fieldset .normal .header
,div .normal .header{
	margin: 10px;
	/* background-color: #eff0f2; */
	/* background: url("../img/hdbg.gif") repeat-x; */
}

fieldset .normal .header td
,div .normal .header td{
	border: 1px solid #d0d0d0;
}
fieldset .normal .res{
	background-color: #f7f7f7;
}
fieldset .normal .res td
,div .normal .res td{
	color: blue;
	border: 1px solid #CCCCCC;
	cursor: hand;
	cursor:pointer;
}

fieldset .normal .res:nth-child(odd){
	background-color: #f7f7f7;
}
fieldset .normal .res:nth-child(even){
	background-color: white;
}

fieldset .normal .res .bthl{
	width: 0px;
	height: 0px;
	padding: 0px;
	margin: 0px;
	border: 0px;
	background-color: none;
}

fieldset .normal .resverm td
,div .normal .resverm td{
	color: red;
	border: 1px solid #CCCCCC;
	cursor: hand;
	cursor:pointer;
}
fieldset .normal .resverde td
,div .normal .resverde td{
	color: #008000;
	border: 1px solid #CCCCCC;
	cursor: hand;
	cursor:pointer;
}
fieldset .normal .respreto td
,div .normal .respreto td{
	color: black;
	border: 1px solid #CCCCCC;
}
</style>


<script type="text/javascript">

</script>


</head>

<body >
<?
if(!empty($_1_u_sgdocupd_idsgdoctipodocumento)){
    $arrtipodoc=getObjeto("sgdoctipodocumento", $_1_u_sgdocupd_idsgdoctipodocumento, "idsgdoctipodocumento");
}

if($_1_u_sgdocupd_status == 'APROVADO' OR $_1_u_sgdocupd_status == 'OBSOLETO'){
			
			 $aprovado="Aprovador";	
			
}else{
	$aprovado = "Alterado por";
}
$sqlfig="select logosis from empresa where 1".getidempresa('idempresa','documento');
	$resfig = d::b()->query($sqlfig) or die($sqlfig."Erro ao retornar figura para cabeçalho do relatà³rio: ".mysqli_error(d::b()));
	$figrel=mysqli_fetch_assoc($resfig);
?>

<fieldset style="border: none; border-top: 2px solid silver;">
	</fieldset>	
<table>
 <tr>
 <td style="display: inline-table; vertical-align:top;">
 	<table  class="tbrepheader">
	<tr>
		<td  rowspan="3" style="width:200;"><img src="<?=$figrel["logosis"]?>" border="0"></td>
	</tr>
	</table>
 </td>
 <td>	
	<table>
	<tr>
		<td>
			<table>
			<tr>
				<td><label>Id:</label></td>
				 <td><label><?=$_1_u_sgdocupd_idsgdoc?></label></td> 
	
				<td><label>Vers&atilde;o:</label></td>
				<td><label><?=$_1_u_sgdocupd_versao?>.<?=$_1_u_sgdocupd_revisao?></label></td> 
			</tr>
			</table>
			<table class="audit">
<?
/*
	$sql="SELECT idsgtipodoc,concat(if(flgfuncao = 'Y',' Funcao - ',''), tipodocumento ) as tipodocumento
	FROM sgtipodoc t 
	where t.idsgtipodoc = ".$_1_u_sgdocupd_idsgtipodoc." and t.idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]." order by if(flgfuncao = 'Y',' (Funcao)','')";
	$res = d::b()->query($sql) or die("A Consulta do tipo do documento falhou :".mysqli_error(d::b())."<br>Sql:".$sql);
	$row = mysqli_fetch_assoc($res);
?>				
			<tr> 
				<td class="rot">Tipo Documento:</td> 
				<td><label><?=$row["tipodocumento"]?></label></td> 
			</tr>
 
 */
?>
			<tr> 
				<td class="rot">Titulo:</td> 
				<td><label><?=$_1_u_sgdocupd_titulo?></label></td> 
			</tr>
<?
	$sql1="SELECT des FROM sgdocstatus where idsgdocstatus = '".$_1_u_sgdocupd_cstatus."'";
	$res1 = d::b()->query($sql1) or die("A Consulta do status do documento falhou :".mysqli_error(d::b())."<br>Sql:".$sql1);
	$row1 = mysqli_fetch_assoc($res1);
	
?>		
			<tr> 
				<td class="rot">Status:</td>
				<td><label><?=$row1["des"]?></label></td> 			
			</tr>
			<?
				if(!empty($_1_u_sgdocupd_responsavel)){?>
					<tr> 
						<td class="rot">Executor:</td>
						<td><label><?=$_1_u_sgdocupd_responsavel?></label></td> 			
					</tr>
			<?}?>
			<?
				if(!empty($_1_u_sgdocupd_inicio) and !empty($_1_u_sgdocupd_fim)){?>
					<tr> 
						<td class="rot">Início:</td>
						<td><label><?=dma($_1_u_sgdocupd_inicio)?></label></td>
						<td class="rot">Fim:</td>
						<td><label><?=dma($_1_u_sgdocupd_fim)?></label></td>
					</tr>
			<?}?>		
			</table>
		</td>
		<td style="vertical-align:top;">
		</td>
	</tr>
	</table>
</td>
</tr>
</table>	
<?
function migracaoPaginas(){
	global $_1_u_sgdocupd_idsgdoc;
	$sqlpag = "select
					d.idsgdoc
					,d.titulo
					,d.versao
					,d.revisao
					,d.idequipamento
					
					,p.idsgdocpag
					,p.pagina
					,p.conteudo
				from sgdoc d 
					left join sgdocpag p on d.idsgdoc = p.idsgdoc
				where d.idsgdoc = ".$_1_u_sgdocupd_idsgdoc." order by p.pagina ASC";//De acordo com a versao anterior

	$respag = d::b()->query($sqlpag) or die("Erro ao recuperar Páginas do Documento: ".mysqli_error(d::b())."\n<br>SQL: ".$sqlpag);

	$conteudo="";
	while ($r = mysqli_fetch_assoc($respag)) {
		$conteudo.=$r["conteudo"];
	}
	return $conteudo;
}
$idsgdoctipo= traduzid('sgdoc', 'idsgdoc', 'idsgdoctipo', $_1_u_sgdocupd_idsgdoc);

//var_dump($arrtipodoc);
//if($idsgdoctipo=='auditoria' or $idsgdoctipo=='avaliacao'){//mostrar questàµes
if($arrtipodoc["flquestionario"]=="Y"){
?>  
    <fieldset>
  <table class="normal" style="font-size:12px;">                
	                <tr class="respreto">
		                <td align="center">Qst.</td>
<?
                            $sqlp="select c.col, tc.rotcurto,tc.dropsql as code,tc.datatype
                                                    from sgdoctipodocumentocamposupd c 
                                                            join "._DBCARBON."._mtotabcol tc on (tc.tab = c.tabela and tc.col=c.col) 
                                                    where c.idsgdoctipodocumento=".$_1_u_sgdocupd_idsgdoctipodocumento." and c.versao=".$_1_u_sgdocupd_versao." and c.idsgdoc=".$_1_u_sgdocupd_idsgdoc." and c.visivel = 'Y' order by c.ord";
                            $resp=d::b()->query($sqlp) or die("Erro ao buscar questões sql".$sqlp);
							//die($sqlp);
                            $qtd=mysqli_num_rows($resp);
                            $col = array();
                            $rotcurto = array();
                            $code = array();
                            $datatype = array();
                            while ($rowp =mysql_fetch_assoc($resp)){
                                    array_push($col, $rowp["col"]);
                                    array_push($rotcurto, $rowp["rotcurto"]);
                                    array_push($code, $rowp["code"]);
                                    array_push($datatype, $rowp["datatype"]);
                                    ?>
                                    <td><?=$rowp["rotcurto"]?></td>
<?
                            }
?>		                
	                </tr>
<?
            $sqlp="select * from sgdocpagupd where  versao=".$_1_u_sgdocupd_versao." and idsgdoc=".$_1_u_sgdocupd_idsgdoc." order by pagina asc";
			//die($sqlp);
            $resp=d::b()->query($sqlp) or die("Erro ao buscar questàµes sql".$sqlp);
            $qtdpag=mysqli_num_rows($resp);
            $vqtdpag=$qtdpag+1;
            $li=99;
            if($qtdpag > 0){
                while($rowp =mysql_fetch_assoc($resp)){                    
                    $li++;
                    $i = 0;
?>
                        <tr  class="respreto" >
                            <td align="center"><?=$rowp["pagina"]?></td>
<?
                            while($i < $qtd){
?>
                            <td>
                              <?=$rowp[$col[$i]]?>
                            </td>
<?
                                    $i++;
                            }
?>
                        </tr>
<?
                }//while($rowp =mysql_fetch_assoc($resp)){
            }//if($qtdpag > 0){
?>
  </table>
    </fieldset>
<?
}else{
        if(empty($_1_u_sgdocupd_conteudo)){


            $sqldoc="select * from sgdocupd where  conteudo is not null  and idsgdoc = ".$_1_u_sgdocupd_idsgdoc." and idsgdocupd < ".$_1_u_sgdocupd_idsgdocupd." order by idsgdocupd desc limit 1";
            $resdoc = d::b()->query($sqldoc) or die("A Consulta de conteudo falhou :".mysqli_error(d::b())."<br>Sql:".$sqldoc); 
            $qtdrowsd= mysqli_num_rows($resdoc);
            if($qtdrowsd>0){
                 $rowdoc= mysqli_fetch_assoc($resdoc);
                $_1_u_sgdocupd_conteudo=$rowdoc['conteudo'];
            }else{

                $sqldoc="select conteudo from sgdoc where conteudo is not null and  idsgdoc = ".$_1_u_sgdocupd_idsgdoc;
                $resdoc = d::b()->query($sqldoc) or die("A Consulta de conteudo falhou :".mysqli_error(d::b())."<br>Sql:".$sqldoc); 
                $qtdrowsd= mysqli_num_rows($resdoc);
                if($qtdrowsd>0){
                    $rowdoc= mysqli_fetch_assoc($resdoc);
                    $_1_u_sgdocupd_conteudo=$rowdoc['conteudo'];
                }else{
                   $paginas = migracaoPaginas();
                    if(!empty(trim($paginas))){
                        $_1_u_sgdocupd_conteudo=$paginas;
                    }
                }
            }

        }
}//if($_1_u_sgdoc_idsgdoctipo=='auditoria' or $_1_u_sgdoc_idsgdoctipo=='avaliacao'){
?>	
<table>
<tr>
	<td width="900px">	
		<fieldset style="border: none; border-top: 2px solid silver;">
		</fieldset>	
			<?=$_1_u_sgdocupd_conteudo?>

	</td>		
</tr>
</table>
<?//listar assinaturas
	if(!empty($_1_u_sgdocupd_idsgdoc)){
			$sql ="select p.nome, c.alteradoem as dataassinatura
                                from carrimbo c,pessoa p
                                where c.tipoobjeto ='documento' 
                                and c.idobjeto =".$_1_u_sgdocupd_idsgdoc."
                                and versao=".$_1_u_sgdocupd_versao."                                
                                and c.status!='PENDENTE' 
                                ".getidempresa('p.idempresa','funcionario')."
                                and p.idpessoa = c.idpessoa order by p.nome";
		
			$res = d::b()->query($sql) or die("A Consulta das assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql); 
			$qtdrows= mysqli_num_rows($res);

		if($qtdrows > 0){
	?>
		<td style="vertical-align:top;">
		<fieldset>
			<legend>Assinaturas</legend>
			  <table align="left" class="normal" style="font-size:12px;"> 
			 <tr> 	
				
				<td class="header">Funcionários</td>
				
				<td class="header">Dt. Assinatura</td>
			 </tr>
	<?			
			while($row = mysqli_fetch_array($res)){		
	?>				
			 <tr class="respreto">
				<td nowrap><?=$row["nome"]?></td>
				
				<td nowrap><?=dma($row["dataassinatura"])?></td>
			</tr>	 
	<?
			}
	?>
			</table>
		</fieldset>		
		</td>
	<?		
		}
	}
	?>
</table>
<?
$sqlAnexos = "SELECT * from sgdocanexo where idsgdoc = ".$_1_u_sgdocupd_idsgdoc." and versao = ".$_1_u_sgdocupd_versao;
$resAnexos = d::b()->query($sqlAnexos) or die("Erro ao buscar anexos: ".mysqli_error(d::b())."\n<br>SQL: ".$sqlAnexos);
$qtdAnexos = mysqli_num_rows($resAnexos);
if($qtdAnexos > 0){?>
	<br></br>
	<fieldset style="border: none; border-top: 2px solid silver;"></fieldset>	
		<table class="normal" style="font-size:12px;">  
			<tr class="respreto">
				<td align="center"><b>Anexos</b></td>
			</tr>
			<?
				while($rowAnexos = mysqli_fetch_assoc($resAnexos)){
					?>
					<tr class="respreto">
						<td>
							<?
							$rowAnexos["camimho"] = str_replace("../", "/", $rowAnexos["caminho"]);
							?>
							<a href="<?=$rowAnexos["caminho"]?>" target="_blank"><?=$rowAnexos["nome"]?></a>
						</td>
					</tr>
					<?
				}?>
		</table>
<?}?>
<fieldset style="border: none; border-top: 2px solid silver;"></fieldset>	
<table class="normal" style="font-size:12px;">  
         <tr class="respreto">
             <td align="center"><b>Histórico</b></td>
         </tr>
         <tr class="respreto">
             <td><?=$_1_u_sgdocupd_acompversao?></td>
         </tr>
     </table>
<br></br>
<fieldset>
                <table class="normal" style="font-size:12px;">                
	                <tr class="respreto">
		                <td></td>
		                <td>Nome</td>
		                <td align="center">Data</td>
		                <td style="width:300px;" align="center">Assinatura</td>
	                </tr>
                         <tr class="respreto">
                                <td>Redator</td>                                
                                <td><?=$_1_u_sgdocupd_criadopor?></td>
                                <td><?=$_1_u_sgdocupd_criadoem?></td>
                                <td></td>
                        </tr>
                        <tr class="respreto">
                                <td><?=$aprovado?> </td>
                                <td><?=$_1_u_sgdocupd_alteradopor?></td>
                                <td><?=$_1_u_sgdocupd_alteradoem?></td>
                                <td></td>
                        </tr>
                </table>
</fieldset>	      
</body>
</html>

