<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function socialiteAuthorize($provider){
		return Socialite::driver($provider)->redirect();
	}

	public function socialiteLogin($provider){
		if(!empty(Request::input('denied'))){
			return Redirect::route('home.index')->withErrors(['Login was canceled by the user.']);
		}
		$oauth = Socialite::driver($provider)->user();
		//print_r($oauth->user);
		//print_r($oauth->getName());
		$user = User::where('provider', '=', $provider)->where('provider_user_id', '=', $oauth->getId())->first();
		// if we can't find user
		if(!count($user)){

			$user = User::where('email', '=', $oauth->getEmail())->first();
			if(!count($user)){
				// must be a new one
				$user = User::create([
					'name'                => $oauth->getName(),
					'email'               => $oauth->getEmail(),
					'avatar'              => $oauth->getAvatar(),
					'provider'            => $provider,
					'provider_user_id'    => $oauth->getId(),
					'provider_user_token' => $oauth->token,
					'roles'               => ['user']
				]);
			}else{
				return Redirect::route('home.index')->withErrors(["You've already linked your ".ucfirst($user->provider)." account. Please login with ".ucfirst($user->provider)." instead."]);
			}
		}
		Auth::login($user);
		return Redirect::intended();
	}

    public function getLogin(){
		return Redirect::route('home.index', ['showLogin'=>'true']);
	}

	public function getLogout(){
		Auth::logout();
	}

	public function postRegister(Request $request){

		$validator = $this->validator($request->all());
		if($validator->fails()){
			return Redirect::back()
					->withErrors($validator)
					->withInput();
		}

		$data = $request->only('name', 'email', 'password');
		$data['roles'] = ['user'];
		$user = $this->create($data);

		Auth::login($user);
		return Redirect::intended();

	}

	protected function create(array $data){
		return User::create([
			'name'                => $data['name'],
			'email'               => $data['email'],
			'password'            => bcrypt($data['password']),
			'avatar'              => '',
			'provider'            => '',
			'provider_user_id'    => '',
			'provider_user_token' => '',
		]);
	}

	    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

}
