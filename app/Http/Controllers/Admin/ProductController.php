<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Product, Category, ProductImage};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name','like',"%{$s}%")->orWhere('sku','like',"%{$s}%"));
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products   = $query->paginate(15)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'category_id'        => 'required|exists:categories,id',
            'price'              => 'required|numeric|min:0',
            'sale_price'         => 'nullable|numeric|lt:price',
            'stock_quantity'     => 'required|integer|min:0',
            'short_description'  => 'nullable|string|max:500',
            'description'        => 'nullable|string',
            'weight'             => 'nullable|numeric|min:0',
            'dimensions'         => 'nullable|string|max:50',
            'is_active'          => 'boolean',
            'is_featured'        => 'boolean',
            'track_quantity'     => 'boolean',
            'allow_backorder'    => 'boolean',
            'main_image'         => 'nullable|image|max:4096|mimes:jpg,jpeg,png,webp',
            'gallery.*'          => 'nullable|image|max:4096|mimes:jpg,jpeg,png,webp',
            'tags'               => 'nullable|string',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active']     = $request->boolean('is_active', true);
        $validated['is_featured']   = $request->boolean('is_featured');
        $validated['track_quantity']= $request->boolean('track_quantity', true);
        $validated['allow_backorder']= $request->boolean('allow_backorder');
        $validated['slug']           = Str::slug($request->name);

        if ($request->hasFile('main_image')) {
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        if ($request->filled('tags')) {
            $validated['tags'] = array_map('trim', explode(',', $request->tags));
        }

        $product = Product::create($validated);

        // Gallery images
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $index => $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'alt_text'   => $product->name,
                    'sort_order' => $index,
                    'is_primary' => $index === 0 && !$request->hasFile('main_image'),
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', "Product \"{$product->name}\" created. SKU: {$product->sku}");
    }

    public function edit(Product $product)
    {
        $product->load('images', 'category');
        $categories = Category::active()->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'category_id'        => 'required|exists:categories,id',
            'price'              => 'required|numeric|min:0',
            'sale_price'         => 'nullable|numeric|lt:price',
            'stock_quantity'     => 'required|integer|min:0',
            'short_description'  => 'nullable|string|max:500',
            'description'        => 'nullable|string',
            'is_active'          => 'boolean',
            'is_featured'        => 'boolean',
            'main_image'         => 'nullable|image|max:4096|mimes:jpg,jpeg,png,webp',
            'gallery.*'          => 'nullable|image|max:4096|mimes:jpg,jpeg,png,webp',
        ]);

        $validated['is_active']   = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('main_image')) {
            if ($product->main_image) Storage::disk('public')->delete($product->main_image);
            $validated['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        if ($request->filled('tags')) {
            $validated['tags'] = array_map('trim', explode(',', $request->tags));
        }

        $product->update($validated);

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $index => $image) {
                $path = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $product->images()->count() + $index,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    public function destroyImage(ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        return response()->json(['success' => true]);
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        return response()->json(['featured' => $product->is_featured]);
    }
}
