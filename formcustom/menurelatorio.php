<?
require_once("../inc/php/validaacesso.php");
require_once(__DIR__."/../form/controllers/menurelatorio_controller.php");
?>
<link rel="stylesheet" href="./inc/css/dashboard.css"/>
<link rel="stylesheet" href="./inc/css/menurelatorio.css"/>

<script src="./inc/js/amcharts/amcharts.js"></script>
<script src="./inc/js/amcharts/serial.js"></script>
<script src="./inc/js/amcharts/pie.js"></script>

<?
function formatarArrModulosUsuario(){
    $modulosUsuario = getModsUsr('MODULOS');

    $virgulaP = ""; $virgulaF = ""; $virgulaFV = "";
    $modsPai = ""; $modsFilho = ""; $modsFilhoVinc = "";
    
    foreach($modulosUsuario as $m => $value){
        switch($value["tipo"]){
            case 'DROP':
                $modsPai .=  $virgulaP."'".$m."'"; 
                $virgulaP = ",";
                break;
            case 'BTINV':
                $modsFilhoVinc .=  $virgulaFV."'".$m."'"; 
                $virgulaFV = ",";
                break;
            case 'LINK':
            case 'MODVINC':
                $modsFilho .=  $virgulaF."'".$m."'"; 
                $virgulaF = ",";
                break;
            default: break;
        }
    }

    return [
        "modulosPai" => $modsPai,
        "modulosFilhos" => $modsFilho,
        "modulosFilhosV" => $modsFilhoVinc,
    ];
}

$mods = formatarArrModulosUsuario();

if($_GET['_menulateral'] != 'N' || !isset($_GET['_menulateral'])){
    

    $jsonReps = json_encode(MenuRelatorioController::buscarRelatoriosPorLps(
        $mods['modulosPai'], $mods['modulosFilhos'], $mods['modulosFilhosV'], cb::idempresa(), getModsUsr("LPS"))
    );

    $userPref = MenuRelatorioController::buscarPreferenciaPessoa($_GET["_modulo"], $_SESSION["SESSAO"]["IDPESSOA"]);
}else{
    $jsonReps = json_encode(MenuRelatorioController::buscarRelatoriosPorLps(
        $mods['modulosPai'], $mods['modulosFilhos'], $mods['modulosFilhosV'], cb::idempresa(), getModsUsr("LPS"), 'N', "WHERE u.idrep IN (".$_GET["_idrep"].")"
    ));
    $userPref = '{}';
}

$logoSistema = MenuRelatorioController::buscarLogoRelatorioBase64(cb::idempresa());

if(!empty($logoSistema)){?>
    <div id="logo_relatorio" class="hidden">
        <img src="data:image/png;base64,<?=$logoSistema?>"/>
    </div>
<?}?>

<script>
    var jsonReps = <?=$jsonReps?>;
    var userPref = <?=$userPref?>;
    var idEmpresaGlobal = <?=cb::idempresa()?>;
    var filtrosAtivos = {};
    var parametrosGet = <?=json_encode($_GET)?>;

    var modPref;
    var idRepTipoPref;
    var idRepPref;

    (function(){
        constroiModulos();

        modPref = Object.keys(userPref)[0];

        if(!modPref) return;

        idRepTipoPref = Object.keys(userPref[modPref])[0];

        if(!idRepTipoPref) return;

        if(isNaN(idRepTipoPref)) {
            CB.setPrefUsuario('d', `${CB.modulo}`);
            return;
        }

        idRepPref = Object.keys(userPref[modPref][idRepTipoPref])[0];

        $(`#mod_${modPref}`).click();
        buscaRelatorios($(`#mod_${modPref}_grp${idRepTipoPref}`)[0], true);
    })();

    (function(){

        if(parametrosGet["_menulateral"] != 'N') return;

        let mod = Object.keys(jsonReps)[0];

        if(!mod) return;

        let idreptipo = Object.keys(jsonReps[mod]['tiporep'])[0];
        
        buscaRelatorios($(`#mod_${mod}_grp${idreptipo}`)[0]);

    })();
    

    function constroiModulos () {
        let displayMenuLateral = (parametrosGet["_menulateral"] != 'N')
                ? "col-md-2"
                : "hidden";

        let colMd = (parametrosGet["_menulateral"] != 'N')
            ? "col-md-10"
            : "col-md-12";

        let filtroRel = $(`
            <div class="row">
                <div title="Voltar para o Topo" id="rollUp" class="pull-end " style="z-index: 1000; display: none; position: fixed; bottom: 8px; right: 8px; font-size: 20px; color: green; cursor: pointer;">
                    <i class="fa fa-arrow-circle-up" aria-hidden="true"></i>
                </div>
                <div class="${displayMenuLateral}" style="max-width:210px" id="displayBarraLateral">
                    <div class="panel panel-default" id="menufiltro">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="nowrap" style="text-align: center">
                                    <div class="col-md-12">
                                        <a title="Mostrar Menu de Relatórios" onclick="listaRel()" class="tipoItem pointer list-group-item" style="font-size: 10px; width: 100%; padding: 5px;">
                                            RELATÓRIO
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div style="text-align:center;">
                                <input onchange="reportSearch(this)" id="_relSearch" style="width:78%; margin-top:-25px;" type="text" placeholder="Buscar Relatório" class="form-control"/>
                                <button title="Buscar Relatório" onclick="reportSearch($('#_relSearch')[0])" style="margin-top: -25px; margin-left: -15px;" class="btn btn-primary btn-sm">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div style="display:none;padding: 5px 20px;" class="col-md-12" id="grupoSearch"></div> 
                            <div class="col-md-12" id="gruposRel"></div> 
                        </div>
                    </div>
                </div>
                <div class="${colMd}" id="displayRelatorio" style="margin-top:23px;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="no-print" id="lst_rel" style="">
                                <div id="lista_relatorios">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div  id="imp_rep"  class="row">
                        <div class="col-md-12">
                            <div id="_chart_control_" class="no-print" style="display:none; border-radius:4px; border: 1px solid rgb(221, 221, 221); background-color: #f5f5f5; height:66px;"></div>                                    
                            <div id="chartdiv" style="display:none;text-align: -webkit-center; width:100%;height:500px;"></div>
                            <div id="btns" class="no-print" style="display:none; border-radius:4px; border: 1px solid rgb(221, 221, 221); background-color: #f5f5f5; margin-bottom:10px; height:66px;">
                                <div style="background-color: #f5f5f5;" id="divFiltro" class="col-md-8"></div>
                                <div style="background-color: #f5f5f5;" class="col-md-4"  id="divBotao"></div>
                            </div>
                            <div id="conteudo_relatorio" style="margin-top: 20px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        `);

        let content = "";
        for(let mod in jsonReps){
            let modInfo = jsonReps[mod];
            let tipoRepContent = "";

            let arrAux = gerarArrayObjOrdenadoPorChave(modInfo.tiporep, 'reptipo');

            for (let o of arrAux){
                tipoRepContent += `
                    <div class="nowrap" style="text-align: left">
                        <div style="margin: 5px;">
                            <a class="pointer list-group-item text-uppercase inicio inative" id="mod_${mod}_grp${o.idreptipo}" mod="${mod}" reptipo="${o.idreptipo}" style="font-size: 9px; width: 100%; padding: 5px; white-space: normal;" onclick="buscaRelatorios(this)">
                                ${o.reptipo}
                            </a>
                            <div id="mod_${mod}_relatorios${o.idreptipo}" style="margin-top:5px"></div>
                        </div>
                    </div>`;
            }

            content += `
                <div class="nowrap" style="text-align: center">
                    <div class="col-md-12">
                        <a class="pointer list-group-item text-uppercase menusuperior" id="mod_${mod}" style="font-size: 10px; width: 100%; padding: 5px; white-space: normal;" idmodulopesq="${modInfo.idmodulopesq}" onclick="hideShowRepTipos(this)">
                            ${modInfo.rotulomenupesq}
                        </a>
                        <div id="tiposrelatorios${modInfo.idmodulopesq}" style="margin-top:5px;display:none;">
                            ${tipoRepContent}
                        </div>
                    </div>
                </div>`;
        }

        filtroRel.find("#gruposRel").append(content);
        
        $("#cbModuloForm").append(filtroRel);
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

    // Registros do etl
    function expandirLinha(element) {

        if(!$(element).hasClass('carregado')) {

            $(element).find('> div:first-child span').addClass('carregando-linha');
            $(element).find('> div:first-child span i').css('opacity', '0');

            $.ajax({
                type: 'get',
                url: '../ajax/_buscaetl.php',
                data: {
                    idetl: $(element).data('idetl'),
                    _idrep: $(element).data('idrep')
                },
                dataType: 'json',
                success: res => {
                    if(Object.values(res).length)
                    {
                        $(element).next().append(mountEtlItem(Object.values(res)));
                    }

                    if($(element))
                    {
                        $(element).next().toggleClass('hidden')
                        $(element).toggleClass('active-row');
                        $('.abrir-fechar-todos').find('button').text('Fechar Todos');
                    } else {return;}

                    if(!$('.res .active-row').length)
                    {
                        $('.abrir-fechar-todos').find('button').text('Abrir Todos');
                    }

                    $(element).addClass('carregado');
                    $(element).find('> div:first-child span').removeClass('carregando-linha');
                    $(element).find('> div:first-child span i').css('opacity', '1');
                }
            });
        } else {

            if(!$(element)) return;

            $(element).next().toggleClass('hidden')
            $(element).toggleClass('active-row');
            $('.abrir-fechar-todos').find('button').text('Fechar Todos');

            if(!$('.res .active-row').length){
                $('.abrir-fechar-todos').find('button').text('Abrir Todos');
            }
        }
    }

    function mountEtlItem(data){
        let divEtlItem = '';

        data.forEach(value => {
            let valor = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value.valor);

            divEtlItem += `
                <div class='row col-12'>
                    <div class='col-sm-2' style='padding-left: 3rem;'>
                        <a class='pointer' onclick=\"janelamodal('${value.url}')\" >${value.id}</a>
                    </div>
                    <div class='col-sm-6' style='padding-left: 3rem;'>
                        <span>${value.nome}</span>
                    </div>
                    <div class='col-sm-2' style='padding-left: 3rem;'>
					    <span>${value.dmadata}</span>
				    </div>
                    <div class='col-sm-2' style='padding-left: 3rem;'  align='right'>
                        <span>${valor}</span>
                    </div>
                </div>
            `;
        });

        return divEtlItem;
    }

    // Items do relatorio Etl
    function abrirFecharTodos(btn) {
        if($('.res .active-row').length)
        {
            $('.res > div').each(function()
            {
                $(this).removeClass('active-row');
                if($(this).next())
                {
                    $(this).next().addClass('hidden');
                }
            });

            $(btn).text('Abrir Todos');
        } else 
        {
            $('.res > div:first-child').each(function(){
                expandirLinha($(this).first()[0]);
            })
        }
    }

    async function buscarRepConf ( idrep ) {
        let idempresa = getUrlParameter('_idempresa') ? "&_idempresa="+getUrlParameter('_idempresa') : "";
        let response = await fetch(`ajax/buscarelatoriomodulonovo.php?idrep=${idrep}${idempresa}`);
        try{
            let json = await response.json();

            if(json["erro"]){
                //@TODO: melhorar a mensagem de erro
                console.warn(json["erro"]);
                return false;
            }

            return json;
        }catch(e){
            //@TODO: melhorar a mensagem de erro
            console.warn(e);
            return false;
        }
        
    }

    //busca os relatorios ligados a cada modulo, com uma funcao async
    //faz apenas uma requisicao, busca todas as informações, e quando clicar novamente a informação ja está salva, nao sendo necessário outra requisicao
    async function buscaRelatorios(vthis, preferencia = false){

        vthis = $(vthis);

        let mod = vthis.attr('mod');
        let idreptipo = vthis.attr('reptipo');

        if(vthis.hasClass("inicio")){
            $(`[id=${vthis.attr('id')}]`).addClass("carregando").removeClass("inicio");

            let reps = jsonReps[mod]['tiporep'][idreptipo].reps;
            let idrep = (idRepPref && preferencia) ? idRepPref : Object.keys(reps)[0];

            let repConf = await buscarRepConf(idrep);

            if(!repConf){
                $(`[id=${vthis.attr('id')}]`).addClass("carregado").removeClass("carregando");
                return false;
            }
            
            jsonReps[mod]['tiporep'][idreptipo].reps[idrep].conf = repConf;

            let abrirMenuLateral = (parametrosGet["_menulateral"] != 'N')
                ? `<i title="Ocultar Menu Lateral" style="font-size: 22px; margin-top: -5px;" id="mod_${mod}_${idreptipo}_btnEsconder" onclick="modoVisualizacao('${mod}', ${idreptipo})" class="fa fa-angle-left fa-2x cinzaclaro hoverpreto pointer" aria-hidden="true"></i>`
                : "";

            let filtros = montaFiltros(mod, idreptipo, idrep);

            let content = `
                <div id="mod_${mod}_filtro_${idreptipo}" idreptipo="${idreptipo}" class="repfiltros">
                    <div class="form-row">
                        <div class="text-uppercase tituloRep" style="color:#949494; font-size: 14px; font-weight:600; padding:10px;border-top-left-radius:4px;border-top-right-radius:4px;color: #666;background-color: #e6e6e6;">
                            <i title="Mostrar Menu Lateral" style="font-size: 22px; margin-top: -5px;display:none" id="mod_${mod}_${idreptipo}_btnMostrar" onclick="modoPesquisa('${mod}', ${idreptipo})" class="fa fa-angle-right fa-2x cinzaclaro hoverpreto pointer" aria-hidden="true"></i>
                            ${abrirMenuLateral}
                            &nbsp; &nbsp; &nbsp; &nbsp; ${jsonReps[mod]['tiporep'][idreptipo].reptipo}
                            <a id="mod_${mod}_editarrep_${idreptipo}" class="fa fa-bars fade hoverazul pull-right" "title="Editar Relatório" href="?_modulo=_rep&_acao=u&idrep=${idrep}" target="_blank"></a>
                        </div>
                        ${filtros}
                        <div class="col-md-12" id="mod_${mod}_select_${idreptipo}" style="background: #f5f5f5;padding-top:10px;">
                            <label for="mod_${mod}_select_rep_${idreptipo}" style="padding-bottom: 3px;padding-left: 10px;">
                                RELATÓRIOS
                            </label>
                            <div style="display: flex;padding-left: 10px;justify-content: space-between;align-items: center;">
                                <select style="width:30%" id="mod_${mod}_select_rep_${idreptipo}" mod="${mod}" reptipo="${idreptipo}" onchange="alterarRepFiltros(this)"> 
                `;
            
            let repsAux = gerarArrayObjOrdenadoPorChave(reps, 'rep');

            for(let o of repsAux){
                let selected = (o.idrep == idrep) ? "selected" : "";
                content += `<option value="${o.idrep}" ${selected}>${o.rep}</option>`;
            }

            let btnPsq = btnPesquisar(mod, idreptipo, idrep);

            content += `
                            </select>
                            ${btnPsq}
                        </div>
                    </div>
                    <div id="mod_${mod}_filterButtons_${idreptipo}" class="col-md-12" style="background-color:whitesmoke; text-align: center;">
                        <i title="Ocultar Filtros" onclick="ocultarFiltros('${mod}', ${idreptipo})" class="fa fa-angle-up fa-2x cinzaclaro hoverpreto pointer"></i>
                    </div>
                </div>
            `;
            
            // Esconde todos os filtros já existentes
            // e desativa todos os grupos selecionados
            $("#lista_relatorios").children().hide();
            $("#gruposRel").find(`[id*=mod_]`).not('.menusuperior').removeClass('ative').addClass('inative');
            $("#grupoSearch").find(`[id*=mod_]`).removeClass('ative').addClass('inative');

            // Adiciona e apresenta os filtros do novo grupo
            // Ativa o grupo selecionado
            $("#lista_relatorios").append(content);

            inicializarJsFiltros(mod, idrep, preferencia);

            $(`[id=${vthis.attr('id')}]`).addClass("carregado").removeClass("carregando").addClass("ative").removeClass("inative");

        }else if(vthis.hasClass("carregado")){
            // mostrar ou esconder relatorios 
            $("#lista_relatorios").children().not(`#mod_${mod}_filtro_${idreptipo}`).hide();
            $("#gruposRel").find(`[id*=mod_]`).not('.menusuperior').not(`#mod_${mod}_grp${idreptipo}`).removeClass('ative').addClass('inative');
            $("#grupoSearch").find(`[id*=mod_]`).not(`#mod_${mod}_grp${idreptipo}`).removeClass('ative').addClass('inative');
            $(`#mod_${mod}_filtro_${idreptipo}`).slideToggle("fast");

            vthis.hasClass("ative")
                ? $(`[id=${vthis.attr('id')}]`).addClass("inative").removeClass("ative")
                : $(`[id=${vthis.attr('id')}]`).addClass("ative").removeClass("inative")
        }

        $("#conteudo_relatorio").html("");
        $("#_chart_control_").html("");
        $("#_chart_control_").hide();
        $("#divFiltro").html("");
        $("#divBotao").html("");
        $("#chartdiv").html("");
        $("#chartdiv").hide();
        $('#btns').hide();
    }

    function montaFiltros (mod, idreptipo, idrep) {

        let report = jsonReps[mod]['tiporep'][idreptipo].reps[idrep];
        let content = `<div id="mod_${mod}_filters_${idrep}" class="col-md-12" style="background: #f5f5f5;">`;

        for (let col in report.conf) {
            let filtro = report.conf[col];
            let required = filtro.psqreq == "Y" ? `<span style='color:red;'>*</span>` : "";
            let rotulo = filtro.rotulo + required;
            
            if(["datetime","date"].includes(filtro.datatype)){
                
                content += `
                    <div class="col-md-2 filtershow" style="height: 70px; margin-top: 6px">
                        <label class="text-uppercase" for="mod_${mod}_input_rep${idrep}${col}">
                            ${rotulo}
                        </label>
                        <span class="input-group input-group-sm" style="position: relative; display: inline-table !important; "> 
                            <input idreptipo="${idreptipo}" idrep="${idrep}" col="${col}" obrigatorio="${filtro.psqreq}" type="text" class="form-control tipodata" autocomplete="off" id="mod_${mod}_input_rep${idrep}${col}">
                            <span class="input-group-addon" title="Limpar Data" onclick="limpaData('${mod}','${idrep}${col}')">
                                <span class="fa fa-close" style="width: 20px;"></span>
                            </span>
                        </span>
                    </div>`;

            }else if(filtro.code || filtro.json){
                try{
                    let code = JSON.parse(filtro.json || filtro.code);
                    let options = "";

                    let disabled = (
                        (filtro.psqreq == "Y" && code.length == 1) ||
                        (filtro.col == "idunidade" && report.flgunidade == "Y" && code.length == 1)
                    ) ? "disabled" : "";

                    for(let o of code){
                        let id = Object.keyAt(o, 0);
                        let value = o[id];
                        if(code.length == 1){
                            options += `<option value="${id}" selected>${value}</option>`;   
                        } else {

                            let selected = (false)? "selected" : "";
                            options += `<option value="${id}" ${selected}>${value}</option>`;
                        }
                    }

                    content += `
                        <div class="col-md-2 filtershow" style="height: 70px; margin-top: 6px">
                            <label class="text-uppercase" for="mod_${mod}_input_rep${idrep}${col}">
                                ${rotulo}
                            </label>
                            <select ${disabled} idreptipo="${idreptipo}" idrep="${idrep}" col="${col}" obrigatorio="${filtro.psqreq}" class="form-control tipojson selectpicker" multiple style="height:30px; padding:0px 5px;" id="mod_${mod}_input_rep${idrep}${col}">
                                ${options}
                            </select>
                        </div>`;

                }catch(e){
                    console.error(e);
                    content += `
                        <div class="col-md-2 filtershow" style="height: 70px; margin-top: 6px">
                            <label class="text-uppercase" for="mod_${mod}_input_rep${idrep}${col}">
                                ${rotulo}
                            </label>
                            <input idreptipo="${idreptipo}" col="${col}" idrep="${idrep}" type="text" obrigatorio="${filtro.psqreq}" class="form-control" style="border:1px solid red;" placeholder="[Erro] Falha ao ler JSON ${col}" autocomplete="off" id="mod_${mod}_input_rep${idrep}${col}">
                        </div>`;
                }
                
            } else if(filtro.entre == 'Y'){
                content += `
                    <div class="col-md-2 filtershow" style="height: 70px; margin-top: 6px">
                        <label class="text-uppercase" for="mod_${mod}_input_rep${idrep}${col}">
                            ${rotulo}
                        </label>
                        <div class="form-control" style="background: #eee; color: #949494 !important; height: 30px !important;">
                            <input idreptipo="${idreptipo}" idrep="${idrep}" obrigatorio="${filtro.psqreq}" col="${col}" class="faixa_entre1" name='input_rep${idrep}${col}_1' id='mod_${mod}_input_rep${idrep}${col}_1' type='text' cbcolentre='mod_${mod}_input_rep${idrep}${col}_1' style="width: 43%; margin-top: -7px !important; margin-left: -9px;">
                            <input type="text" style="width: 17%;margin-top: -7px !important;margin-left: -5px;text-align: center;background-color: #eee;" placeholder="e" disabled="">
                            <input idreptipo="${idreptipo}" idrep="${idrep}" obrigatorio="${filtro.psqreq}" col="${col}" class="faixa_entre2" name='input_rep${idrep}${col}_2' id='mod_${mod}_input_rep${idrep}${col}_2' type='text' cbcolentre='mod_${mod}_input_rep${idrep}${col}_2' style="width: 43%; margin-top: -7px !important; margin-left: -5px;"> 
                        </div>                                 
                    </div>`;

            } else if(["varchar","int","longtext","mediumtext","text"].includes(filtro.datatype)){
                content += `
                    <div class="col-md-2 filtershow" style="height: 70px; margin-top: 6px">
                        <label class="text-uppercase" for="mod_${mod}_input_rep${idrep}${col}">
                            ${rotulo}
                        </label>
                        <input idreptipo="${idreptipo}" idrep="${idrep}" col="${col}" type="text" obrigatorio="${filtro.psqreq}" class="form-control" autocomplete="off" id="mod_${mod}_input_rep${idrep}${col}">
                    </div>`;
            }
        }

        content += `</div>`;

        return content;
    }

    function inicializarJsFiltros( mod, idrep, preferencia = false ) {

        $(`input.tipodata[id*=mod_${mod}_input_rep${idrep}]`).each(function(i, o){

            $(o).daterangepicker({
                "showDropdowns": true,
                "minDate": moment("01012006", "DDMMYYYY"),
                "locale": CB.jDateRangeLocale,
                ranges: {
                    'Hoje': [moment(), moment()],
                    'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Amanhã': [moment().add(1, 'days'), moment().add(1, 'days')],
                    'Esta Semana': [moment().subtract(new Date().getDay(), 'days'), moment().endOf('week')],
                    'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
                    'Próximos 7 dias': [moment(), moment().add(6, 'days')],
                    'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
                    'Próximos 30 dias': [moment(), moment().add(29, 'days')],
                    'Este mês': [moment().startOf('month'), moment().endOf('month')],
                    'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Próximo mês': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
                    'Este Ano':  [moment().startOf('year'), moment().endOf('year')],
                    'Ano passado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                    'Próximo Ano': [moment().add(1, 'year').startOf('year'), moment().add(1, 'year').endOf('year')]
                },
                opens: 'center'
            }).on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('').attr('cbdata', '').addClass('cinzaclaro');
                setFiltro(this);
                CB.oDaterangeTexto.html("");
                CB.oDaterange.find('#cbCloseDaterange').off('click').addClass('hide');
                CB.limparResultados = true;
                CB.resetVarPesquisa();
            }).on('apply.daterangepicker', function(ev, picker) {
                setFiltro(this);
                CB.setIntervaloDataPesquisa(picker.startDate, picker.endDate);
                CB.limparResultados = true;
                CB.resetVarPesquisa();
            });
        });

        $(`select.tipojson[id*=mod_${mod}_input_rep${idrep}]`).selectpicker({
            selectAllText: '<span class="glyphicon glyphicon-check"></span>',
            deselectAllText: '<span class="glyphicon glyphicon-remove"></span>',
            dropupAuto: false, // sempre abrir o dropdown p/ baixo
            liveSearch: true, // habilita pesquisa
            liveSearchNormalize: true, // pesquisa sem distinção de acentos
            actionsBox: true, // habilitar os botões de selecionar/desmarcar tudo
            selectedTextFormat: 'count > 1',
            countSelectedText: '{0} Selecionados'
        }).ready();

        $(`select.tipojson[id*=mod_${mod}_input_rep${idrep}]`).on('change', function(){
            setFiltro(this);
        });

        if(preferencia){
            let filtros = userPref[modPref][idRepTipoPref][idRepPref];

            for(let col in filtros){
                if($(`#mod_${mod}_input_rep${idrep}${col}`).hasClass('tipojson')){
                    $(`#mod_${mod}_input_rep${idrep}${col}`).selectpicker('val', filtros[col].split(','));
                }else{
                    $(`#mod_${mod}_input_rep${idrep}${col}`).val(filtros[col]);
                }
                setFiltro($(`#mod_${mod}_input_rep${idrep}${col}`)[0]);
            }
        }

        $(window).scrollTop(0);

        if(parametrosGet["_menulateral"] != 'N') return;

        for(let col in parametrosGet){

            if($(`#mod_${mod}_input_rep${idrep}${col}`).length == 0 && col != '_fds') continue;

            if($(`#mod_${mod}_input_rep${idrep}${col}`).hasClass('tipojson')){
                $(`#mod_${mod}_input_rep${idrep}${col}`).selectpicker('val', parametrosGet[col].split(','));
            }else if(col == '_fds'){
                $(`[id*=mod_${mod}_input_rep${idrep}].tipodata`).val(parametrosGet[col]);
            }

        }

        $(`#mod_${mod}_btnExtrairRelatorio`).click();
    }

    function setFiltro ( vthis ) {
        let vth         = $(vthis);
        let col         = vth.attr('col');
        let idreptipo   = vth.attr('idreptipo');

        if(!filtrosAtivos[idreptipo]){
            filtrosAtivos[idreptipo] = {
                [col] : vth.val()
            };
        }else{
            filtrosAtivos[idreptipo][col] = vth.val();
        }
    }

    async function alterarRepFiltros ( vth ) {
        let idrep = vth.value;
        let mod = $(vth).attr('mod');
        let idreptipo = $(vth).attr('reptipo');

        $(`#mod_${mod}_editarrep_${idreptipo}`).attr('href', `?_modulo=_rep&_acao=u&idrep=${idrep}`);

        $(`#mod_${mod}_filtro_${idreptipo} [id*=mod_${mod}_filters_]`).hide();
        $(`[id*=btnpsq_mod_${mod}_${idreptipo}]`).hide();

        if($(`#mod_${mod}_filters_${idrep}`).length > 0){

            $(`#mod_${mod}_filters_${idrep}`).show();
            $(`#btnpsq_mod_${mod}_${idreptipo}_${idrep}`).show();

        }else{

            let repConf = await buscarRepConf(idrep);

            if(!repConf) return false;
                
            jsonReps[mod]['tiporep'][idreptipo].reps[idrep].conf = repConf;

            let filtros = montaFiltros(mod, idreptipo, idrep);

            let btnPsq = btnPesquisar(mod, idreptipo, idrep);

            $(filtros).insertBefore(`#mod_${mod}_select_${idreptipo}`);
            $(btnPsq).insertAfter(`#mod_${mod}_select_rep_${idreptipo}`);

            inicializarJsFiltros(mod, idrep);
        }

        for (let col in filtrosAtivos[idreptipo]) {
            let value = filtrosAtivos[idreptipo][col];
            let typeValue = typeof value;

            switch(typeValue){
                case 'object':
                    $(`#mod_${mod}_input_rep${idrep}${col}`).selectpicker('val', filtrosAtivos[idreptipo][col]);
                    break;
                case 'string':
                    $(`#mod_${mod}_input_rep${idrep}${col}`).val(filtrosAtivos[idreptipo][col]);
                    break;
                default: break;
            }
        }

        $("#conteudo_relatorio").html("");
        $("#_chart_control_").html("");
        $("#_chart_control_").hide();
        $("#divFiltro").html("");
        $("#divBotao").html("");
        $("#chartdiv").html("");
        $("#chartdiv").hide();
        $('#btns').hide();
    }

    function btnPesquisar(mod, idreptipo, idrep){
        let report = jsonReps[mod]['tiporep'][idreptipo].reps[idrep];

        return `
            <div class="pull-right filtershow" id="btnpsq_mod_${mod}_${idreptipo}_${idrep}">
                <button id="mod_${mod}_btnExtrairRelatorio" class="btn btn-primary" onclick="extrairRelatorio('${mod}', '${idrep}','${report.url}', 'N')">
                    <i class="fa fa-search" aria-hidden="true"></i> Pesquisar
                </button>
                <button title="Guia de Impressão" class="btn btn-primary" onclick="extrairRelatorio('${mod}', '${idrep}','${report.url}', 'Y')">
                    <i class="fa fa-print" aria-hidden="true"></i>
                </button>
                <div class="pull-right" style="margin-right:09px;">
                    <input type="hidden" id="mod_${mod}_${idrep}tipograph" value="${report.tipograph}">
                </div>
            </div>`;
    }

    //funcao async que extrai o relatorio de acordo com as informaçoes selecionadas. 
    async function extrairRelatorio( mod, idrep, url, novaAba ){
        let preferenciasStr = "";
        let virg = "";
        let eCom = "";
        let camposObrigatorios = [];

        url += `?_idrep=${idrep}`;
        url += getUrlParameter('iddash') ? "&idobjeto="+getUrlParameter('iddash') : "";
        let idempresa = getUrlParameter('_idempresa') ? "&_idempresa="+getUrlParameter('_idempresa') : "";
        let url_filtros = "";
        let url_relatorio = "";
        let infopesquisa = "";
        let url_sistema = window.location.origin+"/";

        if( novaAba != "Y" ){
            $("#conteudo_relatorio").html("");
            $("#_chart_control_").html("");
            $("#_chart_control_").hide();
            $("#divFiltro").html("");
            $("#divBotao").html("");
            $("#chartdiv").html("");
            $("#chartdiv").hide();
            $('#btns').hide();
        }
        
        novaAba == "Y" ? '' : $("#conteudo_relatorio").html("<div class='loader' style='margin-left: auto;margin-right: auto'></div>");

        temgrafico = $(`#mod_${mod}_${idrep}tipograph`).val();

        CB.setPrefUsuario('d', `${CB.modulo}`, undefined, (data) => {
            $(`[id*=mod_${mod}_input_rep${idrep}]`).each(function(i, o){
                let $o = $(o);
                let col = $o.attr('col');
                let obrigatorio = $o.attr("obrigatorio");
                let vlr = ($o.val() != null) ? $o.val().toString().trim() : "";

                if( vlr != '' ){

                    url_filtros += ($o.hasClass("tipodata")) 
                        ? eCom + '_fds=' + vlr.replaceAll(" ","")
                        : eCom + col + "=" + vlr;

                    preferenciasStr += `${virg} "${col}": "${vlr}"`;
                    virg = ",";
                    eCom = "&";

                }else if( obrigatorio == 'Y'){
                    camposObrigatorios.push($o);
                }
            });


            if(camposObrigatorios.length == 0){

                let idreptipo = $(".repfiltros:visible").attr('idreptipo');
                CB.setPrefUsuario('m', `{ "${CB.modulo}" : { "${mod}" : { "${idreptipo}" : { "${idrep}" : { ${preferenciasStr} } } } } }`);
                
                if(url_filtros != ""){
                    url += "&_filtros="+btoa(url_filtros);
                }

                if(novaAba=="Y"){
                    janelamodal(url);
                } else {

                    fetchReport(url);

                    $("#btn_export").on('click', function(){
                        alert("Seu download iniciará em breve, favor aguardar");
                    });
                }

            } else {
                for(let $o of camposObrigatorios){
                    $("[data-id="+$o.attr('id')+"]").addClass("alertaCbvalidacao");
                }
                alert('Preencher Campos Obrigatórios');
            }
        });
    }

    function limpaData(mod, input) {
        let $input = $(`#mod_${mod}_input_rep${input}`);
        $input.val('').attr('cbdata', '').addClass('cinzaclaro');
        setFiltro($input[0]);
    }

    function mostrarGrafico(vt){
        let chartdiv = $("#chartdiv");
        let vthis = vt;

        if($(vthis).is('.inicial')){

            gerarGraficoMultiLinhas();
            
            $(vthis).attr("title","Esconder Gráfico");
            chartdiv.show();
            $(vthis).addClass('carregado').removeClass('inicial');

        }else if($(vthis).hasClass('carregado') && chartdiv.is(":visible")){

            // Esconde gráfico quando clicar no botão
            $(vthis).attr("title","Mostrar Gráfico");
            chartdiv.hide();
        }else if($(vthis).hasClass('carregado') && !chartdiv.is(":visible")){

            // Mostra gráfico quando clicar no botão
            $(vthis).attr("title","Esconder Gráfico");
            chartdiv.show();
        }
            
        gerarGraficoMultiLinhas()
    }

    function hideShowRepTipos ( vthis ) {
        let vth = $(vthis);
        let tiposRep = $(`#tiposrelatorios${vth.attr('idmodulopesq')}`);

        if(vth.hasClass('menuSuperioAtivo')){
            tiposRep.hide();
            vth.removeClass('menuSuperioAtivo');
        }else{
            tiposRep.show();
            vth.addClass('menuSuperioAtivo');
        }
    }

    function modoVisualizacao(mod, id){
        $(`#mod_${mod}_${id}_btnEsconder`).hide();
        $(`#mod_${mod}_${id}_btnMostrar`).show();
        $(`#displayBarraLateral`).removeAttr('class');
        $(`#displayBarraLateral`).hide()
        $(`#displayBarraLateral`).attr('class','col-md-0');
        $(`#displayRelatorio`).removeAttr('class');
        $(`#displayRelatorio`).removeAttr('class','col-md-12');
    }

    function modoPesquisa(mod, id){
        $(`#mod_${mod}_${id}_btnMostrar`).hide();
        $(`#mod_${mod}_${id}_btnEsconder`).show();
        $(`#displayBarraLateral`).removeAttr('class');
        $(`#displayBarraLateral`).attr('class','col-md-2');
        $(`#displayBarraLateral`).show()
        $(`#displayRelatorio`).attr('class');
        $(`#displayRelatorio`).attr('class','col-md-10');
    }


    function filtrarTabela(vthis){
        
        let arrXafterFilter = [];    
        var _nrow=0;
        let $firstLineTable = $($("table.table-striped tbody tr")[0]);
        var value = $('#inputFiltro').val().toLowerCase().trim();
        var arrAcSumValues = {};
        let renderizaGrafico = $('#renderizaGrafico:checked').length == 1 ? true : false;

        $('.linha_soma').remove();



        $firstLineTable.find('[acsum]').each(function(i,o){
            arrAcSumValues[$(o).attr('acsum')] = 0;
        });

        $(".table-striped .tbody .res").filter(function() {
            let $vth = $(this);
            if($vth.text().toLowerCase().indexOf(value) > -1){
                $vth.show();
            }else{
                $vth.hide();
            }
        });

        $("table.table-striped tbody tr").filter(function() {
            let $vth = $(this);
        
            if($vth.text().toLowerCase().indexOf(value) > -1){
                $vth.show();
                for(let key in arrAcSumValues){
                    let colAcsum = $vth.find(`[acsum="${key}"]`);

                    try {
                        let contentVal = $(colAcsum).attr('filtervalue').trim();
                        let valFormatado = (isNaN(contentVal)) 
                                        ? parseFloat(contentVal.replaceAll('.','').replaceAll(',','.'))
                                        : parseFloat(contentVal);  

                        arrAcSumValues[key] += valFormatado;
                
                    } catch (error) {}               
                }
                _nrow++;
                $('#nlinha').html(`${ _nrow} Registros Encontrados`);
            }else{
                $vth.hide();
            }
        });


        for(let key in arrAcSumValues){
            arrAcSumValues[key] = parseFloat(arrAcSumValues[key].toFixed(2))
                                    .toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2,  
                                        maximumFractionDigits: 2
                                    });
        }

        //console.log(arrAcSumValues);

        var tr = '<tr class="res linha_soma bottonLine"><td colspan="500" class="inv"></td></tr><tr class="linha_soma bottonLine">';

        $firstLineTable.children().each(function(i, o){       
            let acSumCol = $(o).attr('acsum') || '';
            let formatoMoeda = $(o).attr('mascara')=="MOEDA"?"R$ ":"";
            if(acSumCol != ''){
                tr +=`<td style="text-align: end;"><strong>${formatoMoeda+arrAcSumValues[acSumCol]}</strong></td>`;
            } else {
                tr +=`<td></td>`;
            }       
        });

        tr += '</tr>';

        $('table.table-striped tfoot').prepend(tr);

        if(value==''){
            $('.linha_soma').remove();
        }

        $("#conteudo_relatorio .normal").attr({'class':'table-striped'});
        $("#conteudo_relatorio td").css('border', 'solid 1px  #C0C0C0'); 
        $('table.table-striped tfoot').css('font-size','10px');



        if(renderizaGrafico){
            if(temgrafico == 'LINHA'){

                $('[eixografico="X"]:visible').each((j, k) => {        
                    arrXafterFilter.push(k.textContent.trim());
                })

                gerarGraficoMultiLinhas($('#getSeparator').val(), arrXafterFilter);

            } else if(temgrafico == 'PIZZA') {

                $('[eixografico="X"]:visible').each((j, k) => {    
                    
                    let eX = k.textContent.trim(); 
                    let eY = $(k.parentElement).find('[eixografico="Y"]:visible').attr('filtervalue').trim();
                    
                    arrXafterFilter.push({
                        'eixoX' : eX,
                        'eixoY' : eY
                    })
                })

                gerarGraficoPizza(arrXafterFilter);

            } else if(temgrafico == 'BARRASLATERAIS') {

                $('[eixografico="X"]:visible').each((j, k) => {    
                    
                    let eX = k.textContent.trim(); 
                    let eY = $(k.parentElement).find('[eixografico="Y"]:visible').attr('filtervalue').trim();
                    
                    arrXafterFilter.push({
                        'eixoX' : eX,
                        'eixoY' : eY
                    })
                })

                gerarGraficoBarrasLaterais(arrXafterFilter);

            } else if(temgrafico == 'BARRASAGRUPADAS') {

                $('[eixografico="X"]:visible').each((j, k) => {    
                    
                    let eX = k.textContent.trim(); 
                    let eY = $(k.parentElement).find('[eixografico="Y"]:visible').attr('filtervalue').trim();
                    
                    arrXafterFilter.push({
                        'eixoX' : eX,
                        'eixoY' : eY
                    })
                })

                gerarGraficoBarrasAgrupadas();

            }
        }    
    }


    function mostrarFiltros(mod, id){
        $('.filtershow').show();
        $(`#mod_${mod}_select_${id}`).show();
        $(`#mod_${mod}_filterButtons_${id}`).html("");
        $(`#mod_${mod}_filterButtons_${id}`).append(`
            <i onclick="ocultarFiltros('${mod}', ${id})" title="Ocultar Filtros"  style="font-size: 22px;" class="fa fa-angle-up fa-2x cinzaclaro hoverpreto pointer"></i>`
        );
    }

    function ocultarFiltros(mod, id){
        $('.filtershow').hide();
        $(`#mod_${mod}_select_${id}`).hide();
        $(`#mod_${mod}_filterButtons_${id}`).html("");
        $(`#mod_${mod}_filterButtons_${id}`).append(`
            <i onclick="mostrarFiltros('${mod}', ${id})" title="Mostrar Filtros"  style="font-size: 22px;" class="fa fa-angle-down fa-2x cinzaclaro hoverpreto pointer"></i>
        `);
    }

    function mudarAgrupamento(vthis){
        let separator = vthis.value;
        gerarGraficoMultiLinhas(separator);
    }


    function gerarCsv( tituloCsv ) {

        // dowload CSV de TABELAS DIVS
        if($("div.table-striped").is(":visible")){
            var CsvContent = "";
            var virg = "";
            var hd="1";
            var value="";

            //monta o header do csv        
                $(`.table-striped`).find(".row").each((i, o) => {
                    if(hd==1){
                        if($(o).is(":visible")){

                            $(o).find("div").each((j, k) => {
                                value="";
                                if($(k).text().includes("R$")){
                                value = parseFloat($(k).text().replaceAll("R$ ","").replaceAll(".","").replace(",",".")).toFixed(2)
                                }else{
                                    value = $(k).text().trim()
                                }
                                CsvContent += virg + value.trim();
                                virg = ";";
                            });
                            CsvContent += "\n";
                            virg = "";
                        }
                        hd++;
                    }
                });
            

            // monta o tbody do csv
            $(`.table-striped .res `).find(".row").each((i, o) => {
                if($(o).is(":visible")){

                    $(o).find("div").each((j, k) => {
                        value="";
                        if($(k).text().includes("R$")){
                            value = parseFloat($(k).text().replaceAll("R$ ","").replaceAll(".","").replace(",",".")).toFixed(2)
                        }else{
                            value = $(k).text().trim()
                        }
                        CsvContent += virg + value;
                        virg = ";";
                    });
                    CsvContent += "\n";
                    virg = "";
                }
            });

            tituloCsv = tituloCsv.toLowerCase().replaceAll(/[^a-zA-Z0-9]/g,'');
            let hiddenElement = document.createElement('a');
            hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(CsvContent);
            hiddenElement.target = '_blank';
            hiddenElement.download = tituloCsv+'.csv';
            hiddenElement.click();
        }


        // dowload CSV de TABELAS table
        if($("table.table-striped").is(":visible")){
            var CsvContent = "";
            var virg = "";

            $("table.table-striped").find("tr").each((i, o) => {
                if($(o).is(":visible")){

                    $(o).find("td").each((j, k) => {

                        if($(k).attr('colspan')){

                            $colspan = $(k).attr('colspan') - 1;
                            let ic = 1;

                            while (ic <= $colspan) {
                                ic++;
                                CsvContent += virg +'';
                                virg = ";";
                            }

                        }
                        value="";
                        if($(k).text().includes("R$")){
                            value = parseFloat($(k).text().replaceAll("R$ ","").replaceAll(".","").replace(",",".")).toFixed(2)
                        }else{
                            value = $(k).text().trim()
                        }
                        CsvContent += virg + value;
                        virg = ";";
                    });
                    CsvContent += "\n";
                    virg = "";

                }
            });

            tituloCsv = tituloCsv.toLowerCase().replaceAll(/[^a-zA-Z0-9]/g,'');

            let hiddenElement = document.createElement('a');
            hiddenElement.href = 'data:text/csv;charset=utf-8,' + '\ufeff' + encodeURI(CsvContent);
            hiddenElement.target = '_blank';
            hiddenElement.download = tituloCsv+'.csv';
            hiddenElement.click();
        }

        if($(".normal").is(":visible")){
            var CsvContent = "";
            var virg = "";

            $(".normal").find("tr.res").each((i, o) => {
                if($(o).is(":visible")){

                    $(o).find("td").each((j, k) => {

                        if($(k).attr('colspan')){

                            $colspan = $(k).attr('colspan') - 1;
                            let ic = 1;

                            while (ic <= $colspan) {
                                ic++;
                                CsvContent += virg +'';
                                virg = ";";
                            }

                        }
                        value="";
                        if($(k).text().includes("R$")){
                            value = parseFloat($(k).text().replaceAll("R$ ","").replaceAll(".","").replace(",",".")).toFixed(2)
                        }else{
                            value = $(k).text().trim()
                        }
                        CsvContent += virg + value;
                        virg = ";";
                    });
                    CsvContent += "\n";
                    virg = "";

                }
            });

            tituloCsv = tituloCsv.toLowerCase().replaceAll(/[^a-zA-Z0-9]/g,'');

            let hiddenElement = document.createElement('a');
            hiddenElement.href = 'data:text/csv;charset=utf-8,' + '\ufeff' + encodeURI(CsvContent);
            hiddenElement.target = '_blank';
            hiddenElement.download = tituloCsv+'.csv';
            hiddenElement.click();
        }
    }

    async function reportSearch(vth){
        let vSearch = vth.value.trim().toLowerCase();
        if (vSearch != ""){
            $("#grupoSearch").html('');

            $("#gruposRel").hide();

            $("#gruposRel .list-group-item[reptipo]").each(function(i, o){
                if(o.text.trim().toLowerCase().includes(vSearch))
                    $(o).clone().appendTo("#grupoSearch");
            });

            $("#grupoSearch").show();

        } else {
            $("#grupoSearch").hide();
            $("#grupoSearch").html('');
            $("#gruposRel").show();
            $("#_relSearch").val('');
        }

    }


    function listaRel(){
        $("#grupoSearch").hide();
        $("#grupoSearch").html('');
        $("#gruposRel").show()
        $("#_relSearch").val('');
    }


    function fetchReport(url_relatorio){
        $('#cbContainer').removeAttr('style') //esse style quebrava a pagina quando a tabela passava os 100% definidos no bootstrap
        $('#rollUp').hide();
        $.get(url_relatorio,function(report){
            
            btnGrafico= temgrafico.length>=1? `
                    <a style="background-color:white; text-align: start;" onclick="printElem('${$('.tituloRep').text().trim()}','a')" class="btn no-print" href="#"></i>Imprimir Tudo</a>
                    <a style="background-color:white; text-align: start;" onclick="printElem('${$('.tituloRep').text().trim()}','t')" class="btn no-print" href="#"></i>Imprimir Tabela</a>
                    <a style="background-color:white; text-align: start;" onclick="printElem('${$('.tituloRep').text().trim()}','g')" class="btn no-print" href="#"></i>Imprimir Gráfico</a>
            `:`     
                    <a style="background-color:white; text-align: start;" onclick="printElem('${$('.tituloRep').text().trim()}','t')" class="btn no-print" href="#"></i>Imprimir Tudo</a>
            `;
            
            //Vai pegar os elementos Html da resposta e tratar eles para exibir no módulo.
            $(".loader").addClass("hide");
            $('#conteudo_relatorio').prepend($(report).filter('.abrir-fechar-todos'));
            $('#conteudo_relatorio').prepend($(report).filter('.n_linhas'));
            $('.n_linhas').show();
            $('#conteudo_relatorio').append($(report).filter('.normal'));
            $('#conteudo_relatorio').append($(report).find('.normal'));

            //Pega relatorios feito com divs // Não esquecer de importar o <style></style>
            $('#conteudo_relatorio').append($(report).filter('.rTable'));
            $('#conteudo_relatorio').append($(report).find('.rTable'));


            $('#conteudo_relatorio').append($(report).filter('._report_amchart_'));
            $("#divBotao").append(`<div style="margin-top:15px; text-align: end;"><div class="dropdown hide">
                <a class="btn btn-success no-print hide"><i class="fa fa-print" aria-hidden="true"></i> Imprimir</a>
                <div class="dropdown-content">
                    ${btnGrafico}
                </div>
                </div> &nbsp &nbsp <a onclick="gerarCsv('${$('.ativado').text().trim()}')" class="btn btn-success no-print hide" id="btn_export" href="#"><i class="fa fa-download" aria-hidden="true"></i>  Download .CSV</a></div>`);
            $("#divFiltro").append(`<div class="col-md-10" id="_ftabela" style="margin-top:15px;"><input style="margin-right:10px;" onkeyup="filtrarTabela(this)" type="text" class="form-control tipotext" autocomplete="off" id="inputFiltro" placeholder="Filtrar Dados"></div>`)
            $("#conteudo_relatorio .header").css({'border':'1px','background-color':'rgb(239, 235, 235)','height':'30px','font-size':'11px','font-weight':'bold'});
            $("#conteudo_relatorio .normal").addClass('table-striped');
            $("#conteudo_relatorio td").css('border', 'solid 1px  #C0C0C0');     
            $("#conteudo_relatorio table").css('border-collapse', 'collapse');
            $('#btns').show();
            

            if(temgrafico == 'LINHA'){
                gerarGraficoMultiLinhas();
                $("#chartdiv").show();
                $('#_chart_control_').append($('#inputAgrupamento'));
                $('#_chart_control_').show();
                $('#inputAgrupamento').show();            
                $('#inputAgrupamento').length < 1 ? $('#_chart_control_').hide():'';
                $('#btns').css('height','83px')
                $('#_ftabela').append(`<div style="text-align:end;" class="form-check"  title="Ao marcar esta opção, o gráfico será renderizado conforme sua busca no filtro de dados. Em tabelas muito extensas o sistema pode apresentar travamentos e lentidão" >
                    <input type="checkbox" class="form-check-input" id="renderizaGrafico">
                    <label class="form-check-label" for="renderizaGrafico">Renderizar Novo Gráfico</label>
                </div>`)

            } else if (temgrafico == 'PIZZA') {
                $("#chartdiv").show();
                $('#_chart_control_').append($('#inputAgrupamento'));
                $('#_chart_control_').show();
                $('#inputAgrupamento').show();            
                $('#inputAgrupamento').length < 1 ? $('#_chart_control_').hide():'';
                $('#btns').css('height','83px')
                $('#_ftabela').append(`<div style="text-align:end;" class="form-check"  title="Ao marcar esta opção, o gráfico será renderizado conforme sua busca no filtro de dados. Em tabelas muito extensas o sistema pode apresentar travamentos e lentidão" >
                    <input onchange="filtrarTabela()" type="checkbox" class="form-check-input" id="renderizaGrafico">
                    <label style="font-size: 12px;" class="form-check-label" for="renderizaGrafico">Renderizar Novo Gráfico</label>
                </div>`)
                gerarGraficoPizza();
            } else if (temgrafico == 'BARRASLATERAIS') {
                $("#chartdiv").show();
                $('#_chart_control_').append($('#inputAgrupamento'));
                $('#_chart_control_').show();
                $('#inputAgrupamento').show();            
                $('#inputAgrupamento').length < 1 ? $('#_chart_control_').hide():'';
                $('#btns').css('height','83px')
                $('#_ftabela').append(`<div style="text-align:end;" class="form-check"  title="Ao marcar esta opção, o gráfico será renderizado conforme sua busca no filtro de dados. Em tabelas muito extensas o sistema pode apresentar travamentos e lentidão" >
                    <input onchange="filtrarTabela()" type="checkbox" class="form-check-input" id="renderizaGrafico">
                    <label style="font-size: 12px;" class="form-check-label" for="renderizaGrafico">Renderizar Novo Gráfico</label>
                </div>`)
                gerarGraficoBarrasLaterais();
            } else if (temgrafico == 'BARRASAGRUPADAS') {
                $("#chartdiv").show();
                $('#_chart_control_').append($('#inputAgrupamento'));
                $('#_chart_control_').show();
                $('#inputAgrupamento').show();            
                $('#inputAgrupamento').length < 1 ? $('#_chart_control_').hide():'';
                $('#btns').css('height','83px')
                $('#_ftabela').append(`<div style="text-align:end;" class="form-check"  title="Ao marcar esta opção, o gráfico será renderizado conforme sua busca no filtro de dados. Em tabelas muito extensas o sistema pode apresentar travamentos e lentidão" >
                    <input onchange="filtrarTabela()" type="checkbox" class="form-check-input" id="renderizaGrafico">
                    <label style="font-size: 12px;" class="form-check-label" for="renderizaGrafico">Renderizar Novo Gráfico</label>
                </div>`)
                gerarGraficoBarrasAgrupadas();
            }

            if($('#nlinha').html()=='Nenhum Registro encontrado'){
                $("#divFiltro").html("");
                $("#_chart_control_").html("");
                $("#_chart_control_").hide();
                $("#divBotao").html("");
                $("#chartdiv").html("");
                $("#chartdiv").hide();
                $('#btns').hide();
            }

            $('#rollUp').show();
        });
    }

    function printElem( rep, print ){
        
        let mywindow = window.open('', 'PRINT');

        let chartContent = '';
        let tableContent = '';
        let n_linhas = document.getElementById("nlinha").innerHTML;

        if(print=='g' || print=='a'){
            chartContent = document.getElementById("chartdiv").innerHTML;
        }

        if(print=='t' || print=='a'){
            tableContent += document.getElementById("conteudo_relatorio").innerHTML;
        }

        let cssContent = `
            .banner {
                position: relative;
                z-index: 5;
                color: #000;
                padding: 20px;
            }    

            .banner .bg img{
                position: fixed;
                z-index: -1;
                background-repeat: no-repeat;
                opacity: 0.1;
                width: 50%;
                top: 50%;
                left: 25%;
            }

            table {
                font-size: 9px;
                width: 100%;
            }

            table img{
                width: 100%;
            }


            
            .table-striped:not(table) .header{border: 1px solid rgb(192, 192, 192) !important; height: 20px !important;}
            .table-striped:not(table) .tbody .res > div > div{border: 1px solid rgb(192, 192, 192) !important;}
            .table-striped:not(table) .sub-row{padding: 3px 0;}
            .table-striped:not(table) .sub-row > div{width: 100%;margin: 0 auto;display: flex;flex-wrap: wrap;}
            .table-striped:not(table) .col-sm-12{padding: 0 10px ;}
            .table-striped:not(table) .sub-row.col-sm-12{padding: 0;}
            .table-striped:not(table) .res > .row > div, .table-striped:not(table) .header {display: table;padding:0 .4rem;}
            .table-striped:not(table) .res > .row > div > *, .table-striped:not(table) .header > *{display: table-cell;vertical-align: middle;}
        `;

        let logoBase64 = document.getElementById('logo_relatorio').innerHTML;
        let htmlContent = `
            <html>
                <head>
                    <title>${ document.title }</title>
                </head>
                <style>
                    ${ cssContent }
                </style>
                <body>
                    <table style="margin-left: 20px;">
                        <tr>
                            <td rowspan="3" style="width:50px;">
                                ${ logoBase64 }
                            </td>
                            <td>
                                <h3>${ rep }</h3>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                ${n_linhas}
                            </td>
                        </tr>
                    </table>
                    <div class="banner">
                        <div class="bg">
                            ${ logoBase64 }
                        </div>
                        <div>
                            ${ chartContent }
                            <span style="font-size:9px">
                                Impressão gerada em ${new Date(Date.now()).toLocaleDateString('pt', { year: "numeric",month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", second: "2-digit" }) } por <?=$_SESSION["SESSAO"]["USUARIO"]?>
                            </span>
                            ${ tableContent }
                        </div>
                    </div>
                </body>
            </html>
        `;

        
        
        mywindow.document.write(htmlContent);

        //Remove conteúdo html indesejado 
        $(mywindow.document).find('.n_linhas').remove();
        $(mywindow.document).find('script').remove();
        $(mywindow.document).find('[style*="touch-action: none;"]').remove();
        $(mywindow.document).find('.abrir-fechar-todos').remove();
        $(mywindow.document).find('[style*="width: 20%;"]').css({'font-size': '9px', 'width': '18%', 'padding':'3px'});



        mywindow.document.close();
        mywindow.focus();
        mywindow.print();
        mywindow.close();

        return true;
    }

    function gerarArrayObjOrdenadoPorChave( obj, chave ) {
        let arr = Object.keys(obj).map((id) => obj[id]);
        return arr.sort((a,b) => (a[chave] > b[chave]) ? 1 : ((b[chave] > a[chave]) ? -1 : 0));
    }

    $("#rollUp").click(function(){$(window).scrollTop(0)});

    $(window).scroll(function() {
        if ($(this).scrollTop() > 0) {
            $('#rollUp').fadeIn();
        } else {
            $('#rollUp').fadeOut();
        }
    });

//# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>