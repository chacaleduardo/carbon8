<script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
<script type="text/javascript">
    const idFormaPagamentoCB = '<?= $_1_u_conciliacaofinanceira_idformapagamento; ?>',
        idContapagarCB = '<?= $_1_u_conciliacaofinanceira_idcontapagar; ?>',
        idEmpresaCB = '<?= $_1_u_conciliacaofinanceira_idempresa; ?>',
        idConciliacaoFinanceira = '<?= $_1_u_conciliacaofinanceira_idconciliacaofinanceira; ?>',
        idPessoaLogada = '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>',
        lancamentoArquivo = <?= json_encode($lancamentoArquivo) ?>,
        permissaoEditar = <?= $permissaoEditar ? 'true' : 'false'  ?>,
        permissaoVisualizar = <?= $permissaoVisualizar ? 'true' : 'false' ?>,
        idFluxoStatusAberto = '<?= $idFluxoStatusAberto ?>',
        statusConciliacao = '<?= $_1_u_conciliacaofinanceira_status; ?>',
        statusAberto = 'INICIO';

    // Dados lancamento
    let dadosFatura = {};
    let dadosGerais = Array.isArray(<?= json_encode($lancamentos) ?>) ? {} : <?= json_encode($lancamentos) ?>;
    let lancamentosRemovidos = [];

    if (idEmpresaCB) buscarCartoesPorIdEmpresa(idEmpresaCB, statusConciliacao == 'CONCILIADO');
    if (idFormaPagamentoCB) buscarFaturasPorIdFormapagamento(idFormaPagamentoCB, statusConciliacao == 'CONCILIADO');

    let timeout = setTimeout(() => {
        if (typeof XLSX == 'undefined') clearInterval(timeout);
        else if (!Object.keys(dadosGerais).length && lancamentoArquivo && permissaoEditar) lerArquivoSalvo(lancamentoArquivo.caminho);
    }, 400);

    if (Object.keys(dadosGerais).length) renderizarLancamentos(false, true);

    // Buscar Cartoes de credito por empresaa
    $('#input-empresa').on('change', function() {
        buscarCartoesPorIdEmpresa(this.value);
    });

    // Buscar fatura aberta por forma de pagamento
    $('#cartao-credito').on('change', function() {
        buscarFaturasPorIdFormapagamento(this.value);
    });

    $('#legenda').on('click', function() {
        const corpo = `<div class="row">
                            <div class="col-xs-12 bg-white">
                                <h2>Crédito</h2>
                                <div class="d-flex flex-col">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="mr-3 border-black bg-0" style="width: 46px; height: 29px"></div>
                                        <h4 class="m-0">Não possuí valor equivalente em nosso sistema.</h4>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="mr-3 border-black bg-50" style="width: 46px; height: 29px"></div>
                                        <h4 class="m-0">Possuem mesmo valor, porém com datas diferentes.</h4>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="mr-3 border-black bg-75" style="width: 46px; height: 29px"></div>
                                        <h4 class="m-0">Possuem mesmo valor e data, porém mais de um valor encontrado</h4>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="mr-3 border-black bg-100" style="width: 46px; height: 29px"></div>
                                        <h4 class="m-0">Data  e valor dos lançamentos foram únicos no período.</h4>
                                    </div>
                                </div>
                            </div>
                        </div>`;


        CB.modal({
            titulo: '<h1 class="font-bold mb-3">Legenda Conciliação</h1>',
            corpo
        });
    });

    // Buscar extrato cartao de credito
    if (
        permissaoEditar &&
        statusConciliacao != 'CONCILIADO' &&
        (
            (statusConciliacao == 'INICIO' || statusConciliacao == 'EMCONCILIACAO' || statusConciliacao == 'APROVACAOSOLICITADA') ||
            (CB.modulo != 'conciliacaofinanceiracartoes')
        )
    ) {
        $('#btn-pesquisar').on('click', function() {
            const idEmpresa = $('#input-empresa').val(),
                idFormaPagamento = $('#cartao-credito').val(),
                idContaPagar = $('#input-nfe').val()

            if (idEmpresa && idFormaPagamento && idContaPagar) {
                $.ajax({
                    method: 'GET',
                    url: '/../../ajax/contapagar.php',
                    dataType: 'json',
                    data: {
                        action: 'buscarExtratoAppPorContapagarIdFormaPagamentoEEmpresa',
                        params: {
                            typeParam: 'array',
                            param: [idContaPagar, idFormaPagamento, idEmpresa]
                        }
                    },
                    success: res => {
                        if (!res || !res.length) return alertAtencao('Nenhum registro encontrado!');
                        dadosGerais = buscarLancamentosFatura();

                        if (!Object.keys(dadosGerais).length) return alertAtencao('Erro ao ler arquivo da fatura!');

                        ultimoIndiceComMatch = verificarMatch(res, 'dtemissao', false, true, true, false, false);

                        let datasOrdenadas = ordenarObjetoPorData(dadosGerais);

                        for (let data in datasOrdenadas) {
                            let lancamentos = ordenarPorTotal(dadosGerais[datasOrdenadas[data]]);

                            if (lancamentos)
                                verificarMatch(lancamentos, 'dataEmissaoFatura', true, false, true, false, false);
                        }

                        salvarLancamentosInput();
                        salvarLancamentos(true);
                    },
                    err: res => {
                        console.log(res);
                    }
                });
            }
        });

        // Ler arquivo de extrato
        $("#input-extrato").dropzone({
            url: "form/_arquivo.php",
            idObjeto: idConciliacaoFinanceira,
            tipoObjeto: 'conciliacaofinanceira',
            tipoArquivo: 'LANCAMENTO',
            idPessoaLogada,
            acceptedFiles: ".xls,.csv,.xlsx,.xlsb,.xlsm,.xltx,.xltm,.xlam,.ods, .txt",
            sending: function(file, xhr, formData) {
                formData.append("idobjeto", this.options.idObjeto);
                formData.append("tipoobjeto", this.options.tipoObjeto);
                formData.append("tipoarquivo", this.options.tipoArquivo);
            },
            success: function(file, response) {
                this.options.loopArquivos(response);
            },
            init: function() {
                var thisDropzone = this;
                $.ajax({
                    url: this.options.url + "?tipoobjeto=" + this.options.tipoObjeto + "&idobjeto=" + this.options.idObjeto + "&tipoarquivo=" + this.options.tipoArquivo
                }).done(function(data, textStatus, jqXHR) {
                    thisDropzone.options.loopArquivos(data);
                })
            },
            loopArquivos: function(data) {
                jResp = jsonStr2Object(data);
                if (jResp.length > 0) {
                    nomeArquivo = jResp[jResp.length - 1].caminho;
                    if (nomeArquivo) {
                        lerArquivoSalvo(nomeArquivo);
                    }
                }
            }
        })

        // Remover arquivo de extrato
        $('#remover-arquivo-extrato').on('click', function() {
            // Remover lancamentos do lancamento
            $.ajax({
                method: 'GET',
                url: '/../../ajax/conciliacaofinanceira.php',
                dataType: 'json',
                data: {
                    action: 'removerLancamentosPorIdConciliacaoFinanceira',
                    params: idConciliacaoFinanceira
                },
                success: res => {
                    if (res.error) return alertAtencao(res.mensagem ?? 'Ocorreu um erro ao tentar remover lançamentos');

                    CB.post({
                        objetos: {
                            _1_d_arquivo_idarquivo: lancamentoArquivo['idarquivo'],
                            parcial: true
                        },
                    });
                },
                err: res => {
                    console.log(res);

                    desabilitarCampos();
                }
            })
        })

        // Selecionar lancamentos da fatura para conciliação
        $('#itens-lancamento').on('click', '.check-fatura', function() {
            const itemCheck = $(this);
            const itensSistemaSelecionados = $('.check-sistema:checked');
            const itensFaturaSelecionados = $('.check-fatura:checked');
            const qtdItensSelecionadosSistema = itensSistemaSelecionados.length;
            const qtdItensSelecionadosFatura = itensFaturaSelecionados.length;

            let valorTotalFatura = 0;

            // Somando valor
            itensFaturaSelecionados.each(function() {
                let valorFatura = parseFloat($(this).data('totalfatura'));
                if (!isNaN(valorFatura)) {
                    valorTotalFatura += valorFatura;
                }
            });

            if (!qtdItensSelecionadosFatura) {
                desmarcarLancamentosSistema();
                desabilitarBtnEnviarMensagemEmMassa();
                $('#btn-enviar-mensagem-em-massa')
                    .find('.badge')
                    .removeClass('opacity-100');
            } else {
                $('#btn-enviar-mensagem-em-massa').find('.badge')
                    .addClass('opacity-100')
                    .text(qtdItensSelecionadosSistema + qtdItensSelecionadosFatura);
            }

            habilitarBtnEnviarMensagemEmMassa();

            $('#itens-selecionados-fatura').data('itensselecionadosfatura', parseInt(qtdItensSelecionadosFatura)).text(qtdItensSelecionadosFatura);
            $('#valor-total-fatura').data('valortotalfatura', parseFloat(valorTotalFatura.toFixed(2))).text(formatarValorBRL(valorTotalFatura));

            if (qtdItensSelecionadosFatura == 1 && qtdItensSelecionadosSistema > 1) {
                $('.check-fatura:checked').parent().parent().addClass('lancamento-pai-fatura');
            } else {
                $('.lancamento-pai-fatura').removeClass('lancamento-pai-fatura');
            }

            if (qtdItensSelecionadosSistema == 1 && qtdItensSelecionadosFatura > 1) {
                $('.check-sistema:checked').parent().parent().addClass('lancamento-pai-sistema');
            } else {
                $('.lancamento-pai-sistema').removeClass('lancamento-pai-sistema');
            }

            liberarBtnConciliar();
        });

        // Selecionar lancamentos do sistema para conciliação
        $('#itens-lancamento').on('click', '.check-sistema', function() {
            const itemCheck = $(this);
            const itensSistemaSelecionados = $('.check-sistema:checked');
            const itensFaturaSelecionados = $('.check-fatura:checked');
            const qtdItensSelecionadosSistema = itensSistemaSelecionados.length;
            const qtdItensSelecionadosFatura = itensFaturaSelecionados.length;

            let valorTotalSistema = 0;

            // Somando valor
            itensSistemaSelecionados.each(function() {
                let valorSistema = parseFloat($(this).data('totalsistema'));
                if (!isNaN(valorSistema)) {
                    valorTotalSistema += valorSistema;
                }
            });

            if (!qtdItensSelecionadosSistema) {
                desmarcarLancamentosFatura();
                desabilitarBtnEnviarMensagemEmMassa();

                $('#btn-enviar-mensagem-em-massa')
                    .find('.badge')
                    .removeClass('opacity-100');
            } else {
                $('#btn-enviar-mensagem-em-massa').find('.badge')
                    .addClass('opacity-100')
                    .text(qtdItensSelecionadosSistema + qtdItensSelecionadosFatura);
            }

            habilitarBtnEnviarMensagemEmMassa();

            $('#itens-selecionados-sistema').data('itensselecionadossistema', parseInt(qtdItensSelecionadosSistema)).text(qtdItensSelecionadosSistema);
            $('#valor-total-sistema').data('valortotalsistema', parseFloat(valorTotalSistema.toFixed(2))).text(formatarValorBRL(valorTotalSistema));

            if (qtdItensSelecionadosSistema == 1 && qtdItensSelecionadosFatura > 1) {
                $('.check-sistema:checked').parent().parent().addClass('lancamento-pai-sistema');
            } else {
                $('.lancamento-pai-sistema').removeClass('lancamento-pai-sistema');
            }

            if (qtdItensSelecionadosFatura == 1 && qtdItensSelecionadosSistema > 1) {
                $('.check-fatura:checked').parent().parent().addClass('lancamento-pai-fatura');
            } else {
                $('.lancamento-pai-fatura').removeClass('lancamento-pai-fatura');
            }

            liberarBtnConciliar();
        });

        // Conciliar
        $('#btn-conciliar').on('click', conciliar);

        $(document).on('keydown', function(event) {
            // Verifica se Shift e C estão pressionados simultaneamente
            if (!$('#btn-conciliar').attr('disabled') && (event.shiftKey && event.key === 'C')) {
                conciliar();
            }
        });

        // Aprovar
        $('#itens-lancamento').on('click', '.btn-aprovar', function() {
            if (!confirm('Uma vez que o lançamento for aprovado, não será possível reverter essa ação. Tenha certeza de que deseja prosseguir, pois esta operação não poderá ser desfeita.')) return false;

            const btnAprovarHTML = $(this).get(0),
                lancamentoHTML = $(this).parent().parent(),
                idLancamento = lancamentoHTML.data('id');

            let lancamentoAtualizado = false;

            mostrarStatus(lancamentoHTML);

            if (idLancamento) {
                lancamentoAtualizado = alterarObjetoPorId(dadosGerais, idLancamento, {
                    match: true,
                    status: 'APROVADO',
                    porcentagem: 100,
                    idGrupo: null
                });

                atualizarPorcentagemLancamento(lancamentoAtualizado, lancamentoAtualizado.porcentagem);
            }

            if (btnAprovarHTML)
                btnAprovarHTML.classList.add('hide');
        })

        // Aprovar todos os lancamentos com match
        $('#btn-conciliar-todos').on('click', aprovarTodos);

        // Desagrupar
        // Ao desagrupar irá apenas passar o status para 75% ( não afetando a posição do lançamento )
        $('#itens-lancamento').on('click', '.btn-desagrupar', function() {
            const btnDesagruparHTML = this,
                lancamentoAtualHTML = $(this).parent().parent(),
                idLancamento = lancamentoAtualHTML.data('id');

            // Retonar elementos para posicao de origem
            if (idLancamento) {
                const lancamentoAtual = Object.assign({}, buscarLancamentoPorId(idLancamento));

                if (Object.keys(lancamentoAtual).length) {
                    // Verificar se o lancamento possui filhos
                    let lancamentosFilhos = {
                        ...lancamentoAtual['filhos']
                    };

                    if (lancamentosFilhos && Object.keys(lancamentosFilhos).length) {
                        for (let indice in lancamentosFilhos) {
                            let lancamento = lancamentosFilhos[indice];
                            // Remover vinculo com lancamento pai
                            lancamento['idPai'] = null;

                            // atualizando posicao dos lancamentos no array pela data de emissao
                            removerLancamentos(lancamento.id, false, true);
                        }

                        // Remover definicao de lancamento pai
                        let lancamentoFaturaAtualizado = alterarObjetoPorId(dadosGerais, lancamentoAtual.id, {
                            pai: false,
                            porcentagem: 0,
                            match: false,
                            filhos: {}
                        });

                        if (lancamentoFaturaAtualizado !== false)
                            atualizarLinhaLancamentoHTML(lancamentoAtualHTML, lancamentoFaturaAtualizado)

                        // Adicionando lancamentos que estavam agrupados novamente nos dados
                        if (Object.keys(lancamentosFilhos)) {
                            for (let indice in lancamentosFilhos) {
                                let lancamentoFilho = lancamentosFilhos[indice];

                                let lancamento = {
                                    ...lancamentoFilho,
                                    id: gerarId(),
                                    dataEmissaoFatura: '',
                                    porcentagem: 0,
                                    match: false
                                };

                                if (!lancamentoFilho['dataEmissaoSistema']) {
                                    lancamento = {
                                        ...lancamentoFilho,
                                        id: gerarId(),
                                        dataEmissaoSistema: '',
                                        porcentagem: 0,
                                        match: false
                                    };

                                    adicionarLancamento(lancamento, lancamento['dataEmissaoFatura']);
                                } else
                                    adicionarLancamento(lancamento, lancamento['dataEmissaoSistema']);
                            }

                            renderizarLancamentos(false, false, false);
                        }
                    } else {
                        // atualizarPorcentagemLancamento(lancamentoAtual, 75);
                        alterarObjetoPorId(dadosGerais, idLancamento, {
                            porcentagem: 75,
                            match: false,
                            status: 'PENDENTE',
                            idGrupo: gerarId()
                        }, false);


                        if (dadosGerais) {
                            // Retorna o lancamento para sua data
                            let datasOrdenadas = ordenarObjetoPorData(dadosGerais);

                            for (let data in datasOrdenadas) {
                                let lancamentos = dadosGerais[datasOrdenadas[data]];

                                if (lancamentos)
                                    verificarMatch(lancamentos, 'dataEmissaoFatura', true, false, true, lancamentoAtual['id']);

                                for (let indice in lancamentos) {
                                    let itemLancamento = lancamentos[indice];
                                    atualizarPorcentagemLancamento(itemLancamento, itemLancamento.porcentagem);
                                }
                            }
                        }
                    }
                }

                if (btnDesagruparHTML) btnDesagruparHTML.classList.add('hide');

                ocultarStatus(lancamentoAtualHTML);
            }
        });

        // Enviar mensagens em massa
        $('#btn-enviar-mensagem-em-massa').on('click', function() {
            const lancamentosSelecionadosDOM = buscarLancamentosMarcadosDOM();
            const btnJQ = $(this);

            if (!lancamentosSelecionadosDOM.length) {
                desabilitarBtnEnviarMensagemEmMassa();

                return alertAtencao("Nenhum lançamento selecionado");
            }

            habilitarBtnEnviarMensagemEmMassa();

            const lancamentosId = lancamentosSelecionadosDOM.map((index, item) => $(item).data('id')).get().join(',');
            // const lancamentosSelecionados = buscarLancamentoPorId(lancamentosId);

            abrirModalMensagem(lancamentosId);
        });
    }

    function buscarFaturasPorIdFormapagamento(idFormaPagamento, desabilitar = false) {
        debugger
        if (!idFormaPagamento) {
            desabilitarCampos();
            return;
        };

        const idempresa = idEmpresaCB ? idEmpresaCB : gIdEmpresa;

        $.ajax({
            method: 'GET',
            url: '/../../ajax/contapagar.php',
            dataType: 'json',
            data: {
                action: 'buscarFaturasPorIdFormapagamento',
                params: {
                    typeParam: 'array',
                    param: [idFormaPagamento, idempresa, idContapagarCB]
                }
            },
            success: res => {
                if (!res) alertAtencao('Nenhuma fatura aberta encontrada para o cartão selecionado!');
                if (res.error) alertAtencao(res.error);

                const optionsNFe = montarOptionsNfe(res);

                $('#input-nfe').html(optionsNFe);
                if (desabilitar)
                    desabilitarCampos()
                else
                    habilitarCampos();

                $('#input-nfe').selectpicker('refresh');
            },
            err: res => {
                console.log(res);

                desabilitarCampos();
            }
        })
    }

    function desabilitarBtnEnviarMensagemEmMassa() {
        $('#btn-enviar-mensagem-em-massa').attr('disabled', true);
    }

    function habilitarBtnEnviarMensagemEmMassa() {
        $('#btn-enviar-mensagem-em-massa').removeAttr('disabled');
    }

    function buscarLancamentosMarcadosDOM() {
        return $('.lancamento-item:has(input[type="checkbox"]:checked)');
    }

    function conciliar() {
        // Buscar lancamento fatura marcado ( buscar lancamento pai marcado )
        const lancamentoFaturaHTML = $($('.check-fatura:checked').parent().parent());

        if (!lancamentoFaturaHTML.length) return alertAtencao('Lançamento da fatura não selecionado');

        const idLancamentoFatura = lancamentoFaturaHTML.data('id');
        const lancamentoFatura = buscarLancamentoPorId(idLancamentoFatura);;
        const dataEmissaoFatura = lancamentoFatura['dataEmissaoFatura'];
        let dataLancamentoDivergenteSistema = '';
        let lancamentoRenderizados = false;
        let renderizarLancamentosNovamente = false;

        // Buscar laçamentos do sistema marcados
        const checkLancamentosSistema = $('.check-sistema:checked');
        const checkLancamentosFatura = $('.check-fatura:checked');

        // Apenas trocar valores de linha para conciliar
        if (checkLancamentosSistema.length === 1 && checkLancamentosFatura.length === 1) {
            // Remover lançamento ligado a fatura ( este será mesclado com o ligado a fatura )
            const lancamentoSistemaHTML = $(checkLancamentosSistema.parent().parent());
            const idLancamentoSistema = lancamentoSistemaHTML.data('id');
            let lancamentoSistemaAtualizado = false;

            // Lancamento marcado do sistema
            const lancamentoSistema = buscarLancamentoPorId(idLancamentoSistema);

            // A linha da fatura é sempre usada como base, então o match será definido apenas nela
            if (lancamentoSistema === false)
                return alertAtencao('Lançamento da fatura não encontrado!');

            if (lancamentoFatura['dataEmissaoFatura'] && lancamentoFatura['dataEmissaoFatura'] != lancamentoSistema['dataEmissaoSistema']) {
                dataLancamentoDivergenteSistema = lancamentoSistema['dataEmissaoSistema'];
            }

            const valoresNovoLancamentoSistema = {
                idnf: lancamentoSistema['idnf'] ?? null,
                dataEmissaoSistema: lancamentoSistema['dataEmissaoSistema'],
                idcontapagar: lancamentoSistema['idcontapagar'],
                idcontapagaritem: lancamentoSistema['idcontapagaritem'],
                descricaoSistema: lancamentoSistema['descricaoSistema'],
                totalSistema: lancamentoSistema['totalSistema'],
                indiceOrigem: lancamentoSistema['indiceOrigem'],
                match: true,
                porcentagem: 100,
                idGrupo: null
            }

            // Caso o item marcado da fatura tenha algum match
            if (
                lancamentoFatura['dataEmissaoSistema'] &&
                lancamentoFatura['descricaoSistema'] &&
                parseFloat(lancamentoFatura['totalSistema'])
            ) {
                const valoresNovoLancamentoFatura = {
                    dataEmissaoSistema: lancamentoFatura['dataEmissaoSistema'],
                    descricaoSistema: lancamentoFatura['descricaoSistema'],
                    totalSistema: lancamentoFatura['totalSistema'],
                    indiceOrigem: lancamentoFatura['indiceOrigem']
                }

                lancamentoSistemaAtualizado = alterarObjetoPorId(dadosGerais, idLancamentoSistema, valoresNovoLancamentoFatura, false);
            } else {
                if (idLancamentoSistema)
                    removerLancamentos(idLancamentoSistema);
                else
                    alertAtencao('Id do lancamento a ser conciliado não encontrado!');
            }

            // Atualizar dados no objeto de lancamentos
            alterarObjetoPorId(dadosGerais, idLancamentoFatura, valoresNovoLancamentoSistema, false);

            if ((lancamentoSistemaAtualizado &&
                    (!lancamentoSistemaAtualizado['dataEmissaoFatura'] && !lancamentoSistemaAtualizado['descricaoFatura'] && !parseFloat(lancamentoSistemaAtualizado['totalFatura']))) &&
                dataLancamentoDivergenteSistema) {
                removerLancamentos(lancamentoSistemaAtualizado['id']);
                adicionarLancamento({
                    ...lancamentoSistemaAtualizado,
                    id: gerarId()
                }, lancamentoFatura['dataEmissaoSistema']);

                renderizarLancamentosNovamente = true;
            }

            // Verificar se resta apenas um lançamento com 75% (com a mesma data e valor) para já dar match automatico
            const itensSemMatch = dadosGerais[dataEmissaoFatura].filter(item => {
                return item.porcentagem == 75 && !item.match && (item.dataEmissaoFatura && item.descricaoFatura && parseFloat(item.totalFatura));
            });

            let lancamentosPorDataFatura = buscarLancamentoPorDataEmissaoEIndice(lancamentoFatura['dataEmissaoFatura']);
            let lancamentosPorDataSistema = false;

            if (lancamentoSistema['dataEmissaoSistema'] && lancamentoFatura['dataEmissaoFatura'] != lancamentoSistema['dataEmissaoSistema']) {
                lancamentosPorDataSistema = buscarLancamentoPorDataEmissaoEIndice(lancamentoSistema['dataEmissaoSistema']);
            }

            if (lancamentosPorDataFatura) {
                lancamentoRenderizados = verificarMatch(lancamentosPorDataFatura, 'dataEmissaoFatura', true, false, true);

                if (renderizarLancamentosNovamente) {
                    renderizarLancamentos(false, false, false);
                    lancamentoRenderizados = true;
                }

                if (!lancamentoRenderizados)
                    for (let indice in lancamentosPorDataFatura) {
                        let itemLancamento = lancamentosPorDataFatura[indice];
                        atualizarPorcentagemLancamento(itemLancamento, itemLancamento.porcentagem);
                    }
            }

            if (lancamentosPorDataSistema) {
                verificarMatch(lancamentosPorDataSistema, 'dataEmissaoFatura', true, false, true);

                for (let indice in lancamentosPorDataSistema) {
                    let itemLancamento = lancamentosPorDataSistema[indice];
                    atualizarPorcentagemLancamento(itemLancamento, itemLancamento.porcentagem);
                }
            }

            let itensSemMatchEpar = dadosGerais[dataEmissaoFatura].filter(item => {
                return item.porcentagem == 75 && !item.match && (!item.dataEmissaoFatura && !item.descricaoFatura && !parseFloat(item.totalFatura));
            });

            const itensSemMatchEparDeOutraData = false;

            if (!itensSemMatch.length && itensSemMatchEpar.length) {
                itensSemMatchEpar.forEach(item => {
                    let ultimoItemParaMatchSemPar = item,
                        ultimoItemParaMatchSemParHTML = $(`[data-id="${ultimoItemParaMatchSemPar['id']}"`)

                    if (ultimoItemParaMatchSemParHTML) {
                        alterarObjetoPorId(dadosGerais, ultimoItemParaMatchSemPar['id'], {
                            match: false,
                            porcentagem: 0
                        }, false);

                        atualizarPorcentagemLancamento(ultimoItemParaMatchSemPar, 0);
                    } else alertAtencao('Ultimo elemento para match não encontrado!');
                })
            }
        } else {
            // No agrupamento será adicionado a linha logo abaixo do lancamento pai selecionada
            // Só pode ser vinculado lançamento da fatura em lançamentos do sistema que estão divergentes

            // Pegar lançamento da fatura selecionado
            const lancamentosPaiHTML = $(`.lancamento-pai-fatura, .lancamento-pai-sistema`);

            if (!lancamentosPaiHTML.length) return alert('Lançamento não selecionado');
            if (lancamentosPaiHTML.length > 1) return alert('Múltiplos lançamentos selecionados na fatura e sistema!');

            let classeCheckLancamento = 'sistema';
            let atributoDataLancamento = 'dataEmissaoFatura'

            if (lancamentosPaiHTML.hasClass('lancamento-pai-sistema')) {
                classeCheckLancamento = 'fatura';
                atributoDataLancamento = 'dataEmissaoSistema'
            }

            const lancamentoPaiDados = buscarLancamentoPorId(lancamentosPaiHTML.data('id'));

            if (lancamentoPaiDados) {
                // Pegar lançamentos do sistema selecionados
                // const lancamentosSistemaHTML = $(`.lancamento-item:has( .check-${classeCheckLancamento}:checked)`);

                let lancamentosFilhoDados = buscarLancamentoPorId($(`.lancamento-item:has( .check-${classeCheckLancamento}:checked)`).map((indice, element) => $(element).data('id')).get());

                if (lancamentosFilhoDados) {
                    removerLancamentos(lancamentosFilhoDados.filter(item => parseInt(item.porcentagem) === 0).map(item => item.id));

                    lancamentosFilhoDados.forEach((item, indice) => {
                        // Alterando lançamento para agrupar na nos novos lançamentos
                        let novoItemLancamento = {
                            ...item,
                            id: gerarId(),
                            idPai: lancamentoPaiDados.id,
                            descricaoFatura: '',
                            dataEmissaoFatura: '',
                            totalFatura: 0.0,
                            status: 'PENDENTE'
                        }

                        if (classeCheckLancamento == 'fatura') {
                            novoItemLancamento = {
                                ...item,
                                id: gerarId(),
                                idPai: lancamentoPaiDados.id,
                                descricaoSistema: '',
                                dataEmissaoSistema: '',
                                totalSistema: 0.0,
                                status: 'PENDENTE'
                            }
                        }

                        lancamentosFilhoDados[indice] = novoItemLancamento;
                    });

                    alterarObjetoPorId(dadosGerais, lancamentoPaiDados['id'], {
                        pai: true,
                        filhos: lancamentosFilhoDados
                    }, false);

                    atualizarStatusLancamento(lancamentoPaiDados[atributoDataLancamento]);
                    renderizarLancamentos(false, false, false);
                }

            } else {
                alertAtencao('Lançamento da fatura não selecionado!');
            }

            lancamentosPaiHTML
                .removeClass('lancamento-pai-fatura')
                .removeClass('lancamento-pai-sistema');
        }

        desmarcarLancamentosFatura();
    }

    function aprovarTodos() {
        if (!confirm('Uma vez que o lançamento for aprovado, não será possível reverter essa ação. Tenha certeza de que deseja prosseguir, pois esta operação não poderá ser desfeita.')) return false;

        const lancamentosComMatch = buscarlancamentosComMatch();

        if (!lancamentosComMatch.length)
            return alertAtencao('Nenhum lançamento pendente para aprovação');

        lancamentosComMatch.forEach(lancamento => {
            let lancamentoHTML = $(`[data-id="${lancamento['id']}"]`);

            if (lancamentoHTML) {
                let btnAprovarHTML = lancamentoHTML.find('.btn-aprovar').get(0);
                let lancamentoAtualizado = false;

                mostrarStatus(lancamentoHTML);

                lancamentoAtualizado = alterarObjetoPorId(dadosGerais, lancamento['id'], {
                    match: true,
                    status: 'APROVADO',
                    porcentagem: 100,
                    idGrupo: null
                }, true, true);

                atualizarPorcentagemLancamento(lancamentoAtualizado, lancamentoAtualizado.porcentagem);

                if (btnAprovarHTML)
                    btnAprovarHTML.classList.add('hide');
            }
        });

        const lancamentosNaoAprovados = buscarlancamentosNaoAprovados();

        // Atualizando status da conciliacao para conciliado
        if (!lancamentosNaoAprovados.length) {
            $('#input-status').val('CONCILIADO');
            $('#conciliacao-status').text('CONCILIADO');
        }

        salvarLancamentos();
    }

    function buscarlancamentosComMatch() {
        let lancamentosMatch = [];

        for (let data in dadosGerais) {
            let lancamentosPorData = dadosGerais[data];

            lancamentosMatch = lancamentosMatch.concat(lancamentosPorData.filter(item => item.match && item.status != 'APROVADO'));
            lancamentosPorData.forEach(item => {
                if (item.filhos && Object.keys(item.filhos).length)
                    for (let indice in item['filhos']) {
                        // Filhos nao precisam validacao de math, pois nao é possivel agrupar lancamentos divergentes
                        if (item['filhos'][indice]['status'] != 'APROVADO') lancamentosMatch.push(item['filhos'][indice]);
                    }
            })
        }

        return lancamentosMatch;
    }

    function buscarlancamentosNaoAprovados() {
        let lancamentosNaoAprovados = [];

        for (let data in dadosGerais) {
            let lancamentosPorData = dadosGerais[data];

            lancamentosNaoAprovados = lancamentosNaoAprovados.concat(lancamentosPorData.filter(item => item.status != 'APROVADO'));
            lancamentosPorData.forEach(item => {
                if (item.filhos && Object.keys(item.filhos).length)
                    for (let indice in item['filhos']) {
                        if (item['filhos'][indice]['status'] != 'APROVADO') lancamentosNaoAprovados.push(item['filhos'][indice]);
                    }
            })
        }

        return lancamentosNaoAprovados;
    }

    function verificarMatch(
        lancamentosParam,
        chavePrincipal = 'dtemissao',
        ignoraItensMatch = false,
        atualizarDadosLancamento = true,
        atualizarPorcentagem = true,
        idLancamentoDesconciliado = false,
        atualizarPorcentagemDOM = true,
        mesclagem = false
    ) {
        let lancamentos = lancamentosParam;
        let indiceMatchLancamento = false;
        let ultimoIndiceComMatch = false;
        let ultimoIndice = {};
        let indicesEncontrados = {};
        let chaveValorComparacao = '';
        let valorComparacao = '';
        let chaveValor = '';
        let itemLancamentoMatch = false;
        let dataEmissaoComparacao = '';
        let comparativoComOutrosLancamentos = false;
        let lancamentosDoBanco = chavePrincipal == 'dtemissao';
        let renderizarLancamento = false;
        let indiceParaObjetoOrdenado = 0;
        let lancamentoOrdenado = {};
        let ultimaDataComMatch = '';
        let dataLancamentosInvalidos = '';

        if (!lancamentos) {
            lancamentos = buscarLancamentosSistema();
        }

        if (!lancamentosDoBanco) {
            for (let chave in lancamentos) {
                if (verificarSeLancamentoEInvalido(lancamentos[chave])) {
                    removerLancamentos(lancamentos[chave]['id']);
                    delete lancamentos[chave];
                }
            }

            for (let chave in lancamentos) {
                lancamentoOrdenado[indiceParaObjetoOrdenado] = lancamentos[chave];
                indiceParaObjetoOrdenado++;
            };

            if (Object.keys(lancamentoOrdenado).length) lancamentos = lancamentoOrdenado;
        }

        for (let chave in lancamentos) {
            let lancamentoRemovido = false,
                dataMatchDiferente = false;

            let lancamento = lancamentos[chave];
            comparativoComOutrosLancamentos = false;

            if (lancamentosDoBanco) {
                lancamento = {
                    ...lancamento,
                    dataEmissaoSistema: lancamento['dtemissao'],
                    descricaoSistema: lancamento['descricao'],
                    totalSistema: lancamento['total']
                }
            }

            dataEmissaoComparacao = lancamento[chavePrincipal];

            // A chaveValorComparacao sera a propriedade do objeto que vai pegar o valor que sera comparado com o valor do objeto com a propriedade chaveValor 
            chaveValorComparacao = 'totalSistema';
            chaveValor = 'totalFatura';

            if (!dataEmissaoComparacao && chaveValorComparacao != chaveValor) {
                dataEmissaoComparacao = lancamento[(chavePrincipal == 'dataEmissaoFatura' ? 'dataEmissaoSistema' : 'dataEmissaoFatura')];
                comparativoComOutrosLancamentos = true;
            }

            if (lancamentosDoBanco)
                dataEmissaoComparacao = formatarData(dataEmissaoComparacao);

            if (indiceMatchLancamento === -1 ||
                ultimaDataComMatch !== dataEmissaoComparacao)
                indiceMatchLancamento = false;

            // Separando indice encontrado por data
            if (!ultimoIndice.hasOwnProperty(dataEmissaoComparacao))
                ultimoIndice[dataEmissaoComparacao] = -1;

            // Buscar lancamentos com o mesmo valor na mesma data
            if (!lancamento['dataEmissaoSistema'] && !lancamento['descricaoSistema'] && !parseFloat(lancamento['totalSistema'])) {
                chaveValorComparacao = 'totalFatura';
                chaveValor = 'totalSistema';
            }

            valorComparacao = parseFloat(lancamento[chaveValorComparacao]);

            indiceMatchLancamento = dadosGerais[dataEmissaoComparacao] ? buscarIndicePorId(dadosGerais[dataEmissaoComparacao], chaveValor, valorComparacao, indiceMatchLancamento, lancamento['match'], (lancamentosDoBanco || mesclagem)) : -1;

            if (indiceMatchLancamento !== -1) {
                if (ultimaDataComMatch != dataEmissaoComparacao) dataMatchDiferente = true;

                ultimaDataComMatch = dataEmissaoComparacao;
            }

            if (
                indiceMatchLancamento != ultimoIndiceComMatch ||
                (dataMatchDiferente && indiceMatchLancamento !== false)
            ) {
                ultimoIndiceComMatch = indiceMatchLancamento;
                if (indiceMatchLancamento !== -1) {
                    if (!indicesEncontrados[dataEmissaoComparacao]) {
                        indicesEncontrados[dataEmissaoComparacao] = []
                    }

                    indicesEncontrados[dataEmissaoComparacao].push(indiceMatchLancamento)
                };
            }

            if (indiceMatchLancamento === -1) {
                do {
                    ultimoIndice[dataEmissaoComparacao]++;

                } while (indicesEncontrados[dataEmissaoComparacao] && indicesEncontrados[dataEmissaoComparacao].includes(ultimoIndice[dataEmissaoComparacao]));
            }

            let indiceArr = indiceMatchLancamento !== -1 ? indiceMatchLancamento : (ultimoIndice[dataEmissaoComparacao] === -1 ? 0 : ultimoIndice[dataEmissaoComparacao]);


            if (!dadosGerais[dataEmissaoComparacao])
                dadosGerais[dataEmissaoComparacao] = [];


            if ((!dadosGerais[dataEmissaoComparacao][indiceArr]) ||
                (
                    dadosGerais[dataEmissaoComparacao].length &&
                    dadosGerais[dataEmissaoComparacao][indiceArr] &&
                    dadosGerais[dataEmissaoComparacao][indiceArr]['totalFatura'] != lancamento['totalSistema'] &&
                    lancamentosDoBanco
                )) {

                // Adiciona lancamentos que perderam seu parceiro
                if (lancamentosDoBanco || !dadosGerais[dataEmissaoComparacao][indiceArr]) {
                    let novoLancamento = {
                        ...lancamento,
                        id: gerarId(),
                        idcontapagar: lancamento['idcontapagar'] ?? null,
                        descricaoFatura: '',
                        dataEmissaoFatura: '',
                        totalFatura: 0,
                        descricaoSistema: lancamento['descricaoSistema'],
                        dataEmissaoSistema: dataEmissaoComparacao,
                        totalSistema: parseFloat(lancamento['totalSistema']),
                        porcentagem: 0
                    };

                    dadosGerais[dataEmissaoComparacao].push(novoLancamento);
                    verificarMatch(dadosGerais[dataEmissaoComparacao], 'dataEmissaoSistema', true, false, true, false, atualizarPorcentagemDOM);

                    if (lancamentosDoBanco) {
                        removerLancamentos(lancamento['id']);
                    } else {
                        renderizarLancamento = true;
                    }
                } else {
                    // Verificar se existe lancamento com o mesmo valor em outra data
                    if (dadosGerais[dataEmissaoComparacao][indiceArr]['totalFatura'] != lancamento['totalSistema'])
                        dadosGerais[dataEmissaoComparacao][indiceArr]['porcentagem'] = verificaPorcentagem(dadosGerais[dataEmissaoComparacao][indiceArr], false, idLancamentoDesconciliado, atualizarPorcentagemDOM);
                }

                if (dadosGerais[dataEmissaoComparacao][indiceArr]['porcentagem'] == 0)
                    dadosGerais[dataEmissaoComparacao][indiceArr]['idGrupo'] = null;

            } else {
                if (lancamento['status'] != 'APROVADO' && (valorComparacao && atualizarDadosLancamento) && indiceMatchLancamento != -1 && !lancamento['match']) {
                    let lancamentoAnteriorSistema = {},
                        lancamentoAnteriorFatura = {};

                    const lancamentoId = lancamento['id'] ?? dadosGerais[dataEmissaoComparacao][indiceArr]['id'];

                    // Caso o lancamento com match possua valor guarda lo para trocar de posição com o outro selecionado
                    if (
                        (
                            lancamento['descricaoSistema'] &&
                            lancamento['dataEmissaoSistema'] &&
                            parseFloat(lancamento['totalSistema'])
                        ) &&
                        (
                            !lancamento['descricaoFatura'] &&
                            !lancamento['dataEmissaoFatura'] &&
                            !parseFloat(lancamento['totalFatura'])
                        )
                    ) {
                        if (dadosGerais[dataEmissaoComparacao][indiceArr]['descricaoSistema'] &&
                            dadosGerais[dataEmissaoComparacao][indiceArr]['dataEmissaoSistema'] &&
                            parseFloat(dadosGerais[dataEmissaoComparacao][indiceArr]['totalSistema'])
                        )
                            lancamentoAnteriorSistema = {
                                ...dadosGerais[dataEmissaoComparacao][indiceArr],
                                id: lancamentoId
                            };

                        // Mesclando lancamento atual com o que deu match
                        dadosGerais[dataEmissaoComparacao][indiceArr] = {
                            ...dadosGerais[dataEmissaoComparacao][indiceArr],
                            idcontapagar: (lancamento['idcontapagar'] ? lancamento['idcontapagar'] : dadosGerais[dataEmissaoComparacao][indiceArr]['idcontapagar']) ?? null,
                            idcontapagaritem: (lancamento['idcontapagaritem'] ? lancamento['idcontapagaritem'] : dadosGerais[dataEmissaoComparacao][indiceArr]['idcontapagaritem']) ?? null,
                            descricaoSistema: lancamento['descricaoSistema'],
                            dataEmissaoSistema: dataEmissaoComparacao,
                            totalSistema: parseFloat(lancamento['totalSistema']),
                            match: true
                        };
                        if (!lancamentosDoBanco) {
                            if (!Object.keys(lancamentoAnteriorSistema).length) {
                                alterarObjetoPorId(dadosGerais, lancamentoId, {
                                    descricaoSistema: '',
                                    dataEmissaoSistema: '',
                                    totalSistema: 0.0,
                                    porcentagem: 0
                                }, false);

                                dataLancamentosInvalidos = dataEmissaoComparacao;
                            } else {
                                alterarObjetoPorId(dadosGerais, lancamentoId, {
                                    descricaoFatura: '',
                                    dataEmissaoFatura: '',
                                    totalFatura: 0.0,
                                    descricaoSistema: lancamentoAnteriorSistema['descricaoSistema'],
                                    dataEmissaoSistema: lancamentoAnteriorSistema['dataEmissaoSistema'],
                                    totalSistema: lancamentoAnteriorSistema['totalSistema']
                                }, false);
                            }
                        }

                        dadosGerais[dataEmissaoComparacao][indiceArr]['porcentagem'] = verificaPorcentagem(dadosGerais[dataEmissaoComparacao][indiceArr], false, false, false);

                    } else if (
                        (
                            lancamento['descricaoFatura'] &&
                            lancamento['dataEmissaoFatura'] &&
                            parseFloat(lancamento['totalFatura'])
                        ) &&
                        (
                            !lancamento['descricaoSistema'] &&
                            !lancamento['dataEmissaoSistema'] &&
                            !parseFloat(lancamento['totalSistema'])
                        )
                    ) {
                        if (dadosGerais[dataEmissaoComparacao][indiceArr]['descricaoFatura'] &&
                            dadosGerais[dataEmissaoComparacao][indiceArr]['dataEmissaoFatura'] &&
                            parseFloat(dadosGerais[dataEmissaoComparacao][indiceArr]['totalFatura'])
                        )
                            lancamentoAnteriorFatura = {
                                ...dadosGerais[dataEmissaoComparacao][indiceArr],
                                id: lancamentoId
                            };

                        // Mesclando lancamento atual com o que deu match
                        dadosGerais[dataEmissaoComparacao][indiceArr] = {
                            ...dadosGerais[dataEmissaoComparacao][indiceArr],
                            idcontapagar: (lancamento['idcontapagar'] ? lancamento['idcontapagar'] : dadosGerais[dataEmissaoComparacao][indiceArr]['idcontapagar']) ?? null,
                            idcontapagaritem: (lancamento['idcontapagaritem'] ? lancamento['idcontapagaritem'] : dadosGerais[dataEmissaoComparacao][indiceArr]['idcontapagaritem']) ?? null,
                            descricaoFatura: lancamento['descricaoFatura'],
                            dataEmissaoFatura: dataEmissaoComparacao,
                            totalFatura: parseFloat(lancamento['totalFatura']),
                            match: true
                        };

                        if (!lancamentosDoBanco) {
                            if (!Object.keys(lancamentoAnteriorFatura).length) {
                                alterarObjetoPorId(dadosGerais, lancamentoId, {
                                    descricaoFatura: '',
                                    dataEmissaoFatura: '',
                                    totalFatura: 0.0,
                                    porcentagem: 0
                                }, false);

                                dataLancamentosInvalidos = dataEmissaoComparacao;
                            } else {
                                alterarObjetoPorId(dadosGerais, lancamentoId, {
                                    descricaoFatura: lancamentoAnteriorFatura['descricaoFatura'],
                                    dataEmissaoFatura: lancamentoAnteriorFatura['dataEmissaoFatura'],
                                    totalFatura: lancamentoAnteriorFatura['totalFatura'],
                                    descricaoSistema: '',
                                    dataEmissaoSistema: '',
                                    totalSistema: 0.0,
                                }, false);
                            }
                        }

                        dadosGerais[dataEmissaoComparacao][indiceArr]['porcentagem'] = verificaPorcentagem(dadosGerais[dataEmissaoComparacao][indiceArr], false, false, false);
                    }
                }

                if (!dadosGerais[dataEmissaoComparacao][indiceArr]['id']) {
                    dadosGerais[dataEmissaoComparacao][indiceArr]['id'] = gerarId();
                }

                itemLancamentoMatch = ultimoIndiceComMatch !== -1 ? dadosGerais[dataEmissaoComparacao][indiceArr] : false;

                if (!lancamentosDoBanco && lancamentosParam && !mesclagem)
                    indiceArr = chave;

                if (dadosGerais[dataEmissaoComparacao][indiceArr]['porcentagem'] == undefined)
                    dadosGerais[dataEmissaoComparacao][indiceArr]['porcentagem'] = 0;

                if (
                    lancamento['status'] != 'APROVADO' &&
                    verificarSeLancamentoAtualiza(dadosGerais[dataEmissaoComparacao][indiceArr], atualizarPorcentagem, ignoraItensMatch)
                )
                    dadosGerais[dataEmissaoComparacao][indiceArr]['porcentagem'] = verificaPorcentagem(dadosGerais[dataEmissaoComparacao][indiceArr], itemLancamentoMatch, idLancamentoDesconciliado, atualizarPorcentagemDOM);

                if (indiceMatchLancamento !== -1 && !dadosGerais[dataEmissaoComparacao][indiceArr]['idGrupo'] && dadosGerais[dataEmissaoComparacao][indiceArr]['porcentagem'] != 100)
                    dadosGerais[dataEmissaoComparacao][indiceArr]['idGrupo'] = gerarId();

                if (dadosGerais[dataEmissaoComparacao][indiceArr]['porcentagem'] == 0)
                    dadosGerais[dataEmissaoComparacao][indiceArr]['idGrupo'] = null;

            }
        }

        if (renderizarLancamento)
            renderizarLancamentos(false, false, false);

        if (dataLancamentosInvalidos) {
            let lancamentos = dadosGerais[dataLancamentosInvalidos];

            for (let chave in lancamentos) {
                if (verificarSeLancamentoEInvalido(lancamentos[chave])) removerLancamentos(lancamentos[chave]['id']);
            }
        }

        return renderizarLancamento;
    }

    function verificarSeLancamentoAtualiza(lancamento, atualizarPorcentagem, ignoraItensMatch) {
        return (
            atualizarPorcentagem &&
            (
                (ignoraItensMatch && lancamento['porcentagem'] != 100) ||
                (
                    ignoraItensMatch &&
                    lancamento['porcentagem'] == 100 && !lancamento['match']
                ) || !ignoraItensMatch
            )
        );
    }

    function verificarSeLancamentoEInvalido(lancamento) {
        return (
            (!lancamento['dataEmissaoFatura'] && !lancamento['descricaoFatura'] && !parseFloat(lancamento['totalFatura'])) &&
            (!lancamento['dataEmissaoSistema'] && !lancamento['descricaoSistema'] && !parseFloat(lancamento['totalSistema']))
        )
    }

    function buscarCartoesPorIdEmpresa(idEmpresa, desabilitar = false) {
        const formaPagamento = 'C.CREDITO';

        if (!idEmpresa) {
            desabilitarCampos();
            return;
        };

        $.ajax({
            method: 'GET',
            url: '/../../ajax/formapagamento.php',
            dataType: 'json',
            data: {
                action: 'buscarFormaPagamentoAtivaPorIdEmpresaEFormaPagamento',
                params: {
                    typeParam: 'array',
                    param: [idEmpresa, formaPagamento]
                }
            },
            success: res => {
                if (!res) alertAtencao('Nenhuma forma de pagamento configurada para empresa selecionada!');

                const optionsFormaPagamento = montarOptions(res);

                $('#cartao-credito').html(optionsFormaPagamento);
                if (desabilitar)
                    desabilitarCampos()
                else
                    habilitarCampos();

                $('#cartao-credito').selectpicker('refresh');
            },
            err: res => {
                console.log(res);

                desabilitarCampos();
            }
        })
    }

    function buscarLancamentosSistema() {
        let lancamentosSistema = [];

        for (let data in dadosGerais) {
            let lancamentos = dadosGerais[data];

            if (lancamentos.length)
                lancamentosSistema = lancamentosSistema.concat([...lancamentos.filter(item => item.dataEmissaoSistema)]);
        }

        return lancamentosSistema;
    }

    function renderizarLancamentos(ultimoIndiceComMatch = false, atualizarPorcentagem = false, atualizarDadosLancamento = true, ignoraMatch = true) {
        $('#itens-lancamento').html('');

        const datasOrdenadas = ordenarObjetoPorData(dadosGerais);
        let totalLancamentosFatura = 0,
            totalLancamentosSistema = 0;

        for (let data in datasOrdenadas) {
            let dataLancamento = dadosGerais[datasOrdenadas[data]];
            dataLancamento = {
                ...ordenarPorTotal(dataLancamento)
            };

            // Verificar match por data
            verificarMatch(dataLancamento, 'dataEmissaoFatura', ignoraMatch, atualizarDadosLancamento, atualizarPorcentagem, false, false, true);

            let lancamentosPorDataAtualizado = dadosGerais[datasOrdenadas[data]];

            for (let chave in lancamentosPorDataAtualizado) {
                const itemLancamento = lancamentosPorDataAtualizado[chave];

                dadosGerais[datasOrdenadas[data]][chave]['indiceOrigem'] = chave;

                // Renderizando elementos antes de atualizar a porcentagem, pois, é preciso para calcular a porcentagem dos elementos filhos ( agrupados )
                let itemHTML = montarItemLancamento(itemLancamento);

                if (itemHTML) {
                    $('#itens-lancamento').append(itemHTML);
                    atualizarPorcentagemLancamento(itemLancamento, itemLancamento.porcentagem);
                }

                if (!isNaN(parseFloat(itemLancamento['totalFatura'])))
                    totalLancamentosFatura += parseFloat(itemLancamento['totalFatura']);

                if (!isNaN(parseFloat(itemLancamento['totalSistema'])))
                    totalLancamentosSistema += parseFloat(itemLancamento['totalSistema']);

                if (itemLancamento['filhos'] && Object.keys(itemLancamento['filhos']).length) {
                    for (let indice in itemLancamento['filhos']) {
                        let itemLancamentoFilho = itemLancamento['filhos'][indice]
                        let itemHTML = montarItemLancamento(itemLancamentoFilho);

                        if (itemHTML) {
                            $('#itens-lancamento').append(itemHTML);
                            atualizarPorcentagemLancamento(itemLancamentoFilho, itemLancamentoFilho.porcentagem);
                        }

                        if (!isNaN(parseFloat(itemLancamentoFilho['totalFatura'])))
                            totalLancamentosFatura += parseFloat(itemLancamentoFilho['totalFatura']);

                        if (!isNaN(parseFloat(itemLancamentoFilho['totalSistema'])))
                            totalLancamentosSistema += parseFloat(itemLancamentoFilho['totalSistema']);
                    }
                }

                liberarAcoesMensagem(itemLancamento);
            }
        }

        atualizarTotalLancamentosFaturaHTML(totalLancamentosFatura);
        atualizarTotalLancamentosSistemaHTML(totalLancamentosSistema)
    }

    function atualizarTotalLancamentosFaturaHTML(total) {
        $('#total-lancamentos-fatura')
            .text(formatarValorBRL(parseFloat(total) ?? 0))
            .data('total', parseFloat(total.toFixed(2)));

        verificarDivergenciaTotalLancamentos();
    }

    function atualizarTotalLancamentosSistemaHTML(total) {
        $('#total-lancamentos-sistema')
            .text(formatarValorBRL(total ?? 0))
            .data('total', parseFloat(total.toFixed(2)));

        verificarDivergenciaTotalLancamentos();
    }

    function verificarDivergenciaTotalLancamentos() {
        let totalLancamentosFatura = parseFloat($('#total-lancamentos-fatura').data('total'));
        totalLancamentosSistema = parseFloat($('#total-lancamentos-sistema').data('total'));

        if (parseFloat(totalLancamentosFatura) === parseFloat(totalLancamentosSistema))
            $('#total-lancamentos-fatura, #total-lancamentos-sistema').removeClass('divergente').addClass('igual');
        else
            $('#total-lancamentos-fatura, #total-lancamentos-sistema').removeClass('igual').addClass('divergente');
    }

    function verificarStatusDosLancamentos(ultimoIndiceComMatch = false) {
        const datasOrdenadas = ordenarObjetoPorData(dadosGerais);
        let porcentagem = 0;

        for (let data in datasOrdenadas) {
            let dataLancamento = dadosGerais[datasOrdenadas[data]];
            dataLancamento = ordenarPorTotal(dataLancamento);

            for (let chave in dataLancamento) {
                const itemLancamento = dataLancamento[chave];
                const itemLancamentoMatch = ultimoIndiceComMatch ? dataLancamento[ultimoIndiceComMatch] : false;

                if (itemLancamento.idPai || itemLancamento.match) continue;

                porcentagem = verificaPorcentagem(itemLancamento, itemLancamentoMatch);

                atualizarPorcentagemLancamento(itemLancamento, porcentagem);

                if (itemLancamento === undefined)
                    alterarObjetoPorId(dadosGerais, itemLancamento['id'], {
                        porcentagem: porcentagem
                    });
            }
        }
    }

    function ocultarStatus(lancamentoHTML) {
        const statusIcon = lancamentoHTML.find('.status').get(0);
        if (statusIcon)
            statusIcon
            .classList.add('hide');
    }

    function mostrarStatus(lancamentoHTML) {
        const statusIcon = lancamentoHTML.find('.status').get(0);
        if (statusIcon)
            statusIcon
            .classList.remove('hide');
    }

    function verificaPorcentagem(itemLancamento, itemLancamentoMatch = false, idLancamentoDesconciliado = false, atualizarStatus = true) {
        let porcentagem = 100;
        const lancamentosFilhos = {
            ...itemLancamento['filhos']
        };

        if (lancamentosFilhos && Object.keys(lancamentosFilhos).length) {
            let somaLancamentos = 0.0;
            let match = false;
            let ladoComparacao = !itemLancamento['dataEmissaoFatura'] && !itemLancamento['descricaoFatura'] && !parseFloat(itemLancamento['totalFatura']) ? 'fatura' : 'sistema';
            let valorComparacao = ladoComparacao == 'sistema' ? itemLancamento['totalFatura'] : itemLancamento['totalSistema'];

            for (let chave in lancamentosFilhos) {
                let lancamentoAtual = lancamentosFilhos[chave];

                if (ladoComparacao == 'sistema')
                    somaLancamentos += parseFloat(lancamentoAtual.totalSistema);
                else
                    somaLancamentos += parseFloat(lancamentoAtual.totalFatura);
            }

            if (somaLancamentos.toFixed(2) == parseFloat(valorComparacao)) {
                match = true;
            } else porcentagem = 0;

            let objetoAtualizacaoLancamentoPai = {
                match,
                porcentagem,
                pai: match
            }

            if (!match) objetoAtualizacaoLancamentoPai['filhos'] = {};

            // Atualizando match pai
            alterarObjetoPorId(dadosGerais, itemLancamento.id, objetoAtualizacaoLancamentoPai, atualizarStatus);

            // Atualizando match dos filhos
            for (let indice in lancamentosFilhos) {
                let lancamentoFilho = lancamentosFilhos[indice];
                let objetoAtualizacao = {
                    match,
                    porcentagem
                };

                if (!match) {
                    objetoAtualizacao['idPai'] = null;
                    removerLancamentos(lancamentoFilho.id, false, true);
                };

                alterarObjetoPorId(dadosGerais, lancamentoFilho.id, objetoAtualizacao, atualizarStatus, true);
            }

            if (!match) {
                // Adicionando lancamentos que estavam agrupados novamente nos dados
                for (let indice in lancamentosFilhos) {
                    let lancamentoFilho = lancamentosFilhos[indice];

                    let lancamento = {
                        ...lancamentoFilho,
                        id: gerarId(),
                        dataEmissaoFatura: '',
                        porcentagem: 0,
                        match: false,
                        idPai: null
                    };

                    if (!lancamentoFilho['dataEmissaoSistema']) {
                        lancamento = {
                            ...lancamentoFilho,
                            id: gerarId(),
                            dataEmissaoSistema: '',
                            porcentagem: 0,
                            match: false,
                            idPai: null
                        };

                        adicionarLancamento(lancamento, lancamento['dataEmissaoFatura']);
                    } else
                        adicionarLancamento(lancamento, lancamento['dataEmissaoSistema']);
                }
            }
        } else {
            if (verificarIgualdadeValoresNaMesmaData(itemLancamento) || verificarMatchDesagrupado(itemLancamento)) {
                porcentagem -= 25;
            } else if (verificarIgualdadeValoresEmDatasDiferentes(itemLancamento)) {
                porcentagem -= 50;
            } else {
                if (itemLancamento['dataEmissaoFatura'] !== itemLancamento['dataEmissaoSistema'])
                    porcentagem -= 50;

                if (parseFloat(itemLancamento['totalFatura']) !== parseFloat(itemLancamento['totalSistema']))
                    porcentagem = 0;
            }
        }

        if (porcentagem == 100) alterarObjetoPorId(dadosGerais, itemLancamento['id'], {
            match: true,
            idGrupo: null
        }, atualizarStatus);

        return porcentagem;
    }

    function verificarIgualdadeValoresEmDatasDiferentes(lancamentoComparacao) {
        // Pegar todos os lancamentos atuais
        for (let data in dadosGerais) {
            let lancamentos = dadosGerais[data];

            // Buscando valores similares em cada data
            for (let indice in lancamentos) {
                let lancamento = lancamentos[indice];

                if (
                    (!lancamentoComparacao['match'] &&
                        parseFloat(lancamentoComparacao['totalFatura']) != parseFloat(lancamentoComparacao['totalSistema'])
                    ) &&
                    (
                        !lancamento['match'] &&
                        (
                            (
                                lancamentoComparacao['dataEmissaoFatura'] != lancamento['dataEmissaoSistema'] &&
                                parseFloat(lancamentoComparacao['totalFatura']) &&
                                parseFloat(lancamentoComparacao['totalFatura']) == parseFloat(lancamento['totalSistema'])
                            ) ||
                            (
                                lancamentoComparacao['dataEmissaoSistema'] != lancamento['dataEmissaoFatura'] &&
                                parseFloat(lancamentoComparacao['totalSistema']) &&
                                parseFloat(lancamentoComparacao['totalSistema']) == parseFloat(lancamento['totalFatura'])
                            )
                        )
                    )
                )
                    return true;
            }
        }

        return false;
    }

    // VERSAO ANTERIOR
    function verificarIgualdadeValores(itemLancamento, itemLancamentoMatch = false) {
        return itemLancamentoMatch &&
            !itemLancamento['match'] &&
            (
                (
                    itemLancamentoMatch['totalFatura'] == itemLancamento['totalFatura'] &&
                    itemLancamentoMatch['totalSistema'] == itemLancamento['totalSistema']
                ) ||
                (
                    (
                        (
                            !parseFloat(itemLancamento['totalFatura']) && !itemLancamento['dataEmissaoFatura'] && !itemLancamento['descricaoFatura']
                        ) && itemLancamentoMatch['totalSistema'] == itemLancamento['totalSistema']
                    ) ||
                    (
                        (
                            !parseFloat(itemLancamento['totalFatura']) && !itemLancamento['dataEmissaoFatura'] && !itemLancamento['descricaoFatura']
                        ) && itemLancamentoMatch['totalFatura'] == itemLancamento['totalSistema']
                    ) ||
                    (
                        (
                            !parseFloat(itemLancamento['totalSistema']) && !itemLancamento['dataEmissaoSistema'] && !itemLancamento['descricaoSistema']
                        ) && itemLancamentoMatch['totalFatura'] == itemLancamento['totalFatura']
                    ) ||
                    (
                        (
                            !parseFloat(itemLancamento['totalSistema']) && !itemLancamento['dataEmissaoSistema'] && !itemLancamento['descricaoSistema']
                        ) && itemLancamentoMatch['totalSistema'] == itemLancamento['totalFatura']
                    )
                )
            ) ||
            (
                !parseFloat(itemLancamento['totalSistema']) && !itemLancamento['dataEmissaoSistema'] && !itemLancamento['descricaoSistema']
            ) && itemLancamentoMatch['totalFatura'] == itemLancamento['totalFatura']
    }

    function verificarIgualdadeValoresNaMesmaData(itemLancamento) {
        let qtdLancamentosIguaisMesmaData = 0;
        let dataComparacao = itemLancamento['dataEmissaoSistema'] ? itemLancamento['dataEmissaoSistema'] : itemLancamento['dataEmissaoFatura'];

        if (dataComparacao) {
            for (let indice in dadosGerais[dataComparacao]) {
                if (qtdLancamentosIguaisMesmaData) return true;

                let lancamento = dadosGerais[dataComparacao][indice];

                if (
                    (lancamento['id'] != itemLancamento['id'] && !lancamento['match']) &&
                    (
                        (parseFloat(itemLancamento['totalSistema']) && parseFloat(itemLancamento['totalSistema']) == parseFloat(lancamento['totalFatura'])) ||
                        (parseFloat(itemLancamento['totalFatura']) && parseFloat(itemLancamento['totalFatura']) == parseFloat(lancamento['totalSistema']))
                        // (parseFloat(itemLancamento['totalSistema']) == parseFloat(lancamento['totalFatura'])) ||
                        // (parseFloat(itemLancamento['totalFatura']) == parseFloat(lancamento['totalSistema']))
                    )
                )
                    qtdLancamentosIguaisMesmaData++;
            }
        }

        return false;
    }

    function verificarMatchDesagrupado(itemLancamento) {
        return (
            !itemLancamento['match'] && itemLancamento['porcentagem'] == 75 &&
            itemLancamento['dataEmissaoFatura'] == itemLancamento['dataEmissaoSistema'] &&
            parseFloat(itemLancamento['totalFatura']) == parseFloat(itemLancamento['totalSistema'])
        )
    }

    /**
     * @param data: atualizar apenas lancamentos de uma data especifica
     */
    function atualizarStatusLancamento(dataEspecifica = false, match = false, idLancamento = false) {
        let porcentagem;

        for (let data in dadosGerais) {
            let lancamentos = dadosGerais[data];

            if (dataEspecifica === false || (data == dataEspecifica)) {
                for (let indice in lancamentos) {
                    let lancamento = lancamentos[indice];

                    if (idLancamento === false || (idLancamento == lancamento['id'])) {
                        porcentagem = 100;

                        if (!lancamento['match']) {
                            porcentagem = verificaPorcentagem(lancamento);
                        } else {
                            if (!lancamento['idGrupo'])
                                lancamento['idGrupo'] = null;

                            porcentagem = lancamento['porcentagem'];
                        }

                        atualizarPorcentagemLancamento(lancamento, porcentagem);
                    }

                    if (idLancamento == lancamento['id']) return;
                }

                if (data == dataEspecifica) break;
            }
        }
    }

    function buscarIndicePorId(array, propriedade, valor, indiceIgnora, lancamentoMatch = false, mesclagem = false) {
        for (let i = 0; i < array.length; i++) {
            // Condicao removida:  && i > indiceIgnora
            if (
                indiceIgnora !== -1 &&
                array[i].hasOwnProperty(propriedade) &&
                (
                    array[i]['porcentagem'] != 100 ||
                    (
                        array[i]['porcentagem'] == 100 && !array[i]['match']
                    )
                ) &&
                array[i][propriedade] == valor &&
                i != parseInt(indiceIgnora) &&
                (
                    (
                        !mesclagem &&
                        !array[i]['match'] &&
                        !lancamentoMatch
                    ) || (indiceIgnora === false || (mesclagem && i > indiceIgnora))
                )
            ) {
                return i; // Retorna o índice do objeto que contém a propriedade X
            }
        }
        return -1; // Retorna -1 se nenhum objeto contiver a propriedade X
    }

    function atualizarPorcentagemLancamento(lancamento, porcentagem = 0) {
        const lancamentoHTML = $(`[data-id="${lancamento['id']}"]`);

        if (!lancamentoHTML.length) return alertAtencao(`Lançamento não ${lancamento['id']} encontrado`);

        // Mostrar / Ocutar acoes
        if (porcentagem == 100) {
            lancamentoHTML.find('input[type="checkbox"]').addClass('hide');
        }

        lancamentoHTML
            .removeClass(`bg-${lancamentoHTML.data('porcentagem')}`)
            .addClass(`bg-${porcentagem}`)
            .data('porcentagem', porcentagem);

        lancamentoHTML
            .find('.porcentagem')
            .text(`${porcentagem}%`);

        if (lancamento['idGrupo']) {
            lancamentoHTML.data('idGrupo', lancamento['idGrupo']);
        }

        if (lancamento['match']) {
            lancamentoHTML.addClass('conciliado');
            lancamentoHTML.find('.check-fatura').add('hide');
            lancamentoHTML.find('.check-sistema').add('hide');
        } else {
            lancamentoHTML.removeClass('conciliado');
            lancamentoHTML.find('.check-fatura').removeClass('hide');
            lancamentoHTML.find('.check-sistema').removeClass('hide');
        }

        atualizarLinhaLancamentoHTML(lancamentoHTML, lancamento);
        liberarAcoesDisponiveisPelaPorcentagem(lancamento, porcentagem);
    }

    function atualizarLinhaLancamentoHTML(lancamentoFaturaHTML, novoLancamentoSistema) {
        if (!novoLancamentoSistema) alertAtencao('Erro ao atualizar lancaçamento.');

        const iconeAprovar = lancamentoFaturaHTML.find('.status').get(0);

        lancamentoFaturaHTML
            .attr('data-dataemissaosistema', novoLancamentoSistema.dataEmissaoSistema ?? '')
            .attr('data-descricaosistema', novoLancamentoSistema.descricaoSistema ?? '')
            .attr('data-totalsistema', novoLancamentoSistema.totalSistema ?? 0.0)
            .attr('data-porcentagem', novoLancamentoSistema.porcentagem ?? 0)
            .attr('data-idgrupo', novoLancamentoSistema.idGrupo ?? '')
            .attr('data-idpai', novoLancamentoSistema.idPai ?? '');

        lancamentoFaturaHTML.find('.check-fatura').data('totalfatura', novoLancamentoSistema.totalFatura ?? 0.0);
        lancamentoFaturaHTML.find('.check-sistema').data('totalsistema', novoLancamentoSistema.totalSistema ?? 0.0);

        if (novoLancamentoSistema.idGrupo)
            lancamentoFaturaHTML.addClass('grupo');
        else
            lancamentoFaturaHTML.removeClass('grupo');

        if (novoLancamentoSistema.pai)
            lancamentoFaturaHTML.addClass('pai');
        else
            lancamentoFaturaHTML.removeClass('pai');

        if (!novoLancamentoSistema.dataEmissaoFatura && !parseFloat(novoLancamentoSistema.totalFatura) && !novoLancamentoSistema.descricaoFatura)
            lancamentoFaturaHTML.find('.fatura').addClass('sem-lancamento');
        else
            lancamentoFaturaHTML.find('.fatura').removeClass('sem-lancamento');

        if (!novoLancamentoSistema.dataEmissaoSistema && !parseFloat(novoLancamentoSistema.totalSistema) && !novoLancamentoSistema.descricaoSistema)
            lancamentoFaturaHTML.find('.sistema').addClass('sem-lancamento');
        else
            lancamentoFaturaHTML.find('.sistema').removeClass('sem-lancamento');

        if (novoLancamentoSistema.status == 'APROVADO')
            iconeAprovar.classList.remove('hide');
        else
            iconeAprovar.classList.add('hide');

        lancamentoFaturaHTML.find('.data-emissao-sistema').text(novoLancamentoSistema.dataEmissaoSistema ?? '');
        lancamentoFaturaHTML.find('.descricao-sistema').text(novoLancamentoSistema.descricaoSistema ?? '');
        lancamentoFaturaHTML.find('.total-sistema').text(formatarValorBRL(novoLancamentoSistema.totalSistema));
    }

    function montarItemLancamento(item, precisaAssociar = false) {
        if (!item) return '';

        let checkFatura = '',
            checkSistema = '',
            semLancamentoFatura = '',
            semLancamentoSistema = '',
            linkFatura = '#',
            onClickMensagem = '',
            mensagem = '',
            iconeMensagemMobile = '',
            onClickLancamentoMobile = '',
            iconeApagarLancamento = '',
            linkArquivoCompra = '#',
            iconeLinkFatura = '';

        if (!item.dataEmissaoFatura && !parseFloat(item.totalFatura) && !item.descricaoFatura)
            semLancamentoFatura = 'sem-lancamento';

        if (!item.dataEmissaoSistema && !parseFloat(item.totalSistema) && !item.descricaoSistema)
            semLancamentoSistema = 'sem-lancamento';

        if (item.idnf) linkFatura = `?_modulo=nfentrada&_acao=u&idnf=${item.idnf}`;
        if (item.anexo) linkArquivoCompra = item.anexo;

        if (permissaoEditar || idEmpresaCB != 2) iconeLinkFatura = ` <a href="${linkFatura}" target="_blank" class="flex">
                                                    <i class="link-fatura hide fa fa-money fa-2x" title="Acessar compra ${item.idnf}"></i>
                                                </a>`

        if (!isNaN(parseInt(item.id))) onClickMensagem = `onClick="abrirModalMensagem(${item.id})"`;

        if (item.mensagem) {
            mensagem = item.mensagem
            if (permissaoVisualizar) {
                iconeMensagemMobile = `<span class="icone-mensagem-mobile hide" onclick="abrirModalMensagemMobile('${mensagem}');">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="none">
                                            <path d="M11 9H9V3H11M11 13H9V11H11M18 0H2C0.9 0 0 0.9 0 2V20L4 16H18C19.1 16 20 15.1 20 14V2C20 0.9 19.1 0 18 0Z" fill="#EFB235"/>
                                        </svg>
                                    </span>`;

                onClickLancamentoMobile = `onclick="abrirLinkCompras(${item.idnf})"`;
            }
        };

        if (item.id.indexOf('id') === -1 && permissaoEditar) {
            iconeApagarLancamento = `<i class="icone-apagar-lancamento hide fa fa-trash-o fa-2x pointer" title="Apagar lançamento" onclick="removerLancamentoDoSistema(${item.id})"></i>`;
        }

        return `
                    <div class="lancamento-item d-flex flex-between font-bold flex-wrap bg-${item.porcentagem ?? 0} ${precisaAssociar ? '' : 'mb-3'} ${item.pai ? 'pai' : ''} ${item.idGrupo ? 'grupo' : ''}" 
                        data-idpai="${item.idPai ?? ''}"
                        data-id="${item.id}" data-dataemissaofatura="${item.dataEmissaoFatura ?? ''}" data-dataemissaosistema="${item.dataEmissaoSistema ?? ''}" data-descricaosistema="${item.descricaoSistema ?? ''}"
                        data-totalsistema="${!isNaN(parseFloat(item.totalSistema)) ? parseFloat(item.totalSistema) : 0}" data-porcentagem="${item.porcentagem ?? 0}" data-idgrupo="${item.idGrupo ?? ''}" data-totalfatura="${!isNaN(parseFloat(item.totalFatura)) ? parseFloat(item.totalFatura) : 0}">
                        <label ${onClickLancamentoMobile} for="check-fatura-${item.id}" class="fatura d-flex flex-between col-xs-12 col-lg-5 align-items-center text-black pointer user-select-none ${semLancamentoFatura}">
                            <!-- Data de emissão -->
                            <span class="col-xs-2 col-lg-4">${item.dataEmissaoFatura ?? ''}</span>
                            <!-- Nome -->
                            <span class="col-xs-5 col-lg-4">${item.descricaoFatura ?? ''}</span>
                            <!-- Valor -->
                            <span class="col-xs-3 col-lg-2 total-fatura">${formatarValorBRL(item.totalFatura ?? 0)}</span>
                            <!-- Porcentagem -->
                            <span class="col-xs-1 col-lg-2 text-center porcentagem">${item.porcentagem ?? 0}%</span>
                        </label>
                        <!-- Check -->
                        <div class="d-flex" style="width: 13px;">
                            <input id="check-fatura-${item.id}" class="check-fatura m-0 hide" type="checkbox" data-totalfatura="${item.totalFatura}" />
                        </div>
                        <!-- Ações -->
                        <div class="acoes d-flex flex-between col-xs-1 justify-content-center align-items-center" style="gap: 1rem;">
                            <!-- Aprovar -->
                            <svg class="hide btn-aprovar" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 13 13" fill="none">
                                <path d="M1.77125 6.40005C1.77113 5.6759 1.96165 4.96449 2.32365 4.33732C2.68565 3.71015 3.20637 3.18933 3.83347 2.82721C4.46057 2.46509 5.17194 2.27443 5.89609 2.2744C6.62023 2.27437 7.33162 2.46498 7.95875 2.82705C8.04483 2.87599 8.14678 2.8889 8.24235 2.86295C8.33791 2.83701 8.41934 2.77432 8.46885 2.68856C8.51836 2.6028 8.53195 2.50094 8.50664 2.4052C8.48133 2.30947 8.41917 2.22763 8.33375 2.17755C7.40439 1.64098 6.32396 1.42606 5.26001 1.56612C4.19606 1.70617 3.20805 2.19337 2.44921 2.95216C1.69037 3.71096 1.20311 4.69893 1.06298 5.76287C0.922862 6.82681 1.13771 7.90726 1.67422 8.83665C2.21072 9.76604 3.0389 10.4924 4.0303 10.9032C5.02171 11.3139 6.12095 11.3861 7.15754 11.1084C8.19412 10.8308 9.11014 10.2189 9.76351 9.36757C10.4169 8.51627 10.7711 7.47318 10.7712 6.40005C10.7712 6.30059 10.7317 6.20521 10.6614 6.13488C10.5911 6.06456 10.4957 6.02505 10.3962 6.02505C10.2968 6.02505 10.2014 6.06456 10.1311 6.13488C10.0608 6.20521 10.0212 6.30059 10.0212 6.40005C10.0212 7.49407 9.58665 8.54328 8.81306 9.31686C8.03947 10.0905 6.99026 10.525 5.89625 10.525C4.80223 10.525 3.75302 10.0905 2.97943 9.31686C2.20584 8.54328 1.77125 7.49407 1.77125 6.40005Z" fill="black" />
                                <path d="M11.4117 2.91563C11.4466 2.88077 11.4742 2.83937 11.4931 2.79382C11.512 2.74826 11.5217 2.69944 11.5217 2.65013C11.5217 2.60082 11.512 2.552 11.4931 2.50644C11.4742 2.46089 11.4466 2.4195 11.4117 2.38463C11.3769 2.34977 11.3355 2.32211 11.2899 2.30324C11.2444 2.28437 11.1955 2.27466 11.1462 2.27466C11.0969 2.27466 11.0481 2.28437 11.0025 2.30324C10.957 2.32211 10.9156 2.34977 10.8807 2.38463L5.89623 7.36988L3.91173 5.38463C3.87686 5.34977 3.83547 5.32211 3.78991 5.30324C3.74436 5.28437 3.69553 5.27466 3.64623 5.27466C3.59692 5.27466 3.54809 5.28437 3.50254 5.30324C3.45698 5.32211 3.41559 5.34977 3.38073 5.38463C3.34586 5.4195 3.3182 5.46089 3.29933 5.50644C3.28046 5.552 3.27075 5.60082 3.27075 5.65013C3.27075 5.69944 3.28046 5.74826 3.29933 5.79382C3.3182 5.83937 3.34586 5.88077 3.38073 5.91563L5.63073 8.16563C5.66556 8.20055 5.70694 8.22826 5.7525 8.24717C5.79806 8.26607 5.8469 8.2758 5.89623 8.2758C5.94555 8.2758 5.99439 8.26607 6.03995 8.24717C6.08551 8.22826 6.12689 8.20055 6.16173 8.16563L11.4117 2.91563Z" fill="black" />
                            </svg>
                            <!-- Desagrupar -->
                            <svg class="hide btn-desagrupar" xmlns="http://www.w3.org/2000/svg" width="16" height="14" viewBox="0 0 13 11" fill="none">
                                <path d="M8.42533 0.635466C8.43981 0.588387 8.44487 0.538918 8.44023 0.489882C8.43559 0.440846 8.42134 0.393205 8.39829 0.349677C8.37524 0.30615 8.34384 0.267589 8.30588 0.236196C8.26793 0.204804 8.22416 0.181194 8.17708 0.166716C8.13 0.152238 8.08053 0.147174 8.0315 0.151815C7.98246 0.156455 7.93482 0.170708 7.89129 0.19376C7.84777 0.216812 7.80921 0.248212 7.77781 0.286167C7.74642 0.324121 7.72281 0.367887 7.70833 0.414966L4.70833 10.165C4.67909 10.26 4.68882 10.3628 4.73538 10.4508C4.78193 10.5387 4.8615 10.6045 4.95658 10.6337C5.05166 10.663 5.15446 10.6532 5.24237 10.6067C5.33028 10.5601 5.39609 10.4805 5.42533 10.3855L8.42533 0.635466ZM4.20733 2.50972C4.24225 2.54455 4.26996 2.58593 4.28887 2.63149C4.30777 2.67705 4.3175 2.72589 4.3175 2.77522C4.3175 2.82454 4.30777 2.87338 4.28887 2.91894C4.26996 2.9645 4.24225 3.00588 4.20733 3.04072L1.84708 5.40022L4.20733 7.75972C4.27775 7.83013 4.31731 7.92563 4.31731 8.02522C4.31731 8.1248 4.27775 8.2203 4.20733 8.29072C4.13692 8.36113 4.04141 8.40069 3.94183 8.40069C3.84225 8.40069 3.74675 8.36113 3.67633 8.29072L1.05133 5.66572C1.01641 5.63088 0.988703 5.5895 0.969798 5.54394C0.950893 5.49838 0.941162 5.44954 0.941162 5.40022C0.941162 5.35089 0.950893 5.30205 0.969798 5.25649C0.988703 5.21093 1.01641 5.16955 1.05133 5.13472L3.67633 2.50972C3.71117 2.47479 3.75255 2.44709 3.79811 2.42818C3.84367 2.40928 3.89251 2.39955 3.94183 2.39955C3.99116 2.39955 4.04 2.40928 4.08556 2.42818C4.13112 2.44709 4.1725 2.47479 4.20733 2.50972ZM8.92633 2.50972C8.89141 2.54455 8.8637 2.58593 8.8448 2.63149C8.82589 2.67705 8.81616 2.72589 8.81616 2.77522C8.81616 2.82454 8.82589 2.87338 8.8448 2.91894C8.8637 2.9645 8.89141 3.00588 8.92633 3.04072L11.2866 5.40022L8.92633 7.75972C8.89147 7.79458 8.86381 7.83597 8.84494 7.88153C8.82607 7.92708 8.81636 7.97591 8.81636 8.02522C8.81636 8.07452 8.82607 8.12335 8.84494 8.1689C8.86381 8.21446 8.89147 8.25585 8.92633 8.29072C8.9612 8.32558 9.00259 8.35324 9.04815 8.37211C9.0937 8.39098 9.14252 8.40069 9.19183 8.40069C9.24114 8.40069 9.28997 8.39098 9.33552 8.37211C9.38107 8.35324 9.42247 8.32558 9.45733 8.29072L12.0823 5.66572C12.1173 5.63088 12.145 5.5895 12.1639 5.54394C12.1828 5.49838 12.1925 5.44954 12.1925 5.40022C12.1925 5.35089 12.1828 5.30205 12.1639 5.25649C12.145 5.21093 12.1173 5.16955 12.0823 5.13472L9.45733 2.50972C9.4225 2.47479 9.38112 2.44709 9.33556 2.42818C9.29 2.40928 9.24116 2.39955 9.19183 2.39955C9.14251 2.39955 9.09367 2.40928 9.04811 2.42818C9.00255 2.44709 8.96117 2.47479 8.92633 2.50972Z" fill="black" />
                            </svg>
                            <!-- Vincular -->
                            <!-- O botão de concilar já desemprenha este papel
                            <svg class="hide btn-vincular" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 13 13" fill="none">
                                <g clip-path="url(#clip0_775_3987)">
                                    <path d="M11.0669 1.15015C11.2658 1.15015 11.4566 1.22916 11.5972 1.36982C11.7379 1.51047 11.8169 1.70123 11.8169 1.90015V10.9001C11.8169 11.0991 11.7379 11.2898 11.5972 11.4305C11.4566 11.5711 11.2658 11.6501 11.0669 11.6501H2.06689C1.86798 11.6501 1.67722 11.5711 1.53656 11.4305C1.39591 11.2898 1.31689 11.0991 1.31689 10.9001V1.90015C1.31689 1.70123 1.39591 1.51047 1.53656 1.36982C1.67722 1.22916 1.86798 1.15015 2.06689 1.15015H11.0669ZM2.06689 0.400146C1.66907 0.400146 1.28754 0.558182 1.00623 0.839486C0.72493 1.12079 0.566895 1.50232 0.566895 1.90015L0.566895 10.9001C0.566895 11.298 0.72493 11.6795 1.00623 11.9608C1.28754 12.2421 1.66907 12.4001 2.06689 12.4001H11.0669C11.4647 12.4001 11.8462 12.2421 12.1276 11.9608C12.4089 11.6795 12.5669 11.298 12.5669 10.9001V1.90015C12.5669 1.50232 12.4089 1.12079 12.1276 0.839486C11.8462 0.558182 11.4647 0.400146 11.0669 0.400146L2.06689 0.400146Z" fill="black"/>
                                    <path d="M5.70733 3.88458C5.74225 3.91942 5.76996 3.9608 5.78887 4.00636C5.80777 4.05192 5.8175 4.10076 5.8175 4.15008C5.8175 4.19941 5.80777 4.24825 5.78887 4.29381C5.76996 4.33937 5.74225 4.38075 5.70733 4.41558L3.72208 6.40008L5.70733 8.38458C5.77775 8.455 5.81731 8.5505 5.81731 8.65008C5.81731 8.74967 5.77775 8.84517 5.70733 8.91558C5.63692 8.986 5.54141 9.02556 5.44183 9.02556C5.34225 9.02556 5.24675 8.986 5.17633 8.91558L2.92633 6.66558C2.89141 6.63075 2.8637 6.58937 2.8448 6.54381C2.82589 6.49825 2.81616 6.44941 2.81616 6.40008C2.81616 6.35076 2.82589 6.30192 2.8448 6.25636C2.8637 6.2108 2.89141 6.16942 2.92633 6.13458L5.17633 3.88458C5.21117 3.84966 5.25255 3.82195 5.29811 3.80305C5.34367 3.78414 5.39251 3.77441 5.44183 3.77441C5.49116 3.77441 5.54 3.78414 5.58556 3.80305C5.63112 3.82195 5.6725 3.84966 5.70733 3.88458ZM7.42633 3.88458C7.39141 3.91942 7.3637 3.9608 7.3448 4.00636C7.32589 4.05192 7.31616 4.10076 7.31616 4.15008C7.31616 4.19941 7.32589 4.24825 7.3448 4.29381C7.3637 4.33937 7.39141 4.38075 7.42633 4.41558L9.41158 6.40008L7.42633 8.38458C7.35592 8.455 7.31636 8.5505 7.31636 8.65008C7.31636 8.74967 7.35592 8.84517 7.42633 8.91558C7.49675 8.986 7.59225 9.02556 7.69183 9.02556C7.79141 9.02556 7.88692 8.986 7.95733 8.91558L10.2073 6.66558C10.2423 6.63075 10.27 6.58937 10.2889 6.54381C10.3078 6.49825 10.3175 6.44941 10.3175 6.40008C10.3175 6.35076 10.3078 6.30192 10.2889 6.25636C10.27 6.2108 10.2423 6.16942 10.2073 6.13458L7.95733 3.88458C7.9225 3.84966 7.88112 3.82195 7.83556 3.80305C7.79 3.78414 7.74116 3.77441 7.69183 3.77441C7.64251 3.77441 7.59367 3.78414 7.54811 3.80305C7.50255 3.82195 7.46117 3.84966 7.42633 3.88458Z" fill="black"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_775_3987">
                                        <rect width="12" height="12" fill="white" transform="translate(0.566895 0.400146)"/>
                                    </clipPath>
                                </defs>
                            </svg> -->
                            <!-- Arquivo anexo na compra -->
                            <a href="${linkArquivoCompra}" target="_blank" class="flex hide anexo-compra">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 6 12" fill="none">
                                    <path d="M5.25 2.89746V8.64746C5.25 9.17789 5.03929 9.6866 4.66421 10.0617C4.28914 10.4367 3.78043 10.6475 3.25 10.6475C2.71957 10.6475 2.21086 10.4367 1.83579 10.0617C1.46071 9.6866 1.25 9.17789 1.25 8.64746V2.39746C1.25 2.06594 1.3817 1.748 1.61612 1.51358C1.85054 1.27916 2.16848 1.14746 2.5 1.14746C2.83152 1.14746 3.14946 1.27916 3.38388 1.51358C3.6183 1.748 3.75 2.06594 3.75 2.39746V7.64746C3.75 7.78007 3.69732 7.90725 3.60355 8.00101C3.50979 8.09478 3.38261 8.14746 3.25 8.14746C3.11739 8.14746 2.99021 8.09478 2.89645 8.00101C2.80268 7.90725 2.75 7.78007 2.75 7.64746V2.89746H2V7.64746C2 7.97898 2.1317 8.29692 2.36612 8.53134C2.60054 8.76577 2.91848 8.89746 3.25 8.89746C3.58152 8.89746 3.89946 8.76577 4.13388 8.53134C4.3683 8.29692 4.5 7.97898 4.5 7.64746V2.39746C4.5 1.86703 4.28929 1.35832 3.91421 0.983247C3.53914 0.608175 3.03043 0.397461 2.5 0.397461C1.96957 0.397461 1.46086 0.608175 1.08579 0.983247C0.710714 1.35832 0.5 1.86703 0.5 2.39746V8.64746C0.5 9.37681 0.789731 10.0763 1.30546 10.592C1.82118 11.1077 2.52065 11.3975 3.25 11.3975C3.97935 11.3975 4.67882 11.1077 5.19454 10.592C5.71027 10.0763 6 9.37681 6 8.64746V2.89746H5.25Z" fill="black"/>
                                </svg>
                            </a>
                            <!-- Icone da fatura -->
                           ${iconeLinkFatura}
                            <!-- Apagar lancamento -->
                            ${iconeApagarLancamento}
                        </div>
                        <!-- Check -->
                        <div class="d-flex" style="width: 13px;">
                            <input id="check-sistema-${item.id}" class="check-sistema m-0 hide" type="checkbox" data-descricaosistema="${item.descricaoSistema}" data-totalsistema="${item.totalSistema}" />
                        </div>
                        <!-- Enviar mensagem -->
                        <div class="d-flex align-items-center" ${onClickMensagem}>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 13" fill="none" width="13px" height="13px" class="pointer btn-enviar-mensagem hide">
                                <g clip-path="url(#clip0_801_5591)">
                                    <path d="M12.1067 0.914745C12.134 0.846597 12.1406 0.771942 12.1259 0.700035C12.1111 0.628128 12.0756 0.562131 12.0237 0.510226C11.9718 0.458321 11.9058 0.422791 11.8339 0.40804C11.762 0.39329 11.6873 0.399967 11.6192 0.427245L0.708934 4.7915C0.612852 4.82996 0.529242 4.89418 0.467308 4.9771C0.405374 5.06001 0.367516 5.15841 0.357902 5.26146C0.348287 5.3645 0.367288 5.4682 0.412814 5.56115C0.45834 5.65409 0.528625 5.73267 0.615934 5.78825L4.36218 8.17175L5.51043 9.97624C5.56467 10.0581 5.64885 10.1155 5.74491 10.1359C5.84096 10.1564 5.94121 10.1384 6.02411 10.0857C6.10702 10.0331 6.16596 9.95001 6.18827 9.85437C6.21058 9.75872 6.19448 9.65814 6.14343 9.57425L5.11143 7.95275L10.7319 2.33225L9.31068 5.88575C9.29116 5.93167 9.28099 5.98103 9.28079 6.03093C9.28058 6.08083 9.29034 6.13027 9.30948 6.17635C9.32863 6.22243 9.35678 6.26423 9.39228 6.2993C9.42779 6.33436 9.46994 6.36199 9.51625 6.38055C9.56257 6.39912 9.61213 6.40826 9.66203 6.40743C9.71192 6.4066 9.76115 6.39582 9.80682 6.37572C9.8525 6.35562 9.8937 6.32661 9.92802 6.29038C9.96234 6.25415 9.98908 6.21144 10.0067 6.16474L12.1067 0.914745ZM10.2017 1.802L4.58118 7.4225L1.32693 5.35175L10.2017 1.802Z" fill="#000000" />
                                    <path d="M9.50879 12.4001C10.205 12.4001 10.8727 12.1236 11.3649 11.6313C11.8572 11.139 12.1338 10.4713 12.1338 9.77515C12.1338 9.07895 11.8572 8.41127 11.3649 7.91899C10.8727 7.42671 10.205 7.15015 9.50879 7.15015C8.8126 7.15015 8.14492 7.42671 7.65263 7.91899C7.16035 8.41127 6.88379 9.07895 6.88379 9.77515C6.88379 10.4713 7.16035 11.139 7.65263 11.6313C8.14492 12.1236 8.8126 12.4001 9.50879 12.4001ZM9.88379 8.65015V9.77515C9.88379 9.8746 9.84428 9.96999 9.77395 10.0403C9.70363 10.1106 9.60825 10.1501 9.50879 10.1501C9.40933 10.1501 9.31395 10.1106 9.24362 10.0403C9.1733 9.96999 9.13379 9.8746 9.13379 9.77515V8.65015C9.13379 8.55069 9.1733 8.45531 9.24362 8.38498C9.31395 8.31466 9.40933 8.27515 9.50879 8.27515C9.60825 8.27515 9.70363 8.31466 9.77395 8.38498C9.84428 8.45531 9.88379 8.55069 9.88379 8.65015ZM9.88379 10.9001C9.88379 10.9996 9.84428 11.095 9.77395 11.1653C9.70363 11.2356 9.60825 11.2751 9.50879 11.2751C9.40933 11.2751 9.31395 11.2356 9.24362 11.1653C9.1733 11.095 9.13379 10.9996 9.13379 10.9001C9.13379 10.8007 9.1733 10.7053 9.24362 10.635C9.31395 10.5647 9.40933 10.5251 9.50879 10.5251C9.60825 10.5251 9.70363 10.5647 9.77395 10.635C9.84428 10.7053 9.88379 10.8007 9.88379 10.9001Z" fill="#000000" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_801_5591">
                                        <rect width="12" height="12" fill="white" transform="translate(0.133789 0.400146)" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                        <div class="d-flex align-items-center" title="${mensagem}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="13px" height="13px" class="pointer btn-info-mensagem hide" fill="#EFB235">
                                <path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"/>
                            </svg>
                        </div>
                        <label ${onClickLancamentoMobile} for="check-sistema-${item.id}" class="sistema d-flex flex-between col-xs-12 col-lg-5 align-items-center text-black pointer user-select-none ${semLancamentoSistema}">
                            <!-- Lancamentos do sistema -->
                            <span class="col-xs-2 col-lg-4 data-emissao-sistema">${item.dataEmissaoSistema ?? ''}</span>
                            <span class="col-xs-5 col-lg-4 descricao-sistema">${item.descricaoSistema ?? ''}</span>
                            <span class="col-xs-3 col-lg-2 total-sistema">${formatarValorBRL(item.totalSistema ?? 0)}</span>
                            <span class="col-xs-1 col-lg-2 d-flex justify-content-center text-center p-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="status w-100 hide" height="12" viewBox="0 0 11 12" fill="none">
                                    <g clip-path="url(#clip0_794_2244)">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.767578 11.625V1.5C0.767578 1.10218 0.925613 0.720644 1.20692 0.43934C1.48822 0.158035 1.86975 0 2.26758 0L8.26758 0C8.6654 0 9.04693 0.158035 9.32824 0.43934C9.60954 0.720644 9.76758 1.10218 9.76758 1.5V11.625C9.76763 11.6901 9.75071 11.7541 9.71851 11.8107C9.6863 11.8673 9.63992 11.9146 9.58391 11.9478C9.5279 11.981 9.46421 11.9991 9.3991 12.0002C9.33399 12.0013 9.26971 11.9855 9.21258 11.9543L5.26758 9.80175L1.32258 11.9543C1.26545 11.9855 1.20117 12.0013 1.13606 12.0002C1.07095 11.9991 1.00725 11.981 0.951247 11.9478C0.895241 11.9146 0.848852 11.8673 0.816647 11.8107C0.784442 11.7541 0.767531 11.6901 0.767578 11.625ZM7.40808 4.3905C7.47849 4.32009 7.51805 4.22458 7.51805 4.125C7.51805 4.02542 7.47849 3.92991 7.40808 3.8595C7.33766 3.78908 7.24216 3.74953 7.14258 3.74953C7.043 3.74953 6.94749 3.78908 6.87708 3.8595L4.89258 5.84475L4.03308 4.9845C3.99821 4.94963 3.95682 4.92198 3.91127 4.90311C3.86571 4.88424 3.81689 4.87453 3.76758 4.87453C3.71827 4.87453 3.66945 4.88424 3.62389 4.90311C3.57834 4.92198 3.53694 4.94963 3.50208 4.9845C3.46721 5.01937 3.43955 5.06076 3.42069 5.10631C3.40182 5.15187 3.3921 5.20069 3.3921 5.25C3.3921 5.29931 3.40182 5.34813 3.42069 5.39369C3.43955 5.43924 3.46721 5.48063 3.50208 5.5155L4.62708 6.6405C4.66191 6.67542 4.70329 6.70313 4.74885 6.72203C4.79441 6.74094 4.84325 6.75067 4.89258 6.75067C4.9419 6.75067 4.99074 6.74094 5.0363 6.72203C5.08186 6.70313 5.12324 6.67542 5.15808 6.6405L7.40808 4.3905Z" fill="white" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_794_2244">
                                            <rect width="10" height="12.0003" fill="white" transform="translate(0.133789)" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </span>
                        </label>
                        ${iconeMensagemMobile}
                    </div>`;
    }

    function removerLancamentoDoSistema(idLancamentoItem) {
        if (!confirm('Deseja realmente remover este lançamento do sistema?')) return false;

        postGenerico({
            _1_d_conciliacaofinanceiraitem_idconciliacaofinanceiraitem: idLancamentoItem
        }, true, true);
    }

    function abrirLinkCompras(idnf = false) {
        let link = `/?_modulo=comprasapp&_acao=u&idnf=${idnf}`;

        if (!idnf)
            link = `/?_modulo=comprasapp&_acao=i`;

        window.open(link, "_blank");

        return false;
    }

    function abrirModalMensagemMobile(mensagem) {
        let titulo = "<h3>Ajuste de lançamento</h3>",
            corpo = montarCorpoModalMensagemMobile(mensagem);

        CB.modal({
            titulo,
            corpo
        });
    }

    /**
     * A versão atual do JQuery não consegue manipular classes em elementos SVG
     */
    function liberarAcoesDisponiveisPelaPorcentagem(lancamento, porcentagem) {
        const lancamentoHTML = $(`.lancamento-item[data-id="${lancamento['id']}"]`);

        if (!lancamentoHTML) return alertAtencao('Lançamento nõa encontrado');

        const elementosAcao = lancamentoHTML
            .find('.btn-vincular, .btn-desagrupar, .btn-aprovar')
            .get()
            .forEach(item => item.classList.add('hide'));

        const btnDesagrupar = lancamentoHTML
            .find('.btn-desagrupar')
            .get(0);

        const btnAprovar = lancamentoHTML
            .find('.btn-aprovar')
            .get(0);

        const checkFatura = lancamentoHTML
            .find('.check-fatura')
            .get(0);

        const checkSistema = lancamentoHTML
            .find('.check-sistema')
            .get(0);

        const linkFatura = lancamentoHTML
            .find('.link-fatura')
            .get(0);

        const anexoCompra = lancamentoHTML
            .find('.anexo-compra')
            .get(0);

        const iconeRemoverLancamento = lancamentoHTML
            .find('.icone-apagar-lancamento')
            .get(0);

        switch (parseInt(porcentagem)) {
            case 100: {
                if (lancamento['status'] != 'APROVADO' && (btnDesagrupar && lancamento['match']))
                    btnDesagrupar.classList.remove('hide');

                if (lancamento['status'] != 'APROVADO' && btnAprovar)
                    btnAprovar.classList.remove('hide');
                break;
            }
            case 75: {
                if (checkFatura && (lancamento['dataEmissaoFatura'] && lancamento['descricaoFatura'] && parseFloat(lancamento['totalFatura'])))
                    checkFatura.classList.remove('hide');

                if (checkSistema && (lancamento['dataEmissaoSistema'] && lancamento['descricaoSistema'] && parseFloat(lancamento['totalSistema'])))
                    checkSistema.classList.remove('hide');

                if (btnAprovar &&
                    ((lancamento['dataEmissaoFatura'] && lancamento['descricaoFatura'] && parseFloat(lancamento['totalFatura'])) && (lancamento['dataEmissaoSistema'] && lancamento['descricaoSistema'] && parseFloat(lancamento['totalSistema']))))
                    btnAprovar.classList.remove('hide');
                break;
            }
            case 50: {
                if (checkFatura)
                    checkFatura.classList.remove('hide');

                if (checkSistema)
                    checkSistema.classList.remove('hide');

                if (btnAprovar &&
                    ((lancamento['dataEmissaoFatura'] && lancamento['descricaoFatura'] && parseFloat(lancamento['totalFatura'])) && (lancamento['dataEmissaoSistema'] && lancamento['descricaoSistema'] && parseFloat(lancamento['totalSistema']))))
                    btnAprovar.classList.remove('hide');
                break;
            }
            case 0: {
                if (checkFatura && (lancamento['dataEmissaoFatura'] && lancamento['descricaoFatura'] && lancamento['totalFatura']))
                    checkFatura.classList.remove('hide');

                if (checkSistema) {
                    if (lancamento['dataEmissaoSistema'] || parseFloat(lancamento['totalSistema']) || lancamento['descricaoSistema'])
                        checkSistema.classList.remove('hide');
                }

                if (
                    iconeRemoverLancamento &&
                    lancamento['status'] != 'APROVADO'
                )
                    iconeRemoverLancamento.classList.remove('hide');
            }
        }

        if (linkFatura && (permissaoEditar || idEmpresaCB != 2)) {
            if (lancamento['idnf'])
                linkFatura.classList.remove('hide');
            else
                linkFatura.classList.add('hide');
        }

        if (anexoCompra) {
            if (lancamento['anexo'])
                anexoCompra.classList.remove('hide');
            else
                anexoCompra.classList.add('hide');
        }
    }

    function abrirModalMensagem(idconciliacaofinanceiraitem) {
        let titulo = "<h3>Enviar mensagem de ajuste</h3>",
            corpo = montarCorpoModalMensagem();

        CB.modal({
            titulo,
            corpo,
            rodape: `<button type="button" class="btn btn-primary" onClick="enviarMensagem('${idconciliacaofinanceiraitem}')">Enviar mensagem</button>`
        });
    }

    function montarCorpoModalMensagem() {
        return `<div class="w-100 d-flex flex-col align-items-center" style="gap: 2rem;">
                    <h3>Informe abaixo quais os campos o colaborador deverá corrigir</h3>
                    <div class="w-100">
                        <label>Mensagem</label>
                        <input class="form-control input-mensagem" placeholder="Digite a mensagem aqui" />
                    </div>
                </div>`
    }

    function montarCorpoModalMensagemMobile(mensagem) {
        return `<div class="w-100 d-flex flex-col align-items-center" style="gap: 2rem;">
                    <div class="w-100">
                        <label>Mensagem financeiro</label>
                        <textarea class="form-control input-mensagem" readonly disabled>${mensagem}</textarea>
                    </div>
                </div>`
    }

    async function enviarMensagem(idconciliacaofinanceiraitem) {
        const mensagem = $('.input-mensagem').val();

        if (!mensagem) alertAtencao('Campo mensagem obrigatório');

        if (idconciliacaofinanceiraitem.indexOf(',') !== -1) {
            const idConciliacoesItem = idconciliacaofinanceiraitem.split(',');
            let objetoAtualizacao = {};
            let arrayIndiceConciliacaoFinanceiraItem = [];
            let arrayIndiceNf = [];
            await idConciliacoesItem.forEach((id, indice) => {
                const lancamento = alterarObjetoPorId(dadosGerais, id, {
                    mensagem
                });
                let indiceConciliacaofinanceiraItem = indice + 1;
                while (arrayIndiceNf.includes(indiceConciliacaofinanceiraItem)) indiceConciliacaofinanceiraItem++;

                if (lancamento) {
                    objetoAtualizacao[`_${indiceConciliacaofinanceiraItem}_u_conciliacaofinanceiraitem_idconciliacaofinanceiraitem`] = id;
                    objetoAtualizacao[`_${indiceConciliacaofinanceiraItem}_u_conciliacaofinanceiraitem_mensagem`] = mensagem;

                    arrayIndiceConciliacaoFinanceiraItem.push(indiceConciliacaofinanceiraItem);

                    if (lancamento['idnf']) {
                        objetoAtualizacao = {
                            ...objetoAtualizacao,
                        }

                        let indiceNf = 1;
                        while (arrayIndiceConciliacaoFinanceiraItem.includes(indiceNf)) indiceNf++;

                        objetoAtualizacao[`_${indiceNf}_u_nf_idnf`] = lancamento['idnf'];
                        objetoAtualizacao[`_${indiceNf}_u_nf_idfluxostatus`] = idFluxoStatusAberto;
                        objetoAtualizacao[`_${indiceNf}_u_nf_status`] = statusAberto;

                        arrayIndiceNf.push(indiceNf);
                    }

                    liberarAcoesMensagem(lancamento);
                } else
                    console.log(`Lançamento ${id} não encontrado para atualizacao do icone de mensagem`);
            });

            postGenerico(objetoAtualizacao);
            desmarcarLancamentosFatura();
            desmarcarLancamentosSistema();

            CB.oModal.modal('hide');
        } else {
            const lancamentoHTML = $(`[data-id="${idconciliacaofinanceiraitem}"]`)
            const lancamento = alterarObjetoPorId(dadosGerais, idconciliacaofinanceiraitem, {
                mensagem
            });


            if (lancamento) {
                let objetoAtualizacao = {
                    _1_u_conciliacaofinanceiraitem_idconciliacaofinanceiraitem: idconciliacaofinanceiraitem,
                    _1_u_conciliacaofinanceiraitem_mensagem: mensagem,
                };

                if (lancamento['idnf']) {
                    objetoAtualizacao = {
                        ...objetoAtualizacao,
                        _2_u_nf_idnf: lancamento['idnf'],
                        _2_u_nf_idfluxostatus: idFluxoStatusAberto,
                        _2_u_nf_status: statusAberto,
                    }
                }

                await postGenerico(objetoAtualizacao);

                liberarAcoesMensagem(lancamento);
                CB.oModal.modal('hide');

                desmarcarLancamentosFatura();
                desmarcarLancamentosSistema();
            } else
                console.log(`Lançamento ${idconciliacaofinanceiraitem} não encontrado para atualizacao do icone de mensagem`)
        }
    }

    function liberarAcoesMensagem(lancamento) {
        const lancamentoHTML = $(`[data-id="${lancamento['id']}"]`),
            btnEnviarMensagem = lancamentoHTML.find('.btn-enviar-mensagem').get(0),
            btnInfoMensagem = lancamentoHTML.find('.btn-info-mensagem').get(0);

        if (lancamento['status'] != 'APROVADO' && btnInfoMensagem && btnEnviarMensagem) {
            if (lancamento['mensagem']) {
                btnEnviarMensagem.classList.add('hide');
                if (!btnInfoMensagem.parentElement.getAttribute('title'))
                    btnInfoMensagem.parentElement.setAttribute('title', lancamento['mensagem']);

                btnInfoMensagem.classList.remove('hide');
            } else {
                btnInfoMensagem.classList.add('hide');

                if (btnInfoMensagem.parentElement.getAttribute('title'))
                    btnInfoMensagem.parentElement.setAttribute('title', '');

                btnEnviarMensagem.classList.remove('hide');
            }
        }
    }

    function montarOptions(formasPagamento) {
        if (!formasPagamento) return false

        let options = '<option value="">Selecionar cartão de crédito</option>';

        if (!formasPagamento.length) options += "<option value=''>Nenhum cartão encontrado</option>";

        for (let idFormaPagamento in formasPagamento)
            options += ` <option value="${formasPagamento[idFormaPagamento]['idformapagamento']}" ${formasPagamento[idFormaPagamento]['idformapagamento'] == idFormaPagamentoCB ? 'selected' : ''}>${formasPagamento[idFormaPagamento]['descricao']}</option>`;

        return options;
    }

    function montarOptionsNfe(contapagar) {
        if (!contapagar) return false

        let options = '<option value="">Selecionar fatura</option>';

        if (!contapagar.length) options += "<option value=''>Nenhuma fatura aberta encontrada</option>";

        for (let idContaPagar in contapagar)
            options += ` <option value="${contapagar[idContaPagar]['idcontapagar']}" ${contapagar[idContaPagar]['idcontapagar'] == idContapagarCB ? 'selected' : ''}>[NFe: ${contapagar[idContaPagar]['idcontapagar']}] - ${formatarValorBRL(contapagar[idContaPagar]['valor'] ?? 0)}</option>`;

        return options;
    }

    function habilitarCampos() {
        $('#cartao-credito').removeAttr('disabled');
        $('#input-nfe').removeAttr('disabled');
    }

    function desabilitarCampos() {
        $('#cartao-credito').attr('disabled', true);
        $('#input-nfe').attr('disabled', true);
    }

    function lerArquivoSalvo(caminhoArquivo) {
        if (!verificarTipoExcel(caminhoArquivo) && !caminhoArquivo.endsWith('.txt')) return alertAtencao('Tipo de arquivo não suportado');

        if (verificarTipoExcel(caminhoArquivo)) {
            abrirModalSelecaoColunasExcel(caminhoArquivo);
        } else {
            extrairDadosDoArquivo(caminhoArquivo);
        }
    }

    function verificarTipoExcel(nomeArquivo) {
        return (
            nomeArquivo.endsWith('.xls') ||
            nomeArquivo.endsWith('.csv') ||
            nomeArquivo.endsWith('.xlsx') ||
            nomeArquivo.endsWith('.xlsb') ||
            nomeArquivo.endsWith('.xlsm') ||
            nomeArquivo.endsWith('.xltx') ||
            nomeArquivo.endsWith('.xltm') ||
            nomeArquivo.endsWith('.xlam') ||
            nomeArquivo.endsWith('.ods')
        )
    }

    function extrairDadosDoArquivo(caminhoArquivo) {
        let arquivoExcel = verificarTipoExcel(caminhoArquivo);
        let novosDadosFatura = {};

        fetch(caminhoArquivo)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Erro ao carregar o arquivo');
                }

                if (arquivoExcel)
                    return response.arrayBuffer();
                else
                    return response.text();
            })
            .then(function(dados) {
                if (arquivoExcel) {
                    const workbook = XLSX.read(dados, {
                        type: 'array',
                        raw: true
                    });
                    const linhaDataInicio = $('#linha-excel-data-inicio').val(),
                        colunaData = $('#coluna-excel-data').val().toUpperCase(),
                        colunaDescricao = $('#coluna-excel-descricao').val().toUpperCase(),
                        colunaValor = $('#coluna-excel-valor').val().toUpperCase();

                    if (!linhaDataInicio || !colunaData || !colunaDescricao || !colunaValor) return alertAtencao('Preencha todos os campos!');

                    // Extrair os dados das colunas selecionadas
                    for (const indice in workbook.Sheets) {
                        let sheet = workbook.Sheets[indice];

                        for (let i = parseInt(linhaDataInicio); i < parseInt(removerLetras(sheet['!ref'].split(':')[1])); i++) {
                            let posicaoExcelData = `${colunaData}${i}`;

                            if (!sheet[posicaoExcelData]) {
                                console.log(`Posição ${colunaData}${i} não encontrada!`);
                                break;
                            }

                            let linhaData = sheet[posicaoExcelData].v;

                            // Converter para data
                            if (typeof linhaData == 'number') linhaData = converterDataExcel(linhaData);

                            if (!verificarDataValida(linhaData)) break;

                            if (linhaData) {
                                const dataExcel = linhaData;
                                const descricaoExcel = sheet[`${colunaDescricao}${i}`].v.trim().replace("'", "");
                                let valorExcel = sheet[`${colunaValor}${i}`].v;

                                if (typeof valorExcel == 'string') {
                                    if (valorFloatValido(valorExcel))
                                        valorExcel = parseFloat(valorExcel);
                                    else
                                        valorExcel = parseFloat(valorExcel.replace('.', '').replace(',', '.'));
                                }

                                if (!novosDadosFatura[dataExcel]) {
                                    novosDadosFatura[dataExcel] = [];
                                }

                                novosDadosFatura[dataExcel].push(Object.freeze({
                                    id: gerarId(),
                                    idcontapagaritem: null,
                                    idcontapagar: null,
                                    descricaoFatura: descricaoExcel,
                                    dataEmissaoFatura: dataExcel,
                                    totalFatura: valorExcel,
                                    descricaoSistema: '',
                                    dataEmissaoSistema: '',
                                    totalSistema: 0.0,
                                    status: 'PENDENTE'
                                }));
                            }
                        }
                    }

                    CB.oModal.modal('hide');

                    alertSalvo('Valores extraidos');
                } else {
                    // Ordenar os arrays pelo totalFatura e totalSistema
                    var linhas = dados.split('\n');

                    for (let i = 0; i < linhas.length; i++) {
                        var colunas = linhas[i].split('\t');

                        // Verifica se há dados suficientes na linha
                        if (colunas.length >= 4) {
                            if (colunas.length > 4) {
                                colunas = colunas.filter(item => item.trim());
                            }

                            var data = colunas[0];
                            var descricao = colunas[1].trim();
                            var dataFormatada = colunas[2].trim().split(' ')[0]; // Removendo espaços em branco extras e pegando apenas a data

                            var valorString = colunas[3].trim().substring(3);

                            if (valorFloatValido(valorString))
                                valorString = parseFloat(valorString);
                            else
                                valorString = parseFloat(valorString.replace('.', '').replace(',', '.'));

                            var valor = parseFloat(valorString);

                            if (!novosDadosFatura[data]) {
                                novosDadosFatura[data] = [];
                            }

                            novosDadosFatura[data].push(Object.freeze({
                                id: gerarId(),
                                idcontapagaritem: null,
                                idcontapagar: null,
                                descricaoFatura: descricao.trim().replace("'", ""),
                                dataEmissaoFatura: data,
                                totalFatura: valor,
                                descricaoSistema: '',
                                dataEmissaoSistema: '',
                                totalSistema: 0.0,
                                status: 'PENDENTE'
                            }));
                        }
                    }

                    if (!Object.keys(novosDadosFatura).length) {
                        const pattern = /(\d{2}\/\d{2}\/\d{4})\s+(.+?)\s+BRL\s+(-?[\d.,]+)/;
                        linhas.forEach(line => {
                            const match = line.match(pattern);
                            if (match) {
                                const [_, data, descricao, valor] = match;

                                if (!novosDadosFatura[data]) {
                                    novosDadosFatura[data] = [];
                                }

                                novosDadosFatura[data].push(Object.freeze({
                                    id: gerarId(),
                                    idcontapagaritem: null,
                                    idcontapagar: null,
                                    descricaoFatura: descricao.trim().replace("'", ""),
                                    dataEmissaoFatura: data,
                                    totalFatura: parseFloat(valor.replace('.', '').replace(',', '.')),
                                    descricaoSistema: '',
                                    dataEmissaoSistema: '',
                                    totalSistema: 0.0,
                                    status: 'PENDENTE'
                                }));
                            }
                        });
                    }
                }

                for (let data in novosDadosFatura) {
                    const lancamentos = novosDadosFatura[data]

                    novosDadosFatura[data] = ordenarPorTotal(lancamentos);
                }

                dadosFatura = Object.freeze(novosDadosFatura);
                $('#btn-pesquisar').removeAttr('disabled');
            })
            .catch(function(error) {
                console.error('Erro ao ler o arquivo:', error);
            });
    }

    function valorFloatValido(stringVal) {
        // Expressão regular para verificar se a string é um float válido
        const floatRegex = /^[+-]?(\d+(\.\d*)?|\.\d+)$/;
        // Verificar se a string corresponde à expressão regular
        if (floatRegex.test(stringVal)) {
            // Usar parseFloat para garantir que a string pode ser convertida em um float
            const floatNumber = parseFloat(stringVal);
            return !isNaN(floatNumber);
        }
        return false;
    }

    function converterDataExcel(serial) {
        // Excel's "zero" date is 30 December 1899 (for backwards compatibility reasons)
        const excelEpoch = new Date(1899, 11, 30);
        const excelEpochMS = excelEpoch.getTime();
        const dayInMS = 86400000;
        const dateInMS = excelEpochMS + (serial * dayInMS);

        // Convert to Date object
        const date = new Date(dateInMS);

        // Format the date as dd/mm/yyyy
        const day = ("0" + date.getDate()).slice(-2);
        const month = ("0" + (date.getMonth() + 1)).slice(-2);
        const year = date.getFullYear();

        return `${day}/${month}/${year}`;
    }

    function removerLetras(string) {
        // Expressão regular para substituir todas as letras por uma string vazia
        return string.replace(/[^\d]/g, '');
    }

    function verificarDataValida(data) {
        // Define a expressão regular para verificar o formato dd/mm/yyyy
        const regex = /^\d{2}\/\d{2}\/\d{4}$/;
        return regex.test(data);
    }

    function abrirModalSelecaoColunasExcel(caminhoArquivo) {
        const camposModal = `<div class="row d-flex align-items-center flex-wrap">
                                        <div class="col-xs-12 mb-3">
                                            <h3>Informe os parâmetros da fatura dentro do excel, para que possa ser feita a leitura.</h3>
                                        </div>
                                        <div class="col-xs-3 form-group">
                                            <label>Linha de inicio das datas de lançamento</label>
                                            <div class="d-flex flex-wrap">
                                                <input id="linha-excel-data-inicio" class="form-control col-xs-6" placeholder="Informe o número" type="number" title="Digite o número da linha da primeira data."/>
                                                <input id="linha-excel-data-inicio" class="form-control col-xs-6" placeholder="Informe o número" type="number" title="Digite o número da linha da última data."/>
                                            </div>
                                        </div>
                                        <div class="col-xs-3 form-group">
                                            <label>Coluna das datas dos lançamentos</label>
                                            <input id="coluna-excel-data" class="form-control" placeholder="Informe a letra" title="Digite a coluna à qual pertence a data no excel" />
                                        </div>
                                        <div class="col-xs-3 form-group">
                                            <label>Coluna da descrição dos lançamentos</label>
                                            <input id="coluna-excel-descricao" class="form-control" placeholder="Informe a letra" title="Digite a coluna à qual pertence a descrição no excel" />
                                        </div>
                                        <div class="col-xs-3 form-group">
                                            <label>Coluna dos valores dos lançamentos</label>
                                            <input id="coluna-excel-valor" class="form-control" placeholder="Informe a letra" title="Digite a coluna à qual pertence o valor no excel" />
                                        </div>
                                    </div>`;

        CB.modal({
            titulo: 'Parâmetros de lançamentos da fatura no excel',
            corpo: camposModal,
            rodape: `<button onClick="extrairDadosDoArquivo('${caminhoArquivo}');" class="btn btn-primary">Extrair</button>`
        });
    }

    function gerarId() {
        // Concatenando um timestamp atual com um número aleatório para criar um ID único
        return 'id_' + '_' + Math.random().toString(36).substr(2, 9);
    }

    function encontrarValoresIguais(id, array1, objeto) {
        return array1.filter(function(item) {
            return item.id !== id && (item.totalSistema == objeto.totalSistema || item.totalFatura == objeto.totalFatura);
        });
    }

    function ordenarObjetoPorData(objeto) {
        // Obter as chaves (datas) e ordená-las
        return Object.keys(objeto).sort(function(a, b) {
            var dataA = converterStringParaData(a);
            var dataB = converterStringParaData(b);
            return dataA.getTime() - dataB.getTime();
        });
    }

    // Função para converter uma string de data no formato 'DD/MM/AAAA' em um objeto Date
    function converterStringParaData(dataString) {
        var partes = dataString.split('/');
        return new Date(partes[2], partes[1] - 1, partes[0]); // Ano, mês (zero-based), dia
    }

    function ordenarPorTotal(array) {
        let arrayLimpo = array ?? [];

        // Ordenar pelo atributo totalFatura em ordem crescente
        arrayLimpo.sort(function(a, b) {
            return parseFloat(a.totalFatura) - parseFloat(b.totalFatura);
        });

        // Se houver empate no totalFatura, ordenar pelo totalSistema em ordem crescente
        arrayLimpo.sort(function(a, b) {
            if (parseFloat(a.totalFatura) == parseFloat(b.totalFatura)) {
                return parseFloat(a.totalSistema) - parseFloat(b.totalSistema);
            }
            return 0;
        });

        // Se não houver lancamentos no sistema ou fatura
        arrayLimpo.sort(function(a, b) {
            return parseFloat(a.totalFatura) - parseFloat(b.totalSistema);;
        });

        return arrayLimpo;
    }

    function ordenarPorPorcentagem(array) {
        return array.sort(function(a, b) {
            return parseInt(b.porcentagem) - parseInt(a.porcentagem);
        });
    }

    function liberarBtnConciliar() {
        if ($('#itens-selecionados-fatura').data('itensselecionadosfatura') && $('#itens-selecionados-sistema').data('itensselecionadossistema') &&
            ($('#valor-total-sistema').data('valortotalsistema') === $('#valor-total-fatura').data('valortotalfatura')))
            $('#btn-conciliar').removeAttr('disabled');
        else
            $('#btn-conciliar').attr('disabled', true);
    }

    function desmarcarLancamentosFatura() {
        $('.check-fatura:checked').each((index, element) => {
            element.checked = false;
        });

        $('#itens-selecionados-fatura').data('itensselecionadosfatura', 0).text(0);
        $('#valor-total-fatura').data('valortotalfatura', 0).text(formatarValorBRL(0));

        desmarcarLancamentosSistema();
    }

    function desmarcarLancamentosSistema() {
        $('.check-sistema:checked').each((index, element) => {
            element.checked = false;
        });

        $('#itens-selecionados-sistema').data('itensselecionadossistema', 0).text(0);
        $('#valor-total-sistema').data('valortotalsistema', 0).text(formatarValorBRL(0));

        liberarBtnConciliar();
    }

    function alterarObjetoPorId(objeto, id, novosDados, atualizarStatus = true, verificaFilhos = false) {
        // Lancamentos
        for (let propriedade in objeto) {
            if (objeto.hasOwnProperty(propriedade)) {
                // Lancamentos por data
                let array = objeto[propriedade];
                for (let i = 0; i < array.length; i++) {
                    // Lancamento
                    let lancamento = array[i];
                    if (array[i].id == id) {
                        // Alterar os dados do objeto encontrado
                        for (let chave in novosDados) {
                            if (novosDados.hasOwnProperty(chave) && chave != 'id') {
                                array[i][chave] = novosDados[chave];
                            }
                        }

                        if (atualizarStatus)
                            atualizarStatusLancamento(!lancamento['dataEmissaoFatura'] ? false : lancamento['dataEmissaoFatura'], lancamento['match'], lancamento['id']);

                        return lancamento; // Objeto encontrado e alterado
                    }

                    if (verificaFilhos && lancamento['filhos']) {
                        for (let indiceFilho in lancamento['filhos']) {
                            let lancamentoFilho = lancamento['filhos'][indiceFilho];

                            if (lancamentoFilho['id'] == id) {
                                for (let chave in novosDados) {
                                    if (novosDados.hasOwnProperty(chave) && chave != 'id') {
                                        array[i]['filhos'][indiceFilho][chave] = novosDados[chave];
                                    }
                                }

                                if (atualizarStatus)
                                    atualizarStatusLancamento(!lancamentoFilho['dataEmissaoFatura'] ? false : lancamentoFilho['dataEmissaoFatura'], lancamentoFilho['match'], lancamentoFilho['id']);

                                return lancamentoFilho; // Objeto encontrado e alterado
                            }
                        }
                    }
                }
            }
        }

        console.log(`Objeto ${id} não encontrado para atualização!`);

        return false; // Objeto não encontrado
    }

    function buscarLancamentosFilhosPorId(id) {
        let arrayId = [],
            lancamentosEncontrados = [];

        if (Array.isArray(id)) {
            arrayId = id;
        }

        for (let propriedade in dadosGerais) {
            if (dadosGerais.hasOwnProperty(propriedade)) {
                let array = dadosGerais[propriedade];
                for (let i = 0; i < array.length; i++) {
                    if (arrayId.length) {
                        if (arrayId.includes(array[i].idPai))
                            lancamentosEncontrados.push({
                                ...array[i]
                            })
                    } else {
                        if (array[i] && array[i].hasOwnProperty('idPai') && array[i].idPai == id) {
                            lancamentosEncontrados.push({
                                ...array[i]
                            }) // Objeto encontrado
                        }
                    }
                }
            }
        }

        if (lancamentosEncontrados.length)
            return lancamentosEncontrados;

        return false; // Objeto não encontrado
    }

    function buscarLancamentoPorId(id) {
        let arrayId = [],
            lancamentosEncontrados = [];

        if (Array.isArray(id)) {
            arrayId = id.map(item => {
                return Number.isNaN(parseInt(item)) ? item : parseInt(item);
            });
        }

        for (let propriedade in dadosGerais) {
            if (dadosGerais.hasOwnProperty(propriedade)) {
                let array = dadosGerais[propriedade];
                for (let i = 0; i < array.length; i++) {
                    if (arrayId.length) {
                        if (arrayId.includes(Number.isNaN(parseInt(array[i].id)) ? array[i].id : parseInt(array[i].id)))
                            lancamentosEncontrados.push({
                                ...array[i]
                            })
                    } else {
                        if (array[i].id == id) {
                            return {
                                ...array[i]
                            }; // Objeto encontrado
                        }
                    }
                }
            }
        }

        if (lancamentosEncontrados.length)
            return lancamentosEncontrados;

        console.log(`Objeto ${id} não encontrado!`);

        return false; // Objeto não encontrado
    }

    function limparObjeto(objeto) {
        const objetoLimpo = {
            ...objeto
        };

        for (let data in objetoLimpo) {
            objetoLimpo[data] = objetoLimpo[data].filter(item => item);
        }
        return objetoLimpo;
    }

    function buscarLancamentoPorDataEmissaoEIndice(dataEmissao, indiceAEncontrar = false) {
        for (let data in dadosGerais) {
            if (data == dataEmissao) {
                if (indiceAEncontrar !== false) {
                    for (let indice in dadosGerais[data]) {
                        if (indice == indiceAEncontrar) {
                            return {
                                ...dadosGerais[data][indice]
                            };
                        }
                    }
                } else
                    return {
                        ...dadosGerais[data]
                    };
            }
        }

        return false;
    }

    function adicionarLancamento(novoLancamento, dataEspecifica) {
        let encontrado = false;

        for (let data in dadosGerais) {
            if (data == dataEspecifica) {
                if (dadosGerais[data]) {
                    dadosGerais[data] = dadosGerais[data].concat(novoLancamento);

                    encontrado = true;
                    break;
                }
            }
        }

        if (!encontrado) {
            if (novoLancamento['dataEmissaoSistema'])
                dadosGerais[novoLancamento['dataEmissaoSistema']] = [novoLancamento];
        }

        if (Array.isArray(novoLancamento))
            novoLancamento.forEach(item => {
                let novoLancamentoHTML = '';

                if (item.idPai) {
                    novoLancamentoHTML = $(montarItemLancamento(item));
                    novoLancamentoHTML.insertAfter(`[data-id="${item.idPai}"]`);
                }
            })

        return encontrado;
    }

    function adicionarLancamentoHTML(lancamentoDados, dataEspecifica) {
        const lancamentoHTML = $(`[data-id="${lancamentoDados['id']}"]`);

        if (lancamentoHTML) {
            console.log(`Lançamento ${lancamentoDados['id']} já existe na DOM!`);

            return lancamentoHTML;
        }

        // Adicionar elemento na ordem da data
        const lancamentos = buscarLancamentoPorDataEmissaoEIndice(dataEspecifica);
    }

    function removerLancamentos(idLancamento, dataEspecifica = false, verificaFilhos = false) {
        // Remover apenas um lancamento por id
        let buscaEspecifica = false;

        if (Array.isArray(idLancamento)) {
            idLancamento = idLancamento.map(item => Number.isNaN(parseInt(item)) ? item : parseInt(item));
        }

        for (let data in dadosGerais) {
            if (!dataEspecifica || data === dataEspecifica) {
                if (dadosGerais[data]) {
                    dadosGerais[data] = dadosGerais[data].filter(item => {
                        if (Array.isArray(idLancamento)) {
                            return !idLancamento.includes(Number.isNaN(parseInt(item.id)) ? item.id : parseInt(item.id));
                        } else {
                            if (item.id == idLancamento)
                                buscaEspecifica = true;

                            return item.id != idLancamento
                        }
                    });

                    if (!dadosGerais[data].length) delete dadosGerais[data];

                    if (verificaFilhos) {
                        if(dadosGerais[data])
                            dadosGerais[data].forEach((item, indiceData) => {
                                for (let indiceFilho in item['filhos']) {
                                    let lancamentoFilho = item['filhos'][indiceFilho];

                                    if (lancamentoFilho['id'] == idLancamento) {
                                        delete dadosGerais[data][indiceData]['filhos'][indiceFilho];
                                        buscaEspecifica = true;
                                    }
                                }
                            });
                        else 
                            console.log(`Data ${data} não encontrada em dadosGerais [removerLancamentos()]`);
                    }

                    removerLancamentosHTML(idLancamento);
                    salvarLancamentosRemovidos(idLancamento);

                    // elemento removido
                    if (buscaEspecifica) return true;

                    if (dataEspecifica !== false)
                        return true;
                }
            }
        }

        removerLancamentosHTML(idLancamento);

        return false
    }

    function removerLancamentosHTML(idLancamento) {
        if (Array.isArray(idLancamento)) {
            idLancamento.forEach(id => {
                let lancamentoHTML = $(`[data-id="${id}"`);

                if (!lancamentoHTML)
                    alertAtencao(`Lançamento ${id} não encontrado [removerLancamentosHTML]`);
                else
                    lancamentoHTML.remove();
            })
        } else {
            let lancamentoHTML = $(`[data-id="${idLancamento}"`);

            if (!lancamentoHTML)
                alertAtencao(`Lançamento ${idLancamento} não encontrado [removerLancamentosHTML]`);
            else
                lancamentoHTML.remove();
        }
    }

    function buscarLancamentosFatura() {
        const lancamentosCongelados = limparObjeto(dadosFatura);
        let lancamentos = {};

        for (let data in lancamentosCongelados) {
            for (let indice in lancamentosCongelados[data]) {
                if (!lancamentos[data]) {
                    lancamentos[data] = [];
                }

                lancamentos[data][indice] = {
                    ...lancamentosCongelados[data][indice]
                };
            }
        }

        return lancamentos;
    }

    /**
     * Post generico para o carbon
     */
    function salvarLancamentos(refresh = false) {
        salvarLancamentosInput();

        let objetos = {
            _1_u_conciliacaofinanceira_idconciliacaofinanceira: idConciliacaoFinanceira,
            _1_u_conciliacaofinanceira_idempresa: $('#input-empresa').val(),
            _1_u_conciliacaofinanceira_idformapagamento: $('#cartao-credito').val(),
            _1_u_conciliacaofinanceira_idcontapagar: $('#input-nfe').val(),
            _1_u_conciliacaofinanceira_status: $('#input-status').val(),
            dados_lancamento: $('#dados-lancamentos-json').val()
        }

        postGenerico(objetos, true, refresh);
    }

    function salvarLancamentosInput() {
        const inputDadosLancamentoJSON = $('#dados-lancamentos-json');

        if (!inputDadosLancamentoJSON) {
            alertAtencao('Dados de lançamento não encontrados para atualização');

            return false;
        }

        inputDadosLancamentoJSON.val(JSON.stringify(dadosGerais, null, 2));

        return true;
    }

    function salvarLancamentosRemovidos(idLancamento = false) {
        if (idLancamento) {
            if (Array.isArray(idLancamento)) {
                idLancamento = idLancamento.filter(id => !Number.isNaN(parseInt(id))).map(id => parseInt(id));

                if (!lancamentosRemovidos.some(item => idLancamento.includes(item.id)))
                    lancamentosRemovidos = lancamentosRemovidos.concat(idLancamento);
            } else {
                idLancamento = parseInt(idLancamento);

                if (!Number.isNaN(idLancamento) && !lancamentosRemovidos.includes(idLancamento))
                    lancamentosRemovidos.push(idLancamento);
            }
        }

        const inputDadosLancamentoRemovidos = $('#dados-lancamentos-remover');
        if (!inputDadosLancamentoRemovidos) {
            alertAtencao('Dados de lançamento não encontrados para atualização');

            return false;
        }

        inputDadosLancamentoRemovidos.val(lancamentosRemovidos);

        return true;
    }

    async function postGenerico(objetos, parcial = true, refresh = false) {
        if (!objetos) {
            console.log('Nenhum valor informado!');
            return;
        }

        CB.post({
            objetos,
            parcial,
            refresh: refresh ? 'refresh' : refresh,
        })
    }

    CB.prePost = salvarLancamentosInput
</script>