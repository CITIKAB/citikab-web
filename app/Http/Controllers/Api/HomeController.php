<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller as BaseController;
use App\Models\User;
use Auth;
use JWTAuth;

class HomeController extends BaseController
{
	public function test() {
    	return User::get();
    }
}
