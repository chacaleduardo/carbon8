<?
require_once(dirname(dirname(__FILE__))."/inc/php/functions.php");

class PERMISSAOSHARE 
{
    function PERMISSAOSHARE($acao, $_modulo = NULL, $_primary = NULL, $_idobjeto = NULL, $_idempresa = NULL)
    {
        if(!empty($_POST))
        {
            $_modulo            = $_POST['_modulo'];
            $_primary           = $_POST['_primary'];
            $_idobjeto          = $_POST['_idobjeto'];
            $_idempresa         = $_POST['idempresa'];
        }     

        switch($acao) 
        {
            case 'mostraModalEmpresa':
                print $this->mostraModalEmpresa($_modulo, $_primary, $_idobjeto);
            break;
            case 'permissaoShare':
                print $this->getPermissaoShare($_modulo, $_primary);
            break;
            case 'insereShareEmpresa':
                print $this->insereShareEmpresa($_modulo, $_primary, $_idobjeto, $_idempresa);
            break;
            case 'deletaShareEmpresa':
                print $this->deletaShareEmpresa($_modulo, $_primary, $_idobjeto, $_idempresa);
            break;            
        }
    }

    function mostraModalEmpresa($_modulo, $_primary, $_idobjeto)
    {       
        $_SESSION["SESSAO"]["USUARIO"];
        $share = $this->getShare($_modulo, $_primary, $_idobjeto);
        $arrayEmpresa = $share['arrayEmpresa'];
        if(count($arrayEmpresa) == 0){$arrayEmpresa = "''";}

        $sql = "SELECT e.idempresa, e.sigla, e.nomefantasia
                  FROM "._DBCARBON."._modulo m JOIN objempresa o ON o.idobjeto = m.idmodulo AND m.modulo = '$_modulo' 
                  JOIN empresa e ON e.idempresa = o.empresa AND o.objeto = 'modulo'
                 WHERE e.status = 'ATIVO' AND e.idempresa NOT IN (".cb::idempresa().")";
        $resinfo = d::b()->query($sql) or die("Falha ao pesquisar empresa");

        if ($resinfo) 
        {
            while($rowemp = mysqli_fetch_assoc($resinfo))
            {
                if(in_array($rowemp['idempresa'], $arrayEmpresa)){$checked = 'checked';} else {$checked = '';};
                $arr[$rowemp["idempresa"]]['sigla'] = $rowemp['sigla'];
                $arr[$rowemp["idempresa"]]['nomefantasia'] = $rowemp['nomefantasia'];
                $arr[$rowemp["idempresa"]]['checked'] = $checked;
            }
            $json = json_encode($arr);
            echo($json);
        }
    }

    function getPermissaoShare($_modulo, $_primary)
    {
        $sql = "SELECT 1 FROM share WHERE modulo = '$_modulo' AND jclauswhere like '%$_primary%'";
        $res = d::b()->query($sql);
        $qtd = mysql_num_rows($res);

        if(in_array('permissaoshare', explode(",", str_replace("'", "" ,getModsUsr("MODULOS")))) && $qtd > 0) { $permissao = TRUE; } else { $permissao = FALSE; }

        return $permissao;
    }

    function insereShareEmpresa($_modulo, $_primary, $_idobjeto, $_idempresa)
    {
        $share = $this->getShare($_modulo, $_primary, FALSE, $_idempresa);        

        if(empty($share['aclauswhere'][0]))
        {
            $share['jclauswhere'][$_primary] = $_idobjeto;
        } else{
            $share['jclauswhere'][$_primary] .= ",".$_idobjeto;
        }               

        $this->updateShare($share['jclauswhere'], $share['idshare']);
        cbSetPostHeader('1', 'html');
    }

    function deletaShareEmpresa($_modulo, $_primary, $_idobjeto, $_idempresa)
    {
        $share = $this->getShare($_modulo, $_primary, FALSE, $_idempresa);        
        $key = array_search($_idobjeto, $share['aclauswhere']);
        if($key !== false){
            unset($share['aclauswhere'][$key]);
            $share['jclauswhere'][$_primary] = implode(",", $share['aclauswhere']);
        }        

        $this->updateShare($share['jclauswhere'], $share['idshare']);   
        cbSetPostHeader('1', 'html');
    }

    function updateShare($jclauswhere, $idshare)
    {
        $jclauswhere2 = json_encode($jclauswhere);
        $sql = "UPDATE share SET jclauswhere = '$jclauswhere2' WHERE idshare = '".$idshare."'";
        $res = d::b()->query($sql);
        if(!$res){
            cbSetPostHeader("0", "Erro ao updateShare");
            die();
        }
    }

    function getShare($_modulo, $_primary, $_idobjeto, $_idempresa = NULL)
    {
        if(!empty($_idempresa))
        {
            $_and =  "AND ovalue = '$_idempresa'";
        }

        $sqlShare = "SELECT idshare, jclauswhere, ovalue FROM share WHERE sharemetodo = 'compartilharCbUser".ucfirst($_modulo)."' AND modulo = '$_modulo' $_and";
        $resShare = d::b()->query($sqlShare) or die("Falha ao pesquisar empresa");

        if(empty($_idempresa))
        {
            $arrayEmpresa = array();
            while($rowShare = mysqli_fetch_assoc($resShare))
            {
                $jclauswhere = json_decode($rowShare["jclauswhere"], true);
                $aclauswhere = explode(',', $jclauswhere[$_primary]);
                if(in_array($_idobjeto, $aclauswhere))
                {
                    array_push($arrayEmpresa, $rowShare["ovalue"]);
                }
            }
        } else {
            $rowShare = mysqli_fetch_assoc($resShare);
            $jclauswhere = json_decode($rowShare["jclauswhere"], true);
            $aclauswhere = explode(',', $jclauswhere[$_primary]);
            $idshare = $rowShare["idshare"];
        }

        $share['jclauswhere'] = $jclauswhere;
        $share['aclauswhere'] = $aclauswhere;
        $share['arrayEmpresa'] = $arrayEmpresa;
        $share['idshare'] = $idshare;
        
        return $share;
    }
}

$acao = $_POST['acao'];

if($acao){
    $acao_etapa = new PERMISSAOSHARE($acao);
}
?>