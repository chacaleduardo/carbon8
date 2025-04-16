<?
require_once("../inc/php/functions.php");
require_once("../inc/php/permissao.php");
cb::idempresa();
$idnf = $_GET['idnf'];

if (empty($idnf)) {
    die("Identificação da nota não enviada");
}
//echo($idnf);


function getClientesfilial()
{
    $sql = "SELECT
            p.idpessoa,
            concat( if(p.cpfcnpj != '', concat(p.nome,' - ', p.cpfcnpj), p.nome), ' - ', e.sigla) as nome,
            CASE p.idtipopessoa
                WHEN 1 THEN 'FUNCIONARIO'
                WHEN 5 THEN 'FORNECEDOR'
                WHEN 2 THEN 'CLIENTE'	
                WHEN 7 THEN 'TERCEIRO'
                WHEN 12 THEN 'REPRESENTAÇÃO'					
            END as tipo
            FROM pessoa p	
            JOIN empresa e on e.idempresa = p.idempresa	
            JOIN endereco d on(d.idpessoa = p.idpessoa and d.status = 'ATIVO' )	
            JOIN tipoendereco t  on(t.idtipoendereco = d.idtipoendereco and t.faturamento = 'Y')
            WHERE p.status IN ('ATIVO','PENDENTE')
            AND p.idtipopessoa  in (1,2,5,7,12)   
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

$idClienteFaturamento = traduzid('empresa', 'idempresa', 'idpessoaform', $_GET['idempresafat']);

$sqlx = "SELECT * FROM (SELECT i.idnfitem,
                                n.idnf,
                                n.envio,
                                i.qtd,
                                i.idprodserv,
                                IFNULL(p.descr, i.prodservdescr) COLLATE utf8mb4_unicode_ci AS descr,
                                IFNULL(i.un, p.un) COLLATE utf8mb4_unicode_ci AS un
                         FROM nf n JOIN nfitem i ON (i.idnf = n.idnf)
                         JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                         JOIN lotecons c ON (c.idobjeto = i.idnfitem  AND c.tipoobjeto = 'nfitem' AND c.qtdd > 0)
                        JOIN lotefracao lf ON (lf.idlotefracao = c.idlotefracao AND lf.idempresa = n.idempresa)
                        WHERE n.idnf = $idnf 
                    UNION 
                        SELECT  i.idnfitem,
                                n.idnf,
                                n.envio,
                                i.qtd,
                                i.idprodserv,
                                IFNULL(p.descr, i.prodservdescr) COLLATE utf8mb4_unicode_ci AS descr,
                                IFNULL(i.un, p.un) COLLATE utf8mb4_unicode_ci AS un
                         FROM nf n1 JOIN nf n ON (n1.idempresafat = n.idempresafat AND n.status IN ('INICIO' , 'ORCAMENTO', 'SOLICITADO', 'PEDIDO', 'PRODUCAO', 'EXPEDICAO', 'FATURAR'))
                         JOIN nfitem i ON (i.idnf = n.idnf)
                         JOIN natop o ON (o.idnatop = n.idnatop)
                         JOIN prodserv p ON (p.idprodserv = i.idprodserv)
                        LEFT JOIN lotecons c ON (c.idobjeto = i.idnfitem AND c.tipoobjeto = 'nfitem'AND c.qtdd > 0)
                        LEFT JOIN lotefracao lf ON (lf.idlotefracao = c.idlotefracao AND lf.idempresa = n.idempresa)
                        WHERE n1.idnf = $idnf
                        GROUP BY i.idnfitem) as nf
                        ORDER BY idnf ASC;";

$resx = d::b()->query($sqlx) or die("erro ao buscar itens do xml no banco de dados sql=" . $sqlx);
$qtdx = mysqli_num_rows($resx);
//$i=1;
if ($qtdx > 0) {
    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Informações</div>
                <div class="panel-body">
                    <table>
                        <tr>
                            <td align="right">Cliente:</td>
                            <td>
                                <input type="text" class="pedido_idpessoa" name="pedido_idpessoa" vnulo cbvalue="" value="" style="width: 45em;">
                            </td>
                        </tr>
                        <tr>
                            <td nowrap align="right">Nat. Oper.:</td>
                            <td>
                                <select name="_dev_i_nf_idnatop">
                                    <option value=""></option>
                                    <? fillselect("SELECT n.idnatop,concat ( n.natop,' - CFOP [',GROUP_CONCAT(c.cfop , ''),']') as natop
                                            FROM natop n  join cfop c on(c.idnatop = n.idnatop   " . getidempresa('c.idempresa', 'natop') . " )
                                           where n.status='ATIVO'
                                            " . getidempresa('n.idempresa', 'natop') . "
                                            group by n.idnatop order by natop", 266); ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="adicionarNfs"></div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">Iten(s)</div>
                <div class="panel-body">

                    <table class="table table-striped planilha" id='itensdev'>
                        <tr>
                            <th>Selecionar</th>
                            <th style="text-align: right !important;">Pedido</th>
                            <th style="text-align: right !important;">Qtd</th>
                            <th style="text-align: center !important;">Un</th>
                            <th>Produto</th>
                            <th>Data Envio</th>
                        </tr>
                        <?
                        while ($rowx = mysqli_fetch_assoc($resx)) {
                            $i = $i + 1;
                            ?>
                            <tr class="respreto">
                                <td><input class="chec<?=$rowx["idnf"]; ?>" title="Gerar" type="checkbox" checked="" name="<?= $i ?>_nfitem_idnfitem" idnfitem="<?=$rowx["idnfitem"]; ?>"></td>
                                <td align="right" class="idLista" idnf="<?=$rowx['idnf'] ?>_<?=$rowx['envio']?>">                                    
                                    <a class="pointer" onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$rowx['idnf'] ?>')">
                                        <?=$rowx['idnf'] ?>
                                    </a>
                                </td>
                                <td align="right">
                                    <?= number_format(tratanumero($rowx['qtd']), 2, ',', '.'); ?>
                                </td>
                                <td align="center"><?=$rowx['un'] ?></td>
                                <td><?=$rowx['descr'] ?></td>
                                <td><?=dma($rowx['envio']) ?></td>
                            </tr>
                        <? } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        jCliF = <?= $jCliF ?>; // autocomplete cliente
        idClienteFaturamento = '<?=$idClienteFaturamento?>';
        //mapear autocomplete de clientes
        jCliF = jQuery.map(jCliF, function(o, id) {
            return {
                "label": o.nome,
                value: id + "",
                "tipo": o.tipo
            }
        });

        //autocomplete de clientes
        $("[name*=pedido_idpessoa]").autocomplete({
            source: jCliF,
            delay: 0,
            select: function(event, ui) {debugger;
                preencheendereco(ui.item.value);
            },
            create: function() {debugger
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.tipo + "</span></a>").appendTo(ul);
                };

                //let encontrado = jCliF.find(item => item.value === idClienteFaturamento);
                //if(encontrado != undefined) {
                //    $(".pedido_idpessoa").val(encontrado.label).attr('cbvalue', idClienteFaturamento);
                //}
            },
            focus: function(event, ui) {debugger;
                event.preventDefault();
                //$(".pedido_idpessoa").val(ui.item.label); // Exibe o nome ao navegar pelas sugestões
            }
        });

        $('#cbModal').on('shown.bs.modal', function () {
            let htmlNf = "";
            let ids = [];
            $(".idLista").each(function() {        
                // Percorre os elementos e adiciona ao array
                //let id = $(this).text().trim(); // Remove espaços extras
                let id = $(this).attr('idnf'); // Remove espaços extras
                if (!ids.includes(id)) { // Verifica se já existe no array
                    ids.push(id);
                }
            });
            console.log(ids);

            $.each(ids, function(index, elem) {
                parteselemento = elem.split("_");
                dataEnvio = parteselemento[1];
                
                htmlNf += `<div class="col-sm-3 text-right">
                        <input title="Gerar" type="checkbox" checked="" onclick="desmarcarNf(${parteselemento[0]})" style="vertical-align: center; position: relative; top: 3px; overflow: hidden; "> 
                            <label style="display: inline-block;font-size: 11px;">
                                ${parteselemento[0]} (${dataEnvio.split('-').reverse().join('/')})
                            </label>
                    </div>`;
            });

            $('.adicionarNfs').html(`<div class="row">
                                        <div class="col-md-12">
                                            <div class="panel panel-default">
                                                <div class="panel-heading">Desmarcar NF's:</div>
                                                <div class="panel-body">                                                    
                                                    ${htmlNf}
                                                </div>
                                            </div>
                                        </div>
                                    </div>`);

            let encontrado = jCliF.find(item => item.value === idClienteFaturamento);
            $(".pedido_idpessoa").val(encontrado.label).attr('cbvalue', idClienteFaturamento);
        });

        function desmarcarNf(vidnf){
            $(`.chec${vidnf}`).prop('checked', false);
        }

        //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodapehtml
    </script>
    <?

} else {
    echo ('Não encontrados itens para transferencia.');
} //if($qtdx>0){       

?>