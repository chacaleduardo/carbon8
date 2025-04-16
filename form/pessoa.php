<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");
require_once("../inc/php/permissao.php");
require_once(__DIR__."/controllers/tipopessoa_controller.php");
require_once(__DIR__."/controllers/arearepresentante_controller.php");
require_once(__DIR__."/controllers/conciliacaofinanceira_controller.php");

if ($_POST) {
    include_once("../inc/php/cbpost.php");
}
/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parâmetros GET que devem ser validados para compor o select principal
 *                pk: indica parâmetro chave para o select inicial
 *                vnulo: indica parâmetros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "pessoa";
$pagvalmodulo = $_GET['_modulo'];
$pagvalcampos = array(
    "idpessoa" => "pk"
);

/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from pessoa where idpessoa = '#pkid'";

//Seta o valor do idtipopessoa para Dependente
if ($_GET['_acao'] == 'i' && $_GET['tipopessoa'] == 'dependente') {
    $_1_u_pessoa_idtipopessoa = 115;
}
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */
include_once("../inc/php/controlevariaveisgetpost.php");

if ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 12) {

    $sqltipopessoa = "select idtipopessoa,tipopessoa
			from tipopessoa 
			where  idtipopessoa in (2,3) 
			and status = 'ATIVO' order by tipopessoa";
} elseif (!empty($_1_u_pessoa_idpessoa)) {
    $sqltipopessoa = "select idtipopessoa,tipopessoa
			from tipopessoa 
			where   status = 'ATIVO'  order by tipopessoa";
} else {
    $sqltipopessoa = "select idtipopessoa,tipopessoa
			from tipopessoa 
			where   status = 'ATIVO' and idtipopessoa != 1  order by tipopessoa";
}

$portifolioArquivo = [];

if ($_1_u_pessoa_idpessoa) {
    $portifolioArquivo = ConciliacaoFinanceiraController::buscarArquivoPorTipoObjetoEIdObjeto($_1_u_pessoa_idpessoa, 'portifolio');
    $arrpessoa = getObjeto("pessoa", $_1_u_pessoa_idpessoa);

    $areasDisponiveisParaVinculo = AreaRepresentanteController::buscarAreasDisponiveisParaVinculo($_1_u_pessoa_idpessoa, cb::idempresa(), true);

    function getProdutos()
    {
        global $_1_u_pessoa_idpessoa;
        $sql = "select idprodserv,descr,codprodserv 
                    from prodserv p
                    where p.tipo = 'PRODUTO' 
                    ".getidempresa('p.idempresa', 'prodserv')."
                    and p.status = 'ATIVO' 
                    and not exists(select 1 from prodservforn pp where pp.idpessoa = ".$_1_u_pessoa_idpessoa." and pp.idprodserv = p.idprodserv)
                    AND p.comprado = 'Y' order by p.descr";

        $res = d::b()->query($sql) or die("getProdutos: Erro: ".mysql_error(d::b())."\n".$sql);

        $arrret = array();
        while ($r = mysqli_fetch_assoc($res)) {
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["idprodserv"]]["descr"] = htmlentities(($r["descr"]));
            $arrret[$r["idprodserv"]]["codprodserv"] = htmlentities(($r["codprodserv"]));
        }
        //	print_r($arrret); die;
        return $arrret;
    }

    //Recupera os produtos a serem selecionados para uma nova Formalização
    $arrProd = getProdutos();

    $jProd = $JSON->encode($arrProd);



    function getClientesx()
    {
        global $_1_u_pessoa_idtipopessoa, $_1_u_pessoa_idpessoa;

        $sqlplanteis1 = "select ifnull(group_concat(idplantel),0) as planteis
        from plantelobjeto
        where tipoobjeto = 'pessoa'
        and idobjeto = ".$_1_u_pessoa_idpessoa;

        $resplanteis1 = d::b()->query($sqlplanteis1) or die("getClientesx: Erro: ".mysql_error(d::b())."\n".$sqlplanteis1);
        $rplanteis1 = mysqli_fetch_assoc($resplanteis1);

        if ($rplanteis1["planteis"] == 0) {
            $and1 = "";
            return 0;
        } else {
            $and1 = "and po.idplantel in (".$rplanteis1["planteis"].")";
        }

        if ($_1_u_pessoa_idtipopessoa == 12 or $_1_u_pessoa_idtipopessoa == 14 or $_1_u_pessoa_idtipopessoa == 16 or empty($_1_u_pessoa_idtipopessoa)) {

            $sql = "select p.idpessoa,p.nome 
                        from pessoa p
                        where not exists(select 1 from pessoacontato c,pessoa pp
                                         where c.idpessoa = p.idpessoa and pp.idtipopessoa in (1,12,14,16) and c.idcontato =pp.idpessoa)
                        and p.idtipopessoa =2
                        and exists (select 1 from plantelobjeto po where po.idobjeto = p.idpessoa and po.tipoobjeto = 'pessoa' ".$and1.")
                        ".share::pessoasPorSessionIdempresa("p.idpessoa")."
                        and p.status in ('ATIVO','PENDENTE')
                        order by p.nome";
        } elseif ($_SESSION["SESSAO"]["IDTIPOPESSOA"] == 12 || $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 14 || $_SESSION["SESSAO"]["IDTIPOPESSOA"] == 16) {
            $sql = "select p.idpessoa,p.nome 
                    from pessoa p
                    where not exists(select 1 from pessoacontato c,pessoa pp
                                         where c.idpessoa = p.idpessoa and pp.idtipopessoa in (1,12,14,16) and c.idcontato =pp.idpessoa)
                    and p.idtipopessoa =2
		            and  exists (select 1 from pessoacontato c where c.idpessoa = p.idpessoa and c.idcontato=".$_SESSION["SESSAO"]["IDPESSOA"].") 					    
                    and p.status in ('ATIVO','PENDENTE')
                    and exists (select 1 from plantelobjeto po where po.idobjeto = p.idpessoa and po.tipoobjeto = 'pessoa' ".$and1.")
                    ".share::pessoasPorSessionIdempresa("p.idpessoa")."
                    order by p.nome";
        } else {
            if ($_1_u_pessoa_idtipopessoa == 9) { // consultor tambem ve cliente
                $_idtipopessoa = 3;
            } else {
                $_idtipopessoa = $_1_u_pessoa_idtipopessoa;
            }
            $sql = "select p.idpessoa,p.nome 
                    from pessoa p
                    where not exists(select 1 from pessoacontato c
                                                            where c.idpessoa = p.idpessoa and c.idcontato =".$_1_u_pessoa_idpessoa.")
                    and p.idtipopessoa in (select idtipopessoa from tipopessoa 
                                                                    where contato REGEXP REPLACE(TRIM(BOTH ',' FROM '".$_idtipopessoa."'),',','|') and status ='ATIVO')
                    and p.status in ('ATIVO','PENDENTE')
                    and exists (select 1 from plantelobjeto po where po.idobjeto = p.idpessoa and po.tipoobjeto = 'pessoa' ".$and1.")
                    ".share::pessoasPorSessionIdempresa("p.idpessoa")."
                    order by p.nome";
        }

        $res = d::b()->query($sql) or die("getClientes: Erro: ".mysql_error(d::b())."\n".$sql);

        $arrret = array();
        while ($r = mysqli_fetch_assoc($res)) {
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["idpessoa"]]["nome"] = $r["nome"];
        }
        return $arrret;
    }

    $arrCli = getClientesx();
    //print_r($arrCli); die;
    $jCli = $JSON->encode($arrCli);

    //exclusivo para representantes
    function getContato()
    {
        //LTM - 25-09-2020 - 374731: Alterado para aparecer as empresas conforme selecionado o plantel
        global $_1_u_pessoa_idtipopessoa, $_1_u_pessoa_idpessoa;

        $sqlplanteis1 = "select ifnull(group_concat(idplantel),0) as planteis
        from plantelobjeto
        where tipoobjeto = 'pessoa'
        and idobjeto = ".$_1_u_pessoa_idpessoa;

        $resplanteis1 = d::b()->query($sqlplanteis1) or die("getClientesx: Erro: ".mysql_error(d::b())."\n".$sqlplanteis1);
        $rplanteis1 = mysqli_fetch_assoc($resplanteis1);

        if ($rplanteis1["planteis"] == 0) {
            $and1 = "";
        } else {
            $and1 = "  and exists (select 1 from plantelobjeto po where po.idobjeto = p.idpessoa and po.tipoobjeto = 'pessoa' and po.idplantel in (".$rplanteis1["planteis"].") )";
        }

        $sql = "select p.idpessoa,concat(e.sigla,'-',p.nome) as nome
                    from pessoa p
                    join empresa e on (e.idempresa=p.idempresa)
                    where not exists(select 1 from pessoacontato c
                                        where c.idcontato = p.idpessoa and c.idpessoa=".$_1_u_pessoa_idpessoa.")
                    and p.idtipopessoa REGEXP REPLACE(TRIM(BOTH ',' FROM (select contato from tipopessoa 
                    where idtipopessoa  = ".$_1_u_pessoa_idtipopessoa." and status ='ATIVO')),',','|')  
                    and p.status in ('ATIVO','PENDENTE')
                    ".$and1."
                    order by p.nome";


        //die($sql);
        $res = d::b()->query($sql) or die("getClientes: Erro: ".mysql_error(d::b())."\n".$sql);

        $arrret = array();
        while ($r = mysqli_fetch_assoc($res)) {
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["idpessoa"]]["nome"] = $r["nome"];
        }
        return $arrret;
    }


    $arrCont = getContato();
    //print_r($arrCont); die;
    $jCont = $JSON->encode($arrCont);
}

function listaAlertas()
{
    global $_1_u_pessoa_idpessoa;
    $s = "select * from (
                        select 
                        d.idimmsgconfdest,
                        CASE
                            WHEN c.titulocurto is  null THEN  c.titulo
                            WHEN c.titulocurto = '' THEN  c.titulo
                            ELSE c.titulocurto
                        END AS titulo,
                        c.idimmsgconf,d.objeto,d.status
                        from immsgconfdest d,immsgconf c
                        where d.objeto = 'pessoa'
                        and d.idimmsgconf = c.idimmsgconf   
                        and d.idobjeto= ".$_1_u_pessoa_idpessoa."      
						".getidempresa('c.idempresa', 'immsgconf')." 						
                 )u order by u.titulo";

    $rts = d::b()->query($s) or die("listaAlertas: ".mysql_error(d::b()));

    echo "<table class='table-hover'><tbody>";
    while ($r = mysqli_fetch_assoc($rts)) {

        if ($r["status"] == 'ATIVO') {
            $opacity = '';
            $cor = 'verde hoververde';
        } else {
            $opacity = 'opacity';
            $cor = 'vermelho hoververmelho ';
        }
        echo "<tr id=".$r["idimmsgconfdest"]." class='".$opacity."'><td>".$r["titulo"]."</td><td><i class='fa fa-check-circle-o $cor pointer' status='".$r["status"]."' idimmsgconfdest='".$r["idimmsgconfdest"]."'  onclick='AlteraStatus(this)'></i></td> <td><a class='fa fa-bars pointer hoverazul' title='Alerta' onclick=\"janelamodal('?_modulo=immsgconf&_acao=u&idimmsgconf=".$r["idimmsgconf"]."')\"></a></td></tr>";
    }
    echo "</tbody></table>";
} //function listaAlertas(){


function listaSetor()
{
    global $_1_u_pessoa_idpessoa;
    $s = "select s.setor,s.idsgsetor
                from sgsetor s ,pessoa p
               where p.idpessoa = ".$_1_u_pessoa_idpessoa."
                 and p.status in ('ATIVO','PENDENTE')
               and p.idtipopessoa=s.idtipopessoa order by s.setor";

    $rts = d::b()->query($s) or die("listaSgArea: ".mysql_error(d::b()));


    while ($r = mysqli_fetch_assoc($rts)) {
        $title = "Editar";
        echo "<tr><td><a title='Setor' target='_blank' href='?_modulo=sgsetor&_acao=u&idsgsetor=".$r["idsgsetor"]."'>".$r["setor"]."</a></td>
                </tr>";
    }
}

if (!empty($_1_u_pessoa_idpessoa)) {
    function getRepresentante()
    {
        global $_1_u_pessoa_idpessoa;

        $lps = arrLpSetoresPessoas($_1_u_pessoa_idpessoa, true);

        if (!empty($lps)) {
            $sqlplanteis = "select ifnull(group_concat(idplantel),0) as planteis
          from plantelobjeto
          where tipoobjeto = 'lp'
          and idobjeto in (".$lps.")
          ";
        } else {
            $sqlplanteis = "select ifnull(group_concat(idplantel),0) as planteis
        from plantelobjeto
        where tipoobjeto = 'pessoa'
        and idobjeto = ".$_1_u_pessoa_idpessoa."
        ";
        }

        $resplanteis = d::b()->query($sqlplanteis) or die("getRepresentante: Erro: ".mysql_error(d::b())."\n".$sqlplanteis);
        $rplanteis = mysqli_fetch_assoc($resplanteis);

        if ($rplanteis["planteis"] == 0) {
            $and = "";
        } else {
            $and = "and po.idplantel in (".$rplanteis["planteis"].")";
        }

        if (!empty($lps)) {
            $exist = "and exists (select 1 from plantelobjeto po where po.idobjeto in ($lps) and po.tipoobjeto = 'lp' ".$and.")";
        } else {
            $exist = "and exists (select 1 from plantelobjeto po where po.idobjeto = p.idpessoa and po.tipoobjeto = 'pessoa' ".$and.")";
        }

        $sql = "select p.idpessoa,p.nome 
                    from pessoa p
                    where  p.idtipopessoa in (1,12)                   
                    and p.status in ('ATIVO','PENDENTE')
                    ".getidempresa('p.idempresa', 'pessoa')."
                    order by p.nome";


        //die($sql);
        $res = d::b()->query($sql) or die("getRepresentante: Erro: ".mysql_error(d::b())."\n".$sql);

        $arrret = array();
        while ($r = mysqli_fetch_assoc($res)) {
            //monta 2 estruturas json para finalidades (loops) diferentes
            $arrret[$r["idpessoa"]]["nome"] = $r["nome"];
        }
        return $arrret;
    }

    $arrRep = getRepresentante();
    //print_r($arrCont); die;
    $jRep = $JSON->encode($arrRep);
}

function getAssinaturasFunc()
{
    global $JSON, $_1_u_pessoa_webmailemail, $_1_u_pessoa_idpessoa;

    $sa = "SELECT 
                w.idwebmailassinatura, w.email, wp.tipo, p.idpessoa, wp.idwebmailassinaturaobjeto, wp.idwebmailassinaturatemplate
            FROM
                webmailassinatura w
                    JOIN
                webmailassinaturaobjeto wp ON (w.idwebmailassinatura = wp.idwebmailassinatura)
                    LEFT JOIN
                pessoa p on (wp.idobjeto = p.idpessoa and wp.tipoobjeto='pessoa' and p.idpessoa = ".$_1_u_pessoa_idpessoa.")
            WHERE
                w.status = 'ATIVO'
                    AND TRIM(w.email) != TRIM('".$_1_u_pessoa_webmailemail."')
                    AND NOT EXISTS (
                        SELECT 1 FROM webmailassinaturaobjeto wp1 WHERE wp1.idobjeto = ".$_1_u_pessoa_idpessoa." AND wp1.tipoobjeto = 'pessoa' AND wp1.idwebmailassinatura = wp.idwebmailassinatura
                    )
                    AND wp.tipo = 'PESSOA'
                    ".getidempresa('w.idempresa', 'funcionario')."
            GROUP BY w.idwebmailassinatura";

    $rsa = d::b()->query($sa) or die("Erro ao consultar assinaturas de e-mail de outros funcionários");
    if (mysqli_num_rows($rsa) == 0) {
        $arrtmp = 0;
    } else {
        $arrtmp = array();
        $i = 0;
        while ($ra = mysqli_fetch_assoc($rsa)) {
            $arrtmp[$i]["idwebmailassinatura"] = $ra["idwebmailassinatura"];
            $arrtmp[$i]["email"] = $ra["email"];
            $arrtmp[$i]["tipo"] = $ra["tipo"];
            $arrtmp[$i]["idpessoa"] = $ra["idpessoa"];
            $arrtmp[$i]["idwebmailassinaturaobjeto"] = $ra["idwebmailassinaturaobjeto"];
            $arrtmp[$i]["idwebmailassinaturatemplate"] = $ra["idwebmailassinaturatemplate"];

            $i++;
        }
        $arrtmp = $JSON->encode($arrtmp);
    }

    return $arrtmp;
}

function getAssinaturasGrupo()
{
    global $JSON, $_1_u_pessoa_webmailemail, $_1_u_pessoa_idpessoa;

    $sa = "SELECT e.idemailvirtualconf, e.email_original as email, w.htmlassinatura as html, w.idwebmailassinatura
            FROM emailvirtualconf e
            JOIN webmailassinatura w ON (e.email_original = w.email) 
            WHERE e.emails_destino LIKE '%{$_1_u_pessoa_webmailemail}%' AND
                NOT EXISTS (
                    SELECT 1 FROM webmailassinaturaobjeto wp WHERE wp.idobjeto = ".$_1_u_pessoa_idpessoa." AND wp.tipoobjeto='pessoa' AND wp.idwebmailassinatura = w.idwebmailassinatura
                )
            AND e.status = 'ATIVO'";

    $rsa = d::b()->query($sa) or die("Erro ao consultar assinaturas de e-mail de outros funcionários");
    if (mysqli_num_rows($rsa) == 0) {
        $arrtmp = 0;
    } else {
        $sqlt = "SELECT idwebmailassinaturatemplate as id, htmltemplate FROM webmailassinaturatemplate WHERE principalempresa = 'Y' AND tipo = 'EMAILVIRTUAL' AND idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"];
        $rest = d::b()->query($sqlt) or die("Falha ao consultar webmailassinturatemplate. SQL: ".$sqlt);

        if (mysql_num_rows($rest) == 0) {
            return 0;
        }

        $rt = mysqli_fetch_assoc($rest);
        $idtemplate = $rt["id"];

        $arrtmp = array();
        $i = 0;
        while ($ra = mysqli_fetch_assoc($rsa)) {
            $arrtmp[$i]["idwebmailassinatura"] = $ra["idwebmailassinatura"];
            $arrtmp[$i]["email"] = $ra["email"];
            $arrtmp[$i]["tipo"] = "EMAILVIRTUAL";
            $arrtmp[$i]["idwebmailassinaturatemplate"] = $idtemplate;

            $i++;
        }
        $arrtmp = $JSON->encode($arrtmp);
    }

    return $arrtmp;
}

function getOutrasAssinaturas()
{
    global $JSON, $_1_u_pessoa_idpessoa, $_1_u_pessoa_webmailemail;

    $sa = "SELECT *
            FROM webmailassinaturatemplate t
            WHERE t.tipo = 'COLABORADOR' AND
                t.status = 'ATIVO' AND
                NOT EXISTS (
                    SELECT 1 FROM webmailassinaturaobjeto wp JOIN webmailassinatura w ON (wp.idwebmailassinatura = w.idwebmailassinatura) 
                    WHERE wp.idobjeto = ".$_1_u_pessoa_idpessoa." AND wp.tipoobjeto='pessoa' AND wp.idwebmailassinaturatemplate = t.idwebmailassinaturatemplate AND w.email = '".$_1_u_pessoa_webmailemail."'
                )";

    $rsa = d::b()->query($sa) or die("Erro ao consultar assinaturas de e-mail de outros funcionários");
    if (mysqli_num_rows($rsa) == 0) {
        $arrtmp = 0;
    } else {

        $arrtmp = array();
        $i = 0;
        while ($ra = mysqli_fetch_assoc($rsa)) {
            $arrtmp[$i]["idwebmailassinaturatemplate"] = $ra["idwebmailassinaturatemplate"];
            $arrtmp[$i]["descricao"] = $ra["descricao"];
            $arrtmp[$i]["htmltemplate"] = $ra["htmltemplate"];
            $arrtmp[$i]["principal"] = $ra["principalempresa"];

            $i++;
        }
        $arrtmp = $JSON->encode($arrtmp);
    }

    return $arrtmp;
}

if (!empty($_1_u_pessoa_idpessoa) and !empty($_1_u_pessoa_webmailemail)) {
    $jAssFunc = getAssinaturasFunc();
    $jAssFunc1 = getAssinaturasGrupo();
    $jAssFunc2 = getOutrasAssinaturas();
}

if ($_1_u_pessoa_idpessoa) {
    $disable = "disabled='disable'";
    $readonly = "readonly";
}
if (!empty($_GET['idtipopessoa']) and empty($_1_u_pessoa_idtipopessoa)) {
    $_1_u_pessoa_idtipopessoa = $_GET['idtipopessoa'];
}

$idempresaLinkMatriz = $_GET["_idempresa"] ? "&_idempresa=".$_GET["_idempresa"] : '';

$_idempresa = $_GET['_idempresa'];

function getPrPref($_idempresa)
{

    $sql = "SELECT CONCAT(IFNULL(p.descrcurta,p.descr),' ',f.rotulo, ' ', IFNULL(f.dose, ' '), ' ', p.conteudo, ' ', ' (', f.volumeformula, ' ', f.un, ')') AS rotulo,
                   f.idprodservformula
	          FROM prodservformula f JOIN prodserv p ON (p.idprodserv = f.idprodserv /*AND p.especial = 'Y'*/ AND p.venda = 'Y' AND p.tipo = 'PRODUTO')
	         WHERE f.status = 'ATIVO' 
               AND p.idempresa = $_idempresa 
          ORDER BY rotulo";
    //die($_SESSION["IDPESSOA"]);
    $res = d::b()->query($sql) or die("getPrPref: Erro: ".mysql_error(d::b())."\n".$sql);

    $arrret = array();
    while ($r = mysqli_fetch_assoc($res)) {
        //monta 2 estruturas json para finalidades (loops) diferentes
        $arrret[$r["idprodservformula"]]["rotulo"] = $r["rotulo"];
    }
    return $arrret;
}

if (!empty($_idempresa)) {
    //Recupera os clientes as serem selecionados
    $arrPr = getPrPref($_idempresa);
    //print_r($arrCli); die;
    $jPr = $JSON->encode($arrPr);
} else {
    $jPr = null;
}

?>

<style>
    .mg0 {
        padding-top: 8px !important;
    }

    .titulo_email {
        position: relative;
        display: block;
        padding: 6px 9px;
        font-size: 11px;
        text-transform: uppercase;
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
                            <? if ($_acao == 'i' and !empty($_GET['idcontato'])) { ?>
                                <input name="novocontatopessoa" type="hidden" value="<?= $_GET['idcontato'] ?>">
                            <? } ?>

                            <? if ($_acao == 'i' and !empty($_GET['idfuncionario'])) { ?>
                                <input name="funcionario" type="hidden" value="<?= $_GET['idfuncionario'] ?>">
                            <? } ?>

                            <label class="idbox"><?= $_1_u_pessoa_idpessoa ?></label>
                            <input name="_1_<?=$_acao?>_pessoa_idpessoa" type="hidden" value="<?= $_1_u_pessoa_idpessoa ?>" readonly='readonly'>
                            <? if ($_1_u_pessoa_idtipopessoa != 9) { ?>
                                <input name="_1_<?=$_acao?>_pessoa_flagobrigatoriocontato" type="hidden" value="Y" readonly='readonly'>
                            <? } ?>
                            <? //LTM 01-09-20: Seta para os novos cadastros o jsonpreferencias para que pois quando faz alteração (update) em alguns módulos, não estava salvando a preferência do usuário pois o campo está NULL 
                            ?>
                            <? if ($_acao == 'i') { ?>
                                <input name="_1_<?=$_acao?>_pessoa_jsonpreferencias" type="hidden" value="{}" readonly='readonly'>
                            <? } ?>
                        </td>
                        <td align="right">Nome:</td>
                        <td><input class="upper" name="_1_<?=$_acao?>_pessoa_nome" type="text" size="48" value="<?= $_1_u_pessoa_nome ?>" vnulo></td>

                        <td align="right">CPF/CNPJ:</td>
                        <td><input name="_1_<?=$_acao?>_pessoa_cpfcnpj" type="text" size="12" id="validacpfcnjp" value="<?= $arrpessoa['cpfcnpj'] ?>" vcpfcnpj></td>
                        <?
                        if ($_1_u_pessoa_idpessoa) {

                            $sqlt = "SELECT * from tipopessoa where contato is  null and idtipopessoa = ".$_1_u_pessoa_idtipopessoa;
                            $rest = d::b()->query($sqlt) or die("A Consulta do tipopessoa : ".mysql_error()."<p>SQL: $sqlt");
                            $rownumt = mysqli_num_rows($rest);
                            if ($rownumt == 1) {
                                ?>
                                <? if ($_1_u_pessoa_idtipopessoa != 115) { ?>
                                    <td align="right">Perfil:</td>
                                    <td>
                                        <select name="_1_<?=$_acao?>_pessoa_perfil" vnulo>
                                            <option value=""></option>
                                            <? fillselect("select 'ADMINISTRATIVO','Administrativo' union select 'COMPRAS','Compras' union select 'TECNICO','Técnico' union select 'PROPRIETARIO','Proprietário' union select 'RT','Responsável Técnico'", $_1_u_pessoa_perfil); ?>
                                        </select>
                                    </td>
                                    <?
                                }
                            }
                        }
                        ?>
                        <td align="right">Tipo:</td>
                        <td>
                            <select <?= $readonly ?> name="_1_<?=$_acao?>_pessoa_idtipopessoa" vnulo>
                                <option value=""></option>
                                <? fillselect($sqltipopessoa, $_1_u_pessoa_idtipopessoa); ?>

                            </select>
                        </td>
                        <td align="right">Status:</td>
                        <td>
                            <select name="_1_<?=$_acao?>_pessoa_status" id="status" vnulo>
                                <?
                                fillselect("select 'PENDENTE','Pendente' union select 'ATIVO','Ativo' union select 'INATIVO','Inativo'  ", $_1_u_pessoa_status);
                                ?>
                            </select>
                            <?
                            //}  
                            ?>
                        </td>
                        <td>
                            <a title="Viagem" class="fa fa-print pull-right fa-lg cinza pointer hoverazul" onclick="janelamodal('report/relviagem.php?acao=u&idpessoa=<?= $_1_u_pessoa_idpessoa ?>')"></a>
                        </td>

                    </tr>

                </table>
            </div>
        </div>
    </div>
</div>

<?
if ($_1_u_pessoa_idpessoa) {
    /*
        $sqlt="SELECT * from tipopessoa where contato is  null and idtipopessoa = ".$_1_u_pessoa_idtipopessoa;
        $rest = d::b()->query($sqlt) or die("A Consulta do tipopessoa : ".mysql_error()."<p>SQL: $sqlt");
        $rownumt= mysqli_num_rows($rest);
 
 */
    if ($rownumt == 1) {
        contato($arrpessoa);
    } elseif ($_1_u_pessoa_idtipopessoa == 2 or $_1_u_pessoa_idtipopessoa == 1 or $_1_u_pessoa_idtipopessoa == 116) {
        cliente($arrpessoa);
    } elseif ($_1_u_pessoa_idtipopessoa == 5  or $_1_u_pessoa_idtipopessoa == 6  or $_1_u_pessoa_idtipopessoa == 7 or $_1_u_pessoa_idtipopessoa == 9 or $_1_u_pessoa_idtipopessoa == 11) {
        fornecedor($arrpessoa);
    } elseif ($_1_u_pessoa_idtipopessoa == 10) {
        secretaria($arrpessoa);
    } elseif ($_1_u_pessoa_idtipopessoa == 12) {
        cliente($arrpessoa);
    }

    $sql = "SELECT e.idendereco,e.idtipoendereco,concat(ifnull(e.logradouro, ''),' ',ifnull(e.endereco, ''),' ',ifnull(e.numero, ''),' ',ifnull(e.complemento, '')) as endereco,e.bairro,c.cidade,e.uf,e.cep
			FROM endereco e left join nfscidadesiaf c on (c.codcidade = e.codcidade)
			WHERE e.status = 'ATIVO'
			and e.idpessoa = ".$_1_u_pessoa_idpessoa." order by e.idtipoendereco";

    $res = d::b()->query($sql) or die("A Consulta falhou : ".mysql_error()."<p>SQL: $sql");
    $rownum = mysqli_num_rows($res);

    ?>
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">Endereço</div>
                    <table class="table table-striped planilha ">
                        <tr>
                            <th>Tipo</th>
                            <th>Endere&ccedil;o</th>
                            <th>Cidade</th>
                            <th>UF</th>
                            <th colspan="2">CEP</th>
                        </tr>
                        <?
                        while ($row = mysqli_fetch_array($res)) {

                            $sqltipo = "SELECT tipoendereco FROM tipoendereco WHERE idtipoendereco = ".$row["idtipoendereco"];
                            $alertend = (empty($row["endereco"]) || empty($row["cidade"]) || empty($row["uf"]) || empty($row["cep"])) ? "style='background-color:red;color: yellow'" : "";
                            $result = d::b()->query($sqltipo) or die("A Consulta falhou : ".mysql_error()."<p>SQL: $sqltipo");
                            $rowtipo = mysqli_fetch_array($result);
                            ?>
                            <tr>
                                <td <?= $alertend ?>><?= $rowtipo["tipoendereco"] ?></td>
                                <td <?= $alertend ?>><?= $row["endereco"] ?></td>
                                <td <?= $alertend ?>><?= $row["cidade"] ?></td>
                                <td <?= $alertend ?>><?= $row["uf"] ?></td>
                                <td <?= $alertend ?>><?= $row["cep"] ?></td>
                                <td>
                                    <a class="fa fa-bars pointer hoverazul" title="Endereço" onclick="janelamodal('?_modulo=endereco&_acao=u&idendereco=<?= $row["idendereco"]; ?>')"></a>
                                </td>
                            </tr>
                        <?
                        }
                        ?>

                        <tr>
                            <td colspan="6">
                                <a class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" title="Cadastro de  Endereço" onclick="janelamodal('?_modulo=endereco&_acao=i&idpessoa=<?= $_1_u_pessoa_idpessoa ?>&_idempresa=<?= $_1_u_pessoa_idempresa ?>')"></a>
                            </td>
                        </tr>
                    </table>

                </div>
            </div>
            <?
            if ($_1_u_pessoa_idtipopessoa != 115) {
                finalidade($arrpessoa);
            }
            $sql0 = "select t.idtag,t.tag,t.descricao, e.sigla from tag t join empresa e on (t.idempresa = e.idempresa) where t.status='ATIVO' and t.idpessoa = ".$_1_u_pessoa_idpessoa."";
            $res10 = d::b()->query($sql0) or die("A Consulta de Conta itens falhou :".mysql_error()."<br>Sql:".$sql0);
            ?>
        </div>
        <div class="col-md-12">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading" data-toggle="collapse" href="#tags">Tags Vinculadas</div>
                    <div class="panel-body">
                        <div class="collapse" id="tags">
                            <table class="table table-striped">
                                <thead>
                                    <th>Tag</th>
                                    <th>Descrição</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                    <? while ($row10 = mysqli_fetch_assoc($res10)) { ?>
                                        <tr>
                                            <td><?= $row10['sigla'] ?>-<?= $row10['tag'] ?></td>
                                            <td><?= $row10['descricao'] ?></td>
                                            <td>
                                                <a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=tag&_acao=u&idtag=<?= $row10['idtag'] ?>');"></a>
                                            </td>
                                        </tr>
                                    <? } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Áreas e Setores de Atuação/LP
                    </div>
                    <div class="panel-body">
                        <? listaSetor() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?
    //LTM (23/04/2021): Insere o CRMV do Veterinário
    if ($_1_u_pessoa_perfil == 'TECNICO' || $_1_u_pessoa_perfil == 'RT' || $_1_u_pessoa_idtipopessoa == 1 || $_1_u_pessoa_idtipopessoa == 3) {
        crmvTecnico($arrpessoa);
    }

    if (($_1_u_pessoa_idtipopessoa == 15 or $_1_u_pessoa_idtipopessoa == 16 or $_1_u_pessoa_idtipopessoa == 12 or $_1_u_pessoa_idtipopessoa == 118) and !empty($_1_u_pessoa_idpessoa)) { ?>
        <div class="row">

            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading" data-toggle="collapse" href="#certificado">Imagem Assinatura</div>
                    <div class="panel-body" id="certificado">
                        <table>
                            <tr>
                                <td>
                                    Imagem Assinatura:
                                </td>
                                <td>
                                    <i class="fa fa-cloud-upload dz-clickable pointer azul" style="display: inline-flex;" id="imagemassinatura"></i>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <? } ?>
    <? if (($_1_u_pessoa_idtipopessoa == 15 or $_1_u_pessoa_idtipopessoa == 16 or $_1_u_pessoa_idtipopessoa == 118) and !empty($_1_u_pessoa_idpessoa) and !empty($_1_u_pessoa_webmailemail)) { ?>
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading" data-toggle="collapse" href="#assinaturas1">Assinatura de E-mail pessoal</div>
                    <div class="panel-body mg0" id="assinaturas1">
                        <ul class="nav">

                            <li class="panel" style="background:#e6e6e6;border: 1px solid #ddd;">
                                <div class="titulo_email">
                                    <a>Conteúdo Assinatura de E-mail</a>
                                </div>
                                <?
                                $sqlc = "SELECT * FROM assinaturaemailcampos WHERE idobjeto = ".$_1_u_pessoa_idpessoa." AND tipoobjeto = 'COLABORADOR'";
                                $resc = d::b()->query($sqlc) or die("Erro ao consultar campos da assinatura de e-mail");
                                $nc = mysqli_num_rows($resc);
                                $rc = mysqli_fetch_assoc($resc);
                                if ($nc == 1) {
                                ?>
                                    <div style="padding: 5px;background:whitesmoke;">
                                        <table class="table">
                                            <tr>
                                                <td colspan="3">
                                                    Nome Assinatura:
                                                    <input type="hidden" name="_ass1_u_assinaturaemailcampos_idassinaturaemailcampos" value="<?= $rc["idassinaturaemailcampos"] ?>">
                                                    <input type="text" name="_ass1_u_assinaturaemailcampos_nome" value="<?= $rc["nome"] ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3">
                                                    Cargo:
                                                    <input type="text" name="_ass1_u_assinaturaemailcampos_cargo" value="<?= $rc["cargo"] ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Telefone:
                                                    <input type="text" name="_ass1_u_assinaturaemailcampos_telefone" value="<?= $rc["telefone"] ?>">
                                                </td>
                                                <td>
                                                    Ramal:
                                                    <input type="text" name="_ass1_u_assinaturaemailcampos_ramal" value="<?= $rc["ramal"] ?>">
                                                </td>
                                                <td>
                                                    Celular:
                                                    <input type="text" name="_ass1_u_assinaturaemailcampos_celular" value="<?= $rc["celular"] ?>">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                <? } else { ?>
                                    <div style="padding: 5px;background:whitesmoke;">
                                        <i class="fa fa-plus-circle verde hovercinza btn-lg pointer" onclick="criarAssinaturaCampos()"></i>
                                    </div>
                                <? } ?>
                            </li>

                        </ul>
                        <? if ($nc == 1) { ?>
                            <hr>
                            <?
                            $sqlw = "SELECT w.idwebmailassinatura as id,w.email,w.htmlassinatura,wp.idwebmailassinaturaobjeto as id2
									FROM 
										webmailassinatura w 
										JOIN webmailassinaturaobjeto wp ON (w.idwebmailassinatura = wp.idwebmailassinatura)
									WHERE
										wp.idobjeto = ".$_1_u_pessoa_idpessoa." AND wp.tipoobjeto='pessoa' AND w.email = '".$_1_u_pessoa_webmailemail."'";

                            $resw = d::b()->query($sqlw) or die("Erro ao consultar Webmailassinatura do colaborador");

                            if (mysqli_num_rows($resw) == 0) { ?>
                                <ul class="nav">
                                    <li class="panel" style="background:#e6e6e6;border: 1px solid #ddd;">
                                        <div class="titulo_email">
                                            <a>Selecione uma Assinatura de E-mail</a>
                                        </div>
                                        <div style="padding: 15px;background:whitesmoke;">
                                            <input id="outrasassinaturas" class="compacto" type="text" cbvalue placeholder="Selecione">
                                        </div>
                                    </li>
                                </ul>

                            <? } else {
                                $rw = mysqli_fetch_assoc($resw);

                                $sqlwp = "SELECT idwebmailassinaturaobjeto as id FROM webmailassinaturaobjeto WHERE idwebmailassinatura = ".$rw["id"];
                                $reswp = d::b()->query($sqlwp) or die("Erro ao consultar webmailassinaturaobjeto");
                                if (mysqli_num_rows($reswp) > 0) {
                                    $virg = "";
                                    $arrt = "";
                                    while ($rwp = mysqli_fetch_assoc($reswp)) {
                                        $arrt .= $virg.$rwp["id"];
                                        $virg = ",";
                                    }
                                    $arrt = "[".$arrt."]";
                                } else {
                                    $arrt = "[".$rw["id2"]."]";
                                }
                            ?>
                                <ul class="nav">

                                    <li class="panel" style="background:#e6e6e6;border: 1px solid #ddd;">
                                        <div class="titulo_email">
                                            <a><?= $rw["email"] ?></a>
                                            <i class="fa fa-trash cinzaclaro hoververmelho pointer" style="float: right;" onclick="deletaidentidade(<?= $rw["id"] ?>,<?= $arrt ?>)"></i>
                                        </div>
                                        <div style="zoom:0.65;-moz-transform: scale(0.65);padding: 20px;background:whitesmoke;">
                                            <?= $rw["htmlassinatura"] ?>
                                        </div>
                                    </li>
                                </ul>
                            <? } ?>
                        <? } ?>
                    </div>

                    <div class="panel-heading" data-toggle="collapse" href="#assinaturas2">Assinaturas de Funcionários Relacionados</div>
                    <div class="panel-body mg0" id="assinaturas2">
                        <input id="assinaturasfunc" class="compacto" type="text" cbvalue placeholder="Selecione">
                        <?
                        $sqlr = "SELECT 
									w1.idwebmailassinatura, w1.email, wp1.tipo, p1.idpessoa, wp1.idwebmailassinaturaobjeto, wp1.idwebmailassinaturatemplate, w1.htmlassinatura
								FROM
									webmailassinatura w1
										JOIN
									webmailassinaturaobjeto wp1 ON (w1.idwebmailassinatura = wp1.idwebmailassinatura)
										LEFT JOIN
									pessoa p1 on (wp1.idobjeto = p1.idpessoa and wp1.tipoobjeto='pessoa' and p1.idpessoa = ".$_1_u_pessoa_idpessoa.")
								WHERE
									w1.status = 'ATIVO'
										AND TRIM(w1.email) != TRIM('".$_1_u_pessoa_webmailemail."')
										".getidempresa('w1.idempresa', 'funcionario')."
										AND p1.idpessoa is not null
										AND wp1.tipo = 'PESSOA'
								ORDER BY w1.email";
                        $resr = d::b()->query($sqlr) or die("Erro ao consultar assinaturas de email");

                        if (mysqli_num_rows($resr) > 0) {
                            $arrass = array(); ?>
                            <table class="table">
                                <? while ($rs = mysqli_fetch_assoc($resr)) {
                                    $arrass[$rs["idwebmailassinaturaobjeto"]] = $rs["htmlassinatura"];
                                ?>
                                    <tr>
                                        <td>
                                            <?= $rs["email"] ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <i class="fa fa-bars hoververmelho cinza pointer" onclick=showWebmailAssinatura(<?= $rs["idwebmailassinaturaobjeto"] ?>)></i>
                                        </td>
                                        <td style="text-align: center;">
                                            <i class="fa fa-trash hoververmelho cinza pointer" onclick=delWebmailAssinatura(<?= $rs["idwebmailassinaturaobjeto"] ?>)></i>
                                        </td>
                                    </tr>
                                <? }
                                $arrass = $JSON->encode($arrass);
                                ?>
                            </table>
                        <? } else {
                            $arrass = 0;
                        } ?>
                    </div>

                    <div class="panel-heading" data-toggle="collapse" href="#assinaturas3">Assinaturas de Grupos de E-mail</div>
                    <div class="panel-body mg0" id="assinaturas3">
                        <input id="assinaturasgrupo" class="compacto" type="text" cbvalue placeholder="Selecione">
                        <?
                        $sqlg = "SELECT wp.idwebmailassinaturaobjeto, w.email, w.htmlassinatura FROM webmailassinaturaobjeto wp JOIN webmailassinatura w ON (wp.idwebmailassinatura = w.idwebmailassinatura) WHERE tipo = 'EMAILVIRTUAL' AND idobjeto = ".$_1_u_pessoa_idpessoa;
                        $resg = d::b()->query($sqlg) or die("Erro ao consultar assinaturas de email");

                        if (mysqli_num_rows($resg) > 0) {
                            $arrass1 = array(); ?>
                            <table class="table">
                                <? while ($rg = mysqli_fetch_assoc($resg)) {
                                    $arrass1[$rg["idwebmailassinaturaobjeto"]] = $rg["htmlassinatura"];
                                    ?>
                                    <tr>
                                        <td>
                                            <?= $rg["email"] ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <i class="fa fa-bars hoververmelho cinza pointer" onclick=showWebmailAssinatura1(<?= $rg["idwebmailassinaturaobjeto"] ?>)></i>
                                        </td>
                                        <td style="text-align: center;">
                                            <i class="fa fa-trash hoververmelho cinza pointer" onclick=delWebmailAssinatura1(<?= $rg["idwebmailassinaturaobjeto"] ?>)></i>
                                        </td>
                                    </tr>
                                <? }
                                $arrass1 = $JSON->encode($arrass1);
                                ?>
                            </table>
                        <? } else {
                            $arrass1 = 0;
                        } ?>
                    </div>

                </div>
            </div>
        </div>
    <? } ?>

    <!--div class="row">
        
        <div class="col-md-12">
        <div class="panel panel-default">  
            <div class="cbupload" id="uploadarquivos" title="Clique ou arraste arquivos para cá" style="width:100%;">
                <i class="fa fa-cloud-upload fonte18"></i>
            </div>
        </div>
        </div>
    </div-->
<?
}

if (!empty($_1_u_pessoa_idpessoa)) { // trocar p/ cada tela a tabela e o id da tabela
    $_idModuloParaAssinatura = $_1_u_pessoa_idpessoa; // trocar p/ cada tela o id da tabela
    require 'viewAssinaturas.php';
}
$tabaud = "pessoa"; //pegar a tabela do criado/alterado em antigo
$idRefDefaultDropzone = "uploadarquivos";
require 'viewCriadoAlterado.php';

function cliente($arrpessoa)
{
    global $_acao, $_1_u_pessoa_idtipopessoa, $_1_u_pessoa_idpessoa, $_modulo;
    $tipoPessoa = TipoPessoaController::buscarPorChavePrimaria($_1_u_pessoa_idtipopessoa);
    $areasVinculadas = AreaRepresentanteController::buscarAreasVinculadasPorIdPessoa($_1_u_pessoa_idpessoa);
    ?>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <table>
                    <tr>
                        <td nowrap align="right">Raz&atilde;o Social:</td>
                        <td colspan="5"><input class="upper size35" name="_1_<?=$_acao?>_pessoa_razaosocial" type="text" id="razaosocial" vnulo value="<?= $arrpessoa['razaosocial'] ?>" vnulo></td>
                    </tr>
                    <tr>
                        <td nowrap align="right">Consumidor Final:</td>
                        <td colspan="5">
                            <select name="_1_<?=$_acao?>_pessoa_indfinal" class="size10">
                                <? fillselect("select 0,'Não'
                                                     union select 1,'Sim'", $arrpessoa['indfinal']); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap align="right">Ins. Municipal:</td>
                        <td colspan="5"><input name="_1_<?=$_acao?>_pessoa_InscricaoMunicipalTomador" type="text" class="size10" maxlength="" value="<?= $arrpessoa['InscricaoMunicipalTomador'] ?>"></td>
                    </tr>
                    <tr>
                        <td nowrap align="right">Ins. Estadual:</td>
                        <td colspan="5"><input name="_1_<?=$_acao?>_pessoa_inscrest" type="text" id="inscestadual" class="size10" maxlength="" value="<?= $arrpessoa['inscrest'] ?>"></td>
                    </tr>
                    <tr>
                        <td align="right">Tel. Empresa:</td>
                        <td colspan="5">
                            <input style="width: 40px;" name="_1_<?=$_acao?>_pessoa_dddfixo" type="text" size="1" value="<?= $arrpessoa['dddfixo'] ?>" maxlength="3">
                            <input style="width: 100px;" name="_1_<?=$_acao?>_pessoa_telfixo" type="text" size="8" value="<?= $arrpessoa['telfixo'] ?>" maxlength="11">
                        </td>
                    </tr>
                    <? if (in_array($arrpessoa['idtipopessoa'], [1, 2, 12, 116])) { ?>
                        <tr>
                            <td nowrap align="right">Email NFS-e:</td>
                            <td colspan="5">
                                <input class="size20" name="_1_<?=$_acao?>_pessoa_email" type="text" value="<?= $arrpessoa['email'] ?>">
                                <?
                                $existepv0 = strpos($arrpessoa['email'], ";");

                                if ($existepv0 === false) {
                                    null;
                                } else {
                                    echo "<br><font color='red'>Atenção: Utilizar  Vírgula para separar Emails!</font></br>";
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap align="right">Email XML NFe:</td>
                            <td colspan="5">
                                <input class="size20" name="_1_<?=$_acao?>_pessoa_emailxmlnfe" type="text" maxlength="" value="<?= $arrpessoa['emailxmlnfe'] ?>" vemail>
                                <?
                                $existepv0 = strpos($arrpessoa['emailxmlnfe'], ";");

                                if ($existepv0 === false) {
                                    null;
                                } else {
                                    echo "<br><font color='red'>Atenção: Utilizar  Vírgula para separar Emails!</font></br>";
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap align="right">Email Material:</td>
                            <td colspan="5">
                                <input class="size20" name="_1_<?=$_acao?>_pessoa_emailmat" type="text" maxlength="" value="<?= $arrpessoa['emailmat'] ?>" vemail>
                                <?
                                $existepv0 = strpos($arrpessoa['emailmat'], ";");

                                if ($existepv0 === false) {
                                    null;
                                } else {
                                    echo "<br><font color='red'>Atenção: Utilizar  Vírgula para separar Emails!</font></br>";
                                }
                                ?>
                            </td>
                        </tr>

                    <? } ?>
                    <tr>
                        <td nowrap align="right">Indicador IE:</td>
                        <td>
                            <select class="size15" name="_1_<?=$_acao?>_pessoa_indiedest">
                                <option value=""></option>
                                <? fillselect("select 1,'[1]-Contribuinte ICMS'
                                                union select 2,'[2]-Contribuinte isento'
                                                union select 9,'[9]-Não Contribuinte'", $arrpessoa['indiedest']); ?>
                            </select>
                        </td>
                        <td align="right" nowrap>Prod. Rural:</td>
                        <td>
                            <select class="size5" name="_1_<?=$_acao?>_pessoa_flgprodrural">
                                <option value=""></option>
                                <? fillselect("select 'S','Sim'
                                                 union select 'N','Não'", $arrpessoa['flgprodrural']); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Contrato de Produto:</td>
                        <?
                        $sqls = "SELECT c.idcontratopessoa,p.*
                                FROM contratopessoa c,contrato p
                                where p.status = 'ATIVO'
                                and p.tipo = 'P'
                                and p.idcontrato = c.idcontrato
                                and c.idpessoa =".$arrpessoa['idpessoa'];

                        $ress = d::b()->query($sqls) or die("A Consulta dos contratos falhou :".mysql_error()."<br>Sql:".$sqls);
                        $qtdrows = mysqli_num_rows($ress);

                        if ($qtdrows > 0) {
                            $y = 9999;
                            while ($rows = mysqli_fetch_array($ress)) {
                                $y = $y + 1;
                                ?>
                                <td align="center">
                                    <a onclick="janelamodal('?_modulo=contrato&_acao=u&idcontrato=<?= $rows['idcontrato']; ?>')"><?= $rows["titulo"] ?><?= $rows["numero"] ?></a>
                                </td>
                                <td align="center">&nbsp;&nbsp;&nbsp;
                                    <a class="fa fa-print pointer hoverazul" title="Folha de Preço" onclick="janelamodal('impcontrato.php?acao=u&idcontrato=<?= $rows['idcontrato']; ?>')">
                                </td>
                                <td align="center">
                                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('contratopessoa',<?= $rows["idcontratopessoa"] ?>)" alt="Excluir"></i>
                                </td>

                            <?
                            } //while($rows = mysqli_fetch_array($ress)){
                        } else { //if($qtdrows> 0){
                            ?>
                            <td colspan="3">
                                <select name="contratopessoa" onchange="contrato(this);">
                                    <option value=""></option>
                                    <? fillselect("SELECT p.idcontrato,concat(p.titulo,' ',p.numero) titulo
												FROM contrato p
												where p.status = 'ATIVO'
												and p.tipo='P'
												and not exists(select 1 from contratopessoa f,contrato c where p.idcontrato = f.idcontrato and f.idpessoa=".$arrpessoa['idpessoa']." and  c.idcontrato = f.idcontrato and c.status='ATIVO') 
												 order by titulo"); ?>
                                </select>
                            </td>

                        <?
                        } //if($qtdrows> 0){
                        ?>
                    </tr>
                    <tr>
                        <td align="right">Contrato de Serviço:</td>

                        <?
                        $sqls = "SELECT c.idcontratopessoa,p.*
                                    FROM contratopessoa c,contrato p
                                    where p.status = 'ATIVO'
                                    and p.tipo = 'S'
                                    and p.idcontrato = c.idcontrato
                                    and c.idpessoa =".$arrpessoa['idpessoa'];

                        $ress = d::b()->query($sqls) or die("A Consulta dos contratos falhou :".mysql_error()."<br>Sql:".$sqls);
                        $qtdrows = mysqli_num_rows($ress);

                        if ($qtdrows > 0) {
                            $y = 9999;
                            while ($rows = mysqli_fetch_array($ress)) {
                                $y = $y + 1;
                                ?>
                                <td align="center">
                                    <a onclick="janelamodal('?_modulo=contrato&_acao=u&idcontrato=<?= $rows['idcontrato']; ?>')"><?= $rows["titulo"] ?><?= $rows["numero"] ?></a>
                                </td>
                                <td align="center">&nbsp;&nbsp;&nbsp;
                                    <a class="fa fa-print pointer hoverazul" title="Folha de Preço" onclick="janelamodal('impcontrato.php?acao=u&idcontrato=<?= $rows['idcontrato']; ?>')">
                                </td>
                                <td align="center">
                                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('contratopessoa',<?= $rows["idcontratopessoa"] ?>)" alt="Excluir"></i>
                                </td>

                            <?
                            } //while($rows = mysqli_fetch_array($ress)){
                        } else { //if($qtdrows> 0){
                            ?>

                            <td colspan="3">
                                <select name="contratopessoa" onchange="contrato(this);">
                                    <option value=""></option>
                                    <? fillselect("SELECT p.idcontrato,concat(p.titulo,' ',p.numero) titulo
                                                    FROM contrato p
                                                    where p.status = 'ATIVO'
                                                    and p.tipo='S'
                                                    and not exists(select 1 from contratopessoa f,contrato c where p.idcontrato = f.idcontrato and f.idpessoa=".$arrpessoa['idpessoa']." and  c.idcontrato = f.idcontrato and c.status='ATIVO') 
                                                    order by titulo"); ?>
                                </select>
                            </td>

                        <?
                        } //if($qtdrows> 0){
                        ?>
                    </tr>
                    <tr>
                        <td align="right">Observação:</td>
                        <td colspan="3">
                            <textarea class="caixa" rows="3" cols="30" name="_1_<?=$_acao?>_pessoa_obs"><?= $arrpessoa['obs'] ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">
                            Fatura Automática:
                        </td>
                        <td>
                            <select class="size5" name="_1_<?=$_acao?>_pessoa_faturaautomatica">
                                <option value=""></option>
                                <? fillselect(array('S' => 'Sim', 'N' => 'Não'), $arrpessoa['faturaautomatica']); ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <div class="panel panel-default">
                <table>
                    <tr style="background: pink;" class="hide">
                        <td nowrap align="right">CC. Email NFS-e:</td>
                        <td colspan="3"><textarea class="caixa" disabled style="width: 100%; height: 35px; font-size: 13px; margin: 0px;" name="_1_<?=$_acao?>_pessoa_emailcopia"><?= $arrpessoa['emailcopia'] ?></textarea>
                            <?
                            $existepv0 = strpos($arrpessoa['emailcopia'], ";");

                            if ($existepv0 === false) {
                                null;
                            } else {
                                echo "<br><font color='red'>Atenção: Utilizar  Vírgula para separar Emails!</font></br>";
                            }
                            ?>
                        </td>
                    </tr>
                    <tr style="background: pink;" class="hide">
                        <td nowrap align="right" nowrap>Email XML NFe:</td>
                        <td colspan="3"><textarea class="caixa" disabled style="width: 100%; height: 35px; font-size: 13px; margin: 0px;" name="_1_<?=$_acao?>_pessoa_emailxmlnfe"><?= $arrpessoa['emailxmlnfe'] ?></textarea>
                            <?
                            $existepv1 = strpos($arrpessoa['emailxmlnfe'], ";");

                            if ($existepv1 === false) {
                                null;
                            } else {
                                echo "<br><font color='red'>Atenção: Utilizar Vírgula para separar Emails!</font></br>";
                            }
                            ?>
                        </td>
                    </tr>
                    <tr style="background: pink;" class="hide">
                        <td nowrap align="right" nowrap>CC. Email XML NFe:</td>
                        <td colspan="3"><textarea class="caixa" disabled style="width: 100%; height: 35px; font-size: 13px; margin: 0px;" name="_1_<?=$_acao?>_pessoa_emailxmlnfecc"><?= $arrpessoa['emailxmlnfecc'] ?></textarea>
                            <?
                            $existepv1 = strpos($arrpessoa['emailxmlnfecc'], ";");

                            if ($existepv1 === false) {
                                null;
                            } else {
                                echo "<br><font color='red'>Atenção: Utilizar Vírgula para separar Emails!</font></br>";
                            }
                            ?>
                        </td>
                    </tr>
                    <tr style="background: pink;" class="hide">
                        <td nowrap align="right" nowrap>Email Material:</td>
                        <td colspan="3"><textarea class="caixa" disabled style="width: 100%; height: 35px; font-size: 13px; margin: 0px;" name="_1_<?=$_acao?>_pessoa_emailmat"><?= $arrpessoa['emailmat'] ?></textarea>
                            <?
                            $existepv1 = strpos($arrpessoa['emailmat'], ";");

                            if ($existepv1 === false) {
                                null;
                            } else {
                                echo "<br><font color='red'>Atenção: Utilizar Vírgula para separar Emails!</font></br>";
                            }
                            ?>
                        </td>
                    </tr>
                    <tr style="background: pink;" class="hide">
                        <td align="right" nowrap>Email Result.:</td>
                        <td style="text-align:top;" colspan="3">
                            <textarea name="_1_<?=$_acao?>_pessoa_emailresult" disabled class="caixa" style="width: 100%; height: 35px; font-size: medium; margin: 0px;" onchange="if(this.value.indexOf(';') != -1){alert('Atenção: Utilizar Vírgula para separar Emails!')}"><?= $arrpessoa['emailresult'] ?></textarea>
                            <?
                            $existepv = strpos($arrpessoa['emailresult'], ";");

                            if ($existepv === false) {
                                null;
                            } else {
                                echo "<br><font color='red'>Atenção: Utilizar Vírgula para separar Emails!</font></br>";
                            }
                            ?>
                        </td>
                    </tr>
                    <tr style="background: pink;" class="hide">
                        <td colspan="4"><span><b>*Esses campos estão desativados, migrar as informações para Contato Empresa.</b></span></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Res. Impresso:</td>
                        <td>
                            <select name="_1_<?=$_acao?>_pessoa_impresultado" vnulo>
                                <option value=""></option>
                                <? fillselect("select 'S','Sim'
                                                     union select 'N','Não'", $arrpessoa['impresultado']); ?>
                            </select>
                        </td>
                        <td align="right" nowrap>Res. Vinc. NF:</td>
                        <td width="35%">
                            <select name="_1_<?=$_acao?>_pessoa_resvincnf" vnulo>
                                <option value=""></option>
                                <? fillselect("select 'S','Sim'
                                                     union select 'N','Não'", $arrpessoa['resvincnf']); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Classificação:</td>
                        <td colspan="3">
                            <select name="_1_<?=$_acao?>_pessoa_classificacao">
                                <option value=""></option>
                                <? fillselect("select 'AVOS','Avós'
                                                     union select 'AVOS INCUBATORIO','Avós Incubatório'
                                                     union select 'FRANGO','Frango'
                                                     union select 'INCUBATORIO','Incubatório'
                                                     union select 'MATRIZ','Matriz'
                                                     union select 'MATRIZ INCUBATORIO','Matriz Incubatório'", $arrpessoa['classificacao']); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Secretaria:</td>
                        <td>
                            <select name="_1_<?=$_acao?>_pessoa_idsecretaria">
                                <option value=""></option>
                                <? fillselect("select idpessoa,nome
                                                from pessoa p
                                                where idtipopessoa = 10
                                                ".share::pessoasPorSessionIdempresa("p.idpessoa")."
                                                and status = 'ATIVO' 
                                                order by nome", $arrpessoa['idsecretaria']); ?>
                            </select>
                        </td>
                        <? if ($arrpessoa['idsecretaria']) { ?>
                            <td><a class="fa fa-bars pointer hoverazul" title="Secretaria" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $arrpessoa['idsecretaria'] ?>')"></i></td>
                        <? } ?>
                    </tr>
                    <tr>
                        <td align="right">Transporte:</td>
                        <td>
                            <select name="_1_<?=$_acao?>_pessoa_idtransportadora">
                                <option value=""></option>
                                <? fillselect("select p.idpessoa, p.nome
                                                from pessoa p
                                                where  p.idtipopessoa = 11
                                                ".share::pessoasPorSessionIdempresa("p.idpessoa")."
                                                and  p.status = 'ATIVO' 
                                                order by  p.nome", $arrpessoa['idtransportadora']); ?>
                            </select>
                        </td>
                        <? if ($arrpessoa['idtransportadora']) { ?>
                            <td><a class="fa fa-bars pointer hoverazul" title="Transportador" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $arrpessoa['idtransportadora'] ?>')"></a></td>
                        <? } ?>
                    </tr>
                    <tr>
                        <td align="right">Preferências:</td>
                        <td>
                            <select name="_1_<?=$_acao?>_pessoa_idpreferencia">
                                <option value=""></option>
                                <? fillselect("SELECT p.idpreferencia, CONCAT(e.sigla, ' - ', p.titulo) as titulo
                                                 FROM preferencia p JOIN empresa e ON  e.idempresa = p.idempresa
                                                WHERE p.idempresa = ".CB::idempresa()." 
                                             ORDER BY p.titulo", $arrpessoa['idpreferencia']); ?>
                            </select>
                        </td>
                        <? if ($arrpessoa['idpreferencia']) { ?>
                            <td><a class="fa fa-bars pointer hoverazul" title="Preferência" onclick="janelamodal('?_modulo=preferencia&_acao=u&idpreferencia=<?= $arrpessoa['idpreferencia'] ?>')"></i></td>
                        <? } ?>
                        <td><a class="fa fa-plus-circle verde pointer hoverazul" title=" Adicionar Preferência" onclick="janelamodal('?_modulo=preferencia&_acao=i&_idempresa=<?= $arrpessoa['idempresa'] ?>')"></i></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-10">
                            Representação
                        </div>
                        <div class="col-md-2">
                            Venda Direta
                            <? if ($arrpessoa['vendadireta'] == "Y") { ?>
                                <i style="padding: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="altvendadireta('N');" alt="Alterar para Não"></i>
                            <? } else { ?>
                                <i style="padding: 0px;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="altvendadireta('Y');" alt="Alterar para Sim"></i>
                            <? } ?>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <?
                    $sql1 = "select o.idpessoaobjeto,e.empresa,o.idobjeto from pessoaobjeto o 
                            left join empresa e on(e.idempresa=o.idobjeto)
                            where o.idpessoa = '".$arrpessoa['idpessoa']."'
                            and o.tipoobjeto = 'empresa' order by e.empresa";

                    $res1 = d::b()->query($sql1) or die("A Consulta de filiais falhou :".mysql_error()."<br>Sql:".$sql1);
                    $qtdf = mysqli_num_rows($res1);
                    $j = 100000;
                    ?>
                    <table class="table table-striped planilha">
                        <?
                        while ($row1 = mysqli_fetch_assoc($res1)) {
                            $j++;
                            ?>
                            <tr>
                                <td>Filial:</td>
                                <td>
                                    <input name="_<?= $j ?>_u_pessoaobjeto_idpessoaobjeto" type="hidden" value="<?= $row1["idpessoaobjeto"] ?>">
                                    <select name="_<?= $j ?>_u_pessoaobjeto_idobjeto">
                                        <option value=""></option>
                                        <? fillselect("SELECT 
                                                            e.idempresa, e.nomefantasia
                                                        FROM
                                                            matrizconf m
                                                                JOIN
                                                            empresa e ON (e.idempresa = m.idempresa
                                                                AND e.status = 'ATIVO'
                                                                AND e.filial = 'Y')
                                                        WHERE
                                                            m.idmatriz = ".$arrpessoa['idempresa']."
                                                        ORDER BY e.nomefantasia", $row1['idobjeto']); ?>
                                    </select>
                                </td>
                                <td align="center">
                                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('pessoaobjeto',<?= $row1['idpessoaobjeto'] ?>)" title="Desvincular Filial"></i>
                                </td>
                            </tr>
                        <?
                        }
                        if ($qtdf < 1) {
                            ?>
                            <tr>
                                <td colspan="5">
                                    <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novopessoaobj('pessoaobjeto','empresa')" alt="Vincular a uma Filial"></i>
                                    <span><b>RELACIONAR A UMA FILIAL!</b></span>
                                </td>
                            </tr>
                        <?
                        }
                        ?>

                    </table>

                    <?
                    $sqlgestor = "select p.nome from plantelobjeto po 
                                join divisaoplantel dp on(dp.idplantel=po.idplantel)
                                join divisao d on (d.iddivisao = dp.iddivisao and d.status='ATIVO') 
                                join pessoa p on (p.idpessoa = d.idpessoa) 
                                where  po.tipoobjeto = 'pessoa' 
                                ".getidempresa('d.idempresa', 'pessoa')." 
                                and po.idobjeto =".$arrpessoa['idpessoa']." order by p.nome ";

                    $resgestor = d::b()->query($sqlgestor) or die("A Consulta de gestores da divisão falhou : ".mysql_error(d::b())."<p>SQL:".$sqlgestor);
                    $qtdgestor = mysqli_num_rows($resgestor);
                    if ($qtdgestor > 0) {
                        ?>
                        <table class="table table-striped">
                            <?
                            $gestor = 'Gestores:';
                            $w = 0;
                            while ($rowgestor = mysql_fetch_assoc($resgestor)) { ?>
                                <? if ($w == 0) { ?><tr>
                                        <th align="right"><? echo $gestor; ?> </th>
                                    </tr> <? } ?>
                                <tr>
                                    <td><?= $rowgestor["nome"] ?></td>
                                </tr>
                                <? $w++;
                            }
                        }

                        ?>
                        </table>
                        <hr>
                        <?

                        if ($arrpessoa["idtipopessoa"] == 12) {
                            if ($_GET['_acao'] == 'u') {
                                $sqlContatorRepresentacao = "SELECT p.nome, pc.idcontato, pc.idpessoa, pc.idpessoacontato, pc.criadoem
                                                            FROM pessoacontato pc
                                                            LEFT JOIN pessoa p ON (p.idpessoa = pc.idcontato)
                                                            WHERE pc.idpessoa = ".$arrpessoa['idpessoa']."
                                                            AND p.status = 'ATIVO'
                                                            ORDER BY nome asc";
                                $resContatoRepresentacao = d::b()->query($sqlContatorRepresentacao) or die("A Consulta de Contatos da Representação falhou : ".mysql_error(d::b())."<p>SQL:".$sqlContatorRepresentacao);
                                $qtdContatoRepresentacao = mysqli_num_rows($resContatoRepresentacao);

                                if ($qtdContatoRepresentacao > 0) {
                                    ?>
                                    <table class="table  table-hover">
                                        <?
                                        $cr = 'Contatos Representação:';
                                        $c = 0;
                                        while ($rowcr = mysql_fetch_assoc($resContatoRepresentacao)) { ?>
                                            <? if ($c == 0) { ?><tr>
                                                    <th colspan="5"><? echo $cr; ?> </th>
                                                </tr> <? } ?>
                                            <tr>
                                                <td><?= $rowcr["nome"] ?></td>
                                                <td title="Data em que o Contato Representação foi vinculado a Representação">Vinculado em <?= dma($rowcr["criadoem"]) ?></td>
                                                <td title="Ir para pagina de cadastro de <?= $rowcr["nome"] ?>"><a style="margin-top: 3px;" class="fa fa-bars pointer hoverazul pull-right" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $rowcr['idcontato'] ?>')"></a></td>
                                                <td title="Desvincular <?= $rowcr["nome"] ?> da representação" style="width: 3%;"><i class="fa fa-trash fa-1x cinzaclaro hoververmelho  pointer" onclick="excluir('pessoacontato',<?= $rowcr['idpessoacontato'] ?>)" alt="Excluir"></i></td>
                                            </tr>
                                            <? $c++;
                                        }
                                    }
                                }
                                ?>
                                    </table>
                                    <hr>
                                    <table>
                                        <tr>
                                            <th colspan="5"><strong>Adicionar Novo Contato Representação</strong></th>
                                        </tr>
                                        <tr>
                                            <td>Nome:</td>
                                            <td colspan="3">
                                                <input type="text" name="idpessoacontatorepresentacao" cbvalue="idpessoacontatorepresentacao" value="" style="width: 40em;">
                                            </td>
                                        </tr>
                                    </table>
                                <? } ?>

                                <?
                                if ($arrpessoa["idtipopessoa"] != 12) {
                                    if ($arrpessoa['vendadireta'] == "Y") {
                                        $sqlplantel = "select 
                                                        u.idplantel
                                                        ,u.plantel
                                                        ,f.idpessoa,f.nome
                                                    from plantel u 
                                                        join plantelobjeto p on( u.idplantel = p.idplantel and p.idobjeto =  ".$arrpessoa['idpessoa']." and p.tipoobjeto = 'pessoa')
                                                                join divisaoplantel dp on(dp.idplantel=u.idplantel)
                                                        join divisao d on(d.iddivisao=dp.iddivisao and d.status='ATIVO')
                                                        join pessoa f on(d.idpessoa=f.idpessoa and f.status='ATIVO')
                                                    where u.status='ATIVO' ".getidempresa('u.idempresa', 'plantel')." order by f.nome ";
                                        $resplantel = d::b()->query($sqlplantel) or die("A Consulta de reponsaveis por plantel  falhou : ".mysql_error(d::b())."<p>SQL:".$sqlplantel);
                                        $qtdresp = mysqli_num_rows($resplantel);

                                ?> <table>

                                            <?
                                            if ($qtdresp < 1) {
                                            ?>
                                                <tr>
                                                    <td align="right">Responsável:</td>
                                                    <td>
                                                        Não possui Gestor ou espécie relacionada
                                                    </td>
                                                </tr>
                                            <?
                                            }

                                            while ($rowpl = mysqli_fetch_assoc($resplantel)) { ?>
                                                <tr id="localiza">
                                                    <td align="right">Responsável:</td>
                                                    <td>
                                                        <?= $rowpl['plantel'] ?> - <?= $rowpl['nome'] ?>
                                                    </td>
                                                </tr>
                                            <?
                                            }
                                        } else {
                                            ?>
                                            <table>

                                                <?
                                                //Representante
                                                $sql = "select c.idpessoacontato
                                                            ,c.idcontato
                                                            ,nome
                                                            ,usuario
                                                            ,concat(dddfixo,'-',telfixo) as tel1
                                                            ,concat(dddcel,'-',telcel) as tel2
                                                            , email
                                                            ,c.participacaoserv
                                                            ,c.participacaoprod
                                                            ,p.idtipopessoa
                                                            from pessoa p
                                                            ,pessoacontato c
                                                            where p.status IN ('ATIVO','PENDENTE')
                                                            and p.idtipopessoa in(12,1)
                                                            and  p.idpessoa = c.idcontato
                                                            and c.idpessoa = ".$arrpessoa['idpessoa']." order by nome";
                                                $res = d::b()->query($sql) or die("A Consulta falhou :".mysql_error()."<br>Sql:".$sql);
                                                $l = 0;
                                                while ($row = mysqli_fetch_array($res)) {
                                                    $l = $l + 1;
                                                    ?>
                                                    <tr>
                                                        <td align="right">Responsável - <?= $l ?>:</td>

                                                        <td style="padding-left: 10px; padding-right: 10px;" nowrap><?= $row["nome"] ?></td>
                                                        <td style="padding-left: 10px; padding-right: 10px;" nowrap>Serv.
                                                            <input name="_com<?= $l ?>_u_pessoacontato_idpessoacontato" type="hidden" size="5" value="<?= $row['idpessoacontato'] ?>">
                                                            <input name="_com<?= $l ?>_u_pessoacontato_participacaoserv" type="text" class="size4" value="<?= $row['participacaoserv'] ?>" vdecimal> %
                                                        </td>
                                                        <td style="padding-left: 10px; padding-right: 10px;" nowrap>Prod .<?= $row["participacaoprod"] ?>
                                                            <input name="_com<?= $l ?>_u_pessoacontato_participacaoprod" type="text" class="size4" value="<?= $row['participacaoprod'] ?>" vdecimal>
                                                            %
                                                        </td>
                                                        <td>
                                                            <? if ($row['idtipopessoa'] == 12) { ?>
                                                                <a class="fa fa-bars pointer hoverazul" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $row["idcontato"] ?>')"></a>
                                                            <? } else { ?>
                                                                <a class="fa fa-bars pointer hoverazul" onclick="janelamodal('?_modulo=funcionario&_acao=u&idpessoa=<?= $row["idcontato"] ?>')"></a>
                                                            <? } ?>
                                                        </td>
                                                        <td>
                                                            <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('pessoacontato',<?= $row["idpessoacontato"] ?>)" alt="Excluir"></i>
                                                        </td>
                                                    </tr>

                                                <?
                                                } //while($row = mysqli_fetch_array($res)){
                                                ?>
                                                <tr>
                                                    <td align="right">Responsável:</td>
                                                    <td colspan="6">
                                                        <input type="text" name="pessoarepresentante" cbvalue="pessoarepresentante" value="" style="width: 30em;">
                                                    </td>

                                                </tr>
                                            <?
                                        }
                                    } //if($arrpessoa["idtipopessoa"]!=12){
                                        ?>
                            </table>
                </div>
            </div>
        </div>

        <?
        $sqlplantel = "SELECT e.sigla, po.idplantel, plantel, idplantelobjeto
                                FROM plantelobjeto po JOIN plantel p ON p.idplantel = po.idplantel
                                join empresa e on e.idempresa = p.idempresa
                                WHERE idobjeto = ".$arrpessoa['idpessoa']." 
                                AND tipoobjeto = 'pessoa'";

        $resplantel = d::b()->query($sqlplantel) or die("A Consulta de planteis da empresa falhou : ".mysql_error(d::b())."<p>SQL:".$sqlplantel);
        $qtdplantel = mysqli_num_rows($resplantel);
        $rowplantel = mysqli_fetch_assoc($resplantel);
        $idplantel = $rowplantel['idplantel'];

        //if($qtdplantel>0){
        ?>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Divisão de Negócio</div>
                <div class="panel-body">
                    <table>
                        <tr>
                            <td valign=top>
                                <? if (empty($rowplantel['idplantelobjeto'])) { ?>
                                    <select name="idplantelobjeto" class="size15" onchange="inserirPlantelEmpresa(this, '<?= $rowplantel['idplantelobjeto'] ?>');" vnulo>
                                        <option value=""></option>
                                        <? fillselect("SELECT idplantel, plantel 
                                                                    FROM plantel
                                                                WHERE status = 'ATIVO' 
                                                                ".getidempresa('idempresa', 'plantel')."
                                                                ORDER BY plantel;", $idplantel); ?>
                                    </select>
                                <? } else {
                                    echo  $rowplantel['sigla']." - ".$rowplantel['plantel'];
                                ?>
                                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="deletarPlantelEmpresa(<?= $rowplantel['idplantelobjeto'] ?>);" title="Excluir!"></i>
                                <? } ?>
                            </td>
                            <td>
                                <? if (!empty($idplantel)) { ?>
                                    <table>
                                        <?
                                        $sqlp = "select 
                                                        p.idtipoplantelpessoa,t.idtipoplantel,t.tipoplantel,p.qtd, p.status
                                                    from 
                                                        tipoplantel t 
                                                    left join 
                                                        tipoplantelpessoa p on(p.idtipoplantel = t.idtipoplantel and p.idpessoa = ".$arrpessoa['idpessoa']." )
                                                    where 
                                                    idplantel = ".$idplantel."
                                                    order by t.tipoplantel";
                                        $resp = d::b()->query($sqlp) or die("A Consulta de tipoplantel falhou : ".mysql_error(d::b())."<p>SQL:".$sqlp);
                                        $i = 5000;
                                        while ($rowp = mysqli_fetch_assoc($resp)) {
                                            $i = $i + 1;
                                            if (empty($rowp['idtipoplantelpessoa'])) {
                                                $acpl = 'i';
                                            } else {
                                                $acpl = 'u';
                                            }
                                        ?>
                                            <tr>
                                                <td style="width: 60px;text-align: right;">
                                                    <?= $rowp['tipoplantel'] ?>
                                                </td>
                                                <td style="width: 150px;padding-right: 20px;">
                                                    <input name="_<?= $i ?>_<?= $acpl ?>_tipoplantelpessoa_idtipoplantelpessoa" type="hidden" size="8" value="<?= $rowp['idtipoplantelpessoa'] ?>">
                                                    <input name="_<?= $i ?>_<?= $acpl ?>_tipoplantelpessoa_idpessoa" type="hidden" size="8" value="<?= $arrpessoa['idpessoa'] ?>">
                                                    <input name="_<?= $i ?>_<?= $acpl ?>_tipoplantelpessoa_idtipoplantel" type="hidden" size="8" value="<?= $rowp['idtipoplantel'] ?>">
                                                    <input name="_<?= $i ?>_<?= $acpl ?>_tipoplantelpessoa_qtd" placeholder="Qtd." type="text" size="8" value="<?= $rowp['qtd'] ?>">
                                                </td>
                                            </tr>
                                        <? } ?>
                                    </table>
                                <? } ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <? if (getModsUsr("MODULOS")['arearepresentante']["permissao"] && $tipoPessoa && !in_array(mb_strtolower($tipoPessoa['tipopessoa']), ['colaborador', 'representante', 'representação'])) { ?>
                <div class="panel panel-default">
                    <div class="panel-heading">Área</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-12 form-group">
                                <label for="areas">Área</label>
                                <input id="areas" type="text" class="form-control">
                            </div>
                        </div>
                        <? if ($areasVinculadas) { ?>
                            <div class="row">
                                <div class="col-xs-12 col-md-6">
                                    <table class="table-hover w-100">
                                        <thead>
                                            <tr>
                                                <th>Área</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <? foreach ($areasVinculadas as $area) { ?>
                                                <tr>
                                                    <td><?= $area['area'] ?></td>
                                                    <td>
                                                        <span class="fa fa-trash pointer text-danger hoververmelho mr-3" onclick="removerVinculoArea(<?= $area['idpessoaobjeto'] ?>)"></span>
                                                        <a target="_blank" href="?_modulo=arearepresentante&_acao=u&idarearepresentante=<?= $area['idarearepresentante'] ?>" class="fa fa-bars pointer"></a>
                                                    </td>
                                                </tr>
                                            <? } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <? } ?>
                    </div>
                </div>
            <? } ?>
        </div>
    </div>
    <?
    //inadimplencia
    $sql = "select dma(i.vencimento) as dmavencimento,
                                i.* from inadimplencia i
                                where i.status = 'ATIVO'
                        and  i.idpessoa = ".$arrpessoa['idpessoa']." order by i.vencimento";
    $res = d::b()->query($sql) or die("A Consulta de inadimplencia falhou : ".mysql_error()."<p>SQL: $sql");
    $rownum1 = mysqli_num_rows($res);
    if ($rownum1 > 0) {
        ?>
        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading">INADIMPLÊNCIA</div>
                    <table class="table table-striped planilha">
                        <tr class="header">
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                        </tr>
                        <?
                        while ($row = mysqli_fetch_array($res)) {
                            if ($row['status'] == "ATIVO") {
                                $cor = "red";
                            } else {
                                $cor = "";
                            }
                        ?>
                            <tr style="background-color: <?= $cor ?>" class="res" onclick="janelamodal('inadimplencia.php?acao=u&idinadimplencia=<?= $row["idinadimplencia"] ?>',600,950);" onMouseOver='hl(this,true);' onMouseOut='hl(this,false);'>
                                <td nowrap class="respreto"><?= $row["valor"] ?></td>
                                <td nowrap class="respreto"><?= $row["dmavencimento"] ?></td>
                                <td nowrap class="respreto"><?= $row["status"] ?></td>
                            </tr>
                        <?
                        } //hile($row = mysqli_fetch_array($res)){
                        ?>
                    </table>
                </div>
            </div>
        </div>
    <?
    } //if($rownum1>0){ 	
    ?>
    <div class="row">
        <?
        if ($arrpessoa["idtipopessoa"] != 12 and $arrpessoa["idtipopessoa"] != 1) {
            pessoacontato($arrpessoa); // se for uma empresa
        } elseif ($arrpessoa["idtipopessoa"] == 12 or $arrpessoa["idtipopessoa"] == 1) {
            listapessoascontato($arrpessoa); //se for um representante
        }
        if ($arrpessoa["idtipopessoa"] == 2) {
            ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading"><a class="prodservprogramacao pointer">Programação de Venda</a></div>
                </div>
            </div>

            <?

            $sql = "select 
                            CONCAT(ifnull(p.descrcurta,p.descr),' ',f.rotulo,
                                                    ' ',
                                                    IFNULL(f.dose, ' '),
                                                    ' ',
                                                    p.conteudo,
                                                    ' ',
                                                    ' (',
                                                    f.volumeformula,
                                                    ' ',
                                                    f.un,
                                                    ')') AS rotulo,pf.idprodservformulapref,f.idprodserv,f.idprodservformula,pf.idprodserv as idprodservlacre
                        from prodservformulapref pf 
                            join prodservformula f on(pf.idprodservformula=f.idprodservformula)
                            join prodserv p on(p.idprodserv =f.idprodserv)
                        where pf.idpessoa = ".$arrpessoa["idpessoa"];

            $res = d::b()->query($sql) or die("A Consulta da formula falhou :".mysql_error()."<br>Sql:".$sql);
            //die($sql);
            $rownum1 = mysqli_num_rows($res);
            //if($rownum1>0){
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading" data-toggle="collapse" href="#lacreinfo">Produtos - Cor do Lacre</div>
                    <div class="panel-body collapse" id="lacreinfo">
                        <table class="table table-striped planilha ">
                            <tr>
                                <th>Produto</th>
                                <th>Lacre</th>
                                <th></th>
                                <th></th>
                            </tr>
                            <?
                            while ($row = mysqli_fetch_assoc($res)) {
                                ?>
                                <tr class="res">
                                    <td nowrap><?= $row["rotulo"] ?></td>
                                    <td nowrap>

                                        <select name="prodservformulapref_idprodserv" onchange="atualizaformula(<?= $row['idprodservformulapref'] ?>,this)">
                                            <option value="">Selecione um lacre</option>
                                            <? fillselect("SELECT 
                                                p.idprodserv, p.descr
                                            FROM
                                                prodserv p
                                            WHERE
                                                p.status = 'ATIVO'
                                                    AND p.tipo = 'PRODUTO'
                                                    AND p.comprado = 'Y'
                                                    AND p.idtipoprodserv = 1173
                                                    AND p.idempresa=".$arrpessoa["idempresa"]."
                                                    AND p.descr LIKE ('%SELO%')
                                                    AND p.descr LIKE ('%ALUMINIO%')
                                            ORDER BY p.descr", $row["idprodservlacre"]); ?>
                                        </select>
                                    </td>
                                    <td><a class="fa fa-bars pointer hoverazul" title="produto" onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $row['idprodserv'] ?>')"></a></td>
                                    <td><a class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="retirarformula(<?= $row['idprodservformulapref'] ?>)" Title="Retirar formula"></a></td>
                                </tr>
                            <?
                            } //while($row = mysqli_fetch_array($res)){
                            ?>
                            <tr>
                                <td>
                                    <input type="text" name="preferencia_idprodservformula" cbvalue="" value="" style="width: 40em;" placeholder="Selecione para adicionar um produto">
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <?
        }
        ?>
    </div>
<?
} // function cliente($arrpessoa){

//empresas do contato
function listapessoascontato($arrpessoa)
{
    $sql = "select 
      p.idpessoa,
      p.status,
      c.idpessoacontato,
      c.emailresultado,
      c.idcontato,
      c.participacaoprod,
      c.participacaoserv,
      c.viagem
      ,nome
      ,p.usuario
      ,concat(dddfixo,'-',telfixo) as tel1
      ,concat(dddcel,'-',telcel) as tel2
      , email
      from pessoa p
      ,pessoacontato c
      where p.status IN ('ATIVO', 'PENDENTE')
      and p.idtipopessoa = 2
      and  p.idpessoa = c.idpessoa			
      and c.idcontato = ".$arrpessoa['idpessoa']." order by nome";
    $res = d::b()->query($sql) or die("A Consulta falhou :".mysql_error()."<br>Sql:".$sql);

    $rownum1 = mysqli_num_rows($res);
?>

    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" href="#clienteInfo">(<?= $rownum1 ?>)-Clientes para <?= $arrpessoa['razaosocial'] ?></div>

            <?
            if ($rownum1 > 0) {
            ?>
                <table class="table table-striped planilha collapse" id="clienteInfo">
                    <tr>
                        <th>Cliente</th>
                        <th>Telefone 1</th>

                        <? if ($arrpessoa["idtipopessoa"] == 12) { ?>
                            <th>Participação Serv.%</th>
                            <th>Participação Prod.%</th>
                        <? } ?>
                        <th></th>
                        <th></th>
                    </tr>
                    <?
                    $y = 888;
                    while ($row = mysqli_fetch_assoc($res)) {
                        $_acao = 'u';
                        $y = $y + 1;
                    ?>
                        <tr class="res">

                            <td nowrap><?= $row["nome"] ?>
                                <? if ($row['status'] == 'PENDENTE') { ?><i title="Cadastro Pendente" class="fa fa-exclamation-triangle laranja btn-lg pointer"></i><? } ?>
                            </td>
                            <td nowrap><?= $row["tel1"] ?></td>

                            <? if ($arrpessoa["idtipopessoa"] == 12) { ?>
                                <td>
                                    <input name="_<?= $y ?>_<?=$_acao?>_pessoacontato_idpessoacontato" type="hidden" size="5" value="<?= $row['idpessoacontato'] ?>">
                                    <input name="_<?= $y ?>_<?=$_acao?>_pessoacontato_participacaoserv" type="text" size="5" value="<?= $row['participacaoserv'] ?>" vdecimal>
                                </td>
                                <td><input name="_<?= $y ?>_<?=$_acao?>_pessoacontato_participacaoprod" type="text" size="5" value="<?= $row['participacaoprod'] ?>" vdecimal></td>
                            <? } ?>
                            <td><a class="fa fa-bars pointer hoverazul" title="Cliente" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $row["idpessoa"] ?>')"></a></td>
                            <td>
                                <a class="fa fa-trash  cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluicontato(<?= $row["idpessoacontato"] ?>)" title="Excluir"></a>
                            </td>
                        </tr>
                    <?
                    } //while($row = mysqli_fetch_array($res)){
                    ?>
                </table>

            <?
            } //if($rownum1>0){
            ?>
            <table>
                <tr>
                    <td>Nome:</td>
                    <td colspan="3">
                        <input type="text" name="pessoacontatoidpessoa" cbvalue="pessoacontatoidpessoa" value="" style="width: 40em;" autocomplete="off">
                    </td>
                </tr>
                <tr>
                    <td>
                        <a class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" title="Nova pesssoa" onclick="janelamodal('?_modulo=pessoa&amp;_acao=i')"></a>
                    </td>
                </tr>
            </table>
        </div>

    </div>

<?
} //function listapessoascontato($arrpessoa){

// lista os contatos da pessoa
function pessoacontato($arrpessoa)
{
    global $idempresaLinkMatriz;
    $sql = "select 
    c.idpessoacontato,
	c.receberes,
	c.receberestodos,
    c.emailresultado,
    c.idcontato,
    c.viagem,
    c.emailnfsecc,
    c.emailxmlnfe,
    c.emailxmlnfecc,
    c.emailmaterial
    ,nome
    ,p.usuario
    ,concat(dddfixo,'-',telfixo) as tel1
    ,concat(dddcel,'-',telcel) as tel2
    , email
    , c.somenteoficial
    from pessoa p
    ,pessoacontato c
    where p.status='ATIVO'
    and  p.idpessoa = c.idcontato			
    and c.idpessoa = ".$arrpessoa['idpessoa']." order by nome";
    $res = d::b()->query($sql) or die("A Consulta falhou :".mysql_error()."<br>Sql:".$sql);
    d::b()->query($sql) or die("A Consulta falhou : ".mysql_error()."<p>SQL: $sql");
    $rownum1 = mysqli_num_rows($res);
?>

    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">(<?= $rownum1 ?>)-Contatos para <?= $arrpessoa['razaosocial'] ?></div>
            <div class="panel-body" id="contatoInfo">
                <?

                if ($rownum1 > 0) {
                    if (in_array($arrpessoa['idtipopessoa'], [2, 3])) {
                        $colspan = 6;
                    } else {
                        $colspan = 5;
                    }
                ?>
                    <table class="table table-striped planilha">
                        <tr align="Center">
                            <td style="background-color: #dad7d7;border-right: 1px solid #b9b9b9;" colspan="4"></td>
                            <td style="background-color: #dad7d7;border-right: 1px solid #b9b9b9;" colspan="3"><B>NOTA FISCAL</B></td>
                            <td style="background-color: #dad7d7;" colspan="<?= $colspan ?>"><B>RESULTADO</B></td>
                        </tr>
                        <tr align="Center">
                            <th>Contato</th>
                            <th>Usuário</th>
                            <th>Telefone 1</th>
                            <th>Viagem</th>
                            <th>Material</th>

                            <th>NFSe</th>
                            <th>XML</th>
                            <th>Positivo PDF</th>
                            <th>Positivo LINK</th>
                            <th>Todos PDF</th>
                            <th>Todos LINK</th>
                            <? if (in_array($arrpessoa['idtipopessoa'], [2, 3])) { ?>
                                <th>Somente Oficial</th>
                            <? } ?>
                            <th></th>
                        </tr>
                        <?
                        while ($row = mysqli_fetch_assoc($res)) {
                        ?>
                            <tr class="res">
                                <td nowrap><a title="<?= $row["email"] ?>" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $row["idcontato"] ?><?= $idempresaLinkMatriz ?>')"><?= $row["nome"] ?></a></td>
                                <td nowrap><?= $row["usuario"] ?></td>
                                <td nowrap><?= $row["tel1"] ?></td>
                                <td align="center">
                                    <?
                                    if ($row["viagem"] == "Y") {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                    ?>
                                    <input title="Recebe email resultado" type="checkbox" <?= $checked ?> name="namereceberes" onclick="altviagem(<?= $row["idpessoacontato"] ?>,'<?= $vchecked ?>');">
                                </td>
                                <td align="center">
                                    <?
                                    if (!empty($row["email"])) {
                                        if ($row["emailmaterial"] == "Y" and !empty($row["email"])) {
                                            $checked = 'checked';
                                            $vchecked = 'N';
                                        } elseif (!empty($row["email"])) {
                                            $checked = '';
                                            $vchecked = 'Y';
                                        } ?>
                                        <input title="Recebe email Material" type="checkbox" <?= $checked ?> onclick="altpessoacontatoemails(<?= $row["idpessoacontato"] ?>,'emailmaterial','<?= $vchecked ?>')">
                                    <? } ?>
                                </td>
                                <td align="center">
                                    <?
                                    if (!empty($row["email"])) {
                                        if ($row["emailnfsecc"] == "Y" and !empty($row["email"])) {
                                            $checked = 'checked';
                                            $vchecked = 'N';
                                        } elseif (!empty($row["email"])) {
                                            $checked = '';
                                            $vchecked = 'Y';
                                        } ?>
                                        <input title="Recebe email CC NFS-e" type="checkbox" <?= $checked ?> onclick="altpessoacontatoemails(<?= $row["idpessoacontato"] ?>,'emailnfsecc','<?= $vchecked ?>')">
                                    <? } ?>
                                </td>
                                <td align="center">
                                    <?
                                    if (!empty($row["email"])) {
                                        if ($row["emailxmlnfe"] == "Y" and !empty($row["email"])) {
                                            $checked = 'checked';
                                            $vchecked = 'N';
                                        } elseif (!empty($row["email"])) {
                                            $checked = '';
                                            $vchecked = 'Y';
                                        } ?>
                                        <input title="Recebe email XML NFe" type="checkbox" <?= $checked ?> onclick="altpessoacontatoemails(<?= $row["idpessoacontato"] ?>,'emailxmlnfe','<?= $vchecked ?>')">
                                    <? } ?>
                                </td>

                                <!--td  align="center"  class="respreto"  title="Recebe email Resultado">	
                                    <?
                                    if (!empty($row["usuario"])) {
                                        if ($row["emailresultado"] == "Y" and !empty($row["usuario"])) {
                                            $checked = 'checked';
                                            $vchecked = 'N';
                                        } elseif (!empty($row["usuario"])) {
                                            $checked = '';
                                            $vchecked = 'Y';
                                        }
                                    ?>
                                        <input title="Recebe email resultado" type="checkbox" <?= $checked ?> name="namereceberes" onclick="altreceberespcontato(<?= $row["idpessoacontato"] ?>,'<?= $vchecked ?>','<?= date("d/m/Y H:i:s") ?>');">
                                        <?
                                    } //!empty($row["usuario"])
                                        ?>
                                </td-->
                                <td nowrap align="center" title="Resultado positivo PDF">
                                    <? if ($row["receberes"] == "PDF") {
                                        $checked = 'checked';
                                        $recebe = '';
                                    } else {
                                        $checked = '';
                                        $recebe = 'PDF';
                                    } ?>
                                    <input title="Recebe PDF" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberespessoacontato(<?= $row["idpessoacontato"] ?>,'<?= $recebe ?>');">
                                </td>
                                <td nowrap align="center" title="Resultado positivo LINK">
                                    <? if ($row["receberes"] == "LINK") {
                                        $checked = 'checked';
                                        $recebe = '';
                                    } else {
                                        $checked = '';
                                        $recebe = 'LINK';
                                    } ?>
                                    <input title="Resultado positivo LINK" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberespessoacontato(<?= $row["idpessoacontato"] ?>,'<?= $recebe ?>');">
                                </td>
                                <td nowrap align="center" title="Resultado todos PDF">
                                    <? if ($row["receberestodos"] == "PDF") {
                                        $checked = 'checked';
                                        $recebe = '';
                                    } else {
                                        $checked = '';
                                        $recebe = 'PDF';
                                    } ?>
                                    <input title="Resultado todos PDF" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberestodospessoacontato(<?= $row["idpessoacontato"] ?>,'<?= $recebe ?>');">
                                </td>
                                <td nowrap align="center" title="Resultado todos LINK">
                                    <? if ($row["receberestodos"] == "LINK") {
                                        $checked = 'checked';
                                        $recebe = '';
                                    } else {
                                        $checked = '';
                                        $recebe = 'LINK';
                                    } ?>
                                    <input title="Resultado todos LINK" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberestodospessoacontato(<?= $row["idpessoacontato"] ?>,'<?= $recebe ?>');">
                                </td>
                                <? if (in_array($arrpessoa['idtipopessoa'], [2, 3])) {

                                    if ($row["somenteoficial"] == "Y") {
                                        $checked = 'checked';
                                        $vchecked = 'N';
                                    } else {
                                        $checked = '';
                                        $vchecked = 'Y';
                                    }
                                ?>
                                    <td nowrap align="center" title="Somente Oficial">
                                        <? if (
                                            $row["receberes"] == "PDF" or $row["receberestodos"] == "PDF"
                                            or $row["receberes"] == "LINK" or $row["receberestodos"] == "LINK"
                                        ) { ?>
                                            <input title="" type="checkbox" <?= $checked ?> onclick="altpessoacontatoemails(<?= $row["idpessoacontato"] ?>, 'somenteoficial', '<?= $vchecked ?>')">
                                        <? } ?>
                                    </td>
                                <? } ?>
                                <td>
                                    <a class="fa fa-trash  cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluicontato(<?= $row["idpessoacontato"] ?>)" title="Excluir"></a>
                                </td>
                            </tr>
                        <?
                        } //while($row = mysqli_fetch_array($res)){
                        ?>
                    </table>

                <?
                } //if($rownum1>0){
                ?>
                <table>
                    <tr>
                        <td>Nome:</td>
                        <td colspan="3">
                            <input type="text" name="pessoacontato" cbvalue="pessoacontato" value="" style="width: 40em;" autocomplete="off">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <a class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" title="Nova pesssoa" onclick="janelamodal('?_modulo=pessoa&_acao=i&idcontato=<?= $arrpessoa['idpessoa'] ?><?= $idempresaLinkMatriz ?>')"></a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
<?
} //listacontato($arrpessoa){


function contato($arrpessoa)
{
    global $_acao, $j, $_1_u_pessoa_idtipopessoa, $idempresaLinkMatriz, $portifolioArquivo;
    ?>
    <div class="row">
        <? if ($arrpessoa['idtipopessoa'] == 12 or $arrpessoa['idtipopessoa'] == 9) { //Representante consultor 
        ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <table>
                        <tr>
                            <td nowrap align="right">Raz&atilde;o Social:</td>
                            <td colspan="5"><input class="upper" name="_1_<?=$_acao?>_pessoa_razaosocial" type="text" id="razaosocial" size="40" vnulo value="<?= $arrpessoa['razaosocial'] ?>"></td>
                        </tr>
                        <tr>
                            <td nowrap align="right">Consumidor Final:</td>
                            <td colspan="5">
                                <select name="_1_<?=$_acao?>_pessoa_indfinal">
                                    <? fillselect("select 0,'Não'
                                                           union select 1,'Sim'", $arrpessoa['indfinal']); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td nowrap align="right">Ins. Municipal:</td>
                            <td colspan="5"><input name="_1_<?=$_acao?>_pessoa_InscricaoMunicipalTomador" type="text" size="30" maxlength="" value="<?= $arrpessoa['InscricaoMunicipalTomador'] ?>"></td>
                        </tr>
                        <tr>
                            <td nowrap align="right">Ins. Estadual:</td>
                            <td colspan="5"><input name="_1_<?=$_acao?>_pessoa_inscrest" type="text" id="inscestadual" size="30" maxlength="" value="<?= $arrpessoa['inscrest'] ?>"></td>
                        </tr>
                        <tr>
                            <td nowrap align="right">Indicador IE:</td>
                            <td>
                                <select class="size15" name="_1_<?=$_acao?>_pessoa_indiedest">
                                    <option value=""></option>
                                    <? fillselect("select 1,'[1]-Contribuinte ICMS'
                          union select 2,'[2]-Contribuinte isento'
                          union select 9,'[9]-Não Contribuinte'", $arrpessoa['indiedest']); ?>
                                </select>
                            </td>
                            <td align="right" nowrap>Prod. Rural:</td>
                            <td>
                                <select class="size5" name="_1_<?=$_acao?>_pessoa_flgprodrural">
                                    <option value=""></option>
                                    <? fillselect("select 'S','Sim'
                                                       union select 'N','Não'", $arrpessoa['flgprodrural']); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">
                                Fatura Automática:
                            </td>
                            <td>
                                <select class="size5" name="_1_<?=$_acao?>_pessoa_faturaautomatica">
                                    <option value=""></option>
                                    <? fillselect(array('S' => 'Sim', 'N' => 'Não'), $arrpessoa['faturaautomatica']); ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        <? } //if($arrpessoa['idtipopessoa']==12 or $arrpessoa['idtipopessoa']==9){
        ?>
        <div class="col-md-6">
            <div class="panel panel-default">
                <table style="width: 95%;">
                    <?
                    //   if($arrpessoa['idtipopessoa']!=8){// Contato Fornecedor
                    ?>

                    <tr>
                        <td></td>
                        <div class="alert alert-info"><i class="fa fa-shield fa-2x"></i>&nbsp;
                            <? if (empty($arrpessoa['usuario'])) { ?>
                                <a class="pointer bold" onclick="javascript:usauriosenha();">Usuário e Senha</a></td>
                            <? } elseif (empty($arrpessoa['email'])) { ?>
                                <a class="pointer bold" onclick="javascript:alert('Atenção: para alteração de senha o email pessoal do usuário deve ser informado e salvo.');$('[name=_1_u_pessoa_email]').addClass('highlight').focus();">Usuário e Senha</a></td>
                            <? } elseif (!empty($arrpessoa['email']) and !empty($arrpessoa['usuario'])) { ?>
                                <a class="pointer bold" onclick="javascript:CB.mostraRecuperaSenha('<?= $arrpessoa['usuario'] ?>','<?= $arrpessoa['email'] ?>')">Usuário e Senha</a></td>
                            <? } ?>
                            <div>
                    </tr>
                    <?
                    //     }//if($arrpessoa['idtipopessoa']!=8){ contato fornecedor
                    ?>
                    <tr>
                        <td align="right">Email pessoal:</td>
                        <td colspan="4"><input name="_1_<?=$_acao?>_pessoa_email" type="text" size="40" value="<?= $arrpessoa['email'] ?>" autocomplete="off"></td>
                    </tr>
                    <tr>
                        <td align="right">Data de Expiração:</td>
                        <td colspan="1"><input style="width: 50%;" name="_1_<?=$_acao?>_pessoa_expiraem" class="calendario" type="text" size="8" value="<?= dma($arrpessoa['expiraem']) ?>" vdata></td>
                    </tr>
                    <tr>
                        <td align="right">Data de Nascimento:</td>
                        <td colspan="1"><input style="width: 50%;" name="_1_<?=$_acao?>_pessoa_nasc" class="calendario" type="text" size="8" value="<?= dma($arrpessoa['nasc']) ?>" vdata></td>
                    </tr>
                    <tr>
                        <td align="right">Tel. Fixo:</td>
                        <td class="nowrap"><input style="width: 40px;" name="_1_<?=$_acao?>_pessoa_dddfixo" type="text" size="1" value="<?= $arrpessoa['dddfixo'] ?>" vnumero maxlength="3">
                            <input style="width: 100px;" name="_1_<?=$_acao?>_pessoa_telfixo" type="text" size="20" value="<?= $arrpessoa['telfixo'] ?>" vnumero maxlength="9">
                        </td>
                        <td align="right">Celular:</td>
                        <td><input style="width: 40px;" name="_1_<?=$_acao?>_pessoa_dddcel" type="text" size="1" value="<?= $arrpessoa['dddcel'] ?>" vnumero maxlength="3">
                            <input style="width: 100px;" name="_1_<?=$_acao?>_pessoa_telcel" type="text" size="20" value="<?= $arrpessoa['telcel'] ?>" vnumero maxlength="9">
                        </td>
                    </tr>

                    <tr>
                        <td align="right">Tel. Comercial:</td>
                        <td><input style="width: 40px;" name="_1_<?=$_acao?>_pessoa_dddcom" type="text" size="1" value="<?= $arrpessoa['dddcom'] ?>" vnumero maxlength="3">
                            <input style="width: 100px;" name="_1_<?=$_acao?>_pessoa_telcom" type="text" size="20" value="<?= $arrpessoa['telcom'] ?>" vnumero maxlength="9">
                        </td>
                    </tr>
                    <? if ($arrpessoa['idtipopessoa'] == 4) { ?>
                        <tr>
                            <td align="right">Email Result.(POS):</td>

                            <td>
                                <select name="_1_<?=$_acao?>_pessoa_receberes" disabled='disabled'>
                                    <option value=""></option>
                                    <? fillselect("select 'LINK','Link'
                                             union select 'PDF','Pdf'", $arrpessoa['receberes']); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">Email Result. (TODOS):</td>

                            <td>
                                <select name="_1_<?=$_acao?>_pessoa_receberestodos" disabled='disabled'>
                                    <option value=""></option>
                                    <? fillselect("select 'LINK','Link'
                                                 union select 'PDF','Pdf'", $arrpessoa['receberestodos']); ?>
                                </select>
                            </td>
                        </tr>
                    <? } ?>
                    <tr>
                        <td align="right">Observação:</td>

                        <td colspan="4"><textarea name="_1_<?=$_acao?>_pessoa_obs" rows="5" cols="60"><?= $arrpessoa['obs'] ?></textarea></td>
                    </tr>
                    <tr>
                        <td align="right">
                            Fatura Automática:
                        </td>
                        <td>
                            <select class="size5" name="_1_<?=$_acao?>_pessoa_faturaautomatica">
                                <option value=""></option>
                                <? fillselect(array('S' => 'Sim', 'N' => 'Não'), $arrpessoa['faturaautomatica']); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">
                            Portifólio
                        </td>
                        <td>
                            <? if ($portifolioArquivo) { ?>
                                <div class="flex">
                                    <a href="<?= $portifolioArquivo['caminho'] ?>" class="mr-3" download>
                                        <i class="fa fa-file-o"></i>
                                    </a>
                                    <i class="fa fa-trash pointer" onclick="removerPortifolio(<?= $portifolioArquivo['idarquivo'] ?>)"></i>
                                </div>
                            <? } else { ?>
                                <label for="" class="pointer">
                                    <i id="input-portifolio" class="fa fa-file-o"></i>
                                </label>
                            <? } ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <? if ($_1_u_pessoa_idtipopessoa != 115) { ?>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">DIVISÃO DE NEGÓCIO</div>
                    <div class="panel-body divisao_negocio">
                        <?
                        $sqlemp = " select 
                        e.idempresa,e.nomefantasia as nome
                    from 
                        plantel u join empresa e on(e.idempresa=u.idempresa and e.status='ATIVO')
                    where 
                        u.status='ATIVO'
                    group by e.idempresa
                    order by 
                    e.empresa
                    ;";
                        $resemp = d::b()->query($sqlemp) or die("Erro ao buscar empresa e plantel sql=".$sqlemp);
                        while ($rowemp = mysqli_fetch_assoc($resemp)) {
                        ?>

                            <div class="panel panel-default" style="font-size: 8pt; margin-top: 5px !important;">
                                <div class="alert bold" style="  padding-left: 10px;   padding-top: 10px; "><?= $rowemp['nome'] ?></div>
                                <table style="width: 100%;">
                                    <tr>
                                        <?
                                        $sqlu = "select 
                                            u.idplantel, e.empresa,concat(e.sigla,'-',u.plantel) as plantel,p.idplantelobjeto
                                        from 
                                            plantel u join empresa e on(e.idempresa=u.idempresa and e.status='ATIVO' and e.idempresa=".$rowemp['idempresa'].")
                                                left join plantelobjeto p on(u.idplantel = p.idplantel 
                                                                    and p.idobjeto =".$arrpessoa["idpessoa"]." 
                                                                    and p.tipoobjeto = 'pessoa')
                                        where 
                                            u.status='ATIVO'
                                        
                                        order by 
                                        e.empresa,
                                            u.plantel ";
                                        $resu = d::b()->query($sqlu) or die("Erro ao buscar plantel sql=".$sqlu);

                                        $i = 0;
                                        while ($rowu = mysqli_fetch_assoc($resu)) {
                                            echo '<td style="width: 25%;">';
                                            if (!empty($rowu['idplantelobjeto'])) { ?>
                                                <i style="padding-right: 0px;" class="fa fa-check-square-o fa-1x btn-lg pointer" onclick="alttipocontato(<?= $rowu['idplantelobjeto'] ?>,'N', this);" alt="Alterar para Não"></i>
                                            <? } else { ?>
                                                <i style="padding-right: 0px;" class="fa fa-square-o fa-1x btn-lg pointer" onclick="alttipocontato(<?= $rowu['idplantel'] ?>,'Y', this);" alt="Alterar para Sim"></i>
                                        <? }
                                            echo ($rowu['plantel']);
                                            $i++;

                                            if (($i % 4) == 0) {
                                                echo '</tr><tr>';
                                            }
                                        }
                                        ?>
                                    </tr>
                                </table>
                            </div>

                        <?
                        }
                        ?>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">Alertas</div>
                    <div class="panel-body ">
                        <table class="table table-striped planilha ">
                            <tr>
                                <td><input id="immsgconf" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                            </tr>
                        </table>
                        <?= listaAlertas() ?>
                    </div>
                </div>
            </div>
        <? } ?>
    </div>

    <? if ($_1_u_pessoa_idtipopessoa != 115) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Clientes relacionados ao <?= traduzid("tipopessoa", "idtipopessoa", "tipopessoa", $arrpessoa['idtipopessoa']) ?></div>
                    <div class="panel-body  ">
                        <?
                        $sql2 = "select c.idpessoacontato,c.receberes,c.receberestodos,c.somenteoficial,c.emailresultado,p.nome,p.idpessoa,p.idtipopessoa,c.participacaoserv,c.participacaoprod,c.alteradopor,dmahms(c.alteradoem) as dmaalteradoem
                                from pessoacontato c,pessoa p
                                where c.idpessoa = p.idpessoa
                                                            and p.status IN ('ATIVO','PENDENTE')
                                and c.idcontato = ".$arrpessoa['idpessoa']."					
                                    order by p.nome";

                        $res2 = d::b()->query($sql2) or die("A Consulta dos contatos falhou :".mysql_error(d::b())."<br>Sql:".$sql2);
                        $qtdrows2 = mysqli_num_rows($res2);
                        if ($qtdrows2 > 0) {
                        ?>
                            <table class="table table-striped planilha">
                                <tr>
                                    <? if ($arrpessoa['idtipopessoa'] == 3 and !empty($arrpessoa['usuario'])) { ?>
                                        <th align="center">Email res.</th>
                                    <? } ?>
                                    <th align="center">Nome</th>
                                    <?/*
                                    if($arrpessoa['idtipopessoa']==12){?>
                                        <th  align="center">Participação Serv.%</th>	
                                        <th  align="center">Participação Prod.%</th>
                                        <th  align="center">Visita</th>	
                                    <?}
                                    */
                                    ?>
                                    <th>Positivo PDF</th>
                                    <th>Positivo LINK</th>
                                    <th>Todos PDF</th>
                                    <th>Todos LINK</th>
                                    <? if (in_array($arrpessoa['idtipopessoa'], [2, 3])) { ?>
                                        <th>Somente Oficial</th>
                                    <? } ?>
                                    <th>Alterado Por</th>
                                    <th>Alterado Em</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                <? $y = 99;
                                while ($row2 = mysqli_fetch_array($res2)) {
                                    $y = $y + 1;
                                    $rowv = "";
                                    /*
                                    if($arrpessoa['idtipopessoa']==12){
                                        $sqlv="select a.idatendimento,a.data,a.*
                                            FROM atendimento a 
                                            where a.idmotivo=28 
                                            and a.idpessoa=".$row2['idpessoa']."
                                            order by a.data desc limit 1";
                                        $resv=d::b()->query($sqlv) or die("A Consulta da visita falhou :".mysql_error(d::b())."<br>Sql:".$sqlv); 
                                        $rowv=mysqli_fetch_assoc($resv);
                                    }
                                    */
                                ?>
                                    <tr>
                                        <? if ($arrpessoa['idtipopessoa'] == 3 and !empty($arrpessoa['usuario'])) { ?>
                                            <td align="center" title="Recebe email Resultado">
                                                <?
                                                if ($row2["emailresultado"] == "Y") {
                                                    $checked = 'checked';
                                                    $vchecked = 'N';
                                                } else {
                                                    $checked = '';
                                                    $vchecked = 'Y';
                                                }
                                                ?>
                                                <input title="Recebe email resultado" type="checkbox" <?= $checked ?> name="namereceberes" onclick="altreceberespcontato(<?= $row2["idpessoacontato"] ?>,'<?= $vchecked ?>','<?= date("d/m/Y H:i:s"); ?>');">

                                            </td>
                                        <? } ?>
                                        <td><?= $row2["nome"] ?></td>
                                        <?
                                        /*
                                    if($arrpessoa['idtipopessoa']==12){?>
                                        <td >
                                            <input name="_<?=$y?>_<?=$_acao?>_pessoacontato_idpessoacontato" type="hidden" size="5" value="<?=$row2['idpessoacontato']?>">
                                            <input name="_<?=$y?>_<?=$_acao?>_pessoacontato_participacaoserv" type="text" size="5" value="<?=$row2['participacaoserv']?>" vdecimal>
                                        </td>
                                        <td ><input name="_<?=$y?>_<?=$_acao?>_pessoacontato_participacaoprod" type="text" size="5" value="<?=$row2['participacaoprod']?>" vdecimal></td>
                                        <td><a class="pointer hoverazul" onclick="janelamodal('?_modulo=atendimento&_acao=u&idatendimento=<?=$rowv["idatendimento"]?>')" title="Editar"><?=dma($rowv['data']);?></a></td>
                                    <?}
                                    */ ?>
                                        <td nowrap align="center" title="Resultado positivo PDF">
                                            <? if ($row2["receberes"] == "PDF") {
                                                $checked = 'checked';
                                                $recebe = '';
                                            } else {
                                                $checked = '';
                                                $recebe = 'PDF';
                                            } ?>
                                            <input title="Recebe PDF" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberespessoacontato(<?= $row2["idpessoacontato"] ?>,'<?= $recebe ?>');">
                                        </td>
                                        <td nowrap align="center" title="Resultado positivo LINK">
                                            <? if ($row2["receberes"] == "LINK") {
                                                $checked = 'checked';
                                                $recebe = '';
                                            } else {
                                                $checked = '';
                                                $recebe = 'LINK';
                                            } ?>
                                            <input title="Resultado positivo LINK" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberespessoacontato(<?= $row2["idpessoacontato"] ?>,'<?= $recebe ?>');">
                                        </td>
                                        <td nowrap align="center" title="Resultado todos PDF">
                                            <? if ($row2["receberestodos"] == "PDF") {
                                                $checked = 'checked';
                                                $recebe = '';
                                            } else {
                                                $checked = '';
                                                $recebe = 'PDF';
                                            } ?>
                                            <input title="Resultado todos PDF" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberestodospessoacontato(<?= $row2["idpessoacontato"] ?>,'<?= $recebe ?>');">
                                        </td>
                                        <td nowrap align="center" title="Resultado todos LINK">
                                            <? if ($row2["receberestodos"] == "LINK") {
                                                $checked = 'checked';
                                                $recebe = '';
                                            } else {
                                                $checked = '';
                                                $recebe = 'LINK';
                                            } ?>
                                            <input title="Resultado todos LINK" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberestodospessoacontato(<?= $row2["idpessoacontato"] ?>,'<?= $recebe ?>');">
                                        </td>
                                        <? if (in_array($arrpessoa['idtipopessoa'], [2, 3])) {

                                            if ($row2["somenteoficial"] == "Y") {
                                                $checked = 'checked';
                                                $vchecked = 'N';
                                            } else {
                                                $checked = '';
                                                $vchecked = 'Y';
                                            }
                                        ?>
                                            <td nowrap align="center" title="Somente Oficial">
                                                <? if (
                                                    $row2["receberes"] == "PDF" or $row2["receberestodos"] == "PDF"
                                                    or $row2["receberes"] == "LINK" or $row2["receberestodos"] == "LINK"
                                                ) { ?>
                                                    <input title="" type="checkbox" <?= $checked ?> onclick="altpessoacontatoemails(<?= $row2["idpessoacontato"] ?>, 'somenteoficial', '<?= $vchecked ?>')">
                                                <? } ?>
                                            </td>
                                        <? } ?>
                                        <td><?= $row2["alteradopor"] ?></td>
                                        <td><?= $row2["dmaalteradoem"] ?></td>
                                        <td>
                                            <a class="fa fa-bars pointer hoverazul" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $row2["idpessoa"] ?><?= $idempresaLinkMatriz ?>')" title="Editar"></a>
                                        </td>
                                        <td>
                                            <a class="fa fa-trash  cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluicontato(<?= $row2["idpessoacontato"] ?>)" title="Excluir"></a>
                                        </td>
                                    </tr>
                                <?
                                }
                                ?>
                            </table>
                        <?
                        }
                        if ($arrpessoa['idtipopessoa'] != 15 or $qtdrows2 < 1) {
                        ?>
                            <table>
                                <tr>
                                    <td>Nome:</td>
                                    <td colspan="3">
                                        <input type="text" name="pessoacontatoidpessoa" cbvalue="pessoacontatoidpessoa" value="" style="width: 40em;" autocomplete="off">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <a class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" title="Nova pesssoa" onclick="janelamodal('?_modulo=pessoa&_acao=i')"></a>
                                    </td>
                                </tr>
                            </table>
                        <?
                        } //if($arrpessoa['idtipopessoa']!=15 or $qtdrows2 < 1){            
                        ?>

                    </div>
                </div>
            </div>
        </div>
    <?
    }
} // function contato($arrpessoa){

function fornecedor($arrpessoa)
{
    global $_acao, $j;
    ?>
    <div class="row">
        <div class="col-md-5">
            <div class="panel panel-default">
                <table>
                    <tr>
                        <?
                        if ($arrpessoa['idtipopessoa'] == 5) {
                            $sql = "select * from vwresultadoavaliacao where idpessoa =".$arrpessoa['idpessoa'];
                            $res = d::b()->query($sql) or die("A Consulta do resultado da avaliacao falhou :".mysql_error()."<br>Sql:".$sql);
                            $qtdrowd = mysqli_num_rows($res);
                            $row = mysqli_fetch_array($res);
                            ?>
                            <td align="right">Avaliação:</td>
                            <td>
                                <label class="idbox">
                                    <?
                                    if ($qtdrowd > 0) {
                                    ?>

                                        <?= $row["resultado"] ?>
                                    <?
                                    } else {
                                        echo ("PENDENTE");
                                    }
                                    ?>
                                </label>
                            </td>
                            <? if (!empty($row["nota"])) { ?>
                                <td align="right">Nota:</td>
                                <td>
                                    <label class="idbox"><?= $row["nota"] ?></label>
                                </td>
                        <? }
                        }
                        ?>

                    </tr>
                    <tr>
                        <td align="right" nowrap>Raz&atilde;o Social:</td>
                        <td><input name="_1_<?=$_acao?>_pessoa_razaosocial" type="text" id="razaosocial" size="40" value="<?= $arrpessoa['razaosocial'] ?>" vnulo></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Consumidor Final:</td>
                        <td colspan="5">
                            <select class="size5" name="_1_<?=$_acao?>_pessoa_indfinal">
                                <? fillselect("select 0,'Não'
                                             union select 1,'Sim'", $arrpessoa['indfinal']); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Regime Tributário:</td>
                        <td colspan="5">
                            <select class="" name="_1_<?=$_acao?>_pessoa_regimetrib">
                                <option value=""></option>
                                <? fillselect("select 1,'Simples Nacional'
                                    union select 2,'Simples Nacional - Excesso de Sublimite da Receita Bruta'
                                    union select 3,'Regime Normal'
                                    union select 4,'Simples Nacional - MEI'", $arrpessoa['regimetrib']); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Email:</td>
                        <td><input name="_1_<?=$_acao?>_pessoa_email" type="text" size="40" maxlength="" value="<?= $arrpessoa['email'] ?>"></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Email XML NFe:</td>
                        <td><input name="_1_<?=$_acao?>_pessoa_emailxmlnfe" type="text" size="40" maxlength="" value="<?= $arrpessoa['emailxmlnfe'] ?>" vemail></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Ins. Municipal:</td>
                        <td><input name="_1_<?=$_acao?>_pessoa_InscricaoMunicipalTomador" type="text" size="30" maxlength="" value="<?= $arrpessoa['InscricaoMunicipalTomador'] ?>"></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Ins. Estadual:</td>
                        <td><input name="_1_<?=$_acao?>_pessoa_inscrest" type="text" id="inscestadual" size="30" maxlength="" value="<?= $arrpessoa['inscrest'] ?>"></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Telefone Fixo:</td>
                        <td>
                            <input style="width: 40px;" name="_1_<?=$_acao?>_pessoa_dddfixo" type="text" size="1" value="<?= $arrpessoa['dddfixo'] ?>" maxlength="2">
                            <input style="width: 100px;" name="_1_<?=$_acao?>_pessoa_telfixo" type="text" size="20" value="<?= $arrpessoa['telfixo'] ?>" maxlength="9">
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Telefone Celular:</td>
                        <td>
                            <input style="width: 40px;" name="_1_<?=$_acao?>_pessoa_dddcel" type="text" size="1" value="<?= $arrpessoa['dddcel'] ?>" vnumero maxlength="2">
                            <input style="width: 100px;" name="_1_<?=$_acao?>_pessoa_telcel" type="text" size="20" value="<?= $arrpessoa['telcel'] ?>" vnumero maxlength="9">
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Indicador IE:</td>
                        <td class="nowrap">
                            <select style="width: 170px;" name="_1_<?=$_acao?>_pessoa_indiedest">
                                <option value=""></option>
                                <? fillselect("select 1,'[1]-Contribuinte ICMS'
                                                union select 2,'[2]-Contribuinte isento'
                                                union select 9,'[9]-Não Contribuinte'", $arrpessoa['indiedest']); ?>
                            </select>
                            Prod. Rural:
                            <select class="size5" name="_1_<?=$_acao?>_pessoa_flgprodrural">
                                <option value=""></option>
                                <? fillselect("select 'S','Sim'
                                             union select 'N','Não'", $arrpessoa['flgprodrural']); ?>
                            </select>
                        </td>
                    </tr>
                    <?
                    if ($arrpessoa['idtipopessoa'] == 11) {
                        if (empty($arrpessoa['observacaonfp'])) {
                            $arrpessoa['observacaonfp'] = "De 2 à 3 dias úteis";
                        }
                        ?>
                        <tr>
                            <td nowrap align="right">Previsão Entrega:</td>
                            <td><input name="_1_<?=$_acao?>_pessoa_observacaonfp" type="text" size="30" value="<?= $arrpessoa['observacaonfp'] ?>"></td>
                        </tr>
                        <tr>
                            <td align="right" nowrap>URL Rastreamento:</td>
                            <td><textarea name="_1_<?=$_acao?>_pessoa_url" rows="2" cols="30"><?= $arrpessoa['url'] ?></textarea></td>
                        </tr>
                    <?
                    }
                    ?>
                    <tr>
                        <td align="right" nowrap>Emails Cotação:</td>
                        <td style="text-align:top;">
                            <textarea name="_1_<?=$_acao?>_pessoa_emailresult" rows="6" cols="30" onchange="if(this.value.indexOf(';') != -1){alert('Atenção: Utilizar Vírgula para separar Emails!')}"><?= $arrpessoa['emailresult'] ?></textarea>
                            <?
                            echo "<br><font color='red'>Atenção: Utilizar Vírgula para separar Emails!</font></br>";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>OBS - Cotação:</td>
                        <td><textarea name="_1_<?=$_acao?>_pessoa_observacaore" rows="3" cols="30"><?= $arrpessoa['observacaore'] ?></textarea></td>
                    </tr>

                    <tr>
                        <td align="right" nowrap>OBS - Gerais:</td>
                        <td><textarea class="caixa" rows="3" cols="30" name="_1_<?=$_acao?>_pessoa_obs"><?= $arrpessoa['obs'] ?></textarea></td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Empresa do Grupo:</td>
                        <td colspan="5">
                            <select class="size20" name="_1_<?=$_acao?>_pessoa_idempresagrupo">
                                <option value=""></option>
                                <? fillselect("select idempresa,concat(sigla,' - ',razaosocial) as razaosocial
                            from empresa
                        where status='ATIVO'
                            order by razaosocial", $arrpessoa['idempresagrupo']); ?>
                            </select>
                            <i title="Necessário para identificar a pessoa que representa a empresa no momento da transferência fiscal." class="fa fa-info-circle fa-1x  pointer hoverpreto btn-lg tip"></i>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">
                            Fatura Automática:
                        </td>
                        <td>
                            <select class="size5" name="_1_<?=$_acao?>_pessoa_faturaautomatica">
                                <option value=""></option>
                                <? fillselect(array('S' => 'Sim', 'N' => 'Não'), $arrpessoa['faturaautomatica']); ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col-md-7">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" href="#localInfo2">
                            <table>
                                <tr>
                                    <td>
                                        <font color="Blue" style="font-weight: bold;">Atendimentos</font>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="panel-body collapse" id="localInfo2">
                            <?
                            $sqlav = "select idatendimento,data,m.motivo,a.status from atendimento a left join  motivo m  on(m.idmotivo=a.idmotivo)
                        where a.idpessoa=".$arrpessoa['idpessoa']." order by data";
                            $resav = d::b()->query($sqlav) or die("Erro ao buscar atendimentos sql=".$sqlav);
                            $qtdav = mysqli_num_rows($resav);
                            if ($qtdav > 0) {
                            ?>
                                <table class="table table-striped planilha">
                                    <tr>
                                        <th>ID</th>
                                        <th>Motivo</th>
                                        <th>Data</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>

                                    <?

                                    while ($rowav = mysqli_fetch_assoc($resav)) {
                                    ?>
                                        <tr>
                                            <td><?= $rowav['idatendimento'] ?></td>
                                            <td><?= $rowav['motivo'] ?></td>
                                            <td><?= dma($rowav['data']) ?></td>
                                            <td><?= $rowav['status'] ?></td>
                                            <td>
                                                <a class="fa fa-bars pointer hoverazul" title="Atendimento" onclick="janelamodal('?_modulo=atendimento&_acao=u&idatendimento=<?= $rowav['idatendimento'] ?>')"></i>
                                            </td>
                                        </tr>
                                <?
                                    }
                                }
                                ?>
                                </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading" data-toggle="collapse" href="#localInfo1">
                            <table>
                                <tr>
                                    <td>
                                        <font color="Blue" style="font-weight: bold;">Garantia da Qualidade</font>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-12 panel-heading" style="background-color: #f5f5f5;">
                            <table>
                                <tr>
                                    <td>
                                        Requerimento de Qualificação
                                    </td>
                                    <td>
                                        <select name="_1_<?=$_acao?>_pessoa_rqqualificacao" id="rqqualificacao" onchange="rqQualificacao(this)">
                                            <? fillselect("select 'Y','Sim'
                                                union select 'N','Não'", $arrpessoa["rqqualificacao"]); ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="panel-body collapse" id="localInfo1">
                            <table style="width: 100%;">
                                <tr>
                                    <td title="Não há nenhuma avaliação para esse fornecedor">Status Qualificação:
                                        <?
                                        if ($arrpessoa['idtipopessoa'] == 5) {
                                            $_sql = "select * from vwresultadoavaliacao where idpessoa =".$arrpessoa['idpessoa'];
                                            $_res = d::b()->query($_sql) or die("A Consulta do resultado da avaliacao falhou :".mysql_error()."<br>Sql:".$_sql);
                                            $_qtdrowd = mysqli_num_rows($_res);
                                            $_row = mysqli_fetch_array($_res);
                                            ?>
                                            <label class="idbox">
                                                <?
                                                if ($arrpessoa["rqqualificacao"] == 'N') {
                                                    echo 'NÃO SE APLICA';
                                                } elseif ($_qtdrowd > 0) {
                                                    echo $_row["resultado"];
                                                } else {
                                                    echo "Pendente";
                                                }
                                                ?>
                                            </label>
                                        <? } ?>
                                    </td>
                                    <td align="right">Emite Certificado de Análise</td>
                                    <td>
                                        <select name="_1_<?=$_acao?>_pessoa_certanalise" id="certanalise" onchange="rqQualificacao()">
                                            <option value=""></option>
                                            <? fillselect("select 'Y','Sim'
                                                union select 'N','Não'
                                                union select 'O','N/A'", $arrpessoa["certanalise"]); ?>
                                        </select>
                                    </td>
                                    <td align="right">Criticidade:</td>
                                    <td>
                                        <select name="_1_<?=$_acao?>_pessoa_criticidade">
                                            <option value=""></option>
                                            <? fillselect("select 'ALTA','Alta'
                                            union select 'MEDIA','Média' 
                                            union select 'BAIXA','Baixa'", $arrpessoa["criticidade"]); ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>

                            <?
                            $sqlav = "select idsgdoc,idregistro,titulo,status,ifnull(nota,'-') as nota
                                from sgdoc 
                                where idpessoa = ".$arrpessoa['idpessoa']." order by titulo";
                            $resav = d::b()->query($sqlav) or die("Erro ao buscar avaliações sql=".$sqlav);
                            $qtdav = mysqli_num_rows($resav);
                            if ($qtdav > 0) {
                            ?>
                                <table class="table table-striped planilha" style="margin: 10px 0px;">
                                    <tr>
                                        <th>Registro</th>
                                        <th>Nota</th>
                                        <th>Título</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>

                                    <?
                                    while ($rowav = mysqli_fetch_assoc($resav)) {
                                    ?>
                                        <tr>
                                            <td><?= $rowav['idregistro'] ?></td>
                                            <td><?= $rowav['nota'] ?></td>
                                            <td><?= $rowav['titulo'] ?></td>
                                            <td><?= $rowav['status'] ?></td>
                                            <td>
                                                <a class="fa fa-bars pointer hoverazul" title="Avaliação" onclick="janelamodal('?_modulo=documento&_acao=u&idsgdoc=<?= $rowav['idsgdoc'] ?>')"></i>
                                            </td>
                                        </tr>
                                        <?
                                    }
                                }
                                ?>
                                </table>


                                <table class="table planilha" style="margin: 10px 0px;  background-color: #f5f5f5!important;">
                                    <tr>
                                        <th colspan="5" style="text-align: center;">Criar Documento de Qualificação</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            Título:
                                        </td>
                                        <td style="width: 100%;">
                                            <input style="background-color: white;" type="text" id="titledoc">
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Tipo;
                                        </td>
                                        <td style="width: 100%;">
                                            <select id='typedoc' vnulo style="width: 100%;">
                                                <option></option>
                                                <? $fsql = "SELECT 
                                                st.idsgdoctipo,
                                                st.rotulo
                                            FROM
                                                fluxoobjeto fo
                                                    JOIN
                                                fluxo f ON (f.idfluxo = fo.idfluxo)
                                                    JOIN
                                                sgdoctipo st ON (st.idsgdoctipo = f.idobjeto)
                                                    LEFT JOIN
                                                imgrupopessoa gp on((gp.idimgrupo = fo.idobjeto and fo.tipoobjeto = 'imgrupo') or (gp.idpessoa = fo.idobjeto and fo.tipoobjeto = 'pessoa'))
                                            WHERE
                                                gp.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA']."
                                                and st.status='ATIVO'
                                                group by idsgdoctipo
                                                ORDER BY rotulo"; ?>
                                                <? fillselect($fsql); ?>
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-xs" onclick="criaDocumento()">Criar</button>
                                        </td>
                                    </tr>
                                </table>


                                <table style="width: 100%;">
                                    <tr>
                                        <td>
                                            Anexo: <i class="fa fa-cloud-upload dz-clickable pointer azul" id="arquivosanexos" title="Clique para adicionar um anexo"></i>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            Observação:
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <textarea rows="3" cols="30" title="Observação" name="_1_<?=$_acao?>_pessoa_obsqualificacao"><?= $arrpessoa['obsqualificacao'] ?></textarea>
                                        </td>
                                    </tr>
                                </table>


                        </div>
                    </div>
                </div>
            </div>
            <?

            //listar Fabricantes
            if ($arrpessoa['idtipopessoa'] == 5) {

                $sql1 = "select p.nome,f.idfornecedorfab 
					from fornecedorfab f,pessoa p
					where p.idpessoa = f.idfabricante
					and f.status = 'ATIVO'
					and f.idfornecedor =".$arrpessoa['idpessoa'];
                $res1 = d::b()->query($sql1) or die("A Consulta dos fabricantes do fornecedor falhou :".mysql_error()."<br>Sql:".$sql1);
                $qtdrowd2 = mysqli_num_rows($res1);

                if ($qtdrowd2 > 0) {
                    ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">Fabricantes Representados Pelo Fornecedor</div>
                        <table class="table table-striped planilha">
                            <tr>
                                <th align="center">Nome</th>
                            </tr>
                            <?
                            while ($row1 = mysqli_fetch_array($res1)) {
                            ?>
                                <tr>
                                    <td align="center">
                                        <a href="javascript:janelamodal('fornecedorfab.php?acao=u&idfornecedorfab=<?= $row1["idfornecedorfab"] ?>&idfornecedor=<?= $arrpessoa['idpessoa'] ?>',283,830)"><?= $row1["nome"] ?></a>
                                    </td>
                                </tr>
                            <?
                            }
                            ?>
                        </table>
                    </div>
                <?
                }
                ?>

                <div class="panel panel-default">
                    <div class="panel-heading" data-toggle="collapse" href="#Produto2">Produtos Vinculados</div>
                    <div class="panel-body collapse" id="Produto2">


                        <?
                        $sql2 = "select p.idprodserv,p.descr,p.codprodserv,f.codforn,f.idprodservforn
					from prodservforn f,prodserv p
					where p.status = 'ATIVO'
					and f.status = 'ATIVO'
					and p.idprodserv = f.idprodserv
					and f.idpessoa =".$arrpessoa['idpessoa']." order by p.descr";
                        $res2 = d::b()->query($sql2) or die("A Consulta dos produtos vinculados falhou :".mysql_error()."<br>Sql:".$sql2);
                        $qtdrowd3 = mysqli_num_rows($res2);

                        $j = 999;
                        if ($qtdrowd3 > 0) {
                        ?>

                            <table class="table table-striped planilha">
                                <tr>
                                    <th align="center">Produto</th>
                                    <th align="center">Código</th>
                                    <th align="center" nowrap>Código do Fornecedor</th>
                                    <th>Retirar</th>
                                </tr>
                                <?

                                while ($row2 = mysqli_fetch_array($res2)) {
                                    $j = $j + 1;
                                ?>
                                    <tr>
                                        <td align="center" nowrap style="font-size: 9px"><a onclick="janelamodal('?_modulo=prodserv&_acao=u&idprodserv=<?= $row2['idprodserv'] ?>')"><?= $row2["descr"] ?></a></td>
                                        <td align="center" style="font-size: 9px"><?= $row2['codprodserv'] ?></td>
                                        <td align="center">
                                            <input name="_<?= $j ?>_u_prodservforn_idprodservforn" size="6" type="hidden" value="<?= $row2["idprodservforn"] ?>">
                                            <input style="font-size: 12px" name="_<?= $j ?>_u_prodservforn_codforn" size="35" type="text" value="<?= $row2["codforn"] ?>">
                                        </td>
                                        <td align="center">
                                            <i class="fa fa-trash  cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('prodservforn',<?= $row2["idprodservforn"] ?>)" alt="Excluir"></i>
                                        </td>
                                    </tr>
                                <?
                                }
                                ?>
                            </table>

                        <?
                        }
                        ?>
                        <table>
                            <tr>
                                <td>Adicionar Produto:</td>
                                <td align="center">
                                    <input type="text" name="prodservfornidprodserv" class="idprodserv" cbvalue placeholder="Informe o produto">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?
            }
 
            if (!empty($arrpessoa['idpessoa']) and $arrpessoa['idtipopessoa'] == 11) {

                $sqlr = "select
                            d.idrotaorigem,d.idrotapara,
                            o.uf,c.cidade,o.codcidade,
                            d.uf as ufpara,cp.cidade as cidadepara ,d.codcidade as codcidadepara,
                            d.obs
                            from rotaorigem o,rotapara d ,nfscidadesiaf c,nfscidadesiaf cp
                            where d.idrotaorigem = o.idrotaorigem
                            and cp.codcidade = d.codcidade
                            and c.codcidade = o.codcidade
                            and d.idpessoa=".$arrpessoa['idpessoa']." order by c.cidade,cidadepara";
                $resr = d::b()->query($sqlr) or die("Erro ao buscar rotas da transportadora sql= ".$sqlr);
                $qtdr = mysqli_num_rows($resr);
                if ($qtdr > 0) {
                ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">Rotas</div>
                        <table class="table table-striped planilha">
                            <tr>
                                <th align="center">Origem</th>
                                <th align="center">Destino</th>
                                <th align="center">Obs.</th>
                                <th></th>
                            </tr>
                            <?
                            while ($rowr = mysqli_fetch_assoc($resr)) {
                            ?>
                                <tr>
                                    <td>
                                        <font color="red"></font> <?= $rowr['cidade'] ?> - <?= $rowr['uf'] ?>
                                    </td>
                                    <td><?= $rowr['cidadepara'] ?> - <?= $rowr['ufpara'] ?></td>
                                    <td><?= $rowr['obs'] ?></td>
                                    <td><a class="fa fa-bars fa-1x cinzaclaro hoverazul btn-lg pointer" onclick="janelamodal('?_modulo=rota&_acao=u&idrotaorigem=<?= $rowr['idrotaorigem'] ?>');"></a></td>
                                </tr>
                            <?
                            }
                            ?>
                        </table>
                    </div>
        </div>
<?
                }
            }
?>

</td>
</tr>
</table>
    </div>

<?
    pessoacontato($arrpessoa); //se for um representante
}

function secretaria($arrpessoa)
{
    global $_acao, $idempresaLinkMatriz;
?>
    <div class="row">
        <div class="col-md-5">
            <div class="panel panel-default">
                <table>
                    <tr>
                        <td align="right" nowrap>Nome Completo</td>
                        <td><input name="_1_<?=$_acao?>_pessoa_razaosocial" type="text" id="razaosocial" size="40" vnulo value="<?= $arrpessoa['razaosocial'] ?>"></td>
                    </tr>
                    <tr>
                        <td align="right">Email</td>
                        <td><input name="_1_<?=$_acao?>_pessoa_email" type="text" size="40" maxlength="" value="<?= $arrpessoa['email'] ?>" vnulo></td>
                    </tr>
                    <tr>
                        <td align="right">Telefone</td>
                        <td>
                            <input style="width: 40px;" name="_1_<?=$_acao?>_pessoa_dddfixo" type="text" size="1" value="<?= $arrpessoa['dddfixo'] ?>" maxlength="2">
                            <input style="width: 100px;" name="_1_<?=$_acao?>_pessoa_telfixo" type="text" size="8" value="<?= $arrpessoa['telfixo'] ?>" maxlength="8">
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Telefone</td>
                        <td>
                            <input style="width: 40px;" style="width: 40px;" name="_1_<?=$_acao?>_pessoa_dddcom" type="text" size="1" value="<?= $arrpessoa['dddcom'] ?>" maxlength="2">
                            <input style="width: 100px;" name="_1_<?=$_acao?>_pessoa_telcom" type="text" size="8" value="<?= $arrpessoa['telcom'] ?>" maxlength="8">
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap>Res. Impresso</td>
                        <td colspan="5">
                            <select name="_1_<?=$_acao?>_pessoa_impresultado" vnulo>
                                <option value=""></option>
                                <? fillselect("select 'S','Sim'
                                        union select 'N','Não'", $arrpessoa['impresultado']); ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td align="right">Observação</td>
                        <td><textarea name="_1_<?=$_acao?>_pessoa_observacaonf" rows="3" cols="30"><?= $arrpessoa['observacaonf'] ?></textarea></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col-md-7">
            <div class="panel panel-default">
                <div class="panel-heading">Histórico</div>

                <table>
                    <tr>
                        <td colspan="10">
                            <a href="javascript:janelamodal('histatendimento.php?acao=u&idpessoa=<?= $arrpessoa['idpessoa'] ?><?= $idempresaLinkMatriz ?>',600,990,100,150)">
                                <font color="Blue" style="font-weight: bold;">Atendimentos</font>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
            <?

            $sqlc = "select * from pessoa where status='ATIVO' and idsecretaria = ".$arrpessoa['idpessoa']." order by nome";
            $resc = d::b()->query($sqlc) or die("Erro ao buscar clientes da secretaria");
            $qtdrowc = mysqli_num_rows($resc);
            if ($qtdrowc > 0) {
            ?>

                <div class="panel panel-default">
                    <div class="panel-heading" data-toggle="collapse" href="#clientevInfo">Clientes Vinculados</div>
                    <table class="table table-striped planilha collapse" id="clientevInfo">
                        <tr>
                            <th align="center" title="RESULTADO">Nome</th>
                            <th></th>
                        </tr>
                        <?
                        while ($rowc = mysqli_fetch_assoc($resc)) {
                        ?>
                            <tr>
                                <td align="left"><?= $rowc['nome'] ?></td>
                                <td><a class="fa fa-bars pointer hoverazul" title="Ficha de Reprodução/Inc." onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $rowc["idpessoa"] ?><?= $idempresaLinkMatriz ?>')"></i></td>
                            </tr>
                        <?
                        }
                        ?>
                    </table>
                <?
            }

                ?>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">Alertas</div>
                    <div class="panel-body ">
                        <table class="table table-striped planilha ">
                            <tr>
                                <td><input id="immsgconf" class="compacto" type="text" cbvalue placeholder="Selecione"></td>
                            </tr>
                        </table>
                        <?= listaAlertas() ?>
                    </div>
                </div>

        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?
            $sql = "select 
            c.idpessoacontato,
			c.idcontato
			,nome
			,concat(dddfixo,'-',telfixo) as tel1
			,concat(dddcel,'-',telcel) as tel2
			, email
			,p.receberes
			,p.receberestodos
			from pessoa p
			,pessoacontato c
			where p.status='ATIVO' 
			and p.idpessoa = c.idcontato
			and c.idpessoa = ".$arrpessoa['idpessoa']." order by nome";
            $res = d::b()->query($sql) or die("A Consulta falhou :".mysql_error()."<br>Sql:".$sql);
            $qtdrowsp = mysqli_num_rows($res);

            ?>

            <div class="panel panel-default">
                <div class="panel-heading" data-toggle="collapse" href="#contatoInfo">Contatos para <?= $arrpessoa['razaosocial'] ?></div>
                <table class="table table-striped planilha collapse" id="contatoInfo">
                    <? if ($qtdrowsp > 0) { ?>
                        <tr class="header">
                            <th align="center" title="RESULTADO">Positivo PDF</th>
                            <th align="center" title="RESULTADO">Positivo LINK</th>
                            <th align="center" title="RESULTADO">Todos PDF</th>
                            <th align="center" title="RESULTADO">Todos LINK</th>
                            <th>Contato</th>
                            <th nowrap>Telefone 1</th>
                            <th nowrap>Telefone 2</th>
                            <th>E-mail</th>
                            <th>Editar</th>
                            <th></th>
                        </tr>

                        <?
                        while ($row = mysqli_fetch_array($res)) {
                        ?>
                            <tr>

                                <td nowrap align="center" title="Resultado positivo PDF">

                                    <? if ($row["receberes"] == "PDF") {
                                        $checked = 'checked';
                                        $recebe = '';
                                    } else {
                                        $checked = '';
                                        $recebe = 'PDF';
                                    }


                                    ?>
                                    <input title="Recebe PDF" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberes(<?= $row["idcontato"] ?>,'<?= $recebe ?>','<?= date("d/m/Y H:i:s"); ?>');">

                                </td>

                                <td nowrap align="center" title="Resultado positivo LINK">
                                    <? if ($row["receberes"] == "LINK") {
                                        $checked = 'checked';
                                        $recebe = '';
                                    } else {
                                        $checked = '';
                                        $recebe = 'LINK';
                                    }

                                    ?>
                                    <input title="Resultado positivo LINK" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberes(<?= $row["idcontato"] ?>,'<?= $recebe ?>','<?= date("d/m/Y H:i:s"); ?>');">

                                </td>

                                <td nowrap align="center" title="Resultado todos PDF">



                                    <? if ($row["receberestodos"] == "PDF") {
                                        $checked = 'checked';
                                        $recebe = '';
                                    } else {
                                        $checked = '';
                                        $recebe = 'PDF';
                                    }


                                    ?>
                                    <input title="Resultado todos PDF" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberestodos(<?= $row["idcontato"] ?>,'<?= $recebe ?>','<?= date("d/m/Y H:i:s"); ?>');">

                                </td>

                                <td nowrap align="center" title="Resultado todos LINK">
                                    <? if ($row["receberestodos"] == "LINK") {
                                        $checked = 'checked';
                                        $recebe = '';
                                    } else {
                                        $checked = '';
                                        $recebe = 'LINK';
                                    }


                                    ?>
                                    <input title="Resultado todos LINK" type="checkbox" <?= $checked ?> name="nameagrupar" onclick="altreceberestodos(<?= $row["idcontato"] ?>,'<?= $recebe ?>','<?= date("d/m/Y H:i:s"); ?>');">
                                </td>

                                <td nowrap><?= $row["nome"] ?></td>
                                <td nowrap><?= $row["tel1"] ?></td>
                                <td nowrap><?= $row["tel2"] ?></td>
                                <td nowrap><?= $row["email"] ?></td>
                                <td><a class="fa fa-bars pointer hoverazul" title="Contato" onclick="janelamodal('?_modulo=pessoa&_acao=u&idpessoa=<?= $row["idcontato"] ?><?= $idempresaLinkMatriz ?>')"></a></td>
                                <td><a class="fa fa-trash  cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluicontato(<?= $row["idpessoacontato"] ?>)" title="Excluir"></a></td>
                            </tr>
                    <?
                        }
                    }
                    ?>
                    <tr>
                        <td>Nome:</td>
                        <td colspan="9">
                            <input type="text" name="pessoacontato" cbvalue="pessoacontato" value="" style="width: 40em;" autocomplete="off">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
<?
} //function secretaria

function finalidade($arrpessoa)
{
    global $arrpessoa;
?>
    <!--Campo Finalidade-->
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading">Finalidade</div>
            <div class="panel-body">
                <?
                $sql1 = "SELECT f.idfinalidadeprodserv,finalidadeprodserv ,p.idfinalidadepessoa
						   FROM finalidadepessoa p left join finalidadeprodserv f on(f.idfinalidadeprodserv= p.idfinalidadeprodserv)
						  WHERE p.idpessoa = '".$arrpessoa['idpessoa']."' ".getidempresa('p.idempresa', 'finalidadeprodserv')."
					   ORDER BY f.finalidadeprodserv;";

                $res1 = d::b()->query($sql1) or die("A Consulta de Finalidade itens falhou :".mysql_error()."<br>Sql:".$sql1);
                $qtdf = mysqli_num_rows($res1);
                $j = 1000000;
                ?>
                <table class="table table-striped planilha">
                    <?

                    while ($row1 = mysqli_fetch_assoc($res1)) {
                        $j++;
                    ?>
                        <tr>
                            <td>
                                <input name="_<?= $j ?>_u_finalidadepessoa_idfinalidadepessoa" type="hidden" value="<?= $row1["idfinalidadepessoa"] ?>">
                                <select name="_<?= $j ?>_u_finalidadepessoa_idfinalidadeprodserv">
                                    <option value=""></option>
                                    <? fillselect("SELECT f.idfinalidadeprodserv,  f.finalidadeprodserv
												   FROM finalidadeprodserv f
												  WHERE  f.status = 'ATIVO' ".getidempresa(' f.idempresa', 'finalidadeprodserv')."
                                                    and not exists (select 1 from finalidadepessoa f2 where f2.idpessoa= ".$arrpessoa['idpessoa']." and f.idfinalidadeprodserv= f2.idfinalidadeprodserv and f2.idfinalidadepessoa!=".$row1["idfinalidadepessoa"]." )
											   ORDER BY  f.finalidadeprodserv;", $row1['idfinalidadeprodserv']); ?>
                                </select>
                            </td>
                            <td align="center">
                                <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('finalidadepessoa',<?= $row1["idfinalidadepessoa"] ?>)" alt="Excluir !"></i>
                            </td>
                        </tr>
                    <?
                    }
                    ?>
                    <tr>
                        <td colspan="5">
                            <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novo('finalidadepessoa')" alt="Inserir novo!"></i>
                            <? if ($qtdf < 1) { ?><span style="color: red;"><b>É NECESSÁRIO CADASTRAR PELO MENOS UMA FINALIDADE.</b></span><? } ?>
                        </td>
                    </tr>
                </table>

            </div>
        </div>
    </div>
<?
} //function finalidade()

function crmvTecnico($arrpessoa)
{
    global $arrpessoa;
?>
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">CRMV</div>
                <div class="panel-body">
                    <?
                    $sql1 = "SELECT idpessoacrmv, 
                                        crmv, 
                                        uf, 
                                        status
                                   FROM pessoacrmv
                                  WHERE idpessoa = '".$arrpessoa['idpessoa']."' ".getidempresa('p.idempresa', 'pessoacrmv')."
                               ORDER BY crmv;";

                    $res1 = d::b()->query($sql1) or die("A Consulta de crmv falhou :".mysql_error()."<br>Sql:".$sql1);
                    $qtdf = mysqli_num_rows($res1);
                    ?>
                    <table class="table table-striped planilha">
                        <tr>
                            <th>CRMV</th>
                            <th>UF</th>
                            <th>Status</th>
                        </tr>
                        <?
                        $cr = 500000;
                        while ($row1 = mysqli_fetch_assoc($res1)) {
                        ?>
                            <tr>
                                <td><input type="text" name="_<?= $cr ?>_u_pessoacrmv_crmv" value="<?= $row1["crmv"] ?>"></td>
                                <td>
                                    <input name="_<?= $cr ?>_u_pessoacrmv_idpessoacrmv" type="hidden" value="<?= $row1["idpessoacrmv"] ?>">
                                    <select name="_<?= $cr ?>_u_pessoacrmv_uf">
                                        <option value=""></option>
                                        <?
                                        $sqltmp =  "SELECT 'AC','AC' UNION
                                                            SELECT 'AL','AL' UNION
                                                            SELECT 'AM','AM' UNION
                                                            SELECT 'AP','AP' UNION
                                                            SELECT 'BA','BA' UNION
                                                            SELECT 'CE','CE' UNION
                                                            SELECT 'DF','DF' UNION
                                                            SELECT 'ES','ES' UNION
                                                            SELECT 'GO','GO' UNION
                                                            SELECT 'MA','MA' UNION
                                                            SELECT 'MG','MG' UNION
                                                            SELECT 'MS','MS' UNION
                                                            SELECT 'MT','MT' UNION
                                                            SELECT 'PA','PA' UNION
                                                            SELECT 'PB','PB' UNION
                                                            SELECT 'PE','PE' UNION
                                                            SELECT 'PI','PI' UNION
                                                            SELECT 'PR','PR' UNION
                                                            SELECT 'RJ','RJ' UNION
                                                            SELECT 'RN','RN' UNION
                                                            SELECT 'RO','RO' UNION
                                                            SELECT 'RR','RR' UNION
                                                            SELECT 'RS','RS' UNION
                                                            SELECT 'SC','SC' UNION
                                                            SELECT 'SE','SE' UNION
                                                            SELECT 'SP','SP' UNION
                                                            SELECT 'TO','TO' UNION
                                                            SELECT 'EX','EX'";
                                        fillselect($sqltmp, $row1['uf']); ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="_<?= $cr ?>_u_pessoacrmv_status" id="status" vnulo>
                                        <? fillselect("SELECT 'ATIVO','Ativo' UNION SELECT 'INATIVO','Inativo'  ", $row1['status']); ?>
                                    </select>
                                </td>
                                <td align="center">
                                    <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" onclick="excluir('pessoacrmv',<?= $row1["idpessoacrmv"] ?>)" alt="Excluir !"></i>
                                </td>
                            </tr>
                        <?
                            $cr++;
                        } ?>
                        <tr>
                            <td colspan="5">
                                <i class="fa fa-plus-circle fa-1x  cinzaclaro hoververde btn-lg pointer" onclick="novo('pessoacrmv')" alt="Inserir novo!"></i>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
<?
} //function crmv()

?>

<div id="usuariosenha" style="display: none">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <table>
                        <tr>
                            <td>Usuário</td>
                            <td>
                                <? if (empty($_1_u_pessoa_usuario)) { //deixar informar somente se nao tiver sido salvo e a variavel da pagina nao existir
                                ?>
                                    <input name="pessoa_usuario" id="username" type="text" size="15" value="<?= $_1_u_pessoa_usuario ?>" maxlength="30" autocomplete="off">
                                    <span id="msgbox" style="display:none"></span>
                                <?
                                } else {
                                ?>
                                    <label style="font-size:14px;"><?= $_1_u_pessoa_usuario ?></label>
                                <? } ?>
                            </td>
                        </tr>
                        <tr>
                            <!-- MAF: LGPD: Nao é permitido que funcionarios alterem a senha de outros usuarios
                    <td ><font color="red">Senha</font></td> 
                    <td><input  name="pessoa_senha"	type="password"	size="15" value="" vnulo ></td>
                    <td></td>
                </tr>
                <tr> 
                    <td ><font color="red">Confirmação</font></td> 
                    <td><input name="confirmacaosenha" type="password" size="15" value="" vnulo ></td> 
-->
                            <? if (empty($_1_u_pessoa_usuario)) { ?>
                                <div class="alert alert-warning"><i class="info-circle"></i>Atenção: O procedimento de criação de senha foi alterado:
                                    <br />O usuário deve ser salvo primeiro, e após isso, deve-se clicar novamente para acessar esta funcionalidade.
                                    <br />Com o usuário já criado, deverá ser selecionada a opção de "<b>Enviar nova senha por email</b>"
                                </div>
                            <? } ?>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?
function getJSetorvinc()
{
    global $JSON, $_1_u_pessoa_idpessoa;
    $s = "select 
                    a.idsgsetor
                    ,a.setor				
                    from sgsetor a
                    where  not exists(
                            SELECT 1
                            FROM pessoaobjeto v
                            where  v.idpessoa= ".$_1_u_pessoa_idpessoa." 						
                                and v.idobjeto = a.idsgsetor	
								and v.tipoobjeto = 'sgsetor'								
                        ) ".getidempresa('idempresa', 'sgsetor')."
                    order by a.setor desc";

    $rts = d::b()->query($s) or die("getJSetorvinc: ".mysql_error(d::b()));

    $arrtmp = array();
    $i = 0;
    while ($r = mysqli_fetch_assoc($rts)) {
        $arrtmp[$i]["value"] = $r["idsgsetor"];
        $arrtmp[$i]["label"] = $r["setor"];
        $i++;
    }

    return $JSON->encode($arrtmp);
}

$jSgsetorvinc = "null";

if (!empty($_1_u_pessoa_idpessoa)) {
    $jSgsetorvinc = getJSetorvinc();
}

function getJimmsgconf()
{
    global $JSON, $_1_u_pessoa_idpessoa;

    $s = "select c.idimmsgconf,c.titulo,d.*
        from immsgconf c,immsgconfdest d,sgsetor s,pessoa p
        where -- c.status='ATIVO' and
            d.idimmsgconf =c.idimmsgconf
        and d.objeto = 'sgsetor'
        and d.idobjeto = s.idsgsetor
        and s.idtipopessoa = p.idtipopessoa
        and not exists (select 1 from immsgconfdest d2 where d2.idimmsgconf = c.idimmsgconf and d2.objeto='pessoa'  and d2.idobjeto=".$_1_u_pessoa_idpessoa." )
        and  p.idpessoa=".$_1_u_pessoa_idpessoa." ".getidempresa('c.idempresa', 'idimmsgconf')."     
        order by c.titulo";

    $rts = d::b()->query($s) or die("getJimmsgconf: ".mysql_error(d::b()));

    $arrtmp = array();
    $i = 0;
    while ($r = mysqli_fetch_assoc($rts)) {
        $arrtmp[$i]["value"] = $r["idimmsgconf"];
        $arrtmp[$i]["label"] = $r["titulo"];
        $i++;
    }
    return $JSON->encode($arrtmp);
}
$jImmsgconf = "null";
if (!empty($_1_u_pessoa_idpessoa)) {
    $jImmsgconf = getJimmsgconf();
}

?>
<script>
    jImmsgconf = <?= $jImmsgconf ?>;
    _idtipopessoa = '<?= $_1_u_pessoa_idtipopessoa ?>';
    const idPessoa = <?= $_1_u_pessoa_idpessoa ?? "''" ?>,
        idPessoaLogada = <?= $_SESSION["SESSAO"]["IDPESSOA"] ?>;

    // Carregar portifolio
    $("#input-portifolio").dropzone({
        url: "form/_arquivo.php",
        idObjeto: idPessoa,
        tipoObjeto: 'portifolio',
        tipoArquivo: 'portifolio',
        idPessoaLogada,
        acceptedFiles: '.pdf',
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
        }
    });

    //Autocomplete de Setores vinculados
    $("#immsgconf").autocomplete({
        source: jImmsgconf,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                lbItem = item.label;
                return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
            };
        },
        select: function(event, ui) {
            CB.post({
                objetos: {
                    "_x_i_immsgconfdest_idobjeto": $(":input[name=_1_" + CB.acao + "_pessoa_idpessoa]").val(),
                    "_x_i_immsgconfdest_idimmsgconf": ui.item.value,
                    "_x_i_immsgconfdest_objeto": 'pessoa'
                },
                parcial: true
            });
        }
    });

    //Monta o Select e Adiciona Novo Contato Representação
    <?
    function getListaContatosRepresentacao()
    {
        global $JSON;
        $SQLlistaContatoRepresentacao = "select idpessoa, nome from pessoa where idtipopessoa = 15 and status = 'ATIVO'";
        $rContatoRepresentacao = d::b()->query($SQLlistaContatoRepresentacao) or die("A Consulta da lista de Contatos Representação falhou : ".mysql_error(d::b())."<p>SQL:".$SQLlistaContatoRepresentacao);
        $arrContRepresentacao = array();
        $i = 0;
        while ($ra = mysqli_fetch_assoc($rContatoRepresentacao)) {
            $arrContRepresentacao[$i]['value'] = $ra['idpessoa'];
            $arrContRepresentacao[$i]['label'] = $ra["nome"];
            $i++;
        }
        $arrContRepresentacaoJSON = json_encode($arrContRepresentacao);
        return $arrContRepresentacaoJSON;
    }
    $arrContRepresentacaoJSON = getListaContatosRepresentacao();
    ?>

    <? if (!empty($arrContRepresentacaoJSON)) { ?>
        arrContRepresentacao = <?= $arrContRepresentacaoJSON ?>; // autocomplete cliente

        //mapear autocomplete de clientes

        //autocomplete de clientes
        $("[name=idpessoacontatorepresentacao]").autocomplete({
            source: arrContRepresentacao,
            delay: 0,
            select: function(event, ui) {
                insericontatorepresentacao(ui.item.value);
            },
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);

                };
            }
        });
    <? } ?>

    <? if (!empty($jCli)) { ?>
        jCli = <?= $jCli ?>; // autocomplete cliente

        //mapear autocomplete de clientes
        jCli = jQuery.map(jCli, function(o, id) {
            return {
                "label": o.nome,
                value: id + ""
            }
        });

        //autocomplete de clientes
        $("[name=pessoacontatoidpessoa]").autocomplete({
            source: jCli,
            delay: 0,
            select: function(event, ui) {
                insericontatocliente(ui.item.value);
            },
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);

                };
            }
        });
    <? } //if(!empty($jCli)){ 



    if (!empty($jCont)) { ?>
        jCont = <?= $jCont ?>; // autocomplete cliente

        //mapear autocomplete de clientes
        jCont = jQuery.map(jCont, function(o, id) {
            return {
                "label": o.nome,
                value: id + ""
            }
        });

        //autocomplete de clientes
        $("[name=pessoacontato]").autocomplete({
            source: jCont,
            delay: 0,
            select: function(event, ui) {
                insericontato(ui.item.value);
            },
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);

                };
            }
        });
    <? } //if(!empty($jCont)){
    if (!empty($jProd)) { ?>
        jProd = <?= $jProd ?>;
        jProd = jQuery.map(jProd, function(o, id) {
            return {
                "label": o.descr,
                value: id,
                "codprodserv": o.codprodserv
            }
        });

        $("[name*=prodservfornidprodserv]").autocomplete({
            source: jProd,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    return $('<li>').append("<a>" + item.label + "<span class='cinzaclaro'> " + item.codprodserv + "</span></a>").appendTo(ul);
                };
            },
            select: function(event, ui) {
                vincprod($("[name=prodservfornidprodserv]").cbval());
            }
        });
    <? } //if(!empty($jProd)){
    ?>

    function usauriosenha() {
        var strCabecalho = "</strong>Usuário e Senha <button id='cbSalvar' type='button' class='btn btn-danger btn-xs' onclick='susuariosenha();'><i class='fa fa-circle'></i>Salvar</button></strong>";
        $("#cbModalTitulo").html((strCabecalho));

        var htmloriginal = $("#usuariosenha").html();
        var objfrm = $(htmloriginal);


        //objfrm.find("[name=pessoa_senha]").attr("vpwd1", "");
        objfrm.find("[name=pessoa_usuario]").attr("name", "_1_u_pessoa_usuario");
        objfrm.find("[name=pessoa_senha]").attr("name", "_1_u_pessoa_senha");
        objfrm.find("[name=confirmacaosenha]").attr("name", "confirmacao_senha");

        objfrm.find("[name=_1_u_pessoa_senha]").attr("vpwd1", "");
        objfrm.find("[name=confirmacao_senha]").attr("vpwd2", "");
        objfrm.find("[name=pessoa_usuario]").attr("vnulo", "");

        $("#cbModalCorpo").html(objfrm.html());
        $('#cbModal').modal('show');

    }

    jSgsetorvinc = <?= $jSgsetorvinc ?>;
    //Autocomplete de Setores vinculados
    $("#sgsetorvinc").autocomplete({
        source: jSgsetorvinc,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {

                lbItem = item.label;

                return $('<li>')
                    .append('<a>' + lbItem + '</a>')
                    .appendTo(ul);
            };
        },
        select: function(event, ui) {
            CB.post({
                objetos: {
                    "_x_i_pessoaobjeto_idpessoa": $(":input[name=_1_" + CB.acao + "_pessoa_idpessoa]").val(),
                    "_x_i_pessoaobjeto_idobjeto": ui.item.value,
                    "_x_i_pessoaobjeto_tipoobjeto": 'SETOR'
                },
                parcial: true
            });
        }
    });

    function insericontato(inid) {

        CB.post({
            objetos: "_x_i_pessoacontato_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_pessoacontato_idcontato=" + inid,
            parcial: true
        });
    }

    function insericontatocliente(inid) {

        CB.post({
            objetos: "_x_i_pessoacontato_idcontato=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_pessoacontato_idpessoa=" + inid,
            parcial: true
        });
    }

    function insericontatorepresentacao(inid) {

        CB.post({
            objetos: "_x_i_pessoacontato_idcontato=" + inid + "&_x_i_pessoacontato_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val(),
            parcial: true
        });
    }

    function excluicontato(inid) {
        if (confirm("Deseja retirar o contato?")) {
            CB.post({
                objetos: "_x_d_pessoacontato_idpessoacontato=" + inid,
                parcial: true
            });
        }
    }

    function altpessoacontatoemails(id, campo, val) {
        CB.post({
            objetos: "_x_u_pessoacontato_idpessoacontato=" + id + "&_x_u_pessoacontato_" + campo + "=" + val,
            parcial: true
        });
    }

    function altreceberespessoacontato(inid, val) {
        CB.post({
            objetos: "_x_u_pessoacontato_idpessoacontato=" + inid + "&_x_u_pessoacontato_receberes=" + val,
            parcial: true
        });

    }

    function altreceberestodospessoacontato(inid, val) {
        CB.post({
            objetos: "_x_u_pessoacontato_idpessoacontato=" + inid + "&_x_u_pessoacontato_receberestodos=" + val,
            parcial: true
        });

    }

    function altviagem(inid, val) {
        CB.post({
            objetos: "_x_u_pessoacontato_idpessoacontato=" + inid + "&_x_u_pessoacontato_viagem=" + val,
            parcial: true
        });
    }

    function susuariosenha() {

        CB.post({
            objetos: '',
            posPost: function(resp, status, ajax) {
                if (status = "success") {
                    $("#cbModalCorpo").html("");
                    $('#cbModal').modal('hide');
                } else {
                    alert(resp);
                }
            }
        });
    }

    function vincprod(vid) {
        CB.post({
            objetos: "_x_i_prodservforn_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_prodservforn_idprodserv=" + vid,
            parcial: true
        });
    }


    function contrato(vthis) {
        //$(vthis).val();
        CB.post({
            objetos: "_x_i_contratopessoa_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_contratopessoa_idcontrato=" + $(vthis).val(),
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

    function altreceberespcontato(inid, val, vdt) {

        CB.post({
            objetos: "_x_u_pessoacontato_idpessoacontato=" + inid + "&_x_u_pessoacontato_emailresultado=" + val + "&_x_u_pessoacontato_emailresultadodata=" + vdt,
            parcial: true
        });

    }

    function altreceberes(inid, val, vdt) {

        CB.post({
            objetos: "_x_u_pessoa_idpessoa=" + inid + "&_x_u_pessoa_receberes=" + val + "&_x_u_pessoa_receberesdata=" + vdt,
            parcial: true
        });

    }

    function altreceberestodos(inid, val, vdt) {

        CB.post({
            objetos: "_x_u_pessoa_idpessoa=" + inid + "&_x_u_pessoa_receberestodos=" + val + "&_x_u_pessoa_receberesdata=" + vdt,
            parcial: true
        });

    }

    function novo(inobj) {
        CB.post({
            objetos: "_x_i_" + inobj + "_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val(),
            parcial: true
        });

    }

    function novopessoaobj(inobj, tipoobj) {

        CB.post({
            objetos: "&_x_i_" + inobj + "_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_" + inobj + "_tipoobjeto=" + tipoobj,
            parcial: true
        });

    }

    function alttipocontato(incampo, inval, vthis) {
        if (inval == 'N') {
            $(vthis).addClass('fa-square-o').removeClass('fa-check-square-o');
            CB.post({
                objetos: "_x_d_plantelobjeto_idplantelobjeto=" + incampo,
                parcial: true
            });
        } else if (inval == 'Y') {
            $(vthis).addClass('fa-check-square-o').removeClass('fa-square-o');
            CB.post({
                objetos: "_x_i_plantelobjeto_idobjeto=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_plantelobjeto_idplantel=" + incampo + "&_x_i_plantelobjeto_tipoobjeto=pessoa",
                parcial: true
            });
        } else {
            alert("Parâmetro da função alttipocontato inválido");
            console.log("Valor " + inval + " para o campo inval inválido");
        }
    }

    function inserirPlantelEmpresa(incampo, idplantelobjeto) {
        var idplantel = incampo.value;
        CB.post({
            objetos: "_x_i_plantelobjeto_idobjeto=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_plantelobjeto_idplantel=" + idplantel + "&_x_i_plantelobjeto_tipoobjeto=pessoa"
        });
    }

    function deletarPlantelEmpresa(idplantelobjeto) {
        CB.post({
            objetos: "_x_d_plantelobjeto_idplantelobjeto=" + idplantelobjeto,
            parcial: true
        });
    }

    function novoobjeto(inobj) {
        CB.post({
            objetos: "_x_i_" + inobj + "_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val(),
            parcial: true
        });

    }

    function retiraund(inidunidadeobjeto) {
        CB.post({
            objetos: "_x_d_unidadeobjeto_idunidadeobjeto=" + inidunidadeobjeto,
            parcial: true
        });
    }

    function inseriund(inidund) {
        CB.post({
            objetos: "_x_i_unidadeobjeto_idobjeto=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_i_unidadeobjeto_idunidade=" + inidund + "&_x_i_unidadeobjeto_tipoobjeto=pessoa",
            parcial: true
        });
    }

    function AlteraStatus(vthis) {

        var idimmsgconfdest = $(vthis).attr('idimmsgconfdest');
        var status = $(vthis).attr('status');
        var cor, novacor;

        if (status == 'ATIVO') {
            cor = 'verde hoververde';
            novacor = 'vermelho hoververmelho';
            CB.post({
                objetos: "_x_u_immsgconfdest_idimmsgconfdest=" + idimmsgconfdest + "&_x_u_immsgconfdest_status=INATIVO",
                parcial: true,
                msgSalvo: "Status Alterado",
                posPost: function() {
                    $(vthis).removeClass(cor);
                    $(vthis).addClass(novacor);
                }
            });

        } else {

            cor = 'vermelho hoververmelho';
            novacor = 'verde hoververde';
            CB.post({
                objetos: "_x_u_immsgconfdest_idimmsgconfdest=" + idimmsgconfdest + "&_x_u_immsgconfdest_status=ATIVO",
                parcial: true,
                msgSalvo: "Status Alterado",
                posPost: function() {
                    $(vthis).removeClass(cor);
                    $(vthis).addClass(novacor);
                }
            });
        }
    }

    <? if (!empty($jRep)) { ?>
        jRep = <?= $jRep ?>; // autocomplete cliente

        //mapear autocomplete de clientes
        jRep = jQuery.map(jRep, function(o, id) {
            return {
                "label": o.nome,
                value: id + ""
            }
        });

        //autocomplete de clientes
        $("[name=pessoarepresentante]").autocomplete({
            source: jRep,
            delay: 0,
            select: function(event, ui) {
                insericontato(ui.item.value);
            },
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);

                };
            }
        });
    <? } ?>

    $(".prodservprogramacao").click(function() {
        var idpessoa = $("[name=_1_u_pessoa_idpessoa]").val();
        CB.modal({
            url: "?_modulo=prodservprogramacao&_acao=u&idpessoa=" + idpessoa,
            header: "Programação de Venda"
        });
    });
    if ($("[name=_1_u_pessoa_idpessoa]").val()) {
        $("#arquivosanexos").dropzone({
            url: "form/_arquivo.php",
            idObjeto: $("[name=_1_u_pessoa_idpessoa]").val(),
            tipoObjeto: 'fornecedor',
            tipoArquivo: 'ANEXO',
            idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
            //,caminho: "../inc/nfe/sefaz4/certs/"
        });
    }

    if ($("[name=_1_u_pessoa_idpessoa]").val()) {
        $("#uploadarquivos").dropzone({
            url: "form/_arquivo.php",
            idObjeto: $("[name=_1_u_pessoa_idpessoa]").val(),
            tipoObjeto: 'pessoa',
            tipoArquivo: 'ANEXO',
            idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>'
        });
    }

    const _acao = '<?= $_GET["_acao"] ?>';
    $(document).ready(function() {
        //Verifica disponibilidade de cpf/cnpf

        $("#validacpfcnjp").blur(function() {
            var cpfcnpj = $("#validacpfcnjp").val();
            if (cpfcnpj != "" && cpfcnpj != "0" && _acao != '' && _acao != 'u') {

                // GVT - 25/06/2020 - Função assíncrona para verificar a disponibilidade do cpf/cnpj
                //                    retorno: 0 = CPF/CNPJ está disponível
                //                    retorno: 1 = CPF/CNPJ já existe no banco de dados
                //                    retorno: -1 = Entrada inválida
                verificacpfcnpj(cpfcnpj).then(retorno => {
                    switch (retorno) {
                        case "0":
                            $("#validacpfcnjp").css("border", "2px solid green");
                            break;
                        case "1":
                            if (!confirm("CPF/CNPJ já cadastrado. Deseja continuar?")) {
                                $("#validacpfcnjp").val("");
                            } else {
                                $("#validacpfcnjp").css("border", "2px solid red");
                                $("#validacpfcnjp").attr("title", "CPF/CNPJ já existente");
                            }
                            break;
                        case "-1":
                            console.warn("Verifique o valor de retorno da função verificacpfcnpj");
                            $("#validacpfcnjp").css("border", "2px solid yellow");
                            break;
                        default:
                            console.warn("Verifique o valor de retorno da função verificacpfcnpj");
                            $("#validacpfcnjp").css("border", "2px solid yellow");
                            break;
                    }
                }).catch(e => {
                    console.warn("Verfique a PROMISSE da função verificacpfcnpj: " + e);
                });
            } else {
                $("#validacpfcnjp").css("border", "");
                $("#validacpfcnjp").attr("title", "");
            }
        });
    });

    function altvendadireta(inval) {
        CB.post({
            objetos: "_x_u_pessoa_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_x_u_pessoa_vendadireta=" + inval
        });
    }

    <? if (!empty($_1_u_pessoa_idpessoa)) { ?>
        if ($("[name=_1_u_pessoa_idpessoa]").val()) {

            $("#imagemassinatura").dropzone({
                url: "form/_arquivo.php",
                idObjeto: $("[name=_1_u_pessoa_idpessoa]").val(),
                tipoObjeto: 'pessoa',
                tipoArquivo: 'ASSINATURA',
                idPessoaLogada: '<?= $_SESSION["SESSAO"]["IDPESSOA"] ?>',
                maxFiles: 1,
                caminho: 'upload/cert/'
            });
        }

    <? } ?>

    <? if (!empty($_1_u_pessoa_idpessoa) and !empty($_1_u_pessoa_webmailemail)) { ?>

        function deletaidentidade(inassinatura, inpessoa) {
            if (confirm("Deseja realmente excluir essa identidade de e-mail?")) {
                var obj = {};

                inpessoa.forEach((o, i) => {
                    obj[`_wp${i}_d_webmailassinaturaobjeto_idwebmailassinaturaobjeto`] = o;
                });

                obj["_w_d_webmailassinatura_idwebmailassinatura"] = inassinatura

                CB.post({
                    objetos: obj,
                    parcial: true
                });
            }
        }

        var jAssFunc = <?= $jAssFunc ?> || 0;

        if (jAssFunc != 0) {
            $("#assinaturasfunc").autocomplete({
                source: jAssFunc,
                delay: 0,
                create: function() {
                    $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                        return $('<li>').append("<a>" + item.email + "</a>").appendTo(ul);

                    };
                },
                select: function(event, ui) {
                    CB.post({
                        objetos: {
                            "_x_i_webmailassinaturaobjeto_idobjeto": $("[name='_1_u_pessoa_idpessoa']").val(),
                            "_x_i_webmailassinaturaobjeto_tipoobjeto": 'pessoa',
                            "_x_i_webmailassinaturaobjeto_idwebmailassinatura": ui.item.idwebmailassinatura,
                            "_x_i_webmailassinaturaobjeto_idwebmailassinaturatemplate": ui.item.idwebmailassinaturatemplate,
                            "_x_i_webmailassinaturaobjeto_tipo": ui.item.tipo
                        },
                        parcial: true
                    });
                }
            });
        }

        var jAssFunc1 = <?= $jAssFunc1 ?> || 0;

        if (jAssFunc1 != 0) {
            $("#assinaturasgrupo").autocomplete({
                source: jAssFunc1,
                delay: 0,
                create: function() {
                    $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                        return $('<li>').append("<a>" + item.email + "</a>").appendTo(ul);

                    };
                },
                select: function(event, ui) {
                    CB.post({
                        objetos: {
                            "_x_i_webmailassinaturaobjeto_idobjeto": $("[name='_1_u_pessoa_idpessoa']").val(),
                            "_x_i_webmailassinaturaobjeto_tipoobjeto": 'pessoa',
                            "_x_i_webmailassinaturaobjeto_idwebmailassinatura": ui.item.idwebmailassinatura,
                            "_x_i_webmailassinaturaobjeto_idwebmailassinaturatemplate": ui.item.idwebmailassinaturatemplate,
                            "_x_i_webmailassinaturaobjeto_tipo": ui.item.tipo
                        },
                        parcial: true
                    });
                }
            });
        }

        var jAssFunc2 = <?= $jAssFunc2 ?> || 0;

        if (jAssFunc2 != 0) {
            $("#outrasassinaturas").autocomplete({
                source: jAssFunc2,
                delay: 0,
                create: function() {
                    $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                        if (item.principal == 'Y') {
                            return $('<li>').append("<a>" + item.descricao + "</a><i class='fa fa-star' title='Template Principal da Empresa'></i>").appendTo(ul);
                        } else {
                            return $('<li>').append("<a>" + item.descricao + "</a>").appendTo(ul);
                        }

                    };
                },
                select: function(event, ui) {
                    $.ajax({
                        type: "POST",
                        url: "ajax/replaceassinaturaemail.php",
                        data: {
                            id: '<?= $_1_u_pessoa_idpessoa ?>' || 0,
                            tipo: 'PESSOA',
                            idtemplate: ui.item.idwebmailassinaturatemplate,
                            template: ui.item.htmltemplate
                        },
                        success: function(data, textStatus, jqXHR) {

                            if (jqXHR.getResponseHeader('X-CB-RESPOSTA') == 'id') {
                                var aux = $("<div>" + data + "</div>");
                                $("body").append(aux.find("#_temp").html());
                                var idwebmailassinaturaobjeto = jqXHR.getResponseHeader('idwebmailassinaturaobjeto');
                                setTimeout(async function() {
                                    try {
                                        let dataUrl = await domtoimage.toPng($("#_template").get(0))
                                        let img = new Image();
                                        img.src = dataUrl;
                                        aux.find("#_temp").html(img);
                                        $("#_template").remove();
                                        CB.post({
                                            urlArquivo: 'ajax/replaceassinaturaemail.php?salvar=Y',
                                            refresh: 'refresh',
                                            objetos: {
                                                idwebmailassinaturaobjeto: idwebmailassinaturaobjeto,
                                                htmlassinatura: aux.html()
                                            },
                                            posPost: function(data, texto, jqXHR) {
                                                let resp = JSON.parse(data);
                                                if (resp["erro"]) {
                                                    alert(resp["erro"])
                                                }
                                            }
                                        });
                                    } catch (error) {
                                        console.error('oops, something went wrong!', error);
                                        $("#_template").remove(); //mudar selector
                                    }

                                }, 500);
                            }
                        },

                        error: function(objxmlreq) {
                            alertErro('Erro:<br>' + objxmlreq.status);
                        }
                    });

                }
            });
        }

        <? if (empty($arrass)) {
            $arrass = 0;
        } ?>
        var arrass = <?= $arrass ?>;

        if (arrass != 0) {
            function showWebmailAssinatura(vid) {
                CB.modal({
                    titulo: "Assinatura Funcionário",
                    corpo: arrass[vid],
                    classe: "sessenta"
                });
            }
        }

        function delWebmailAssinatura(vid) {

            CB.post({
                objetos: {
                    "_x_d_webmailassinaturaobjeto_idwebmailassinaturaobjeto": vid
                },
                parcial: true
            });
        }

        <? if (empty($arrass1)) {
            $arrass1 = 0;
        } ?>
        var arrass1 = <?= $arrass1 ?>;

        if (arrass1 != 0) {
            function showWebmailAssinatura1(vid) {
                CB.modal({
                    titulo: "Assinatura Grupo de E-mail",
                    corpo: arrass1[vid],
                    classe: "sessenta"
                });
            }
        }

        function delWebmailAssinatura1(vid) {

            CB.post({
                objetos: {
                    "_x_d_webmailassinaturaobjeto_idwebmailassinaturaobjeto": vid
                },
                parcial: true
            });
        }

        function criarAssinaturaCampos() {
            CB.post({
                objetos: {
                    "_Ncampos_i_assinaturaemailcampos_idobjeto": $(":input[name=_1_" + CB.acao + "_pessoa_idpessoa]").val(),
                    "_Ncampos_i_assinaturaemailcampos_tipoobjeto": 'COLABORADOR'
                },
                parcial: true
            });
        }

    <? } ?>


    //# sourceURL=<?= $_SERVER["SCRIPT_NAME"] ?>_rodape


    $(document).ready(function() {
        checkRqQualificacao();
    });

    function rqQualificacao() {
        let value = $('#rqqualificacao option').filter(':selected').val();
        let certanalise = $('#certanalise option').filter(':selected').val();
        let idpessoa = '<?= $_1_u_pessoa_idpessoa ?>';

        if (value == 'NÃO') {
            $('#localInfo1').css('display', 'none')
        } else {
            $('#localInfo1').css('display', '')
        }

        CB.post({
            objetos: {
                "_1_u_pessoa_rqqualificacao": value,
                "_1_u_pessoa_certanalise": certanalise,
                "_1_u_pessoa_idpessoa": idpessoa

            },
            parcial: true
        });
    }

    function checkRqQualificacao() {
        let value = $('#rqqualificacao option').filter(':selected').val();
        if (value == 'NÃO') {
            $('#localInfo1').css('display', 'none')
        } else {
            $('#localInfo1').css('display', '')
        }
    }

    const idpessoa = '<?= $_1_u_pessoa_idpessoa ?>';

    function criaDocumento() {
        let titulo = $('#titledoc').val();
        let tipo = $('#typedoc').val();
        let criadopor = '<?= $_SESSION['SESSAO']['USUARIO'] ?>';
        let idregistro = '<?= geraRegistrosgdoc('avaliacao') ?>';
        if (titulo && tipo) {
            CB.post({
                objetos: {
                    "_1_i_sgdoc_titulo": titulo,
                    "_1_i_sgdoc_idsgdoctipo": tipo,
                    "_1_i_sgdoc_criadopor": criadopor,
                    "_1_i_sgdoc_versao": "0",
                    "_1_i_sgdoc_revisao": "0",
                    "_1_i_sgdoc_status": "AGUARDANDO",
                    "_1_i_sgdoc_idpessoa": idpessoa,
                    "_1_i_sgdoc_idsgdoc": "",
                    "_1_i_sgdoc_criadopor": "",
                    "_1_i_sgdoc_idsgdoccopia": "",
                    "_1_i_sgdoc_idregistro": idregistro
                },
                parcial: true
            });
        } else {
            alertAtencao('O campo título e o campo tipo é obrigatório!');
            $('#titledoc').focus();
        }
    }

    CB.on('prePost', function(inParam) {
        if ($('.divisao_negocio i.fa-check-square-o').length == 0 && _idtipopessoa == 8) {
            alert('É necessário selecionar a Unidade de Negócio para preenchimento de Clientes relacionados ao Contato Fornecedor.');
            return;
        }
    });

    const areasDisponiveisParaVinculo = <?= json_encode($areasDisponiveisParaVinculo) ?>;

    $("#areas").autocomplete({
        source: areasDisponiveisParaVinculo,
        delay: 0,
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {

                return $('<li>')
                    .append('<a>' + item.label + '</a>')
                    .appendTo(ul);
            };
        },
        select: function(event, ui) {
            CB.post({
                objetos: {
                    "_x_i_pessoaobjeto_idobjeto": ui.item.value,
                    "_x_i_pessoaobjeto_idpessoa": idpessoa,
                    "_x_i_pessoaobjeto_tipoobjeto": 'arearepresentante',
                    "_x_i_pessoaobjeto_responsavel": 'N'
                },
                parcial: true
            });
        }
    });

    function removerVinculoArea(idpessoaobjeto) {
        CB.post({
            objetos: {
                _x_d_pessoaobjeto_idpessoaobjeto: idpessoaobjeto
            },
            parcial: true
        })
    }


    var jPr = <?= $jPr ?> || 0;
    //mapear autocomplete de 
    jPr = jQuery.map(jPr, function(o, id) {
        return {
            "label": o.rotulo,
            value: id + ""
        }
    });

    //autocomplete de 
    $("[name*=preferencia_idprodservformula]").autocomplete({
        source: jPr,
        delay: 0,
        select: function() {
            console.log($(this).cbval());
            inserirformula($(this).cbval());
        },
        create: function() {
            $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $('<li>').append("<a>" + item.label + "</a>").appendTo(ul);
            };
        }
    });
    // FIM autocomplete 

    function inserirformula(inid) {
        CB.post({
            objetos: "_1_i_prodservformulapref_idpessoa=" + $("[name=_1_u_pessoa_idpessoa]").val() + "&_1_i_prodservformulapref_idprodservformula=" + inid,
            parcial: true
        });
    }

    function retirarformula(inid) {

        CB.post({
            objetos: "_1_d_prodservformulapref_idprodservformulapref=" + inid,
            parcial: true
        });

    }

    function atualizaformula(inid, vthis) {

        CB.post({
            objetos: "_1_u_prodservformulapref_idprodservformulapref=" + inid + "&_1_u_prodservformulapref_idprodserv=" + $(vthis).val(),
            parcial: true
        })

    }

    function removerPortifolio(idAquivo) {
        CB.post({
            objetos: {
                _1_d_arquivo_idarquivo: idAquivo,
                parcial: true
            },
        });
    }
</script>
<script src="inc/js/dom-to-image/dom-to-image.min.js"></script>
<?
require_once '../inc/php/readonly.php';
?>