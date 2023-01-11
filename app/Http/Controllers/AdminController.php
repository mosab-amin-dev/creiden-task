<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminRegisterRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Admin;


class AdminController extends Controller
{
    public function login(AdminLoginRequest $request) {
        $user = Admin::where('email', $request->validated('email'))->first();
        $token = $user->createToken(uniqid())->plainTextToken;
        if (!$token) {
            return $this->apiResponse(null, self::STATUS_NOT_FOUND, __('site.credentials_not_match_records'));
        }
        return $this->apiResponse(['user' => new UserResource($user), 'token' => $token ], self::STATUS_OK, __('site.successfully_logged_in'));
    }

    public function logout()
    {
        $auth_user = auth()->user();
        $auth_user->currentAccessToken()->delete();
        $auth_user->save();
        return $this->apiResponse(null,self::STATUS_OK,__('site.logout_success'));
    }

    public function register(AdminRegisterRequest $request)
    {
        $user = Admin::create($request->validated());
        $token = $user->createToken(uniqid())->plainTextToken;

        return $this->apiResponse(['user'=>new UserResource($user),'token'=>$token],self::STATUS_OK,__('site.code_correct'));
    }

}
