<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
    include_once("../inc/php/cbpost.php");
}

$idprodserv=$_GET["idprodserv"];
$cliente=$_GET["cliente"];
$idrepresentante=$_GET["idrepresentante"];
$campoplantel=$_GET["campoplantel"];
$dataSelecionada=$_GET["dataSelecionada"];
$exercicio=$_GET["exercicio"];



if(!empty($dataSelecionada)){
    $databusca = explode("-", $dataSelecionada);
    $dataini = validadate($databusca[0]);
    $datafim = validadate($databusca[1]);
    if ($dataini and $datafim){
        $dataini=$dataini;
        $datafim=$datafim;
    }else{
        $dataini='';
        $datafim='';
    }
}

function getRepresentante(){
    
    if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){
           
        $sqlrep="select p.idpessoa,p.nome from pessoacontato c ,pessoa p
                    where c.idcontato = ".$_SESSION["SESSAO"]["IDPESSOA"]."
                    and p.idpessoa = c.idpessoa
                   ".getidempresa('p.idempresa','pessoa')."
                    and p.idtipopessoa = 12";
    }else{
        $sqld="select * from divisao where status ='ATIVO' and idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"];
        $resd= d::b()->query($sqld) or die("getRepresentante: Falha ao buscar divisao de negocios: ".mysqli_error(d::b())."\n".$sqld);
        $qtresd=mysqli_num_rows($resd);
        while($rowd=mysqli_fetch_assoc($resd)){
     		$striddivgestor.=$virg.$rowd['inidplantel'];
			$virg=',';
		}
        
        if($qtresd>0){
            $str1=" and exists (select 1 from divisao d join divisaoplantel dp on(d.iddivisao=dp.iddivisao) 
                        join plantelobjeto pl on(   pl.idplantel = dp.idplantel) 
                        where d.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]."
                        and pl.idobjeto = p.idpessoa 
                        and  pl.tipoobjeto = 'pessoa' 
                    )";

            $str2=" and exists (select 1 from divisao d join divisaoplantel dp on(d.iddivisao=dp.iddivisao) 
                        join plantelobjeto pl on(   pl.idplantel = dp.idplantel) 
                        where
                        d.idpessoa = ".$_SESSION["SESSAO"]["IDPESSOA"]."
                        and pl.idobjeto = f.idpessoa 
                        and  pl.tipoobjeto = 'pessoa' 
                    )";
        }
            
        $sql="select * from pessoacontato c join pessoa p on(p.idpessoa=c.idpessoa  and p.idtipopessoa = 2) where c.idcontato=".$_SESSION["SESSAO"]["IDPESSOA"];
        $res= d::b()->query($sql) or die("getRepresentante: Falha ao buscar contatos: ".mysqli_error(d::b())."\n".$sql);
        $qtres=mysqli_num_rows($res);
        if($qtres>0 and $qtresd<1){
            $sqlrep="select idpessoa,nome from pessoa where idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"];     
        }else{    
            $sqlrep="select idpessoa,nome from (
                select p.idpessoa,p.nome 
                from pessoa p
                where  p.idtipopessoa = 12
                ".$str1."
                ".getidempresa('p.idempresa','pessoa')."
                union 
                select f.idpessoa,f.nome 
                from pessoa f 
                where f.idtipopessoa = 1
                ".$str2."
                ".getidempresa('f.idempresa','pessoa')."
                and f.status in ('ATIVO','PENDENTE')
                and exists (select 1 from pessoacontato c join pessoa p on(p.idpessoa=c.idpessoa and p.status='ATIVO' and p.idtipopessoa = 2)
                where c.idcontato = f.idpessoa)
            ) as u order by nome"; 
            
            
        }
        
    }

    $res = d::b()->query($sqlrep) or die("getRepresentante: Falha: ".mysqli_error(d::b())."\n".$sqlrep);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=$r["nome"];
    }
	return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrRep=getRepresentante();
//print_r($arrCli); die;
$jRep=$JSON->encode($arrRep);

function getagente(){
    $sql="select p.idprodserv,p.descr 
                    from prodserv p 
                    join  unidadeobjeto u on( u.idunidade = 9 and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
                    where p.tipo = 'PRODUTO'
                    and p.status = 'ATIVO' 
                    ".getidempresa('p.idempresa','pessoa')."
                    and p.especial='Y'
                    order by p.descr";
    $res = d::b()->query($sql) or die("getagente: Falha: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idprodserv"]]["descr"]=$r["descr"];
    }
    return $arrret;

}

$arrAg=getagente();
//print_r($arrCli); die;
$jAgentes=$JSON->encode($arrAg);

?>
<style>
a.tip:hover {
    cursor: hand;
    position: relative
}
a.tip span {
    display: none
}
a.tip:hover span {
    border: #c0c0c0 1px dotted;
    padding: 5px 20px 5px 5px;
    display: block;
    z-index: 100;
    background: #f0f0f0 no-repeat 100% 5%;
    left: 0px;
    margin: 10px;
    width: 200px;
    position: absolute;
    top: 10px;
    text-decoration: none
}
</style>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Pesquisar </div>
        <div class="panel-body" >
            <div class="row">   
                <div class="col-md-1">Cliente:</div>
                <div class="col-md-4"> <input name="cliente" class="size30"  value="<?=$cliente?>"></div>
                <div class="col-md-1">Plantel:</div>
                <div class="col-md-3"> 
                <select class="size10" name="campoplantel" id="campoplantel"  >
                       <option value=""></option>
                   <?fillselect("select idplantel,plantel 
                                   from plantel 
                                   where status='ATIVO' 
                                   and idempresa=".$_SESSION["SESSAO"]["IDEMPRESA"]."
                                   and prodserv='Y' order by plantel",$campoplantel);?>
                   </select>
                </div> 
                <?
                /*
                if(empty( $exercicio)){
                    $exercicio =  date("Y");
                } */              
                ?>
                <div class="col-md-1"></div>
                <div class="col-md-2"></div>
            </div>
           <? // if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){$idrepresentante=$_SESSION["SESSAO"]["IDPESSOA"]; $readonly="readonly='readonly'";}?>
           <?
           if($_SESSION["SESSAO"]["IDTIPOPESSOA"]==15 or $_SESSION["SESSAO"]["IDTIPOPESSOA"]==16){$obrigatoriorepresentante='Y';}else{$obrigatoriorepresentante='N';}?>
            
            <div class="row">
                <div class="col-md-1">Representante:</div>
                <div class="col-md-4"> 
                    <input <?=$readonly?>  type="text" name="idrepresentante"  id="idrepresentante" cbvalue="<?=$idrepresentante?>" value="<?=$arrRep[$idrepresentante]["nome"]?>" style="width: 30em;" >
		        </div>               

                <div class="col-md-1">Isolamento:</div>
                <div class="col-md-3">
                    <button id="btSelecaoData" class="btn btn-default">
                        <i class="fa fa-calendar"></i>
                        <?if(empty($dataSelecionada)){?>
                        <span id="dataSelecionada"><span class="cinza">Selecione a data</span></span>
                        <?}else{?>
                        <span id="dataSelecionada"><?=$dataSelecionada?></span>
                        <?}?>
                    </button>
                </div>
            </div>

            
            <div class="row">
                <div class="col-md-1">Semente:</div>
                <div class="col-md-4"> 
                    <input <?=$readonly?>  type="text" name="idprodserv"  id="idprodserv" cbvalue="<?=$idprodserv?>" value="<?=$arrAg[$idprodserv]["descr"]?>" style="width: 30em;" >
		        </div>
                <div class="col-md-1">Exercicio:</div>
                <div class="col-md-3">
                    <input name="exercicio" class="size8"  value="<?=$exercicio?>">
                </div>
            </div>
           
<script>
$("#btSelecaoData").daterangepicker({
    "autoUpdateInput": false,
    //"singleDatePicker": true,
    "showDropdowns": true,
    "linkedCalendars": false,
    "opens": "left",
    "locale": CB.jDateRangeLocale
}).on("apply.daterangepicker", function(e, picker) {
    $out = $("#dataSelecionada");
    //Exemplo:
    //Se $out for um elemento html, utilizar o metodo html().
    //Se for um input, utilizar val() conforme a necessidade
    //Outras opcoes: http://www.daterangepicker.com/
    let strIntervalo=picker.startDate.format(picker.locale.format) + "-" + picker.endDate.format(picker.locale.format);
    $out.html(strIntervalo);
   // alert("Data selecionada");
});
</script>
            <div class="row"> 
                <div class="col-md-8"></div>
                <div class="col-md-2">
                <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar()">
                    <span class="fa fa-search"></span>
                </button> 
                </div>	 
                <? $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
                    $full_url = $protocol."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"."&csv=1";
                    ?>
                <div class="col-md-2">
                    <button class="btn btn-default btn-dark">
                        <a href="<?=$full_url?>" title="Fazer download CSV">
                            <i class="fa fa-file-excel-o"> Exportar CSV</i>
                        </a>
                    </button>
                </div>
                 
            </div>
        </div>
    </div>
    </div>
</div>
<?


if(!empty($cliente)){
    $clausulad .= " and (ps.nome like   '%".$cliente."%')";
}
if(!empty($exercicio)){
    $clausulad .= " and l.exercicio =  '".$exercicio."'";
}
if(!empty($idprodserv)){
    $clausulad .= " and l.idprodserv =  ".$idprodserv."";
}
if(!empty($campoplantel)){
     $clausulad .= " and exists(select 1 from plantelobjeto o where o.idobjeto =ps.idpessoa and o.tipoobjeto ='pessoa' and o.idplantel = ".$campoplantel.") ";
}
if(!empty($dataini) and !empty($datafim)){
    $clausulad .= " and l.fabricacao between '".$dataini."' and '".$datafim."' ";
}

if(!empty($idrepresentante)){
   /*
	$clausularep=" join pessoacontato c on(c.idcontato =".$idrepresentante.")
                        join pessoacontato c2 on (c2.idcontato =c.idpessoa and c2.idpessoa = p.idpessoa)";
	*/
	  $clausularep=" join pessoacontato c2 on (c2.idcontato =".$idrepresentante." and c2.idpessoa = ps.idpessoa)";
    
    //$clausularepcp=" and cc.idpessoa=".$idrepresentante." ";
}

if($_GET and (!empty($idrepresentante) and $obrigatoriorepresentante=='Y') or (!empty($clausulad) and $obrigatoriorepresentante=='N')){

$sql="select l.idlote,a.idregistro,l.orgao,l.exercicio,l.partida,l.vencimento,l.tipificacao,l.observacao,p.descr,ps.nome,ps.idpessoa,p.idprodserv
        from lote l 
        join prodserv p on(p.idprodserv = l.idprodserv 
                            and p.especial ='Y' 
                            and  p.tipo = 'PRODUTO'
                            and p.status = 'ATIVO' )
        join  unidadeobjeto u on( u.idunidade = 9 and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
        join resultado r on(r.idresultado = l.idobjetosolipor)
        join amostra a on(a.idamostra=r.idamostra)
        LEFT JOIN subtipoamostra sta on(sta.idsubtipoamostra=a.idsubtipoamostra)
        join pessoa ps on(ps.idpessoa=a.idpessoa)
        ". $clausularep."
        where l.tipoobjetosolipor='resultado' ".$clausulad." order by p.descr,ps.nome";
//die($sql);
 $res=d::b()->query($sql) or die("Erro ao buscar Clientes sql=".$sql);
 $qtdrows=mysqli_num_rows($res);


?>  <!-- <?=$sql?>-->
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Resultado da pequisa<span id="cbResultadosInfo" numrows="<?=$qtdrows?>"> (<?=$qtdrows?> resultados encontrados)</span>
           </div>
        <div class="panel-body">
<?
    if($qtdrows>0){
?>

	        <table class="table table-striped planilha">
            <thead>
            <tr>   
                <th>Exercício</th>
                <th>Registro</th>
                <th>Produto</th>
                <th>Solicitado Por</th>                
                <th>Partida</th>
                <th>Vencimento</th>
                <th>Orgão</th>
                <th>Tipificação</th>
                <th>Observação</th>
            </tr>
            </thead>
            <tbody>
            
<?
        if (!empty($_REQUEST['csv'])){
		    $conteudoexport = "EXERCÍCIO;REGISTRO;PRODUTO;SOLICITADO POR;PARTIDA;VENCIMENTO;ORGÃO;TIPIFICAÇÃO;OBSERVAÇÃO;\n";
    	}
        while($row=mysqli_fetch_assoc($res)){
?>
            <tr>
                <td><?=$row['exercicio']?></td>
                <td><?=$row['idregistro']?></td>
                <td><?=$row['descr']?></td>
                <td><?=$row['nome']?></td>
                
                <td><?=$row['partida']?></td>
                <td><?=dma($row['vencimento'])?></td>
                <td><?=$row['orgao']?></td>
                <td><?=$row['tipificacao']?></td>
                <td><?=$row['observacao']?></td>
            </tr>
<?          
        if (!empty($_REQUEST['csv'])){
              $conteudoexport .= $row["exercicio"].";".$row["idregistro"].";".$row["descr"].";".$row['nome'].";".$row['partida'].";".dma($row['vencimento']).";".$row['orgao'].";". preg_replace('/[\n|\r|\n\r|\r\n]{2,}/',' ',$row['tipificacao']).";". preg_replace('/[\n|\r|\n\r|\r\n]{2,}/',' ',$row['observacao']).";\n";
        }  
        }// while($row=mysql_fetch_assoc($res)){ 
?>
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
}elseif(empty($idrepresentante) or empty($dataini) or empty($datafim)){
?>
<link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
	<br>
	<div class="row">
		<div class="col-md-12">
			<div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

			<strong><i class="glyphicon glyphicon-info-sign"></i> Para pesquisa favor preencher os campos.
			<br/>   <br/><li>Representante</li>			
                        <br/>
                        <br/>
                        <br/>
			</div>
		</div>
	</div>
<?
}
?>
<script>
    
jRep=<?=$jRep?>;// autocomplete cliente

//mapear autocomplete de clientes
jRep = jQuery.map(jRep, function(o, id) {
    return {"label": o.nome, value:id+""}
});

//autocomplete 
$("[name*=idrepresentante]").autocomplete({
    source: jRep
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});

jAgentes=<?=$jAgentes?>;// autocomplete cliente

//mapear autocomplete de clientes
jAgentes = jQuery.map(jAgentes, function(o, id) {
    return {"label": o.descr, value:id+""}
});

//autocomplete 
$("[name*=idprodserv]").autocomplete({
    source: jAgentes
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});
    

    
function pesquisar(){

    var cliente = $("[name=cliente]").val();
    var exercicio = $("[name=exercicio]").val();
    var campoplantel = $("[name=campoplantel]").val();
    var idrepresentante = $('#idrepresentante').attr('cbvalue');
    var idprodserv = $('#idprodserv').attr('cbvalue');
    var dataSelecionada=$("#dataSelecionada").html();
    
    var avescort = $("[avescort]").attr('avescort');;
    var avespost = $("[avespost]").attr('avespost');;
    var bovinos = $("[bovinos]").attr('bovinos');;
    var suinos = $("[suinos]").attr('suinos');;

    var str="cliente="+cliente+"&idprodserv="+idprodserv+"&idrepresentante="+idrepresentante+"&campoplantel="+campoplantel+"&dataSelecionada="+dataSelecionada+"&exercicio="+exercicio;
  
        CB.go(str);
}









$(document).ready(function(){
    $(".cancelBtn").click(function()
    {
      $("#dataSelecionada").html("<span class='cinza'>Selecione a data</span>");
    });
});
//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;
</script>
<?
if (!empty($_REQUEST['csv'])){
    ob_end_clean();
    /* Gerar o nome do arquivo para exportar
     * Substitui qualquer caractere estranho pelo sinal de '_'
     * Caracteres que NAO SERAO substituidos:
     *   - qualquer caractere de A a Z (maiusculos)
     *   - qualquer caracteres de a a z (minusculos)
     *   - qualquer caractere de 0 a 9
     *   - e pontos '.'
     */ 
    $infilename = 'sementes_'.date('dmY');
    //gera o csv
    header('Content-Encoding: UTF-8');
    header('Content-Type: text/csv; charset=utf-8' );
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header("Content-Disposition: attachment; filename=".$infilename.".csv");
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo "\xEF\xBB\xBF";
	
	echo $conteudoexport;
}
die;?>