<?php

namespace Carbon8;


use cmd;
/* 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */

require_once("vendor/autoload.php");
require_once "../inc/php/functions.php";

if (!empty($_POST)) {
	$obs = $_POST['obs'];

	foreach ($_POST['banco'] as $banco) { 
		if (!$banco['banco']) continue;

		$obs .= "\n \n Banco: {$banco['banco']} \n";
		$obs .= "Agência: {$banco['agencia']} \n";
		$obs .= "C/C: {$banco['conta']} \n";
		$obs .= "Contato: {$banco['contato']} \n \n";
	}

	$contatoempresa = [
		"_c_i_pessoa_usuario" => $_POST["usuario"],
		"_c_i_pessoa_senha" => senha_hash($_POST["senha"]),
		"_c_i_pessoa_tipoauth" => "bsp",
		"_c_i_pessoa_email" => $_POST["email"],
		"_c_i_pessoa_nome" => strtoupper($_POST['nome']),
		"_c_i_pessoa_idtipopessoa" => 3,
		"_c_i_pessoa_status" => "PENDENTE",
		"_c_i_pessoa_perfil" => "RT",
		"_c_i_pessoa_obs" => $obs,
		"_c_i_pessoa_cpfcnpj" => $_POST[$_POST['tipocliente']],
	];

	$endereco = [
		"_e_i_endereco_idtipoendereco" => 1,
		"_e_i_endereco_cep" => $_POST["cep"],
		"_e_i_endereco_logradouro" => $_POST["logradouro"],
		"_e_i_endereco_codcidade" => $_POST["codcidade"],
		"_e_i_endereco_uf" => $_POST["uf"],
		"_e_i_endereco_endereco" => $_POST["endereco"],
		"_e_i_endereco_complemento" => $_POST["complemento"],
		"_e_i_endereco_bairro" => $_POST["bairro"],
		"_e_i_endereco_cidade" => $_POST["cidade"],
		"_e_i_endereco_status" => 'ATIVO',
	];

	// para cadastro de pessoa fisica, se não tiver a razão social preencher com o nome
	// todo cadastro gera um empresa mesmo que pessoa fisica, porque o sistema precisa disso
	// para realizar a nota de cobrança
	$_POST['razao'] = $_POST['razao']?$_POST['razao']:$_POST['nome'];
	
	$empresa = [
		"_1_i_pessoa_nome" => strtoupper($_POST['nome']),
		"_1_i_pessoa_razaosocial" => strtoupper($_POST['razao']),
		"_1_i_pessoa_cpfcnpj" => $_POST[$_POST['tipocliente']],
		"_1_i_pessoa_InscricaoMunicipalTomador" => $_POST['insc_m'],
		"_1_i_pessoa_inscrest" => $_POST['insc_e'],
		"_1_i_pessoa_dddfixo" => $_POST['ddd'],
		"_1_i_pessoa_telfixo" => $_POST['telefone'],
		"_1_i_pessoa_email" => strtolower($_POST['email']),
		"_1_i_pessoa_emailxmlnfe" => strtolower($_POST['emailxmlnfe']),
		"_1_i_pessoa_emailmat" => strtolower($_POST['email_mat']),
		"_1_i_pessoa_indfinal" => $_POST['consumidor_final'],
		"_1_i_pessoa_indiedest" => $_POST['contribuente_icms'],
		"_1_i_pessoa_obs" => $obs,
		"_1_i_pessoa_idtipopessoa" => 2,
		"_1_i_pessoa_flgprodrural" => $_POST['produtor_rural']
	];

	//savepos: salvar pessoacontato vinculando empresa e contato empresa
	//savepos: salvar endereco vinculando com empresa
}

class Cadastro
{
	private $idpessoa = 0;
	private $idcontato = 0;
	public $empresa = [];
	public $endereco = [];
	public $usuario = [];
	public $pessoacontato = [];
	public $pessoaplantel = [];
	private $cmd;
	private $contribuinte_icms = [
		"1" => 1, /* Contribuinte ICMS */
		"2" => 2, /* Contribuinte isento */
		"3" => 9 /* Não Contribuinte */
	];

	public function __construct()
	{
		require_once('../inc/php/functions.php');
		require_once('../inc/php/cmd.php');

		$_GET['_modulo'] = 'cadastroext';
		$_SESSION["SESSAO"]["MIGRACAO"]['LPS'] = 94;
		$_SESSION["SESSAO"]["IDPESSOA"] = 1029;
		$_SESSION["SESSAO"]["USUARIO"] = 'SISLAUDO';
		$_SESSION["SESSAO"]["IDEMPRESA"] = 1;

		$this->cmd = new cmd();
	}

	public function formatarCPF_CNPJ($campo, $formatado = true){
		//retira formato
		$codigoLimpo = preg_replace("/\D/",'',$campo);
		// pega o tamanho da string menos os digitos verificadores
		$tamanho = (strlen($codigoLimpo) -2);
		//verifica se o tamanho do código informado é válido
		if ($tamanho != 9 && $tamanho != 12){
			return false; 
		}
	 
		if ($formatado){ 
			// seleciona a máscara para cpf ou cnpj
			$mascara = ($tamanho == 9) ? '###.###.###-##' : '##.###.###/####-##'; 
	 
			$indice = -1;
			for ($i=0; $i < strlen($mascara); $i++) {
				if ($mascara[$i]=='#') $mascara[$i] = $codigoLimpo[++$indice];
			}
			//retorna o campo formatado
			$retorno = $mascara;
	 
		}else{
			//se não quer formatado, retorna o campo limpo
			$retorno = $codigoLimpo;
		}
	 
		return $retorno;
	 
	}

	public function setEmpresa(array $empresa)
	{
		$empresa['_1_i_pessoa_cpfcnpj'] = $this->formatarCPF_CNPJ($empresa['_1_i_pessoa_cpfcnpj'], false);

		$this->empresa = $empresa;
		$this->empresa["_1_i_pessoa_indiedest"] = $this->contribuinte_icms[$empresa['_1_i_pessoa_indiedest']];
		$this->empresa["_1_i_pessoa_idempresa"] = 1;
		$this->empresa["_1_i_pessoa_criadopor"] = 'SISLAUDO';
		$this->empresa["_1_i_pessoa_alteradopor"] = 'SISLAUDO';
		return $this->_save($this->empresa);
	}

	public function setEndereco(array $endereco)
	{
		$this->endereco = $endereco;
		$this->endereco["_e_i_endereco_idpessoa"] = $this->idpessoa;
		return $this->_save($this->endereco);
	}

	public function setContatoEmpresa(array $usuario)
	{
		$usuario['_c_i_pessoa_cpfcnpj'] = $this->formatarCPF_CNPJ($usuario['_c_i_pessoa_cpfcnpj'], false);
		$this->usuario = $usuario;
		//$this->usuario["idpessoa"] = $this->idpessoa;
		return $this->_save($this->usuario);
	}
	public function setPessoaContato()
	{
		$this->pessoacontato["_1_i_pessoacontato_idpessoa"] = $this->idpessoa;
		$this->pessoacontato["_1_i_pessoacontato_idcontato"] = $this->idcontato;
		return $this->_save($this->pessoacontato);
	}
	
	public function setPessoaPlantelPet($idpessoa){
		$this->pessoaplantel["_1_i_plantelobjeto_idobjeto"] = $idpessoa;
		$this->pessoaplantel["_1_i_plantelobjeto_tipoobjeto"] = 'pessoa';
		$this->pessoaplantel["_1_i_plantelobjeto_idempresa"] = 1;
		$this->pessoaplantel["_1_i_plantelobjeto_idplantel"] = 34;
		return $this->_save($this->pessoaplantel);
	}

	private function _save($data)
	{
		try {
			$res = $this->cmd->save($data);
			if (!$res) {
				die($this->cmd->erro);
			} else {
				return $this->cmd->getPkid();
			}
		} catch (\Throwable $th) {
			echo $th;
		}
	}

	public function run()
	{
		//armazena a pagina de origem do POST
		if (!isset($_SERVER["HTTP_REFERER"]))
			$_SERVER["HTTP_REFERER"] = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
		//salva os dados da empresa, persiste o idpessoa
		$this->idpessoa = $this->setEmpresa($this->empresa);
		if (!$this->idpessoa)
			die('Não foi possível realizar esse cadastro.');
		$this->setPessoaPlantelPet($this->idpessoa);

		if (!$this->setEndereco($this->endereco))
			die('Não foi possível salvar endereço');

		$this->idcontato = $this->setContatoEmpresa($this->usuario);
		if (!$this->idcontato) {
			die('Não foi possível salvar os dados de usuário');
		}
		$this->setPessoaPlantelPet($this->idcontato);
		$this->setPessoaContato();
	}
}

$cadastroFinalizado = false;

if (!empty($_POST)) {
	$cadastro = new Cadastro();
	$cadastro->empresa = $empresa;
	$cadastro->usuario = $contatoempresa;
	$cadastro->endereco = $endereco;
	$cadastro->run();

	$cadastroFinalizado = true;

	// if (!headers_sent()) {
	// 	// Redireciona o navegador para a nova URL
	// 	header('Location: /');
	// 	exit(); // Encerra o script após o redirecionamento
	// }
}

require_once('./inc/template.php');
