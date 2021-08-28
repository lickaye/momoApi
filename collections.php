<?php
session_start();
//variable api momo
define('USER_ID', '4c310d9d-a9c1-4d98-a433-8a0501166eb1'); // API user id
define('API_KEY', 'ca6b7089bc774bbaa06c9d48b4c922f6'); // API kEY
define('COLLECTION_SUBSCRIPTION_KEY', 'c7818539fa964888b6551916319bdc2c'); // Collection subscription key

//Test here
//echo get_accesstoken();
//echo requestToPay();




//function de la creation du token
function get_accesstoken()
{

    $credentials = base64_encode(USER_ID . ':' . API_KEY);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://proxy.momoapi.mtn.com/collection/token/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Ocp-Apim-Subscription-Key:' . COLLECTION_SUBSCRIPTION_KEY,
            'Content-Length: 0',
            'Authorization: Basic ' . $credentials
        ),
    ));
    $response = curl_exec($curl);
    $response = json_decode($response);


    $access_token = $response->access_token;

    if (!$access_token) {
        throw new Exception("Invalid access token generated");
        return FALSE;
    }


    return $access_token;
}
//var_dump(get_uuid());
/////////////////////////////////////////////
//var_dump(get_accesstoken());



// request payment from customer || le payement
function requestToPay()
{

    /********************************** */
    ///POST  
    $uid = get_uuid();
    //var_dump("UUID_POST".$uid);


    $numero = isset($_POST['numero']) ? $_POST['numero'] : null;
    $prix = isset($_POST['prix']) ? $_POST['prix'] : null;
    $um = $numero;
    $amount = $prix;

    //var_dump($amount);
    $access_token_post = get_accesstoken();
    //var_dump("POST_TOKEN".$access_token_post);

    //echo '<br>';
    $endpoint_url = 'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay';

    # Parameters
    $data = array(
        "amount" => "$amount",
        "currency" => "XAF", //default for sandbox
        "externalId" =>"$uid", //reference number

        "payer" => array(

            "partyIdType" => "MSISDN",
            "partyId"     => "$um"  //user phone number, these are test numbers)
        ),

        "payerMessage" => "Payment Request",
        "payeeNote" => "Please confirm payment"


    );

    $data_string = json_encode($data);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $endpoint_url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    //curl_setopt($curl, CURLOPT_TIMEOUT, 50);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    curl_setopt(
        $curl,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',  //optional
            'Authorization: Bearer '.$access_token_post, //optional
            'X-Callback-Url: https://evxpro.net/mtncallback', //optional, not required for sandbox
            'X-Reference-Id: '.$uid,
            'X-Target-Environment: mtncongo',
            'Ocp-Apim-Subscription-Key: ' . COLLECTION_SUBSCRIPTION_KEY,

        )
    );

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $curl_response = curl_exec($curl); //will respond with HTTP 202 Accepted
    // close curl resource to free up system resources
    //var_dump($curl);
    curl_close($curl);


    ///GET
    //var_dump("UUID_GET" .$uid);

  
    
    $access_token = $access_token_post;
 

   // var_dump("GET_TOKEN".$access_token);
    $endpoint_url = 'https://proxy.momoapi.mtn.com/collection/v1_0/requesttopay/'.$uid;


 

    # Parameters
    $data = array(
        "amount" => "$amount",
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
            
            'Authorization: Bearer '.$access_token, //optional
            'X-Callback-Url: https://evxpro.net/mtncallback', //optional, not required for sandbox
            'X-Target-Environment: mtncongo',
            'Ocp-Apim-Subscription-Key: ' . COLLECTION_SUBSCRIPTION_KEY,

        )
    );



    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

 

    $curl_response = curl_exec($curl); //will respond with HTTP 202 Accepted
    // close curl resource to free up system resources
    //var_dump($curl_response);
    curl_close($curl);

    ///SESSION VERIFICATION 
    $_SESSION['num'] = $um;
    $_SESSION['token'] = $access_token;
    $_SESSION['url'] = $endpoint_url;
    $_SESSION['uid'] = $uid;

    header('location:verification.php');
    
}


$access = requestToPay();
//ar_dump($access);

function get_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}


