<script>
    //------- Injeção PHP no Jquery -------
    idfolhapagamento = '<?=$_1_u_folhapagamento_idfolhapagamento?>';
    idempresa = '<?=$_1_u_folhapagamento_idempresa?>';
    lancamentos = <?=json_encode($detalhamentoLancamentos)?> || {};
    //------- Injeção PHP no Jquery -------

	//------- Variáveis Globais -------
	//------- Variáveis Globais -------

    //------- Exececuções para Carregar o módulo ----------
    if ($("[name=_1_u_folhapagamento_idfolhapagamento]").val()) 
    {
		$("#uploadtxt").dropzone({
            previewTemplate: $("#cbDropzone").html(),
            url: "form/_arquivo.php",
            idObjeto: $("[name=_1_u_folhapagamento_idfolhapagamento]").val(),
            tipoObjeto: 'folhapagamento',
            tipoArquivo: 'folhaponto',
            acceptedFiles: ".txt",
            init: function(){
                this.on("error", function(file, errorMessage) {
                    alertAtencao('Tipo de arquivo não suportado');
                    this.removeAllFiles(); // Remove todos os arquivos
                });
                myDropzone = this;
            },
            sending: function(file, xhr, formData) {
                formData.append("idobjeto", this.options.idObjeto);
                formData.append("tipoobjeto", this.options.tipoObjeto);
                formData.append("tipoarquivo", this.options.tipoArquivo);
            },
            success: function(file, response) {
                console.log("Upload completo para o arquivo:", file.name);
                jResp = jsonStr2Object(response);
                caminhoArquivo = jResp[jResp.length - 1].caminho;
                processFile(caminhoArquivo);
            },
        });
	}

    $('#remover-arquivo').on('click', function() {
        // Remover lancamentos do lancamento
        $.ajax({
            type: "GET",
            url : "ajax/folhapagamento.php",
            data: `action=removerLancamentoFolhaPonto&params=${idfolhapagamento}`

        }).done(function(data, textStatus, jqXHR){
            dados = JSON.parse(data.trim());
            if(dados[0].error == true){                
                alertAtencao(dados[0].mensagem)
            }else{
                alertAzul(dados[0].mensagem);
                CB.inicializaModulo();
            }
        });
    });
    //------- Exececuções para Carregar o módulo ----------

    //------- Funções JS -------
    // Função para processar os dados do arquivo
    function processFile(caminhoArquivo) {
        dataObjects = [] ;

        fetch(caminhoArquivo)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Erro ao buscar o arquivo");
                }
                return response.arrayBuffer(); // Obtém o conteúdo como texto
            })
            .then(buffer => {
                const decoder = new TextDecoder('windows-1252'); // Usando 'windows-1252' que é comum em arquivos ANSI
                const utf8Content = decoder.decode(buffer); // Decodificando para UTF-8
                
                var linhas = utf8Content.split('\n');
                linhas = linhas.filter(item => item);

                // Ignora linhas de cabeçalho ou outras linhas não desejadas
                linhas.forEach((linha, index) => {
                    if(linha.length > 0){
                        if(index == 0) {
                            const dataObject = {
                                empresa: linha.replace(/"/g, "").trim()
                            };

                            dataObjects.push(dataObject); // Adiciona o objeto ao array
                        } else {
                            const parts = linha?.split(","); // Divide a linha em partes separadas por vírgulas
                            if(parts.length == 2) {
                                const dataObject = {
                                    periodo: linha.replace(/"/g, "").trim()
                                };
                                dataObjects.push(dataObject); // Adiciona o objeto ao array
                            } else {

                                // Extraímos os valores de cada parte, removendo as aspas extra
                                const datalancamento = parts[0].trim();
                                const nome = parts[1].replace(/"/g, "").trim();
                                const lancamento = parts[2].replace(/"/g, "").trim();
                                const contadebito = parts[3].trim();
                                const contacredito = parts[4].trim();
                                const valor = parts[5].trim();
                                const centrocusto = parts[6].trim();
                                const codhistorico = parts[7].trim();
                                const historico = parts[8].replace(/"/g, "").trim();

                                // Criação do objeto
                                const dataObject = {
                                    datalancamento: datalancamento,
                                    nome: nome,
                                    lancamento: lancamento,
                                    contadebito: contadebito,
                                    contacredito: contacredito,
                                    valor: valor,
                                    centrocusto: centrocusto,
                                    codhistorico: codhistorico,
                                    historico: historico
                                }

                                dataObjects.push(dataObject); // Adiciona o objeto ao array
                            }                                                
                        }
                    }
                });
                
                dataObjectsJson = JSON.stringify(dataObjects);

                $.ajax({
                    type: "POST",
                    url : "ajax/folhapagamento.php",
                    data: {
                        action: 'validarDadosArquivo',
                        params: `${idfolhapagamento}|${dataObjectsJson}|${idempresa}`
                    },
                    success: function(data, textStatus, jqXHR){
                        data = data.trim();
                        data = jsonStr2Object(data);
                        if(data.error == true){  
                            const spanErro = `<span>${data.mensagem}</span>`;          
                            $('#mensagem-erro').html(spanErro);
                            $('#mensagem-erro').removeClass('hidden');
                            myDropzone.removeAllFiles();
                        }else{
                            CB.post({
                                objetos: {
                                    dataObjects: dataObjectsJson,
                                    _1_u_folhapagamento_idfolhapagamento: idfolhapagamento
                                },
                                parcial: true
                            }); 
                        }
                    },                         
                    error: function(objxmlreq){
                        alert('Erro:<br>'+objxmlreq.status);
                    }
                });
            })
            .catch(error => {
                console.error("Erro ao processar o arquivo:", error);
            });
    }

    let agGridAtual = false;

    function mostrarDetalhamentoFolha(codigoevento, nome) { debugger;
        strCabecalho = `<strong class="col-xs-10" style="text-wrap: nowrap; max-width: 100%; overflow: hidden; text-overflow: ellipsis;">
                            Lançamento de ${nome}
                        </strong>`;

        CB.modal({
            titulo: strCabecalho,
            corpo: `<div class="corpoModal ag-theme-balham" style="height: 500px; width: 100%;"></div>`,
            classe: 'sessenta',
        });
        
        agGridAtual = montaDetalhamentoAgGrid(codigoevento);
        
    }

    function montaDetalhamentoAgGrid(codigoevento){debugger;

        var debounceCadastrado = null;

        var listarDetalhamento = lancamentos[codigoevento];
        const columnDefs = [
            { 
                field: 'datalancamento',
                headerName: "DATA LANÇAMENTO",
                cellClass: 'small-font',
                width: 130,
                cellRenderer: params => { return formatarData(params.data.datalancamento); }
            }, 
            { 
                field: 'nomecurto',
                headerName: "NOME DO COLABORADOR",
                width: 300,
                cellRenderer: params => { return params.data.nomecurto; }
            }, 
            { 
                field: 'descricaolancamento',
                headerName: "DESCRIÇÃO DO LANÇAMENTO",
                cellClass: 'small-font',
                width: 200,
                cellRenderer: params => { return params.data.descricaolancamento; }
            },
            { 
                field: 'valorlancamento', 
                headerName: "VALOR",
                cellClass: 'small-font',
                width: 150,
                cellRenderer: params => { return formatarValorBRL(params.data.valorlancamento); }
            },
            { 
                field: null, 
                headerName: "Ação",
                headerTooltip: "Ação",
                cellClass: 'small-font',
                width: 50,
                cellRenderer: params => {
                    return `<button id="gerar-btn" onclick="removerLinha('${params.data.idfolhapagamentoitem}', '${codigoevento}')" class="fa fa-trash fa-1x cinzaclaro hoververmelho pointer ui-droppable borderButton">
                                <span id="btn-loading" class="spinner-nf" style="display: none;"></span>
                            </button>`
                }
            },
        ]
        
        const localeText = {
            // Traduções comuns
            page: "Página",
            more: "Mais",
            to: "para",
            of: "de",
            next: "Próximo",
            last: "Último",
            first: "Primeiro",
            previous: "Anterior",
            loadingOoo: "Carregando...",
            selectAll: "Selecionar Tudo",
            searchOoo: "Procurar...",
            blanks: "Em branco",
            filterOoo: "Filtrando...",
            applyFilter: "Aplicar Filtro",
            equals: "Igual",
            notEqual: "Diferente",
            lessThan: "Menor que",
            greaterThan: "Maior que",
            lessThanOrEqual: "Menor ou igual a",
            greaterThanOrEqual: "Maior ou igual a",
            inRange: "No intervalo",
            contains: "Contém",
            notContains: "Não contém",
            startsWith: "Começa com",
            endsWith: "Termina com",
            // Traduções para a parte de paginação
            pageNext: "Próxima Página",
            pageLast: "Última Página",
            pageFirst: "Primeira Página",
            pagePrevious: "Página Anterior",
            pageSizeSelectorLabel: "Quantidade de Páginas:",
            ariaPageSizeSelectorLabel: "Quantidade de Páginas:",
            // Traduções para a parte de agrupamento
            group: "Grupo",
            groupColumns: "Colunas de Grupo",
            groupColumnsEmptyMessage: "Arraste aqui para agrupar",
            // Adicione mais traduções conforme necessário
        }

        const gridOptionsDetalhamento = {
            pagination: true,
            paginationPageSize: 50,
            paginationPageSizeSelector: [50, 100, 200, 500, 1000, 1500, 2000],
            onPaginationChanged: onPaginationChanged,
            rowData: listarDetalhamento,
            getRowId: params => { 
                return params.data.idfolhapagamentoitem; // Classe padrão para os outros
            },
            domLayout: 'autoHeight',
            localeText: localeText,
            columnDefs: columnDefs,
            defaultColDef: {
                editable: false,
            }
        }
        
        return new agGrid.createGrid(document.querySelector('.corpoModal'), gridOptionsDetalhamento); 
    }
    
    function onPaginationChanged(params) {
        const currentPageSize = params.api.paginationGetPageSize();
    }

    function gerarNF(idfolhapagamento, codigoevento, idempresa) {
        strCabecalho = `<strong class="col-xs-11" style="max-width: 100%; overflow: hidden; text-overflow: ellipsis;">
                            Criar Nova NF
                            <button onclick="executarGerarNf('${idfolhapagamento}', '${codigoevento}', '${idempresa}')" class="btn btn-primary rounded text-light botao-gerar-nf" style="float: right; margin-top: 5px;">
                                <span id="btn-text">Salvar</span>
                            </button>
                        </strong>`;

        CB.modal({
            titulo: strCabecalho,
            corpo: `<div class="row d-flex flex-between flex-wrap">
                        <div class="col-xs-12 col-md-12">
                            <label>Digite o Nº da NNFE</label>
                            <div class="d-flex align-items-center">
                                <input name="numero_nnfe" class="size20" id="numero_nfe" type="text" vnulo>
                            </div>
                        </div>
                    </div>`,
            classe: 'trinta',
        });
    }

    function executarGerarNf(idfolhapagamento, codigoevento, idempresa){
        const button = $(`#gerar-btn-${codigoevento}`);debugger;
        nnfe = $(`#numero_nfe`).val();

        if(nnfe == undefined || nnfe.length == 0){
            alertAtencao('Preencher o Campo NNFE. ');
        } else {
            // Adiciona spinner ao botão
            if (!button.find(".spinner").length) {
                button.append('<div class="spinner"></div>');
            }

            // Oculta o texto e exibe o spinner
            button.prop("disabled", true); // Desativa o botão
            button.find(".spinner").show();
            button.contents().filter(function () {
                return this.nodeType === 3; // Apenas o texto do botão
            }).wrap('<span class="hidden-text"></span>').hide();

            CB.post({
                objetos: {
                    _1_u_folhapagamento_idfolhapagamento: idfolhapagamento,
                    _1_u_folhapagamento_idempresa: idempresa,
                    codigoevento: codigoevento,
                    nnfe: nnfe
                },
                parcial: true,
                posPost: function(){
                    button.prop("disabled", false);
                    button.find(".spinner").remove();
                    button.find(".hidden-text").contents().unwrap().show();
                }
            });
        }
    }
    
    function removerLinha(idfolhapagamentoitem, codigoevento){
        debugger
        lancamentos[codigoevento] = lancamentos[codigoevento].filter(item => item.idfolhapagamentoitem != idfolhapagamentoitem);
        const lancamentoAtual = lancamentos[codigoevento];
        const qtdLancamentos = lancamentos[codigoevento].length;
        const valorLancamento = lancamentoAtual.reduce((total, atual) => total + parseFloat(atual.valorlancamento), 0);

        CB.post({
            objetos: {
                _1_d_folhapagamentoitem_idfolhapagamentoitem: idfolhapagamentoitem
            },
            parcial: true,
            refresh: false,
            posPost: function(){
                if(agGridAtual) agGridAtual.setGridOption("rowData", lancamentos[codigoevento]);

                $(`.qtd-${codigoevento}`).text(qtdLancamentos);
                $(`.valor-${codigoevento}`).text(formatarValorBRL(valorLancamento.toFixed(2)));
            }
        }); 
    }
    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape1
    //------- Funções JS -------
</script>