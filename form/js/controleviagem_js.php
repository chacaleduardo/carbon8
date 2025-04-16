<script>
    var gIdEmpresaModulo = '<?= $_GET['_idempresa'] ?>';
    const nomeUsuario = '<?= $_SESSION['SESSAO']['NOME'] ?>';
    const usuario = '<?= $_SESSION['SESSAO']['USUARIO'] ?>';
    const viagem = <?= json_encode($controleViagem) ?>;
    let idControleViagem = '';
    let idTagAtual = '';

    // QRScanner
    let video = '';
    let qrScanner = '';

    if (!Object.keys(viagem).length && (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia)) {
        alertAtencao("Navegador não suporta acesso à câmera.");
    } else {
        if (Object.keys(viagem).length)
            montarTela('finalizar_viagem', viagem);
        else
            montarTela();

        $('#controle-viagem').on('click', '#lanterna', ligarDesligarLanterna)
        $('#controle-viagem').on('click', '#iniciar-viagem', function() {
            const elementoJQ = $(this),
                idTag = elementoJQ.data('idtag');

            if (!idTag) return alertAtencao('ID tag não informado.');

            montarTela('iniciar_viagem', idTag).catch(err => {
                console.log(err);
                alertAtencao('Ocorreu um erro ao tentar iniciar viagem!');
            })
        });
    }

    function montarCamerasDisponiveis(cameras) {
        let camerasOption = '';

        cameras.forEach(camera => {
            camerasOption += `<option value="${camera.id}">${camera.label}</option>`
        })

        $('#cameras').html(camerasOption);
    }

    function ligarDesligarLanterna() {
        const lanternaJQ = $('#lanterna');

        if (qrScanner.isFlashOn()) {
            lanternaJQ
                .removeClass('btn-primary')
                .addClass('btn-secondary');

            return qrScanner.turnFlashOff().catch(err => {
                alertAtencao('Não foi possível acionar a lanterna.')
            });
        }

        lanternaJQ
            .removeClass('btn-secondary')
            .addClass('btn-primary');

        return qrScanner.turnFlashOn().catch(err => {
            alertAtencao('Não foi possível acionar a lanterna.')
        });
    }

    async function buscarTela(id, param = '') {
        switch (id) {
            case 'iniciar_viagem':
                $('#iniciar-viagem').addClass('disabled');
                $('#iniciar-viagem').find('h2').text('Carregando');

                return await IniciarViagem(param);
            case 'finalizar_viagem':
                return FinalizarViagem(param);
            default:
                return TelaPrincipal();
        }
    }

    async function montarTela(id = '', param = '') {
        const {
            titulo,
            corpo,
            botoes
        } = await buscarTela(id, param);

        if (!corpo) return montarTela();

        if (!titulo && !corpo) return;

        const corpoPrincipalHTML = `
                                    <div class="col-md-12">
                                        <div class="panel panel-default">
                                            <div class="panel-heading row">
                                                <h2 id="cabecalho" class="col-xs-12 font-bold">${titulo}</h2>
                                            </div>
                                            <div id="corpo" class="panel-body">
                                                ${corpo}
                                            </div>
                                        </div>
                                        <div class="w-100 d-flex flex-col">
                                            ${botoes ?? ''}
                                        </div>
                                    </div>
                                `;

        $('#controle-viagem').html(corpoPrincipalHTML);

        if (qrScanner)
            qrScanner.destroy();

        if (!id) inicializarScanner();
    }

    function inicializarScanner() {
        video = document.getElementById('leitor-qrcode');
        qrScanner = new QrScanner(video, result => {
            qrScanner.stop();
            // Exiba o resultado do QR Code lido
            const idTag = getUrlParameter('idtag', result);

            if (!idTag) {
                qrScanner.start();
                $('#qr-lido')
                    .removeClass('d-flex')
                    .addClass('hidden');

                return alertAtencao('ID da tag do veículo não encontrado.')
            };

            $('#qr-lido')
                .removeClass('hidden')
                .addClass('d-flex');

            $('#status-leitura')
                .removeClass('d-flex')
                .addClass('hidden');

            $('#iniciar-viagem')
                .removeClass('hidden')
                .addClass('d-flex')
                .data('idtag', idTag);

            qrScanner.stop();
        });

        // Inicie a câmera e o QRScanner
        QrScanner.hasCamera().then(hasCamera => {
            if (hasCamera) {
                qrScanner.start().catch(error => {
                    alertAtencao(error);
                });
            } else {
                alertAtencao("Nenhuma câmera encontrada.");
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
    }

    async function IniciarViagem(idTag) {
        if (!idTag) {
            alertAtencao('Id tag está vazio!');

            return {
                titulo: '',
                corpo: ''
            }
        }

        let corpoHTML = '';

        const botoes = `<button class="btn btn-primary text-uppercase my-3" onclick="abrirModalIniciarViagem(${idTag})">Iniciar viagem</button>
                        <button class="btn btn-outline-secondary text-uppercase" onclick="montarTela()">Cancelar</button>`;

        await $.ajax({
            url: '../../ajax/tag.php',
            method: 'GET',
            dataType: 'json',
            data: {
                params: idTag,
                action: 'buscarTagPorIdTag'
            },
            success: res => {
                if (!res) {
                    alertAtencao('Tag não encontrada');
                    return;
                }

                if (res.error) {
                    alertAtencao(res.error);
                    return;
                }

                qrScanner.stop();

                const {
                    sigla,
                    tag,
                    descricao,
                    placa
                } = res;

                corpoHTML = `
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-group d-flex flex-col">
                                <label for="">TAG do veículo</label>
                                <strong id="tag-veiculo">${sigla}-${tag}</strong>
                            </div>
                            <div class="form-group d-flex flex-col">
                                <label for="">Descrição</label>
                                <strong id="descricao" class="text-uppercase">${descricao}</strong>
                            </div>
                            <div class="form-group d-flex flex-col">
                                <label for="">Placa</label>
                                <strong id="placa" class="text-uppercase">${placa}</strong>
                            </div>
                        </div>
                    </div>`;
            },
            error: err => {
                qrScanner.start();
            }
        });

        return {
            titulo: 'Veículo',
            corpo: corpoHTML,
            botoes
        }
    }

    function TelaPrincipal() {
        return {
            titulo: 'Iniciar viagem',
            corpo: `<div class="row">
                    <div class="col-xs-12">
                        <p>Vamos iniciar sua viagem! Clique no botão abaixo e leia o <strong>QRCODE</strong> dentro do carro.</p>
                    </div>
                    <div class="row col-xs-12">
                        <div id="container-qrcode" class="col-xs-12 my-3">
                            <video id="leitor-qrcode"></video>
                            <div id="qr-lido" class="hidden justify-content-center align-items-center">
                                <i class="fa fa-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="d-flex flex-col justify-content-center align-items-center col-xs-12 text-center bg-white">
                            <span>Selecione a câmera ou dispositivo de leitura</span>
                            <div class="d-flex align-items-center flex-between w-100 my-3">
                                <strong class="col-xs-2 p-0">Câmera: </strong>
                                <select id="cameras" class="col-xs-9" type="text"></select>
                            </div>
                            <div class="w-100 d-flex">
                                <button id="status-leitura" class="btn btn-secondary col-xs-12 py-4 text-center d-flex justify-content-center align-items-center px-2">
                                    <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                                        <g clip-path="url(#clip0_16405_330)">
                                            <path d="M0.5 1.25C0.5 1.05109 0.579018 0.860322 0.71967 0.71967C0.860322 0.579018 1.05109 0.5 1.25 0.5H5.75C5.94891 0.5 6.13968 0.579018 6.28033 0.71967C6.42098 0.860322 6.5 1.05109 6.5 1.25C6.5 1.44891 6.42098 1.63968 6.28033 1.78033C6.13968 1.92098 5.94891 2 5.75 2H2V5.75C2 5.94891 1.92098 6.13968 1.78033 6.28033C1.63968 6.42098 1.44891 6.5 1.25 6.5C1.05109 6.5 0.860322 6.42098 0.71967 6.28033C0.579018 6.13968 0.5 5.94891 0.5 5.75V1.25ZM18.5 1.25C18.5 1.05109 18.579 0.860322 18.7197 0.71967C18.8603 0.579018 19.0511 0.5 19.25 0.5H23.75C23.9489 0.5 24.1397 0.579018 24.2803 0.71967C24.421 0.860322 24.5 1.05109 24.5 1.25V5.75C24.5 5.94891 24.421 6.13968 24.2803 6.28033C24.1397 6.42098 23.9489 6.5 23.75 6.5C23.5511 6.5 23.3603 6.42098 23.2197 6.28033C23.079 6.13968 23 5.94891 23 5.75V2H19.25C19.0511 2 18.8603 1.92098 18.7197 1.78033C18.579 1.63968 18.5 1.44891 18.5 1.25ZM1.25 18.5C1.44891 18.5 1.63968 18.579 1.78033 18.7197C1.92098 18.8603 2 19.0511 2 19.25V23H5.75C5.94891 23 6.13968 23.079 6.28033 23.2197C6.42098 23.3603 6.5 23.5511 6.5 23.75C6.5 23.9489 6.42098 24.1397 6.28033 24.2803C6.13968 24.421 5.94891 24.5 5.75 24.5H1.25C1.05109 24.5 0.860322 24.421 0.71967 24.2803C0.579018 24.1397 0.5 23.9489 0.5 23.75V19.25C0.5 19.0511 0.579018 18.8603 0.71967 18.7197C0.860322 18.579 1.05109 18.5 1.25 18.5ZM23.75 18.5C23.9489 18.5 24.1397 18.579 24.2803 18.7197C24.421 18.8603 24.5 19.0511 24.5 19.25V23.75C24.5 23.9489 24.421 24.1397 24.2803 24.2803C24.1397 24.421 23.9489 24.5 23.75 24.5H19.25C19.0511 24.5 18.8603 24.421 18.7197 24.2803C18.579 24.1397 18.5 23.9489 18.5 23.75C18.5 23.5511 18.579 23.3603 18.7197 23.2197C18.8603 23.079 19.0511 23 19.25 23H23V19.25C23 19.0511 23.079 18.8603 23.2197 18.7197C23.3603 18.579 23.5511 18.5 23.75 18.5ZM6.5 6.5H8V8H6.5V6.5Z" fill="white" />
                                            <path d="M11 3.5H3.5V11H11V3.5ZM5 5H9.5V9.5H5V5ZM8 17H6.5V18.5H8V17Z" fill="white" />
                                            <path d="M11 14H3.5V21.5H11V14ZM5 15.5H9.5V20H5V15.5ZM17 6.5H18.5V8H17V6.5Z" fill="white" />
                                            <path d="M14 3.5H21.5V11H14V3.5ZM15.5 5V9.5H20V5H15.5ZM12.5 12.5V15.5H14V17H12.5V18.5H15.5V15.5H17V18.5H18.5V17H21.5V15.5H17V12.5H12.5ZM15.5 15.5H14V14H15.5V15.5ZM21.5 18.5H20V20H17V21.5H21.5V18.5ZM15.5 21.5V20H12.5V21.5H15.5Z" fill="white" />
                                            <path d="M18.5 14H21.5V12.5H18.5V14Z" fill="white" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_16405_330">
                                                <rect width="24" height="24" fill="white" transform="translate(0.5 0.5)" />
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    <h2 class="m-0">Aguardando leitura</h2>
                                </button>
                                <button id="iniciar-viagem" class="btn btn-primary col-xs-12 py-4 text-center hidden justify-content-center align-items-center px-2"">
                                    <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 25 25" fill="none">
                                        <g clip-path="url(#clip0_16405_330)">
                                            <path d="M0.5 1.25C0.5 1.05109 0.579018 0.860322 0.71967 0.71967C0.860322 0.579018 1.05109 0.5 1.25 0.5H5.75C5.94891 0.5 6.13968 0.579018 6.28033 0.71967C6.42098 0.860322 6.5 1.05109 6.5 1.25C6.5 1.44891 6.42098 1.63968 6.28033 1.78033C6.13968 1.92098 5.94891 2 5.75 2H2V5.75C2 5.94891 1.92098 6.13968 1.78033 6.28033C1.63968 6.42098 1.44891 6.5 1.25 6.5C1.05109 6.5 0.860322 6.42098 0.71967 6.28033C0.579018 6.13968 0.5 5.94891 0.5 5.75V1.25ZM18.5 1.25C18.5 1.05109 18.579 0.860322 18.7197 0.71967C18.8603 0.579018 19.0511 0.5 19.25 0.5H23.75C23.9489 0.5 24.1397 0.579018 24.2803 0.71967C24.421 0.860322 24.5 1.05109 24.5 1.25V5.75C24.5 5.94891 24.421 6.13968 24.2803 6.28033C24.1397 6.42098 23.9489 6.5 23.75 6.5C23.5511 6.5 23.3603 6.42098 23.2197 6.28033C23.079 6.13968 23 5.94891 23 5.75V2H19.25C19.0511 2 18.8603 1.92098 18.7197 1.78033C18.579 1.63968 18.5 1.44891 18.5 1.25ZM1.25 18.5C1.44891 18.5 1.63968 18.579 1.78033 18.7197C1.92098 18.8603 2 19.0511 2 19.25V23H5.75C5.94891 23 6.13968 23.079 6.28033 23.2197C6.42098 23.3603 6.5 23.5511 6.5 23.75C6.5 23.9489 6.42098 24.1397 6.28033 24.2803C6.13968 24.421 5.94891 24.5 5.75 24.5H1.25C1.05109 24.5 0.860322 24.421 0.71967 24.2803C0.579018 24.1397 0.5 23.9489 0.5 23.75V19.25C0.5 19.0511 0.579018 18.8603 0.71967 18.7197C0.860322 18.579 1.05109 18.5 1.25 18.5ZM23.75 18.5C23.9489 18.5 24.1397 18.579 24.2803 18.7197C24.421 18.8603 24.5 19.0511 24.5 19.25V23.75C24.5 23.9489 24.421 24.1397 24.2803 24.2803C24.1397 24.421 23.9489 24.5 23.75 24.5H19.25C19.0511 24.5 18.8603 24.421 18.7197 24.2803C18.579 24.1397 18.5 23.9489 18.5 23.75C18.5 23.5511 18.579 23.3603 18.7197 23.2197C18.8603 23.079 19.0511 23 19.25 23H23V19.25C23 19.0511 23.079 18.8603 23.2197 18.7197C23.3603 18.579 23.5511 18.5 23.75 18.5ZM6.5 6.5H8V8H6.5V6.5Z" fill="white" />
                                            <path d="M11 3.5H3.5V11H11V3.5ZM5 5H9.5V9.5H5V5ZM8 17H6.5V18.5H8V17Z" fill="white" />
                                            <path d="M11 14H3.5V21.5H11V14ZM5 15.5H9.5V20H5V15.5ZM17 6.5H18.5V8H17V6.5Z" fill="white" />
                                            <path d="M14 3.5H21.5V11H14V3.5ZM15.5 5V9.5H20V5H15.5ZM12.5 12.5V15.5H14V17H12.5V18.5H15.5V15.5H17V18.5H18.5V17H21.5V15.5H17V12.5H12.5ZM15.5 15.5H14V14H15.5V15.5ZM21.5 18.5H20V20H17V21.5H21.5V18.5ZM15.5 21.5V20H12.5V21.5H15.5Z" fill="white" />
                                            <path d="M18.5 14H21.5V12.5H18.5V14Z" fill="white" />
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_16405_330">
                                                <rect width="24" height="24" fill="white" transform="translate(0.5 0.5)" />
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    <h2 class="text-tuppercase m-0">Iniciar viagem</h2>
                                </button>
                                <button id="lanterna" class="col-xs-2 btn btn-secondary text-center hidden">
                                    <i class="fa fa-lightbulb-o fa-2x" style="margin:3px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`
        };
    }

    function abrirModalIniciarViagem(idTag) {
        const corpo = `<div class="w-100 d-flex flex-col">
                            <h3>Informe a quilometragem inicial do veículo</h3>
                            <div class="form-group my-3">
                                <label for="">Quilometragem inicial(KM) <strong class="text-danger">*</strong>:</label>
                                <input id="input-kminicial" type="text" class="form-control" placeholder="Informe o KM inicial" />
                            </div>
                            <button class="w-100 py-3 btn btn-primary text-uppercase" onclick="iniciarViagem(${idTag})">
                                Iniciar viagem
                            </button>
                        </div>`;

        CB.modal({
            titulo: 'Iniciar viagem',
            corpo
        })
    }

    function iniciarViagem(idTag) {
        const kmInicial = $('#input-kminicial').val();

        if (!kmInicial) return alertAtencao('Informe a quilometragem inicial.');

        $.ajax({
            url: '../../ajax/tag.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'iniciarViagem',
                params: {
                    typeParam: 'array',
                    param: [idTag, kmInicial, gIdEmpresaModulo, usuario]
                }
            },
            success: res => {
                if (!res || res.error) return alertAtencao(res.error);

                idControleViagem = res;

                const corpo = `<div class="d-flex flex-col align-items-center">
                                    <h3>Lembre-se de alguns pontos importantes para garantir uma viagem tranquila:</h3>
                                    <ul>
                                        <li class="mb-2"><h4>Verifique se todos os documentos necessários estão com você.</h4></li>
                                        <li><h4>Faça uma revisão rápida no carro para evitar imprevistos.(<strong>triângulo de sinalização, estepe, Líquido radiador ....</strong>)</h4></li>
                                    </ul>
                                    <h3>Estamos à disposição para qualquer necessidade durante sua jornada. Tenha uma excelente viagem!</h3>
                                    <div>
                                        <h3 class="font-bold">
                                            Boa viagem, <span class="text-capitalize">${(nomeUsuario.split(' ')[0] ?? 'Usuário').toLowerCase()}</span>!
                                        </h3>
                                    </div>
                                    <img class="mt-3" src="/form/img/bro.png" />
                                </div>`;

                CB.modal({
                    titulo: 'Viagem iniciada com sucesso!',
                    corpo,
                    aoFechar: res => {
                        if (idControleViagem) window.location.href = `?_modulo=controledeviagens&_acao=u&idcontroleviagem=${idControleViagem}&_idempresa=${gIdEmpresaModulo}`
                    }
                })
            },
            error: err => {
                console.log(err);
                alertAtencao('Ocorreu um erro!');
            }
        })
    }

    function FinalizarViagem(viagem) {
        const titulo = 'Veículo';
        let botoes = ``;

        let kmFinal = '',
            observacao = ''
        viagemFinalizada = false;

        if (viagem.kmfinal) {
            viagemFinalizada = true;
            kmFinal = ` <div class="form-group d-flex flex-col">
                            <label for="">Quilometragem final(KM):</label>
                            <strong>${parseFloat(viagem.kmfinal).toLocaleString('pt-BR')} KM</strong>
                        </div>`;
        }

        if (viagem.observacao) {
            observacao = ` <div class="form-group d-flex flex-col">
                            <label for="">Observação:</label>
                            <strong>${viagem.observacao}</strong>
                        </div>`;
        }

        if (!viagemFinalizada) {
            botoes = `<button class="btn btn-outline-primary text-uppercase my-3" onclick="abrirModalFinalizarViagem()">Finalizar viagem</button`;
        }

        const corpo = ` <div class="row">
                            <input name="_1_u_controleviagem_idcontroleviagem" type="hidden" value="${viagem.idcontroleviagem}">
                            <div class="col-xs-12">
                                <div class="form-group d-flex flex-col">
                                    <label for="">TAG do veículo</label>
                                    <strong>${viagem.tag}</strong>
                                </div>
                                <div class="form-group d-flex flex-col">
                                    <label for="">Descrição</label>
                                    <strong class="text-uppercase">${viagem.descricao}</strong>
                                </div>
                                <div class="form-group d-flex flex-col">
                                    <label for="">Placa</label>
                                    <strong class="text-uppercase">${viagem.placa}</strong>
                                </div>
                                <div class="form-group d-flex flex-col">
                                    <label for="">Condutor</label>
                                    <strong class="text-capitalize">${viagem.condutor.toLowerCase()}</strong>
                                </div>
                                <div class="form-group d-flex flex-col">
                                    <label for="">Quilometragem inicial(KM): </label>
                                    <strong class="text-uppercase">${parseFloat(viagem.kminicial).toLocaleString('pt-BR')} KM</strong>
                                </div>
                                ${kmFinal}
                                ${observacao}
                            </div>
                        </div>`;

        return {
            titulo,
            corpo,
            botoes
        }
    }

    function finalizarViagem() {
        const kmFinal = parseFloat($('#input-kmfinal').val());

        if (!kmFinal) return alertAtencao('Informe quilometragem final.');
        if (kmFinal < parseFloat(viagem.kminicial)) return alertAtencao('A quilometragem final não pode ser maior que a inicial.');

        CB.post();

        const corpo = `<div class="d-flex flex-col align-items-center">
                        <img class="mt-3" src="/form/img/bro.png" />
                    </div>`;

        CB.modal({
            titulo: 'Viagem finalizada com sucesso!',
            corpo
        })
    }

    function abrirModalFinalizarViagem() {
        const dataAtual = dataEHoraAtual();

        const corpo = `<div class="w-100 d-flex flex-col">
                            <input name="_1_u_controleviagem_datafinalviagem" type="text" value="${dataAtual}" class="hidden" hidden />
                            <input name="_1_u_controleviagem_status" type="hidden" value="Finalizada" />
                            <h3>Informe a quilometragem inicial do veículo</h3>
                            <div class="form-group my-3">
                                <label for="">Quilometragem final(KM) <strong class="text-danger">*</strong>:</label>
                                <input id="input-kmfinal" name="_1_u_controleviagem_kmfinal" type="text" class="form-control" placeholder="Informe o KM final" />
                            </div>
                            <div class="form-group my-3">
                                <label for="">Observação::</label>
                                <input id="observacao" name="_1_u_controleviagem_observacao" type="text" class="form-control" placeholder="Informar caso necessário" />
                            </div>
                            <button class="w-100 py-3 btn btn-primary text-uppercase" onclick="finalizarViagem()">
                                Finalizar viagem
                            </button>
                        </div>`;

        CB.modal({
            titulo: 'Finalizar viagem',
            corpo
        });
    }

    function dataEHoraAtual() {
        // Obtém a data e hora atuais
        var agora = new Date();

        // Formata a data para YYYY-MM-DD
        var dia = ("0" + agora.getDate()).slice(-2);
        var mes = ("0" + (agora.getMonth() + 1)).slice(-2);
        var ano = agora.getFullYear();

        // Formata a hora para HH:MM:SS
        var hora = ("0" + agora.getHours()).slice(-2);
        var minuto = ("0" + agora.getMinutes()).slice(-2);
        var segundo = ("0" + agora.getSeconds()).slice(-2);

        // Combina data e hora no formato YYYY-MM-DDTHH:MM:SS
        return `${dia}/${mes}/${ano} ${hora}:${minuto}:${segundo}`;
    }
</script>