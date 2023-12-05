<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */
// use App\Mail\SupportMailManager;
//demo

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\Frontend\EnquiryContoller;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\WishlistController;
use App\Http\Livewire\Frontend\Cart;
use App\Http\Livewire\Frontend\Checkout;
use App\Models\State;
use App\Models\User;
use App\Utility\SendSMSUtility;
use Carbon\Carbon;

// Route::get('/demo/cron_1', [DemoController::class, 'cron_1']);
// Route::get('/demo/cron_2', [DemoController::class, 'cron_2']);
// Route::get('/convert_assets', [DemoController::class, 'convert_assets']);
// Route::get('/convert_category', [DemoController::class, 'convert_category']);
// Route::get('/convert_tax', [DemoController::class, 'convertTaxes']);
// Route::get('/insert_product_variant_forcefully', [DemoController::class, 'insert_product_variant_forcefully']);
// Route::get('/update_seller_id_in_orders/{id_min}/{id_max}', [DemoController::class, 'update_seller_id_in_orders']);
// Route::get('/migrate_attribute_values', [DemoController::class, 'migrate_attribute_values']);

Route::get('/refresh-csrf', function () {
    return csrf_token();
});


Auth::routes([
    'verify' => false,
    'reset' => true
]);
Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');
// Route::post('/currency', [CurrencyController::class, 'changeCurrency'])->name('currency.change');

// Route::get('/signin', [HomeController::class, 'login'])->name('user.login');
// Route::get('/registration', [HomeController::class, 'registration'])->name('user.registration');
// Route::post('/signin/cart', [HomeController::class, 'cart_login'])->name('cart.login.submit');

// //Home Page
Route::get('/', function () {

    dd("Hi");

    // $user = User::find(9);


    // $user->verification_code = rand(100000, 999999);
    // $user->verification_code_expiry = Carbon::now()->addMinutes(5);
    // $user->save();
    // $message = "Hi $user->name, Greetings from Farook! Your OTP: $user->verification_code Treat this as confidential. Sharing this with anyone gives them full access to your Farook Account.";

    // $status = SendSMSUtility::sendSMS('971507428638', $message);
    // dd($status);
})->name('home');
// Route::post('/home/section/brands', [HomeController::class, 'load_brands_section'])->name('home.section.brands');
// Route::post('/home/section/large_banner', [HomeController::class, 'load_large_banner_section'])->name('home.section.large_banner');
// Route::post('/category/nav-element-list', [HomeController::class, 'get_category_items'])->name('category.elements');

// Route::get('/flash-deals', [HomeController::class, 'all_flash_deals'])->name('flash-deals');
// Route::get('/flash-deal/{slug}', [HomeController::class, 'flash_deal_details'])->name('flash-deal-details');

// Route::get('/sitemap.xml', function () {
//     return base_path('sitemap.xml');
// });

// Route::get('/search', [SearchController::class, 'index'])->name('search');
// Route::get('/search?keyword={search}', [SearchController::class, 'index'])->name('suggestion.search');
// Route::post('/ajax-search', [SearchController::class, 'ajax_search'])->name('search.ajax');
Route::get('/category/{category_slug}', [SearchController::class, 'listingByCategory'])->name('products.category');
// Route::get('/brand/{brand_slug}', [SearchController::class, 'listingByBrand'])->name('products.brand');

// // Quick view
// Route::get('/product/quick_view', [HomeController::class, 'productQuickView'])->name('product.quick_view');
// Route::post('/product/details/same_brand', [HomeController::class, 'productSameBrandView'])->name('product.details.same_brand');
// Route::post('/product/details/related_products', [HomeController::class, 'productRelatedProductsView'])->name('product.details.related_products');
// Route::post('/product/details/also_bought', [HomeController::class, 'productAlsoBoughtView'])->name('product.details.also_bought');
// Route::get('/product/{slug}', [HomeController::class, 'product'])->name('product');
// Route::post('/product/variant_price', [HomeController::class, 'variant_price'])->name('products.variant_price');
// Route::get('/shop/{slug}', [HomeController::class, 'shop'])->name('shop.visit');
// Route::get('/shop/{slug}/{type}', [HomeController::class, 'filter_shop'])->name('shop.visit.type');

// Route::get('/cart', Cart::class)->name('cart');
// Route::post('/cart/addtocart', [CartController::class, 'addToCart'])->name('cart.addToCart');
// Route::post('/cart/removeFromCart', [CartController::class, 'removeFromCart'])->name('cart.removeFromCart');

// // 


Route::group(['middleware' => ['user']], function () {
    Route::get('/my-account', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [HomeController::class, 'profile'])->name('profile');
    Route::get('/profile/password', [HomeController::class, 'profilePassword'])->name('profile.password');
    Route::post('/profile/password', [HomeController::class, 'profilePasswordUpdate']);
    Route::post('/new-user-verification', [HomeController::class, 'new_verify'])->name('user.new.verify');
    Route::post('/new-user-email', [HomeController::class, 'update_email'])->name('user.change.email');

    Route::post('/user/update-profile', [HomeController::class, 'userProfileUpdate'])->name('user.profile.update');

    Route::resource('purchase_history', PurchaseHistoryController::class);
    Route::get('/purchase_history/details/{order_id}', [PurchaseHistoryController::class, 'purchase_history_details'])->name('purchase_history.details');
    Route::get('/purchase_history/destroy/{id}', [PurchaseHistoryController::class, 'destroy'])->name('purchase_history.destroy');

    Route::resource('wishlists', WishlistController::class);
    Route::post('/wishlists/remove', [WishlistController::class, 'remove'])->name('wishlists.remove');

    Route::resource('addresses', AddressController::class);
    Route::post('/addresses/update/{id}', [AddressController::class, 'update'])->name('addresses.update');
    Route::get('/addresses/destroy/{id}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/set_default', [AddressController::class, 'set_default'])->name('addresses.set_default');
});
