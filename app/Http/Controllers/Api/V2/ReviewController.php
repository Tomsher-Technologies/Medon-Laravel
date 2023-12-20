<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ReviewCollection;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\User;

class ReviewController extends Controller
{
    public function index($id)
    {
        return new ReviewCollection(Review::where('product_id', $id)->where('status', 1)->orderBy('updated_at', 'desc')->paginate(10));
    }

    public function submit(Request $request)
    {
        $product = Product::find($request->product_id);
        $user = User::find(auth()->user()->id);

        /*
         @foreach ($detailedProduct->orderDetails as $key => $orderDetail)
                                            @if($orderDetail->order != null && $orderDetail->order->user_id == Auth::user()->id && $orderDetail->delivery_status == 'delivered' && \App\Models\Review::where('user_id', Auth::user()->id)->where('product_id', $detailedProduct->id)->first() == null)
                                                @php
                                                    $commentable = true;
                                                @endphp
                                            @endif
                                        @endforeach
        */

        $reviewable = false;

        foreach ($product->orderDetails as $key => $orderDetail) {
            if($orderDetail->order != null && $orderDetail->order->user_id == auth()->user()->id && $orderDetail->delivery_status == 'delivered' && \App\Models\Review::where('user_id', auth()->user()->id)->where('product_id', $product->id)->first() == null){
                $reviewable = true;
            }
        }

        if(!$reviewable){
            return response()->json([
                'result' => false,
                'message' => translate('You cannot review this product')
            ]);
        }

        $review = new \App\Models\Review;
        $review->product_id = $request->product_id;
        $review->user_id = auth()->user()->id;
        $review->rating = $request->rating;
        $review->comment = $request->comment;
        $review->viewed = 0;
        $review->save();

        $count = Review::where('product_id', $product->id)->where('status', 1)->count();
        if($count > 0){
            $product->rating = Review::where('product_id', $product->id)->where('status', 1)->sum('rating')/$count;
        }
        else {
            $product->rating = 0;
        }
        $product->save();

        if($product->added_by == 'seller'){
            $seller = $product->user->seller;
            $seller->rating = (($seller->rating*$seller->num_of_reviews)+$review->rating)/($seller->num_of_reviews + 1);
            $seller->num_of_reviews += 1;
            $seller->save();
        }

        return response()->json([
            'result' => true,
            'message' => translate('Review  Submitted')
        ]);
    }

    public function saveReview(Request $request){
        $product_id = getProductIdFromSlug($request->product_slug);
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : null;

        if($product_id != null && $user_id != null){
            $review_count = Review::where('product_id', $product_id)->where('user_id',$user_id)->count();
            if($review_count == 0){
                $review = new \App\Models\Review;
                $review->product_id = $product_id;
                $review->user_id = auth('sanctum')->user()->id;
                $review->rating = $request->rating;
                $review->comment = $request->comment;
                $review->viewed = 0;
                $review->save();
    
                $count = Review::where('product_id', $product_id)->where('status', 1)->count();
                $product = Product::find($product_id);
                if($count > 0){
                    $product->rating = Review::where('product_id', $product->id)->where('status', 1)->sum('rating')/$count;
                }
                else {
                    $product->rating = 0;
                }
                $product->save();
                return response()->json(['success' => true,"message"=>"Review submitted","data" => []],200);
            }else{
                return response()->json(['success' => false,"message"=>"Review already submitted","data" => []],200);
            }
            
        }else{
            return response()->json(['success'=>false,'message'=>'No product found','data' => []],200);
        }
    }

}
