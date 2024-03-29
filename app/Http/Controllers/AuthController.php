<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Str;
use App\Models\User;
use Exception;


class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'signin']]);
    }

    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function signin(Request $request)
    {

        if ($request->isJson()) {

            $data = $request->only('name', 'surname', 'email', 'password', 'specialty', 'country');

            // Se comprueba que los campos cumplen el formato
            $validator = Validator::make($data, [
                'name' => 'required|max:60',
                'surname' => 'required|max:60',
                'email' => 'required|email|max:255',
                'password' => 'required|max:60',
                'specialty' => 'required|max:60',
                'country' => 'required|max:60',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Los datos introducidos no son correctos'], 422);
            }

            if (User::where('email', $data['email'])->doesntExist()) {
                // Si el usuario existe
                try {

                    User::create([
                        'uid' => Str::random(32),
                        'name' => $data['name'],
                        'surname' => $data['surname'],
                        'email' => $data['email'],
                        'password' => Hash::make($data['password']),
                        'specialty' => $data['specialty'],
                        'country' => $data['country'],
                    ]);

                    return response()->json(['message' => 'Usuario creado correctamente'], 201);
                } catch (Exception $e) {
                    return response()->json(['error' => 'Ha ocurrido un problema en el registro '], 500);
                }
            } else {
                return response()->json(['error' => 'El usuario ya se encuentra registrado con ese email'], 400);
            }
        } else {
            return response()->json(['error' => 'El formato no es válido'], 400);
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

            $credentials = $request->only('email', 'password');


            // Se comprueba que los campos cumplen el formato
            $validator = Validator::make($credentials, [
                'email' => 'required|email|max:255',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Los datos introducidos no son correctos'], 422);
            }


            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Email o contraseña incorrectos'], 400);
            }

            // Éxito - Login correcto
            return $this->respondWithToken($token);
        } else {
            return response()->json(['error' => 'El formato no es válido'], 400);
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
        return response()->json(['message' => 'Se ha cerrado la sesión correctamente'], 200);
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
        ], 200);
    }
}
