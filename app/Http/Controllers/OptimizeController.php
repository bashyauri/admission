<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class OptimizeController extends Controller
{
    /**
     * Run the Artisan optimize command.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function run(Request $request)
    {
        // Optionally, you can add further authorization here
        Artisan::call('optimize');
        return response()->json(['status' => 'optimized']);
    }
}
