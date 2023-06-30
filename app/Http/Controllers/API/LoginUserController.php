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
    $user = DB::table('users')->where('email', $data['login'])->orwhere('employee_id', $data['login'])->orwhere('sso_unid', $data['login'])
      ->orwhere('phone', $data['login'])->first();


    if (!empty($user)) {
      $email = $user->email;
      $password = $user->password;
    } else {
      $result_array = array(
        'status' => 'fail',
        'msg' => 'Not registered, please signup'
      );

      return response()->json($result_array, 405);
    }

    if (!$user->status) {
      $result_array = array(
        'status' => 'fail',
        'msg' => 'User is Blocked'
      );

      return response()->json($result_array, 405);
    }

    // return $user;




    $check_password = Hash::check($data['password'], $password);
    if (Auth::attempt(['email' => $email, 'password' => request('password')])) {
      // return $user;
      $details = Auth::user();
      $id = $details->id;
      $user = User::with('UserRole')->find($id);
      // return $user;
      $ip = \Request::ip();
      User::where('id', $id)->update(['user_ip' => $ip]);

      $send_api_res = array();
      $send_api_res['user'] = $user;
      $send_api_res['accessToken'] = $user->createToken('Personal Access Token')->accessToken;
      return $send_api_res;
    } else {
      $result_array = array(
        'status' => 'fail',
        'msg' => 'Invalid credentials entered'
      );
      return response()->json($result_array, 200);
    }
  }

  public function logout()
  {
    if (Auth::check()) {

      $token = Auth::user()->token();
      $token->revoke();

      $result_array = array(
        'status' => 'success',
        'msg' => 'Logout successfull'
      );
      return response()->json($result_array, 200);
    } else {
      $result_array = array(
        'status' => 'success',
        'msg' => 'not Logged in'
      );
      return response()->json($result_array, 200);
    }
  }
}
