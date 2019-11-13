<?php

namespace ClickerVolt;

class UUID
{

    static function alphaNum($maxLength = 16)
    {

        $uid = uniqid();
        $uidLength = strlen($uid);

        $suffix = '';
        if ($maxLength > 0) {
            $prefixLength = $maxLength - $uidLength;
            for ($x = 0; $x < $prefixLength; $x++) {
                $suffix .= substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 1);
            }

            $uid .= $suffix;
        }

        return substr($uid, 0, $maxLength);
    }
}
