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


/*****************************************************
 *
 *
 * SERIALIZAR MENSAJE
 *
 *
 * ********************************************************/
// The algorithm manager with the HS256 algorithm.
$algorithmManager = AlgorithmManager::create([
    new RS512(),
]);


$jwk = JWKFactory::createFromKeyFile(
    '../boa.key', // The filename
    null,                   // Secret if the key is encrypted
    [
        'use' => 'sig',         // Additional parameters
        'kid' => 'boaws'
    ]
);

$jsonConverter = new StandardConverter();

// We instantiate our JWS Builder.
$jwsBuilder = new JWSBuilder(
    $jsonConverter,
    $algorithmManager
);

// The payload we want to sign. The payload MUST be a string hence we use our JSON Converter.
$payload = $jsonConverter->encode([
    'gestion' => 2019,
    'idEntidad' => 494,
    'idDa' => 15,
    'nroPreventivo' => 1,
    'nroCompromiso' => 1,
    'nroDevengado' => 1,
    'nroPago' => 0,
    'nroSecuencia' => 0,
    'nroDevengadoSip' => 0,
    'tipoFormulario' => "C",
    'tipoDocumento' => "O",
    'tipoEjecucion' => "N",
    'preventivo' => "S",
    'compromiso' => "S",
    'devengado' => "S",
    'pago' => "N",
    'devengadoSip' => "N",
    'pagoSip' => "N",
    "regularizacion"=> "N",
    "fechaElaboracion"=> "02/12/2019",
    "claseGastoCip"=> 1,
    "claseGastoSip"=> null,
    "idCatpry"=> null,
    "sigade"=> null,
    "otfin"=> null,
    "resumenOperacion"=> "Publicacion forma manual planillas diciembre 2019",
    "moneda"=> 69,
    "fechaTipoCambio"=> "02/10/2019",
    "compraVenta"=> "C",
    "totalAutorizadoMo"=> 1000,
    "totalRetencionesMo"=> 200,
    "totalMultasMo"=> 0,
    "liquidoPagableMo"=> 800,
]);

$jws = $jwsBuilder
    ->create()                               // We want to create a new JWS
    ->withPayload($payload)                  // We set the payload
    ->addSignature($jwk, ['alg' => 'RS512'],['kid' => 'boaws']) // We add a signature with a simple protected header
    ->build();


$serializer = new JSONFlattenedSerializer($jsonConverter); // The serializer

$token = $serializer->serialize($jws, 0); // We serialize the signature at index 0 (we only have one signature).


/*************************************************
 *
 *
 * OBTENER ACCESS TOKEN
 *
 **************************************************/


$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://sigeppruebas-wl12.sigma.gob.bo/rsseguridad/apiseg/token?grant_type=refresh_token&client_id=0&redirect_uri=%2Fmodulo%2Fapiseg%2Fredirect&client_secret=0&refresh_token=CSO313059200:vmIGOk050ZEbb8afnwXRUad3jFoKotQjyl9aArcMf9v5OMHKPLkuY4YgtMysm0MUaqZD9feeUgVbm6rCiZk0yvvVcsnQW5GlNqr6",
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

if ($err) {
    echo "cURL Error #:" . $err;
} else {
    /*************************************************
     *
     *
     * HACER PETICION POST
     *
     **************************************************/
    $token_response = json_decode($response);
    $access_token = $token_response->{'access_token'};
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://sigeppruebas-wl12.sigma.gob.bo/ejecucion-gasto/api/v1/egadocumento",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $token,
        CURLOPT_HTTPHEADER => array(
            "authorization: bearer " . $access_token,
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: a3949f68-6846-29c1-0219-282f88c61cbb"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    $http_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
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
        
        $token = $response;
		echo $token;
		var_dump($http_code);
        $serializer = new JSONFlattenedSerializer($jsonConverter);

        // We try to load the token.
        $jws = $serializer->unserialize($token);
        echo '<pre>' . var_export(json_decode($jws->getPayload()), true) . '</pre>';
//        var_dump($jws);
        $isVerified = $jwsVerifier->verifyWithKey($jws, $jwk, 0);
    }
}