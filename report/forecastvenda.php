<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/assinatura_controller.php");
require_once("../form/controllers/fluxo_controller.php");
require_once("../form/controllers/prodserv_controller.php");
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

$sqlhist = "select * from objetojson where idobjeto=".$_GET['idforecastvenda']." and tipoobjeto='forecastvenda' and versaoobjeto=".$_GET['versao'];
$res = d::b()->query($sqlhist) or die("Erro ao recuperar json: ".mysqli_error(d::b()));
$row = mysqli_fetch_assoc($res);
$hist = unserialize(base64_decode($row["jobjeto"]));

    $produtos["naoplanejados"]   = $hist["naoplanejados"];
    $produtos["emplanejamento"] = $hist["emplanejamento"];
    $produtos["planejados"] = $hist["planejados"];
    ?>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading">
            <? if ($_acao == 'i') { ?>
                <table>
                    <tr>
                        <td>
                            <input readonly id="idforecast" name="_1_i_forecastvenda_idforecastvenda" type="hidden" value="" readonly='readonly'>
                        </td>
                        <td>Exercício</td>
                        <td class="col-xs-10">
                            <input readonly id="Ano" name="_1_i_forecastvenda_exercicio" type="text" oninput="validateAno(this)">
                            <input readonly name="_1_i_forecastvenda_idempresa" type="hidden" value="<?= $_GET['idempresa'] ?>">
                        </td>
                    </tr>
                </table>
            <? } else { ?>
                <div class="d-flex flex-between align-items-center p-2">
                    <span class="title" style="font-size: large;">FORECAST DE VENDAS <i class="btn fa fa-info-circle"
                            title='Condições do Item: TIPO: Produto (+) TIPO DO PRODUTO: Produto Acabado (+) PERFIL: Venda "SIM"'></i>
                    </span>
                    <div>
                        <label class="alert-warning" title="<?= $_1_u_forecastvenda_exercicio ?>" id="statusButton"><?= $_1_u_forecastvenda_exercicio ?> </label>
                    <td>- Versão: </td>
                        <label class="alert-warning" id="statusButton"><?= $_GET['versao']?></label>
                    </div>
                </div>
            <? } ?>
        </div>
        <? if ($_acao != 'i') { ?>
            <div class="panel-body">
                <form id="formpesquisa">
                    <input readonly type="hidden" name="_acao" value="<?= $_GET['_acao'] ?>">
                    <input readonly type="hidden" name="_modulo" value="<?= $_GET['_modulo'] ?>">
                    <input readonly type="hidden" name="_idempresa" value="<?= $_GET['_idempresa'] ?>">
                    <input readonly type="hidden" name="idforecastvenda" value="<?= $_GET['idforecastvenda'] ?>">
                    <input readonly type="hidden" name="exercicio" value="<?= $_1_u_forecastvenda_exercicio ?>">
                    <input readonly type="hidden" name="especies" value="<?= $_GET['especies'] ?? "" ?>">
                    <input readonly type="hidden" name="produtos" value="<?= $_GET['tiprodutospos'] ?? "" ?>">
                    <input readonly type="hidden" name="tipos" value="<?= $_GET['tipos'] ?? "" ?>">

                    <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastvenda_idempresa?>" name="_1_<?=$_acao ?>_forecastvenda_idempresa">
                    <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastvenda_idforecastvenda?>" name="_1_<?=$_acao ?>_forecastvenda_idforecastvenda">
                    <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastvenda_exercicio?>" name="_1_<?=$_acao ?>_forecastvenda_exercicio">
                    <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastvenda_status?>" name="_1_<?=$_acao ?>_forecastvenda_status">
                    <input readonly class="size8" type="hidden" value="<?=$_1_u_forecastvenda_versao?>" name="_1_<?=$_acao ?>_forecastvenda_versao">
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
    require '../form/viewCriadoAlterado.php';
}

function carregaForecast($tagCollapse, $produtos, $quantidade, $titulo)
{ ?>
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading <?= $tagCollapse ?>">
                <a class="d-flex p-2 justify-content-between align-items-center text-center" data-toggle='collapse' data-target='#<?= $tagCollapse ?>' aria-expanded='false' aria-controls='<?= $tagCollapse ?>'>
                    <div class="d-flex justify-content-between text-center">
                        <span style="font-size:1.5rem"> <?= $titulo ?> <span> <?= $quantidade ?> </span></span>
                    </div>
                    <i class="mr-2 fa fa-arrows-v"></i>
                </a>
            </div>
            <div class="panel-body collapse">
                <div class="table-responsive form-label">
                    <table class="table table-bordered">
                        <?php
                        foreach ($produtos as $key => $categoria) {
                            $count = array_key_last($categoria['produtos']) + 1;
                            echo "<tr>
                                        <td class='subgrupo' colspan='17'> 
                                            <a class='px-3 w-100 d-flex justify-content-between align-items-center'  data-toggle='collapse' data-target='#{$tagCollapse}_collapse_{$categoria['idtipoprodserv']}' aria-expanded='false' aria-controls='{$tagCollapse}_collapse_{$categoria['idtipoprodserv']}'>
                                                <b>{$categoria['tipoprodserv']} <span id='{$tagCollapse}_count_{$count}'>{$count}</span></b>
                                                <i class='mr-2 fa fa-arrows-v'></i>
                                            </a>
                                        ";
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
                                                    <!--<th style='text-align:center; width:8%'>AJUSTE (%) <i class='btn fa fa-info-circle' title='Somente pode ser preenchido depois de todos os meses cadastrados'></i></th>-->
                                                </tr>
                                            </thead>
                                            <tbody>";
                            foreach ($categoria['produtos'] as $key => $produto) {
                                echo "
                                        <tr class='esconderdiv '>
                                            <td>{$produto['plantel']}</td>
                                            <td><a target='_blank' title='Abrir Produto'>{$produto['descrcurta']}</a></td>
                                            <td>{$produto['rotulo']}</td>
                                            <td style='text-align:center'>{$produto['un']}
                                    ";

                                $produto_json = json_decode($produto['produto_json'], true);
                                foreach ($produto_json['meses'] as $dado) {
                                    echo "<td>
                                                <input readonly value='{$dado['planejado']}'
                                                    oninput=\"enablesave('{$dado['mes']}', '{$produto['idprodserv']}', '{$dado['acao']}', '{$produto['idprodservformula']}', this)\"
                                                    data-idplanejamentoprodserv='{$dado['idplanejamentoprodserv']}'
                                                    data-idprodservformula='{$produto['idprodservformula']}' style='text-align: center;'>
                                                <div id='input-container-{$dado['mes']}{$produto['idprodserv']}{$produto['idprodservformula']}_{$dado['acao']}'></div>
                                            </td>";
                                    /* if ($dado['mes'] == '12') {
                                            echo "<td><input readonly name='_{$dado['mes']}{$produto['idprodserv']}{$produto['idprodservformula']}_{$dado['acao']}_planejamentoprodserv_adicional' value='{$dado['adicional']}' disabled='disabled' style='text-align: center;'></td>";
                                        } */
                                }
                                echo "</td>
                                    </tr>";
                            }
                            echo '</tbody>
                                    </table>
                                    </div>
                                </tr>
                                ';
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?}?>
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
    </script>
<? } ?>