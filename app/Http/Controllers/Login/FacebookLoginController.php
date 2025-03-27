<?php

namespace App\Http\Controllers\Login;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
  

class FacebookLoginController extends Controller
{


    public function redirect(){

        return Socialite::driver('facebook')->redirect();

    }

           
    public function handleCallback(){

        $facebookUser = Socialite::driver('facebook')->stateless()->user();
        $user         = User::where('email', $facebookUser->email)->first();
        
        if(!$user)
        {
            $user = User::create(['name' => $facebookUser->name, 'email' => $facebookUser->email, 'password' => \Hash::make(rand(100000,999999))]);
        }

        Auth::login($user);

        return redirect('/dashboard');
    }

}