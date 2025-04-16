<script src="../inc/js/svg.js" type="text/Javascript"></script>
<!-- PanZoom js -->
<script src='../inc/js/panzoom.min.js'></script>
<!-- Color palette -->
<script src="./../inc/js/mapaequipamento/color-palette.js" type="text/Javascript"></script>
<script type="text/Javascript">
    // Remover css para n ter conflito
    $('link[href="./inc/css/dashboard.css"]').remove();

    var tagsParaFiltragem = <?= $tags ?>;

    var idMapaEquipamentoAtual = "INSERT";

    var equipamentoCarregado = false;

    var blocos = <?= $blocosAtivosJSON ?>,
        blocoAtual = {},
        JQsvgArea = $('.svg-area');

    var JQitemsArea = $('.items'),
        circlesAtual = [];

    // Valores que possam vir do GET
    var idBlocoViaGet = '<?= $idBlocoViaGet ?>';
    var idSalaViaGet = '<?= $idSalaViaGet ?>' ;
    var idEquipamentoViaGet = '<?= $idEquipamentoViaGet ?>';

    /**
     * multiplica às coordenadas de acordo com o zoom
     */
    var zoomAtual = 0.5,
        svgWidth = 0,
        svgHeight = 0,
        posicaoX = false,
        posicaoY = false,
        zoomInicial = 0.5,
        coorenadasIniciaisZoom = {
            x: 1000,
            y: 500
        };

    var equipamentoAutoComplete = buscarTags();

    /**
     * Fonte
     * 
     * Unidade de medida: rem
     */
    var fontSize = 2.5;
    var tamanhoDaFontePadrao = 2.5;
    var tamanhoAtualDasFontesDosTitulos = tamanhoDaFontePadrao;

    var idSalaQuery = null;

    var ctrlKey = false;

    /**
     * Responsavel por guardar o id do setInterval para 
     * chamada do metodo updateDeviceInfo()
     */
    var updateDeviceInterval = false;

    // Tipos de titulos para sala, bloco e empresa
    let titleTypes = {
        sala: 'is-room',
        bloco: 'is-block',
        empresa: 'is-company',
        equipamento: 'is-equipment'
    };

    // Valore para o a lista de opcoes criadas com o btn direito
    var optionTipoObjeto = null;
    var optionIdObjeto = null;

    // Cores padroes
    var corPadraoEmpresa = '46, 149, 147';
    var corPadraoBloco = '46, 149, 147';
    var corPadraoSala = '128, 128, 128';
    var corPadraoEquipamento = '180, 200, 255';

    var salvarLayout = false;

    // Timeout para delay
    var delayPesquisa;

    // Inicializando variavei
    resetValues();

    // Mostrar esconder titulos
    showHideInfo();

    /**
     * Eventos para:
     * Remover, Atualizar, Alterar sala
     * ao ser focada
     */
    optionsEvent();

    /**
     * Faz uma requisicao de 30 em 30 segundo
     * para atualizar as informacoes dos devices
     * vinculados as tags do bloco atual
     */
    updateDeviceInfoInterval();

    if(!blocos.error)
    {
        $("#blocos").autocomplete({
            source: blocos,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    lbItem = `${item.descricao}`;
                    return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                };
            },
            select: function(event, ui) {
                carregarMapa(ui.item);
            }
        });
    } 
    // else {
    //     return blocos;
    // }


    // Criacao de zonas
    var draw = SVG().addTo('.svg-area').size("100%", "100%");
    var X = 0,
        Y = 0;

    var coordenadasL = [],
        mouseX = 0,
        L = "";
        mouseY = 0;

    var path = "";

    // Sala q o equipamento sera vinculado
    var idSalaVinculo = null,
        idCurrentPath = null;

    var i = 0;

    /**
     * Carregar equipamentos vinculados a unidade selecionada
     */

    /**
     * Posicionar equipamentos na ultima localização salva
     */

    // EventListener para BTN criar e cancelar delimitacao areas para vinculo
    $('#btn-create-room').on('click', btnCreateEvent);
    $('#btn-cancel-room').on('click', btnCancelEvent);
    // Delimitar com quadrado
    $('#btn-create-square').on('click', drawPathSquareEvent);

    // EventListener para BTN remover delimitacao de salas
    // $('#btn-remove-room').on('click', btnRemoveRoomEvent);
    // $('#btn-finish-room').on('click', finishRemovableEvent);

    // Evento para campo de filtragem de tags
    $('#tag-filter').on('keydown', eventoFiltragemDeTags);

    // EventListener para BTN mover salas
    $('#btn-move-room').on('click', btnMoveRoomEvent);

    // Salvar no click
    $('#btn-save').on('click', function()
    {
        if(idMapaEquipamentoAtual == 'INSERT')
        {
            return alertAtencao('Planta não encontrada!');
        }

        CB.post({
            objetos: {
                _x_u_mapaequipamento_idmapaequipamento: idMapaEquipamentoAtual,
                _x_u_mapaequipamento_json: `${JSON.stringify(blocoAtual.json)}`
            },
            refresh: false,
            parcial: true
        });
    });

    CB.prePost = function() {
        $('#input-idmapaequipamento').val(idMapaEquipamentoAtual);
        $('#input-json').val(`${JSON.stringify(blocoAtual.json)}`); 
    };

    // Remove elementos com ESC
    $(document).on('keydown', function(e)
    {
        if(e.keyCode == 27)
        {
            removeLock();
            hideCircles();
            disableCircles();
            // Cancelar definicao de sala
            btnCancelEvent();
            hideColorPalette();
            removeOptions();
            disableEquipment();
            removeActiveEquipment();
            esconderTitulos();

            optionIdObjeto = null;
            optionTipoObjeto = null;

            verificarSalvamentoDoLayout();

            return $('.choose-block').remove();
        }
    });

    // Setar ou sair da tela inteira
    $(document).on('fullscreenchange', enterExitFullScreen);

    // Filtrar equipamentos por tipo
    $('#tipo').on('change', function()
    {
        filtrarEquipamentosPorTipo($(this).val());
    });

    // Alterar unidade de medida
    $('#un').on('change', updateDeviceInfo);

    // Select Picker's
    $('select#tipo, select#un').selectpicker({
        selectAllText: '<span class="glyphicon glyphicon-check"></span>',
        deselectAllText: '<span class="glyphicon glyphicon-remove"></span>'
    }).selectpicker('render');

    // Limpar filtros
    $('#clear-filter').on('click', clearFilters);

    // Apresentar opções da empresa/bloco/sala ao clicar
    $('.main-content').on('click', 'path:not(.creating)', lockPath);

    // Travar tag no click
    $('.items').on('mousedown', '.item', lockEquipment);
    $('.items').on('mouseup', '.item', removeLockEquipment);

    // Abrir e fechar barra de ferramentas do mapa
    $('.open-close-tools').on('click', openOrCloseTools);

    // Mostrar / ocultar planta
    $('#btn-show-hide-room').on('click', showHideRoomImage);

    // Alterar cor da sala
    $('#cbModuloForm').on('click', '.item-color', changeColorRoom);

    // Mostrar esconder paleta
    $('#zoom-area').on('click', '#btn-show-hide-palette', showColorPalette);
    /**
     * Diminui um nivel no z-index nos items que sao draggables
     * para que as propriedades offsetX e offsetY venha do elemento svgarea
     * e nao do q esta sendo arrastado
     */
    $('.main-content').on('mousedown', '.title-path.is-equipment, .title-path.is-room, .title-path.is-block, .title-path.is-company', function()
    {
        $(this).addClass('z-index-0');
    });

    // Remover foco da sala ativa
    $('#zoom-area').on('click', removeLockOnClickOff);

    $('.main-content').on('mouseup', '.title-path.is-room, .title-path.is-block, .title-path.is-company, path:not(.creating)', function()
    {
        let JQelement = $(this);

        if(JQelement.prop('tagName') == 'path')
        {
            return $(`#title-${JQelement.attr('id')}`).removeClass('z-index-0');    
        }

        JQelement.removeClass('z-index-0');
    });

    /**
     * Mapa carregando salas e equipamentos de forma desordenada, buscar causa posteriormente
     */
    if(idBlocoViaGet)
    {
        $(() => setTimeout(() => carregarMapa(idBlocoViaGet), 200));
    }
    // $('.main-content').on('mousedown', '.item.locked-equipment', function()
    // {
    //     $('.item.locked-equipment').removeClass('z-index-main');
    // });

    // $('.main-content').on('mouseup', '.item.locked-equipment', function()
    // {
    //     $('.item.locked-equipment').addClass('z-index-main');
    // });

    /**
     * @param tag || idtag
     */
    function carregarMapa(tag)
    {
        let dadosTag = tag;

        if(typeof tag != 'object')
        {
            dadosTag = buscarTagPorIdTag(tag);
        }

        if(blocoAtual.id == dadosTag.idtag)
        {
            return false;
        }

        /**
         * Definir titulo no header
         */
        $('#bloco-title').text(`/${dadosTag.descricao}`);

        // updateDeviceInfoIntervalStop();

        /**
         * Reiniciar valores
         * @param all : se true, reseta o json do bloco atual
         */
        resetValues(true);
        clearPaths();
        clearText();
        clearItems();
        
        blocoAtual.id = dadosTag.idtag;
        blocoAtual.nome = dadosTag.descricao;
        blocoAtual.planta = dadosTag.caminho;

        /**
         * Carregado equipamentos para vinculo
         */
        /**
         * Adicionar as tag vinculadas
         * ao bloco selecionado no
         * autocomplete #tag para ser
         * adicionado ao mapa.
         * Será adicionado em:
         * salas[salaAtual].equipamentos
         */
        // loadTagAutoComplete();

        /**
         * Define o tamanho do svg de acordo com o tamanho da imagem
         * e insere a mesma da DOM
         */
        if(!mountImage())
        {
            /**
             * Reiniciar valores
             * @param all : se true, reseta o json do bloco atual
             */
            resetValues(true);
            clearPaths();
            clearText();
            clearItems();
            idMapaEquipamentoAtual = false;

            return false;
        };

        /**
         * Mostrar campo de equipamentos e btn's de edicao
         */
        $('.tag-input').removeClass('hidden');
        $('.tipo-input').removeClass('hidden');
        $('.un-input').removeClass('hidden');
        $('.map-header').removeClass('hidden');
        $('.tools').removeClass('hidden');

        /**
         * TODO: Verificar se esse mapa já existe
         * Se sim, apenas adicionar nova sala ao json
         * Caso contrário cria um novo mapa
         */
        loadBlocoJson();

        if(idMapaEquipamentoAtual == 'INSERT')
        {
            createOrUpdateMapaEquipamento();
        }

        /**
         * Carregando tag's (equipamentos ) da empresa
         * 
         * TODO:
         * Carregar quando a sala for vinculada à uma tag
         * - Buscar equipamentos dessa sala
         */
        // if(!equipamentoCarregado)
        // {
        //     equipamentoCarregado = true;
        //     loadEquipment();
        // }

        /**
         *  Carregar path's e circles
        */
        loadPath();

        /**
         * TODO: Transformar inicializacao svg em metodo
         */
        // svgInit();

        /**
         * Evento de click no titulo das tag para seu registro
         */
        clickRoomLinkEvent();

        /**
         * Evento de click na tag para seu registro
         */
        clickEquipmentLinkEvent();

        /**
         * Faz uma requisicao de 30 em 30 segundo
         * para atualizar as informacoes dos devices
         * vinculados as tags do bloco atual
         */
        // updateDeviceInfoInterval();

        /**
         * Remover drag dos equipamentos
         * habilitar apenas quando a sala do mesmo
         * estiver focada
         */
        disableEquipment();

        /**
         * Carregar paleta de cores
         */
        loadColorPalette(false, '#808080');

        eventosDeZoomDosTitulos();

        verificarSobreposicaoDeEquipamentosPorPath();
    }

    function eventoFiltragemDeTags(e)
    {
        if(e.keyCode != 13) return false;
    }

    // Drag and drop
    /**
     * Arrastar tag e alterar o vinculo da sala
     * com o drop
     */
    function itemsDraggablesEvent()
    {
        $('.item').draggable({
            start: function(event, ui){
                primeiroMovimento = true;

                /**
                 * Torna z index dos titulos negativos
                 * para n conflitar no drop do elemento
                 */
                $('.title-path').addClass('-z-index');
                $('.item.locked-equipment').removeClass('z-index-main');
                let idLine = false;

                if(!JQsvgArea.hasClass('show-all-title'))
                {
                    idLine = `line-title-${$(this).data('idtagpai')}`;
                }

                hideLine(idLine);

                if(!salvarLayout)
                {
                    salvarLayout = true;
                }

                ui.position.left = parseInt($(this).attr('originalpositionx'));
                ui.position.top = parseInt($(this).attr('originalpositiony'));

                /**
                 * Para contagem para atualizacao dos device's
                 */
                // updateDeviceInfoIntervalStop();
            },
            drag: function( event, ui ) {
                let idEquipamento = $(this).find('i').data('idtag');

                if(primeiroMovimento)
                {
                    ui.position.left = parseInt($(this).attr('originalpositionx'));
                    ui.position.top = parseInt($(this).attr('originalpositiony'));

                    primeiroMovimento = false;
                } else {
                    ui.position.left = event.offsetX - 4;
                    ui.position.top = event.offsetY - 2;
                }

                $(`#title-${idEquipamento}`).css('left', ui.position.left).css('top', (ui.position.top - 10));
            },
            stop: function( event, ui ) {
                let idLine = false,
                    idEquipamento = $(this).attr('id').split('-')[1],
                    idDoLocalDropado = $(event.toElement).attr('id'),
                    idUnidade = $(event.toElement).attr('idunidade');

                if(!JQsvgArea.hasClass('show-all-title'))
                {
                    idLine = `line-title-${$(this).data('idtagpai')}`;
                }

                $('.item.locked-equipment').addClass('z-index-main');
                showLine(idLine);

                // Locar Tag
                if($(this).attr('idempresa') != $(`path#${idDoLocalDropado}`).attr('idempresa'))
                {
                    let JQpath = $(`path#${idDoLocalDropado}`),
                        idDaEmpresaDaSalaDropada = JQpath.attr('idempresa');

                    if(!confirm(`${$(this).attr('title')} será locado. Deseja continuar?`))
                    {
                        $(this).css('left', `${$(this).attr('originalPositionX')}px`).css('top', `${$(this).attr('originalPositionY')}px`);
                    } else 
                    {
                        let tagLocada = locarTag(idEquipamento, idDaEmpresaDaSalaDropada, idUnidade);

                        idEquipamento = tagLocada.idtag || tagLocada.idobjeto;
                    }
                }

                if(idDoLocalDropado != $(this).attr('data-idtagpai'))
                {
                    if(confirm(`Deseja alterar ${$(this).attr('title')} para ${$('#'+idDoLocalDropado).attr('title')}?`))
                    {
                        updateIdTagSalaPai(event, $(this));
                    } else {
                        $(this).css('left', `${$(this).attr('originalPositionX')}px`).css('top', `${$(this).attr('originalPositionY')}px`);
                    }
                } else 
                {
                    updateEquipmentById(idEquipamento, {
                        x: event.offsetX - 4,
                        y: event.offsetY - 2
                    });

                    $(this).attr('originalPositionX', event.offsetX - 4);
                    $(this).attr('originalPositionY', event.offsetY - 2);
                }

                $('.title-path').removeClass('-z-index');

                verificarSobreposicaoDeEquipamentos($(this).data('idtagpai'), idEquipamento);
            },
            revert: false,
            tolerance: "intersect",
            helper: "original"
        });
    }

    function verificarSalvamentoDoLayout()
    {
        if(salvarLayout)
        {
            createOrUpdateMapaEquipamento();

            salvarLayout = false;
        }
    }

    function verificarSobreposicaoDeEquipamentosPorPath()
    {
        let JQpaths = $('path[tipo="room"], path[tipo="block"]');

        JQpaths.each(function(key) {
            verificarSobreposicaoDeEquipamentos(this.id);
        });
    }

    /**
     * Remover foco de salas ou equipamento ao clicar fora dos mesmos
     */
    function removeLockOnClickOff(e)
    {
        let JQelement = $(e.target);

        if(JQelement.prop('tagName') != 'path' && JQelement.prop('tagName') != 'I'
            && JQelement.prop('tagName') != 'BUTTON' && JQelement.prop('tagName') != 'SPAN'
            && !JQelement.hasClass('item') && !JQelement.hasClass('title-path')
            && !JQelement.hasClass('main-content') && JQelement.attr('id') != 'choose'
            && !JQelement.hasClass('icon')
        )
        {
            hideColorPalette();

            // Remover cor de foco dos ultimo path selecionado
            removeLock();
            removeActiveEquipment();
            hideCircles();
            disableCircles();
            disableEquipment();
            esconderTitulos();
            hideOptions();
            removerBlocoDeEscolhaDaTagParaVinculo();
            verificarSalvamentoDoLayout();

            optionTipoObjeto = null;
            optionIdObjeto = null;
        }
    }

    function updateIdTagSalaPai(event, JQelement)
    {
        idSalaVinculo ='DESVINCULAR';

        let idEquipamento = JQelement.find('i').data('idtag'),
            registroSala = buscarTagPaiOuFilho(idEquipamento, 'idtag');

        /**
         * Pegar id do pai do elemento(sala) onde esta sendo droppado
         */
        if($(event.toElement).data('idtagpai'))
        {
            idSalaVinculo = $(event.toElement).data('idtagpai');
        }

        /**
         * Pega id do elemento (sala) caso ele n tenha pai
         */
        if(idSalaVinculo == 'DESVINCULAR' && event.toElement.id)
        {
            idSalaVinculo = event.toElement.id;
        }

        // if($(event.toElement).prop('tagName') == 'tspan')
        // {
        //     idSalaVinculo = $(event.toElement).parent().attr('href').replace('#', '');
        // }

        let idSala = null;

        if(registroSala && !registroSala.error)
        {
            idSala = registroSala.idtagsala
        }

        /**
         * Faz uma requisicao de 30 em 30 segundo
         * para atualizar as informacoes dos devices
         * vinculados as tags do bloco atual
         */
        // updateDeviceInfoInterval();

        /**
         * Caso a sala seja a mesma, nao atualiza
         */
        if(parseInt(idSalaVinculo) == JQelement.attr('data-idtagpai'))
        {
            /**
            * Salva as últimas coordenadas da tag dropada
            */
            let newValue = {
                idtagpai: idSalaVinculo,
                x: event.offsetX,
                y: event.offsetY
            };

            updateEquipmentById(idEquipamento, newValue);

            // Atualizar input para salvar com ctrl + S
            $('input#input-json').val(JSON.stringify(blocoAtual.json));

            $('.title-path').removeClass('-z-index');

            return;
        }

        /**
         * Remove registro de vinculo
         * caso a tag não estteja em nenhuma sala
         */
        if(idSalaVinculo == 'DESVINCULAR')
        {
            CB.post({
                objetos: {
                    "_x_d_tagsala_idtagsala": idSala
                },
                parcial: true,
                refresh: false
            });

            /**
             * Atualizar equipamentos na DOM
             */

            $('.title-path').removeClass('-z-index');
            return JQelement.removeAttr('data-idtagpai');
        }

        /**
         * Verificar se a sala dropada possi vinculo
         */
        if(idSalaVinculo.indexOf('sem') !== -1)
        {
            alertAtencao('Área sem vínculo.');
        } else
        {
            /**
            * atualizar idtagpai da tag dropada nessa sala
            */
            if(idSala)
            {
                CB.post({
                        objetos: {
                                "_x_u_tagsala_idtagsala": idSala,
                                "_x_u_tagsala_idtagpai": idSalaVinculo
                        },
                        parcial: true,
                        refresh: false
                });
            } else {
                    CB.post({
                        objetos: {
                                "_x_i_tagsala_idtag": idEquipamento,
                                "_x_i_tagsala_idtagpai": idSalaVinculo
                        },
                        parcial: true,
                        refresh: false
                });
            }
        }

        /**
        * Salva as últimas coordenadas da tag dropada
        */
        let newValue = {
            idtagpai: idSalaVinculo,
            x: event.offsetX,
            y: event.offsetY
        };

        updateEquipmentById(idEquipamento, newValue);

        createOrUpdateMapaEquipamento();

        JQelement.attr('data-idtagpai', idSalaVinculo);
        $('.title-path').removeClass('-z-index');
    }

    function locarTag(idTag, idEmpresa, idUnidade)
    {
        let tagLocada = false,
            JQequipamento = $(`#item-${idTag}`);;
        
        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'locarTag',
                params: [idTag, idEmpresa, idUnidade]
            },
            success: response => {
                tagLocada = response;
            },
            error: err => {
                console.log(err);
            }
        });

        if(!tagLocada.error)
        {
            updateEquipmentById(JQequipamento.attr('id').split('-')[1], {
                idtag: tagLocada.idtag ? tagLocada.idtag : tagLocada.idobjeto,
                tag: tagLocada.tag,
                idempresa: idEmpresa
            });
        }

        return tagLocada;
    }

    function changeColorRoom(e)
    {
        hideColorPalette();
        if(!path)
        {
            return alertAtencao('Sala não encontrada');
        }

        let JQelement = $(e.currentTarget),
            idPath = path.idSala ? path.idSala : path.attr('id')
            JQpath = $(`path#${idPath}`);

        let colorRgb = hexToRgb(JQelement.data('color')),
            fill = `rgba(${colorRgb}, .1)`,
            stroke = `rgba(${colorRgb}, 1)`;

        JQpath.attr('fill', fill);
        JQpath.attr('stroke', stroke);

        // Alterando cor da linha e titulo relacionados a sala
        let JQtitle = $(`#title-${idPath} h1`).css('backgroundColor', stroke),
            JQline = $(`#line-title-${idPath}`).attr('stroke', stroke);

        if(JQtitle.parent().find('.indicador-pressao').length)
        {
            JQtitle.parent().find('.indicador-pressao').css('backgroundColor', stroke);
        }

        updateSalaById(idPath, {
            fill: fill,
            stroke: stroke,
            cor: colorRgb
        });

        atualizarTagPorIdTag(idPath, {
            cor: JQelement.data('color')
        });
    }

    function atualizarTagPorIdTag(id, newValue)
    {
        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'atualizarTagPorIdTag',
                params: {
                    id: id,
                    values: newValue,
                    typeParam: 'array'
                }
            },
            success: response => {
            
            },
            error: err => {
                console.log(err);
            }
        });
    }

    function titleDraggableEvent(id = false)
    {
        let JQequipamento = null;

        let JQTitleElement = $(".title-path.is-room, .title-path.is-block, .title-path.is-company"),
            addX = 0,
            isDiv = false;

        let idObjeto = id.split('-')[1];

        if(id)
        {
            JQTitleElement = $(`div#${id}`);
        }

        JQTitleElement.draggable({
            start: function(event, ui){
                let JQthis = $(this);
                // JQthis.addClass('z-index-0');
                hideOptions();
                /**
                 * Removendo classe para o equipamento nao interferir
                 * no drag quando o cursor passar por cima do equipamento
                 */
                $('.item.locked-equipment').removeClass('z-index-main');

                if(JQsvgArea.hasClass('show-all-title') && !$(this).hasClass('is-equipment'))
                {
                    hideLine(`line-${JQthis.attr('id')}`, true);
                }

                let equipamentoId = JQthis.attr('id').split('-')[1];

                $(`.title-path:not(#${JQthis.attr('id')})`).addClass('-z-index');

                addX = JQthis[0].clientWidth / 2;

                ui.position.left = event.offsetX - addX;
                ui.position.top = event.offsetY;
                
                JQequipamento = $(`i[data-idtag='${equipamentoId}']`).parent().parent();
            },
            drag: function(event, ui) {
                ui.position.left = event.offsetX - addX;
                ui.position.top = event.offsetY;

                if($(this).hasClass('is-room'))
                {
                    moveLine(`line-${id}`, event.offsetX, event.offsetY);   
                }

                // if(JQequipamento && JQequipamento.length)
                // {
                //     JQequipamento.css('left', ui.position.left).css('top', (ui.position.top + 10));
                // }
            },
            stop: function(event, ui)
            {
                let JQthis = $(this),
                    textCoordenates = {
                        titleX: event.offsetX - addX,
                        titleY: event.offsetY
                    };

                $('.item.locked-equipment').addClass('z-index-main');
                if(JQsvgArea.hasClass('show-all-title') && !$(this).hasClass('is-equipment'))
                {
                    showLine();
                }

                $(this).removeClass('z-index-0');
                $(`.title-path:not(#${$(this).attr('id')})`).removeClass('-z-index');

                if(JQthis.hasClass('is-block'))
                {
                    updateBlocoById(idObjeto, textCoordenates);
                } else 
                {
                    updateSalaById(idObjeto, textCoordenates);
                }

                // if(JQequipamento && JQequipamento.length)
                // {
                //     updateIdTagSalaPai(event, JQequipamento);   
                // }

                createOrUpdateMapaEquipamento();
                showOptions();
            },
            helper: 'original',
            revert: false,
            tolerance: "intersect",
        });
    }

    /**
     * TODO: Criar metodo para tela inteira
     */
    function enterExitFullScreen()
    {
        if($('#cbMenuSuperior').hasClass('hidden'))
        {
            $('#cbMenuSuperior').removeClass('hidden');
            $('.main-content').removeClass('full-map');
        } else 
        {
            $('#cbMenuSuperior').addClass('hidden');
            $('.main-content').addClass('full-map');
        }
    }

    /**
    * Vai para o link da tela de cadastro da tag no doubleClick
    * */ 
    function clickEquipmentLinkEvent()
    {
        $('.main-content').on('dblclick', '.item', function()
        {
            let JQelement = $(this);
            let idTag = JQelement.find('i').data('idtag');

            openTagLink(idTag);
        });

        $('.main-content').on('dblclick', 'path', function()
        {
            let JQelement = $(this);
            let idTag = JQelement.attr('id');

            openTagLink(idTag);
        });
    }

    /**
    * Vai para o link da tela de cadastro da tag no doubleClick
    * */ 
    function clickRoomLinkEvent()
    {
        $('.main-content').on('click', '.title-path, .indicador-pressao', function()
        {
            let JQelement = $(this);
            let idTag = JQelement.attr('id').split('-')[1];

            openTagLink(idTag);
        });
    }

    function openTagLink(idtag)
    {
        let link = `/?_modulo=tag&_acao=u&idtag=${idtag}`;
        window.open(link, '_blank');
    }

    /**
     * Travar cor e redimensionamento na sala selecionada
     */
    function lockPath(e)
    {
        if(e.ctrlKey)
        {
            return;
        }

        let JQpathSelected = $(e.currentTarget),
            pathId = JQpathSelected.attr('id'),
            type = JQpathSelected.attr('tipo'),
            isSquare = JQpathSelected.attr('form') == 'square' ? true : false;

        if(pathId == optionIdObjeto)
        {
            removeOptions();
            createOptions(((mouseX + JQpathSelected[0].instance.width()) + 10), mouseY, type);
            lockEquipmentBySalaId(pathId);

            return;
        }

        // Atualizar tamanho da fonte
        $(`#title-${pathId} h1`).css('fontSize', `${tamanhoAtualDasFontesDosTitulos}rem`);

        verificarSalvamentoDoLayout();

        removerBlocoDeEscolhaDaTagParaVinculo();

        hideColorPalette();

        // Remover cor de foco dos ultimo path selecionado
        removeLock();
        removeActiveEquipment();
        hideCircles();
        disableCircles();
        disableEquipment();

        optionTipoObjeto = $(e.currentTarget).prop('tagName');
        optionIdObjeto = optionTipoObjeto != 'path' ? $(e.currentTarget).find('i').data('idtag') : $(e.currentTarget).attr('id');

        // Esconde titulo da sala clicada anteriormente
        if(!JQsvgArea.hasClass('show-all-title') && ((optionTipoObjeto && optionTipoObjeto == 'path') && optionIdObjeto))
        {
            esconderTitulos(`title-${pathId}`, true);
        }

        lockEquipmentBySalaId(pathId);
        lockTitleById(`title-${pathId}`);
        mostrarTitulos(`title-${pathId}`, true);
        enableEquipmentBySalaId(pathId);

        lockLineBySalaId(pathId);

        // Definindo path clicado como atual
        // if(optionIdObjeto.indexOf('sem') === -1)
        // {
            if(JQpathSelected.attr('tipo') == 'block')
            {
                path = getBlocoById(pathId);
            } else 
            {
                path = getSalaById(pathId);
            }
        // }

        if(!path)
        {
            console.log('Id da sala selecionada não encontrado no JSON');
        }

        JQpathSelected.attr('class', 'locked');

        pathResize(pathId, true);
        showCircles(pathId, isSquare);
        enableCircles(pathId, isSquare);

        /**
         * Valores globais das coordenadas da 
         * sala focada
         */
        mouseX = JQpathSelected[0].instance.x();
        mouseY = JQpathSelected[0].instance.y();

        createOptions(((mouseX + JQpathSelected[0].instance.width()) + 10), mouseY, type);

        if(isSquare)
        {
            for(let i = 1; i <= 4; i++)
            {
                $(`circle.circle-${pathId}[side=${i}]`).attr('disabled', true);
            }

            // Aumentar ou diminuir quadrado a partir dos lados
            sizePathSquareEvent();
        }
        esconderTitulos(false, false, false, false);
        mostrarTitulosDoPathFocado();
    }

    function verificarSobreposicaoDeEquipamentos(idPath, idEquipamento = false)
    {
        let JQequipamento = false;
        if(idEquipamento)
        {
            JQequipamento = $(`div#item-${idEquipamento}[data-idtagpai="${idPath}"]`).get();
        }

        let JQequipamentos = $(`div[data-idtagpai="${idPath}"]`).get();

        $('div.verified').removeClass('verified');

        for(let chaveElemento in (JQequipamento ? JQequipamento : JQequipamentos))
        {
            let JQequipamentoAtual = $((JQequipamento ? JQequipamento : JQequipamentos)[chaveElemento]);

            let medidasDoElemento = {
                largura: JQequipamentoAtual.width(),
                altura: JQequipamentoAtual.height()
            };

            let coordernadasXY = {
                x: {
                    inicio: parseInt(JQequipamentoAtual.css('left').replace('px', '')),
                    fim: (parseInt(JQequipamentoAtual.css('left').replace('px', '')) + medidasDoElemento.largura)
                },
                y: {
                    inicio: parseInt(JQequipamentoAtual.css('top').replace('px', '')),
                    fim: (parseInt(JQequipamentoAtual.css('top').replace('px', '')) + medidasDoElemento.altura)
                }
            };

            for(let chaveElementoVerificacao in JQequipamentos)
            {
                let JQequipamentoVerificacao = $(JQequipamentos[chaveElementoVerificacao]);
                let verified = JQequipamentoVerificacao.hasClass('verified');

                if(JQequipamentoVerificacao.attr('id') == JQequipamentoAtual.attr('id')) continue;

                let coordernadasVerificacaoXY = {
                    x: {
                        inicio: parseInt(JQequipamentoVerificacao.css('left').replace('px', '')),
                        fim: (parseInt(JQequipamentoVerificacao.css('left').replace('px', '')) + medidasDoElemento.largura)
                    },
                    y: {
                        inicio: parseInt(JQequipamentoVerificacao.css('top').replace('px', '')),
                        fim: (parseInt(JQequipamentoVerificacao.css('top').replace('px', '')) + medidasDoElemento.altura)
                    }
                };

                // Verificar se elementos se encontram no eixo X
                if( // Verificar se elementos se encontram no eixo X
                    // (coordernadasXY.x.inicio > coordernadasVerificacaoXY.x.inicio && coordernadasXY.x.inicio < coordernadasVerificacaoXY.x.fim ||
                    // coordernadasXY.x.fim > coordernadasVerificacaoXY.x.inicio && coordernadasXY.x.fim < coordernadasVerificacaoXY.x.fim ) &&
                    // // Verificar se elementos se encontram no eixo Y
                    // (coordernadasXY.y.inicio < coordernadasVerificacaoXY.y.fim  && coordernadasXY.y.inicio > coordernadasVerificacaoXY.y.inicio ||
                    // coordernadasXY.y.fim > coordernadasVerificacaoXY.y.inicio && coordernadasXY.y.fim < coordernadasVerificacaoXY.y.fim)
                    (coordernadasXY.x.inicio == coordernadasVerificacaoXY.x.inicio && coordernadasXY.x.fim == coordernadasVerificacaoXY.x.fim) &&
                    (coordernadasXY.y.inicio == coordernadasVerificacaoXY.y.inicio && coordernadasXY.y.fim == coordernadasVerificacaoXY.y.fim)
                )
                {
                    if(verified)
                    {
                        JQequipamentoVerificacao.removeClass('overlapping');
                    }

                    JQequipamentoAtual.addClass('verified overlapping');

                    break;
                }

                JQequipamentoAtual.removeClass('overlapping');
            }
        }
    }

    /**
     * Travar tag selecionada
     */
    function lockEquipment(e)
    {
        if(e.ctrlKey)
        {
            return;
        }

        let JQEquipmentSelected;

        // Remover cor de foco dos ultimo path selecionado
        // hideCircles();
        // removeLockEquipment();
        removeActiveEquipment();
        // disableCircles();

        removeOptions();

        if(['integer', 'string'].includes(typeof e))
        {
            JQEquipmentSelected = $(`#item-${e}`);
        } else 
        {
            JQEquipmentSelected = $(e.currentTarget);
        }

        // disableEquipment(JQEquipmentSelected.attr('id'), true);

        JQEquipmentSelected.addClass('locked-equipment active-equipment');
    }

    function lockEquipmentBySalaId(id)
    {
        $(`div[data-idtagpai='${id}']`).addClass('locked-equipment active-equipment');
    }

    function lockLineBySalaId(id)
    {
        $(`path#line-title-${id}`).attr('class', 'locked');
    }

    function lockTitleById(id)
    {
        $(`div#${id}`).find('h1').addClass('locked');
    }

    // Remover cor de foco dos ultimo path selecionado
    function removeLock()
    {
        $('.locked').removeAttr('class');
        $('.locked-equipment').removeClass('locked-equipment');
    };

    function removeLockEquipment(id = false)
    {
        if(id && typeof id != 'object')
        {
            return $(`div#${id}`).removeClass('locked-equipment');
        }

        $('.locked-equipment').removeClass('locked-equipment');
    }

    function removeActiveEquipment(id = false)
    {
        if(id)
        {
            return $(`div#${id}`).removeClass('active-equipment');
        }

        $('.active-equipment').removeClass('active-equipment');
    }

    // Mostrar circles
    function showCircles(pathId = null, isSquare = false)
    {
        let JQallCircles = $(`circle`);

        if(pathId)
        {
            // Pegar id do path para buscar circles vinculados
            JQallCircles = $(`circle.circle-${pathId}`);

            if(isSquare)
            {
                JQallCircles = $(`circle[class*='circle-${pathId}']`);
            }
        }

        JQallCircles.each((index, element) => {
            // let elementClass = $(element).attr('class').replace('opacity-0', '');
            // elementClass = `${elementClass} opacity-1`;

            $(element).attr('active', true);
        })
    }

    // Econder circles
    function hideCircles(pathId = null)
    {
        let JQallCircles = $(`circle`);

        if(pathId)
        {
            // Pegar id do path para buscar circles vinculados
            JQallCircles = $(`circle.circle-${pathId}`);
        }

        JQallCircles.each((index, element) => {
            // let elementClass = $(element).attr('class').replace('opacity-1', '');
            // elementClass = `${elementClass} opacity-0`;

            $(element).removeAttr('active');
        });
    }

    // Desabilitar eventos da tag circle
    function disableCircles(idPath = null)
    {
        let JQcircles = $(`circle:not(.pe-none)`);

        if(idPath)
        {
            JQcircles = $(`circle.circle-${pathId}`);
        }
        
        JQcircles.attr('disabled', true);
    }

    // Habiliar eventos da tag circle
    function enableCircles(pathId = null)
    {
        let JQcircles = $(`circle.pe-none, circle[disabled]`);

        if(pathId)
        {
            JQcircles = $(`circle.circle-${pathId}`);
        }

        JQcircles.removeAttr('disabled');
        JQcircles.css('pointerEvents', 'default');
    }

    /**
     * Desabilitar eventos dos equipamentos
     */
    function disableEquipment(id = null, not = false)
    {
        if(id)
        {
            if(not)
            {
                return $(`div.item:not(.pe-none):not(#${id})`).addClass('pe-none');
            }

            return $(`div#${id}']`).addClass('pe-none');
        }

        $(".item:not(.pe-none)").addClass('pe-none');
    }

    /**
     * Habilitar eventos dos equipamentos
     */
    function enableEquipment(id = null)
    {
        if(id)
        {
            return $(`i[data-idtag='${id}']`).parent().parent().removeClass('pe-none');
        }

        $(".item.po-none").removeClass('pe-none');
    }

    /**
     * Habilitar eventos dos equipamentos pelo id da sala
     */
    function enableEquipmentBySalaId(id)
    {
        $(`div[data-idtagpai='${id}']`).removeClass('pe-none');
    }

    /**
     * Redimencionar path (sala)
     */
    function pathResize(pathId, isSquare = false)
    {
        // Pegar id do path para buscar circles vinculados
        let JQallCircles = $(`circle`),
            JQcircles = $(`circle[class*='circle-${pathId}']`),
            JQpath = null;

        let keyCurrentCoordinate = null,
            dAttribute = null;

        let JQline = $(`#line-title-${pathId}`),
            JQlineTitleCoordenate = false;

        if(JQline.length)
        {
            JQlineTitleCoordenate = {
                from: [JQline.attr('d').split(' ')[0].replace('M', ''), JQline.attr('d').split(' ')[1]],
                to: [JQline.attr('d').split(' ')[2].replace('L', ''), JQline.attr('d').split(' ')[3]]
            }
        }

        JQcircles.draggable({
            start: function(e, element)
            {
                // Circle atual
                let JQelement = $(e.target)
                JQpath = $(`path#${JQelement.attr('class').replace('circle-', '')}`);

                // Posicao da coordenada que sera afetada
                keyCurrentCoordinate = null;
                dAttribute = JQpath.attr('d').split(' ');

                // Buscando coordenada que será afetada no path
                dAttribute.forEach((element, key) => {
                    if((element == `L${JQelement.attr('cx')}` || element == `M${JQelement.attr('cx')}`) && dAttribute[key + 1] == `${JQelement.attr('cy')}`)
                    {
                        keyCurrentCoordinate = key;

                        return;
                    }
                })
            },
            drag: function(e, element)
            {
                // Atualizando path
                if(keyCurrentCoordinate !== null)
                {
                    // Atualizando posicao do circle
                    $(this).attr('cx', e.offsetX).attr('cy', e.offsetY);

                    dAttribute[keyCurrentCoordinate] = `${dAttribute[keyCurrentCoordinate].substr(0, 1) == 'L' ? 'L' : 'M'}${e.offsetX}`;
                    dAttribute[keyCurrentCoordinate + 1] = `${e.offsetY}`;

                    JQpath.attr('d', dAttribute.join(' '));

                    if($(this).attr('side') == 1 && JQline)
                    {
                        // Manter ligacao com title
                        JQline.attr('d', `M${e.offsetX} ${e.offsetY} L${JQlineTitleCoordenate.to[0]} ${JQlineTitleCoordenate.to[1]}`);   
                    }
                }
            },
            stop: function(e, element)
            {
                if(keyCurrentCoordinate !== null)
                {
                    // Atualizando coordenadas do path no objeto do blocoAtual
                    updatePathBySalaId(JQpath.attr('id'), dAttribute.join(' '));

                    // Atualizando coordenadas do cicle no objeto do blocoAtual
                    updateCircleByPathId(JQpath.attr('id'), $(this).attr('id'), {
                        cx: $(this).attr('cx'),
                        cy: $(this).attr('cy')
                    });

                    createOrUpdateMapaEquipamento();
                }
            },
            revert: false,
            tolerance: "intersect",
            helper: "original"
        });

        // $('circle').on('mouseenter', function(e){
            
        // });

        // $('circle').on('mouseleave', function(e){
        //     let JQelement = $(e.target);
        // });
    }

    // Evento para definir quadrado
    function defineSquarePathEvent(e)
    {

    }

    function openOrCloseTools(e)
    {
        let JQelement = $(e.currentTarget);

        if(JQelement.hasClass('opened'))
        {
            JQelement.removeClass('opened');
            $('.map-header').css('transform', 'translateY(-100%)');
            $('.tools-list').css('transform', 'translateX(-100%)');
        } else
        {
            JQelement.addClass('opened');
            $('.map-header').css('transform', 'translateY(0%)');
            $('.tools-list').css('transform', 'translateX(0%)');
        }
    }

    /**
    * Definir cursor no elemento svg
    */
    function defineCursor(type = 'default')
    {
        $('body').css('cursor', type);
    }

    // Evento para definir zona
    function btnCreateEvent(e)
    {
        btnCancelEvent();

        // Remover foco da sala ativa
        $('#zoom-area').off('click', removeLockOnClickOff);
        $('.main-content').off('click', 'path:not(.creating)', lockPath);
        JQsvgArea.addClass('creating-room');

        removeActiveTool();
        defineCursor('crosshair');

        /**
        * Para contagem para atualizacao dos device's
        */
        // updateDeviceInfoIntervalStop();

        let JQelement = $(e.currentTarget);
        resetValues();

        drawPathEvent();
        JQelement.addClass('active-tool');
        // $('#btn-cancel-room').removeClass('hidden');
    }

    function removeActiveTool()
    {
        $('.active-tool').removeClass('active-tool');
    }

    function setActiveTool(JQelement)
    {
        removeActiveTool();

        JQelement.addClass('active-tool')
    }

    /**
    * Cancelar criacao de zona
    */
    function btnCancelEvent(e)
    {
        SVG.off(document, 'mouseup', finishPathSquare);
        SVG.off(document, 'mousedown', createPathSquare);
        SVG.off(document, 'mousemove', sizePathSquare);

        $('.main-content').off('click', 'path:not(.creating)', lockPath);
        $('.main-content').on('click', 'path:not(.creating)', lockPath);
        $('#zoom-area').off('click', removeLockOnClickOff);
        $('#zoom-area').on('click', removeLockOnClickOff);

        $('.active-tool').removeClass('active-tool');
        JQsvgArea.removeClass('creating-room');

        defineCursor();
        /**
        * Iniciar contagem para atualizacao dos device's
        */
        // updateDeviceInfoInterval();

        let JQelement = $('#btn-cancel-room');

        // if(!e)
        // {
        //     /**
        //      * Reseta valores do path
        //      */
        //     resetValues();
        //     disableRoomCreation();

        //     return JQelement.addClass('active-tool');
        // }
        if(e)
        {
            JQelement = $(e.currentTarget);
        }

        if(path)
        {
            if(path.node && (path.attr('class') && path.attr('class').search('creating') !== -1))
            {
                /**
                * Remove path da DOM
                */
                removePathById(path.attr('id'));

                /**
                * Reseta valores do path
                */
                resetValues();
            }
        }

        disableRoomCreation();

        if(JQelement)
        {
            JQelement.addClass('active-tool');
        }
        // $('#btn-create-room').removeClass('hidden');

        /**
        * Remover bloco de seleção de sala
        */
        $('.choose-block').remove();
    }

    /**
    * Remove path atual da dom
    */
    // Nao sera necessario
    // function removeCurrentPath()
    // {
    //     // Remover pontos
    //     $(`path#${idCurrentPath} ~ circle`).remove();
    //     // Remover path atual
    //     $(`path#${idCurrentPath}`).remove();
    // }

    /**
    * Remove path da dom
    * @param id idtagsala
    */
    function removePathById(id)
    {
        // Remover pontos
        $(`circle[class*='circle-${id}']`).remove();
        // Remover path atual
        $(`path#${id}`).remove();
        removeTitle(`title-${id}`);
    }

    /**
    * Evento para remover salas
    */
    function btnRemoveRoomEvent(e)
    {
        /**
        * Para contagem para atualizacao dos device's
        */
        // updateDeviceInfoIntervalStop();

        let JQelement = $(e.currentTarget);

        $('#btn-finish-room').removeClass('hidden');
        $('#btn-remove-room').addClass('hidden');

        /**
        * Adiciona a classe removable
        * para aquela sala ser reconhecida
        * como removivel
        */
        makeAllRemovable();
    }

    /**
    * Evento para mover salas
    */
    function btnMoveRoomEvent(e)
    {
        let JQelement = $(e.currentTarget);

        $('#btn-finish-room').removeClass('hidden');
        $('#btn-remove-room').addClass('hidden');

        /**
        * Adiciona a classe removable
        * para aquela sala ser reconhecida
        * como movel
        */
        makeAllRoomsMovable();
    }

    /**
    * Tornar todos os paths (salas) removiveis
    * com o click
    */
    function makeAllRemovable()
    {
        $('path').attr('class', 'removable');
        $('.item').addClass('removable');

        setTimeout(() => {
            $('.removable').on('click', removableEvent);
        }, 200);
    }

    /**
    * Tornar todos os paths (salas) moviveis
    * com o click
    */
    function makeAllRoomsMovable()
    {
        $('path').attr('class', 'movable');

        setTimeout(() => {
            $('.movable').draggable({
                start: function(event, ui)
                {
                    if(posicaoX === false || posicaoY === false)
                    {
                        posicaoX = $(this).offset().left;
                        posicaoY = $(this).offset().top;
                        // console.log(`${posicaoX}, ${event.offsetX}, ${ui.position.left}`);
                    }
                },
                drag: function(event, ui)
                {   
                    // console.log(event.offsetX, ui.position.left);

                    $(this).css('transform', `translate(${ui.position.left - posicaoX}px, ${ui.position.top - posicaoY}px)`);
                },
                revert: false,
                tolerance: "intersect",
                helper: "original"
            });
        }, 200);
    }

    /**
    * Pegar coordenadas X Y em relacao ao ultimo evento do mouse
    */
    function getLastCoordinates(e)
    {
        // console.log(e);
        // console.log(`X: ${currentEvent.screenX - previousEvent.screenX}`);
    }

    /**
    * Evento para remover sala da DOM
    * e atualizar colunas json 
    * no registro da tabela
    * mapaequipamento e desvincular
    * todas as tags dessa sala
    */
    function removableEvent()
    {
        let isPath = optionTipoObjeto == 'path' ? true : false,
            JQelement = isPath ? $(`path#${optionIdObjeto}`) : $(`div#item-${optionIdObjeto}`),
            elementTitle = JQelement.attr('title');

        if(!confirm(`Remover ${elementTitle}?`))
        {
            return console.log('não remove');
        }

        let countEquipments = $(`.item[data-idtagpai='${$(JQelement).attr('id')}']`).length,
            countRoom = $(`path[idtagpai='${$(JQelement).attr('id')}']`).length;

        if(countEquipments && !confirm(`Há ${countEquipments} equipamentos vínculados a essa sala. Deseja mesmo remover?`))
        {
            return console.log('não remove (equipamentos)');
        }

        if(countRoom && !confirm(`Há ${countRoom} salas vínculadas a essa sala. Deseja mesmo remover?`))
        {
            return console.log('não remove (salas)');
        }


        if(!isPath)
        {
            /**
            * Id da equipamento(tag) selecionada
            */
            // let idTagEquipamento = $(JQelement).children(0).data('idtag');
            let idTagEquipamento = $(JQelement).find('i').data('idtag');
            /**
            * Remover equipamento do objeto blocoAtual
            * e atualizar o registro no mapaequipamento
            */
            let equipamentoKey = false;
            getEquipments().forEach((element, key) => {
                if(element.idtag == idTagEquipamento)
                {
                    return equipamentoKey = key;
                }
            });
            
            if(equipamentoKey === false)
            {
                return console.log('Equipamento não encontrada no json do blocoAtual');
            }

            /**
            * Adicionando equipamento novamente ao autocomplete
            */
            let equipamento = getEquipamentoById(idTagEquipamento);

            if(equipamento)
            {
                equipamento.descricao = equipamento.title;
                equipamentoAutoComplete.push(equipamento);
            }

            // $( "#tag" ).autocomplete( "option", "source", equipamentoAutoComplete);

            // let idSala = $(e.target).parent().data('idtagpai');
            removeEquipamentoByKey(equipamentoKey);

            /**
            * Desvincular tag's das salas
            */
            /**
            * pegar todos equipamentos vinculados
            * a essa sala e remover registro da tabela
            * tagsala
            */
            // let idSala = buscarTagPaiOuFilho(idTagSala);

            /**
            * Atualiza json
            */
            if(!idTagEquipamento === undefined)
            {
                return console.log('idtagequipamento não encontrado!');
            }

            CB.post({
                objetos: {
                    "_x_u_mapaequipamento_idmapaequipamento": idMapaEquipamentoAtual,
                    "_idtagequipamento_": idTagEquipamento,
                    "_x_u_mapaequipamento_json": `${JSON.stringify(blocoAtual.json)}`,
                },
                refresh: false
            });

            console.log("json atualizado");

            return removeEquipmentById(idTagEquipamento);

        } else
        {
            /**
            * Id da sala(tag) selecionada
            */
            // let idTagSala = e.target.id;
            var idTag = JQelement.attr('id');

            /**
            * Remover sala do objeto blocoAtual
            * e atualizar o registro no mapaequipamento
            */
            /**
            * TODO: Pegar sala pelo ID
            */
            let salaKey = false,
                tag     = false;

            if(JQelement.attr('tipo') == 'block')
            {
                tag = getBlocoById(idTag);
            } else 
            {
                tag = getSalaById(idTag);
                // getSalasMapa().forEach((element, key) => {
                //     if(element.idSala == idTagSala)
                //     {
                //         return salaKey = key;
                //     }
                // });
            }

            if(tag === false)
            {
                removePathById(idTag);

                return console.log('TAG não encontrada no json do blocoAtual');
            }

            /**
            * Remover salas vinculadas caso haja
            */
            let idTagRemove = '';

            $(`path[idtagpai=${idTag}]`).each((index, element) => {
                if(JQelement.attr('tipo') == 'room')
                {
                    // Remover vinculo com os equipamentos das salas q serao removidas
                    $(`.item[data-idtagpai='${element.id}']`).each((indexEquipment, elementEquipment) => {
                        removeEquipmentById($(elementEquipment).find('i').data('idtag'));
                    });
                }

                // Removendo sala do objeto blocoAtual e filhos dessa sala
                getSalasMapa().forEach((elementFilho, indexFilho) => {
                    if(elementFilho.idSalaPai == idTag || elementFilho.idSalaPai == element.id)
                    {
                        removeSalaByKey(indexFilho);

                        // Remover vinculo com os equipamentos das salas q serao removidas
                        $(`.item[data-idtagpai='${elementFilho.idSala}']`).each((indexEquipment, elementEquipment) => {
                            removeEquipmentById($(elementEquipment).find('i').data('idtag'));
                        });
                    }
                });

                removePathById(element.id);

                if(element.id.indexOf('sem-vinc') !== -1)
                {
                    return;
                }

                idTagRemove += `,${element.id}`;
            });

            // if(idTagRemove)
            // {
            //     idSala += idTagRemove;
            // }

            if(JQelement.attr('tipo') == 'block')
            {
                // Remove bloco do objeto blocoAtual
                removeBlocoById(idTag);  
            } else 
            {
                // Remove sala do objeto blocoAtual
                removeSalaById(idTag);
            }

            // Remove equipamentos vinculados a sala do json e da DOM
            $(`.item[data-idtagpai='${idTag}']`).each((indexEquipment, elementEquipment) => {
                // if(!idTagRemove)
                // {
                //     idTagRemove = $(element).data('idtag');
                // } else
                // {
                //     idTagRemove += `,${$(element).data('idtag')}`;
                // }

                removeEquipmentById($(elementEquipment).find('i').data('idtag'));
            });

            /**
            * Desvincular tag's dessa sala
            */
            /**
            * pegar todos equipamentos vinculados
            * a essa sala e remover registro da tabela
            * tagsala
            */
            // let idSala = buscarTagPaiOuFilho(idTagSala);

            // Removendo path da DOM
            removePathById(idTag);
        }

        /**
        * Atualiza json
        */
        /**
        * Remove o vinculo dos equipamentos da sala
        */
        // CB.post({
        //     objetos: {
        //         "_x_u_mapaequipamento_idmapaequipamento": idMapaEquipamentoAtual,
        //         "_idtagsala_": idSala,
        //         "_x_u_mapaequipamento_json": `${JSON.stringify(blocoAtual.json)}`,
        //     },
        //     refresh: false
        // });
        /**
        * Caso esse mapa não exista, é
        * criado um novo registro no saveprepost
        */
        createOrUpdateMapaEquipamento();

        console.log("json atualizado");
    }

    /**
    * Evento para mover path (salas)
    * Atualizar coordenadas no bd
    */
    function movableEvent()
    {
        /**
        * TODO: desvincular tag's que ficaram
        * fora da sala
        */

    }

    /**
    * Evento para concluir remoção de  salas
    */
    function finishRemovableEvent(e)
    {
        /**
        * Iniciar contagem para atualizacao dos device's
        */
        // updateDeviceInfoInterval();

        let JQelement = $(e.currentTarget);

        $('.removable').off('click', removableEvent);
        $('path.removable').removeAttr('class');
        $('div.removable').removeClass('removable');

        resetBtnRemoveRoom();
    }

    /**
    * Redefinir atributos do botao de remoção de zona
    */
    function resetBtnRemoveRoom()
    {
        $('#btn-remove-room').removeClass('hidden');
        $('#btn-finish-room').addClass('hidden');
    }

    /**
    * Remove tag ( equipamento ) da dom / objeto blocoAtual
    * @param id idtag
    */
    function removeEquipmentById(id, removeJson = true)
    {
        if(removeJson)
        {
            for(let key in getEquipments())
            {
                if(getEquipments()[key].idtag == id)
                {
                    delete blocoAtual.json.equipamentos[key];
                }
            }

            blocoAtual.json.equipamentos = blocoAtual.json.equipamentos.filter(element => element);
        }

        // Removendo da DOM
        $(`i[data-idtag='${id}']`).parent().parent().remove();

        // Remove titulo do equipamento da DOM
        removeTitle(`title-${id}`);
    }

    /**
    * Redefinir atributos do botao de definção de zona
    */
    function resetBtnCreate()
    {
        $('#btn-create-room').removeClass('hidden');
        $('#btn-cancel-room').addClass('hidden');
    }

    // Habilitar criacao de zonas
    function enableRoomCreation(onMouseMove = false)
    {
        if(onMouseMove)
        {
            return window.addEventListener('mousemove', updatePath);
        }

        window.addEventListener('click', updatePath);
    }

    // Desabilitar criacao de zonas
    function disableRoomCreation()
    {
        window.removeEventListener('mousemove', updatePath);
        window.removeEventListener('click', updatePath);

        SVG.off(document, 'click', createPath);
        SVG.off(document, 'click', drawPath);

        console.log("evento desabled");
    }

    /**
    * Eventos de click para criar zona e
    * interligar pontos
    */
    function drawPathEvent()
    {
        setTimeout(() => {
            SVG.on(document, 'click', createPath);
            SVG.on(document, 'click', drawPath);
        }, 400);
    }

    // Eventos de mousedown para definir um quadrado
    function drawPathSquareEvent(e)
    {
        setActiveTool($(e.currentTarget));
        defineCursor('crosshair');
        JQsvgArea.addClass('creating-room');
        ctrlKey = false;

        SVG.off(document, 'mousedown', createPathSquare);
        SVG.off(document, 'mouseup', finishPathSquare);

        SVG.on(document, 'mousedown', createPathSquare);
        SVG.on(document, 'mouseup', finishPathSquare);
    }

    function finishPathSquare(e)
    {
        if(!path || e.ctrlKey || ctrlKey)
        {
            return false;
        }
        
        SVG.off(document, 'mouseup', finishPathSquare);
        SVG.off(document, 'mousedown', createPathSquare);
        SVG.off(document, 'mousemove', sizePathSquare);

        JQsvgArea.removeClass('creating-room');
        path.removeClass('creating');
        defineCursor();

        path.addClass('created ui-widget-header');

        // Coordenadas X e Y do primeiro canto
        let mainXCircle = $(`circle.circle-${path.attr('id')}[side='1']`).attr('cx')
            mainYCircle = $(`circle.circle-${path.attr('id')}[side='1']`).attr('cy');

        path.attr('titleX', mainXCircle);
        path.attr('titleY', mainYCircle);

        let sides = 8;

        let pathId = path.attr('id'),
            JQpathSelected = $(`path#${pathId}`),
            X = JQpathSelected[0].instance.x(),
            Y = JQpathSelected[0].instance.y(),
            width = JQpathSelected[0].instance.width(),
            height = JQpathSelected[0].instance.height(),
            offsetX = width,
            offsetY = height;

        let extremidadesDoQuadrado = path.attr('d').split(' '),
            atualizarCircles = false;

        if(width < 15)
        {
            atualizarCircles = true;

            extremidadesDoQuadrado[2] = `L${parseInt(extremidadesDoQuadrado[2].replace('L', '')) + 15}`;
            extremidadesDoQuadrado[4] = `L${parseInt(extremidadesDoQuadrado[4].replace('L', '')) + 15}`;

            $(`circle.circle-${pathId}[side='2']`).attr('cx', parseInt(extremidadesDoQuadrado[2].replace('L', '')));
            $(`circle.circle-${pathId}[side='3']`).attr('cx', parseInt(extremidadesDoQuadrado[4].replace('L', '')));
        }

        if(height < 15)
        {
            atualizarCircles = true;

            extremidadesDoQuadrado[5] = `${parseInt(extremidadesDoQuadrado[5]) + 15}`;
            extremidadesDoQuadrado[7] = `${parseInt(extremidadesDoQuadrado[7]) + 15}`;

            $(`circle.circle-${pathId}[side='3']`).attr('cy', parseInt(extremidadesDoQuadrado[5]));
            $(`circle.circle-${pathId}[side='4']`).attr('cy', parseInt(extremidadesDoQuadrado[7]));
        }

        if(atualizarCircles)
        {
            JQpathSelected.attr('d', extremidadesDoQuadrado.join(' '));

            X = JQpathSelected[0].instance.x();
            Y = JQpathSelected[0].instance.y();
            width = JQpathSelected[0].instance.width();
            height = JQpathSelected[0].instance.height();
            offsetX = width;
            offsetY = height;
        }

        updateSalaById(pathId, {
            titleX: mainXCircle,
            titleY: mainYCircle
        });

        for(let i = 5; i <= sides; i++)
        {
            // Par == Y
            if(i % 2 == 0)
            {
                widthOrHeight = JQpathSelected[0].instance.height();

                if(offsetX)
                {
                    createCircle(draw, X + offsetX, Y + (height / 2), pathId).attr('side', i);

                    offsetX = false;
                } else 
                {
                    createCircle(draw, X, Y + (height / 2), pathId).attr('side', i);
                }
                continue;
            }
        
            // Impar == X
            if(!offsetX && offsetY)
            {
                createCircle(draw, X + (width / 2), Y + offsetY, pathId).attr('side', i);
                offsetY = false;
            } else 
            {
                createCircle(draw, X + (width / 2), Y, pathId).attr('side', i);
            }
        }

        circlesAtual = [];
        $(`circle[class*='circle-${path.attr('id')}']`).each((index, element) => {
            circlesAtual.push({
                id: $(element).attr('id'),
                r: $(element).attr('r'),
                cx: $(element).attr('cx'),
                cy: $(element).attr('cy')
            });
        });

        /**
        * Escolher um bloco/sala para vincular à area demarcada
        */
        if(path.attr('idtagpai'))
        {
            path.attr('tipo', 'room');

            /**
            * Criar titulo na DOM
            */
            createTitle(`title-${path.attr('id')}`, path.attr('title'), mainXCircle, mainYCircle);
            titleDraggableEvent(`title-${path.attr('id')}`);
            mostrarTitulos(`title-${path.attr('id')}`);
            
            return chooseRoom();
        }

        path.attr('tipo', 'block');
        /**
        * Criar titulo na DOM
        */
        createTitle(`title-${path.attr('id')}`, path.attr('title'), mainXCircle, mainYCircle, 'bloco');
        titleDraggableEvent(`title-${path.attr('id')}`);
        mostrarTitulos(`title-${path.attr('id')}`);

        return chooseBlock();
    }

    function createPathSquare(e)
    {
        ctrlKey = false;

        if(e.ctrlKey || ($(e.target).prop('tagName') != 'svg' && $(e.target).prop('tagName') != 'path'))
        {
            ctrlKey = true;

            return false;
        }

        // Dimensionar tamanho do quadrado
        SVG.on(document, 'mousemove', sizePathSquare);

        let idPathSquare = `sem-vinc-${parseInt(Math.random() * 100)}`;

        mouseX = e.offsetX;
        mouseY = e.offsetY;

        path = draw.path(`M${mouseX} ${mouseY} L${mouseX} ${mouseY} L${mouseX} ${mouseY} L${mouseX} ${mouseY} Z`).attr({
            id: idPathSquare,
            title: 'Bloco sem vinculo',
            stroke: `rgb(${corPadraoSala})`,
            class: "creating",
            form: 'square',
            cor: corPadraoSala,
            fill: `rgba(${corPadraoSala}, .1)`
        });

        // Criando uma sala dentro de outra
        if($(e.target).first().prop('tagName') == 'path')
        {
            path.attr('idtagpai', $(e.target).attr('id'));
            path.attr('title', 'Sala sem vinculo');
        }

        // Criar os quatro cantos do quadrado
        for(let i = 1; i <= 4; i++)
        {
            let JQcircle = createCircle(draw, mouseX, mouseY, `${idPathSquare}`);

            JQcircle.attr('side', i);
        }
    }

    function sizePathSquare(e)
    {
        let pathSquareId = path.attr('id');

        // Pegar cantos os quatro cantos do quadrado
        let cornerMainX = path.attr('d').split(' ')[0].replace('M', ''),
            cornerMainY = path.attr('d').split(' ')[1];

        mouseX = e.offsetX;
        mouseY = e.offsetY;

        // Coordenadas do eixo X
        let corner2X = `${mouseX}`,
            corner3X = `${mouseX}`;

        // Coordenadas do eixo Y
        let corner3Y = mouseY,
            corner4Y = mouseY;

        // Mover circles
        $(`.circle-${pathSquareId}[side='2']`).attr('cx', mouseX),
        $(`.circle-${pathSquareId}[side='3']`).attr('cx', mouseX).attr('cy', mouseY),
        $(`.circle-${pathSquareId}[side='4']`).attr('cy', corner4Y);

        path.attr('d', `M${cornerMainX} ${cornerMainY} L${corner2X} ${cornerMainY} L${corner3X} ${corner3Y} L${cornerMainX} ${corner4Y} Z`);
    }

    /**
    * Redimensionar altura e largura de path que foram criados a partir de um quadrado
    */
    function sizePathSquareEvent()
    {
        let JQpathSquare = $('path[form="square"]');

        JQpathSquare.each((index, element) => {
            let pathSquareId = element.id;

            let JQcircleSide5 = $(`circle.circle-${pathSquareId}[side='5']`),
                JQcircleSide6 = $(`circle.circle-${pathSquareId}[side='6']`),
                JQcircleSide7 = $(`circle.circle-${pathSquareId}[side='7']`),
                JQcircleSide8 = $(`circle.circle-${pathSquareId}[side='8']`);

            // Pegar os quatro cantos do quadrado
            let corners = null;

            let JQline = null,
                JQlineTitleCoordenate = null;


            // Aumentar pelo TOPO 
            JQcircleSide5.draggable({
                start: function()
                {
                    JQline = $(`#line-title-${pathSquareId}`);

                    $(`.item[data-idtagpai='${pathSquareId}']`).addClass('z-index-0');

                    if(JQline.length)
                    {
                        JQlineTitleCoordenate = {
                            from: [JQline.attr('d').split(' ')[0].replace('M', ''), JQline.attr('d').split(' ')[1]],
                            to: [JQline.attr('d').split(' ')[2].replace('L', ''), JQline.attr('d').split(' ')[3]]
                        };
                    }

                    removeOptions();
                    corners = getMainCorners(pathSquareId);
                },
                drag: function(e, ui)
                {
                    // Movimentar circle cliclado
                    $(this).attr('cy', e.offsetY);

                    // Redimensionar path
                    mouseY = e.offsetY;

                    if(JQlineTitleCoordenate)
                    {
                        // Manter ligacao com title
                        JQline.attr('d', `M${JQlineTitleCoordenate.from[0]} ${mouseY} L${JQlineTitleCoordenate.to[0]} ${JQlineTitleCoordenate.to[1]}`);
                    }

                    // Mover circles das extremidades do topo
                    $(`.circle-${pathSquareId}[side=1], .circle-${pathSquareId}[side=2]`).attr('cy', mouseY);

                    // Deixar circles laterais centralizados
                    if(parseInt($(`.circle-${pathSquareId}[side=1]`).attr('cy')) < corners.corner4[1])
                    {
                        JQcircleSide6.attr('cy', parseInt(corners.corner3[1]) - (element.instance.height() / 2));
                        JQcircleSide8.attr('cy', parseInt(corners.corner4[1]) - (element.instance.height() / 2));
                    } else
                    {
                        JQcircleSide6.attr('cy', parseInt(corners.corner3[1]) + (element.instance.height() / 2));
                        JQcircleSide8.attr('cy', parseInt(corners.corner4[1]) + (element.instance.height() / 2));
                    }

                    $(element).attr('d', `M${corners.corner1[0]} ${mouseY} L${corners.corner2[0]} ${mouseY} L${corners.corner3[0]} ${corners.corner3[1]} L${corners.corner4[0]} ${corners.corner4[1]} Z`);
                },
                stop(e, ui)
                {
                    $(`.item[data-idtagpai='${pathSquareId}']`).removeClass('z-index-0');

                    let posOptionX = element.instance.x() + (element.instance.width() + 10),
                        posOptionY = element.instance.y();

                    // Atualizando coordenadas do path no objeto do blocoAtual
                    updatePathBySalaId(pathSquareId, $(element).attr('d'));

                    // Atualizando coordenadas do cicle no objeto do blocoAtual
                    updateCircleByPathId(pathSquareId, false, {
                        cx: $(this).attr('cx'),
                        cy: $(this).attr('cy')
                    });

                    createOptions(posOptionX, posOptionY, $(element).attr('tipo'));

                    createOrUpdateMapaEquipamento();
                },
                revert: false,
                tolerance: "intersect",
                helper: "original"
            });

            // Aumentar pela DIREITA
            JQcircleSide6.draggable({
                start: function()
                {
                    $(`.item[data-idtagpai='${pathSquareId}']`).addClass('z-index-0');
                    removeOptions();
                    corners = getMainCorners(pathSquareId);
                },
                drag: function(e, ui)
                {
                    // Movimentar circle cliclado
                    $(this).attr('cx', event.offsetX);

                    // Redimensionar path
                    mouseX = e.offsetX;

                    // Mover circles das extremidades da direita
                    $(`.circle-${pathSquareId}[side=2], .circle-${pathSquareId}[side=3]`).attr('cx', mouseX);

                    // Deixar circles do top e bottom centralizados
                    if(parseInt($(`.circle-${pathSquareId}[side=2]`).attr('cx')) < corners.corner1[0])
                    {
                        JQcircleSide5.attr('cx', parseInt(corners.corner1[0]) - (element.instance.width() / 2));
                        JQcircleSide7.attr('cx', parseInt(corners.corner4[0]) - (element.instance.width() / 2));
                    } else
                    {
                        JQcircleSide5.attr('cx', parseInt(corners.corner1[0]) + (element.instance.width() / 2));
                        JQcircleSide7.attr('cx', parseInt(corners.corner4[0]) + (element.instance.width() / 2));
                    }

                    $(element).attr('d', `M${corners.corner1[0]} ${corners.corner1[1]} L${mouseX} ${corners.corner2[1]} L${mouseX} ${corners.corner3[1]} L${corners.corner4[0]} ${corners.corner4[1]} Z`);
                },
                stop(e, ui)
                {
                    $(`.item[data-idtagpai='${pathSquareId}']`).removeClass('z-index-0');
                    let posOptionX = element.instance.x() + (element.instance.width() + 10),
                        posOptionY = element.instance.y();

                    // Atualizando coordenadas do path no objeto do blocoAtual
                    updatePathBySalaId(pathSquareId, $(element).attr('d'));

                    // Atualizando coordenadas do circle no objeto do blocoAtual
                    updateCircleByPathId(pathSquareId, false, {
                        cx: $(this).attr('cx'),
                        cy: $(this).attr('cy')
                    });

                    createOptions(posOptionX, posOptionY, $(element).attr('tipo'));

                    createOrUpdateMapaEquipamento();
                },
                revert: false,
                tolerance: "intersect",
                helper: "original"
            });

            // Aumentar pelo INFERIOR
            JQcircleSide7.draggable({
                start: function()
                {
                    $(`.item[data-idtagpai='${pathSquareId}']`).addClass('z-index-0');

                    removeOptions();
                    corners = getMainCorners(pathSquareId);
                },
                drag: function(e, ui)
                {
                    // Movimentar circle cliclado
                    $(this).attr('cy', event.offsetY);

                    // Redimensionar path
                    mouseY = e.offsetY;

                    // Mover circles do inferior
                    $(`.circle-${pathSquareId}[side=3], .circle-${pathSquareId}[side=4]`).attr('cy', mouseY);

                    // Deixar circles laterais centralizados
                    if(parseInt($(`.circle-${pathSquareId}[side=4]`).attr('cy')) < corners.corner1[1])
                    {
                        JQcircleSide6.attr('cy', parseInt(corners.corner1[1]) - (element.instance.height() / 2));
                        JQcircleSide8.attr('cy', parseInt(corners.corner2[1]) - (element.instance.height() / 2));
                    } else
                    {
                        JQcircleSide6.attr('cy', parseInt(corners.corner1[1]) + (element.instance.height() / 2));
                        JQcircleSide8.attr('cy', parseInt(corners.corner2[1]) + (element.instance.height() / 2));
                    }

                    $(element).attr('d', `M${corners.corner1[0]} ${corners.corner1[1]} L${corners.corner2[0]} ${corners.corner2[1]} L${corners.corner3[0]} ${mouseY} L${corners.corner4[0]} ${mouseY} Z`);
                },
                stop(e, ui)
                {
                    $(`.item[data-idtagpai='${pathSquareId}']`).removeClass('z-index-0');

                    let posOptionX = element.instance.x() + (element.instance.width() + 10),
                        posOptionY = element.instance.y();

                    // Atualizando coordenadas do path no objeto do blocoAtual
                    updatePathBySalaId(pathSquareId, $(element).attr('d'));

                    // Atualizando coordenadas do circle no objeto do blocoAtual
                    updateCircleByPathId(pathSquareId, false, {
                        cx: $(this).attr('cx'),
                        cy: $(this).attr('cy')
                    });

                    createOptions(posOptionX, posOptionY, $(element).attr('tipo'));

                    createOrUpdateMapaEquipamento();
                },
                revert: false,
                tolerance: "intersect",
                helper: "original"
            });

            // Aumentar pela ESQUERDA
            JQcircleSide8.draggable({
                start: function()
                {
                    $(`.item[data-idtagpai='${pathSquareId}']`).addClass('z-index-0');

                    JQline = $(`#line-title-${pathSquareId}`);

                    if(JQline.length)
                    {
                        JQlineTitleCoordenate = {
                            from: [JQline.attr('d').split(' ')[0].replace('M', ''), JQline.attr('d').split(' ')[1]],
                            to: [JQline.attr('d').split(' ')[2].replace('L', ''), JQline.attr('d').split(' ')[3]]
                        };
                    }

                    removeOptions();
                    corners = getMainCorners(pathSquareId);
                },
                drag: function(e, ui)
                {
                    // Movimentar circle cliclado
                    $(this).attr('cx', e.offsetX);

                    // Redimensionar path
                    mouseX = e.offsetX;

                    if(JQlineTitleCoordenate)
                    {
                        // Manter ligacao com title
                        JQline.attr('d', `M${mouseX} ${JQlineTitleCoordenate.from[1]} L${JQlineTitleCoordenate.to[0]} ${JQlineTitleCoordenate.to[1]}`);
                    }

                    // Mover circles das da esquerda
                    $(`.circle-${pathSquareId}[side=1], .circle-${pathSquareId}[side=4]`).attr('cx', mouseX);

                    // Deixar circles do top e bottom centralizados
                    if(parseInt($(`.circle-${pathSquareId}[side=1]`).attr('cx')) < corners.corner2[0])
                    {
                        JQcircleSide5.attr('cx', parseInt(corners.corner2[0]) - (element.instance.width() / 2));
                        JQcircleSide7.attr('cx', parseInt(corners.corner3[0]) - (element.instance.width() / 2));
                    } else
                    {
                        JQcircleSide5.attr('cx', parseInt(corners.corner2[0]) + (element.instance.width() / 2));
                        JQcircleSide7.attr('cx', parseInt(corners.corner3[0]) + (element.instance.width() / 2));
                    }

                    $(element).attr('d', `M${mouseX} ${corners.corner1[1]} L${corners.corner2[0]} ${corners.corner2[1]} L${corners.corner3[0]} ${corners.corner3[1]} L${mouseX} ${corners.corner4[1]} Z`);
                },
                stop(e, ui)
                {
                    $(`.item[data-idtagpai='${pathSquareId}']`).removeClass('z-index-0');

                    let posOptionX = element.instance.x() + (element.instance.width() + 10),
                        posOptionY = element.instance.y();

                    // Atualizando coordenadas do path no objeto do blocoAtual
                    updatePathBySalaId(pathSquareId, $(element).attr('d'));

                    // Atualizando coordenadas do circle no objeto do blocoAtual
                    updateCircleByPathId(pathSquareId, false, {
                        cx: $(this).attr('cx'),
                        cy: $(this).attr('cy')
                    });

                    createOptions(posOptionX, posOptionY, $(element).attr('tipo'));

                    createOrUpdateMapaEquipamento();
                },
                revert: false,
                tolerance: "intersect",
                helper: "original"
            });
        });
    }

    function buscarUnidadesPeloIdEmpresa(idempresa)
    {
        let unidades = false;

        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'buscarUnidadesPeloIdEmpresa',
                params: idempresa
            },
            success: response => {
                unidades = response;
            },
            error: err => {
                console.log(err);
            }
        });

        return unidades;
    }

    // Pegar quatro cantos principais do quadrado
    function getMainCorners(pathId = false)
    {
        if(!pathId || pathId.search('sem-vinc') != -1)
        {
            pathId = path.idSala ? path.idSala : path.id;
        }
            
        let JQpath = $(`path#${pathId}`),
            corners = JQpath.attr('d').split(' ');

        return {
            corner1: [corners[0].replace('M', ''), corners[1]],
            corner2: [corners[2].replace('L', ''), corners[3]],
            corner3: [corners[4].replace('L', ''), corners[5]],
            corner4: [corners[6].replace('L', ''), corners[7]]
        }
    }

    // Cria o path
    function createPath(e)
    {
        if(e.ctrlKey || ($(e.target).prop('tagName') != 'svg' && $(e.target).prop('tagName') != 'path'))
        {
            return false;
        }

        if($(e.target).hasClass('created'))
        {
            return;
        }

        if(!path)
        {
            console.log('Novo Path criado');

            /**
            * Usar coordenadas relativas ao svg
            */
            // X = e.offsetX;
            // Y = e.offsetY;
            // ATual
            // X = e.layerX;
            // Y = e.layerY;

            // console.log(zoomAtual);

            // console.log(e);

            X = e.offsetX;
            Y = e.offsetY;
            // console.log(mouseX, mouseY);

            /**
            * Valores padroes ate q uma sala seja vinculada
            */
            let JQsalasSemVinculo = $("path[id*='sem-vinc']"),
                idExistentes = [],
                idSala = `sem-vinc-1`;

            JQsalasSemVinculo.each((key, element) => {

                if(idSala == '')
                {
                    idSala = element.id;
                }

                idExistentes.push(element.id);
            });

            while(idExistentes.includes(idSala))
            {
                idSala = `sem-vinc-${parseInt(Math.random() * 100)}`;
            }

            path = draw.path(`M${X} ${Y}`).attr({
                id: idSala,
                title: 'Sala sem vinculo',
                stroke: `rgb(${corPadraoSala})`,
                class: "creating"
            });

            idCurrentPath = path.attr('id');

            // Criando uma sala dentro de outra
            if($(e.target).first().prop('tagName') == 'path')
            {
                path.attr('idtagpai', $(e.target).attr('id'));
            }

            createCircle(draw, X, Y, path.attr('id'));
        }

        // Desabilitar outros circles para evitar colisao
        $(`circle:not(.circle-${path.attr('id')})`).addClass('pe-none');

        // onmouse move
        if(mouseX && mouseY)
        {
            coordenadasL.push(` L${mouseX} ${mouseY}`);
            L = coordenadasL.join(' ');
        }

        enableRoomCreation(true);

        path.fill(`rgba(${corPadraoSala}, .1)`);
    }

    // Atualiza o path
    function updatePath(e)
    {
        // console.log(`layer: ${e.layerX}, ${e.layerY} | client: ${e.clientX}, ${e.clientY} | screen: ${e.screenX}, ${e.screenY} | offset: ${e.offsetX}, ${e.offsetY} | page: ${e.pageX}, ${e.pageY}`);
        // Atual
        // mouseX = e.layerX,
        // mouseY = e.layerY;

        mouseX = e.offsetX,
        mouseY = e.offsetY;
        // mouseX = e.layerX + (svgWidth / ((svgWidth * zoomAtual) - svgWidth)),
        // mouseY = e.layerY + (svgHeight / ((svgHeight * zoomAtual) - svgHeight));

        // console.log(mouseX, mouseY);

        // mouseX = (e.offsetX),
        // mouseY = (e.offsetY);

        // Onmouse move
        path.plot(`M${X} ${Y} ${L} L${mouseX} ${mouseY}`);
        // Onmouse click
        // coordenadasL.push(` L${mouseX} ${mouseY}`);
        // L = coordenadasL.join(' ');

        // path.plot(`M${X} ${Y} ${L}`);
    }

    // Cria circulo no click na criacao da zona
    function createCircle(pathElement, x, y, idPath, id = false, r = 7, disabled = false)
    {
        /**
        * Valores padroes ate q uma sala seja vinculada
        */
        let JQcirclesSemVinculo = $("circle"),
            idExistentes = [],
            idCircle = `circle-1`;

        JQcirclesSemVinculo.each((key, element) => {
            idExistentes.push(element.id);
        });

        while(idExistentes.includes(idCircle))
        {
            idCircle = `circle-${parseInt(Math.random() * 10000)}`;
        }

        // let idCreate = `circle-${$('circle').length + 1}`

        let circleElement = pathElement.circle(r).attr({
                                id: id ? id : idCircle,
                                class: `circle-${idPath}`
                            }).cx(x).cy(y);

        if(disabled)
        {
            $('circle').addClass('pe-none');
        }

        return circleElement;
    }

    /**
    * Define as coordenadas do path
    */
    function drawPath(e)
    {
        /**
        * Definir id da sala na qualq esta sendo criado outra
        * para posteriormente buscar os equipamentos do tipo quarto termico
        * para que seja criados como sala buscando no autocomplete
        */
        if(!idSalaQuery && $(e.target).attr('tipo') == 'room' && $(e.target).prop('tagName') == 'path' && $(e.target).attr('id').search('sem-vinc') === -1)
        {
            idSalaQuery = $(e.target).attr('id');
        }

        // onmouse click
        // updatePath(e);
        if(e.ctrlKey)
        {
            return false;
        }

        if(mouseX && mouseY)
        {
            if($(e.target).first().prop('tagName') == 'circle')
            {
                JQsvgArea.removeClass('creating-room');
                defineCursor();
                /**
                * Removendo ultimas coordenadas quando for feita a ultima
                * ligacao, já q sempre sera uma reta
                */
                // L.split(" ").filter(element => element)
                LArray = path.attr('d').split(' ').filter(element => element && element.search('M') === -1);

                LArray.splice(LArray.length - 2, 2);
                LArray.splice(0, 1);

                L = LArray.join(" ");
                path.plot(`M${X} ${Y} ${L} Z`);
                path.removeClass('creating');

                path.addClass('created ui-widget-header');
                $(`circle:not(.circle-${path.attr('id')})`).css('pointerEvents', 'auto');

                /**
                * Desenhar texto no centro do path criado
                */
                let titleX = {
                    min: X,
                    max: 0
                },
                    titleY = {
                        min: Y,
                        max: 0
                    };

                for(let i in LArray)
                {
                    let key = parseInt(i);

                    // Coordenada X
                    if(i % 2 == 0)
                    {   
                        if(parseInt(LArray[key].replace('L', '')) > titleX.max)
                        {
                            titleX.max = parseInt(LArray[key].replace('L', ''));
                        }
                        
                        continue
                    }

                    // Coordenada Y
                    if(parseInt(LArray[key]) > titleY.max)
                    {
                        titleY.max = parseInt(LArray[key]);
                    }
                }

                path.attr('cor', corPadraoSala);

                path.attr('titleX', path.x() + (path.width() / 3));
                path.attr('titleY', path.y() + (path.height() / 2));

                /**
                * 
                * 1° apresentar unidades disponiveis para vinculo
                * 
                * 2° apresentar salas disponiveis para essa unidade
                * ao selecionar vincular o idsala á tag e atualizar a sala da tag
                */
                /**
                * Salver circle's no json
                */
            /**
                * Resetando circles
                */
                circlesAtual = [];
                $(`circle.circle-${path.attr('id')}`).each((index, element) => {
                    circlesAtual.push({
                        id: $(element).attr('id'),
                        r: $(element).attr('r'),
                        cx: $(element).attr('cx'),
                        cy: $(element).attr('cy')
                    });
                });

                /**
                * Sendo executado antes da criacao do circle
                */
                // setTimeout(() => {
                //     pathResize();
                // }, 200);

                // Habilitar foco para edicao nas salas
                $('.main-content').on('click','path:not(.creating)', lockPath);

                /**
                * Escolher um bloco/sala para vincular à area demarcada
                */
                if(path.attr('idtagpai'))
                {
                    path.attr('tipo', 'room');

                    /**
                    * Criar titulo na DOM
                    */
                    createTitle(`title-${path.attr('id')}`, path.attr('title'), path.x() + (path.width() / 3), path.y() + (path.height() / 2));
                    titleDraggableEvent(`title-${path.attr('id')}`);
                    mostrarTitulos(`title-${path.attr('id')}`);
                    
                    return chooseRoom();
                }

                path.attr('tipo', 'block');
                /**
                * Criar titulo na DOM
                */
                createTitle(`title-${path.attr('id')}`, path.attr('title'), path.x() + (path.width() / 3), path.y() + (path.height() / 2), 'bloco');
                titleDraggableEvent(`title-${path.attr('id')}`);
                mostrarTitulos(`title-${path.attr('id')}`);
                return chooseBlock();
            }

            createCircle(draw, mouseX, mouseY, path.attr('id'));
        }
    }

    /**
    * Selecionar um bloco para vincular á zona criada
    */
    function chooseBlock(change = false)
    {
        disableRoomCreation();
        btnCancelEvent();

        let blocos = buscarBlocosDisponiveis();

        let pathId = typeof path.id != 'function' ? path.id : path.attr('id'),
            JQbloco   = $(`path#${pathId}`);

        let blocoObject = {
            id: pathId,
            titulo: JQbloco.attr('title'),
            path: JQbloco.attr('d'),
            fill: `rgba(${corPadraoSala}, .1)`,
            stroke: `rgb(${corPadraoSala})`,
            circles: circlesAtual,
            titleX: JQbloco.get(0).getAttribute('titleX'),
            titleY: JQbloco.get(0).getAttribute('titleY'),
            cor: corPadraoSala,
            x: JQbloco[0].instance.x(),
            y: JQbloco[0].instance.y(),
            form: JQbloco.attr('form')
        };

        addBloco(blocoObject);

        if(!blocos)
        {
            return alertAtencao('Nenhum bloco com sala vinclulada encontrado!');
        }

        createDivChoose('bloco');

        $('#choose').autocomplete({
            source: blocos,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    lbItem = item.descricao;
                    return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                };
            },
            select: function(event, ui)
            {
                let idBloco = ui.item.id,
                    bloco   = ui.item.descricao,
                    corRBG  = hexToRgb(ui.item.cor),
                    idUnidade = ui.item.idunidade;

                // Atualizando circles
                $(`circle.circle-${pathId}`).attr('class', `circle-${idBloco}`);

                let JQtitle = $(`#title-${pathId}`);

                updateBlocoById(pathId, {
                    id: idBloco,
                    titulo: bloco,
                    cor: corRBG,
                    idunidade: idUnidade
                });

                JQtitle.find('h1').text(bloco);
                JQtitle.attr('id', `title-${idBloco}`);
                $(`#line-title-${pathId}`).attr('id', `line-title-${idBloco}`);
                titleDraggableEvent(`title-${idBloco}`);

                JQbloco.attr('id', idBloco);
                JQbloco.attr('title', bloco);
                JQbloco.attr('cor', corRBG);
                JQbloco.attr('idunidade', idUnidade);

                createOrUpdateMapaEquipamento();

                removeDivChoose();
            }
        });
    }

    // Adicionar bloco ao blocoAtual
    function addBloco(bloco)
    {
        if(typeof bloco != 'object')
        {
            console.log('addBloco(): Parâmetro inválido');
            return alertAtencao("Valor para inserção de um novo bloco inváido");
        }

        if(!blocoAtual.json.blocos)
        {
            blocoAtual.json.blocos = [];
        }

        blocoAtual.json.blocos.push(bloco);
    }

    /**
    * Selecionar uma sala para vincular á zona criada
    */
    function chooseRoom(change = false)
    {
        disableRoomCreation();
        btnCancelEvent();
        /**
        * Iniciar contagem para atualizacao dos device's
        */
        // updateDeviceInfoInterval();

        /**
        * TODO: Adicionar Try Catch
        * 
        * Buscar salas para vinculo
        */
        let tagPaiId = `'${path.idSalaPai ? path.idSalaPai : path.attr('idtagpai')}'`;
        
        if(idSalaQuery)
        {
            tagPaiId = `${tagPaiId}|${idSalaQuery}`;
            idSalaQuery = null;
        }

        let salas = buscarTagsPaiOuFilhos(tagPaiId, true, 'tp.idtag', idMapaEquipamentoAtual);

        /**
        * TODO:
        * Salvar path's que não possuem vinculo com
        * sala, para q depois seja possivel atribuir uma
        * sala clicando no path
        */
        let idSala = path.idSala ? path.idSala : path.attr('id'),
            JQsala = $(`path#${idSala}`),
            sala = JQsala.attr('title');

        if(!change)
        {
            let salaInObjeto = getSalaById(idSala),
                salaNewInfo = {
                    idSala: idSala,
                    idSalaPai: JQsala.attr('idtagpai'),
                    titulo: sala,
                    path: JQsala.attr('d'),
                    fill: `rgba(${corPadraoSala}, .1)`,
                    stroke: `rgb(${corPadraoSala})`,
                    circles: circlesAtual,
                    titleX: JQsala.get(0).getAttribute('titleX'),
                    titleY: JQsala.get(0).getAttribute('titleY'),
                    cor: corPadraoSala,
                    x: JQsala[0].instance.x(),
                    y: JQsala[0].instance.y(),
                    form: JQsala.attr('form')
                };

            if(salaInObjeto)
            {
                updateSalaById(idSala, salaNewInfo);
            } else 
            {
                blocoAtual.json.salas.push(salaNewInfo);
            }

            JQsala.attr({
                id: idSala,
                title: sala
            });

            // Atualizando titulo da sala
            $(`#title-${idSala}`).find('h1').text(sala);

            /**
            * Caso esse mapa não exista, é
            * criado um novo registro no saveprepost
            */
            createOrUpdateMapaEquipamento();
        }

        if(!salas || !salas.length)
        {
            return alertAtencao('Nenhuma sala encontrada para vinculo!');
        }

        createDivChoose();

        $('#choose').autocomplete({
            source: salas,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    lbItem = item.descricaoFilho;
                    return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                };
            },
            select: function(event, ui)
            { 
                let idSala  = ui.item.idtagFilho,
                    sala    = ui.item.descricaoFilho,
                    idBloco = ui.item.idtagPai,
                    pathId  = path.idSala ? path.idSala : path.attr('id'),
                    corSala = ui.item.corFilho ? hexToRgb(ui.item.corFilho) : corPadraoSala,
                    idEmpresa    = ui.item.idempresaFilho,
                    JQpathCriado = $(`path#${pathId}`),
                    JQtituloDoPathCriado = $(`#title-${idSala}`),
                    idUnidade = ui.item.idunidadeFilho;

                // Atualizando circles
                $(`circle[class*='circle-${pathId}']`).each((index, element) => {
                    $(element).attr('class', `circle-${idSala}`);
                });

                // Verificando se a sala esta sendo alterada
                if(change)
                {
                    JQtituloDoPathCriado = $(`#title-${pathId}`);

                    JQpathCriado.attr('title', sala);
                    JQpathCriado.attr('id', idSala);
                    JQpathCriado.attr('idempresa', idEmpresa);

                    /**
                    * TODO: Carregar titulo da sala quando
                    * ele for alterada
                    */
                    // Atualizando titulo da sala
                    JQtituloDoPathCriado.find('h1').text(sala);
                    JQtituloDoPathCriado.attr('id', `title-${idSala}`);
                    JQtituloDoPathCriado.addClass('is-room');
                    JQtituloDoPathCriado.css('backgroundColor', `rgba(${corSala}, 1)`);
                    $(`#line-title-${pathId}`).attr('id', `line-title-${idSala}`);
                    $(`#line-title-${pathId}`).attr('fill', `rgba(${corSala}, 1)`);
                    titleDraggableEvent(`title-${idSala}`);
                    JQpathCriado.attr('idempresa', idEmpresa);
                    JQpathCriado.attr('idunidade', idUnidade);
                    // $(`text#text-${path.idSala}`).text(sala);

                    updateSalaById(pathId, {
                        idSala: idSala,
                        titulo: sala,
                        titleX: JQsala.get(0).getAttribute('titleX'),
                        titleY: JQsala.get(0).getAttribute('titleY'),
                        idempresa: idEmpresa,
                        idunidade: idUnidade
                    });

                    removeEquipmentBySalaId(pathId, true);
                } else
                {
                    // Atualizando id do titulo da sala
                    $(`#title-${JQsala.attr('id')}`).attr('id', `title-${idSala}`);

                    // definindo id da sala
                    JQsala.attr({
                        id: idSala,
                        title: sala,
                        cor: corSala,
                        fill: `rgba(${corSala}, .1)`,
                        stroke: `rgba(${corSala}, 1)`,
                        idempresa: idEmpresa,
                        idunidade: idUnidade
                    });

                    /**
                    * TODO: Carregar titulo da sala quando
                    * ele for alterada
                    */
                    // Atualizando titulo da sala
                    JQtituloDoPathCriado.find('h1').text(sala);
                    JQtituloDoPathCriado.addClass('is-room');
                    $(`#line-title-${pathId}`).attr('id', `line-title-${idSala}`);
                    $(`#line-title-${idSala}`).attr('stroke', `rgba(${corSala}, 1)`);
                    JQtituloDoPathCriado.find('h1').css('backgroundColor', `rgba(${corSala}, 1)`);
                    // draw.plain(sala).attr('x', path.attr('titleX')).attr('y', path.attr('titleY')).attr('id', `text-${idSala}`);

                    /**
                    * Distancia do elemento da borda e topo
                    * no momento em que foi criado.
                    * 
                    * Usar porque o translate ira tomar essa posicao
                    * como inicial
                    */
                    // let posicaoX = $(`path#${idSala}`).offset().left.toFixed(2),
                    //     posicaoY = $(`path#${idSala}`).offset().top.toFixed(2);

                    let idSalaPai = JQsala.attr('idtagpai');

                    /**
                    * Salver path e circles no json
                    */
                    let newRoomInfo = {
                        idSala: idSala,
                        idSalaPai: idSalaPai,
                        cor: corSala,
                        titulo: sala,
                        path: JQsala.attr('d'),
                        fill: `rgba(${corSala}, .1)`,
                        stroke: `rgba(${corSala}, 1)`,
                        circles: circlesAtual,
                        titleX: JQsala.get(0).getAttribute('titleX'),
                        titleY: JQsala.get(0).getAttribute('titleY'),
                        form: JQsala.attr('form'),
                        idempresa: idEmpresa,
                        idunidade: idUnidade
                    };

                    updateSalaById(pathId, newRoomInfo);

                    /**
                    * Criacao de uma sala dentro de outra
                    */
                    if(idSalaPai && idSala.indexOf('sem-vinc') === -1)
                    {
                        if(typeof idSalaPai != 'number' && idSalaPai.indexOf('sem-vinc') !== -1)
                        {
                            return alertAtencao('Sala sem vínculo!');
                        }

                        /**
                        * Verificando se essa sala possui um pai
                        */
                        let registroSala = buscarTagPaiOuFilho(idSala, 'idtag'),
                            idRegistroSala = null;

                        if(registroSala && !registroSala.error)
                        {
                            idRegistroSala = registroSala.idtagsala
                        }
                        /**
                        * Atualizar idtagpai da sala criada
                        */
                        if(idRegistroSala)
                        {
                            CB.post({
                                    objetos: {
                                            "_x_u_tagsala_idtagsala": idRegistroSala,
                                            "_x_u_tagsala_idtagpai": idSalaPai
                                    },
                                    parcial: true,
                                    refresh: false
                            });
                        } else {
                                CB.post({
                                    objetos: {
                                            "_x_i_tagsala_idtag": idSala,
                                            "_x_i_tagsala_idtagpai": idSalaPai
                                    },
                                    parcial: true,
                                    refresh: false
                            });
                        }
                    }

                    titleDraggableEvent(`title-${idSala}`);
                }

                let equipamentos = getEquipamentosBySalaId(idSala);

                /**
                * Carregar equipamentos da sala vinculada
                */
                if(equipamentos)
                {
                    let circleEixoX = $(`circle.circle-${idSala}`).get(1).instance.x(),
                        circleEixoY = $(`circle.circle-${idSala}`).get(1).instance.y();

                    equipamentos.forEach((item,key) => {
                        let equipamento = {
                            x: circleEixoX,
                            y: circleEixoY,
                            idtag: item.idtagFilho,
                            cssicone: item.cssiconeFilho,
                            idtagpai: item.idtagPai,
                            title: item.descricaoFilho,
                            tag: item.tagFilho,
                            valor: item.valor,
                            un: item.un,
                            cor: `rgba(${hexToRgb(item.cor ? item.cor : item.corTipoFilho)}, 1)`,
                            idtagtipo: item.idtagtipoFilho,
                            idempresa: idEmpresa
                        };

                        if(item.device)
                        {
                            equipamento.device = item.device
                            equipamento.deviceInfo = item.deviceInfo
                        }

                        /**
                        * Salva equipamentos no objeto do blocoAtual
                        */
                        blocoAtual.json.equipamentos.push(equipamento);

                        loadEquipment(equipamento, (circleEixoX + 50), (circleEixoY + 50));
                    });
                }

                // path.attr({
                //     posicaoOriginalX: posicaoX,
                //     posicaoOriginalY: posicaoY
                // });

                /**
                * Caso esse mapa não exista, é
                * criado um novo registro no saveprepost
                */

                createOrUpdateMapaEquipamento();

                // Buscar equipamentos
                // let equipamentos = getEquipamentosBySalaId(idSala);

                // resetBtnCreate();

                removeDivChoose();
                lockEquipmentBySalaId(idSala);
            }
        });

        // $('.choose-block').removeClass('hidden');
    }

    /**
    * Criar div para escolher uma sala ou bloco para vinculo
    */
    function createDivChoose(type = 'sala')
    {
        let divChoose = `<div class="choose-block" style="transform: translate(${mouseX}px, ${mouseY}px)">
                            <div class="col-xs-12">
                                <label for="room-choose">Selecionar ${type} </label>
                                <div class="form-group">
                                    <input id="choose" type="text" class="form-control" title="Selecionar ${type}" placeholder="Selecionar ${type}"/>
                                </div>
                            </div>
                        </div>`;

        JQsvgArea.append(divChoose);
    }

    function removeDivChoose()
    {
        $('.choose-block').remove();
    }

    function updateDeviceInfoInterval()
    {
        updateDeviceInterval = setInterval(() => {
            updateDeviceInfo();
            console.log('INFO DEVICE ATUALIZADA');
        }, 5000);
    }

    function updateDeviceInfoIntervalStop()
    {
        clearInterval(updateDeviceInterval);
    }

    /**
    * Resetar valores
    */
    function resetValues(all = false)
    {
        if(all)
        {
            idMapaEquipamentoAtual = "INSERT";

            blocoAtual.json = {};
            // Blocos
            blocoAtual.json.blocos = [];
            // Salas
            blocoAtual.json.salas = [];
            // Equipamentos
            blocoAtual.json.equipamentos = [];

            // Remove titulos da DOM
            removeTitle();
        }

        idSalaQuery = null;

        fontSize = 2.5;

        // Coordenadas do path
        X = 0,
        Y = 0;

        coordenadasL = [],
        mouseX = 0,
        L = "";
        mouseY = 0;

        path = "";

        // Valore para o a lista de opcoes criadas com o btn direito
        optionTipoObjeto = null;
        optionIdObjeto = null;

        salvarLayout = false;

        removeOptions();
    }

    // N sera preciso
    function setDroppableItems()
    {
        $('path').on('mouseup', setSalaVinculo);
    }

    // N sera preciso
    function setSalaVinculo(e)
    {
        idSalaVinculo = 'DESVINCULAR';

        console.log(e.target);
        
        if(e.target.id)
        {
            idSalaVinculo = e.target.id;
        }

        // alert(`Id da sala ${e.target.id}`);
    }

    // Inserir Imagem(planta) na DOM
    function mountImage()
    {
        let imgElement = document.createElement('img');
            JQimgElement = $(imgElement);

        // Definindo atributos
        if(!blocoAtual.planta)
        {
            alertAtencao('Planta não encontrada!');
            // Remover imagens anteriores, se hover
            removeImage();
            return false;
        }
        imgElement.setAttribute('src', blocoAtual.planta)
        imgElement.classList.add('map');

        // Remover imagens anteriores, se hover
        removeImage();

        // Inserir nova imagem (Planta)
        JQsvgArea.append(JQimgElement);

        /**
        * Timeout para o scrip não ser 
        * executado antes da imagem ser inserida
        */
        let delay = setInterval(() => {
            /**
            * Se o tamanho da imagem for fixo, utilizar .innerWidth(), .innerHeight()
            * innerWidth(): pegar o tamanho renderizado da imagem na DOM 
            * sem padding e margin
            */
            /**
            * .width() | .height(): pegar o tamanho original da imagem
            */
            // draw.size(JQimgElement.width(), JQimgElement.height());
            svgWidth  = JQimgElement.prop('naturalWidth');
            svgHeight = JQimgElement.prop('naturalHeight');

            console.log('Carregando planta');

            if(svgWidth)
            {
                $('.svg-area').css('width', `${svgWidth}px`).css('height', `${svgHeight}px`);

                clearInterval(delay);
                console.log('Planta inserida');
            }
        }, 800);

        return true;
    }

    function removeImage()
    {
        if(JQsvgArea.find('img').length)
        {
            JQsvgArea.find('img').remove();
        }
    }

    function removeTitle(id = false)
    {
        if(id)
        {
            removeLine(`line-${id}`);

            return $(`div#${id}`).remove();
        }

        $('div[class*="title-"]:not(.title-m5), div.indicador-pressao').remove();
    }

    function esconderTitulos(id = false, not = false, ocultar = false, desativarInfo = true)
    {
        if(id)
        {
            let JQelement = $(`.svg-area:not(.show-all-title) div#${id}`);

            if(not)
            {
                // $(`.svg-area:not(.show-all-title) div.title-path:not(.hidden):not(#${id})`)
                JQelement = $(`.svg-area:not(.show-all-title) div.title-path:not(#${id})`);
            }

            $(`path[id*=line]:not([hidden=true]):not([id=line-${id}])`).attr('hidden', true);
            $(`path[id*=line]:not([hidden=true]):not([id=line-${id}])`).attr('class', 'hidden');

            JQelement.addClass('hidden')
            if(ocultar)
            {
                JQelement.attr('oculto', true);
            }

            return;
        }

        let paths = $('.title-path');

        if(buscarPathFocado().get().length)
        {
            paths = $(`.title-path:not(#title-${buscarPathFocado().attr('id')})`);
        }

        if(!JQsvgArea.hasClass('show-all-title'))
        {
            paths.attr('hidden', true);
        }

        paths.addClass('hidden');

        $('div[class*="title-"]:not(.title-m5):not(.title-path)').addClass('hidden');
        // $('path[id*=line]').attr('hidden', true);
        hideLine();

        if(desativarInfo)
        {
            JQsvgArea.removeClass('show-all-title');
            JQsvgArea.addClass('hidden-all-title');
        }
    }

    function mostrarTitulos(id = false, removeHidden = false, not = false)
    {
        if(id)
        {
            let JQsala = $(`path#${id.replace('title-', '')}`),
                isSala = (JQsala.attr('tipo') == 'room' || JQsala.hasClass('creating')) ? true : false;

            if(removeHidden)
            {
                $(`div#${id}`).attr('hidden', false);
            }

            if(not)
            {
                $(`div.title-path:not(#${id}):not([hidden])`).removeClass('hidden');
            } else 
            {
                $(`div#${id}:not([hidden])`).removeClass('hidden');
            }

            if(isSala)
            {
                // let JQtitle = $(`div#${id}:not([hidden])`),
                //     X = JQsala[0].instance.x(),
                //     Y = JQsala[0].instance.y(),
                //     posFrom = {
                //         x: X + ((10 * JQsala[0].instance.width()) / 100),
                //         y: Y + ((10 * JQsala[0].instance.height()) / 100)
                //     },
                //     posTo = {
                //         x: parseInt(JQtitle.css('left').replace('px', '')) + parseInt((JQtitle.width() / 2)),
                //         y: parseInt(JQtitle.css('top').replace('px', '')) + (75 * parseInt((JQtitle.height())) / 100)
                //     };

                createLine(`line-${id}`);
            }

            return true;
        }

        if(buscarPathFocado().get().length)
        {
            mostrarTitulosDoPathFocado();

            return true;
        }

        if(JQsvgArea.hasClass('show-all-title'))
        {
            $('div[class*=title-]').removeAttr('hidden');
            return $('div.title-path:not([oculto="true"])').removeClass('hidden');
        }

        $('div[class*=title-]:not([hidden])').removeClass('hidden');
    }

    function mostrarTitulosDoPathFocado()
    {
        let idPath = $('.locked[tipo="room"]').attr('id');

        // Mostrar titulo e linha da sala
        $(`#title-${idPath}`).removeAttr('hidden');

        $(`.item[data-idtagpai='${idPath}']`).each((key, element) => {
            // Mostrar titulo do equipamento
            mostrarTitulos(`title-${$(element).attr('id').split('-')[1]}`);
        });
    }


    /**
    * Carregar equipamentos do mapa
    */
    function loadEquipment(equipment = false, posX = 0, posY = 0)
    {
        /**
        * Carrega um equipamento especifico
        */
        if(equipment)
        {
            // Não carregar equipamentos do tipo quarto termico, pois serao considerados como sala
            if(equipment.idtagtipo == 476)
            {
                return;
            }

            let divEItem = mountEquipmentIcon(equipment, posX, posY);

            if(divEItem)
            {
                JQitemsArea.append(divEItem);
            }

            return itemsDraggablesEvent();
        }

        /**
        * Carregar equipamentos do blocoAtual
        */
        let equipamentos = getEquipments();

        if(equipamentos.length)
        {
            for(let key in equipamentos)
            {
                // Não carregar equipamentos do tipo quarto termico, pois serao considerados como sala
                if(equipamentos[key].idtagtipo == 476)
                {
                    continue;
                }
            
                let divEItem = mountEquipmentIcon(equipamentos[key]);

                if(divEItem)
                {
                    JQitemsArea.append(divEItem);
                }
            }

            itemsDraggablesEvent();
        }

        // clickEquipmentLinkEvent();
    }

    /**
    * Criar indicador caso a tag esteja tenha vinculo com
    * um device
    */
    function mountIndicador(equipment, posX = 0, posY = 0)
    {
        let stylePosition = '';

        if(equipment.x || posX > 0)
        {
            stylePosition = `left: ${equipment.x || posX}px;top: ${equipment.y - 20 || posY - 20}px`;
        }

        return `<div class="indicador rounded" data-idtag="${equipment.idtag}" style="background-color: ${equipment.cor};${stylePosition};">
                    <div class="device px-2 py-1">
                        <span>${equipment.valor || 'Desconhecido'} ${equipment.un}</span>
                    </div>
                </div>`;
    }

    /**
    * Criar icone que ira representar a tag
    */
    function mountEquipmentIcon(equipment, posX = 0, posY = 0)
    {
        let idTagPai = equipment.idtagpai ? equipment.idtagpai : '',
            dataIdTagPai = "",
            stylePosition = "",
            coordenadasXY = null,
            icon = equipment.cssicone ? equipment.cssicone : 'fa fa-question',
            backgroundColor = `background-color: ${equipment.cor ? equipment.cor : 'rgba('+corPadraoEquipamento+')'}`,
            color = `color: ${backgroundColor ? '#fff' : '#000'}`,
            indicador = "",
            isIndicador = false,
            tituloM5 = "",
            empresaDivergente = false,
            equipamentoFocado = "";

        if(idTagPai)
        {
            dataIdTagPai = `data-idtagpai="${idTagPai}"`;

            let JQtagPai = $(`path#${idTagPai}`);

            if(JQtagPai.length)
            {
                let JQTagVo = $(`path#${JQtagPai.attr('id')}`);

                if(JQTagVo.attr('idempresa') != equipment.idempresa)
                {
                    empresaDivergente = true;
                }
            }
        }

        if(!posX && !posY)
        {
            let salaElement = document.getElementById(idTagPai);

            /**
            * Verificar se a sala do equipameno esta na DOM
            * Pode ser q tenha um equipamento do tipo quarto termico
            * que seja uma sala, mas ainda n foi vinculada à um path
            */
            if(salaElement && salaElement.instance)
            {
                posX = salaElement.instance.x() + 50;
                posY = salaElement.instance.y() + 50;
            } else {
                return false;
            }
        }

        if(equipment.x || posX > 0)
        {
            stylePosition = `left: ${equipment.x || posX}px;top: ${equipment.y || posY}px`;
            posX = equipment.x ? equipment.x : posX;
            posY = equipment.y ? equipment.y : posY;
        }

        if(equipment.deviceInfo && equipment.device.iddevice)
        {
            isIndicador = true;
            backgroundColor = `background-color: ${equipment.deviceInfo.cor || 'rgba('+corPadraoEquipamento+')'}`;
            indicador = `<div class="indicadores d-flex flex-column">`;

            for(let info in equipment.deviceInfo)
            {
                if((!equipment.deviceInfo[info] || typeof equipment.deviceInfo[info] != 'object') || !equipment.deviceInfo[info].valor)
                {
                    continue;
                }

                indicador += `  <div>
                                    <span>${equipment.deviceInfo[info].valor}</span><span class="un">${equipment.deviceInfo[info].un}</span>
                                </div>`;
            }

            // equipment.deviceInfo.forEach(item => {
            //     indicador += `<span class="ml-2">${item.valor || 'Desconhecido'} ${item.un || ''}</span>`;
            // });

            tituloM5 = `  <div class="title-m5" style="${backgroundColor};">
                                <span>${equipment.tag}</span>
                            </div>`;

            indicador += `</div>`;
        }

        if(idEquipamentoViaGet && idEquipamentoViaGet == equipment.idtag)
        {
            equipamentoFocado = 'active-equipment';
        }

        let JQelement = $(`#item-${equipment.idtag}`),
            content = `<div class="icon ${isIndicador ? 'px-2 py-1 rounded text-white d-flex align-items-center is-indicador' : ''}" style="${isIndicador ? (backgroundColor+';white-space: nowrap;') : ''}">
                            <i class="${icon} ${isIndicador ? 'hidden' : ''}" data-idtag="${equipment.idtag}" style="${color}"></i>
                            ${tituloM5}
                            ${indicador}
                        </div>`;

        if(!JQelement.length)
        {
            JQelement = $(`<div id="item-${equipment.idtag}" class="item z-index-main ignore-custom-ui ${equipamentoFocado}" title="${equipment.tag ? equipment.tag+'-' : ''}${equipment.title}" ${dataIdTagPai} style="${(empresaDivergente || equipment.localDesatualizado === true) ? 'background-color: #DC143C' : !isIndicador ? backgroundColor : 'background-color: transparent !important'};${stylePosition};" idempresa="${equipment.idempresa}" originalPositionX="${posX}" originalPositionY="${posY}">
                        </div>`);
        } else {
            JQelement.find('div').remove();
        }

        JQelement.append(content);

        // removeTitle(`title-${equipment.idtag}`);

        if(!isIndicador)
        {
            createTitle(`title-${equipment.idtag}`, `${equipment.tag}`, posX, (posY - 10), 'equipamento');
        }

        return JQelement;
    }

    // function verificarSobreposicaoDeEquipamentos(id = false)
    // {
    //     let JQpath = $('path[tipo="room"]')

    //     if(id)
    //     {
    //         JQFilhos = $(`.item[idtagpai = '${id}']`);
    //         let elementosSobrepostos = [],
    //             margemDeDistancia = 29;

    //         for(let key in JQFilhos)
    //         {
    //             let JQelement = JQFilhos[key],
    //                 posicaoX = null;

    //             for(let keyFilho in JQFilhos)
    //             {
    //                 if(key == keyFilho)
    //                 {
    //                     continue;
    //                 }

    //                 let JQelementFilho = $(JQFilhos[keyFilho]),
    //                     posicaoXFilho = parseInt(JQelementFilho.css('left').replace('px', ''));

    //                 if(posicaoXFilho >= JQelement.css('left') && posicaoXFilho <= (JQelement.css('left') + margemDeDistancia))
    //                 {

    //                 }
    //             }
    //         }
    //     }

    //     if(pathId)

    //     JQFilho.forEach(element => {
            
    //     });
    // }

    /**
    * Carregar path's e circles da planta selecionada
    */
    function loadPath()
    {
        // Verifica se json possui algum valor
        if(Object.keys(blocoAtual.json).length)
        {
            let defaultColor = corPadraoSala;

            // Carregando blocos
            blocoAtual.json.blocos.forEach((element, key) => {
                if(element === null)
                {
                    return;
                }

                let rgbColor = hexToRgb(element.cor)|| defaultColor;

                // Desenhar path's
                let pathElement = draw.path(element.path).attr({
                    id: element.id,
                    idunidade: element.idunidade,
                    title: element.titulo,
                    stroke: `rgba(${rgbColor})`,
                    fill: `rgba(${rgbColor}, .1)`,
                    cor: `${rgbColor}`,
                    titleX: element.titleX,
                    titleY: element.titleY,
                    tipo: 'block',
                    form: element.form,
                    idempresa: element.idempresa
                });

                // Definindo cor do objeto do bloco como rgb
                blocoAtual.json.blocos[key]['cor'] = rgbColor;

                // Desenhar circles
                let i = 1
                element.circles.forEach(elementCircle => {
                    /**
                    * cx: Coordenada do eixo X do centro do circulo
                    * cy: Coordenada do eixo Y do centro do circulo
                    * r: O raio do circulo
                    */
                    let JQcircle = createCircle(draw, elementCircle.cx, elementCircle.cy, pathElement.attr('id'), elementCircle.id, 7);

                    JQcircle.attr('side', i);

                    i++;
                });

                // Criando titulo na DOM
                createTitle(`title-${element.id}`, element.titulo, element.titleX ? element.titleX : 0, element.titleY ? element.titleY : 0, 'bloco');
            });

            // Carregando salas
            blocoAtual.json.salas.forEach((element, key) => {
                if(element === null)
                {
                    return;
                }

                let rgbColor = hexToRgb(element.cor)|| defaultColor;

                let JQtagPai = $(`path#${element.idSalaPai}`);

                if(JQtagPai.attr('idempresa') != element.idempresa || element.localDesatualizado === true)
                {
                    rgbColor = hexToRgb("#DC143C");
                }

                // Desenhar path's
                let pathElement = draw.path(element.path).attr({
                    id: element.idSala,
                    idunidade: element.idunidade,
                    idtagpai: element.idSalaPai,
                    title: element.titulo,
                    stroke: `rgba(${rgbColor})`,
                    fill: `rgba(${rgbColor}, .1)`,
                    cor: `${rgbColor}`,
                    titleX: element.titleX,
                    titleY: element.titleY,
                    indpressao: element.indpressao,
                    tipo: 'room',
                    form: element.form,
                    idempresa: element.idempresa
                });

                if(idSalaViaGet && idSalaViaGet == element.idSala)
                {
                    pathElement.attr('class', 'locked');
                }

                // Definindo cor do objeto do bloco como rgb
                blocoAtual.json.salas[key]['cor'] = rgbColor;

                // Desenhar circles
                let i = 1;
                element.circles.forEach(elementCircle => {
                    /**
                    * cx: Coordenada do eixo X do centro do circulo
                    * cy: Coordenada do eixo Y do centro do circulo
                    * r: O raio do circulo
                    */
                    let JQcircle = createCircle(draw, elementCircle.cx, elementCircle.cy, pathElement.attr('id'), elementCircle.id, 7);

                    JQcircle.attr('side', i);

                    i++;
                });

                // Criando titulo na DOM
                createTitle(`title-${element.idSala}`, element.titulo, element.titleX ? element.titleX : 0, element.titleY ? element.titleY : 0);
            });

            /**
            * Carregar equipamentos dessa sala
            */
            loadEquipment();

            /**
            * EventListeners para redimencionar path's
            */
            // pathResize();

            /**
            * Aumenta e diminui o tamanho dos elementos
            * na execucao do zoom
            */
            // resizeElementsInZoom();
        }
    }

    // Converter hex para RGB
    function hexToRgb(hex)
    {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);

        return result ? `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}` : null;
    }

    /**
    * Carrega as informações do mapa
    * Adicionar as informaçõoes no objeto blocoAtual.json
    */
    function loadBlocoJson()
    {
        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'carregarMapaPorIdTag',
                params: blocoAtual.id
            },
            success: response => {
                /**
                * TODO: Verificar resposta
                * Se não houver registro, definir variavel
                * idMapaEquipamentoAtual = false
                */
                if(response.error)
                {
                    idMapaEquipamentoAtual = 'INSERT';

                    return console.log(response.error);
                }

                if(response.mapa)
                {
                    blocoAtual.json = response.mapa;

                    blocoAtual.json.blocos = Object.values(response.mapa.blocos).filter(element => element);
                    blocoAtual.json.salas = Object.values(response.mapa.salas).filter(element => element);
                    blocoAtual.json.equipamentos = Object.values(response.mapa.equipamentos).filter(element => element);
                }
                
                idMapaEquipamentoAtual = response.idmapaequipamento;

                $('#input-idmapaequipamento').val(idMapaEquipamentoAtual);
            },
            error: err => {
                console.log(err);
            }
        });
    }

    function getEquipamentosBySalaId(id)
    {
        return buscarTagsPaiOuFilhos(id, false, 'tp.idtag');
    }

    function buscarTagsPaiOuFilhos(id, notExists = false, type = 'tp.idtag', idMapaEquipamento = false)
    {
        let tags = null;

        if(id.search('sem-vinc') === -1)
        {
            $.ajax({
                url: '../ajax/mapaequipamento.php',
                method: 'GET',
                dataType: 'json',
                async: false,
                data: {
                    action: 'buscarTagsPaiOuFilhos',
                    params: [id, notExists, type, idMapaEquipamento]
                },
                success: responseEquipment => {
                    /**
                    * Tratar erro
                    */
                    if(!responseEquipment)
                    {
                        return tags = false;
                    }

                    if(typeof responseEquipment == "object")
                    {
                        return tags = Object.values(responseEquipment);
                    }

                    tags = responseEquipment;
                }
            });
        }

        return tags;
    }

    function buscarBlocosDisponiveis()
    {
        let blocos = false;

        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'buscarBlocosDisponiveis',
                params: idMapaEquipamentoAtual
            },
            success: response => {
                /**
                * Tratar erro
                */
                if(response.error)
                {
                    console.log(response.error);
                    return false;
                }

                blocos =  response;
            }
        });

        return blocos;
    }

    /**
    * tipo: idtagpai ou idtag
    */
    function buscarTagPaiOuFilho(id, tipo = 'idtagpai')
    {
        let sala = null;

        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'buscarTagPaiOuFilho',
                params: [
                    id, tipo
                ]
            },
            success: response => {
                sala = response;
            }
        });

        return sala;
    }

    function loadTagAutoComplete()
    {
        if(!equipamentoAutoComplete.error)
        {
            $('#tag').autocomplete({
                source: equipamentoAutoComplete,
                delay: 0,
                create: function()
                {
                    $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                        lbItem = `${item.descricao}`;
                        return $('<li>')
                            .append('<a>' + lbItem + '</a>')
                            .appendTo(ul);
                    };
                },
                select: function(event, ui)
                {
                    /**
                    * TODO: Buscar Icones
                    */
                    let equipamento = {
                        idtag: ui.item.idtag,
                        cssicone: ui.item.cssicone,
                        idtagpai: ui.item.idtagpai,
                        title: ui.item.descricao,
                        tag: ui.item.tag
                    };
                    
                    if(!blocoAtual.json.equipamentos)
                    {
                        blocoAtual.json.equipamentos = [];
                    }

                    blocoAtual.json.equipamentos.push(equipamento);

                    /**
                    * Atualizar autocomplete
                    */
                    /**
                    * TODO: Quando o equipamento
                    * tiver mais q uma unidade, apenas
                    * subrair um
                    */
                    equipamentoAutoComplete = equipamentoAutoComplete.filter(item => item.idtag != ui.item.idtag);
                    $( "#tag" ).autocomplete( "option", "source", equipamentoAutoComplete);

                    /**
                    * Caso esse mapa não exista, é
                    * criado um novo registro
                    */
                    createOrUpdateMapaEquipamento()

                    loadEquipment(equipamento);
                }
            });
        }
    }

    // Filtrar tags
    $('#campo-tag-filtro').autocomplete({
        source: tagsParaFiltragem,
        minLength: 3,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                lbItem = `${item.descricao}`;
                return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
            };
        },
        select: function(e, ui) {
            let idTag = `item-${ui.item.idtag}`,
                JQelement = $(`#${idTag}`);
            // Voltar valores do zoom para iniciais
            resetarValoresDeZoom();
            $(this).val(ui.item.descricao);

            // Buscando tag na planta atual
            if(!JQelement.length)
            {
                let infoTagBloco = buscarBlocoDaTag(ui.item.idtag);

                if(infoTagBloco.length)
                {
                    if(!confirm(`TAG ${ui.item.descricao} encontrada no bloco ${infoTagBloco.bloco}, deseja alterar?`)) return false;

                    idBlocoViaGet = infoTagBloco.idbloco;
                    idSalaViaGet = infoTagBloco.idsala;
                    idEquipamentoViaGet = infoTagBloco.idequipamento;

                    carregarMapa(idBlocoViaGet);

                    JQelement.removeClass('animacao-piscar');

                    setTimeout(() => JQelement.addClass('animacao-piscar'), 200);
                    return esconderTags(idTag);
                }

                return mostrarTags();
            }

            JQelement.removeClass('animacao-piscar');

            setTimeout(() => JQelement.addClass('animacao-piscar'), 200);
            esconderTags(idTag);
        }
    });

    $('#campo-tag-filtro').on('blur', function() {
        if(!$(this).val()) return mostrarTags();
    });

    function mostrarTags(idTag = false)
    {
        let JQtags = $('.item'),
            JQtitulosDosEquipamentos = $('.title-path.is-equipment');

        JQtitulosDosEquipamentos.removeAttr('oculto');

        if(idTag)
        {
            JQtags = $(`#${idTag}`);
        }

        mostrarTitulos();
        JQtags.removeClass('hidden');
    }

    function esconderTags(idTag = false)
    {
        let JQtags;

        if(idTag)
        {
            JQtags = $(`.item:not(#${idTag})`);
            esconderTitulos(`title-${idTag.split('-')[1]}`, true, true);
        } else 
        {
            JQtags = $('.item');
            esconderTitulos();
        }

        JQtags.addClass('hidden');

    }

    function resetarValoresDeZoom()
    {
        return panzoom(document.querySelector('.svg-area'), {
            beforeMouseDown: function(e) {
                var shouldIgnore = !e.ctrlKey;
                return shouldIgnore;
            },
            zoomDoubleClickSpeed: 1, 
            initialZoom: zoomInicial,
            maxZoom: 6,
            minZoom: 0.2,
            initialX: coorenadasIniciaisZoom.x,
            initialY: coorenadasIniciaisZoom.y,
        });
    }

    /**
     * Buscar bloco onde tag está localizada
     */
    function buscarBlocoDaTag(idTag)
    {
        let tag = [];

        $.ajax({
            url: './ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'buscarBlocoDaTag',
                params: idTag
            },
            success: response => {
                tag = response;
                tag.length = Object.keys(tag).length;
            }
        });

        return tag;
    }

    /**
    * Buscar tags(equipamentos)
    */
    function buscarTags()
    {
        let equipamentos = null;

        $.ajax({
            url: './ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'buscarTags',
                params: blocoAtual.id
            },
            success: response => {
                equipamentos = response;
            }
        });

        return equipamentos;
    }

    /**
    * Retonar salas do bloco atual
    */
    function getSalasMapa()
    {
        return blocoAtual.json.salas.filter(element => element);
    }

    // Atualizar equipamento (tag) pela chave ( posicao no array )
    function updateEquipmentByKey(key, newValue)
    {
        if(blocoAtual.json.equipamentos)
        {
            return blocoAtual.json.equipamentos[key] = newValue;
        }

        console.log('EQUIPAMENTO NÃO ENCONTRADO PARA ATUALIZAÇÃO');
    }

    /**
    * Remove a sala do objeto blocoAtual
    */
    function removeSalaByKey(salaKey)
    {
        if(blocoAtual.json.salas[salaKey])
        {
            delete blocoAtual.json.salas[salaKey];

            blocoAtual.json.salas = blocoAtual.json.salas.filter(element => element);

            console.log("sala removida do blocoAtual");
        }
    }

    /**
    * Remove a sala do objeto blocoAtual pelo id
    */
    function removeSalaById(id)
    {
        for(let i in getSalasMapa())
        {
            if(getSalasMapa()[i].idSala == id)
            {
            delete blocoAtual.json.salas[i];

            break;
            }
        }

        blocoAtual.json.salas = blocoAtual.json.salas.filter(element => element);
    }

    /**
    * Remove o bloco do objeto blocoAtual pelo id
    */
    function removeBlocoById(id)
    {
        for(let i in getBlocosMapa())
        {
            if(getBlocosMapa()[i].id == id)
            {
            delete blocoAtual.json.blocos[i];
            }
        }

        blocoAtual.json.blocos = blocoAtual.json.blocos.filter(element => element);
    }

    function removeEquipamentoByKey(equipamentoKey)
    {
        if(blocoAtual.json.equipamentos[equipamentoKey])
        {
            delete blocoAtual.json.equipamentos[equipamentoKey];

            blocoAtual.json.equipamentos = blocoAtual.json.equipamentos.filter(element => element);

            console.log("equipamento removido do blocoAtual");
        }
    }

    /**
    * Remove equipamentos da DOM pelo id da sala
    * TODO: remover do objeto do blocoAtual se necessario
    */
    function removeEquipmentBySalaId(id, removeJson = false)
    {
        if(removeJson)
        {
            for(let key in blocoAtual.json.equipamentos)
            {
                if(blocoAtual.json.equipamentos[key]['idtagpai'] && blocoAtual.json.equipamentos[key]['idtagpai'] == id)
                {
                    // Remover titulo da DOM
                    removeTitle(`title-${blocoAtual.json.equipamentos[key]['idtag']}`);

                    delete blocoAtual.json.equipamentos[key];
                }
            }

            blocoAtual.json.equipamentos = blocoAtual.json.equipamentos.filter(element => element);   
        }

        // Removendo da DOM
        $(`div[data-idtagpai='${id}']`).remove();
    }

    /**
    * Retorna o objeto da sala a partir da posicao passada
    */
    function getSalaByKey(salaKey)
    {
        return getSalasMapa().filter((element, key) => key == salaKey)[0];
    }

    /**
    * Retorna o objeto da sala a partir do id
    */
    function getSalaById(id)
    {
        if(getSalasMapa().length)
        {
            return getSalasMapa().filter(element => element.idSala == id)[0];
        }

        return false;
    }

    // Retonar o objeto do bloco a partir do id
    function getBlocoById(id)
    {
        if(getBlocosMapa().length)
        {
            return getBlocosMapa().filter(element => element.id == id)[0];
        }
    }

    /**
    * Atualiza os valores do path de uma sala
    * a partir do id
    */
    function updatePathBySalaId(id, newValue)
    {
        getSalasMapa().forEach
        ((element, key) => {
            if(element.idSala == id)
            {
                return blocoAtual.json.salas[key].path = newValue;
            }
        });
    }

    // Atualiza as informacoes do device
    function updateDeviceInfo()
    {
        let error = false,
            type = $('#un').val();

        if(!getCurrentDevices().length)
        {
            updateDeviceInfoIntervalStop();
            
            return console.log('Nenhum DEVICE encontrado!');
        }

        if(!type)
        {
            $(`.item`).find('.icon').css('transform', 'scale(1)');
            
            $('.indicadores *').remove();
            $('.is-indicador > i').removeClass('hidden');

            return false;
        }

        $('.is-indicador > i').addClass('hidden');

        let idDasTags = getCurrentDevices().map(element => {
            if(element[Object.keys(element)[0]].device.idtagoriginal)
            {
                return element[Object.keys(element)[0]].device.idtagoriginal;
            }

            return element[Object.keys(element)[0]].idtag;
        }).join('-'),
            idDosDevices = getCurrentDevices().map(element => element[Object.keys(element)[0]].device.iddevice).join('-'),
            idDosDevicesSensoresBloco = getCurrentDevices().map(element => element[Object.keys(element)[0]].device.iddevicesensorbloco).join('-');

        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: true,
            data: {
                action: 'buscarInformacoesDoDevice',
                params: [
                    idDosDevices,
                    idDasTags,
                    idDosDevicesSensoresBloco,
                    type.join('|')
                ]
            },
            success: response => {
                if(Object.keys(response).length)
                {
                    for(let id in response)
                    {
                        let equipmentId = id,
                            newEquipment = {},
                            JQequipamento = $(`#item-${equipmentId}`);

                        newEquipment.deviceInfo = [];
                        newEquipment.deviceInfo['cor'] =  response[equipmentId].cor

                        if(Object.keys(response[equipmentId]).includes('valor'))
                        {
                            newEquipment.deviceInfo = [{
                                un: response[equipmentId].un,
                                valor: response[equipmentId].valor
                            }];
                        } else 
                        {
                            for(let index in response[equipmentId])
                            {
                                if(index != 'cor' && index != 'label')
                                {
                                    newEquipment.deviceInfo.push({
                                        un: response[equipmentId][index].un,
                                        valor: response[equipmentId][index].valor
                                    });
                                }
                            }
                        }

                        if(!JQequipamento.length)
                        {
                            let equipamentOriginal = buscarDevicePorIdOriginal(equipmentId);

                            if(equipamentOriginal)
                            {
                                equipmentId = equipamentOriginal.idtag;
                                JQequipamento = $(`#item-${equipmentId}`);
                            }
                        }

                        newEquipment = updateEquipmentById(equipmentId, newEquipment);

                        if(newEquipment)
                        {
                            loadEquipment(newEquipment);

                            // JQequipamento.find('.title-m5').removeClass('hidden');
                            JQequipamento.find('.icon').css('transform', 'scale(3)');
                        }
                    }
                } else {
                    console.log('ERRO AO ATUALIAR UNIDADE DE MEDIDA');
                    
                    error = true;
                }
            }
        });

        if(!error)
        {
            createOrUpdateMapaEquipamento();
        }
    }

    function buscarDevicePorIdOriginal(id)
    {
        let device = false;

        for(let key in getCurrentDevices())
        {
            if(getCurrentDevices()[key][Object.keys(getCurrentDevices()[key])[0]].device.idtagoriginal == id)
            {
                device = getCurrentDevices()[key][Object.keys(getCurrentDevices()[key])[0]];

                break;
            }
        }

        return device;
    }

    /**
    * Atualiza os valores da sala
    * a partir do id
    */
    function updateSalaById(id, newValue)
    {
        var newValueObj = newValue;

        for(let i in getSalasMapa())
        {
            if(getSalasMapa()[i].idSala == id)
            {
                for(let element in newValueObj)
                {
                    blocoAtual.json.salas[parseInt(i)][element] = newValueObj[element];
                }

                break;
            }
        }
    }

    function updateBlocoById(id, newValue)
    {
        var newValueObj = newValue;

        for(let i in getBlocosMapa())
        {
            if(getBlocosMapa()[i].id == id)
            {
                for(let element in newValueObj)
                {
                    blocoAtual.json.blocos[parseInt(i)][element] = newValueObj[element];
                }

                break;
            }
        }
    }

    function getBlocosMapa()
    {
        return blocoAtual.json.blocos;
    }

    /**
    * Atualiza os equipamentos de uma sala
    */
    function updateEquipmentsSalaBySalaId(id, newValue)
    {
        let ids = getEquipments().filter(element => element.idtagpai == id).map(elementMap => elementMap.idtag);

        if(!ids.includes(newValue.idtag))
        {
            return blocoAtual.json.equipamentos.push(newValue);
        }

        for(let i in getEquipments())
        {
            if(getEquipments()[i].idtagpai == id && getEquipments()[i].idtag == newValue.idtag)
            {
                blocoAtual.json.equipamentos[parseInt(i)] = newValue;
            }
        }
    }

    function getEquipamentoById(id)
    {
        var equipamento = null;

        for(let key in getEquipments())
        {
            if(getEquipments()[key].idtag == id)
            {
                equipamento = blocoAtual.json.equipamentos[key];

                break;
            }
        }
        // getEquipments().every((element, key) => {
        //     if(element.idtag == id)
        //     {
        //         equipamento = blocoAtual.json.equipamentos[key];

        //         return false;
        //     }
        // });

        if(!equipamento)
        {
            console.log('Equipamento não encontrado no objeto do blocoAtual')
        }

        return equipamento;
    }

    /**
    * Atualiza os valores do circulo
    * a partir do id da sala e do circulo
    */
    function updateCircleByPathId(pathId, idCircle = false, newValue, type = 'room')
    {
        let objeto = type == 'block' ? getBlocosMapa() : getSalasMapa();

        for(let key in objeto)
        {
            if(type == 'block')
            {
                if(objeto[key].id == pathId)
                {
                    
                }
            }

            if(type == 'room')
            {
                if(objeto[key].idSala == pathId)
                {
                    for(let indexCircle in objeto[key].circles)
                    {
                        if(idCircle && idCircle == objeto[key].circles[indexCircle].id)
                        {
                            for(let newKey in newValue)
                            {
                                blocoAtual.json.salas[key].circles[indexCircle][newKey] = newValue[newKey];
                            }

                            break;
                        }

                        let JQcircle = $(`circle#${objeto[key].circles[indexCircle].id}`);

                        blocoAtual.json.salas[key].circles[indexCircle]['cx'] = JQcircle.attr('cx');
                        blocoAtual.json.salas[key].circles[indexCircle]['cy'] = JQcircle.attr('cy');
                    }

                    break;
                }
            }
        }

        // getSalasMapa().forEach((element, key) => {
        //     if(element.idSala == idSala)
        //     {
        //         if(!idCircle)
        //         {

        //         }

        //         return element.circles.forEach((elementCircle, keyCircle) => {
        //             if(elementCircle.id == idCircle)
        //             {
        //                 newValue.id = elementCircle.id;
        //                 newValue.r = elementCircle.r;

        //                 return blocoAtual.json.salas[key].circles[keyCircle] = newValue;
        //             }
        //         })
        //     }
        // });
    }

    /**
    * Remove todos as salas Path's da DOM
    */
    function clearPaths()
    {
        $('path').remove();
        $('circle').remove();
    }

    /**
    * Remove todos os textPaths's da DOM
    */
    function clearText()
    {
        $('svg text').remove();
    }

    /**
    * Remove todos items da DOM
    */
    function clearItems()
    {
        $('.items div').remove();
    }

    /**
    * Pegar equipamentos do blocoAtual
    */
    function getEquipments()
    {
        if(!blocoAtual.json.equipamentos)
        {
            return [];
        }

        return blocoAtual.json.equipamentos;
    }

    /**
    * Pegar equipamentos que possuam vinculo com device
    */
    function getCurrentDevices()
    {
        let devicesArr = [];

        if(!blocoAtual.json)
        {
            return devicesArr;
        }

        getEquipments().forEach((element, key) => {
            if(element.device && element.device.iddevice)
            {
                let data = {};

                data[key] = element;

                devicesArr.push(data);
            }
        });

        return devicesArr;
    }

    /**
    * Apresenta opções de edicao da sala
    */
    function createOptions(posX, posY, tipo = 'room')
    {
        $('.option-list').remove();

        let changeEventType = {
            'room': 'change-room',
            'block': 'change-block'
        };
        
        // mouseX = optionTipoObjeto != 'path' ? (parseInt(getComputedStyle(e.currentTarget).left.replace('px', '')) + 7) : e.offsetX;
        // mouseY = optionTipoObjeto != 'path' ? (parseInt(getComputedStyle(e.currentTarget).top.replace('px', '')) + 7) : e.offsetY;

        let divOptions = `<div class="option-list" style="left: ${posX}px; top: ${posY}px">
                                <ul>`;

        if(optionTipoObjeto != 'path')
        {
            divOptions += `<li id="remove" title="Remover sala">
                                <i class="fa fa-trash"></i>
                            </li>`;
        } else 
        {
            divOptions += `<li id="${changeEventType[tipo]}" title="Alterar sala">
                                <i class="fa fa-edit"></i>
                            </li>
                            <li id="btn-show-hide-palette" title="Alterar cor da Sala">
                                <i class="fa fa-paint-brush"></i>
                            </li>
                            <li id="remove" title="Remover sala">
                                <i class="fa fa-trash"></i>
                            </li>`;
        }

        divOptions += `</ul>
                    </div>`;

        // if(optionTipoObjeto == 'path')
        // {
        //     // Definindo path clicado como atual
        //     if(optionIdObjeto.indexOf('sem') === -1)
        //     {
        //         path = getSalaById(optionIdObjeto);
        //     }

        //     if(!path)
        //     {
        //         return console.log('Id da sala selecionada não encontrada');
        //     }
        // }
        
        JQsvgArea.append(divOptions);

        // setTimeout(() => {
        //     optionsEvent();
        // }, 100);
    }

    function removeOptions()
    {
        $('.option-list').remove();
    }

    function removerBlocoDeEscolhaDaTagParaVinculo()
    {
        $('.choose-block').remove()
    }

    function showOptions()
    {
        $('.option-list.hidden').removeClass('hidden');
    }

    function hideOptions()
    {
        $('.option-list:not(.hidden)').addClass('hidden');
    }

    function optionsEvent()
    {
        // Alterar Bloco
        $('.main-content').on('click', '#change-block', function(e)
        {
            chooseBlock(true);
            removeOptions();
        });

        // Alterar sala
        $('.main-content').on('click', '#change-room', function(e)
        {
            chooseRoom(true);
            removeOptions();
        });

        // Atualizar dados da sala
        // $('.main-content').on('click', '#update-room', function(e)
        // {
        //     updateRoom();
        //     removeOptions();
        // });

        // Remove empresa / blooco / sala / tag selecionada
        $('.main-content').on('click', '#remove', function(e)
        {
            removableEvent();
            removeOptions();
        });
    }


    function getMapaLastInsertId()
    {
        let idMapa = false;

        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'buscarUltimoRegistroDoMapaEquipamento'
            },
            success: response => {
                if(response.error)
                {
                    idMapa = false;

                    return console.log(response.error);
                }
                
                idMapa = response.idmapaequipamento;

                $('#input-idmapaequipamento').val(idMapa);
            },
            error: err => {
                console.log(err);
            }
        });

        return idMapa;
    }

    function getMapaEquipamentoByTagId()
    {
        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'buscarMapaPorIdTag',
                params: blocoAtual.id
            },
            success: response => {
                if(response.error)
                {
                    idMapaEquipamentoAtual = false;

                    return console.log(response.error);
                }
                
                idMapaEquipamentoAtual = response.idmapaequipamento;

                $('#input-idmapaequipamento').val(idMapaEquipamentoAtual);
            },
            error: err => {
                console.log(err);
            }
        });
    }

    function buscarTagPorIdTag(id)
    {
        let tag = null;

        $.ajax({
            url: '../ajax/mapaequipamento.php',
            method: 'GET',
            dataType: 'json',
            async: false,
            data: {
                action: 'buscarTagPorIdTag',
                params: id
            },
            success: response => {
                tag = response
            },
            error: err => {
                console.log(err);
            }
        });

        return tag;
    }

    /**
    * Atualizar equipamentos e demais informacoes 
    * da sala ( usa o path atual como referencia )
    */
    function updateRoom()
    {
        let emptyRoom = false;

        let idSala = path.idSala ? path.idSala : path.attr('id'),
            equipments = buscarTagsPaiOuFilhos(idSala, false, 'tp.idtag'),
            // Equipamentos da sala clicada (json)
            currentEquipmets = getEquipmentsBySalaId(idSala),
            ids = equipments ? equipments.map(element => element.idtagFilho) : [];

        let equipmentObject = {};

        removeEquipmentBySalaId(idSala);

        // Verificar se o json está vazio
        if(!currentEquipmets.length && equipments)
        {
            emptyRoom = true;

            for(let key in equipments)
            {
                equipmentObject = {
                    cor: equipments[key]['cor'] || null,
                    cssicone: equipments[key]['cssiconeFilho'],
                    idtag: equipments[key]['idtagFilho'],
                    idtagpai: equipments[key]['idtagPai'],
                    title: equipments[key]['descricaoFilho'],
                    un: equipments[key]['un'] || null,
                    valor: equipments[key]['valor'] || null,
                    x: (path.x ? path.x : document.getElementById(idSala).instance.x() + 50),
                    y: (path.y ? path.y : document.getElementById(idSala).instance.y() + 50),
                    idtagtipo: equipments[key]['idtagtipoFilho'],
                    tag: equipments[key]['tagFilho']
                };

                // Verifica se o equipamento possui vinculo com device
                if(equipments[key]['device'])
                {
                    equipmentObject.device = {
                        iddevice: equipments[key]['device']['iddevice'],
                        iddevicesensorbloco: equipments[key]['device']['iddevicesensorbloco'],
                        tipo: equipments[key]['device']['tipo']
                    };

                    // Pegar informacoes sobre pressao temperatura etc
                    // Valor padrao: temperatura (t)
                    if(equipments[key]['deviceInfo'])
                    {
                        equipmentObject.deviceInfo = [
                            {
                                valor: equipments[key]['deviceInfo'][0]['valor'],
                                un: equipments[key]['deviceInfo'][0]['un'],
                                cor: equipments[key]['deviceInfo'][0]['color']
                            }
                        ];
                    }
                }

                currentEquipmets.push(equipmentObject);

                loadEquipment(equipmentObject);

                $(`#item-${equipmentObject.idtag}`).addClass('locked-equipment');
            }
        }

        // Verificar se nao ha mais nenhum equipamento vinculado a sala
        if(currentEquipmets.length && !equipments)
        {
            for(let keyEquip in currentEquipmets)
            {
                equipmentObject = {
                    cor: equipments[key]['cor'] || null,
                    cssicone: equipments[key]['cssiconeFilho'],
                    idtag: equipments[key]['idtagFilho'],
                    idtagpai: equipments[key]['idtagPai'],
                    title: equipments[key]['descricaoFilho'],
                    un: equipments[key]['un'] || null,
                    valor: equipments[key]['valor'] || null,
                    x: (path.x ? path.x : document.getElementById(idSala).instance.x() + 50),
                    y: (path.y ? path.y : document.getElementById(idSala).instance.y() + 50),
                    idtagtipo: equipments[key]['idtagtipoFilho'],
                    tag: equipments[key]['tagFilho']
                };

                if(equipments[key]['device'])
                {
                    equipmentObject.device = {
                        iddevice: equipments[key]['device']['iddevice'],
                        iddevicesensorbloco: equipments[key]['device']['iddevicesensorbloco'],
                        tipo: equipments[key]['device']['tipo']
                    };

                    // Pegar informacoes sobre pressao temperatura etc
                    // Valor padrao: temperatura (t)
                    if(equipments[key]['deviceInfo'])
                    {
                        equipmentObject.deviceInfo = [
                            {
                                valor: equipments[key]['deviceInfo'][0]['valor'],
                                un: equipments[key]['deviceInfo'][0]['un'],
                                cor: equipments[key]['deviceInfo'][0]['color']
                            }
                        ];
                    }
                }

                currentEquipmets.push(equipmentObject);

                loadEquipment(equipmentObject);
                $(`#item-${equipmentObject.idtag}`).addClass('locked-equipment');
            }
        }

        if(equipments && !emptyRoom)
        {
            for(let key in equipments)
            {
                // Verifica se equipameto já esta no json
                let have = false;
                // Atualizar dados do equipamento no objeto que sera salvo como json
                for(let keyTwo in currentEquipmets)
                {
                    let idEquipment = currentEquipmets[keyTwo]['idtag'];

                    // Verifica se o equipamento ainda pertence a sala
                    if(!ids.includes(idEquipment))
                    {
                        removeEquipmentById(idEquipment);
                        delete currentEquipmets[keyTwo];

                        continue;
                    }

                    if(idEquipment == equipments[key]['idtagFilho'] && !have)
                    {
                        have = true;

                        equipmentObject = {
                            cor: equipments[key]['cor'] || null,
                            cssicone: equipments[key]['cssiconeFilho'],
                            idtag: equipments[key]['idtagFilho'],
                            idtagpai: equipments[key]['idtagPai'],
                            title: equipments[key]['descricaoFilho'],
                            un: equipments[key]['un'] || null,
                            valor: equipments[key]['valor'] || null,
                            x: currentEquipmets[keyTwo]['x'],
                            y: currentEquipmets[keyTwo]['y'],
                            idtagtipo: equipments[key]['idtagtipoFilho'],
                            tag: equipments[key]['tagFilho']
                        };

                        if(equipments[key]['device'])
                        {
                            equipmentObject.device = {
                                iddevice: equipments[key]['device']['iddevice'],
                                iddevicesensorbloco: equipments[key]['device']['iddevicesensorbloco'],
                                tipo: equipments[key]['device']['tipo']
                            };

                            // Pegar informacoes sobre pressao temperatura etc
                            // Valor padrao: temperatura (t)
                            if(equipments[key]['deviceInfo'])
                            {
                                equipmentObject.deviceInfo = [
                                    {
                                        valor: equipments[key]['deviceInfo'][0]['valor'],
                                        un: equipments[key]['deviceInfo'][0]['un'],
                                        cor: equipments[key]['deviceInfo'][0]['color']
                                    }
                                ];
                            }
                        }

                        currentEquipmets[keyTwo] = equipmentObject;

                        // break;
                    }
                }

                if(!have)
                {
                    equipmentObject = {
                        cor: equipments[key]['cor'] || null,
                        cssicone: equipments[key]['cssiconeFilho'],
                        idtag: equipments[key]['idtagFilho'],
                        idtagpai: equipments[key]['idtagPai'],
                        title: equipments[key]['descricaoFilho'],
                        un: equipments[key]['un'] || null,
                        valor: equipments[key]['valor'] || null,
                        x: (path.x ? path.x : document.getElementById(idSala).instance.x() + 50),
                        y: (path.y ? path.y : document.getElementById(idSala).instance.y() + 50),
                        idtagtipo: equipments[key]['idtagtipoFilho'],
                        tag: equipments[key]['tagFilho']
                    };

                    if(equipments[key]['device'])
                    {
                        equipmentObject.device = {
                            iddevice: equipments[key]['device']['iddevice'],
                            iddevicesensorbloco: equipments[key]['device']['iddevicesensorbloco'],
                            tipo: equipments[key]['device']['tipo']
                        };

                        // Pegar informacoes sobre pressao temperatura etc
                        // Valor padrao: temperatura (t)
                        if(equipments[key]['deviceInfo'])
                        {
                            equipmentObject.deviceInfo = [
                                {
                                    valor: equipments[key]['deviceInfo'][0]['valor'],
                                    un: equipments[key]['deviceInfo'][0]['un'],
                                    cor: equipments[key]['deviceInfo'][0]['color']
                                }
                            ];
                        }
                    }

                    currentEquipmets.push(equipmentObject);
                }

                loadEquipment(equipmentObject);
                $(`#item-${equipmentObject.idtag}`).addClass('locked-equipment');
            };   
        }

        currentEquipmets.filter(element => element).forEach(equipmentElement => {
            updateEquipmentsSalaBySalaId(idSala, equipmentElement);
        });

        /**
        * TODO: VERIFICAR
        * por que está sendo criado outro registro em mapaequipamento
        */
        createOrUpdateMapaEquipamento();

        alertSalvo('Sala atualizada!')
    }

    function showHideInfo()
    {
        $('.info').on('click', function()
        {
            // Mostra titulos
            if(!JQsvgArea.hasClass('show-all-title'))
            {
                JQsvgArea.removeClass('hidden-all-title');
                JQsvgArea.addClass('show-all-title');

                $('.title-path.is-room').removeAttr('oculto');

                /**
                * É necessario mostrar o titulo para que a propriedade
                * width e height do elemento seja valida
                */
                $('.title-path:not([oculto])').attr('hidden', false);

                let pathFocado = buscarPathFocado();

                if(pathFocado.get().length)
                {
                    $(`#title-${pathFocado.attr('id')}`).removeClass('hidden');
                } else 
                {
                    $('.title-path.is-room:not([oculto])').removeClass('hidden');
                }

                mostrarTitulos();

                return showLine();
            }

            // Remove titulos
            hideLine();
            JQsvgArea.removeClass('show-all-title');
            JQsvgArea.addClass('hidden-all-title');
            $('.title-path.is-room').attr('oculto', true);

            esconderTitulos();
        });
    }

    function buscarPathFocado()
    {
        if($('.locked[tipo="room"]').get().length)
        {
            return $('.locked[tipo="room"]');
        }

        return $();
    }

    function esconderTitulosQuePossuemAttrOculto()
    {
        if(!JQsvgArea.hasClass('show-all-title'))
        {
            $('.title-path[oculto]').addClass('hidden');
        }

        $('.title-path[oculto]').attr('hidden', true);
    }

    function showColorPalette()
    {
        removeOptions();

        let JQcolorPalette = $('.color-palette');

        if(!JQcolorPalette.hasClass('opacity-1'))
        {
            JQcolorPalette.css('transform', 'translateX(100%)');
            JQcolorPalette.addClass('opacity-1');
        }
    }

    function hideColorPalette()
    {
        let JQcolorPalette = $('.color-palette');

        if(JQcolorPalette.hasClass('opacity-1'))
        {
            JQcolorPalette.css('transform', 'translateX(-100%)');

            JQcolorPalette.removeClass('opacity-1');
        }
    }

    // Mostrar / ocultar planta
    function showHideRoomImage(e)
    {
        defineCursor();
        disableRoomCreation();

        let JQelement = $(e.currentTarget),
            JQmapImage = $('.map');

        if(JQmapImage.hasClass('opacity-0'))
        {
            JQmapImage.removeClass('opacity-0');
            JQelement.addClass('active-eye');
        } else 
        {
            JQmapImage.addClass('opacity-0');
            JQelement.removeClass('active-eye');
        }
    }

    function updateEquipmentById(id, newValue)
    {
        let equipamento = false;

        if(getEquipments().length)
        {
            /**
            * Atualiza equipamento
            */
            for(let key in getEquipments())
            {
                if(getEquipments()[key].idtag == id)
                {
                    let JQequipamento = $(`#item-${id}`);

                    for(let i in newValue)
                    {
                        if(i == 'idtag')
                        {
                            let novoId =  newValue[i];

                            let idNovoDoEquipamento = `item-${newValue[i]}`;

                            JQequipamento.attr('id', idNovoDoEquipamento);
                            JQequipamento.find('i').data('idtag', idNovoDoEquipamento.split('-')[1]);
                        }

                        if(i == 'idempresa')
                        {
                            let idDaNovaEmpresa = newValue[i],
                                JQpath = $(`path[idempresa='${idDaNovaEmpresa}']:not([tipo='block'])`),
                                siglaDaEmpresaDaSalaDropada = JQpath.attr('title').split('-')[0],
                                equipamento = getEquipamentoById(blocoAtual.json.equipamentos[key]['idtag']);

                            let novaDescricaoDoEquipamento = JQequipamento.attr('title').split('-');
                            
                            // Sigla da empresa
                            novaDescricaoDoEquipamento[0] = siglaDaEmpresaDaSalaDropada;
                            // Tag
                            novaDescricaoDoEquipamento[1] = equipamento.tag;

                            novaDescricaoDoEquipamento = novaDescricaoDoEquipamento.join('-');

                            // Atualizar empresa do equipamento
                            JQequipamento.attr('idempresa', idDaNovaEmpresa);
                            JQequipamento.attr('title', novaDescricaoDoEquipamento);

                            blocoAtual.json.equipamentos[key]['title'] = novaDescricaoDoEquipamento;
                        }

                        if(i == 'idtagpai')
                        {
                            let pathDropado = $(`path#${newValue['idtagpai']}`);

                            if(JQequipamento.attr('idempresa') != pathDropado.attr('idempresa'))
                            {
                                let novaDescricaoDoEquipamento = JQequipamento.attr('title').split('-'),
                                    siglaDaEmpresaDaSalaDropada = pathDropado.attr('title').split('-')[0];
                            
                                // Sigla da empresa
                                novaDescricaoDoEquipamento[0] = siglaDaEmpresaDaSalaDropada;

                                novaDescricaoDoEquipamento = novaDescricaoDoEquipamento.join('-');
                                JQequipamento.attr('title', novaDescricaoDoEquipamento);
                            }
                        }

                        blocoAtual.json.equipamentos[key][i] = newValue[i];
                    }
                    
                    equipamento = getEquipments()[key];

                    break;
                }
            }
        }

        return equipamento;
    }

    function getEquipmentsBySalaId(id)
    {
        return getEquipments().filter(element => element.idtagpai == id);
    }

    /**
    * Filtrar equipamentos. mostrar / ocultar
    */
    function filtrarEquipamentosPorTipo(ids, tipo = 'idtagtipo')
    {
        $('.item').removeClass('filtered');
        let key = tipo;

        // if(ids)
        // {
        //     $('#clear-filter').removeClass('hidden');
        // }

        if(!ids)
        {
            mostrarTitulos();

            return clearFilters();
        }

        if(getEquipments().length)
        {
            /**
            * Mostra todos os equipamentos para proxima filtragem 
            * caso tenha tido uma filtragem anterior
            * 
            * (remove filtros)
            */
            clearFilters(false);

            for(let index in getEquipments()[0])
            {
                if(index == tipo)
                {
                    key = tipo;

                    break;
                }
            }
        }

        getEquipments().forEach(element => {
            if(!ids.includes(element[key]))
            {
                $(`i[data-idtag='${element.idtag}']`).parent().parent().addClass('hidden');
                $(`i[data-idtag='${element.idtag}']`).parent().parent().removeClass('filtered');

                // Esconder titulo da tag
                $(`#title-${element.idtag}`).attr('oculto', true);
                esconderTitulos(`title-${element.idtag}`);
            } else {
                $(`i[data-idtag='${element.idtag}']`).parent().parent().addClass('filtered');

                if(!JQsvgArea.hasClass('show-all-title'))
                {
                    // Mostrar titulo da tag
                    mostrarTitulos(`title-${element.idtag}`);
                }

                $(`#title-${element.idtag}`).removeAttr('oculto');
            }
        });

        esconderTitulosQuePossuemAttrOculto();

        /**
        * Pegar todos as tags com o tipo diferente do filtrado
        * para serem ocultados
        */
        // getEquipments().filter(element => !ids.includes(element[key])).forEach(filteredElement => {
        //     $(`i[data-idtag='${filteredElement.idtag}']`).parent().parent().addClass('hide');
        // });

        /**
        * Pegar todos as tags com o tipo igual do filtrado
        */
        // getEquipments().filter(element => ids.includes(element[key])).forEach(filteredElement => {
        //     $(`i[data-idtag='${filteredElement.idtag}']`).parent().parent().addClass('filtered');
        // });
    }

    /**
    * Mostra todos os equipamentos ignorando o filtro atual
    */
    function clearFilters(reset = true)
    {
        if(reset)
        {
            let JQoptions = $('#tipo').children();
            JQoptions.removeAttr('selected');

            $('#clear-filter').addClass('hidden');
        }

        getEquipments().forEach(equipment => {
            $(`i[data-idtag='${equipment.idtag}']`).parent().parent().removeClass('hidden');
            $(`#title-${equipment.idtag}`).removeAttr('oculto');
            mostrarTitulos(`title-${equipment.idtag}`);
        });
    }

    /**
    * Caso esse mapa não exista, é
    * criado um novo registro
    */
    function createOrUpdateMapaEquipamento()
    {
        if(idMapaEquipamentoAtual === false)
        {
            return alertAtencao('Erro ao atualizar planta: idmapaequipamentoatual não encontrado');
        }

        if(idMapaEquipamentoAtual === 'INSERT')
        {
            CB.post({
                objetos: {
                    "_x_i_mapaequipamento_json": `${JSON.stringify(blocoAtual.json)}`,
                    "_x_i_mapaequipamento_idtag": `${blocoAtual.id}`
                },
                parcial: true,
                refresh: false,
                msgSalvo:false
            });


            idMapaEquipamentoAtual = getMapaLastInsertId();
        } else {
            CB.post({
                objetos: {
                    "_x_u_mapaequipamento_idmapaequipamento": idMapaEquipamentoAtual,
                    "_x_u_mapaequipamento_json": `${JSON.stringify(blocoAtual.json)}`,
                    "_x_u_mapaequipamento_idtag": `${blocoAtual.id}`
                },
                parcial: true,
                refresh: false,
                msgSalvo:false
            });
        }
    }

    // Criar titulo na DOM
    function createTitle(id, title, posX, posY, type = 'sala')
    {
        let pathId = id.search('sem') === -1 ? id.split('-')[1] : id.replace('title-', '');
            isPath = $(`path#${pathId}`).length ? true : false;

        let sala = getSalaById(pathId),
            bgColor = isPath ? `rgba(${corPadraoSala})` : '#f1f1f1',
            color = isPath ? '#fff' : '#000',
            indicadorPressao = '';

        let hidden = (pathId == idSalaViaGet) ? '' : 'hidden',
            locked = (pathId == idSalaViaGet) ? 'locked' : '';

        if(sala)
        {
            bgColor = `rgba(${sala.cor})`

            if(sala.indpressao)
            {
                indicadorPressao = `<div class="indicador-pressao" style="background-color:${bgColor};">`;

                for(let i = 0; i < parseInt(sala.indpressao); i++)
                {
                    indicadorPressao += `<i class="fa fa-plus"></i>`;
                }

                indicadorPressao += `</div>`;
            }
        }

        let JQdivTitle = $(`#${id}`),
            content = `<h1 class="${locked}" style="font-size: ${fontSize}rem;color:${color};background-color: ${bgColor};">${title}</h1>
                        ${indicadorPressao}`,
            exists = JQdivTitle.length ? true : false ;
            
        if(!exists)
        {
            JQdivTitle = $(`<div id="${id}" class="title-path ignore-custom-ui ${hidden} ${titleTypes[type]}" style="left: ${posX}px; top: ${posY}px;" hidden=true>   
                        </div>`);
        } else
        {
            JQdivTitle.find('*').remove();
        }

        JQdivTitle.append(content);
        JQsvgArea.append(JQdivTitle);

        if(pathId == idSalaViaGet)
        {
            createLine(`line-${id}`);
            $(`#line-${id}`).attr('class', 'locked');
        }

        if(!exists)
        {
            titleDraggableEvent(id);
        }

        // setTimeout(clickRoomLinkEvent, 200);
    }

    /**
    * Criar uma linha que liga de um ponto
    * a outro
    */
    function createLine(id)
    {
        let JQtitleId = id.replace('line-', '');
            JQtitle = $(`div#${JQtitleId}.is-room:not([hidden])`),
            JQsala = $(`path#${JQtitleId.replace('title-', '')}`);

        if(!JQsala.length || !JQtitle.length)
        {
            return false;
        }

        let X = JQsala[0].instance.x(),
            Y = JQsala[0].instance.y(),
            primeiroCirculoEixoX = $(`circle.circle-${JQtitleId.replace('title-', '')}[side='1']`).attr('cx'),
            primeiroCirculoEixoY = $(`circle.circle-${JQtitleId.replace('title-', '')}[side='1']`).attr('cy');
            
            
        if(!primeiroCirculoEixoX)
        {
            return false
        }

        let posFrom = {
                x: parseInt(primeiroCirculoEixoX),
                y: parseInt(primeiroCirculoEixoY)
            },
            posTo = {
                x: parseInt(JQtitle.css('left').replace('px', '')) + parseInt((JQtitle.width() / 2)),
                y: parseInt(JQtitle.css('top').replace('px', '')) + (75 * parseInt((JQtitle.height())) / 100)
            };

        let currentPathLine = $(`path#${id}`);

        // Verificando se linha ja foi criada
        if(currentPathLine.length)
        {
            return currentPathLine.removeAttr('hidden');
        }

        // Criando nova instancia de path para a linha
        let cor = $(`path#${id.replace('line-title-', '')}`).attr('cor'),
            pathLine = draw.path(`M${posFrom.x} ${posFrom.y} L${posTo.x} ${posTo.y}`).attr({
            id: id,
            stroke: `rgba(${cor})`
        });
    }

    /**
    * Mostra uma linha partindo da sala ate seu respectivo titulo
    */
    function showLine(id = false)
    {
        if(id)
        {
            return $(`path#${id}`).attr('hidden', false);
        }

        let JQallTitles = $('.title-path.is-room');

        JQallTitles.each((key, element) => {
            let JQelement = $(element);

                // idSala = element.id.replace('title-', ''),
                // JQsala = $(`path#${idSala}`),
                // X = JQsala[0].instance.x(),
                // Y = JQsala[0].instance.y(),
                // posFrom = {
                //     x: X + ((20 * JQsala[0].instance.width()) / 100),
                //     y: Y + ((20 * JQsala[0].instance.height()) / 100)
                // },
                // posTo = {
                //     x: parseInt(JQelement.css('left').replace('px', '')) + parseInt((JQelement.width() / 2)),
                //     y: parseInt(JQelement.css('top').replace('px', '')) + (75 * parseInt((JQelement.height())) / 100)
                // };

            if(!$(element).hasClass('hidden'))
            {
                createLine(`line-${JQelement.attr('id')}`);
            }
        });
    }

    function hideLine(id = false, not = false)
    {
        if(id)
        {
            if(not)
            {
                return $(`path[id*=line]:not(#${id})`).attr('hidden', true);
            }

            return $(`path#${id}`).attr('hidden', true);
        }

        $(`path[id*=line]:not(.locked)`).attr('hidden', true);
    }

    function removeLine(id = false)
    {
        if(id)
        {
            return $(`path#${id}`).remove();
        }

        $('path[id*=line]').remove();
    }

    /**
    * Mover direcao de uma linha
    */
    function moveLine(id, posX, posY)
    {
        let JQline = $(`path#${id}`),
            JQtitle = $(`div#${id.replace('line-', '')}`);

        if(!JQline.length)
        {
            return false;
        }

        let coordenates = JQline.attr('d').split(' ');

        let X = parseInt(posX.toFixed()),
            Y = parseInt(posY.toFixed());

        X += (10 * JQtitle.width()) / 100;
        Y +=  JQtitle.height() / 2;

        let newCoordenates = `${coordenates[0]} ${coordenates[1]} L${X} ${Y}`;

        JQline.attr('d', newCoordenates);
    }

    // Criar indicador de pressao da sala na DOM
    function createIndPressao(titleId)
    {
        let JQelement = $(`path#${idSala}`);

        if(!posX && !posY)
        {
            if(JQelement.length)
            {
                posX = JQelement[0].instance.x();
                posY = JQelement[0].instance.y() - 10;
            }
        }

        posX += ((JQelement[0].instance.width() * 80) / 100 );

        let divIndicador = `<div id="indicador-${idSala}" class="indicador-pressao" style="left: ${posX}px; top: ${posY}px;" title="Indicador de pressao">
                                <h1 style="max-width: ${maxWidth}px;font-size: ${fontSize}rem;">${title}</h1>
                                <div>
                                    <i class="fa fa-plus"></i>
                                </div>
                            </div>`;

        JQsvgArea.append(divIndicador);
    }

    /**
    * Zoom da DOM
    */
    var svgZoom = resetarValoresDeZoom();

    function eventosDeZoomDosTitulos()
    {
        var intervaloDeTempo = null;

        svgZoom.on('zoom', function(e) {
            var JQTituloAtivo = $('.title-path:has( > h1.locked)');

            if(!JQTituloAtivo.length) return false;

            var JQH1 = JQTituloAtivo.find('h1'),
                id = JQTituloAtivo.attr('id').split('-')[1],
                JQlinhaDoTitulo = $(`#line-title-${id}`);

            JQlinhaDoTitulo.attr('hidden', true);

            zoomAtual = parseFloat(parseFloat(e.getTransform().scale).toFixed(2));
            tamanhoAtualDasFontesDosTitulos = (zoomInicial * tamanhoDaFontePadrao) / zoomAtual;       
            JQH1.css('fontSize', `${tamanhoAtualDasFontesDosTitulos}rem`);

            if (intervaloDeTempo) clearTimeout(intervaloDeTempo);
            intervaloDeTempo = setTimeout(function()
            {
                let x = JQTituloAtivo.get(0).offsetLeft + (JQTituloAtivo.width() / 2),
                    y = JQTituloAtivo.get(0).offsetTop;

                if(JQTituloAtivo.hasClass('is-room'))
                {
                    moveLine(`line-title-${id}`, x, y);
                }

                JQlinhaDoTitulo.removeAttr('hidden');
            }, 500);
        });
    }

    function resizeElementsInZoom()
    {
        let lastScroll = 0;     
        /**
        * Diminuir circles conforme o zoom
        * para facilitar a o posicionamento
        */
        let JQcircles = $('circle');

        let JQcircle = $(JQcircles.get(0)),
            originalR = parseFloat(parseFloat(JQcircle.attr('r')).toFixed(2)),
            newR = 0;

        /**
        * Valor para diminuir mais os elementos
        */
        let valorExtra = 0.5;

        /**
        * Movimentacao da salas
        */
        if(posicaoX != false)
        {
            posicaoX = false;
        }

        if(posicaoY != false)
        {
            posicaoY = false;
        }
        

        svgZoom.on('zoom', function(e) {
            let positionY = e.getTransform().y,
                zoomIn = true,
                currentR = parseFloat(parseFloat(JQcircle.attr('r')).toFixed(2));

            zoomAtual = parseFloat(parseFloat(e.getTransform().scale).toFixed(2));

            if(positionY > lastScroll)
            {
                zoomIn = false;   
            }

            lastScroll = positionY;

            // Zoomin
            if(zoomIn)
            {
                lastZoom = parseFloat(zoomAtual.toFixed(2));
                // console.log("ZOOM IN");
                // console.log(`ZOOM ATUAL: ${zoomAtual}`);
                newR = parseFloat((currentR - ((originalR * (zoomAtual - 1)) / 100)).toFixed(2));

                JQcircles.attr('r', newR);
                return;
            }

            // Zoom out
            // console.log('ZOOM OUT');
            // console.log(`ULTIMO ZOOM: ${lastZoom}`);
            newR = parseFloat((currentR + ((originalR * (lastZoom - zoomAtual)) / 100)).toFixed(2));

            JQcircles.attr('r', newR);
        });
    }

    // svgZoom.on('zoomend', function(e) {
    //     console.log('Zoom maximo');
    // //     console.log(lastZoom);
    //     zoomAtual = e.getTransform().scale;

    //      /**
    //      * Aumentar circles conforme o zoom
    //      * para facilitar a o posicionamento
    //      */
    //     let JQcircles = $('circle');

    //     JQcircles.each((index, element) => {
    //         let JQcircle = $(element);
    //             currentR = JQcircle.attr('r');

    //         JQcircle.attr('r', currentR + ((currentR * (lastZoom - 1)) / 100));
    //     });

    //     /**
    //      * Movimentacao da salas
    //      */
    //     if(posicaoX != false)
    //     {
    //         posicaoX = false;
    //     }

    //     if(posicaoY != false)
    //     {
    //         posicaoY = false;
    //     }
    // });



    svgZoom.on('transform', function(e) {
        /**
        * Movimentacao da salas
        */
        if(posicaoX != false)
        {
            posicaoX = false;
        }

        if(posicaoY != false)
        {
            posicaoY = false;
        }
    });
    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
