<?php
ini_set('display_errors','1');
require_once __DIR__ . '/../vendor/autoload.php';


use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jose\Component\Signature\Serializer\JSONFlattenedSerializer;
/*************************************************
 *
 *
 * OBTENER ACCESS TOKEN
 *
 **************************************************/


$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "http://sigeppre-wl12.sigma.gob.bo/rsseguridad/apiseg/token?grant_type=refresh_token&client_id=0&redirect_uri=%2Fmodulo%2Fapiseg%2Fredirect&client_secret=0&refresh_token=SCY485838000:dRR2cnqs3MOdIUki5riGmN080DJ9HXd0Z2vPN4bZw1fTrOv8mT2vKFEczIP4fY009ZmbPpqRrEGZbn1KGhH5jzYNbRNyc9VOX430",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    //CURLOPT_MAXREDIRS => 10,
    //CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: application/x-www-form-urlencoded"
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if (isset($_POST['action'])) {

    if ($_POST['action']=='register') {
        $nroPreventivo = $_POST['nroPreventivo'];
        $nroCompromiso = $_POST['nroCompromiso'];
        $nroDevengado= $_POST['nroDevengado'];
        $nroPago= $_POST['nroPago'];
        $fecha = "13/05/2019";

    }
}

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    /*************************************************
     *
     *
     * HACER PETICION GET
     *
     **************************************************/
    $token_response = json_decode($response);
    var_dump($token_response);
    $access_token = $token_response->{'access_token'};
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://sigeppre-wl12.sigma.gob.bo/rsbeneficiarios/api/v1/beneficiarios/natural?numeroDocumento=&primerApellido=ZABALA&segundoApellido=MITA&nombres=ROMER&fechaNacimiento=19-03-1984",
        //CURLOPT_URL => "http://sigeppre-wl12.sigma.gob.bo/rsbeneficiarios/api/v1/beneficiarios/juridico?numeroDocumento=190980028",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "Authorization: bearer " . $access_token,
            "Cache-Control: no-cache",
            "Postman-Token: 011d15eb-f4ff-48db-85a6-1b380958342b"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        /*************************************************
         *
         *
         * DESSERIALIZAR MENSAJE
         *
         **************************************************/
        // The algorithm manager with the HS256 algorithm.
        $algorithmManager = AlgorithmManager::create([
            new RS512()
        ]);

        // We instantiate our JWS Verifier.
        $jwsVerifier = new JWSVerifier(
            $algorithmManager
        );
        $jwk = JWKFactory::createFromKeyFile(
            '../boa.key', // The filename
            null
        );

        // The JSON Converter.
        $jsonConverter = new StandardConverter();
        //var_dump('respuesta:', $response);

        $token = $response;

        $usus=json_decode($token, true);
        var_dump('BENEFICIARIO:',$usus['data']['beneficiario']);

        echo '<pre>' . var_export(json_decode($token), true) . '</pre>';

        /*$serializer = new JSONFlattenedSerializer($jsonConverter);

        // We try to load the token.
        $jws = $serializer->unserialize($token);
        echo $jws;
        echo '<pre>' . var_export(json_decode($jws->getPayload()), true) . '</pre>';
        //var_dump($jws);
        $isVerified = $jwsVerifier->verifyWithKey($jws, $jwk, 0);*/
    }
}