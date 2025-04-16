<?
session_start();
require_once("../inc/php/functions.php");
require_once("../inc/php/validaacesso.php");


/*
 * $pagvaltabela: tablea principal a ser atualizada pelo formulario html
 * $pagvalcampos: Informar os parà¢metros GET que devem ser validados para compor o select principal
 *                pk: indica parà¢metro chave para o select inicial
 *                vnulo: indica parà¢metros secundários que devem somente ser validados se nulo ou não
 */
$pagvaltabela = "pessoa";
$pagvalmodulo='funcionario';
$pagvalcampos = array(
	"idpessoa" => "pk"
);
/*
 * $sqlinicial: Faz o select para inicializar as variáveis que preenchem os campos da tela em caso de update
 */
$pagsql = "select * from pessoa where idpessoa = '#pkid'";
/*
 * controlevariaveisgetpost.php: Realiza o procedimento de validacao do GET e preenchimento das variáveis que vieram por POST
 */

include_once("../inc/php/controlevariaveisgetpost.php");
?>
<html>
<head>
<title>Admissão</title>
</head>


<link href="../inc/css/report.css" media="all" rel="stylesheet" type="text/css" />


<body>
<?
		$_timbrado = $_GET["_timbrado"] != ''? $_GET["_timbrado"]:'';
		$timbradoidempresa = $_GET["_timbradoidempresa"] != ''? "and idempresa = ".$_GET["_timbradoidempresa"]:getImagemRelatorio('pessoa', 'idpessoa', $_1_u_pessoa_idpessoa);
		
		if($_timbrado != 'N'){
	
			$_sqltimbrado="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'HEADERSERVICO'";
			$_restimbrado = mysql_query($_sqltimbrado) or die("Erro ao retornar figura para cabeçalho do relatório: ".mysql_error());
			$_figtimbrado=mysql_fetch_assoc($_restimbrado);

			$_sqltimbrado1="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMMARCADAGUA'";
			$_restimbrado1 = mysql_query($_sqltimbrado1) or die("Erro ao retornar figura do relatório: ".mysql_error());
			$_figtimbrado1=mysql_fetch_assoc($_restimbrado1);

			$_sqltimbrado2="select * from empresaimagem where 1 ".$timbradoidempresa." and tipoimagem = 'IMAGEMRODAPE'";
			$_restimbrado2 = mysql_query($_sqltimbrado2) or die("Erro ao retornar figura do relatório: ".mysql_error());
			$_figtimbrado2=mysql_fetch_assoc($_restimbrado2);
			
			$_timbradocabecalho = $_figtimbrado["caminho"];
			$_timbradomarcadagua = $_figtimbrado1["caminho"];
			$_timbradorodape = $_figtimbrado2["caminho"];
			
			if(!empty($_timbradocabecalho)){?>
				<div id="_timbradocabecalho"><img src="<?=$_timbradocabecalho?>" height="90px" width="100%"></div>
			<?}
		}
	$sqlempresa = "select * from empresa where 1 ".getidempresa('idempresa','empresa');
	$resempresa = mysql_query($sqlempresa) or die("Erro ao retornar informações da empresa: ".mysql_error());
	$rowempresa=mysql_fetch_assoc($resempresa);
?>
    <pagina>
	<p>
	<div class="row">
		<div class="col grupo 100 quebralinha">
			<div class="titulogrupo"></div>
		</div>
	</div>
	<div class="row">
            <div class="col 20 rot">Nome Empresa:</div>
            <div class="col 90 val"><?=$rowempresa["razaosocial"]?></div>	      
        </div>
	<div class="row">
            <div class="col 20 rot">CNPJ:</div>
            <div class="col 90 val"><?=formatarCPF_CNPJ($rowempresa["cnpj"],true);?></div>	      
        </div>
	<div class="row">
            <div class="col 20 rot">Endereço:</div>
            <div class="col 90 val"><?=$rowempresa["xlgr"].' - '.$rowempresa["nro"].' - '.$rowempresa["xbairro"].' - CEP: '.formatarCEP($rowempresa["cep"],true).' - '.$rowempresa["xmun"].'/'.$rowempresa["uf"]?></div>	      
        </div>
	<p>
	<div class="row">
		<div class="col grupo 100 quebralinha">
			<div class="titulogrupo">Os Campos Abaixo Devem Ser Preenchidos Obrigatóriamente</div>
		</div>
	</div>
	
	<div class="row">
            <div class="col 20 rot">Nome Empregado:</div>
            <div class="col 90 val"><?=strtoupper($_1_u_pessoa_nome)?></div>	      
        </div>
	
   
<?
		$sqlend="select c.cidade,e.logradouro,e.endereco,e.numero,e.complemento,e.bairro,e.cep,e.uf
			from nfscidadesiaf c,endereco e
			where c.codcidade = e.codcidade
			and e.idtipoendereco=5
			and e.idpessoa =".$_1_u_pessoa_idpessoa;
		$resend=mysql_query($sqlend) or die("erro ao buscar informaçàµes do endereço sql=".$sqlend);
		$qtdend=mysql_num_rows($resend);
		$rowend=mysql_fetch_assoc($resend);
		
?>	
	<div class="row">
            <div class="col 20 rot">Endereço:</div>
            <div class="col 90 val">
		    <?=$rowend["logradouro"]?> <?=$rowend["endereco"]?> 
		    <?if($rowend['numero']){echo("N&ordm;:".$rowend['numero']);}?>
		    <?if($rowend['complemento']){echo(", ".$rowend["complemento"]);}?>				
		    <?if($rowend['bairro']){echo(", BAIRRO: ". $rowend['bairro']);}?>				
		    <?if($rowend['cidade']){echo(", ".$rowend['cidade']);}?>
		    <?if($rowend['uf']){echo("-".$rowend['uf']);}?>
	    </div>	      
        </div>	
	<div class="row">
            <div class="col 20 rot">CEP:</div>
            <div class="col 20 val"><?if($rowend['cep']){echo(formatarCEP($rowend['cep'],true) );}?></div>	      
	    <div class="col 10 rot">Escolaridade:</div>
            <div class="col 30 val"><?=$_1_u_pessoa_escolaridade?></div>	      
	    <div class="col 10 rot">Est. Civil:</div>
            <div class="col 20 val"><?=$_1_u_pessoa_estcivil?></div>
	</div>
	<? if(array_key_exists("rhfolha", getModsUsr("MODULOS")) == 1) { ?>
		<div class="row">
				<div class="col 20 rot">Salário:</div>
				<div class="col 20 val"><?=$_1_u_pessoa_salario?> R$</div>	      
			<div class="col 10 rot">Insalubridade:</div>
				<div class="col 30 val"><?=$_1_u_pessoa_insalubridade?> Por: Mês</div>	      
			<div class="col 10 val"></div>
				<div class="col 20 val"></div>
		</div>
	<? } ?>
	<div class="row">
            <div class="col 20 rot">Dependentes p/ IR:</div>
            <div class="col 20 val">(&nbsp;&nbsp;&nbsp;&nbsp;) Esposa(o)</div>	      
	    <div class="col 10 rot"></div>
            <div class="col 30 val">(&nbsp;&nbsp;<?=$_1_u_pessoa_qtddependente?> &nbsp;&nbsp;) Qtde de Filhos</div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<div class="row">
            <div class="col 20 rot">Data Admissão:</div>
            <div class="col 20 val"><?=$_1_u_pessoa_contratacao?> </div>	      
	    <div class="col 10 rot">Função:</div>
            <div class="col 30 val"><?=traduzid("sgcargo","idsgcargo","cargo",$_1_u_pessoa_idsgcargo)?></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<div class="row">
            <div class="col 20 rot">Contrato de Experiência:</div>
            <div class="col 20 val"><?=$_1_u_pessoa_ctrexp?>dias. </div>	      
	    <div class="col 10 rot">PIS:</div>
            <div class="col 30 val"><?=$_1_u_pessoa_pis?></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<p>
	<div class="row">
		<div class="col grupo 100 quebralinha">
			<div class="titulogrupo">Horário de Trabalho</div>
		</div>
	</div>
	<div class="row">
            <div class="col 100 rot">
		Segunda à  Sexta das_______à s_______e_______ás_______
	    </div>
	</div>
							
	<div class="row">
            <div class="col 100 rot">Sábado das______ás______</div>
	</div>
	<p>
	<div class="row">
		<div class="col grupo 100 quebralinha">
			<div class="titulogrupo">Documentos</div>
		</div>
	</div>
	<div class="row">
            <div class="col 20 rot">Carteira de Trabalho:</div>
            <div class="col 20 val"><?=$_1_u_pessoa_ctps?> </div>	      
	    <div class="col 10 rot">Série:</div>
            <div class="col 30 val"><?=$_1_u_pessoa_ctpsserie?></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<div class="row">
            <div class="col 20 rot">CPF:</div>
            <div class="col 20 val"><?echo(formatarCPF_CNPJ($_1_u_pessoa_cpfcnpj,true));?></div>	      
	    <div class="col 10 rot"></div>
            <div class="col 30 val"></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<div class="row">
            <div class="col 20 rot">Título de Eleitor:</div>
            <div class="col 20 val"><?=$_1_u_pessoa_titeleitor?></div>	      
	    <div class="col 10 rot">Zona:</div>
            <div class="col 30 val"><?=$_1_u_pessoa_zona?></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<div class="row">
            <div class="col 20 rot">Carteira de Identidade:</div>
            <div class="col 20 val"><?=$_1_u_pessoa_rg?></div>	      
	    <div class="col 10 rot">Estado:</div>
            <div class="col 30 val"><?=$_1_u_pessoa_rgorgexpedidor?></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<div class="row">
            <div class="col 20 rot">Cert. Alist. Militar:</div>
            <div class="col 20 val"><?=$_1_u_pessoa_alistmil?></div>	      
	    <div class="col 10 rot">Categoria:</div>
            <div class="col 30 val"></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>	
	<div class="row">
            <div class="col 20 rot">CNH:</div>
            <div class="col 20 val"><?=$_1_u_pessoa_cnh?></div>	      
	    <div class="col 10 rot"></div>
            <div class="col 30 val"></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<div class="row">
            <div class="col 20 rot">Dt de Nascimento:</div>
            <div class="col 20 val"><?=$_1_u_pessoa_nasc?></div>	      
	    <div class="col 10 rot">Naturalidade:</div>
            <div class="col 30 val"><?=$_1_u_pessoa_cidnasc?></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<div class="row">
            <div class="col 20 rot">Pai:</div>
            <div class="col 20 val"><?=strtoupper($_1_u_pessoa_nomepai)?></div>	      
	    <div class="col 10 rot"></div>
            <div class="col 30 val"></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<div class="row">
            <div class="col 20 rot">Mãe:</div>
            <div class="col 20 val"><?=strtoupper($_1_u_pessoa_nomemae)?></div>	      
	    <div class="col 10 rot"></div>
            <div class="col 30 val"></div>	      
	    <div class="col 10 val"></div>
            <div class="col 20 val"></div>
	</div>
	<p>
	<div class="row">
		<div class="col grupo 100 quebralinha">
			<div class="titulogrupo">Para direito a Cota de Salário-família, Apresentar:</div>
		</div>
	</div>
																							
	<div class="row">
			
			<div class=" col 100 quebralinha">
			    Xerox Certidão de Nascimento dos filhos menores 14 anos juntamente com: filhos até 																	
	7 anos cartão de vacina atualizado, maiores de 7 anos apresentar semestralmente atestado frequência escolar.
			</div>
	</div>
	<p>
	<div class="row">
		<div class="col grupo 100 quebralinha">
			<div class="titulogrupo">Documentos a Apresentar</div>
		</div>
	</div>
																							
	<div class="row">
			
			<div class=" col 100 quebralinha">
			    01 foto 3x4<BR>																	
	Exame Admissional<BR>																	
	Obs.: A data do exame impreterivelmente não pode ser com data posterior a sua admissão.<BR>																	
	Livro de Registro de Empregados		<BR>															
	Carteira de Trabalho<BR>
			</div>
	</div>	
	<p>														
	<div class="row">
		<div class="col grupo 100 quebralinha">
			<div class="titulogrupo"></div>
		</div>
	</div>
	<p>
	<div class="row">			
	    <div class=" col 100 quebralinha">
		Uberlândia - MG,_______de_______________________________de____________.
	    </div>
	</div>	
	<div class="row">			
	    <div class=" col 100 quebralinha">
		Empresa:______________________________________________________________.
	    </div>
	</div>	
	<div class="row">			
	    <div class=" col 100 quebralinha">
		Documentos entregues a:_______________________________________________.
	    </div>
	</div>	
	<div class="row">			
	    <div class=" col 100 quebralinha">
		Responsável pelo Registro:___________________________________________.
	    </div>
	</div>	
<p>&nbsp;</p>
		<?
		if(!empty($_timbradorodape)){?>
			<div id="_timbradorodape"><img src="<?=$_timbradorodape?>" height="90px" width="100%"></div>
		<?}?>
</body>
</html>


