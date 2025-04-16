<?
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/permissao.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}

require_once(__DIR__ . "/controllers/plantel_controller.php");

$idunidadepadrao = getUnidadePadraoModulo($_GET["_modulo"], cb::idempresa());

if (!$idunidadepadrao) {
    echo '<div class="alert alert-warning" role="alert" style="text-transform:uppercase;margin-top:20px" >
			<div class="row">
				<div class="col-md-12"><b>Módulo Padrão não configurado. Entre em contato com o Administrador do Sistema.
				</div>
			</div>
		</div>';
    die();
}

?>

<link rel="stylesheet" href="/form/css/controlevencimentolotes_css.css" />
<div id="controle-lote-vencimento">
    <div class="row">
        <div class="col-xs-12">
            <div class="panel panel-default">
                <div class="panel-heading row">
                    <h2 class="col-xs-12 font-bold">Lotes a vencer</h2>
                </div>
                <div class="panel-body">
                    <div class="row col-xs-12 d-flex flex-wrap align-items-center">
                        <!-- Acao -->
                        <input name="_acao" type="hidden" value="u"/>
                        <!-- Data de vencimento -->
                        <div class="col-xs-12 col-md-2 form-group">
                            <label for="">Data de vencimento</label>
                            <select name="" id="data-vencimento" class="form-control">
                                <option value="0">Vencidos</option>
                                <option value="30">Até 30 dias</option>
                                <option value="60">Até 60 dias</option>
                                <option value="90">Até 90 dias</option>
                                <option value="120">Até 120 dias</option>
                                <option value="150">Até 150 dias</option>
                                <option value="180">Até 180 dias</option>
                            </select>
                        </div>
                        <!-- Unidade -->
                        <div class="col-xs-12 col-md-2 form-group">
                            <label for="">Unidade</label>
                            <select name="" id="unidade" class="form-control selectpicker" multiple>
                                <option value="11">Retém</option>
                                <option value="290">Logística</option>
                            </select>
                        </div>
                        <button id="btn-buscar" class="btn btn-primary rounded text-light mr-4">
                            <i class="fa fa-search text-white m-0"></i>
                        </button>
                        <button id="btn-retirar" class="btn btn-primary rounded text-light font-bold">
                            Retirar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="lotes" class="col-xs-12 hide">
            <div class="col-xs-12 border">
                <h3><span id="qtd-registros">0</span> registro(s) encontrado(s)</h3>
                <hr />
                <table id="tabela-produtos" class="table table-hover table-striped table-condensed">
                    <thead class="text-uppercase">
                        <tr class="text-center">
                            <th>Empresa</th>
                            <th>Id lote</th>
                            <th>Cliente</th>
                            <th>Produto</th>
                            <th>Partida interna</th>
                            <th>partida</th>
                            <th>Exercício</th>
                            <th>Vencimento</th>
                            <th>Estoque</th>
                            <th>Descrição</th>
                            <th>
                                <input type="checkbox" name="" id="check-todos" />
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-uppercase"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>
<? require_once(__DIR__ . '/js/controlevencimentolotes_js.php') ?>