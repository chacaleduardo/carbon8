<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}
################################################## Atribuindo o resultado do metodo GET
$vencimento_1 	= $_GET["vencimento_1"];
$vencimento_2 	= $_GET["vencimento_2"];
$idagencia 	= $_GET["idagencia"];
$pesquisa 	= $_GET["pesquisa"];
$status		= $_GET["status"];

$sql=" select * from pessoa where flgsocio='Y' and idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor=mysqli_num_rows($res);

?>
<script type="text/javascript" src="../inc/js/jscolor/jscolor.js"></script>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Filtros para Listagem </div>
        <div class="panel-body" >
	<table>
	    <tr>
		<td class="rotulo">Período</td>
		<td><font class="9graybold">entre</font></td>
		<td><input autocomplete="off" name="vencimento_1" vpar="" id="vencimento_1" class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_1?>" autocomplete="off"></td>
		<td><font class="9graybold">&nbsp;e&nbsp;</font></td>
		<td><input autocomplete="off" name="vencimento_2" vpar="" id="vencimento_2"class="calendario" size="10" style="width: 90px;" value="<?=$vencimento_2?>" autocomplete="off"></td>
	    </tr>
	    <tr>
		<td align="right">Agência:</td> 
		<td colspan="10">
		    <select name="idagencia"  id="idagencia" >

			<?fillselect("select idagencia,agencia from agencia where  status = 'ATIVO'   ".getidempresa('idempresa','contapagar')."
                                        UNION 
                                        select 'TODAS','Todas'",$idagencia);?>
		    </select>	
		</td>
	    </tr>
	    <tr>
		<td align="right">Pesquisa:</td>
		<td></td>
		<td><select name="pesquisa">
			<?
			$sql2 = " SELECT 'simples','Simples' UNION SELECT 'detalhe','Detalhada' UNION SELECT 'detalheitem','Detalhada Itens' ";
			fillselect($sql2,$pesquisa);
			?>
		</select></td>
	    </tr>
	    <tr> 
		<td class="rotulo">Status</td>
		<td></td>
		<td>
		    <select name="status">
		    <?
		    fillselect("select 'QUITADO','Quitado' union select 'PENDENTE','Pendente' union select 'TODOS','Todos'",$status);
		    ?>		    
		    </select>
		</td>
	    </tr>
	</table>	
	<div class="row"> 
	    <div class="col-md-8">
                <a class="fa hoverazul pointer" onclick="reldet();"><font style="color: blue;display:inline;cursor:pointer; text-decoration: underline;">Relatório Detalhado.</font></a>
	    </div>
	    <div class="col-md-2">
		<button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
		    <span class="fa fa-search"></span>
		</button> 
	    </div>	   
	</div>
        </div>
    </div>
    </div>
</div>
<?
/*
 * colocar condição para executar select
 */
if($_GET and !empty($vencimento_1) and !empty($vencimento_2)){

if (!empty($vencimento_1) or !empty($vencimento_2)){
    $dataini = validadate($vencimento_1);
    $datafim = validadate($vencimento_2);

    if ($dataini and $datafim){
	    $clausulac .= " and (datareceb  BETWEEN '" . $dataini ."' and '" .$datafim ."')"."  ";
    }else{
	    die ("Datas n&atilde;o V&aacute;lidas!");
    }
}

if(!empty($idagencia) and $idagencia!='TODAS'){
    
    $clausulac .= " and cp.idagencia=".$idagencia."  " ;
}
if(!empty($status) and $status!='TODOS'){
    $clausulac .= " and cp.status='".$status."'  " ;
}
if($flgdiretor<1){
  $viscontaitem=" and ci.visualizarext='Y' ";  
}else{
    $viscontaitem="";
}
//
/*	
	$sqlgrupo ="select ci.idcontaitem, ci.contaitem,sum(cp.valor) * -1 as somatotal,ci.cor,cp.status,ci.ordem,ci.somarelatorio,ci.previsao,cp.datareceb,cp.alteradoem
		        from contapagar cp,contaitem ci
		        where cp.tipo = 'D'		       
		        and cp.idcontaitem = ci.idcontaitem 			
			".$clausulac."
			group by ci.idcontaitem order by ci.ordem,ci.contaitem";
 */       
      
	$sqlgrupo ="select  u.idcontaitem, u.contaitem,sum(u.valor) * -1 as somatotal,u.cor,u.status,u.ordem,u.somarelatorio,u.previsao,u.datareceb,u.alteradoem 
                    from  (
                                    select  
                                            ci.idcontaitem, ci.contaitem,i.valor,ci.cor,cp.status,ci.ordem,ci.somarelatorio,ci.previsao,cp.datareceb,cp.alteradoem
                                    from  contapagar cp,contapagaritem i,contaitem ci
                                    where cp.tipoespecifico= 'AGRUPAMENTO'
                                    and ci.somarelatorio='Y'
                                    ".$viscontaitem."
                                    and cp.status!='INATIVO'
                                    and i.status!='INATIVO'
                                    and cp.tipo = 'D'	
                                     ".getidempresa('cp.idempresa','contapagar')."
                                    and cp.idcontapagar = i.idcontapagar
                                    and i.idcontaitem = ci.idcontaitem 
                                    ".$clausulac."
                            union all   
                                    select 
                                            ci.idcontaitem, ci.contaitem,cp.valor,ci.cor,cp.status,ci.ordem,ci.somarelatorio,ci.previsao,cp.datareceb,cp.alteradoem
                                    from contapagar cp,contaitem ci
                                    where cp.tipo = 'D'	
                                    and ci.somarelatorio='Y'
                                     ".$viscontaitem."
                                    and  cp.tipoespecifico!= 'AGRUPAMENTO'
                                     ".getidempresa('cp.idempresa','contapagar')."
                                    and cp.status!='INATIVO'
                                    and cp.idcontaitem = ci.idcontaitem 
                                    ".$clausulac."
                    ) as u
                            group by u.idcontaitem order by u.ordem,u.contaitem";
	echo "<!--";
	echo $sqlgrupo;
	echo "-->";
	if (!empty($sqlgrupo)){		

		$resgrupo =  d::b()->query($sqlgrupo) or die("Falha ao pesquisar grupo de contas: " . mysqli_error() . "<p>SQL: $sqlgrupo");
		$ires = mysqli_num_rows($resgrupo);
		$saldototal = 0;
	}
}
?>

<!-- Mostrar mensagem de Aguarde e bloquear tela  -->

<script >

</script>
<style>
    .backbranco{
	background-color:white !important;
    }
</style>

<?
if($_GET and $ires>0){
    if($pesquisa=='detalhe'){
	$collapse="";
        $collapseitem="collapse";
    }elseif($pesquisa=='detalheitem'){
        $collapse="";
        $collapseitem="";
    }else{
	$collapse="collapse";
        $collapseitem="collapse";
    }

	$vtotal=0;
	$id=0;
	while ($row = mysqli_fetch_assoc($resgrupo)){
	    $id=$id+1;
		if(!empty($row["idcontaitem"])){
			if($row["somarelatorio"]=="Y"){
				$vtotal=$vtotal+$row["somatotal"];
				$previsao=$previsao+$row["previsao"];
			}
?>

    <div class="panel panel-default" style="background-color: #<?=$row["cor"]?>;">
    <div class="panel-body">
	<table>
	    <tr class="draggable">
		<td>
		    <input name="_1_u_contaitem_cor" type="hidden" value="<?=$row["cor"]?>" size="6"
			class="color" style="cursor:pointer;" onchange="cor(this,<?=$row["idcontaitem"]?>);">
		</td>
		<td>
		    <?=$row["contaitem"]?> 
		</td>
		<td>
		    Previsão:
		</td>
		<td>
		    <input class="size6" name="_1_u_contaitem_previsao"  onchange="previsao(this,<?=$row["idcontaitem"]?>);"  type="text"  value="<?=$row['previsao']?>">
		</td>
		<td> Gasto:</td>
		<td ><label class="alert-warning"><?=$row["somatotal"]?></label></td>
		
		<td>
		    <?if($flgdiretor>0){                    
                    if($row["somarelatorio"]=="Y"){?>
			<i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="altflag(<?=$row["idcontaitem"]?>,'N');" title="Somar no final."></i>
		    <?}else{?>	
			<i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="altflag(<?=$row["idcontaitem"]?>,'Y');" title="Somar no final."></i>
		    <?}
                    }
                    ?>
		</td>
		<td><i class="fa fa-arrows-v cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#prodInfo<?=$id?>"></i></td>
		<td><i class="fa fa-arrows cinzaclaro hover move " title="Alterar item."></i></td>
	    </tr>
	</table>

    </div>
	<div class="panel-body">

	<div  class="<?=$collapse?>" id="prodInfo<?=$id?>">
	    <table class="table table-striped planilha">
	<?
	//if($pesquisa=="detalhe"){
		$sql="select 
			    u.idcontaitem, u.contaitem,u.idpessoa,u.nome,sum(u.valor) * -1 as somatotal,u.cor,u.status,u.ordem,u.somarelatorio,u.previsao,u.datareceb,u.alteradoem
			from(
			    select  
			    ci.idcontaitem, ci.contaitem,p.idpessoa,p.nome,i.valor,ci.cor,cp.status,ci.ordem,ci.somarelatorio,ci.previsao,cp.datareceb,cp.alteradoem
			    from  contapagar cp join contapagaritem i join contaitem ci join pessoa p
			    where cp.tipoespecifico= 'AGRUPAMENTO'
                            and cp.status!='INATIVO'
                            and i.status!='INATIVO'
			    and cp.tipo = 'D'	
			    and p.idpessoa =i.idpessoa
                            and ci.somarelatorio='Y'
                             ".$viscontaitem."
			    and cp.idcontapagar = i.idcontapagar
                             ".getidempresa('cp.idempresa','contapagar')."
			    and i.idcontaitem =  ".$row["idcontaitem"]."
			    and i.idcontaitem = ci.idcontaitem 
			    ".$clausulac."
				    union all
			    select ci.idcontaitem, ci.contaitem,p.idpessoa,p.nome,cp.valor,ci.cor,cp.status,ci.ordem,ci.somarelatorio,ci.previsao,cp.datareceb,cp.alteradoem
			    from contapagar cp join pessoa p join contaitem ci
			    where cp.tipo = 'D'	
                            and ci.somarelatorio='Y'
                             ".$viscontaitem."
                            and cp.tipoespecifico != 'AGRUPAMENTO'
			    and cp.idpessoa = p.idpessoa
                            and cp.status!='INATIVO'
			    and cp.idcontaitem = ci.idcontaitem 
                             ".getidempresa('cp.idempresa','contapagar')."
			    and cp.idcontaitem =  ".$row["idcontaitem"]."
			    ".$clausulac."
			    ) as u
			group by u.idcontaitem,u.idpessoa order by somatotal asc";	
	
		echo "<!--";
		echo $sql;
		echo "-->";
	
			
		$res =  d::b()->query($sql) or die("Falha ao pesquisar pessoas de contas: " . mysqli_error() . "<p>SQL: $sql");
	
		while ($row2 = mysqli_fetch_assoc($res)){
                     $id=$id+1;
	?>	
		
		    <tr>
				<td><b><?=$row2["nome"]?></b></td>
				<td align="right"><b><?=$row2["somatotal"]?></b></td>
                <td><i class="fa fa-arrows-v cinzaclaro pointer" title="Detalhar"  data-toggle="collapse" href="#prodInfo<?=$id?>"></i></td>
		    </tr>
		
	<?
                        $sql3="select 
                                    u.idcontapagar,u.idnf,u.nnfe,u.qtd,u.obs,u.parcela,u.parcelas,u.valor,u.total,round((u.total/u.parcelas),2) as totalparcela,u.idprodserv
									-- ,u.xdescr,u.xqtd,u.xvalor as xtotal,round((u.xvalor/u.parcelas),2) as xtotalparcela
                                from(
                                    select  
                                        cp.idcontapagar,ci.idcontaitem, ci.contaitem,p.idpessoa,p.nome,i.valor,ci.cor,cp.status,ci.ordem,ci.somarelatorio,ci.previsao,cp.datareceb,cp.alteradoem,
                                        n.idnf,n.nnfe,nf.qtd,nf.obs,cp.parcela,ifnull(n.parcelas,1) as parcelas ,nf.total,nf.idprodserv
										-- ,x.descr as xdescr,x.qtd as xqtd,x.valor as xvalor
                                    from  contapagar cp join contapagaritem i join contaitem ci join pessoa p join nf n 
									left join nfitem nf on (n.idnf = nf.idnf and nf.nfe='Y')
									-- left join nfitemxml x on (n.idnf=x.idnf)
                                    where cp.tipoespecifico= 'AGRUPAMENTO'
                                                and cp.status!='INATIVO'
                                                and i.status!='INATIVO'
                                                and cp.tipo = 'D'	
                                                and p.idpessoa =i.idpessoa
                                                and ci.somarelatorio='Y'
                                                 ".$viscontaitem."
                                                and cp.idcontapagar = i.idcontapagar
                                                and i.tipoobjetoorigem ='nf'
                                                and n.idnf = i.idobjetoorigem                                               
                                                and ci.idcontaitem =  ".$row2["idcontaitem"]."
                                                ".getidempresa('cp.idempresa','contapagar')."
                                                and i.idpessoa =".$row2["idpessoa"]."
                                                and i.idcontaitem = ci.idcontaitem 
                                                  ".$clausulac." 
                                    union all
                                    select 
                                        cp.idcontapagar,ci.idcontaitem, ci.contaitem,p.idpessoa,p.nome,cp.valor,ci.cor,cp.status,ci.ordem,ci.somarelatorio,ci.previsao,cp.datareceb,cp.alteradoem,
                                            n.idnf,n.nnfe,nf.qtd,nf.obs,cp.parcela,if(n.idnf>0,cp.parcelas,1) as parcelas,ifnull(nf.total,cp.valor) as total,nf.idprodserv
											-- ,x.descr as xdescr,x.qtd as xqtd,x.valor as xvalor
                                    from contapagar cp join pessoa p join contaitem ci 
										left join nf n on(cp.tipoobjeto = 'nf' and cp.idobjeto = n.idnf)
										left join nfitem nf on( n.idnf = nf.idnf and nf.nfe='Y')
										-- left join nfitemxml x on (n.idnf=x.idnf)
                                    where cp.tipo = 'D'	
                                                and ci.somarelatorio='Y'
                                                 ".$viscontaitem."
                                                and cp.tipoespecifico != 'AGRUPAMENTO'
                                                and cp.idpessoa = p.idpessoa
                                                and cp.status!='INATIVO'
                                                and cp.idcontaitem = ci.idcontaitem                                                 
                                                 ".getidempresa('cp.idempresa','contapagar')."
                                                and cp.idpessoa =".$row2["idpessoa"]."
                                                and ci.idcontaitem =  ".$row2["idcontaitem"]."
                                                  ".$clausulac."
                                    ) as u order by total desc,valor desc";

        		echo "<!--";
                        echo $sql3;
                        echo "-->";


                        $res3 =  d::b()->query($sql3) or die("Falha ao pesquisar itens das contas: " . mysqli_error() . "<p>SQL: $sql3");
                        $qtd3=mysqli_num_rows($res3);
                       
?>     
                    <tr class="<?=$collapseitem?>" id="prodInfo<?=$id?>">
                        <td colspan="2">
                           
                                <table >
                       
<?
                        while ($row3 = mysqli_fetch_assoc($res3)){
  ?>                            
                                <tr> 
									<th>NFe:</th>
                                    <td>
                                        <a class="pointer hoverazul" title="NFe" onclick="janelamodal('?_modulo=nfentrada&_acao=u&idnf=<?=$row3["idnf"]?>')"><?=$row3["nnfe"]?></a>
                                    </td>
									<th>Fatura:</th>
									<td>
                                        <a class="pointer hoverazul" title="Fatura" onclick="janelamodal('?_modulo=contapagar&_acao=u&idcontapagar=<?=$row3["idcontapagar"]?>')"><?=$row3["idcontapagar"]?></a>
                                    </td>
									<th>QTD:</th>
									<td align="right"><?if($row3["qtd"]){ echo $row3["qtd"];}else{ echo $row3["xqtd"];}?></td>
									<th>Item:</th>
                                    <td>
          <?                            
                                        if($row3["idprodserv"]>1){                                            
                                            $descr=traduzid('prodserv', 'idprodserv', 'descr', $row3["idprodserv"]);
                                            echo($descr);
										}elseif($row3["obs"]){
											echo($row3["obs"]);
										}else{
                                            echo($row3["xdescr"]);
                                        }
          ?>
                                    </td>
									<th>Valor:</th>
									<td align="right"><?if($row3["totalparcela"]){echo $row3["totalparcela"];}else{echo $row3["valor"];}?></td>
									<th>Parcela:</th>
									<td> <?=$row3["parcela"]?>-<?=$row3["parcelas"]?></td>
                                </tr>
<?
                        }//while ($row3 = mysqli_fetch_assoc($res3)){
?>                            

                                </table>
                           
                        </td>
                    </tr>
<?                        
        
		}//while ($row2 = mysqli_fetch_assoc($res)){
	?>
	    </table>
	</div>
	</div>
    </div>

<?
		}
	}//while ($row = mysqli_fetch_assoc($res)){

	?>
    <div class="panel panel-default" >
        <div class="panel-heading">Despesas de <label class="alert-warning"> <?=dma($dataini)?></label> á <label class="alert-warning"> <?=dma($datafim)?></label></div>
        <div class="panel-body">
	    <table class="table table-striped planilha">	
		    <tr style="height: 5px;"></tr>
		    <tr>
			    <td colspan='2'><font size="2">Previsão</font></td>
			    <td align="right"><font size="2"><?=number_format($previsao, 2, '.','');?></font></td>
		    </tr>	
		    <tr>
			    <td colspan='2'><font size="2">Total</font></td>
			    <td align="right"><font size="2"><?=number_format($vtotal, 2, '.','');?></font></td>
		    </tr>
	    </table>
	</div>
    </div>
 

<?
}//if($_GET){
?>

<script>
function previsao(vthis,vidcontaitem){
        CB.post({
        objetos: "_1_u_contaitem_idcontaitem="+vidcontaitem+"&_1_u_contaitem_previsao="+$(vthis).val()
	,parcial: true
	,refresh: false
	,msgSalvo: "Previsão alterada."
    }); 
}    
function cor(vthis,vidcontaitem){
        CB.post({
        objetos: "_1_u_contaitem_idcontaitem="+vidcontaitem+"&_1_u_contaitem_cor="+$(vthis).val()
	,parcial: true
	,refresh: true
	,msgSalvo: "cor alterada."
    }); 
}    
    
function pesquisar(){
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var idagencia = $("[name=idagencia]").val();
     var status = $("[name=status]").val();
    var pesquisa = $("[name=pesquisa]").val();
    var str="vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&idagencia="+idagencia+"&status="+status+"&pesquisa="+pesquisa;
    CB.go(str);
}

$(document).keypress(function(e) {
  if(e.which == 13) {
    pesquisar();
  }
});

function altflag(inid,inval){
	CB.post({
		objetos: "_x_u_contaitem_idcontaitem="+inid+"&_x_u_contaitem_somarelatorio="+inval
		,parcial: true		
	});
}

function ordenaItens(){
    $.each($("#tbItens tbody").find("tr"), function(i,otr){
        //Recupera objetos de update e de insert
        $(this).find(":input[name*=nfitem_ord],:input[name*=ord]").val(i);
    })
}

function reldet(){
    
    var vencimento_1 = $("[name=vencimento_1]").val();
    var vencimento_2 = $("[name=vencimento_2]").val();
    var idagencia = $("[name=idagencia]").val();
    var status = $("[name=status]").val();
    var pesquisa = $("[name=pesquisa]").val();
    
    var str="vencimento_1="+vencimento_1+"&vencimento_2="+vencimento_2+"&idagencia="+idagencia+"&status="+status+"&pesquisa="+pesquisa;
     janelamodal('report/findespesas.php?'+str+'');

}

$("#tbItens tbody").sortable({
    update: function(event, objUi){
        ordenaItens();
    }
});

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>