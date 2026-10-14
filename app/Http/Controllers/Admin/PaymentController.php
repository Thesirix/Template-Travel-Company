<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        $payements = Payment::with('user')->get()->all();
        return view('admin.paiements', compact('payements'));
    }
}
