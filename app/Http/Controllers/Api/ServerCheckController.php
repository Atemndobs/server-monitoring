<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ServerCheckService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ServerCheckController extends Controller
{
    public ServerCheckService $serverCheckService;

    /**
     * @param ServerCheckService $serverCheckService
     */
    public function __construct(ServerCheckService $serverCheckService)
    {
        $this->serverCheckService = $serverCheckService;
    }

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
        return response(([
            'status' => 'success',
            'response' => [
                'nest | 3000' => $this->nestStatus(),
                'vue | 8080' => $this->vueStatus(),
                'artisan | 8899' => $this->artisanStatus(),
            ],
        ]));
    }

    public function nestStatus()
    {
        return  $this->serverCheckService->getRunningProcesses('nest');
    }

    public function vueStatus()
    {
        return  $this->serverCheckService->getRunningProcesses('vue');
    }

    public function artisanStatus()
    {
        return  $this->serverCheckService->getRunningProcesses('artisan');
    }
}
