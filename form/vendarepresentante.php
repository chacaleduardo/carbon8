<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
    include_once("../inc/php/cbpost.php");
}
$emissao1=$_GET["emissao1"];
$emissao2=$_GET["emissao2"];
$cliente=$_GET["cliente"];
$idrepresentante=$_GET["idrepresentante"];
$avescort=$_GET["avescort"];
$avespost=$_GET["avespost"];
$suinos=$_GET["suinos"];
$bovinos=$_GET["bovinos"];

?>

<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Pesquisar </div>
        <div class="panel-body" >
            <div class="row">      
                <div class="col-md-1">Perí­odo:</div>
                <div class="col-md-1"> <input name="emissao1" class="calendario" size="10" style="width: 90px;"	value="<?=$emissao1?>" autocomplete="off"></div>
                <div class="col-md-1 nowrap">e:&nbsp;&nbsp;&nbsp;&nbsp;<input name="emissao2" class="calendario" size="10" style="width: 90px;"	value="<?=$emissao2?>" autocomplete="off"></div>
		<div class="col-md-1"></div>
	
	    </div>           
          
            <div class="row">      
                <div class="col-md-1">Cliente:</div>
                <div class="col-md-5"> <input name="cliente"  size="10"	value="<?=$cliente?>"></div>
               
                <div class="col-md-1">Representante:</div>
		 <div class="col-md-4"> 
		<?if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15){
		    
			
			$sqlrep="select p.idpessoa,p.nome from pessoacontato c ,pessoa p
					where c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"]."
					and p.idpessoa = c.idpessoa
					and p.idtipopessoa = 12
					".getidempresa('p.idempresa','pessoa')."";
			$resrep=d::b()->query($sqlrep) or die("Erro ao buscar representante sql=".$sql);
			$rowrep=mysqli_fetch_assoc($resrep);
			
			$idrepresentante=$rowrep['idpessoa'];
			
			echo($rowrep['nome']);
			
		    //print_r( $_SESSION["SESSAO"]["NOME"]);
?>
		     <input name="idrepresentante"  size="10" type="hidden"	value="<?=$idrepresentante?>">
<?
		}else{
?>
               
		    <select  name="idrepresentante" id="idrepresentante"  >
			<option value=""></option>
                    <?fillselect("select idpessoa,nome from pessoa where idtipopessoa = 12 and status ='ATIVO' ".getidempresa('idempresa','pessoa')." order by nome",$idrepresentante);?>
                    </select>
<?
		}
?>
		</div>
            </div>

            <div class="row"> 
		<div class="col-md-8"></div>
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





if (!empty($emissao1) or !empty($emissao2)){
	$dataini = validadate($emissao1);
	$datafim = validadate($emissao2);

	if ($dataini and $datafim){
		$sclausula .= " and ( n.dtemissao  BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:59')";
		//$sclausulacom .= " and  n.dtemissao BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:59'";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}



if(!empty($cliente)){
    $clausulad .= " and (p.nome like   '%".$cliente."%')";
}



if(!empty($idrepresentante)){
   /*
	$clausularep=" join pessoacontato c on(c.idcontato =".$idrepresentante.")
                        join pessoacontato c2 on (c2.idcontato =c.idpessoa and c2.idpessoa = p.idpessoa)";
	*/
	  $clausularep=" join pessoacontato c2 on (c2.idcontato =".$idrepresentante." and c2.idpessoa = p.idpessoa)";
    
    //$clausularepcp=" and cc.idpessoa=".$idrepresentante." ";
}

if(empty($sclausula) and !empty($idrepresentante) ){
	echo("<br>Favor informar o Perí­odo.</br>");
}elseif(!empty($sclausula) and empty($idrepresentante)){
	echo("<br>Favor informar o Representante.</br>");
}

if($_GET and !empty($sclausula)and !empty($idrepresentante)){
$sql="SELECT 
    p.idpessoa,
    p.nome,
    IFNULL( SUM(total),0)  AS sumfat,  
   round((sum(n.total)*c2.participacaoprod)/100,2) as sumcom
FROM
    pessoa p  
    ".$clausularep."
    join nf n on (n.idpessoa = p.idpessoa)
WHERE
    p.idtipopessoa = 2
        AND p.status = 'ATIVO'
        ".$sclausula."
	    and  n.tiponf = 'V'
        and n.geracontapagar='Y'
		and n.comissao='Y'
		AND n.status not in ('CANCELADO','ORCAMENTO')
		".$clausulad."
            group by idpessoa
ORDER BY p.nome;
";

 $res=d::b()->query($sql) or die("Erro ao buscar Clientes sql=".$sql);
 $qtdrows=mysqli_num_rows($res);


?>  <!-- <?=$sql?>-->
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Resultado da pequisa<span id="cbResultadosInfo" numrows="<?=$qtdrows?>"> (<?=$qtdrows?> resultados encontrados)</span></div>
        <div class="panel-body">
<?
    if($qtdrows>0){
?>
            <table class="table table-striped planilha">
            <thead>
            <tr>             
                <th >Cliente</th>
				<th></th>
                <th style="text-align:right; ">Venda</th>
                <th style="text-align:right; ">Comissão</th>	
            </tr>
            </thead>
            <tbody>
<?
        $i=0;
	$arrvisita=array();
	$comissao=0;
	$fatacumulado=0;
        while($row=mysqli_fetch_assoc($res)){
            $i=$i+1;
	    $fatacumulado=$fatacumulado+floatval($row['sumfat']);
	    $comissao=$comissao+floatval($row['sumcom']);
		
		  $sqlv="select * from nf n where n.idpessoa =".$row['idpessoa']." ".$sclausula."  and  n.tiponf = 'V'
					and n.geracontapagar='Y'
					AND n.status not in ('CANCELADO','ORCAMENTO')
					and n.comissao='Y'";
	        $resv = d::b()->query($sqlv) or die("erro ao buscar visitas: " . mysqli_error(d::b()) . "<p>SQL: ".$sqlv);

            $y=0;	        
             

	    
?>
            <tr> 
                <td>
                    <a  title="cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?=$row['idpessoa']?>')">
                        <?=$row["nome"]?>
                    </a>
                </td>
				<td>
					<div class="oVisita">
						<a class="fa fa-search azul pointer hoverazul" title=" Ver Visitas" data-target="webuiPopover0" ></a>
					</div>
					<div class="webui-popover-content">
						<table>
<?
					while($r= mysqli_fetch_assoc($resv)) {		     
?>			    
						<tr>
							<td class="nowrap">	
								 <a class="fa azul pointer hoverazul" title="Nota fiscal" onclick="janelamodal('?_modulo=pedidorepresentante&_acao=u&idnf=<?=$r["idnf"]?>')">
								 <?=$r["total"]?> 
								 </a>
							</td>
						</tr>	    
<?
					}
		    ?>
						</table>
					</div>
				</td>
				<td align="right">
					<?=$row['sumfat']?>
				</td>
			
				<td align="right">
					<?=$row['sumcom']?>
				</td>
            </tr>
<?
        }// while($row=mysql_fetch_assoc($res)){ 
?>
        
	    <tr style="background: #e6e6e6; height: 40px;">
		<td >TOTAL:</td>
		<th></th>
		<td  align="right" style="text-align:right"><?=number_format($fatacumulado,2,'.','');?></td>
		<td  align="right" style="text-align:right"><?=number_format($comissao,2,'.','');?></td>
	    </tr>

            </tbody>
            </table>
<?
  }else{//if($qtdrows>0){

    echo("Não foram encadas parcelas nestas condiçàµes.");
      
  }//if($qtdrows>0){
  ?>
        </div>
    </div>
    </div>
</div>
<?
}//if($_GET and !empty($clausulad)){
?>
<script>
    
CB.preLoadUrl = function(){
	//Como o carregamento é via ajax, os popups ficavam aparecendo após o load
	$(".webui-popover").remove();
}

$(".oVisita").webuiPopover({
	trigger: "hover"
	,placement: "right"
	,delay: {
        show: 300,
        hide: 0
    }
});	
	
function alteracrm(vthis,idpessoa){    
    CB.post({
	 objetos: "_x_u_pessoa_idpessoa="+idpessoa+"&_x_u_pessoa_statuscrm="+$(vthis).val()
	 ,parcial: true
	 ,refresh: false
     });    
}
    
function pesquisar(){

    var emissao1 = $("[name=emissao1]").val();
    var emissao2 = $("[name=emissao2]").val();
    var cliente = $("[name=cliente]").val();
    var idrepresentante = $("[name=idrepresentante]").val();
    

    var str="emissao1="+emissao1+"&emissao2="+emissao2+"&cliente="+cliente+"&idrepresentante="+idrepresentante;
  
        CB.go(str);
}

function limpar(){
    var idremessa =$('#idremessa').val();
    CB.go("idremessa="+idremessa);
}

CB.preLoadUrl = function(){
	//Como o carregamento é via ajax, os popups ficavam aparecendo apà³s o load
	$(".webui-popover").remove();
}

$(".oVisita").webuiPopover({
	trigger: "hover"
	,placement: "right"
	,delay: {
        show: 300,
        hide: 0
    }
});

function altplantel(vthis,vcampo){  
    
    if($(vthis).attr(vcampo)=="N"){
        $(vthis).attr(vcampo,"Y");
        $(vthis).removeClass('fa-square-o');
        $(vthis).addClass('fa-check-square-o');
    }else{
        $(vthis).attr(vcampo,"N");
        $(vthis).removeClass('fa-check-square-o');
        $(vthis).addClass('fa-square-o');
    }
}
//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
</script>