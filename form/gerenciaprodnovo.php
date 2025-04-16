<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
if($_POST){
    include_once("../inc/php/cbpost.php");
}

$idpessoa=$_GET["idpessoa"];
$idprodserv=$_GET["idprodserv"];
$status=$_GET["status"];
$idplantel=$_GET['idplantel'];
$validacao=$_GET['validacao'];

function getcliente(){
	
    $sql= "select p.idpessoa,p.nome 
                from pessoa p
                where p.idtipopessoa=2 
                and exists (    
                                select 1
                                from amostra l
                                where  l.idpessoa = p.idpessoa    
                )
                and p.status in ('ATIVO','PENDENTE') order by p.nome";

    $res = d::b()->query($sql) or die("getcliente: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"]=(($r["nome"]));
    }
    return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCli=getcliente();
//print_r($arrCli); die;
$jCli=$JSON->encode($arrCli);


function getproduto(){
	
  /*  
    $sql= "select idprodserv,descr 
            from prodserv p
            where  p.fabricado='Y' 
            and p.venda='N' 
            and p.especial ='Y' 
            ".getidempresa('p.idempresa','prodserv')." 
            and exists(select 1 from prodservprproc o where o.idprodserv = p.idprodserv)
            and p.status='ATIVO' order by p.descr";*/
    $sql="select p.idprodserv,p.descr 
    from prodserv p join  unidadeobjeto u on( u.idunidade = 9 and u.idobjeto = p.idprodserv and u.tipoobjeto = 'prodserv')
    where p.tipo = 'PRODUTO'
    and p.status = 'ATIVO' 
    ".getidempresa('p.idempresa','prodserv')." 
    and p.especial='Y'
     order by p.descr";

    $res = d::b()->query($sql) or die("getproduto: Erro: ".mysqli_error(d::b())."\n".$sql);

    $arrret=array();
    while($r = mysqli_fetch_assoc($res)){
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idprodserv"]]["descr"]=(($r["descr"]));
    }
    return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrProd=getproduto();
//print_r($arrCli); die;
$jProd=$JSON->encode($arrProd);


if(!empty($idpessoa)){
    $clausulalote .= " and l.idpessoa =".$idpessoa." ";
}

if(!empty($idprodserv)){
    $clausulad .= " and p.idprodserv =  ".$idprodserv."";
}

if($status=='ATIVO'){
    $clausulals .= " and ls.status not in ('CANCELADO','ESGOTADO')";
}else{
    $clausulals .= " and ls.status not in ('CANCELADO') ";
}

if($idplantel){
    $strplantel=" and f.idplantel=".$idplantel;
    $strinplantel=" and exists (select 1 from prodservformula f where f.idprodserv= p.idprodserv and f.status='ATIVO' and f.idplantel = ".$idplantel.") ";
}else{
    $strplantel='';
    $strinplantel='';
}

if($validacao=='V'){
    $strvalidacao=" and not exists (select 1 from prodservforn pf 
                                            where pf.idprodserv=l.idprodserv 
                                            and pf.idprodservformula= f.idprodservformula
                                            and pf.idpessoa=l.idpessoa and pf.validadoem > DATE_SUB(now(), INTERVAL 12 MONTH))
                    and not exists (select 1 from prodservforn pf 
                                            where pf.idprodserv=l.idprodserv 
                                            and pf.idprodservformula= f.idprodservformula
                                            and pf.idpessoa=l.idpessoa and pf.valido='N')                      
                    ";

    $invalidacao=" and not exists (select 1 from prodservforn pf 
                                            where pf.idprodserv=p.idprodserv 
                                            and pf.validadoem > DATE_SUB(now(), INTERVAL 12 MONTH))";
	
}elseif ($validacao=='O') {
    $strvalidacao=" and exists (select 1 from prodservforn pf 
                                    where pf.idprodserv=l.idprodserv 
                                    and pf.idprodservformula= f.idprodservformula
                                    and pf.idpessoa=l.idpessoa and pf.validadoem > DATE_SUB(now(), INTERVAL 12 MONTH))
                and not exists (select 1 from prodservforn pf 
                                    where pf.idprodserv=l.idprodserv 
                                    and pf.idprodservformula= f.idprodservformula
                                    and pf.idpessoa=l.idpessoa and pf.valido='N') ";

    $invalidacao=" and exists (select 1 from prodservforn pf 
                                    where pf.idprodserv=p.idprodserv 
						and pf.validadoem > DATE_SUB(now(), INTERVAL 12 MONTH))";
}elseif($validacao=='I'){
    $strvalidacao=" and exists (select 1 from prodservforn pf 
						where pf.idprodserv=l.idprodserv 
						and pf.idprodservformula= f.idprodservformula
						and pf.idpessoa=l.idpessoa and pf.valido='N')";
		
    $invalidacao=" and exists (select 1 from prodservforn pf 
						where pf.idprodserv=p.idprodserv 
						and pf.valido='N')";
}else{
    $strvalidacao=" ";
    $invalidacao=" ";
}

?>
<style>
	
.insumosEspeciais a i.fa{
    display: inline-block !important;
}	

.itemestoque{
    Xwidth:100%;
    width:auto;
    display: inline-block;
    text-align: right;
    margin: 3px;
}
.itemestoque.especial{
    display:none;
}
.itemestoque.especial.especialvisivel{
    display:inline-block !important;
}

.cbProduto{
	color: black;
	padding: 0px 10px;
        font-weight: bold;
}

</style>
<div class="row">
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading" >Pesquisar </div>
        <div class="panel-body" >         
            <div class="row">      
                <div class="col-md-1">Cliente:</div>
                <div class="col-md-5">
                    <input id="idpessoa"  type="text" name="idpessoa"  cbvalue="<?=$idpessoa?>" value="<?=$arrCli[$idpessoa]["nome"]?>" style="width: 40em;" vnulo>
                </div>               
                <div class="col-md-1"></div>
                <div class="col-md-5"> 
                 
               </div>
            </div>
            <div class="row">      
                <div class="col-md-1">Produto:</div>
                <div class="col-md-5">	
                        <input id="idprodserv"  type="text" name="idprodserv"  cbvalue="<?=$idprodserv?>" value="<?=$arrProd[$idprodserv]["descr"]?>" style="width: 60em;" vnulo>
                </div>               
                <div class="col-md-2"></div>
                <div class="col-md-4"> 
				 
                </div>
            </div>
            <div class="row">
                <div class="col-md-1">Tipo/Especie:</div>
                <div class="col-md-2">
                    <select class='size10' name="idplantel" id="idplantel"  >
                    <option value=""></option>
                       <?fillselect("select idplantel,plantel from plantel where status='ATIVO' and prodserv='Y' ".getidempresa('idempresa','plantel')."  order by plantel",$idplantel);?>
                    </select>
                </div>               
                <div class="col-md-1">Validação:</div>
                <div class="col-md-5"> 
                    <select class='size10' name="validacao" id="validacao"  >	
                         <?fillselect(" select 'T','Todos'
                                    union select 'NA','Não Autorizada' 
                                    union select 'A','Autorizada'
                                    union select 'O','Aprovado'
                                    union select 'P','Pendente' ",$validacao);?>
                    </select>
               </div>	
            </div>

            <div class="row"> 
                <div class="col-md-8"></div>
                <div class="col-md-1 nowrap">
                   <button id="cbPesquisar" class="btn btn-default btn-primary" onclick="pesquisar(this)">
                        <span class="fa fa-search"></span>
                   </button> 
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!--link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" /-->
	<br>
	<div class="row">
		<div class="col-md-12">
			<div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

			<strong><i class="glyphicon glyphicon-info-sign"></i> Para pesquisa favor preencher pelo menos um dos campos.
			<br/>
                <br/><li>Cliente</li>
			    <br/><li>Produto</li>
                    <br/><li>Tipo/Especie</li>
			</div>
		</div>
	</div>


<script>
<?

if(empty($jCli)){
    $jCli="null";
}

?>
	
jCli=<?=$jCli?>;// autocomplete cliente


//mapear autocomplete de clientes
jCli = jQuery.map(jCli, function(o, id) {
    return {"label": o.nome, value:id}
});
//autocomplete de clientes
$("[name*=idpessoa]").autocomplete({
    source: jCli
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});	

<?
if(empty($jProd)){
    $jProd="null";
}
?>
jProd=<?=$jProd?>;// autocomplete 


//mapear autocomplete de clientes
jProd = jQuery.map(jProd, function(o, id) {
    return {"label": o.descr, value:id}
});
//autocomplete 
$("[name*=idprodserv]").autocomplete({
    source: jProd
    ,delay: 0
    ,create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $('<li>').append("<a>"+item.label+"</a>").appendTo(ul);
        };
    }	
});
	
  
function pesquisar(vthis){

    var idpessoa =  $("[name=idpessoa]").attr("cbvalue");
    var idprodserv = $("[name=idprodserv]").attr("cbvalue");
    var status = $("[name=status]").val();
    var idplantel = $("[name=idplantel]").val();
    var validacao = $("[name=validacao]").val();
    var str="idprodserv="+idprodserv+"&idpessoa="+idpessoa+"&status="+status+"&validacao="+validacao+"&idplantel="+idplantel;
  
    // CB.go(str);

    if(getUrlParameter("_idempresa") != ""){
        str += "&_idempresa="+getUrlParameter("_idempresa");
    }
     

    CB.modal({
        url: '?_modulo=gerenciaprodcorponovo&'+str + "&_modo=form"
        ,header:"Gerência de Sementes"
    });

}

//o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>;

</script>
