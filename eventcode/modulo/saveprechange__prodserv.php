<?
$iu = $_SESSION['arrpostbuffer']['1']['i']['prodserv']['tipo'] ? 'i' : 'u';

if($iu == 'i' && !empty($_SESSION['arrpostbuffer']['1']['i']['prodserv']['codprodserv']))
{
    //buscar unidade de almoxarifado
    $unidadeestoque = ProdservController::buscarIdunidadePorTipoUnidade(3, cb::idempresa());
    if(empty($unidadeestoque['idunidade'])){ 
        $tipounidade = traduzid('tipounidade', 'idtipounidade', 'tipounidade', 3);
        die('Não configurada unidade do tipo '.$tipounidade.' para a empresa.');
    }
    $_SESSION['arrpostbuffer']['1']['i']['prodserv']['idunidadeest'] = $unidadeestoque['idunidade'];

    $unidadealerta = ProdservController::buscarIdunidadePorTipoUnidade(19, cb::idempresa());
    if(empty($unidadealerta['idunidade'])){
        $tipounidade = traduzid('tipounidade', 'idtipounidade', 'tipounidade', 19);
        die('Não configurada unidade do tipo '.$tipounidade.' para a empresa.');
    }
	$_SESSION['arrpostbuffer']['1']['i']['prodserv']['idunidadealerta'] = $unidadealerta['idunidade'];

    $arrfind = array("#","'", ";",'"',"$","*",'&');   
    $strcod = $_SESSION['arrpostbuffer']['1']['i']['prodserv']['codprodserv'];
    $_SESSION['arrpostbuffer']['1']['i']['prodserv']['codprodserv'] = trim(str_replace($arrfind, "", $strcod)); 

    $strdescr = $_SESSION['arrpostbuffer']['1']['i']['prodserv']['descr'];
    $_SESSION['arrpostbuffer']['1']['i']['prodserv']['descr'] = trim(str_replace($arrfind, "", $strdescr)); 

    $strdescrcurta = $_SESSION['arrpostbuffer']['1']['i']['prodserv']['descrcurta'];
    $_SESSION['arrpostbuffer']['1']['i']['prodserv']['descrcurta'] = trim(str_replace($arrfind, "", $strdescrcurta)); 
}


//retirar as unidades da empresa não mais relacionada a prodserv
$d_idobjempresa = $_SESSION['arrpostbuffer']['x']['d']['objempresa']['idobjempresa'];
if(!empty($d_idobjempresa))
{
    $_objEmpresa = ProdservController::buscarObjempresaPorIdObjempresa($d_idobjempresa);
    $qtd = $_objEmpresa['qtdLinhas'];
    $_dadosObjEmpresa = $_objEmpresa['dados'];
    if($qtd > 0)
    {
        $_listarUnidadeObjeto = ProdservController::buscarUnidadeObjeto($_dadosObjEmpresa['empresa'], $_dadosObjEmpresa['idprodserv'], 'prodserv');        
        foreach($_listarUnidadeObjeto as $unidadeObjeto)
        {
            $_SESSION['arrpostbuffer']['1']['d']['unidadeobjeto']['idunidadeobjeto'] = $unidadeObjeto['idunidadeobjeto'];
        }
        retarraytabdef('unidadeobjeto');
    }
}

//retirar unidade de negocio verificar se tem formula para a divisao
$d_idplantelobjeto = $_SESSION['arrpostbuffer']['x']['d']['plantelobjeto']['idplantelobjeto'];
if(!empty($d_idplantelobjeto))
{
    $qtd = ProdservController::buscarQtdFormulaPlantelObjeto($d_idplantelobjeto);
    if($qtd > 0){        
        die("Existe uma fórmula para esta unidade. Favor alterar a fórmula para poder retirar está configuração do produto.");
    }
}

//479417 marcar unidade almoxarifado para produtos comprados
$comprado = $_SESSION['arrpostbuffer']['1']['u']['prodserv']['comprado'];
$_idprodserv = $_SESSION['arrpostbuffer']['1']['u']['prodserv']['idprodserv'];
if($comprado == "Y" && !empty($_idprodserv))
{
    $row = ProdservController::buscarUnidadesDisponiveisPorUnidadeObjeto($_idprodserv, 'prodserv', getidempresa('u.idempresa','prodserv'), 'AND u.idtipounidade = 3');
    $qtd = count($row);
   
    if($qtd > 0)
    {
        if(empty($row['idunidadeobjeto']) && !empty($row['idunidade']))
        {
            $_SESSION['arrpostbuffer']['1x']['i']['unidadeobjeto']['idunidade'] = $row['idunidade'];
            $_SESSION['arrpostbuffer']['1x']['i']['unidadeobjeto']['idobjeto'] = $_idprodserv;
            $_SESSION['arrpostbuffer']['1x']['i']['unidadeobjeto']['tipoobjeto'] = 'prodserv';
        }
    }
}

//Validar se foi alterado o qtdi e qtdpd da prodservformulains para versionar, caso tenha alguma alteração
$prodservformulains_qtdi = $_SESSION["arrpostbuffer"]['ifi1']['u']["prodservformulains"]["qtdi"];
$prodservformulains_qtdpd = $_SESSION["arrpostbuffer"]['ifi1']['u']["prodservformulains"]["qtdpd"];
$idprodservformulains = $_SESSION["arrpostbuffer"]['ifi1']['u']["prodservformulains"]["idprodservformulains"];
if(!empty($idprodservformulains) && (!empty($prodservformulains_qtdi) || !empty($prodservformulains_qtdpd)))
{
    $rowProdForIns = ProdservController::buscarProdservFormulaInsPorIdProdservFormulaIns($idprodservformulains);
    if($rowProdForIns['editar'] == 'N')
    {
        $_SESSION["arrpostbuffer"]['ifi1prod']['u']["prodservformula"]["idprodservformula"] = $rowProdForIns['idprodservformula'];
        $_SESSION["arrpostbuffer"]['ifi1prod']['u']["prodservformula"]["versao"] = $rowProdForIns['versao'] + 1;
        $_SESSION["arrpostbuffer"]['ifi1prod']['u']["prodservformula"]["editar"] = 'Y';

        retarraytabdef('prodservformula');
    }
}

$gerandohistorico = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'];
if (!empty($gerandohistorico)) 
{
    $campo = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['campo'];
    $valor = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['valor'];
    $tabela = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['tipoobjeto'];
    $_id = $_SESSION['arrpostbuffer']['h1']['i']['modulohistorico']['idobjeto'];

    $_SESSION['arrpostbuffer']['1']['u']['prodserv']['id' . $tabela] = $_id;

    $arrfind = array("#","'", ";",'"',"$","*",'&');   
    if($campo=='descr' or   $campo=='descrcurta'  or $campo=='descrgenerica'){
        $valor =trim(str_replace($arrfind, "", $valor));
    }

    $_SESSION['arrpostbuffer']['1']['u']['prodserv'][$campo] = $valor;
    if($campo == 'venda' && $valor == 'Y')
    {
        $_SESSION['arrpostbuffer']['1']['u']['prodserv']['nfe'] = $valor;
    }

    montatabdef();
}

if(!empty($_SESSION['arrpostbuffer']['1']['u']['prodserv']['descr']))
{
    $arrfind = array("#", "'", ";",'"',"$","*",'&');
    $strdescr = $_SESSION['arrpostbuffer']['1']['u']['prodserv']['descr'];
    $_SESSION['arrpostbuffer']['1']['u']['prodserv']['descr'] = trim(str_replace($arrfind, "", $strdescr)); 
}

if(!empty($_SESSION['arrpostbuffer']['1']['u']['prodserv']['descrcurta']))
{
    $arrfind = array("#", "'", ";",'"',"$","*",'&');
    $strdescrcurta = $_SESSION['arrpostbuffer']['1']['u']['prodserv']['descrcurta'];
    $_SESSION['arrpostbuffer']['1']['u']['prodserv']['descrcurta'] = trim(str_replace($arrfind, "", $strdescrcurta)); 
}

?>