<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if($_POST){
	require_once("../inc/php/cbpost.php");
}


//Parà¢metros mandatà³rios para o carbon
$pagvaltabela = "pessoa";
$pagvalcampos = array(
	"idpessoa" => "pk"
);

//Select que inicializa as variáveis que preenchem os campos da tela em caso de update
$pagsql = "select * from pessoa where  idpessoa = '#pkid'";

//Validacao do GET e criacao das variáveis 'variáveis' para a página
include_once("../inc/php/controlevariaveisgetpost.php");
?>

<div class='row'> 	
    <div class="col-md-12">
        <div class="panel panel-default" >
            <div class="panel-body">
                <table class="table table-striped planilha" style="background-color: whitesmoke;">          
                    <?  
                    $sqlu="SELECT e.idempresa,e.razaosocial,o.idobjempresa 
                    from empresa e 
                    left join objempresa o 
                        on (o.empresa = e.idempresa and o.objeto='PESSOA' and o.idobjeto = ".$_1_u_pessoa_idpessoa.")
                    where e.status='ATIVO'";
                    $resu = d::b()->query($sqlu) or die("A Consulta de empresa e pessoa falhou : " . mysql_error() . "<p>SQL: $sqlv");
                    ?>				
                    <tr>
                        <td colspan=""><b>Colaborador:</b> <?=$_1_u_pessoa_nomecurto?></td>
                    </tr>
                    <tr>                                  
                        <?                 
                        $i = 0;
                        while ($rowu = mysqli_fetch_assoc($resu))
                        {			
                            if(!empty($rowu['idobjempresa']))
                            {
                                $sqlMatriz = "SELECT idmatrizobj 
                                                FROM matrizpermissao 
                                               WHERE idpessoa = '$_1_u_pessoa_idpessoa' 
                                                 AND idempresa = ".$rowu['idempresa'].";";
                                $resMatriz = d::b()->query($sqlMatriz) or die("A Consulta de empresa e pessoa falhou : " . mysql_error() . "<p>SQL: $sqlMatriz");
                                $rowMatriz = mysqli_fetch_assoc($resMatriz);
                                ?>                  
                                <td>   
                                    <div class="panel panel-default">
                                        <i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="retira(<?=$rowu['idobjempresa']?>);" alt="Retirar ">
                                            &nbsp;&nbsp;<span style="font-family: Arial;"><?echo($rowu['idempresa']." - ".$rowu['razaosocial']);?></span>
                                        </i>
                                        <? if(empty(getIdlpMatriz($_1_u_pessoa_idpessoa, $rowu['idempresa']))){ ?>
                                            <i class="fa fa-warning vermelho fa-2x" title="Sem permissão LP para a empresa <?=$rowu['razaosocial']?>"></i>
                                        <? } ?>
                                        <? if(empty($rowMatriz['idmatrizobj'])){ ?>
                                            <i class="fa fa-gear vermelho fa-2x" title="Sem permissão Matriz para a empresa <?=$rowu['razaosocial']?>"></i>
                                        <? } ?>
                                    </div>
                                </td>       
                            <?               
                            }else{?> 
                                <td> 
                                    <div class="panel panel-default">
                                        <i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer"  onclick="inseri(<?=$rowu['idempresa']?>,<?=$_1_u_pessoa_idpessoa?>);" alt="Inserir ">
                                            &nbsp;&nbsp;<span style="font-family: Arial;"><?echo($rowu['idempresa']." - ".$rowu['razaosocial']);?></span>
                                        </i>
                                    </div>
                                </td>	
                            <?               
                            }  
                            $i++;  
                            if($i%3 == 0)
                            {
                                echo '</tr><tr>';
                            }          
                        }//while ($rowu = mysqli_fetch_assoc($resu)){
                        ?>                   
                    </tr>       
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function retira(vid){

        CB.post({
            objetos: "_x_d_objempresa_idobjempresa="+vid
        });
        
    }
    function inseri(idund,vid){
        CB.post({
            objetos: "_x_i_objempresa_empresa="+idund+"&_x_i_objempresa_objeto=pessoa&_x_i_objempresa_idobjeto="+vid
        });
    }
</script>