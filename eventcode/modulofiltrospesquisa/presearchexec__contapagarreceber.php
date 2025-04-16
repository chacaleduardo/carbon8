<?

$idusuario= $_SESSION["SESSAO"]["IDPESSOA"];//$perfilpag_idpessoa;

$sql=" select * from pessoa where flgsocio='Y' and idpessoa=".$idusuario;
$res = d::b()->query($sql) or die("Erro ao buscar usuário: " . mysqli_error(d::b()));
$flgdiretor=mysqli_num_rows($res);

//verificar se e usuario com modulo master restaurar ativo
$sqlm=" select if('restaurar' in (".getModsUsr("SQLWHEREMOD")."),'Y','N') as master";
$resm = d::b()->query($sqlm) or die("Falha ao pesquisar SQLWHEREMOD usuario master : " . mysqli_error(d::b()) . "<p>SQL: $sqlm");
$rowm=mysqli_fetch_assoc($resm);

    if($flgdiretor<1){       
        
        $_SESSION["SEARCH"]["WHERE"]["tiponf "]=" tiponf  != 'D'";

        if(!array_key_exists("quitarrh", getModsUsr("MODULOS"))){
           $_SESSION["SEARCH"]["WHERE"]["tiponf "]=" tiponf  != 'R'";
        }

        if($rowm['master']!="Y"){           
            $_SESSION["SEARCH"]["WHERE"]["visivel "]=" visivel  = 'S'";
            
           
              
           
            /*// retidado a pedido @768039 - RELATORIO CONTAS A PAGAR - CREDITO E DÉBITO
            if (array_key_exists("quitarcredito", getModsUsr("MODULOS")) or array_key_exists("quitardebito", getModsUsr("MODULOS"))) {
                if(array_key_exists("quitarcredito", getModsUsr("MODULOS")) and array_key_exists("quitardebito", getModsUsr("MODULOS"))){
                    $_SESSION["SEARCH"]["WHERE"]["tipo "]=" tipo in ('D','C')";
                
                }elseif(array_key_exists("quitarcredito", getModsUsr("MODULOS"))){
                    $_SESSION["SEARCH"]["WHERE"]["tipo "]=" tipo  = 'C'";
                
                }elseif(array_key_exists("quitardebito", getModsUsr("MODULOS"))){
                    $_SESSION["SEARCH"]["WHERE"]["tipo "]=" tipo  = 'D'";
                }else{
                    ?>  
                    <link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
                    <br>
                    <div class="row">
                    <div class="col-md-12">
                            <div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">
    
                            <strong><i class="glyphicon glyphicon-info-sign"></i> Usuário sem permissão para visualização.
                            <br/>
                            <br/>É necessário liberar nas permissões do usuário uma das opções:
                            <br/>*Quitar Débitos
                            <br/>*Quitar Créditos
                                        <br/>
                                        <br/>Favor entrar em contato com Departamento de Processos - Ramal: 110
                            </div>
                        </div>
                    </div>
        <?
                     die;
                }
            }else{
                ?>  
                <link rel="stylesheet" href="../inc/css/bootstrap/css/bootstrap.min.css" />
                <br>
                <div class="row">
		        <div class="col-md-12">
                        <div class="alert alert-warning aviso" role="alert" style="font-size:12px !important;">

                        <strong><i class="glyphicon glyphicon-info-sign"></i> Usuário sem permissão para visualização.
                        <br/>
                        <br/>É necessário liberar nas permissões do usuário uma das opções:
                        <br/>*Quitar Débitos
                        <br/>*Quitar Créditos
                                    <br/>
                                    <br/>Favor entrar em contato com Departamento de Processos - Ramal: 110
                        </div>
                    </div>
                </div>
    <?
                 die;
            }
            */
        }      
    }

    $_SESSION["SEARCH"]["WHERE"]["tipo "]=" tipo  = 'D'";



?>