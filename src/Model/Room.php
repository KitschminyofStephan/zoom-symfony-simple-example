<?php

namespace App\Model;

use DateTime;

class Room
{
    private int $meetingNumber;

    public function __construct(string $meetingNumber)
    {
        $this->meetingNumber = $meetingNumber;
    }

    /**
     * Method
     */

    public function base64url_encode($str) {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    public function generateJWTSignature($apiKey, $apiSecret, $meetingNumber, $role): string
    {
        date_default_timezone_set("UTC");
        $time = time() * 1000 - 30000; //time in ms
        $data = base64_encode($apiKey . $meetingNumber . $time . $role);
        $hash = hash_hmac('sha256', $data, $apiSecret, true);
        $_sig = $apiKey . "." . $meetingNumber . "." . $time . "." . $role . "." . base64_encode($hash);

        //return signature, url safe base64 encoded
        return $this->base64url_encode($_sig);
    }

    public function generateSDKSignature($sdkKey, $sdkSecret, $meetingNumber, $role): string
    {
        $date = new DateTime();

        $iat = round(($date->getTimestamp() - 30000) / 1000);
        $exp = $iat + 60 * 60 * 2;

        $headers = array('alg'=>'HS256', 'typ'=>'JWT');
        $payload = array('sdkKey'=>$sdkKey, 'mn'=>$meetingNumber, 'role'=>$role, 'iat'=>$iat, 'exp'=>$exp, 'appKey'=>$sdkKey, 'tokenExp'=>$exp);

        $headers_encoded = $this->base64url_encode(json_encode($headers));
        $payload_encoded = $this->base64url_encode(json_encode($payload));

        $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $sdkSecret, true);
        $signature_encoded = $this->base64url_encode($signature);

        $jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
        return $jwt;
    }


    /**
     * Get & Set
     */
    public function getMeetingNumber(): string
    {
        return $this->meetingNumber;
    }

    public function setMeetingNumber(string $meetingNumber): void
    {
        $this->meetingNumber = $roomName;
    }
}