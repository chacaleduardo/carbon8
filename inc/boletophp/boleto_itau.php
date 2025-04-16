<?php
require_once("../php/functions.php");
$idcontapagar = $_GET['idcontapagar'];

$geraarquivo = $_GET['geraarquivo'];
$gravaarquivo = $_GET['gravaarquivo'];

(!empty($_GET['_idempresa'])) ? $idempresa = $_GET['_idempresa'] : $idempresa = $_SESSION["SESSAO"]["IDEMPRESA"];
ob_start();
$se = "select e.DDDPrestador,e.TelefonePrestador,
		e.razaosocial,e.nomefantasia,e.cnpj,e.xmun,e.uf,e.xlgr,e.nro,xbairro,e.site,a.nagencia,a.nconta,a.juros,a.multa,
		    CASE
                WHEN a.instrucao = null or a.instrucao = '' THEN ' '              
                ELSE a.instrucao 
            END as instrucao
		from contapagar c join empresa e on(e.idempresa=c.idempresa)
		join agencia a on(a.idagencia=c.idagencia)
		where c.idcontapagar=" . $idcontapagar;
$re = d::b()->query($se) or die("Erro ao buscar informações do cedente" . mysqli_error(d::b()));
$roe = mysqli_fetch_assoc($re);

if (empty($roe['juros'])) {
	$txjuro = 0.00;
} else {
	$txjuro = $roe['juros'] / 100;
}

if (empty($roe['multa'])) {
	$txmulta = 0.00;
} else {
	$txmulta = $roe['multa'] / 100;
}
/*
juros 1 mes 
multa 2 mes
baixar 2 mes o boleto
carteira 109
 */

$sql = "select c.idcontapagar,dma(c.datapagto) as datapagto,c.valor,round((c.valor*" . $txjuro . ")/30,2) as juro,round((c.valor*" . $txmulta . "),2) as multa,c.idcontapagar,p.razaosocial,p.cpfcnpj,
	e.logradouro,e.endereco,e.numero,e.complemento,e.cep,e.uf,ci.cidade,c.idobjeto,c.tipoobjeto,c.parcela,c.parcelas, c.idempresa
	from contapagar c left join pessoa p on(p.idpessoa = c.idpessoa) 
	left join endereco e on(e.idpessoa=p.idpessoa and e.idtipoendereco=2 and e.status='ATIVO')
	left join nfscidadesiaf ci  on (ci.codcidade=e.codcidade)
	where c.idcontapagar=" . $idcontapagar;
$res = d::b()->query($sql) or die("Erro ao buscar informações da conta:" . mysqli_error(d::b()));
$row = mysqli_fetch_assoc($res);

$nossonumero = $row['idcontapagar'];

if ($row["tipoobjeto"] == "nf" and !empty($row["idobjeto"])) {

	$sqlf = "select n.idnf,p.nome,n.tiponf,n.controle,n.nnfe from nf n,pessoa p where p.idpessoa = n.idpessoa and idnf =" . $row["idobjeto"];
	$qrf = d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:" . mysqli_error(d::b()));
	$qtdrowsf = mysqli_num_rows($qrf);
	$resf = mysqli_fetch_assoc($qrf);

	$numero_doc = $resf["nnfe"];
	$dadosboleto["especie_doc"] = 'DM';

	if (!empty($resf['controle'])) {
		$nossonumero = $resf['controle'] . $row['parcela'];
	}
} elseif ($row["tipoobjeto"] == "notafiscal" and !empty($row["idobjeto"])) {

	$sqlf = "select p.nome,n.numerorps,n.controle,n.nnfe,n.idnotafiscal from notafiscal n,pessoa p where p.idpessoa = n.idpessoa and idnotafiscal =" . $row["idobjeto"];
	$qrf = d::b()->query($sqlf) or die("Erro ao buscar nome do cliente da nota:" . mysqli_error(d::b()));
	$qtdrowsf = mysqli_num_rows($qrf);
	$resf = mysqli_fetch_assoc($qrf);

	$numero_doc = $resf["nnfe"];
	$dadosboleto["especie_doc"] = 'DS';
	if (!empty($resf['controle'])) {
		$nossonumero = $resf['controle'] . $row['parcela'];
	}
}
// +----------------------------------------------------------------------+
// | BoletoPhp - Versão Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo está disponível sob a Licença GPL disponível pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Você deve ter recebido uma cópia da GNU Public License junto com     |
// | esse pacote; se não, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colaborações de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de João Prado Maia e Pablo Martins F. Costa				  |
// | 														              |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto Itaú: Glauber Portella                        |
// +----------------------------------------------------------------------+

// ------------------------- DADOS DINÂMICOS DO SEU CLIENTE PARA A GERAÇÃO DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulário c/ POST, GET ou de BD (MySql,Postgre,etc)	//

// DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento = 0;
$taxa_boleto = 0.00;
//$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
$data_venc = $row['datapagto'];
$valor_cobrado = $row['valor']; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(",", ".", $valor_cobrado);
$valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

if ($roe['instrucao'] == 'Y') {
	$instrucao = 'NÃO RECEBER APÓS 30 DIAS DO VENCIMENTO.';
	$instrucao2 = 'PROTESTAR APÓS 30 DIAS DO VENCIMENTO.';
} else {
	$instrucao = '';
	$instrucao2 = '';
}

//####
$dadosboleto["local_pagamento"] = "PAGÁVEL EM QUALQUER BANCO ATÉ O VENCIMENTO";
$dadosboleto["aceite"] = 'N';

$dadosboleto["nosso_numero"] = $nossonumero;  // Nosso numero - REGRA: Máximo de 8 caracteres!
$dadosboleto["numero_documento"] = $numero_doc;	// Num do pedido ou nosso numero
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); //date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y");
//$dadosboleto["data_processamento"] ='28/10/2017'; //date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $row['razaosocial'] . " - CPF/CNPJ: " . formatarCPF_CNPJ($row['cpfcnpj'], true);
$dadosboleto["endereco1"] = $row['logradouro'] . " " . $row['endereco'] . " " . $row['numero'] . " " . $row['complemento'];
$dadosboleto["endereco2"] = $row['cidade'] . " - " . $row['uf'] . " -  CEP:" . formatarCEP($row['cep']);

// INFORMACOES PARA O CLIENTE
//$dadosboleto["demonstrativo1"] = "INATA Produtos Biológicos - http://www.inata.com.br";
$dadosboleto["demonstrativo1"] = $roe['nomefantasia'] . " - <u>" . $roe['site'] . "</u>";
$dadosboleto["demonstrativo2"] = "";
$dadosboleto["demonstrativo3"] = "NF: " . $numero_doc . " - PARCELA: " . $row['parcela'] . " de " . $row['parcelas'] . " ";

$dadosboleto["instrucoes1"] = "Qualquer dúvida contate o BENEFICIÁRIO. ";
$dadosboleto["instrucoes2"] = "APÓS O VENCIMENTO JUROS DE R$ ..." . str_replace(".", ",", $row['juro']) . " AO DIA";
$dadosboleto["instrucoes3"] = "APÓS O VENCIMENTO MULTA DE R$ ..." . str_replace(".", ",", $row['multa']);
$dadosboleto["instrucoes4"] = $instrucao;
$dadosboleto["instrucoes5"] = $instrucao2;
$dadosboleto["instrucoes6"] = "NF: " . $numero_doc . " - PARCELA: " . $row['parcela'] . " de " . $row['parcelas'] . " ";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"] = "";
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "";

// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
$nconta = explode("-", $roe['nconta']);
// DADOS DA SUA CONTA - ITAÚ
$dadosboleto["agencia"] = $roe['nagencia']; // Num da agencia, sem digito
$dadosboleto["conta"] = $nconta[0];	// Num da conta, sem digito
$dadosboleto["conta_dv"] = $nconta[1]; 	// Digito do Num da conta

// DADOS PERSONALIZADOS - ITAÚ
$dadosboleto["carteira"] = "109";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

$cpfcnpj = formatarCPF_CNPJ($roe['cnpj'], true);
// SEUS DADOS
$dadosboleto["identificacao"] = $roe['razaosocial'];
$dadosboleto["cpf_cnpj"] = $cpfcnpj;
$dadosboleto["endereco"] = $roe['xlgr'] . ", " . $roe['nro'];
$dadosboleto["cidade_uf"] = $roe['xmun'] . " / " . $roe['uf'] . " - (" . $roe['DDDPrestador'] . ") " . $roe['TelefonePrestador'];
$dadosboleto["cedente"] = $roe['nomefantasia'];

// NÃO ALTERAR!
include("include/funcoes_itau.php");
include("include/layout_itau.php");

if ($geraarquivo == 'Y') {

	$html = ob_get_contents();
	//limpar o codigo html
	$html = preg_replace('/>\s+</', "><", $html);

	// Limpa (erase) o conteúdo do buffer de saída
	ob_end_clean();

	// Incluímos a biblioteca DOMPDF
	require_once("../dompdf/dompdf_config.inc.php");

	// Instanciamos a classe
	$dompdf = new DOMPDF();

	//$html=preg_match("//u", $html)?utf8_decode($html):$html;
	$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

	// Passamos o conteúdo que será convertido para PDF
	$dompdf->load_html($html);

	// Definimos o tamanho do papel e
	// sua orientação (retrato ou paisagem)
	//$dompdf->set_paper(array(0, 0, 690, 841.89),'portrait');
	$dompdf->set_paper('A4', 'portrait');


	// O arquivo é convertido
	$dompdf->render();

	if ($gravaarquivo == 'Y') {
		// Salvo no diretório temporário do sistema
		$output = $dompdf->output();
		file_put_contents("/var/www/laudo/tmp/nfe/Boleto_NF_" . $numero_doc . "_Parc_" . $row['parcela'] . "_de_" . $row['parcelas'] . ".pdf", $output);
		echo ("OK");
	} else {
		// e exibido para o usuário
		$dompdf->stream("Boleto" . $row['idcontapagar'] . ".pdf");
	}
}
