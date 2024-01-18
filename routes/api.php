<?php

use App\Http\Controllers\Api\V2\AddressController;
use App\Http\Controllers\Api\V2\AppBannerController;
use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\BannerController;
use App\Http\Controllers\Api\V2\BrandController;
use App\Http\Controllers\Api\V2\BusinessSettingController;
use App\Http\Controllers\Api\V2\CartController;
use App\Http\Controllers\Api\V2\CategoryController;
use App\Http\Controllers\Api\V2\CommonController;
use App\Http\Controllers\Api\V2\DeliveryBoyController;
use App\Http\Controllers\Api\V2\ProductController;
use App\Http\Controllers\Api\V2\ProfileController;
use App\Http\Controllers\Api\V2\ReviewController;
use App\Http\Controllers\Api\V2\WishlistController;
use App\Http\Controllers\Api\V2\WebsiteController;
use App\Http\Controllers\Api\V2\CheckoutController;
use App\Http\Controllers\Api\V2\ErpController;
use App\Http\Controllers\Api\V2\PasswordResetController;

Route::group(['prefix' => 'v2/auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('verify-opt', [AuthController::class, 'verify_otp']);
    Route::post('resend-opt', [AuthController::class, 'resend_otp']);
    Route::post('check-user-exist', [AuthController::class, 'check_user_exist']);
    Route::post('forgot-password', [PasswordResetController::class, 'forgetRequest']);
    Route::post('reset-password', [PasswordResetController::class, 'resetRequest']);

    // Route::post('password/forget_request', 'Api\V2\PasswordResetController@forgetRequest');
    // Route::post('password/confirm_reset', 'Api\V2\PasswordResetController@confirmReset');
    // Route::post('password/resend_code', 'Api\V2\PasswordResetController@resendCode');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('user',  [AuthController::class, 'user']);
        
    });
    
});

Route::post('update-product',[ErpController::class, 'updateProduct'])->middleware('erp_token');

Route::group(['prefix' => 'v2'], function () {
    Route::apiResource('banners', BannerController::class)->only('index');
    
    // Wishlist
    Route::group(['middleware' => ['auth:sanctum']], function () {
        // Customer
        Route::group(['prefix' => 'profile'], function () {
            Route::get('counters', [ProfileController::class, 'counters']);
            // Route::post('update', [ProfileController::class, 'update']);
            Route::apiResource('address', AddressController::class);
            Route::post('address/make_default', [AddressController::class, 'makeShippingAddressDefault']);
            Route::post('address/delete', [AddressController::class, 'deleteAddress']);
            Route::post('address/update', [AddressController::class, 'updateAddress']);

            Route::get('account', [ProfileController::class, 'getUserAccountInfo']);
            Route::post('/change-password', [ProfileController::class, 'changePassword']);
            Route::post('/send-otp', [ProfileController::class, 'sendOTPPhonenumber']);
            Route::post('/verify-phone', [ProfileController::class, 'verifyPhonenumber']);
            Route::post('/update-userdata', [ProfileController::class, 'updateUserData']);
            
        });
        Route::group(['prefix' => 'account'], function () {
            Route::get('orders', [ProfileController::class, 'orderList']);
            Route::get('order-details', [ProfileController::class, 'orderDetails']);
        });    
        Route::post('save-location', [ProfileController::class, 'saveLiveLocation']);
        // Route::get('product', [ProductController::class, 'show']);
        Route::get('wishlists/count', [WishlistController::class, 'getCount']);
        Route::apiResource('wishlists', WishlistController::class)->only('index', 'store', 'destroy');
        Route::post('wishlist/remove', [WishlistController::class, 'removeWishlistItem']);
        Route::post('review/submit', [ReviewController::class, 'saveReview']);
        Route::get('review/check', [ReviewController::class, 'checkReviewStatus']);

        Route::post('place-order', [CheckoutController::class, 'placeOrder']);
        Route::post('return-request', [CheckoutController::class, 'returnRequest']);
        Route::post('cancel-order', [CheckoutController::class, 'cancelOrderRequest']);
       
    });
    Route::post('payment-success', [CheckoutController::class, 'successPayment'])->name('payment-success');
    Route::post('payment-cancel', [CheckoutController::class, 'cancelPayment'])->name('payment-cancel');

    Route::post('app-payment-success', [CheckoutController::class, 'successAppPayment'])->name('app-payment-success');
    Route::post('app-payment-cancel', [CheckoutController::class, 'cancelAppPayment'])->name('app-payment-cancel');
    // Products
    Route::apiResource('categories', CategoryController::class)->only('index');
    Route::get('brands/top', [BrandController::class, 'top']);
    Route::apiResource('brands', BrandController::class)->only('index');
    Route::apiResource('brands', BrandController::class)->only('index');
    Route::get('products', [ProductController::class, 'index']);
    Route::get('product', [ProductController::class, 'show']);
    Route::get('related-products', [ProductController::class, 'relatedProducts']);
    // Cart
    Route::get('cart/count', [CartController::class, 'getCount']);
    Route::post('cart/change_quantity', [CartController::class, 'changeQuantity']);
    Route::post('cart/remove', [CartController::class, 'removeCartItem']);
    Route::apiResource('cart', CartController::class)->only('index', 'store', 'destroy');
    Route::post('coupon-apply', [CheckoutController::class, 'apply_coupon_code']);
    Route::post('coupon-remove', [CheckoutController::class, 'remove_coupon_code']);

    // Common
    Route::apiResource('business-settings', BusinessSettingController::class)->only('index');
    // Route::get('cities', [AddressController::class, 'getCities']);
    // Route::get('states', [AddressController::class, 'getStates']);
    Route::get('countries', [AddressController::class, 'getCountries']);
    Route::get('states-by-country',  [AddressController::class, 'getStatesByCountry']);
    Route::post('upload-prescription', [ProfileController::class, 'uploadPrescription']);

    Route::get('get-prescriptions',  [ProfileController::class, 'getPrescriptions']);
    // Home
    Route::group(['prefix' => 'app'], function () {
        Route::apiResource('app-banners', AppBannerController::class)->only('index');
        Route::get('top_categories', [CommonController::class, 'homeTopCategory']);
        Route::get('top_brands', [CommonController::class, 'homeTopBrand']);
        Route::get('top_brands', [CommonController::class, 'homeTopBrand']);
        Route::get('ad_banners', [CommonController::class, 'homeAdBanners']);
        Route::get('offers', [CommonController::class, 'homeOffers']);
        Route::get('offer-details', [CommonController::class, 'offerDetails']);
    });

    Route::group(['prefix' => 'website'], function () {
        Route::get('header', [WebsiteController::class, 'websiteHeader']);
        Route::get('home', [WebsiteController::class, 'websiteHome']);
        Route::get('categories', [WebsiteController::class, 'websiteCategories']);
        Route::get('offers', [WebsiteController::class, 'categoryOffers']);
        // Route::get('footer', [WebsiteController::class, 'websiteFooter']);
        Route::get('store-locator', [WebsiteController::class, 'storeLocations']);
        Route::get('page-contents', [WebsiteController::class, 'pageContents']);
        Route::post('contact-us', [WebsiteController::class, 'contactUs']);
    });

    // Footer
    Route::get('footer', [CommonController::class, 'footer']);

    // Footer newsletter
    Route::post('newsletter', [CommonController::class, 'newsletter']);

    // Splash screen
    Route::get('splash_screen', [CommonController::class, 'splash_screen']);

    // Rider
    Route::group(['prefix' => 'delivery-boy', 'middleware' => ['auth:sanctum']], function () {
        Route::post('change-status', [DeliveryBoyController::class, 'change_status']);
        Route::get('get-status', [DeliveryBoyController::class, 'get_status']);
        Route::get('dashboard-summary', [DeliveryBoyController::class, 'dashboard_summary']);
        Route::prefix('deliveries')->group(function () {
            Route::post('/picked_up', [DeliveryBoyController::class, 'picked_up_delivery']);
            Route::post('/completed-delivery', [DeliveryBoyController::class, 'complete_delivery']);

            Route::get('/pending-orders', [DeliveryBoyController::class, 'assigned_delivery']);
            Route::get('/completed-orders', [DeliveryBoyController::class, 'completed_delivery']);
            Route::get('/completed-returns', [DeliveryBoyController::class, 'completed_return_delivery']);
        });
    });
    // -----------------------------------------------------------------------------------

    Route::get('get-search-suggestions', 'Api\V2\SearchSuggestionController@getList');

    Route::get('categories/featured', 'Api\V2\CategoryController@featured');
    Route::get('categories/home', 'Api\V2\CategoryController@home');
    Route::get('categories/top', 'Api\V2\CategoryController@top');
    Route::get('sub-categories/{id}', 'Api\V2\SubCategoryController@index')->name('subCategories.index');

    Route::apiResource('colors', 'Api\V2\ColorController')->only('index');

    Route::apiResource('currencies', 'Api\V2\CurrencyController')->only('index');

    Route::apiResource('customers', 'Api\V2\CustomerController')->only('show');

    Route::apiResource('home-categories', 'Api\V2\HomeCategoryController')->only('index');

    //Route::get('purchase-history/{id}', 'Api\V2\PurchaseHistoryController@index')->middleware('auth:sanctum');
    //Route::get('purchase-history-details/{id}', 'Api\V2\PurchaseHistoryDetailController@index')->name('purchaseHistory.details')->middleware('auth:sanctum');

    Route::get('purchase-history', 'Api\V2\PurchaseHistoryController@index')->middleware('auth:sanctum');
    Route::get('purchase-history-details/{id}', 'Api\V2\PurchaseHistoryController@details')->middleware('auth:sanctum');
    Route::get('purchase-history-items/{id}', 'Api\V2\PurchaseHistoryController@items')->middleware('auth:sanctum');

    Route::get('filter/categories', 'Api\V2\FilterController@categories');
    Route::get('filter/brands', 'Api\V2\FilterController@brands');


    // Route::get('products/seller/{id}', 'Api\V2\ProductController@seller');
    // Route::get('products/category/{id}', 'Api\V2\ProductController@category')->name('api.products.category');
    // Route::get('products/sub-category/{id}', 'Api\V2\ProductController@subCategory')->name('products.subCategory');
    // Route::get('products/sub-sub-category/{id}', 'Api\V2\ProductController@subSubCategory')->name('products.subSubCategory');
    // Route::get('products/brand/{id}', 'Api\V2\ProductController@brand')->name('api.products.brand');
    // Route::get('products/todays-deal', 'Api\V2\ProductController@todaysDeal');
    // Route::get('products/featured', 'Api\V2\ProductController@featured');
    // Route::get('products/best-seller', 'Api\V2\ProductController@bestSeller');
    // Route::get('products/related/{id}', 'Api\V2\ProductController@related')->name('products.related');

    // Route::get('products/featured-from-seller/{id}', 'Api\V2\ProductController@newFromSeller')->name('products.featuredromSeller');
    // Route::get('products/search', 'Api\V2\ProductController@search');
    // Route::get('products/variant/price', 'Api\V2\ProductController@variantPrice');
    // Route::get('products/home', 'Api\V2\ProductController@home');
    // Route::apiResource('products', 'Api\V2\ProductController')->except(['store', 'update', 'destroy']);

    // Route::get('cart-summary', 'Api\V2\CartController@summary')->middleware('auth:sanctum');
    // Route::post('carts/process', 'Api\V2\CartController@process')->middleware('auth:sanctum');
    // Route::post('carts/add', 'Api\V2\CartController@add')->middleware('auth:sanctum');
    // Route::post('carts/change-quantity', 'Api\V2\CartController@changeQuantity')->middleware('auth:sanctum');
    // Route::apiResource('carts', 'Api\V2\CartController')->only('destroy')->middleware('auth:sanctum');
    // Route::post('carts', 'Api\V2\CartController@getList')->middleware('auth:sanctum');


    
    

    Route::post('update-address-in-cart', 'Api\V2\AddressController@updateAddressInCart')->middleware('auth:sanctum');

    Route::get('payment-types', 'Api\V2\PaymentTypesController@getList');

    Route::get('reviews/product/{id}', 'Api\V2\ReviewController@index')->name('api.reviews.index');
    Route::post('reviews/submit', 'Api\V2\ReviewController@submit')->name('api.reviews.submit')->middleware('auth:sanctum');

    Route::get('shop/user/{id}', 'Api\V2\ShopController@shopOfUser')->middleware('auth:sanctum');
    Route::get('shops/details/{id}', 'Api\V2\ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'Api\V2\ShopController@allProducts')->name('shops.allProducts');
    Route::get('shops/products/top/{id}', 'Api\V2\ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'Api\V2\ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'Api\V2\ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'Api\V2\ShopController@brands')->name('shops.brands');
    Route::apiResource('shops', 'Api\V2\ShopController')->only('index');

    Route::apiResource('sliders', 'Api\V2\SliderController')->only('index');

    // Route::get('wishlists-check-product', 'Api\V2\WishlistController@isProductInWishlist')->middleware('auth:sanctum');
    // Route::get('wishlists-add-product', 'Api\V2\WishlistController@add')->middleware('auth:sanctum');
    // Route::get('wishlists-remove-product', 'Api\V2\WishlistController@remove')->middleware('auth:sanctum');
    // Route::get('wishlists', 'Api\V2\WishlistController@index')->middleware('auth:sanctum');
    // Route::apiResource('wishlists', 'Api\V2\WishlistController')->except(['index', 'update', 'show']);

    Route::get('policies/seller', 'Api\V2\PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'Api\V2\PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'Api\V2\PolicyController@returnPolicy')->name('policies.return');

    // Route::get('user/info/{id}', 'Api\V2\UserController@info')->middleware('auth:sanctum');
    // Route::post('user/info/update', 'Api\V2\UserController@updateName')->middleware('auth:sanctum');


    Route::post('shipping_cost', 'Api\V2\ShippingController@shipping_cost')->middleware('auth:sanctum');


    Route::post('offline/payment/submit', 'Api\V2\OfflinePaymentController@submit')->name('api.offline.payment.submit');

    Route::post('order/store', 'Api\V2\OrderController@store')->middleware('auth:sanctum');
});

Route::fallback(function () {
    return response()->json([
        'data' => [],
        'success' => false,
        'status' => 404,
        'message' => 'Invalid Route'
    ]);
});
