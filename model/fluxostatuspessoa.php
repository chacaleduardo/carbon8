<?php

require_once("../inc/php/functions.php");

class EventoResponsavel {
 
    private $conn;
    private $table_name = "fluxostatuspessoa";
 
    public $idpessoa;
    public $idevento;
    public $idempresa;
    public $idobjeto;
    public $tipoobjeto;
    public $idobjetoext;
    public $tipoobjetoext;
    public $status;
    public $idfluxostatuspessoa;
    public $visualizado;
    public $inseridomanualmente;

    public $criadoem;
    public $criadopor;
    public $alteradoem;
    public $alteradopor;
 
    public function __construct() {
        $this->conn = d::b();
    }
 
    function read() {
        
        $sql = "SELECT idfluxostatuspessoa, 
                        idevento, visualizado,
                        idpessoa, idobjeto, tipoobjeto
                FROM
                    " . $this->table_name . "
                ORDER BY
                    idevento";  
 
        $stmt = $this->conn->prepare( $sql );
        $stmt->execute();
 
        return $stmt;
    }

    function create() {

        $this->idevento = htmlspecialchars(strip_tags($this->idevento));
        $this->idobjeto = htmlspecialchars(strip_tags($this->idobjeto));
        $this->tipoobjeto = htmlspecialchars(strip_tags($this->tipoobjeto));
        $this->idempresa = htmlspecialchars(strip_tags($this->idempresa));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->visualizado = htmlspecialchars(strip_tags($this->visualizado));
        $this->idobjetoext = htmlspecialchars(strip_tags($this->idobjetoext));
        $this->tipoobjetoext = htmlspecialchars(strip_tags($this->tipoobjetoext));
        $this->inseridomanualmente = htmlspecialchars(strip_tags($this->inseridomanualmente));
        $this->criadoem = htmlspecialchars(strip_tags($this->criadoem));
        $this->criadopor = htmlspecialchars(strip_tags($this->criadopor));
        $this->alteradoem = htmlspecialchars(strip_tags($this->alteradoem));
        $this->alteradopor = htmlspecialchars(strip_tags($this->alteradopor));

        if (empty($this->tipoobjetoext)) {
            $this->tipoobjetoext = '';
        }

        $intabela = new Insert();
        $intabela->setTable($this->table_name);
        $intabela->idevento = $this->idevento;
        $intabela->idobjeto = $this->idobjeto;
        $intabela->idobjetoext = $this->idobjetoext;
        $intabela->tipoobjeto = $this->tipoobjeto;
        $intabela->tipoobjetoext = $this->tipoobjetoext;
        $intabela->inseridomanualmente = $this->inseridomanualmente;
        $intabela->status = $this->status;
        $intabela->visualizado = $this->visualizado;
       
        $idtabela = $intabela->save();


    }

    function delete() {
 
        $sql = "DELETE FROM " . $this->table_name . " WHERE id = ". $this->idfluxostatuspessoa ."";
         
        $res = d::b()->query($sql);
    }

    function deletePorIDEvento() {
        $sql = "DELETE FROM " . $this->table_name . " WHERE idevento = ". $this->idevento ."";
        $res = d::b()->query($sql);
    }
}
?>