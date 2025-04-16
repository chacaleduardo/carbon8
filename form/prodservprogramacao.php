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
$pagvaltabela = "pessoa";
$pagvalcampos = array(
	"idpessoa" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from pessoa where idpessoa = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");



function jsonProdutos(){
    global $_1_u_pessoa_idpessoa;
    //buscar, entre os planteis ativos, o plantel do usuario
    $sqltpl="
        select group_concat(pl.idplantel) as planteis from plantel pl
        join plantelobjeto plo on plo.idplantel=pl.idplantel
        where campo is not null and status='ATIVO' and plo.tipoobjeto='pessoa' and plo.idobjeto=".$_1_u_pessoa_idpessoa;
    $resPlantelUsuario= d::b()->query($sqltpl) or die("A Consulta dos planteis falhou : " . mysqli_error() . "<p>SQL: $sqltpl");

    //iniciar variavel auxiliar de planteis do usuario com nada
    $planteis='';

    //caso tenha planteis, preencher variavel auxiliar da busca de produtos
    if(mysqli_num_rows($resPlantelUsuario)){
        $resPlantelUsuario = mysqli_fetch_array($resPlantelUsuario);
        if(!empty($resPlantelUsuario['planteis'])){
            $planteis=" and f.idplantel in (".$resPlantelUsuario['planteis'].") ";
        }
    }   
   //buscar produtos de acordo com os planteis do cliente, produtos de venda, marcados como especial (vacinas)
    $sqlprodserv = "select f.idprodservformula,ifnull(p.descrcurta,p.descr) as descr,
		concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo
            from prodservformula f 
            join prodserv p on(p.idprodserv=f.idprodserv)
            where p.venda='Y'
            and p.especial='Y'
            and f.status='ATIVO'
            ".$planteis."
            and p.tipo='PRODUTO'
            ".getidempresa('p.idempresa','prodserv')."
            AND p.status='ATIVO' order by p.descrcurta";
    echo "<!-- sql".$sqlprodserv."-->";

    $resProdutos = d::b()->query($sqlprodserv);
    $produtos=array();
    
    //montar array de produtos (vacinas)
    $i=0;
    if(mysqli_num_rows($resProdutos)>0)
        while ($produto=mysqli_fetch_assoc($resProdutos)) {
            $produtos[$i]["value"]=$produto["idprodservformula"];
            $produtos[$i]["label"]= $produto["descr"];
            $produtos[$i]["tipo"]= $produto["rotulo"];
            $i++;
        }
    return $produtos;
}

//Recupera os produtos a serem selecionados para uma nova Formalização
$arrProd=jsonProdutos();
//print_r($arrCli); die;
$jsonProd=$JSON->encode($arrProd);


$sql = "select s.descr,p.idprodserv,concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,pl.plantel,s.vlrvenda,p.*
        from prodservforn p 
        left join prodserv s on(s.idprodserv=p.idprodserv)
        left join prodservformula f on(p.idprodservformula=f.idprodservformula)
        left join plantel pl on(pl.idplantel=f.idplantel)
        where p.idpessoa=".$_1_u_pessoa_idpessoa." 
        and p.status='ATIVO'
        and p.valido = 'Y'";
		
	$res = d::b()->query($sql) or die("A Consulta das programações falhou :".mysqli_error()."<br>Sql:".$sql);
	//die($sql);
	$rownum1= mysqli_num_rows($res);
?>     
        <div class="row ">
	    <div class="col-md-12" >
	    <div class="panel panel-default" >
		    <div class="panel-heading" >
                     <table>
                        <tr>		
                            <td align="right">Programação:</td> 
                            <td>
                                <span class="idbox"><?=$_1_u_pessoa_nome?></span>
                                <input name="_1_<?=$_acao?>_pessoa_idpessoa" type="hidden" value="<?=$_1_u_pessoa_idpessoa?>" readonly='readonly'>
                            </td>                        
                        </tr>


                        </table>
                    </div>
		<div class="panel-body">  
		<table class="table table-striped planilha"  >  
                <tr >	
                    <th>Qtd</th>
		    <th>Produto</th>                    
                    <th>Criado por</th>
                    <th>Criado em</th>
                    <th>Alterdo por</th>
                    <th>Alterado em</th>
                    <th></th>
		</tr>	 
<?	
                    $i=1;
		while($row = mysqli_fetch_assoc($res)){
                    $i=$i+1;
	?>
                <tr class="res" >
                    <td><input class='size4' name="_<?=$i?>_u_prodservforn_qtd" type="text"	value="<?=$row['qtd']?>"></td>
                    <td nowrap >
                        <input name="_<?=$i?>_u_prodservforn_idprodservforn" type="hidden"	value="<?=$row['idprodservforn']?>"	readonly='readonly'>
                        <a class=" pointer hoverazul" title="Empresa" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row["idprodserv"]?>')"> <?=$row["descr"]?></a>
                        <BR>
                        <?=$row["rotulo"]?>
                    </td>           
                    
                    <td><?=$row['criadopor']?></td>
                    <td><?=dmahms($row['criadoem'])?></td>
                    <td><?=$row['alteradopor']?></td>
                    <td><?=dmahms($row['alteradoem'])?></td>
                    <td>
                      <a class="fa fa-trash  cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir(<?=$row['idprodservforn']?>)" title="Inativar"></a>
                    </td>
                </tr>
	<?
		}//while($row = mysqli_fetch_array($res)){
		?>
                <tr>
                    <td></td>
                    <td ><input type="text" name="prodservformula_idprodservformula" placeholder="Selecione um produto para entrar na programação."  cbvalue="" value=""></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
	   
		</table>
		</div>
	    </div>
	    </div>
	</div>
		

<script>

 jsonProd = <?=$jsonProd?>;//// autocomplete produto
    
//autocomplete de produto
$("[name*=prodservformula_idprodservformula]").autocomplete({
    source: jsonProd
    ,delay: 0
    ,select: function(event, ui){
        insereprodservformula(ui.item.value);		
    },create: function(){
        $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
         return $('<li>').append("<a>"+item.label+"<span class='cinzaclaro'> "+item.tipo+"</span></a>").appendTo(ul);
        };
    }	
});

function insereprodservformula(idprodservformula){
    //alert(idprodservformula);
    
    CB.post({
        objetos: "_x_i_prodservforn_idpessoa="+$("[name=_1_u_pessoa_idpessoa]").val()+"&_x_i_prodservforn_idprodservformula="+idprodservformula
        ,parcial: true        
    })  
    
}
function excluir(idprodservforn){
     CB.post({
        objetos: "_x_u_prodservforn_idprodservforn="+idprodservforn+"&_x_u_prodservforn_status=INATIVO"
        ,parcial: true        
    })  
}




</script>