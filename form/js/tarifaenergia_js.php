<?
require_once(__DIR__."/../controllers/tarifaenergia_controller.php");

$intervalos = PrecoEnergiaController::buscarIntervalosExistentes($_1_u_tarifaenergiapadrao_idtarifaenergiapadrao);

$jsonIntervalos = json_encode($intervalos);
?>

<script type="text/Javascript">


    var intervalos = <?php echo $jsonIntervalos; ?>;
    console.log(intervalos);

    function transformarEmDate(hora) {
        const [hours, minutes] = hora.split(':');
        const date = new Date();
        date.setHours(parseInt(hours, 10), parseInt(minutes, 10), 0, 0);
        return date;
    }

    function verificarConflito(inicio, fim) {
        const inicioNovo = transformarEmDate(inicio).getTime();
        const fimNovo = transformarEmDate(fim).getTime();

        for (let intervalo of intervalos.dados) {
            let inicioExistente = transformarEmDate(intervalo.inicio).getTime();
            let fimExistente = transformarEmDate(intervalo.fim).getTime();

            if ((inicioNovo >= inicioExistente && inicioNovo <= fimExistente) || 
                (fimNovo >= inicioExistente && fimNovo <= fimExistente) ||
                (inicioExistente >= inicioNovo && fimExistente <= fimNovo)) {
                return true;
            }
        }
        return false;
    }

    function CBpostconferencia() {
        
        const inicio = document.getElementById('inicioverifica').value;
        const fim = document.getElementById('fimverifica').value;

        const inicioDate = transformarEmDate(inicio);
        const fimDate = transformarEmDate(fim);

        if (inicioDate >= fimDate) {
            alert('O horário de início deve ser menor que o horário de fim.');
            return;
        }

        if (verificarConflito(inicio, fim)) {
            alert('Já existe um intervalo de tempo salvo neste período.');
        } else {
            CB.post();
        }
    }
    
    function abrirModalTarifaPico (idTarifa) {
    const modalHTML = `
                    <div class="panel-body">
						<table>
						    <input name="_h1_i_tarifaenergiapico_idtarifaenergiapadrao" value="${idTarifa}" type="hidden">
						<tr>
                            <td align="right">Valor de pico:</td>
                            <td>
                                 <input vnulo placeholder="Adicione valor de cobrança" name="_h1_i_tarifaenergiapico_valor" type="text" id="valorpico" size="28">
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Inicio:</td>
                            <td>
                                <input vnulo name="_h1_i_tarifaenergiapico_inicio" type="time" id="inicioverifica" size="20">
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Fim:</td>
                            <td>
                                <input vnulo name="_h1_i_tarifaenergiapico_fim" type="time" id="fimverifica" size="20">
                            </td>
                        </tr>
						</table>
		            </div>
                    `;
                strCabecalho = "</strong>Adicionar Horário de Pico " + "  <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CBpostconferencia();'><i class='fa fa-circle'></i>Salvar</button></strong>";
		CB.modal({
            
			titulo: strCabecalho,
			corpo: modalHTML,
			classe: 'sessenta'

            
		});
    }

        
        function abrirModalAlteraPico (idTarifaPico, valorPico, inicioPico, fimPico) {
        const modalHTML = `
                        <div class="panel-body">
                            <table>
                                <input name="_h1_u_tarifaenergiapico_idtarifaenergiapico" type="hidden" readonly value="${idTarifaPico}">
                            <tr>
                                <td align="right">Valor de pico:</td>
                                <td>
                                    <input vnulo name="_h1_u_tarifaenergiapico_valor" value="${valorPico}" type="text" id="valorpico" size="28">
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Inicio:</td>
                                <td>
                                    <input vnulo name="_h1_u_tarifaenergiapico_inicio" type="time" value="${inicioPico}" id="inicioverifica" size="20">
                                </td>
                            </tr>
                            <tr>
                                <td align="right">Fim:</td>
                                <td>
                                    <input vnulo name="_h1_u_tarifaenergiapico_fim"  type="time" value="${fimPico}" id="fimverifica" size="20">
                                </td>
                            </tr>
                            </table>
                        </div>
                        `;

                        strCabecalho = "</strong>Alterar Valor de Pico " + "  <button id='cbSalvar' type='button' class='btn btn-success btn-xs' onclick='CB.post();'><i class='fa fa-circle'></i>Salvar</button></strong>";
            CB.modal({
                
                titulo: strCabecalho,
                corpo: modalHTML,
                classe: 'sessenta'

                
            });
        }

        if(typeof ST != 'undefined') {
            const idTarifaPadrao = $("#idtarifaenergiapadrao").val();
            ST.customFunction = (idfluxo, idfluxostatushist, idfluxostatus, idstatusf, statustipo, idfluxostatuspessoa, ocultar, prioridade, tipobotao, idcarrimbo, log = 0) => {
			    if(statustipo == 'ATIVO') {
                    if(!confirm('Se a tarifa estiver ativa, o valor da cobrança será calculado. Lembre-se: só pode haver uma tarifa ativa.')) return false;
                    
                    $passa = true;

                    $.ajax({
                        method: 'GET',
                        url: '/../../ajax/tarifaenergia.php',
                        dataType: 'json',
                        async: false,
                        data: {
                            action: 'VerificaStatusTipoCobranca',
                            params: idTarifaPadrao
                        },  
                        success: res => {
                            if (res.error){
                                $passa = false;
                                return alertAtencao(res.error)
                            };
                        },
                        err: res => {
                            $passa = false;
                            console.log(res);
                        }
                        
                    });
                    return $passa;
			    };

                return true;
		    } 
        }
</script>