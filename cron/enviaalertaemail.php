<?
session_start();
$sessionid = session_id();//PEGA A SESSÃO

ini_set("display_errors","1");
error_reporting(E_ALL);

if (defined('STDIN')){//se estiver sendo executao em linhade comando
  include_once("/var/www/carbon8/inc/php/functions.php");
  include_once("/var/www/carbon8/inc/php/composer/vendor/autoload.php");
  //include_once("/var/www/carbon8/inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");
}else{//se estiver sendo executado via requisicao http
  include_once("../inc/php/functions.php");
  include_once ("../inc/php/composer/vendor/autoload.php");
  //include_once("../inc/composer/vendor/phpmailer/phpmailer/PHPMailerAutoload.php");

}

use \Firebase\JWT\JWT;

session_start();
$_SESSION['IDCOMUNICACAOEXT']="";

function criacomunicacaoext($intipo,$inid,$inobj){
            //CRIAR COMUNICACAO EXTERNA
        $se="INSERT INTO comunicacaoext
                        (idempresa,tipo,status,idobjeto,tipoobjeto,criadopor,criadoem,alteradopor,alteradoem)
                values
                        (1,'".$intipo."','PROCESSANDO',".$inid.",'".$inobj."','sislaudo',sysdate(),'sislaudo',sysdate())";
        $re=d::b()->query($se);
        $_idcomunicacaoext= mysqli_insert_id(d::b());
        
        //echo($_idcomunicacaoext);
        if(empty($_idcomunicacaoext)){
            $sl="INSERT INTO immsgconflog
                        (idempresa,idimmsgconf,status,observacao,criadopor,criadoem,alteradopor,alteradoem)
                        VALUES
                        (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$inid.",'ERRO','Falha ao gerar comunicacao externa','immsgconf',now(),'immsgconf',now())";
            d::b()->query($sl) or die("Erro ao gerar log [immsgconflog] alerta geral: ".mysqli_error(d::b()));
                //recupera o ultimo ID inserido
            $idimmsgconflog = mysqli_insert_id(d::b());
            if(empty($idimmsgconflog)){
                echo("Erro ao buscar id do alerta geral");
                die("Erro ao buscar id do alerta geral");
            }
            //return 'continue';
        }else{
            $_SESSION['IDCOMUNICACAOEXT']=$_idcomunicacaoext;
        }
}//function criacomunicacaoext($intipo,$inid,$inobj){

//cria comunicacao item
function criacomunicacaoextitem($inid,$inobj){
    // CRIA A COMUNICACAOITEM
    $sl="INSERT INTO comunicacaoextitem
            (idempresa,idcomunicacaoext,idobjeto,tipoobjeto,status,criadopor,criadoem,alteradopor,alteradoem)
                VALUES
                (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$_SESSION['IDCOMUNICACAOEXT'].",".$inid.",'".$inobj."','ENVIANDO','immsgconf',now(),'immsgconf',now())";
    d::b()->query($sl);
        //recupera o ultimo ID inserido
    $idcomunicacaoextitem = mysqli_insert_id(d::b());

     if(empty($idcomunicacaoextitem)){
        atualizacomunicacao("ERRO","Erro ao criar comunicacao item : " . mysqli_error(d::b()) . " sql=".$sl);
        die("Erro ao criar comunicacao item");
        //return 'continue';
    }else{
        $_SESSION['IDCOMUNICACAOEXTITEM']=$idcomunicacaoextitem;
    } 
}//function criacomunicacaoextitem($inid,$inobj){

// CRIA A COMUNICACAOITEM
function criacomunicacaoextdest($inid, $inobj,$indestino){
    
    $sl="INSERT INTO comunicacaoextdest
        (idempresa,idcomunicacaoextitem,idobjeto,tipoobjeto,destino,status,criadopor,criadoem,alteradopor,alteradoem)
        VALUES
        (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$_SESSION['IDCOMUNICACAOEXTITEM'].",".$inid.",'".$inobj."','".$indestino."','PROCESSANDO','sislaudo',now(),'sislaudo',now())";
    //die($sl);
    d::b()->query($sl);
        //recupera o ultimo ID inserido
    $idcomunicacaoextdest = mysqli_insert_id(d::b());

     if(empty($idcomunicacaoextdest)){
        atualizacomunicacao("ERRO","Erro ao criar comunicacao destino : " . mysqli_error(d::b()) . " sql=".$sl);
        //return 'continue';
        die("Erro ao criar comunicacao destino");
    }else{
        $_SESSION['IDCOMUNICACAOEXTDEST']=$idcomunicacaoextdest;
    }   
   
}//function criacomunicacaoextdest($inid, $inobj,$indestino){

function atualizacomunicacao($instatus,$inmsg){
    $vinmsg=str_replace("'"," ",$inmsg);
    $su="update comunicacaoext set status='".$instatus."',conteudo='".$vinmsg."',alteradoem=sysdate(),alteradopor='sislaudo' where idcomunicacaoext=".$_SESSION['IDCOMUNICACAOEXT'];
    $ru=d::b()->query($su);
    if(!$ru){
        die("1-Falha ao atualizar comunicacaoext : " . mysqli_error(d::b()) . "<p>SQL: $su");
    }  
}

function atualizacomunicacaoextitem($instatus,$inmsg){
    $vinmsg=str_replace("'"," ",$inmsg);
    $su="update comunicacaoextitem set status='".$instatus."',conteudo='".$vinmsg."',alteradoem=sysdate(),alteradopor='sislaudo' where idcomunicacaoextitem=".$_SESSION['IDCOMUNICACAOEXTITEM'];
    $ru=d::b()->query($su);
    //die($su);
    if(!$ru){
       // die("1-Falha ao atualizar comunicacaoextdest : " . mysqli_error(d::b()) . "<p>SQL: $su");
        atualizacomunicacao("ERRO","1-Falha ao atualizar comunicacaoextdest : " . mysqli_error(d::b()) . " sql=".$su);
    }
    if($instatus=="ERRO"){
        atualizacomunicacao("ERRO",$vinmsg);
    }
   
}

function atualizacomunicacaodest($instatus,$inmsg){
    $vinmsg=str_replace("'"," ",$inmsg);
    $su="update comunicacaoextdest set status='".$instatus."',conteudo='".$vinmsg."',alteradoem=sysdate(),alteradopor='sislaudo' where idcomunicacaoextdest=".$_SESSION['IDCOMUNICACAOEXTDEST'];
    $ru=d::b()->query($su);
    //die($su);
    if(!$ru){
       // die("1-Falha ao atualizar comunicacaoextdest : " . mysqli_error(d::b()) . "<p>SQL: $su");
        atualizacomunicacaoextitem("ERRO","1-Falha ao atualizar comunicacaoextdest : " . mysqli_error(d::b()) . " sql=".$su);
    }
    if($instatus=="ERRO"){
        atualizacomunicacaoextitem("ERRO",$vinmsg);
    }
   
}

function alertageral($inidimmsgconf,$idpk,$modulo,$msg,$status){
    $vinmsg=str_replace("'"," ",$msg);
       $sl="INSERT INTO immsgconflog
                        (idempresa,idimmsgconf,idpk,modulo,status,observacao,criadopor,criadoem,alteradopor,alteradoem)
                        VALUES
                        (".$_SESSION["SESSAO"]["IDEMPRESA"].",".$inidimmsgconf.",".$idpk.",'".$modulo."','".$status."','".$vinmsg."','immsgconf',now(),'immsgconf',now())";
        $res=d::b()->query($sl);
        if(!$res){
            echo("Erro gerar  alerta geral".$msg);
            die();
        }
}
 function novolog($idobjeto,$tipoobjeto,$modulo,$mensagem,$status){
    /*
     $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
            values (".$row["idorcamento"].",'orcamento','EMAILORCSERV','".$ret1."','ERRO',sysdate())";
    */

       $vinmsg=str_replace("'"," ",$mensagem);
   
        $sql = "insert into log (idobjeto,tipoobjeto,tipolog,log,status,criadoem)
                values (".$idobjeto.",'".$tipoobjeto."','".$modulo."','".$vinmsg."','".$status."',sysdate())";
        $res=d::b()->query($sql);   

   
								
 }



   //busca  as configurações para envio da mensagem
    $sql="select 
		m.tab,m.modulo,m.rotulomenu,m.urldestino,ic.tabela,tc.col,ic.idimmsgconf,ic.assunto,ic.tipo,ic.mensagem,ic.emailteste,ic.apartirde,ic.emailfrom,ic.emailcco,ic.rotulofrom,ic.idrodapeemail,ic.modulodest,ic.expiraem
	from carbonnovo._modulo m,immsgconf ic,carbonnovo._mtotabcol tc
	where m.modulo =ic.modulo                 
            and tc.primkey ='Y'         
            and tc.tab = m.tab
            and ic.tipo in('E','ET','EP')
            and ic.status='ATIVO'
           ";
    $res=d::b()->query($sql) or die("A Consulta na immsgconf falhou : " . mysqli_error(d::b()) . "<p>SQL: $sql");
    
    $qtd= mysqli_num_rows($res);
    echo($qtd." Alertas selecionados \n");
    
    if(!$res){
        alertageral(9999,999,'enviaalertaemail',"A Consulta na immsgconf falhou : " . mysqli_error(d::b()) . "<p>SQL:". $sql,"ERRO");
    }

    // Gera identificador do envio do email.
    $envioid = geraIdEnvioEmail();

    while($row=mysqli_fetch_assoc($res)){
        
        $intipo=$row["modulo"];
        //CRIAR A COMUNICACAO
        $retcom=criacomunicacaoext($intipo,$row["idimmsgconf"],'immsgconf');
        
        //busca os filtros para seleção
            $sqlf="select col,sinal,valor,nowdias,idimmsgconffiltros,substituir,substituirtit,nomearq,extensaoarq from immsgconffiltros where idimmsgconf =".$row["idimmsgconf"];
            $resf=d::b()->query($sqlf); 
            if(!$resf){
                atualizacomunicacao("ERRO","A Consulta na immsgconffiltros falhou : " . mysqli_error(d::b()) . " sql=".$sqlf);                
                alertageral($row["idimmsgconf"],$row["idimmsgconf"],'immsgconf',"A Consulta na immsgconffiltros falhou","ERRO");
                die("A Consulta na immsgconffiltros falhou : ");
            }
            $qtdf=mysqli_num_rows($resf);
            $and=" and ";
            if($qtdf>0){
                $clausula="";
                $arrret=array();
                while($rowf=mysqli_fetch_assoc($resf)){                    
                    
                    $arrret[$rowf['col']]=$rowf['substituir'];
                    $arrretitulo[$rowf['col']]=$rowf['substituirtit'];
                    
                    //nome do arquivo para anexar depois
                    if(!empty($rowf['nomearq']) and !empty($rowf['extensaoarq'])){
                        $arrnomearq[$rowf['col']]=$rowf['nomearq'];
                        $arrextarq[$rowf['col']]=$rowf['extensaoarq'];
                    }          
                    
                    //ECHO($rowf['substituir']); die();
                 
                   if($rowf["valor"]!='null' and $rowf["valor"]!=' ' and $rowf["valor"]!='' and !empty($rowf["valor"])){
                       if($rowf["valor"]=='now'){
                            if(!empty($rowf["nowdias"])){
                                $date=date("Y-m-d H:i:s");
                                $valor=date('Y-m-d H:i:s', strtotime($date. ' - '.$rowf["nowdias"].' day'));
                            }else{
                                $valor=date("Y-m-d H:i:s"); 
                            }
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
                       
                    }
                }
            }
        
        // busca na tabela configurada os ids
        $sqlx="SELECT 
                a.".$row['col']." AS idpk,a.* 
            FROM
                ".$row["tab"]." a 
            WHERE a.alteradoem > '".$row['apartirde']."'
           ".$clausula."
                    AND NOT EXISTS( SELECT 1 FROM comunicacaoext l,comunicacaoextitem i 
                                        WHERE i.idobjeto = a.".$row['col']."
                                        AND i.idcomunicacaoext = l.idcomunicacaoext
                                        AND i.tipoobjeto = '".$row['modulo']."' 
                                        AND i.status='SUCESSO'
                                        AND l.idobjeto =".$row['idimmsgconf']." 
                                        AND l.tipoobjeto ='immsgconf')";
   
        //echo($sqlx);die;
        $resx=d::b()->query($sqlx); 
        $qtdx= mysqli_num_rows($resx);
        echo($qtdx." Registros selecionados \n");
        if(!$resx){
            atualizacomunicacao("ERRO","A Consulta na tabela de origem dos dados falhou : " . mysqli_error(d::b()) . " sql=".$sqlx);
            alertageral($row["idimmsgconf"],$row["idimmsgconf"],'immsgconf'," Consulta na tabela de origem dos dados falhou".$sqlx,"ERRO");
            //continue;
            //die("A Consulta na tabela de origem dos dados falhou");
        }else{  
            $arrColunas = mysqli_fetch_fields($resx);
            while($rowx=mysqli_fetch_assoc($resx)){ 
                $erro=0;
                //cria comunicacao item
                $retcricomexti=criacomunicacaoextitem($rowx["idpk"], $row['modulo']);
               /* if($retcricomexti=='continue'){
                    continue;
                }
                * 
                */

                //carrega a mensagem
                $mensagem1 = $row["mensagem"];
                //carrega o assunto titulo email
                $assunto=$row["assunto"];
                // Substitui os campos conforme configurado
                foreach ($arrColunas as $col) {
                        //$arrret[$i][$col->name]=$robj[$col->name];
                        //$arrret[$col->name];               

                    $mensagem1 = str_replace($arrret[$col->name], $rowx[$col->name], $mensagem1);                
                    $assunto = str_replace($arrretitulo[$col->name], $rowx[$col->name], $assunto);
                }//foreach ($arrColunas as $col) {


                $sqlc="select p.idpessoa,p.nome,p.email,s.idsgsetor as idlp,p.idtipopessoa,p.usuario,p.idempresa
                    from pessoacontato c join pessoa p left join sgsetor s on (p.idtipopessoa=s.idtipopessoa )
                    where p.status='ATIVO'
                    and p.email is not null
                    and c.idpessoa=".$rowx["idpessoa"]."
                    and c.idcontato = p.idpessoa";

                //echo($sqlc);die;
                $resc=d::b()->query($sqlc);

                if(!$resc){
                    atualizacomunicacaoextitem("ERRO","A Consulta dos contatos  falhou : " . mysqli_error(d::b()) . " sql=".$sqlc);
                    alertageral($row["idimmsgconf"],$rowx["idpessoa"],'pessoa'," A Consulta dos contatos  falhou".$sqlc,"ERRO");
                    novolog($rowx["idpk"],$row['tabela'],$row['modulo']," A Consulta dos contatos ".$rowx["idpessoa"]." falhou","ERRO");
                    //continue;
                    //die("A Consulta dos contatos  falhou ");
                }else{
                    while($rowc=mysqli_fetch_assoc($resc)){ 

                        $sqlexist="select count(*) as existe 
                                    from (	
                                        SELECT 
                                                p.idpessoa, p.nome
                                        FROM
                                                pessoa p,
                                                immsgconfdest c
                                        WHERE
                                                c.objeto = 'pessoa'
                                                and c.status='ATIVO'
                                                and c.idobjeto = p.idpessoa
                                                AND p.idpessoa = ".$rowc["idpessoa"]."
                                                AND c.idimmsgconf = ".$row["idimmsgconf"]."
                                                AND p.status = 'ATIVO'
                                    ) as u";

                        $rexist=d::b()->query($sqlexist);
                        $qtdexist=mysqli_num_rows($rexist);
                        if($qtdexist<1){
                          atualizacomunicacaoextitem("ERRO","O contato não possui o alerta " . mysqli_error(d::b())." sql=".$sqlexist); 

                          alertageral($row["idimmsgconf"],$rowc["idpessoa"],'pessoa'," O contato não possui o alerta ".$sqlexist,"ERRO");
                          novolog($rowx["idpk"],$row['tabela'],$row['modulo']," O contato ".$rowc["idpessoa"]."não possui o alerta","ERRO");
                          //continue;
                          //die("A Consulta para verificar se contato possui o alerta falhou : ");
                        }else{

                            $rexiste=mysqli_fetch_assoc($rexist);
                            if($rexiste>0){
                               $mensagem=$mensagem1;     
                                $retcriacomextdest=criacomunicacaoextdest($rowc["idpessoa"],'pessoa',$rowc['email']);  
                                /*
                                if($retcriacomextdest=="continue"){
                                    continue;
                                }
                                */

                                if(!empty($rowc["idlp"])){
                                    $strlp = $rowc["idlp"];
                                }else{//if(!empty($rowc["idlp"])){

                                    $slps="select l.idlp from pessoaobjeto po,"._DBCARBON."._lp l where po.idpessoa=".$rowc["idpessoa"]." and l.idlp=po.idobjeto and po.tipoobjeto = 'sgsetor'";                            
                                    $rlp = mysql_query($slps); 
                                    
                                    $qtdlp= mysqli_num_rows($rlp);
                                            
                                    if($qtdlp<1){
                                        $slps="select l.idlp from pessoa p,sgsetor s,"._DBCARBON."._lp l 
                                                where p.idtipopessoa = s.idtipopessoa 
                                                and p.idpessoa=".$rowc["idpessoa"]." 
                                                and l.idlp=s.idsgsetor";
                                        $rlp = mysql_query($slps);
                                        $qtdlp= mysqli_num_rows($rlp);
                                        if($qtdlp<1){
                                            atualizacomunicacaodest('ERRO',"1-O usuário nao possui configuracao de LP ou setor de Atuacao");
                                            alertageral($row["idimmsgconf"],$rowc["idpessoa"],'pessoa',"1-O usuário nao possui configuracao de LP ou setor de Atuacao","ERRO");
                                            novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"1-O usuário nao possui configuracao de LP ou setor de Atuacao","ERRO");
                                        }
                                    }
                                    $arrRet = array();
                                    $arrRetAspas = array();
                                    while($r=mysql_fetch_assoc($rlp)){
                                        $arrRet[]=$r["idlp"];
                                        $arrRetAspas[]="'".$r["idlp"]."'";
                                    }

                                    $strlp= implode(",", $arrRetAspas);                         

                                }//if(!empty($rowc["idlp"])){

                                if(empty($strlp)){
                                    atualizacomunicacaodest('ERRO',"O usuário nao possui configuracao de LP ou setor de Atuacao");
                                    alertageral($row["idimmsgconf"],$rowc["idpessoa"],'pessoa',"O usuário nao possui configuracao de LP ou setor de Atuacao","ERRO");
                                    novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"O usuário nao possui configuracao de LP ou setor de Atuacao","ERRO");
                                    //continue;
                                    //die("O usuário nao possui configuracao de LP ou setor de Atuacao");
                                }else{      

                                    if($row["tipo"]=='EP' or $row["tipo"]=='ET'){// se tiver token
                                        //gera data expieracao
                                        $sdate = date("Y-m-d H:i:s");
                                        $date=date('Y-m-d H:i:s', strtotime($sdate. ' + '.$row["expiraem"].' days')); 

                                        // gravar o usuario gerado
                                        $sqlrand="select FLOOR(RAND()*1000000000) as user";
                                        $resrand=d::b()->query($sqlrand);
                                        $rowrand=mysqli_fetch_assoc($resrand);
                                        $usuario=$rowrand['user'];

                                        $su="update comunicacaoextdest set usuario='".$usuario."',alteradoem=sysdate(),alteradopor='sislaudo' where idcomunicacaoextdest=".$_SESSION['IDCOMUNICACAOEXTDEST'];
                                        $ru=d::b()->query($su);
                                        
                                        
                                        if(empty($usuario)){
                                            atualizacomunicacaodest('ERRO',"Erro ao gerar usuario");
                                            alertageral($row["idimmsgconf"],$rowc["idpessoa"],'pessoa',"Erro ao gerar usuario","ERRO");
                                            novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"Erro ao gerar usuario","ERRO");
                                        }

                                        /*
                                         * 8	Contato Fornecedor FORNECEDOR
                                         * 4	Contato Oficial	   OFICIAL
                                         */
                                        /*
                                            if(empty($rowc["usuario"]) and $rowc["idpessoa"]==8 ){
                                                $usuario="FORNECEDOR";
                                            }elseif(empty($rowc["usuario"]) and $rowc["idpessoa"]==4){                              
                                                $usuario="OFICIAL";
                                            }elseif(!empty($rowc["usuario"])){
                                                $usuario=$rowc["usuario"];
                                            }else{
                                                //atualizacomunicacaodest('ERRO',"O contato id ".$rowc["idpessoa"]." nao possui usuário");
                                                //continue;
                                                //die("O contato id ".$rowc["idpessoa"]." nao possui usuário");
                                                 $usuario="CONTATO";
                                            }
                                        */
                                        /*
                                        * Cria token JWT e devolve ao cliente atavés do header Http
                                        */
                                        $token = array(
                                                "iss" => "sislaudo",
                                                "exp" => strtotime($date),                             
                                                "idlp" => $strlp,
                                                "idtipopessoa" => $rowc["idtipopessoa"],
                                                "idpessoa" => $rowc["idpessoa"],
                                                "usuario" => $usuario,
                                                "nome" => $rowc["nome"],
                                                "idempresa" => $rowc["idempresa"]
                                        );
                                        //echo(_CPRIK); die;
//print_r($token); die;
                                       $jwt = JWT::encode($token, _JWTKEY, 'HS256');

                                        if(empty($jwt)){
                                            atualizacomunicacaodest('ERRO',"Erro ao gerar token");
                                            alertageral($row["idimmsgconf"],$rowc["idpessoa"],'pessoa',"Erro ao gerar token","ERRO");
                                            novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"Erro ao gerar token","ERRO");
                                            //continue;
                                            die("Erro ao gerar token");
                                        }

                                        if(!empty($row["modulodest"])){
                                            $modulourl="?_modulo=".$row["modulodest"]."&_acao=u";
                                        }else{
                                            $modulourl=$row["urldestino"]."?_acao=u";
                                        }

                                        if($row["tipo"]=='EP'){
                                            $gerapdf="&gerapdf=Y";
                                        }else{
                                           $gerapdf=""; 
                                        }
                                        $urlservidor="https://sislaudo.laudolab.com.br/";
                                        $stringurl=$urlservidor.$modulourl."&".$row['col']."=".$rowx["idpk"]."".$gerapdf."&_jwt=".$jwt;                        

                                        //$mensagem =$mensagem. "<a href='".$stringurl."'> aqui </a>";
                                        $mensagem= str_replace("urllink", "<a href='".$stringurl."'>aqui</a>", $mensagem);

                                        //echo($mensagem); die;
                                    }
                                    if(!empty($row["idrodapeemail"])){// inserir rodapé
                                        $sqlr="select * from rodapeemail where idrodapeemail=".$row["idrodapeemail"];
                                        $resr= d::b()->query($sqlr); 
                                        if(!$resr){
                                           atualizacomunicacaodest("ERRO","Erro ao buscar rodape de email : ".mysql_error()." sql=".$sqlr);
                                           alertageral($row["idimmsgconf"],$row["idrodapeemail"],'rodapeemail',"Erro ao buscar rodape de email","ERRO");
                                           novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"Erro ao buscar rodape de email","ERRO");
                                           //continue;
                                           die("Erro ao buscar rodape de email");
                                        }
                                        $rowr=mysqli_fetch_assoc($resr);

                                        $rodapeemailhtml = $rowr["valor"];
                                        $mensagem=$mensagem."<br><p>".$rodapeemailhtml;               
                                    }//if(!empty($row["idparaplweb"])){

                                    $mensagemhtm = "<html><body style='font-family:Arial, Tahoma;font-size:14px;'>".$mensagem."</body></html>";

                                    //da explode para pegar os emails
                                    if(!empty($row["emailteste"])){
                                        $stremail = explode(",",$row['emailteste']);
                                        $emailtot=$row['emailteste'];
                                    }elseif(!empty($rowc['email'])){
                                        $stremail = explode(",",$rowc['email']);
                                        $emailtot=$rowc['email'];
                                    }
                                    
                                    if(empty($stremail)){
                                        atualizacomunicacaodest('ERRO','Nao possui email para envio'); 
                                        alertageral($row["idimmsgconf"],$rowc["idpessoa"],'pessoa',"Nao possui email para envio","ERRO"); 
                                        novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"Contato ".$rowc["idpessoa"]." nao possui email para envio","ERRO");
                                    }else{
                                        //$stremail = explode(",",'hermespedro@yahoo.com.br,fabio@laudolab.com.br');
                                        
                                        $su="update comunicacaoextdest set destino='".$emailtot."',alteradoem=sysdate(),alteradopor='sislaudo' where idcomunicacaoextdest=".$_SESSION['IDCOMUNICACAOEXTDEST'];
                                        $ru=d::b()->query($su);
                                        
                                        for($i=0;$i<count($stremail);$i++){
                                            $ret="";
                                            $emailunico = $stremail[$i];
                                           // echo $emailunico;

                                            /************************CABECALHO E TEXTO**************************/
                                            /*** FROM***/
                                            $emailFrom=$row["emailfrom"];
                                            $nomeFrom=$row["rotulofrom"];
                                            /***DESTINATARIO***/
                                            $emailDest=$emailunico;
                                            //$emailDest='hermespedro@yahoo.com.br';
                                            $emailDestNome=$rowc['nome'];
                                            /***CCO***/
                                            $emailDestCCO=$row["emailcco"];
                                            $emailDestCCONome=$row["rotulofrom"];
                                            /*** ASSUNTO***/
                                            //$assunto=$row["assunto"];
                                            /*** TEXTO***/
                                            /*$mensagem = 'Formulário gerado via website'.'<br/>';
                                            $mensagem .= '-------------------------------<br/><br/>';
                                            $mensagem .= 'Nome: hermes pedro <br/>';
                                            $mensagem .= 'E-mail: resultados@laudolab.com.br<br/>';
                                            $mensagem .= 'Assunto: teste do teste<br/>';
                                            $mensagem .= '-------------------------------<br/><br/>';
                                            $mensagem .= 'Mensagem: "teste"<br/>';
                                            */	                                       

                                           // ATUALIZA COMUNICACAO PARA ENVIANDO
                                           //atualizacomunicacao('ENVIANDO',$mensagem);                            
                                            //echo($mensagemhtm); die;   
                                            /******************************CONFIGURACOES E ENVIO*****************************************/

                                            $mail = new PHPMailer();
                                            $mail->IsSMTP();
											$mail->SMTPDebug=2;
                                            $mail->SMTPAuth  = false;
											$mail->SMTPAutoTLS = false;
                                            //$mail->Charset   = 'utf8_decode()';
											$mail->CharSet = "UTF-8";
                                            $mail->Host  = '192.168.0.15';
                                            $mail->Port  = '587';
                                            //$mail->Username  = "admin_laudolab";
                                            //$mail->Password  = "37383738";
                                            $mail->From  = $emailFrom;
                                            $mail->FromName  = $nomeFrom;
                                            $mail->IsHTML(true);
                                            $mail->Subject  = $assunto;
                                            $mail->Body  = $mensagemhtm;
                                            //email destino
                                            $mail->AddAddress($emailDest,$emailDestNome);
                                            //email copia oculta para o primeiro email da string email a ser enviada
                                            if(!empty($emailDestCCO) and $i==0){
                                                $mail->AddBCC($emailDestCCO, $emailDestCCONome);
                                            }
                                            
                                            reset($arrColunas);                                         
                                           
                                            foreach ($arrColunas as $col) { 
                                               
                                                if(!empty($arrnomearq[$col->name]) and !empty($arrextarq[$col->name]) ){ 
                                                                                                                        
                                                    $mail->AddAttachment('/var/www/laudo/tmp/'.$arrnomearq[$col->name].''.$rowx[$col->name].'.'.$arrextarq[$col->name].'');    
                                                }
                                               
                                            }
                                            $queueid = ""; 
                                            $mail->Debugoutput = function($debugstr, $level) {

						//printa tudo
						echo "\n<br>".$debugstr;

						//printa somente o queueid
						$pattern='/(queued\ as\ )(.*)/';
						if (preg_match($pattern, $debugstr, $match)){
							global $queueid;
							$queueid = trim($match[2]);
							//echo($match[2]);
						}

					   };
                                            // Copia
                                            //$mail->AddCC('destinarario@dominio.com.br', 'Destinatario'); 
                                            //email copia oculta
                                      //      $mail->AddBCC($emailDestCCO, $emailDestCCONome);

                                            // Adicionar um anexo
                                          //  $mail->AddAttachment('/var/www/laudo/tmp/resultadopdf/resultadocomext16283.pdf');      
                                            //enviar
                                            if(!$mail->Send()){
                                                $erro=1;
                                                //$mensagemRetorno = 'Erro ao enviar Email: '. print($mail->ErrorInfo);
                                                $mensagemRetorno =$mensagemRetorno.'Erro ao enviar Email: '. print($mail);
                                                atualizacomunicacaodest('ERRO',$mensagemRetorno); 
                                                alertageral($row["idimmsgconf"],$rowc["idpessoa"],'pessoa',"Erro ao enviar Email:","ERRO");                                                 
                                                echo($mensagemRetorno);
                                                novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"Erro ao enviar Email ".$emailDest." ","ERRO");
                                            }else{
												// GVT - 05/02/2020 - Verificação para impedir erro no insert em mailfila
												if(empty($row["idimmsgconf"])){
													$link = 'N';
													$idobjetoaux = 0;
												}else{
													$link = "?_modulo=immsgconfemail&_acao=u&idimmsgconf=".$row["idimmsgconf"];
													$idobjetoaux = $row["idobjetosolipor"];
												}
												// ------------------- Feito por Gabriel Valentin Tiburcio em 06/01/2020
												$_sql = "insert into mailfila (idempresa,remetente,destinatario,queueid,status,idobjeto,tipoobjeto,idpessoa,idenvio,enviadode,link,criadoem,criadopor,alteradopor,alteradoem) 
														values (1,'".$emailFrom."','".$emailDest."','".$queueid."','EM FILA',".$idobjetoaux.",'immsgconfemail',1029,'".$envioid."','".$_SERVER["SCRIPT_NAME"]."','".$link."',sysdate(),'sislaudo','sislaudo',sysdate())";

												d::b()->query($_sql) or die("erro ao inserir email na tabela filaemail ".$_sql);
												// ---------------------------------------------------------------------
						
                                                $erro=0;
                                                $mensagemRetorno = $mensagemRetorno.' '.$emailunico;
                                                
                                                $vinmsg=str_replace("'"," ",$mensagem);
                                                //FINALIZA COMUNICACAOITEM
                                                $su="update comunicacaoextdest set conteudo='".$vinmsg."', status='SUCESSO',alteradoem=sysdate(),alteradopor='sislaudo' where idcomunicacaoextdest=".$_SESSION['IDCOMUNICACAOEXTDEST'];
                                                $ru=d::b()->query($su);
                                                if(!$ru){
                                                    echo("1-Falha ao atualizar SUCESSO comunicacaoextdest : " . mysqli_error(d::b()) . "<p>SQL: $su");
                                                }
                                                
                                                echo($mensagemRetorno);
                                                novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"Enviado email para ".$emailDest." ","SUCESSO");
                                                
                                            }
                                        }
                                        /***********************************************************************/
                                        echo $mensagemRetorno;
                                    }//if(empty($stremail)){
                                }//if(empty($strlp)){
                            }//se o contato possui o alerta
                        }//if(!$rexist){
                    }//loop contatos
                }//if(!$resc){
                if($erro!=1){
                    //FINALIZA COMUNICACAOITEM
                    $su="update comunicacaoextitem set status='SUCESSO',alteradoem=sysdate(),alteradopor='sislaudo' where idcomunicacaoextitem=".$_SESSION['IDCOMUNICACAOEXTITEM'];
                    $ru=d::b()->query($su);
                    if(!$ru){
                        die("1-Falha ao atualizar SUCESSO comunicacaoextitem : " . mysqli_error(d::b()) . "<p>SQL: $su");
                    }
                    novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"Finalizado com sucesso ","SUCESSO");
                }else{
                    $mensagemRetorno="";
                    $erro=0;
                    
                    $su="update comunicacaoextitem set status='ERRO',alteradoem=sysdate(),alteradopor='sislaudo' where idcomunicacaoextitem=".$_SESSION['IDCOMUNICACAOEXTITEM'];
                    $ru=d::b()->query($su);
                    if(!$ru){
                        die("1-Falha ao atualizar ERRO comunicacaoextitem : " . mysqli_error(d::b()) . "<p>SQL: $su");
                    }
                    novolog($rowx["idpk"],$row['tabela'],$row['modulo'],"Finalizado com ERRO ","ERRO");
                }

            }//loop dos dados a enviar    
        }//if(!$resx){
        //FINALIZA COMUNICACAO
        
        $su="update comunicacaoext set `to`='".$mensagemRetorno."', status='SUCESSO',alteradoem=sysdate(),alteradopor='sislaudo' where idcomunicacaoext=".$_SESSION['IDCOMUNICACAOEXT'];
        $ru=d::b()->query($su);
        if(!$ru){
            die("1-Falha ao atualizar SUCESSO comunicacaoext : " . mysqli_error(d::b()) . "<p>SQL: $su");
        }
        $mensagemRetorno="";
        $erro=0;
    }//while($row=mysqli_fetch_assoc($res)){
