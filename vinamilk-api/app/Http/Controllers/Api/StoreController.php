<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        // Return all stores so the frontend map can plot them
        $stores = Store::all();
        
        return response()->json([
            'data' => $stores
        ]);
    }
}
