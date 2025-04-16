<?
require_once("../inc/php/validaacesso.php");
require_once("controllers/farmacovigilancia_controller.php");

if($_POST){
    include_once("../inc/php/cbpost.php");
}

$pagvaltabela = "farmacovigilancia";
$pagvalcampos = array(
    "idfarmacovigilancia" => "pk"
);

$pagsql = "SELECT * FROM farmacovigilancia WHERE idfarmacovigilancia = '#pkid'";
include_once("../inc/php/controlevariaveisgetpost.php");

if (empty($_1_u_farmacovigilancia_status)){
	$_1_u_farmacovigilancia_status = 'ABERTO';
}

$arrayCliente = FarmacovigilanciaController::listarClientes();
$arrayProdserv = FarmacoVigilanciaController::listarProdutos();
$arrayEspecie = FarmacovigilanciaController::buscarEspeciefinalidade(cb::idempresa());
if($_1_u_farmacovigilancia_idpessoa)
    $arrayEndereco = FarmacovigilanciaController::buscarEnderecoPorIdpessoa($_1_u_farmacovigilancia_idpessoa);

?>
<link href="../form/css/farmacovigilancia_css.css?_<?=date("dmYhms")?>" rel="stylesheet">
<script src="../form/js/jquery.mask.min.js"></script>
<div class="row">
    <div class="col-md-12" >
        <div class="panel panel-default" style="padding-bottom: 11px;">
            <div class="panel-heading">
                <div class="d-flex flex-wrap <?= ($_acao == 'i' ? '' : 'flex-between') ?>">FOMULÁRIO DE FARMACOVIGILÂNCIA</div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="form-group col-xs-12 col-md-12">
                        <!-- Tipo Notificação -->
                        <div class="form-group col-xs-3 col-md-3">
                            <label>Tipo Notificação:</label>
                            <!-- <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_notificacao" class="form-control" value="<?=mb_strtoupper($_1_u_farmacovigilancia_notificacao)?>" style="text-transform:uppercase"> -->
                            <select type="text" name="_1_<?=$_acao?>_farmacovigilancia_notificacao" class="form-control" value="<?=mb_strtoupper($_1_u_farmacovigilancia_notificacao)?>" style="text-transform:uppercase">
                                <? fillselectNoError(array('INICIAL' => 'Inicial', 'ACOMPANHAMENTO' => 'Acompanhamento'), $_1_u_farmacovigilancia_notificacao); ?>
                            </select>
                            <input type="hidden" name="_1_<?=$_acao?>_farmacovigilancia_idfarmacovigilancia" value="<?=$_1_u_farmacovigilancia_idfarmacovigilancia?>">
                        </div>
                        <?
                        
                        ?>
                        <!-- NF -->
                        <div class="form-group col-xs-2 col-md-2">
                            <label>NF:</label><br />
                            <? if(empty($_1_u_farmacovigilancia_nf)) { 
                                $escondeInput = '';
                                $escondeLabel = 'display:none';    
                                $readonly = '';                        
                            } else { 
                                $escondeInput = 'display:none';
                                $escondeLabel = '';
                                $readonly = 'disabled';
                            } ?>
                            <input type="text" <?=$readonly?> name="_1_<?=$_acao?>_farmacovigilancia_nf" class="form-control wnoventa inputNf" style="<?=$escondeInput?>">
                            <label class="alert-warning labelNf" style="<?=$escondeLabel?>">
                                <a onclick="janelamodal('?_modulo=pedido&_acao=u&idnf=<?=$_1_u_farmacovigilancia_nf?>')"><?=$_1_u_farmacovigilancia_nf?></a>
                            </label>
                            <i class="fa fa-pencil azul pointer alteraNf" editar='Y' onclick="alteravalor('Nf', this)"></i>
                        </div>

                        <!-- ID Evento Sislaudo -->
                        <div class="form-group col-xs-2 col-md-2">
                            <label>ID Evento Sislaudo:</label><br />
                            <? if(empty($_1_u_farmacovigilancia_idevento)) { 
                                $escondeInput = '';
                                $escondeLabel = 'display:none';    
                                $readonly = '';                        
                            } else { 
                                $escondeInput = 'display:none';
                                $escondeLabel = '';
                                $readonly = 'disabled';
                            } ?>
                            <input type="text" <?=$readonly?> name="_1_<?=$_acao?>_farmacovigilancia_idevento" class="form-control wnoventa inputEvento" style="<?=$escondeInput?>">
                            <label class="alert-warning labelEvento" style="<?=$escondeLabel?>">
                                <a onclick="janelamodal('?_modulo=evento&_acao=u&idevento=<?=$_1_u_farmacovigilancia_idevento?>')"><?=$_1_u_farmacovigilancia_idevento?></a>
                            </label>
                            <i class="fa fa-pencil azul pointer alteraevento" editar='Y' onclick="alteravalor('evento', this)"></i>
                        </div>

                        <!-- Data de Abertura -->
                        <div class="form-group col-xs-2 col-md-2">
                            <label>Data de Abertura:</label>
                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_dataabertura" class="form-control calendario" value="<?=$_1_u_farmacovigilancia_dataabertura?>" autocomplete="off" >
                        </div>
                        <div class="form-group col-xs-1 col-md-1"></div>
                        <div class="form-group col-xs-2 col-md-2">
                            <label>Data de Conclusão:</label>
                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_dataconclusao" class="form-control calendario" value="<?=$_1_u_farmacovigilancia_dataconclusao?>" autocomplete="off" >
                        </div>
                    </div>
                    <div class="form-group col-xs-12 col-md-12">
                        <!-- Problema Relatado -->
                        <div class="form-group col-xs-12 col-md-12">
                            <label>Problema Relatado:</label><br />
                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_problemarelatado" class="form-control" value="<?=$_1_u_farmacovigilancia_problemarelatado?>" autocomplete="off">
                        </div>
                    </div>
                    <? if($_acao == 'u') { ?>
                        <div class="form-group col-xs-12 col-md-12">
                            <div class="form-group col-xs-12 col-md-12">
                                <label class="dp-flex">
                                    <? $checked = ($_1_u_farmacovigilancia_farmacovigilancia == 'Y') ? 'checked' : ""; ?>
                                    <? $disabled = ($_1_u_farmacovigilancia_farmacovigilancia == 'Y') ? 'disabled' : ""; ?>
                                    <input type="checkbox" id="farmacovigilancia" class="my-0 mr-3" onclick="alterarFarmaco(this)" <?=$checked?> <?=$disabled?>> Farmacovigilância
                                </label>
                            </div>
                        </div>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="panel panel-default" style="padding-bottom: 11px;">
            <div class="panel-heading" <?=($_acao == 'i' ? '' : 'style="background-color: rgb(23, 98, 146) !important; color: white;"')?>>
                <div class="d-flex flex-wrap <?=($_acao == 'i' ? '' : 'flex-between')?> ">FORMULÁRIO</div>
            </div>
            <div class="bckwh">
                <div class="panelAbas div-m" id="mainPanel">
                    <ul class="pd-top nav nav-tabs" id="Tab_lp" role="tablist">
                        <li role="presentation panel-heading" class="tabs-container li_farmaco active" value="farmaco_dados_importantes">
                            <a href="#farmaco_dados_importantes" class="cinzaclaro define" role="tab" data-toggle="tab">DADOS INFORMANTE</a>                        
                        </li>                        
                        <li role="presentation panel-heading" class="tabs-container li_farmaco" value="farmaco_dados_cliente">
                            <a href="#farmaco_dados_cliente" class="cinzaclaro define" role="tab" data-toggle="tab">DADOS CLIENTE</a>
                        </li>
                        <li role="presentation panel-heading" class="tabs-container li_farmaco" value="farmaco_dados_produto">
                            <a href="#farmaco_dados_produto" class="cinzaclaro define" role="tab" data-toggle="tab">DADOS PRODUTO</a>
                        </li>
                        <li role="presentation panel-heading" class="tabs-container li_farmaco" value="farmaco_dados_animais">
                            <a href="#farmaco_dados_animais" class="cinzaclaro define" role="tab" data-toggle="tab">DADOS ANIMAIS</a>
                        </li>                
                        <li role="presentation panel-heading" class="tabs-container li_farmaco" value="farmaco_outros_desvios">
                            <a href="#farmaco_outros_desvios" class="cinzaclaro define" role="tab" data-toggle="tab">OUTROS DESVIOS RELACIONADOS AO PRODUTO</a>
                        </li>         
                        <? if($_1_u_farmacovigilancia_farmacovigilancia == 'Y') { ?>
                            <li role="presentation panel-heading" class="tabs-container li_farmaco" value="farmaco_ficha_tecnica">
                                <a href="#farmaco_ficha_tecnica" class="cinzaclaro define" role="tab" data-toggle="tab">FICHA TÉCNICA</a>
                            </li>
                            <li role="presentation panel-heading" class="tabs-container li_farmaco" value="farmaco_outro_produto">
                                <a href="#farmaco_outro_produto" class="cinzaclaro define" role="tab" data-toggle="tab">OUTRO PRODUTO UTILIZADO</a>
                            </li>
                                                   
                            <li role="presentation panel-heading" class="tabs-container li_farmaco" value="farmaco_aboon">
                                <a href="#farmaco_aboon" class="cinzaclaro define" role="tab" data-toggle="tab">ABOON</a>
                            </li>
                            <li role="presentation panel-heading" class="tabs-container li_farmaco" value="farmaco_plano_acao">
                                <a href="#farmaco_plano_acao" class="cinzaclaro define" role="tab" data-toggle="tab">PLANO DE AÇÃO</a>
                            </li>
                        <? } ?>
                        <li role="presentation panel-heading" class="tabs-container li_farmaco" value="farmaco_avalgq">
                            <a href="#farmaco_avalgq" class="cinzaclaro define" role="tab" data-toggle="tab">Avaliação GQ</a>
                        </li>
                    </ul>

                    <!------------------------  DADOS DO INFORMANTE ------------------------>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade active in" id="farmaco_dados_importantes">                       
                            <div class="panel-body">
                                <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Nome -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>Nome:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_nomeinformante" class="form-control" value="<?=$_1_u_farmacovigilancia_nomeinformante?>" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Endereço -->
                                        <div class="form-group col-xs-6 col-md-6">
                                            <label>Endereço:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_enderecoinformante" class="form-control" value="<?=$_1_u_farmacovigilancia_enderecoinformante?>" autocomplete="off">
                                        </div>
                                        
                                        <!-- Telefone -->
                                        <div class="form-group col-xs-3 col-md-3">
                                            <label>Telefone:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_telefoneinformante" class="form-control telefoneinformante" value="<?=$_1_u_farmacovigilancia_telefoneinformante?>" autocomplete="off">
                                        </div>

                                        <!-- Email -->
                                        <div class="form-group col-xs-3 col-md-3">
                                            <label>Email:</label>
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_emailinformante" class="form-control" value="<?=$_1_u_farmacovigilancia_emailinformante?>" autocomplete="off">
                                        </div>
                                    </div>                                    
                                </div>
                            </div>                                                                        
                        </div>
                        <!------------------------  DADOS DO INFORMANTE ------------------------>                        
                        
                        <!------------------------  DADOS DO CLIENTE ------------------------>
                        <div role="tabpanel" class="tab-pane fade" id="farmaco_dados_cliente">                          
                            <div class="panel-body">
                                <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">                                  
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Nome -->
                                        <?
                                            if($_1_u_farmacovigilancia_nf) {
                                                $busca = FarmacovigilanciaController::buscarPessoaPorIdnf($_1_u_farmacovigilancia_nf);
                                                if($busca && !$_1_u_farmacovigilancia_idpessoa){
                                                    $_1_u_farmacovigilancia_idpessoa = $busca;
                                                }
                                            }
                                            
                                        ?>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>Nome:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_idpessoa" class="form-control wnoventaoito" cbvalue="<?=$_1_u_farmacovigilancia_idpessoa?>" value="<?=$arrayCliente[$_1_u_farmacovigilancia_idpessoa]["nome"] ?>" autocomplete="off">
                                            <? if($_1_u_farmacovigilancia_idpessoa) { ?>
                                                <a title="Empresa" class="fa fa-bars fade pointer hoverazul" href="?_modulo=pessoa&_acao=u&idpessoa=<?=$_1_u_farmacovigilancia_idpessoa?>" target="_blank"></a>
                                            <? } ?>
                                        </div>
                                    </div>
                                    <?
                                    if($_1_u_farmacovigilancia_idpessoa && !$_1_u_farmacovigilancia_idendereco){
                                        $dadosCliente = FarmacovigilanciaController::buscarEnderecoPorIdpessoaFiltrado($_1_u_farmacovigilancia_idpessoa);
                                    } else {
                                        $dadosCliente = FarmacovigilanciaController::buscarEnderecoPessoaPorIdEndereco($_1_u_farmacovigilancia_idendereco);
                                    }
                                    ?>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Endereço -->
                                        <div class="form-group col-xs-6 col-md-16">
                                            <label>Endereço:</label><br />
                                            <? if(empty($_1_u_farmacovigilancia_idendereco) && empty($_1_u_farmacovigilancia_idpessoa)) { 
                                                $escondeLabelEnderecoPessoa = '';
                                                $escondeInputEndereco = 'display:none';
                                                $escondeLabelEndereco = 'display:none';      
                                                $readonlyEndereco = 'disabled';
                                            } elseif(empty($_1_u_farmacovigilancia_idendereco) && !empty($_1_u_farmacovigilancia_idpessoa)) {   
                                                $escondeLabelEnderecoPessoa = 'display:none';
                                                $escondeInputEndereco = '';
                                                $escondeLabelEndereco = 'display:none';      
                                                $readonlyEndereco = '';                      
                                            } else { 
                                                $escondeLabelEnderecoPessoa = 'display:none';
                                                $escondeInputEndereco = 'display:none';
                                                $escondeLabelEndereco = '';
                                                $readonlyEndereco = 'disabled';
                                            } 
                                            ?>

                                            <label class="enderecocliente labelEndereco" style="<?=$escondeLabelEndereco?>"><?=$dadosCliente['logradouro']." ".$dadosCliente['endereco'].", ".$dadosCliente['numero']." - ".$dadosCliente['bairro']." - ".$dadosCliente['cidade']."/".$dadosCliente['uf']?></label>
                                            <input type="text" <?=$readonlyEndereco?> style="<?=$escondeInputEndereco?>" vnulo name="_1_<?=$_acao?>_farmacovigilancia_idendereco" class="form-control wnoventa inputEndereco">
                                            <i class="fa fa-pencil azul pointer alterarEndereco"  editar='Y' onclick="alteravalor('endereco', this)"></i>
                                            <label class="enderecocliente labelEnderecoPessoa" style="<?=$escondeLabelEnderecoPessoa?>"> - </label>
                                        </div>

                                        <!-- Telefone -->
                                        <div class="form-group col-xs-3 col-md-3">
                                            <label>Telefone:</label><br />
                                            <? if($_1_u_farmacovigilancia_idpessoa) { ?>
                                                <label class="telefonecliente"><?=$dadosCliente['telefone']?></label>
                                            <? } else { ?>
                                                <label class="telefonecliente"> - </label>
                                            <? } ?>
                                        </div>

                                        <!-- Email -->
                                        <div class="form-group col-xs-3 col-md-3">
                                            <label>Email:</label><br />
                                            <? if($_1_u_farmacovigilancia_idpessoa) { ?>
                                                <label class="emailcliente"><?=$dadosCliente['email']?></label>
                                            <? } else { ?>
                                                <label class="emailcliente"> - </label>
                                            <? } ?>
                                        </div>
                                    </div>                                           
                                </div>
                            </div>                                     
                        </div>
                        <!------------------------  DADOS DO CLIENTE ------------------------>
                    
                        <!------------------------  DADOS DO PRODUTO ------------------------>
                        <div role="tabpanel" class="tab-pane fade" id="farmaco_dados_produto">                            
                            <div class="panel-body">
                                <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important"> 
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Produto -->
                                        <div class="form-group col-xs-6 col-md-6">
                                            <label>Produto:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_idprodserv" class="form-control" cbvalue="<?=$_1_u_farmacovigilancia_idprodserv?>" value="<?=$arrayProdserv[$_1_u_farmacovigilancia_idprodserv]["descr"] ?>" autocomplete="off" >
                                        </div>

                                        <!-- Lote -->
                                        <div class="form-group col-xs-6 col-md-6">
                                            <label>Lote/Partida:</label><br />
                                            <span class="farmacovigilancia_idlote"></span>
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_idlote" class="form-control" cbvalue="<?=$_1_u_farmacovigilancia_idlote?>" value="<?=traduzid('lote','idlote',"CONCAT(partida, '/', exercicio)",$_1_u_farmacovigilancia_idlote,false)?>" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Data de recebimento -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Data de recebimento:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_recebimento" class="form-control calendario" value="<?=$_1_u_farmacovigilancia_recebimento?>" autocomplete="off">
                                        </div>

                                        <!-- Temperatura de Recebimento -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Temperatura de Recebimento:</label><br />
                                            <!-- <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_temperatura" class="form-control" value="<?=$_1_u_farmacovigilancia_temperatura?>" autocomplete="off"> -->
                                            <select type="text" name="_1_<?=$_acao?>_farmacovigilancia_temperatura" class="form-control" value="<?=$_1_u_farmacovigilancia_temperatura?>" autocomplete="off">
                                                <option></option>
                                                <? fillselectNoError(array('DENTRO' => 'Dentro', 'FORA' => 'Fora'), $_1_u_farmacovigilancia_temperatura); ?>
                                            </select>
                                        </div>
                        
                                        <!-- Doses aplicadas -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Doses aplicadas:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_doses" class="form-control" value="<?=$_1_u_farmacovigilancia_doses?>" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Via de Administração -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>Via de Administração:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_via" class="form-control" value="<?=$_1_u_farmacovigilancia_via?>" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Data da 1ª aplicação do produto -->
                                        <div class="form-group col-xs-2 col-md-2">
                                            <label>Data da 1ª aplicação do produto:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_primaplicacao" class="form-control calendario" value="<?=$_1_u_farmacovigilancia_primaplicacao?>" autocomplete="off"> 
                                        </div>

                                        <!-- Temperatura -->
                                        <div class="form-group col-xs-3 col-md-3">
                                            <label>Temperatura De Aplicação:</label>
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_tempprimaplicacao" class="form-control" value="<?=$_1_u_farmacovigilancia_tempprimaplicacao?>" autocomplete="off">
                                        </div>

                                        <!-- Data da 2ª aplicação do produto -->
                                        <div class="form-group col-xs-2 col-md-2">
                                            <label>Data da 2ª aplicação do produto:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_segaplicacao" class="form-control calendario" value="<?=$_1_u_farmacovigilancia_segaplicacao?>" autocomplete="off">
                                        </div>

                                        <!-- Temperatura -->
                                        <div class="form-group col-xs-3 col-md-3">
                                            <label>Temperatura De Aplicação:</label>
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_tempsegaplicacao" class="form-control" value="<?=$_1_u_farmacovigilancia_tempsegaplicacao?>" autocomplete="off">
                                        </div>

                                        <!-- Data de início da reação adversa -->
                                        <div class="form-group col-xs-2 col-md-2">
                                            <label>Data de início da reação adversa:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_inicioreacao" class="form-control calendario" value="<?=$_1_u_farmacovigilancia_inicioreacao?>" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>                                            
                        </div>
                        <!------------------------  DADOS DO PRODUTO ------------------------>
                        
                        <!------------------------  DADOS DO ANIMAIS ------------------------>
                        <div role="tabpanel" class="tab-pane fade" id="farmaco_dados_animais">                            
                            <div class="panel-body">
                                <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Espécie -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>Espécie:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_especie" class="form-control" cbvalue="<?=$_1_u_farmacovigilancia_especie?>" value="<?=$arrayEspecie[$_1_u_farmacovigilancia_especie]["descr"] ?>" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Raça -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>Raça/Linhagem:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_raca" class="form-control" value="<?=$_1_u_farmacovigilancia_raca?>" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Sexo -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Sexo:</label><br />
                                            <select type="text" name="_1_<?=$_acao?>_farmacovigilancia_sexo" class="form-control" value="<?=$_1_u_farmacovigilancia_sexo?>" autocomplete="off">
                                                <option></option>
                                                <? fillselect(array('F' => 'Feminino', 'M' => 'Masculino'), $_1_u_farmacovigilancia_sexo); ?>
                                            </select>
                                        </div>

                                        <!-- Idade -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Idade:</label>
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_idade" class="form-control" value="<?=$_1_u_farmacovigilancia_idade?>" autocomplete="off">
                                        </div>

                                        <!-- Peso médio -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Peso médio:</label>
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_peso" class="form-control" value="<?=$_1_u_farmacovigilancia_peso?>" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Condição de saúde dos animais antes do tratamento -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>Materias Coletados:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_materialcoleta" class="form-control" value="<?=$_1_u_farmacovigilancia_materialcoleta?>" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Condição de saúde dos animais antes do tratamento -->
                                        <div class="form-group col-xs-6 col-md-6">
                                            <label>Necrópsia:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_necropsia" class="form-control" value="<?=$_1_u_farmacovigilancia_necropsia?>" autocomplete="off">
                                        </div>
                                        <div class="form-group col-xs-6 col-md-6">
                                            <label>Achados Necrópsia:</label><br />
                                            <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_achadosnecropsia" class="form-control" value="<?=$_1_u_farmacovigilancia_achadosnecropsia?>" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>                                            
                        </div>
                        <!------------------------  DADOS DO ANIMAIS ------------------------>

                        <!------------------------  OUTROS DESVIOS RELACIONADOS AO PRODUTO ------------------------>
                        <div role="tabpanel" class="tab-pane fade" id="farmaco_outros_desvios">                            
                            <div class="panel-body">
                                <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Alteração da cor do produto -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Alteração da cor do produto:</label><br />
                                            <select name="_1_<?=$_acao?>_farmacovigilancia_corproduto" class="form-control">
                                                <option></option>
                                                <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_corproduto); ?>
                                            </select>
                                        </div>

                                        <!-- Presença de corpo estranho -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Presença de corpo estranho:</label><br />
                                            <select name="_1_<?=$_acao?>_farmacovigilancia_corpoestranho" class="form-control">
                                                <option></option>
                                                <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_corpoestranho); ?>
                                            </select>
                                        </div>
                                    
                                        <!-- Defeito de Embalagem -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Defeito de Embalagem:</label><br />
                                            <select name="_1_<?=$_acao?>_farmacovigilancia_defeitoembalagem" class="form-control">
                                                <option></option>
                                                <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_defeitoembalagem); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Precipitação -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Precipitação:</label>
                                            <select name="_1_<?=$_acao?>_farmacovigilancia_precipitacao" class="form-control">
                                                <option></option>
                                                <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_precipitacao); ?>
                                            </select>
                                        </div>

                                        <!-- Vazamento do Produto -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Vazamento do Produto:</label>
                                            <select name="_1_<?=$_acao?>_farmacovigilancia_vazamento" class="form-control">
                                                <option></option>
                                                <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_vazamento); ?>
                                            </select>
                                        </div>

                                        <!-- Outros -->
                                        <div class="form-group col-xs-4 col-md-4">
                                            <label>Outros:</label>
                                            <select name="_1_<?=$_acao?>_farmacovigilancia_outros" class="form-control">
                                                <option></option>
                                                <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_outros); ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-xs-12 col-md-12">
                                        <!-- Descrição do desvio -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>Descrição do desvio:</label><br />
                                            <textarea name="_1_<?=$_acao?>_farmacovigilancia_descricaodesvio" class="form-control"><?=$_1_u_farmacovigilancia_descricaodesvio?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>                                            
                        </div>    
                        <!------------------------  OUTROS DESVIOS RELACIONADOS AO PRODUTO ------------------------> 
                        
                        <? if($_1_u_farmacovigilancia_farmacovigilancia == 'Y') { ?>
                            <!------------------------  FICHA TÉCNICA ------------------------>
                            <div role="tabpanel" class="tab-pane fade" id="farmaco_ficha_tecnica">                            
                                <div class="panel-body">
                                    <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Condição de saúde dos animais antes do tratamento -->
                                            <div class="form-group col-xs-12 col-md-12">
                                                <label>Condição de saúde dos animais antes do tratamento:</label><br />
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_condicaosaude" class="form-control" value="<?=$_1_u_farmacovigilancia_condicaosaude?>" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Há histórico de reação prévia a outros produtos? -->
                                            <div class="form-group col-xs-12 col-md-12">
                                                <label>Há histórico de reação prévia a outros produtos?</label><br />
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_reacaoprevia" class="form-control" value="<?=$_1_u_farmacovigilancia_reacaoprevia?>" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Qtd de animais vacinados -->
                                            <div class="form-group col-xs-3 col-md-3">
                                                <label>Qtd de animais vacinados:</label><br />
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_qtdanimaisvacinados" class="form-control" value="<?=$_1_u_farmacovigilancia_qtdanimaisvacinados?>" autocomplete="off">
                                            </div>

                                            <!-- Qts de animais acometidos -->
                                            <div class="form-group col-xs-3 col-md-3">
                                                <label>Qts de animais acometidos:</label>
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_qtdanimaisacometidos" class="form-control" value="<?=$_1_u_farmacovigilancia_qtdanimaisacometidos?>" autocomplete="off">
                                            </div>

                                            <!-- Qtd de animais recuperados -->
                                            <div class="form-group col-xs-3 col-md-3">
                                                <label>Qtd de animais recuperados:</label><br />
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_qtdanimaisrecuperados" class="form-control" value="<?=$_1_u_farmacovigilancia_qtdanimaisrecuperados?>" autocomplete="off">
                                            </div>

                                            <!-- Qtd de animais mortos -->
                                            <div class="form-group col-xs-3 col-md-3">
                                                <label>Qtd de animais mortos:</label>
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_qtdanimaismortos" class="form-control" value="<?=$_1_u_farmacovigilancia_qtdanimaismortos?>" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Lado de aplicação do produto -->
                                            <div class="form-group col-xs-3 col-md-3">
                                                <label>Lado de aplicação do produto:</label><br />
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_ladopescoco" class="form-control" value="<?=$_1_u_farmacovigilancia_ladopescoco?>" autocomplete="off">
                                            </div>

                                            <!-- Teve vômito? Se sim, quantos % -->
                                            <div class="form-group col-xs-3 col-md-3">
                                                <label>Teve vômito? Se sim, quantos %?</label>
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_qtdvomito" class="form-control" value="<?=$_1_u_farmacovigilancia_qtdvomito?>" autocomplete="off" vdecimal>
                                            </div>

                                            <!-- Teve vômito? Se sim, quantos % -->
                                            <div class="form-group col-xs-3 col-md-3">
                                                <label>Teve  sangramento na aplicação? Se sim, quantos %?</label><br />
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_qtdsangramento" class="form-control" value="<?=$_1_u_farmacovigilancia_qtdsangramento?>" autocomplete="off" vdecimal>
                                            </div>

                                            <!-- Tamanho da agulha utilizado na vacinação -->
                                            <div class="form-group col-xs-3 col-md-3">
                                                <label>Tamanho da agulha utilizado na vacinação:</label>
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_tamagulha" class="form-control" value="<?=$_1_u_farmacovigilancia_tamagulha?>" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Teve formação de Abcessos pós aplicação no local da aplicação? -->
                                            <div class="form-group col-xs-6 col-md-6">
                                                <label>Teve formação de Abcessos pós aplicação no local da aplicação?</label><br />
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_abcessos" class="form-control" value="<?=$_1_u_farmacovigilancia_abcessos?>" autocomplete="off">
                                            </div>

                                            <!-- Qtd de animais acometidos com sequelas -->
                                            <div class="form-group col-xs-6 col-md-6">
                                                <label>Qtd de animais acometidos com sequelas:</label>
                                                <input type="text" name="_1_<?=$_acao?>_farmacovigilancia_qtdsequelas" class="form-control" value="<?=$_1_u_farmacovigilancia_qtdsequelas?>" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Descrição da reação adversa -->
                                            <div class="form-group col-xs-12 col-md-12">
                                                <label>Descrição da reação adversa:</label><br />
                                                <textarea name="_1_<?=$_acao?>_farmacovigilancia_reacaoadversa" class="form-control"><?=$_1_u_farmacovigilancia_reacaoadversa?></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Tratamento da reação adversa -->
                                            <div class="form-group col-xs-12 col-md-12">
                                                <label>Tratamento da reação adversa:</label><br />
                                                <textarea name="_1_<?=$_acao?>_farmacovigilancia_tratamentoreacaoadversa" class="form-control"><?=$_1_u_farmacovigilancia_tratamentoreacaoadversa?></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Tratamento da reação adversa -->
                                            <div class="form-group col-xs-12 col-md-12">
                                                <label>Equipamento de vacinação // Outras observações:</label><br />
                                                <textarea name="_1_<?=$_acao?>_farmacovigilancia_equipamentovac" class="form-control"><?=$_1_u_farmacovigilancia_equipamentovac?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>                                            
                            </div>
                            <!------------------------  FICHA TÉCNICA ------------------------>
                            
                            <!------------------------  OUTRO PRODUTO UTILIZADO ------------------------>
                            <div role="tabpanel" class="tab-pane fade" id="farmaco_outro_produto">                            
                               
                                <?
                                    $arrprod = FarmacovigilanciaController::buscarProdutosPorIdFarmacovigilancia($_1_u_farmacovigilancia_idfarmacovigilancia);
                                    $i = 999;
                                    foreach($arrprod as $key => $value) {
                                        $i++;
                                ?>
                                    <div class="panel-body">
                                    <div class="panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <!-- <div class="form-group col-xs-12 col-md-12">
                                             Foi utilizado outro produto comcomitantemente? 
                                            <div class="form-group col-xs-12 col-md-12">
                                                <label>Foi utilizado outro produto comcomitantemente?</label><br />
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_outroproduto" class="form-control">
                                                    <option></option>
                                                    <? //fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_outroproduto); ?>
                                                </select>                                            
                                            </div>
                                        </div> -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Produto -->
                                            <div class="form-group col-xs-4 col-md-4">
                                                <label>Produto:</label><br />
                                                <input type="text" name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_produto" class="form-control" value="<?=$value["produto"]?>" autocomplete="off">
                                                <input type="hidden" name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_idprodutofarmacovigilancia" class="form-control" value="<?=$value["idprodutofarmacovigilancia"]?>" autocomplete="off">
                                            </div>

                                            <!-- Data de Administração -->
                                            <div class="form-group col-xs-4 col-md-4">
                                                <label>Data de Administração:</label><br />
                                                <input type="text" name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_administracao" class="form-control calendario" value="<?=$value["administracao"]?>" autocomplete="off">
                                            </div>

                                            <!-- Fabricante -->
                                            <div class="form-group col-xs-4 col-md-4">
                                                <label>Fabricante:</label>
                                                <input type="text" name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_fabricante" class="form-control" value="<?=$value["fabricante"]?>" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Dosagem -->
                                            <div class="form-group col-xs-4 col-md-4">
                                                <label>Dosagem:</label><br />
                                                <input type="text" name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_dosagem" class="form-control" value="<?=$value["dosagem"]?>" autocomplete="off">
                                            </div>

                                            <!-- Lote -->
                                            <div class="form-group col-xs-4 col-md-4">
                                                <label>Lote:</label><br />
                                                <input type="text" name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_lote" class="form-control" value="<?=$value["lote"]?>" autocomplete="off">
                                            </div>

                                            <!-- Via de aplicação -->
                                            <div class="form-group col-xs-4 col-md-4">
                                                <label>Via de aplicação:</label>
                                                <input type="text" name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_aplicacao" class="form-control" value="<?=$value["aplicacao"]?>" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- A recomendação da bula foi seguida? -->
                                            <div class="form-group col-xs-4 col-md-4">
                                                <label>A recomendação da bula foi seguida?</label><br />
                                                <select name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_bula" class="form-control">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $value["bula"]); ?>
                                                </select>
                                            </div>

                                            <!-- Respeitam as Boas Práticas de Vacinação? -->
                                            <div class="form-group col-xs-4 col-md-4">
                                                <label>Respeitam as Boas Práticas de Vacinação?</label><br />
                                                <select name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_boaspraticas" class="form-control">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $value["boaspraticas"]); ?>
                                                </select>
                                            </div>

                                            <!-- Agulha e vacinadora estão em boas condições de uso e foram higienizadas adequadamente antes da vacinação? -->
                                            <div class="form-group col-xs-4 col-md-4">
                                                <label>Agulha e vacinadora estão em boas condições de uso e foram higienizadas adequadamente antes da vacinação?</label><br />
                                                <input name="_<?=$i?>_<?=$_acao?>_produtofarmacovigilancia_condicoesusoagulha" class="form-control" value="<?=$value["condicoesusoagulha"]?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>                                            
                            <?}?>
                                <div class="panel-body">
                                    <div class="col-md-12">
                                        <div class="d-flex flex-column">
                                            <i class="fa fa-plus-circle fa-2x verde btn-lg pointer" onclick="inserirProdutoFarmacovigilancia(<?=$_1_u_farmacovigilancia_idfarmacovigilancia?>)"></i>
                                            Adicionar produto de uso concomitante
                                        </div>
                                    </div>
                                </div>
                            </div>                        
                            <!------------------------  OUTRO PRODUTO UTILIZADO ------------------------>
                            
                            <!------------------------  PLANO DE AÇÃO ------------------------>
                            <div role="tabpanel" class="tab-pane fade" id="farmaco_plano_acao">                            
                                <div class="panel-body">
                                    <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Foi utilizado outro produto comcomitantemente? -->
                                            <div class="form-group col-xs-12 col-md-12">
                                                <label>Descreva o Plano de Ação:</label><br />
                                                <textarea name="_1_<?=$_acao?>_farmacovigilancia_planoacao" class="form-control"><?=$_1_u_farmacovigilancia_planoacao?></textarea>                                          
                                            </div>
                                            <div class="form-group col-xs-12 col-md-12">
                                                <label>Descreva a Resolução Interna:</label><br />
                                                <textarea name="_1_<?=$_acao?>_farmacovigilancia_resolucaointerna" class="form-control"><?=$_1_u_farmacovigilancia_resolucaointerna?></textarea>                                          
                                            </div>
                                            <div class="form-group col-xs-12 col-md-12">
                                                <label>Descreva a Conclusão:</label><br />
                                                <textarea name="_1_<?=$_acao?>_farmacovigilancia_conclusao" class="form-control"><?=$_1_u_farmacovigilancia_conclusao?></textarea>                                          
                                            </div>
                                            <div class="form-group col-xs-12 col-md-12">
                                                <table class="table">
                                                    <tr>
                                                        <th><label>O que?</label></th>
                                                        <th><label>Quem?</label></th>
                                                        <th><label>Quando?</label></th>
                                                        <th><label>Onde?</label></th>
                                                        <th><label>Porque?</label></th>
                                                        <th><label>Como?</label></th>
                                                        <th><label>Quanto?</label></th>
                                                    </tr>
                                                    <tr>
                                                        <td><textarea name="_1_<?=$_acao?>_farmacovigilancia_oque" class="form-control"><?=$_1_u_farmacovigilancia_oque?></textarea></td>
                                                        <td><textarea name="_1_<?=$_acao?>_farmacovigilancia_quem" class="form-control"><?=$_1_u_farmacovigilancia_quem?></textarea></td>
                                                        <td><textarea name="_1_<?=$_acao?>_farmacovigilancia_quando" class="form-control"><?=$_1_u_farmacovigilancia_quando?></textarea></td>
                                                        <td><textarea name="_1_<?=$_acao?>_farmacovigilancia_onde" class="form-control"><?=$_1_u_farmacovigilancia_onde?></textarea></td>
                                                        <td><textarea name="_1_<?=$_acao?>_farmacovigilancia_porque" class="form-control"><?=$_1_u_farmacovigilancia_porque?></textarea></td>
                                                        <td><textarea name="_1_<?=$_acao?>_farmacovigilancia_como" class="form-control"><?=$_1_u_farmacovigilancia_como?></textarea></td>
                                                        <td><textarea name="_1_<?=$_acao?>_farmacovigilancia_quanto" class="form-control"><?=$_1_u_farmacovigilancia_quanto?></textarea></td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>                                            
                            </div>                        
                            <!------------------------  PLANO DE AÇÃO ------------------------>
                               
                            <!------------------------  aboon  ------------------------>
                            <div role="tabpanel" class="tab-pane fade" id="farmaco_aboon">                            
                                <div class="panel-body">
                                    <!------------------------  CONEXÃO ASSOCIATIVA  ------------------------>
                                    <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <div class="form-group col-xs-12 col-md-12 texto-central">                                        
                                            <label class="color-ft-dl">1 - CONEXÃO ASSOCIATIVA</label>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Associação Razoável -->
                                            <label>Existe uma associação razoável no tempo entre a administração do produto e o início do evento adverso?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_associacaorazoavel" class="form-control" onchange="salvarCampos(this, 'associacaorazoavel')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim (Associação Razoável)', 'N' => 'Não (Nenhuma Associação Razoável)', 'D' => 'Desconhecido'), $_1_u_farmacovigilancia_associacaorazoavel); ?>
                                                </select>
                                            </div>
                                            <div class="info_farmaco pdt-10 dib col-xs-1 col-md-1">
                                                <a class="fa fa-1x fa-info-circle btn-lg azul pointer hoverazul pl-0" title="Associação Razoável" data-target="webuiPopover0"></a>
                                            </div>
                                            <div class="webui-popover-content">
                                                <br />
                                                <div>                                          
                                                    <p>a. no tempo (incluindo de-challenge** e re-challenge**)</p>
                                                    <p>b. com local anatômico.</p>
                                                    <p> 4.1.1.
                                                    O evento observado está associado com a administração do VMP (medicamento de uso veterinário)? A cronologia está em concordância com o tratamento? Existe uma associação razoável no tempo entre a administração do produto e o início e a duração do evento adverso?</p>
                                                    <h5>** De-challenge:</h5> Quando a droga suspeita foi descontinuada ou retirada ou dose reduzida devido ao evento adverso (EA). Pode ser:                                        
                                                    <ul>
                                                        <li><strong>Positivo:</strong> O evento adverso melhorou ou resolveu após a descontinuação da droga.</li>
                                                        <li><strong>Negativo:</strong> O evento adverso não melhorou ou não resolveu após a descontinuação da droga.</li>
                                                        <li><strong>Desconhecido:</strong> O resultado do de-challenge é desconhecido.</li>
                                                        <li><strong>Não aplicável:</strong> Tratamento para evento adverso, morte, descontinuação da droga antes do erro de medicação para EA, overdose de medicamento, exposição a medicamentos durante a prenhez.</li>
                                                    </ul>

                                                    <h5>** Re-challenge:</h5> Quando o medicamento suspeito foi reiniciado após o de-challenge e é aplicável somente após de-challenge positivo. Pode ser:
                                                    <ul>
                                                        <li><strong>Positivo:</strong> Medicamento suspeito foi reintroduzido e o evento reapareceu.</li>
                                                        <li><strong>Negativo:</strong> Drogas suspeitas foram reintroduzidas e eventos não reapareceram.</li>
                                                        <li><strong>Desconhecido:</strong> Medicamento suspeito reintroduzido, mas o resultado de rechallange era desconhecido.</li>
                                                        <li><strong>Não aplicável:</strong> Quando a droga não foi reintroduzida.</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_associacaorazoavel){
                                                    case 'Y':
                                                        echo "A, B";
                                                    break;
                                                    case 'N':
                                                        echo "N";
                                                    break;
                                                    case 'D':
                                                        echo "01 ou 0";
                                                    break;
                                                }
                                                ?>                                                
                                            </div>
                                        </div>                                            

                                        <!-- Aplicar Antídoto -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>Houve alguma melhora depois de interromper o tratamento ou administrar um antídoto (de-challenge)?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_aplicarantidoto" class="form-control" onchange="salvarCampos(this, 'aplicarantidoto')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim (Melhorou)', 'N' => 'Não (Sem melhoras)', 'D' => 'Desconhecido (Sem desafio realizado)'), $_1_u_farmacovigilancia_aplicarantidoto); ?>
                                                </select>
                                            </div> 
                                            <div class="col-xs-1 col-md-1"></div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_aplicarantidoto){
                                                    case 'Y':
                                                        echo "A, B";
                                                    break;
                                                    case 'N':
                                                        echo "O, N";
                                                    break;
                                                    case 'D':
                                                        echo "A, B, O1, O, N";
                                                    break;
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Recorrência -->
                                            <label> O que aconteceu após o rechallenge - recorrência, sem recorrência ou sem rechallenge feito?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_recorrencia" class="form-control" onchange="salvarCampos(this, 'recorrencia')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim (Recorrência)', 'N' => 'Não (Não recorrência)', 'D' => 'Desconhecido (Sem desafio realizado)'), $_1_u_farmacovigilancia_recorrencia); ?>
                                                </select>
                                            </div>
                                            <div class="info_farmaco pdt-10 dib col-xs-1 col-md-1" id="associacao_razoavel">
                                                <a class="fa fa-1x fa-info-circle btn-lg azul pointer hoverazul pl-0" title="Associação Razoável" data-target="webuiPopover0"></a>
                                            </div>
                                            <div class="webui-popover-content">
                                                <br />
                                                <div>                                          
                                                    <p>O evento adverso reapareceu após o re-challenge (mesmo animal ou animal relacionado)?</p>
                                                    <p>Evento similar é conhecido nesse paciente de uma exposição anterior?</p>
                                                </div>
                                            </div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_recorrencia){
                                                    case 'Y':
                                                        echo "A, B";
                                                    break;
                                                    case 'N':
                                                        echo "N";
                                                    break;
                                                    case 'D':
                                                        echo "A, B, O1, O, N";
                                                    break;
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <!-- Distribuição dos Sinais -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>A localização/distribuição dos sinais pode ser devido ao tratamento?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_ditribuicaosinais" class="form-control" onchange="salvarCampos(this, 'ditribuicaosinais')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim (Conexão anatômica associativa)', 'N' => 'Não (Sem conexão associativa)', 'D' => 'Desconhecido'), $_1_u_farmacovigilancia_ditribuicaosinais); ?>
                                                </select>
                                            </div>  
                                            <div class="col-xs-1 col-md-1"></div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_ditribuicaosinais){
                                                    case 'Y':
                                                        echo "A, B";
                                                    break;
                                                    case 'N':
                                                        echo "N";
                                                    break;
                                                    case 'D':
                                                        echo "A, B, O1, O, N";
                                                    break;
                                                }
                                                ?>
                                            </div>                                          
                                        </div>

                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Associação Razoável Local Anatômico -->                
                                            <label>Existe uma associação razoável no tempo e/ou local anatômico?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_razoavelanatomico" class="form-control" onchange="salvarCampos(this, 'razoavelanatomico')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim (Associação razoável)', 'N' => 'Não (Nenhum associação razoável)', 'D' => 'Desconhecido'), $_1_u_farmacovigilancia_razoavelanatomico); ?>
                                                </select>
                                            </div> 
                                            <div class="col-xs-1 col-md-1"></div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_razoavelanatomico){
                                                    case 'Y':
                                                        echo "A, B";
                                                    break;
                                                    case 'N':
                                                        echo "N";
                                                    break;
                                                    case 'D':
                                                        echo "01 ou 0";
                                                    break;
                                                }
                                                ?>
                                            </div> 
                                        </div>
                                    </div>
                                    <!------------------------  CONEXÃO ASSOCIATIVA  ------------------------>

                                    <!------------------------  EXPLICAÇÃO FARMACOLÓGICA E/OU IMUNOLÓGICA  ------------------------>
                                    <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <div class="form-group col-xs-12 col-md-12 texto-central">                                        
                                            <label class="color-ft-dl">2 - EXPLICAÇÃO FARMACOLÓGICA E/OU IMUNOLÓGICA</label>
                                        </div>
                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Farmacológico/Toxicológico -->
                                            <label>O evento relatado se encaixa no perfil farmacológico/toxicológico ou potencial alérgico do produto?</label><br />
                                            <div class="form-group col-xs-11 col-md-11 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_farmacologicotoxicologico" class="form-control" onchange="salvarCampos(this, 'farmacologicotoxicologico')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_farmacologicotoxicologico); ?>
                                                </select>
                                            </div>
                                            <div class="info_farmaco pdt-10 dib col-xs-1 col-md-1" id="associacao_razoavel">
                                                <a class="fa fa-1x fa-info-circle btn-lg azul pointer hoverazul pl-0" title="Associação Razoável" data-target="webuiPopover0"></a>
                                            </div>
                                            <div class="webui-popover-content">
                                                <br />
                                                <div>        
                                                    <ul>
                                                        <li>Farmacologia conhecida, toxicologia do produto (substância activa e/ou excipientes);</li>
                                                        <li>Concentrações de VMP no sangue;</li>
                                                        <li>Relação dose-efeito (grau de contribuição de um produto para o desenvolvimento de uma reação).</li>
                                                    </ul>    
                                                    
                                                    <p> 4.1.1.
                                                    O evento relatado se encaixa no perfil toxicológico ou potencial alérgico do produto? O conhecimento farmacológico/toxicológico do produto se encaixa nos sinais? O evento adverso, a descrição dos fenômenos clínicos, é consistente ou, pelo menos, plausível, dada a farmacologia e toxicologia conhecidas do produto?</p>
                                                    <p>Compostos semelhantes causam eventos desse tipo?</p>                                
                                                </div>
                                            </div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_farmacologicotoxicologico){
                                                    case 'Y':
                                                        echo "A, B";
                                                    break;
                                                    case 'N':
                                                        echo "N";       
                                                    break;                                             
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        
                                        
                                        <!-- Dose Efeito -->
                                        <div class="form-group col-xs-12 col-md-12">
                                            <label>O evento adverso mostrou uma relação dose-efeito (por exemplo, superdosagem)?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_doseefeito" class="form-control" onchange="salvarCampos(this, 'doseefeito')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim', 'N' => 'Não', 'D' => 'Desconhecido'), $_1_u_farmacovigilancia_doseefeito); ?>
                                                </select>
                                            </div>
                                            <div class="info_farmaco pdt-10 dib col-xs-1 col-md-1" id="associacao_razoavel">
                                                <a class="fa fa-1x fa-info-circle btn-lg azul pointer hoverazul pl-0" title="Associação Razoável" data-target="webuiPopover0"></a>
                                            </div>
                                            <div class="webui-popover-content">
                                                <br />
                                                <div>                                                            
                                                    <p> 4.1.2.
                                                    Houve superdosagem do produto? A concentração do produto no sangue excedeu a concentração terapêutica?</p>
                                                    <p> As concentrações no plasma são conhecidas? Qual dose foi utilizada - superdosagem, dose correta, dose baixa, dose desconhecida?</p>                                
                                                    <p>O evento adverso mostrou uma relação dose-efeito?</p>
                                                </div>
                                            </div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_doseefeito){
                                                    case 'Y':
                                                        echo "A, B";
                                                    break;
                                                    case 'N':
                                                        echo "A, B, O1, O, N"; 
                                                    break; 
                                                    case 'D':
                                                        echo "A, B, O1, O, N";
                                                    break;                                              
                                                }
                                                ?>
                                            </div>                                          
                                        </div>                                   

                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Associação Farmacológico/Toxicológico -->                
                                            <label class="pdl-10">Existe uma associação razoável com o perfil farmacológico/toxicológico conhecido, o potencial alérgico do produto e/ou uma relação dose-efeito?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_associacaofarmacotoxi" class="form-control" onchange="salvarCampos(this, 'associacaofarmacotoxi')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_associacaofarmacotoxi); ?>
                                                </select>
                                            </div>  
                                            <div class="col-xs-1 col-md-1"></div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_associacaofarmacotoxi){
                                                    case 'Y':
                                                        echo "A, B";
                                                    break;
                                                    case 'N':
                                                        echo "O1, O, N"; 
                                                    break;                                               
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!------------------------  EXPLICAÇÃO FARMACOLÓGICA E/OU IMUNOLÓGICA  ------------------------>

                                    <!------------------------  PRESENÇA DE PRODUTO CARACTERÍSTICO OU FENÔMENOS CLÍNICOS OU PATOLÓGICOS RELACIONADOS COM O TRATAMENTO  ------------------------>
                                    <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <div class="form-group col-xs-12 col-md-12 texto-central">                                        
                                            <label class="color-ft-dl">3 - PRESENÇA DE PRODUTO CARACTERÍSTICO OU FENÔMENOS CLÍNICOS OU PATOLÓGICOS RELACIONADOS COM O TRATAMENTO</label>
                                        </div>                                   

                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Exames Laboratoriais -->                
                                            <label class="pdl-10">Dados adicionais (exames laboratoriais, achados patológicos) confirmam a probabilidade clínica?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_exameslaboratoriais" class="form-control" onchange="salvarCampos(this, 'exameslaboratoriais')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim', 'N' => 'Não', 'NA' => 'Não Aplicável/Não Disponível'), $_1_u_farmacovigilancia_exameslaboratoriais); ?>
                                                </select>
                                            </div>  
                                            <div class="info_farmaco pdt-10 dib col-xs-1 col-md-1" id="associacao_razoavel">
                                                <a class="fa fa-1x fa-info-circle btn-lg azul pointer hoverazul pl-0" title="Associação Razoável" data-target="webuiPopover0"></a>
                                            </div>
                                            <div class="webui-popover-content">
                                                <br />
                                                <div>                                                            
                                                    <p>Os fenômenos clínicos ou patológicos característicos presentes estão relacionados ao produto ou ao tratamento?</p>
                                                    <p>Existe algum critério mensurável para confirmar o evento adverso de forma objetiva, confirmando os fatores conhecidos (resultados post-mortem ou laboratoriais)?</p>                                
                                                </div>
                                            </div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_exameslaboratoriais){
                                                    case 'Y':
                                                        echo "A, B";
                                                    break;
                                                    case 'N':
                                                        echo "N";
                                                    break;
                                                    case 'NA':
                                                        echo "A, B, O1, O, N";      
                                                    break;                                          
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!------------------------  PRESENÇA DE PRODUTO CARACTERÍSTICO OU FENÔMENOS CLÍNICOS OU PATOLÓGICOS RELACIONADOS COM O TRATAMENTO  ------------------------>

                                    <!------------------------  CONHECIMENTO PRÉVIO DE RELATÓRIOS SEMELHANTES  ------------------------>
                                    <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <div class="form-group col-xs-12 col-md-12 texto-central">                                        
                                            <label class="color-ft-dl">4 - CONHECIMENTO PRÉVIO DE RELATÓRIOS SEMELHANTES</label>
                                        </div>                                   

                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Evento Relatado -->                
                                            <label class="pdl-10">O que dizer da consistência do evento relatado - já está descrito na literatura ou no SPC, já foi relatado anteriormente?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_eventorelatado" class="form-control" onchange="salvarCampos(this, 'eventorelatado')">
                                                    <option></option>
                                                    <? fillselect(array('YD' => 'Sim (Descritos na Literatura ou no SPC, descritos no registro)', 
                                                                        'YO' => 'Sim (Observado antes, mas não se adequando pharm./tox. perfil)', 
                                                                        'NN' => 'Não (Nunca observado antes, mas daptando pharm./tox. perfil)', 
                                                                        'NO' => 'Não (Nunca obersvado antes, não se encaixa pharm./tox. perfil)'), $_1_u_farmacovigilancia_eventorelatado); ?>
                                                </select>
                                            </div>  
                                            <div class="info_farmaco pdt-10 dib col-xs-1 col-md-1" id="associacao_razoavel">
                                                <a class="fa fa-1x fa-info-circle btn-lg azul pointer hoverazul pl-0" title="Associação Razoável" data-target="webuiPopover0"></a>
                                            </div>
                                            <div class="webui-popover-content">
                                                <br />
                                                <div>                                                            
                                                    <p>a. da literatura</p>
                                                    <p>b. de eventos adversos relatados antes</p>       
                                                    <p>Há algum relato desse evento conhecido na literatura?</p>     
                                                    <p>O evento é conhecido e esperado (descrito no SPC [summary of product characteristics - BULA])?</p>                    
                                                    <p>Houve relatórios anteriores com esses tipos de sinais? Esse tipo de evento foi relatado antes em um evento adverso?</p>
                                                    <p>O evento adverso (geralmente) é conhecido como potencialmente relacionado ao produto ou tratamento mencionado? ("evento adverso" a este respeito é o único sinal patológico ou o complexo [maioria dos sinais]. "Conhecido" significa publicado na literatura ou relatado antes e classificado como A (provável) ou B (possível)).</p>
                                                </div>
                                            </div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_eventorelatado){
                                                    case 'YD':
                                                        echo "A, B";
                                                    break;
                                                    case 'YO':
                                                        echo "B, O1, O, N";
                                                    break;
                                                    case 'NN':
                                                        echo "B, O1, O, N";
                                                    break;
                                                    case 'NO':
                                                        echo "O1, O, N";    
                                                    break;                                            
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!------------------------  CONHECIMENTO PRÉVIO DE RELATÓRIOS SEMELHANTES  ------------------------>

                                    <!------------------------  EXCLUSÃO DE OUTRAS CAUSAS  ------------------------>
                                    <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <div class="form-group col-xs-12 col-md-12 texto-central">                                        
                                            <label class="color-ft-dl">5 - EXCLUSÃO DE OUTRAS CAUSAS</label>
                                        </div>                                   

                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Outra Explicação  -->                
                                            <label class="pdl-10">Existe alguma outra explicação (confirmada, possível, sem outra explicação)?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_outraexplicacao" class="form-control" onchange="salvarCampos(this, 'outraexplicacao')">
                                                    <option></option>
                                                    <? fillselect(array('YC' => 'Sim (Confirmado)', 'YP' => 'Sim (Possível)', 'N' => 'Não'), $_1_u_farmacovigilancia_outraexplicacao); ?>
                                                </select>
                                            </div>  
                                            <div class="info_farmaco pdt-10 dib col-xs-1 col-md-1" id="associacao_razoavel">
                                                <a class="fa fa-1x fa-info-circle btn-lg azul pointer hoverazul pl-0" title="Associação Razoável" data-target="webuiPopover0"></a>
                                            </div>
                                            <div class="webui-popover-content">
                                                <br />
                                                <div>                                                            
                                                    <p>Existem outras possíveis causas para o evento adverso? Existe outra (também) causa provável?</p>
                                                    <p>Existe outra causa obviamente mais provável? Este evento adverso, no meu melhor conhecimento, não está relacionado ao tratamento?</p>       
                                                    <p>Uso de combinação de produtos / outros produtos usados?</p>     
                                                    <p>A doença atual está contribuindo para sinais? O estado de saúde do animal contribui para os sinais?</p>                    
                                                    <p>Os fatores predisponentes são conhecidos?</p>
                                                    <p>Existem outras causas confirmadas conhecidas (resultados post-mortem, resultados laboratoriais, re-desafio, outros produtos usados com potencial farmacológico-toxicológico para causar este evento)?</p>
                                                </div>
                                            </div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_outraexplicacao){
                                                    case 'YC':
                                                        echo "N";
                                                    break;
                                                    case 'YP':
                                                        echo "B, O1, O";
                                                    break;
                                                    case 'N':
                                                        echo "A";     
                                                    break;                                           
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!------------------------  EXCLUSÃO DE OUTRAS CAUSAS  ------------------------>

                                    <!------------------------  TOTALIDADE E CONFIABILIDADE DOS DADOS NOS RELATÓRIOS DE CASOS  ------------------------>
                                    <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <div class="form-group col-xs-12 col-md-12 texto-central">                                        
                                            <label class="color-ft-dl">6 - TOTALIDADE E CONFIABILIDADE DOS DADOS NOS RELATÓRIOS DE CASOS</label>
                                        </div>                                   

                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Informação Insuficiente -->                
                                            <label class="pdl-10">A informação reportada é insuficiente? Existe razão para duvidar da fonte / informação do relatório?</label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <select name="_1_<?=$_acao?>_farmacovigilancia_informacaoinsuficiente" class="form-control" onchange="salvarCampos(this, 'informacaoinsuficiente')">
                                                    <option></option>
                                                    <? fillselect(array('Y' => 'Sim', 'N' => 'Não'), $_1_u_farmacovigilancia_informacaoinsuficiente); ?>
                                                </select>
                                            </div>   
                                            <div class="col-xs-1 col-md-1"></div>
                                            <div class="form-group col-xs-1 col-md-1 pdt-10-fs-12">
                                                <?
                                                switch($_1_u_farmacovigilancia_informacaoinsuficiente){
                                                    case 'Y':
                                                        echo "O1, O";     
                                                    break;                                   
                                                    case 'N':
                                                        echo "A, B, N";  
                                                    break;                                              
                                                }
                                                ?>
                                            </div>                                       
                                        </div>
                                    </div>
                                    <!------------------------  TOTALIDADE E CONFIABILIDADE DOS DADOS NOS RELATÓRIOS DE CASOS  ------------------------>
                                    
                                    <!------------------------  AVALIAÇÃO DA CAUSALIDADE ATRAVÉS DO JULGAMENTO DAS RESPOSTAS AO QUESTIONÁRIO - CRITÉRIOS MÍNIMOS  ------------------------>
                                    <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                        <div class="form-group col-xs-12 col-md-12 texto-central">                                        
                                            <label class="color-ft-dl">RESULTADO DA AVALIAÇÃO</label>
                                        </div>

                                        <div class="form-group col-xs-12 col-md-12">
                                            <!-- Informação Insuficiente -->                
                                            <label class="pdl-10">Avaliação da causalidade através do julgamento das respostas ao questionário - critérios mínimos: </label><br />
                                            <div class="form-group col-xs-10 col-md-10 wd-65">
                                                <?
                                                if($_1_u_farmacovigilancia_razoavelanatomico == 'Y' && $_1_u_farmacovigilancia_associacaofarmacotoxi == 'Y'
                                                    && $_1_u_farmacovigilancia_outraexplicacao == 'N' && $_1_u_farmacovigilancia_informacaoinsuficiente == 'N'){
                                                    echo "<div class='col-xs-12 bg-danger texto-resultado'> Para inclusão na categoria A (Provável) </div>";                                            
                                                } elseif(($_1_u_farmacovigilancia_razoavelanatomico == 'Y' && $_1_u_farmacovigilancia_associacaofarmacotoxi == 'Y'
                                                        && in_array($_1_u_farmacovigilancia_outraexplicacao, array('YC', 'YP')) && $_1_u_farmacovigilancia_informacaoinsuficiente == 'N')
                                                        || (in_array($_1_u_farmacovigilancia_eventorelatado, array('YD', 'YO')) && $_1_u_farmacovigilancia_informacaoinsuficiente == 'N'
                                                        && $_1_u_farmacovigilancia_razoavelanatomico == 'Y' && $_1_u_farmacovigilancia_associacaofarmacotoxi == 'Y')){
                                                    echo "<div class='col-xs-12 bg-success texto-resultado'> Para inclusão na categoria B (Possível) </div>";
                                                } elseif(($_1_u_farmacovigilancia_razoavelanatomico == 'Y' && $_1_u_farmacovigilancia_associacaofarmacotoxi == 'Y'
                                                        && in_array($_1_u_farmacovigilancia_outraexplicacao, array('N')) && $_1_u_farmacovigilancia_informacaoinsuficiente == 'Y')){
                                                    echo "<div class='col-xs-12 bg-purple texto-resultado'> Para inclusão na categoria O1 (Inconclusivo) </div>";
                                                } elseif(($_1_u_farmacovigilancia_informacaoinsuficiente == 'Y' && $_1_u_farmacovigilancia_razoavelanatomico == 'D'
                                                        && $_1_u_farmacovigilancia_associacaofarmacotoxi == 'N' && $_1_u_farmacovigilancia_exameslaboratoriais == 'NA'
                                                        && in_array($_1_u_farmacovigilancia_eventorelatado, array('NN', 'NO')) && $_1_u_farmacovigilancia_outraexplicacao == 'N')){
                                                    echo "<div class='col-xs-12 bg-primary texto-resultado'> Para inclusão na categoria O (Não classificável/Não avaliável) </div>";
                                                } elseif(in_array($_1_u_farmacovigilancia_outraexplicacao, array('YC', 'YP')) && $_1_u_farmacovigilancia_informacaoinsuficiente == 'N'){
                                                    echo "<div class='col-xs-12 bg-wine texto-resultado'> Para inclusão na categoria N (Improvável) </div>";
                                                }
                                                ?>
                                            </div>   
                                        </div>
                                    </div>
                                    <!------------------------  AVALIAÇÃO DA CAUSALIDADE ATRAVÉS DO JULGAMENTO DAS RESPOSTAS AO QUESTIONÁRIO - CRITÉRIOS MÍNIMOS  ------------------------>
                                </div>                                            
                            </div>

                            
                            
                            <!------------------------  aboon  ------------------------>
                        <? } ?>
                        <div role="tabpanel" class="tab-pane fade" id="farmaco_avalgq">     
                            <div class="panel-body">
                                <div class="panel panel-default " style="margin-top: -15px !important; border: 0px !important">
                                    <div class="form-group col-xs-12 col-md-12">                                        
                                        <label>Descreva a Avaliação da Garantia da Qualidade:</label><br />
                                        <textarea name="_1_<?=$_acao?>_farmacovigilancia_avalgarantia" class="form-control"><?=$_1_u_farmacovigilancia_avalgarantia?></textarea>                                          
                                    </div>
                                </div>
                            </div>                                                                        
                        </div>
                        <div id="circularProgressIndicator" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>

<script>
    if( $("[name=_1_u_farmacovigilancia_idfarmacovigilancia]").val() ){
        $(".cbupload").dropzone({
            idObjeto: $("[name=_1_u_farmacovigilancia_idfarmacovigilancia]").val()
            ,tipoObjeto: 'farmacovigilancia'
        });
    }
</script>
<?
$tabaud = "farmacovigilancia";
require 'viewCriadoAlterado.php';
require_once('../form/js/farmacovigilancia_js.php'); 
?>