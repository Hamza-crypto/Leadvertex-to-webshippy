<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('pages.users.index', compact('users'));
    }

    public function create()
    {
        $users = User::all();
        return view('pages.users.add', compact('users'));
    }

    public function store(UserRequest $request)
    {
        User::create(
            [
                'name' => $request->name,
                'email_verified_at' => now(),
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'role' => $request->role,
            ]
        );

        Session::flash('success', 'User created successfully.');
        return redirect()->route('users.create');
    }

    public function edit(User $user)
    {
        return view(
            'pages.users.edit',
            [
                'user' => $user,
                'tab' => 'account',
            ]
        );
    }

    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
           'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        Session::flash('success', __('Account information successfully updated.'));
        return back();
    }
    public function password_update(Request $request, User $user)
    {
        $tab = 'password';
        $this->validate($request, [
            'password' => 'required|confirmed',
        ]);
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        Session::flash('success', 'Password updated successfully.');
        return back();

    }

    public function destroy(User $user)
    {
        $user->delete();
        Session::flash('success', 'User deleted successfully.');
        return redirect()->route('users.index');
    }

    public function update_wallet_info(Request $request, User $user)
    {
        if ($request->usdt != null) {
            $user->metas()->updateOrCreate(
                ['meta_key' => 'usdt_address'],
                ['meta_value' => $request->usdt]
            );
        }

        if ($request->btc != null) {
            $user->metas()->updateOrCreate(
                ['meta_key' => 'btc_address'],
                ['meta_value' => $request->btc]
            );
        }

        if ($request->trc != null) {
            $user->metas()->updateOrCreate(
                ['meta_key' => 'trc_address'],
                ['meta_value' => $request->trc]
            );
        }

        if (Auth()->user()->role == 'admin') {
            $user->metas()->updateOrCreate(
                ['meta_key' => 'rate'],
                ['meta_value' => $request->rate]
            );
        } else {
            $user->metas()->updateOrCreate(
                ['meta_key' => 'rate'],
                ['meta_value' => $user->rate]
            );
        }

        Session::flash('account', 'Wallet info updated successfully.');
        return redirect()->back();
    }

    public function update_gateway(Request $request, User $user)
    {

        if (Auth()->user()->role == 'admin') {

            $user->metas()->updateOrCreate(
                ['meta_key' => 'gateway'],
                ['meta_value' => $request->gateway_id]
            );
        }

        Session::flash('account', 'Gateway updated successfully.');
        return redirect()->back();
    }

    public function update_parent(Request $request, User $user)
    {

        if (Auth()->user()->role == 'admin') {

            if ($request->parent == 0) {
                $parent_id = null;
            } else {
                $parent_id = $request->parent;
            }
            $user->updateOrCreate(
                ['id' => $user->id],
                ['parent_id' => $parent_id]
            );
        }

        Session::flash('account', 'Successfully assigned.');
        return redirect()->back();
    }

    public function update_order_category(Request $request, User $user)
    {
        $user->metas()->updateOrCreate(
            ['meta_key' => 'order_category'],
            ['meta_value' => $request->category]
        );

        Session::flash('account', 'Order Category updated successfully.');
        return redirect()->back();
    }

    public function update_payable_section(Request $request, User $user)
    {
        $status = 0;

        if ($request->has('status')) {
            $status = 1;
        }
        $user->metas()->updateOrCreate(
            ['meta_key' => 'payable_visible'],
            ['meta_value' => $status]
        );

        Session::flash('account', 'Successfully updated.');
        return redirect()->back();
    }
}