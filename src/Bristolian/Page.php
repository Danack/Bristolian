<?php

namespace Bristolian;

class Page
{
    private static string $qr_message = "Show this QR code to someone, and they can scan it with the camera in their device";

    public static function setQrShareMessage(string $string): void
    {
        self::$qr_message = $string;
    }

    public static function getQrShareMessage(): string
    {
        return self::$qr_message;
    }
}
