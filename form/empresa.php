<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../form/controllers/confcontapagar_controller.php");

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "empresa";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
	"idempresa" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from empresa where idempresa = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");
?>
<?
if ($_1_u_empresa_idempresa) {
	$arrempresa = getObjeto("empresa", $_1_u_empresa_idempresa);
		/*
        $sqlt="SELECT * from tipoempresa where contato is  null and idtipoempresa = ".$_1_u_empresa_idtipoempresa;
        $rest = d::b()->query($sqlt) or die("A Consulta do tipoempresa : " . mysqli_error() . "<p>SQL: $sqlt");
        $rownumt= mysqli_num_rows($rest);
 
 */;
?>

	<style>
		.dropdown-menu {
			max-height: 430px !important;
		}

		.imagensheaders {
			border: 1px solid darkgray;
			cursor: pointer;
			max-width: 100%;
			max-height: 100px;
			min-width: 40px;
			min-height: 40px;
		}

		.tbltest {
			width: 48%;
			border-radius: 9px;
			border: 1px solid;
			border-collapse: unset !important;
			border-color: #c8c4c4;
		}

		.tbltest th {
			background-color: #c8c4c4;
			border-top-right-radius: 9px;
			border-top-left-radius: 9px;
			border: 1px solid;
			border-collapse: unset !important;
			border-color: #c8c4c4;
			color: black;
		}
	</style>
	<div class="row ">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading ">
					<table>

						<tr>
							<td align="right">ID:</td>
							<td>
								<label class="idbox"><?= $_1_u_empresa_idempresa ?></label>
								<input name="_1_<?= $_acao ?>_empresa_idempresa" type="hidden" value="<?= $_1_u_empresa_idempresa ?>" readonly='readonly'>

							</td>
							<td align="right">Nome:</td>
							<td><input class="upper" name="_1_<?= $_acao ?>_empresa_empresa" type="text" size="48" value="<?= $_1_u_empresa_empresa ?>" vnulo></td>
							<? if (!empty($_1_u_empresa_idempresa)) { ?>
								<td align="right">Filial:</td>
								<td>
									<? if ($_1_u_empresa_filial == 'Y') {
										$checked = 'checked=checked';
										$value = 'N';
									} else {
										$checked = '';
										$value = 'Y';
									} ?>
									<input type="checkbox" <?= $checked ?> onclick="habilitarfilial('<?= $value ?>',<?= $_1_u_empresa_idempresa ?>)">
								</td>
							<? } ?>
							<td align="right">Habilitar Matriz:</td>
							<td>
								<? if ($_1_u_empresa_habilitarmatriz == 'Y') {
									$checkedMatriz = 'checked=checked';
									$valueMatriz = 'N';
								} else {
									$checkedMatriz = '';
									$valueMatriz = 'Y';
								} ?>
								<input type="checkbox" <?= $checkedMatriz ?> onclick="habilitarmatriz('<?= $valueMatriz ?>',<?= $_1_u_empresa_idempresa ?>)">
							</td>

							<td align="right">Status:</td>
							<td>
								<?
								if ($_1_u_empresa_status == 'INATIVO') {
								?>
									<label class="idbox"><?= $_1_u_empresa_status ?></label>
								<?
								} else {
								?>
									<select name="_1_<?= $_acao ?>_empresa_status" id="status" vnulo>
										<?
										fillselect("select 'PENDENTE','Pendente' union select 'ATIVO','Ativo' union select 'INATIVO','Inativo'  ", $_1_u_empresa_status);
										?>
									</select>
								<?
								}
								?>
							</td>
							<td>
								<a title="Viagem" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/relviagem.php?acao=u&idempresa=<?= $_1_u_empresa_idempresa ?>')"></a>
							</td>

						</tr>

					</table>
				</div>
			</div>
		</div>
	</div>


	<div class="row">
		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#dadoscad">Dados Cadastrais</div>
				<div class="panel-body" id="dadoscad">
					<div style="display:inline-block">
						<div class="col-md-3" align="right">
							Raz&atilde;o Social:
						</div>
						<div class="col-md-9">
							<input name="_1_<?= $_acao ?>_empresa_razaosocial" type="text" id="razaosocial" size="40" vnulo value="<?= $arrempresa['razaosocial'] ?>">
						</div>
						<div class="col-md-3" align="right">
							Nome Fantasia:
						</div>
						<div class="col-md-9">
							<input name="_1_<?= $_acao ?>_empresa_nomefantasia" type="text" id="nomefantasia" size="40" vnulo value="<?= $arrempresa['nomefantasia'] ?>">
						</div>
						<div class="col-md-3" align="right">
							CNPJ:
						</div>
						<div class="col-md-9">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_cnpj" type="text" id="cnpj" size="40" vnulo value="<?= $arrempresa['cnpj'] ?>">
						</div>
						<div class="col-md-3" align="right">
							Insc. Estadual:
						</div>
						<div class="col-md-3">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_inscestadual" type="text" id="inscestadual" size="40" vnulo value="<?= $arrempresa['inscestadual'] ?>">
						</div>
						<div class="col-md-3" align="right">
							Cod Cidade:
						</div>
						<div class="col-md-3">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_CodCidade" type="text" id="CodCidade" size="40" vnulo value="<?= $arrempresa['CodCidade'] ?>">
						</div>
						<div class="col-md-3" align="right">
							DDD Prestador:
						</div>
						<div class="col-md-3">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_DDDPrestador" type="text" id="DDDPrestador" size="40" vnulo value="<?= $arrempresa['DDDPrestador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							Telefone:
						</div>
						<div class="col-md-3">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_TelefonePrestador" type="text" id="TelefonePrestador" size="40" vnulo value="<?= $arrempresa['TelefonePrestador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							Site:
						</div>
						<div class="col-md-3">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_site" type="text" id="site" size="40" value="<?= $arrempresa['site'] ?>">
						</div>
						<div class="col-md-3" align="right">
							Email Resultado:
						</div>
						<div class="col-md-3">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_emailres" type="text" id="site" size="40" value="<?= $arrempresa['emailres'] ?>">
						</div>
						<div class="d-flex align-items-center w-100">
							<div class="col-xs-3" align="right">
								CEO:
							</div>
							<div class="col-xs-9">
								<select name="_1_<?= $_acao ?>_empresa_idceo" id="" class="selectpicker form-control" data-live-search="true">
									<?= fillselect(PessoaController::buscarColaboradoresPorIdempresaEStatus(cb::idempresa(), 'ATIVO', true), $_1_u_empresa_idceo) ?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- LTM (19-10-2021) Configuração Matriz -->
			<? if ($_1_u_empresa_habilitarmatriz == 'Y') { ?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<a id="modalconfiguracaomatriz" class="point">Configuração Acesso Matriz</a>
					</div>
				</div>
			<? } ?>


			<!--JLAL - 14-08-20 **Criação do cadastro de contador**-->
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#dadoscad">Dados Contador</div>
				<div class="panel-body" id="dadoscad">
					<div style="display:inline-block">
						<div class="col-md-3" align="right">
							Nome:
						</div>
						<div class="col-md-9">
							<input name="_1_<?= $_acao ?>_empresa_nomecontador" type="text" id="nomecontador" size="40" value="<?= $arrempresa['nomecontador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							CPF:
						</div>
						<div class="col-md-9">
							<input name="_1_<?= $_acao ?>_empresa_cpfcontador" type="text" id="cpfcontador" size="40" value="<?= $arrempresa['cpfcontador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							CNPJ:
						</div>
						<div class="col-md-4">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_cnpjcontador" type="text" id="cnpjcontador" size="40" value="<?= $arrempresa['cnpjcontador'] ?>">
						</div>
						<div class="col-md-1" align="right">
							CRC:
						</div>
						<div class="col-md-4">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_crccontador" type="text" id="crccontador" size="40" value="<?= $arrempresa['crccontador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							CEP:
						</div>
						<div class="col-md-4">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_cepcontador" type="text" id="cepcontador" size="40" value="<?= $arrempresa['cepcontador'] ?>">
						</div>
						<div class="col-md-1" align="right">
							END:
						</div>
						<div class="col-md-4">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_enderecocontador" type="text" id="enderecocontador" size="40" value="<?= $arrempresa['enderecocontador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							NUM:
						</div>
						<div class="col-md-2">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_numcontador" type="text" id="numcontador" size="40" value="<?= $arrempresa['numcontador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							COMPL:
						</div>
						<div class="col-md-4">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_complementocontador" type="text" id="complementocontador" size="40" value="<?= $arrempresa['complementocontador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							BAIRRO:
						</div>
						<div class="col-md-9">
							<input name="_1_<?= $_acao ?>_empresa_bairrocontador" type="text" id="bairrocontador" size="40" value="<?= $arrempresa['bairrocontador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							FONE:
						</div>
						<div class="col-md-4">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_fonecontador" type="text" id="fonecontador" size="40" value="<?= $arrempresa['fonecontador'] ?>">
						</div>
						<div class="col-md-1" align="right">
							FAX:
						</div>
						<div class="col-md-4">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_faxcontador" type="text" id="faxcontador" size="40" value="<?= $arrempresa['faxcontador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							EMAIL:
						</div>
						<div class="col-md-9">
							<input name="_1_<?= $_acao ?>_empresa_emailcontador" type="text" id="emailcontador" size="40" value="<?= $arrempresa['emailcontador'] ?>">
						</div>
						<div class="col-md-3" align="right">
							COD_MUN:
						</div>
						<div class="col-md-3">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_codmuncontador" type="text" id="codmuncontador" size="40" value="<?= $arrempresa['codmuncontador'] ?>">
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#outrosdados">Outros Dados</div>
				<div class="panel-body" id="outrosdados">
					<div style="display:inline-block">
						<div class="col-md-3" align="right">
							Cor Sistema:
						</div>
						<div class="col-md-9">
							<input type="color" name="_1_<?= $_acao ?>_empresa_corsistema" value="<?= $arrempresa['corsistema'] ?>">
						</div>
						<div class="col-md-3" align="right">
							Logo Sistema:
						</div>
						<div class="col-md-9">
							<img src="" id="imagemlogosistema" class="imagensheaders" title="Tamanho recomendado 98 X 98" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Imagem Relatório Serviço:
						</div>
						<div class="col-md-9">
							<img src="" id="imagemheaderservico" class="imagensheaders" title="Tamanho recomendado 700 X 129" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Imagem Relatório Produto:
						</div>
						<div class="col-md-9">
							<img src="" id="imagemheaderproduto" class="imagensheaders" title="Tamanho recomendado 1533 X 187" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Imagem Relatório Pedido:
						</div>
						<div class="col-md-9">
							<img src="" id="imagemheaderpedido" class="imagensheaders" title="Tamanho recomendado 1654 X 307" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Imagem Email:
						</div>
						<div class="col-md-9">
							<img src="" id="imagememail" class="imagensheaders" title="Tamanho recomendado 1241 X 205" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Imagem DANFE:
						</div>
						<div class="col-md-9">
							<img src="" id="imagemdanfe" class="imagensheaders" title="Tamanho recomendado 213 X 72" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Imagem Rodapé:
						</div>
						<div class="col-md-9">
							<img src="" id="imagemrodape" class="imagensheaders" title="Tamanho recomendado 879 X 153" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Marca D'Água:
						</div>
						<div class="col-md-9">
							<img src="" id="imagemmarcadagua" class="imagensheaders" title="Tamanho recomendado 1280 X 720" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Ícone:
						</div>
						<div class="col-md-9">
							<img src="" id="imagemicon" class="imagensheaders" title="100 X 100" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Ícone Lateral:
						</div>
						<div class="col-md-9">
							<img src="" id="imagemlogin" class="imagensheaders" title="24 X 24" class="dz-clickable">
						</div>
						<div class="col-md-3" align="right">
							Imagem ZPL:
						</div>
						<div class="col-md-9">
							<input type="text" name="_1_<?= $_acao ?>_empresa_zplimg" value="<?= $_1_u_empresa_zplimg ?>" class="form-control">
						</div>
						<div class="col-md-3" align="right">
							Rodapés de Email:
						</div>
						<div class="col-md-9">
							<i class="fa fa-cloud-upload dz-clickable pointer azul" id="rodapeanexo" title="Clique para adicionar um rodapé"></i>
						</div>
					</div>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#certificado">Certificado</div>
				<div class="panel-body" id="certificado">
					<div style="display:inline-block">
						<div class="col-md-3" align="right">
							Anexo Certificado
						</div>
						<div class="col-md-9">
							<i class="fa fa-cloud-upload dz-clickable pointer azul" id="certanexo" title="Clique para adicionar um certificado"></i>
						</div>

						<div class="col-md-3" align="right">
							Senha:
						</div>
						<div class="col-md-3">
							<input class="" name="_1_<?= $_acao ?>_empresa_senha" type="text" size="40" value="<?= $arrempresa['senha'] ?>">
						</div>
						<div class="col-md-3" align="right">
							Validade:
						</div>
						<div class="col-md-3">
							<input class="upper calendario" name="_1_<?= $_acao ?>_empresa_validade" type="text" id="validade" size="40" value="<?= dma($arrempresa['validade']) ?>">
						</div>

						<div class="col-md-3" align="right">
							Atualização
						</div>
						<div class="col-md-3">
							<input class="upper" style="background:#eee" readonly disabled name="_1_<?= $_acao ?>_empresa_nfatualizacao" type="text" id="nfatualizacao" size="40" value="<?= dmahms($arrempresa['nfatualizacao']) ?>">
						</div>
						<div class="col-md-3" align="right">
							Ambiente
						</div>
						<div class="col-md-3">
							<input class="upper" style="background:#eee" readonly disabled name="_1_<?= $_acao ?>_empresa_nftpAmb" type="text" id="urlwsprod" size="40" value="<?= $arrempresa['nftpAmb'] ?>">
						</div>

						<div class="col-md-3" align="right">
							Razão Social
						</div>
						<div class="col-md-9">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_nfrazaosocial" type="text" id="nfrazaosocial" size="40" value="<?= $arrempresa['nfrazaosocial'] ?>">
						</div>

						<div class="col-md-3" align="right">
							Sigla UF
						</div>
						<div class="col-md-3">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_nfsiglaUF" type="text" id="nfsiglaUF" size="40" value="<?= $arrempresa['nfsiglaUF'] ?>">
						</div>

						<div class="col-md-3" align="right">
							CNPJ
						</div>
						<div class="col-md-3">
							<input class="upper" name="_1_<?= $_acao ?>_empresa_nfcnpj" type="text" id="nfcnpj" size="40" value="<?= $arrempresa['nfcnpj'] ?>">
						</div>

						<div class="col-md-3" align="right">
							Scheme
						</div>
						<div class="col-md-3">
							<input class="upper" style="background:#eee" readonly disabled name="_1_<?= $_acao ?>_empresa_nfschemes" type="text" id="nfschemes" size="40" value="<?= $arrempresa['nfschemes'] ?>">
						</div>

						<div class="col-md-3" align="right">
							Versão
						</div>
						<div class="col-md-3">
							<input class="upper" style="background:#eee" readonly disabled name="_1_<?= $_acao ?>_empresa_nfversao" type="text" id="nfversao" size="40" value="<?= $arrempresa['nfversao'] ?>">
						</div>

						<div class="col-md-3" align="right">
							Token
						</div>
						<div class="col-md-3">
							<input class="upper" style="background:#eee" readonly disabled name="_1_<?= $_acao ?>_empresa_nftokenIBPT" type="text" id="nftokenIBPT" size="40" value="<?= $arrempresa['nftokenIBPT'] ?>">
						</div>
					</div>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#cotacao">Cotação</div>
				<div class="panel-body" id="cotacao">
					<table>
						<tr>
							<td align="left" nowrap style="display:block;">Informações do Solicitante:</td>
						</tr>
						<tr>
							<td><textarea name="_1_<?= $_acao ?>_empresa_infosolicitante" cols="100" rows="10"><?= $arrempresa['infosolicitante'] ?></textarea></td>
						</tr>
						<tr>
							<td align="left" nowrap style="display:block;">Rodapé Cotação:</td>
						</tr>
						<tr>
							<td><textarea name="_1_<?= $_acao ?>_empresa_rodapecotacao" cols="100" rows="3"><?= $arrempresa['rodapecotacao'] ?></textarea></td>
						</tr>
						<tr>
							<td align="left" nowrap style="display:block;">Observações do Solicitante:</td>
						</tr>
						<tr>
							<td><textarea name="_1_<?= $_acao ?>_empresa_obssolicitante" cols="100" rows="10"><?= $arrempresa['obssolicitante'] ?></textarea></td>
						</tr>
					</table>
				</div>
				<div class="panel-heading" data-toggle="collapse" href="#cotacao1">Email - Cotação</div>
				<div class="panel-body" id="cotacao1">
					<table>
						<tr>
							<td align="left" nowrap>Localização MAPS:</td>
							<td align="left" nowrap>Email NFE:</td>
						</tr>
						<tr>
							<td><input name="_1_<?= $_acao ?>_empresa_localizacaomaps" type="text" size="40" value="<?= $arrempresa['localizacaomaps'] ?>"></td>
							<td><input name="_1_<?= $_acao ?>_empresa_emailnfe" type="text" size="40" value="<?= $arrempresa['emailnfe'] ?>"></td>
						</tr>
						<tr>
							<td align="left" nowrap style="display:block;">Email CQ:</td>
						</tr>
						<tr>
							<td><input name="_1_<?= $_acao ?>_empresa_emailcq" type="text" size="40" value="<?= $arrempresa['emailcq'] ?>"></td>
						</tr>
						<tr>
							<td align="left" nowrap style="display:block;">Referência p/ entrega:</td>
						</tr>
						<tr>
							<td colspan="2"><textarea name="_1_<?= $_acao ?>_empresa_refentrega" cols="100" rows="5"><?= $arrempresa['refentrega'] ?></textarea></td>
						</tr>
						<tr>
							<td align="left" nowrap style="display:block;">Horário de Recebimento:</td>
						</tr>
						<tr>
							<td colspan="2"><textarea name="_1_<?= $_acao ?>_empresa_horariorecebimento" cols="100" rows="5"><?= $arrempresa['horariorecebimento'] ?></textarea></td>
						</tr>
					</table>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#pedido">Pedido</div>
				<div class="panel-body" id="pedido">
					<table>
						<tr>
							<td align="left" nowrap style="display:block;" class="nowrap">Pedido Consome Lote da Produção:</td>
							<td>
								<select name="_1_<?= $_acao ?>_empresa_loteproducao" id="loteproducao" class="size7">
									<?
									fillselect("select 'Y','Sim' union select 'N','Não'", $_1_u_empresa_loteproducao);
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td align="left" nowrap style="display:block;">Observação:</td>
							<td><textarea name="_1_<?= $_acao ?>_empresa_pedidoobs" cols="100" rows="10"><?= $arrempresa['pedidoobs'] ?></textarea></td>
						</tr>
					</table>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#idpessoaform">Pessoa Empresa Relacionada a <?= $_1_u_empresa_empresa ?></div>
				<div class="panel-body" id="idpessoaform">
					<table>
						<tr>
							<td align="left" nowrap style="display:block;">Empresa/Cadastro de Pessoas:</td>
							<td class="nowrap">
								<select name="_1_<?= $_acao ?>_empresa_idpessoaform" id="idpessoaform">
									<option value=""></option>
									<?
									fillselect("SELECT idpessoa,nome 
                                                        FROM pessoa p
                                                        where p.status= 'ATIVO'                                                                                  
                                                        and p.idtipopessoa = 2
                                                        -- " . getidempresa('p.idempresa', 'pessoa') . "
                                                        order by p.nome", $_1_u_empresa_idpessoaform);
									?>
								</select>
							</td>
						</tr>
						<!-- <tr>
                        <td align="left" nowrap style="display:block;">Fatura Automática:</td>
						<td>
							<select  name="_1_<?= $_acao ?>_empresa_idformapagamento" >
								<option value=""></option>
								<? fillselect("SELECT idformapagamento,descricao 
                                                        FROM formapagamento p
                                                        where p.status= 'ATIVO'                                                                                  
                                                       and p.idempresa = " . $_1_u_empresa_idempresa . "
                                                        order by p.descricao", $_1_u_empresa_idformapagamento); ?>		
							</select>
						</td>
                    </tr>
					<tr>
                        <td align="left" nowrap style="display:block;">Categoria:</td>
						<td>
							<select id="idcontaitem" onchange="preencheti()"  name="_1_<?= $_acao ?>_empresa_idcontaitem" >
								<option value=""></option>
									<? //fillselect(ConfContapagarController::buscarContaItemAtivoShare(), $_1_u_empresa_idcontaitem); 
									?>	
							</select>
						</td>
                    </tr>
					<tr>
                        <td align="left" nowrap style="display:block;"> Subcategoria:</td>
						<td>
							<select id="idtipoprodserv" name="_1_<?= $_acao ?>_empresa_idtipoprodserv" >
								<option value=""></option>
								<? //fillselect(ConfContapagarController::listarTipoProdservConfpagar($_1_u_empresa_idcontaitem), $_1_u_empresa_idtipoprodserv); 
								?>	
							</select>
						</td>
                    </tr>-->
					</table>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#cobranca">
					Rateios e Cobranças para <?= $_1_u_empresa_empresa ?>
				</div>
				<div class="panel-body" id="cobranca">
					<?
					$cobrancas = EmpresaController::buscarCobrancaEmpresa($_1_u_empresa_idempresa);
					foreach ($cobrancas as $k => $row) { ?>
						<div class="panel panel-default">
							<div class="panel-heading" data-toggle="collapse" href="#cobranca<?= $row['idempresacobranca'] ?>">
								<?= $_1_u_empresa_empresa ?>&nbsp;<i class="fa fa-long-arrow-right"></i>&nbsp;<?= traduzid('empresa', 'idempresa', 'empresa', $row['idempresad']) ?>
							</div>
							<div class="panel-body" id="cobranca<?= $row['idempresacobranca'] ?>">
								<div style="display: flex;justify-content: space-evenly;" ;>
									<table class='tbltest'>
										<tr>
											<th>
												<h5 style="margin-left: 10px;">CRÉDITO</h5>
											</th>
										</tr>
										<tr>
											<td nowrap style="display:block;"><span style="margin-left: 10px;">Fatura Automática:</span></td>
											<input type="hidden" value="<?= $row['idempresacobranca'] ?>" name="_ec<?= $row['idempresacobranca'] ?>_<?= $_acao ?>_empresacobranca_idempresacobranca">
										</tr>
										<tr>
											<td>
												<select style="margin-left: 10px;" class="size25" name="_ec<?= $row['idempresacobranca'] ?>_<?= $_acao ?>_empresacobranca_idformapagamento">
													<option value=""></option>
													<? fillselect("SELECT idformapagamento,descricao 
																		FROM formapagamento p
																		where p.status= 'ATIVO'                                                                                  
																	and p.idempresa = " . $_1_u_empresa_idempresa . "
																		order by p.descricao", $row['idformapagamento']); ?>
												</select>
											</td>
										</tr>
										<tr>
											<td nowrap style="display:block;"><span style="margin-left: 10px;">Categoria:</span></td>
										</tr>
										<tr>
											<td>
												<select style="margin-left: 10px;" class="size25" id="idcontaitem<?= $row['idempresacobranca'] ?>" onchange="preencheti(<?= $row['idempresacobranca'] ?>,true)" name="_ec<?= $row['idempresacobranca'] ?>_<?= $_acao ?>_empresacobranca_idcontaitem">
													<option value=""></option>
													<? fillselect(ConfContapagarController::buscarContaItemAtivoShare(), $row['idcontaitem']); ?>
												</select>
											</td>
										</tr>
										<tr>
											<td nowrap style="display:block;"><span style="margin-left: 10px;"> Subcategoria:</span></td>
										</tr>
										<tr>
											<td>
												<select style="margin-left: 10px;" class="size25" id="idtipoprodserv<?= $row['idempresacobranca'] ?>" name="_ec<?= $row['idempresacobranca'] ?>_<?= $_acao ?>_empresacobranca_idtipoprodserv">
													<option value=""></option>
													<? fillselect(ConfContapagarController::listarTipoProdservConfpagar($row['idcontaitem']), $row['idtipoprodserv']); ?>
												</select>
											</td>
										</tr>
									</table>
									<table class='tbltest'>
										<tr>
											<th>
												<h5 style="margin-left: 10px;">DÉBITO</h5>
											</th>
										</tr>
										<tr>
											<td nowrap><span style="margin-left: 10px;">Fatura Automática:</span></td>
											<input type="hidden" value="<?= $row['idempresacobranca'] ?>" name="_ec<?= $row['idempresacobranca'] ?>_<?= $_acao ?>_empresacobranca_idempresacobranca">
										</tr>
										<tr>
											<td>
												<select style="margin-left: 10px;" class="size25" name="_ec<?= $row['idempresacobranca'] ?>_<?= $_acao ?>_empresacobranca_idformapagamentod">
													<option value=""></option>
													<? fillselect("SELECT idformapagamento,descricao 
																		FROM formapagamento p
																		where p.status= 'ATIVO'                                                                                  
																	and p.idempresa = " . $row['idempresad'] . "
																		order by p.descricao", $row['idformapagamentod']); ?>
												</select>
											</td>
										</tr>
										<tr>
											<td nowrap><span style="margin-left: 10px;">Categoria:</span></td>
										</tr>
										<tr>
											<td>
												<select style="margin-left: 10px;" class="size25" id="idcontaitemd<?= $row['idempresacobranca'] ?>" onchange="preencheti(<?= $row['idempresacobranca'] ?>,false)" name="_ec<?= $row['idempresacobranca'] ?>_<?= $_acao ?>_empresacobranca_idcontaitemd">
													<option value=""></option>
													<? fillselect(ConfContapagarController::buscarContaItemAtivoShareEmpresaDest($row['idempresad']), $row['idcontaitemd']); ?>
												</select>
											</td>
										</tr>
										<tr>
											<td nowrap><span style="margin-left: 10px;"> Subcategoria:</span></td>
										</tr>
										<tr>
											<td>
												<select style="margin-left: 10px;" class="size25" id="idtipoprodservd<?= $row['idempresacobranca'] ?>" name="_ec<?= $row['idempresacobranca'] ?>_<?= $_acao ?>_empresacobranca_idtipoprodservd">
													<option value=""></option>
													<? fillselect(ConfContapagarController::listarTipoProdservConfpagar($row['idcontaitemd']), $row['idtipoprodservd']); ?>
												</select>
											</td>
										</tr>
									</table>
								</div>
								<div class="panel">
									<span style="font-size: 13px;">
										Excluir configuração: <i class="fa fa-trash vermelho pointer fa-lg" onclick="excluir('empresacobranca',<?= $row['idempresacobranca'] ?>)"></i>
									</span>
								</div>
							</div>
						</div>
					<? } ?>
					<span id="empresacobrancabt" style="font-size: 13px;">
						Nova Configuração de rateio: <i class="fa fa-plus-circle verde pointer fa-lg" onclick="shownovaCobranca(<?= $_1_u_empresa_idempresa ?>)"></i>
					</span>
					<div id="novacobranca" class="hidden">
						<span>Empresa destino:</span>
						<select type="text" id="empresadestino" onchange="novaCobranca(<?= $_1_u_empresa_idempresa ?>,this)">
							<option value=""></option>
							<? fillselect(EmpresaController::toFillSelect(EmpresaController::listarEmpresasAtivasSemConfCobranca($_1_u_empresa_idempresa))) ?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#dadosfaturamento">Dados de Faturamento</div>
				<div class="panel-body" id="dadosfaturamento">
					<div style="display:inline-block">

						<div class="col-md-2" align="right">
							aliqissativ
						</div>
						<div class="col-md-2">
							<input class="upper size5" name="_1_<?= $_acao ?>_empresa_aliqissativ" type="text" id="aliqissativ" vnulo value="<?= $arrempresa['aliqissativ'] ?>">
						</div>
						<div class="col-md-2" align="right">
							aliqpis
						</div>
						<div class="col-md-2">
							<input class="upper size5" name="_1_<?= $_acao ?>_empresa_aliqpis" type="text" id="aliqpis" vnulo value="<?= $arrempresa['aliqpis'] ?>">
						</div>

						<div class="col-md-2" align="right">
							aliqcofins
						</div>
						<div class="col-md-2">
							<input class="upper size5" name="_1_<?= $_acao ?>_empresa_aliqcofins" type="text" id="aliqinss" vnulo value="<?= $arrempresa['aliqcofins'] ?>">
						</div>
						<div class="col-md-2" align="right">
							aliqinss
						</div>
						<div class="col-md-2">
							<input class="upper size5" name="_1_<?= $_acao ?>_empresa_aliqinss" type="text" id="aliqinss" vnulo value="<?= $arrempresa['aliqinss'] ?>">
						</div>

						<div class="col-md-2" align="right">
							aliqir
						</div>
						<div class="col-md-2">
							<input class="upper size5" name="_1_<?= $_acao ?>_empresa_aliqir" type="text" id="aliqir" vnulo value="<?= $arrempresa['aliqir'] ?>">
						</div>

						<div class="col-md-2" align="right">
							aliqcsll
						</div>
						<div class="col-md-2">
							<input class="upper size5" name="_1_<?= $_acao ?>_empresa_aliqcsll" type="text" id="aliqcsll" vnulo value="<?= $arrempresa['aliqcsll'] ?>">
						</div>
						<div class="col-md-2" align="right">
							Regime Tributário
						</div>
						<div class="col-md-10">
							<select name="_1_<?= $_acao ?>_empresa_crt" class="size30">
								<option value=""></option>
								<? fillselect("select 1,'Simples Nacional' union select 2,'Simples Nacional, excet. receita bruta' union select 3,'Regime Normal. (v2.0)'", $arrempresa['crt']); ?>
							</select>
						</div>
						<div class="col-md-2" align="right">
							Regime Contábil
						</div>
						<div class="col-md-10">
							<select name="_1_<?= $_acao ?>_empresa_crc" class="size30">
								<option value=""></option>
								<? fillselect("select 'LR','Lucro Real' union select 'LP','Lucro Presumido'", $arrempresa['crc']); ?>
							</select>
						</div>
						<div class="col-md-2" align="right">
							Regime Apuração
						</div>
						<div class="col-md-10">
							<select name="_1_<?= $_acao ?>_empresa_cra" class="size30">
								<option value=""></option>
								<? fillselect("select 'FC','Caixa' union select 'CP','Competência'", $arrempresa['cra']); ?>
							</select>
						</div>
						<div class="col-md-2" align="right">Indicador IE</div>
						<div class="col-md-10">
							<select class="size15" name="_1_<?= $_acao ?>_empresa_indiedest">
								<option value=""></option>
								<? fillselect("select 1,'[1]-Contribuinte ICMS'
								union select 2,'[2]-Contribuinte isento'
								union select 9,'[9]-Não Contribuinte'", $arrempresa['indiedest']); ?>
							</select>
						</div>
						<div class="col-md-2" align="right">
							Código do serviço
						</div>
						<div class="col-md-10">
							<input class="upper size50" name="_1_<?= $_acao ?>_empresa_atividadeserv" type="text" id="urlwsprod" value="<?= $arrempresa['atividadeserv'] ?>">
						</div>
						<div class="col-md-2" align="right">
							Descrição Atividade
						</div>
						<div class="col-md-10">
							<input class="upper size50" name="_1_<?= $_acao ?>_empresa_atividadescrserv" type="text" id="urlwsprod" value="<?= $arrempresa['atividadescrserv'] ?>">
						</div>
						<div class="col-md-2" align="right">
							urlwsserv
						</div>
						<div class="col-md-10">
							<input class="upper size30" name="_1_<?= $_acao ?>_empresa_urlwsserv" type="text" id="urlwsserv" size="40" value="<?= $arrempresa['urlwsserv'] ?>">
						</div>
						<div class="col-md-2" align="right">
							urlwsprod
						</div>
						<div class="col-md-10">
							<input class="upper size30" name="_1_<?= $_acao ?>_empresa_urlwsprod" type="text" id="urlwsprod" size="40" value="<?= $arrempresa['urlwsprod'] ?>">
						</div>

						<div class="col-md-2" align="right">
							email
						</div>
						<div class="col-md-10">
							<input class="upper size30" name="_1_<?= $_acao ?>_empresa_email" type="text" id="email" size="40" vnulo value="<?= $arrempresa['email'] ?>">
						</div>
						<div class="col-md-2" align="right">
							Data Contingência
						</div>
						<div class="col-md-4">
							<input class="calendario size15" name="_1_<?= $_acao ?>_empresa_datacontingencia" type="text" value="<?= dmahms($arrempresa['datacontingencia']) ?>">
						</div>
						<div class="col-md-2" align="right">
							Contingência
						</div>
						<div class="col-md-2">
							<select name="_1_<?= $_acao ?>_empresa_contingencia" class="size5">
								<? fillselect("select 'N','Não' union select 'Y','Sim'", $arrempresa['contingencia']); ?>
							</select>
						</div>

					</div>
				</div>
			</div>

			<? if (!empty($arrempresa['idempresa'])) { ?>
				<div class="panel panel-default">
					<div class="panel-heading" data-toggle="collapse" href="#dadosicms">Informações ICMS</div>
					<div class="panel-body" id="dadosicms">
						<div class="col-md-12">
							<table class="table table-striped planilha">
								<?
								$sqlListarEstados = "SELECT * FROM classificacaoicms WHERE idempresa = '".$arrempresa['idempresa']."' AND uf = '".$arrempresa['uf']."'";
								$resListarEstados = d::b()->query($sqlListarEstados) or die("A Consulta de Finalidade itens falhou :" . mysql_error() . "<br>Sql:" . $sql1);
								$rowLE = mysqli_fetch_assoc($resListarEstados);
								$qtdC = mysqli_num_rows($resListarEstados);
								$_acaoC = ($qtdC > 0) ? 'u' : 'i';
								?>
								<tr>
									<td class="size10">
										<input name="_e9988_<?=$_acaoC?>_classificacaoicms_idclassificacaoicms" type="hidden" value="<?=$rowLE["idclassificacaoicms"] ?>">
										<input name="_e9988_<?=$_acaoC?>_classificacaoicms_idempresa" type="hidden" value="<?=$arrempresa['idempresa']?>">
										<input name="_e9988_<?=$_acaoC?>_classificacaoicms_uf" type="hidden" value="<?=$arrempresa['uf'] ?>">
										<input name="_e9988_<?=$_acaoC?>_classificacaoicms_status" type="hidden" value="ATIVO">
										<?=$arrempresa['uf'] ?>
									</td>
									<td align="center">
										<input name="_e9988_<?=$_acaoC?>_classificacaoicms_descricaoicms" type="text" value="<?=$rowLE["descricaoicms"] ?>">
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>

				<div class="panel panel-default">
					<div class="panel-heading" data-toggle="collapse" href="#dadosfinalidade">Finalidade</div>
					<div class="panel-body" id="dadosfinalidade">
						<?
						$sql1 = "SELECT f.idfinalidadeprodserv,finalidadeprodserv ,p.idfinalidadeempresa
						    	  FROM finalidadeempresa p left join finalidadeprodserv f on(f.idfinalidadeprodserv= p.idfinalidadeprodserv)
						  WHERE p.idempresaobj = '" . $arrempresa['idempresa'] . "' 
					   ORDER BY f.finalidadeprodserv;";

						$res1 = d::b()->query($sql1) or die("A Consulta de Finalidade itens falhou :" . mysql_error() . "<br>Sql:" . $sql1);
						$qtdf = mysqli_num_rows($res1);
						?>
						<div class="col-md-12">


							<table class="table table-striped planilha">
								<?
								$i = 9977;
								while ($row1 = mysqli_fetch_assoc($res1)) {
									$i++;
								?>
									<tr>
										<td>
											<input name="_<?= $i ?>_u_finalidadeempresa_idfinalidadeempresa" type="hidden" value="<?= $row1["idfinalidadeempresa"] ?>">
											<select name="_<?= $i ?>_u_finalidadeempresa_idfinalidadeprodserv">
												<option value="">Selecione a finalidade</option>
												<? fillselect("SELECT f.idfinalidadeprodserv, f.finalidadeprodserv
												   FROM finalidadeprodserv f
												  WHERE f.status = 'ATIVO' 
                                                   and not exists (select 1 from finalidadeempresa f2 where f2.idempresaobj= " . $arrempresa['idempresa'] . " and f.idfinalidadeprodserv= f2.idfinalidadeprodserv and f2.idfinalidadeempresa!=" . $row1["idfinalidadeempresa"] . " )
											   ORDER BY f.finalidadeprodserv", $row1['idfinalidadeprodserv']); ?>
											</select>
										</td>
										<td align="center">
											<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('finalidadeempresa',<?= $row1['idfinalidadeempresa'] ?>)" alt="Excluir !"></i>
										</td>
									</tr>
								<?
								}

								?>
								<tr>
									<td colspan="5">
										<i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novaFinalidade('finalidadeempresa')" alt="Inserir novo!"></i>
										<? if ($qtdf < 1) { ?><span style="color: red;"><b>É NECESSÁRIO CADASTRAR PELO MENOS UMA FINALIDADE.</b></span><? } ?>
									</td>
								</tr>
							</table>


						</div>

					</div>
				</div>
			<? } ?>
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#endereco">Endereço</div>
				<div class="panel-body" id="endereco">
					<div style="display:inline-block">
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-2" align="right">
									CEP:
								</div>
								<div class="col-md-3 " align="left">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_cep" type="text" id="cep" size="40" vnulo value="<?= $arrempresa['cep'] ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-2 " align="right">
									Endereço:
								</div>
								<div class="col-md-9">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_xlgr" type="text" id="xlgr" size="40" vnulo value="<?= $arrempresa['xlgr'] ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-2" align="right">
									Nº:
								</div>
								<div class="col-md-2">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_nro" type="text" id="nro" size="40" vnulo value="<?= $arrempresa['nro'] ?>">
								</div>
								<div class="col-md-3" align="right">
									Bairro:
								</div>
								<div class="col-md-4">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_xbairro" type="text" id="xbairro" size="40" vnulo value="<?= $arrempresa['xbairro'] ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-2" align="right">
									Cód. Muinicípio:
								</div>
								<div class="col-md-2">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_cmun" type="text" id="cmun" size="40" vnulo value="<?= $arrempresa['cmun'] ?>">
								</div>
								<div class="col-md-3" align="right">
									Município:
								</div>
								<div class="col-md-4">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_xmun" type="text" id="xmun" size="40" vnulo value="<?= $arrempresa['xmun'] ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="col-md-2" align="right">
									Cód. UF:
								</div>
								<div class="col-md-2">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_cuf" type="text" id="cuf" size="40" vnulo value="<?= $arrempresa['cuf'] ?>">
								</div>
								<div class="col-md-3" align="right">
									UF:
								</div>
								<div class="col-md-4">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_uf" type="text" id="uf" size="40" vnulo value="<?= $arrempresa['uf'] ?>">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">

								<div class="col-md-2" align="right">
									Cód. País:
								</div>
								<div class="col-md-2">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_cpais" type="text" id="cpais" size="40" vnulo value="<?= $arrempresa['cpais'] ?>">
								</div>
								<div class="col-md-3" align="right">
									País:
								</div>
								<div class="col-md-4">
									<input class="upper" name="_1_<?= $_acao ?>_empresa_xpais" type="text" id="xpais" size="40" vnulo value="<?= $arrempresa['xpais'] ?>">
								</div>

							</div>
						</div>
					</div>
				</div>

			</div>

			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#dominio">Configuração do servidor de Emails</div>
				<div class="panel-body" id="dominio" style="padding-top: 0px !important;">
					<span>Colocar domínios válidos de internet, aqui, libera o domínio a receber emails externos.<br>* Necessita configuração de Infra.</span>
					<table class="table table-striped planilha">
						<?
						$sqlh = "select *
                            from dominio 
                            where idempresa=" . $_1_u_empresa_idempresa . " order by dominio ";

						$resh = d::b()->query($sqlh) or die("Erro ao buscar dominios : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlh);
						$qtdh = mysqli_num_rows($resh);
						if ($qtdh > 0) {
						?>
							<tr>
								<th>Domínio</th>
								<th>Status</th>
								<th></th>
							</tr>

							<?
							while ($rowh = mysqli_fetch_assoc($resh)) {
								$i = $i + 1;
							?>
								<tr>
									<td>
										<input name="_<?= $i ?>_u_dominio_iddominio" type="hidden" value="<?= $rowh['iddominio'] ?>">
										<input name="_<?= $i ?>_u_dominio_dominio" type="text" value="<?= $rowh['dominio'] ?>">
									</td>
									<td>
										<select name="_<?= $i ?>_u_dominio_status" type="text" vnulo>
											<? fillselect("select 'INATIVO','INATIVO' union select 'ATIVO','ATIVO'", $rowh['status']); ?>
										</select>
									</td>
									<td style="width:1%">
										<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('dominio',<?= $rowh["iddominio"] ?>)" alt="Excluir !"></i>
									</td>
								</tr>
						<?
							} //while($rowh=mysqli_fetch_assoc($resh)){  
						} //if($qtdh>0){
						?>
						<tr>
							<td colspan="2">Novo:<i class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novo('dominio')" alt="Inserir novo dominio!"></i></td>
						</tr>
					</table>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#confemp">Configurações de Layout por Host</div>
				<div class="panel-body" id="confemp" style="padding-top: 0px !important;">
					<? $sqlh = "select *
                            from empresalayout
                            where idempresa=" . $_1_u_empresa_idempresa;

					$resh = d::b()->query($sqlh) or die("Erro ao buscar layout : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlh);
					$qtdh = mysqli_num_rows($resh);
					?>
					<table style="width: 100%;">
						<?
						if ($qtdh > 0) {
							while ($rowh = mysqli_fetch_assoc($resh)) {
								$i++;
						?>

								<tr>
									<th colspan="3" style="font-size: 1.2em;text-align: center !important;">
										<?= $rowh['hostname'] ?>
										<input name="_e<?= $i ?>_u_empresalayout_idempresalayout" type="hidden" value="<?= $rowh['idempresalayout'] ?>">
										<input name="_e<?= $i ?>_u_empresalayout_hostname" type="hidden" value="<?= $rowh['hostname'] ?>">
									</th>
								</tr>
								<tr>
									<td style="width: 7%;">
										Favicon:&nbsp;&nbsp;
									</td>
									<td>
										<input name="_e<?= $i ?>_u_empresalayout_favicon" type="text" value="<?= $rowh['favicon'] ?>">
									</td>
								</tr>
								<tr>
									<td style="width: 7%;">
										Css:&nbsp;&nbsp;
									</td>
									<td>
										<textarea name="_e<?= $i ?>_u_empresalayout_css"><?= $rowh['css'] ?></textarea>
									</td>
								</tr>
								<tr>
									<td style="width: 7%;">
										Footer:&nbsp;&nbsp;
									</td>
									<td>
										<textarea name="_e<?= $i ?>_u_empresalayout_footer"><?= $rowh['footer'] ?></textarea>
									</td>
								</tr>

						<? }
						} ?>
					</table>
					<br>
					<table style="width: 100%;">
						<tr>
							<td colspan="2">Novo Host: <input id="novohost" class="size20" type="text"> <i class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novoLayout()" alt="Inserir novo dominio!"></i></td>
						</tr>
					</table>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading" data-toggle="collapse" href="#infemail">Configurações de Email</div>
				<div class="panel-body" id="infemail">
					<table style="width: 100%;">
						<thead>
							<tr>
								<td>
									<table style="width: 100%;">
										<thead>
											<tr>
												<th style="text-align:center;">Tipo</th>
												<th style="text-align:center;">Nome do Remetente</th>
											</tr>
										</thead>
									</table>
								</td>
							</tr>
						</thead>
						<tbody>
							<?
							$sqlrodape = "SELECT * FROM empresarodapeemail WHERE idempresa = " . $_1_u_empresa_idempresa;

							$resrodape = d::b()->query($sqlrodape) or die("Erro ao buscar rodapés de email : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlrodape);
							$qtdrodape = mysqli_num_rows($resrodape);
							if ($qtdrodape > 0) {
								$i = 99;
								$j = 599;
								while ($rowrodape = mysqli_fetch_assoc($resrodape)) {
									$i = $i + 1;
							?>
									<tr>
										<td colspan="2">
											<div style="border: 1px solid darkgray;border-left-width: 5px;border-radius: 5px;padding-top: 5px;">
												<table style="width: 100%;">
													<tr>
														<td style="width:40%;">
															<input name="_<?= $i ?>_u_empresarodapeemail_idempresarodapeemail" type="hidden" value="<?= $rowrodape['idempresarodapeemail'] ?>">
															<select name="_<?= $i ?>_u_empresarodapeemail_tipoenvio" vnulo>
																<option></option>
																<?
																fillselect("SELECT 'COTACAO','Orçamento de Compra - Cotação'
																union SELECT 'COTACAOAPROVADA','Orçamento de Compra - Cotação Aprovada'
																union SELECT 'DETALHAMENTO','NFS - Detalhamento'
																union SELECT 'NFP','Pedido - Nota Fiscal de Produto'
																union SELECT 'NFPS','Pedido - Nota Fiscal de Serviço'
																union SELECT 'NFS','NFS - Nota Fiscal de Serviço' 
																union SELECT 'ORCPROD','Pedidos - Orçamento Produto'
																union SELECT 'ORCSERV','Orçamento Serviços - Orçamento de Serviço'
																union SELECT 'RESULTADOOFICIAL','Resultados - E-mails Oficiais'
																union SELECT 'EMAILCONTATOEMPRESA','Resultados - E-mails Contato Empresa'
																union SELECT 'PEDIDOENTREGUE', 'Pedido Entregue'", $rowrodape['tipoenvio']);
																?>
															</select>
														</td>
														<td>
															<input name="_<?= $i ?>_u_empresarodapeemail_nomeremetente" type="text" value="<?= $rowrodape['nomeremetente'] ?>">
														</td>
														<td style="width:1%;" data-toggle="collapse" href="#inf<?= $i ?>">
															<i class="fa fa-arrows-v cinzaclaro hoververde btn-sm pointer"></i>
														</td>
														<td style="width:1%;">
															<? if (!empty($rowrodape['tipoenvio'])) { ?>
																<a href="form/rodapeemails.php?idempresa=<?= $_1_u_empresa_idempresa ?>&tipoemail=<?= $rowrodape['tipoenvio'] ?>" target="_blank">
																	<i class="fa fa-print cinzaclaro hoverazul btn-sm pointer"></i>
																</a>
															<? } ?>
														</td>
														<td style="width:1%;">
															<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer" onclick="excluir('empresarodapeemail',<?= $rowrodape["idempresarodapeemail"] ?>)" alt="Excluir !"></i>
														</td>
													</tr>
													<tr>
														<td colspan="5">
															<div class="panel panel-default" id="inf<?= $i ?>" style="background-color:#EDEDED;">
																<div class="panel-heading" data-toggle="collapse" href="#info<?= $i ?>">Informações</div>
																<div class="panel-body" id="info<?= $i ?>">
																	<table style="width:100%;">
																		<tr>
																			<td>Assunto:</td>
																		</tr>
																		<tr>
																			<td colspan="2"><input name="_<?= $i ?>_u_empresarodapeemail_assunto" type="text" value="<?= $rowrodape['assunto'] ?>" placeholder="Exemplo: Orçamento _info_ nome empresa"></td>
																		</tr>
																		<tr>
																			<td>Com Cópia:</td>
																			<td>Nome Com Cópia:</td>
																		</tr>
																		<tr>
																			<td><input name="_<?= $i ?>_u_empresarodapeemail_comcopia" type="text" value="<?= $rowrodape['comcopia'] ?>"></td>
																			<td><input name="_<?= $i ?>_u_empresarodapeemail_nomecc" type="text" value="<?= $rowrodape['nomecc'] ?>"></td>
																		</tr>
																		<tr>
																			<td colspan="2">
																				<hr>
																			</td>
																		</tr>
																		<? if (!empty($rowrodape['tipoenvio'])) { ?>
																			<tr>
																				<td>Email(s) Remetente:</td>
																			</tr>
																			<tr>
																				<td colspan="2">
																					<table style="width:100%;">
																						<tbody>
																							<?
																							$sqlemail = "SELECT *
																							   FROM empresaemails  																							WHERE tipoenvio = '" . $rowrodape['tipoenvio'] . "' 
																								AND idempresa = $_1_u_empresa_idempresa
																								AND idempresarodapeemail = '" . $rowrodape['idempresarodapeemail'] . "'";

																							$resemail = d::b()->query($sqlemail) or die("Erro ao buscar emails da empresa : " . mysqli_error(d::b()) . "<p>SQL:" . $sqlemail);
																							$qtdemail = mysqli_num_rows($resemail);
																							if ($qtdemail > 0) {

																								while ($rowemail = mysqli_fetch_assoc($resemail)) {
																									$j = $j + 1 + $i;
																							?>
																									<tr>
																										<td>
																											<input name="_<?= $j ?>_u_empresaemails_idempresaemails" type="hidden" value="<?= $rowemail['idempresaemails'] ?>">
																											<input name="_<?= $j ?>_u_empresaemails_idempresarodapeemail" type="hidden" value="<?= $rowrodape['idempresarodapeemail'] ?>">
																											<select name="_<?= $j ?>_u_empresaemails_idemailvirtualconf">
																												<option></option>
																												<?
																												fillselect("SELECT idemailvirtualconf,email_original 
																												FROM emailvirtualconf 
																												WHERE 1 " . getidempresa('idempresa', 'empresa') . "
																												AND status = 'ATIVO'
																												ORDER BY email_original asc", $rowemail['idemailvirtualconf']);
																												?>
																											</select>
																										</td>
																										<td style="width:1%;">
																											<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer" onclick="excluir('empresaemails',<?= $rowemail['idempresaemails'] ?>)" alt="Excluir !"></i>
																										</td>
																									</tr>
																								<?
																								}
																							} else {
																								$j = $j + 1 + $i;
																								?>
																								<tr>
																									<td>
																										<input name="_<?= $j ?>_i_empresaemails_tipoenvio" type="hidden" value="<?= $rowrodape['tipoenvio'] ?>">
																										<input name="_<?= $j ?>_i_empresaemails_idempresarodapeemail" type="hidden" value="<?= $rowrodape['idempresarodapeemail'] ?>">
																										<select name="_<?= $j ?>_i_empresaemails_idemailvirtualconf">
																											<option></option>
																											<?
																											fillselect("SELECT idemailvirtualconf,email_original 
																											FROM emailvirtualconf 
																											WHERE 1 " . getidempresa('idempresa', 'empresa') . "
																											AND status = 'ATIVO'
																											ORDER BY email_original asc", $rowemail['idemailvirtualconf']);
																											?>
																										</select>
																									</td>
																									<td style="width:1%;">
																										<i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-sm pointer" onclick="excluir('empresaemails',<?= $rowemail['idempresaemails'] ?>)" alt="Excluir !"></i>
																									</td>
																								</tr>
																							<?
																							}
																							?>
																							<tr>
																								<td colspan="2">Novo:<i class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novoemail('<?= $rowrodape['tipoenvio'] ?>',<?= $_1_u_empresa_idempresa ?>)" alt="Inserir novo email!"></i></td>
																							</tr>
																						</tbody>
																					</table>
																				</td>
																			</tr>
																		<? } ?>
																	</table>
																</div>
																<div class="panel-heading" data-toggle="collapse" href="#rodape<?= $i ?>">Rodapé</div>
																<div class="panel-body" id="rodape<?= $i ?>">
																	<table>
																		<tr>
																			<td>Telefone:</td>
																			<td>Assinatura:</td>
																		</tr>
																		<tr>
																			<td><input name="_<?= $i ?>_u_empresarodapeemail_telefone" type="text" value="<?= $rowrodape['telefone'] ?>"></td>
																			<td><input name="_<?= $i ?>_u_empresarodapeemail_titulo" type="text" value="<?= $rowrodape['titulo'] ?>"></td>
																		</tr>
																		<tr>
																			<td>Texto:</td>
																		</tr>
																		<tr>
																			<td colspan="2">
																				<textarea name="_<?= $i ?>_u_empresarodapeemail_texto" cols="100" rows="3"><?= $rowrodape['texto'] ?></textarea>
																			</td>
																		</tr>
																	</table>
																</div>
															</div>
														</td>
													</tr>
												</table>
											</div>
										</td>
									</tr>

									<tr>
										<td colspan="5">
											<hr>
										</td>
									</tr>
							<?
								} //while($rowh=mysqli_fetch_assoc($resh)){  
							} //if($qtdh>0){
							?>
						</tbody>
						<tr>
							<td colspan="2">Novo:<i class="fa fa-plus-circle fa-x verde btn-lg pointer" onclick="novo('empresarodapeemail')" alt="Inserir tipo email!"></i></td>
						</tr>

					</table>
				</div>
			</div>

		</div>
	</div>

	<!--div class="row">
        
        <div class="col-md-12">
        <div class="panel panel-default">  
            <div class="cbupload" title="Clique ou arraste arquivos para cá" style="width:100%;height:100%;">
                    <i class="fa fa-cloud-upload fonte18"></i>
            </div>
        </div>
        </div>
    </div-->
<?
}
?>
<?
if (!empty($_1_u_empresa_idempresa)) { // trocar p/ cada tela a tabela e o id da tabela
	$_idModuloParaAssinatura = $_1_u_empresa_idempresa; // trocar p/ cada tela o id da tabela
	require 'viewAssinaturas.php';
}
$tabaud = "empresa"; //pegar a tabela do criado/alterado em antigo
require 'viewCriadoAlterado.php';
?>
<script>
	$('.selectpicker').selectpicker();

	function preencheti(idempresacobranca, credito) {

		if (credito) {
			alias = '';
		} else {
			alias = 'd';
		}

		$("#idtipoprodserv" + alias + idempresacobranca).html("<option value=''>Procurando....</option>");

		$.ajax({
			type: "get",
			url: "ajax/buscacontaitem.php",
			data: {
				idcontaitem: $("#idcontaitem" + alias + idempresacobranca).val()
			},

			success: function(data) {
				$("#idtipoprodserv" + alias + idempresacobranca).html(data);
			},

			error: function(objxmlreq) {
				alert('Erro:<br>' + objxmlreq.status);

			}
		}) //$.ajax

	}
	<? if (!empty($_1_u_empresa_idempresa)) { ?>
		$(".cbupload").dropzone({
			idObjeto: $("[name=_1_u_empresa_idempresa]").val(),
			tipoObjeto: 'empresa',
			idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
		});

		$("#certanexo").css('cursor', 'pointer');

		$("#certanexo").dropzone({
			url: "form/_arquivo.php",
			idObjeto: $("[name=_1_u_empresa_idempresa]").val() || '<?= $_1_u_empresa_idempresa ?>',
			tipoObjeto: 'empresa',
			tipoArquivo: 'CERTIFICADO',
			idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
			//,caminho: "../inc/nfe/sefaz4/certs/"
		});

		// GVT - 18/06/2020 - Alterado a criação de Dropzones de imagens da Empresa
		// Para adicionar novo Dropzone, colocar:
		//		identificador: id do dropzone no HTML
		//		tipoimagem: nome para identificar o tipo de imagem.
		// Obs:
		//		O nome do tipoimagem será utilizado em eventcode/arquivo/posupload__empresa__"tipoimagem".php,
		//		para tratamento e inserção na tabela empresaimagem.
		var imagens = [{
				"identificador": "imagemheaderservico",
				"tipoimagem": "HEADERSERVICO"
			},
			{
				"identificador": "imagemheaderpedido",
				"tipoimagem": "HEADERPEDIDO"
			},
			{
				"identificador": "imagemheaderproduto",
				"tipoimagem": "HEADERPRODUTO"
			},
			{
				"identificador": "imagemlogosistema",
				"tipoimagem": "LOGOSISTEMA"
			},
			{
				"identificador": "imagememail",
				"tipoimagem": "IMAGEMEMAIL"
			},
			{
				"identificador": "imagemdanfe",
				"tipoimagem": "IMAGEMEMPRESADANFE"
			},
			{
				"identificador": "imagemrodape",
				"tipoimagem": "IMAGEMRODAPE"
			},
			{
				"identificador": "imagemmarcadagua",
				"tipoimagem": "IMAGEMMARCADAGUA"
			},
			{
				"identificador": "imagemicon",
				"tipoimagem": "IMAGEMICON"
			},
			{
				"identificador": "imagemlogin",
				"tipoimagem": "IMAGEMLOGIN"
			}
		];

		// Função construtora de dropzones.

		imagens.map(function(x) {
			//console.log(x.identificador+" : "+x.tipoimagem);
			var id = x.identificador;
			var tipoimagem = x.tipoimagem;

			$("#" + id).dropzone({
				url: "form/_arquivo.php",
				idObjeto: $("[name=_1_u_empresa_idempresa]").val() || '<?= $_1_u_empresa_idempresa ?>',
				tipoObjeto: 'empresa',
				tipoArquivo: tipoimagem,
				idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>',
				sending: function(file, xhr, formData) {
					formData.append("idobjeto", this.options.idObjeto);
					formData.append("tipoobjeto", this.options.tipoObjeto);
					formData.append("tipoarquivo", this.options.tipoArquivo);
				},
				success: function(file, response) {
					this.options.loopArquivos(response);
				},
				init: function() {
					var thisDropzone = this;
					$.ajax({
						url: this.options.url + "?tipoobjeto=" + this.options.tipoObjeto + "&idobjeto=" + this.options.idObjeto + "&tipoarquivo=" + this.options.tipoArquivo
					}).done(function(data, textStatus, jqXHR) {
						thisDropzone.options.loopArquivos(data);
					})
				},
				loopArquivos: function(data) {
					jResp = jsonStr2Object(data);
					if (jResp.length > 0) {
						nomeArquivo = jResp[jResp.length - 1].nome;
						if (nomeArquivo) {
							$("#" + id).attr("src", "upload/imagenssistema/" + nomeArquivo);
						}
					}
				}
			})
		});

		<?
		$sqlcert = "SELECT * FROM empresaimagem WHERE tipoimagem = 'RODAPEEMAIL' AND idempresa = " . $_1_u_empresa_idempresa;
		$rescert = d::b()->query($sqlcert) or die("A Consulta do certificado falhou :" . mysql_error() . "<br>Sql:" . $sqlcert);
		$ncert = mysql_num_rows($rescert);
		if ($ncert > 0) {
			$arrcerttmp = array();
			$w = 0;
			while ($rcert = mysqli_fetch_assoc($rescert)) {
				$arrcerttmp[$w]["id"] = $rcert["idempresaimagem"];
				$arrcerttmp[$w]["nome"] = str_replace("../upload/imagenssistema/", "", $rcert["caminho"]);
				$arrcerttmp[$w]["caminho"] = $rcert["caminho"];
				$w++;
			}

			$arrcerttmp = $JSON->encode($arrcerttmp);
		} else {
			$arrcerttmp = 0;
		}
		?>
		var jCert = <?= $arrcerttmp ?>;

		$("#rodapeanexo").dropzone({
			idObjeto: $("[name=_1_u_empresa_idempresa]").val() || '<?= $_1_u_empresa_idempresa ?>',
			tipoObjeto: 'empresa',
			tipoArquivo: 'RODAPEEMAIL',
			idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>',
			init: function() {
				this.on("sending", function(file, xhr, formData) {
					formData.append("idobjeto", this.options.idObjeto);
					formData.append("tipoobjeto", this.options.tipoObjeto);
					formData.append("tipoarquivo", this.options.tipoArquivo);
					formData.append("idPessoaLogada", this.options.idPessoaLogada);
				});

				this.on("error", function(file, response, xhr) {
					if (xhr.getResponseHeader('x-cb-formato') == 'erro' && xhr.getResponseHeader('x-cb-resposta') == '0') {
						alertAtencao("Formato do Arquivo de Certificado Inválido");
					} else {
						alertErro("Ocorreu um erro inesperado");
					}
				});

				this.on("addedfile", function(file) {
					var removeButton = Dropzone.createElement("<i class='fa fa-trash hoververmelho' title='Apagar arquivo'></i>");

					var _this = this;

					removeButton.addEventListener("click", function(e) {
						e.preventDefault();
						e.stopPropagation();
						if (confirm("Deseja realmente excluir o arquivo?")) {

							_this.removeFile(file);
							CB.post({
								objetos: "_9999_d_empresaimagem_idempresaimagem=" + file.id
							})
						}
					});


					file.previewElement.appendChild(removeButton);

					file.previewElement.addEventListener("click", function(e) {
						e.preventDefault();
						e.stopPropagation();

						janelamodal("upload/imagenssistema/" + file.nome);
					});

				});

				vthis = this;
				if (jCert !== 0) {
					jCert.forEach(function(el, i) {
						var mockFile = {
							name: el.nome,
							nome: el.nome,
							caminho: el.caminho,
							id: el.id
						};
						vthis.emit("addedfile", mockFile).emit("complete", mockFile);
					});
				}
			}
		});
	<? } ?>

	function novo(inobj) {
		CB.post({
			objetos: "_x_i_" + inobj + "_status=ATIVO"
		});
	}

	function novaFinalidade(inobj) {
		CB.post({
			objetos: "_x_i_" + inobj + "_status=ATIVO&_x_i_" + inobj + "_idempresaobj=" + $("[name=_1_u_empresa_idempresa]").val()
		});
	}

	function habilitarfilial(val, id) {
		CB.post({
			objetos: "_x_u_empresa_filial=" + val + "&_x_u_empresa_idempresa=" + id
		});
	}

	function habilitarmatriz(val, id) {
		CB.post({
			objetos: "_x_u_empresa_habilitarmatriz=" + val + "&_x_u_empresa_idempresa=" + id
		});
	}

	function novoLayout() {
		if ($("#novohost").val()) {
			CB.post({
				objetos: "_x_i_empresalayout_idempresa=" + $("[name=_1_u_empresa_idempresa]").val() + "&_x_i_empresalayout_hostname=" + $("#novohost").val()
			});
		} else {
			alertAtencao("Host não pode ser vazio!");
		}

	}

	function novoemail(tipoenvio, idempresa) {
		CB.post({
			objetos: "_x_i_empresaemails_tipoenvio=" + tipoenvio + "&_x_i_empresaemails_idempresa=" + idempresa
		});
	}

	function shownovaCobranca(idempresa) {
		$("#novacobranca").removeClass('hidden');
		$("#empresacobrancabt").addClass('hidden');
	}

	function novaCobranca(idempresa, vthis) {
		CB.post({
			objetos: "_x_i_empresacobranca_idempresa=" + idempresa + "&_x_i_empresacobranca_idempresad=" + $(vthis).val(),
			parcial: true
		});
	}

	function excluir(tab, inid) {
		if (confirm("Deseja retirar este?")) {
			CB.post({
				objetos: "_x_d_" + tab + "_id" + tab + "=" + inid,
				parcial: true
			});
		}
	}

	$("#modalconfiguracaomatriz").click(function() {
		var idempresa = $("[name=_1_u_empresa_idempresa]").val();
		CB.modal({
			url: "?_modulo=matrizconf&_acao=u&idmatriz=" + idempresa,
			header: "Fornecedor"
		});
	});

	function inserirempresa(vthis){
		var $estado = $(vthis).val();
		var idempresa = $("[name=_1_u_empresa_idempresa]").val();

		if($estado != '') {
			CB.post({
				objetos: `_100_i_classificacaoicms_idempresa=${idempresa}&_100_i_classificacaoicms_estado=${$estado}&_100_i_classificacaoicms_status=ATIVO`,		
				parcial: true
			});
		}
	}

</script>
<?
require_once '../inc/php/readonly.php';
?>