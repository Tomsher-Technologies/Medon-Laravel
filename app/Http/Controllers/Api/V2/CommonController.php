<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\SplashScreenCollection;
use App\Models\App\SplashScreens;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function newsletter(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'Please enter your email',
            'email.email' => 'Please enter a valid email'
        ]);

        $sub =  Subscriber::updateOrCreate([
            'email' => $request->email
        ]);

        if ($sub->wasRecentlyCreated) {
            return response()->json([
                'result' => true,
                'status' => 1,
                'message' => "You have been sucessfull subscribed to our newsletter",
            ], 200);
        }

        return response()->json([
            'result' => true,
            'status' => 0,
            'message' => "You are aleardy subscribed to our newsletter",
        ], 200);
    }

    public function footer()
    {
        return response()->json([
            'result' => true,
            'app_links' => array([
                'play_store' => array([
                    'link' => get_setting('play_store_link'),
                    'image' => api_asset(get_setting('play_store_image')),
                ]),
                'app_store' => array([
                    'link' => get_setting('app_store_link'),
                    'image' => api_asset(get_setting('app_store_image')),
                ]),
            ]),
            'social_links' => array([
                'facebook' => get_setting('facebook_link'),
                'twitter' => get_setting('twitter_link'),
                'instagram' => get_setting('instagram_link'),
                'youtube' => get_setting('youtube_link'),
                'linkedin' => get_setting('linkedin_link'),
            ]),
            'copyright_text' => get_setting('frontend_copyright_text'),
            'contact_phone' => get_setting('contact_phone'),
            'contact_email' => get_setting('contact_email'),
            'contact_address' => get_setting('contact_address'),
        ]);
    }

    public function splash_screen()
    {
        $screens = SplashScreens::where('status', 1)->orderBy('sort_order')->get();

        return new SplashScreenCollection($screens); 
    }
}
