<?php

namespace App\Http\Controllers;

use App\ZodiacSign;
use Illuminate\Http\Request;

class ZodiacController extends Controller
{
    /**
     * Show the zodiac info.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $zodiacSigns = ZodiacSign::all();
        
        return response()->json($zodiacSigns);
    }
}
