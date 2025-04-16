<?
set_time_limit(0);

require_once("../../php/functions.php");

$idempresa = $_POST['idempresa'];
$razao = $_POST['razao'];
$cnpj = $_POST['cnpj'];
$cor = empty($_POST['cor'])?"#666666":$_POST['cor'];
$filial = $_POST['filial'] ? 'Y' : 'N';

/* share
insert into share (sharemetodo,otipo,okey,ovalue,modulo,descr,acesso,ptipoobj,pobj
,jclauswhere,criadopor,criadoem,alteradopor,alteradoem )
(
select sharemetodo,otipo,okey,13,modulo,descr,acesso,ptipoobj,pobj
,jclauswhere,criadopor,criadoem,alteradopor,alteradoem 
from share where ovalue=2);

inserir modulos na empresa

insert into  objempresa ( idempresa,idobjeto,objeto,empresa,criadopor,criadoem,alteradopor,alteradoem )
(select idempresa,idobjeto,objeto,13,'hermesp',now(),'hermesp',now() from  objempresa o where o.empresa=15 AND o.objeto='modulo')

*/

if(!empty($idempresa) and !empty($razao) and !empty($cnpj)){
    // Cria nova empresa
    if(!empty($_POST['_idempresa_'])){
    	$iid = $_POST['_idempresa_'];
    }else{
        
        $empresa = [
            'idempresa' => $idempresa,
            'razao' => $razao,
            'cnpj' => preg_replace('/\D/', '', $cnpj),
            'cor' => $cor,
            'filial' => $filial
        ];

        list($a, $b, $c) = explode(' ', $empresa['razao']);
        $empresa['sigla'] = "{$a[0]}{$b[0]}{$c[0]}";

        // buscar dados da empresa a ser clonada
        $sqlBuscarEmpresa = "SELECT validade, certificado, senha, nfrazaosocial FROM empresa WHERE idempresa = {$empresa['idempresa']}";
        $empresaOrigem = d::b()->query($sqlBuscarEmpresa) or die("Erro ao buscar empresa: Erro: ".mysqli_error(d::b())."\n".$sqlBuscarEmpresa);
        $empresaOrigem = $empresaOrigem->fetch_array();

    	$sql = "INSERT INTO empresa (
                razaosocial,
                nomefantasia,
                cnpj,
                sigla,
                validade,
                certificado,
                senha,
                filial,
                status,
                nfrazaosocial,
                corsistema,
                criadopor,
                criadoem
            ) VALUES (
                '{$empresa['razao']}',
                '{$empresa['razao']}',
                '{$empresa['cnpj']}',
                '{$empresa['sigla']}',
                '{$empresaOrigem['validade']}',
                '{$empresaOrigem['certificado']}',
                '{$empresaOrigem['senha']}',
                '{$empresa['filial']}',
                'ATIVO',
                '{$empresaOrigem['nfrazaosocial']}',
                '{$empresa['cor']}',
                'sislaudo',
                now()
            )";

	    $res = d::b()->query($sql) or die("Erro ao Criar Empresas: Erro: ".mysqli_error(d::b())."\n".$sql);

	    // Recupera o Último ID inserido
	    $iid = mysqli_insert_id(d::b());
    }

    $query = "";
    $arrtmp = array();

    if($empresa['filial'] == 'Y') {
        // Inserir empresa na matrizconf
        $sqlInserirMatrizConf = "INSERT INTO matrizconf (
            idmatriz,
            idempresa,
            criadopor,
            criadoem,
            alteradopor,
            alteradoem,
            matrizfilial
        ) VALUES (
            {$empresa['idempresa']},
            $iid,
            'sislaudo',
            now(),
            'sislaudo',
            now(),
            '{$empresa['filial']}'
        )";
        $resInserindoMatrizConfi = d::b()->query($sqlInserirMatrizConf) or die("Erro ao Criar Empresas: Erro: ".mysqli_error(d::b())."\n".$sqlInserirMatrizConf);
    }

    // Buscar share e definir acesso para empresa criada e a origem da sua matriz
    $sql="INSERT INTO share (
                sharemetodo,otipo,okey,ovalue,modulo,descr,acesso,ptipoobj,pobj
                ,jclauswhere,criadopor,criadoem,alteradopor,alteradoem 
            )
            (
                SELECT 
                    sharemetodo,otipo,okey,$iid,modulo,descr,acesso,ptipoobj,pobj
                    ,REGEXP_REPLACE(jclauswhere, '\"idempresa\":\"[^\"]*\"', '\"idempresa\":\"{$empresa['idempresa']},$iid\"') as jclauswhere,
                    'sislaudo',now(),'sislaudo',now()
                FROM share 
                WHERE ovalue=".$idempresa."
            )";

    d::b()->query($sql) or die("Erro ao copiar share: Erro: ".mysqli_error(d::b())."\n".$sql);

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Unidades

    $sqlunidade = "SELECT idunidade,unidade,ifnull(idtipounidade,'NULL') as idtipounidade,convestoque,status,ifnull(ord,'NULL') as ord,producao,almoxarifado,cq FROM unidade WHERE status = 'ATIVO' AND idempresa = ".$idempresa;
    $resunidade = d::b()->query($sqlunidade) or die("Erro ao Buscar Unidades da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqlunidade);
    
    $i=0;
    while($rowunidade=mysqli_fetch_assoc($resunidade)){
        $q1 = "insert INTO unidade (idempresa,unidade,idtipounidade,convestoque,status,ord,producao,almoxarifado,cq,criadopor,criadoem) VALUES (".$iid.",'".$rowunidade["unidade"]."',".$rowunidade["idtipounidade"].",'".$rowunidade["convestoque"]."','".$rowunidade["status"]."',".$rowunidade["ord"].",'".$rowunidade["producao"]."','".$rowunidade["almoxarifado"]."','".$rowunidade["cq"]."','sislaudo',now());\n";
        d::b()->query($q1) or die("Erro ao Criar Unidades: Erro: ".mysqli_error(d::b())."\n".$q1);
        $idnew = mysqli_insert_id(d::b());
        $arrtmp["unidade"][$i]["id_ant"] = $rowunidade["idunidade"];
        $arrtmp["unidade"][$i]["id_new"] = $idnew;
        $i++;
    }

    // FIM - Clona Unidades
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Módulos

    $sqlobj = "SELECT idobjeto,objeto,empresa FROM objempresa WHERE objeto = 'modulo' and empresa = ".$idempresa;
    $resobj = d::b()->query($sqlobj) or die("Erro ao Buscar Objempresa da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqlobj);

    while($rowobj=mysqli_fetch_assoc($resobj)){
        $q1 = "insert INTO objempresa (idempresa,idobjeto,objeto,empresa,criadopor,criadoem) VALUES (".$iid.",'".$rowobj["idobjeto"]."','".$rowobj["objeto"]."',".$iid.",'sislaudo',now());\n";
        d::b()->query($q1) or die("Erro ao Criar objempresa: Erro: ".mysqli_error(d::b())."\n".$q1);
    }

    // FIM - Clona Módulos
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona UnidadeObjeto

    $sqluniobj = "SELECT idunidade,idobjeto FROM unidadeobjeto where tipoobjeto='modulo' and idempresa = ".$idempresa;
    $resuniobj = d::b()->query($sqluniobj) or die("Erro ao Buscar UnidadeObjeto da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqluniobj);

    while($rowuniobj=mysqli_fetch_assoc($resuniobj)){
		foreach($arrtmp["unidade"] as $v){
			if($v["id_ant"] == $rowuniobj["idunidade"]){
				$q1 = "insert INTO unidadeobjeto (idempresa,idunidade,idobjeto,tipoobjeto,criadopor,criadoem) VALUES (".$iid.",".$v["id_new"].",'".$rowuniobj["idobjeto"]."','modulo','sislaudo',now());\n";
                d::b()->query($q1) or die("Erro ao Criar unidadeobjeto: Erro: ".mysqli_error(d::b())."\n".$q1);
			}
		}
	}

    // FIM - Clona UnidadeObjeto
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Áreas

    $sqlsa = "select a.idsgarea,ifnull(a.idunidade,'NULL') as idunidade,a.area,a.desc,a.status,a.grupo from sgarea a where a.status = 'ATIVO' and idempresa = ".$idempresa;
    $ressa = d::b()->query($sqlsa) or die("Erro ao Buscar Áreas da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqlsa);

    $i=0;
    while($rowsa=mysqli_fetch_assoc($ressa)){
        if($rowsa["idunidade"] == 'NULL'){
            $q1 = "insert INTO sgarea (`idempresa`,`idunidade`,`area`,`desc`,`status`,`grupo`,`criadopor`,`criadoem`) VALUES (".$iid.",NULL,'".$rowsa["area"]."','".$rowsa["desc"]."','".$rowsa["status"]."','".$rowsa["grupo"]."','sislaudo',now());\n";
            d::b()->query($q1) or die("Erro ao Criar Áreas: Erro: ".mysqli_error(d::b())."\n".$q1);
            $idnew = mysqli_insert_id(d::b());
            $arrtmp["sgarea"][$i]["id_ant"] = $rowsa["idsgarea"];
            $arrtmp["sgarea"][$i]["id_new"] = $idnew;
            $i++;
        }else{
            foreach($arrtmp["unidade"] as $v){
                if($v["id_ant"] == $rowsa["idunidade"]){
                    $newidun = $v["id_new"];
                    $q1 = "insert INTO sgarea (`idempresa`,`idunidade`,`area`,`desc`,`status`,`grupo`,`criadopor`,`criadoem`) VALUES (".$iid.",".$newidun.",'".$rowsa["area"]."','".$rowsa["desc"]."','".$rowsa["status"]."','".$rowsa["grupo"]."','sislaudo',now());\n";
                    d::b()->query($q1) or die("Erro ao Criar Áreas: Erro: ".mysqli_error(d::b())."\n".$q1);
                    $idnew = mysqli_insert_id(d::b());
                    $arrtmp["sgarea"][$i]["id_ant"] = $rowsa["idsgarea"];
                    $arrtmp["sgarea"][$i]["id_new"] = $idnew;
                    $i++;
                    break;
                }
            }
        }
    }

    // FIM - Clona Áreas
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Departamentos

    $sqlsd = "select a.idsgdepartamento,ifnull(a.idunidade,'NULL') as idunidade,a.departamento,a.desc,a.status from sgdepartamento a where a.status = 'ATIVO' and idempresa = ".$idempresa;
    $ressd = d::b()->query($sqlsd) or die("Erro ao Buscar Áreas da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqlsd);

    $i=0;
    while($rowsd=mysqli_fetch_assoc($ressd)){
        if($rowsd["idunidade"] == 'NULL'){
            $q1 = "insert INTO sgdepartamento (`idempresa`,`idunidade`,`departamento`,`desc`,`status`,`grupo`,`criadopor`,`criadoem`) VALUES (".$iid.",NULL,'".$rowsd["departamento"]."','".$rowsd["desc"]."','".$rowsd["status"]."','".$rowsd["grupo"]."','sislaudo',now());\n";
            d::b()->query($q1) or die("Erro ao Criar Departamentos: Erro: ".mysqli_error(d::b())."\n".$q1);
            $idnew = mysqli_insert_id(d::b());
            $arrtmp["sgdepartamento"][$i]["id_ant"] = $rowsd["idsgdepartamento"];
            $arrtmp["sgdepartamento"][$i]["id_new"] = $idnew;
            $i++;
        }else{
            foreach($arrtmp["unidade"] as $v){
                if($v["id_ant"] == $rowsd["idunidade"]){
                    $newidun = $v["id_new"];
                    $q1 = "insert INTO sgdepartamento (`idempresa`,`idunidade`,`departamento`,`desc`,`status`,`grupo`,`criadopor`,`criadoem`) VALUES (".$iid.",".$newidun.",'".$rowsd["departamento"]."','".$rowsd["desc"]."','".$rowsd["status"]."','".$rowsd["grupo"]."','sislaudo',now());\n";
                    d::b()->query($q1) or die("Erro ao Criar Departamentos: Erro: ".mysqli_error(d::b())."\n".$q1);
                    $idnew = mysqli_insert_id(d::b());
                    $arrtmp["sgdepartamento"][$i]["id_ant"] = $rowsd["idsgdepartamento"];
                    $arrtmp["sgdepartamento"][$i]["id_new"] = $idnew;
                    $i++;
                    break;
                }
            }
        }
    }

    // FIM - Clona Departamentos
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Setores

    $sqlss = "select a.idsgsetor,ifnull(a.idunidade,'NULL') as idunidade,a.setor,a.desc,a.status from sgsetor a where a.status = 'ATIVO' and idempresa = ".$idempresa;
    $resss = d::b()->query($sqlss) or die("Erro ao Buscar Áreas da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqlss);

    $i=0;
    while($rowss=mysqli_fetch_assoc($resss)){
        if($rowss["idunidade"] == 'NULL'){
            $q1 = "insert INTO sgsetor (`idempresa`,`idunidade`,`setor`,`desc`,`status`,`grupo`,`criadopor`,`criadoem`) VALUES (".$iid.",NULL,'".$rowss["setor"]."','".$rowss["desc"]."','".$rowss["status"]."','".$rowss["grupo"]."','sislaudo',now());\n";
            d::b()->query($q1) or die("Erro ao Criar Setores: Erro: ".mysqli_error(d::b())."\n".$q1);
            $idnew = mysqli_insert_id(d::b());
            $arrtmp["sgsetor"][$i]["id_ant"] = $rowss["idsgsetor"];
            $arrtmp["sgsetor"][$i]["id_new"] = $idnew;
            $i++;
        }else{
            foreach($arrtmp["unidade"] as $v){
                if($v["id_ant"] == $rowss["idunidade"]){
                    $newidun = $v["id_new"];
                    $q1 = "insert INTO sgsetor (`idempresa`,`idunidade`,`setor`,`desc`,`status`,`grupo`,`criadopor`,`criadoem`) VALUES (".$iid.",".$newidun.",'".$rowss["setor"]."','".$rowss["desc"]."','".$rowss["status"]."','".$rowss["grupo"]."','sislaudo',now());\n";
                    d::b()->query($q1) or die("Erro ao Criar Setores: Erro: ".mysqli_error(d::b())."\n".$q1);
                    $idnew = mysqli_insert_id(d::b());
                    $arrtmp["sgsetor"][$i]["id_ant"] = $rowss["idsgsetor"];
                    $arrtmp["sgsetor"][$i]["id_new"] = $idnew;
                    $i++;
                    break;
                }
            }
        }
    }

    // FIM - Clona Setores
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Módulos das LP's

    $sqllp = "SELECT idlp,descricao,grupo FROM "._DBCARBON."._lp WHERE status = 'ATIVO' AND idempresa = ".$idempresa;
    $reslp = d::b()->query($sqllp) or die("Erro ao Buscar LP's da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqllp);

    $i=0;
    while($rowlp=mysqli_fetch_assoc($reslp)){
        $q1 = "insert INTO "._DBCARBON."._lp (idempresa,descricao,grupo,criadopor,criadoem) VALUES (".$iid.",'".$rowlp["descricao"]."','".$rowlp["grupo"]."','sislaudo',now());\n";
        d::b()->query($q1) or die("Erro ao Criar LP's: Erro: ".mysqli_error(d::b())."\n".$q1);
        $idnew = mysqli_insert_id(d::b());

        $sqllpmod = "SELECT modulo,permissao,solassinatura,ifnull(ord,'NULL') as ord FROM "._DBCARBON."._lpmodulo WHERE idlp = ".$rowlp["idlp"];
        $reslpmod = d::b()->query($sqllpmod) or die("Erro ao Buscar LpMódulo da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqllpmod);
        while($rowlpmod=mysqli_fetch_assoc($reslpmod)){
            $q2 = "insert INTO "._DBCARBON."._lpmodulo (idlp,modulo,permissao,solassinatura,ord) VALUES (".$idnew.",'".$rowlpmod["modulo"]."','".$rowlpmod["permissao"]."','".$rowlpmod["solassinatura"]."',".$rowlpmod["ord"].");\n";
            d::b()->query($q2) or die("Erro ao Criar LpMódulo: Erro: ".mysqli_error(d::b())."\n".$q2);
        }

        $arrtmp["lp"][$i]["id_ant"] = $rowlp["idlp"];
        $arrtmp["lp"][$i]["id_new"] = $idnew;
        $i++;
    }

    $arrpessoas = [8211,97560];

    // VERIFICA A EXISTÊNCIA DE UMA LP DE PROCESSOS
    $sqllppr = "SELECT idlp FROM "._DBCARBON."._lp WHERE status = 'ATIVO' AND descricao = 'PROCESSOS' AND idempresa = ".$iid;
    $reslppr = d::b()->query($sqllppr) or die("Erro ao Buscar LP's Processos da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqllppr);
    $nrows = mysqli_num_rows($reslppr);

    if($nrows > 0){
        // CASO EXISTA LP
        $rowlppr=mysqli_fetch_assoc($reslppr);

        // VERIFICA A EXISTÊNCIA DE UM DEPARTAMENTO DE PROCESSOS
        $sqldppr = "SELECT idsgdepartamento FROM sgdepartamento WHERE status = 'ATIVO' AND departamento = 'Departamento PROCESSOS' AND idempresa = ".$iid;
        $resdppr = d::b()->query($sqldppr) or die("Erro ao Buscar Departamento Processos da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqldppr);
        $nrows1 = mysqli_num_rows($resdppr);
        if($nrows1 > 0){
            // CASO EXISTA DEPARTAMENTO
            $rowdppr=mysqli_fetch_assoc($resdppr);

            // ADICIONA AS PESSOAS DO ARRAY NO DEPARTAMENTO ENCONTRADO
            foreach($arrpessoas as $idpessoa){
                $_q1 = "insert INTO pessoaobjeto (idempresa,idpessoa,idobjeto,tipoobjeto,criadopor,criadoem) VALUES (".$iid.",".$idpessoa.",".$rowdppr["idsgdepartamento"].",'sgdepartamento','sislaudo',now());\n";
                d::b()->query($_q1) or die("Erro ao Criar Pessoa Objeto: Erro: ".mysqli_error(d::b())."\n".$_q1);
            }
        }else{
            // CASO NÃO EXISTA DEPARTAMENTO

            // CRIA O DEPARTAMENTO DE PROCESSOS

            $_q = "insert INTO sgdepartamento (`idempresa`,`departamento`,`status`,`criadopor`,`criadoem`) VALUES (".$iid.",'Departamento PROCESSOS','ATIVO','sislaudo',now());\n";
            d::b()->query($_q) or die("Erro ao Criar Departamento de Processos: Erro: ".mysqli_error(d::b())."\n".$_q);
            $idnew = mysqli_insert_id(d::b());

            // ADICIONA AS PESSOAS DO ARRAY NO DEPARTAMENTO CRIADO
            foreach($arrpessoas as $idpessoa){
                $_q1 = "insert INTO pessoaobjeto (idempresa,idpessoa,idobjeto,tipoobjeto,criadopor,criadoem) VALUES (".$iid.",".$idpessoa.",".$idnew.",'sgdepartamento','sislaudo',now());\n";
                d::b()->query($_q1) or die("Erro ao Criar Pessoa Objeto: Erro: ".mysqli_error(d::b())."\n".$_q1);
            }

            // VINCULA NOVO DEPARTAMENTO CRIADO A LP EXISTENTE
            $q3 = "insert INTO lpobjeto (idempresa,idlp,idobjeto,tipoobjeto,criadopor,criadoem) VALUES (".$iid.",".$rowlppr["idlp"].",".$idnew.",'sgdepartamento','sislaudo',now());\n";
            d::b()->query($q3) or die("Erro ao Criar LpObjeto: Erro: ".mysqli_error(d::b())."\n".$q3);
        }
        
        // LIBERA ACESSO A NOVA EMPRESA AS PESSOAS DO ARRAY
        foreach($arrpessoas as $idpessoa){
            $q4 = "insert INTO objempresa (idempresa,idobjeto,objeto,empresa,criadopor,criadoem) VALUES (".$iid.",".$idpessoa.",'pessoa',".$iid.",'sislaudo',now());\n";
            d::b()->query($q4) or die("Erro ao Criar LpObjeto: Erro: ".mysqli_error(d::b())."\n".$q4);
        }
    }else{
        // CASO NÃO EXISTA LP

        // CRIA LP DE PROCESSOS
        $q3 = "insert INTO "._DBCARBON."._lp (idempresa,descricao,grupo,criadopor,criadoem) VALUES (".$iid.",'PROCESSOS','N','sislaudo',now());\n";
        d::b()->query($q3) or die("Erro ao Criar LP's: Erro: ".mysqli_error(d::b())."\n".$q3);
        $idnew = mysqli_insert_id(d::b());

        // VINCULA MENU SUPERIOR DE RH E MÓDULO DE LPS A NOVA LP CRIADA
        $q6 = "insert INTO "._DBCARBON."._lpmodulo (idlp,modulo,permissao,solassinatura,ord) VALUES (".$idnew.",'rh','w','N',NULL);\n";
        d::b()->query($q6) or die("Erro ao Criar LpMódulo: Erro: ".mysqli_error(d::b())."\n".$q6);
        $q6 = "insert INTO "._DBCARBON."._lpmodulo (idlp,modulo,permissao,solassinatura,ord) VALUES (".$idnew.",'_lp','w','N',NULL);\n";
        d::b()->query($q6) or die("Erro ao Criar LpMódulo: Erro: ".mysqli_error(d::b())."\n".$q6);

        // VERIFICA A EXISTÊNCIA DE DEPARTAMENTO DE PROCESSOS
        $sqldppr = "SELECT idsgdepartamento FROM sgdepartamento WHERE status = 'ATIVO' AND departamento = 'Departamento PROCESSOS' AND idempresa = ".$iid;
        $resdppr = d::b()->query($sqldppr) or die("Erro ao Buscar Departamento Processos da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqldppr);
        $nrows1 = mysqli_num_rows($resdppr);
        if($nrows1 > 0){
            // CASO EXISTA DEPARTAMENTO
            $rowdppr=mysqli_fetch_assoc($resdppr);

            // ADICIONA PESSOAS DO ARRAY AO DEPARTAMENTO ENCONTRADO
            foreach($arrpessoas as $idpessoa){
                $_q1 = "insert INTO pessoaobjeto (idempresa,idpessoa,idobjeto,tipoobjeto,criadopor,criadoem) VALUES (".$iid.",".$idpessoa.",".$rowdppr["idsgdepartamento"].",'sgdepartamento','sislaudo',now());\n";
                d::b()->query($_q1) or die("Erro ao Criar Pessoa Objeto: Erro: ".mysqli_error(d::b())."\n".$_q1);
            }

            // VINCULA DEPARTAMENTO ENCONTRADO A LP CRIADA
            $q3 = "insert INTO lpobjeto (idempresa,idlp,idobjeto,tipoobjeto,criadopor,criadoem) VALUES (".$iid.",".$idnew.",".$rowdppr["idsgdepartamento"].",'sgdepartamento','sislaudo',now());\n";
            d::b()->query($q3) or die("Erro ao Criar LpObjeto: Erro: ".mysqli_error(d::b())."\n".$q3);
        }else{
            // CASO NÃO EXISTA DEPARTAMENTO

            // CRIA O DEPARTAMENTO DE PROCESSOS
            $_q = "insert INTO sgdepartamento (`idempresa`,`departamento`,`status`,`criadopor`,`criadoem`) VALUES (".$iid.",'Departamento PROCESSOS','ATIVO','sislaudo',now());\n";
            d::b()->query($_q) or die("Erro ao Criar Departamento de Processos: Erro: ".mysqli_error(d::b())."\n".$_q);
            $idnewdp = mysqli_insert_id(d::b());

            // ADICIONA AS PESSOAS DO ARRAY AO DEPARTAMENTO CRIADO
            foreach($arrpessoas as $idpessoa){
                $_q1 = "insert INTO pessoaobjeto (idempresa,idpessoa,idobjeto,tipoobjeto,criadopor,criadoem) VALUES (".$iid.",".$idpessoa.",".$idnewdp.",'sgdepartamento','sislaudo',now());\n";
                d::b()->query($_q1) or die("Erro ao Criar Pessoa Objeto: Erro: ".mysqli_error(d::b())."\n".$_q1);
            }

            // VINCULA NOVO DEPARTAMENTO CRIADO A LP CRIADA
            $q3 = "insert INTO lpobjeto (idempresa,idlp,idobjeto,tipoobjeto,criadopor,criadoem) VALUES (".$iid.",".$idnew.",".$idnewdp.",'sgdepartamento','sislaudo',now());\n";
            d::b()->query($q3) or die("Erro ao Criar LpObjeto: Erro: ".mysqli_error(d::b())."\n".$q3);
        }

        // LIBERA ACESSO A NOVA EMPRESA AS PESSOAS DO ARRAY
        foreach($arrpessoas as $idpessoa){
            $q5 = "insert INTO objempresa (idempresa,idobjeto,objeto,empresa,criadopor,criadoem) VALUES (".$iid.",".$idpessoa.",'pessoa',".$iid.",'sislaudo',now());\n";
            d::b()->query($q5) or die("Erro ao Criar LpObjeto: Erro: ".mysqli_error(d::b())."\n".$q5);
        }
    }

    // FIM - Clona Módulos das LP's
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Objetos das LP's
    $lps = "in (";
    $virg = "";
    foreach($arrtmp["lp"] as $v){
        $lps .= $virg.$v["id_ant"];
        $virg = ",";
    }
    $lps .= ")";

    if($lps == 'in ()'){
        $lps = '= 0';
    }

    $sqllpobj = "SELECT * from lpobjeto WHERE idlp ".$lps." AND tipoobjeto <> 'pessoa' AND idempresa = ".$idempresa;
    $reslpobj = d::b()->query($sqllpobj) or die("Erro ao Buscar LpObjeto da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqllpobj);
    while($rowlpobj=mysqli_fetch_assoc($reslpobj)){
        foreach($arrtmp["lp"] as $v){
            if($v["id_ant"] == $rowlpobj["idlp"]){
                $newidlp = $v["id_new"];
                break;
            }
        }
        foreach($arrtmp[$rowlpobj["tipoobjeto"]] as $v){
            if($v["id_ant"] == $rowlpobj["idobjeto"]){
                $query .= "insert into lpobjeto (idempresa,idlp,idobjeto,tipoobjeto,criadopor,criadoem) VALUES (".$iid.",".$newidlp.",".$v["id_new"].",'".$rowlpobj["tipoobjeto"]."','sislaudo',now());\n";
            }
        }
    }
    // FIM - Clona Objetos das LP's
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Snippets

    $sqlsn = "SELECT idsnippet,idempresa,snippet,ifnull(notificacao,'NULL') as notificacao,cssicone,code,tipo,ifnull(msgconfirm,'NULL') as msgconfirm,status from "._DBCARBON."._snippet WHERE status = 'ATIVO' and idempresa = ".$idempresa;
    $ressn = d::b()->query($sqlsn) or die("Erro ao Buscar Snippets da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqlsn);

    $i=0;
    while($rowsn=mysqli_fetch_assoc($ressn)){
        $q1 = "insert INTO "._DBCARBON."._snippet (idempresa,snippet,notificacao,cssicone,code,tipo,msgconfirm,status,criadopor,criadoem) VALUES (".$iid.",'".$rowsn["snippet"]."','".$rowsn["notificacao"]."','".$rowsn["cssicone"]."','".addslashes($rowsn["code"])."','".$rowsn["tipo"]."','".$rowsn["msgconfirm"]."','".$rowsn["status"]."','sislaudo',now());\n";
        d::b()->query($q1) or die("Erro ao Criar Snippets: Erro: ".mysqli_error(d::b())."\n".$q1);
        $idnew = mysqli_insert_id(d::b());
        $arrtmp["_snippet"][$i]["id_ant"] = $rowsn["idsnippet"];
        $arrtmp["_snippet"][$i]["id_new"] = $idnew;
        $i++;
    }

    // FIM - Clona Snippets
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Dashboards

    $sqld = "SELECT iddashboard,dashboard,dashboard_title,panel_id,panel_class_col,panel_title,card_id,card_class_col,card_url,
    card_notification_bg,card_notification,card_color,card_border_color,card_title,card_title_sub,card_value,card_icon,card_title_modal,
    card_url_modal,ifnull(code,'NULL') as code,url,ifnull(idunidade,'NULL') as idunidade,ifnull(especial,'N') as especial,status
    from dashboard WHERE status = 'ATIVO' and idempresa = ".$idempresa;
    $resd = d::b()->query($sqld) or die("Erro ao Buscar Dashboards da Empresa: Erro: ".mysqli_error(d::b())."\n".$sqld);

    $i=0;
    while($rowd=mysqli_fetch_assoc($resd)){
        if($rowd["idunidade"] == 'NULL'){
            $q1 = "insert INTO dashboard (idempresa,dashboard,dashboard_title,panel_id,panel_class_col,panel_title,card_id,card_class_col,card_url,
            card_notification_bg,card_notification,card_color,card_border_color,card_title,card_title_sub,card_value,card_icon,card_title_modal,
            card_url_modal,code,url,idunidade,especial,status,criadopor,criadoem) 
            VALUES (".$iid.",'".$rowd["dashboard"]."','".$rowd["dashboard_title"]."','".$rowd["panel_id"]."','".$rowd["panel_class_col"]."',
            '".$rowd["panel_title"]."','".$rowd["card_id"]."','".$rowd["card_class_col"]."','".addslashes($rowd["card_url"])."',
            '".$rowd["card_notification_bg"]."','".$rowd["card_notification"]."','".$rowd["card_color"]."','".$rowd["card_border_color"]."',
            '".addslashes($rowd["card_title"])."','".addslashes($rowd["card_title_sub"])."','".$rowd["card_value"]."','".$rowd["card_icon"]."','".$rowd["card_title_modal"]."',
            '".$rowd["card_url_modal"]."','".addslashes($rowd["code"])."','".$rowd["url"]."',NULL,'".$rowd["especial"]."','".$rowd["status"]."','sislaudo',now());\n";
            
            d::b()->query($q1) or die("Erro ao Criar Dashboard: Erro: ".mysqli_error(d::b())."\n".$q1);
            $idnew = mysqli_insert_id(d::b());
            $arrtmp["dashboard"][$i]["id_ant"] = $rowd["iddashboard"];
            $arrtmp["dashboard"][$i]["id_new"] = $idnew;
            $i++;
        }else{
            foreach($arrtmp["unidade"] as $v){
                if($v["id_ant"] == $rowd["idunidade"]){
                    $newidun = $v["id_new"];

                    $q1 = "insert INTO dashboard (idempresa,dashboard,dashboard_title,panel_id,panel_class_col,panel_title,card_id,card_class_col,card_url,
                    card_notification_bg,card_notification,card_color,card_border_color,card_title,card_title_sub,card_value,card_icon,card_title_modal,
                    card_url_modal,code,url,idunidade,especial,status,criadopor,criadoem) 
                    VALUES (".$iid.",'".$rowd["dashboard"]."','".$rowd["dashboard_title"]."','".$rowd["panel_id"]."','".$rowd["panel_class_col"]."',
                    '".$rowd["panel_title"]."','".$rowd["card_id"]."','".$rowd["card_class_col"]."','".$rowd["card_url"]."',
                    '".$rowd["card_notification_bg"]."','".$rowd["card_notification"]."','".$rowd["card_color"]."','".$rowd["card_border_color"]."',
                    '".$rowd["card_title"]."','".$rowd["card_title_sub"]."','".$rowd["card_value"]."','".$rowd["card_icon"]."','".$rowd["card_title_modal"]."',
                    '".$rowd["card_url_modal"]."','".addslashes($rowd["code"])."','".$rowd["url"]."',".$newidun.",'".$rowd["especial"]."','".$rowd["status"]."','sislaudo',now());\n";
                    
                    d::b()->query($q1) or die("Erro ao Criar Dashboard: Erro: ".mysqli_error(d::b())."\n".$q1);
                    $idnew = mysqli_insert_id(d::b());
                    $arrtmp["dashboard"][$i]["id_ant"] = $rowd["iddashboard"];
                    $arrtmp["dashboard"][$i]["id_new"] = $idnew;
                    $i++;
                    break;
                }
            }
        }
    }

    // FIM - Clona Dashboards
    // ----------------------------------------------------------------------------------------------------------------------------

    // ----------------------------------------------------------------------------------------------------------------------------
    // INÍCIO - Clona Snippets/Dashboards das LP's

    $_sql = "SELECT * from "._DBCARBON."._lpobjeto WHERE tipoobjeto in ('_snippet','dashboard') and idlp ".$lps;
    $_res = d::b()->query($_sql) or die("Erro ao Buscar Snippets da Empresa: Erro: ".mysqli_error(d::b())."\n".$_sql);

    while($_row=mysqli_fetch_assoc($_res)){
        foreach($arrtmp["lp"] as $v){
            if($v["id_ant"] == $_row["idlp"]){
                $newidlp = $v["id_new"];
                break;
            }
        }
        foreach($arrtmp[$_row["tipoobjeto"]] as $v){
            if($v["id_ant"] == $_row["idobjeto"]){
                $query .= "insert into "._DBCARBON."._lpobjeto (idlp,idobjeto,tipoobjeto,criadopor,criadoem) VALUES ('".$newidlp."',".$v["id_new"].",'".$_row["tipoobjeto"]."','sislaudo',now());\n";
            }
        }
    }

    // FIM - Clona LP's Snippets/Dashboards
    // ----------------------------------------------------------------------------------------------------------------------------

    echo $query;
    $resaux = d::b()->multi_query($query) or die("clona unidades: ".  mysqli_error(d::b()));
}else{
    header("HTTP/1.1 500 Parâmetros inválidos");
	die("Parâmetros Inválidos");
}
?>