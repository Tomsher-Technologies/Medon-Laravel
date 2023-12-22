<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\WishlistCollection;
use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlist = Wishlist::with('product')->where('user_id', $request->user()->id)->get();
        return new WishlistCollection($wishlist);
    }

    public function store(Request $request)
    {
        $product_slug = $request->has('product_slug') ? $request->product_slug : '';
        $product_id = getProductIdFromSlug($product_slug);
        $user_id = (!empty(auth('sanctum')->user())) ? auth('sanctum')->user()->id : '';

        if($product_id != '' && $user_id != ''){
            $checkWhishlist =   Wishlist::where('user_id',$user_id)->where('product_id',$product_id)->count();

            if($checkWhishlist != 0){
                Wishlist::where('user_id',$user_id)->where('product_id',$product_id)->delete();
            }else{
                Wishlist::create(
                    [
                        'user_id' => $user_id,
                        'product_id' => $product_id
                    ]
                );
            }
            return response()->json([
                'status' => true,
                'wishlist_count' => $this->getWishlistCount($user_id),
                'message' => 'Wishlist updated'
            ], 200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Details not found'
            ], 200);
        }
    }

    public function destroy(Request $request, $id)
    {
        $wishlist = Wishlist::where([
            'user_id' => $request->user()->id
        ])->findOrFail($id);
        $wishlist->delete();

        return response()->json([
            'result' => true,
            'wishlist_count' => $this->getWishlistCount($request->user()->id),
            'message' => translate('Product is successfully removed from your wishlist')
        ], 200);
    }


    public function getCount(Request $request)
    {
        return response()->json([
            'result' => true,
            'wishlist_count' => $this->getWishlistCount($request->user()->id),
        ], 200);
    }
    
    public function getWishlistCount($user)
    {
        return Wishlist::where([
            'user_id' => $user
        ])->count();
    }

    public function isProductInWishlist(Request $request)
    {
        $product = Wishlist::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->count();
        if ($product > 0)
            return response()->json([
                'message' => translate('Product present in wishlist'),
                'is_in_wishlist' => true,
                'product_id' => (int)$request->product_id,
                'wishlist_id' => (int)Wishlist::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->first()->id
            ], 200);

        return response()->json([
            'message' => translate('Product is not present in wishlist'),
            'is_in_wishlist' => false,
            'product_id' => (int)$request->product_id,
            'wishlist_id' => 0
        ], 200);
    }
}
