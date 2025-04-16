<?
require_once("../inc/php/validaacesso.php");

if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "abreviacao";
$pagvalcampos = array(
	"idabreviacao" => "pk"
);

$sql= "select * from abreviacao";
$res = d::b()->query($sql) or die("getModulo: Erro: ".mysqli_error(d::b())."\n".$sql);

$resultadoAbreviacoes = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>

<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading" id="insereModal">

            <table>
                <tr>
                    <td>Nova Abreviação</td>
                </tr>
            </table>
        </div>

        <div style="margin-top: -15px;" class="panel-body" id="appendEditar">
            <table id="formularioInserir">
                <tr>
                    <td>Abreviação</td>
                    <td>
                        <input name="_1_i_abreviacao_abreviacao" type="text" value="<?= $_1_u_abreviacao_abreviacao ?>">
                    </td>
                    <td>Palavra</td>
                    <td>
                        <input name="_1_i_abreviacao_palavra" type="text" value="<?= $_1_u_abreviacao_palavra ?>">
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <div style="margin-top: 35px;">
            <table class="table table-striped table-hover">
                <tr>
                    <th style="padding-top: 15px;">
                        Pesquisar:
                    </th>
                    <th colspan="6">
                        <input type="text" class="form-control" id="pesquisarabreviacao" style="width: 45%;">
                    </th>
                </tr>   
                <tr>
                    <th width="10%">
                        Abreviação
                    </th>
                    <th width="20%">
                        Palavra
                    </th>
                    <th>
                        Criado Por
                    </th>
                    <th>
                        Criado Em
                    </th>
                    <th>
                        Alterado Por
                    </th>
                    <th>
                        Alterado Em
                    </th>
                </tr>    

            <tbody id="filtrarAbreviacoes">
            <?php  
            foreach($resultadoAbreviacoes as $row){
            ?>
                <tr>
                    <td id="abreviacao_<?=$row['idabreviacao']?>"><?=$row['abreviacao']?></td>
                    <td id="palavra_<?=$row['idabreviacao']?>"><?=$row['palavra']?></td>
                    <td><?= $row['criadopor']?></td>
                    <td><?= dmahms($row['criadoem'])?></td>
                    <td><?= $row['alteradopor'] ?></td>
                    <td><?= dmahms($row['alteradoem']) ?></td>
                    <td class="text-right"><i class="fa fa-edit btn" onclick="showModal(<?= $row['idabreviacao'] ?>)" style="font-size: 1.5rem; color: #337ab7; margin-right: 30px;"></i> <i class="fa fa-trash btn" onclick="excluirAbreviacao(<?= $row['idabreviacao'] ?>)" style="font-size: 1.5rem; color: red;"></i></td>
                </tr>
            <?php
            };
            ?>
            </tbody>
        </table>
    </div>
</div>





<script>
function showModal(id){debugger
    $oModal = $(`
                        <div id="modalUpdateAbreviacao" style="display: none;"></div>
                        
                        <table id="formularioInserir">
                                <tr>
                                    <td>Abreviação</td>
                                    <td>
                                        <input name="_1_u_abreviacao_abreviacao" id="novaAbreviacao" type="text" value="${$("#abreviacao_"+id).html().trim()}">
                                    </td>
                                    <td>Palavra</td>
                                    <td>
                                        <input name="_1_u_abreviacao_palavra" id="novaPalavra" type="text" value="${$("#palavra_"+id).html().trim()}">
                                    </td>
                                    <td id="addButton">
                                        <button id="cbSalvar" type="button" class="btn btn-success btn-xs" onclick="atualizaAbreviacao(${id})" title="Salvar">
                                            <i class="fa fa-circle"></i>Salvar
                                        </button>
                                    </td>
                                </tr>
                            </table>    
                        </div>
            `);
    CB.modal({
        titulo: "Atualizar Abreviação",
        corpo: [$oModal],
        classe: 'quarenta'
    });
}

function atualizaAbreviacao(id) {debugger
    CB.post({
        objetos: {
            "_w_u_abreviacao_idabreviacao"  : id,
            "_w_u_abreviacao_abreviacao"    : $("#novaAbreviacao").val(),
            "_w_u_abreviacao_palavra"       : $("#novaPalavra").val()
        }
        ,parcial: true
    });
    $('#cbModal').modal('hide');
}

function excluirAbreviacao(id){
    if (window.confirm('Confirma a Exclusão da abreviação de ' + $("#palavra_"+id).html() +' - '+'('+$("#abreviacao_"+id).html()+')')) {
        CB.post({
            objetos: {
                "_w_d_abreviacao_idabreviacao"  : id
            }
            ,parcial: true
        });
    }
}

//FIltrar Tabela
$(document).ready(function(){
  $("#pesquisarabreviacao").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#filtrarAbreviacoes tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});

</script>


