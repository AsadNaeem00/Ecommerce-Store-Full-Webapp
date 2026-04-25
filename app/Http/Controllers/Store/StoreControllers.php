<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\{Product, Category, Order, OrderItem, OrderStatusHistory, Review, SliderImage, HomepageSection, Setting, Page};
use App\Services\{CartService, Payment\PaymentManager};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ══════════════════════════════════════════════════════
// HOME CONTROLLER
// ══════════════════════════════════════════════════════
class HomeController extends Controller
{
    public function index()
    {
        $sections        = HomepageSection::enabled()->get()->keyBy('section_key');
        $sliders         = SliderImage::active()->get();
        $featuredProducts= Product::with('category')->active()->featured()->take(8)->get();
        $newArrivals     = Product::with('category')->active()->latest()->take(8)->get();
        $categories      = Category::active()->topLevel()->where('show_in_nav', true)->withCount('products')->take(8)->get();

        return view('store.home.index', compact('sections','sliders','featuredProducts','newArrivals','categories'));
    }

    public function page(string $slug)
    {
        $page = Page::findBySlug($slug);
        if (!$page) abort(404);
        return view('store.pages.show', compact('page'));
    }

    public function contact(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'name'    => 'required|string|max:100',
                'email'   => 'required|email',
                'message' => 'required|string|max:2000',
            ]);
            // In production: queue email to store_email setting
            return back()->with('success', 'Your message has been sent! We will get back to you shortly.');
        }
        return view('store.pages.contact');
    }
}

// ══════════════════════════════════════════════════════
// PRODUCT CONTROLLER
// ══════════════════════════════════════════════════════
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->active();

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }
        if ($request->filled('q')) {
            $s = $request->q;
            $query->where(fn($q) => $q->where('name','like',"%{$s}%")->orWhere('short_description','like',"%{$s}%"));
        }
        if ($request->filled('sort')) {
            match ($request->sort) {
                'price_asc'  => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                'newest'     => $query->latest(),
                'featured'   => $query->orderByDesc('is_featured'),
                default      => $query->latest(),
            };
        } else {
            $query->orderByDesc('is_featured')->latest();
        }

        if ($request->filled('min_price')) $query->where('price', '>=', $request->min_price);
        if ($request->filled('max_price')) $query->where('price', '<=', $request->max_price);

        $products   = $query->paginate(12)->withQueryString();
        $categories = Category::active()->withCount(['products' => fn($q) => $q->where('is_active',true)])->get();

        return view('store.products.index', compact('products', 'categories'));
    }

    public function show(string $slug)
    {
        $product = Product::with(['category','images','reviews' => fn($q) => $q->approved()->latest()])->where('slug',$slug)->active()->firstOrFail();
        $related = Product::with('category')->active()->where('category_id', $product->category_id)->where('id','!=',$product->id)->take(4)->get();

        return view('store.products.show', compact('product', 'related'));
    }

    public function storeReview(Request $request, Product $product)
    {
        $request->validate([
            'reviewer_name'  => 'required|string|max:100',
            'reviewer_email' => 'required|email',
            'rating'         => 'required|integer|between:1,5',
            'title'          => 'nullable|string|max:100',
            'body'           => 'required|string|max:2000',
        ]);

        // Prevent duplicate review from same email
        if (Review::where('product_id', $product->id)->where('reviewer_email', $request->reviewer_email)->exists()) {
            return back()->with('error', 'You have already submitted a review for this product.');
        }

        Review::create([
            'product_id'    => $product->id,
            'reviewer_name' => e($request->reviewer_name),
            'reviewer_email'=> $request->reviewer_email,
            'rating'        => $request->rating,
            'title'         => $request->title ? e($request->title) : null,
            'body'          => e($request->body),
            'ip_address'    => $request->ip(),
        ]);

        return back()->with('success', 'Thank you! Your review will appear after moderation.');
    }
}

// ══════════════════════════════════════════════════════
// CART CONTROLLER
// ══════════════════════════════════════════════════════
class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        return view('store.cart.index', ['cart' => $this->cart->get(), 'cartService' => $this->cart]);
    }

    public function add(Request $request)
    {
        $request->validate(['product_id' => 'required|integer|exists:products,id', 'quantity' => 'nullable|integer|min:1|max:100']);
        $result = $this->cart->add($request->product_id, $request->integer('quantity', 1));

        if ($request->wantsJson()) return response()->json($result);
        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function update(Request $request)
    {
        $request->validate(['product_id' => 'required|integer', 'quantity' => 'required|integer|min:0']);
        $result = $this->cart->update($request->product_id, $request->quantity);
        if ($request->wantsJson()) return response()->json($result);
        return back()->with($result['success'] ? 'success' : 'error', $result['message'] ?? 'Updated.');
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);
        $result = $this->cart->remove($request->product_id);
        if ($request->wantsJson()) return response()->json($result);
        return back()->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        $this->cart->clear();
        return redirect()->route('store.cart')->with('success', 'Cart cleared.');
    }

    public function mini()
    {
        return response()->json([
            'items'    => $this->cart->get(),
            'count'    => $this->cart->count(),
            'subtotal' => $this->cart->subtotal(),
            'total'    => $this->cart->total(),
            'currency' => Setting::get('currency_symbol', '₨'),
        ]);
    }
}

// ══════════════════════════════════════════════════════
// CHECKOUT CONTROLLER
// ══════════════════════════════════════════════════════
class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('store.cart')->with('error', 'Your cart is empty.');
        }

        $gateways    = PaymentManager::availableGateways();
        $gatewayInfo = PaymentManager::gatewayLabels();
        $provinces   = ['Islamabad ICT','Punjab','KPK','Sindh','Balochistan','Tribal Areas'];

        return view('store.checkout.index', [
            'cart'        => $this->cart->get(),
            'cartService' => $this->cart,
            'gateways'    => $gateways,
            'gatewayInfo' => $gatewayInfo,
            'provinces'   => $provinces,
        ]);
    }

    public function placeOrder(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('store.cart')->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'customer_name'     => 'required|string|max:100',
            'customer_email'    => 'required|email',
            'customer_phone'    => 'required|string|max:20',
            'shipping_address'  => 'required|string|max:500',
            'shipping_city'     => 'required|string|max:100',
            'shipping_province' => 'required|string|max:100',
            'payment_method'    => 'required|in:cod,easypaisa,jazzcash,card',
            'terms'             => 'accepted',
        ]);

        try {
            DB::beginTransaction();

            $orderNumber = $this->cart->generateOrderNumber();

            $order = Order::create([
                'order_number'      => $orderNumber,
                'customer_name'     => e($request->customer_name),
                'customer_email'    => $request->customer_email,
                'customer_phone'    => $request->customer_phone,
                'shipping_address'  => e($request->shipping_address),
                'shipping_city'     => e($request->shipping_city),
                'shipping_province' => $request->shipping_province,
                'shipping_postal_code'=> $request->shipping_postal_code,
                'subtotal'          => $this->cart->subtotal(),
                'shipping_cost'     => $this->cart->shippingCost(),
                'discount_amount'   => 0,
                'total_amount'      => $this->cart->total(),
                'payment_method'    => $request->payment_method,
                'payment_status'    => 'unpaid',
                'customer_notes'    => $request->customer_notes ? e($request->customer_notes) : null,
                'ip_address'        => $request->ip(),
            ]);

            // Create order items & deduct stock
            foreach ($this->cart->toOrderItems() as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
                if ($item['quantity']) {
                    \App\Models\Product::where('id', $item['product_id'])
                        ->where('track_quantity', true)
                        ->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Initial status history
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'pending',
                'note'       => 'Order placed by customer.',
                'created_at' => now(),
            ]);

            DB::commit();

            // Handle payment gateway
            $gateway = PaymentManager::gateway($request->payment_method);
            $paymentData = [
                'order_number' => $order->order_number,
                'order_id'     => $order->id,
                'amount'       => $order->total_amount,
                'email'        => $order->customer_email,
                'phone'        => $order->customer_phone,
                'items'        => $this->cart->toOrderItems(),
                'shipping_cost'=> $order->shipping_cost,
            ];

            $paymentResult = $gateway->initiatePayment($paymentData);

            // Clear cart after successful order
            $this->cart->clear();

            // Store order number in session for confirmation page
            session(['last_order_number' => $order->order_number]);

            if ($paymentResult['success']) {
                if ($paymentResult['method'] === 'COD') {
                    return redirect()->route('store.order.confirmation', ['order' => $order->order_number]);
                }
                if ($paymentResult['method'] === 'REDIRECT') {
                    return redirect()->away($paymentResult['redirect_url']);
                }
                // POST redirect (EasyPaisa / JazzCash)
                return view('store.checkout.payment-redirect', [
                    'redirect_url' => $paymentResult['redirect_url'],
                    'form_data'    => $paymentResult['form_data'],
                    'gateway'      => $request->payment_method,
                ]);
            }

            return redirect()->route('store.order.confirmation', ['order' => $order->order_number])
                ->with('warning', 'Order placed but payment could not be initiated. Please use COD or try again.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order placement failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to place order. Please try again.')->withInput();
        }
    }

    public function confirmation(string $order)
    {
        $order = Order::where('order_number', $order)->with('items')->firstOrFail();
        return view('store.checkout.confirmation', compact('order'));
    }
}

// ══════════════════════════════════════════════════════
// PAYMENT CALLBACK CONTROLLER
// ══════════════════════════════════════════════════════
class PaymentCallbackController extends Controller
{
    public function success(Request $request, string $gateway)
    {
        try {
            $gw     = PaymentManager::gateway($gateway);
            $result = $gw->verifyPayment($request->all());

            if ($result['success']) {
                $order = Order::where('order_number', $result['order_number'])->first();
                if ($order) {
                    $order->update([
                        'payment_status'   => 'paid',
                        'payment_reference'=> $result['transaction_id'] ?? null,
                        'status'           => 'confirmed',
                    ]);
                    OrderStatusHistory::create([
                        'order_id'   => $order->id,
                        'status'     => 'confirmed',
                        'note'       => "Payment confirmed via {$gateway}. Ref: " . ($result['transaction_id'] ?? 'N/A'),
                        'created_at' => now(),
                    ]);
                    return redirect()->route('store.order.confirmation', ['order' => $order->order_number])
                        ->with('success', 'Payment successful! Your order has been confirmed.');
                }
            }

            return redirect()->route('store.checkout.index')
                ->with('error', 'Payment could not be verified. Contact support with your order details.');
        } catch (\Exception $e) {
            \Log::error("Payment callback error [{$gateway}]: " . $e->getMessage());
            return redirect()->route('store.home')->with('error', 'Payment processing error.');
        }
    }

    public function webhook(Request $request, string $gateway)
    {
        try {
            $gw     = PaymentManager::gateway($gateway);
            $result = $gw->callbackHandler($request->all());

            if ($result['success'] && isset($result['order_number'])) {
                Order::where('order_number', $result['order_number'])->update([
                    'payment_status'   => 'paid',
                    'payment_reference'=> $result['transaction_id'] ?? null,
                    'status'           => 'confirmed',
                ]);
            }
            return response()->json(['received' => true]);
        } catch (\Exception $e) {
            \Log::error("Webhook error [{$gateway}]: " . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }
}
