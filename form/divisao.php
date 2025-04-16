<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
    require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "divisao";
$pagvalcampos = array(
	"iddivisao" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from divisao where iddivisao = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default" >
            <div class="panel-heading">
                <table>
                <tr> 		    
                    <td>
                        <input name="_1_<?=$_acao?>_divisao_iddivisao" type="hidden" value="<?=$_1_u_divisao_iddivisao?>" readonly='readonly'>
                    </td> 	   
                    <td  align="right">Divisão:</td> 
                    <td>
                        <input name="_1_<?=$_acao?>_divisao_divisao" type="text" value="<?=$_1_u_divisao_divisao?>">
                    </td> 
                    <?//if(!empty($_1_u_divisao_idplantel)){?>
                    <td  align="right">Gestor:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_divisao_idpessoa">
                            <option value=""></option>
                            <?fillselect("select p.idpessoa,ifnull(p.nomecurto,p.nome) as nome
                                                from pessoa p 
                                                where p.status = 'ATIVO' 
                                                and idtipopessoa = 1 
                                                and p.idempresa =".cb::idempresa()."
                                            order by p.nomecurto",$_1_u_divisao_idpessoa);?>		
                        </select>
                    </td> 
                    <?//}?>
                    <td  align="right">Tipo:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_divisao_tipo">
                            <?fillselect("select 'PRODUTO','Produto' union select 'SERVICO','Serviço'",$_1_u_divisao_tipo);?>		
                        </select>
                    </td> 
                    
                    <td  align="right">Status:</td> 
                    <td>
                        <select name="_1_<?=$_acao?>_divisao_status">
                            <?fillselect("select 'ATIVO','Ativo' union select 'INATIVO','Inativo'",$_1_u_divisao_status);?>		
                        </select>
                    </td> 
                </tr>	    
                </table>
            </div>
            <div class="panel-body"> 
            <div class="col-md-4">
            <?if(!empty($_1_u_divisao_iddivisao)){?>
            <table class="table table-striped planilha">
            <tr>
                <th align="center" colspan="2">Unidade de  Negócio</th>

            </tr>
            <?
             $sqlm="select d.iddivisaoplantel,p.idplantel,p.plantel from divisaoplantel d join plantel p on(p.idplantel=d.idplantel)
             where d.iddivisao=".$_1_u_divisao_iddivisao." order by p.plantel";
             $resm =  d::b()->query($sqlm)  or die("Erro plantel no campo prompt Drop sql:".$sqlm);
             while ($rowm = mysqli_fetch_assoc($resm)) {
            ?>
            <tr>
                <td><?=$rowm['plantel']?></td>
                <td><i class="fa fa-trash pointer hoververmelho cinza" onclick="excluirdiv(<?=$rowm['iddivisaoplantel']?>)"></i></td>
            </tr>
            <?
             }
            ?>
            <tr>
           
                   
                    <td colspan="2">
                        <select name="divisao_idplantel" onchange="inseridiv(this)">
                        <option value=''></option>
                            <?fillselect("select idplantel,plantel from plantel where status='ATIVO'  ".getidempresa('idempresa','plantel')." and prodserv='Y' order by plantel");?>		
                        </select>
                        <?
                        /*
                        $arrvalor=explode(",",$_1_u_divisao_inidplantel);
                            $sqlm="select idplantel,plantel from plantel where status='ATIVO'  ".getidempresa('idempresa','plantel')." and prodserv='Y' order by plantel";
                        ?>

                        <select class="selectpicker valoresselect" multiple="multiple" data-live-search="true" onchange="atualizavalor(this,<?=$_1_u_divisao_iddivisao?>);">
<?                         
                        $resm =  d::b()->query($sqlm)  or die("Erro plantel no campo prompt Drop sql:".$sqlm);
                        while ($rowm = mysqli_fetch_assoc($resm)) {
                            if (in_array($rowm['idplantel'],$arrvalor)){
                                $selected= 'selected';
                            }else{
                                $selected= '';
                            }
                            echo '<option data-tokens="'.retira_acentos($rowm['plantel']).'" value="'.$rowm['idplantel'].'" '.$selected.' >'.$rowm['plantel'].'</option>'; 
                        }		
		?>
                    </select>
<?
                    */
?>

                    </td> 
                    
            </tr>
            </table>
            <?}?>
            </div>

            </div>
        </div>
    </div>
</div>
<?
if( !empty($_1_u_divisao_tipo)){
    ?>               
    <div class="row">
    <div class="col-md-12">
        <div class="panel panel-default" >
            <div class="panel-heading"></div>
    <?
      /*  $sql="select * from (		
                            SELECT p.idprodserv,p.descrcurta,p.descr ,f.idprodservformula,
                            concat(f.rotulo,'-',ifnull(f.dose,'--'),' Doses ',' (',f.volumeformula,' ',f.un,')') as rotulo,
                            d.iddivisaoitem,d.comissaogest
                            FROM 
                                 prodserv p
                                 join divisaoplantel dp 
                                  join plantelobjeto pl on( pl.idobjeto = p.idprodserv  and pl.tipoobjeto = 'prodserv' and pl.idplantel = dp.idplantel) 
                                                         join prodservformula f  on(p.idprodserv =f.idprodserv  and f.status='ATIVO')  
                                     left join divisaoitem d on(d.iddivisao=dp.iddivisao and d.idprodserv=p.idprodserv and d.idprodservformula=f.idprodservformula)
                            where
                                 p.tipo='".$_1_u_divisao_tipo."'
                                 and dp.iddivisao=".$_1_u_divisao_iddivisao."
                             ".getidempresa('p.idempresa','prodserv')."
                            and p.status='ATIVO'  and p.venda='Y' and p.comissionado = 'Y'
                            union 
                            SELECT p.idprodserv,p.descrcurta,p.descr ,'' as idprodservformula,
                            '' as rotulo, d.iddivisaoitem,d.comissaogest
                            FROM 
                                 prodserv p
                                 join divisaoplantel dp 
                                  join plantelobjeto pl on( pl.idobjeto = p.idprodserv  and pl.tipoobjeto = 'prodserv' and pl.idplantel = dp.idplantel)
                                  left join divisaoitem d on(d.iddivisao=dp.iddivisao and d.idprodserv=p.idprodserv)
                            where
                                 p.tipo='".$_1_u_divisao_tipo."'
                                 and dp.iddivisao=".$_1_u_divisao_iddivisao."
                            ".getidempresa('p.idempresa','prodserv')."
                            and p.status='ATIVO' and p.venda='Y' and p.comissionado = 'Y'
                            and not exists( select 1 from  prodservformula f  where  p.idprodserv =f.idprodserv  and f.status='ATIVO' )
                ) as u order by u.descr";
                */
        if($_1_u_divisao_tipo=='PRODUTO'){
            $strvenda=" and p.venda='Y' ";
        }else{
            $strvenda=" ";
        }
        $sql="SELECT p.idprodserv,p.descrcurta,p.descr,d.iddivisaoitem,d.comissaogest     
                FROM 
                    prodserv p
                    join divisaoplantel dp 
                    join plantelobjeto pl on( pl.idobjeto = p.idprodserv  and pl.tipoobjeto = 'prodserv' and pl.idplantel = dp.idplantel)
                    left join divisaoitem d on(d.iddivisao=dp.iddivisao and d.idprodserv=p.idprodserv)
                where
                    p.tipo='".$_1_u_divisao_tipo."'
                    and dp.iddivisao=".$_1_u_divisao_iddivisao."
                    ".getidempresa('p.idempresa','prodserv')."
                and p.status='ATIVO' 
                ".$strvenda." 
                and p.comissionado = 'Y'       
            order by p.descr";

        $res = d::b()->query($sql) or die("A Consulta dos produtos falhou : " . mysqli_error() . "<p>SQL: $sql");
    echo "<!--";
    echo $sql;
    echo "-->";
        $rownum= mysqli_num_rows($res);
        if($rownum>0){
        ?>
    <table class="table table-striped planilha " >
        <tr>
            <th>Produto</th>            
            <th>Comissão Gestor% </th>
            <th>
                <input title='Alterar a comissão' style='background-color: white'  type="text" id='comissaogest'	value="" class='size3' onchange='alteracom(this);' >
                <a class="fa fa-download verde pointer hoverazul" title="Alterar valores" onclick="alteracom(this);"></a>
            </th>
        </tr>
<?
            $i=1;
	    while ($row = mysqli_fetch_assoc($res)){
                $i=$i+1;
                if(empty($row['iddivisaoitem'])){
                    $iu='i';
                }else{
                    $iu='u';
                }
                if(empty($row['descrcurta'])){
                    $descr=$row['descr'];
                }else{
                    $descr=$row['descrcurta'];
                }
?>
        <tr>
            <td>
                <?if($iu=='u'){?>
                <input name="_<?=$i?>_<?=$iu?>_divisaoitem_iddivisaoitem" type="hidden"	value="<?=$row['iddivisaoitem']?>"	readonly='readonly'>
                <?}?>
                <input name="_<?=$i?>_<?=$iu?>_divisaoitem_iddivisao" type="hidden"	value="<?=$_1_u_divisao_iddivisao?>"	readonly='readonly'>
                <input name="_<?=$i?>_<?=$iu?>_divisaoitem_idprodserv" type="hidden"	value="<?=$row['idprodserv']?>"	readonly='readonly'>
                
                <a class=" pointer hoverazul" title="Cadastro de Produto" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?=$row["idprodserv"]?>')"> <?=$descr?></a>
            </td>
           
            <td colspan="2"><input name="_<?=$i?>_<?=$iu?>_divisaoitem_comissaogest" placeholder="0.00" type="text" class="size5 valcomissao" value="<?=$row['comissaogest']?>"	></td>
        </tr>
        
<?        
            }
?>
    </table>
<?          
        }else{
            echo('Não encontrado produto ou serviço de venda comissionado.');
        }

?>
   </div>
        </div>
    </div>
</div>
<? 
}
?>

<?
    $tabaud = "divisao"; //pegar a tabela do criado/alterado em antigo
    require '../form/viewCriadoAlterado.php';
?>
<script>

function retiraundneg(inidunidadeobjeto){
	CB.post({
		objetos: "_x_d_plantelobjeto_idplantelobjeto="+inidunidadeobjeto
	});
}
function inseriundneg(inidund){
	CB.post({
		objetos: "_x_i_plantelobjeto_idobjeto="+$("[name=_1_u_divisao_iddivisao]").val()+"&_x_i_plantelobjeto_idplantel="+inidund+"&_x_i_plantelobjeto_tipoobjeto=divisao"
	});
}

function alteracom(vthis){
    var com = $("#comissaogest").val();
    $('.valcomissao').val(com);
}

$('.selectpicker').selectpicker('render');

function atualizavalor(vthis,iddivisao){
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_u_divisao_iddivisao":iddivisao
            ,"_x_u_divisao_inidplantel":strval
        }
        ,parcial: true
        ,refresh:false
    });
}

function inseridiv(vthis){
    var strval= $(vthis).val();
    CB.post({
        objetos: {
            "_x_i_divisaoplantel_iddivisao":$("[name=_1_u_divisao_iddivisao]").val()
            ,"_x_i_divisaoplantel_idplantel":strval
        }
        ,parcial: true
    });
}

function excluirdiv(inid){
	CB.post({
		objetos: "_x_d_divisaoplantel_iddivisaoplantel="+inid
	});
}
//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>