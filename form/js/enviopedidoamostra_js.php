<script>
    
    $('.selectpicker').selectpicker('render');
    
    dadosCliente = {},
    etapaAtual = 0;
    etapas = [
        'form-dados-cliente',
        'form-exames',
        'modo-envio'
    ];
    let testes = <?= json_encode($testes) ?>;
    exames = [];
    amostras = [];

    const modalFinalizarHTML = (protocolo) =>{ 
    return `<div class="w-full flex-col py-4">
        <div class="flex flex-col items-center gap-10">
            <span class="text-base text-center font-bold md:text-4xl px-2">O pedido de envio de amostra <br> foi realizado com sucesso!</span>
            <div>
                <img class="md:h-80" src="/inc/img/contatomenurapido/sucesso.svg" alt="">
            </div>
            <div class="w-full flex flex-col gap-2 justify-center items-center md:text-xl font-light px-2 text-center md:px-0 md:text-start">
                <span>Assim que o pedido for aprovado, entraremos em contato!</span>
            </div>
            
            <div class="w-full flex flex-col gap-2 justify-center items-center md:text-xl font-light px-2 text-center md:px-0 md:text-start">
                <a class="text-xl md:text-3xl font-bold text-[#176292] underline" target="_blank" href="/report/emissaoreciboenvioamostra.php/?protocolo=${protocolo}">Recibo em PDF</a>
            </div>
            
            <a class="text-xl md:text-3xl font-bold text-[#176292] underline" href="/?_modulo=contatomenurapido">Voltar</a>
        </div>
    </div>`};
    

    // Remover erro ao digitar no campo
    $('.required').on('blur', function() {
        const elementJQ = $(this);
        if (elementJQ.val() && elementJQ.hasClass('error')) elementJQ.removeClass('error');
    });

    function avancar(e) {
        e.preventDefault();
        if (!validarCamposObrigatorios()) return alertAtencao('Preencha os campos obrigatórios.');

        if (etapaAtual === 0){
            atualizarCamposCliente();
        }

        // Validar etapa 2
        if (etapaAtual === 1 && !validarExames()) {
            // if($('#subtipoamostra-amostra').length){
            //     new TomSelect('#subtipoamostra-amostra',{options:options});
            // }
            return alertAtencao('Adicione o exame.');
        }

        // Se for a ultima etapa, criar amostra

        alterarEtapa(etapaAtual + 1);

        atualizarBotaoVoltar();
        atualizarBotaoAvancar();
    }

    function voltar(e) {
        e.preventDefault();
        alterarEtapa(etapaAtual - 1);
        atualizarBotaoVoltar();
        atualizarBotaoAvancar();
    }

    finalizar = async () => {
        // Valida os campos obrigatórios
        if (!validarCamposObrigatorios()) return alertAtencao('Preencha os campos obrigatórios.');

        // Prepara os dados de envio
        let dadosEnvio = {
            modoEnvio: $('#input-modo-envio').val(),
            dataRequisicao: $('#data-requisicao').val(),
            horaRequisicao: $('#hora-requisicao').val()
        };
        
        // Cria o objeto params
        let params = {
            'dadosCliente': dadosCliente,
            'amostras': amostras,
            'dadosEnvio': dadosEnvio
        };

        console.log(params);

        // Faz a requisição AJAX
        await $.ajax({
            method: 'POST',
            url: '../../ajax/enviopedidoamostra.php',
            data: {
                action: 'gerarAmostra',
                params: JSON.stringify(params)
            },
            beforeSend: function() {
                // Função antes do envio (vazia ou com alguma lógica opcional)
            },
            success: function(data, textStatus, jqXHR) {
                // Depuração
                debugger;
                data = JSON.parse(data);
                // Verifica se a resposta é bem-sucedida
                if (data.success) {
                    // Exibe o modal de sucesso
                    modal('Pedido de envio de amostra', modalFinalizarHTML(data.protocolo));
                } else {
                    // Exibe a mensagem de erro
                    alertErro(data.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // Lida com erros de requisição AJAX
                alertErro('Ocorreu um erro: ' + textStatus);
            }
        });
    }

    function validarExames() {
        return amostras.map(function(data){
            return data.subtipoamostraId && data.exames.length;
        });
    }

    function atualizarCamposCliente() {
        const dadosClienteJQ = $('#form-dados-cliente input, #form-dados-cliente select, #form-dados-cliente textarea').get();

        dadosClienteJQ.forEach(item => {
            let elementJQ = $(item);

            if (elementJQ.attr('name')) {
                if (item.type === 'checkbox') {
                    if (elementJQ.is(':checked')) {
                        if (!dadosCliente[elementJQ.attr('name')]) dadosCliente[elementJQ.attr('name')] = [];

                        dadosCliente[elementJQ.attr('name')].push(elementJQ.val())
                    }
                } else if (item.type === 'radio') {
                    if (item.checked) {
                        dadosCliente[elementJQ.attr('name')] = elementJQ.val();
                    }
                } else if (item.tagName === 'SELECT') {
                    dadosCliente[elementJQ.attr('name')] = elementJQ.val();
                } else {
                    dadosCliente[elementJQ.attr('name')] = elementJQ.val();
                }
            }
        });

        console.log(dadosCliente);
    }

    // Validar campos obrigatórios
    function validarCamposObrigatorios() {
        const camposObrigatoriosJQ = $('.required').filter(function() {
            let elementJQ = $(this);

            if (elementJQ.attr('type') == 'radio') {
                return $(this).is(':visible') && !$(`[name="${elementJQ.attr('name')}"]`).get().some(item => item.checked);
            }

            return $(this).is(':visible') && !$(this).val()
        });

        if (!camposObrigatoriosJQ.length && (etapaAtual === 1 && exames.length)) return true;

        camposObrigatoriosJQ.addClass('error');

        return !camposObrigatoriosJQ.length;
    }

    function alterarEtapa(novaEtapa) {
        const etapaJQ = $(`#${etapas[novaEtapa]}`);

        if (etapaJQ.length) {
            const etapaAnteriorJQ = $(`#${etapas[etapaAtual]}`);

            etapaAnteriorJQ
                .removeClass('flex')
                .addClass('hidden');

            etapaJQ
                .addClass('flex')
                .removeClass('hidden');
        }

        etapaAtual = novaEtapa;
    }

    function processaMateriais() {
        let params = {}
        console.log(Object.keys(params).map(key => `${key}=${params[key]}`).join('&'));
    }

    function atualizarBotaoVoltar() {
        if (!etapaAtual) {
            $('#btn-cancelar')
                .removeClass('hidden')
                .addClass('block');

            $('#btn-voltar')
                .removeClass('block')
                .addClass('hidden');
        } else {
            $('#btn-cancelar')
                .addClass('hidden')
                .removeClass('block');

            $('#btn-voltar')
                .addClass('block')
                .removeClass('hidden');
        }
    }

    function atualizarBotaoAvancar() {
        if (etapaAtual === 2) {
            $('#btn-enviar-laboratorio')
                .removeClass('hidden')
                .addClass('block');

            $('#btn-proximo')
                .addClass('hidden')
                .removeClass('block');
        } else {
            $('#btn-proximo')
                .removeClass('hidden')
                .addClass('block');

            $('#btn-enviar-laboratorio')
                .removeClass('block')
                .addClass('hidden');
        }
    }

    adicionarExame = async (amostraIndex, amostraKey) => {
        if (!validarCamposObrigatorios()) return alertAtencao('Preencha os campos obrigatórios.');

        const exame = buscarExame($('#input-exame-'+amostraKey).val());
        
        const dadosExame = {
            key: (new Date().getTime()),
            idexame: exame.idprodserv,
            exame: exame.descr,
            urgente: $('input[name=urgente_envio_'+amostraKey+']:checked').val()
        };

        const exameHTML = montarExameHTML(amostraKey,dadosExame);
        const corpoTabelaExamesJQ = $('#corpo-exames-'+amostraKey);

        // Adicionando exame na variavel
        if(typeof amostras[amostraIndex-1].exames == 'undefined'){
            amostras[amostraIndex-1].exames = [];
        }
        amostras[amostraIndex-1].exames.push(dadosExame);


        await corpoTabelaExamesJQ.append(exameHTML);
    }

    adicionarAmostra = async () => {
        if (!validarCamposObrigatorios()) return alertAtencao('Preencha os campos obrigatórios.');
        
        const dadosAmostra = {
            key: (new Date().getTime()),
            idsubtipoamostra: ($('#subtipoamostra-amostra').val()),
            subtipoamostra: buscarSubtipoamostra($('#subtipoamostra-amostra').val()),
            dataColeta: $('#subtipoamostra-data-coleta').val(),
            urgente: ($('input[name=subtipoamostra_urgente_envio]:checked').val())
        };
        console.log(dadosAmostra)
        amostras.push(dadosAmostra);
        console.log(amostras)
        
        const amostraHTML = montarAmostraHTML(amostras.length, dadosAmostra);
        const corpoTabelaAmostra = $('#corpo-amostras');

       await corpoTabelaAmostra.append(amostraHTML);

        new TomSelect('#input-exame-'+dadosAmostra.key);
    }
    
    removerAmostra = (index) => {
        if (confirm("Deseja remover essa amostra?") == true) {
            // Encontrar o índice do item com a chave correspondente
            let itemIndex = amostras.findIndex(amostra => amostra.key == index);
            console.log(itemIndex);

            if (itemIndex !== -1) { // Se o item for encontrado
                // Remover o item do array
                amostras.splice(itemIndex, 1);
                
                // Remover o item do DOM (opcional)
                document.getElementById('form-amostras-'+index).remove();
            } else {
                console.log("Amostra não encontrada.");
            }
        }
    }

    removerExame = (index, indexExame) => {
        if (confirm("Deseja remover esse exame?") == true) {
            // Encontrar o índice do item com a chave correspondente
            let itemIndex = amostras.findIndex(amostra => amostra.key == index);
            console.log(itemIndex);
            let itemIndexExame = amostras[itemIndex].exames.findIndex(exame => exame.key == indexExame);
            console.log(itemIndexExame);

            if (itemIndexExame !== -1) { // Se o item for encontrado
                // Remover o item do array
                amostras[itemIndex].exames.splice(itemIndexExame, 1);
                
                // Remover o item do DOM (opcional)
                document.getElementById('exame-'+indexExame).remove();

            } else {
                console.log("Exame não encontrado.");
            }
        }
    }

    function montarAmostraHTML(index,amostra) {
        return `
            <div id="form-amostras-${amostra.key}" class="mt-4 w-full flex flex-wrap rounded-md border border-[#C0C0C0]">
			    <span class="text-center w-full text-white p-2 primary-bg rounded font-bold flex justify-between">
                    <span></span>
                    <span>AMOSTRA - ${amostra.subtipoamostra.text}</span>
                    <i onclick="removerAmostra(${amostra.key})" class="botao-remover material-icons">delete</i>
                </span>
                    <div class="w-full flex p-4 bg-[#F5F5F5] gap-2">
                        <div class="w-full md:w-4/12">
                            <span class="text-xs text-[#989898]">Tipo amostra</span></span>
                            <input id="tipoamostra-${index}" name="tipoamostra-${index}" class="w-full py-3 bg-white border border-[#DDDDDD] 
                            rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" 
                            value="${amostra.subtipoamostra.text}">
                            <input type="hidden" name="idtipoamostra-${index}"  value="${amostra.idsubtipoamostra}"/>
                        </div>
                        <div class="w-full md:w-4/12">
                            <span class="text-xs text-[#989898]">Data de coleta</span></span>
                            <input id="datacoleta-${index}" name="datacoleta-${index}" class="w-full py-3 bg-white border border-[#DDDDDD] 
                            rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" 
                            value="${amostra.dataColeta ? formatarData(amostra.dataColeta): 'Não informada'}">
                        </div>
                        <div class="w-full md:w-4/12">
                            <span class="text-xs text-[#989898]">Urgente</span></span>
                            <input id="urgente-${index}" name="urgente-${index}" class="w-full py-3 bg-white border border-[#DDDDDD] 
                            rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" 
                            value="${amostra.urgente==1 ? 'Sim' : 'Não'}">
                        </div>
                    </div>
			    
                    ${montarFormExameHTML(index,amostra.key)}
                 </div>
            </div>
            `;
    }

    function buscarExame(id) {
        return testes.find(item => item.idprodserv == id);
    }

    function buscarSubtipoamostra(id) {
        return options.find(item => item.value == id);
    }

    function montarFormExameHTML(index,amostraKey) {
        return `
        <div id="form-exames-${amostraKey}" class="w-full flex flex-wrap">
            <div class="w-full px-4 flex flex-col bg-[#F5F5F5] gap-2 border-t-2 border-[#cccccc]">
			    <span class="w-full text-[#989898] py-2 rounded font-bold">Cadastro de Exames</span>
            </div>
			<div class="w-full px-4 flex flex-col bg-[#F5F5F5] gap-2">
				<!-- Exame / Data da coleta / urgente -->
				<div class="w-full flex flex-col md:flex-row gap-2">
					<!-- Exame -->
					<div class="w-full md:w-9/12">
						<span class="text-xs text-[#989898]">Exame <span class="text-red-600">*</span></span>
						<select id="input-exame-${amostraKey}"
                            class="form-control select2 required w-full bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] text-[#666666]" type="text" placeholder="Informe o exame">
                            <option value="" disabled selected>Selecione uma opção</option>
                            ${testes.map(function(teste){ return `<option value="${teste.idprodserv}">${teste.descr}</option>`; }).join()}						
						</select>
					</div>
					<!-- Urgente* -->
					<div class="flex flex-col">
						<span class="text-xs text-[#989898] mt-auto">Urgente <span class="text-red-600">*</span></span>
						<div class="w-full flex md:justify-center items-center gap-4 my-auto">
							<div class="flex gap-2 text-[#666666]">
								<input id="urgente-nao-${amostraKey}" class="required" name="urgente_envio_${amostraKey}" checked type="radio" value="0">
								<label class="cursor-pointer" for="urgente-nao-${amostraKey}">Não</label>
							</div>
							<div class="flex gap-2 text-[#666666]">
								<input id="urgente-sim-${amostraKey}" class="required" name="urgente_envio_${amostraKey}" type="radio" value="1">
								<label class="cursor-pointer" for="urgente-sim-${amostraKey}">Sim</label>
							</div>
						</div>
					</div>
					<div class="w-full md:w-3/12 xl:w-2/12 4xl:w-1/12 flex md:justify-center items-end mb-2">
						<button class="primary-bg rounded-md border-none py-2 px-4 text-white font-bold" onclick="adicionarExame(${index},${amostraKey});"> Adicionar Exame</button>
					</div>
				</div>
				<!-- Header -->
				<div class="pt-4 w-full flex">
					<div class="w-10/12">
						<span>Exame</span>
					</div>
					<div class="w-2/12">
						<span>Urgente</span>
					</div>
				</div>
				<!-- Body -->
                </div>
			<div id="corpo-exames-${amostraKey}" class="w-full pt-4 border-[#cccccc] bg-[#F5F5F5]"></div>
		</div>
        `;
    }

    function montarExameHTML(amostraKey,exame) {
        return `<div id="exame-${exame.key}" class="w-full flex flex-wrap border-t-2 p-4 border-[#cccccc]">
                    <div class="border-l-3 pl-3 border-[#178b94] w-10/12">
                        <span>${exame.exame}</span>
                    </div>
                    
                    <div class="w-2/12 flex justify-between">
                        <span>${exame.urgente==1 ? 'Sim' : 'Não'}</span>
                        <button class="" onclick="removerExame('${amostraKey}','${exame.key}')"><i class="botao-remover material-icons">delete</i></button>
                    </div>
                </div>`;
    }

    CB.on("prePost", function(inParam) {

        if (inParam === undefined) {
            inParam = {
                objetos: {}
            };
        }

        if (inParam.objetos === undefined || inParam.parcial !== true) {
            return;
            $(":input[name=_1_i_amostra_observacao]").val(inParam);
        }
        console.log(inParam)

        return false;
    });

    function formatarData(data) {
        const partes = data.split("-"); // ["2024", "12", "12"]
        return `${partes[2]}/${partes[1]}/${partes[0]}`; // Saída: 12/12/2024
    }
    //#sourceURL=enviaJS
</script>