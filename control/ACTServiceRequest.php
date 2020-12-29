<?php
/**
*@package pXP
*@file gen-ACTServiceRequest.php
*@author  (admin)
*@date 27-12-2018 13:10:13
*@description Clase que recibe los parametros enviados por la vista para mandar a la capa de Modelo
*/

class ACTServiceRequest extends ACTbase{    
			
	function listarServiceRequest(){
		$this->objParam->defecto('ordenacion','id_service_request');
		
		$this->objParam->defecto('dir_ordenacion','asc');
		if($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
			$this->objReporte = new Reporte($this->objParam,$this);
			$this->res = $this->objReporte->generarReporteListado('MODServiceRequest','listarServiceRequest');
		} else{
			$this->objFunc=$this->create('MODServiceRequest');
			
			$this->res=$this->objFunc->listarServiceRequest($this->objParam);
		}
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
				
	function insertarServiceRequest(){
		$this->objFunc=$this->create('MODServiceRequest');
		$this->res=$this->objFunc->insertarServiceRequest($this->objParam);	
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
	
	function getServiceStatus(){
		$this->objFunc=$this->create('MODServiceRequest');	
		$this->res=$this->objFunc->getServiceStatus($this->objParam);	
		$this->res->imprimirRespuesta($this->res->generarJson());
	}
						
	function eliminarServiceRequest(){
		$this->objFunc=$this->create('MODServiceRequest');
		$this->res=$this->objFunc->eliminarServiceRequest($this->objParam);
		$this->res->imprimirRespuesta($this->res->generarJson());
	}

    /*{developer: franklin.espinoza, date:15/09/2020, description: "Elimina C31 Sistema Sigep"}*/
    function revertirProcesoSigep(){
        $this->objFunc=$this->create('MODServiceRequest');
        $this->res=$this->objFunc->revertirProcesoSigep($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    /*{developer: franklin.espinoza, date:15/09/2020, description: "Verifica C31 Sistema Sigep"}*/
    function readyProcesoSigep(){ //var_dump('valores', $this->objParam->getParametro('id_service_request'), $this->objParam->getParametro('direction'), $this->objParam->getParametro('momento'));exit;
        $this->objFunc=$this->create('MODServiceRequest');
        $this->res=$this->objFunc->readyProcesoSigep($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }

    /*{developer: franklin.espinoza, date:15/09/2020, description: "Verifica C31 Sistema Sigep"}*/
    function setupSigepProcess(){ //var_dump('valores', $this->objParam->getParametro('id_service_request'), $this->objParam->getParametro('direction'), $this->objParam->getParametro('momento'));exit;
        $this->objFunc=$this->create('MODServiceRequest');
        $this->res=$this->objFunc->setupSigepProcess($this->objParam);
        $this->res->imprimirRespuesta($this->res->generarJson());
    }



    /*{developer: franklin.espinoza, date:15/09/2020, description: "Verifica C31 Sistema Sigep"}*/
    function procesarEstadoRevertidoC31(){

        $response_status = true;
        while($response_status) {

            $this->objFunc=$this->create('MODSigepServiceRequest');
            $this->res=$this->objFunc->procesarServices($this->objParam);

            $response = $this->res->datos;


            $next = $response['end_process'];
            if( $next ){
                $response_status = $next;
            }else{
                $response_status = false;
            }

        }

        $this->res->imprimirRespuesta($this->res->generarJson());
    }

}

?>