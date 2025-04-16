<script src="https://cdn.jsdelivr.net/npm/davidshimjs-qrcodejs@0.0.2/qrcode.min.js"></script>
<script>
    const nomeDoElementoJQ = $('#corpo');

    let loteAtual = '',
        tagsCamaraFriaOptions = '',
        idPrateleiraSelecionada = '',
        qtdRetem = 0,
        qtdAlocada = 0,
        qtdAlocacaoAtingida = false,
        idTagDimAtual = false,
        pedidoAtual = false,
        quantidadeDisponivelLote = 0,
        prodServAtual = '',
        loteFracaoMovAtual = [];

    // QRScanner
    let video = '';
    let qrScanner = '';

    // Devolver lote
    $('#cbModal').on('click', '#btn-devolver-lote', function() {
        const idLote = $(this).data('idlote');

        if (!idLote) return alertAtencao('ID lote não informado.');

        // Retornar lote para producao novamente ( transferir para unidade de producao novamente )
        $.ajax({
            url: '../../ajax/lote.php',
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'devolverLoteProducao',
                params: idLote
            },
            success: res => {
                if (Object.keys(res).length && res.error) return alertAtencao(res.error);

                if (!res) return alertAtencao('Ocorreu um erro ao devolver lote');

                alertAzul('Lote devolvido');
                montarElemento('');
            },
            error: err => {
                alertAtencao('Ocorreu um erro ao devolver lote.');
                console.log(err);
            }
        })
    });

    // Reter itens
    $('#cbModuloForm').on('click', '#opcoes-produtos-retem li', function() {
        const elementoJQ = $(this);

        $('.qtd-vacina-selecionada').removeClass('qtd-vacina-selecionada');
        elementoJQ.addClass('qtd-vacina-selecionada');

        qtdRetem = parseInt(elementoJQ.data('qtd'));

        if (isNaN(qtdRetem)) {
            qtdRetem = 0;

            return alertAtencao('Quantidade a ser retida inválida.');
        };
    });

    function montarElemento(id, param = '') {
        nomeDoElementoJQ.html(trocaDeTela(id, param));

        switch (id) {
            case 'qrcodevacina':
                /**
                 * TODO: Adicionar vinculo com formalizacao e verificar se a atividade atual é logistica
                 */
                inicializarScanner((result => {
                    qrScanner.stop();
                    $('#camera-take').addClass('lido');

                    // Exiba o resultado do QR Code lido
                    const idFormalizacao = getUrlParameter('idformalizacao', result);
                    const idEmpresa = getUrlParameter('_idempresa', result);

                    if (!idFormalizacao) {
                        qrScanner.start();
                        $('#camera-take').removeClass('lido');
                        return alertAtencao('ID da formalização não encontrado.')
                    };

                    if (!idEmpresa) {
                        qrScanner.start();
                        $('#camera-take').removeClass('lido');
                        return alertAtencao('ID da empresa não encontrado.')
                    };

                    $.ajax({
                        url: '../../ajax/lote.php',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            action: 'buscarLoteIdPorFormalizacao',
                            params: [idFormalizacao, idEmpresa]
                        },
                        success: res => {
                            if (res.error) {
                                $('#camera-take').removeClass('lido');
                                qrScanner.start();
                                return alertAtencao(res.error)
                            };

                            loteAtual = res.lote;
                            quantidadeDisponivelLote = res.lote.qtddisponivel ?? 0;

                            if (res.statusLoteAtiv == 'PROCESSANDO')
                                montarElemento('produtos_retidos', res.lote);
                            else
                                montarElemento('guardar_vacina', res.lote);
                        },
                        error: err => {
                            alertAtencao('Ocorreu um erro ao buscar infromações do lote.');
                            console.log(err);
                        }
                    }).error(err => {
                        console.log(err);
                        qrScanner.start();
                        alertAtencao('Ocorreu um erro ao solicitar informações do lote.')
                    })
                }))
                break;
            case 'qrcode_alocar_produtos':
                let paginaAnterior = 'produtos_retidos',
                    paginaAnteriorParam = prodServAtual;

                if(param == 'alocar_produtos' ) {
                    paginaAnterior = 'alocar_produtos';
                    paginaAnteriorParam = loteFracaoMovAtual;
                }

                inicializarScanner((idTagDim => {
                    qrScanner.stop();
                    $('#camera-take').addClass('lido');

                    if (!idTagDim) {
                        setTimeout(() => qrScanner.start(), 1000);
                        $('#camera-take').removeClass('lido');
                        return alertAtencao('ID da posição da prateleira não encontrada.')
                    };

                    let qtd = qtdAlocada > 0 ? qtdAlocada : qtdRetem;

                    idTagDimAtual = idTagDim;

                    // Pegar quantidade de produtos que serão retidos
                    if (!qtd) {
                        montarElemento('produtos_retidos');
                        return alertAtencao('Quantidade de produtos a serem retido inválida. Selecione uma opção·');
                    }

                    // Verifiucar se ja existe lote na posicao escaneada
                    $.ajax({
                        url: '../../ajax/lote.php',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            action: 'verificarSeExisteLotePosicao',
                            params: idTagDim
                        },
                        success: loteFracaoMov => {
                            if (loteFracaoMov) {
                                if (!loteFracaoMov) {
                                    $('#camera-take').removeClass('lido');
                                    qrScanner.start();
                                    return alertAtencao('Ocorreu um erro ao tentar víncular lote à prateleira.')
                                };

                                if (loteFracaoMov.error) {
                                    $('#camera-take').removeClass('lido');
                                    qrScanner.start();
                                    return alertAtencao(loteFracaoMov.error);
                                };

                                if(!Array.from(loteFracaoMov).length) buscarLotesPrateleira();
                                else montarElemento('alocar_produtos_existentes', loteFracaoMov);
                            } else {
                                buscarLotesPrateleira();
                            }
                        },
                        error: err => {
                            alertAtencao('Ocorreu um erro ao buscar infromações do lote.');
                            console.log(err);
                        }
                    }).error(err => {
                        console.log(err);
                        qrScanner.start();
                        alertAtencao('Ocorreu um erro ao solicitar informações do lote.')
                    });
                }), paginaAnterior, paginaAnteriorParam);
                break;
            case 'qrcode_retirar_vacina':
                inicializarScanner((idNfItem => {
                    qrScanner.stop();
                    $('#camera-take').addClass('lido');

                    if (!idNfItem) {
                        setTimeout(() => qrScanner.start(), 1000);
                        $('#camera-take').removeClass('lido');
                        return alertAtencao('ID da posição da prateleira não encontrada.')
                    };

                    // Verifiucar se ja existe lote na posicao escaneada
                    $.ajax({
                        url: '../../ajax/lote.php',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            action: 'buscarInfoPedido',
                            params: idNfItem
                        },
                        success: pedido => {
                            if (!pedido) {
                                $('#camera-take').removeClass('lido');
                                qrScanner.start();
                                return alertAtencao('Ocorreu um erro ao buscar cupom.')
                            };

                            if (pedido.error) {
                                $('#camera-take').removeClass('lido');
                                qrScanner.start();
                                return alertAtencao(pedido.error);
                            };

                            if (parseFloat(pedido.qtdretirada) > parseFloat(pedido.qtdestoque)) {
                                $('#camera-take').removeClass('lido');
                                qrScanner.start();
                                return alertAtencao('Quantidade a ser retirada maior que a do estoque.');
                            }

                            if (!pedido.idlotefracaomov) {
                                $('#camera-take').removeClass('lido');
                                qrScanner.start();
                                return alertAtencao('Posição do lote não encontrada.');
                            }

                            pedidoAtual = pedido;

                            montarElemento('varetirarcina', pedido);
                        },
                        error: err => {
                            alertAtencao('Ocorreu um erro ao buscar infromações do cupom.');
                            console.log(err);
                        }
                    }).error(err => {
                        console.log(err);
                        qrScanner.start();
                        alertAtencao('Ocorreu um erro ao solicitar informações do cupom.')
                    });
                }));
                break;
            case 'qrcode_realocar_produtos':
                inicializarScanner((idTagDim => {
                    qrScanner.stop();
                    $('#camera-take').addClass('lido');

                    if (!idTagDim) {
                        setTimeout(() => qrScanner.start(), 1000);
                        $('#camera-take').removeClass('lido');
                        return alertAtencao('ID da posição da prateleira não encontrada.')
                    };

                    // Pegar quantidade de produtos que serão retidos
                    if (!qtdAlocada) {
                        montarElemento('produtos_retidos');
                        return alertAtencao('Quantidade de produtos a serem retido inválida.');
                    }

                    // Reter produtos na posicao da tagdim
                    $.ajax({
                        url: '../../ajax/lote.php',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            action: 'alocarProdutos',
                            params: [pedidoAtual.idlotefracaomov, pedidoAtual.idlote, idTagDim, qtdAlocada]
                        },
                        success: loteFracaoMov => {
                            if (!loteFracaoMov) {
                                $('#camera-take').removeClass('lido');
                                qrScanner.start();
                                return alertAtencao('Ocorreu um erro ao tentar víncular lote à prateleira.')
                            };

                            if (loteFracaoMov.error) {
                                $('#camera-take').removeClass('lido');
                                qrScanner.start();
                                return alertAtencao(loteFracaoMov.error);
                            };

                            resetarVariaveis();
                            montarElemento('');
                            openModal('mensagem_de_sucesso');
                        },
                        error: err => {
                            alertAtencao('Ocorreu um erro ao buscar infromações do lote.');
                            console.log(err);
                        }
                    }).error(err => {
                        console.log(err);
                        qrScanner.start();
                        alertAtencao('Ocorreu um erro ao solicitar informações do lote.')
                    });
                }));
                break;

        }

        CB.oModal.modal('hide');
    }

    const listaABC = () => {
        const alfabeto = [0];
        for (let i = 65; i <= 90; i++) {
            alfabeto.push(String.fromCharCode(i));
        }
        return alfabeto;
    }

    const buscarLetraAlfabeto = (indice) => {
        return listaABC()[indice] ?? '-';
    }

    const listaDesabilitada = ['0', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z']
    const listaOcupada = ['A', 'G', 'K', 'M']
    const listaLivre = ['B', 'C', 'D', 'E', 'F', 'H', 'I', 'J', 'L', 'N', 'O', 'P']

    const valoresColuna = [{
            id: 1,
            titulo: '01',
            quantidade: 1,
        },
        {
            id: 2,
            titulo: '02',
            quantidade: 0,
        },
        {
            id: 3,
            titulo: '03',
            quantidade: 0,
        },
        {
            id: 4,
            titulo: '04',
            quantidade: 0,
        },
        {
            id: 5,
            titulo: '05',
            quantidade: 0,
        },
        {
            id: 6,
            titulo: '06',
            quantidade: 0,
        },
        {
            id: 7,
            titulo: '07',
            quantidade: 5,
        },
    ]


    const trocaDeTela = (id, param = '') => {
        switch (id) {
            case 'qrcodevacina':
                return montarQrCode();
            case 'qrcode_alocar_produtos':
                return montarQrCode();
            case 'qrcode_retirar_vacina':
            case 'qrcode_realocar_produtos':
                return montarQrCode();
            case 'guardar_vacina':
                // TELA GUARDAR VACINAS
                return GuardarVacinas(param);
            case 'varetirarcina':
                qtdAlocada = param.qtdestoque - param.qtdretirada;
                const onClick = parseFloat(param.qtdretirada) < parseFloat(param.qtdestoque) ? `montarElemento('retirar_produto_lote', ${qtdAlocada})` : "openModal('mensagem_de_sucesso')";

                // TELA RETIRAR VACINA
                return `<div class="panel panel-default">
                            <div class="panel-heading row bg-primary text-white py-4">
                                <span class="font-bold h4">Cupom</span>
                            </div>
                            <div class="col-12 d-flex flex-column bg-light p-4">
                                <div class="mb-4">
                                    <span class="text-secondary font-weight-normal">Nome do produto:</span>
                                    <h5 class="ml-2">
                                        ${param.descr}
                                    </h5>
                                </div>
                                <div class="mb-4">
                                    <span class="text-secondary font-weight-normal">Fórmula</span>
                                    <h5 class="ml-2">${param.rotulo}</h5>
                                </div>
                                <div class="w-100 d-flex mb-4">
                                    <div class="mr-5">
                                        <span class="text-secondary font-weight-normal">Partida interna:</span>
                                        <h5 class="ml-2">${param.partida}</h5>
                                    </div>
                                    <div>
                                        <span class="text-secondary font-weight-normal">Selo:</span>
                                        <h5 class="ml-2">${param.selo ?? '-'}</h5>
                                    </div>
                                </div>
                                <div class="w-100 d-flex mb-4">
                                    <div class="mr-5">
                                        <span class="text-primary font-weight-normal  text-primary">Quantidade a ser retirada</span>
                                        <h5 class="ml-2  text-primary">${param.qtdretirada}</h5>
                                    </div>
                                    <div>
                                        <span class="text-secondary font-weight-normal">Quantidade em estoque</span>
                                        <h5 class="ml-2">${param.qtdestoque ?? 0}</h5>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="w-100  d-flex flex-column mt-3">
                            <button onclick="${onClick}" class="col-12  btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                                RETIRAR</button>
                            <button onclick="montarElemento('')" class="col-12 bg-light text-gray btn-secondary-custom border-1 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                                CANCELAR</button>
                        </div>`

            case 'produtos_retidos':
                // TELA PRODUTOS RETIDOS
                return `<div class="panel panel-default">
                        <div class="panel-heading bg-primary row text-white py-4">
                            <span class="font-bold h4">Produto retidos</span>
                        </div>
                        <div id="opcoes-produtos-retem" class="col-12 d-flex flex-column bg-light p-4">
                            <li data-qtd="3" class="h4">Vacina Autógena - 03 Frascos </li>
                            <li data-qtd="30" class="h4">Vacina Comercial Viva - 30 Frascos </li>
                            <li data-qtd="11" class="h4">Vacina Comercial Inativada 500ml - 11 Frascos</li>
                            <li data-qtd="20" class="h4">Vacina Comercial Inativada 200ml - 20 Frascos</li>
                            <li data-qtd="10" class="h4">Diluente -  10  Frascos</li>
                            <li data-qtd="4" class="h4">Antígenos - 04 Frascos</li>
                        </div>
                    </div>
                    <div class="w-100  d-flex flex-column mt-3">
                        <button onClick="montarElemento('qrcode_alocar_produtos')" class="col-12 btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            <span class="icon-custom">
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g id="QrCodeScan" clip-path="url(#clip0_6403_63)">
                                        <path id="Vector" d="M0.538452 1.25C0.538452 1.05109 0.61747 0.860322 0.758122 0.71967C0.898774 0.579018 1.08954 0.5 1.28845 0.5H5.78845C5.98736 0.5 6.17813 0.579018 6.31878 0.71967C6.45943 0.860322 6.53845 1.05109 6.53845 1.25C6.53845 1.44891 6.45943 1.63968 6.31878 1.78033C6.17813 1.92098 5.98736 2 5.78845 2H2.03845V5.75C2.03845 5.94891 1.95943 6.13968 1.81878 6.28033C1.67813 6.42098 1.48736 6.5 1.28845 6.5C1.08954 6.5 0.898774 6.42098 0.758122 6.28033C0.61747 6.13968 0.538452 5.94891 0.538452 5.75V1.25ZM18.5385 1.25C18.5385 1.05109 18.6175 0.860322 18.7581 0.71967C18.8988 0.579018 19.0895 0.5 19.2885 0.5H23.7885C23.9874 0.5 24.1781 0.579018 24.3188 0.71967C24.4594 0.860322 24.5385 1.05109 24.5385 1.25V5.75C24.5385 5.94891 24.4594 6.13968 24.3188 6.28033C24.1781 6.42098 23.9874 6.5 23.7885 6.5C23.5895 6.5 23.3988 6.42098 23.2581 6.28033C23.1175 6.13968 23.0385 5.94891 23.0385 5.75V2H19.2885C19.0895 2 18.8988 1.92098 18.7581 1.78033C18.6175 1.63968 18.5385 1.44891 18.5385 1.25ZM1.28845 18.5C1.48736 18.5 1.67813 18.579 1.81878 18.7197C1.95943 18.8603 2.03845 19.0511 2.03845 19.25V23H5.78845C5.98736 23 6.17813 23.079 6.31878 23.2197C6.45943 23.3603 6.53845 23.5511 6.53845 23.75C6.53845 23.9489 6.45943 24.1397 6.31878 24.2803C6.17813 24.421 5.98736 24.5 5.78845 24.5H1.28845C1.08954 24.5 0.898774 24.421 0.758122 24.2803C0.61747 24.1397 0.538452 23.9489 0.538452 23.75V19.25C0.538452 19.0511 0.61747 18.8603 0.758122 18.7197C0.898774 18.579 1.08954 18.5 1.28845 18.5ZM23.7885 18.5C23.9874 18.5 24.1781 18.579 24.3188 18.7197C24.4594 18.8603 24.5385 19.0511 24.5385 19.25V23.75C24.5385 23.9489 24.4594 24.1397 24.3188 24.2803C24.1781 24.421 23.9874 24.5 23.7885 24.5H19.2885C19.0895 24.5 18.8988 24.421 18.7581 24.2803C18.6175 24.1397 18.5385 23.9489 18.5385 23.75C18.5385 23.5511 18.6175 23.3603 18.7581 23.2197C18.8988 23.079 19.0895 23 19.2885 23H23.0385V19.25C23.0385 19.0511 23.1175 18.8603 23.2581 18.7197C23.3988 18.579 23.5895 18.5 23.7885 18.5ZM6.53845 6.5H8.03845V8H6.53845V6.5Z" fill="white" />
                                        <path id="Vector_2" d="M11.0385 3.5H3.53845V11H11.0385V3.5ZM5.03845 5H9.53845V9.5H5.03845V5ZM8.03845 17H6.53845V18.5H8.03845V17Z" fill="white" />
                                        <path id="Vector_3" d="M11.0385 14H3.53845V21.5H11.0385V14ZM5.03845 15.5H9.53845V20H5.03845V15.5ZM17.0385 6.5H18.5385V8H17.0385V6.5Z" fill="white" />
                                        <path id="Vector_4" d="M14.0385 3.5H21.5385V11H14.0385V3.5ZM15.5385 5V9.5H20.0385V5H15.5385ZM12.5385 12.5V15.5H14.0385V17H12.5385V18.5H15.5385V15.5H17.0385V18.5H18.5385V17H21.5385V15.5H17.0385V12.5H12.5385ZM15.5385 15.5H14.0385V14H15.5385V15.5ZM21.5385 18.5H20.0385V20H17.0385V21.5H21.5385V18.5ZM15.5385 21.5V20H12.5385V21.5H15.5385Z" fill="white" />
                                        <path id="Vector_5" d="M18.5385 14H21.5385V12.5H18.5385V14Z" fill="white" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_6403_63">
                                            <rect width="24" height="24" fill="white" transform="translate(0.538452 0.5)" />
                                        </clipPath>
                                    </defs>
                                </svg>
                            </span>
                            <span>
                                ALOCAR PRODUTOS RETIDOS
                            </span>
                        </button>
                        <button onclick="montarElemento('visualizar_estoque', 'produtos_retidos')" class="col-12 bg-light text-gray btn-secondary-custom border-1 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            <span class="ml-5 icon-custom">
                                <svg width="22" height="21" viewBox="0 0 22 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.0384521 1.5C0.0384521 1.10218 0.196487 0.720644 0.477792 0.43934C0.759096 0.158035 1.14063 0 1.53845 0H4.53845C4.93628 0 5.31781 0.158035 5.59911 0.43934C5.88042 0.720644 6.03845 1.10218 6.03845 1.5V4.5C6.03845 4.89782 5.88042 5.27936 5.59911 5.56066C5.31781 5.84196 4.93628 6 4.53845 6H1.53845C1.14063 6 0.759096 5.84196 0.477792 5.56066C0.196487 5.27936 0.0384521 4.89782 0.0384521 4.5V1.5ZM7.53845 1.5C7.53845 1.10218 7.69649 0.720644 7.97779 0.43934C8.2591 0.158035 8.64063 0 9.03845 0H12.0385C12.4363 0 12.8178 0.158035 13.0991 0.43934C13.3804 0.720644 13.5385 1.10218 13.5385 1.5V4.5C13.5385 4.89782 13.3804 5.27936 13.0991 5.56066C12.8178 5.84196 12.4363 6 12.0385 6H9.03845C8.64063 6 8.2591 5.84196 7.97779 5.56066C7.69649 5.27936 7.53845 4.89782 7.53845 4.5V1.5ZM15.0385 1.5C15.0385 1.10218 15.1965 0.720644 15.4778 0.43934C15.7591 0.158035 16.1406 0 16.5385 0H19.5385C19.9363 0 20.3178 0.158035 20.5991 0.43934C20.8804 0.720644 21.0385 1.10218 21.0385 1.5V4.5C21.0385 4.89782 20.8804 5.27936 20.5991 5.56066C20.3178 5.84196 19.9363 6 19.5385 6H16.5385C16.1406 6 15.7591 5.84196 15.4778 5.56066C15.1965 5.27936 15.0385 4.89782 15.0385 4.5V1.5ZM0.0384521 9C0.0384521 8.60218 0.196487 8.22064 0.477792 7.93934C0.759096 7.65804 1.14063 7.5 1.53845 7.5H4.53845C4.93628 7.5 5.31781 7.65804 5.59911 7.93934C5.88042 8.22064 6.03845 8.60218 6.03845 9V12C6.03845 12.3978 5.88042 12.7794 5.59911 13.0607C5.31781 13.342 4.93628 13.5 4.53845 13.5H1.53845C1.14063 13.5 0.759096 13.342 0.477792 13.0607C0.196487 12.7794 0.0384521 12.3978 0.0384521 12V9ZM7.53845 9C7.53845 8.60218 7.69649 8.22064 7.97779 7.93934C8.2591 7.65804 8.64063 7.5 9.03845 7.5H12.0385C12.4363 7.5 12.8178 7.65804 13.0991 7.93934C13.3804 8.22064 13.5385 8.60218 13.5385 9V12C13.5385 12.3978 13.3804 12.7794 13.0991 13.0607C12.8178 13.342 12.4363 13.5 12.0385 13.5H9.03845C8.64063 13.5 8.2591 13.342 7.97779 13.0607C7.69649 12.7794 7.53845 12.3978 7.53845 12V9ZM15.0385 9C15.0385 8.60218 15.1965 8.22064 15.4778 7.93934C15.7591 7.65804 16.1406 7.5 16.5385 7.5H19.5385C19.9363 7.5 20.3178 7.65804 20.5991 7.93934C20.8804 8.22064 21.0385 8.60218 21.0385 9V12C21.0385 12.3978 20.8804 12.7794 20.5991 13.0607C20.3178 13.342 19.9363 13.5 19.5385 13.5H16.5385C16.1406 13.5 15.7591 13.342 15.4778 13.0607C15.1965 12.7794 15.0385 12.3978 15.0385 12V9ZM0.0384521 16.5C0.0384521 16.1022 0.196487 15.7206 0.477792 15.4393C0.759096 15.158 1.14063 15 1.53845 15H4.53845C4.93628 15 5.31781 15.158 5.59911 15.4393C5.88042 15.7206 6.03845 16.1022 6.03845 16.5V19.5C6.03845 19.8978 5.88042 20.2794 5.59911 20.5607C5.31781 20.842 4.93628 21 4.53845 21H1.53845C1.14063 21 0.759096 20.842 0.477792 20.5607C0.196487 20.2794 0.0384521 19.8978 0.0384521 19.5V16.5ZM7.53845 16.5C7.53845 16.1022 7.69649 15.7206 7.97779 15.4393C8.2591 15.158 8.64063 15 9.03845 15H12.0385C12.4363 15 12.8178 15.158 13.0991 15.4393C13.3804 15.7206 13.5385 16.1022 13.5385 16.5V19.5C13.5385 19.8978 13.3804 20.2794 13.0991 20.5607C12.8178 20.842 12.4363 21 12.0385 21H9.03845C8.64063 21 8.2591 20.842 7.97779 20.5607C7.69649 20.2794 7.53845 19.8978 7.53845 19.5V16.5ZM15.0385 16.5C15.0385 16.1022 15.1965 15.7206 15.4778 15.4393C15.7591 15.158 16.1406 15 16.5385 15H19.5385C19.9363 15 20.3178 15.158 20.5991 15.4393C20.8804 15.7206 21.0385 16.1022 21.0385 16.5V19.5C21.0385 19.8978 20.8804 20.2794 20.5991 20.5607C20.3178 20.842 19.9363 21 19.5385 21H16.5385C16.1406 21 15.7591 20.842 15.4778 20.5607C15.1965 20.2794 15.0385 19.8978 15.0385 19.5V16.5Z" fill="#808080" />
                                </svg>
                            </span>
                            VISUALIZAR ESTOQUE</button>
                    </div>`

            case 'alocar_produtos':
                return AlocarProdutos(param);
            case 'alocar_produtos_existentes':
                return AlocarProdutosExistente(param);
            case 'visualizar_estoque':
                return VisualizarEstoque(param);

            case 'procurar_espaco':
                idPrateleiraSelecionada = $('#select-tags').val();

                // ${listaDesabilitada.includes(item) ? "bg-disabled text-gray" : listaOcupada.includes(item) ? "bg-danger" : "text-blue"}
                $.ajax({
                    url: '../../ajax/lote.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        action: 'buscarTagDimEPosicoesPorIDtag',
                        params: idPrateleiraSelecionada
                    },
                    success: res => {
                        let prateleiraHTML = '';

                        if (!res) return alertAtencao('Ocorreu um erro ao buscar prateleira.');
                        if (res.error) return alertAtencao(res.error);

                        for (indiceColuna in res.colunas) {
                            let coluna = res.colunas[indiceColuna];
                            $(`#coluna-link-${coluna.label}`).removeClass('bg-disabled text-gray');

                            prateleiraHTML += `
                                <div
                                    id="coluna-${coluna.label}"
                                    class="w-100 text-center my-3 font-weight-bold bg-secondary d-flex align-items-center justify-content-center">
                                    <span class="text-secondary h5 font-weight-bold">
                                        COLUNA ${buscarLetraAlfabeto(coluna.label)}
                                    </span>
                                </div>
                                <div class="w-100 text-center d-flex flex-column my-2 font-weight-bold overflow-auto" style="height: 300px;">`;


                            for (indiceLinha in coluna.linhas) {
                                let linha = coluna.linhas[indiceLinha];

                                prateleiraHTML += `
                                    <div class="w-100 d-flex mb-3">
                                        <div class="mr-1 bg-secondary d-flex align-items-center justify-content-center p-4 col-xs-2">
                                            <span class="p-2 px-3 text-gray bg-white text-xl border-2">
                                                ${linha.label}
                                            </span>
                                        </div>
                                        <div class="d-flex flex-wrap col-xs-10">`;

                                for (indiceCaixa in linha.caixas) {
                                    let caixa = linha.caixas[indiceCaixa],
                                        loteFracaoQtd = parseFloat(caixa.lotefracaoqtd),
                                        parametro = {
                                            idtagdim: caixa.idtagdim,
                                            possuiLote: loteFracaoQtd > 0,
                                            coluna: coluna.label,
                                            linha: linha.label
                                        };

                                    if (loteFracaoQtd > 0) {
                                        $(`#coluna-link-${coluna.label}`).addClass('bg-danger');
                                    }

                                    prateleiraHTML += `
                                        ${
                                            loteFracaoQtd > 0 ?
                                                // Prateleira Ocupada 
                                                `<button
                                                        data-param='${JSON.stringify(parametro)}' 
                                                        onclick="openModal('view_lote', this.getAttribute('data-param'), true)" class="col-xs-3 col-md-2 bg-danger border-0 d-flex align-items-center justify-content-center p-4">
                                                    <div class="d-flex items-center align-items-center" style="position: relative;">
                                                        <span
                                                            class="p-3 bg-danger custom-border mb-4 d-flex align-items-center justify-content-center"
                                                            style="position: absolute; top: 7; margin-left: -14px; border-radius: 50%; height: 25px; width: 25px">
                                                        ${loteFracaoQtd}
                                                        </span>
                                                        <svg width="19" height="19" viewBox="0 0 19 19" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M0.539062 0.649414H18.5391V4.64941H0.539062V0.649414ZM1.53906 5.64941H17.5391V18.6494H1.53906V5.64941ZM7.03906 8.64941C6.90645 8.64941 6.77928 8.70209 6.68551 8.79586C6.59174 8.88963 6.53906 9.01681 6.53906 9.14941V10.6494H12.5391V9.14941C12.5391 9.01681 12.4864 8.88963 12.3926 8.79586C12.2988 8.70209 12.1717 8.64941 12.0391 8.64941H7.03906Z"
                                                                fill="#EDDF99" />
                                                        </svg>
                                                    </div>
                                                </button>`
                                            : // Prateleira vazia 
                                                `<button
                                                        data-param='${JSON.stringify(parametro)}' 
                                                        onclick="openModal('view_lote', this.getAttribute('data-param'))" class="col-xs-3 col-md-2 d-flex align-items-center justify-content-center bg-yellow-custom p-4" style="border: 2px solid whitesmoke;">
                                                    <div class="d-flex items-center align-items-center position-relative">
                                                        <span class="text-xl text-white">
                                                                ${caixa.label}
                                                        </span>
                                                    </div>
                                                </button>`
                                            }
                                        `;
                                }

                                prateleiraHTML += `</div>
                                                </div>`;
                            }

                            prateleiraHTML += `</div>`;
                        }

                        $('#conteudo-prateleira').html(prateleiraHTML);
                        renderizarSelectPicker();
                    },
                    error: err => {
                        console.log(err);
                        alertAtencao('Ocorreu um erro ao buscar prateleira');
                    }
                });

                let onClickBtn = "",
                    labelBtn = 'CANCELAR';

                switch(param) {
                    case 'produtos_retidos': 
                        onClickBtn = `montarElemento('produtos_retidos')`;
                        break;
                    case 'alocar_produtos':
                        onClickBtn = `montarElemento('alocar_produtos')`;
                        break;
                    default:
                        onClickBtn = "montarElemento('')";
                }

                if(param) labelBtn = 'VOLTAR';

                // TELA PROCURAR ESPAÇO
                return `<div class="col-12 d-flex flex-column">
                    <span class="font-weight-normal">Rua</span>
                    <select id="select-tags" class="col-12 mt-2 d-flex select-picker px-0" style="height: 40px;" data-live-search="true">
                        ${tagsCamaraFriaOptions}
                    </select>
                    <div class="d-flex flex-column mt-3">
                        <button onclick="montarElemento('procurar_espaco')"
                            class="col-12 bg-primary btn-primary-custom border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            PROCURAR</button>
                        <button
                            onclick="${onClickBtn}"
                            class="col-12 bg-light text-gray btn-secondary-custom border-1 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            ${labelBtn}
                        </button>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading bg-primary row text-white py-4">
                            <span class="font-bold h4">G8 ALMOXARIFADO NV RUA 4 - Locações</span>
                        </div>
                        <div class="col-12 d-flex flex-column bg-light p-4">
                            <span class="font-weight-normal text-gray">Colunas</span>
                                 <div class="w-100 d-flex flex-wrap">
                                    ${listaABC().map((item, indice) => (`<a id="coluna-link-${indice}" href="#coluna-${indice}" class="button-col-location bg-disabled text-gray mr-1 mb-2">${item}</a>`)).join(' ')}
                                </div>
                            <div class="w-100 d-flex align-items-center mt-3">
                                <select class="w-50 d-flex" style="height: 50px;">
                                    <option value="">4048 - IMPRESSORA ZEB</option>
                                    <option value="2">*G8 ALMOXARIFADO NV RUA 4</option>
                                    <option value="3">*G8 ALMOXARIFADO NV RUA 3</option>
                                    <option value="4">*G8 ALMOXARIFADO NV RUA 2</option>
                                    <option value="5">*G8 ALMOXARIFADO NV RUA 1</option>
                                </select>
                                <button"
                                    class="w-50 bg-primary border-0 font-bold d-flex align-items-center justify-content-center"
                                    style="height: 50px;">
                                    <span class="h5">IMPRESSÃO EM LOTE</span>
                                    </button>
                            </div>
                            <div id="conteudo-prateleira" class="d-flex p-0 flex-wrap col-xs-12 col-md-6 col-lg-4 col-xl-3"></div>
                        </div>
                    </div>
                </div>`

            case 'retirar_produto_lote':
                // TELA DE RETIRADA PRODUTO DO LOTE
                return `<div class="col-12 d-flex flex-col align-items-center justify-content-center" style="margin: auto;">
                    <h3 class="font-weight-bold">Possui <span class="text-danger">${param} item(ns)</span> restantes!</h3>
                    <h4>
                        Deseja guardar nas sobras?
                    </h4>
                    <div class="w-100  d-flex flex-column mt-3">
                        <button onclick="montarElemento('qrcode_realocar_produtos')"
                            class="col-12 btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            SIM</button>
                        <button onclick="retirarProduto()"
                            class="col-12 text-gray custom-border bg-white btn-secondary-custom border-1 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            NÃO</button>
                    </div>
                </div>`
        }
        // TELA INICIAL CAMERA FRIA
        resetarVariaveis();

        return `<div class="panel panel-default">
            <div class="panel-heading row">
                <span class="font-bold h5">Produto</span>
            </div>
            <div class="w-100 panel-body d-flex flex-column">
                <span class="text-secondary font-weight-light">Clique no botão e aponte a câmera para o QR CODE</span>
                <button onclick="montarElemento('qrcodevacina')" class="col-12 btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                    <span class="icon-custom">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="QrCodeScan" clip-path="url(#clip0_6403_63)">
                                <path id="Vector" d="M0.538452 1.25C0.538452 1.05109 0.61747 0.860322 0.758122 0.71967C0.898774 0.579018 1.08954 0.5 1.28845 0.5H5.78845C5.98736 0.5 6.17813 0.579018 6.31878 0.71967C6.45943 0.860322 6.53845 1.05109 6.53845 1.25C6.53845 1.44891 6.45943 1.63968 6.31878 1.78033C6.17813 1.92098 5.98736 2 5.78845 2H2.03845V5.75C2.03845 5.94891 1.95943 6.13968 1.81878 6.28033C1.67813 6.42098 1.48736 6.5 1.28845 6.5C1.08954 6.5 0.898774 6.42098 0.758122 6.28033C0.61747 6.13968 0.538452 5.94891 0.538452 5.75V1.25ZM18.5385 1.25C18.5385 1.05109 18.6175 0.860322 18.7581 0.71967C18.8988 0.579018 19.0895 0.5 19.2885 0.5H23.7885C23.9874 0.5 24.1781 0.579018 24.3188 0.71967C24.4594 0.860322 24.5385 1.05109 24.5385 1.25V5.75C24.5385 5.94891 24.4594 6.13968 24.3188 6.28033C24.1781 6.42098 23.9874 6.5 23.7885 6.5C23.5895 6.5 23.3988 6.42098 23.2581 6.28033C23.1175 6.13968 23.0385 5.94891 23.0385 5.75V2H19.2885C19.0895 2 18.8988 1.92098 18.7581 1.78033C18.6175 1.63968 18.5385 1.44891 18.5385 1.25ZM1.28845 18.5C1.48736 18.5 1.67813 18.579 1.81878 18.7197C1.95943 18.8603 2.03845 19.0511 2.03845 19.25V23H5.78845C5.98736 23 6.17813 23.079 6.31878 23.2197C6.45943 23.3603 6.53845 23.5511 6.53845 23.75C6.53845 23.9489 6.45943 24.1397 6.31878 24.2803C6.17813 24.421 5.98736 24.5 5.78845 24.5H1.28845C1.08954 24.5 0.898774 24.421 0.758122 24.2803C0.61747 24.1397 0.538452 23.9489 0.538452 23.75V19.25C0.538452 19.0511 0.61747 18.8603 0.758122 18.7197C0.898774 18.579 1.08954 18.5 1.28845 18.5ZM23.7885 18.5C23.9874 18.5 24.1781 18.579 24.3188 18.7197C24.4594 18.8603 24.5385 19.0511 24.5385 19.25V23.75C24.5385 23.9489 24.4594 24.1397 24.3188 24.2803C24.1781 24.421 23.9874 24.5 23.7885 24.5H19.2885C19.0895 24.5 18.8988 24.421 18.7581 24.2803C18.6175 24.1397 18.5385 23.9489 18.5385 23.75C18.5385 23.5511 18.6175 23.3603 18.7581 23.2197C18.8988 23.079 19.0895 23 19.2885 23H23.0385V19.25C23.0385 19.0511 23.1175 18.8603 23.2581 18.7197C23.3988 18.579 23.5895 18.5 23.7885 18.5ZM6.53845 6.5H8.03845V8H6.53845V6.5Z" fill="white" />
                                <path id="Vector_2" d="M11.0385 3.5H3.53845V11H11.0385V3.5ZM5.03845 5H9.53845V9.5H5.03845V5ZM8.03845 17H6.53845V18.5H8.03845V17Z" fill="white" />
                                <path id="Vector_3" d="M11.0385 14H3.53845V21.5H11.0385V14ZM5.03845 15.5H9.53845V20H5.03845V15.5ZM17.0385 6.5H18.5385V8H17.0385V6.5Z" fill="white" />
                                <path id="Vector_4" d="M14.0385 3.5H21.5385V11H14.0385V3.5ZM15.5385 5V9.5H20.0385V5H15.5385ZM12.5385 12.5V15.5H14.0385V17H12.5385V18.5H15.5385V15.5H17.0385V18.5H18.5385V17H21.5385V15.5H17.0385V12.5H12.5385ZM15.5385 15.5H14.0385V14H15.5385V15.5ZM21.5385 18.5H20.0385V20H17.0385V21.5H21.5385V18.5ZM15.5385 21.5V20H12.5385V21.5H15.5385Z" fill="white" />
                                <path id="Vector_5" d="M18.5385 14H21.5385V12.5H18.5385V14Z" fill="white" />
                            </g>
                            <defs>
                                <clipPath id="clip0_6403_63">
                                    <rect width="24" height="24" fill="white" transform="translate(0.538452 0.5)" />
                                </clipPath>
                            </defs>
                        </svg>
                    </span>
                    <span>
                        GUARDAR VACINA
                    </span>
                </button>
                <button onclick="montarElemento('qrcode_retirar_vacina')" class="col-12  btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                    <span class="icon-custom">
                        <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g id="QrCodeScan" clip-path="url(#clip0_6403_63)">
                                <path id="Vector" d="M0.538452 1.25C0.538452 1.05109 0.61747 0.860322 0.758122 0.71967C0.898774 0.579018 1.08954 0.5 1.28845 0.5H5.78845C5.98736 0.5 6.17813 0.579018 6.31878 0.71967C6.45943 0.860322 6.53845 1.05109 6.53845 1.25C6.53845 1.44891 6.45943 1.63968 6.31878 1.78033C6.17813 1.92098 5.98736 2 5.78845 2H2.03845V5.75C2.03845 5.94891 1.95943 6.13968 1.81878 6.28033C1.67813 6.42098 1.48736 6.5 1.28845 6.5C1.08954 6.5 0.898774 6.42098 0.758122 6.28033C0.61747 6.13968 0.538452 5.94891 0.538452 5.75V1.25ZM18.5385 1.25C18.5385 1.05109 18.6175 0.860322 18.7581 0.71967C18.8988 0.579018 19.0895 0.5 19.2885 0.5H23.7885C23.9874 0.5 24.1781 0.579018 24.3188 0.71967C24.4594 0.860322 24.5385 1.05109 24.5385 1.25V5.75C24.5385 5.94891 24.4594 6.13968 24.3188 6.28033C24.1781 6.42098 23.9874 6.5 23.7885 6.5C23.5895 6.5 23.3988 6.42098 23.2581 6.28033C23.1175 6.13968 23.0385 5.94891 23.0385 5.75V2H19.2885C19.0895 2 18.8988 1.92098 18.7581 1.78033C18.6175 1.63968 18.5385 1.44891 18.5385 1.25ZM1.28845 18.5C1.48736 18.5 1.67813 18.579 1.81878 18.7197C1.95943 18.8603 2.03845 19.0511 2.03845 19.25V23H5.78845C5.98736 23 6.17813 23.079 6.31878 23.2197C6.45943 23.3603 6.53845 23.5511 6.53845 23.75C6.53845 23.9489 6.45943 24.1397 6.31878 24.2803C6.17813 24.421 5.98736 24.5 5.78845 24.5H1.28845C1.08954 24.5 0.898774 24.421 0.758122 24.2803C0.61747 24.1397 0.538452 23.9489 0.538452 23.75V19.25C0.538452 19.0511 0.61747 18.8603 0.758122 18.7197C0.898774 18.579 1.08954 18.5 1.28845 18.5ZM23.7885 18.5C23.9874 18.5 24.1781 18.579 24.3188 18.7197C24.4594 18.8603 24.5385 19.0511 24.5385 19.25V23.75C24.5385 23.9489 24.4594 24.1397 24.3188 24.2803C24.1781 24.421 23.9874 24.5 23.7885 24.5H19.2885C19.0895 24.5 18.8988 24.421 18.7581 24.2803C18.6175 24.1397 18.5385 23.9489 18.5385 23.75C18.5385 23.5511 18.6175 23.3603 18.7581 23.2197C18.8988 23.079 19.0895 23 19.2885 23H23.0385V19.25C23.0385 19.0511 23.1175 18.8603 23.2581 18.7197C23.3988 18.579 23.5895 18.5 23.7885 18.5ZM6.53845 6.5H8.03845V8H6.53845V6.5Z" fill="white" />
                                <path id="Vector_2" d="M11.0385 3.5H3.53845V11H11.0385V3.5ZM5.03845 5H9.53845V9.5H5.03845V5ZM8.03845 17H6.53845V18.5H8.03845V17Z" fill="white" />
                                <path id="Vector_3" d="M11.0385 14H3.53845V21.5H11.0385V14ZM5.03845 15.5H9.53845V20H5.03845V15.5ZM17.0385 6.5H18.5385V8H17.0385V6.5Z" fill="white" />
                                <path id="Vector_4" d="M14.0385 3.5H21.5385V11H14.0385V3.5ZM15.5385 5V9.5H20.0385V5H15.5385ZM12.5385 12.5V15.5H14.0385V17H12.5385V18.5H15.5385V15.5H17.0385V18.5H18.5385V17H21.5385V15.5H17.0385V12.5H12.5385ZM15.5385 15.5H14.0385V14H15.5385V15.5ZM21.5385 18.5H20.0385V20H17.0385V21.5H21.5385V18.5ZM15.5385 21.5V20H12.5385V21.5H15.5385Z" fill="white" />
                                <path id="Vector_5" d="M18.5385 14H21.5385V12.5H18.5385V14Z" fill="white" />
                            </g>
                            <defs>
                                <clipPath id="clip0_6403_63">
                                    <rect width="24" height="24" fill="white" transform="translate(0.538452 0.5)" />
                                </clipPath>
                            </defs>
                        </svg>
                    </span>
                    <span class="mr-3">RETIRAR VACINA</span></button>
                <button onclick="montarElemento('visualizar_estoque')" class="col-12 bg-light text-gray btn-secondary-custom border-1 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                    <span class="ml-5 icon-custom">
                        <svg width="22" height="21" viewBox="0 0 22 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.0384521 1.5C0.0384521 1.10218 0.196487 0.720644 0.477792 0.43934C0.759096 0.158035 1.14063 0 1.53845 0H4.53845C4.93628 0 5.31781 0.158035 5.59911 0.43934C5.88042 0.720644 6.03845 1.10218 6.03845 1.5V4.5C6.03845 4.89782 5.88042 5.27936 5.59911 5.56066C5.31781 5.84196 4.93628 6 4.53845 6H1.53845C1.14063 6 0.759096 5.84196 0.477792 5.56066C0.196487 5.27936 0.0384521 4.89782 0.0384521 4.5V1.5ZM7.53845 1.5C7.53845 1.10218 7.69649 0.720644 7.97779 0.43934C8.2591 0.158035 8.64063 0 9.03845 0H12.0385C12.4363 0 12.8178 0.158035 13.0991 0.43934C13.3804 0.720644 13.5385 1.10218 13.5385 1.5V4.5C13.5385 4.89782 13.3804 5.27936 13.0991 5.56066C12.8178 5.84196 12.4363 6 12.0385 6H9.03845C8.64063 6 8.2591 5.84196 7.97779 5.56066C7.69649 5.27936 7.53845 4.89782 7.53845 4.5V1.5ZM15.0385 1.5C15.0385 1.10218 15.1965 0.720644 15.4778 0.43934C15.7591 0.158035 16.1406 0 16.5385 0H19.5385C19.9363 0 20.3178 0.158035 20.5991 0.43934C20.8804 0.720644 21.0385 1.10218 21.0385 1.5V4.5C21.0385 4.89782 20.8804 5.27936 20.5991 5.56066C20.3178 5.84196 19.9363 6 19.5385 6H16.5385C16.1406 6 15.7591 5.84196 15.4778 5.56066C15.1965 5.27936 15.0385 4.89782 15.0385 4.5V1.5ZM0.0384521 9C0.0384521 8.60218 0.196487 8.22064 0.477792 7.93934C0.759096 7.65804 1.14063 7.5 1.53845 7.5H4.53845C4.93628 7.5 5.31781 7.65804 5.59911 7.93934C5.88042 8.22064 6.03845 8.60218 6.03845 9V12C6.03845 12.3978 5.88042 12.7794 5.59911 13.0607C5.31781 13.342 4.93628 13.5 4.53845 13.5H1.53845C1.14063 13.5 0.759096 13.342 0.477792 13.0607C0.196487 12.7794 0.0384521 12.3978 0.0384521 12V9ZM7.53845 9C7.53845 8.60218 7.69649 8.22064 7.97779 7.93934C8.2591 7.65804 8.64063 7.5 9.03845 7.5H12.0385C12.4363 7.5 12.8178 7.65804 13.0991 7.93934C13.3804 8.22064 13.5385 8.60218 13.5385 9V12C13.5385 12.3978 13.3804 12.7794 13.0991 13.0607C12.8178 13.342 12.4363 13.5 12.0385 13.5H9.03845C8.64063 13.5 8.2591 13.342 7.97779 13.0607C7.69649 12.7794 7.53845 12.3978 7.53845 12V9ZM15.0385 9C15.0385 8.60218 15.1965 8.22064 15.4778 7.93934C15.7591 7.65804 16.1406 7.5 16.5385 7.5H19.5385C19.9363 7.5 20.3178 7.65804 20.5991 7.93934C20.8804 8.22064 21.0385 8.60218 21.0385 9V12C21.0385 12.3978 20.8804 12.7794 20.5991 13.0607C20.3178 13.342 19.9363 13.5 19.5385 13.5H16.5385C16.1406 13.5 15.7591 13.342 15.4778 13.0607C15.1965 12.7794 15.0385 12.3978 15.0385 12V9ZM0.0384521 16.5C0.0384521 16.1022 0.196487 15.7206 0.477792 15.4393C0.759096 15.158 1.14063 15 1.53845 15H4.53845C4.93628 15 5.31781 15.158 5.59911 15.4393C5.88042 15.7206 6.03845 16.1022 6.03845 16.5V19.5C6.03845 19.8978 5.88042 20.2794 5.59911 20.5607C5.31781 20.842 4.93628 21 4.53845 21H1.53845C1.14063 21 0.759096 20.842 0.477792 20.5607C0.196487 20.2794 0.0384521 19.8978 0.0384521 19.5V16.5ZM7.53845 16.5C7.53845 16.1022 7.69649 15.7206 7.97779 15.4393C8.2591 15.158 8.64063 15 9.03845 15H12.0385C12.4363 15 12.8178 15.158 13.0991 15.4393C13.3804 15.7206 13.5385 16.1022 13.5385 16.5V19.5C13.5385 19.8978 13.3804 20.2794 13.0991 20.5607C12.8178 20.842 12.4363 21 12.0385 21H9.03845C8.64063 21 8.2591 20.842 7.97779 20.5607C7.69649 20.2794 7.53845 19.8978 7.53845 19.5V16.5ZM15.0385 16.5C15.0385 16.1022 15.1965 15.7206 15.4778 15.4393C15.7591 15.158 16.1406 15 16.5385 15H19.5385C19.9363 15 20.3178 15.158 20.5991 15.4393C20.8804 15.7206 21.0385 16.1022 21.0385 16.5V19.5C21.0385 19.8978 20.8804 20.2794 20.5991 20.5607C20.3178 20.842 19.9363 21 19.5385 21H16.5385C16.1406 21 15.7591 20.842 15.4778 20.5607C15.1965 20.2794 15.0385 19.8978 15.0385 19.5V16.5Z" fill="#808080" />
                        </svg>
                    </span>
                    VISUALIZAR ESTOQUE
                </button>
            </div>
        </div>
    `;
    }

    function resetarVariaveis() {
        loteAtual = '',
        tagsCamaraFriaOptions = '',
        idPrateleiraSelecionada = '',
        qtdRetem = 0,
        qtdAlocada = 0,
        qtdAlocacaoAtingida = false,
        idTagDimAtual = false,
        pedidoAtual = false;
    }

    function retirarProduto() {
        $.ajax({
            url: '../../ajax/lote.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'retirarProduto',
                params: [pedidoAtual.idlotefracaomov, pedidoAtual.qtdretirada]
            },
            success: res => {
                if (!res) return alertAtencao('Ocorreu um erro ao tentar dar baixa.');
                if (res.error) return alertAtencao(res.error);

                montarElemento();
                openModal('mensagem_de_sucesso');
            },
            error: err => {
                console.log(err);
                alertAtencao('Ocorreu um erro ao tentar dar baixa.');
            }
        })
    }

    function buscarLotesPrateleira() {
        let qtd = qtdAlocada > 0 ? qtdAlocada : qtdRetem;

        // Reter produtos na posicao da tagdim
        $.ajax({
            url: '../../ajax/lote.php',
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'reterLote',
                params: [loteAtual.idlote, idTagDimAtual, qtd, qtdAlocada]
            },
            success: loteFracaoMov => {
                if (!loteFracaoMov) {
                    $('#camera-take').removeClass('lido');
                    qrScanner.start();
                    return alertAtencao('Ocorreu um erro ao tentar víncular lote à prateleira.')
                };

                if (loteFracaoMov.error) {
                    $('#camera-take').removeClass('lido');
                    qrScanner.start();
                    return alertAtencao(loteFracaoMov.error);
                };

                qtdAlocacaoAtingida = loteFracaoMov.qtdAtingida;
                quantidadeDisponivelLote = parseFloat(loteFracaoMov.quantidadeDisponivelLote ? loteFracaoMov.quantidadeDisponivelLote : (!quantidadeDisponivelLote ? loteFracaoMov.quantidadeDisponivelLote : quantidadeDisponivelLote));
                loteFracaoMovAtual = loteFracaoMov.loteFracaoMov;

                montarElemento('alocar_produtos', loteFracaoMov.loteFracaoMov);
            },
            error: err => {
                alertAtencao('Ocorreu um erro ao buscar infromações do lote.');
                console.log(err);
            }
        }).error(err => {
            console.log(err);
            qrScanner.start();
            alertAtencao('Ocorreu um erro ao solicitar informações do lote.')
        });
    }

    function AlocarProdutos(loteFracaoMov) {
        if(!loteFracaoMov) loteFracaoMov = loteFracaoMovAtual;

        const posicoes = Array.from(loteFracaoMov).map(pos => (
            `<tr class="col-12 ${pos.retem == 'Y' ? 'bg-success' : 'col-table-gray text-secondary'}">
                <td class="col-2 colTable font-weight-light py-4">${formatarData(pos.criadoem)}</td>
                <td class="col-2 colTable font-weight-light">${pos.qtd}</td>
                <td class="col-8 colTable font-weight-light">${pos.descricao}</td>
            </tr>`
        )).join(' ');

        const btnAcao = qtdAlocacaoAtingida ?
            `   <button onclick="alteraStatusAtiv(${loteAtual.idlote}, 'CONCLUIDO', 'N', true)"
                        class="col-12 btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                        <span>
                            CONCLUIR
                        </span>
                    </button>` :
            `
                    <button onclick="openModal('alocar_produtos')"
                        class="col-12 btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                        <span class="icon-custom">
                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g id="QrCodeScan" clip-path="url(#clip0_6403_63)">
                                    <path id="Vector"
                                        d="M0.538452 1.25C0.538452 1.05109 0.61747 0.860322 0.758122 0.71967C0.898774 0.579018 1.08954 0.5 1.28845 0.5H5.78845C5.98736 0.5 6.17813 0.579018 6.31878 0.71967C6.45943 0.860322 6.53845 1.05109 6.53845 1.25C6.53845 1.44891 6.45943 1.63968 6.31878 1.78033C6.17813 1.92098 5.98736 2 5.78845 2H2.03845V5.75C2.03845 5.94891 1.95943 6.13968 1.81878 6.28033C1.67813 6.42098 1.48736 6.5 1.28845 6.5C1.08954 6.5 0.898774 6.42098 0.758122 6.28033C0.61747 6.13968 0.538452 5.94891 0.538452 5.75V1.25ZM18.5385 1.25C18.5385 1.05109 18.6175 0.860322 18.7581 0.71967C18.8988 0.579018 19.0895 0.5 19.2885 0.5H23.7885C23.9874 0.5 24.1781 0.579018 24.3188 0.71967C24.4594 0.860322 24.5385 1.05109 24.5385 1.25V5.75C24.5385 5.94891 24.4594 6.13968 24.3188 6.28033C24.1781 6.42098 23.9874 6.5 23.7885 6.5C23.5895 6.5 23.3988 6.42098 23.2581 6.28033C23.1175 6.13968 23.0385 5.94891 23.0385 5.75V2H19.2885C19.0895 2 18.8988 1.92098 18.7581 1.78033C18.6175 1.63968 18.5385 1.44891 18.5385 1.25ZM1.28845 18.5C1.48736 18.5 1.67813 18.579 1.81878 18.7197C1.95943 18.8603 2.03845 19.0511 2.03845 19.25V23H5.78845C5.98736 23 6.17813 23.079 6.31878 23.2197C6.45943 23.3603 6.53845 23.5511 6.53845 23.75C6.53845 23.9489 6.45943 24.1397 6.31878 24.2803C6.17813 24.421 5.98736 24.5 5.78845 24.5H1.28845C1.08954 24.5 0.898774 24.421 0.758122 24.2803C0.61747 24.1397 0.538452 23.9489 0.538452 23.75V19.25C0.538452 19.0511 0.61747 18.8603 0.758122 18.7197C0.898774 18.579 1.08954 18.5 1.28845 18.5ZM23.7885 18.5C23.9874 18.5 24.1781 18.579 24.3188 18.7197C24.4594 18.8603 24.5385 19.0511 24.5385 19.25V23.75C24.5385 23.9489 24.4594 24.1397 24.3188 24.2803C24.1781 24.421 23.9874 24.5 23.7885 24.5H19.2885C19.0895 24.5 18.8988 24.421 18.7581 24.2803C18.6175 24.1397 18.5385 23.9489 18.5385 23.75C18.5385 23.5511 18.6175 23.3603 18.7581 23.2197C18.8988 23.079 19.0895 23 19.2885 23H23.0385V19.25C23.0385 19.0511 23.1175 18.8603 23.2581 18.7197C23.3988 18.579 23.5895 18.5 23.7885 18.5ZM6.53845 6.5H8.03845V8H6.53845V6.5Z"
                                        fill="white" />
                                    <path id="Vector_2"
                                        d="M11.0385 3.5H3.53845V11H11.0385V3.5ZM5.03845 5H9.53845V9.5H5.03845V5ZM8.03845 17H6.53845V18.5H8.03845V17Z"
                                        fill="white" />
                                    <path id="Vector_3"
                                        d="M11.0385 14H3.53845V21.5H11.0385V14ZM5.03845 15.5H9.53845V20H5.03845V15.5ZM17.0385 6.5H18.5385V8H17.0385V6.5Z"
                                        fill="white" />
                                    <path id="Vector_4"
                                        d="M14.0385 3.5H21.5385V11H14.0385V3.5ZM15.5385 5V9.5H20.0385V5H15.5385ZM12.5385 12.5V15.5H14.0385V17H12.5385V18.5H15.5385V15.5H17.0385V18.5H18.5385V17H21.5385V15.5H17.0385V12.5H12.5385ZM15.5385 15.5H14.0385V14H15.5385V15.5ZM21.5385 18.5H20.0385V20H17.0385V21.5H21.5385V18.5ZM15.5385 21.5V20H12.5385V21.5H15.5385Z"
                                        fill="white" />
                                    <path id="Vector_5" d="M18.5385 14H21.5385V12.5H18.5385V14Z" fill="white" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_6403_63">
                                        <rect width="24" height="24" fill="white" transform="translate(0.538452 0.5)" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </span>
                        <span>
                            ALOCAR PRODUTOS
                        </span>
                    </button>`

        // ALOCAR PRODUTOS
        return `<div class="panel panel-default">
                    <div class="panel-heading row bg-primary text-white py-4">
                        <span class="font-bold h4">Local</span>
                    </div>
                    <div class="col-12 d-flex flex-column bg-light">
                        <strong class="h5 font-bold">Legenda</strong>
                        <div class="d-flex align-items-center mb-2">
                            <span class="col-1 p-4 bg-success"></span>
                            <span class="ml-2 h5">Produtos retidos</span>
                            <span class="col-1 ml-4 p-4" style="background: #DADADA;"></span>
                            <span class="ml-2 h5">Produtos alocados</span>
                        </div>
                        <table class="row mt-1">
                            <thead class="col-12 custom-border mb-2">
                                <th class="col-2 font-bold">Data</th>
                                <th class="col-2 font-bold">Qtd</th>
                                <th class="col-8 font-bold text-center">Local</th>
                            </thead>
                            <td class="py-2"></td>
                            <tbody>
                                ${posicoes}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="w-100  d-flex flex-column mt-3">
                    ${btnAcao}
                    <button onclick="montarElemento('visualizar_estoque', 'alocar_produtos')"
                        class="col-12 bg-light btn-secondary-custom border-1 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                        <span class="ml-4 icon-custom">
                            <svg width="22" height="21" viewBox="0 0 22 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M0.0384521 1.5C0.0384521 1.10218 0.196487 0.720644 0.477792 0.43934C0.759096 0.158035 1.14063 0 1.53845 0H4.53845C4.93628 0 5.31781 0.158035 5.59911 0.43934C5.88042 0.720644 6.03845 1.10218 6.03845 1.5V4.5C6.03845 4.89782 5.88042 5.27936 5.59911 5.56066C5.31781 5.84196 4.93628 6 4.53845 6H1.53845C1.14063 6 0.759096 5.84196 0.477792 5.56066C0.196487 5.27936 0.0384521 4.89782 0.0384521 4.5V1.5ZM7.53845 1.5C7.53845 1.10218 7.69649 0.720644 7.97779 0.43934C8.2591 0.158035 8.64063 0 9.03845 0H12.0385C12.4363 0 12.8178 0.158035 13.0991 0.43934C13.3804 0.720644 13.5385 1.10218 13.5385 1.5V4.5C13.5385 4.89782 13.3804 5.27936 13.0991 5.56066C12.8178 5.84196 12.4363 6 12.0385 6H9.03845C8.64063 6 8.2591 5.84196 7.97779 5.56066C7.69649 5.27936 7.53845 4.89782 7.53845 4.5V1.5ZM15.0385 1.5C15.0385 1.10218 15.1965 0.720644 15.4778 0.43934C15.7591 0.158035 16.1406 0 16.5385 0H19.5385C19.9363 0 20.3178 0.158035 20.5991 0.43934C20.8804 0.720644 21.0385 1.10218 21.0385 1.5V4.5C21.0385 4.89782 20.8804 5.27936 20.5991 5.56066C20.3178 5.84196 19.9363 6 19.5385 6H16.5385C16.1406 6 15.7591 5.84196 15.4778 5.56066C15.1965 5.27936 15.0385 4.89782 15.0385 4.5V1.5ZM0.0384521 9C0.0384521 8.60218 0.196487 8.22064 0.477792 7.93934C0.759096 7.65804 1.14063 7.5 1.53845 7.5H4.53845C4.93628 7.5 5.31781 7.65804 5.59911 7.93934C5.88042 8.22064 6.03845 8.60218 6.03845 9V12C6.03845 12.3978 5.88042 12.7794 5.59911 13.0607C5.31781 13.342 4.93628 13.5 4.53845 13.5H1.53845C1.14063 13.5 0.759096 13.342 0.477792 13.0607C0.196487 12.7794 0.0384521 12.3978 0.0384521 12V9ZM7.53845 9C7.53845 8.60218 7.69649 8.22064 7.97779 7.93934C8.2591 7.65804 8.64063 7.5 9.03845 7.5H12.0385C12.4363 7.5 12.8178 7.65804 13.0991 7.93934C13.3804 8.22064 13.5385 8.60218 13.5385 9V12C13.5385 12.3978 13.3804 12.7794 13.0991 13.0607C12.8178 13.342 12.4363 13.5 12.0385 13.5H9.03845C8.64063 13.5 8.2591 13.342 7.97779 13.0607C7.69649 12.7794 7.53845 12.3978 7.53845 12V9ZM15.0385 9C15.0385 8.60218 15.1965 8.22064 15.4778 7.93934C15.7591 7.65804 16.1406 7.5 16.5385 7.5H19.5385C19.9363 7.5 20.3178 7.65804 20.5991 7.93934C20.8804 8.22064 21.0385 8.60218 21.0385 9V12C21.0385 12.3978 20.8804 12.7794 20.5991 13.0607C20.3178 13.342 19.9363 13.5 19.5385 13.5H16.5385C16.1406 13.5 15.7591 13.342 15.4778 13.0607C15.1965 12.7794 15.0385 12.3978 15.0385 12V9ZM0.0384521 16.5C0.0384521 16.1022 0.196487 15.7206 0.477792 15.4393C0.759096 15.158 1.14063 15 1.53845 15H4.53845C4.93628 15 5.31781 15.158 5.59911 15.4393C5.88042 15.7206 6.03845 16.1022 6.03845 16.5V19.5C6.03845 19.8978 5.88042 20.2794 5.59911 20.5607C5.31781 20.842 4.93628 21 4.53845 21H1.53845C1.14063 21 0.759096 20.842 0.477792 20.5607C0.196487 20.2794 0.0384521 19.8978 0.0384521 19.5V16.5ZM7.53845 16.5C7.53845 16.1022 7.69649 15.7206 7.97779 15.4393C8.2591 15.158 8.64063 15 9.03845 15H12.0385C12.4363 15 12.8178 15.158 13.0991 15.4393C13.3804 15.7206 13.5385 16.1022 13.5385 16.5V19.5C13.5385 19.8978 13.3804 20.2794 13.0991 20.5607C12.8178 20.842 12.4363 21 12.0385 21H9.03845C8.64063 21 8.2591 20.842 7.97779 20.5607C7.69649 20.2794 7.53845 19.8978 7.53845 19.5V16.5ZM15.0385 16.5C15.0385 16.1022 15.1965 15.7206 15.4778 15.4393C15.7591 15.158 16.1406 15 16.5385 15H19.5385C19.9363 15 20.3178 15.158 20.5991 15.4393C20.8804 15.7206 21.0385 16.1022 21.0385 16.5V19.5C21.0385 19.8978 20.8804 20.2794 20.5991 20.5607C20.3178 20.842 19.9363 21 19.5385 21H16.5385C16.1406 21 15.7591 20.842 15.4778 20.5607C15.1965 20.2794 15.0385 19.8978 15.0385 19.5V16.5Z"
                                    fill="#808080" />
                            </svg>
                        </span>
                        VISUALIZAR ESTOQUE
                    </button>
                </div>`;
    }


    function AlocarProdutosExistente(loteFracaoMov) {
        const posicoes = Array.from(loteFracaoMov).map(pos => (
            `<tr class="col-12 col-table-gray text-secondary">
                <td class="col-2 colTable font-weight-light py-4">${pos.qtd}</td>
                <td class="col-2 colTable font-weight-light">${pos.partida}</td>
                <td class="col-8 colTable font-weight-light">${pos.plantel}</td>
            </tr>`
        )).join(' ');

        // ALOCAR PRODUTOS
        return `<div class="panel panel-default">
                    <div class="panel-heading row bg-primary text-white py-4">
                        <span class="font-bold h4">Local</span>
                    </div>
                    <div class="col-12 d-flex flex-column bg-light align-items-center">
                        <h4>Este recipiente já possui os seguintes produtos: </h4>
                        <table class="row mt-1">
                            <thead class="col-12 custom-border mb-2">
                                <th class="col-2 font-bold">Qtd</th>
                                <th class="col-2 font-bold">Partida</th>
                                <th class="col-8 font-bold text-center">Espécie</th>
                            </thead>
                            <td class="py-2"></td>
                            <tbody>
                                ${posicoes}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="w-100  d-flex flex-column mt-3">
                    <button onclick="buscarLotesPrateleira()"
                            class="col-12 btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            CONFIRMAR ALOCAÇÃO
                    </button>
                    <button
                        onclick="montarElemento('qrcode_alocar_produtos')"
                        class="col-12 bg-light btn-secondary-custom border-1 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                       CANCELAR
                    </button>
                </div>`;
    }

    function VisualizarEstoque(paginaAnterior) {
        let options = `<option value="">Selecione a rua</option>`;

        $.ajax({
            url: '../../ajax/tag.php',
            method: 'GET',
            dataType: 'json',
            data: {
                action: 'buscarPrateleiras',
                params: [loteAtual.idempresa, loteAtual.idunidade]
            },
            success: res => {
                if (!res) return alertAtencao('Camarâs frias não encontradas.');
                if (res.error) return alertAtencao(res.error);

                options += res.map(item => (`<option value="${item.idtag}">${item.descricao}</option>`)).join(' ');
                $('#select-tags').html(options)

                tagsCamaraFriaOptions = options;

                renderizarSelectPicker()
            },
            error: err => {
                console.log(err);
                alertAtencao('Ocorreu um erro ao tentar visualizar estoque.');
            }
        });

        let onClickBtn = "",
            labelBtn = 'CANCELAR';

        switch(paginaAnterior) {
            case 'produtos_retidos': 
                onClickBtn = `montarElemento('produtos_retidos')`;
                break;
            case 'alocar_produtos':
                onClickBtn = `montarElemento('alocar_produtos')`;
                break;
            default:
                onClickBtn = "montarElemento('')";
        }

        if(paginaAnterior) labelBtn = 'VOLTAR';

        // - CSC -Logística(Estoque)
        // TELA VISUALIZAR ESTOQUE
        return `<div class="col-12 d-flex flex-column">
                    <strong>Local</strong>
                    <select id="select-tags" class="col-12 mt-2 d-flex select-picker px-0" style="height: 40px;" data-live-search="true">
                        <option value="">Carregando</option>
                    </select>
                    <div class="w-100  d-flex flex-column mt-3">
                        <button onclick="montarElemento('procurar_espaco', '${paginaAnterior}')"
                            class="col-12 bg-primary btn-primary-custom border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            PROCURAR</button>
                        <button
                            onclick="${onClickBtn}"
                            class="col-12 bg-light text-gray btn-secondary-custom border-1 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            ${labelBtn}
                        </button>
                    </div>
                </div>`;
    }

    function openModal(id = '', param = '') {
        function propriedadesModal() {
            switch (id) {
                case 'confirm':
                    return {
                        titulo: 'Verificação',
                            corpo: ` <div class="w-100">
                                        <h4 class="text-center mb-4">A quantidade recebida está igual a quantidade do sistema?</h4>
                                        <div class="w-100  d-flex flex-column mt-3">
                                            <button
                                                onclick="alteraStatusAtiv(${param}, 'PROCESSANDO', 'N')"
                                                class="col-12  btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                                                SIM
                                            </button>
                                            <button
                                                    id="btn-devolver-lote"
                                                    data-idlote="${param}"
                                                    class="col-12  btn-props-custom btn-danger border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                                                NÃO
                                            </button>
                                        </div>
                 `
                    }
                case 'alocar_produtos':
                    return {
                        titulo: 'Alocar produtos',
                            corpo: `<div class="w-100">
                                        <h4 class="text-center mb-4">Informe se quantidade de produtos que serão armazenadas</h4>
                                        <div class="d-flex flex-column">
                                            <strong>Quantidade</strong>
                                            <input id="qtd-produtos-alocar" type="number" class="mt-2" style="padding: 20px 8px;" placeholder="Insira a quantidade" />
                                            <h4 class="w-100 d-block mt-3">Quantidade disponível: ${quantidadeDisponivelLote}</h4>
                                        </div>
                                        <div class="w-100 d-flex flex-column mt-3">
                                            <button onclick="alocarQuantidadeProdutos()"
                                                class="col-12 btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                                                SALVAR
                                            </button>
                                        </div>
                                    </div>`
                    }
                case 'view_lote':
                    param = JSON.parse(param);

                    if (param.possuiLote) {
                        // Buscar lotes da posicao
                        $.ajax({
                            url: '../../ajax/lote.php',
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'verificarSeExisteLotePosicao',
                                params: param.idtagdim
                            },
                            success: res => {
                                let fracaoLotesHTML = '';

                                if (!res) return alertAtencao('Ocorreu um erro ao buscar lotes desta posição');
                                if (res.error) return alertAtencao(res.error);

                                fracaoLotesHTML = Array
                                    .from(res)
                                    .map(item => (
                                        `
                                                        <tr>
                                                            <td class="py-2"></td>
                                                        </tr>
                                                        <tr class="col-12 col-table-gray">
                                                            <td class="col-3 colTable alterFont py-4">${item.qtd}</td>
                                                            <td class="col-3 colTable alterFont">${item.partida}</td>
                                                            <td class="col-6 colTable alterFont">${item.plantel}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="py-2"></td>
                                                        </tr>`))
                                    .join(' ');

                                $('#tabela-fracao-lotes > tbody').html(fracaoLotesHTML);
                            },
                            error: err => {
                                console.log(err);
                                alertAtencao('Occoreu um erro ao buscar lotes desta posição');
                            }
                        });
                    }

                    return {
                        titulo: `
                                <div> Lotes da Localização: Coluna <span class="text-danger">${buscarLetraAlfabeto(param.coluna)}</span> Linha <span class="text-danger">${param.linha}</span> </div>
                            `,
                            corpo: `<div class="panel panel-default">
                                        <div class="panel-heading bg-primary row text-white py-4">
                                            <span class="font-bold h5">Local</span>
                                        </div>
                                        <div class="col-12 d-flex flex-column bg-light p-4">
                                            <table id="tabela-fracao-lotes" class="row mt-1">
                                                <thead class="col-12 custom-border mb-2">
                                                    <th class="col-3 font-bold alterFont">Qtd</th>
                                                    <th class="col-3 font-bold alterFont">Partida</th>
                                                    <th class="col-6 font-bold alterFont">Espécie</th>
                                                </thead>
                                                <tbody>
                                                    <td class="py-2"></td>
                                                    <tr class="col-12 col-table-gray">
                                                        <td colspan='3' class="col-3 colTable alterFont py-4">
                                                             ${param.possuiLote ? 'Carregando' : 'Não possui lotes'}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <button
                                        class="w-100 mt-4 bg-primary border-0 font-bold d-flex align-items-center justify-content-center"
                                        style="height: 50px;">
                                            <span class="icon-custom">
                                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <g id="QrCodeScan" clip-path="url(#clip0_6403_63)">
                                                        <path id="Vector" d="M0.538452 1.25C0.538452 1.05109 0.61747 0.860322 0.758122 0.71967C0.898774 0.579018 1.08954 0.5 1.28845 0.5H5.78845C5.98736 0.5 6.17813 0.579018 6.31878 0.71967C6.45943 0.860322 6.53845 1.05109 6.53845 1.25C6.53845 1.44891 6.45943 1.63968 6.31878 1.78033C6.17813 1.92098 5.98736 2 5.78845 2H2.03845V5.75C2.03845 5.94891 1.95943 6.13968 1.81878 6.28033C1.67813 6.42098 1.48736 6.5 1.28845 6.5C1.08954 6.5 0.898774 6.42098 0.758122 6.28033C0.61747 6.13968 0.538452 5.94891 0.538452 5.75V1.25ZM18.5385 1.25C18.5385 1.05109 18.6175 0.860322 18.7581 0.71967C18.8988 0.579018 19.0895 0.5 19.2885 0.5H23.7885C23.9874 0.5 24.1781 0.579018 24.3188 0.71967C24.4594 0.860322 24.5385 1.05109 24.5385 1.25V5.75C24.5385 5.94891 24.4594 6.13968 24.3188 6.28033C24.1781 6.42098 23.9874 6.5 23.7885 6.5C23.5895 6.5 23.3988 6.42098 23.2581 6.28033C23.1175 6.13968 23.0385 5.94891 23.0385 5.75V2H19.2885C19.0895 2 18.8988 1.92098 18.7581 1.78033C18.6175 1.63968 18.5385 1.44891 18.5385 1.25ZM1.28845 18.5C1.48736 18.5 1.67813 18.579 1.81878 18.7197C1.95943 18.8603 2.03845 19.0511 2.03845 19.25V23H5.78845C5.98736 23 6.17813 23.079 6.31878 23.2197C6.45943 23.3603 6.53845 23.5511 6.53845 23.75C6.53845 23.9489 6.45943 24.1397 6.31878 24.2803C6.17813 24.421 5.98736 24.5 5.78845 24.5H1.28845C1.08954 24.5 0.898774 24.421 0.758122 24.2803C0.61747 24.1397 0.538452 23.9489 0.538452 23.75V19.25C0.538452 19.0511 0.61747 18.8603 0.758122 18.7197C0.898774 18.579 1.08954 18.5 1.28845 18.5ZM23.7885 18.5C23.9874 18.5 24.1781 18.579 24.3188 18.7197C24.4594 18.8603 24.5385 19.0511 24.5385 19.25V23.75C24.5385 23.9489 24.4594 24.1397 24.3188 24.2803C24.1781 24.421 23.9874 24.5 23.7885 24.5H19.2885C19.0895 24.5 18.8988 24.421 18.7581 24.2803C18.6175 24.1397 18.5385 23.9489 18.5385 23.75C18.5385 23.5511 18.6175 23.3603 18.7581 23.2197C18.8988 23.079 19.0895 23 19.2885 23H23.0385V19.25C23.0385 19.0511 23.1175 18.8603 23.2581 18.7197C23.3988 18.579 23.5895 18.5 23.7885 18.5ZM6.53845 6.5H8.03845V8H6.53845V6.5Z" fill="white" />
                                                        <path id="Vector_2" d="M11.0385 3.5H3.53845V11H11.0385V3.5ZM5.03845 5H9.53845V9.5H5.03845V5ZM8.03845 17H6.53845V18.5H8.03845V17Z" fill="white" />
                                                        <path id="Vector_3" d="M11.0385 14H3.53845V21.5H11.0385V14ZM5.03845 15.5H9.53845V20H5.03845V15.5ZM17.0385 6.5H18.5385V8H17.0385V6.5Z" fill="white" />
                                                        <path id="Vector_4" d="M14.0385 3.5H21.5385V11H14.0385V3.5ZM15.5385 5V9.5H20.0385V5H15.5385ZM12.5385 12.5V15.5H14.0385V17H12.5385V18.5H15.5385V15.5H17.0385V18.5H18.5385V17H21.5385V15.5H17.0385V12.5H12.5385ZM15.5385 15.5H14.0385V14H15.5385V15.5ZM21.5385 18.5H20.0385V20H17.0385V21.5H21.5385V18.5ZM15.5385 21.5V20H12.5385V21.5H15.5385Z" fill="white" />
                                                        <path id="Vector_5" d="M18.5385 14H21.5385V12.5H18.5385V14Z" fill="white" />
                                                    </g>
                                                    <defs>
                                                        <clipPath id="clip0_6403_63">
                                                            <rect width="24" height="24" fill="white" transform="translate(0.538452 0.5)" />
                                                        </clipPath>
                                                    </defs>
                                                </svg>
                                            </span>
                                            <h3 class="m-0" onclick="gerarQrCode(${param.idtagdim})">
                                                IMPRIMIR QR CODE
                                            </h3>
                                    </button>
                                `
                    }
                case 'mensagem_de_sucesso':
                    return {
                        titulo: 'Produto alocado com sucesso',
                            corpo: `<div class="col-12 d-flex justify-content-center">
                                                    <img src="/form/img/bro.png" />
                                            </div>`
                    }
            }
        }

        if (id !== '') {
            CB.modal(propriedadesModal(id, param))
        }
    }

    function alocarQuantidadeProdutos() {
        qtdAlocada = parseFloat($('#qtd-produtos-alocar').val());

        if (!qtdAlocada) return alertAtencao('Quantidade inválida');
        if(qtdAlocada > quantidadeDisponivelLote) return alertAtencao(`Quantidade excede disponível no lote.<br> Tentando alocar ${qtdAlocada}, disponível ${quantidadeDisponivelLote}`);

        return montarElemento('qrcode_alocar_produtos', 'alocar_produtos');
    }

    function gerarQrCode(value) {
        const qrCodeHtml = document.createElement('div');
        qrCodeHtml.id = 'qrcode';

        new QRCode(qrCodeHtml, `${value}`);

        const newWindow = window.open();
        newWindow.document.write('<html><head><title>QR Code</title></head><body>');
        newWindow.document.body.appendChild(qrCodeHtml);
        newWindow.document.write('</body></html>');
        newWindow.document.close();
    }

    function GuardarVacinas(produto) {
        quantidadeDisponivelLote = produto.qtddisponivel ?? 0;

        return `<div class="panel panel-default">
                        <div class="panel-heading row bg-primary text-white py-4">
                            <span class="font-bold h4">Produto</span>
                        </div>
                        <div class="col-12 d-flex flex-column bg-light p-4">
                            <div class="mb-4">
                                <span class="text-secondary font-weight-normal">Nome do produto:</span>
                                <h5 class="ml-2">${produto.descr}</h5>
                            </div>
                            <div class="mb-4">
                                <span class="teqtdpadraofloteproducao&_acao=u&idlote=${produto.idlote}&_idempresa=${produto.idempresa}" target="_blank">
                                    <h5 class="ml-2 text-primary">${produto.partidainterna}</h5>
                                </a>
                            </div>
                            <div class="mb-4">
                                <span class="text-secondary font-weight-normal">Fórmula:</span>
                                <h5 class="ml-2">${produto.formula}</h5>
                            </div>
                            <div class="mb-4">
                                <span class="text-secondary font-weight-normal">Quantidade de frascos:</span>
                                <h5 class="ml-2">${produto.qtddisponivelformatada}</h5>
                            </div>
                            <div class="mb-2">
                                <span class="text-secondary font-weight-normal">Observação*</span>
                                <textarea id="obs-lote" rows="5" cols="33" type='text' placeholder="Adicione alguma informação">${produto.observacao}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="w-100  d-flex flex-column mt-3">
                        <button
                            onClick="openModal('confirm', ${produto.idlote})"
                            class="col-12  btn-primary-custom bg-primary border-0 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            CONFERIR</button>
                        <button
                        onclick="montarElemento('')"
                            class="col-12 bg-light text-gray btn-secondary-custom border-1 my-2 py-3 font-bold rounded-circle d-flex align-items-center justify-content-center">
                            CANCELAR</button>
                    </div>`
    }

    /**
     * @paginaRetorno string página para qual vai quando o usuario fecha a camera
     * @paginaDados string parametros que são passados para paginaRetorno
     */
    function inicializarScanner(callback, paginaRetorno = '', paginaDados = '') {
        video = document.getElementById('leitor-qrcode');

        if (!video) {
            alertAtencao('Elemento para QrCode não encontrado.');

            return false;
        }

        if (qrScanner) qrScanner.destroy();

        qrScanner = new QrScanner(video, callback);

        // Inicie a câmera e o QRScanner
        QrScanner.hasCamera().then(hasCamera => {
            if (hasCamera) {
                qrScanner.start().catch(error => {
                    alertAtencao(error);
                });
            } else {
                alertAtencao("Nenhuma câmera encontrada.");
                montarElemento();
            }
        });

        QrScanner.listCameras().then(res => {
            montarCamerasDisponiveis(res);
        });

        qrScanner.hasFlash().then(res => {
            if (res) {
                $("#status-leitura")
                    .removeClass('col-xs-12')
                    .addClass('col-xs-10')
                    .addClass('com-lanterna');

                $("#lanterna")
                    .removeClass('hidden');
            }
        });


        $('#cameras').on('change', function() {
            qrScanner.setCamera(this.value).then(res => {
                console.log(res);
            }).catch(err => {
                console.log(err);
                alertAtencao('Ocorreu um erro ao alterar a câmera.')
            });
        });

        $('#close-cam').on('click', () => {
            qrScanner.stop();
            montarElemento(paginaRetorno, paginaDados);
        });

        return qrScanner;
    }

    function montarQrCode() {
        return `<div id="qrcode-block">
                    <div id="camera-take">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    </svg>
                    <video id="leitor-qrcode"></video>
                    <div id="camera-block">
                        <select id="cameras"></select>
                    </div>
                    <span id="close-cam">
                        <i class="fa fa-times"></i>
                    </span>
                </div>`;
    }

    function montarCamerasDisponiveis(cameras) {
        let camerasOption = '';

        cameras.forEach(camera => {
            camerasOption += `<option value="${camera.id}">${camera.label}</option>`
        })

        $('#cameras').html(camerasOption);
    }

    function alteraStatusAtiv(idlote, status, bloquearstatus, concluir = false) {
        const obs = $('#obs-lote').val();

        $.ajax({
            url: '../../ajax/lote.php',
            method: 'POST',
            dataType: 'json',
            data: {
                params: [idlote, status, bloquearstatus],
                action: 'atualizarStatusAtividade'
            },
            success: res => {
                if (res && res.error) return alertAtencao(res.error);

                if (res.atualizandoAtividade) {
                    if (concluir) {
                        montarElemento('concluir');
                        return alertAzul('Atividade concluida');
                    }

                    CB.post({
                        objetos: {
                            _1_u_lote_idlote: idlote,
                            _1_u_lote_observacao: obs,
                        },
                        refresh: false,
                        parcial: true
                    });

                    prodServAtual = res.prodserv;

                    montarElemento('produtos_retidos', res.prodserv);
                } else alertAtencao('Occoreu um erro ao conferir lote.');
            },
            error: err => {
                alertAtencao('Occorreu um erro ao tentar atualizar o status da atividade.');
                console.log(err);
            }
        });
    }

    function renderizarSelectPicker() {
        $('.select-picker').selectpicker('render');
    }

    montarElemento();
</script>