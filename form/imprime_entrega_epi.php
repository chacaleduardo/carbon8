<?
require_once("../inc/php/functions.php");
require_once("controllers/assinatura_controller.php");
//require_once("../inc/php/validaacesso.php");

$_acao = 'u';
if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */

//validar qdo for pra imprimir todas as epi do funcionário
if ($_GET['idpessoa']) {
	$sql = "select identregaepi, idpessoa  from entregaepi e where e.idpessoa = " . $_GET['idpessoa'] . " and e.status = 'RECEBIDO';";
	$entregaepi = d::b()->query($sql) or die("Erro ao buscar relatorio de epi");

	//montando o where para o select.
	while ($e = mysqli_fetch_array($entregaepi)) {
		$epis[] = $e['identregaepi'];
	}
    $epis2 = implode(',', $epis);
	$_GET['identregaepi'] = end($epis);

} else {
	$epis2 = $_GET['identregaepi']; 
}
 
$pagvaltabela = "entregaepi";
$pagvalcampos = array(
	"identregaepi" => "pk"
); 
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from entregaepi where identregaepi = " . $_GET['identregaepi'];
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

$sql = "SELECT  p.*,  
e.razaosocial as razaosocialemp,
e.idempresa,
c.cargo, 
s.setor,
po.idobjeto,
s.setor
FROM pessoa p
left JOIN empresa e on p.idempresa = e.idempresa
left JOIN sgcargo c on p.idsgcargo = c.idsgcargo
LEFT JOIN pessoaobjeto po ON p.idpessoa = po.idpessoa
LEFT JOIN sgsetor s ON s.status = 'ATIVO' and s.idsgsetor = po.idobjeto
where p.idpessoa =  $_1_u_entregaepi_idpessoa";

$res = d::b()->query($sql) or die("getFuncionario: Erro: " . mysqli_error(d::b()) . "\n" . $sql);

$func = mysqli_fetch_object($res);

$sql = "SELECT e.*
                FROM  entregaepiitens e 
                INNER join solmatitem s on s.idsolmat = e.idsolmat and e.idprodserv = s.idprodserv
            WHERE
                e.identregaepi in (" . $epis2 . ")
                and e.qtd > 0 ";
$res = d::b()->query($sql) or die("Erro ao buscar relatorio de epi");


//pega imagem
$_sqltimbrado = "select tipoimagem,caminho from empresaimagem where idempresa=" . $_1_u_entregaepi_idempresa . " and tipoimagem in ('HEADERPRODUTO')";
$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: " . mysql_error());
$_figtimbrado = mysql_fetch_assoc($_restimbrado);


ob_start();
?>
<html>

<head>
	<title>Entrega EPI</title>
	<style>
		body {
			font-family: sans-serif;
			max-width: 700px;
		}

		h5 {
			font-weight: bold;
			font-size: 15px;
		}

		table {
			table-layout: auto;
			border: 0px;
			width: 100%;
		}

		thead {
			font-size: 13px;
			font-weight: bold;
			background-color: #E6E6E6;
			font-family: Roboto;
			font-weight: 700;
		}

		tbody {
			font-size: 10px;
			text-align: center;
		}

		span {
			font-size: 13px;
		}
	</style>
</head>

<body>
	<div style="flex-direction: column; justify-content: flex-start; align-items: center; gap: 16px; display: inline-flex">
		<img alt="<?= $func->razaosocialemp ?>" src="<?= $_figtimbrado["caminho"] ?>" height="80%" width="80%">
		<h5>Ficha de controle de recebimento, uso e guarda de equipamento de proteção individual - EPI</h5>
		<div style="align-self: stretch; height: 92px; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 8px; display: flex">
			<div style="align-self: stretch; height: 92px; padding-left: 16px; padding-right: 16px; padding-top: 8px; padding-bottom: 8px; border-radius: 4px; border: 1px #666666 solid; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 4px; display: flex">
				<div style="align-self: stretch; justify-content: space-between; align-items: flex-start; display: inline-flex">
					<div style="flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 4px; display: inline-flex">
						<div style="justify-content: flex-start; align-items: flex-start; gap: 4px; display: inline-flex">
							<div style="justify-content: flex-start; align-items: flex-start; gap: 10px; display: flex">
								<div style="color: #212121; font-size: 10px;  font-weight: 700; word-wrap: break-word">Nome:</div>
							</div>
							<div style="color: #212121; font-size: 10px;  font-weight: 400; word-wrap: break-word"><?= $func->nome ?></div>
						</div>
						<div style="justify-content: flex-start; align-items: flex-start; gap: 4px; display: inline-flex">
							<div style="justify-content: flex-start; align-items: flex-start; gap: 10px; display: flex">
								<div style="color: #212121; font-size: 10px;  font-weight: 700; word-wrap: break-word">CNPJ/CPF : </div>
							</div>
							<div style="color: #212121; font-size: 10px;  font-weight: 400; word-wrap: break-word"><?= $func->cpfcnpj ?></div>
						</div>
						<div style="justify-content: flex-start; align-items: flex-start; gap: 4px; display: inline-flex">
							<div style="justify-content: flex-start; align-items: flex-start; gap: 10px; display: flex">
								<div style="color: #212121; font-size: 10px;  font-weight: 700; word-wrap: break-word">Empresa : </div>
							</div>
							<div style="color: #212121; font-size: 10px;  font-weight: 400; word-wrap: break-word"><?= $func->razaosocialemp ?></div>
						</div>
						<div style="justify-content: flex-start; align-items: flex-start; gap: 4px; display: inline-flex">
							<div style="justify-content: flex-start; align-items: flex-start; gap: 10px; display: flex">
								<div style="color: #212121; font-size: 10px;  font-weight: 700; word-wrap: break-word">Setor:</div>
							</div>
							<div style="color: #212121; font-size: 10px;  font-weight: 400; word-wrap: break-word"><?= $func->setor ?></div>
						</div>
						<div style="justify-content: flex-start; align-items: flex-start; gap: 4px; display: inline-flex">
							<div style="justify-content: flex-start; align-items: flex-start; gap: 10px; display: flex">
								<div style="color: #212121; font-size: 10px;  font-weight: 700; word-wrap: break-word">Função:</div>
							</div>
							<div style="color: #212121; font-size: 10px;  font-weight: 400; word-wrap: break-word"><?= $func->cargo ?></div>
						</div>
					</div>
					<div style="flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 4px; display: inline-flex">
						<div style="justify-content: flex-start; align-items: flex-start; gap: 4px; display: inline-flex">
							<div style="justify-content: flex-start; align-items: flex-start; gap: 10px; display: flex">
								<div style="color: #212121; font-size: 10px;  font-weight: 700; word-wrap: break-word">Data Admissão:</div>
							</div>
							<div style="color: #212121; font-size: 10px;  font-weight: 400; word-wrap: break-word"><?= formatadatadbweb($func->contratacao) ?></div>
						</div>
						<? if ($func->demissao) { ?>
							<div style="justify-content: flex-start; align-items: flex-start; gap: 4px; display: inline-flex">
								<div style="justify-content: flex-start; align-items: flex-start; gap: 10px; display: flex">
									<div style="color: #212121; font-size: 10px;  font-weight: 700; word-wrap: break-word">Data Demissão:</div>
								</div>
								<div style="color: #212121; font-size: 10px;  font-weight: 400; word-wrap: break-word"><?= formatadatadbweb($func->demissao) ?></div>
							</div>
						<? } ?>
					</div>
				</div>
			</div>
		</div>
		<div style="align-self: stretch; height: 352px; padding-left: 16px; padding-right: 16px; padding-top: 8px; padding-bottom: 8px; background: white; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 8px; display: flex">
			<div style="align-self: stretch"><span style="color: #40464F; font-size: 12px;  font-weight: 700; word-wrap: break-word">Declaração<br /></span><span style="color: #40464F; font-size: 12px;  font-weight: 500; word-wrap: break-word"><br /></span><span style="color: #40464F; font-size: 12px;  font-weight: 700; word-wrap: break-word">Declaro para todos os fins legais que:<br /></span><span style="color: #40464F; font-size: 12px;  font-weight: 500; word-wrap: break-word"> <br /></span><span style="color: #40464F; font-size: 12px;  font-weight: 700; word-wrap: break-word">1°</span><span style="color: #40464F; font-size: 12px;  font-weight: 300; word-wrap: break-word">- Recebi os Equipamentos de Proteção Individual constantes da lista abaixo, novos e em perfeitas condições de uso, respectivo treinamento quanto à necessidade da utilização dos mesmos, bem como da minha responsabilidade quanto ao seu uso conforme determinado na NR-01, da Portaria MTB 3214/78 e que estou ciente das obrigações descritas na NR-06, baixada pela Portaria MTB 3214/78, sub item 6.6.1, a saber:<br />a) usar o fornecido pela organização, observado o disposto no item 6.5.2;<br />b) utilizar apenas para a finalidade a que se destina;<br />c) responsabilizar-se pela limpeza, guarda e conservação;<br />d) comunicar à organização quando extraviado, danificado ou qualquer alteração que o torne impróprio para uso;e<br />e) cumprir as determinações da organização sobre o uso adequado.<br /></span><span style="color: #40464F; font-size: 12px;  font-weight: 500; word-wrap: break-word">2°- </span><span style="color: #40464F; font-size: 12px;  font-weight: 300; word-wrap: break-word">Que estou ciente das disposições do Art. 462 e §1º da CLT, e autorizo o desconto salarial proporcional ao custo de reparação do dano que os EPIs aos meus cuidados venham apresentar e das disposições do Art 158 alínea "a" da CLT, e do item 1.8 da NR 1, em especial o item 1.8.1, de que constitui ato faltoso a recusa injustificada de usar o EPI fornecido pela empresa, incorrendo nas penas da Lei.<br /></span><span style="color: #40464F; font-size: 12px;  font-weight: 500; word-wrap: break-word">3º- </span><span style="color: #40464F; font-size: 12px;  font-weight: 300; word-wrap: break-word">Fico proibido de dar ou emprestar o equipamento que estiver sob minha responsabilidade, só podendo fazê-lo se receber ordem por escrito da pessoa autorizada para tal fim.<br /></span><span style="color: #40464F; font-size: 12px;  font-weight: 500; word-wrap: break-word">4°-</span><span style="color: #40464F; font-size: 12px;  font-weight: 300; word-wrap: break-word"> Estando os equipamentos em minha posse, estarei sujeito a inspeções sem prévio aviso.<br /></span><span style="color: #40464F; font-size: 12px;  font-weight: 500; word-wrap: break-word">5°- </span><span style="color: #40464F; font-size: 12px;  font-weight: 700; word-wrap: break-word">Declaro ainda ter ciência de que os EPI's descartáveis (Luvas cirúrgicas, Jalecos, Toucas e Máscaras cirúrgicas triplas) necessários ao desempenho das minhas atribuições também são colocados a minha disposição na unidade/setor no qual trabalho, em período integral. Tais EPI's serão substituídos conforme necessidade, sendo seu descarte obrigatório em local adequado para esse fim.</span></div>
		</div>
		<table>
			<thead>
				<tr>
					<th>Solicitação(Solmat)</th>
					<th>Qtd</th>
					<th>Unidade</th>
					<th>Descrição</th>
					<th>Lote</th>
					<th>certificado</th>
					<th>Data de entrega</th>
				</tr>
			</thead>
			<tbody>
				<? 
				$i = 0;
				while ($r = mysqli_fetch_array($res)) { 
					if($i == 0){ 
						$_1_u_entregaepi_identregaepi = $r['identregaepi'];
					}
					?>
					<tr>
						<td><?= $r['idsolmat']; ?></td>
						<td><?= $r['qtd']; ?></td>
						<td><?= $r['un']; ?></td>
						<td><?= $r['descitem']; ?></td>
						<td><?= $r['partidaexercicio']; ?></td>
						<td><?= $r['certificadoepi']; ?></td>
						<td><?= formatadatadbweb($r['criadoem']); ?></td>
					</tr>
					
				<? $i++;
				} ?>
			</tbody>
		</table>

		<br />
		<br />
		<div style="align-self: stretch; height: 84.15px; flex-direction: column; justify-content: flex-start; align-items: flex-start; gap: 12px; display: flex">
			<div style="align-self: stretch; height: 52.15px; flex-direction: column; justify-content: center; align-items: center; gap: 10px; display: flex">
				<?
				$lAss = AssinaturaController::buscarAssinatura(($_GET['idpessoa'] ? $epis[0]: $epis2), 'entregaepi', 'base64');
				$qtdAss = count($lAss);
				?>
				<img style="width: 101.93px; height: 52.15px" src="<?= $lAss['assinatura'] ?>" />
				<span style="color: #40464F; font-size: 10px;  font-weight: 700; word-wrap: break-word">Assinatura</span>
				<span> <?= $func->nome ?> </span>
			</div>
			<div style="align-self: stretch; padding-left: 8px; padding-right: 8px; justify-content: center; align-items: center; gap: 10px; display: inline-flex">
			</div>
		</div>
	</div>
</body>

</html>