<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApiDocumentationService;
use Illuminate\Http\Request;

class ApiDocumentationController extends Controller
{
    public function index(Request $request)
    {
        $service = new ApiDocumentationService();
        $documentation = $service->generate();

        return response()->json($documentation);
    }
}
