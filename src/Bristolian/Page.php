<?php

namespace Bristolian;

class Page
{
    private static string $qr_message = "Show this QR code to someone, and they can scan it with the camera in their device";

    static function setQrShareMessage(string $string)
    {
        self::$qr_message = $string;
    }

    static function getQrShareMessage(): string
    {
        return self::$qr_message;
    }
}