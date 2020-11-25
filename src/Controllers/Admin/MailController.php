<?php

namespace Azuriom\Plugin\Flyff\Controllers\Admin;

use Illuminate\Http\Request;
use Azuriom\Plugin\Flyff\Models\Mail;
use Azuriom\Plugin\Flyff\Models\User;
use Azuriom\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

class MailController extends Controller
{
    public function index(Request $request)
    {
        return view('flyff::admin.mails.index', [
            'mails' => Mail::paginate(20),
        ]);
    }

}