<?php

namespace App\Model;

use DateTime;

class Room
{
    private $meetingId = null;
    private $meetingName = null;
    private $password = null;

    /**
     * Get & Set
     */
    public function getMeetingId()
    {
        return $this->meetingId;
    }

    public function setMeetingId(int $meetingId): void
    {
        $this->meetingId = $meetingId;
    }

    public function getMeetingName()
    {
        return $this->meetingName;
    }

    public function setMeetingName(string $meetingName): void
    {
        $this->meetingName = $meetingName;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * Method
     */

    private function base64url_encode($str) {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    public function generateJWTSignature($apiKey, $apiSecret, $meetingId, $role): string
    {
        date_default_timezone_set("UTC");
        $time = time() * 1000 - 30000; //time in ms
        $data = base64_encode($apiKey . $meetingId . $time . $role);
        $hash = hash_hmac('sha256', $data, $apiSecret, true);
        $_sig = $apiKey . "." . $meetingId . "." . $time . "." . $role . "." . base64_encode($hash);

        //return signature, url safe base64 encoded
        return $this->base64url_encode($_sig);
    }

    public function generateSDKSignature($sdkKey, $sdkSecret, $meetingId, $role): string
    {
        date_default_timezone_set("UTC");
        $time = time() * 1000 - 30000; //time in ms

        $iat = round(($time - 30000) / 1000);
        $exp = $iat + 60 * 60 * 2;

        $headers = array('alg'=>'HS256', 'typ'=>'JWT');
        $payload = array('sdkKey'=>$sdkKey, 'mn'=>$meetingId, 'role'=>$role, 'iat'=>$iat, 'exp'=>$exp, 'appKey'=>$sdkKey, 'tokenExp'=>$exp);

        $headers_encoded = $this->base64url_encode(json_encode($headers));
        $payload_encoded = $this->base64url_encode(json_encode($payload));

        $signature = hash_hmac('SHA256', "$headers_encoded.$payload_encoded", $sdkSecret, true);
        $signature_encoded = $this->base64url_encode($signature);

        $jwt = $headers_encoded . "." . $payload_encoded . "." . $signature_encoded;
        return $jwt;
    }

    private function base64UrlEncode2($text) //TODO try to use same baseUrlEncode which SDKSignature and JWT generator
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }
    public function generateJWTtoken($apiKey, $apiSecret) // the difference with SDKSignature come from the exp value 
    {
        $now = new DateTime();

        // Create the token header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        // Create the token payload
        $payload = json_encode([
            'iss' => $apiKey,
            'exp' => $now->getTimestamp() + 5000,
        ]);

        // Encode Haeder
        $base64UrlHeader = $this->base64UrlEncode2($header);

        // Encode Payload
        $base64UrlPayload = $this->base64UrlEncode2($payload);

        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $apiSecret, true);

        // Encode Signature to Base64Url String
        $base64UrlSignature = $this->base64UrlEncode2($signature);

        // Create JWT
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        return $jwt;
    }
}