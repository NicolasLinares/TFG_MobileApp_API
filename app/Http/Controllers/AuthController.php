<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Exception;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

        /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request)
    {

        if ($request->isJson()) {
            $data = $request->json()->all();

            if (User::where('email', $data['email'])->doesntExist()) {
                // Si el usuario existe
                try{
                    $user = User::create([
                        'name'=> $data['name'],
                        'uid'=> Str::random(32),
                        'surnames'=> $data['surnames'],
                        'email'=> $data['email'],
                        'password'=> Hash::make($data['password']),
                        'speciality'=> $data['speciality'],
                        'country'=> $data['country'],
                    ]);

                    return response()->json(['message' =>'User succesfully created'], 201);
                } catch (Exception $e) {
                    return response()->json(['error' => 'Ha ocurrido un problema en el registro' ], 500);
                }
            } else {
                return response()->json(['error' => 'El usuario ya se encuentra registrado con ese email' ], 400);
            }        
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        if ($request->isJson()) {
            try {
                $data = $request->json()->all();
                $user = User::where('email', $data['email'])->first();

                $credentials = request(['email', 'password']);
                if (!$token = auth()->attempt($credentials)) {
                    return response()->json(['error' => 'Email o contraseña incorrectos.'], 400);
                }
                return $this->respondWithToken($token);

            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Unauthorized' ], 401);
            }
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }

    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }
    public function payload()
    {
        return response()->json(auth()->payload());
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL(),  // Se define en .env
            'user' => auth()->user(),
        ]);
    }



}