<?php
require_once "../inc/php/functions.php";

// Valida token de acesso
$jwt = validaTokenReduzido();
if ($jwt["sucesso"] !== true)
{
	echo json_encode(['error' => "Erro: Não autorizado."]);
	die;
}

// Verifica se uma acão de handler foi enviada
$action = $_GET['action'] ?? $_POST['action'];

if($action)
{
	// CONTROLLERS
	require_once "../form/controllers/enviopedidoamostra_controller.php";
	require_once "../form/controllers/amostra_controller.php";

	// Verifica se os parametros enviados corretamente
	$params = $_GET['params'] ?? $_POST['params'];
	// Parse de json
	$params = json_decode($params, true);

	// Chama a função do controller correspondente por handler
	return $action($params);
}

// Função para adicionar amostras do pedido
function gerarAmostra($dados)
{
	try
	{
		// Toda amostra envida pelo cliente deve ter o status PROVISORIO
		// A ser checado por nosso time de veterinarios
		$dados['dadosCliente']['status'] = 'PROVISORIO';

		// Gerar numero de protocolo em sha1 com criadoem e idpessoa
		$dados['dadosCliente']['protocolo'] = sha1(date('YmdHis'). $_SESSION["SESSAO"]["USUARIO"]);

		// Verificar se as amostras foram enviadas correctamente
		if (!is_array($dados['amostras']) || empty($dados['amostras']))
		{
			echo json_encode(['error' => 'As amostras não foram enviadas corretamente']);
			die();
		}
		
		// Para cada envio de amostra teremos n amostras em um mesmo envio
		// sendo elas agrupadas em enviopedido por numero de protocolo
		foreach ($dados['amostras'] as $key => $amostra){
			// Gerar amostra
			$idamostra = EnvioPedidoAmostraController::gerarAmostraPeloPedido($dados['dadosCliente'], $dados['dadosEnvio'], $amostra);
			// Se ok, gerar os testes (exames/prodserv) para cada amostra determinados no formulario de envio
			if($idamostra)
			{
				// Salvar o idamostra gerado no pedido
				$dados['amostras'][$key]['idamostra'] = $idamostra;
				// Adicionar examos
				adicionaExames($idamostra, $amostra['exames']);
			}
		}
		
		// Gerar envio de pedido (enviopedido) com protocolo
		$idpedido = EnvioPedidoAmostraController::gerarEnvioPedido($dados['dadosCliente']['protocolo'], $dados);
		
		// Verificar envio do pedido e retornar responta ao front
		if($idpedido)
		{
			// O protocolo será retornado e usado para exibição do recibo de envio
			echo json_encode(['success' => 'Pedido gerado com sucesso.', 'protocolo' => $dados['dadosCliente']['protocolo']]);
		}
		else
		{
			echo json_encode(['error' => 'Pedido não gerado. Favor Verificar os dados preenchidos. Em caso de dúvidas entrar em contato com nosso suporte.']);
		}
	}
	catch (\Throwable $th)
	{
		echo json_encode(['error' => $th->getMessage()]);
	}
}

// Função para dicionar testes na amostra
function adicionaExames($idamostra, $exames)
{
	try
	{
		// Verificar o idamostra
		if(!is_int($idamostra))
		{
			throw 'O campo numérico idamostra não foi enviado corretamente: ' . $idamostra;
		}

		// Buscar o fluxo de status para amostras que estão em aberto (ABERTO)
		require_once "../form/controllers/fluxo_controller.php";
		$rowFluxo = FluxoController::getIdFluxoStatus('resultaves', 'ABERTO');
		
		// Verificar se os exames foram enviados corretamente
		if (is_array($exames) && !empty($exames)){
			foreach($exames as $exame)
			{
				// Criar testes da amostra
				AmostraController::criarTestesDaAmostraERetorna(
					cb::idempresa(),
					$idamostra,
					($exame["idexame"]?:"null"),
					($exame["quantidade"]?:"1"),
					($exame["idsecretaria"]?:"null"),
					($exame["loteetiqueta"]?:"0"),
					($exame["npedido"]?:"null"),
					($exame["ord"]?:"0"),
					($rowFluxo),
					$_SESSION["SESSAO"]["USUARIO"],
					($exame["cobrar"]?:'Y')
				);
			}
		}
		else
		{
			throw 'Os exames da amosta ' .$idamostra.' não foram enviados corretamente';
		}
	}
	catch (\Throwable $th)
	{
		echo json_encode(['error' => $th->getMessage()]);
		return false;
	}

	return true;
}
