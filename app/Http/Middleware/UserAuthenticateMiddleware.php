<?php

namespace App\Http\Middleware;

use Closure;
use \Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKeyAuthenticate;

class AppError extends Exception { }

class UserAuthenticateMiddleware
{
    // This middleware is responsible for authenticating the users
    public function handle(Request $request, Closure $next): Response
    {
        try
        {
            if(!$request->hasHeader('Authorization')) {
                throw new AppError('Unauthorized. Authorization is empty!');
            }

            $publicKey = urldecode($request->header('Authorization'));

            if(!$request->hasHeader('user-id')) {
                throw new AppError('Unauthorized. user-id is empty!');
            }

            $user = User::where('id', $request->header("user-id"))->first();

            if(empty($user))  {
                throw new AppError('Unauthorized. Invalid key authorization');
            }

            $publicHash = hash('sha256', $publicKey);

            if($user->publicHash != $publicHash) {
                throw new AppError('Unauthorized. Invalid key to the user!');
            }
            
            $request->merge(['user' => $user]);

        } catch (AppError $ex) {
            return response()->json(['success' => false,'message' => $ex->getMessage()], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
