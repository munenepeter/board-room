<?php

namespace BoardRoom\Controllers;

use BoardRoom\Core\Mantle\Auth;
use BoardRoom\Core\Mantle\Request;
use BoardRoom\Core\Mantle\Session;

class AuthController extends Controller
{
    public function index()
    {
        if (Session::get('loggedIn')) {

            return redirect('/');

        }
        return view('login');
    }

    public function login()
    {
        $this->validateLoginRequest();

        Auth::login($this->request()->form('email'), $this->request()->form('password'));

        if (Session::get('loggedIn')) {

            return redirect('/');

        } elseif (Session::get('_msg_error') || Session::get('_msg_error') !== '') {

            return view('login', [
                'errors' => Session::get('_msg_error')
            ]);
        }
    }

    public function signout()
    {
        if(request('_logout') !== md5(Session::get('email'))){
            logger("Warning","Logout: Someone is trying to spoof logout for". Session::get('email'));
            return redirect('/');
        }
        Auth::logout(Session::get('email'));
        return redirect('/');
    }

    private function validateLoginRequest()
    {
        $this->request()->validate($_POST, [
            'email' => 'required',
            'password' => 'required'
        ]);
    }
}