<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;

class QuoteController extends Controller
{

    public function index(Request $request){
        $devises = QuoteRequest::get();

        return view('agent.devis.list', compact('devises'));



    }

}
