<?
require_once("../inc/php/functions.php");

class DOCUMENTO 
{ 
    //Lista Tipos de Documentos - utilizado no Evento
    function getTipoDocumento()
    {
        $sqlm = "SELECT idsgdoctipo, rotulo
                   FROM sgdoctipo
                  WHERE status='ATIVO'
                    AND idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
               ORDER BY rotulo;";
        $resm =  d::b()->query($sqlm)  or die("Erro tagtipo campo Prompt sql model/documento:".$sqlm);
        return $resm;
    }

    //Retorna os Documentos Para inserir no Evento - Nativo
    function getDocumentos($inidsgdoctipo) 
    {
        global $JSON;        
        $inidsgdoctipo=str_replace(",", "','", $inidsgdoctipo);

        $sql = "SELECT d.idsgdoc,
                       concat(d.idregistro,'-',d.titulo) as titulo,
                       d.idsgdoctipo
                  FROM sgdoc d
                 WHERE d.idempresa =" . idempresa() . "
                   AND d.idsgdoctipo in ('".$inidsgdoctipo."')
              ORDER BY d.titulo";
        $rts = d::b()->query($sql) or die("getDocumentos model/documento: " . mysqli_error(d::b())." ".$sql);

        $arrtmp = array();
        $i = 0;

        while ($r = mysqli_fetch_assoc($rts)) {
            $arrtmp[$i]["value"] = $r["idsgdoc"];
            $arrtmp[$i]["label"] = $r["titulo"];
            $i++;
        }
       
        return $JSON->encode($arrtmp);
    }

    function getPermissaoDoc($iddoc)
    {
        $sqspPermissao = "SELECT idobjeto FROM fluxostatuspessoa 
                           WHERE idobjeto = ".$_SESSION["SESSAO"]["IDPESSOA"] ." 
                             AND idmodulo = '".$iddoc."' AND modulo = 'documento'
                        UNION
                            SELECT p.idpessoa FROM pessoa p JOIN sgdoc d on(d.criadopor = p.usuario)
                            WHERE d.idsgdoc = ".$iddoc."
                            and p.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA'];
                             ;
        $res = d::b()->query($sqspPermissao) or die("[model-documento] - Erro ao configuracao de Permissão : " . mysql_error() . "<p>SQL:".$sqspPermissao);
        return $res;
    }
}
?>