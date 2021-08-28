<?php

//put your API Key and Secret in these two variables.
define('USER_ID', '4c310d9d-a9c1-4d98-a433-8a0501166eb1'); // API user id
define('API_KEY', 'ca6b7089bc774bbaa06c9d48b4c922f6'); // API kEY
define('COLLECTION_SUBSCRIPTION_KEY', 'c7818539fa964888b6551916319bdc2c'); // Collection subscription key

//Test here
//echo get_accesstoken();
//echo requestToPay();

//When called this function will request an Access Token

$credentials = base64_encode(USER_ID . ':' . API_KEY);

function get_accesstoken()
{

    $credentials = base64_encode(USER_ID . ':' . API_KEY);

    $ch = curl_init("https://proxy.momoapi.mtn.com/collection/token/");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json',
            'Ocp-Apim-Subscription-Key: ' . COLLECTION_SUBSCRIPTION_KEY
        )
    );

    $response = curl_exec($ch);
    $response = json_decode($response);
    var_dump($response);

    $access_token = $response->access_token;
    if (!$access_token) {
        throw new Exception("Invalid access token generated");
        return FALSE;
    }


    return $access_token;
}


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
        'Authorization: Basic NGMzMTBkOWQtYTljMS00ZDk4LWE0MzMtOGEwNTAxMTY2ZWIxOmNhNmI3MDg5YmM3NzRiYmFhMDZjOWQ0OGI0YzkyMmY2'
    ),
));

$response = curl_exec($curl);

var_dump($response);



// request payment from customer
function requestToPay()
{

    $access_token = get_accesstoken();
    $endpoint_url = 'https://ericssonbasicapi1.azure-api.net/collection/v1_0/requesttopay';

    # Parameters
    $data = array(
        "amount" => "1",
        "currency" => "XAF", //default for sandbox
        "externalId" => "123456", //reference number

        "payer" => array(

            "partyIdType" => "MSISDN",
            "partyId"     => "069832678"  //user phone number, these are test numbers)
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
            'Authorization: Bearer ' . $access_token, //optional
            //'X-Callback-Url: https://anzilasandbox.ngrok.io', //optional, not required for sandbox
            'X-Reference-Id: ' . get_uuid(),
            'X-Target-Environment: sandbox',
            'Ocp-Apim-Subscription-Key: ' . COLLECTION_SUBSCRIPTION_KEY,

        )
    );

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $curl_response = curl_exec($curl); //will respond with HTTP 202 Accepted
    // close curl resource to free up system resources

    curl_close($curl);
}


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
