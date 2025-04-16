<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	include_once("../inc/php/cbpost.php");
}


/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "lote";
$pagvalcampos = array(
	"idlote" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from lote where idlote = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

function get_CadastroInsumos(){
	$sql = "select 
				idprodserv
				,descr
				,especial
				,codprodserv
				,un
                                ,unconv
			from prodserv 
			where status = 'ATIVO'
            and especial='N'
				and tipo='PRODUTO'
                                ".getidempresa('idempresa','prodserv')."
			order by descr";

	$res = d::b()->query($sql) or die("getCadastroInsumos: Falha:\n".mysqli_error(d::b())."\n".$stmp);

	$arrColunas = mysqli_fetch_fields($res);
	$arrret=array();
	$colid=$arrColunas[0]->name;//"agrupar" pela primeira coluna do select
	while($r = mysqli_fetch_assoc($res)){
		foreach ($arrColunas as $col) {
			$arrret[$r[$colid]][$col->name]=$r[$col->name];
		}
	}
	return $arrret;
	
}

$arrCadInsumos=get_CadastroInsumos();
$jCadInsumos=$JSON->encode($arrCadInsumos);

?>
<div class="row">
<div class="col-md-12">
<div class="panel panel-default">

    	<div class="panel-body">
		
<?//listar fornecedores

    $sql = "select i.* from  loteformulains i join prodserv p on(p.idprodserv = i.idprodserv and p.especial='N')
     where i.idlote=".$_1_u_lote_idlote." order by i.descr";
    $res = d::b()->query($sql) or die("A Consulta das configurações falhou :".mysql_error()."<br>Sql:".$sql); 
    $qtdrows= mysqli_num_rows($res);
    if($qtdrows>0){
       
   
?>			
		<table class="table table-striped planilha" > 
			<tr >
			   <th>Insumo</th>			   
               <th>Alteradopor</th>
               <th>Alteradoem</th>                
			</tr>
		<?	
        $ifi=0;					
        while($row = mysqli_fetch_assoc($res)){
            $ifi++;		
		?>				
		    <tr >
                <td>
                   
                    <input type="text" idloteformulains="<?=$row["idloteformulains"]?>" name="loteformulains_idprodserv" title="<?=$arrCadInsumos[$row["idprodserv"]]["codprodserv"]." #".$row["idprodserv"]?>" cbvalue="<?=$row["idprodserv"]?>" class="fonte08 loteformulains_idprodserv">		   
                </td>
                <td><?=$row['alteradopor']?></td>
                <td><?=dmahms($row['alteradoem'])?></td>
            </tr>
	     <?
        }//  while($row = mysqli_fetch_assoc($res)){
        ?>
		</table>
        <?
    }
        ?>
	</div>
</div>
</div>
</div>
<script>
jCadInsumos=<?=$jCadInsumos?>;
//Autocomplete Cadastro de Insumos
$(".loteformulains_idprodserv").autocomplete({
	source: jQuery.map(jCadInsumos, function(item, id) {
				return {"label": item.descr, value:id, "codprodserv":item.codprodserv, "especial":item.especial}
			}),
	create: function( event, ui ) {
		$this=$(this);
		vDescr=evalJson(`jCadInsumos[${$this.cbval()}].descr`);

		if($this.cbval() && vDescr){

			$this.val(vDescr);//Recupera a descrição de cada input durante a inicialização
			$this.data('ui-autocomplete')._renderItem = function (ul, item) {
				lbItem = item.label + " - <span class=cinzaclaro>"+item.codprodserv+"</span>";
				return $('<li>')
					.append('<a>' + lbItem + '</a>')
					.appendTo(ul);
			};
		}else{
			$this.css("color","#d43f3a").css("border","1px solid #d43f3a").closest("tr").find(".insumoinativo").removeClass("hidden")
			$this.attr("placeholder","Erro: Insumo com status INATIVO");
		}
	},
    select: function(event, ui){
        debugger;
        $this=$(this);
        CB.post({
				objetos: "_1_u_loteformulains_idloteformulains="+$this.attr("idloteformulains")+"&_1_u_loteformulains_idprodserv="+$this.attr("cbvalue")
				,parcial:true
                ,refresh:false
		}); 		
    }
});
</script>