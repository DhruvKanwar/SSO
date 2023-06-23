<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Hash;
use Validator;

class LoginUserController extends Controller
{
  //
  public function signin(Request $request)
  {
    // print_r($request->all());
    // exit;
    $data = $request->all();


    $validator = Validator::make($request->all(), [
      'login' => 'required',
      'password' => 'required|max:8|min:8',
    ]);

    if (preg_match("/([%\$#{}!()+\=\-\*\'\"\/\\\]+)/", request('login'))) {
      $result_array = array(
        'status' => 'fail',
        'msg' => 'Invalid characters given'
      );

      return response()->json($result_array, 405);
    }

    if ($validator->fails()) {
      $errors = $validator->errors();

      if ($errors->first('login')) {
        return response()->json(['status' => 'error', 'msg' => $errors->first('password')], 400);
      }
      if ($errors->first('password')) {
        return response()->json(['status' => 'error', 'msg' => $errors->first('password')], 400);
      }

      return response()->json(['error' => $validator->errors()], 400);
    }

    $user = array();
    $user = DB::table('users')->where('email', $data['login'])
      ->orwhere('phone', $data['login'])->get();

    //    return $user;


    if (!$user->isEmpty()) {
      $email = $user[0]->email;
      $password = $user[0]->password;
    } else {
      $result_array = array(
        'status' => 'fail',
        'msg' => 'Not registered, please signup'
      );

      return response()->json($result_array, 405);
    }



    $check_password = Hash::check($data['password'], $password);
    if (Auth::attempt(['email' => $email, 'password' => request('password')])) {
      // return $user;
      $details = Auth::user();
      $id = $details->id;
      $user = User::find($id);
      $token['accessToken'] = $user->createToken('Personal Access Token')->accessToken;
      return $token;
    } else {
      $result_array = array(
        'status' => 'fail',
        'msg' => 'Invalid credentials entered'
      );
      return response()->json($result_array, 200);
    }
  }
}
