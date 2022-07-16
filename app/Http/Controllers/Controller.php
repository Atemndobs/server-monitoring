<?php

namespace App\Http\Controllers;

use ForestAdmin\LaravelForestAdmin\Http\Controllers\ForestController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends ForestController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
