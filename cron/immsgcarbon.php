<?
session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
    include_once("/var/www/carbon8/inc/php/functions.php");
}else{//se estiver sendo executado via requisicao http
    include_once("../inc/php/functions.php");
}
//busca  as configurações para envio da mensagem
$sql="select 
		m.tab,m.modulo,m.rotulomenu,tc.col,ic.idimmsgconf,ic.titulo,ic.tipo,ic.code,ic.mensagem,ic.apartirde
            from "._DBCARBON."._modulo m,immsgconf ic,"._DBCARBON."._mtotabcol tc
            where m.modulo =ic.modulo                 
                and tc.primkey ='Y'         
                and tc.tab = m.tab
                and ic.status='ATIVO'
                and exists (select 1 from immsgconffiltros f where f.valor!=' ' and f.valor is not null and f.idimmsgconf = ic.idimmsgconf)";
$res=d::b()->query($sql) or die("A Consulta na immsgconf falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");

while($row=mysqli_fetch_assoc($res)){
    //busca os filtros para seleção
    $sqlf="select col,sinal,valor,idimmsgconffiltros from immsgconffiltros where valor!=' ' and valor is not null and idimmsgconf =".$row["idimmsgconf"];
    $resf=d::b()->query($sqlf) or die("A Consulta na immsgconffiltros falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlf");
    $and=" ";
    while($rowf=mysqli_fetch_assoc($resf)){
       if($rowf["valor"]!='null' and $rowf["valor"]!=' ' and $rowf["valor"]!=''){
           if($rowf["valor"]=='now'){
                $valor=date("Y-m-d H:i:s");                    
            }else{
                $valor=$rowf["valor"];                        
            }   
            
            if($rowf['sinal']=='in'){
                $strvalor = str_replace(",","','",$valor);
                $clausula.= $and." a.".$rowf["col"]." in ('".$strvalor."')";
            }elseif($rowf['sinal']=='like'){
                $clausula.= $and." a.".$rowf["col"]." like ('%".$valor."%')";
            }else{
                 $clausula.= $and." a.".$rowf["col"]." ".$rowf['sinal']." '".$valor."'";
            }
            $and=" and ";
        }
    }
    // busca na tabela configurada os ids
    $sqlx="SELECT 
            a.".$row['col']." AS idpk, 778 as idpessoa
        FROM
            ".$row["tab"]." a join pessoa p on(p.usuario = a.alteradopor)
        WHERE
            ".$clausula."               
                AND a.alteradoem > '".$row['apartirde']."'
                AND NOT EXISTS( SELECT 
                    1
                FROM
                    immsgconflog l
                WHERE
                    l.idpk = a.".$row['col']."
                        AND l.modulo = '".$row['modulo']."'
                        AND l.idimmsgconf = ".$row['idimmsgconf'].")";
    //echo($sqlx);die;
    $resx=d::b()->query($sqlx) or die("A Consulta na tabela de origem dos dados falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlx");
    while($rowx=mysqli_fetch_assoc($resx)){  
        // insere um log
        $sl="INSERT INTO immsgconflog
            (idempresa,idimmsgconf,idpk,modulo,status,criadopor,criadoem,alteradopor,alteradoem)
            VALUES
            (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$row['idimmsgconf'].",".$rowx['idpk'].",'".$row['modulo']."','ENVIANDO','immsgconf',now(),'immsgconf',now())";
        d::b()->query($sl) or die("Erro ao gerar log [immsgconflog]: ".mysqli_error(d::b()));
            //recupera o ultimo ID inserido
        $idimmsgconflog = mysqli_insert_id(d::b());
         if(empty($idimmsgconflog)){
            //Erro: valor vazio ao recuperar insert_id
            return '{"code":"VALOR_VAZIO_INSERTID_LOG"}';
        }
        /****************************************************************
         *			Cria corpo da mensagem: Insere na msgbody
         ****************************************************************/

        $sm = "INSERT INTO immsgbody (msg, idpessoa,modulo,modulopk,criadoem)
                VALUES ('".$row['mensagem']."',".$rowx['idpessoa'].",'".$row['modulo']."','".$rowx['idpk']."',now())";

        d::b()->query($sm) or die("Erro ao criar msgbody: ".mysqli_error(d::b()));

        //recupera o ultimo ID inserido
        $idimmsgbodyins = mysqli_insert_id(d::b());

        if(empty($idimmsgbodyins)){
            //Erro: valor vazio ao recuperar insert_id
            return '{"code":"VALOR_VAZIO_INSERTID"}';
        }
        
        $sqlc="SELECT 
                    p.idpessoa
                FROM
                    pessoaobjeto po,
                    pessoa p,
                    immsgconfdest c
                WHERE c.objeto = 'sgsetor'
                     and po.idobjeto = c.idobjeto
					 and po.tipoobjeto = 'sgsetor'
                     and c.idimmsgconf=".$row['idimmsgconf']."
                        AND po.idpessoa = p.idpessoa                       
                        AND p.status = 'ATIVO'
                        and p.idpessoa = 1098 -- retirar hermes";
         $resc=d::b()->query($sqlc) or die("A busca dos contatos falhou : " . mysqli_error(d::b()) . "<p>SQL: $sqlc");
        while($rowc=mysqli_fetch_assoc($resc)){
            /****************************************************************
            * Insere a mensagem na tabela de chat com o id da mensagem relacionada
            ****************************************************************/

            $si = "INSERT INTO immsg
                            (idimmsgbody, tipo, idpessoa,statustarefa, criadoem) 
                            VALUES 
                            (".$idimmsgbodyins.",'".$row['tipo']."',". $rowc['idpessoa'] . ",'A',now())";
            d::b()->query($si) or die("Erro ao inserir msg: ".mysqli_error(d::b()));

            //recupera o ultimo ID inserido
            $idmsgins = mysqli_insert_id(d::b());

            $link="?_modulo=".$row['modulo']."&_acao=u&".$row['col']."=".$rowx['idpk'];
            $nome=$row['rotulomenu'].": ".$row['col']."=".$rowx['idpk'];

            $a="INSERT INTO imarq
                    (idempresa,idimmsgbody,arq,nome,tipo)
                    VALUES
                    (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$idimmsgbodyins.",'".$link."','".$nome."','L')";
            d::b()->query($a) or die("Erro ao inserir arquivo: ".mysqli_error(d::b()));   

            if($row['tipo']=="A"){
                /****************************************************************
                * Insere uma assinatura pedente
                ****************************************************************/
                $sa="INSERT INTO carrimbo
                        (idempresa,idpessoa, idobjeto, tipoobjeto,  idobjetoext, tipoobjetoext, status,criadopor, criadoem,alteradopor,alteradoem)
                        VALUES
                        (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$rowc['idpessoa'].", '".$rowx['idpk']."', '".$row['modulo']."', 'PENDENTE','immsgconf', now(),'immsgconf',now());";
                d::b()->query($sa) or die("Erro ao inserir msg: ".mysqli_error(d::b()));    
            }             
        }// while($rowc=mysqli_fetch_assoc($resc)){
        // atualiza o log para sucesso
        $su="update immsgconflog set status='SUCESSO',idimmsgbody=".$idimmsgbodyins." where idimmsgconflog=".$idimmsgconflog;
        d::b()->query($su) or die("Erro ao atualizar log [immsgconflog] : ".mysqli_error(d::b()));        
    }// while($rowx=mysqli_fetch_assoc($resx)){
}//while($row=mysqli_fetch_assoc($res)){