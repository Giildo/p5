<?php

namespace App\various;

trait appHash
{
    /**
     * @param string $text
     * @return string
     */
    public function appHash(string $text): string
    {
        $code1 = strlen($text) + 15;
        $code2 = (int)(($code1 + 23) * ($code1 / 5));

        $code3 = strlen($text) * 2;
        $code4 = (int)((strlen($code3 . $text) * 17) / 3);

        $text_hash1 = md5($code1 . $text . $code2);
        $text_hash2 = md5($code3 . $text . $code4);
        $text_hash3 = md5($code4 . $text . $code1);
        $text_hash4 = md5($code2 . $text . $code3);

        return $text_hash3 . $text_hash2 . $text_hash4 . $text_hash1;
    }
}
