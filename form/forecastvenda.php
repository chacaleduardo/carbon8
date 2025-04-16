<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("controllers/assinatura_controller.php");
require_once("controllers/fluxo_controller.php");
require_once("controllers/prodserv_controller.php");
if ($_POST) {
    require_once("../inc/php/cbpost.php");
}

$_1_u_forecastvenda_idforecastvenda = $_GET['idforecastvenda'];

if (!function_exists('array_key_last')) {
    function array_key_last(array $array)
    {
        if (!empty($array)) {
            return key(array_slice($array, -1, 1, true));
        }
        return null;
    }
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "forecastvenda";
$pagvalcampos = array(
    "idforecastvenda" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from {$pagvaltabela} where id{$pagvaltabela} = #pkid";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if ($_acao == 'u') {
    [$produtos["naoplanejados"], $naoplanejados]   = ProdServController::buscaDadosProdutoForecast($_GET["_idempresa"], $_GET['tipos'], $_GET['especies'], $_GET['produtos'], $_1_u_forecastvenda_exercicio, 1);
    [$produtos["emplanejamento"], $emplanejamento] = ProdServController::buscaDadosProdutoForecast($_GET["_idempresa"], $_GET['tipos'], $_GET['especies'], $_GET['produtos'], $_1_u_forecastvenda_exercicio, 2);
    [$produtos["planejados"], $planejados]         = ProdServController::buscaDadosProdutoForecast($_GET["_idempresa"], $_GET['tipos'], $_GET['especies'], $_GET['produtos'], $_1_u_forecastvenda_exercicio, 3);
} ?>

<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <? if ($_acao == 'i') { ?>
                <table>
                    <tr>
                        <td>
                            <input id="idforecast" name="_1_i_forecastvenda_idforecastvenda" type="hidden" value="" readonly='readonly'>
                        </td>
                        <td>Exercício: </td>
                        <td class="col-xs-10">
                            <input id="Ano" name="_1_i_forecastvenda_exercicio" type="text" oninput="validateAno(this)">
                            <input name="_1_i_forecastvenda_idempresa" type="hidden" value="<?= $_GET['idempresa'] ?>">
                        </td>
                    </tr>
                </table>
            <? } else { ?>
                <div class="d-flex flex-between align-items-center p-2">
                    <span class="title" style="font-size: large;">FORECAST DE VENDAS <i class="btn fa fa-info-circle"
                            title='Condições do Item: <?= ((cb::idempresa() == 1) ? ('TIPO: Serviço (+) PERFIL: Venda "SIM"') : ('TIPO: Produto (+) TIPO DO PRODUTO: Produto Acabado (+) PERFIL: Venda "SIM"')) ?> '></i>
                    </span>
                    <div>
                        <td>Exercício: </td>
                        <label class="alert-warning" title="<?= $_1_u_forecastvenda_exercicio ?>" id="statusButton"><?= $_1_u_forecastvenda_exercicio ?> </label>
                        <td>Versão: </td>
                        <label class="alert-warning" title="<?= $_1_u_forecastcompra_versao ?>" id="statusButton"><?= (isset($_1_u_forecastvenda_versao) ? $_1_u_forecastvenda_versao : 'NA') ?></label>
                    </div>
                </div>
            <? } ?>
        </div>
        <? if ($_acao != 'i') { ?>
            <div class="panel-body">
                <form id="formpesquisa">
                    <input type="hidden" name="_acao" value="<?= $_GET['_acao'] ?>">
                    <input type="hidden" name="_modulo" value="<?= $_GET['_modulo'] ?>">
                    <input type="hidden" name="_idempresa" value="<?= $_GET['_idempresa'] ?>">
                    <input type="hidden" name="idforecastvenda" value="<?= $_GET['idforecastvenda'] ?>">
                    <input type="hidden" name="exercicio" value="<?= $_1_u_forecastvenda_exercicio ?>">
                    <input type="hidden" name="especies" value="<?= $_GET['especies'] ?? "" ?>">
                    <input type="hidden" name="produtos" value="<?= $_GET['tiprodutospos'] ?? "" ?>">
                    <input type="hidden" name="tipos" value="<?= $_GET['tipos'] ?? "" ?>">

                    <input class="size8" type="hidden" value="<?= $_1_u_forecastvenda_idempresa ?>" name="_1_<?= $_acao ?>_forecastvenda_idempresa">
                    <input class="size8" type="hidden" value="<?= $_1_u_forecastvenda_idforecastvenda ?>" name="_1_<?= $_acao ?>_forecastvenda_idforecastvenda">
                    <input class="size8" type="hidden" value="<?= $_1_u_forecastvenda_exercicio ?>" name="_1_<?= $_acao ?>_forecastvenda_exercicio">
                    <input class="size8" type="hidden" value="<?= $_1_u_forecastvenda_status ?>" name="_1_<?= $_acao ?>_forecastvenda_status">
                    <input class="size8" type="hidden" value="<?= $_1_u_forecastvenda_versao ?>" name="_1_<?= $_acao ?>_forecastvenda_versao">

                    <div class="form-group col-md-6 col-lg-2">
                        <label for="tipo">Tipo (Subcategoria): </label>
                        <select id="tipo" onchange="$(`input[name='tipos']`).val($('#tipo').selectpicker('val')?.toString()??'');"
                            class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                            <? fillselect("
                                    SELECT t.idtipoprodserv, t.tipoprodserv FROM tipoprodserv t 
                                    WHERE t.status = 'ATIVO' AND t.idempresa = " . cb::idempresa()  . " 
                                    AND exists( 
                                        SELECT 1 FROM laudo.prodserv p
                                        WHERE
                                        p.status = 'ATIVO'
                                        AND p.venda = 'Y'" . ((cb::idempresa() == 1) ? (" AND p.tipo = 'SERVICO'") : (" AND p.produtoacabado = 'Y' AND p.tipo='PRODUTO' ")) . "
                                        AND p.idempresa = " . cb::idempresa()  . "
                                        AND p.idtipoprodserv = t.idtipoprodserv
                                    )
                                    GROUP BY t.tipoprodserv 
                                    ORDER BY t.tipoprodserv");
                            ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6  col-lg-2">
                        <label for="especie">Espécie:</label>
                        <select id="especie" onchange="$(`input[name='especies']`).val($('#especie').selectpicker('val')?.toString()??'');"
                            class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                            <? fillselect("
                                    SELECT pl.idplantel, pl.plantel
                                    FROM laudo.prodserv p 
                                    JOIN laudo.prodservformula pf on p.idprodserv = pf.idprodserv and pf.status='ATIVO'
                                    join laudo.plantel pl on pl.idplantel = pf.idplantel and pl.status='ATIVO'
                                    WHERE p.status = 'ATIVO' AND p.venda = 'Y'  AND p.produtoacabado = 'Y'
                                    AND pl.idempresa = " . cb::idempresa()  . "
                                    group by idplantel
                                    ORDER BY pl.plantel;");
                            ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6  col-lg-5">
                        <label for="produto"><?= cb::idempresa() == 1 ? "Serviço:" : "Produto:" ?></label>
                        <select id="produto" onchange="$(`input[name='produtos']`).val($('#produto').selectpicker('val')?.toString()??'');"
                            class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                            <?
                            fillselect("
                                SELECT p.idprodserv, concat(t.tipoprodserv,' - ',SUBSTRING(p.descr, 1, 60))
                                FROM prodserv p 
                                INNER JOIN tipoprodserv t ON t.idtipoprodserv = p.idtipoprodserv
                                WHERE p.status = 'ATIVO' AND p.venda = 'Y'" . ((cb::idempresa() == 1) ? (" AND p.tipo = 'SERVICO'") : (" AND p.produtoacabado = 'Y' AND p.tipo='PRODUTO' ")) .
                                " AND p.idempresa = " . cb::idempresa()  . "
                                AND NOT EXISTS ( 
                                    select 1 from planejamentoprodserv ps 
                                    where p.idprodserv = ps.idprodserv 
                                    and ps.exercicio = " . $_1_u_forecastvenda_exercicio . "
                                )
                                ORDER BY p.descr;");
                            ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6  col-lg-3 pt-4 mt-1">
                        <div class="d-flex justify-content-end">
                            <label>&nbsp</label>
                            <button id="cbPesquisar" type="submit" class="btn btn-primary mx-3" onclick="$('#formpesquisa').submit()">
                                <span class="fa fa-search"></span> Pesquisar
                            </button>
                            <button class="btn btn-default" onclick="limparFiltros()">Limpar</button>
                            <button onclick="linkAbrirRelatorio(this, 370, {'idempresa':<?= $_1_u_forecastvenda_idempresa ?>, 'exercicio':<?= $_1_u_forecastvenda_exercicio ?>});" type="button" class="btn btn-default ml-3 align-items-center" style="display: inline-flex;"><i class="fa fa-bar-chart m-0"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        <? } ?>
    </div>
</div>

<? if ($_acao != 'i') {
    carregaForecast('naoplanejados', $produtos['naoplanejados'], $naoplanejados, 'SEM FORECAST');
    carregaForecast('emplanejamento', $produtos['emplanejamento'], $emplanejamento, 'FORECAST PARCIAL');
    carregaForecast('planejado', $produtos['planejados'], $planejados, 'FORECAST TOTAL');

    $_disableDefaultDropzone = true; // plara não mostrar a opcão de upload do dropzone
    $tabaud = "forecastvenda"; //pegar a tabela do criado/alterado em antigo
    require 'viewCriadoAlterado.php';
}

function carregaForecast($tagCollapse, $produtos, $quantidade, $titulo)
{ ?>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading <?= $tagCollapse ?>">
                <a href="#<?= $tagCollapse ?>" class="d-flex p-2 justify-content-between align-items-center text-center" data-toggle='collapse' data-target='#<?= $tagCollapse ?>' aria-expanded='false' aria-controls='<?= $tagCollapse ?>'>
                    <div class="d-flex justify-content-between text-center">
                        <span style="font-size:1.5rem"> <?= $titulo ?> [<span> <?= $quantidade ?> </span>]</span>
                    </div>
                    <? if ($titulo == 'SEM FORECAST') { ?>
                        <input type="text" onclick="event.preventDefault();event.stopPropagation();" class="w-30 form-control tipotext" autocomplete="off" id="inputFiltro" placeholder="Filtre por Especie, Nome, Fórmula ou Unidade">
                    <? } ?>
                    <i class="mr-2 fa fa-arrows-v"></i>
                </a>
            </div>
            <div class="panel-body collapse" id="<?= $tagCollapse ?>">
                <div class="table-responsive form-label">
                    <table class="table table-bordered">
                        <?php
                        foreach ($produtos as $key => $categoria) {
                            $count = array_key_last($categoria['produtos']) + 1;
                            echo "<tr>
                                        <td class='subgrupo' colspan='17'> 
                                            <a href='#{$tagCollapse}_collapse_{$categoria['idtipoprodserv']}' class='px-3 w-100 d-flex justify-content-between align-items-center'  data-toggle='collapse' data-target='#{$tagCollapse}_collapse_{$categoria['idtipoprodserv']}' aria-expanded='false' aria-controls='{$tagCollapse}_collapse_{$categoria['idtipoprodserv']}'>
                                                <b>{$categoria['contaitem']} - {$categoria['tipoprodserv']} [<span id='{$tagCollapse}_count_{$count}'>{$count}</span>]</b>
                                                <i class='mr-2 fa fa-arrows-v'></i>
                                            </a>";
                            echo "<div class='collapse' id='{$tagCollapse}_collapse_{$categoria['idtipoprodserv']}'>
                                        <table class='table table-bordered collapse mb-0'>
                                            <thead>
                                                <tr>
                                                    <th style='text-align:center; width:5%'>ESPÉCIE</th>
                                                    <th style='text-align:center; width:15%'>PRODUTO</th>
                                                    <th style='text-align:center; width:8%'>FÓRMULA</th>
                                                    <th style='text-align:center; width:5%'>UN</th>
                                                    <th style='text-align:center; width:5%'>JAN</th>
                                                    <th style='text-align:center; width:5%'>FEV</th>
                                                    <th style='text-align:center; width:5%'>MAR</th>
                                                    <th style='text-align:center; width:5%'>ABR</th>
                                                    <th style='text-align:center; width:5%'>MAI</th>
                                                    <th style='text-align:center; width:5%'>JUN</th>
                                                    <th style='text-align:center; width:5%'>JUL</th>
                                                    <th style='text-align:center; width:5%'>AGO</th>
                                                    <th style='text-align:center; width:5%'>SET</th>
                                                    <th style='text-align:center; width:5%'>OUT</th>
                                                    <th style='text-align:center; width:5%'>NOV</th>
                                                    <th style='text-align:center; width:5%'>DEZ</th>
                                                    <th style='text-align:center; width:5%'>Ticket</th>
                                                    <!--<th style='text-align:center; width:8%'>AJUSTE (%) <i class='btn fa fa-info-circle' title='Somente pode ser preenchido depois de todos os meses cadastrados'></i></th>-->
                                                </tr>
                                            </thead>
                                            <tbody>";
                            foreach ($categoria['produtos'] as $key => $produto) {
                                echo "
                                        <tr class='esconderdiv '>
                                            <td>{$produto['plantel']}</td>
                                            <td><a href='?_modulo=prodserv&_acao=u&idprodserv={$produto['idprodserv']}' target='_blank' title='Abrir Produto'>" . (strlen($produto['descrcurta']) > 1 ? $produto['descrcurta'] : $produto['descr']) . "</a></td>
                                            <td>{$produto['rotulo']}</td>
                                            <td style='text-align:center'>{$produto['un']}";

                                $produto_json = json_decode($produto['produto_json'], true);
                                foreach ($produto_json['meses'] as $dado) {
                                    echo "<td>
                                                <input value='{$dado['planejado']}'
                                                    oninput=\"enablesave('{$dado['mes']}', '{$produto['idprodserv']}', '{$dado['acao']}', '{$produto['idprodservformula']}', this)\"
                                                    data-idplanejamentoprodserv='{$dado['idplanejamentoprodserv']}'
                                                    data-idprodservformula='{$produto['idprodservformula']}' style='text-align: center;'>
                                                <div id='input-container-{$dado['mes']}{$produto['idprodserv']}{$produto['idprodservformula']}_{$dado['acao']}'></div>
                                            </td>";
                                    if ($dado['mes'] == '12') {
                                        echo "<td><input name='_{$dado['mes']}{$produto['idprodserv']}{$produto['idprodservformula']}_{$dado['acao']}_planejamentoprodserv_vlrticketmedio' value='{$dado['vlrticketmedio']}' disabled='disabled' style='text-align: center;'></td>";
                                    }
                                    /* if ($dado['mes'] == '12') {
                                            echo "<td><input name='_{$dado['mes']}{$produto['idprodserv']}{$produto['idprodservformula']}_{$dado['acao']}_planejamentoprodserv_adicional' value='{$dado['adicional']}' disabled='disabled' style='text-align: center;'></td>";
                                        } */
                                }
                                echo "</td>
                                    </tr>";
                            }
                            echo '</tbody>
                                    </table>
                                    </div>
                                </tr>';
                        } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
<? } ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Histórico</div>
            <div class="panel-body">
                <table style="width: 100%;" class="planilha grade compacto">
                    <tr>
                        <th><b>Versões</b></th>
                        <th><b>Criado Por</b></th>
                        <th><b>Criado Em</b></th>
                        <th><b>Alterado Por</b></th>
                        <th><b>Alterado Em</b></th>
                    </tr>
                    <? if (1 == 1) { ?>
                        <tr>
                            <td style="width: 30%;">Versão: <?= $_1_u_forecastvenda_versao ?>.0</td>
                        </tr>
                    <? }

                    $_listarHistorico = PrProcController::buscarObjetoPorTipoObjeto($_1_u_forecastvenda_idforecastvenda, 'forecastvenda');
                    foreach ($_listarHistorico as $historico) {
                        $rc = unserialize(base64_decode($historico["jobjeto"]));

                        if (($rc['forecastcompra']) || ($historico['versaoobjeto'] == $_1_u_forecastvenda_versao && ($_1_u_forecastvenda_status == 'REVISAO' || $_1_u_forecastvenda_status == 'AGUARDANDO'))) {
                            continue;
                        } ?>
                        <tr class="res">
                            <td nowrap><a href="report/forecastvenda.php?idforecastvenda=<?= $historico['idobjeto'] ?>&versao=<?= $historico['versaoobjeto'] ?>" target="_blank">Versão: <?= $historico['versaoobjeto'] ?>.0</a></td>
                            <td style="line-height: 1.5;"><?= nl2br($historico['criadopor']) ?></td>
                            <td style="line-height: 1.5;"><?= dmahms(nl2br($historico['criadoem'])) ?></td>
                            <td style="line-height: 1.5;"><?= nl2br($historico['alteradopor']) ?></td>
                            <td style="line-height: 1.5;"><?= dmahms(nl2br($historico['alteradoem'])) ?></td>
                        </tr>
                    <? } ?>
                </table>
            </div>
        </div>
    </div>
</div>
<? if ($_GET['_acao'] != 'i') { ?>
    <script>
        getProdutos = async function() {
            await $.ajax({
                type: "get",
                url: "ajax/forecastvenda.php",
                data: {
                    exercicio: <?= $_1_u_forecastvenda_exercicio ?>,
                    idempresa: <?= $_1_u_forecastvenda_idempresa ?>
                },
                success: function(data) {
                    $("#produto").html(data);
                    $("#cbCarregando").hide();
                },
                error: function(objxmlreq) {
                    alert('Erro:<br>' + objxmlreq.status);
                }
            });
        }
        //função para enviar somente o que for alterado.
        function enablesave(mes, produto, acao, idprodservformula, element) {
            const idempresa = '<?= $_GET["_idempresa"] ?>';
            const exercicio = '<?= $_1_u_forecastvenda_exercicio ?>';

            // Verifique se os dados é válido
            if (!mes || !produto || !acao || !element) {
                console.error("Parâmetros inválidos fornecidos para enablesave.");
                return;
            }

            // Verifique se o contêiner já existe, se não, crie um novo
            let inputContainer = document.getElementById(`input-container-${mes}${produto}${idprodservformula}_${acao}`);
            if (!inputContainer) {
                inputContainer = document.createElement('div');
                inputContainer.id = `input-container-${mes}${produto}${idprodservformula}_${acao}`;
                inputContainer.className = `allinput`;
                element.parentNode.insertBefore(inputContainer, element.nextSibling);

                //preciso dropar o container e criar um outro com update
            }

            // Limpe os inputs antigos no contêiner
            inputContainer.innerHTML = '';

            // Defina os dados dos novos inputs
            const inputs = [{
                    name: `idplanejamentoprodserv`,
                    value: element.dataset.idplanejamentoprodserv || ''
                },
                {
                    name: `idempresa`,
                    value: idempresa
                },
                {
                    name: `idprodserv`,
                    value: produto
                },
                {
                    name: `idprodservformula`,
                    value: element.dataset.idprodservformula || ''
                },
                {
                    name: `exercicio`,
                    value: exercicio
                },
                {
                    name: `mes`,
                    value: mes
                },
                {
                    name: `status`,
                    value: 'CRIADO'
                },
                {
                    name: `planejado`,
                    value: element.value,
                    id: `value${mes}${produto}${idprodservformula}_${acao}`
                }
            ];

            // Crie e adicione os inputs ao contêiner
            inputs.forEach(input => {
                const inputElement = document.createElement('input');
                inputElement.type = 'hidden';
                inputElement.name = `_${mes}${produto}${idprodservformula}_${acao}_planejamentoprodserv_${input.name}`;
                inputElement.value = input.value;
                if (input.id) {
                    inputElement.id = input.id;
                }
                inputContainer.appendChild(inputElement);
            });

            // Habilite os inputs recém-criados
            inputContainer.querySelectorAll('input').forEach(input => input.disabled = false);

            console.log("Inputs criados e habilitados com sucesso:", inputContainer);

            $(`input[name^="_${mes}${produto}${idprodservformula}_${acao}_planejamentoprodserv_adicional"]`).removeAttr('disabled');
            $(`input[name^="_${mes}${produto}${idprodservformula}_${acao}_planejamentoprodserv_vlrticketmedio"]`).removeAttr('disabled');
        }

        function limparFiltros() {
            $('.selectpicker').selectpicker('val', []);
            $('#filters input').val('');
        }

        getProdutos();

        $(document).ready(function() {

            tipos = '<?= $_GET['tipos'] ?? "" ?>';
            produtos = '<?= $_GET['produtos'] ?? "" ?>';
            especies = '<?= $_GET['especies'] ?? "" ?>';

            $('#tipo').selectpicker('val', tipos?.split(','));
            $('#produto').selectpicker('val', produtos?.split(','));
            $('#especie').selectpicker('val', especies?.split(','));

            $("#inputFiltro").on("keyup", function() {
                // Captura o valor do input, normaliza e remove caracteres especiais
                var value = $(this).val().toLowerCase()
                    .normalize("NFD").replace(/[^\w\s]/g, "");

                // Filtra as linhas da tabela
                $(".table tbody tr.esconderdiv").each(function() {
                    let seletor = $(this).attr("data-text");

                    // Se não houver data-text, usa o texto visível da linha
                    if (!seletor) {
                        seletor = $(this).text();
                    }

                    seletor = seletor.toLowerCase().normalize("NFD").replace(/[^\w\s]/g, "");

                    // Exibe ou oculta a linha com base no valor do filtro
                    $(this).toggle(seletor.indexOf(value) > -1);
                });
                // Conta as linhas visíveis
                const count = $(".table tbody tr:visible").length;

                // Exibe o total de resultados visíveis
                $("#resultadoCount").text(`${count} Resultados encontrados`);
            });
        });

        //para não dar refresh na página depois do ctrl+s
        CB.on("prePost", function(inParam) {
            if (inParam === undefined) {
                inParam = {
                    objetos: {},
                    parcial: false,
                    refresh: false
                };
            }
            return inParam;
        });

        //limpar os input com insert ou update
        CB.on("posPost", function(inParam) {
            setTimeout(() => {}, 2000);
            $('.allinput').html('');
            alertAtencao('Atualizando página por favor aguarde.');
            //vamos dar um time para dar tempo de ler a informação
            setTimeout(() => {}, 5000);
            location.reload(true);
        });


        $(document).ready(function() {

            $("#inputFiltro").on("keyup", function() {
                // abri os collapse  qdo começar a filtrar
                $('.collapse').collapse('show');

                var value = $(this).val().toLowerCase()
                    .normalize("NFD").replace(/[^\w\s]/g, "");

                $(".table tbody tr.esconderdiv").each(function() {
                    let seletor = $(this).attr("data-text");

                    if (!seletor) {
                        seletor = $(this).text();
                    }

                    seletor = seletor.toLowerCase().normalize("NFD").replace(/[^\w\s]/g, "");

                    $(this).toggle(seletor.indexOf(value) > -1);
                });

                const count = $(".table tbody tr:visible").length;
                $("#resultadoCount").text(`${count} Resultados encontrados`);
            });

            // qdo apagar tudo volta ao normal
            $("#inputFiltro").after('<button id="clearFilter" class="btn btn-sm btn-default ml-2">Limpar</button>');

            $("#clearFilter").on("click", function() {
                $("#inputFiltro").val('').trigger('keyup');
                $('.collapse').collapse('hide');
            });
        }); // Add this debounce function before the document ready function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        $(document).ready(function() {

            const debouncedFilter = debounce(function() {
                var value = $(this).val().toLowerCase()
                    .normalize("NFD").replace(/[^\w\s]/g, "");

                $('.collapse').collapse('show');

                $(".table tbody tr.esconderdiv").each(function() {
                    let seletor = $(this).attr("data-text");

                    if (!seletor) {
                        seletor = $(this).text();
                    }

                    seletor = seletor.toLowerCase().normalize("NFD").replace(/[^\w\s]/g, "");

                    $(this).toggle(seletor.indexOf(value) > -1);
                });

                const count = $(".table tbody tr:visible").length;
                $("#resultadoCount").text(`${count} Resultados encontrados`);
            }, 300); // 300ms delay

            $("#inputFiltro").on("keyup", debouncedFilter);

        });

        // Debounce para não travar a tela
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>

    <style>
        .scroll-offset {
            padding-top: 30px;
            /* Ajuste a altura conforme necessário */
            margin-top: -30px;
            /* Ajuste a altura conforme necessário */
        }

        td {
            vertical-align: middle !important;
        }

        td:has(input) {
            padding: 0px !important;
            border: 0px solid #cccccc;
            border-radius: 0px;
            background-color: #fff !important;
        }

        td input {
            width: 100%;
            border: 0px solid #cccccc !important;
            border-radius: 0px !important;
            text-align: center;
            min-height: 50px;
            max-height: 50px;
        }

        .row {
            margin-left: 0px !important;
            margin-right: 0px !important;
        }

        td.subgrupo {
            cursor: pointer;
            text-align: left;
            background-color: rgb(205 205 205 / 30%) !important;
        }

        td.subgrupo a {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }

        .table {
            background-color: whitesmoke !important;
        }

        .table.collapse {
            border-style: hidden;
        }

        td.subgrupo:hover {
            background-color: rgb(205 205 205 / 50%);
        }

        #resultadoCount {
            font-size: 14px;
            display: flex;
            align-items: center;
            color: #333;
        }

        .naoplanejados {
            background-color: #f0bfbf !important;
        }

        .emplanejamento {
            background-color: rgb(238 161 5 / 40%) !important;
        }

        .planejado {
            background-color: rgb(105 183 105 / 40%) !important;
        }

        .panel-body {
            padding-top: 10px !important;
        }

        table>thead>tr.active>th {
            background-color: #f5f5f5;
            padding: 3px;
            vertical-align: sub;
        }

        .justify-content-between {
            justify-content: space-between;
        }

        a i.fa-arrows-v {
            margin-right: 0.5rem !important;
            font-size: medium;
            align-self: center;
            margin-bottom: 2px;
        }
    </style>
<? } else {
    //vamos validar os anos que pode ser inseridos no campo de input
?>
    <script>
        function validateAno(input) {
            const forbiddenValues = [<?= ProdServController::buscaforecastcriado(cb::idempresa()) ?>]; // Valores proibidos
            const value = $(input).val().trim(); // Obtém o valor do input e remove espaços em branco

            if (forbiddenValues.includes(value)) {
                alertAtencao("Ano " + value + " já cadastrado. Não é possível repetir.");
                $(input).val(''); // Limpa o campo
            }
        }

        function ReadonlyStatus() {

            if ($('.status-input').val() === 'APROVADO') {

                $('input').attr('readonly', true);
            } else {

                $('input').removeAttr('readonly');
            }
        }
        $(document).ready(function() {
            ReadonlyStatus();
        });
    </script>
<? } ?>