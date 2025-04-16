<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../inc/php/validaacesso.php");
require_once("../inc/php/laudo.php");

if ($_POST) {
	require_once(_CARBON_ROOT . "inc/php/cbpost.php");
}

require_once(__DIR__ . "/controllers/enviopedidoamostra_controller.php");
require_once(__DIR__ . "/controllers/amostra_controller.php");
require_once(__DIR__ . "/controllers/prodserv_controller.php");

$testes = ProdservController::buscarProdservServicosExames();

function jsonTipoSubtipo(){
	return AmostraController::buscarSubtipoamostraPorIdunidade(1);
}

require_once(__DIR__ . "/controllers/empresa_controller.php");
$empresasvinculadas = EmpresaController::buscarEmpresasVinculadasAPessoa($_SESSION['SESSAO']['IDPESSOA']);
$subtipoamostras = json_decode(jsonTipoSubtipo(), true);
?>

<style>
	.required {
		transition: .3s ease 0s;
	}

	.required.error {
		border: 2px solid red;
		background-color: #ff404012;
	}
</style>
<div id="app" class="ww-full flex flex-col m-auto gap-6 justify-center items-center pl-3 pr-3 mt-8">
	<div class="w-full flex flex-col m-auto  gap-6 justify-center items-center">

		<span class="w-full text-2xl md:text-4xl text-center font-medium text-[#40464F]">
			Insira as informações da amostra do PET
		</span>

		<span class="w-11/12 text-base md:text-xl text-start">
			Encaminhar um formulário por animal e enviá-lo juntamente com as amostras para o laboratório. As amostras
			devem estar devidamente identificadas. Os Campos com * são de preenchimento obrigatório e essenciais para
			realizarmos o cadastro.
		</span>

		<div id="form-dados-cliente" class="w-full flex flex-wrap rounded-md border border-[#C0C0C0] m-6 '">
			<span class="w-full text-white py-2 primary-bg text-center rounded font-bold">Dados do cliente</span>
			<div class="w-full p-4 flex flex-col bg-[#F5F5F5] gap-2">
				<!-- Nome do Animal -->
				<div class="flex gap-3">
					<div class="w-11/12">
						<span class="text-xs text-[#989898]">Nome do Animal <span class="text-red-600">*</span></span>
						<input id="nomeAnimal" name="paciente" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666] required" type="text" placeholder="Informe o nome do animal" />
					</div>
					<div class="flex flex-col">
						<span class="text-xs text-[#989898] mt-auto">Urgente <span class="text-red-600">*</span></span>
						<div class="w-full flex justify-center items-center gap-4 my-auto">
							<div class="flex gap-2 text-[#666666]">
								<input id="modo-sim" name="urgente_animal" type="radio" value="1">
								<label for="modo-sim" class="cursor-pointer">Sim</label>
							</div>
							<div class="flex gap-2 text-[#666666]">
								<input id="modo-nao" name="urgente_animal" type="radio" value="0" checked>
								<label for="modo-nao" class="cursor-pointer">Não</label>
							</div>
						</div>
					</div>
				</div>
				<!-- Tutor Responsável -->
				<div class="w-full">
					<span class="text-xs text-[#989898]">Tutor Responsável <span class="text-red-600">*</span></span>
					<input id="tutor" name="tutor" class="required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o nome do tutor/responsável" />
				</div>
				<div class="w-full flex flex-col md:flex-row gap-2">
					<!-- Razão social -->
					<div class="md:w-6/12">
						<span class="text-xs text-[#989898]">Razão social <span class="text-red-600">*</span></span>
						<select id="razaoSocial" name="razao_social" class="required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]">
							<?php foreach ($empresasvinculadas as $empresa) { ?>
								<option value="" disable>Selecione uma opção</option>
								<option value="<?= $empresa['idpessoa'] ?>">
									<?= $empresa['razao_cpfcnpj'] ?>
								</option>
							<? } ?>
						</select>
					</div>
					<!-- Clínica veterinária -->
					<div class="md:w-6/12">
						<span class="text-xs text-[#989898]"> Clínica veterinária <span class="text-red-600">*</span></span>
						<input id="clinicaVeterinaria" name="clinica_veterinaria" class="readonly required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o nome da clínica veterinária" />
					</div>
				</div>
				<!-- Sexo/idade(Anos)/idade(meses)/idade(dias) -->
				<div class="w-full flex flex-col md:flex-row gap-2">
					<!-- Idade (Anos)-->
					<div class="w-full md:w-6/12">
						<span class="text-xs text-[#989898]">Idade <span class="text-red-600">*</span></span>
						<input id="idade" name="idade" class="required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe a idade" />
					</div>
					<!-- Idade (Meses)* -->
					<div class="w-full md:w-6/12">
						<span class="text-xs text-[#989898]">Período <span class="text-red-600">*</span></span>
						<select id="periodo" name="periodo" class="required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]">
							<option class="font-light" disabled selected>
								Selecione uma opção
							</option>
							<option value="Dia(s)">Dia(s)</option>
							<option value="G">Dias de Gestação</option>
							<option value="Semana(s)">Semanas(s)</option>
							<option value="Mês(es)">Mês(es)</option>
							<option value="Ano(s)">Ano(s)</option>
							<option value="ª Progênie">Progênie</option>
						</select>
					</div>
				</div>
				<!-- Raça / Espécie -->
				<div class="w-full flex flex-col md:flex-row gap-2">
					<!-- Espécie -->
					<div class="w-full md:w-6/12">
						<div><span class="text-xs text-[#989898]">Espécie</span> <span class="text-red-600">*</span></span></div>
					
						<select id="especie" name="especie"
							class="form-control select2 required w-full  bg-white border border-[#DDDDDD] 
							rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] text-[#666666]" 
							type="text" placeholder="Informe a espécie">
							<option value="" disabled selected>Selecione uma opção</option>
							<?php
							$especiefinalidades = json_decode(AmostraController::buscarEspeciefinalidade(cb::idempresa()));
							foreach ($especiefinalidades as $id => $especie) { ?>
								<option value="<?= $id ?>">
									<?= $especie->especie.' / '.$especie->finalidade ?>
								</option>
							<? } ?>
						</select>
					</div>
					<!-- Sexo -->
					<div class="w-full md:w-6/12">
						<span class="text-xs text-[#989898]">Sexagem <span class="text-red-600">*</span></span>
						<select id="sexagem" name="sexagem" class="required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]">
							<option class="font-light" disabled selected>
								Selecione uma opção
							</option>
							<option value="Macho">Macho</option>
							<option value="Fêmea">Fêmea</option>
						</select>
					</div>
				</div>
				<!-- Veterinário / CRMV / Clínica veterinária -->
				<div class="w-full flex flex-col md:flex-row gap-2">
					<!-- Veterinário -->
					<div class="w-full md:w-6/12">
						<span class="text-xs text-[#989898]">Veterinário <span class="text-red-600">*</span></span>
						<input id="veterinario" name="veterinario" class="required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o nome do veterinário" />
					</div>
					<!-- CRMV -->
					<div class="w-full md:w-6/12">
						<span class="text-xs text-[#989898]">CRMV<span class="text-red-600">*</span></span>
						<input id="crmv" name="crmv" class="required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o CRMV" />
					</div>

					<!-- UF-->
					<div class="w-full md:w-6/12">
						<span class="text-xs text-[#989898]"> UF <span class="text-red-600">*</span></span>
						<input id="uf" name="uf" class="required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o unidade federativa do veterináreio " />
					</div>
				</div>
				<div class="w-full flex flex-col md:flex-row gap-2">
					<!-- E-mail -->
					<div class="w-full md:w-12/12">
						<span class="text-xs text-[#989898]">E-mail</span> <span class="text-red-600">*</span></span>
						<input id="email" name="email" class="required w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe o e-mail do veterinário" />
					</div>
					
				</div>
				<!-- Observação -->
				<div class="w-full">
					<span class="text-xs text-[#989898]">Observação</span>
					<textarea id="observacao" name="clinica_observacao" class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe uma observação, caso necessário"></textarea>
				</div>
			</div>
			<?
			/* <div class="w-full p-4 flex flex-col bg-[#F5F5F5] gap-2 ">
				<!-- Material enviado -->
				<span class="text-xs text-[#989898]">Material enviado</span>
				<!-- Lista de checkbox  Material enviado  -->
				<div class="w-full 2xl:w-10/12 flex flex-col xl:flex-row justify-between">
					<? foreach (EnvioPedidoAmostraController::$materialEnviadoCheck as $key => $material) { ?>
						<div class="w-2/12 flex gap-2">
							<input id="materia-<?= $key ?>" name="material_enviado" type="checkbox" value="<?= $material['value'] ?>">
							<label for="materia-<?= $key ?>" class="text-[#666666] font-light cursor-pointer"><?= $material['label'] ?></label>
						</div>
					<? } ?>
				</div>
			</div>
			<!-- Lista de checkbox Descrição Material Enviado  -->
			<div class="w-full p-4 flex flex-col bg-[#F5F5F5] gap-2 ">
				<span class="text-xs text-[#989898]">Descrição Material Enviado</span>
				<div class="w-full 2xl:w-10/12 flex flex-col xl:flex-row flex-wrap">
					<!-- Medula Ossea / Swab Pele -->
					<? foreach (EnvioPedidoAmostraController::$descricaoMaterialEnviado as $key => $material) { ?>
						<div class="w-2/12 flex gap-2 mb-2">
							<input id="descricao-material-<?= $key ?>" name="descricao_material_enviado" type="checkbox" value="<?= $material['value'] ?>">
							<label for="descricao-material-<?= $key ?>" class="cursor-pointer text-[#666666] font-light"><?= $material['label'] ?></label>
						</div>
					<? } ?>
				</div>
			</div> */
			?>
		</div>

		<!-- Amostra -->
		<div id="form-exames" class="hidden w-full flex flex-wrap">
			<div id="form-amostras" class=" w-full flex flex-wrap rounded-md border border-[#C0C0C0]">
				<span class="w-full text-white py-2 primary-bg text-center rounded font-bold">Dados do PET - Cadastro de Amostras</span>
				<div class="w-full p-4 flex flex-col bg-[#F5F5F5] gap-2">
					<!-- Exame / Data da coleta / urgente -->
					<div class="w-full flex flex-col md:flex-row gap-2 justify-between">
						<!-- Exame -->
						<div class="w-full md:w-5/12">
							<span class="text-xs text-[#989898]">Informe o tipo da amostra <span class="text-red-600">*</span></span>
							
							<select id="subtipoamostra-amostra" name="subtipo_mostra"
								class="form-control select2 required w-full  bg-white border border-[#DDDDDD] 
								rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] text-[#666666]" 
								type="text" placeholder="Informe o tipo amostra">
							</select>
						</div>
						<!-- Data da Coleta -->
						<div class="w-full md:w-4/12">
							<span class="text-xs text-[#989898]">Informe a data da coleta</span>
							<input id="subtipoamostra-data-coleta" class="w-full p-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="date" placeholder="Informe a data da coleta" />
						</div>
						<!-- Urgente* -->
						<div class="flex flex-col">
							<span class="text-xs text-[#989898] mt-auto">Urgente <span class="text-red-600">*</span></span>
							<div class="w-full flex md:justify-center items-center gap-4 my-auto">
								<div class="flex gap-2 text-[#666666]">
									<input id="subtipoamostra-urgente-nao" class="required" name="subtipoamostra_urgente_envio" checked type="radio" value="0">
									<label class="cursor-pointer" for="urgente-nao">Não</label>
								</div>
								<div class="flex gap-2 text-[#666666]">
									<input id="subtipoamostra-urgente-sim" class="required" name="subtipoamostra_urgente_envio" type="radio" value="1">
									<label class="cursor-pointer" for="urgente-sim">Sim</label>
								</div>
							</div>
						</div>
						<div class="w-full md:w-5/12 xl:w-2/12 4xl:w-1/12 flex md:justify-center items-end mb-2">
							<button class="primary-bg rounded-md border-none py-2 px-4 text-white font-bold" onclick="adicionarAmostra();"> Adicionar amostra</button>
						</div>
					</div>
				</div>
			</div>
			<div id="corpo-amostras" class="w-full"></div>
		</div>

		<!-- Modo de envio -->
		<div id="modo-envio" class="hidden w-full flex flex-wrap rounded-md border border-[#C0C0C0]">
			<span class="w-full text-white py-2 primary-bg text-center rounded font-bold">Dados do PET - Modo de Envio</span>
			<div class="w-full p-4 flex flex-col bg-[#F5F5F5] gap-2">
				<!-- Modo de Envio / Data de Requisição / Hora de Requisição -->
				<div class="w-full flex flex-col md:flex-row gap-2">
					<!-- Modo de Envio -->
					<div class="w-full md:w-6/12">
						<span class="text-xs text-[#989898]">Modo de Envio <span class="text-red-600">*</span></span>
						<input id="input-modo-envio" name="input-modo-envio"  class="w-full py-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666] required" type="text" placeholder="Informe o modo de envio.">
					</div>
					<!-- Data de Requisição -->
					<div class="w-full md:w-6/12">
						<span class="text-xs text-[#989898]">Data de Requisição</span>
						<input id="data-requisicao"  name="data-requisicao" class="w-full p-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="date" placeholder="Informe a data de requisição">
					</div>
					<!-- Hora de Requisição -->
					<div class="w-full md:w-6/12">
						<span class="text-xs text-[#989898]">Hora de Requisição</span>
						<input id="hora-requisicao"  name="hora-requisicao" class="w-full p-3 bg-white border border-[#DDDDDD] rounded focus:outline-none focus:ring-1 focus:ring-[#d1d1d1] ps-3 text-[#666666]" type="text" placeholder="Informe a hora de requisição">
					</div>
				</div>
			</div>
		</div>

		<!-- Botões Cancelar e Próxim¾o -->
		<div class="w-full flex justify-between mt-4 mb-24">
			<a id="btn-cancelar" class="py-2 primary-text font-bold rounded" href="/?_modulo=contatomenurapido">
				Cancelar
			</a>
			<a id="btn-voltar" class="hidden p-2 px-4 primary-text font-bold rounded cursor-pointer" onclick="voltar(event)">
				Voltar
			</a>
			<a id="btn-proximo" class="p-2 px-4 primary-bg font-bold rounded text-white cursor-pointer" onclick="avancar(event)">
				Próximo
			</a>
			<button id="btn-enviar-laboratorio" class="hidden p-2 px-4 bg-[#178B94] font-bold rounded text-white" onclick="finalizar()">
				Enviar para laboratório
			</button>
		</div>
	</div>
</div>

<script>
	// Função para preencher os campos ao clicar no botão (opcional)
	function preencherCampos() {
		// Chamando a função de preenchimento automático
		document.dispatchEvent(new Event('DOMContentLoaded'));
	}
	function carregaEspecie(){
		$($('#especie-ts-control'))[0].addEventListener('change',function(ev) {
			console.log(ev);
			$("#especiefinalidade").val($(ev.target).val());
		});
	}

	let subtipoamostras = <?= jsonTipoSubtipo() ?>;
	let empresasvinculadas = <?= json_encode($empresasvinculadas); ?>;

	console.log(empresasvinculadas);
	
	$('#razaoSocial').on('change', function(ev){
		console.log(ev.target.value);
		const itemIndex = empresasvinculadas.findIndex(empresavinculada => empresavinculada.idpessoa === ev.target.value);
		if (itemIndex==0)
			$('#clinicaVeterinaria').val(empresasvinculadas[itemIndex].nome?empresasvinculadas[itemIndex].nome:empresasvinculadas[itemIndex].razao);
		else
			$('#clinicaVeterinaria').val("");
	})

	window.options = subtipoamostras[1].map(function(ev) {
		return {value:ev.value,text:ev.label};
	});

/* 	
	window.testes = testes.map(function(ev) {
		return {value:ev.idprodserv,text:ev.descr};
	}); */

	$(document).ready(function() {
		new TomSelect('#especie',{});
		new TomSelect('#subtipoamostra-amostra',{options:options});
	});
	
	console.log(subtipoamostras[1]);
</script>
<style>
/* Sobrescreva os estilos padrão do Select2 */
/* .select2-container .select2-selection--single {
    @apply bg-white border border-gray-300 rounded px-4 py-2 focus:ring focus:border-blue-300;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    @apply text-gray-500;
} */
 .ts-control{
	padding: 0.85rem !important;
 }
 .botao-remover:hover{
	cursor: pointer;
	color: rgb(220 38 38 / var(--tw-text-opacity)) !important;
 }
 select {
	padding: 0.85rem !important;
 }
 .readonly {
    pointer-events: none;  /* Desabilita todos os eventos de mouse, incluindo cliques */
    user-select: none;     /* Impede a seleção de texto */
    background-color: #fafafa;  /* Torna o fundo cinza para indicar que está desativado (opcional) */
    color: #999;           /* Altera a cor do texto para parecer desativado (opcional) */
}
</style>
<? require_once(__DIR__ . "/js/enviopedidoamostra_js.php") ?>