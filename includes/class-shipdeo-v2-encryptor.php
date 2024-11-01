<?php

class Shipdeo_V2_Encryptor
{
    public static function encrypt($data)
    {
        return base64_encode($data);
    }

    public static function decrypt($data)
    {
        return base64_decode($data);
    }
}
