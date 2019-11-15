<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZodiacSign extends Model
{
    /**
     * 星座編號對照表
     *
     * ＠var array
     */
    public static $zodiacSignMap = [
        0 => '牡羊座',
        1 => '金牛座',
        2 => '雙子座',
        3 => '巨蟹座',
        4 => '獅子座',
        5 => '處女座',
        6 => '天平座',
        7 => '天蠍座',
        8 => '射手座',
        9 => '摩羯座',
        10 => '水瓶座',
        11 => '雙魚座'
    ];
}
