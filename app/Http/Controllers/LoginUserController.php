<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Hash;

class LoginUserController extends Controller
{

    public function signin(Request $request){
        // print_r($request->all());
        // exit;
        $data=$request->all();
        // return $data;
      
        // $validator = Validator::make($request->all(), [
        //     'login' => 'required',
        //     'password' => 'required|max:7|min:5',
        // ]);
  
        // if (preg_match("/([%\$#{}!()+\=\-\*\'\"\/\\\]+)/",request('login'))) {
        //   $result_array = array(
        //     'status' => 'fail',
        //     'msg' => 'Invalid characters given'
        //   );
  
        //   return response()->json($result_array, 405);
        // }
  
        // if ($validator->fails()) {
        //   $errors = $validator->errors();
  
  
        //   if ($errors->first('password')) {
        //     return response()->json(['status' => 'error', 'msg'=>$errors->first('password')], 400);
        //   }
          
        //   return response()->json(['error'=>$validator->errors()], 400);
        // }
  
        $user = array();
        $user = DB::table('users')->where('email',$data['login'])
        ->orwhere('phone',$data['login'])->get();

    //    return $user;
     
  
        if (!$user->isEmpty()) {
          $email = $user[0]->email;
          $password=$user[0]->password;
        } else {
          $result_array = array(
            'status' => 'fail',
            'msg' => 'Not registered, please signup'
          );
  
          return response()->json($result_array, 405);
        }



  
//    return $email;
  
        // return response()->json(['success' => $email], $this-> successStatus);
     
        //    $password_in=Hash::make($data['password'] );
         
           $check_password= Hash::check($data['password'], $password);
           if(Auth::attempt(['email' => $email, 'password' => request('password')])){
            // return $user;
            $details = Auth::user();
            $id= $details->id;
            $user = User::find($id);
           $token=$user->createToken('Personal Access Token')-> accessToken;
           return $token;
            // $user->token()->revoke();
            // $token = $user->createToken('access_token')->accessToken;
            // Creating a token with scopes...
            // $token = $user->createToken('access_token',["view-user"])->accessToken;
            // return $token;
            
            $user = Auth::user();
       
            $details = Auth::user();
            $id= $details->id;
            $user = User::find($id);
            return view('home');
            // $token =$user->createToken('access_token')->accessToken;



        }

      if($check_password)
      {
        // return $success;

        $user = User::where("email", $email)->first();
        // return $user;
        Auth::login($user, true);
        if (Auth::check()) {
            $details = Auth::user();
            $id= $details->id;
            $user = User::find($id);
            // return $user;
            // $token = $user->createToken('access_token',["view-user"])->accessToken;
        // $token = $user->createToken('access_token')->accessToken;
        $token = $user->createToken('access_token')->accessToken;
        // $token = $user->createToken('access_token')->accessToken;

            return $token;

            $user = Auth::user();
            $result_array = array(
                'status' => 'success',
                'msg' => 'Login successful',
                'token' =>  $success['token'],
                'user_details' => $user
              );
      
              return response()->json($result_array, 200);
        }
      }

        else{
          $result_array = array(
            'status' => 'fail',
            'msg' => 'Invalid credentials entered'
          );
          return response()->json($result_array, 200);
        }
      }
}

?>