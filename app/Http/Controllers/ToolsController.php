<?php

namespace App\Http\Controllers;

class ToolsController extends Controller
{
    public function smsCharacterCounter()
    {
        return view('tools.sms-character-counter');
    }
}
