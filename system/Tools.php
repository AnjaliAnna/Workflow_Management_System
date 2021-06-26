<?php
class Tools
{
    public static function getFromJson($jsonStr,$propName)
    {
        try
        {
            return json_decode($jsonStr,true)[$propName];
        }
        catch(Exception $eX)
        {

        }
    }
}