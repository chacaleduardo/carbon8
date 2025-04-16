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
$pagvaltabela = "bannerlogin";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idbannerlogin" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from " . _DBAPP . ".bannerlogin where idbannerlogin = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
require_once("../inc/php/controlevariaveisgetpost.php");
require_once(__DIR__ . "/controllers/conciliacaofinanceira_controller.php");
require_once(__DIR__ . "/controllers/bannerlogin_controller.php");

$arquivos = [];
$banners = [];
if ($_1_u_bannerlogin_idbannerlogin) {
    $lancamentoArquivo = ConciliacaoFinanceiraController::buscarArquivoPorTipoObjetoEIdObjeto($_1_u_bannerlogin_idbannerlogin, 'bannerlogin', true);
    $banners = BannerLoginController::buscarBannersPorIdBannerLogin($_1_u_bannerlogin_idbannerlogin);
}

?>
<style>
    .diveditor {
        border: 1px solid gray;
        background-color: white;
        color: black;
        font-family: Arial, Verdana, sans-serif;
        font-size: 10pt;
        font-weight: normal;
        width: 800px;
        height: 260px;
        word-wrap: break-word;
        overflow: auto;
        padding: 5px;
    }

    .desabilitado {
        background-color: #ece5e5 !important;
    }

    .row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .form-control {
        height: 34px !important;
    }

    .img-block,
    .img-block-mobile {
        overflow-y: auto;
        max-height: 400px;
    }

    .dz-details {
        display: none !important;
    }
</style>
<link rel="stylesheet" href="/form/css/bannerlogin_css.css?version=1.0" />
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="w-100 d-flex row">
                    <!-- Id empresa -->
                    <input type="hidden" class="form-control" name="_1_<?= $_acao ?>_bannerlogin_idempresa" value="<?= $_1_u_bannerlogin_idempresa ?? cb::idempresa() ?>">
                    <!-- ID -->
                    <? if ($_1_u_bannerlogin_idbannerlogin) { ?>
                        <div class="col-xs-2 form-group">
                            <label for="" class="text-white">ID</label>
                            <label for="" class="alert-warning d-flex align-items-center form-control"><?= $_1_u_bannerlogin_idbannerlogin ?></label>
                            <input type="hidden" class="form-control" name="_1_<?= $_acao ?>_bannerlogin_idbannerlogin" value="<?= $_1_u_bannerlogin_idbannerlogin ?>">
                        </div>
                    <? } ?>
                    <!-- Titulo (OBS) -->
                    <div class="col-xs-3 form-group">
                        <label for="" class="text-white">Descrição</label>
                        <input type="text" class="form-control" name="_1_<?= $_acao ?>_bannerlogin_titulo" value="<?= $_1_u_bannerlogin_titulo ?>">
                    </div>
                    <!-- Data Inicio -->
                    <div class="col-xs-3 form-group">
                        <label for="" class="text-white">Data de inicio</label>
                        <input id="data-inicio" class="form-control calendario" name="_1_<?= $_acao ?>_bannerlogin_datainicio" value="<?= $_1_u_bannerlogin_datainicio ?>" vnulo>
                    </div>
                    <!-- Data fim -->
                    <div class="col-xs-3 form-group">
                        <label for="" class="text-white">Data de fim</label>
                        <input id="data-fim" class="form-control calendario" name="_1_<?= $_acao ?>_bannerlogin_datafim" value="<?= $_1_u_bannerlogin_datafim ?>">
                    </div>
                    <!-- Status -->
                    <div class="col-xs-1 form-group ml-auto">
                        <label for="" class="text-white">Status</label>
                        <select id="" class="form-control w-100" name="_1_<?= $_acao ?>_bannerlogin_status" value="<?= $_1_u_bannerlogin_status ?>">
                            <option value="ATIVO" <?= $_1_u_bannerlogin_status == 'ATIVO' ? 'selected' : '' ?>>Ativo</option>
                            <option value="INATIVO" <?= $_1_u_bannerlogin_status == 'INATIVO' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                </div>
            </div>
            <? if ($_1_u_bannerlogin_idbannerlogin) { ?>
                <div class="panel-body">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-xs-12 col-md-6 d-flex">
                                <!-- Desktop -->
                                <div class="col-xs-12 col-md-6">
                                    <h3 title="Dimensão recomendada">Imagens desktop (750 X 750)</h3>
                                    <hr>
                                    <div class="w-100 d-flex flex-wrap border rounded py-1 px-3 my-4 img-block">
                                        <? if (count($banners['desktop'])) { ?>
                                            <? foreach ($banners['desktop'] as $banner) { ?>
                                                <div class="w-100 d-flex my-3 flex-between align-items-center img-item">
                                                    <div class="img rounded pointer" data-titulo="<?= $banner['nomeoriginal'] ?>" data-img="<?= $banner['caminho'] ?>" style="background-image: url(<?= $banner['caminho'] ?>);"></div>
                                                    <h4 class="title" title="<?= $banner['nomeoriginal'] ?>"><?= $banner['nomeoriginal'] ?></h4>
                                                    <i class="fa fa-trash fa-2x pointer" onclick="removerBanner(<?= $banner['idarquivo'] ?>)"></i>
                                                </div>
                                                <hr>
                                            <? } ?>
                                        <? } else { ?>
                                            <h4 class="nenhuma-img">Nenhuma imagem cadastrada.</h4>
                                        <? } ?>
                                    </div>
                                    <label for="" id="input-banner" class="btn btn-primary rounded text-light mr-3 d-inline-flex">
                                        Adicionar <i class="ml-2 fa fa-plus"></i>
                                    </label>
                                </div>
                                <!-- Mobile -->
                                <div class="col-xs-12 col-md-6">
                                    <h3 title="Dimensão recomendada">Imagens mobile (390x224)</h3>
                                    <hr>
                                    <div class="w-100 d-flex flex-wrap border rounded py-1 px-3 my-4 img-block-mobile">
                                        <? if (count($banners['mobile'])) { ?>
                                            <? foreach ($banners['mobile'] as $banner) { ?>
                                                <div class="w-100 d-flex my-3 flex-between align-items-center img-item">
                                                    <div class="img rounded pointer" data-titulo="<?= $banner['nomeoriginal'] ?>" data-img="<?= $banner['caminho'] ?>" style="background-image: url(<?= $banner['caminho'] ?>);"></div>
                                                    <h4 class="title" title="<?= $banner['nomeoriginal'] ?>"><?= $banner['nomeoriginal'] ?></h4>
                                                    <i class="fa fa-trash fa-2x pointer" onclick="removerBanner(<?= $banner['idarquivo'] ?>)"></i>
                                                </div>
                                                <hr>
                                            <? } ?>
                                        <? } else { ?>
                                            <h4 class="nenhuma-img">Nenhuma imagem cadastrada.</h4>
                                        <? } ?>
                                    </div>
                                    <label for="" id="input-banner-mobile" class="btn btn-primary rounded text-light mr-3 d-inline-flex">
                                        Adicionar <i class="ml-2 fa fa-plus"></i>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <? } ?>
        </div>
    </div>
</div>
<?
$_disableDefaultDropzone = true;
$tabaud = "arquivo"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';

require_once(__DIR__ . "/js/bannerlogin_js.php");
?>