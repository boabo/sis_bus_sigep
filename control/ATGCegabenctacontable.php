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
    CURLOPT_URL => "https://sigep.sigma.gob.bo/rsseguridad/apiseg/token?grant_type=refresh_token&client_id=0&redirect_uri=%2Fmodulo%2Fapiseg%2Fredirect&client_secret=0&refresh_token=ACM372006900:FIjmQpcjzzYNEjOD61rsQ8eYnlediCY9wDMOTvckiFdU1um1XeHXp8SWkaUkosISNQ7DP9HXfAipuRsXa7XVLe2CmWCwcPOL03BB",
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
     * HACER PETICION GET
     *
     **************************************************/
    $token_response = json_decode($response);
    $access_token = $token_response->{'access_token'};
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://sigep.sigma.gob.bo/ejecucion-gasto/api/cola/ega_cuentas_contables/" . $_GET["cola_id"],
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

        $token = $response;

        echo $token;

        $serializer = new JSONFlattenedSerializer($jsonConverter);

        // We try to load the token.
        $jws = $serializer->unserialize($token);
        echo '<pre>' . var_export(json_decode($jws->getPayload()), true) . '</pre>';
        //var_dump($jws);
        $isVerified = $jwsVerifier->verifyWithKey($jws, $jwk, 0);
    }
}