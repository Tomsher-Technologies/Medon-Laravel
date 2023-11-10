<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\Admin\NewEnquiry;
use App\Models\Product;
use App\Models\Products\ProductEnquiries;
use Auth;
use Cache;
use DB;
use Illuminate\Http\Request;
use Mail;

use function GuzzleHttp\Promise\queue;

class EnquiryContoller extends Controller
{

    protected $user_col = "";
    protected $user_id = "";

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $this->user_col = "user_id";
                $this->user_id = Auth::id();
            } else {
                $this->user_col = "temp_user_id";
                $this->user_id = getTempUserId();
            }

            return $next($request);
        });
    }

    public function index()
    {
        $enquiries = ProductEnquiries::whereStatus(0)->where($this->user_col, $this->user_id)->with('products')->first();
        return view('frontend.enquiry.enquiry', compact('enquiries'));
    }

    public function add(Request $request)
    {
        $product = Product::select(['id', 'variant_product', 'choice_options'])->where('slug', $request->slug)->with([
            'stocks'
        ])->first();

        $enquiries = ProductEnquiries::whereStatus(0)->where(
            $this->user_col,
            $this->user_id
        )->first();

        if (!$enquiries) {
            $enquiries = ProductEnquiries::create([
                'status' => 0,
                $this->user_col => $this->user_id,
                'comment' => ""
            ]);

            $enquiries->products()->syncWithoutDetaching([
                $product->id => [
                    'sku' => $product->stocks->first()->sku,
                    'varient' => $product->stocks->first()->variant,
                    'quantity' => 1,
                ]
            ]);
        } else {
            $piviot_products = DB::table('product_product_enquiry')->where([
                'product_id' => $product->id,
                'product_enquiry_id' => $enquiries->id
            ])->first();

            $quantity = 1;

            if ($piviot_products) {
                $quantity = $piviot_products->quantity + 1;
            }

            DB::table('product_product_enquiry')->upsert(
                [
                    [
                        'product_id' => $product->id,
                        'product_enquiry_id' => $enquiries->id,
                        'quantity' => $quantity
                    ],
                ],
                ['product_id', 'product_enquiry_id'],
                ['quantity']
            );
        }

        Cache::flush('user_enquiry_count_' . $this->user_id);

        return response()->json([
            'message' => [
                'name' => $product->name
            ],
            'count' => enquiryCount()
        ], 200);
    }

    public function remove(Request $request)
    {
        $enquiries = ProductEnquiries::whereStatus(0)->where($this->user_col, $this->user_id)->first();
        $enquiries->products()->detach($request->id);
        Cache::flush('user_enquiry_count_' . $this->user_id);
        return response()->json([
            'message' => "Product removed from enquiry",
            'count' => enquiryCount()
        ], 200);
    }

    public function changeQuantity(Request $request)
    {
        $enquiries = ProductEnquiries::whereStatus(0)->where($this->user_col, $this->user_id)->first();

        DB::table('product_product_enquiry')->upsert(
            [
                [
                    'product_id' => $request->product_id,
                    'product_enquiry_id' => $enquiries->id,
                    'quantity' => $request->quantity
                ],
            ],
            ['product_id', 'product_enquiry_id'],
            ['quantity']
        );
        return response()->json([
            'message' => "Product removed from enquiry",
            'count' => enquiryCount()
        ], 200);
    }

    public function submit(Request $request)
    {
        return response()->json([
            'message' => $request,
            'count' => 0
        ], 210);

        parse_str($request->data, $data);

        $enquiries = ProductEnquiries::whereStatus(0)->where($this->user_col, $this->user_id)->first();

        $enquiries->update([
            'comment' => $data['message'],
            'status' => 1,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone_number' => $data['phone'],
        ]);

        $enquiries->load('products');

        Mail::to(getAdminEmail())->queue(new NewEnquiry($enquiries));

        Cache::flush('user_enquiry_count_' . $this->user_id);

        return response()->json([
            'message' => "Enquriy sent succesfully",
            'count' => 0
        ], 200);
    }
}
