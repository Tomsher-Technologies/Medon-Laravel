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
Route::post('/currency', [CurrencyController::class, 'changeCurrency'])->name('currency.change');

Route::get('/signin', [HomeController::class, 'login'])->name('user.login');
Route::get('/registration', [HomeController::class, 'registration'])->name('user.registration');
Route::post('/signin/cart', [HomeController::class, 'cart_login'])->name('cart.login.submit');

//Home Page
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/home/section/brands', [HomeController::class, 'load_brands_section'])->name('home.section.brands');
Route::post('/home/section/large_banner', [HomeController::class, 'load_large_banner_section'])->name('home.section.large_banner');
Route::post('/category/nav-element-list', [HomeController::class, 'get_category_items'])->name('category.elements');

Route::get('/flash-deals', [HomeController::class, 'all_flash_deals'])->name('flash-deals');
Route::get('/flash-deal/{slug}', [HomeController::class, 'flash_deal_details'])->name('flash-deal-details');

Route::get('/sitemap.xml', function () {
    return base_path('sitemap.xml');
});

Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search?keyword={search}', [SearchController::class, 'index'])->name('suggestion.search');
Route::post('/ajax-search', [SearchController::class, 'ajax_search'])->name('search.ajax');
Route::get('/category/{category_slug}', [SearchController::class, 'listingByCategory'])->name('products.category');
Route::get('/brand/{brand_slug}', [SearchController::class, 'listingByBrand'])->name('products.brand');

// Quick view
Route::get('/product/quick_view', [HomeController::class, 'productQuickView'])->name('product.quick_view');
Route::post('/product/details/same_brand', [HomeController::class, 'productSameBrandView'])->name('product.details.same_brand');
Route::post('/product/details/related_products', [HomeController::class, 'productRelatedProductsView'])->name('product.details.related_products');
Route::post('/product/details/also_bought', [HomeController::class, 'productAlsoBoughtView'])->name('product.details.also_bought');
Route::get('/product/{slug}', [HomeController::class, 'product'])->name('product');
Route::post('/product/variant_price', [HomeController::class, 'variant_price'])->name('products.variant_price');
Route::get('/shop/{slug}', [HomeController::class, 'shop'])->name('shop.visit');
Route::get('/shop/{slug}/{type}', [HomeController::class, 'filter_shop'])->name('shop.visit.type');

Route::get('/cart', Cart::class)->name('cart');
Route::post('/cart/addtocart', [CartController::class, 'addToCart'])->name('cart.addToCart');
Route::post('/cart/removeFromCart', [CartController::class, 'removeFromCart'])->name('cart.removeFromCart');

// 

//Checkout Routes
Route::group(['prefix' => 'checkout'], function () {
    Route::get('/', Checkout::class)->name('checkout.checkout_page');
    Route::any('/delivery_info', [CheckoutController::class, 'store_shipping_info'])->name('checkout.store_shipping_infostore');
    Route::post('/payment_select', [CheckoutController::class, 'store_delivery_info'])->name('checkout.store_delivery_info');
    Route::get('/shipping_methods', [CheckoutController::class, 'get_shipping_methods'])->name('checkout.shipping_methods');

    Route::get('/order-confirmed/{order}', [CheckoutController::class, 'order_confirmed'])->name('order_confirmed');
    Route::get('/order-failed/{order}', [CheckoutController::class, 'order_failed'])->name('order_failed');
    Route::get('/payment/{order}', [CheckoutController::class, 'checkout'])->name('payment.checkout');
    Route::post('/get_pick_up_points', [HomeController::class, 'get_pick_up_points'])->name('shipping_info.get_pick_up_points');
    Route::get('/payment-select', [CheckoutController::class, 'get_payment_info'])->name('checkout.payment_info');
    Route::post('/apply_coupon_code', [CheckoutController::class, 'apply_coupon_code'])->name('checkout.apply_coupon_code');
    Route::post('/remove_coupon_code', [CheckoutController::class, 'remove_coupon_code'])->name('checkout.remove_coupon_code');
});

Route::group(['prefix' => 'enquiry'], function () {
    Route::get('/', [EnquiryContoller::class, 'index'])->name('enquiry.index');
    Route::post('/', [EnquiryContoller::class, 'submit']);
    Route::post('/add', [EnquiryContoller::class, 'add'])->name('enquiry.add');
    Route::post('/remove', [EnquiryContoller::class, 'remove'])->name('enquiry.remove');
    Route::post('/change_quantity', [EnquiryContoller::class, 'changeQuantity'])->name('enquiry.change_quantity');
});

Route::resource('subscribers', SubscriberController::class);

Route::get('/brands', [HomeController::class, 'all_brands'])->name('brands.all');
Route::get('/categories', [HomeController::class, 'all_categories'])->name('categories.all');
Route::get('/sellers', [HomeController::class, 'all_seller'])->name('sellers');
Route::get('/coupons', [HomeController::class, 'all_coupons'])->name('coupons.all');
Route::get('/inhouse', [HomeController::class, 'inhouse_products'])->name('inhouse.all');


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

// Route::group(['prefix' => 'seller', 'middleware' => ['seller', 'verified', 'user']], function () {
//     Route::get('/products', [HomeController::class, 'seller_product_list'])->name('seller.products');
//     Route::get('/product/upload', [HomeController::class, 'show_product_upload_form'])->name('seller.products.upload');
//     Route::get('/product/{id}/edit', [HomeController::class, 'show_product_edit_form'])->name('seller.products.edit');

//     Route::get('/reviews', [ReviewController::class, 'seller_reviews'])->name('reviews.seller');

//     //Upload
//     Route::any('/uploads', [AizUploadController::class, 'index'])->name('my_uploads.all');
//     Route::any('/uploads/new', [AizUploadController::class, 'create'])->name('my_uploads.new');
//     Route::any('/uploads/file-info', [AizUploadController::class, 'file_info'])->name('my_uploads.info');
//     Route::get('/uploads/destroy/{id}', [AizUploadController::class, 'destroy'])->name('my_uploads.destroy');
// });

Route::group(['middleware' => ['auth']], function () {
    Route::resource('orders', OrderController::class);
    Route::get('/orders/destroy/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('/orders/details', [OrderController::class, 'order_details'])->name('orders.details');
    Route::post('/orders/update_delivery_status', [OrderController::class, 'update_delivery_status'])->name('orders.update_delivery_status');
    Route::post('/orders/update_payment_status', [OrderController::class, 'update_payment_status'])->name('orders.update_payment_status');
    Route::post('/orders/update_tracking_code', [OrderController::class, 'update_tracking_code'])->name('orders.update_tracking_code');
});

//Address
Route::post('/get-city', [CityController::class, 'get_city'])->name('get-city');
Route::post('/get-states', [AddressController::class, 'getStates'])->name('get-state');
Route::post('/get-cities', [AddressController::class, 'getCities'])->name('get-city');

//Custom page
Route::get('/{slug}', [PageController::class, 'show_custom_page'])->name('custom-pages.show_custom_page');
