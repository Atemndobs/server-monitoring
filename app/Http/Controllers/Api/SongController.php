<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SongController
{
    public function getSongs()
    {
        $res = Http::post("127.0.0.1:8899/api/ping", [
            'json' => [
                'name' => 'foo',
                'age' => 'bar',
            ],
        ]);
        $response = json_encode([
            'status' => $res->status(),
            'response' => $res->body(),
        ]);

        return response($response);
    }
}
