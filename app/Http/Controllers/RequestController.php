<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Support\Facades\Http;


class RequestController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private static function makeRequest()
    {
        return Http::get(env('API_PROVA'));
    }

    public static function getVoosJson(){
        return self::makeRequest()->json();
    }
}


