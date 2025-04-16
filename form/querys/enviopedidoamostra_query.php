<?php
require_once(__DIR__ . "/_iquery.php");

class EnvioPedidoAmostra implements DefaultQuery
{

    public static $table = 'entregaepi';
    public static $pk = 'identregaepi';

    public static function buscarPorChavePrimaria()
    {
        return replaceQueryWildCard(self::buscarPorChavePrimariaSQL, [
            'table' => self::$table,
            'pk' => self::$pk
        ]);
    }

    public static function buscarInformacoesPedidoAmostra()
    {
        return "SELECT *
			FROM enviopedido ep
			where ep.protocolo = '?protocolo?';";
    }

    public static function gerarAmostraPeloPedido()
    {
        return "INSERT INTO amostra (
                                idpessoa,
                                paciente,
                                nroamostra,
                                tutor,
                                idade,
                                tipoidade,
                                idespeciefinalidade,
                                sexo,
                                responsavel,
                                responsavelcolcrmv,
                                responsavelcolcont,
                                uf,
                                email,
                                clienteterceiro,
                                observacaointerna,
                                status,
                                idunidade,
                                idempresa,
                                exercicio,
                                idsubtipoamostra,
                                idnucleo,
                                datacoleta,
                                horacoleta,
                                meiotransp,
                                idregistro,
                                dataamostra,
                                criadoem,
                                criadopor
                                )
                VALUES (
                        '?idpessoa?',
                        '?paciente?',
                        '?nroamostra?',
                        '?tutor?',
                        '?idade?',
                        '?tipoidade?',
                        '?idespeciefinalidade?',
                        '?sexo?',
                        '?responsavel?',
                        '?responsavelcolcrmv?',
                        '?responsavelcolcont?',
                        '?uf?',
                        '?email?',
                        '?clienteterceiro?',
                        '?observacaointerna?',
                        '?status?',
                        '?idunidade?',
                        '?idempresa?',
                        '?exercicio?',
                        '?idsubtipoamostra?',
                        '?idnucleo?',
                        '?datacoleta?',
                        '?horacoleta?',
                        '?meiotransp?',
                        '?idregistro?',
                        '?dataamostra?',
                        '?criadoem?',
                        '?criadopor?'
                    )";
    }
    
    public static function gerarRegistroAmostraPeloPedido($idunidade)
    {
        $exercicio = date('Y').'PROVISORIO';
        $sql = "update seqregistro set idregistro = (idregistro + 1) 
                where exercicio = '" . $exercicio . "'
                -- and idempresa = " . $_SESSION["SESSAO"]["IDEMPRESA"] . "
                and idunidade = " . $idunidade;
        ### Tenta incrementar e recuperar o ID Atual do exercicio corrente
        d::b()->query($sql) or die("Falha 1 atualizando IdRegistro : " . mysqli_error(d::b()) . "<p>SQL: $sql");

        $sql = "SELECT left(exercicio, 4) AS exercicio, idregistro 
                FROM seqregistro
                where exercicio = '" . $exercicio . "'
                -- and idempresa = " . $_SESSION["SESSAO"]["IDEMPRESA"] . "
                    and idunidade = " . $idunidade;

        $resexercicio = d::b()->query($sql);

        if (!$resexercicio) {
            die("Falha Pesquisando Exercicio X IdRegistro : <p>SQL: " . $sql . "<br>Erro:" . mysqli_error(d::b()));
        }

        $rowexercicio = mysqli_fetch_assoc($resexercicio);

        ### Caso nao retorne nenhuma linha, sera necessario inicializar um novo ano de exercicio, com idamostra=1
        if (empty($rowexercicio["idregistro"])) {
            $sqlatualizaexercicio =  "INSERT INTO seqregistro (idempresa,exercicio,idregistro,idunidade) 
                    VALUES (" . $_SESSION["arrpostbuffer"]["1"]["i"]["amostra"]["idempresa"] . ",'" . $exercicio . "'," . $_SESSION["SESSAO"]["IDEMPRESA"] . "," . $idunidade . ");";

            $resexercicio = d::b()->query($sqlatualizaexercicio) or die("Falha 2 atualizando Exercicio : " . mysqli_error(d::b()) . "<p>SQL: $sqlatualizaexercicio");

            if (!$resexercicio) {
                echo "Falha iniciando nova combinacao para Exercicio X IdAmostra : " . mysqli_error(d::b()) . "<p>SQL: $sql";
                die();
            }

            $sql = "SELECT left(exercicio, 4) AS exercicio, idregistro 
                        FROM seqregistro where exercicio = '" . $exercicio . "'
                        -- and idempresa = " . $_SESSION["SESSAO"]["IDEMPRESA"] . "
                        and idunidade = " . $idunidade;

            $resexercicio = d::b()->query($sql) or die("(3)Falha Pesquisando Exercicio X IdAmostra: " . mysqli_error(d::b()) . "<p>SQL: $sql");

            if (!$resexercicio) {
                echo "Falha 4 Pesquisando Exercicio X IdAmostra : " . mysqli_error(d::b()) . "<p>SQL: $sql";
                die();
            }
            $rowexercicio = mysqli_fetch_array($resexercicio);
        }

        return $rowexercicio["idregistro"];
    }

    public static function gerarEnvioPedido() {
        return "INSERT INTO enviopedido (
                            idamostra,
                            jsonpedido,
                            protocolo,
                            criadoem,
                            criadopor
                            )
                VALUES (
                    '?idamostra?',
                    '?jsonpedido?',
                    '?protocolo?',
                    '?criadoem?',
                    '?criadopor?'
                )";
    }
}