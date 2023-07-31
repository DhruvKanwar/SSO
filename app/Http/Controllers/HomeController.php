<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Passport\Token;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function info()
    {
        return "hello";
    }

    public function differentAccount(Request $request)
    {
        Auth::logout();
        Session::put('url.intended', $request->current_url);
        return redirect("login");
    }

    public function resetAuth(array $guards = null)
    {
        $guards = $guards ?: array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            $guard = $this->app['auth']->guard($guard);

            if ($guard instanceof \Illuminate\Auth\SessionGuard) {
                $guard->logout();
            }
        }

        $protectedProperty = new \ReflectionProperty($this->app['auth'], 'guards');
        $protectedProperty->setAccessible(true);
        $protectedProperty->setValue($this->app['auth'], []);
    }
    public static function createAccessToken()
    {
        $details = Auth::user();
        $id = $details->id;
        $user = User::find($id);
        // return $user;
        // $user->token()->revoke();
        // $token = $user->createToken('access_token')->accessToken;
        // Creating a token with scopes...
        $token = $user->createToken('access_token', ["view-user"])->accessToken;
        return $token;
    }

    public function get_user_info()
    {
        return "Hello Laravel";
    }

    public function tokenPurging()
    {
        
//  will work once in a day during midnight crone 
        $now = Carbon::now();
        // Delete expired tokens (with a buffer time) from the database
        Token::where('created_at', '<', $now)->delete();

        return response()->json(['message' => 'Expired tokens have been purged from the database.']);
    
    }
}
