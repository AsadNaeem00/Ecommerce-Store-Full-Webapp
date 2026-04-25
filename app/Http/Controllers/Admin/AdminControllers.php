<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Category, Order, OrderStatusHistory, Review, Setting, PaymentConfig, HomepageSection, SliderImage, Page};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage};
use Illuminate\Support\Str;

// ══════════════════════════════════════════════════════
// CATEGORY CONTROLLER
// ══════════════════════════════════════════════════════
class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->latest()->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
            'parent_id'   => 'nullable|exists:categories,id',
            'sort_order'  => 'nullable|integer',
        ]);

        $data = $request->only('name','description','parent_id','sort_order');
        $data['slug']      = Str::slug($request->name);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['show_in_nav'] = $request->boolean('show_in_nav', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);
        return back()->with('success', "Category \"{$category->name}\" created. Code: {$category->category_code}");
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);

        $data = $request->only('name','description','parent_id','sort_order');
        $data['is_active']   = $request->boolean('is_active');
        $data['show_in_nav'] = $request->boolean('show_in_nav');

        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);
        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with products. Reassign products first.');
        }
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}

// ══════════════════════════════════════════════════════
// ORDER CONTROLLER
// ══════════════════════════════════════════════════════
class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();

        if ($request->filled('status'))  { $query->where('status', $request->status); }
        if ($request->filled('payment')) { $query->where('payment_method', $request->payment); }
        if ($request->filled('search'))  {
            $s = $request->search;
            $query->where(fn($q) => $q->where('order_number','like',"%{$s}%")
                                      ->orWhere('customer_name','like',"%{$s}%")
                                      ->orWhere('customer_phone','like',"%{$s}%")
                                      ->orWhere('customer_email','like',"%{$s}%"));
        }

        $orders = $query->paginate(20)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items', 'statusHistory');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
            'note'   => 'nullable|string|max:500',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        OrderStatusHistory::create([
            'order_id'   => $order->id,
            'status'     => $request->status,
            'note'       => $request->note ?? "Status changed from {$oldStatus} to {$request->status}",
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Order status updated to ' . ucfirst($request->status));
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate(['payment_status' => 'required|in:unpaid,paid,partially_paid,refunded']);
        $order->update(['payment_status' => $request->payment_status]);
        return back()->with('success', 'Payment status updated.');
    }
}

// ══════════════════════════════════════════════════════
// REVIEW CONTROLLER
// ══════════════════════════════════════════════════════
class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query  = Review::with('product')->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        $reviews = $query->paginate(20)->withQueryString();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);
        return back()->with('success', 'Review approved.');
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);
        return back()->with('success', 'Review rejected.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }
}

// ══════════════════════════════════════════════════════
// SETTINGS CONTROLLER
// ══════════════════════════════════════════════════════
class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name'   => 'required|string|max:100',
            'store_email'  => 'required|email',
            'logo'         => 'nullable|image|max:2048|mimes:jpg,jpeg,png,svg,webp',
            'favicon'      => 'nullable|image|max:512|mimes:ico,png',
            'background_image' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,gif,webp',
            'color_primary'    => 'nullable|string|max:7',
            'color_secondary'  => 'nullable|string|max:7',
        ]);

        $data = $request->except(['_token', '_method', 'logo', 'favicon', 'background_image']);
        $data['background_animated'] = $request->boolean('background_animated') ? '1' : '0';
        $data['maintenance_mode']    = $request->boolean('maintenance_mode')    ? '1' : '0';

        foreach (['logo', 'favicon', 'background_image'] as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $old = Setting::get($fileKey);
                if ($old) Storage::disk('public')->delete($old);
                $data[$fileKey] = $request->file($fileKey)->store('branding', 'public');
            }
        }

        Setting::setMany($data);
        return back()->with('success', 'Settings saved successfully.');
    }

    public function branding()
    {
        $settings = Setting::getGroup('branding');
        return view('admin.settings.branding', compact('settings'));
    }
}

// ══════════════════════════════════════════════════════
// PAYMENT SETTINGS CONTROLLER
// ══════════════════════════════════════════════════════
class PaymentSettingsController extends Controller
{
    public function index()
    {
        $configs = PaymentConfig::all()->keyBy('gateway');
        return view('admin.payments.index', compact('configs'));
    }

    public function update(Request $request, string $gateway)
    {
        $request->validate([
            'is_enabled'   => 'boolean',
            'is_test_mode' => 'boolean',
            'merchant_id'  => 'nullable|string|max:200',
            'api_key'      => 'nullable|string|max:500',
            'api_secret'   => 'nullable|string|max:500',
        ]);

        $config = PaymentConfig::forGateway($gateway);
        if (!$config) return back()->with('error', 'Gateway not found.');

        $data = [
            'is_enabled'   => $request->boolean('is_enabled'),
            'is_test_mode' => $request->boolean('is_test_mode'),
            'merchant_id'  => $request->merchant_id,
        ];

        if ($request->filled('api_key'))    $data['api_key']    = $request->api_key;
        if ($request->filled('api_secret')) $data['api_secret'] = $request->api_secret;

        // Gateway-specific extra config
        $extra = $config->extra_config ?? [];
        foreach (['hash_key','return_url','account_number','publishable_key','webhook_secret'] as $field) {
            if ($request->filled($field)) $extra[$field] = $request->input($field);
        }
        $data['extra_config'] = $extra;

        $config->update($data);
        return back()->with('success', ucfirst($gateway) . ' payment settings saved.');
    }
}

// ══════════════════════════════════════════════════════
// HOMEPAGE BUILDER CONTROLLER
// ══════════════════════════════════════════════════════
class HomepageController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('sort_order')->get();
        $sliders  = SliderImage::orderBy('sort_order')->get();
        return view('admin.homepage.index', compact('sections', 'sliders'));
    }

    public function updateSections(Request $request)
    {
        $sections = $request->input('sections', []);
        foreach ($sections as $key => $data) {
            HomepageSection::where('section_key', $key)->update([
                'is_enabled' => isset($data['is_enabled']),
                'sort_order' => $data['sort_order'] ?? 0,
                'title'      => $data['title'] ?? '',
                'subtitle'   => $data['subtitle'] ?? null,
            ]);
        }
        return back()->with('success', 'Homepage sections updated.');
    }

    public function addSlide(Request $request)
    {
        $request->validate([
            'image'     => 'required|image|max:5120|mimes:jpg,jpeg,png,webp',
            'title'     => 'nullable|string|max:200',
            'subtitle'  => 'nullable|string|max:500',
            'cta_text'  => 'nullable|string|max:50',
            'cta_url'   => 'nullable|string|max:255',
        ]);

        $path = $request->file('image')->store('slider', 'public');
        SliderImage::create([
            'image_path' => $path,
            'title'      => $request->title,
            'subtitle'   => $request->subtitle,
            'cta_text'   => $request->cta_text ?? 'Shop Now',
            'cta_url'    => $request->cta_url  ?? '/products',
            'sort_order' => SliderImage::count(),
            'is_active'  => true,
        ]);
        return back()->with('success', 'Slide added successfully.');
    }

    public function deleteSlide(SliderImage $slide)
    {
        Storage::disk('public')->delete($slide->image_path);
        $slide->delete();
        return back()->with('success', 'Slide deleted.');
    }

    public function updateSlideOrder(Request $request)
    {
        foreach ($request->input('order', []) as $index => $id) {
            SliderImage::where('id', $id)->update(['sort_order' => $index]);
        }
        return response()->json(['success' => true]);
    }
}

// ══════════════════════════════════════════════════════
// PAGES CONTROLLER
// ══════════════════════════════════════════════════════
class PagesController extends Controller
{
    public function index()
    {
        $pages = Page::latest()->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate(['title'=>'required|string|max:200','content'=>'required|string']);
        $page->update($request->only('title','content','meta_title','meta_description'));
        return back()->with('success', 'Page updated.');
    }
}
