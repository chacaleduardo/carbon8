<?
require_once("../inc/php/functions.php");
require_once("./controllers/eventotipo_controller.php");
?>
<style>
    .container-kanban {
        padding: 8px;
        display: flex;
        background-color: #f0f0f0;
        gap: 12px;
        width: 100%;
        overflow-x: auto;

    }

    .kanban-column {
        height: 800px;
        padding: 10px 0;
        border-radius: 5px;
        display: flex;
        min-width: 170px;
        align-items: center;
        flex-direction: column;
        justify-content: center;
        background-color: #fff;
        vertical-align: top;
        font-size: 16px;
    }

    .kanban-list {
        height: 100%;
        padding: 0 10px;
        overflow-y: auto;
        border-radius: 5px;
        display: inline-block;
        background-color: #fff;
        vertical-align: top;
        font-size: 14px;
    }

    .kanban-item {
        display: flex;
        justify-content: space-between;
        overflow: hidden;
        flex-direction: column;
        padding: 10px 6px;
        margin: 10px 0;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        cursor: grab;
        height: 150px;
        border-left: 12px solid rgb(51, 202, 207);
        border-right: 3px solid rgb(51, 202, 207);
        border-top: 3px solid rgb(51, 202, 207);
        border-bottom: 3px solid rgb(51, 202, 207);
    }

    .kanban-item-nao-lido {
        display: flex;
        justify-content: space-between;
        overflow: hidden;
        flex-direction: column;
        padding: 10px 6px;
        margin: 10px 0;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        cursor: grab;
        height: 150px;
        border-left: 12px solid rgb(142 148 149);
        border-right: 3px solid rgb(142 148 149);
        border-top: 3px solid rgb(142 148 149);
        border-bottom: 3px solid rgb(142 148 149);
    }

    .kanban-item-atrasado {
        display: flex;
        justify-content: space-between;
        overflow: hidden;
        flex-direction: column;
        padding: 10px 6px;
        margin: 10px 0;
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        cursor: grab;
        height: 150px;
        border-left: 12px solid rgb(207 51 51);
        border-right: 3px solid rgb(207 51 51);
        border-top: 3px solid rgb(207 51 51);
        border-bottom: 3px solid rgb(207 51 51);
    }

    .title-column {
        display: flex;
        align-items: center;
        height: 60px;
        text-align: center;
    }

    .title-item {
        width: 100%;
        display: flex;
        justify-content: space-between;
    }

    .div-button-filtrar {
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
    }

    .description-card {
        display: -webkit-box;
        line-clamp: 3;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        text-align: inherit;
    }

    ::-webkit-scrollbar {
        height: 4px;
        width: 5px;
        border-radius: 8px;
        background: #151517d9;
        transform: translate3d(0, 0, 0);
        -webkit-transform: translate3d(0, 0, 0);
    }

    ::-webkit-scrollbar-thumb {
        background: #979797;
        border-radius: 8px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #a3a4a8d9;
    }
</style>
<div class="flex flex-column">
    <div class="col-md-12">
        <div class="panel panel-default ">
            <div class="panel-heading">
                <h3 class="panel-title">Filtros</h3>
                <?
                if (array_key_exists("gerenciamentotarefasalteracoes", getModsUsr("MODULOS")) != 1) {
                    $permissao = "N";
                } else {
                    $permissao = "Y";
                }
                ?>
                <input type="hidden" id="permissao" value="<?= $permissao ?>">
            </div>
            <div class="panel-body">
                <div class="col-md-12" id="filters">
                    <div class="col-md-3">
                        <label for="idempresa">Empresa</label>
                        <select name="idempresa" id="idempresa" class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                            <?
                            $options = EventoTipoController::buscarCampoPorIdEventoTipoCampo(28,'textocurto5');
                            if ($options["codedeletado"]){
                                $options["code"] = $options["code"]. " UNION ".$options["codedeletado"];
                            }
                            $execCode = d::b()->query($options['code']) or die("Erro ao buscar setores: " . mysqli_error(d::b()) . "<br>" . $options['code']);
                            while ($row = mysqli_fetch_array($execCode,MYSQLI_NUM)) {
                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($row[1]) . '" value="' . $row[0] . '" >' . $row[1] . '</option>';
                            }
                            echo '<option ' . $selected . ' data-tokens="' . retira_acentos("Não selecionado") . '" value="' . "" . '" >' . "Não selecionado" . '</option>';
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="setor">Setor</label>
                        <select name="setor" id="setor" class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                            <?
                            $options = EventoTipoController::buscarCampoPorIdEventoTipoCampo(28,'textocurto2');
                            if ($options["codedeletado"]){
                                $options["code"] = $options["code"]. " UNION ".$options["codedeletado"];
                            }
                            $execCode = d::b()->query($options['code']) or die("Erro ao buscar setores: " . mysqli_error(d::b()) . "<br>" . $options['code']);
                            while ($row = mysqli_fetch_array($execCode,MYSQLI_NUM)) {
                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($row[1]) . '" value="' . $row[0] . '" >' . $row[1] . '</option>';
                            }
                            echo '<option ' . $selected . ' data-tokens="' . retira_acentos("Não selecionado") . '" value="' . "" . '" >' . "Não selecionado" . '</option>';
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                            <?
                            $sql = "SELECT fs.idfluxostatus, s.rotulo
                                    FROM fluxo f 
                                        JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
                                        JOIN carbonnovo._status s ON s.idstatus = fs.idstatus
                                    WHERE f.status = 'ATIVO' AND f.modulo = 'evento'
                                        AND s.statustipo NOT IN ('CANCELADO', 'CONCLUÍDO')
                                        AND f.tipoobjeto = 'ideventotipo' AND f.idobjeto = 28
                                        AND fs.idfluxostatus IN (2743,2744,2949,2745,2798,2747,2746)
                                    ORDER BY ordem";

                            $res = mysql_query($sql) or die("NF Saída - Erro ao recuperar status: " . mysql_error());
                            while ($row = mysql_fetch_assoc($res)) {
                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($row["rotulo"]) . '" value="' . $row["idfluxostatus"] . '" >' . $row["rotulo"] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="urgencia">Urgência</label>
                        <select name="urgencia" id="urgencia" class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                            <?
                            $ArrayUrgencia = [
                                "sim" => "SIM",
                                "nao" => "NÃO"
                            ];

                            foreach ($ArrayUrgencia as $key => $value) {
                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($value) . '" value="' . $key . '" >' . $value . '</option>';
                            }
                            echo '<option ' . $selected . ' data-tokens="' . retira_acentos("Não selecionado") . '" value="' . "" . '" >' . "Não selecionado" . '</option>';

                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="responsavel">Responsável</label>
                        <select name="responsavel" id="responsavel" class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                            <?
                            $options = EventoTipoController::buscarCampoPorIdEventoTipoCampo(28,'nomecompleto');
                            $execCode = d::b()->query($options['code']) or die("Erro ao buscar setores: " . mysqli_error(d::b()) . "<br>" . $options['code']);
                            while ($row = mysqli_fetch_array($execCode,MYSQLI_NUM)) {
                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($row[1]) . '" value="' . $row[0] . '" >' . $row[1] . '</option>';
                            }
                            echo '<option ' . $selected . ' data-tokens="' . retira_acentos("Não selecionado") . '" value="' . "" . '" >' . "Não selecionado" . '</option>';

                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="criadopor">Criado Por</label>
                        <br>
                        <input type="text" name="criadopor" id="criadopor" class="form-control" style="width: 100% !important;">
                    </div>
                    <div class="col-md-3">
                        <label for="datafim">Data de entrega</label>
                        <br>
                        <input type="text" name="datafim" id="datafim" class="form-control tipodata" style="width: 100% !important;" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label for="lido">Não Lido</label>
                        <br>
                        <select name="lido" id="lido" class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                            <?
                            $ArrayUrgencia = [
                                "N" => "Lido",
                                "0" => "Não Lido"
                            ];

                            foreach ($ArrayUrgencia as $key => $value) {
                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($value) . '" value="' . $key . '" >' . $value . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sustentacao">Sustentação</label>
                        <br>
                        <select name="sustentacao" id="sustentacao" class="form-control selectpicker" multiple="multiple" data-actions-box="true" data-live-search="true">
                        <?
                        
                            $options = EventoTipoController::buscarCampoPorIdEventoTipoCampo(28,'textocurto3');
                            if ($options["codedeletado"]){
                                $options["code"] = $options["code"]. " UNION ".$options["codedeletado"];
                            }
                            $execCode = d::b()->query($options['code']) or die("Erro ao buscar setores: " . mysqli_error(d::b()) . "<br>" . $options['code']);
                            while ($row = mysqli_fetch_array($execCode,MYSQLI_NUM)) {
                                echo '<option ' . $selected . ' data-tokens="' . retira_acentos($row[1]) . '" value="' . $row[0] . '" >' . $row[1] . '</option>';
                            }
                            echo '<option ' . $selected . ' data-tokens="' . retira_acentos("Não selecionado") . '" value="' . "" . '" >' . "Não selecionado" . '</option>';

                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="evento">Titulo</label>
                        <br>
                        <input type="text" name="evento" id="evento" class="form-control" style="width: 100% !important;">
                    </div>
                </div> 
                <div class="col-md-12 div-button-filtrar">
                    <div class="col-md-3" style="display: flex;justify-content: flex-end;">
                        <button class="btn btn-outline-warning" onclick="limparFiltros()" >
                            <span class="fa fa-close"></span> Limpar
                        </button>
                        &nbsp;&nbsp;
                        <button class="btn btn-primary" onclick="GetEvents()">Filtrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="kanban" class="col-md-12">
        <div class="container-kanban col-md-12">
            <?
            $sql = "SELECT fs.idfluxostatus, s.rotulo
                    FROM fluxo f 
                        JOIN fluxostatus fs ON fs.idfluxo = f.idfluxo
                        JOIN carbonnovo._status s ON s.idstatus = fs.idstatus
                    WHERE f.status = 'ATIVO' AND f.modulo = 'evento'
                        AND s.statustipo NOT IN ('CANCELADO', 'CONCLUÍDO')
                        AND f.tipoobjeto = 'ideventotipo' AND f.idobjeto = 28
                        AND fs.idfluxostatus IN (2740,2741,2896,2742,2743,2744,2949,2745,2798,2747,2746,2803,3059, 3259)
                    ORDER BY ordem";

            $res = mysql_query($sql) or die("NF Saída - Erro ao recuperar status: " . mysql_error());
            while ($row = mysql_fetch_assoc($res)) { ?>
                <div class="kanban-column col-md-2">
                    <div class="title-column">
                        <strong><?= $row["rotulo"] ?></strong>
                        <strong id="contador<?= $row["idfluxostatus"] ?>"></strong>
                    </div>
                    <div class="kanban-list col-md-12" id="<?= $row["idfluxostatus"] ?>"></div>
                </div>
            <? } ?>
            <? ?>
        </div>

    </div>
    <!-- GRÁFICO DE GUNT -->
    <div class="flex flex-row">
        <div class="col-md-12">
            <h1>Gerenciamento de Tarefas</h1>
        </div>
    </div>
    <div id="gunt" class="col-md-12"></div>
</div>
<!-- jsDelivr :: Sortable :: Latest (https://www.jsdelivr.com/package/npm/sortablejs) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    //embeded iframe 
    var iframe = document.createElement('iframe');
    iframe.src = "https://lookerstudio.google.com/embed/reporting/06c77149-1244-4e23-a295-92e06720928d/page/APv8D";
    iframe.style.width = '85lvw';
    iframe.style.height = '85lvh';
    iframe.style.margin = 'auto';

    $(".selectpicker").selectpicker("render")

    document.getElementById('gunt').appendChild(iframe);

    let idTimeout;
    // Verifica se a biblioteca Sortable já foi importada
    idTimeout = setInterval(() => {
        if (typeof Sortable != 'undefined') {
            $(`.kanban-list`).each((i, e) => {

                new Sortable(e, {
                    group: $(e).attr("id"), // Permite a movimentação entre grupo
                    animation: 120,
                    ghostClass: 'blue-background-class',
                    // Called by any change to the list (add / update / remove)
                    onSort: function( /**Event*/ evt) {
                        if ($(`#permissao`).val() == "Y") {
                            // Cria um array para armaz/*  */enar a nova ordem dos elementos
                            var order = {};
                            $(this.el).find(".kanban-item").each(function(index, element) {
                                order[$(element).attr("idevento")] = index
                            });

                            order["action"] = "sortEvento"



                            $.ajax({
                                type: "POST",
                                url: "ajax/eventsKanban.php",
                                data: order,
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded",
                                    "authorization": (Cookies.get('jwt') || localStorage.getItem("jwt") || "")
                                },
                                success: function(data) {
                                    console.log(data)
                                }
                            })
                        }

                    },

                });

            })

            clearInterval(idTimeout);
        }
    }, 400);



    async function GetEvents() {
        var obj = {}
        $(`#filters`).find(`select`).each((i, e) => {
            if ($(e).val() != null) {
                obj[$(e).attr("name")] = $(e).val().join(",")
            }

        })

        $(`#filters`).find(`input:not(.tipodata)`).each((i, e) => {
            if ($(e).val() != null && $(e).val() != "") {
                obj[$(e).attr("name")] = $(e).val()
            }

        })

        $(`#filters`).find(`input.tipodata`).each((i, e) => {
            if ($(e).val() != null && $(e).val() != "") {
                datainicioPtBr = $(e).val().split(" - ")[0]
                datafimPtBr = $(e).val().split(" - ")[1]
                datainicio = datainicioPtBr.split("/").reverse().join("-") + " 00:00:00"
                datafim = datafimPtBr.split("/").reverse().join("-") + " 23:59:59"
                obj["iniciodatafim"] = datainicio
                obj["fimdatafim"] = datafim
            }

        })

        $(`.kanban-item, .kanban-item-atrasado`).remove()
        obj["action"] = "buscarEventosKaban"
        console.log(obj)
        $.ajax({
            type: "POST",
            url: "ajax/eventsKanban.php",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "authorization": (Cookies.get('jwt') || localStorage.getItem("jwt") || "")
            },
            data: obj,
            success: function(data) {
                try {
                    data = JSON.parse(data)
                    console.log(data)
                    if (data){
                        $(`.kanban-list`).each((i, e) => {
                            $(e).find(`.kanban-item, .kanban-item-atrasado`).remove()
                            $(`#contador${$(e).attr("id")}`).html(``)
                            if ( data[$(e).attr("id")] != undefined) {
                                $(`#contador${$(e).attr("id")}`).html(`&nbsp;(${data[$(e).attr("id")].length})`)
                                for (evento in data[$(e).attr("id")]) {
    
                                    if (data[$(e).attr("id")][evento].viu != "N") {
                                        //background cinza escuro
                                        iconeye = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16" style="color: red">
                                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"></path>
                                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"></path>
                                                    </svg>&nbsp;`
                                    } else {
                                        //background cinza claro
                                        iconeye = ""
                                    }
    
                                    if (data[$(e).attr("id")][evento].vencido == "NO PRAZO") {
                                        classe = "kanban-item"
                                    } else {
                                        classe = "kanban-item-atrasado"
                                    }
    
                                    if (data[$(e).attr("id")][evento].dmadatafim != null) {
                                        //abrevia a data de fim
                                        datafim = data[$(e).attr("id")][evento].dmadatafim.split("/")
                                        data[$(e).attr("id")][evento].dmadatafim = datafim[0] + "/" + datafim[1]
                                    }
    
                                    if (data[$(e).attr("id")][evento].urgencia == "sim") {
                                        //urgente
                                        icon = `<i class="fa fa-exclamation-triangle" style="color:orange"></i>&nbsp;`
    
                                    } else {
                                        //não urgente
                                        icon = ``
                                    }
    
    
                                    $(e).append(`
                                    <div id="evento-${data[$(e).attr("id")][evento].idevento}" class="${classe}" data-placement="bottom"  ondblclick="janelamodal('?_modulo=evento&_acao=u&idevento=${data[$(e).attr("id")][evento].idevento}')" idevento=${data[$(e).attr("id")][evento].idevento}>
                                        
                                        <div class='title-item'>
                                            <strong >${data[$(e).attr("id")][evento].idevento}</strong>
                                            <strong >${data[$(e).attr("id")][evento].responsavel || ""}</strong>
                                        </div>
                                        <span class="description-card">
                                            ${data[$(e).attr("id")][evento].evento}
                                        </span>
                                        <div class='title-item'>
                                            <strong style="display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;">${iconeye}${icon}${data[$(e).attr("id")][evento].dmadatafim || "-"}</strong>
                                            <strong >${data[$(e).attr("id")][evento].siglacliente || ""}</strong>
                                        </div>
                                    </div>
                                    `)
    
    
                                    if(data[$(e).attr("id")][evento].previsao == null){
                                        data[$(e).attr("id")][evento].previsao = "00:00"
                                    }
                                    if(data[$(e).attr("id")][evento].registrado == null){
                                        data[$(e).attr("id")][evento].registrado = "00:00"
                                    }
                                    previsaoFormatada = data[$(e).attr("id")][evento].previsao.split(":")
                                    previsao = (previsaoFormatada[0]) +":"+ (previsaoFormatada[1])
                                    registradoFormatada = data[$(e).attr("id")][evento].registrado.split(":")
                                    registrado = (registradoFormatada[0]) +":"+ (registradoFormatada[1])
    
    
                                    //Adiciona popovers
                                    $(`#evento-${data[$(e).attr("id")][evento].idevento}`).popover({
                                        trigger: 'hover',
    
                                        html: true,
                                        content: `
                                        <div>
                                            <strong>Dias no status:</strong> ${data[$(e).attr("id")][evento].diasnostatus || "-"}<br>
                                            <strong>Horas Previstas:</strong> ${previsao || "-"}<br>
                                            <strong>Horas Gastas:</strong> ${registrado || "-"}<br>
                                        </div>
                                        `
                                    });
                                }
                            }
                        })
                    }

                } catch (e) {
                    console.log(e)
                }
            }
        })
    }
    $(`input.tipodata`).each(function(i, o){
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
            });

        $(o).val('');
    });

    function limparFiltros(){
            //limpar campos selectpicker sem utilizar as funções padrões do plugin (selectpicker)
            //o uso das funções proprias do plugin ocasionam quebra das requisições dos filtros via ajax
            //buscando filtros ativos
            $.map($('.bs-actionsbox'),function(el){ 
                //forçando função interna do picker para remoção dos campos selecionados
                $(el).find('.bs-deselect-all').trigger('click');
                $(el).removeClass('filtroAtivo');
            });
            $(`cancel.daterangepicker`).click();
            //limpar campos input
            $('input').val('');
        }
</script>