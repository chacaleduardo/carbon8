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
@media screen{
    .print{
            display: none !important;
    }
    

}

@media print {
    .screen{
        display: none !important;
    }
    
tr:nth-child(even) {background-color: #f2f2f2 !important;}

}
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

$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$valor_1		= trim($_GET["valor_1"]);
$valor_2		= trim($_GET["valor_2"]);
$itemconta 		= trim($_GET["itemconta"]);
$idcontadesc		= $_GET["idcontadesc"];
$drops			= false;
$controle		= $_GET["controle"];
$tipo			= $_GET["tipo"];
$statuspgto		= $_GET["statuspgto"];
$idagencia = $_GET["idagencia"];
$previsao = $_GET["previsao"];
//$contadesc = $_GET["contadesc"];
$obs= $_GET["obs"];
$visivel=$_GET["visivel"];
$nome=$_GET["nome"];
$idformapagamento=$_GET["idformapagamento"];
$idcartao=$_GET["idcartao"];
if(empty($_GET["idempresa"])){
	$idempresa = cb::idempresa();
} else {
	$idempresa = $_GET["idempresa"];
}

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
    $clausulad .="      and exists (select 1 from contapagaritem i where i.idcontapagar=cp.idcontapagar and i.tipoobjetoorigem in ('nf','notafiscal') )
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
						and cp.status !='INATIVO'
						and cp.idempresa=".$idempresa."
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
											and cp.status !='INATIVO'
                                            and cp.tipoespecifico='NORMAL'
											and cp.idempresa=".$idempresa."
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
											and cp.status !='INATIVO'
                                            and cp.tipoespecifico='NORMAL'
											and cp.idempresa=".$idempresa."
                                            ".$andagencia." 
                                            ".$sqlit."
                                         
                                        ) as u group by u.idpessoa
                       order by u.nome asc,u.status desc,u.id asc ";

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

<script >

var reloadpage = true;//Utilizado para informar à req.xml para efetuar refresh APÓS a respota
var xmlonreadystate = "xmldocU=xmldoc.toUpperCase();if(xmldocU.indexOf('ERR')>0){alert(xmldoc);}";
/*
 * Funcao para preencher automaticamente valores de campos "gemeos" ex: data_1 e data_2
 */
function fill_2(inobj){

	//Confirma se o objeto possui a identificacao correta (nomecampo_1) para gemeos
	if(inobj.id.indexOf("_2") > -1) {
		
		var strnome_1 = inobj.id.replace("_2","_1");
		var obj_1 = document.getElementById(strnome_1);

		if(inobj != null && inobj.value == ""){
			inobj.value = obj_1.value;
			inobj.select();
		}
		//if(inobj.value != "" and inobj.value != undefined){
			
		//}
	}

}

</script>
<style>
    .tddescricao{
	width: 700px;
    }
</style>
<div class="row screen">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
	    <?    	echo "<!--";
	echo $sql;
	echo "-->";
	?>
	<table>
            <!--
	<tr>
		<td class="rotulo">Conta</td>
		<td></td>
		<td><input type="text" name="controle" vpar="" id="controle"
			value="<?=$controle?>" autocomplete="off" class="input10"></td>	
	</tr>
            -->
	<tr>
		<td class="rotulo">Empresa:</td>
		<td></td>
		<td>
			<select name="_empresa" onchange="selecionarAgencia(this)">
				<?
				fillselect("SELECT idempresa, nomefantasia 
							FROM empresa 
							WHERE status = 'ATIVO' ".getidempresa("idempresa",$_GET["_modulo"]), $idempresa);
				?>				
			</select>
		</td>
	</tr>
	<tr>
	    <td class="rotulo">Pessoa:</td>
	    <td></td>
	    <td><input type="text" name="nome" vpar="" id="nome"
		    value="<?=$nome?>" autocomplete="off" class="input10"></td>	
	</tr>
        <?if($flgdiretor>0){
            $selectitem="select idcontaitem, contaitem from contaitem where idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]." and status='ATIVO' order by contaitem";
        ?>
        <?}else{
             $selectitem="select idcontaitem, contaitem from contaitem where idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]." and status='ATIVO' AND visualizarext='Y' order by contaitem";            
        }?>
	<tr>
		<td class="rotulo">Categoria:</td>
		<td></td>
		<td><select name="itemconta"><option value='' ></option>
		<?
		fillselect($selectitem,$itemconta);
		?>

		</select></td>
	</tr>
       
	<!--
    <tr>
		<td class="rotulo">Descrição</td>
		<td></td>
		<td><input type="text" name="contadesc" vpar="" id="contadesc"
			value="<?=$contadesc?>" autocomplete="off" class="input10"></td>	
	</tr>
 -->
	<tr>
		<td class="rotulo">Observação:</td>
		<td></td>
		<td><input type="text" name="obs" vpar="" id="obs"
			value="<?=$obs?>" autocomplete="off" class="input10"></td>	
	</tr>
	<tr>
		<td class="rotulo">Período</td>
		<td><font class="9graybold">entre:</font></td>
		<td><input class="calendario" name="vencimento_1" vpar="" id="vencimento_1"
			value="<?=$vencimento_1?>" autocomplete="off" type="text"
			class="input10" onchange="this.focus()"></td>
		<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
		<td><input class="calendario" name="vencimento_2" vpar="" id="vencimento_2"
			value="<?=$vencimento_2?>" autocomplete="off" type="text"
			class="input10" onfocus="fill_2(this)"> </td>
	</tr>
	<tr>
		<td class="rotulo">Valor</td>
		<td><font class="9graybold">entre:</font></td>
		<td><input name="valor_1" vpar="" value="<?=$valor_1?>"
			autocomplete="off" type="text" class="input10"></td>
		<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
		<td><input name="valor_2" vpar="" value="<?=$valor_2?>"
			autocomplete="off" type="text" class="input10"></td>
	</tr>
        <?if($flgdiretor>0){?>
	<tr>
		<td class="rotulo">Tipo:</td>
		<td></td>
		<td><select name="tipo">
		<?
		fillselect("SELECT 'T','Todos' UNION SELECT 'C','Crédito' UNION SELECT 'D','Débito' ",$tipo);
		?>

		</select></td>
	</tr>
        <?}?>
	<tr>	 
	    <td >Pagamento:</td>
	    <td></td>
	    <td >
		<select id="formapgto" name="idformapagamento" >
			<option value=""></option>
			<?=getPagamentoFiltro($formapgto,$idempresa)?>
		</select>		
	    </td>
	</tr> 
<?
/*
	if(!empty($idcartao) or $formapgto =="C.CREDITO" ){
	    $displaycartao='display: block;';
	}else{
	    $displaycartao='display: none;';
	}
 
 */
?>
        <!--
	<tr>	 
	    <td align="right"><div id="lbcartao" style="<?=$displaycartao?>"> Cartão:</div></td> 
        <td></td>
	    <td>
		<div  id="cartao" style="<?=$displaycartao?>">		   
		<select id="idcartao" name="idcartao" >
		     <option value=""></option>
		    <?fillselect("select idcartao,cartao from cartao where status = 'ATIVO' order by cartao ",$idcartao);?>		
		</select>
		</div>
	    </td>
	</tr> 
        -->
	<tr>
		<td class="rotulo">Status:</td>
		<td></td>
		<td><select name="statuspgto">
		<?
		fillselect("SELECT '','Todos' UNION SELECT 'ABERTO','Aberto' UNION SELECT 'FECHADO','Fechado' UNION SELECT 'PENDENTE','Pendente' UNION SELECT 'AG QUITACAO','Ag. Quitação' UNION SELECT 'QUITADO','Quitado'",$statuspgto);
		?>

		</select></td>
	</tr>
	<tr>
		<td class="rotulo">Agência:</td>
		<td></td> 
		<td>
		    <select name="idagencia"  id="idagencia" > 
				<option value="">Todas</option>
				<?=getAgencia($idagencia, $idempresa)?>                       
		    </select>
		</td>
	</tr>
        <tr>
		<td class="rotulo">Previsão:</td>
		<td></td> 
		<td>

                   <?//if($flgdiretor>0){?>
		    <select name="previsao"  id="previsao" > 
                         <option value=""></option>
			<?fillselect("select 'N','Não'",$previsao);?>
                       
		    </select>
                </td>
	</tr>
	</table>
	<div class="row"> 
	    <div class="col-md-8">
		
	    </div>
	    <div class="col-md-2">
		<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
		    <span class="fa fa-search"></span>
		</button> 
	 <?
	 if($_GET and !empty($clausulad)){
	 ?> 
              <i class="fa fa-print fa-2x  fade pointer hoverazul  btn-lg pointer" onclick="imprimir()" title="Imprimir"></i>
			  <?
			  $href='report/recontapagar.php?controle='.$_GET["controle"].'&nome='.$_GET["nome"].'&emissao_1='.$_GET["emissao_1"].'&emissao_2='.$_GET["emissao_2"].'&vencimento_1='.$_GET["vencimento_1"];
			  $href .= '&idformapagamento='.$_GET["idformapagamento"].'&itemconta='.$_GET["itemconta"].'&visivel='.$_GET["visivel"].'&idcontadesc='.$_GET["idcontadesc"];
			  $href .= '&tipo='.$_GET["tipo"].'&idagencia='.$_GET["idagencia"].'&contadesc='.$_GET["contadesc"].'&obs='.$_GET["obs"].'&previsao='.$_GET["previsao"];
			  $href .= '&vencimento_2='.$_GET["vencimento_2"].'&valor_1='.$_GET["valor_1"].'&valor_2='.$_GET["valor_2"].'&nnfe='.$_GET["nnfe"].'&statuspgto='.$_GET["statuspgto"].'&_idempresa='.$idempresa.'&reportexport=csv';
			  ?>
			  <a href="<?=$href?>" target="_blank">
					<i class="fa fa-file-excel-o verde fade pointer fa-2x hoververde"  title="Exportar .csv"></i>
				</a>
<?
	 }
?>				
            </div>
	</div>
            
    </div>
    </div>
    </div>
</div>

<?
if($_GET and !empty($clausulad)){

?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Relatório de Contas</div>
        <div class="panel-body">

	<table class="normal" style="font-size: 10px;">
	<tr class="header">
		<th class="screen"></th>
		<th  class="screen">Conta</th>
                <th>Emissão</th>
                <th>Nota</th>		
		<th class="screen">Tipo de Faturamento</th>
		<th>Pessoa</th>
                <th  class="screen">Pagamento</th>
		<th  class="screen">Obs</th>
		<th>Valor</th>
                <th>Vencimento</th>
		<th  class="screen">Tipo</th>
		<th>Agência</th>
                
		<th  class="screen">Status</th>
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
	    <tr class="respreto" style="background-color: <?=$cortr?>;">
			
		<td align="center" class="nowrap screen">
		    <?=$strdif?>
		    <a class="fa <?=$img?> fa-1x cinzaclaro hoverazul pointer" title="Editar" onclick="<?=$stredit?>"></a>
		</td> 
                <td class="screen"><?=$row["id"]?></td>
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
		
		<td nowrap align="" class="screen"><?=$intemext?></td>
		<td nowrap ><?=$pessoa?></td>
                <td nowrap class="screen"><?=$row["descricao"]?></td>
		<td  align="center" class="tddescricao screen">            
                <?=$row["obs"]?>
           <?/*if($row["tipoespecifico"] =='CONTA ITEM'){echo("CONTA ITEM");}*/?>
        </td>
		<td nowrap align="right">
                    <?=number_format(tratanumero($row["valor"]), 2, ',', '.');?>
                </td>
                <td nowrap><?=$row["dmadatareceb"] ?></td>
		<td nowrap align="center" class="screen"><?=$row["tipo"]?></td>
		<td nowrap align="center"><?=$row["agencia"]?></td>
                
		<td nowrap class="screen"><?=$row["status"]?></td>
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
	<table class="table table-striped planilha" >
	<tr >
		<th>#</th>
		<th>Tipo Lançamento</th>
		<th>Valores</th>
	</tr>
	<tr  style="background-color: #c8d0ff;">
		<td align="right"><?=$qtdcred ?></td>
		<td align="left">CRÉDITO</td>
		<td align="left"><?=number_format(tratanumero($vlrcredito), 2, ',', '.');?></td>
	</tr>
	<tr style="background-color: #ffc8c8;">
		<td align="right"><?=$qtddeb ?></td>
		<td align="left">DÉBITO</td>
		<td align="left"><?=number_format(tratanumero($vlrdebito), 2, ',', '.');?></td>
	</tr>
	<tr style="background-color: <?=$cortrfim?>;">
		<td align="right"><?=$qtdcred + $qtddeb ?></td>
		<td align="left">SOMA VALORES</td>
		<td align="left"><?=number_format(tratanumero($somatotais), 2, ',', '.');?></td>
	</tr>
	</table>
	</div>
    </div>
    </div>
</div>
	<?
        $resg = d::b()->query($sqlg) or die("Falha ao pesquisar contas agrupadas : " . mysqli_error(d::b()) . "<p>SQL: $sqlg");
?>
<div class="row print" style="page-break-before: always;">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Contas por fornecedor</div>
        <div class="panel-body">
	<table class="table table-striped planilha" style="width: 100%;">
	<tr>
            <th>Nome</th>
            <th>Valor</th>
	</tr>
<?
        $gvalor=0;
        while($rowg=mysqli_fetch_assoc($resg)){
            $gvalor=$gvalor+$rowg['valor'];
?>
        <tr>
            <td><?=$rowg['nome']?></td>
            <td align="right"><?=number_format(tratanumero($rowg['valor']), 2, ',', '.');?> </td>
	</tr>      
<?        
        }
?>       
        <tr>
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

<script>
    
$().ready(function() {
	$("#formapgto").change(function(){
	    if($("#formapgto").val()=="C.CREDITO"){
		$("#lbcartao").show();
	        $("#cartao").show();
	    } else{
                $("#idcartao option").prop("selected", false);
		$("#lbcartao").hide();
	        $("#cartao").hide();
	    }
	});
});

function pesquisar(){
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var valor_1 = $("[name=valor_1]").val();
    var valor_2 = $("[name=valor_2]").val(); 
    var statuspgto = $("[name=statuspgto]").val(); 
    var controle = $("[name=controle]").val();    
    var itemconta = $("[name=itemconta]").val();   
    var idcontadesc = $("[name=idcontadesc]").val();
    var tipo = $("[name=tipo]").val();    
    var idagencia = $("[name=idagencia]").val();
    var contadesc = $("[name=contadesc]").val();
    var obs = $("[name=obs]").val();
    var visivel = $("[name=visivel]").val();
    var nome = $("[name=nome]").val();
     var previsao = $("[name=previsao]").val();
    var idformapagamento = $("[name=idformapagamento]").val();
	var empresa = $("[name=_empresa]").val();
   // var idcartao = $("[name=idcartao]").val();
 
    var str="vencimento_1="+vencimento_1+"&idformapagamento="+idformapagamento+"&vencimento_2="+vencimento_2+"&idempresa="+empresa+"&nome="+nome+"&valor_1="+valor_1+"&valor_2="+valor_2+"&statuspgto="+statuspgto+"&itemconta="+itemconta+"&visivel="+visivel+"&idcontadesc="+idcontadesc+"&tipo="+tipo+"&idagencia="+idagencia+"&controle="+controle+"&contadesc="+contadesc+"&obs="+obs+"&previsao="+previsao;
    
    CB.go(str);
}

$(document).keypress(function(e) {
  if(e.which == 13) {
    pesquisar();
  }
});

function selecionarAgencia(valor)
{
	var empresa = $("[name=_empresa]").val();
	var str="idempresa="+empresa;
	CB.go(str);
}


function imprimir(){
    
    window.print();
  
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>