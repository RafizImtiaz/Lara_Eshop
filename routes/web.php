<?php
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\admin\Adminlogincontroller;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\Categorycontroller;
use App\Http\Controllers\admin\Homecontroller;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProductImageController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\TempImagescontroller;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

    // Route::get('/test', function () {
    //     orderEmail(13);
    // });



Route::get('/',[FrontController::class,'index'])->name('front.home');
Route::get('/shop/{categorySlug?}/{subCategorySlug?}',[ShopController::class,'index'])->name('front.shop');
Route::get('/product/{slug}',[ShopController::class,'product'])->name('front.product');
Route::get('/cart',[CartController::class,'cart'])->name('front.cart');
Route::post('/add-to-cart',[CartController::class,'addToCart'])->name('front.addToCart');
Route::post('/update-cart',[CartController::class,'updateCart'])->name('front.updateCart');
Route::post('/delete-item',[CartController::class,'deleteItem'])->name('front.deleteItem.cart');
Route::get('/checkout',[CartController::class,'checkout'])->name('front.checkout');
Route::post('/process-checkout',[CartController::class,'processCheckout'])->name('front.processCheckout');
Route::get('/thanks/{orderId}',[CartController::class,'thankyou'])->name('front.thankyou');
Route::post('/get-order-summary',[CartController::class,'getOrderSummary'])->name('front.getOrderSummary');


//account
Route::group(['prefix'=>'account'], function () {
    Route::group(['middleware'=>'auth'], function () {
        Route::get('/profile',[AuthController::class,'profile'])->name('account.profile');
        Route::get('/logout',[AuthController::class,'logout'])->name('account.logout');
        Route::get('/my-orders',[AuthController::class,'orders'])->name('account.orders');
        Route::get('/order-detail/{orderId}',[AuthController::class,'orderDetail'])->name('account.orderDetail');

    });

    Route::group(['middleware'=>'guest'], function () {
        Route::get('/login',[AuthController::class,'login'])->name('account.login');
        Route::post('/authenticate',[AuthController::class,'authenticate'])->name('account.authenticate');

        Route::get('/register',[AuthController::class,'register'])->name('account.register');
        Route::post('/process-register',[AuthController::class,'processRegister'])->name('account.processRegister');
        

    });

});




Route::group(['prefix'=>'admin'], function () {
    Route::group(['middleware'=>'admin.auth'], function () {

        Route::get('/dashboard',[Homecontroller::class,'index'])->name('admin.dashboard');
        Route::get('/logout',[Homecontroller::class,'logout'])->name('admin.logout');

        //categories
        Route::get('/categories',[Categorycontroller::class,'index'])->name('categories.index');
        Route::get('/categories/create',[Categorycontroller::class,'create'])->name('categories.create');
        Route::post('/categories',[Categorycontroller::class,'store'])->name('categories.store');
        Route::get('/categories/{category}/edit',[Categorycontroller::class,'edit'])->name('categories.edit');
        Route::put('/categories/{category}',[Categorycontroller::class,'update'])->name('categories.update');
        Route::delete('/categories/{category}',[Categorycontroller::class,'destroy'])->name('categories.delete');



        //Sub_Categories
        Route::get('/sub-categories',[SubCategoryController::class,'index'])->name('sub-categories.index');
        Route::get('/sub-categories/create',[SubCategoryController::class,'create'])->name('sub-categories.create');
        Route::post('/sub-categories',[SubCategoryController::class,'store'])->name('sub-categories.store');
        Route::get('/sub-categories/{subCategory}/edit',[SubCategoryController::class,'edit'])->name('sub-categories.edit');
        Route::put('/sub-categories/{subCategory}',[SubCategoryController::class,'update'])->name('sub-categories.update');
        Route::delete('/sub-categories/{category}',[SubCategoryController::class,'destroy'])->name('sub-categories.delete');



        //Brands
        Route::get('/Brands/create',[BrandController::class,'create'])->name('Brands.create');
        Route::post('/Brands',[BrandController::class,'store'])->name('Brands.store');
        Route::get('/Brands',[BrandController::class,'index'])->name('Brands.index');
        Route::get('/Brands/{brand}/edit',[BrandController::class,'edit'])->name('Brands.edit');
        Route::put('/Brands/{brand}',[BrandController::class,'update'])->name('Brands.update');
        Route::delete('/Brands/{brand}',[BrandController::class,'destroy'])->name('Brands.delete');


        //Product
        Route::get('/products/create',[ProductController::class,'create'])->name('products.create');
        Route::post('/products',[ProductController::class,'store'])->name('products.store');
        Route::get('/products',[ProductController::class,'index'])->name('products.index');
        Route::get('/product-subcategories',[ProductSubCategoryController::class,'index'])->name('product-subcategories.index');
        Route::get('/products/{product}/edit',[ProductController::class,'edit'])->name('products.edit');
        Route::put('/products/{product}',[ProductController::class,'update'])->name('products.update');
        Route::delete('/products/{product}',[ProductController::class,'des#troy'])->name('products.delete');
        Route::get('/get-products',[ProductController::class,'getProducts'])->name('products.getProducts');


        //shipping
        Route::get('/shipping/create',[ShippingController::class,'create'])->name('shipping.create');
        Route::post('/shipping',[ShippingController::class,'store'])->name('shipping.store');
        Route::get('/shipping/{id}',[ShippingController::class,'edit'])->name('shipping.edit');
        Route::put('/shipping/{id}',[ShippingController::class,'update'])->name('shipping.update');
        Route::delete('/shipping/{id}',[ShippingController::class,'destroy'])->name('shipping.delete');


        //Orders
        Route::get('/orders',[OrderController::class,'index'])->name('orders.index');
        Route::get('/orders/{id}',[OrderController::class,'detail'])->name('orders.detail');
        Route::post('/order/change-status/{id}',[OrderController::class,'changeOrderStatus'])->name('orders.changeOrderStatus');
        Route::post('/order/send-email/{id}',[OrderController::class,'sendInvoiceEmail'])->name('orders.sendInvoiceEmail');



        Route::post('/product-images/update',[ProductImageController::class,'update'])->name('product-images.update');
        Route::delete('/product-images',[ProductImageController::class,'destroy'])->name('product-images.destroy');
        //temp-images
        Route::post('/upload-temp-image',[TempImagescontroller::class,'create'])->name('temp-image.create');

        

        Route::get('/getslug',function(Request $request){
            $slug = '';
            if(!empty($request->title)){
               $slug= Str::slug($request->title);
            }
            return response()->json([
                    'status' => true,
                    'slug' => $slug,
            ]);

        })->name('getslug');

    });
    Route::group(['middleware'=>'admin.guest'], function () {

        Route::get('/login',[Adminlogincontroller::class,'index'])->name('admin.login');
        Route::post('/authenticate',[Adminlogincontroller::class,'authenticate'])->name('admin.authenticate');

    });


    
});
