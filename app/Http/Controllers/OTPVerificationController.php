<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Utility\SendSMSUtility;
use Carbon\Carbon;

class OTPVerificationController extends Controller
{
    public function send_code(User $user)
    {
        $user->verification_code = rand(100000, 999999);
        $user->verification_code_expiry = Carbon::now()->addMinutes(5);
        $user->save();
        $message = "Hi $user->name, Greetings from Medon Pharmacy! Your OTP: $user->verification_code Treat this as confidential. Sharing this with anyone gives them full access to your Account.";
        

        $status = SendSMSUtility::sendSMS($user->phone, $message);
    }
}
