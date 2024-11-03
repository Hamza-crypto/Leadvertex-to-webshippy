<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index($tab = null, $user_id = null)
    {
        if (session('status') == 'password-updated') {
            $tab = 'password';
        }

        $user = Auth::user();

        return view('pages.profile.index', [
            'user' => $user,
            'tab' => $tab,
        ]);
    }

    public function account(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('profile.index', 'account')->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        Session::flash('success', 'Account information successfully updated.');

        return back();
    }
}