<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: application/json');

session_start();




define('USER_ID', '4c310d9d-a9c1-4d98-a433-8a0501166eb1'); // API user id
define('API_KEY', 'ca6b7089bc774bbaa06c9d48b4c922f6'); // API kEY
define('COLLECTION_SUBSCRIPTION_KEY', 'c7818539fa964888b6551916319bdc2c'); // Collection subscription key


if(isset($_SESSION['token'])){

$json =array();

    ///GET
    //var_dump("UUID_GET" .$uid);
    $um = $_SESSION['num'];
    $access_token = $_SESSION['token'];
    $uid = $_SESSION['uid'];

    $endpoint_url = $_SESSION['url'];

    # Parameters
    $data = array(
        "amount" => "1",
        "currency" => "XAF", //default for sandbox
        "externalId" => "$uid", //reference number

        "payer" => array(

            "partyIdType" => "MSISDN",
            "partyId"     => "$um"  //user phone number, these are test numbers)
        ),




    );

    $data_string = json_encode($data);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $endpoint_url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
    //curl_setopt($curl, CURLOPT_TIMEOUT, 50);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    curl_setopt(
        $curl,
        CURLOPT_HTTPHEADER,
        array(

            'Authorization: Bearer ' . $access_token, //optional
            'X-Callback-Url: https://evxpro.net/mtncallback', //optional, not required for sandbox
            'X-Target-Environment: mtncongo',
            'Ocp-Apim-Subscription-Key: ' . COLLECTION_SUBSCRIPTION_KEY,

        )
    );

     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

     $curl_response = curl_exec($curl); //will respond with HTTP 202 Accepted
    // close curl resource to free up system resources
     $exp = explode(",", $curl_response);
    //
     $exlode = explode('"', $exp[8]);
     $status = $exlode[3];
 
     $faille = 'FAILED';

    if($status== 'PENDING'){
        $json[] = array(
            "message" => " transaction en attente...",
            "status"  => "attente"
        );
    }elseif($status == 'FAILED'){
        $json[] = array(
            "message" => "la transaction a echoué",
            "status"  => "echec"
   );
    }else{
        $json[] = array(
            "message" => "vous avez effectué le payement avec success",
            "status"  => "valide"
        );
    }
    curl_close($curl);

    echo json_encode($json);

}
