<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

if ($_POST) {
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
        <div class="panel panel-default">
            <div class="panel-body">
                <table class="table table-striped planilha" style="background-color: whitesmoke;">
                    <?
                    $sqlu = "select e.idempresa,e.razaosocial,o.idobjempresa 
                            from empresa e 
                            left join objempresa o 
                                on (o.empresa = e.idempresa and o.objeto='pessoa' and o.idobjeto = " . $_1_u_pessoa_idpessoa . ")
                            where e.status='ATIVO'";
                    $resu = d::b()->query($sqlu) or die("A Consulta de empresa e pessoa falhou : " . mysqli_error() . "<p>SQL: $sqlu");
                    ?>
                    <tr>
                        <td colspan="3"><b>Colaborador:</b> <?=$_1_u_pessoa_nomecurto?>
                        <input name="_1_<?=$_acao ?>_pessoa_idpessoa" type="hidden" value="<?=$_1_u_pessoa_idpessoa ?>" readonly='readonly'>
                        <input name="_1_<?=$_acao ?>_pessoa_idtipopessoa" type="hidden" value="<?=$_1_u_pessoa_idtipopessoa ?>" readonly='readonly'>
                        </td>
                    </tr>
                    <tr>
                        <?
                        while ($rowu = mysqli_fetch_assoc($resu)) {
                            if (!empty($rowu['idobjempresa'])) {
                                ?> <td>
                                    <div class="panel panel-default">
                                        <i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="retira(<?= $rowu['idobjempresa'] ?>);" alt="Retirar ">&nbsp;&nbsp;<? echo ($rowu['idempresa'] . " - " . $rowu['razaosocial']); ?></i>
                                    </div>
                                </td>
                            <?
                            } else { ?>
                                <td>
                                    <div class="panel panel-default">
                                        <i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="inseri(<?= $rowu['idempresa'] ?>,<?= $_1_u_pessoa_idpessoa ?>);" alt="Inserir ">&nbsp;&nbsp;<? echo ($rowu['idempresa'] . " - " . $rowu['razaosocial']); ?></i>
                                    </div>
                                </td>
                            <?
                            }

                            $i++;  
                            if($i%3 == 0)
                            {
                                echo '</tr><tr>';
                            }  
                        } //while ($rowu = mysqli_fetch_assoc($resu)){
                        ?>

                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>


<script>
    function retira(vid) {

        CB.post({
            objetos: "_x_d_objempresa_idobjempresa=" + vid
        });

    }

    function inseri(idund, vid) {
        CB.post({
            objetos: "_x_i_objempresa_empresa=" + idund + "&_x_i_objempresa_objeto=pessoa&_x_i_objempresa_idobjeto=" + vid
        });
    }

   
</script>