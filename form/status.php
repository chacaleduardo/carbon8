<?
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");

$_acao = $_GET['_acao'];

if ($_POST) {
	include_once("../inc/php/cbpost.php");
}

$pagvaltabela = "fluxo";
$pagvalcampos = array(
	"idfluxo" => "pk"
);

/*
* $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
*/
$pagsql = "SELECT * FROM $pagvaltabela WHERE idfluxo = '#pkid'";
/*
* controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
*/
include_once("../inc/php/controlevariaveisgetpost.php");

require_once(__DIR__."/controllers/fluxo_controller.php");

function listaSgsetor2()
{
    global $_1_u_fluxo_idfluxo;           
    $s = "SELECT d.idfluxoobjeto,
                 s.grupo,       
                 s.idimgrupo,
                 d.criadopor,
                 d.criadoem,
                 s.status,
                 e.sigla
            FROM fluxoobjeto d,
                 imgrupo s,
                 empresa e
           WHERE s.idimgrupo = d.idobjeto
             AND e.idempresa = s.idempresa
             AND d.tipoobjeto = 'imgrupo'
             AND d.tipo = 'CRIADOR'
             AND d.idfluxo = ".$_1_u_fluxo_idfluxo."
        ORDER BY e.idempresa, s.grupo";

    $rts = d::b()->query($s) or die("listaSgsetor2: ". mysql_error(d::b()));

    while ($r = mysqli_fetch_assoc($rts)) 
    {
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
        if ($r["status"] == 'ATIVO'){ $cor = 'verde hoververde'; }else{ $cor = 'vermelho hoververmelho';}
        echo "  <tr>
                    <td style='min-width: 10px;' id='statuses'>
                        <span class='circle button-blue'></span>
                    </td>
                    <td title='$title'>
                        {$r['sigla']} - {$r['grupo']}
                    </td>
                    <td>
                        <i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='retiraeventotiporesp({$r['idfluxoobjeto']})' title='Excluir!'></i>
                    </td>
                    <td>
                        <a href='?_modulo=imgrupo&_acao=u&idimgrupo={$r['idimgrupo']}' target='_blank'>
                            <i class='fa fa-bars pointer' title='Editar grupo'></i>
                        </a>
                    </td>
                </tr>";
    }
}

function listaPessoa2()
{   
    global $_1_u_fluxo_idfluxo;
    $s = "SELECT d.idfluxoobjeto,
                ifnull(s.nomecurto,s.nome)AS nome,
                 s.idpessoa,
                 d.criadopor,
                 d.criadoem,
                 s.status,
                 e.sigla
            FROM fluxoobjeto d,
                 pessoa s,
                 empresa e
           WHERE s.idpessoa = d.idobjeto
             AND e.idempresa = s.idempresa
             AND d.tipoobjeto = 'pessoa'
             AND d.tipo = 'CRIADOR'
             AND d.idfluxo = ".$_1_u_fluxo_idfluxo."
        ORDER BY e.idempresa, s.nome";

    $rts = d::b()->query($s) or die("listaPessoa: ". mysql_error(d::b()));
   
    while ($r = mysqli_fetch_assoc($rts)) 
    {
        $mod='funcionario';       
        if ($r["status"] == 'ATIVO')
        { 
            $opacity = ''; $cor = 'verde hoververde'; 
        }else{ 
            $opacity = 'opacity'; $cor = 'vermelho hoververmelho ';
        }
       
		$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='retiraeventotiporesp(".$r["idfluxoobjeto"].")'></i>";   
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);    
        
        echo "<tr id=".$r["idfluxoobjeto"]." class='".$opacity."' title=".$title."> 
                <td style='min-width: 10px;' id='statuses'><span class='circle button-blue'></span></td><td>".$r['sigla'].' - '.$r["nome"]."</td><td>".$botao."</td>
                <td>
                    <a href='?_modulo=confacessocolaborador&_acao=u&idpessoa={$r['idpessoa']}' target='_blank'>
                        <i class='fa fa-bars pointer' title='Editar pessoa'></i>
                    </a>
                </td>
            </tr>";                                                                
    }
}
function listaPessoa3()
{   
    global $_1_u_fluxo_idfluxo;
    $s = "SELECT d.idfluxoobjeto,
                ifnull(s.nomecurto,s.nome)AS nome,
                 s.idpessoa,
                 d.criadopor,
                 d.criadoem,
                 s.status,
                 e.sigla
            FROM fluxoobjeto d,
                 pessoa s,
                 empresa e
           WHERE s.idpessoa = d.idobjeto
             AND e.idempresa = s.idempresa
             AND d.tipoobjeto = 'pessoa'
             AND d.tipo = 'ABERTO'
             AND d.idfluxo = ".$_1_u_fluxo_idfluxo."
        ORDER BY e.idempresa, s.nome";

    $rts = d::b()->query($s) or die("listaPessoa: ". mysql_error(d::b()));
   
    while ($r = mysqli_fetch_assoc($rts)) 
    {
        $mod='funcionario';       
        if ($r["status"] == 'ATIVO')
        { 
            $opacity = ''; $cor = 'verde hoververde'; 
        }else{ 
            $opacity = 'opacity'; $cor = 'vermelho hoververmelho ';
        }
       
		$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='retiraeventotiporesp(".$r["idfluxoobjeto"].")'></i>";   
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);    
        
        echo "<tr id=".$r["idfluxoobjeto"]." class='".$opacity."' title=".$title."> 
                <td style='min-width: 10px;' id='statuses'><span class='circle button-blue'></span></td><td>".$r['sigla'].' - '.$r["nome"]."</td><td>".$botao."</td>
                <td>
                    <a href='?_modulo=confacessocolaborador&_acao=u&idpessoa={$r['idpessoa']}' target='_blank'>
                        <i class='fa fa-bars pointer' title='Editar pessoa'></i>
                    </a>
                </td>
            </tr>";                                                                
    }
}

function listaEmpresa2()
{   
    global $_1_u_fluxo_idfluxo;
    $s = "SELECT d.idfluxoobjeto,
                 s.nomefantasia AS nome,
                 s.idempresa,
                 d.criadopor,
                 d.criadoem,
                 s.status,
                 s.sigla
            FROM fluxoobjeto d,
                 empresa s
           WHERE s.idempresa = d.idobjeto
             AND d.tipoobjeto = 'empresa'
             AND d.tipo = 'CRIADOR'
             AND d.idfluxo = ".$_1_u_fluxo_idfluxo."
        ORDER BY s.nomefantasia";

    $rts = d::b()->query($s) or die("listaEmpresa: ". mysql_error(d::b()));
   
    while ($r = mysqli_fetch_assoc($rts)) 
    {
        $mod='empresa';       
        if ($r["status"] == 'ATIVO')
        { 
            $opacity = ''; $cor = 'verde hoververde'; 
        }else{ 
            $opacity = 'opacity'; $cor = 'vermelho hoververmelho ';
        }
       
		$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='retiraeventotiporesp(".$r["idfluxoobjeto"].")'></i>";   
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);    
        
        echo "<tr id=".$r["idfluxoobjeto"]." class='".$opacity."' title=".$title."> 
                <td style='min-width: 10px;' id='statuses'><span class='circle button-blue'></span></td><td>".$r['sigla'].' - '.$r["nome"]."</td><td>".$botao."</td>
                <td>
                    <a href='?_modulo=empresa&_acao=u&idempresa={$r['idempresa']}' target='_blank'>
                        <i class='fa fa-bars pointer' title='Editar empresa'></i>
                    </a>
                </td>
            </tr>";                                                                
    }
}

function listaSgsetor()
{
    global $_1_u_fluxo_idfluxo;
    $s = "SELECT d.idfluxoobjeto,
                 s.grupo,
                 s.idimgrupo,
                 d.criadopor,
                 d.criadoem,
                 s.status,
                 d.assina,
                 d.inidstatus,
                 e.sigla               
            FROM fluxoobjeto d,
                 imgrupo s,
                 empresa e
           WHERE s.idimgrupo = d.idobjeto
             AND s.idempresa = e.idempresa
             AND d.tipoobjeto = 'imgrupo'
             AND d.tipo = 'PARTICIPANTE'
             AND d.idfluxo = ".$_1_u_fluxo_idfluxo."
        ORDER BY s.grupo";

    $rts = d::b()->query($s) or die("listaSgsetor: ". mysql_error(d::b()));

    while ($r = mysqli_fetch_assoc($rts)) 
    {     
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
        echo "<tr>
                <td style='min-width: 10px;' id='statuses'><span class='circle button-blue'></span></td><td title='".$title."'>".$r['sigla'].' - '.$r["grupo"]."</td>
                <td>
                    <select class='size7' name='_assinar' onchange='atEventotiporesp(this,".$r["idfluxoobjeto"].")' >
                        <option value=''></option>";
                        echo fillselect("select 'PARCIAL','Parcial' union select 'TODOS','Todos'  union select 'INDIVIDUAL', 'Individual'",$r['assina']);
        echo "  </select>
                </td>";

        $sqb = "SELECT ts.idfluxostatus, 
                       e.rotulo, 
                       e.rotuloresp, 
                       e.cor
                FROM fluxostatus ts JOIN "._DBCARBON."._status e ON (ts.idstatus = e.idstatus)
                WHERE idfluxo = ".$_1_u_fluxo_idfluxo."
            ORDER BY ordem";
        $rb = d::b()->query($sqb) or die("Erro ao buscar status do fluxo: ". mysql_error(d::b()));         
        $_status = array();   
        while($robSt = mysqli_fetch_assoc($rb))
        {
            $_status[] = $robSt;
        }
        ?>
        <td class="proxes">
            <?
            foreach($_status AS $rob){
                if($r['inidstatus']!=null and $r['inidstatus']!=''){
                    $aridstatusoc = explode(",",$r['inidstatus']);
                }else{
                    $aridstatusoc=array();  
                }

                if (in_array($rob['idfluxostatus'], $aridstatusoc)) 
                {
                    $pclass='selecionado';
                    $pcircle='fa-circle';
                    $key = array_search($rob['idfluxostatus'], $aridstatusoc);
                    if($key !== false){
                        unset($aridstatusoc[$key]);
                    }
                    $aridstatusoc = implode(",", $aridstatusoc);
                }else{
                    $pclass=''; 
                    $pcircle='fa-circle-o';
                    if(count($aridstatusoc)>0){
                        array_push($aridstatusoc,$rob['idfluxostatus']);
                        $aridstatusoc = implode(",", $aridstatusoc);
                    }else{
                        $aridstatusoc = $rob['idfluxostatus'];
                    }
                }
                ?>
                <i title="<?=$rob['rotuloresp']?>" class="iproxstatus fa <?=$pcircle?> dropdown-toggle <?=$pclass?>" data-toggle="dropdown" style="color:<?=$rob['cor']?>;" onclick="selecionaProxStatusResp(this,<?=$r['idfluxoobjeto']?>,'<?=$aridstatusoc?>', 'inidstatus')" ></i>
                <?
            }
            ?>
        </td> 
        <?        
        echo "<td><i class=\"fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable\" onclick=\"retiraeventotiporesp(".$r['idfluxoobjeto'].")\" title='Excluir!'></i></td>
                <td>
                    <a href='?_modulo=imgrupo&_acao=u&idimgrupo={$r['idimgrupo']}' target='_blank'>
                        <i class='fa fa-bars pointer' title='Editar grupo'></i>
                    </a>
                </td>
            </tr>";
    }
}

function listaPessoa()
{    
    global $_1_u_fluxo_idfluxo;
    $s = "SELECT d.idfluxoobjeto,
                 if(s.nomecurto is null, s.nome, s.nomecurto) AS nome,
                 s.idpessoa,
                 d.criadopor,
                 d.criadoem,
                 s.status,
                 d.inidstatus,
                 d.assina,
                 e.sigla
            FROM fluxoobjeto d,
                 pessoa s,
                 empresa e
           WHERE s.idpessoa = d.idobjeto
             AND s.idempresa = e.idempresa
             AND d.tipoobjeto = 'pessoa'
             AND d.tipo = 'PARTICIPANTE'
             AND d.idfluxo = ".$_1_u_fluxo_idfluxo."
        ORDER BY s.nome";

    $rts = d::b()->query($s) or die("listaPessoa: ". mysql_error(d::b()));
   
    while ($r = mysqli_fetch_assoc($rts)) 
    {
        $mod='funcionario';       
        if ($r["status"] == 'ATIVO'){ $opacity = ''; $cor = 'verde hoververde'; }else{ $opacity = 'opacity'; $cor = 'vermelho hoververmelho ';}      
		$botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable' onclick='retiraeventotiporesp(".$r["idfluxoobjeto"].")'></i>";
     
        $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true); 
        
        echo "<tr id=".$r["idfluxoobjeto"]." class='".$opacity."' title=".$title.">
                <td style='min-width: 10px;' id='statuses'><span class='circle button-blue'></span></td><td>".$r['sigla'].' - '.$r["nome"]."</td>
                <td>
                    <select class='size7' name='_assinar' onchange='atEventotiporesp(this,".$r["idfluxoobjeto"].")' >
                        <option value=''></option>";
                        echo fillselect("select 'PARCIAL','Parcial' union select 'TODOS','Todos'  union select 'INDIVIDUAL', 'Individual'",$r['assina']);
        echo "  </select>
                </td>";
        $sqb = "SELECT ts.idfluxostatus, 
                       e.rotulo, 
                       e.rotuloresp, 
                       e.cor
                FROM fluxostatus ts JOIN "._DBCARBON."._status e ON ts.idstatus = e.idstatus
                WHERE ts.idfluxo = ".$_1_u_fluxo_idfluxo."
            ORDER BY ordem";
            $rb = d::b()->query($sqb) or die("Erro ao buscar status do fluxo Pessoa: ". mysql_error(d::b()));
            
        $_status = array();   
        while($robSt = mysqli_fetch_assoc($rb))
        {
            $_status[] = $robSt;
        }
        ?>
        <td class="proxes">
           <?
            foreach($_status AS $rob)
            {
                if($r['inidstatus'] != null and $r['inidstatus']!=''){
                    $aridstatusoc = explode(",",$r['inidstatus']);
                }else{
                    $aridstatusoc = array();  
                }

                if (in_array($rob['idfluxostatus'], $aridstatusoc)) {
                    $pclass='selecionado';
                    $pcircle='fa-circle';
                    $key = array_search($rob['idfluxostatus'], $aridstatusoc);
                    if($key !== false){
                        unset($aridstatusoc[$key]);
                    }
                    $aridstatusoc = implode(",", $aridstatusoc);
                }else{
                    $pclass=''; 
                    $pcircle='fa-circle-o';
                    if(count($aridstatusoc)>0){
                        array_push($aridstatusoc,$rob['idfluxostatus']);
                        $aridstatusoc = implode(",", $aridstatusoc);
                    }else{
                        $aridstatusoc = $rob['idfluxostatus'];
                    }
                }
                ?>
                <i title="<?=$rob['rotuloresp']?>" class="iproxstatus fa <?=$pcircle?> dropdown-toggle <?=$pclass?>" data-toggle="dropdown" style="color:<?=$rob['cor']?>;" onclick="selecionaProxStatusResp(this, <?=$r['idfluxoobjeto']?>, '<?=$aridstatusoc?>', 'inidstatus')" ></i>
            <?
            }
            ?>
        </td> 
        <?
        echo"  <td>".$botao."</td>
                    <td>
                        <a href='?_modulo=confacessocolaborador&_acao=u&idpessoa={$r['idpessoa']}' target='_blank'>
                            <i class='fa fa-bars pointer' title='Editar grupo'></i>
                        </a>
                    </td>
                </tr>";                                                                
    } 
}

function getJSetorvinc() 
{
	global $JSON, $_1_u_fluxo_idfluxo;
	$sql = "SELECT i.idimgrupo, i.grupo, e.sigla
            FROM imgrupo i, empresa e
            WHERE i.status='ATIVO'
            and e.idempresa = i.idempresa
                AND NOT EXISTS(
                    SELECT 1
                        FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
                        WHERE ms.idfluxo = '".$_1_u_fluxo_idfluxo."' 
                        AND r.tipoobjeto ='imgrupo'
                        AND i.idimgrupo = r.idobjeto)
            ORDER BY grupo ASC";

	$rts = d::b()->query($sql) or die("getJSetorvinc: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["idimgrupo"];
		$arrtmp[$i]["label"] = $r['sigla'].' - '.$r["grupo"];
		$i++;
	}

	return $JSON->encode($arrtmp);
}
function getJEmpresavinc() 
{
	global $JSON, $_1_u_fluxo_idfluxo;
	$sql = "SELECT e.idempresa, e.nomefantasia,e.sigla
            FROM empresa e
            WHERE e.status='ATIVO'
                AND NOT EXISTS(
                    SELECT 1
                        FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
                        WHERE ms.idfluxo = '".$_1_u_fluxo_idfluxo."'
                        AND r.tipoobjeto ='empresa'
                        AND e.idempresa = r.idobjeto)
            ORDER BY nomefantasia ASC";

	$rts = d::b()->query($sql) or die("getJSetorvinc: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"] = $r["idempresa"];
		$arrtmp[$i]["label"] = $r['sigla'].' - '.$r["nomefantasia"];
		$i++;
	}

	return $JSON->encode($arrtmp);
}

function getJfuncionario($tipo = NULL) 
{	
	global $JSON, $_1_u_fluxo_idfluxo;

    if(!empty($tipo)){
        $condicao = " AND tipo = '$tipo'";
    }
	
	$sql = "SELECT a.idpessoa, ifnull(a.nomecurto,a.nome) as nomecurto,e.sigla
                   FROM pessoa a JOIN empresa e on(e.idempresa = a.idempresa) JOIN objempresa oe on oe.idobjeto = a.idpessoa 
                  WHERE a.status in('ATIVO', 'PENDENTE', 'AFASTADO')
                    AND (a.idtipopessoa in (1,8))
                    AND NOT a.usuario is null
                    AND NOT EXISTS(SELECT 1
						              FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
						             WHERE ms.idfluxo = '".$_1_u_fluxo_idfluxo."'
						               AND r.tipoobjeto = 'pessoa'
						               AND a.idpessoa = r.idobjeto $condicao)
			UNION
                SELECT p.idpessoa, ifnull(p.nomecurto,p.nome) as nomecurto,e.sigla
				  FROM pessoa p JOIN empresa e on(e.idempresa = p.idempresa)
				 WHERE p.status in('ATIVO', 'PENDENTE', 'AFASTADO')
				  AND p.idtipopessoa in (15, 16, 113)
				  AND NOT p.usuario is null
				  AND NOT EXISTS(SELECT 1
						  FROM fluxo ms JOIN fluxoobjeto r ON ms.idfluxo = r.idfluxo
						 WHERE ms.idfluxo = '".$_1_u_fluxo_idfluxo."'
						   AND r.tipoobjeto = 'pessoa'
						   AND p.idpessoa = r.idobjeto $condicao)						
			ORDER BY nomecurto asc";

	$rts = d::b()->query($sql) or die("oioi: ". mysql_error(d::b()));

	$arrtmp = array();
	$i = 0;

	while ($r = mysqli_fetch_assoc($rts)) {
		$arrtmp[$i]["value"]=$r["idpessoa"];
		$arrtmp[$i]["label"]= $r['sigla'].' - '.$r["nomecurto"];
		$i++;
	}
	
	return $JSON->encode($arrtmp);    
}

?>

<style>
.config-status{
    display: flex;
}
.panel-status{
    height: auto;
    padding: 5px;
    flex-direction: column;
}
#novoStatus{
    align-self: flex-end;
}
td.proxes{
    white-space: nowrap;
    position: relative;
}
td.proxes i {
    font-size: 18px;
    margin-right: 3px;
    color: silver;
    cursor: pointer;
}

td.proxes i.iproxstatus{
    opacity: 0.5;
    transition: all .5s;
}
td.proxes i.iproxstatus:hover{
    opacity: 1;
}
td.proxes i.iproxstatus.selecionado{
    opacity: 1;
}

.td_titulo {width: 7%; text-align: right;}
.td_campos {width: 15%;}
.select {width: 100% !important;}
.select-picker
{
    margin: 0px; 
    padding: 0px;
    width:100%;
    font-size:11px;
    height: 30px;
}
.select-picker > button{height: 100% !important;}
.dropdown-menu{max-height: 300px !important;}
</style>

<div class="row">
    <div class="col-md-12" >
        <div class="panel panel-default" >
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-10">Fluxo</div>
                    <div class="col-md-1 head">Status:</div>
                        <div class="col-md-1" style="margin-left: -30px; margin-top: -5px;">
                            <select name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_status">
                                <? fillselect("SELECT 'ATIVO','Ativo' union select 'INATIVO','Inativo'", $_1_u_fluxo_status); ?>
                            </select>
                        </div>
                </div>
            </div>
            <div class="panel-body">
                <div class="panel-heading">
                    <table>
                        <tr>
                            <td class="td_titulo">
                                <input name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_idfluxo" id="idfluxo" type="hidden" value="<?= $_1_u_fluxo_idfluxo?>" readonly='readonly'>
                                Módulo:
                            </td>
                            <td class="td_campos">
                                <select class="select select-picker" name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_modulo" onchange="saveModulo(this, 'modulo')" vnulo data-live-search="true">
                                    <option value=""></option>
                                    <? fillselect("SELECT m.modulo, CONCAT(m.rotulomenu, ' - ', m.modulo) AS rotulo
                                                    FROM "._DBCARBON."._modulo m,"._DBCARBON."._mtotabcol tc
                                                    WHERE tc.primkey ='Y'
                                                    AND exists (select 1 from "._DBCARBON."._mtotabcol t where t.tab = m.tab and col='alteradoem' )
                                                    AND tc.tab = m.tab
                                                    AND m.status = 'ATIVO'
                                                    ORDER BY m.rotulomenu", $_1_u_fluxo_modulo); ?>
                                </select>
                            </td>
                            <? if($_1_u_fluxo_idfluxo) { ?>
                                <td class="td_titulo">Tipo:</td>
                                <td class="td_campos">
                                    <select class="select" name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_tipoobjeto" onchange="saveModulo(this, 'tipoobjeto')">
                                        <option value=""></option>
                                        <? fillselect("SELECT mtc.col, CONCAT(mtc.col, ' - ', if(length(mtc.rotcurto)=0,mtc.col,mtc.rotcurto) )as rotcurto
                                                        FROM "._DBCARBON."._modulo m 
                                                        JOIN "._DBCARBON."._mtotabcol mtc ON mtc.tab=m.tab
                                                    LEFT JOIN "._DBCARBON."._modulofiltros mf ON mf.modulo = m.modulo and mf.col = mtc.col
                                                        WHERE m.modulo = '".$_1_u_fluxo_modulo."' 
                                                        AND exists(SELECT 1 FROM information_schema.tables it
                                                                    WHERE it.table_name = m.tab)
                                                    ORDER BY mtc.col, mtc.rotcurto", $_1_u_fluxo_tipoobjeto); ?>                    
                                    </select>	
                                </td>
                                <td class="td_titulo"> Valor: </td>
                                <td class="td_campos">
                                    <? 
                                    if($_1_u_fluxo_modulo == 'formalizacao'){
                                        $dropSql = "SELECT subtipo, descricao  FROM formalizacaosubtipo WHERE status = 'ATIVO' ";
                                    } else {

                                        $sqlDropsql = "SELECT dropsql 
                                                    FROM "._DBCARBON."._modulo m JOIN "._DBCARBON."._mtotabcol mtc ON mtc.tab=m.tab
                                                WHERE m.modulo = '".$_1_u_fluxo_modulo."' AND mtc.col = '".$_1_u_fluxo_tipoobjeto."'";
                                        $rbDropsql = d::b()->query($sqlDropsql) or die("Erro ao buscar status do fluxo: ". mysql_error(d::b()));
                                        $robDropsql = mysqli_fetch_assoc($rbDropsql);
                                        if(empty($robDropsql['dropsql'])){ $disabled = 'disabled';}
                                        $dropSql = str_replace('".getidempresa(\'idempresa\',\'tipoprodserv\')."', ' AND idempresa = '.$_SESSION["SESSAO"]["IDEMPRESA"], $robDropsql['dropsql']);
                                    }
                                    ?>
                                    <select class="select" name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_idobjeto" onchange="saveModulo(this, 'idobjeto')" <?=$disabled?>>
                                        <option value=""></option>
                                        <? fillselect($dropSql, $_1_u_fluxo_idobjeto); ?>                    
                                    </select>
                                </td>
                                <td class="td_titulo"> Conf. Prazo D: </td>
                                <td class="td_campos">
                                    <select class="select" name="_1_<?= $_acao ?>_<?= $pagvaltabela?>_colprazod" onchange="saveModulo(this, 'colprazod')">
                                        <? fillselect("SELECT mtc.col, CONCAT(mtc.col, ' - ', if(length(mtc.rotcurto)=0,mtc.col,mtc.rotcurto) )as rotcurto
                                                        FROM "._DBCARBON."._modulo m 
                                                        JOIN "._DBCARBON."._mtotabcol mtc ON mtc.tab=m.tab
                                                    LEFT JOIN "._DBCARBON."._modulofiltros mf ON mf.modulo = m.modulo and mf.col = mtc.col
                                                        WHERE m.modulo = '".$_1_u_fluxo_modulo."' 
                                                        and mtc.datatype in ('date', 'datetime')
                                                        AND exists(SELECT 1 FROM information_schema.tables it
                                                                    WHERE it.table_name = m.tab)
                                                    ORDER BY mtc.col, mtc.rotcurto", $_1_u_fluxo_colprazod); ?>                    
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="td_titulo">Descrição:</td>
                                <td class="td_campos" colspan="7">
                                    <textarea name="_1_<?=$_acao?>_<?=$pagvaltabela?>_descricao" rows="5"><?=$_1_u_fluxo_descricao?></textarea>
                                </td>
                            <? } ?>
                        </tr>
                    </table>                    
                </div>
            </div>
            <? if($_1_u_fluxo_idfluxo) { ?>  
                <div class="col-lg-14">
                    <div class="panel-status config-status">
                        <table id="eventostatus">
                            <tr>
                                <td>&nbsp;</td>
                                <td>[Bt] Status</td>
                                <td></td>
                                <td>E</td>
                                <td></td>
                                <td>Prazo D</td>
                                <td>Ord.</td>                               
                                <td>Fluxo (Pessoa)</td>
                                <td></td>
                                <td >Ocultar Bt (Evento)</td>
                                <td style="width:48px;">Bt Criador</td>
                                <td style="width:48px;">Bt Partic.</td>
                                <td style="width:48px;">Ocultar </td>
                                <td style="width:48px;">Nova Msg</td>
                            </tr>
                            <?
                            $sqst = "SELECT ts.idfluxostatus,
                                            ts.ocultar,
                                            ts.botaocriador,
                                            ts.botaoparticipante,
                                            ts.novamensagem,
                                            ts.idstatus,
                                            ts.fluxo,
                                            ts.fluxoocultar,
                                            ts.ordem,
                                            ts.idetapa,
                                            ts.prazod,
                                            e.rotulo,
                                            e.rotuloresp,
                                            e.cor,
                                            e.botao,
                                            ts.numfluxostatus,
                                            d.iddashcard,
                                            de.iddashcard as iddashcardetapa
                                       FROM fluxostatus ts
                                  LEFT JOIN "._DBCARBON."._status e ON (ts.idstatus = e.idstatus)
                                  LEFT JOIN dashcard d on d.objeto = ts.idfluxostatus and d.tipoobjeto = 'fluxostatus' AND d.modulo = '$_1_u_fluxo_modulo'
                                  LEFT JOIN dashcard de on de.objeto = ts.idetapa and de.tipoobjeto = 'etapa'
                                      WHERE idfluxo = '$_1_u_fluxo_idfluxo'
                                    ORDER BY ordem";
                            $rst = d::b()->query($sqst) or die("Erro ao buscar configuracao dos botoes: ". mysql_error(d::b()));
                            $ifi=0;
                            while($rowst=mysqli_fetch_assoc($rst)){
                                $ifi=$ifi+1;
                                ?>
                                <tr  style="border-bottom: #999999 solid 1px;">
                                    <td>
                                        <i class="fa fa-arrows cinzaclaro hover move" title="Ordenar Status>"></i>
                                        <input type="hidden" name="_ifi<?=$ifi?>_u_fluxostatus_idfluxostatus" value="<?=$rowst["idfluxostatus"]?>">
                                        <input type="hidden" name="_ifi<?=$ifi?>_u_fluxostatus_ordem" value="<?=$rowst["ordem"]?>">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="fa fa-circle colorpicker input-group-addon pointer dropdown-toggle " data-toggle="dropdown" title="Alterar cor" style="color:<?=$rowst['cor']?>" color="<?=$rowst['cor']?>">
                                            </span>
                                            <select class="select-picker" name="" onchange="atulizaevtstatus(this,<?=$rowst['idfluxostatus']?>, 'idstatus', '<?=$rowst['idstatus']?>', <?=$_1_u_fluxo_idfluxo?>)" data-live-search='true'>
                                                <option></option>
                                                <?fillselect(FluxoController::buscarStatusParaVinculoPorIdFluxoStatus($_1_u_fluxo_idfluxo, $rowst['idfluxostatus'], true), $rowst['idstatus']);?>		
                                            </select>  
                                            
                                        </div>
                                    </td>
                                    <td><a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=_status&_acao=u&idstatus=<?=$rowst['idstatus'];?>');"></a>
                                    <? if($rowst['iddashcard']){ ?>
                                            <a class="fa fa-th-large cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=dashcard&_acao=u&iddashcard=<?=$rowst['iddashcard'];?>');"></a>
                                        <? } ?> 
                                    </td>
                                    <td class="<?=$rowst['idetapa'];?>">
                                        <? if(!empty($_1_u_fluxo_idobjeto)){$sqlStatus = " AND idobjeto = '".$_1_u_fluxo_idobjeto."' AND tipoobjeto = '".$_1_u_fluxo_tipoobjeto."'"; } ?>
                                        <select  style="width: 40px !important;margin: 0px; padding: 0px;font-size:11px;" class="size6" name="" onchange="CB.post({objetos:'_ajax_u_fluxostatus_idfluxostatus=<?=$rowst['idfluxostatus']?>&_ajax_u_fluxostatus_idetapa='+$(this).val()})">
                                            <option></option>
                                            <?fillselect("SELECT idetapa,
                                                                 etapa
                                                            FROM etapa
                                                        WHERE status = 'ATIVO' AND modulo = '".$_1_u_fluxo_modulo."'
                                                         $sqlStatus ", $rowst['idetapa']);?>		
                                        </select>
                                        
                                    </td>
                                    <td>
                                        <? if($rowst['idetapa']){ ?>
                                            <a class="fa fa-bars cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=etapa&_acao=u&idetapa=<?=$rowst['idetapa'];?>');"></a>
                                        <? } ?>
                                        <? if($rowst['iddashcardetapa']){ ?>
                                            <a class="fa fa-th-large cinzaclaro hoverazul pointer" onclick="janelamodal('?_modulo=dashcard&_acao=u&iddashcard=<?=$rowst['iddashcardetapa'];?>');"></a>
                                        <? } ?> 
                                    </td>

                                    <td>
                                        <input type="text" name="_ifi<?=$ifi?>_u_fluxostatus_prazod" value="<?=$rowst['prazod']?>">
                                    </td>

                                    <td><input type="text" name="_ifi<?=$ifi?>_u_fluxostatus_numfluxostatus" id="numfluxostatus" class="size3" value="<?=$rowst['numfluxostatus']?>"></td>                                 
                                    <td class="proxes">
                                        <?
                                        $sqb="SELECT ts.idfluxostatus,
                                                    ts.fluxo,
                                                    ts.ordem,
                                                    e.rotulo,
                                                    e.rotuloresp,
                                                    e.botao,
                                                    e.cor
                                                FROM fluxostatus ts JOIN "._DBCARBON."._status e ON (ts.idstatus = e.idstatus)
                                            WHERE idfluxo = ".$_1_u_fluxo_idfluxo."
                                                AND ts.idfluxostatus != ".$rowst['idfluxostatus']."
                                            ORDER BY ordem";
                                        $rb = d::b()->query($sqb) or die("Erro ao buscar status do fluxo: ". mysql_error(d::b()));
                                        while($rob=mysqli_fetch_assoc($rb))
                                        {
                                            if($rowst['fluxo'] != null and $rowst['fluxo']!=''){
                                                $idfluxostatus = array_filter(explode(",",$rowst['fluxo']), function($element){ return $element;});
                                            }else{
                                                $idfluxostatus = array();  
                                            }

                                            if (in_array($rob['idfluxostatus'], $idfluxostatus)) {
                                                $pclass='selecionado';
                                                $pcircle='fa-circle';
                                                $key = array_search($rob['idfluxostatus'], $idfluxostatus);
                                                if($key!==false){
                                                    unset($idfluxostatus[$key]);
                                                }
                                                $idfluxostatus = implode(",", $idfluxostatus);
                                            }else{
                                                $pclass=''; 
                                                $pcircle='fa-circle-o';
                                                if(count($idfluxostatus)>0){
                                                    array_push($idfluxostatus,$rob['idfluxostatus']);
                                                    $idfluxostatus = implode(",", $idfluxostatus);
                                                }else{
                                                    $idfluxostatus = $rob['idfluxostatus'];
                                                }
                                            }
                                            ?>
                                            <i title="[<?=$rob['botao']?>] (U - <?=$rob['rotuloresp']?>) (E - <?=$rob['rotulo']?>)" class="iproxstatus fa <?=$pcircle?> dropdown-toggle <?=$pclass?>"  data-toggle="dropdown" style="color:<?=$rob['cor']?>;" onclick="selecionaProxStatus(this,<?=$rowst["idfluxostatus"]?>,'<?=$idfluxostatus?>')" ></i>
                                        <?
                                        }
                                        ?>
                                    </td>
                                    <td style="padding-right: 25px;"></td>
                                    <td class="proxes">
                                        <?
                                        $sqb="SELECT ts.idfluxostatus,
                                                    ts.fluxoocultar,
                                                    ts.ordem,
                                                    e.rotulo,
                                                    e.rotuloresp,
                                                    e.cor,
                                                    e.botao
                                                FROM fluxostatus ts JOIN "._DBCARBON."._status e ON (ts.idstatus = e.idstatus)
                                            WHERE idfluxo = ".$_1_u_fluxo_idfluxo."
                                            ORDER BY ordem";
                                        //die($sqb);
                                        $rb = d::b()->query($sqb) or die("Erro ao buscar status do fluxo 2: ". mysql_error(d::b()));
                                        while($rob=mysqli_fetch_assoc($rb))
                                        {
                                            if($rowst['fluxoocultar'] != null and $rowst['fluxoocultar'] != ''){
                                                $idfluxostatusoc = explode(",",$rowst['fluxoocultar']);
                                            }else{
                                                $idfluxostatusoc=array();  
                                            }
                                            //echo(count($idstatusoc));

                                            if (in_array($rob['idfluxostatus'], $idfluxostatusoc)) {
                                                $pclass='selecionado';
                                                $pcircle='fa-circle';
                                                $key = array_search($rob['idfluxostatus'], $idfluxostatusoc);
                                            if($key!==false){
                                                unset($idfluxostatusoc[$key]);
                                            }
                                            $idfluxostatusoc = implode(",", $idfluxostatusoc);
                                            }else{
                                                $pclass=''; 
                                                $pcircle='fa-circle-o';
                                                if(count($idfluxostatusoc)>0){
                                                    array_push($idfluxostatusoc,$rob['idfluxostatus']);
                                                    $idfluxostatusoc = implode(",", $idfluxostatusoc);
                                                }else{
                                                    $idfluxostatusoc=$rob['idfluxostatus'];
                                                }
                                            }
                                            ?>
                                            <i title="[<?=$rob['botao']?>] (U - <?=$rob['rotuloresp']?>) (E - <?=$rob['rotulo']?>)" class="iproxstatus fa <?=$pcircle?> dropdown-toggle <?=$pclass?>" data-toggle="dropdown" style="color:<?=$rob['cor']?>;" onclick="selecionaProxStatusOc(this,<?=$rowst["idfluxostatus"]?>,'<?=$idfluxostatusoc?>')" ></i>
                                            <?
                                        }
                                            
                                        /** Se excluir algum status será alterado o evento fluxostatuspessoa para o INICIO 
                                         * Alteração Realizada em 15/01/2020 - Lidiane	
                                         */
                                        //Busca o Status incial do Evento
                                        $sql1 = "SELECT mf.idstatus FROM fluxostatus mf JOIN fluxo ms ON mf.idfluxo = ms.idfluxo
                                                   JOIN "._DBCARBON."._status s ON s.idstatus = mf.idstatus
                                                   JOIN "._DBCARBON."._statustipo st ON st.statustipo = s.statustipo
                                                  WHERE mf.idfluxo = ".$_1_u_fluxo_idfluxo." AND st.statustipo = 'INICIO'";	
                                        $res1 = d::b()->query($sql1) or die("Erro ao buscar informções da configuração do Tipo Evento Status: ".mysql_error(d::b()));
                                        $row1 = mysqli_fetch_assoc($res1);
                                        ?>
                                    </td> 
                                    <td align="center">
                                        <?if($rowst['botaocriador']=='Y'){$checkedcr="checked='checked'"; $fl="N";  }else{$checkedcr=''; $fl="Y";}?>
                                        <input title="Botão do Criador"  type="checkbox"  value="" onchange="moduloFluxoStatus(<?=$rowst['idfluxostatus']?>,'botaocriador','<?=$fl?>')" <?=$checkedcr?>>
                                    </td>
                                    <td align="center">
                                        <?if($rowst['botaoparticipante']=='Y'){$checkedcr="checked='checked'"; $fl="N";  }else{$checkedcr=''; $fl="Y";}?>
                                        <input title="Botão do Participante"  type="checkbox"  value="" onchange="moduloFluxoStatus(<?=$rowst['idfluxostatus']?>,'botaoparticipante','<?=$fl?>')" <?=$checkedcr?>>
                                    </td>
                                    <td align="center">
                                        <?if($rowst['ocultar']=='Y'){$checkedoc="checked='checked'"; $fl="N";  }else{$checkedoc=''; $fl="Y";}?>
                                        <input title="Ocultar"  type="checkbox"  value="" onchange="moduloFluxoStatus(<?=$rowst['idfluxostatus']?>,'ocultar','<?=$fl?>')" <?=$checkedoc?>>
                                    </td>                                                                 
                                    <td align="center">
                                        <?if($rowst['novamensagem']=='Y'){$checkednv="checked='checked'"; $fl="N"; }else{$checkednv=''; $fl="Y";}?>
                                        <input title="Nova Mensagem"  type="checkbox"  value="" onchange="moduloFluxoStatus(<?=$rowst['idfluxostatus']?>,'novamensagem','<?=$fl?>')" <?=$checkednv?>>
                                    </td>
                                    <td align="center">
                                        <i class="fa fa-cog cinzaclaro hoverpreto pointer" onclick="callModal(<?=$rowst['idfluxostatus']?>, '<?=$rowst['botao']?>')"></i>
                                    </td>
                                    <td>   
                                        <?
                                        //Valida se o fluxo tem algum vínculo no módulo. Caso tenha, não será possível excluir
                                        $tabela = getModuloTab($_1_u_fluxo_modulo);
                                        if(!empty($tabela))
                                        {
                                            $sqlVinculo = "SELECT 1 FROM $tabela WHERE idfluxostatus = '".$rowst['idfluxostatus']."'";
                                            $resVinculo = d::b()->query($sqlVinculo) or die("Erro ao consultar SQL Vinculo: ".mysql_error()." " .$sql);  
                                            $qtdVinculo = mysql_num_rows($resVinculo);  
                                            if($qtdVinculo == 0) 
                                            { 
                                                $onclickFluxo = 'onclick="deletarStatus('.$rowst['idfluxostatus'].', \''.$rowst['iddashcard'].'\');"';
                                                $desabilitabotao = "";
                                                $cursor = "";
                                            } else {
                                                $onclickFluxo = "";
                                                $desabilitabotao = 'disabled';
                                                $cursor = 'style="cursor: not-allowed"';
                                            
                                            }
                                        } else {
                                            $desabilitabotao = "";
                                            $cursor = "";
                                        }                                        
                                        ?>                     
                                        <i class="fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable" <?=$cursor?> <?=$onclickFluxo?> title="Excluir!" <?=$desabilitabotao?>></i>
                                    </td>
                                </tr>
                            <? 
                            }//
                            ?>
                        </table>    
                        <button id="novoStatus" class="btn btn-success" onclick="novoStatus(<?=$_1_u_fluxo_idfluxo?>,<?=$ifi+1?>)"><i class="fa fa-plus"></i></button>            
                    </div>   
                </div> 
                <div class="row" style="margin-left: 0px;">
                    <div class="col-lg-3">
                        <div class=" panel-status participantes">                                                            
                            <table>
                                <tr id="menuPermissoes">
                                    <td>Criador:</td>
                                    <td id="tdfuncionario2">
                                        <input id="eventoresp2" class="compacto" type="text" cbvalue placeholder="Selecione">
                                    </td>
                                    <td id="tdsgsetor2">
                                        <input id="sgsetorvinc2" class="compacto" type="text" cbvalue placeholder="Selecione">
                                    </td>
                                    <td id="tdempresa2">
                                        <input id="empresavinc2" class="compacto" type="text" cbvalue placeholder="Selecione">
                                    </td>
                                    <td class="nowrap" style="width: 165px">
                                        <div class="btn-group nowrap" role="group" aria-label="...">
                                            <button onclick="showfuncionario2()" type="button" class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright selecionado" title="Selecionar Funcionário" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
                                            <button onclick="showsgsetor2()" type="button" class=" btn btn-default fa fa-users hoverlaranja pointer floatright " title="Selecionar Setor" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>										
                                            <button onclick="showempresa2()" type="button" class=" btn btn-default fa fa-building hoverlaranja pointer floatright " title="Selecionar Setor" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>										
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <table class='table table-striped planilha'>
                                <?=listaSgsetor2()?> 	
                                <?=listaPessoa2()?>									
                                <?=listaEmpresa2()?>									
                            </table>								
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class=" panel-status participantes">                                                            
                            <table>
                                <tr id="menuPermissoes">
                                    <td>Participantes:</td>
                                    <td id="tdfuncionario">
                                        <input id="eventoresp" class="compacto" type="text" cbvalue placeholder="Selecione">
                                    </td>
                                    <td id="tdsgsetor">
                                        <input id="sgsetorvinc" class="compacto" type="text" cbvalue placeholder="Selecione">
                                    </td>
                                    <td class="nowrap" style="width: 110px">
                                        <div class="btn-group nowrap" role="group" aria-label="...">
                                            <button onclick="showfuncionario()" type="button" class=" btn btn-default fa fa-user fa-1x hoverlaranja pointer floatright selecionado" title="Selecionar Funcionário" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>
                                            <button onclick="showsgsetor()" type="button" class=" btn btn-default fa fa-users hoverlaranja pointer floatright " title="Selecionar Setor" style="margin-right: 8px; border-radius: 4px;">&nbsp;</button>                                       
                                        </div>
                                    </td>
                                </tr>
                            </table>                                                       
                            <table class='table table-striped planilha'>
                                <tr>
                                    <th></th>
                                    <th>Pessoa/Grupo</th>
                                    <th>Assinatura</th>
                                    <th class="nowrap">Acessar Status:</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td> Todos:</td>
                                    <td>
                                        <select name="_1_<?=$_acao?>_fluxo_assinar" onchange="CB.post();">
                                            <option value=""></option>
                                            <?fillselect("select 'PARCIAL','Parcial' union select 'TODOS','Todos' union select 'INDIVIDUAL', 'Individual'",$_1_u_fluxo_assinar);?>		
                                        </select>
                                    </td>
                                    <td class="proxes">
                                        <?
                                        $sqb="SELECT ts.idfluxostatus,
                                                     e.rotulo,
                                                     e.rotuloresp,
                                                     e.cor
                                                FROM fluxo ms JOIN fluxostatus ts ON ms.idfluxo = ts.idfluxo
                                                JOIN "._DBCARBON."._status e on ts.idstatus = e.idstatus
                                                WHERE ms.idfluxo = ".$_1_u_fluxo_idfluxo."
                                            ORDER BY ordem";
                                        $rb = d::b()->query($sqb) or die("Erro ao buscar status do fluxo: ". mysql_error(d::b()));
                                        while($rob=mysqli_fetch_assoc($rb))
                                        {
                                            if($_1_u_fluxo_inidstatus != null and $_1_u_fluxo_inidstatus != ''){
                                                $aridstatusoc = explode(",",$_1_u_fluxo_inidstatus);
                                            }else{
                                                $aridstatusoc=array();  
                                            }         

                                            if (in_array($rob['idfluxostatus'], $aridstatusoc)) {
                                                $pclass='selecionado';
                                                $pcircle='fa-circle';
                                                $key = array_search($rob['idfluxostatus'], $aridstatusoc);
                                                if($key!==false){
                                                    unset($aridstatusoc[$key]);
                                                }
                                                $aridstatusoc = implode(",", $aridstatusoc);
                                            }else{
                                                $pclass=''; 
                                                $pcircle='fa-circle-o';
                                                if(count($aridstatusoc)>0){
                                                    array_push($aridstatusoc,$rob['idfluxostatus']);
                                                    $aridstatusoc = implode(",", $aridstatusoc);
                                                }else{
                                                    $aridstatusoc=$rob['idfluxostatus'];
                                                }
                                            }
                                            ?>
                                            <i title="<?=$rob['rotuloresp']?>" class="iproxstatus fa <?=$pcircle?> dropdown-toggle <?=$pclass?>" data-toggle="dropdown" style="color:<?=$rob['cor']?>;" onclick="selecionaProxStatusEv(this, <?=$_1_u_fluxo_idfluxo?>, '<?=$aridstatusoc?>')" ></i>

                                        <?
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?=listaSgsetor()?>                     
                                <?=listaPessoa()?>                            
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class=" panel-status participantes">                                                            
                            <table>
                                <tr id="menuPermissoes3">
                                    <td>Aberto para:</td>
                                    <td id="tdfuncionario3">
                                        <input id="eventoresp3" class="compacto" type="text" cbvalue placeholder="Selecione">
                                    </td>
                               </tr>
                            </table>
                            <table class='table table-striped planilha'>                             
                                <?=listaPessoa3()?>			
                            </table>								
                        </div>
                    </div>

                </div>  
            <? } ?>    
        </div>
    </div>
</div>
<?
//pegar a tabela do criado/alterado em antigo
if ($_acao == 'u') {
	$_idModuloParaAssinatura = $_1_u_fluxo_idfluxo;
	require '../form/viewAssinaturas.php';
}
$tabaud = "fluxo"; 
require 'viewCriadoAlterado.php';

$jSgsetorvinc 		= getJSetorvinc();
$jFuncionariocriador= getJfuncionario('CRIADOR');
$jFuncionario   	= getJfuncionario('PARTICIPANTE');
$jEmpresa   	    = getJEmpresavinc();

?>

<script language="javascript">
    jSgsetorvinc    	= <?=$jSgsetorvinc?>;
    jFuncionario    	= <?= $jFuncionario ?>;
    jFuncionariocriador	= <?= $jFuncionariocriador ?>;
    jEmpresa    	    = <?= $jEmpresa ?>;

    $('.tagRow').hide();
    $('.docRow').hide();
    $('.pessoaRow').hide();


    $('#tdsgsetor').hide();
    $('#tdfuncionario').show();

    $('#tdsgsetor2').hide();
    $('#tdempresa2').hide();
    $('#tdfuncionario2').show();

    $('.select-picker').selectpicker('render');

    function showfuncionario() {
        $('#tdsgsetor').hide();
        $('#tdfuncionario').show();
    }

    function showsgsetor() {
        $('#tdsgsetor').show();
        $('#tdfuncionario').hide();
    }
    function showfuncionario2() {
        $('#tdsgsetor2').hide();
        $('#tdfuncionario2').show();
        $('#tdempresa2').hide();
    }

    function showsgsetor2() {
        $('#tdsgsetor2').show();
        $('#tdfuncionario2').hide();
        $('#tdempresa2').hide();
    }
    function showempresa2() {
        $('#tdempresa2').show();
        $('#tdsgsetor2').hide();
        $('#tdfuncionario2').hide();
    }

    //Salva o Módulo para aparecer os Tipos De Documentos ou 
    function saveModulo(vthis, campo)
    {
        CB.post({
            objetos: '&_1_<?= $_acao ?>_<?= $pagvaltabela?>_idfluxo=<?=$_1_u_fluxo_idfluxo?>'
                    +'&_1_<?= $_acao ?>_<?= $pagvaltabela?>_'+campo+'='+$(vthis).val()             
        })
    }

    function novoStatus(inidfluxo,ordem)
    {    
        CB.post({
            objetos: {
                "_x_i_fluxostatus_idfluxo":inidfluxo    
                ,"_x_i_fluxostatus_ordem":ordem
            }
            ,parcial: true
        });
    }

    function selecionaProxStatus(inObj, inidstatus, aridstatus)
    {
        $ico=$(inObj);
        if($ico.hasClass("selecionado")){
            $ico.removeClass("fa-circle selecionado").addClass("fa-circle-o");
        }else{
            $ico.removeClass("fa-circle-o").addClass("fa-circle selecionado");
        }
            
        CB.post({
            objetos: {
                "_x_u_fluxostatus_idfluxostatus": inidstatus,
                "_x_u_fluxostatus_fluxo": aridstatus
            },
            parcial: true
        });
    }

    function atulizaevtstatus(vthis, idfluxostatus, incampo, idstatus, idfluxo)
    {
        var Vval= $(vthis).val();
        CB.post({
            objetos: {
                "_x_u_fluxostatus_idfluxostatus": idfluxostatus,
                "idstatus": idstatus,
                "idfluxo": idfluxo,
                "idstatusatual": Vval
            }
            ,parcial:true
            ,posPost: function(){
                alteraPosicaoStatus(vthis, idfluxostatus, incampo);
            }
        });      
    }

    function alteraPosicaoStatus(vthis, idfluxostatus, incampo){

        var Vval= $(vthis).val();

        CB.post({
            objetos: "_x_u_fluxostatus_idfluxostatus="+idfluxostatus+"&_x_u_fluxostatus_"+incampo+"="+Vval
            ,parcial:true
        });
    }

    function deletarStatus(idfluxostatus,iddashcard)
    {
        CB.post({
            objetos: {
                "_x_d_fluxostatus_idfluxostatus": idfluxostatus,
                "_x1_d_dashcard_iddashcard": iddashcard
                
            }
            ,parcial:true
        });
    }

    function moduloFluxoStatus(idfluxostatus,incampo,infl)
    {
        CB.post({			
            objetos:"_x_u_fluxostatus_idfluxostatus="+idfluxostatus+"&_x_u_fluxostatus_"+incampo+"="+infl
            ,parcial: true				
        });
    }

    <? if($_1_u_fluxo_idfluxo){ ?>
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
            select: function(event, setor) {
                
                    CB.post({
                        objetos: {
                            "_x_i_fluxoobjeto_idfluxo":<?=$_1_u_fluxo_idfluxo?>
                            ,"_x_i_fluxoobjeto_idobjeto": setor.item.value
                            ,"_x_i_fluxoobjeto_tipoobjeto": 'imgrupo'                    
                    }
                    ,parcial: true
                });
            }
        });

        //Autocomplete de Setores vinculados
        $("#empresavinc2").autocomplete({
            source: jEmpresa,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    lbItem = item.label;
                    return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                };
            },
            select: function(event, empresa) {
                
                    CB.post({
                        objetos: {
                            "_x_i_fluxoobjeto_idfluxo":<?=$_1_u_fluxo_idfluxo?>
                            ,"_x_i_fluxoobjeto_idobjeto": empresa.item.value
                            ,"_x_i_fluxoobjeto_tipoobjeto": 'empresa'
                            ,"_x_i_fluxoobjeto_tipo": 'CRIADOR'
                        }
                    ,parcial: true
                });
            }
        });
        $("#sgsetorvinc2").autocomplete({
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
            select: function(event, setor) {
                
                    CB.post({
                        objetos: {
                            "_x_i_fluxoobjeto_idfluxo":<?=$_1_u_fluxo_idfluxo?>
                            ,"_x_i_fluxoobjeto_idobjeto": setor.item.value
                            ,"_x_i_fluxoobjeto_tipoobjeto": 'imgrupo'
                            ,"_x_i_fluxoobjeto_tipo": 'CRIADOR'
                        }
                    ,parcial: true
                });
            }
        });

        //Autocomplete de Setores vinculados
        $("#eventoresp").autocomplete({
            source: jFuncionario,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    lbItem = item.label;
                    
                    return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                };
            },
            select: function(event, funcionario) {
                
            CB.post({
                    objetos: {
                        "_x_i_fluxoobjeto_idfluxo":<?=$_1_u_fluxo_idfluxo?>
                        ,"_x_i_fluxoobjeto_idobjeto": funcionario.item.value
                        ,"_x_i_fluxoobjeto_tipoobjeto": 'pessoa'
                    }
                    ,parcial: true
                });
            }
        });

        //Autocomplete de Setores vinculados
        $("#eventoresp2").autocomplete({
            source: jFuncionariocriador,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    lbItem = item.label;
                    
                    return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                };
            },
            select: function(event, funcionario) {            
                CB.post({
                    objetos: {
                        "_x_i_fluxoobjeto_idfluxo":<?=$_1_u_fluxo_idfluxo?>
                        ,"_x_i_fluxoobjeto_idobjeto": funcionario.item.value
                        ,"_x_i_fluxoobjeto_tipoobjeto": 'pessoa'
                        ,"_x_i_fluxoobjeto_tipo": 'CRIADOR'
                    }
                    ,parcial: true
                });
            }
        });

        $("#eventoresp3").autocomplete({
            source: jFuncionariocriador,
            delay: 0,
            create: function() {
                $(this).data('ui-autocomplete')._renderItem = function(ul, item) {
                    lbItem = item.label;
                    
                    return $('<li>')
                        .append('<a>' + lbItem + '</a>')
                        .appendTo(ul);
                };
            },
            select: function(event, funcionario) {            
                CB.post({
                    objetos: {
                        "_x_i_fluxoobjeto_idfluxo":<?=$_1_u_fluxo_idfluxo?>
                        ,"_x_i_fluxoobjeto_idobjeto": funcionario.item.value
                        ,"_x_i_fluxoobjeto_tipoobjeto": 'pessoa'
                        ,"_x_i_fluxoobjeto_tipo": 'ABERTO'
                    }
                    ,parcial: true
                });
            }
        });

    <? } ?>

    function retiraeventotiporesp(inid){
	    CB.post({
            objetos: {
                "_x_d_fluxoobjeto_idfluxoobjeto":inid
            }
            ,parcial: true
        });
    }

    function selecionaProxStatusOc(inObj,inidstatus,aridstatus)
    {
        $ico=$(inObj);
        if($ico.hasClass("selecionado")){
            $ico.removeClass("fa-circle selecionado").addClass("fa-circle-o");
        }else{
            $ico.removeClass("fa-circle-o").addClass("fa-circle selecionado");
        }
            
        CB.post({
            objetos: {
                "_x_u_fluxostatus_idfluxostatus": inidstatus,
                "_x_u_fluxostatus_fluxoocultar": aridstatus
            },
            parcial: true
        });
    }

    function selecionaProxStatusResp(inObj, idfluxoobjeto, aridstatus, vcampo){
        $ico=$(inObj);
        if($ico.hasClass("selecionado")){
            $ico.removeClass("fa-circle selecionado").addClass("fa-circle-o");
        }else{
            $ico.removeClass("fa-circle-o").addClass("fa-circle selecionado");
        }
            
        CB.post({
            objetos: "_x_u_fluxoobjeto_idfluxoobjeto="+idfluxoobjeto+
                     "&_x_u_fluxoobjeto_"+vcampo+"="+aridstatus,
            parcial: true
        });
    }

    function atEventotiporesp(vthis, idfluxoobjeto){
        CB.post({			
                objetos:"_x_u_fluxoobjeto_idfluxoobjeto="+idfluxoobjeto+
                        "&_x_u_fluxoobjeto_assina="+$(vthis).val()
                ,parcial: true				
        });
    }

    function selecionaProxStatusEv(inObj, inididfluxo, aridstatus)
    {
        $ico=$(inObj);
        if($ico.hasClass("selecionado")){
            $ico.removeClass("fa-circle selecionado").addClass("fa-circle-o");
        }else{
            $ico.removeClass("fa-circle-o").addClass("fa-circle selecionado");
        }
            
        CB.post({
            objetos: {
                "_x_u_fluxo_idfluxo": inididfluxo,
                "_x_u_fluxo_inidstatus": aridstatus
            },
            parcial: true
        });
    }

    //Permite ordenação dos elementos
    $("#eventostatus tbody").sortable({
        update: function(event, objUi){
            ordenaStatus();
        }
    });

    function ordenaStatus(){
        $.each($("#eventostatus tbody").find("tr"), function(i,otr){
            $(this).find(":input[name*=ordem]").val(i);
        });
    }

    //LTM - 25-05-2021 - Retorna o idFluxoStatus Selecionado
    function getjLp(vid)
    {
        $.ajax({
            type: "post",
            url: "ajax/_status.php?vopcao=getLp&vidfluxostatus="+vid+"&vidfluxo=<?=$_1_u_fluxo_idfluxo?>",
            async: false,
            success: function(data) {
                jLp = data;
            }
        });  

        return jLp;
    }

    function callModal(vid, nomeBotao){

        var jLp = getjLp(vid);

        if(jLp === 0){ // Caso não haja LP's para a empresa
            CB.modal({
                titulo: `</strong>Permissões LP [${nomeBotao}]</strong>`,
                corpo: "Nenhuma LP encontrada para a empresa",
                classe: 'trinta',
            });
            return;
        }

        // Corpo Modal
        var $oModal = $(`
            <table class="table table-striped">
                <thead>
                    <tr>
                        <td colspan="2"><input type="text" placeholder="Pesquise a LP" id="_pesquisa_lp_"/></td>
                        <td style="text-align:center;"><button id="_salvar_permissoes_" class="btn btn-success btn-sm"><i class="fa fa-circle"></i> Salvar</button></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <span style="margin-right: 20px;">Filtrar por:</span>
                            <input type="checkbox" class="_filtro_" permissao="r" checked="true"> <span style="margin-right: 20px;">Leitura</span>
                            <input type="checkbox" class="_filtro_" permissao="w" checked="true"> <span style="margin-right: 20px;">Escrita</span>
                            <input type="checkbox" class="_filtro_" permissao="n" checked="true"> <span>Sem Permissão</span>
                        </td>
                    </tr>
                    <tr style="display:none" class="permissao_todas_lps_tela">
                        <td>Mudar Permissão de Todas LP's Tela</td>
                        <td></td>
                        <td>
                            <i class="fa fa-list verde hoverpreto pointer tela_limpar" style="padding-right: 15px;" onclick="mudarPermissao('_permissao_', 'limpar')"></i>
                            <i class="fa fa-eye azul hoverpreto pointer tela_bloquear" style="padding-right: 15px;" onclick="mudarPermissao('_permissao_', 'bloquear')"></i>
                            <i class="fa fa-pencil vermelho hoverpreto pointer tela_escrita" style="padding-right: 15px;" onclick="mudarPermissao('_permissao_', 'escrita')"></i>
                        </td>
                    </tr>
                    <tr style="display:none" class="permissao_todas_lps_botao">
                        <td>Mudar Permissão de Todas LP's Botão</td>
                        <td></td>
                        <td>
                            <i class="fa fa-list verde hoverpreto pointer botao_limpar" style="padding-right: 15px;" onclick="mudarPermissao('_permissaobotao_', 'limpar')"></i>
                            <i class="fa fa-eye azul hoverpreto pointer botao_bloquear" style="padding-right: 15px;" onclick="mudarPermissao('_permissaobotao_', 'bloquear')"></i>
                            <i class="fa fa-pencil vermelho hoverpreto pointer botao_escrita" style="padding-right: 15px;" onclick="mudarPermissao('_permissaobotao_', 'escrita')"></i>
                        </td>
                    </tr>
                    <tr>
                        <th><b>LP</b></th>
                        <th style="text-align:center;">
                            <input name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="marcarTodosProdutoAlerta(this, 'tela')">
                            <b>Permissão Tela</b>
                        </th>
                        <th style="text-align:center;">
                            <input name="marcartodos" title="Marcar/Desmarcar Todos" type="checkbox" onclick="marcarTodosProdutoAlerta(this, 'botao')">
                            <b>Permissão Botão</b>
                        </th>
                    </tr>
                </thead>
                <tbody id="_corpo_modal_"></tbody>
            </table>
        `);

        var oBody = "";

        var objLp = JSON.parse(jLp);

        // Loop de construção das linhas da tabela do Modal
        objLp.forEach((i) => {
            let classe = '', acao = '';
            switch(i.permissao){
                case 'n': classe = "list verde"; break;
                case 'r': classe = "eye azul"; break;
                case 'w': classe = "pencil vermelho"; break;
                default	: classe = "list verde"; break;
            }

            switch(i.permissaobotao){
                case 'n': classebotao = "list verde"; break;
                case 'r': classebotao = "eye azul"; break;
                case 'w': classebotao = "pencil vermelho"; break;
                default	: classebotao = "list verde"; break;
            }

            if(i.idfluxostatuslp == 0){
                acao = 'i';
            }else{
                acao = 'u';
            }

            oBody += `
                <tr name="${i.descricao}">
                    <td style="width:65%;" title="Idlp: ${i.idlp}">${i.descricao}</td>
                    <td _permissao_="${i.permissao}" title="Permissão: ${i.permissao}" permissao="${i.permissao}" idlp="${i.idlp}" acao="${acao}" idfluxostatuslp="${i.idfluxostatuslp}" idfluxostatus="${i.idfluxostatuslp}" style="text-align: center;">
                        <i class="fa fa-${classe} hoverpreto pointer _permissao_"></i>
                    </td>
                    <td _permissaobotao_="${i.permissaobotao}"title="Permissão Botão: ${i.permissaobotao}" permissaobotao="${i.permissaobotao}" idlp="${i.idlp}" acao="${acao}" idfluxostatuslp="${i.idfluxostatuslp}" idfluxostatus="${i.idfluxostatuslp}" style="text-align: center;">
                        <i class="fa fa-${classebotao} hoverpreto pointer _permissaobotao_"></i>
                    </td>
                </tr>
            `;
        });

        // Adiciona linhas da tabela no corpo do Modal
        $oModal.find("#_corpo_modal_").append(oBody);

        // Função para troca de ícone de Permissão
        $oModal.find("td[permissao] i").on('click', function(){
            let icon = $(this);
            let list = "fa-list verde";
            let eye = "fa-eye azul";
            let pencil = "fa-pencil vermelho";
            let td = $(this).parent();

            if(icon.hasClass(list)){
                icon.addClass(eye).removeClass(list);
            }else if(icon.hasClass(eye)){
                icon.addClass(pencil).removeClass(eye);
            }else{
                icon.addClass(list).removeClass(pencil);
            }

            if(td.attr("_permissao_") == 'n' && icon.hasClass(list)){
                td.css('background','');
            }else if(td.attr("_permissao_") == 'r' && icon.hasClass(eye)){
                td.css('background','');
            }else if(td.attr("_permissao_") == 'w' && icon.hasClass(pencil)){
                td.css('background','');
            }else{
                td.css('background','#ffeca1');
            }
        });

        $oModal.find("td[permissaobotao] i").on('click', function(){
            let icon = $(this);
            let list = "fa-list verde";
            let eye = "fa-eye azul";
            let pencil = "fa-pencil vermelho";
            let td = $(this).parent();

            if(icon.hasClass(list)){
                icon.addClass(eye).removeClass(list);
            }else if(icon.hasClass(eye)){
                icon.addClass(pencil).removeClass(eye);
            }else{
                icon.addClass(list).removeClass(pencil);
            }
            
            if(td.attr("_permissaobotao_") == 'n' && icon.hasClass(list)){
                td.css('background','');
            }else if(td.attr("_permissaobotao_") == 'r' && icon.hasClass(eye)){
                td.css('background','');
            }else if(td.attr("_permissaobotao_") == 'w' && icon.hasClass(pencil)){
                td.css('background','');
            }else{
                td.css('background','#ffeca1');
            }
        });

        // Filtro por texto
        $oModal.find("#_pesquisa_lp_").on('keyup', function() {
            var filter, table, td, a, i, txtValue;
            filter = this.value.toUpperCase();
            table = document.getElementById("_corpo_modal_");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                a = tr[i].attributes[0];
                txtValue = a.textContent || a.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        });

        //Filtro por checkbox
        $oModal.find("._filtro_").on('click', function(){

            let checked = $(this).parent().children(":checked");
            let unchecked = $(this).parent().children(":not(:checked)");
            
            checked.each((j,k) => {
                let permissao = $(k).attr('permissao')
                $(`#_corpo_modal_ td[_permissao_='${permissao}']`).show()
            });

            unchecked.each((j,k) => {
                let permissao = $(k).attr('permissao')
                $(`#_corpo_modal_ td[_permissao_='${permissao}']`).hide()
            });

            checked.each((j,k) => {
                let permissao = $(k).attr('permissao')
                $(`#_corpo_modal_ td[_permissaobotao_='${permissao}']`).show()
            });

            unchecked.each((j,k) => {
                let permissao = $(k).attr('permissao')
                $(`#_corpo_modal_ td[_permissaobotao_='${permissao}']`).hide()
            });
        });

        // Salvamento das alterações de permissão
        $oModal.find("#_salvar_permissoes_").on('click', function(){
            var iteraveis = $("#_corpo_modal_ td[permissao]");
            var iteraveisbotao = $("#_corpo_modal_ td[permissaobotao]");
            var objPost = {};

            iteraveis.each((j,k) => {

                let pai = $(k);
                let acao = pai.attr('acao'), permissao = pai.attr('permissao'), idlp = pai.attr('idlp'), idfluxostatuslp = pai.attr('idfluxostatuslp');

                let filho = $(k).children();
                let list = filho.hasClass("fa-list"), eye = filho.hasClass("fa-eye"), pencil = filho.hasClass("fa-pencil");
                
                if(acao == 'i' && !list){
                    //monta insert idlp fluxostatuslp cbpost
                    objPost[`_iModS${j}_i_fluxostatuslp_idlp`] = idlp;
                    objPost[`_iModS${j}_i_fluxostatuslp_permissao`] = (eye)? 'r' : 'w';
                    objPost[`_iModS${j}_i_fluxostatuslp_idfluxostatus`] = vid;

                }else{
                    if(eye && permissao != 'r'){
                        // atualiza para leitura
                        objPost[`_uModS${j}_u_fluxostatuslp_idfluxostatuslp`] = idfluxostatuslp;
                        objPost[`_uModS${j}_u_fluxostatuslp_permissao`] = 'r';

                    }else if(pencil && permissao != 'w'){
                        // atualiza para escrita
                        objPost[`_uModS${j}_u_fluxostatuslp_idfluxostatuslp`] = idfluxostatuslp;
                        objPost[`_uModS${j}_u_fluxostatuslp_permissao`] = 'w';
                    }else if(list && permissao != 'n'){
                        // deleta idfluxostatuslp
                        objPost[`_dModS${j}_d_fluxostatuslp_idfluxostatuslp`] = idfluxostatuslp;
                    }
                }

            });

            iteraveisbotao.each((j,k) => {

                let pai = $(k);
                let acao = pai.attr('acao'), permissao = pai.attr('permissaobotao'), idlp = pai.attr('idlp'), idfluxostatuslp = pai.attr('idfluxostatuslp');

                let filho = $(k).children();
                let list = filho.hasClass("fa-list"), eye = filho.hasClass("fa-eye"), pencil = filho.hasClass("fa-pencil");

                if(acao == 'i' && !list){
                    //monta insert idlp fluxostatuslp cbpost
                    objPost[`_iModS${j}_i_fluxostatuslp_idlp`] = idlp;
                    objPost[`_iModS${j}_i_fluxostatuslp_permissaobotao`] = (eye)? 'r' : 'w';
                    objPost[`_iModS${j}_i_fluxostatuslp_idfluxostatus`] = vid;

                }else{
                    if(eye && permissao != 'r'){
                        // atualiza para leitura
                        objPost[`_uModS${j}_u_fluxostatuslp_idfluxostatuslp`] = idfluxostatuslp;
                        objPost[`_uModS${j}_u_fluxostatuslp_permissaobotao`] = 'r';

                    }else if(pencil && permissao != 'w'){
                        // atualiza para escrita
                        objPost[`_uModS${j}_u_fluxostatuslp_idfluxostatuslp`] = idfluxostatuslp;
                        objPost[`_uModS${j}_u_fluxostatuslp_permissaobotao`] = 'w';
                    }else if(list && permissao != 'n'){
                        // deleta idfluxostatuslp
                        objPost[`_dModS${j}_d_fluxostatuslp_idfluxostatuslp`] = idfluxostatuslp;
                    }
                }

                });

            var keys = Object.keys(objPost);
            if(keys.length == 0){
                return;
            }

            if(!confirm("Deseja realmente fazer essas alterações?")){
                return;
            }

            CB.post({
                objetos: objPost
                ,parcial: true
                ,posPost: function(){
                    CB.oModal.modal('hide');
                }		
            });
            
        });
        
        CB.modal({
            titulo: `</strong>Permissões LP [${nomeBotao}]</strong>`,
            corpo: [$oModal],
            classe: 'cinquenta',
        });
    }

    function marcarTodosProdutoAlerta(vthis, tipo)
    {
        if(tipo == 'tela'){       
            if($(vthis).is(':checked') == true) {     
                $('.permissao_todas_lps_tela').show();
            } else {
                $('.permissao_todas_lps_tela').hide();
            }
        } else {
            if($(vthis).is(':checked') == true) {     
                $('.permissao_todas_lps_botao').show();
            } else {
                $('.permissao_todas_lps_botao').hide();
            }
        }
    }

    function mudarPermissao(tipo, comando)
    {   
        let list = "fa-list verde";
        let eye = "fa-eye azul";
        let pencil = "fa-pencil vermelho";

        $(`.${tipo}`).each((j, k) => {
            let icon = $(k);
            if(comando == 'escrita'){
                icon.addClass(pencil).removeClass(list).removeClass(eye);
            }else if(comando == 'bloquear'){
                icon.addClass(eye).removeClass(list).removeClass(pencil);
            }else{
                icon.addClass(list).removeClass(pencil).removeClass(eye);
            }
        });        
    }

    //# sourceURL=<?=$_SERVER["SCRIPT_NAME"]?>_rodape
</script>
