<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
    include_once("../inc/php/cbpost.php");
}
$emissao1=$_GET["emissao1"];
$emissao2=$_GET["emissao2"];
$idcliente=$_GET["idcliente"];



//Recupera a listagem de clientes
$arrCli=getClientes();
$jClientes=$JSON->encode($arrCli);

?>
<script>


</script>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Pesquisar </div>
        <div class="panel-body" >
            <div class="row">      
                <div class="col-md-2">Período:</div>
                <div class="col-md-4"> <input name="emissao1" class="calendario" size="10" style="width: 90px;"	value="<?=$emissao1?>"></div>
                <div class="col-md-2">e:</div>
                <div class="col-md-4"> <input name="emissao2" class="calendario" size="10" style="width: 90px;"	value="<?=$emissao2?>"></div>
		
	    </div>
          
            <div class="row">      
                <div class="col-md-2">Cliente:</div>
		<div class="col-md-10">
		    <input type="text" name="idcliente" vnulo cbvalue="<?=$idcliente?>" value="<?=$arrCli[$idcliente]["nome"]?>" vnulo>
		</div>
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
		$sclausula .= " and ( dtemissao  BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:00') ";
		$sclausulacom .= " and (criadoem BETWEEN '" . $dataini ." 00:00:00' and '" .$datafim ." 23:59:00') ";
	}else{
		die ("Datas n&atilde;o V&aacute;lidas!");
	}
}





if($_GET and !empty($idcliente)){
    
   

$sql="select * from  (	
		select  idnf as id,idnf as rotulo,'' as exercicio, 'pedido' as tipo,p.status 
		from nf p where p.idpessoa  = ".$idcliente." ".$sclausula." ".getidempresa('p.idempresa','nf')."
		union
		select idlote as id,partida as rotulo,l.exercicio as exercicio, 'lote' as tipo,l.status  
		from lote l where l.idpessoa = ".$idcliente." ".$sclausulacom." ".getidempresa('l.idempresa','lote')."
		union
		select  idamostra as id, idregistro as rotulo,a.exercicio as exercicio, 'amostra' as tipo,a.status  
		from amostra a where a.idunidade=6 and a.idpessoa = ".$idcliente." ".$sclausulacom." ".getidempresa('a.idempresa','amostra')."
	    ) as u order by tipo,id;";

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
                <th>ID</th>
		<th>Exercício</th>
		<th>Tipo</th>		
		<th>Status</th>		
            </tr>
            </thead>
            <tbody>
<?
       
        while($row=mysqli_fetch_assoc($res)){
	    
	    if($row['tipo']=='amostra'){
		$link="amostraautogenas&_acao=u&idamostra=".$row['id'];
	    }elseif($row['tipo']=='lote'){
		$link="formalizacao&_acao=u&idlote=".$row['id'];
	    }elseif($row['tipo']=='pedido'){
		$link="pedido&_acao=u&idnf=".$row['id'];
	    }
           
?>
            <tr> 
                <td>
                    <a  title="ID" class="pointer" onclick="janelamodal('?_modulo=<?=$link?>')">
                        <?=$row["rotulo"]?>
                    </a>
                </td> 
		<td><?=$row["exercicio"]?></td>
		<td>
		    <?=$row['tipo']?>
		</td>
		<td>		
		    <?=$row['status']?>
		</td>		
            </tr>
<?
        }// while($row=mysql_fetch_assoc($res)){ 
?>
            
	  
            </tbody>
            </table>
<?
  }else{//if($qtdrows>0){

    echo("Não foram encadas parcelas nestas condições.");
      
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
    
    
jClientes=<?=$jClientes?>;
jClientes = jQuery.map(jClientes, function(o, id) {
	return {"label": o.nome, value:id+"" ,"centrocusto":o.centrocusto}
});


$("[name*=idcliente]").autocomplete({
	source: jClientes
	,delay: 0
	,create: function(){
		$(this).data('ui-autocomplete')._renderItem = function (ul, item) {
			return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.codprodserv+"</span></a>").appendTo(ul);
		};
	}
	,select: function(event,ui ){
		$("[name*=idcliente]").val(ui.item.value);
	}
});

function pesquisar(){

    var emissao1 = $("[name=emissao1]").val();
    var emissao2 = $("[name=emissao2]").val();
    var idcliente = $("[name=idcliente]").val();

    var str="emissao1="+emissao1+"&emissao2="+emissao2+"&idcliente="+idcliente;
  
        CB.go(str);
}

function limpar(){
    var idremessa =$('#idremessa').val();
    CB.go("idremessa="+idremessa);
}

//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
</script>