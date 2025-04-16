<?
/*
 * gerar os campos da prateleira
 * 
 */
require_once("../inc/php/cmd.php");
require_once(__DIR__."/../../form/controllers/fluxo_controller.php");

// CONTROLLERS
require_once(__DIR__."/../../form/controllers/tag_controller.php");
require_once(__DIR__."/../../form/controllers/tagdim_controller.php");

// QUERY
require_once(__DIR__."/../../form/querys/_iquery.php");
require_once(__DIR__."/../../form/querys/tag_query.php");
require_once(__DIR__."/../../form/querys/tagsala_query.php");
require_once(__DIR__."/../../form/querys/device_query.php");

$iu = $_SESSION['arrpostbuffer']['1']['u']['tag']['idtag'] ? 'u' : 'i';
$idtagclass=$_SESSION['arrpostbuffer']['1']['i']['tag']['idtagclass'];

/**
 * Se for insert e a clasisiicacao for prateleira
 */
if($iu == "i" and $idtagclass==4){
	
	$linha = $_SESSION['arrpostbuffer']['1']['i']['tag']['linha'];
	$coluna = $_SESSION['arrpostbuffer']['1']['i']['tag']['coluna'];
	$caixa = $_SESSION['arrpostbuffer']['1']['i']['tag']['caixa'];
	$idtag = $_SESSION["_pkid"];
	
	$inseridoPrateleiraNaTagDim = TagDimController::inserirPrateleiraNaTagDim($idtag, $linha, $coluna, $caixa);
}

if(!empty($_SESSION["_pkid"]) && !empty($_POST['data_inicio']) && $_SESSION["arrpostbuffer"]["1"]["u"]["tag"]["status"] == 'LOCADO')
{
    $idTagQueSeraLocada = $_SESSION["_pkid"];
    $idEmpresaDoNovoLocal = $_POST['duplicar_idempresa'];
    $idUnidadeDoNovoLocal = $_POST['unidade_destino'];

    $dataInicioLocacao = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['data_inicio'])));
    $dataFimLocacao = $_POST['data_fim'] ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['data_fim']))) : false;

    $tagJaLocadaNestePeriodo = TagController::validarPrazoLocacao($idTagQueSeraLocada, $idEmpresaDoNovoLocal, $dataInicioLocacao);

    if($tagJaLocadaNestePeriodo)
    {
        die('Tag indisponível para Alocar nesse período.');
    }

    $tagVeioDeUmaLocacao = SQL::ini(TagReservaQuery::buscarPeloIdObjeto(), [
            'idobjeto' => $idTagQueSeraLocada,
            'tipoobjeto' => 'tag'
        ])::exec();

    $tagLocacaoPai = TagController::locacaoDeTag($idTagQueSeraLocada, $idEmpresaDoNovoLocal, $idUnidadeDoNovoLocal, $dataInicioLocacao, $dataFimLocacao, $tagVeioDeUmaLocacao->data[0]);

    /**
     * Verifica se a tag possui filhos
    */
    $filhosDaSalaQueEstaSendoLocada = SQL::ini(TagQuery::buscarFilhos(), [
        'idtag' => $idTagQueSeraLocada
    ])::exec();

    if($filhosDaSalaQueEstaSendoLocada->numRows())
    {
        TagController::locacaoDeTagFilhos(isset($tagLocacaoPai['idobjeto']) ? $tagLocacaoPai['idobjeto'] : $tagLocacaoPai['idtag'], $filhosDaSalaQueEstaSendoLocada->data, $idEmpresaDoNovoLocal, $idUnidadeDoNovoLocal, $dataInicioLocacao, $dataFimLocacao);
    }

	cbSetCustomHeader('X-i-TAG', true);
    cbSetCustomHeader('newidtag', isset($tagLocacaoPai['idobjeto']) ? $tagLocacaoPai['idobjeto'] : $tagLocacaoPai['idtag']);

}

$idfuncionario = $_SESSION['arrpostbuffer']['1'][$iu]['tag']['idpessoa'];
$idtag = $_SESSION['arrpostbuffer']['1'][$iu]['tag']['idtag'];
if($_POST['_idpessoa_old'] != $idfuncionario){

    $historico = TagController::buscarHistoricoTag($idfuncionario, $idtag);
    if($historico){
        TagController::updateHistoricoTag($historico['idtaghistorico']);
    }

    if(!empty($idfuncionario)){
        $arrayHistorico = [
            "idempresa" => CB::idempresa(),
            "idtag" => $idtag,        
            "campo" => 'funcionario',
            "campovalue" => $idfuncionario,
            "datainicio" => date('Y-m-d h:m:s'),
            "usuario" => $_SESSION["SESSAO"]["USUARIO"]
        ];
        TagController::inserirTagHistorico($arrayHistorico);
    }  
}
?>