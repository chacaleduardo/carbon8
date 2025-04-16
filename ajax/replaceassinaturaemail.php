<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    echo JSON_ENCODE([
        'error' => "Erro: Não autorizado."
    ]);
    die;
}

function salvarAssinatura($idwebmailassinaturaobjeto, $html){

    $qr = "UPDATE webmailassinaturaobjeto set htmlassinatura = '".$html."' WHERE idwebmailassinaturaobjeto = ".$idwebmailassinaturaobjeto;
    $rs = d::b()->query($qr);
    if(!$rs){
        return '{"erro":"Erro ao atualizar webmailassinaturaobjeto","message":"Erro ao atualizar assinatura", "id": "'.$idwebmailassinaturaobjeto.'"}';
    }else{
        if(!empty($_POST["pessoas"])){
            $arrPessoas = explode(",",$_POST["pessoas"]);
    
            foreach($arrPessoas as $k => $v){
                $insert = "INSERT INTO `laudo`.`webmailassinaturaobjeto`
                (`idobjeto`,
                `tipoobjeto`,
                `idempresa`,
                `idwebmailassinatura`,
                `htmlassinatura`,
                `idwebmailassinaturatemplate`,
                `tipo`,
                `criadopor`,
                `criadoem`,
                `alteradopor`,
                `alteradoem`
                )
                SELECT ".$v.", 'pessoa',idempresa,idwebmailassinatura,htmlassinatura,idwebmailassinaturatemplate,tipo,'sislaudo',now(),'sislaudo',now()
                from webmailassinaturaobjeto where idwebmailassinaturaobjeto in (".$idwebmailassinaturaobjeto.")";
                
    
                d::b()->query($insert) or die("Falha ao inserir template. SQL: ".$insert);
            }
            
        }
        return '{"message":"Salvo"}';
    }
}

if($_GET["salvar"] == 'Y'){
    cbSetPostHeader('1', 'html');
    if($_GET["gerar"] == 'Y'){
        $i = 0;
        $listIdwebmailassinaturaobjeto = array();
        $listHtml = array();
        foreach($_POST as $key => $value){
            $key = preg_replace('/([\d#])/', '', $key);
            if($key == "idwebmailassinaturaobjeto"){
                $listIdwebmailassinaturaobjeto[$i] = $value;
            }

            if($key == "htmlassinatura"){
                $listHtml[$i] = $value;
                $i++;
            }
        }

        if(isset($_POST['templates']))
        {
            $queryTemplate = "SELECT htmltemplate
                            FROM webmailassinaturatemplate t
                            WHERE t.idwebmailassinaturatemplate = ";

            foreach($_POST['templates'] as $key => $template)
            {
                $query = "$queryTemplate {$template['idtemplate']}";
                $result = d::b()->query($query) or die('Erro ao buscar template: '.mysql_error(d::b()));

                $templateHTML = mysql_fetch_assoc($result)['htmltemplate'];

                $queryAssinaturaEmail = "SELECT nome, cargo, telefone, ramal, celular
                                        FROM assinaturaemailcampos
                                        WHERE idassinaturaemailcampos = {$template['idassinaturaemailcampos']};";

                $resultAssinaturaEmail = d::b()->query($queryAssinaturaEmail) or die('Erro ao buscar assinaturaemailcampos: '.mysql_error(d::b()));
                $replace = mysql_fetch_assoc($resultAssinaturaEmail);

                $queryWebMailTemplate = "SELECT w.idwebmailassinaturatemplate as id, w.descricao, w.htmltemplate, e.* 
                                        FROM webmailassinaturatemplate w 
                                        JOIN empresa e ON (w.idempresa = e.idempresa) 
                                        WHERE w.idwebmailassinaturatemplate = {$template['idtemplate']}";
                
                $resultWebMailTemplate = d::b()->query($queryWebMailTemplate) or die('Erro ao buscar webmailassinaturatemplate: '.mysql_error(d::b()));

                foreach(mysql_fetch_assoc($resultWebMailTemplate) as $keyResult => $item)
                {
                    $replace[$keyResult] = $item;
                }

                salvarAssinatura($_POST['templates'][$key]['idwebmailassinaturaobjeto'], replaceInTemplate($templateHTML, $replace));
            }

            echo "Templates atualizados";
            die;
        }

        $i = count($listIdwebmailassinaturaobjeto);

        if($i == count($listHtml) and $i > 0){
            
            $virg = "";
            echo '[';
            for($j = 0; $j < $i; $j++){
                echo $virg . salvarAssinatura($listIdwebmailassinaturaobjeto[$j], $listHtml[$j]);
                $virg = ",";
            }
            echo ']';
            die;
        }else{
            echo '{"erro":"Número de campos POST enviados"}';
            die;
        }
    }else{
        echo salvarAssinatura($_POST["idwebmailassinaturaobjeto"], $_POST["htmlassinatura"]);
        die;
    }
}else{
    if($_POST["gerar"] == 'Y'){
        if(!empty($_POST["idtemplate"])){
            $arrtmp = array();
            $i = 0;
            foreach($_POST["id"] as $value){
                $arr = geratemplate($value, $_POST["tipo"], $_POST["idtemplate"], $_POST["template"]);
                if($arr){
                    $arrtmp[$i]["html"] = $arr["html"];
                    $arrtmp[$i]["lastinsert"] = $arr["lastinsertwp"];
                    $i++;
                }
            }
            cbSetPostHeader('id',$arr['lastinsertwp']);
            echo $JSON->encode($arrtmp);
            die;
        }
    }else{
        if(!empty($_POST["id"]) and empty($_POST["idtemplate"])){
            $arr = geratemplate($_POST["id"], $_POST["tipo"]);
            if(!$arr){
                cbSetPostHeader('Erro',501);
                die;
            }else{
                cbSetPostHeader('id',$arr['lastinsert']);
                if($_POST["tipo"] == 'PESSOA'){
                    cbSetCustomHeader('idwebmailassinaturaobjeto', $arr["lastinsertwp"]);
                }
                echo $arr['html'];
                die;
            }
        }else{
            if(!empty($_POST["id"]) and !empty($_POST["idtemplate"])){
                $arr = geratemplate($_POST["id"], $_POST["tipo"], $_POST["idtemplate"], $_POST["template"]);
                if(!$arr){
                    cbSetPostHeader('Erro',501);
                    die;
                }else{
                    cbSetPostHeader('id',$arr['lastinsert']);
                    cbSetCustomHeader('idwebmailassinaturaobjeto', $arr["lastinsertwp"]);
                    echo $arr['html'];
                    die;
                }
            }
        }
    }
}

function geratemplate($id, $tipo, $idtemplate = 0, $template = ""){
    $n = $res = $tipov = "";
    switch($tipo){
        case 'PESSOA':
            $sql = "SELECT * FROM assinaturaemailcampos WHERE tipoobjeto = 'COLABORADOR' AND idobjeto = ".$id;
            $res = d::b()->query($sql) or die("Falha ao consultar pessoa. SQL: ".$sql);
            $n = mysql_num_rows($res);
            if($n == 0){
                $qr = "SELECT p.nomecurto as nome, '(34) 3222-5700' as telefone, s.cargo FROM pessoa p left join sgcargo s on (p.idsgcargo = s.idsgcargo) WHERE p.status = 'ATIVO' and p.idpessoa = ".$id;
                $resaux = d::b()->query($qr);
                $rowaux = mysqli_fetch_assoc($resaux);
                d::b()->query("INSERT INTO assinaturaemailcampos (idempresa, tipoobjeto, idobjeto, nome, cargo, telefone, criadopor, criadoem, alteradopor, alteradoem) VALUES (".cb::idempresa().", 'COLABORADOR', ".$id.", '".$rowaux["nome"]."', '".$rowaux["cargo"]."', '".$rowaux["telefone"]."', 'sislaudo', now(), 'sislaudo', now())");
                
                $res = d::b()->query($qr);
                $n = mysql_num_rows($res);
            }
            $tipov = "COLABORADOR";
            break;
        case 'EMAILVIRTUAL':
            $sql = "SELECT * FROM assinaturaemailcampos WHERE tipoobjeto = 'EMAILVIRTUAL' AND idobjeto = ".$id;
            $res = d::b()->query($sql) or die("Falha ao consultar pessoa. SQL: ".$sql);
            $n = mysql_num_rows($res);
            if($n == 0){
                $qr = "SELECT ifnull(ev.titulo,er.titulo) as titulo,ev.email_original AS email, er.telefone as telefone 
                        FROM emailvirtualconf ev
                        LEFT JOIN empresaemails ee ON (ev.idemailvirtualconf = ee.idemailvirtualconf AND ev.idempresa = ee.idempresa)
                        LEFT JOIN empresarodapeemail er ON (ee.tipoenvio = er.tipoenvio AND ev.idempresa = er.idempresa)
                        WHERE ev.idemailvirtualconf = ".$id." 
                        AND ev.status = 'ATIVO'
                        ORDER BY er.titulo DESC LIMIT 1";
                $resaux = d::b()->query($qr);
                $rowaux = mysqli_fetch_assoc($resaux);
                d::b()->query("INSERT INTO assinaturaemailcampos (idempresa, tipoobjeto, idobjeto, nome, cargo, telefone, criadopor, criadoem, alteradopor, alteradoem) VALUES (".cb::idempresa().", 'EMAILVIRTUAL', ".$id.", '".$rowaux["titulo"]."', '".$rowaux["email"]."', '".$rowaux["telefone"]."', 'sislaudo', now(), 'sislaudo', now())");
                
                $res = d::b()->query($qr);
                $n = mysql_num_rows($resaux);
            }
            $tipov = "EMAILVIRTUAL";
            break;
        default:
            return false;
    }

    if($idtemplate == 0 or $template == ""){
        $sqlt = "SELECT idwebmailassinaturatemplate as id, htmltemplate FROM webmailassinaturatemplate WHERE principalempresa = 'Y' AND tipo = '".$tipov."' AND idempresa = ".cb::idempresa();
        $rest = d::b()->query($sqlt) or die("Falha ao consultar webmailassinturatemplate. SQL: ".$sqlt);
        
        if(mysql_num_rows($rest) == 0){
            return false;
        }

        $rt = mysqli_fetch_assoc($rest);
        $idtemplate = $rt["id"];
        $template = $rt["htmltemplate"];
    }

    $sql1 = "SELECT w.idwebmailassinaturatemplate as id, w.descricao, w.htmltemplate, e.* FROM webmailassinaturatemplate w JOIN empresa e ON (w.idempresa = e.idempresa) WHERE w.idwebmailassinaturatemplate = ".$idtemplate;
    $res1 = d::b()->query($sql1) or die("Falha ao consultar templates da empresa. SQL: ".$sql1);
    $n1 = mysql_num_rows($res1);

    if($n1 == 1 and $n == 1){
        

        $html = $template;

        $row = mysqli_fetch_assoc($res);


        if(strpos($html, "_nome_") !== false){
            (!empty($row["nome"])) ? $html = str_replace("_nome_",$row["nome"],$html) : $html = str_replace("_nome_","",$html);
        }

        if(strpos($html, "_cargo_") !== false){
            (!empty($row["cargo"])) ? $html = str_replace("_cargo_",$row["cargo"],$html) : $html = str_replace("_cargo_","",$html);
        }

        if(strpos($html, "_telefone_") !== false){
            (!empty($row["telefone"])) ? $html = str_replace("_telefone_",$row["telefone"],$html) : $html = str_replace("_telefone_","",$html);
        }

        if(strpos($html, "_ramal_") !== false){
            (!empty($row["ramal"])) ? $html = str_replace("_ramal_","Ramal ".$row["ramal"],$html) : $html = str_replace("_ramal_","",$html);
        }

        if(strpos($html, "_celular_") !== false){
            (!empty($row["celular"])) ? $html = str_replace("_celular_",$row["celular"],$html) : $html = str_replace("_celular_","",$html);
        }

        $row1=mysqli_fetch_assoc($res1);

        $endereco = $row1["xlgr"]. " - ".$row1["nro"]." - ".$row1["xbairro"];

        $html = str_replace("_razaosocial_",$row1["razaosocial"],$html);
        $html = str_replace("_empresa_",$row1["nomefantasia"],$html);
        $html = str_replace("_cnpj_",formatarCPF_CNPJ($row1["cnpj"],true),$html);
        $html = str_replace("_inscestadual_",$row1["inscestadual"],$html);
        $html = str_replace("_enderecocompleto_",$endereco,$html);
        $html = str_replace("_site_",$row1["site"],$html);
        $html = str_replace("_ddd_",$row1["DDDPrestador"],$html);
        $html = str_replace("_telefone_",$row1["TelefonePrestador"],$html);
        $html = str_replace("_cep_",formatarCEP($row1["cep"],true),$html);
        $html = str_replace("_mun_",$row1["xmun"],$html);
        $html = str_replace("_uf_",$row1["uf"],$html);


        if($tipo == 'PESSOA'){
            
            $sqlp = "SELECT webmailemail FROM pessoa WHERE idpessoa = ".$id;
            $resp = d::b()->query($sqlp) or die("Erro ao consultar cadastro da pessoa");
            $rp = mysqli_fetch_assoc($resp);

            $sqle = "SELECT * from webmailassinatura where email ='".$rp["webmailemail"]."'";
            $rese = d::b()->query($sqle) or die("Erro ao consultar cadastro da pessoa");
            if (mysqli_num_rows($rese) == 0) {
                $s = "INSERT INTO webmailassinatura (idempresa,email,htmlassinatura,status,criadopor,criadoem,alteradopor,alteradoem) 
                VALUES (".$row1["idempresa"].",'".$rp["webmailemail"]."','".mysqli_real_escape_string(d::b(),$html)."','ATIVO','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
                $r = d::b()->query($s);
                if(!$r) return false;

                $lastinsert = d::b()->insert_id;


                $s1 = "INSERT INTO webmailassinaturaobjeto (idempresa,idobjeto,tipoobjeto,idwebmailassinatura,htmlassinatura,idwebmailassinaturatemplate,tipo,criadopor,criadoem,alteradopor,alteradoem) 
                VALUES (".$row1["idempresa"].",".$id.",'pessoa',".$lastinsert.",'".mysqli_real_escape_string(d::b(),$html)."',".$idtemplate.",'".$tipo."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
                $r1 = d::b()->query($s1) or die("Falha ao criar webmailassinaturaobjeto. SQL: ".$s1);
                if(!$r1) return false;

                $lastinsertwp = d::b()->insert_id;

                $arrtmp = array(
                    'html' => $html,
                    'lastinsert' => $lastinsert,
                    'lastinsertwp' => $lastinsertwp
                );
            }else{
                $rowe = mysqli_fetch_assoc($rese);
                
                $qr = "SELECT idwebmailassinaturaobjeto FROM webmailassinaturaobjeto where idobjeto = ".$id." and idwebmailassinaturatemplate = ".$idtemplate." order by idwebmailassinaturaobjeto desc limit 1";
                $rs = d::b()->query($qr);
                if(mysqli_num_rows($rs) > 0){
                    $rw = mysqli_fetch_assoc($rs);
                    $s1 = "UPDATE webmailassinaturaobjeto SET htmlassinatura = '".mysqli_real_escape_string(d::b(),$html)."', alteradopor = 'sislaudo', alteradoem = now() WHERE idwebmailassinaturaobjeto = ".$rw["idwebmailassinaturaobjeto"];
                    $r1 = d::b()->query($s1);
                    if(!$r1) return false;

                    $lastinsertwp = $rw["idwebmailassinaturaobjeto"];
                }else{
                    $s1 = "INSERT INTO webmailassinaturaobjeto (idempresa,idobjeto,tipoobjeto,idwebmailassinatura,htmlassinatura,idwebmailassinaturatemplate,tipo,criadopor,criadoem,alteradopor,alteradoem) 
                    VALUES (".$row1["idempresa"].",".$id.",'pessoa',".$rowe['idwebmailassinatura'].",'".mysqli_real_escape_string(d::b(),$html)."',".$idtemplate.",'".$tipo."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
                    $r1 = d::b()->query($s1);
                    if(!$r1) return false;

                    $lastinsertwp = d::b()->insert_id;
                }
                

                $arrtmp = array(
                    'html' => $html,
                    'lastinsert' => $rowe['idwebmailassinatura'],
                    'lastinsertwp' => $lastinsertwp
                );
            }


        }else{
            $sqlp = "SELECT email_original as email 
                    FROM emailvirtualconf 
                    WHERE idemailvirtualconf = $id
                    AND status = 'ATIVO'";
            $resp = d::b()->query($sqlp) or die("Erro ao consultar cadastro do grupo de email");
            $rp = mysqli_fetch_assoc($resp);

            $sqle = "SELECT * from webmailassinatura where email ='".$rp["email"]."'";
            $rese = d::b()->query($sqle) or die("Erro ao consultar cadastro da pessoa");

            if (mysqli_num_rows($rese) == 0) {
                $s = "INSERT INTO webmailassinatura (idempresa,email,htmlassinatura,status,criadopor,criadoem,alteradopor,alteradoem) 
                VALUES (".$row1["idempresa"].",'".$rp["email"]."','".mysqli_real_escape_string(d::b(),$html)."','ATIVO','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
                $r = d::b()->query($s);
                if(!$r) return false;
                
                $lastinsert = d::b()->insert_id;

                $s1 = "INSERT INTO webmailassinaturaobjeto (idempresa,idobjeto,tipoobjeto,idwebmailassinatura,htmlassinatura,idwebmailassinaturatemplate,tipo,criadopor,criadoem,alteradopor,alteradoem) 
                VALUES (".$row1["idempresa"].",".$id.",'emailvirtualconf',".$lastinsert.",'".mysqli_real_escape_string(d::b(),$html)."',".$idtemplate.",'".$tipo."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
                $r1 = d::b()->query($s1) or die("Falha ao criar webmailassinaturaobjeto. SQL: ".$s1);
                if(!$r1) return false;

                $lastinsertwp = d::b()->insert_id;

                $arrtmp = array(
                    'html' => $html,
                    'lastinsert' => $lastinsert,
                    'lastinsertwp' => $lastinsertwp
                );
                
            }else {
                $rowe = mysqli_fetch_assoc($rese);

                $qr = "SELECT idwebmailassinaturaobjeto FROM webmailassinaturaobjeto where idobjeto = ".$id." and idwebmailassinaturatemplate = ".$idtemplate." order by idwebmailassinaturaobjeto desc limit 1";
                $rs = d::b()->query($qr);
                if(mysqli_num_rows($rs) > 0){
                    $rw = mysqli_fetch_assoc($rs);
                    $s1 = "UPDATE webmailassinaturaobjeto SET htmlassinatura = '".mysqli_real_escape_string(d::b(),$html)."', alteradopor = 'sislaudo', alteradoem = now() WHERE idwebmailassinaturaobjeto = ".$rw["idwebmailassinaturaobjeto"];
                    $r1 = d::b()->query($s1);
                    if(!$r1) return false;

                    $lastinsertwp = $rw["idwebmailassinaturaobjeto"];
                }else{

                    $s1 = "INSERT INTO webmailassinaturaobjeto (idempresa,idobjeto,tipoobjeto,idwebmailassinatura,htmlassinatura,idwebmailassinaturatemplate,tipo,criadopor,criadoem,alteradopor,alteradoem) 
                    VALUES (".$row1["idempresa"].",".$id.",'emailvirtualconf',".$rowe['idwebmailassinatura'].",'".mysqli_real_escape_string(d::b(),$html)."',".$idtemplate.",'".$tipo."','".$_SESSION["SESSAO"]["USUARIO"]."',now(),'".$_SESSION["SESSAO"]["USUARIO"]."',now())";
                    $r1 = d::b()->query($s1) or die("Falha ao criar webmailassinaturaobjeto. SQL: ".$s1);
                    if(!$r1) return false;

                    $lastinsertwp = d::b()->insert_id;

                }

                $arrtmp = array(
                    'html' => $html,
                    'lastinsert' => $rowe['idwebmailassinatura'],
                    'lastinsertwp' => $lastinsertwp
                );
            }
            
        }

        return $arrtmp;
    }else{
        return false;
    }
}

function replaceInTemplate($template, $replace)
{
    if(strpos($template, "_nome_") !== false){
        (!empty($replace["nome"])) ? $template = str_replace("_nome_",$replace["nome"],$template) : $template = str_replace("_nome_","",$template);
    }

    if(strpos($template, "_cargo_") !== false){
        (!empty($replace["cargo"])) ? $template = str_replace("_cargo_",$replace["cargo"],$template) : $template = str_replace("_cargo_","",$template);
    }

    if(strpos($template, "_telefone_") !== false){
        (!empty($replace["telefone"])) ? $template = str_replace("_telefone_",$replace["telefone"],$template) : $template = str_replace("_telefone_","",$template);
    }

    if(strpos($template, "_ramal_") !== false){
        (!empty($replace["ramal"])) ? $template = str_replace("_ramal_","Ramal ".$replace["ramal"],$template) : $template = str_replace("_ramal_","",$template);
    }

    if(strpos($template, "_celular_") !== false){
        (!empty($replace["celular"])) ? $template = str_replace("_celular_",$replace["celular"],$template) : $template = str_replace("_celular_","",$template);
    }

    $endereco = $replace["xlgr"]. " - ".$replace["nro"]." - ".$replace["xbairro"];

    $template = str_replace("_razaosocial_",$replace["razaosocial"],$template);
    $template = str_replace("_empresa_",$replace["nomefantasia"],$template);
    $template = str_replace("_cnpj_",formatarCPF_CNPJ($replace["cnpj"],true),$template);
    $template = str_replace("_inscestadual_",$replace["inscestadual"],$template);
    $template = str_replace("_enderecocompleto_",$endereco,$template);
    $template = str_replace("_site_",$replace["site"],$template);
    $template = str_replace("_ddd_",$replace["DDDPrestador"],$template);
    $template = str_replace("_telefone_",$replace["TelefonePrestador"],$template);
    $template = str_replace("_cep_",formatarCEP($replace["cep"],true),$template);
    $template = str_replace("_mun_",$replace["xmun"],$template);
    $template = str_replace("_uf_",$replace["uf"],$template);

    return $template;
}
?>