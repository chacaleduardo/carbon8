let queryParams = CB.locationSearch.split('&').reduce(function (acc, param) {
    var [key, value] = param.split('=');
    acc[key] = value;
    return acc;
}, {});

try {
    let timeBloqueioTela; // Variável para armazenar o ID do setInterval
    function convertSecondsToMMSS(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
        const formattedSeconds = remainingSeconds < 10 ? '0' + remainingSeconds : remainingSeconds;
        return formattedMinutes + ':' + formattedSeconds;
    }

    function startTimer(selector, timeInSeconds) {
        let remainingTime = timeInSeconds;
        let timerInterval;

        function updateTimer() {
            if (remainingTime >= 0) {
                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;
                const formattedTime = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                $(selector).text(formattedTime);
                remainingTime--;
                let boxBloqueio = $('#box-bloqueio');

                if (boxBloqueio.hasClass('me-true')) {
                    if (remainingTime === 60) { // Verifica se o tempo restante é 1 minuto
                        CB.modal({
                            titulo: 'Aviso',
                            corpo: `<div class="text-center">
                            <div class="bloqueio-icone"><i class="fa fa-lock fa-2x"></i></div>
                            <h2 class'text-danger'> Você ainda está ai? </h2><br>
                            <p class='h3'>Sua sessão expira em: ${remainingTime} segundos.<br> Quer renovar a sessão?<p>
                        </div>`,
                            rodape: `<button type="button" class="btn btn-primary" id="renovar-sessao">Renovar</button>
                                <button type="button" class="btn btn-secondary" id="fechar-sessao">Fechar</button>`
                        });

                        $('#renovar-sessao').off('click').on('click', function () {
                            clearInterval(timerInterval); // Limpa o intervalo antigo
                            remainingTime = timeInSeconds; // Reinicia o tempo
                            updateTimer(); // Atualiza imediatamente

                            CB.oModal.modal("hide");
                            refreshBloqueio();
                            timerInterval = setInterval(updateTimer, 1000); // Cria um novo intervalo
                        });

                        $('#fechar-sessao').off('click').on('click', function () {
                            console.log('queryParams', queryParams);
                            CB.oModal.modal("hide");
                        });
                    }
                }
                if (boxBloqueio.hasClass('me-true')) {
                    if (remainingTime === 0) {
                        CB.oModal.modal("hide")
                        CB.modal({
                            titulo: 'Aviso',
                            corpo: `<div class="text-center">
                            <div class="bloqueio-icone"><i class="fa fa-lock fa-2x"></i></div>
                            <h2 class="text-danger"> Você ainda está aí? </h2><br>
                            <p class="h3">O tempo de sessão expirou, recarregue a página para assumir a sessão ou verificar o novo status.</p>
                        </div>`,
                            rodape: `<button type="button" class="btn btn-primary" id="renovar-sessao">Recarregar</button>
                                <button type="button" class="btn btn-secondary" id="fechar-sessao">Fechar</button>`
                        });

                        $('#renovar-sessao').off('click').on('click', function () {
                            window.location.reload();
                            CB.oModal.modal("hide");
                        });

                        $('#fechar-sessao').off('click').on('click', function () {
                            console.log('queryParams', queryParams);
                            CB.oModal.modal("hide");
                        });
                    } 
                }
            }else { 
                clearInterval(timerInterval); // Limpa o intervalo se o tempo acabar

            }

        }

        updateTimer(); // Atualiza o timer imediatamente
        timerInterval = setInterval(updateTimer, 1000);
    }
    // Função para interromper o timer
    function stopTimer() {
        clearInterval(timeBloqueioTela);
    }



    function refreshBloqueio() {
        let queryParams = CB.locationSearch.split('&').reduce(function (acc, param) {
            var [key, value] = param.split('=');
            acc[key] = value;
            return acc;
        }, {});
        $.ajax({
            url: 'ajax/_renovabloqueiotela.php?pk=' + queryParams['id' + queryParams['_modulo']] + '&modulo=' + queryParams['_modulo'], // O caminho para o seu script PHP
            type: 'POST',
            success: function (response) {
                console.log('refreshBloqueio', response);

            },
            error: function (xhr, status, error) {
                console.error('Erro na requisição AJAX:', error);
            }

        })
    }

    if (queryParams.hasOwnProperty('_modulo')) {
        if (queryParams.hasOwnProperty('id' + queryParams['_modulo'])) {
            $.ajax({
                url: 'ajax/_consultabloqueiotela.php?pk=' + queryParams['id' + queryParams['_modulo']] + '&modulo=' + queryParams['_modulo'], // O caminho para o seu script PHP
                type: 'GET',
                success: function (response) {
                    if (response['status'] == true) {
                        let boxBloqueio = $('#box-bloqueio');
                        let bloqueioTimer = $('#box-bloqueio .bloqueio-timer')
                        let bloqueioIcone = $("#box-bloqueio .bloqueio-icone")
                        let btnSalvar = $('#cbSalvar')
                        let usuario = ''
                        if (response['nome']) {
                            usuario = response['nome']
                        }
                        boxBloqueio.removeClass('hidden')
                        bloqueioTimer.html(convertSecondsToMMSS(response['timeout']))

                        startTimer('#box-bloqueio .bloqueio-timer', response['timeout'])
                        $('#box-bloqueio').removeClass('hidden');
                        if (!response['me']) {
                            btnSalvar.attr('disabled', true)
                        }

                        if (response['idpessoa'] != gIdpessoa) {
                            console.log('idpessoabloqueio', response['idpessoa'], ' idpessoalogada', gIdpessoa)
                            boxBloqueio.find('p').each(function () {
                                $(this).addClass('bg-danger text-white')
                                boxBloqueio.addClass('me-false')
                                bloqueioIcone.addClass('text-white')
                                bloqueioIcone.attr('title', `Aviso: Tela em modo LEITURA. O usuário ${usuario} está editando. Para liberar a edição, feche a aba do navegador ou pressione ESC ou clique no X no canto superior direito.

`)
                            });
                            $('.fa-pencil').each(function () {
                                $(this).addClass('hidden');
                            });
                        } else {
                            bloqueioIcone.attr('title', 'Aviso: Tela em modo EDIÇÃO')
                            boxBloqueio.addClass('me-true')
                        }
                    }
                    console.log('Resposta do PHP:', response);
                },
                error: function (xhr, status, error) {
                    console.error('Erro na requisição AJAX:', error);
                }
            });
        }
    }

} catch (error) {
    console.log("Erro ao carregar variáveis de bloqueio", error)
}

let aviso = false
setTimeout(() => {
    let intervalo = setInterval(() => {
        if (queryParams.hasOwnProperty('_modulo')) {
            if (queryParams.hasOwnProperty('id' + queryParams['_modulo'])) {
                $.ajax({
                    url: 'ajax/_consultabloqueiotela.php?pk=' + queryParams['id' + queryParams['_modulo']] + '&modulo=' + queryParams['_modulo'], // O caminho para o seu script PHP
                    type: 'GET',
                    success: function (response) {
                        if (response['status'] == false) {
                            let boxBloqueio = $('#box-bloqueio');
                            let bloqueioTimer = $('#box-bloqueio .bloqueio-timer')
                            let bloqueioIcone = $("#box-bloqueio .bloqueio-icone")
                            let btnSalvar = $('#cbSalvar')
                            let usuario = ''
                            if (!aviso) {
                                if (boxBloqueio.hasClass('me-false')) {
                                    aviso = true
                                    CB.modal({
                                        titulo: 'Aviso',
                                        corpo: `<div class="text-center">
                                        <div class="bloqueio-icone"><i class="fa fa-lock fa-2x"></i></div>
                                        <h2 class="text-danger"> Você ainda está aí? </h2><br>
                                        <p class="h3">A edição da tela está livre! Recarregue para assumir a sessão.</p>
                                    </div>`,
                                        rodape: `<button type="button" class="btn btn-primary" id="renovar-sessao">Recarregar</button>
                                            <button type="button" class="btn btn-secondary" id="fechar-sessao">Fechar</button>`
                                    });

                                    $('#renovar-sessao').off('click').on('click', function () {
                                        CB.oModal.modal("hide");
                                        window.location.reload();
                                    });

                                    $('#fechar-sessao').off('click').on('click', function () {
                                        console.log('queryParams', queryParams);
                                        CB.oModal.modal("hide");
                                    });
                                }
                            }
                        }
                        console.log('Resposta do PHP:', response);
                    },
                    error: function (xhr, status, error) {
                        console.error('Erro na requisição AJAX:', error);
                    }
                });
            }
        }
    }, 10000);
}, 10000);


$(window).on('beforeunload', function (e) {
    console.log('beforeunload')
    // Mensagem de confirmação
    let queryParams = CB.locationSearch.split('&').reduce(function (acc, param) {
        var [key, value] = param.split('=');
        acc[key] = value;
        return acc;
    }, {});
    console.log('queryParams', queryParams)

    if (queryParams.hasOwnProperty('_modulo')) {
        if (queryParams.hasOwnProperty('id' + queryParams['_modulo'])) {
            $.ajax({
                url: 'ajax/_removebloqueiotela.php?pk=' + queryParams['id' + queryParams['_modulo']] + '&modulo=' + queryParams['_modulo'], // O caminho para o seu script PHP
                type: 'POST',
                success: function (response) {
                    console.log('Resposta do PHP:', response);
                },
                error: function (xhr, status, error) {
                    console.error('Erro na requisição AJAX:', error);
                }
            });
        }
        let boxBloqueio = $('#box-bloqueio');
        boxBloqueio.addClass('hidden')
    }
});