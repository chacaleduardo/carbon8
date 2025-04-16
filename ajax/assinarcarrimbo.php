<?
require_once("../inc/php/functions.php");

$jwt = validaTokenReduzido();

if($jwt["sucesso"] !== true){
    header("HTTP/1.1 401 Unauthorized");
    die;
}

$idcarrimbo	= $_POST["idcarrimbo"];
$versao	= $_POST["versao"];


//verifica se existem documentos de treinamento vinculados
$chekTraining = "SELECT 
                    1
                FROM
                    sgdocvinc vc
                        JOIN
                    sgdoc sg ON (vc.iddocvinc = sg.idsgdoc)
                        JOIN
                    fluxostatuspessoa f ON (f.idmodulo = sg.idsgdoc)
                        AND f.modulo = 'documento'
                        JOIN
                    carrimbo c ON (c.idobjeto = sg.idsgdoc)
                WHERE
                    sg.idsgdoctipo = 'treinamento'
                        AND vc.idsgdoc = (SELECT 
                            idobjeto
                        FROM
                            carrimbo
                        WHERE
                            idcarrimbo = ".$idcarrimbo.")
                AND c.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA']."";

	
    $ct = d::b()->query($chekTraining) or die("Falha ao verificar se o documento tem treinamento vinculado ". mysqli_error(d::b()));
//se existir documento de treinamento vinculado, vai verificar se a pessoa do id carrimbo já assinou ele
	if (mysqli_num_rows($ct) > 0) {

        $documentoVinculado=true;

        $sql="SELECT 
                `idpessoa`, `status`
            FROM
                carrimbo
            WHERE
                idobjeto = (SELECT 
                        sg.idsgdoc
                    FROM
                        sgdocvinc vc
                            JOIN
                        sgdoc sg ON (vc.iddocvinc = sg.idsgdoc)
                            JOIN
                        fluxostatuspessoa f ON (f.idmodulo = sg.idsgdoc)
                            AND f.modulo = 'documento'
                            JOIN
                        carrimbo c ON (c.idobjeto = sg.idsgdoc)
                    WHERE
                        sg.idsgdoctipo = 'treinamento'
                            AND vc.idsgdoc = (SELECT 
                                idobjeto
                            FROM
                                carrimbo
                            WHERE
                                idcarrimbo = ".$idcarrimbo.")
                            AND c.idpessoa = ".$_SESSION['SESSAO']['IDPESSOA']."
                    ORDER BY sg.idsgdoc DESC
                    LIMIT 1)
                    AND tipoobjeto = 'documento'
            GROUP BY idpessoa";

            $getIdpessoaTreinamento = d::b()->query($sql) or die("Falha ao verificar conclusão do treinamento: ". mysqli_error(d::b()));

                while($r = mysqli_fetch_assoc($getIdpessoaTreinamento)){
                    if($r['status']=='ASSINADO'){
                        $idPessoaTreinamento=$r['idpessoa'];
                    }
                }

    }

        if($documentoVinculado){ 

            if(empty($idPessoaTreinamento)){

                cbSetPostHeader('1','carrimboNotificacao');
                echo('Treinamento'); 
                die();

            }else {
                cbSetPostHeader('1','carrimbo');
                //objetos: "_x_u_carrimbo_idcarrimbo="+inidcarrimbo+"&_x_u_carrimbo_versao="+versao+"&_x_u_carrimbo_status=ATIVO"

                $sql="update carrimbo set status='ASSINADO',alteradopor='".$_SESSION['SESSAO']['USUARIO']."',alteradoem=now()
                        where idcarrimbo = ".$idcarrimbo;
                $res = d::b()->query($sql);
                
                
                if(!$res){
                    cbSetPostHeader('1','carrimbosucesso');
                    echo('Erro');
                // die("1-Erro ao confirmar assinatura: " . mysqli_error() . "<p>SQL: ".$sql);
                }else{
                    cbSetPostHeader('1','carrimboerro');
                    echo('Assinado');
                }
            }

        } else {

            cbSetPostHeader('1','carrimbo');
            //objetos: "_x_u_carrimbo_idcarrimbo="+inidcarrimbo+"&_x_u_carrimbo_versao="+versao+"&_x_u_carrimbo_status=ATIVO"

            $sql="update carrimbo set status='ASSINADO',alteradopor='".$_SESSION['SESSAO']['USUARIO']."',alteradoem=now()
                    where idcarrimbo = ".$idcarrimbo;
            $res = d::b()->query($sql);
            
            
            if(!$res){   
                cbSetPostHeader('1','carrimbosucesso');
                echo('Erro');	
            // die("1-Erro ao confirmar assinatura: " . mysqli_error() . "<p>SQL: ".$sql);
            }else{
                cbSetPostHeader('1','carrimboerro');
                echo('Assinado');
            }
        }