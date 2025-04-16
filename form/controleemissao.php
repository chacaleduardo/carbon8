<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "controleemissao";
$pagvalcampos = array(
	"idcontroleemissao" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from controleemissao where idcontroleemissao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if(empty($_1_u_controleemissao_tipo)){
    $_1_u_controleemissao_tipo='CLIENTE';
}
?>
<script>
<?if($_1_u_controleemissao_tipo=="OFICIAL" or $_1_u_controleemissao_status=="ENVIADO"){?>
$("input").not('[name*="controleemissao_idcontroleemissao"]').prop( "disabled", true );
$("select" ).prop( "disabled", true );

<?}?>
</script>
<style>
    
</style>
<div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">		
            <table>
                <tr>
                    <td>Nome:</td>
                    <td class="nowrap">
                        <input id="idcontroleemissao" name="_1_<?=$_acao?>_controleemissao_idcontroleemissao" type="hidden"	value="<?=$_1_u_controleemissao_idcontroleemissao?>">
                        
                        <?if(empty($_1_u_controleemissao_idpessoa)){?>
                        <select <?=$disabled?> name="_1_<?=$_acao?>_controleemissao_idpessoa" id="idpessoa"  vnulo>
                                <option value=""></option>
                                <?fillselect("SELECT p.idpessoa,p.nome 
                                                        FROM pessoa p
                                                        where  p.idtipopessoa = 2
                                                         ".getidempresa('p.idempresa','pessoa')."
                                                        and p.status = 'ATIVO'
                                                        and exists  (select 1 from pessoacontato c  where p.idpessoa =c.idpessoa ) order by p.nome",$_1_u_controleemissao_idpessoa);?>		
                        </select>
                        <?}else{
                                $nome=traduzid('pessoa', 'idpessoa', 'nome', $_1_u_controleemissao_idpessoa);                              
                                echo($nome);
                            ?>
                        <input id="idpessoa"  name="_1_<?=$_acao?>_controleemissao_idpessoa" type="hidden" value="<?=$_1_u_controleemissao_idpessoa?>">
                        <?}?>
                    </td>
                    <td align="right">Exercicio:</td>
                    <td>
                    <?
                    if(empty($_1_u_controleemissao_exercicio)){
                            $_1_u_controleemissao_exercicio= date("Y");
                    }
                    ?>
                    <input name="_1_<?=$_acao?>_controleemissao_exercicio" id="exercicio"  type="text" size="4" value="<?=$_1_u_controleemissao_exercicio?>">

                    </td>
                    <td align="right">Status:</td> 
                    <td>
                        <select   name="_1_<?=$_acao?>_controleemissao_status">
                            <?fillselect("select 'ABERTO','Aberto' union select 'PENDENTE','Pendente' union select 'ENVIADO','Enviado'",$_1_u_controleemissao_status);?>		
                        </select>
                    </td>
                    <td>
                        <input name="_1_<?=$_acao?>_controleemissao_tipo" type="hidden"	value="<?=$_1_u_controleemissao_tipo?>">
                        <?=$_1_u_controleemissao_tipo?>
                    </td>
                    <td><a title="Ver Resultados." class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/emissaoresultado.php?_acao=u&idcontroleemissao=<?=$_1_u_controleemissao_idcontroleemissao?>')"></a></td>
                </tr>
            </table>
        </div>
        <div class="panel-body">
      
        </div>
    </div>
</div>
 <?
    if($_1_u_controleemissao_tipo!="OFICIAL" and $_1_u_controleemissao_status!="ENVIADO"){
 ?>       
    <div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Filtros de Busca</div>
        <div class="panel-body">
            <div class='row'>
                <div class="col-md-3" >
                <div class="panel panel-default" >
                    <table class="table table-striped planilha">
                        <tr>
                            <th>NFs</th>
                        </tr>
                        <tr>
                            <td>
                                <select tabindex="5" name="nnfe" id="nnfe" style="border: 1px solid silver;">
                                    <option value=""></option>
                                    <?fillselect("select n.idnotafiscal,n.nnfe
                                            from notafiscal n
                                            where n.nnfe is not null
                                            and n.exercicio = ".$_1_u_controleemissao_exercicio."
                                            and n.idpessoa =  ".$_1_u_controleemissao_idpessoa);?>
                                </select>
                            </td>
                        </tr>
                    </table>  
                </div>
                </div>
                <div class="col-md-3" >
                    <div class="panel panel-default" >
                        <table class="table table-striped planilha">
                            <tr>
                                <th>Registro</th>
                            </tr>
                            <tr>
                                <td><input name="idregistro" id="idregistro" class="size10" type="text" value="" style="border: 1px solid silver;" > e <input name="idregistro2" id="idregistro2" class="size10" type="text" value="" style="border: 1px solid silver;" ></td>
                            </tr>
                        </table>
                    </div>
                </div>
                 <div class="col-md-3" >
                    <div class="panel panel-default" >
                        <table class="table table-striped planilha">
                            <tr>
                                <th>Núcleo</th>
                            </tr>
                            <tr>
                                <td><input name="nucleo" id="nucleo" size="4" type="text" value="" style="border: 1px solid silver;" ></td>
                            </tr>
                        </table>                    
                    </div>
                </div>
                 <div class="col-md-3" >
                    <div class="panel panel-default" >
                        <table class="table table-striped planilha">
                            <tr>
                                <th>Lote</th>
                            </tr>
                            <tr>
                                <td><input name="lote" id="lote" size="4" type="text" value="" style="border: 1px solid silver;" ></td>
                            </tr>
                        </table>                      
                    </div>
                </div>
            </div>
            <div class="panel-body" id="resp">
                
            </div>
        </div>
    </div>
    </div>

 
           
 
<?
    }//if($_1_u_controleemissao_tipo!="OFICIAL" and $_1_u_controleemissao_status!="ENVIADO"){
?>      


<?
	if($_1_u_controleemissao_tipo=="OFICIAL" or $_1_u_controleemissao_status=="ENVIADO"){
			$sql1="select e.idcontroleemissaoitem,a.idregistro,a.exercicio,a.lote,n.nucleo,p.nome,pr.descr
			from controleemissaoitem e,resultado r,pessoa p,prodserv pr,amostra a left join nucleo n on(n.idnucleo = a.idnucleo)
			where pr.idprodserv = r.idtipoteste 
			and p.idpessoa = a.idpessoa
			and a.idamostra =r.idamostra
			and r.idresultado = e.idobjeto
                        and e.tipoobjeto='resultado'
			and e.idcontroleemissao =".$_1_u_controleemissao_idcontroleemissao;
			$res1=mysql_query($sql1);
			$qtdrows1=mysql_num_rows($res1);
		if($qtdrows1>0){
?>
<div class="col-md-12" >
    <div class="panel panel-default" >
        <div class="panel-heading">Resultados</div>
        <div class="panel-body">
		<table id="tabx" class="table table-striped planilha">
			<tr id="tabx">				
                            <th align="center">Registro</th>
                            <th align="center">Exercicio</th>	
                            <th align="center">Teste</th>
                            <th align="center">Nucleo</th>							
                            <th align="center">Lote</th>
                            <th align="center">Cliente</th>
			</tr>
<?			
			while($row1=mysql_fetch_assoc($res1)){
?>
			<tr>				
                            <td ><?=$row1["idregistro"]?></td>
                            <td ><?=$row1["exercicio"]?></td>
                            <td ><?=$row1["descr"]?></td>								    	
                            <td><?=$row1["nucleo"]?></td>
                            <td ><?=$row1["lote"]?></td>
                            <td><?=$row1["nome"]?></td>
			</tr>			
<?		
			}
?>
		</table>
        </div>
    </div>
</div>
<?	
		}		
	}
?>


<?
if(!empty($_1_u_controleemissao_idcontroleemissao)){// trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_controleemissao_idcontroleemissao; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
	$tabaud = "controleemissao"; //pegar a tabela do criado/alterado em antigo
	require 'viewCriadoAlterado.php';
?>

<script>
$(document).ready(function() {
    //atualiza os resultados selecionados para envio dos links
    pesquisaresult();

    //pesquisa os resultados ao alterar o numero da nf na drop
    $("#nnfe").change(function(){
        pesquisaresult();
    });

    // desabilitar a tecla ENTER e ativar em alguns campos estabelecidos
    $(document).keypress(function(e)
    {
        if(e.keyCode==13){//Enter 

            //se for um desses ai onde esta o target chama a função
            if(e.target.id == 'nnfe' || e.target.id == 'nucleo' ||  e.target.id == 'lote'|| e.target.id=='idregistro' || e.target.id=='idregistro2'){
                pesquisaresult();
                e.preventDefault();					
            }
            //senão desabilita o enter
            e.preventDefault();

        }
    });

});    
    
    
// função para pesquisar os resultados
function pesquisaresult(){	
	var vidnotafiscal = $("#nnfe").val();
	var vidcontroleemissao = $("#idcontroleemissao").val();
	var vidcliente = $("#idpessoa").val();
	var vnucleo = $("#nucleo").val();
	var vlote = $("#lote").val();
	var vexercicio = $("#exercicio").val();
	var vidregistro = $("#idregistro").val();
	var vidregistro2 = $("#idregistro2").val();
	
		document.body.style.cursor = 'wait';
		$.get("ajax/listaresultado.php", 
				{idnotafiscal : vidnotafiscal,
				idcontroleemissao : vidcontroleemissao,
				idcliente : vidcliente,
				nucleo : vnucleo,
				lote : vlote,
				exercicio : vexercicio,
				idregistro:vidregistro,
				idregistro2:vidregistro2}, 
				function(resposta){
					$("#resp").html(resposta);
				}
		);
		$('#resp').show();	
			
		document.body.style.cursor = '';
}

//função para inserir os resultados selecionados na lista do email
function inserir(vidresultado){
	var vidcontroleemissao= $("#idcontroleemissao").val();
     
        str="_x_i_controleemissaoitem_idcontroleemissao="+vidcontroleemissao+"&_x_i_controleemissaoitem_idobjeto="+vidresultado+"&_x_i_controleemissaoitem_tipoobjeto=resultado";
	
        CB.post({
	    objetos: str
	   ,parcial:true
           ,refresh:false
           ,posPost: function(data, textStatus, jqXHR){
                pesquisaresult();
            }
	});         
  
	
}

function retirar(vidcontroleemissaoitem){
    
     str="_x_d_controleemissaoitem_idcontroleemissaoitem="+vidcontroleemissaoitem;
	
        CB.post({
	    objetos: str
	   ,parcial:true
           ,posPost: function(data, textStatus, jqXHR){
                       pesquisaresult();
            }
	});
	
}

</script>
<?
require_once '../inc/php/readonly.php';
?>