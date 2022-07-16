<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ServerCheckService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ServerCheckController extends Controller
{
    public function appHealthCheck()
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

    public function serverHealthcheck()
    {
        $service = new ServerCheckService();

        $processes = $service->checkServerProcesses();
        $processes = explode("\n", $processes);

        return response(([
            'status' => 'success',
            'response' => $processes,
        ]));
    }

    public function nestStatus()
    {

    }

    public function vueStatus()
    {

    }
}
