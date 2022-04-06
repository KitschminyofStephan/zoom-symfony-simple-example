<?php

namespace App\Model;

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

    public function generateSignature($apiKey, $apiSecret, $meetingNumber, $role): string
    {
        date_default_timezone_set("UTC");
        $time = time() * 1000 - 30000; //time in ms
        $data = base64_encode($apiKey . $meetingNumber . $time . $role);
        $hash = hash_hmac('sha256', $data, $apiSecret, true);
        $_sig = $apiKey . "." . $meetingNumber . "." . $time . "." . $role . "." . base64_encode($hash);

        //return signature, url safe base64 encoded
        return rtrim(strtr(base64_encode($_sig), '+/', '-_'), '=');
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