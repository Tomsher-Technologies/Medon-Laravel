<?php

namespace Shabeersha\EventTracker\Controllers;

use Auth;
use Shabeersha\EventTracker\EventTracker;

use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;
use FacebookAds\Object\ServerSide\ActionSource;
use FacebookAds\Object\ServerSide\Content;
use FacebookAds\Object\ServerSide\CustomData;
use FacebookAds\Object\ServerSide\DeliveryCategory;
use FacebookAds\Object\ServerSide\Event;
use FacebookAds\Object\ServerSide\EventRequest;
use FacebookAds\Object\ServerSide\UserData;
use Illuminate\Support\Facades\URL;

class EventTrackerController
{
    private $access_token;
    private $pixel_id;
    private $api;

    // public function __construct()
    // {

    // }

    public function __invoke(EventTracker $inspire)
    {
        // $quote = $inspire->justDoIt();
        // return $quote;

        $this->access_token = env('CONVERSIONS_API_ACCESS_TOKEN');
        $this->pixel_id = env('CONVERSIONS_API_PIXEL_ID');

        $this->api = Api::init(null, null, $this->access_token);

        // $api->setLogger(new CurlLogger());

        $user = new UserData();

        if (Auth::check()) {
            $user->setEmails(array(Auth::user()->email));
            if (Auth::user()->phone) {
                $user->setPhones(array(Auth::user()->phone));
            }
        }

        $user->setClientIpAddress($_SERVER['REMOTE_ADDR']);
        $user->setClientUserAgent($_SERVER['HTTP_USER_AGENT']);
        $user_data = $user;

        $events = array();

        // $content = (new Content())
        //     ->setProductId('product123')
        //     ->setQuantity(1)
        //     ->setDeliveryCategory(DeliveryCategory::HOME_DELIVERY);

        // $custom_data = (new CustomData())
        //     ->setContents(array($content))
        //     ->setCurrency('usd')
        //     ->setValue(123.45);
        // $event = (new Event())
        // ->setEventName('Purchase')
        // ->setEventTime(time())
        // ->setEventSourceUrl('http://jaspers-market.com/product/123')
        // ->setUserData($user_data)
        // ->setCustomData($custom_data)
        // ->setActionSource(ActionSource::WEBSITE);

        $events[] = (new Event())
            ->setEventName('PageView')
            ->setEventTime(time())
            ->setEventSourceUrl(URL::current())
            ->setUserData($user_data)
            ->setActionSource(ActionSource::WEBSITE);

        // array_push($events, $event);
        // array_push($events, $event2);


        $request = (new EventRequest($this->pixel_id))
            ->setEvents($events);

        $response = $request->execute();
    }
}
