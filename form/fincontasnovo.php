<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}
$sql=" select * from pessoa where flgsocio='Y' and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor=mysqli_num_rows($res);

if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or array_key_exists("quitardebito", getModsUsr("MODULOS")) or $flgdiretor>0) {
    //se não for diretor
    if($flgdiretor<1){       
        
        if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) and array_key_exists("quitardebito", getModsUsr("MODULOS"))){
             ?>  

            <link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
            <br>
            <div class="row">
                    <div class="col-md-12">
                            <div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

                            <strong><i class="glyphicon glyphicon-info-sign"></i> Usuário só deve ter:
                            <br/>
                            <br/>Quitar Crédito ou Quitar Débito em suas permissões no sistema.
                            <br/>
                            <br/>Favor entrar em contato com Departamento de Processos - Ramal: 110
                            </div>
                    </div>
            </div>
<?
             die;
        }elseif(array_key_exists("quitarcredito", getModsUsr("MODULOS"))){
            $clausulalp .=" and cp.tipo in ('C') and cp.tipoobjeto  in('nf','notafiscal')"; 
            $joincontaitem="";
           
        }elseif(array_key_exists("quitardebito", getModsUsr("MODULOS"))){
            $clausulalp .=" and cp.tipo in ('D') "; 

            $joincontaitem="left join contaitem i on (  cp.idcontaitem = i.idcontaitem and i.visualizarext='Y') ";
        }
    }else{
        $clausulalp='';
        $joincontaitem="";
    }
}else{
 ?>  
<style>


</style>
                
<link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
	<br>
	<div class="row">
		<div class="col-md-12">
			<div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

			<strong><i class="glyphicon glyphicon-info-sign"></i> Usuário sem permissão para visualização.
			<br/>
			<br/>É necessário liberar nas permissões do usuário uma das opções:
			<br/>*Quitar Débitos
			<br/>*Quitar Créditos
                        <br/>
                        <br/>Favor entrar em contato com Departamento de Processos - Ramal: 110
			</div>
		</div>
	</div>
<?
    die;
}

#inserção de 2 drops(ITEM E TIPO) para pesquisa -- 27/08/09 -- Fabiano
#28-08-09-maf-insercao do campo idcontadesc como filtro
################################################## Atribuindo o resultado do metodo GET

$_fds 	= $_GET["_fds"]; 

$datas = explode("-", $_fds);

$vencimento_1 	= $datas[0];
$vencimento_2 	= $datas[1];

//echo($vencimento_1);

$valor_1		  = trim($_GET["valor_1"]);
$valor_2		  = trim($_GET["valor_2"]);
$itemconta 		  = trim($_GET["itemconta"]);
$idcontadesc	  = $_GET["idcontadesc"];
$drops			  = false;
$controle		  = $_GET["controle"];
$tipo			  = $_GET["tipo"];
$statuspgto		  = $_GET["status"];
$idagencia        = $_GET["idagencia"];
$previsao         = $_GET["previsao"];
//$contadesc = $_GET["contadesc"];
$obs              = $_GET["obs"];
$visivel          =$_GET["visivel"];
$nome             =empty($_GET["_fts"])?$_GET['nome']:$_GET["_fts"];
$idformapagamento =$_GET["idformapagamento"];
$idcartao         =$_GET["idcartao"];

$buscaitens="Y";

//$clausula .= " vencimento > '2009-01-01' and ";

//print_r($_SESSION["post"]);
if(!empty($controle) and $controle!='undefined'){
	$clausulad .= " idcontapagar = " . $controle ." and ";
    $clausulai .=" pg.idcontapagar = " . $controle ." and ";
    

}
if($previsao=='N'){
    $clausulad.=" exists (select 1 from nf n where n.idnf = cp.idobjeto and n.status ='CONCLUIDO') AND 
                            cp.tipoobjeto ='nf' and " ;
}

if(!empty($itemconta) and $itemconta!='undefined'){
	$clausulad .=" exists (select 1 from  contaitem ci
							where  cp.idcontaitem = ci.idcontaitem
							and ci.idcontaitem = ".$itemconta." ) and ";
    $clausulai .=" exists (select 1 from  contaitem ci
							where  cp.idcontaitem = ci.idcontaitem
							and ci.idcontaitem = ".$itemconta." ) and ";
	$drops = true;
	
}
IF($idformapagamento){
    $clausulad.=" cp.idformapagamento=".$idformapagamento." and ";
    if($previsao=='N'){
        $prev="  AND pg.tipoobjeto = 'nf' AND  exists (select 1 from nf n where n.idnf =pg.idobjeto and n.status ='CONCLUIDO')" ;
    }
    
    $joincp=" join  contapagar pg on(pg.idcontapagar=cp.idcontapagar ".$prev." and pg.idformapagamento=".$idformapagamento." )";
}else{
     if($previsao=='N'){
        $prev=" AND pg.tipoobjeto = 'nf' AND  exists (select 1 from nf n where n.idnf =pg.idobjeto and n.status ='CONCLUIDO') " ;
    }
    
      $joincp=" join  contapagar pg on( pg.idcontapagar=cp.idcontapagar ".$prev." )";
}

if(!empty($obs) and $obs!='undefined'){
	$clausulad .=" cp.obs like ('%".$obs."%') and ";
    $buscaitens="N";
}
/*
if(!empty($contadesc) and $contadesc!='undefined'){
	$clausulad .=" cd.contadesc like ('%".$contadesc."%') and ";
	$drops = true;
}
*/
if (!empty($vencimento_1) and !empty($vencimento_2) and $vencimento_1!='undefined' and $vencimento_2!='undefined'){
	$dataini = validadate($vencimento_1);
	$datafim = validadate($vencimento_2);

	if ($dataini and $datafim){
		
		$clausulad .= " (datareceb  BETWEEN '" . $dataini ."' and '" .$datafim ."')"." and ";
                $clausulai .= " (pg.datareceb  BETWEEN '" . $dataini ."' and '" .$datafim ."')"." and ";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}

if (!empty($valor_1) and !empty($valor_2)  and $valor_1!='undefined' and $valor_2!='undefined'){
	if (is_numeric($valor_1) and is_numeric($valor_2)){
		
		$clausulad .= " (cp.valor BETWEEN " . $valor_1 ." and " .$valor_2 .")  and ";
        $clausulai .= " (cp.valor BETWEEN " . $valor_1 ." and " .$valor_2 .")  and ";
	}else{
		die ("Os valores de contas informados [".$valor_1."] e [".$valor_2."] s&atilde;o inv&aacute;lidos!");
	}
}




    

if (!empty($statuspgto) and $statuspgto!='undefined'){
	
	$clausulad .= " cp.status = '" . $statuspgto ."' and";
    $clausulai .= " cp.status = '" . $statuspgto ."' and";

}

if($nome){
    $cpessoa =" join pessoa p ";
    $clausulad .= " (p.idpessoa = cp.idpessoa  and p.nome like('%".$nome."%')) and";
    $clausulai .= " (p.idpessoa = cp.idpessoa  and p.nome like('%".$nome."%')) and";
}else{    
    $cpessoa =" left join pessoa p on(p.idpessoa = cp.idpessoa ) ";    
}



if(!$clausulad == ''){//aqui estava assim
	$clausulad = " where " . substr($clausulad,1,strlen($clausulad) - 5);
   
}

//verificar se e usuario com modulo master restaurar ativo
 $sqlm=" select if('restaurar' in (".getModsUsr("SQLWHEREMOD")."),'Y','N') as master";
 $resm = d::b()->query($sqlm) or die("Falha ao pesquisar SQLWHEREMOD usuario master : " . mysqli_error(d::b()) . "<p>SQL: $sqlm");
 $rowm=mysqli_fetch_assoc($resm);
//die($sqlm);
     
if($_GET and !empty($clausulad)){
   if($flgdiretor<1){   
        if(array_key_exists("quitarcredito", getModsUsr("MODULOS"))){
            $clausulad .=" and cp.tipo in ('C') and cp.tipoobjeto  in('nf','notafiscal')  "; 
            $joincontaitem="";
           // $buscaitens="N";
            $clausulad .="      and exists (select 1 from contapagaritem i where i.idcontapagar=c.idcontapagar and i.tipoobjetoorigem in ('nf','notafiscal') )
                                    ";
             $clausulai.= "  cp.visivel='S' and cp.tipo in ('C') and ";

        }elseif(array_key_exists("quitardebito", getModsUsr("MODULOS"))){
            $clausulad .=" and  cp.tipo in ('D')   "; 
            $joincontaitem=" join nf n on (  cp.idobjeto = n.idnf and (cp.tipoobjeto like ('nf%') or cp.tipoobjeto='gnre') and n.tiponf not in('R','D'))  ";

            $clausulad .=" and (exists (select 1 from contapagaritem i,nf n where i.idcontapagar=cp.idcontapagar and i.tipoobjetoorigem ='nf' and i.idobjetoorigem = n.idnf and n.tiponf not in('D','R') ) 
                                or 
                                exists (select 1 from contapagaritem i where i.idcontapagar=cp.idcontapagar and i.tipoobjetoorigem ='contapagar' )
                                )   ";
             $clausulai.= "  cp.visivel='S' and cp.tipo in ('D') and";

        }
   }

    if(!empty($tipo) and $tipo!='undefined' and $tipo!='T'){
	if($tipo == 'C'){
            $clausulad .= " and cp.tipo = 'C'  ";
            $clausulai .= "  cp.tipo = 'C' and ";
           //$buscaitens="N";
	}elseif($tipo == 'D'){
            $clausulad .= " and cp.tipo = 'D'  ";
            $clausulai .= "  cp.tipo = 'D' and";
	}
    }   

     
if($flgdiretor<1){
  $clausulad .= " and cp.visivel = 'S' ";         
}elseif(!empty($visivel) and ($visivel!='undefined') and $rowm['master']=="Y"){
	$clausulad .= " and cp.visivel = '".$visivel."' ";
       
    }elseIF(!empty($visivel) and ($visivel!='undefined') and $rowm['master']!="Y"){
	$clausulad .= " and cp.visivel = 'S' ";
       
    }elseif($rowm['master']!="Y"){
	$clausulad .= " and cp.visivel = 'S' ";       
    }
    
    
    

  /*  
    if(!empty($idcartao)){
        $clausulad .= " and exists ( select 1 from contapagaritem ci,nf n
                            where ci.idcontapagar =cp.idcontapagar
                            and ci.tipoobjetoorigem = 'nf'
                            and n.idnf= ci.idobjetoorigem
                            and n.idcartao = ".$idcartao.") ";  
        $clausulai.=" exists( select 1 from nf n  where  cp.tipoobjetoorigem = 'nf'
                            and n.idnf= cp.idobjetoorigem
                            and n.idcartao = ".$idcartao.") and ";
    }
*/
    //echo( $clausulad);
  
	
		if(!empty($idagencia)){
			$andagencia = " and cp.idagencia = ".$idagencia." ";
			
		}else{
			$andagencia = " ";
		}
/*	
		if($tipo =='C'){
			
				$sql = "select cp.tipoobjeto ,cp.idcontapagar as id,cp.idobjeto,cp.obs,cp.valor,datareceb,dma(cp.datareceb) as dmadatareceb,cp.status,cp.tipo as tipo, cp.idcontadesc ,cp.idcontaitem, cp.obs,cp.parcelas,cp.parcela,a.agencia,cp.tipoespecifico,p.nome,cp.idpessoa
							from  agencia a,contapagar cp  
							left join contadesc cd on ( cp.idcontadesc = cd.idcontadesc)
							".$cpessoa."	
                            " . $clausulad."  
							and a.idagencia = cp.idagencia
							and cp.tipo = 'C'	
							".getidempresa('cp.idempresa','contapagar')."	
							".$andagencia."
							order by datareceb asc,status desc,id asc";
	
		}elseif($tipo =='D'){
	
				$sql = "select cp.tipoobjeto ,cp.idcontapagar as id,cp.idobjeto,cp.obs,cp.valor,datareceb,dma(cp.datareceb) as dmadatareceb,cp.status,cp.tipo as tipo, cp.idcontadesc ,cp.idcontaitem, cp.obs,cp.parcelas,cp.parcela,a.agencia,cp.tipoespecifico,p.nome,cp.idpessoa
						from agencia a,contapagar cp  
						left join contadesc cd on ( cp.idcontadesc = cd.idcontadesc)
						".$cpessoa."
						" . $clausulad."
						and a.idagencia = cp.idagencia
						and cp.tipo = 'D'	
						".getidempresa('cp.idempresa','contapagar')."
						".$andagencia."				 
						order by datareceb asc, status desc, id asc";
					
		}else{
            
        */
        
        if($buscaitens=='Y'){
            $sqlit=" union all
                        SELECT cp.tipoobjetoorigem as tipoobjeto ,cp.idcontapagar as id,cp.idobjetoorigem as idobjeto,'' as obs,cp.valor,pg.datareceb as datareceb,dma(pg.datareceb) as dmadatareceb,cp.status,pg.tipo as tipo, '' as idcontadesc ,cp.idcontaitem,'' as parcelas,'' as parcela,a.agencia,'CONTA ITEM' as tipoespecifico,p.nome,cp.idpessoa,f.descricao,pg.ndocumento
                        FROM agencia a,contapagaritem cp ".$joincp." left join formapagamento f on(cp.idformapagamento=f.idformapagamento)				
                         ".$cpessoa."
                        where " .$clausulai." 
			
                        a.idagencia = cp.idagencia 
                        ".getidempresa('cp.idempresa','contas')."
                         ".$andagencia."";
        }else{
            $sqlit="";
        }
        
				$sql = "select * from (
                                        SELECT cp.tipoobjeto ,cp.idcontapagar as id,cp.idobjeto,cp.obs,cp.valor,datareceb,dma(datareceb) as dmadatareceb,cp.status,cp.tipo as tipo, cp.idcontadesc ,cp.idcontaitem,cp.parcelas,cp.parcela,a.agencia,cp.tipoespecifico,p.nome,cp.idpessoa,f.descricao,cp.ndocumento
                                            FROM agencia a,contapagar cp ".$joincontaitem."
                                            left join contadesc cd on ( cp.idcontadesc = cd.idcontadesc)
                                            left join formapagamento f on ( cp.idformapagamento = f.idformapagamento)
                                             ".$cpessoa."
                                             " . $clausulad ."
                                            and a.idagencia = cp.idagencia 
                                            and cp.tipoespecifico='NORMAL'
                                            ".getidempresa('cp.idempresa','contapagar')."
                                            ".$andagencia." 
                                            ".$sqlit."
                                         
                                        ) as u
                       order by u.datareceb asc,u.status desc,u.id asc ";
     
                    $sqlg = "select 
                                    tipoobjeto,
                                    id,
                                   idobjeto,
                                   obs,
                                   sum(valor) as valor,
                                   datareceb,
                                   dmadatareceb,
                                   status,
                                    tipo,
                                   idcontadesc,
                                   idcontaitem,
                                   parcelas,
                                   parcela,
                                   agencia,
                                   tipoespecifico,
                                   nome,
                                   idpessoa,
                                   descricao,
                                   ndocumento
                            from (
                                        SELECT cp.tipoobjeto ,cp.idcontapagar as id,cp.idobjeto,cp.obs,cp.valor,datareceb,dma(datareceb) as dmadatareceb,cp.status,cp.tipo as tipo, cp.idcontadesc ,cp.idcontaitem,cp.parcelas,cp.parcela,a.agencia,cp.tipoespecifico,p.nome,cp.idpessoa,f.descricao,cp.ndocumento
                                            FROM agencia a,contapagar cp ".$joincontaitem."
                                            left join contadesc cd on ( cp.idcontadesc = cd.idcontadesc)
                                            left join formapagamento f on ( cp.idformapagamento = f.idformapagamento)
                                             ".$cpessoa."
                                             " . $clausulad ."
                                            and a.idagencia = cp.idagencia 
                                            and cp.tipoespecifico='NORMAL'
                                            ".getidempresa('cp.idempresa','contapagar')."
                                            ".$andagencia." 
                                            ".$sqlit."
                                         
                                        ) as u group by u.idpessoa
                       order by u.nome asc,u.status desc,u.id asc ";

					   echo '<!-- '.$sqlg.' -->';
//die($sql);
	if (!empty($sql)){
		$res = d::b()->query($sql) or die("Falha ao pesquisar NF : " . mysqli_error(d::b()) . "<p>SQL: $sql");
		$ires = mysqli_num_rows($res);
		$somatotais = 0;
		$vlrcredito = 0;
		$vlrdebito = 0;
		$qtdcred = 0;
		$qtddeb = 0;
		$parc='';
	}
}
?>

<html>
<head>
<title>Contas à Pagar</title>
<link href="../css/rep.css" media="all" rel="stylesheet" type="text/css" />
<style>
    .tddescricao{
	width: 700px;
    }
    html{
	font-family: Arial, FreeSans, Sans,  Serif, SansSerif;
	font-size: 11px;
	margin:0px;
	padding:0px;
}

body{
	margin:0px;
	padding:0px;
}
.tbrepheader{
	border: 0px;
	width: 100%;
}
.tbrepheader .header{
	font-size: 13px;
	font-weight: bold;
}

.tbrepheader .subheader{
	font-size: 10px;
	color: gray;
}
.tbrepheader .titulo{
	font-size: 18px;
	font-weight: bold;
}
.tbrepheader .res{
	font-size: 18px;
}
.normal{
	border: 1px solid silver;
	border-collapse: collapse;	
}

.normal td{
	border: 1px solid silver;
	padding: 0px 3px 0px 3px;
}

.normal .header{
	font-size: 10px;
	font-weight: bold;
	color: rgb(75,75,75);
	background-color: rgb(222,222,222);
}
.normal .res{
	font-size: 11px;
}
.normal .res .link{
	background-color:#FFFFFF;
	cursor:pointer;
}
.normal .res .tot{
	background-color:#E8E8E8;
	font-weight: bold;	
	text-align: center;
}
.normal .res .inv{
	border: 0px;
}
.normal .tdcounter{
	border:1px dotted rgb(222,222,222);
	background-color:white;
	color:silver;
	font-size:8px;
}
.newreppage{
	page-break-before: always;
}
.fldsheader{
	border:none;
	border-top: 2px solid silver;
	height: 0px;
	margin: 0px;
	padding: 0px;
	padding-bottom: 5px;
	padding-left:5px;
}
.fldsheader legend{
	font-size: 8px;
	color: gray;
	background-color: white;
}
.fldsfooter{
	border:none;
	border-top: 2px solid silver;
	height: 0px;
	margin: 0px;
	padding: 0px;
	margin-top: 5px;
	padding-left:5px;
}
.fldsfooter legend{
	font-size: 8px;
	color: gray;
	background-color: white;
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
a.btbr20{
	display: block;
}

</style>

</head>
<body>
<?
if($_GET and !empty($clausulad)){

?>
	<div class="panel-heading">Relatório de Contas</div>
	<table class="normal" style="font-size: 10px;">
	<tr class="header">		
            <td align="center">Emissão</td>
            <td align="center">Nota</td>
            <td align="center">Pessoa</td>
            <td align="center">Valor</td>
            <td align="center">Vencimento</td>
            <td align="center">Agência</td>
	</tr>
	<?
	while ($row = mysqli_fetch_array($res)){
		 $descnf="";
		$cortr = "";
                 $pessoa='';
		if($row["tipo"]== 'C'){
			/*
		 	* $stredit hABILITA O BOTAO PARA EDICAO CONFORME A ORIGEM DO REGISTRO
		 	*/
			if($row["origem"]== "notafiscal"){
				$stredit = "janelamodal('?_modulo=contasreceber&_acao=u&controle=".$row["id"]."');";
				$parc = ' / '.$row["parcela"];
			}else{				
				$stredit = "janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=".$row["id"]."');";
				$parc = '';
			}
			$vlrcredito = $vlrcredito + $row["valor"];
			$qtdcred = $qtdcred + 1;
			$cortr = "#c8d0ff";//azul
			
		}elseif($row["tipo"]=="D"){
			$stredit = "janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=".$row["id"]."');";
           
    			$vlrdebito = $vlrdebito + $row["valor"];
            		$qtddeb = $qtddeb + 1;
           
			$parc = '';
			if($row["status"]=="PENDENTE"){
			    $cortr = "#F9FF52";//Amarelo
			}elseif($row["status"]=="ABERTO"){
			    $cortr = "#69b769";//laranja
			}else{
			    $cortr = "#f0bfbf";//red + fraco
			}
			
			
		}else{
			$stredit = "alert('Erro: Campo ORIGEM nao informado!');";
		}
		if($row["tipoespecifico"]!="NORMAL" and $row["tipoespecifico"] !='CONTA ITEM'){
		    $img = "fa-google";
		    //verificar se os items da contapagar possuem valor diferente da contapagar
		    $sqlci="select sum(valor) as valori from contapagaritem where idcontapagar=".$row['id'];
		    $resci=d::b()->query($sqlci) or die("Erro ao buscar valor da contapagaritem sql=".$sqlci);
		    $rowci= mysqli_fetch_assoc($resci);
		    if(tratanumero($row['valor'])!=$rowci['valori']){
			$dif=$rowci['valori']- tratanumero($_1_u_contapagar_valor);

			$strdif = "<i class='fa fa-exclamation-triangle fa-1x laranja pointer' title='Valor dos itens difere da parcela!!!'></i>";

		    }else{$strdif="";}
		}else{
		    $img="fa-bars";
		    $strdif="";
		}
               
                if($row["tipoobjeto"] == "nf" and !empty($row["idobjeto"] )){
                    
                   
		    $sqlf = "select n.idnf,p.nome,n.tiponf,n.controle,n.nnfe,n.dtemissao as emissao from nf n,pessoa p where p.idpessoa = n.idpessoa  and idnf =".$row["idobjeto"];
		    $qrf = d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:".mysqli_error(d::b()));
		    $qtdrowsf= mysqli_num_rows($qrf);
		    $resf = mysqli_fetch_assoc($qrf);
		    if($resf["tiponf"]=="C"){
			$tiponf="Entrada";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=="V"){
			$tiponf="Saída";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }			
		    if($resf["tiponf"]=='S'){ 
			$tiponf="Serviço";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=='T'){ 
			$tiponf="Cte";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=='E'){ 
			$tiponf="Consessionária";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }
		    if($resf["tiponf"]=='M'){ 
			$tiponf="Manual/Cupom";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
			}
			if($resf["tiponf"]=='B'){ 
			$tiponf="Recibo";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
			}
		    if($resf["tiponf"]=='F'){ 
			$tiponf="Fatura";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }	
			if($resf["tiponf"]=='R'){ 
			$tiponf="PJ";
			$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
		    }	
			if($resf["tiponf"]=='O'){ 
				$tiponf="Outros";
				$descnf = "NF ". $tiponf." - ".$resf["nnfe"];
				}	
		    $pessoa = $resf["nome"];
                    $emissao=$resf['emissao'];
			
		}elseif($row["tipoobjeto"] == "notafiscal" and !empty($row["idobjeto"] )){
			
		    $sqlf = "select p.nome,n.numerorps,n.nnfe,n.idnotafiscal,n.emissao from notafiscal n,pessoa p where  p.idpessoa = n.idpessoa and idnotafiscal =".$row["idobjeto"];
		    $qrf = d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:".mysqli_error(d::b()));
		    $qtdrowsf= mysqli_num_rows($qrf);
		    $resf = mysqli_fetch_assoc($qrf);
		    $tiponf="Saída";	
		    $pessoa = $resf["nome"];
		    $descnf = "NFS-e - ".$resf["nnfe"];
                    $emissao=$resf['emissao'];
			
		}else{	
		   if(!empty($row["idformapagamento"])){
			$sqlff = "select c.descricao from formapagamento c  where c.idformapagamento =".$row["idformapagamento"];			
			$qrff = d::b()->query($sqlff) or die("Erro ao buscar descrição da formapagamento:".mysqli_error(d::b()));			
			$resff = mysqli_fetch_assoc($qrff);			
                        $descnf=$row["ndocumento"];
                        $pessoa= $resff["descricao"];
                        $janelanf = "janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=".$row["idcontapagar"]."');";
                         $emissao='';
                    }else{
                        $descnf=$row["ndocumento"];
                        $pessoa="";
                        $janelanf ="";
                        $emissao='';
                    }	    
		  
		}
		?>
	    <tr class="res" >
			
		
                <td><?=dma($emissao)?></td>
		<td nowrap align=""><?=$descnf?></td>
		
		<?
		$intemext="";
			
		if(!empty($row["idcontaitem"])){
			$sqlf2 = "select c.contaitem from contaitem c  where c.idcontaitem =".$row["idcontaitem"];			
			$qrf2 = d::b()->query($sqlf2) or die("Erro ao buscar descrição item da nota:".mysqli_error(d::b()));			
			$resf2 = mysqli_fetch_assoc($qrf2);
			$intemext = $resf2["contaitem"];
		}else{
		    $intemext="";
		}
		    
		
		if($row["idpessoa"]){
		    $pessoa =traduzid("pessoa","idpessoa","nome",$row["idpessoa"]);
		}elseif($row["idcontadesc"]){
		    $pessoa =traduzid("contadesc","idcontadesc","contadesc",$row["idcontadesc"]);
		}	
		
		
		//echo "<br>descricao :".$row["descricao"]."iditem:".$iditem;
		?>
		
		
		<td nowrap ><?=$pessoa?></td>
               

		<td nowrap align="right">
                    <?=number_format(tratanumero($row["valor"]), 2, ',', '.');?>
                </td>
                <td nowrap><?=$row["dmadatareceb"] ?></td>
		
		<td nowrap align="center"><?=$row["agencia"]?></td>
                
		
	</tr>
	<?
	}//while ($row = mysqli_fetch_array($res)){
	$somatotais = $vlrcredito + $vlrdebito;
	$cortrfim = "";	
	if($somatotais >= 0){
		$cortrfim = "c8d0ff";
	}else{
		$cortrfim= "ffc8c8";
	}
	
	?>
	</table>

	<br>
	<table class="normal" >
	<tr class="header">
		<td>#</td>
		<td>Tipo Lançamento</td>
		<td>Valores</td>
	</tr>
	<tr  class="res" >
		<td align="right"><?=$qtdcred ?></td>
		<td align="left">CRÉDITO</td>
		<td align="left"><?=number_format(tratanumero($vlrcredito), 2, ',', '.');?></td>
	</tr>
	<tr  class="res">
		<td align="right"><?=$qtddeb ?></td>
		<td align="left">DÉBITO</td>
		<td align="left"><?=number_format(tratanumero($vlrdebito), 2, ',', '.');?></td>
	</tr>
	<tr  class="res">
		<td align="right"><?=$qtdcred + $qtddeb ?></td>
		<td align="left">SOMA VALORES</td>
		<td align="left"><?=number_format(tratanumero($somatotais), 2, ',', '.');?></td>
	</tr>
	</table>
</div>
	<?
        $resg = d::b()->query($sqlg) or die("Falha ao pesquisar contas agrupadas : " . mysqli_error(d::b()) . "<p>SQL: $sqlg");
?>
<div class="row print" style="page-break-before: always;">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Contas por fornecedor</div>
        <div class="panel-body">
	<table class="normal" style="width: 100%;">
	<tr class="header">
            <td>Nome</td>
            <td>Valor</td>
	</tr>
<?
        $gvalor=0;
        while($rowg=mysqli_fetch_assoc($resg)){
            $gvalor=$gvalor+$rowg['valor'];
?>
        <tr class="res">
            <td><?=$rowg['nome']?></td>
            <td align="right"><?=number_format(tratanumero($rowg['valor']), 2, ',', '.');?> </td>
	</tr>      
<?        
        }
?>       
        <tr class="header">
            <td></td>
            <td align="right"><?=number_format(tratanumero($gvalor), 2, ',', '.');?></td>
        </tr>
        </table>
        </div>
    </div>
    </div>
</div>
  
<?
		}//if($_GET){
		?>
</body>
<script>

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
</html>