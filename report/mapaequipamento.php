<?
    require_once("../inc/php/functions.php");
    if ($_POST) {
        include_once("../inc/php/cbpost.php");
    }

    // QUERYS
    require_once(__DIR__."/../form/querys/_iquery.php");
    require_once(__DIR__."/../form/querys/tag_query.php");

    // CONTROLLERS
    require_once(__DIR__."/../form/controllers/tag_controller.php");

    /*
    * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
    * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
    *                pk: indica parâmetro chave para o select inicial
    *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
    */
    $pagvaltabela = "mapaequipamento";
    $pagvalmodulo=$_GET['_modulo'];
    $pagvalcampos = array(
        "idmapaequipamento" => "pk"
    );
    /*
    * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
    */
    $pagsql = "select * from mapaequipamento where idmapaequipamento = '#pkid'";

    $idBlocoViaGet = $_GET['idbloco'];
    $idSalaViaGet  = $_GET['idsala'];
    $idEquipamentoViaGet = $_GET['idequipamento'];

    $blocosAtivos = TagController::buscarTagsPorIdClassificacao();
    $blocosAtivosJSON = json_encode($blocosAtivos);

    $tags = json_encode(TagController::buscarTagsAtivas());
?>

<!-- Css -->
<link rel="stylesheet" href="/../form/css/mapaequipamento_css.css" type="text/Css" />

<!-- TODO: Atualizar bootstrap -->
<main>
    <div class="row">
        <div class="col-xs-12 d-flex">
            <h2>Mapa de equipamentos</h2>
            <h2 id="bloco-title" class="ml-2"></h2>
        </div>
    </div>
    <div class="row mt-3">
        <!-- TODO:
            Atualizar planta no change
            Carregar blocos (tags)
        -->
        <!-- Selecionar bloco -->
        <div class="col-xs-12 col-md-3">
            <label for="blocos">Selecionar bloco</label>
            <div class="form-group">
                <input id="blocos" type="text" class="form-control" title="Selecionar bloco" placeholder="Selecionar bloco" />
            </div>
        </div>
        <!-- Adicionar equipamento ( tag ) -->
        <!-- <div class="col-xs-12 col-md-3 tag-input hidden">
            <label for="select-bloco">Adicionar equipamento</label>
            <div class="form-group">
                <input id="tag" type="text" class="form-control" title="Selecionar equipamento" placeholder="Selecionar equipamento" />
            </div>
        </div> -->
        <!-- Unidade de medida -->
        <div class="col-xs-12 col-md-3 hidden un-input">
            <label for="un">Unidade de medida</label>
            <div class="form-group">
                <select id="un" type="text" class="form-control" title="Selecionar unidade de medida" placeholder="Selecionar unidade de medida" data-actions-box="true" multiple>
                    <option value="t">TEMPERATURA</option>
                    <option value="p">PRESSÃO</option>
                    <option value="u">UMIDADE</option>
                    <option value="d">DIFERENCIAL</option>
                </select>
            </div>
        </div>
        <!-- Filtrar por tipo -->
        <div class="col-xs-12 col-md-3 hidden tipo-input">
            <label for="tipo">Filtrar por tipo</label>
            <div class="form-group position-relative d-flex align-items-center">
                <select id="tipo" type="text" class="form-control selectpicker" title="Filtrar por tipo" placeholder="Filtrar por tipo" data-actions-box="true" data-live-search="true" multiple>
                    <? fillselect(TagController::buscarTiposAtivosDeEquipamentos(true)) ?>
                </select>
                <!-- <button id="clear-filter" class="h-100 text-center hidden d-flex align-items-center justify-content-center">
                    <i class="fa fa-times-circle mr-auto text-danger"></i>
                </button> -->
            </div>
        </div>
        <!-- Filtrar por tag -->
        <div class="col-xs-12 col-md-3 tipo-input">
            <label for="tipo">Filtrar por TAG</label>
            <div class="form-group position-relative d-flex align-items-center">
                <input id="campo-tag-filtro" type="text" class="form-control" placeholder="TAG" title="Filtrar por TAG" />
            </div>
        </div>
    </div>
    <div id="zoom-area" class="row mt-5 justify-content-center position-relative mb-5">
        <div class="col-xs-12 main-content svg-area d-flex justify-content-center">
            <!-- Tag's (Equipamentos) -->
            <div class="items">
                <!-- <div class="item z-index-main ignore-custom-ui" title="teste">
                    <i class="fa fa-user-circle"></i>
                </div> -->
            </div>
        </div>
        <!-- ID mapaequipamento -->
        <input id="input-idmapaequipamento" type="text" class="hidden" value="" name="_x_u_mapaequipamento_idmapaequipamento" />
        <!-- Json -->
        <input id="input-json" type="text" class="hidden" value="" name="_x_u_mapaequipamento_json" />
        <!-- Ações -->
        <div class="d-flex align-items-center map-header hidden">
            <div class="d-inline mr-2">
                <!-- Mostrar/Ocultar planta -->
                <button id="btn-show-hide-room" class="active-eye" title="Mostrar / Ocultar planta">
                    <i class="fa fa-eye"></i>
                </button>
            </div>
            <!-- Definir sala -->
            <div class="d-inline mr-2">
                <!-- Mostrar titulo das salas -->
                <button class="info">
                    <i class="fa fa-info fa-2x" title="Mostrar/Ocultar informações"></i>
                </button>
            </div>
            <!-- Remover sala -->
            <!-- <div class="d-inline mx-2">
                <button id="btn-remove-room" class="btn btn-danger" title="Remover">
                    <i class="fa fa-eraser"></i>
                </button>
                <button id="btn-finish-room" class="btn btn-success hidden" title="Concluído">
                    <i class="fa fa-check"></i>
                </button>  
            </div> -->
            <!-- Salvar -->
            <!-- <div class="d-inline">
                <button id="btn-save" class="btn btn-success d-flex justify-content-center align-items-center" title="Salvar Layout Equipamentos">
                    <i class="fa fa-save"></i>
                </button>
            </div> -->
            <!-- Mover sala -->
            <!-- <div class="d-inline">
                <button id="btn-move-room" class="btn btn-warning">Mover sala</button>
                <button id="btn-finish-room" class="btn btn-success hidden">Concluído</button>  
            </div> -->
        </div>
        <!-- Ferramentas -->
        <div class="tools hidden">
            <div class="open-close-tools d-flex justify-content-center align-items-center opened">
                <i class="fa fa-navicon"></i>
            </div>
            <ul class="tools-list">
                <!-- Selecionar / Cancelar definicao de sala -->
                <li class="tool-item">
                    <button id="btn-cancel-room" title="Seleção">
                        <i class="fa fa-mouse-pointer"></i>
                    </button>
                </li>
                <!-- Definir sala: -->
                <li class="tool-item">
                    <button id="btn-create-room" title="Desenhar sala">
                        <i class="fa fa-pencil"></i>
                    </button>
                </li>
                <!-- Mostrar / Ocultar planta -->
                <!-- <li class="tool-item">
                    <button id="btn-show-hide-room" title="Mostrar / Ocultar planta">
                        <i class="fa fa-eye"></i>
                    </button>
                </li> -->
                <!-- Criar quadrado -->
                <li class="tool-item">
                    <button id="btn-create-square" title="Criar Sala">
                        <i class="fa fa-square-o"></i>
                    </button>
                </li>
                <!-- Cores -->
                <ul class="color-palette"></ul>
            </ul>
        </div>
    </div>
</main>

<? require(__DIR__."/../form/js/mapaequipamento_js.php"); ?>