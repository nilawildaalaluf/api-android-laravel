<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Auth; 

class AuthController extends Controller
{
    public function register(Request $request, User $user)
    {
      $this->validate($request,[
        'name'      => 'required',
        'email'     => 'required|email|unique:users',
        'password'  => 'required|min:6',
      ]);

      $user = $user->create([
          'name'      => $request->name,
          'email'     => $request->email,
          'password'  => bcrypt($request->password),
          'api_token' => bcrypt($request->email)
      ]);

      $respose = fractal()
          ->item($user)
          ->transformWith(new UserTransformer)
          ->addMeta([
              'token' => $user->api_token, 'id_user' => $user->id])
          ->toArray();

      return response()->json($respose, 201);
    }


    public function login(Request $request, User $user)
    {
        if (!Auth::attempt(['email'=> $request->email, 'password' => $request->password]))
          {
            return response()->json(['error' => 'your credential is wrong', 401]);
          }

        $user  = $user->find(Auth::user()->id);
        
        return fractal()
            ->item($user)
            ->transformWith(new UserTransformer)
            ->addMeta([
              'token' => $user->api_token, 'id_user' => $user->id])
            ->toArray();
      // return 'login';
    }
}
