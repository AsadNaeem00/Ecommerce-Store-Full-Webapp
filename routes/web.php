<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    AuthController, DashboardController, ProductController, CategoryController,
    OrderController, ReviewController, SettingsController, PaymentSettingsController,
    HomepageController, PagesController
};
use App\Http\Controllers\Store\{
    HomeController, ProductController as StoreProductController,
    CartController, CheckoutController, PaymentCallbackController
};

// ══════════════════════════════════════════════════════
// ADMIN ROUTES
// ══════════════════════════════════════════════════════
Route::prefix('admin')->name('admin.')->group(function () {

    // Auth (no middleware)
    Route::get('login',  [AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::post('logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');

    // Protected admin routes
    Route::middleware(['auth', 'admin'])->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('profile',  [AuthController::class, 'profile'])->name('profile');
        Route::put('profile',  [AuthController::class, 'updateProfile'])->name('profile.update');

        // Products
        Route::resource('products', ProductController::class)->except(['show']);
        Route::delete('products/image/{image}', [ProductController::class, 'destroyImage'])->name('products.image.destroy');
        Route::post('products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');

        // Categories
        Route::get('categories',               [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories',              [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}',    [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Orders
        Route::get('orders',                                   [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}',                           [OrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{order}/status',                    [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::put('orders/{order}/payment-status',            [OrderController::class, 'updatePaymentStatus'])->name('orders.payment-status');

        // Reviews
        Route::get('reviews',                  [ReviewController::class, 'index'])->name('reviews.index');
        Route::put('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
        Route::put('reviews/{review}/reject',  [ReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('reviews/{review}',      [ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Settings
        Route::get('settings',  [SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings',  [SettingsController::class, 'update'])->name('settings.update');
        Route::get('settings/branding', [SettingsController::class, 'branding'])->name('settings.branding');

        // Payment Settings
        Route::get('payments',                    [PaymentSettingsController::class, 'index'])->name('payments.index');
        Route::put('payments/{gateway}',          [PaymentSettingsController::class, 'update'])->name('payments.update');

        // Homepage Builder
        Route::get('homepage',                   [HomepageController::class, 'index'])->name('homepage.index');
        Route::put('homepage/sections',          [HomepageController::class, 'updateSections'])->name('homepage.sections');
        Route::post('homepage/slider',           [HomepageController::class, 'addSlide'])->name('homepage.slider.add');
        Route::delete('homepage/slider/{slide}', [HomepageController::class, 'deleteSlide'])->name('homepage.slider.delete');
        Route::post('homepage/slider/order',     [HomepageController::class, 'updateSlideOrder'])->name('homepage.slider.order');

        // Pages
        Route::get('pages',                [PagesController::class, 'index'])->name('pages.index');
        Route::get('pages/{page}/edit',    [PagesController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}',         [PagesController::class, 'update'])->name('pages.update');
    });
});

// ══════════════════════════════════════════════════════
// STORE FRONTEND ROUTES
// ══════════════════════════════════════════════════════
Route::middleware(['maintenance'])->group(function () {

    // Home
    Route::get('/',        [HomeController::class, 'index'])->name('store.home');
    Route::get('/contact', [HomeController::class, 'contact'])->name('store.contact');
    Route::post('/contact',[HomeController::class, 'contact'])->name('store.contact.send');
    Route::get('/page/{slug}', [HomeController::class, 'page'])->name('store.page');

    // Products
    Route::get('/products',          [StoreProductController::class, 'index'])->name('store.products.index');
    Route::get('/products/{slug}',   [StoreProductController::class, 'show'])->name('store.products.show');
    Route::post('/products/{product}/reviews', [StoreProductController::class, 'storeReview'])->name('store.products.reviews');

    // Cart
    Route::get('/cart',    [CartController::class, 'index'])->name('store.cart');
    Route::post('/cart/add',    [CartController::class, 'add'])->name('store.cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('store.cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('store.cart.remove');
    Route::post('/cart/clear',  [CartController::class, 'clear'])->name('store.cart.clear');
    Route::get('/cart/mini',    [CartController::class, 'mini'])->name('store.cart.mini');

    // Checkout
    Route::get('/checkout',        [CheckoutController::class, 'index'])->name('store.checkout.index');
    Route::post('/checkout/place', [CheckoutController::class, 'placeOrder'])->name('store.checkout.place');
    Route::get('/order/{order}/confirmation', [CheckoutController::class, 'confirmation'])->name('store.order.confirmation');

    // Payment Callbacks
    Route::get('/payment/{gateway}/success',  [PaymentCallbackController::class, 'success'])->name('store.payment.success');
    Route::get('/payment/{gateway}/callback', [PaymentCallbackController::class, 'success'])->name('store.payment.callback');
    Route::post('/payment/{gateway}/webhook', [PaymentCallbackController::class, 'webhook'])->name('store.payment.webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
});
