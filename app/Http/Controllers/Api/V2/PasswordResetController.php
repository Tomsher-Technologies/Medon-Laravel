<?php

namespace App\Http\Controllers\Api\V2;

use App\Notifications\ForgotPassword;
use Illuminate\Http\Request;
use App\Models\User;
// use App\Models\PasswordReset;
use App\Notifications\PasswordResetRequest;
use Illuminate\Support\Str;
use App\Http\Controllers\OTPVerificationController;

use Hash;

class PasswordResetController extends Controller
{
    public function forgetRequest(Request $request)
    {
        $email = $request->has('email') ? $request->email : '';
        if($email){
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => translate('User is not found')], 200);
            }else{
                $user->verification_code = rand(100000, 999999);
                $user->save();
                $user->notify(new ForgotPassword($user));
                return response()->json([
                    'status' => true,
                    'message' => translate('Verification code is sent')
                ], 200);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => translate('Email is not found')], 200);
        }
    }

    public function resetRequest(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6'
        ]);
        $code = $request->has('code') ? $request->code : '';
        $email = $request->has('email') ? $request->email : '';
        $password = $request->has('password')? trim($request->password): '';

        if($code != '' && $email != '' &&  $password != ''){
            $user = User::where('email', $email)->where('verification_code', $code)->first();
            if ($user != null) {
                $user->verification_code = null;
                $user->password = Hash::make($password);
                $user->save();
                return response()->json([
                    'status' => true,
                    'message' => translate('Your password is reset.Please login'),
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => translate('Invalid verification code'),
                ], 200);
            }
        }else {
            return response()->json([
                'status' => false,
                'message' => translate('Please fill all the fields'),
            ], 200);
        }
    }

    public function resendCode(Request $request)
    {

        if ($request->verify_by == 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $user = User::where('phone', $request->email_or_phone)->first();
        }


        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate('User is not found')], 404);
        }

        $user->verification_code = rand(100000, 999999);
        $user->save();

        if ($request->verify_by == 'email') {
            $user->notify(new AppEmailVerificationNotification());
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }



        return response()->json([
            'result' => true,
            'message' => translate('A code is sent again'),
        ], 200);
    }
}
