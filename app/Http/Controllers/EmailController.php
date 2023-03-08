<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordEmail;

class EmailController extends Controller {

    public function sendResetPasswordEmail()
    {
        Mail::to('g.castro.ojt.clarkoutsourcing@gmail.com')->send(new ResetPasswordEmail());
    }
}
