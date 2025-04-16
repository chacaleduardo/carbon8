<?
require_once("../inc/php/validaacesso.php");

class PESSOA 
{ 
	//Retorna Tipo Pessoa - Utilizado no Evento
	function getListaTipoPessoa()
	{
		$sqlm = "SELECT idtipopessoa, tipopessoa
				  FROM tipopessoa
				WHERE status='ATIVO'
				  AND idempresa = ".$_SESSION["SESSAO"]["IDEMPRESA"]."
			  ORDER BY tipopessoa;";
		$resm =  d::b()->query($sqlm)  or die("Erro tipopessoa campo 	Prompt Drop sql model/pessoa:".$sqlm);
		return $resm;
	}

	//Retorna Lista de Funcionários para inserir no Evento - Nativo
	function getJfuncionario($_1_u_evento_idevento) 
	{   
		global $JSON, $_1_u_evento_idevento;        
		$sql = "SELECT a.idpessoa ,a.nomecurto    
				FROM pessoa a
				WHERE a.idempresa =".idempresa()." 
				  AND a.status ='ATIVO'
				  AND a.idtipopessoa = 1
				  AND NOT idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"]."
				  AND NOT EXISTS(
					  SELECT 1
						FROM fluxostatuspessoa r
					  WHERE r.idmodulo= '".$_1_u_evento_idevento."' 
						AND r.tipoobjeto ='pessoa'
						AND a.idpessoa = r.idobjeto)
			  ORDER BY a.nomecurto ASC";

		$rts = d::b()->query($sql) or die("Erro ao atualizar Lista de Funcionarios model/pessoa: ". mysqli_error(d::b()));

		$arrtmp = array();
		$i = 0;

		while ($r = mysqli_fetch_assoc($rts)) {
			$arrtmp[$i]["value"]=$r["idpessoa"];
			$arrtmp[$i]["label"]= $r["nomecurto"];
			$i++;
		}

		return $JSON->encode($arrtmp);    
	}

	//Retorna os Grupos para inserir no Evento - Nativo
	function getJFuncSetvinc($idmodulo, $modulo) 
	{   
		global $JSON;     

		//LTM - 22-09-2020 - 374136: Acrescentado para selecionar representantes
		$sql = "SELECT a.idpessoa AS pessoa, CONCAT('<i class=\"fa fa-user\" style=\"color:#ddd;font-size:10px;\"></i> ',IF(a.nomecurto is NULL,concat(e.sigla,' - ',a.nome), concat(e.sigla,' - ',a.nomecurto))) AS nome, 'pessoa' AS 'tipo', IF(a.nomecurto is NULL, a.nome, a.nomecurto) as labelnome
					FROM pessoa a left join empresa e on (e.idempresa=a.idempresa)
					WHERE  a.idtipopessoa = 1
					".share::pessoaseventoPorSessionIdempresa('a.idpessoa')."
					-- AND NOT idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"]." 
					AND NOT usuario is null
					AND NOT EXISTS(
						SELECT 1
							FROM fluxostatuspessoa r
							WHERE r.idmodulo= '".$idmodulo."' 
							AND r.modulo = '".$modulo."'
							AND r.tipoobjeto ='pessoa'
							AND a.idpessoa = r.idobjeto)
				UNION
					SELECT idimgrupo AS pessoa, CONCAT('<i class=\"fa fa-users\" style=\"color:lightblue;font-size:10px;\"></i> ',concat(e.sigla,' - ',g.grupo)) AS nome, 'grupo' as 'tipo', grupo as labelnome
						FROM imgrupo g left join empresa e on (e.idempresa=g.idempresa)
						WHERE 1
						".share::gruposeventoPorSessionIdempresa('g.idimgrupo')."
						AND NOT EXISTS(
							SELECT 1
								FROM fluxostatuspessoa r
								WHERE r.idmodulo= '".$idmodulo."' 
								AND r.modulo = '".$modulo."'
								AND r.tipoobjeto ='imgrupo'
								AND g.idobjetoext = r.idobjeto)	
				UNION
					SELECT a.idpessoa AS pessoa, CONCAT('<i class=\"fa fa-user\" style=\"color:#ddd;font-size:10px;\"></i> ',IF(a.nomecurto is NULL,concat(e.sigla,' - ',a.nome), concat(e.sigla,' - ',a.nomecurto))) AS nome, 'pessoa' AS 'tipo', IF(a.nomecurto is NULL, a.nome, a.nomecurto) as labelnome
					FROM pessoa a left join empresa e on (e.idempresa=a.idempresa)
					WHERE a.idtipopessoa in (15, 16, 113)
					".share::pessoaseventoPorSessionIdempresa('a.idpessoa')."
					-- AND NOT idpessoa=".$_SESSION["SESSAO"]["IDPESSOA"]."
					AND NOT usuario is null
					AND NOT EXISTS(
						SELECT 1
							FROM fluxostatuspessoa r
							WHERE r.idmodulo= '".$idmodulo."' 
							AND r.modulo = '".$modulo."'
							AND r.tipoobjeto ='pessoa'
							AND a.idpessoa = r.idobjeto)			
				ORDER BY nome ASC";
		$rts = d::b()->query($sql) or die("getJFuncSetvinc model/pessoa: ". mysqli_error(d::b()));

		$arrtmp = array();
		$i = 0;

		while ($r = mysqli_fetch_assoc($rts)) {
			$arrtmp[$i]["value"]=$r["pessoa"].','.$r["tipo"];
			$arrtmp[$i]["label"]= $r["nome"];
			$arrtmp[$i]["labelnome"]= $r["labelnome"];
			$i++;
		}
		  
		return $JSON->encode($arrtmp);    
	}

	//Retorna as Pessoas de acordo com o tipo que é passado
	function getPessoas($intipopessoa) {
		global $JSON;
		$sql = "SELECT p.idpessoa, 
					   CONCAT(e.sigla, ' - ',p.nome) AS nome,
					   p.idtipopessoa
				  FROM pessoa p JOIN empresa e ON e.idempresa = p.idempresa
				 WHERE 1 /*".getidempresa('p.idempresa','', true)."*/
				   AND p.idtipopessoa in (".$intipopessoa.")
				   AND p.status IN ('ATIVO', 'PENDENTE')

				ORDER BY p.nome";

		$rts = d::b()->query($sql) or die("Erro Pessoas model/pessoa: " . mysqli_error(d::b()));

		$arrtmp = array();
		$i = 0;

		while ($r = mysqli_fetch_assoc($rts)) {
			$arrtmp[$i]["value"] = $r["idpessoa"];         
			$arrtmp[$i]["label"] = $r["nome"];
			$i++;
		}

		return $JSON->encode($arrtmp);
	}
	
	//Função que retorna os funcionários
	function getJfuncionarioListaTodos() 
	{	
		global $JSON;

		$sql = "SELECT a.idpessoa ,a.nomecurto    
				  FROM pessoa a
				 WHERE a.idempresa =".idempresa()." 
				   AND a.status ='ATIVO'
				   AND a.idtipopessoa = 1
			  ORDER BY a.nomecurto asc";

		$rts = d::b()->query($sql) or die("oioi: ". mysqli_error(d::b()));

		$arrtmp = array();
		$i = 0;

		while ($r = mysqli_fetch_assoc($rts)) {
			$arrtmp[$i]["value"]=$r["idpessoa"];
			$arrtmp[$i]["label"]= $r["nomecurto"];
			$i++;
		}

		return $JSON->encode($arrtmp);    
	}
	
	function getJSetorvinc() 
	{
		global $JSON;

		$sql = "SELECT idimgrupo, grupo
				  FROM imgrupo
				 WHERE idempresa = ".idempresa()." 
				   AND status='ATIVO'
				ORDER BY grupo ASC";

		$rts = d::b()->query($sql) or die("getJSetorvinc: ". mysqli_error(d::b()));

		$arrtmp = array();
		$i = 0;

		while ($r = mysqli_fetch_assoc($rts)) {
			$arrtmp[$i]["value"] = $r["idimgrupo"];
			$arrtmp[$i]["label"] = $r["grupo"];
			$i++;
		}

		return $JSON->encode($arrtmp);
	}

	//Retorna os botões do Eventotipo na listagem de Participantes inseridos.
	function listaPessoa()
    {
        global $_1_u_eventotipo_ideventotipo;
        $s = "SELECT d.idfluxoobjeto, 
					 s.nomecurto as nome,
					 s.idpessoa,
					 d.criadopor,
					 d.criadoem,
					 s.status,
					 d.assina,
					 d.inidstatus
                from fluxo ms JOIN fluxoobjeto d ON d.idfluxo = ms.idfluxo 
				JOIN pessoa s ON s.idpessoa = d.idobjeto and d.tipoobjeto ='pessoa' and d.tipo = 'PARTICIPANTE'
				where ms.ideventotipo = ".$_1_u_eventotipo_ideventotipo." order by s.nome";
    
        $rts = d::b()->query($s) or die("listaPessoa: ". mysqli_error(d::b()));       
        while ($r = mysqli_fetch_assoc($rts)) 
        {
            $mod='funcionario';           
            if ($r["status"] == 'ATIVO'){ $opacity = ''; $cor = 'verde hoververde'; }else{ $opacity = 'opacity'; $cor = 'vermelho hoververmelho ';}
           
            $botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable'    onclick='retiraeventotiporesp(".$r["ideventotiporesp"].")'></i>";  
            $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
            
            echo "<tr id=".$r["idfluxoobjeto"]." class='".$opacity."' title=".$title."> 
                    <td style='min-width: 10px;' id='statuses'><span class='circle button-blue'></span></td><td>".$r["nome"]."</td>
                    <td>
                        <select class='size7' name='_assinar' onchange='atEventotiporesp(this,".$r["idfluxoobjeto"].")'>
                            <option value=''></option>";
                            echo fillselect("select 'PARCIAL','Parcial' union select 'TODOS','Todos'  union select 'INDIVIDUAL', 'Individual'",$r['assina']);
            echo "  </select>
                    </td>";
            ?>
            <td class="proxes">
                <?
                $sqb="select e.idstatus,e.rotulo,e.rotuloresp,e.cor
                    from fluxo ms JOIN fluxofluxo ts ON ms.idfluxo = ts.idfluxo AND ms.tipoobjeto = 'ideventotipo'
					join "._DBCARBON."._status e on(ts.idobjeto = e.idstatus)
                    where ideventotipo=".$_1_u_eventotipo_ideventotipo."
                    order by ordem";
                //die($sqb);
                $rb = d::b()->query($sqb) or die("Erro ao buscar status do fluxo: ". mysqli_error(d::b()));
                while($rob=mysqli_fetch_assoc($rb))
                {
                    if($r['inidstatus']!=null and $r['inidstatus']!=''){
                        $aridstatusoc = explode(",",$r['inidstatus']);
                    }else{
                        $aridstatusoc=array();  
                    }

                    if (in_array($rob['idstatus'], $aridstatusoc)) {
                        $pclass='selecionado';
                        $pcircle='fa-circle';
                        $key = array_search($rob['idstatus'], $aridstatusoc);
                    if($key!==false){
                        unset($aridstatusoc[$key]);
                    }
                    $aridstatusoc = implode(",", $aridstatusoc);
                    }else{
                        $pclass=''; 
                        $pcircle='fa-circle-o';
                        if(count($aridstatusoc)>0){
                            array_push($aridstatusoc,$rob['idstatus']);
                            $aridstatusoc = implode(",", $aridstatusoc);
                        }else{
                            $aridstatusoc=$rob['idstatus'];
                        }
                    }
                    ?>
                    <i title="<?=$rob['rotuloresp']?>" class="iproxstatus fa <?=$pcircle?> dropdown-toggle <?=$pclass?>" data-toggle="dropdown" style="color:<?=$rob['cor']?>;" onclick="selecionaProxStatusResp(this,<?=$r["idfluxoobjeto"]?>,'<?=$aridstatusoc?>')" ></i>
                    <?
                }
                ?>
            </td> 
            <?
            echo"  <td>".$botao."</td> </tr>";                                                                    
        }     
    }	
	
	function listaPessoa2()
	{
    
        global $_1_u_eventotipo_ideventotipo;
        $s = "select d.idfluxoobjeto,
					 s.nomecurto as nome,
					 s.idpessoa,
					 d.criadopor,
					 d.criadoem,
					 s.status,
					 d.assina
                 from fluxo ms JOIN fluxoobjeto d ON d.idfluxo = ms.idfluxo 
				JOIN pessoa s ON s.idpessoa = d.idobjeto and d.tipoobjeto ='pessoa' and d.tipo = 'CRIADOR'
				where ms.ideventotipo = ".$_1_u_eventotipo_ideventotipo." order by s.nome";

        $rts = d::b()->query($s) or die("listaPessoa: ". mysqli_error(d::b()));
    
       
        while ($r = mysqli_fetch_assoc($rts)) {
    
            $mod='funcionario';
           
            if ($r["status"] == 'ATIVO'){ $opacity = ''; $cor = 'verde hoververde'; }else{ $opacity = 'opacity'; $cor = 'vermelho hoververmelho ';}
           
            $botao="<i class='fa fa-trash fa-1x cinzaclaro hoververmelho btn-lg pointer ui-droppable'    onclick='retiraeventotiporesp(".$r["idfluxoobjeto"].")'></i>";
         
            $title="Vinculado por: ".$r["criadopor"]." - ".dmahms($r["criadoem"],true);
            
            echo "<tr id=".$r["idfluxoobjeto"]." class='".$opacity."' title=".$title."> 
                    <td style='min-width: 10px;' id='statuses'><span class='circle button-blue'></span></td><td>".$r["nome"]."</td><td>".$botao."</td>    
                    </tr>";
                                                                    
        }   
    }
}
?>