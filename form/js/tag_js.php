<script type="text/Javascript">
    //o comentario abaixo faz com que este pedaço de script apareça na aba 'sources' do inspetor do google chrome
    //@ sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>;
    var idPessoa = <?= $_SESSION["SESSAO"]["IDPESSOA"] ?>,
        idTag    = <?= $_1_u_tag_idtag ?? 'null' ?>,
        idTagClass = <?= $idTagClass ?? 'null' ?>,
        acao = '<?= $_acao ?>',
        tagsDescricoes = <?= json_encode($tagDescricoes) ?>,
        idUnidade = <?= $_1_u_tag_idunidade ?? 'null' ?>,
        link = '<?= $link ?>',
        tagDimLotesId = <?= json_encode($tagDimLotes) ?>,
        letrasVisiveis = <?= json_encode($letrasVisiveis) ?>,
        alfabeto = <?= json_encode(TagController::$alfabeto) ?>,
        tagsPrateleiras = <?= json_encode($tagsPrateleiras) ?>;


    $(`#showimpressao`).hide()
    $(`.showimpressao`).on("change", _ =>{
        if($(".showimpressao:checked").length > 0)
            $(`#showimpressao`).show()
        else 
            $(`#showimpressao`).hide()
    });

    $(`.checkbox-lote`).on("change", _ => {
        let linhaAtiva = $(".checkbox-lote:checked");

        if(linhaAtiva.get().length == 1)
        {            
            if(linhaAtiva.parent().parent().find('.lote-quantidade').get().length)
                $(`#transferencia-lotes`).show();
        } else
            $(`#transferencia-lotes`).hide();
    });

    function removerLotesImp() {
        const linhaAtiva = $(".checkbox-lote:checked"),
              caixas = linhaAtiva.parent().parent().find('.caixas > div:has(.lote-quantidade)');
        // Removendo linhas de impressao de lotes
        const idImpLotes = caixas.map((item, element) => `.imp-tagdim-${$(element).data('idtagdim')}`).toArray().join(',');
        $(idImpLotes).remove();
        $('#qtd-lotes').text($('#impressao-por-lote > div').length);

    }

    $('#btn-verificar-disponibilidade').on('click', function()
    {
        let linhaAtiva = $(".checkbox-lote:checked"),
            linhaIdTagDim = linhaAtiva.data('idtagdim');

        if(linhaAtiva.get().length > 1 || !linhaAtiva.get().length)
            return alertAtencao(`Quantidade de linhas selecionadas inválidas! [${linhaAtiva.get().length}]`);

        $(this).data('idtagdim', linhaIdTagDim);

        if(!$(this).data('idtagdim'))
            return alertAtencao(`idtagdim inválido! [${$(this).data('idtagdim')}]`);

        let caixas = linhaAtiva.parent().parent().find('.caixas > div:has(.lote-quantidade)');

        caixasId = caixas.get().map(element=>$(element).data('idtagdim')).join('|');

        $('#input-prateleiras').attr('disabled', true);
        $('#transferir-coluna').attr('disabled', true);
        $('#transferir-linha').attr('disabled', true);
        $(this).attr('disabled', true);

        $.ajax({
            method: 'GET',
            url: '../ajax/tag.php',
            dataType: 'json',
            data: {
                action: 'transferirLoteMultiplo',
                params: [
                    caixasId,
                    $('#input-prateleiras').attr('cbvalue'),
                    gIdEmpresa,
                    idUnidade,
                    $('#transferir-coluna').val(),
                    $('#transferir-linha').val()
                ]
            },
            success: res => {
                $(this).removeAttr('disabled');
                
                if(res.error)
                {
                    $('#input-prateleiras').attr('disabled', true);
                    $('#transferir-coluna').removeAttr('disabled');
                    $('#transferir-linha').removeAttr('disabled');
                    return alertAtencao(res.error);
                }

                // Removendo linhas de impressao de lotes
                removerLotesImp()

                // Removendo caixa da prateleira
                for(chave in res)
                {
                    if($('#input-prateleiras').attr('cbvalue') == idTag)
                    {
                        const divQtdLoteOrigem = $(`#tagdim-${res[chave].idtagdimorigem} .lote-quantidade`);
                        const divQtdLoteDestino = $(`#tagdim-${res[chave].idtagdimdestino} .lote-quantidade`);
                        const qtdLoteOrigem = divQtdLoteOrigem.text() ? parseInt(divQtdLoteOrigem.text()) : 0;
                        const qtdLoteDestino = divQtdLoteDestino.text() ? parseInt(divQtdLoteDestino.text()) : 0;

                        if(!divQtdLoteDestino.length) {
                            const JQdestino = $(`#tagdim-${res[chave].idtagdimdestino}`);
                            const divTagDim = `<div class="relative">
                                    <i class="fa fa-archive mr-2" title=""></i>
                                    <div class="lote-quantidade">${qtdLoteOrigem + qtdLoteDestino}</div>
                                </div>`;

                            JQdestino.prepend(divTagDim)

                            JQdestino.on('click', () => {
                                mostralote(res[chave].idtagdimdestino, JQdestino.data('label'));
                            });
                        } else {
                            divQtdLoteDestino.text(qtdLoteOrigem + qtdLoteDestino);
                        }
                    }

                    $(`#tagdim-${res[chave].idtagdimorigem} div:first-child`).remove();
                }

                $('#input-prateleiras').removeAttr('disabled');
                $('#transferir-coluna').attr('disabled', true).val('');
                $('#transferir-linha').attr('disabled', true).val('');

                $('#input-prateleiras').val('');
                // habilitaImpressao($('#transferir-linha').val().padStart(2, '0'), true)

                alertSalvo('Lote transferido com sucesso!');
            },
            error: err => {
                //
            }
        })
    });

    function retiraund(inidunidadeobjeto)
    {
        CB.post({
            objetos: {
                '_x_d_unidadeobjeto_idunidadeobjeto': inidunidadeobjeto
            }
        });
    }

    function excluirtag(inidtagsala)
    {
        CB.post({
            objetos: {
                '_x_d_tagsala_idtagsala': inidtagsala
            }
        });
    }

    function inseriund(inidund)
    {
        CB.post({
            objetos: {
                '_x_i_unidadeobjeto_idobjeto': $("[name=_1_u_tag_idtag]").val(),
                '_x_i_unidadeobjeto_idunidade': inidund,
                '_x_i_unidadeobjeto_tipoobjeto': 'tag'
            },
            refresh: "refresh"
        });
    }

    function inserirlocal(vthis)
    {
        CB.post({
            objetos: {
                '_x_i_tagsala_idtag': $("[name=_1_u_tag_idtag]").val(),
                '_x_i_tagsala_idtagpai': $(vthis).val()
            },
            refresh:"refresh"
            
        });
    }

    function inserirlocalcontem(vthis){
    CB.post({
        objetos: {
            '_x_i_tagsala_idtagpai': $("[name=_1_u_tag_idtag]").val(),
            '_x_i_tagsala_idtag': $(vthis).val()
        },
        refresh:"refresh"
    });
    }

    if(idTag)
    {
        $("#tag").dropzone({
                idObjeto: $("[name=_1_u_tag_idtag]").val(),
                tipoObjeto: 'tag',
                idPessoaLogada: idPessoa
            });

        $("#planta").dropzone({
            idObjeto: $("[name=_1_u_tag_idtag]").val(),
            tipoObjeto: 'tagplanta',
            idPessoaLogada: idPessoa
        });

        $("#certificado").dropzone({
            idObjeto: $("[name=_1_u_tag_idtag]").val(),
            tipoObjeto: 'tagcertificado',
            idPessoaLogada: idPessoa
        });
    }

    function imprimeEtiquetaAndarColuna(idtag, qtdValInput = null, idTagdim = false, idLote = ''){
        let arr = [];
        let ip = $("#impressoraetiqueta").val();
        let linhaColuna = '';
        let qtdVal = 1;

        if(qtdValInput && idTagdim) {
            qtdVal = $(qtdValInput).prev().val();

            linhaColuna = $(`div[data-idtagdim='${idTagdim}']`).data('linhacoluna');
            arr.push(`${idtag}_${linhaColuna}`);
        } else {
            $(".showimpressao:checked").each((index,element) => {
            linhaColuna = $(element).attr('id');
            arr.push(`${idtag}_${linhaColuna}`);
        });
        }

         $.ajax({
                type: "post",
                url : "ajax/etiquetaemlote.php",
                data: {
                    data: arr,
                    qtd: qtdVal,
                    ip: ip,
                    modulo: "lotealmoxarifado",
                    idlote: idLote
                },
                success: function(data){
                    if(data.error)
                    {
                        return alertAtencao(data.error);
                    }

                    alertAzul("Enviado para impressão","",1000);
                }
            });
    }

    function showModalEtiqueta(grupo = 1,filhos = ""){
        _controleImpressaoModulo({
            modulo: getUrlParameter("_modulo"),
            grupo: grupo,
            idempresa: getUrlParameter("_idempresa") || "1",
            objetos:{
                modulo: getUrlParameter("_modulo"),
                idtag: $("input[name=_1_u_tag_idtag]").val(),
                idempresa: getUrlParameter("_idempresa") || "1",
                filhos: filhos
            }
        });
    }
    function imprimeEtiqueta(inid){
        var imprimir=true;
        CB.imprimindo=true;

        if(!confirm("Deseja realmente enviar para a impressora?")){
            imprimir=false;
        }

        if(imprimir){
            $.ajax({
                type: "get",
                url : "ajax/impetiquetasemente.php?tipoobjeto=tag&idobjeto="+inid,
                success: function(data){
                    if(data.error)
                    {
                        return alertAtencao(data.error);
                    }

                    alertAzul("Enviado para impressão","",1000);
                }
            });
        }
    }

    function showdim(vthis){
        var tclass=$(vthis).val();
        if(tclass==4)
        {
            if(document.getElementById('trdimensoes'))
            {
                return document.getElementById('trdimensoes').style.display = "block";
            }

            return $('.main-form').append(montaLinhaColuna());
        }

        if($('#trdimensoes').length)
        {
            return $('#trdimensoes').remove();
        }
    }


    function flgrevisado(vthis){
    var atval=$(vthis).attr('atval');
    var idtag=$(vthis).attr('idtag');
    if(vthis.checked){
        mensagem = "Alterado Para Revisado.";
    } else {
        mensagem = "Alterada Para Não Revisado.";
    }
    CB.post({
        objetos: {
            '_x_u_tag_idtag': idtag,
            '_x_u_tag_revisado': atval
        },
        parcial:true,
        refresh: false,
        msgSalvo: mensagem,
        posPost: function() {
            if(atval=='Y'){
                $(vthis).attr('atval','N');
            }else{
                $(vthis).attr('atval','Y');
            }
        }
    })

    }

    function abrirmodal(iddevice,nomeciclo,grupo)
    {
        var strCabecalho = "</strong><label>Ciclo " + nomeciclo + "</label></strong>";
        //Altera o cabeçalho da janela modal
        $("#cbModalTitulo")
            .html(strCabecalho)
            //.append(`<i class='fa fa-print floatright' id='btPrintativ' title='Impressão' onclick="printativ('${nomeciclo}','${iddevice}')"></i>`)
        ;
        var data = $("#atividadeinfo"+iddevice+grupo)[0].innerHTML;
        $('#cbModal').addClass('oitenta');
        $('#cbModal').addClass('fade');
        $('#cbModal').attr("modal", "modalatividade" + iddevice + grupo);
        $('#cbModal').addClass('hideshowtable');
        $("#cbModalCorpo").html(data);
        $('#cbModal').modal('show');
    }

    function printativ(titulo,iddevice) {
        let titleAnt = document.title;
        document.title = titulo;
        window.print();
    }

    if(acao == 'u')
    {
        // instanciarAutocompletePrateleiras($('#transferencia-lotes'));
        instanciarAutocompletePrateleiras($('#input-prateleiras'));

        function modalEmpresa()
        {
            let div = '';
            $.ajax({
                method: 'post'
                ,url: 'ajax/alocacaotag.php'
                ,data: {
                    "idtag": idTag,
                    "tipo": 'empresa'
                }
            }).done(function(data,texto,jqXHR){
                if(data.error)
                {
                    return alertAtencao(data.error);
                }

                let info = JSON.parse(data);
                let permissoesMatriz = gMatrizPermissoes.split(',');
                div += '<div class="panel panel-default" style="margin-top: -5px !important;">';
                    div += '<div class="panel-body">';
                        div += '<table><tr><td>Empresa:</td><td>';
                        div += '<select name="duplicar_idempresa" id="idempresa" onchange="alteraUnidade(this)" vnulo>';
                            div += '<option></option>';
                            for(permissoesMatriz in  info)
                            {
                                if (info[permissoesMatriz] != undefined && permissoesMatriz != 'idempresa') 
                                {				
                                    div += '<option value="'+permissoesMatriz+'">'+info[permissoesMatriz].nomefantasia+'</option>';
                                }
                            }
                        div += '</select></td>';
                        div += '<td>Unidade Destino:</td><td><select name="unidade_destino" id="unidade_destino" class="size10" autocomplete="off" vnulo><option></option></select></td>';
                        div += '<td>Data Início Locação:</td><td><input name="data_inicio" class="calendariomodal size10" type="text" size="6" autocomplete="off" vnulo></td>';
                        div += '<td>Data Fim Locação:</td><td><input name="data_fim" id="data_fim" class="calendariomodal size10" type="text" size="6" autocomplete="off" vnulo></td></tr>';
                        div += '<tr><td colspan="8"><br/>Caso o prazo seja indeterminado clique no campo ao lado e preencha somente a data do Início da Locação. <input id="prazoindeterminado" name="repetircheckbox" type="checkbox" onclick="removeVnulo()"></td></tr>';
                        div += '<tr><td colspan="8" style="text-align: right;"><button type="button" onclick="CB.post()" class="btn btn-danger btn-sm"><i class="fa fa-circle"></i>Salvar</button></td></tr>';
                        div += '</table>';
                    div += '</div>';
                div += '</div>';
                div += '<br /><br />';

                <? if (!empty($tagLocada)) { ?> 
                    let locado = <?= json_encode($tagLocada) ?>;
                    let botacaocancelar;
                    div += '<div class="modal-header" style="margin-bottom: -37px;">';
                        div += '<h4 class="modal-title" id="cbModalTitulo">Histórico Alocações</h4>';
                    div += '</div>';
                    div += '<div id="cbModalCorpo" class="modal-body">';
                        div += '<div class="row">';
                                div += '<div class="panel panel-default">';
                                    div += '<div class="panel-body">';
                                        div += '<table class="table table-striped planilha">';
                                            div += '<tr><th>Início Locação</th><th>Descrição</th><th>Tag</th><th>Status</th><th>Fim Locação</th><th></th></tr>';
                                            for(let key of Object.keys(locado))
                                            {
                                                let $_locado = locado[key];
                                                if($_locado.status == 'ATIVO'){
                                                    botacaocancelar = `<i class="fa fa-ban pointer" title="Cancelar Locação" onclick="cancelarLocacao(${$_locado.idtagreserva})"></i>`;
                                                } else {
                                                    botacaocancelar = `-`;
                                                }
                                                div += `<tr><td>${$_locado.inicio}</td><td>${$_locado.sigla} - ${$_locado.descricao}</td><td>${$_locado.sigla} - ${$_locado.tag} <a class="fa fa-bars pointer fade" href="?_modulo=tag&_acao=u&idtag=${$_locado.idtag}" target="_blank"></a></td><td>${$_locado.status}</td><td>${$_locado.fim}</td><td>${botacaocancelar}</td></tr>`;
                                            }
                                        div += '</table>';
                                    div += '</div>';
                            div += '</div>';
                        div += '</div>';
                    div += '</div>';
                <? } ?>

                CB.modal({
                    corpo: div,
                    titulo: "LOCAÇÃO",
                    classe: 'oitenta',
                    aoAbrir: function(){
                        $('.calendariomodal').daterangepicker({
                            locale: {
                                format: 'DD/MM/YYYY',
                            },
                            singleDatePicker: true,
                            showDropdowns: true,
                            autoUpdateInput: false,
                        }).on('apply.daterangepicker', function(ev, picker) {
                            $(this).val(picker.startDate.format('DD/MM/YYYY'));
                        }).on('cancel.daterangepicker', function(ev, picker) {
                            $(this).val('');
                        });
                    }
                });		
            });
        }

        function modalPedido(inidtag){
            janelamodal('?_modulo=tag&_acao=u&idtag='+inidtag);
        }

        function alteraUnidade(vthis)
        {
            let idempresa = $('#idempresa').val();
            $.ajax({
                type: "post",
                url : "ajax/alocacaotag.php",
                data: {
                    "idtag": idTag,
                    "tipo": 'unidade',
                    "idempresa": idempresa
                },
                success: function(data){
                    if(data.error)
                    {
                        return alertAtencao(data.error);
                    }

                    $("#unidade_destino").html(data);
                },
                error: function(objxmlreq){
                    alert('Erro:<br>'+objxmlreq.status); 
                }
            });
        }

        CB.on("posPost",function(args){
            if(args.jqXHR.getResponseHeader('X-i-TAG'))
            {
                modalEmpresa();
                modalPedido(args.jqXHR.getResponseHeader('newidtag'));	
            }
        });

        function removeVnulo()
        {
            $("#data_fim").removeAttr('vnulo');
        }

        if(letrasVisiveis.length)
        {
            letrasVisiveis.forEach(letra => {
                $(`#link-${letra}`).removeAttr('disabled');
            });
        }

        if(tagDimLotesId.length)
        {   
            $.ajax({
                method: 'POST',
                url: '../ajax/tag.php',
                data: {
                    action: 'verificarSePossuiLoteFracaoPorIdTagDimEIdUnidade',
                    params: [tagDimLotesId.join('|'), idUnidade]
                },
                dataType: 'json',
                success: res => {
                    if(res.error)
                        alertAtencao(res.error);

                    criarDivLote(jsonStr2Object(res));
                },
                error: res => {
                    console.log(res);
                }
            });

            $('#voltar-topo').removeClass('hidden');
            $('#voltar-topo').on('click', _ => {
                window.scroll(0,0)}
            );
        }
    }

    function criarDivLote(dados)
    {
        for(let chave in dados)
        {
            let item = dados[chave],
                JQitem = $(`.tagdim-${item.linha}-${item.coluna}`);
            
            let divTagDim = `<div class="relative">
                                <i class="fa fa-archive mr-2" title=""></i>
                                <div class="lote-quantidade">${item.qtd}</div>
                            </div>`;

            JQitem.on('click', () => {
                mostralote(item.idtagdim, JQitem.data('label'));
            });
            JQitem.prepend(divTagDim);

            $(`#link-${alfabeto[item.coluna]}`).addClass('tem-lote');
        }
    }

    function mostralote(inid, strlocal, checked = false, impressao = false, classe = '')
    {
       $.ajax({
            type: "get",
            url : "ajax/tag.php",
            data: {
                "action": 'buscarLotePorIdTagDimEIdUnidade',
                "params": [`${idUnidade}|${inid}`]
            },
            success: res => {
                let lotes = jsonStr2Object(res),
                    qtdFracao,
                    expoente,
                    corpoModal = '';

                if(impressao) {
                    const divImpressaLoteJQ = $('#impressao-por-lote');
                    let lotesImpressaoJQ = '';

                    if(!checked) {
                        lotes.forEach(item => {
                            lotesImpressaoJQ += `<div id="lote-imp-${item.idlote}" class="d-flex w-100 flex-wrap justify-content-center align-items-center imp-tagdim-${item.idtagdim}">
                                                    <div class="col-xs-9 form-group pl-0">
                                                        <label for="">Lote</label>
                                                        <div class="input-group" onclick="window.open('?_modulo=${link}&_acao=u&idlote=${item.idlote}', '_blank');">
                                                            <input value="${item.prodserv} - ${item.partida}/${item.exercicio}" type="text" class="form-control" readonly style="background-color: #eee;opacity: 1;cursor: pointer;" />
                                                            <span role="button" class="input-group-addon">
                                                                ${item.qtdLote}${item.qtdFracao ? ` / ${item.qtdFracao}` : ''} 
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-xs-3">
                                                        <label for="">Quantidade</label>
                                                        <div class="input-group">
                                                            <input id="qtd-imp" value="${item.qtdFracaoFinal}" type="number" min="0" class="form-control" />
                                                            <span role="button" class="btn btn-primary btn-md input-group-addon" onclick="imprimeEtiquetaAndarColuna(${idTag}, this, ${item.idtagdim}, ${item.idlote})">Imprimir</span>
                                                        </div>
                                                    </div>
                                                </div>`;
                        })

                        divImpressaLoteJQ.append(lotesImpressaoJQ);
                    } else {
                        lotes.forEach(item => {
                            $(`#lote-imp-${item.idlote}`).remove();
                        })
                    }

                    $('#qtd-lotes').text($('#impressao-por-lote > div').length);
                    
                    if($('#impressao-por-lote #cbCarregando').length) $('#impressao-por-lote #cbCarregando').remove()

                    return;
                }

                let camposTransferenciaHTML = $(`<div id="campo-transferir-${inid}" class="row m-0">
                                                        <div class="col-xs-12 form-group">
                                                            <label>Transferir para </label>
                                                            <input id="input-prateleiras-modal" class="form-control" type="text" />
                                                        </div>
                                                        <div class="col-xs-3 form-group">
                                                            <label>Coluna </label>
                                                            <select id="transferir-coluna-modal" class="form-control" disabled></select>
                                                        </div>
                                                        <div class="col-xs-3 form-group">
                                                            <label>Linha </label>
                                                            <select id="transferir-linha-modal" class="form-control" disabled></select>
                                                        </div>
                                                        <div class="col-xs-6 form-group">
                                                            <button id="btn-verificar-disponibilidade-modal" class="btn btn-primary" data-idtagdim="${inid}" disabled>
                                                                <span class="mr-2">Transferir</span>
                                                                <i class="fa fa-sign-out text-white"></i>
                                                            </button>
                                                        </div>
                                                    </div>`);

                for(let chave in lotes)
                {
                    let lote = lotes[chave],
                        cor = "#CFCFCF"; // Cinza

                    if((lote.qtd_exp && typeof lote.qtd_exp == 'string') && (lote.qtd_exp.indexOf('d') !== -1 || lote.qtd_exp.indexOf('e') !== -1))
                    {
                        expoente = recuperaExpoente(tratarNumero(lote.qtd), lote.qtd_exp);
                    } else 
                    {
                        if(lote.convestoque == 'N')
                        {
                            qtdFracao = lote.qtd/lote.valconvori;
                        } else
                        {
                            qtdFracao =lote.qtd;
                        }

                        expoente = parseFloat(tratarNumero(qtdFracao)).toLocaleString('pt-br', {maximumFractionDigits: 2});
                    }

                    if(lote.status == "APROVADO")
                    {
                        cor = "#90EE90"; // Verde
                    }else if(lote.status == "QUARENTENA")
                    {
                        cor = "#FFA500"; // Laranja
                    }else if(lote.status == "REPROVADO")
                    {
                        cor = "#FF6347"; // Vermelho
                    }else if(lote.status == "PROCESSANDO")
                    {
                        cor = "#B7E9EB"; // Azul
                    }

                    corpoModal += `<div class="tagdim-${inid}">	
                                        <div style= "background-color:${cor}; border: #BEBEBE solid 1px; margin: 1px; min-height: 40px; text-align: center;" >
                                            <font color="red" style="font-size: 12px;">
                                                ${expoente}
                                            </font>
                                            <a title="" onclick="janelamodal('?_modulo=${link}&_acao=u&idlote=${lote.idlote}')" class="pointer">
                                                <font color="Blue" style="font-size: 12px; font-weight: bold;">
                                                    ${lote.partida}/${lote.exercicio}
                                                </font>
                                            </a>
                                        </div>
                                    </div>`
                }
                CB.modal({
                    titulo: "<strong>Lotes da Localização: "+strlocal+"</strong>",
                    corpo: corpoModal,
                    classe: 'trinta'
                });

                $('#cbModalCorpo').prepend(camposTransferenciaHTML);
                instanciarAutocompletePrateleiras($('#input-prateleiras-modal'), true);
                eventoBtnTransferirLote($('#btn-verificar-disponibilidade-modal'), true);
            }
       });
    }

    function eventoBtnTransferirLote(JQelement, modal = false)
    {
        let seletorModal = modal ? '-modal' : '';

        JQelement.on('click', function() {
            $(this).attr('disabled', true);
            
            $(`#transferir-coluna${seletorModal}`).attr('disabled', true);
            $(`#transferir-linha${seletorModal}`).attr('disabled', true);
debugger
            $.ajax({
                method: 'GET',
                url: '../ajax/tag.php',
                dataType: 'json',
                data: {
                    action: 'transferirLote',
                    params: [
                        $(this).data('idtagdim'),
                        $(`#input-prateleiras${seletorModal}`).attr('cbvalue'),
                        idUnidade,
                        gIdEmpresa,
                        $(`#transferir-coluna${seletorModal}`).val(),
                        $(`#transferir-linha${seletorModal}`).val()
                    ]
                },
                success: res => {
                    $(this).removeAttr('disabled');
                    
                    if(res.error)
                    {
                        $(`#transferir-coluna${seletorModal}`).removeAttr('disabled');
                        $(`#transferir-linha${seletorModal}`).removeAttr('disabled');
                        return alertAtencao(res.error);
                    }

                    if($('#input-prateleiras-modal').attr('cbvalue') == idTag) {
                        const divQtdLoteDestino = $(`#tagdim-${res.idtagdimdestino} .lote-quantidade`);
                        const qtdLoteOrigem = $(`#tagdim-${res.idtagdimorigem} .lote-quantidade`).text() ? parseInt($(`#tagdim-${res.idtagdimorigem} .lote-quantidade`).text()) : 0;
                        const qtdLoteDestino = divQtdLoteDestino.text() ? parseInt(divQtdLoteDestino.text()) : 0;

                        // Atualizando quantidade de lotes na caixa
                        if(!divQtdLoteDestino.length) {
                            const JQdestino = $(`#tagdim-${res.idtagdimdestino}`);
                            const divTagDim = `<div class="relative">
                                    <i class="fa fa-archive mr-2" title=""></i>
                                    <div class="lote-quantidade">${qtdLoteOrigem + qtdLoteDestino}</div>
                                </div>`;

                            JQdestino.prepend(divTagDim)

                            JQdestino.on('click', () => {
                                mostralote(res.idtagdimdestino, JQdestino.data('label'));
                            });
                        } else {
                            divQtdLoteDestino.text(qtdLoteOrigem + qtdLoteDestino);
                        }

                        $(`#tagdim-${res.idtagdimdestino} .lote-quantidade`).text(qtdLoteOrigem + qtdLoteDestino);
                    }
                    

                    $(`#transferir-coluna${seletorModal}`).parent().remove();
                    $(`#transferir-linha${seletorModal}`).parent().remove();                 

                    $(this).remove();
                    $(`#input-prateleiras${seletorModal}`).attr('disabled', true);
                    $(`#input-prateleiras${seletorModal}`).autocomplete("destroy");

                    // Removendo registro lotes
                    if(modal)
                        $('div[class^="tagdim-"]').remove();

                    // Removendo caixa da prateleira
                    $(`#tagdim-${res.idtagdimorigem} div:first-child`).remove();
                    $('#cbModal').modal('hide');

                    alertSalvo('Lote transferido com sucesso!');
                },
                error: err => {
                    //
                }
            })
        });
    }

    function instanciarAutocompletePrateleiras(JQelement, modal = false)
    {
        if(!JQelement.get().length) return;

        JQelement.autocomplete({
            source: tagsPrateleiras,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    lbItem = `${item.sigla} - ${item.tag} ${item.descricao}`;
                    return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                };
            },
            select: function(event, ui) {
                $.ajax({
                    method: 'GET',
                    url: '../ajax/tag.php',
                    dataType: 'json',
                    data: {
                        action: 'buscarPrateleira',
                        params: ui.item.idtag
                    },
                    success: res => {
                        $(this).val(res.descricao);
                        $(this).attr('cbvalue', res.idtag);

                        let seletorModal = modal ? "-modal" : '';

                        $(`#transferir-coluna${seletorModal}`).removeAttr('disabled');
                        $(`#transferir-linha${seletorModal}`).removeAttr('disabled');
                        $(`#btn-verificar-disponibilidade${seletorModal}`).removeAttr('disabled');

                        // Criando options de acordo com a quantidade de colunas / linhas
                        let colunas = '',
                            linhas = '';

                        // Colunas
                        for(let i = 0; i <= parseInt(res.coluna); i++)
                        {
                            colunas += `<option value="${i}">${alfabeto[i]}</option>`;
                        }

                        // Linhas
                        for(let i = 1; i <= parseInt(res.linha); i++)
                        {
                            linhas += `<option value="${i}">${i}</option>`;
                        }

                        let seletorPai = "";

                        if(modal)
                            seletorPai = '#cbModalCorpo';
                        
                        $(`${seletorPai} #transferir-coluna${seletorModal}`)
                            .removeAttr('disabled')
                            .html($(colunas));
                        $(`${seletorPai} #transferir-linha${seletorModal}`)
                            .removeAttr('disabled')
                            .html($(linhas));

                        $(`#btn-verificar-disponibilidade${seletorModal}`).removeAttr('disabled');
                    }
                });
            }
        });
    }

    function montaLinhaColuna()
    {
        return `
            <table>
                <tr id="trdimensoes" > 
                    <td align="right">Linhas:</td> 
                    <td>
                        <select name="_1_i_tag_linha" vnulo>
                            <option value=""></option>
                                <? fillselect(TagController::$linhas); ?>		
                        </select>
                    </td>  
                    <td align="center">X</td>
                    <td align="right">Colunas:</td> 
                    <td>
                        <select name="_1_i_tag_coluna" vnulo>
                            <option value=""></option>
                                <? fillselect(TagController::$colunas); ?>
                        </select>
                    </td>
                    <td align="center">X</td>
                    <td align="right">Caixas:</td> 
                    <td>
                        <select name="_1_i_tag_caixa">
                            <option value=""></option>
                                <? fillselect(TagController::$caixas); ?>
                        </select>
                    </td>
                </tr>
            </table>
        `;
    }

    function cancelarLocacao(idTagReserva)
    {
        $.ajax({
            url: '../ajax/tag.php',
            method: 'GET',
            async: false,
            data: {
                action: 'cancelarLocacao',
                params: idTagReserva
            },
            success: response => {
                if(response.error)
                {
                    return alertAtencao(response.error);
                }

                alertSalvo('Locação cancelada');

                setTimeout(() => {location.reload()}, 2000);
            },
            error: err => {
                console.log(err);
            }
        });
    }

    let delay;

    $('#tag-descricao').autocomplete({
        source: tagsDescricoes,
        minLength: 3,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                lbItem = `${item.label}`;
                return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
            };
        }
    });

    $('#tag-descricao').off('blur');
    $('#tag-descricao').on('blur', function(e){
        $(this).autocomplete('close');
    });
    $('#tag-descricao').on('keyup', function()
    {
        clearInterval(delay);

        delay = setInterval(() => $(this).removeAttr('cbvalue'), 400);
    });


    $('.select-picker').selectpicker('render');

    function habilitaImpressao(labelHTML, checked = null) {
        const inputLinha = $(`#${$(labelHTML).attr('for')}`).get(0)

        if(!inputLinha.checked)
            if(!confirm('Carregar lotes para impressão?')) return;

        const caixaJQ = $(labelHTML).parent().find('+ div div[id^="tagdim"]:has( .lote-quantidade)');
        const idtagdimArr = caixaJQ.map(function() {
            return $(this).data('idtagdim')
        }).toArray();
        const label = caixaJQ.data('label');

        if(caixaJQ.length && !inputLinha.checked)
        {
            $('#impressao-por-lote').append(`<div id="cbCarregando" class="hideprint adicionado-manualmente"></div>`)
            mostralote(idtagdimArr.join('|'), label, checked !== null ? checked : inputLinha.checked, true);
        } else 
            removerLotesImp()

    }


</script>