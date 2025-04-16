<?
require_once("../inc/php/functions.php");
require_once("../inc/php/permissao.php");
require_once("../form/controllers/nfentrada_controller.php");
cb::idempresa();
$idretornoremessa = $_GET['idretornoremessa'];

if (empty($idretornoremessa)) {
    die("Identificação do retorno de remessa não enviado");
}
//echo($idnf);

$arrayFormaPagamento = NfEntradaController::listarFormaPagamentoAtivoPorLP();

function getClientesfilial()
{

    $sql = "SELECT
            p.idpessoa,
            concat( if(p.cpfcnpj !='',concat(p.nome,' - ',p.cpfcnpj),p.nome), ' - ',e.sigla) as nome,
            CASE p.idtipopessoa
            WHEN 1 THEN 'FUNCIONARIO'
                WHEN 5 THEN 'FORNECEDOR'
                WHEN 2 THEN 'CLIENTE'	
                WHEN 7 THEN 'TERCEIRO'
                WHEN 12 THEN 'REPRESENTAÇÃO'					
            END as tipo
            FROM pessoa p	
            join empresa e on e.idempresa = p.idempresa	           
            WHERE p.status IN ('ATIVO','PENDENTE')
            AND p.idtipopessoa  in (5,7)   
            " . share::otipo('cb::usr')::pessoaPorCbUserIdempresa("p.idpessoa") . "             
            ORDER BY p.nome";

    $res = d::b()->query($sql) or die("getClientes: Erro: " . mysqli_error(d::b()) . "\n" . $sql);

    $arrret = array();
    while ($r = mysqli_fetch_assoc($res)) {
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idpessoa"]]["nome"] = $r["nome"];
        $arrret[$r["idpessoa"]]["tipo"] = $r["tipo"];
    }
    return $arrret;
}
//Recupera os produtos a serem selecionados para uma nova Formalização
$arrCliF = getClientesfilial();
//print_r($arrCli); die;
$jCliF = $JSON->encode($arrCliF);

?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Informações</div>
            <div class="panel-body">
                <table id='itens'>
                    <tr>
                        <td align="right">Nome:</td>
                        <td>
                            <input type="text" name="_1_i_nf_idpessoa" vnulo cbvalue="" value="" style="width: 45em;">
                        </td>
                        <td>Emissão:</td>
                        <td>
                            <input name="_1_i_nf_dtemissao" class="calendario form-control" id="fdata" type="text" value="<?= $_1_u_nf_dtemissao ?>">
                        </td>
                    </tr>
                    <tr>
                        <td nowrap align="right">Pagamento:</td>
                        <td>
                            <input id="forma_pag" cbvalue='<?= $_1_u_nf_idformapagamento ?>' name="_1_i_nf_idformapagamento" vnulo value="<?= $arrayFormaPagamento[$_1_u_nf_idformapagamento]['descricao'] ?>">
                        </td>

                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    jCliF = <?= $jCliF ?>; // autocomplete cliente
    //mapear autocomplete de clientes
    jCliF = jQuery.map(jCliF, function(o, id) {
        return {
            "label": o.nome,
            value: id + "",
            "tipo": o.tipo
        }
    });

    //autocomplete de clientes
    $("[name*=_1_i_nf_idpessoa]").autocomplete({
        source: jCliF,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
            };
        }
    });

    //------- Injeção PHP no Jquery -------
    jpag = <?= json_encode($arrayFormaPagamento) ?> || 0;
    //------- Injeção PHP no Jquery -------

    //------- Funções JS -------
    //autocomplete de Pagamentos
    jpag = jQuery.map(jpag, function(o, id) {
        return {
            "label": o.descricao,
            value: id
        }
    });

    $("#forma_pag").autocomplete({
        source: jpag,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });
</script>