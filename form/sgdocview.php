<?
require_once("../inc/php/validaacesso.php");
$idpessoa = $_SESSION["SESSAO"]["IDPESSOA"];

if($_POST){
	include_once("../inc/php/cbpost.php");
}

//Parà¢metros mandatà³rios para o carbon
$pagvaltabela = "sgdoc";
$pagvalcampos = array(
	"idsgdoc" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from sgdoc where  idsgdoc = '#pkid'";

/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");


$sqlpag = "select
			    d.idsgdoc
                            ,d.idregistro
			    ,d.titulo
			    ,d.versao
			    ,d.revisao
			    ,d.idequipamento
                            ,td.rotulo
			    ,ps1.nome as elaborador
			    ,ps2.nome as aprovador
			    ,std.tipodocumento			   
			    ,s.des as status
			    ,d.conteudo
                            ,d.status as des
                            ,d.status  as statusdoc
                            ,d.idsgdoctipo
			from 
			    sgdoc d 
					left join sgdoctipo td on d.idsgdoctipo = td.idsgdoctipo
                                        left join sgdoctipodocumento std on d.idsgdoctipodocumento = std.idsgdoctipodocumento
                                        left join sgdocstatus s on s.idsgdocstatus= d.status
					left join pessoa ps1 on ps1.usuario = d.criadopor
					left join pessoa ps2 on ps2.usuario = d.alteradopor
			where
				d.idsgdoc = ".$_1_u_sgdoc_idsgdoc;

$respag = d::b()->query($sqlpag) or die("Erro ao recuperar Páginas do Documento: ".mysqli_error()."\n<br>SQL: ".$sqlpag);


function migracaoPaginas(){
	global $_1_u_sgdoc_idsgdoc;
	$sqlpag = "select
					d.idsgdoc
                                        ,d.idregistro
					,d.titulo
					,d.versao
					,d.revisao
					,d.idequipamento
					,d.idsgtipodoc
					,p.idsgdocpag
					,p.pagina
					,p.conteudo
                                        ,d.idsgdoctipo
				from sgdoc d 
					left join sgdocpag p on d.idsgdoc = p.idsgdoc
				where d.idsgdoc = ".$_1_u_sgdoc_idsgdoc." order by p.pagina ASC";//De acordo com a versao anterior

	$respag = d::b()->query($sqlpag) or die("Erro ao recuperar Páginas do Documento: ".mysqli_error(d::b())."\n<br>SQL: ".$sqlpag);

	$conteudo="";
	while ($r = mysqli_fetch_assoc($respag)) {

                
		$conteudo.=$r["conteudo"];
	}
	return $conteudo;
}

if(!empty($_1_u_sgdoc_idsgdoctipodocumento)){
    $arrtipodoc=getObjeto("sgdoctipodocumento", $_1_u_sgdoc_idsgdoctipodocumento, "idsgdoctipodocumento");
}

?>
<html lang="pt-br" dir="ltr" class="CSS1Compat" >
<head>
<title>Impressão</title>

<style data-cke-temp="1" type="text/css" media="all">
html {
	height: 100% !important;
}

img:-moz-broken {
	-moz-force-broken-image-icon: 1;
	width: 24px;
	height: 24px;
}
p.pagebreak{
	height: 0px;
	width: 0px;
	margin: 0px;
	padding: 0px;
	border: none;
	page-break-before:always;
}
.tablecabecalho{
	padding:0px;
	margin: 0px;
	border: 1px solid silver;
	width: 100%;
	border-collapse: collapse;
}
.tablecabecalho td{
	border-collapse: collapse;
	border: 1px solid silver;
	margin: 0px;
	padding-left: 6px;
	font-size:12px;
}
.bold{
	font-weight: bold;
}
.copiancontrolada{
	border: none;
	position:fixed;
	top: 50%;
	left:80px;
	z-index:-100;
}
.tablecabecalho .label{
	font-size: 10px;
	font-weight: bold;
	text-align: right;
}
.tablerodape{
	padding:0px;
	margin: 0px;
	border: 1px solid silver;
	width: 100%;
	border-collapse: collapse;
    position: fixed;
    width: auto;
    bottom: 0px;
}
.tablerodape td{
	border-collapse: collapse;
	border: 1px solid silver;
	margin: 0px;
	padding-left: 6px;
	padding-right: 6px;
	font-size:12px;
}
.tablerodape .label{
	font-size: 10px;
	font-weight: bold;
	border:none;
}

a.btbr20{
	display: none;
}

/* Botao branco fonte 8 */
a.btbr20:link{
	position: fixed;

	right: 15px;

    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;
      
	background: #cccccc; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ececec', endColorstr='#dcdcdc'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ececec), to(#dcdcdc)); /* webkit */
	background: -moz-linear-gradient(top,  #ececec, #dcdcdc); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	
 	text-decoration: none;
}
a.btbr20:hover
{
    font-weight: bold;
    font-size:20px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;

	background: #eaeaf4; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff0000', endColorstr='#c02900'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ff0000), to(#c02900)); /* webkit */
	background: -moz-linear-gradient(top, #ffffff, #e1e1e1); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	text-decoration: none;
} 
a.btbr20:visited {
	border: 1px solid silver;
	color:white;
	text-decoration: none;
}


a.btbr10{
	display: none;
}

/* Botao branco fonte 8 */
a.btbr10:link{
	position: fixed;

	right: 15px;

    font-weight: bold;
    font-size:10px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;
      
	background: #cccccc; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ececec', endColorstr='#dcdcdc'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ececec), to(#dcdcdc)); /* webkit */
	background: -moz-linear-gradient(top,  #ececec, #dcdcdc); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	
 	text-decoration: none;
}
a.btbr10:hover
{
    font-weight: bold;
    font-size:10px;
    color: silver;
    
	border: 1px solid #d7d7d7;
    cursor: pointer;

    padding-left: 5px;
    padding-right: 5px;
    padding-bottom: 1px;
    margin-left: 5px;

	background: #eaeaf4; /* para browsers sem suporte a CSS 3 */

	/* Gradiente */
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ff0000', endColorstr='#c02900'); /* IE */
	background: -webkit-gradient(linear, left top, right top, from(#ff0000), to(#c02900)); /* webkit */
	background: -moz-linear-gradient(top, #ffffff, #e1e1e1); /* FF */

    /* Arredondamento */
	-moz-border-radius: 8px;
 	-webkit-border-radius: 8px;
 	border-radius: 8px 8px 8px 8px;
 	text-decoration: none;
} 
a.btbr10:visited {
	border: 1px solid silver;
	color:white;
	text-decoration: none;
}
</style>

<style data-cke-temp="1" type="text/css" media="screen">
body{
	padding-bottom: 30px; /* *Este valor deve ser maior que a altura do rodapé. Margem de seguranca para mostrar o final do texto antes de imprimir. Sem isso o texto ficará oculto atrás do rodapé. */
}
.escondetab{
	display: none;
}
p.pagebreak{
	border-bottom: 1px solid silver;
	width: 100%;
	
}
a.btbr20{
	display: block;
}
a.btbr10{
	display: block;
}
.tablerodape{
	padding:0px;
	margin: 0px;
	border: 1px solid silver;
	width: 100%;
	border-collapse: collapse;
    position: fixed;
    width: auto;
    bottom: 0px;
    background-color: white;
}
.copiancontrolada{
	display: none;
}
</style>

</head>
<body style="width: 643px; max-width: 643px" spellcheck="false" class="cke_show_borders">
<?if(traduzid("sgdoc","idsgdoc","idsgtipodoc",$idsgdoc)!=51){?>
    
<?
$pbreak = false;
$escondetab = "";
$rpag=mysqli_fetch_assoc($respag);
/*
	if($_1_u_sgdoc_cpctr=='N'){?>
	<img class="copiancontrolada" border="0" src="../inc/img/copiancontrolada.gif"/>
	<?}else{?>
	<img class="copiancontrolada" border="0" src="../inc/img/copiancontrolada.gif"/>
	<?}
 * */
 
}


if($rpag['statusdoc']!='APROVADO'){ 
    $sqlpag1="select * from sgdocupd where  idsgdoc = ".$_1_u_sgdoc_idsgdoc."  order by idsgdocupd desc limit 1";
    
    //die($sqlpag1);
    $respag1 = d::b()->query($sqlpag1) or die("Erro ao buscar versão anterior do Documento: ".mysqli_error(d::b())."\n<br>SQL: ".$sqlpag1);
    $qtdv= mysqli_num_rows($respag1);
    if($qtdv>0){
        $rpag=mysqli_fetch_assoc($respag1);
        if(empty($rpag['conteudo'])){
            $sqldoc="select * from sgdocupd where  conteudo is not null  and idsgdoc = ".$_1_u_sgdoc_idsgdoc." order by idsgdocupd desc limit 1";
            $resdoc = d::b()->query($sqldoc) or die("A Consulta de conteudo falhou :".mysqli_error(d::b())."<br>Sql:".$sqldoc); 
            $qtdrowsd= mysqli_num_rows($resdoc);
            if($qtdrowsd>0){
                $rowdoc= mysqli_fetch_assoc($resdoc);
                $rpag['conteudo']=$rowdoc['conteudo'];
            }else{

                $sqldoc="select conteudo from sgdoc where conteudo is not null and  idsgdoc = ".$_1_u_sgdoc_idsgdoc;
                $resdoc = d::b()->query($sqldoc) or die("A Consulta de conteudo falhou :".mysqli_error(d::b())."<br>Sql:".$sqldoc); 
                $qtdrowsd= mysqli_num_rows($resdoc);
                if($qtdrowsd>0){
                    $rowdoc= mysqli_fetch_assoc($resdoc);
                    $rpag['conteudo']=$rowdoc['conteudo'];
                }else{
                    $paginas = migracaoPaginas();
                    if(!empty(trim($paginas))){
                        $rpag['conteudo']=$paginas;
                    }
                }
            }  
        }
    }else{
        $sqlpag = "select
			    d.idsgdoc
                            ,d.idregistro
			    ,d.titulo
			    ,d.versao
			    ,d.revisao
			    ,d.idequipamento
                            ,td.rotulo
			    ,ps1.nome as elaborador
			    ,ps2.nome as aprovador
			    ,std.tipodocumento			   
			    ,s.des as status 
			    ,d.conteudo
                            ,d.idsgdoctipo
                            
			from 
			    sgdoc d 
					left join sgdoctipo td on d.idsgdoctipo = td.idsgdoctipo
                                        left join sgdoctipodocumento std on d.idsgdoctipodocumento = std.idsgdoctipodocumento
                                        left join sgdocstatus s on s.status = d.status
					left join pessoa ps1 on ps1.usuario = d.criadopor
					left join pessoa ps2 on ps2.usuario = d.alteradopor
			where
				d.idsgdoc = ".$_1_u_sgdoc_idsgdoc;

        $respag = d::b()->query($sqlpag) or die("Erro ao recuperar Páginas do Documento: ".mysqli_error()."\n<br>SQL: ".$sqlpag);
        $rpag=mysqli_fetch_assoc($respag);
        
    }


}

if(empty($rpag['conteudo'])){
    $paginas = migracaoPaginas();
    if(!empty(trim($paginas))){
	    $rpag['conteudo']=$paginas;
    }	
}
	
	
	
?>
	<TABLE class="tablecabecalho <?=$escondetab?>" spacing="0">
	    <thead>
		<TR>
		<?
			$sqlfig="select logosis from empresa where idempresa =".$_SESSION["SESSAO"]["IDEMPRESA"];
			$resfig = mysql_query($sqlfig) or die("Erro ao retornar figura para cabeçalho do relatà³rio: ".mysql_error());
			$figrel=mysql_fetch_assoc($resfig);
			$figrel["logosis"] = str_replace("../", "", $figrel["logosis"]);
			
			?>
			<TD ROWSPAN=7 align="center" style="width:74px;padding:4px;"><img src="<?=$figrel["logosis"]?>" border="0"></TD>
			<TD ROWSPAN=2 class="bold"><?=$rpag["tipodocumento"]?></TD>
                        <?if(empty($rpag["idregistro"])){$rpag["idregistro"]=$rpag["idsgdoc"];}?>
			<TD >Cód.</TD>
			<TD colspan="2"><?=$rpag["idregistro"]?></TD>
			
		</TR>
		<TR>
			<TD >Rev.</TD>
			<TD colspan="2"><?=$rpag["versao"].".".$rpag["revisao"]?></TD>
		</TR>
		
		<TR>
			<TD rowspan="3" class="bold"><?=$rpag["titulo"]?></TD>
                </TR>
		<TR>
			<TD>Status</TD>
			<TD colspan="2"><?=$rpag["status"]?></TD>
		</TR>
	    </thead>
					
<style>
.listaitens{
	border: none;
	margin: 5px;
	padding: 0px;
}
.listaitens{
	font-size: 11px;
	list-style: none outside none;
}
.listaitens .cab{/* cabecalho para liste de itens*/
	color: gray;
	font-size:9px;
	list-style: none outside none;
}

</style>
<tr>
<?
	$sqlv="SELECT s.titulo,s.idsgdoc,v.idsgdocvinc
		FROM `sgdocvinc` v,sgdoc s  
		where s.idsgdoc=v.iddocvinc 
			and v.idsgdoc = ".$idsgdoc." order by titulo";
	$resv = d::b()->query($sqlv) or die("A Consulta dos documentos vinculados falhou :".mysqli_error()."<br>Sql:".$sqlv);
	$qtdrows1= mysqli_num_rows($resv);

	if($qtdrows1>0 ){
?>
<td colspan="4">
				<ul class="listaitens">
					<li class="cab">Documentos vinculados:</li>
<?		while($rdvinc = mysqli_fetch_array($resv)){?>

					<li><a target="_blank" href="sgdocprint.php?acao=u&idsgdoc=<?=$rdvinc["idsgdoc"]?>"><?=$rdvinc["idsgdoc"]?> - <?=$rdvinc["titulo"]?></a></li>

<?		}
	}
?>
	</tr>
	<tr>
<?
		$sqlarq = "select a.*, dmahms(criadoem) as datacriacao 
					from arquivo a 
					where 
						a.tipoobjeto = 'sgdoc' 
						and a.idobjeto = ".$idsgdoc." 
						and tipoarquivo = 'ANEXO' 
					order by idarquivo asc";
	
		//echo $sqlarq."<br>";
		$res = d::b()->query($sqlarq) or die("Erro ao pesquisar arquivos:".mysqli_error());
		$numarq= mysqli_num_rows($res);

	if($numarq>0  ){
?>

	<td colspan="4">
		<ul class="listaitens">
			<li class="cab">Arquivos Anexos (<?=$numarq?>)</li>
<?		while ($row = mysqli_fetch_array($res)) {?>
			<li><a title="Abrir arquivo" target="_blank"  href="../upload/<?=$row["nome"]?>"><?=$row["nome"]?></a></li>
<?		}
?>
		</ul>
	</td>

<?
	}
?>	

	</tr>
	
		
	</TABLE>
<?
//if($rpag["idsgdoctipo"] =='auditoria' or $rpag["idsgdoctipo"]=='avaliacao')
if($arrtipodoc["flquestionario"]=="Y"){
?><p>
    <TABLE class="tablecabecalho">                
	                <tr >
		                <td align="center">Qst.</td>
<?
                            $sqlp="select c.col, tc.rotcurto,tc.dropsql as code,tc.datatype
                                                    from sgdoctipodocumentocamposupd c 
                                                            join "._DBCARBON."._mtotabcol tc on (tc.tab = c.tabela and tc.col=c.col) 
                                                    where c.idsgdoctipodocumento=".$_1_u_sgdoc_idsgdoctipodocumento." and c.versao=".$rpag["versao"]." and c.idsgdoc=".$rpag["idsgdoc"]." and c.visivel = 'Y' order by c.ord";
                            $resp=d::b()->query($sqlp) or die("Erro ao buscar questões sql".$sqlp);
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
            $sqlp="select * from sgdocpagupd where  versao=".$rpag["versao"]." and idsgdoc=".$rpag["idsgdoc"]." order by pagina asc";
           // die($sqlp);
            $resp=d::b()->query($sqlp) or die("Erro ao buscar questàµes sql".$sqlp);
            $qtdpag=mysqli_num_rows($resp);
            $vqtdpag=$qtdpag+1;
            $li=99;
            if($qtdpag > 0){
                while($rowp =mysql_fetch_assoc($resp)){                    
                    $li++;
                    $i = 0;
?>
                        <tr >
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

<?}else{?>

<!-- INICIO IMPRESSAO CONTEUDO PAGINA <?=$rpag["pagina"]?> -->
	<?=$rpag["conteudo"]?>
<!-- TERMINO IMPRESSAO CONTEUDO PAGINA <?=$rpag["pagina"]?> -->
<?}?>
		<p>	
	<TABLE class="tablerodape <?=$escondetab?>" spacing="0">
		<TR>
			<TD nowrap><label class="label">Elaborador:</label><br><?=$rpag["elaborador"]?></TD>
			<TD style="width:80%;"></TD>
			<TD nowrap><label class="label">Aprovador:</label><br><?=$rpag["aprovador"]?></TD>
		</TR>
	</TABLE>
<?
	$pbreak=true;
	$escondetab = "escondetab";

?>
<?
		$sqlalt="select dma(a.alteradoem) as dmadata,a.* from sgdocupd a
				where a.idsgdoc = ".$idsgdoc." order by a.idsgdocupd desc";
			$resalt = d::b()->query($sqlalt) or die("A Consulta do relatà³rio de versàµes falhou :".mysqli_error()."<br>Sql:".$sqlalt);
			$qtdrowa2= mysqli_num_rows($resalt);		
			
				if($qtdrowa2>0){							
	?>	
				<TABLE class="tablecabecalho">
				<TR>
					<TD class="bold" align="center"  colspan="3">Histórico do Documento</TD>
				</TR>
				<TR>	
					<TD class="bold" align="center">Versão</TD>
					<TD class="bold" align="center">Data</TD>
					<TD class="bold">Descrição</TD>
				</TR>				
	<?			
				while($rowalt = mysqli_fetch_array($resalt)){	
	?>
				<TR>
					<td align="center"><?=$rowalt["versao"]?>.<?=$rowalt["revisao"]?></td>
					<td align="center"><?=$rowalt["dmadata"]?></td> 
					<td style="width:80%"><?=nl2br($rowalt["acompversao"])?></td> 					
				</tr>
	<?
				}
	?>
			</table>
	<?
			}
			
/*			
			$sql = "select p.idpessoa
				    ,p.nome 
				    ,dma(c.alteradoem) as dataleitura
				    ,dma(c.alteradoem) as dataassinatura 
				from carrimbo c ,pessoa p 
				where c.idpessoa = p.idpessoa
				and c.status ='ATIVO'
                                and c.tipoobjeto='documento'
				and c.idobjeto=".$idsgdoc." order by nome";
		
			$res = d::b()->query($sql) or die("A Consulta de assinaturas falhou :".mysqli_error(d::b())."<br>Sql:".$sql); 
			$existe = mysqli_num_rows($res);
			if($existe>0){
?>				
				<table class="tablecabecalho">
				    <thead>
					<tr>
                                            <th class="bold" align="center" colspan="2">
						Assinaturas
					    </th>
					</tr>
				    </thead>
					<tr>
						<td class="bold" align="center">Funcionários</td>
						<td  class="bold" align="center">Data Assinatura</td>		
					</tr>			
<?			
				while($row = mysqli_fetch_assoc($res)){			
				
		?>	
					<tr class="res">
						<td nowrap><?=$row["nome"]?></td>
						<td nowrap><?=$row["dataassinatura"]?></td>
					</tr>				
		<?							
				}
		?>	
			</table>
			<p>&nbsp;</p>

<?
			} 
 *
 */                         
	?>		
</body>
</html>
<script>

$('#cbSalvar').addClass('disabled');   

</script>
