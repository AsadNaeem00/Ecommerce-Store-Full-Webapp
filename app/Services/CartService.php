<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    private const SESSION_KEY = 'ecommerce_cart';

    public function get(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function add(int $productId, int $quantity = 1): array
    {
        $product = Product::with('category')->active()->find($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found.'];
        }
        if (!$product->isInStock()) {
            return ['success' => false, 'message' => 'Product is out of stock.'];
        }

        $cart = $this->get();
        $key  = "product_{$productId}";

        if (isset($cart[$key])) {
            $newQty = $cart[$key]['quantity'] + $quantity;
            // Check stock
            if ($product->track_quantity && $newQty > $product->stock_quantity) {
                return ['success' => false, 'message' => "Only {$product->stock_quantity} items available."];
            }
            $cart[$key]['quantity'] = $newQty;
            $cart[$key]['subtotal'] = round($product->current_price * $newQty, 2);
        } else {
            $cart[$key] = [
                'product_id'   => $product->id,
                'name'         => $product->name,
                'sku'          => $product->sku,
                'price'        => $product->current_price,
                'quantity'     => $quantity,
                'subtotal'     => round($product->current_price * $quantity, 2),
                'image'        => $product->main_image_url,
                'slug'         => $product->slug,
                'category_id'  => $product->category_id,
                'category_code'=> $product->category->category_code ?? '0000',
                'stock'        => $product->stock_quantity,
            ];
        }

        Session::put(self::SESSION_KEY, $cart);
        return ['success' => true, 'message' => 'Item added to cart.', 'count' => $this->count()];
    }

    public function update(int $productId, int $quantity): array
    {
        $cart = $this->get();
        $key  = "product_{$productId}";

        if (!isset($cart[$key])) {
            return ['success' => false, 'message' => 'Item not in cart.'];
        }

        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        $product = Product::find($productId);
        if ($product && $product->track_quantity && $quantity > $product->stock_quantity) {
            return ['success' => false, 'message' => "Only {$product->stock_quantity} items available."];
        }

        $cart[$key]['quantity'] = $quantity;
        $cart[$key]['subtotal'] = round($cart[$key]['price'] * $quantity, 2);
        Session::put(self::SESSION_KEY, $cart);

        return ['success' => true, 'subtotal' => $cart[$key]['subtotal'], 'cart_total' => $this->total()];
    }

    public function remove(int $productId): array
    {
        $cart = $this->get();
        unset($cart["product_{$productId}"]);
        Session::put(self::SESSION_KEY, $cart);
        return ['success' => true, 'message' => 'Item removed.', 'count' => $this->count()];
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum(array_column($this->get(), 'quantity'));
    }

    public function subtotal(): float
    {
        return round(array_sum(array_column($this->get(), 'subtotal')), 2);
    }

    public function shippingCost(): float
    {
        $subtotal    = $this->subtotal();
        $freeMin     = (float) \App\Models\Setting::get('free_shipping_min', 2000);
        $defaultCost = (float) \App\Models\Setting::get('default_shipping_cost', 200);
        return $subtotal >= $freeMin ? 0 : $defaultCost;
    }

    public function total(): float
    {
        return round($this->subtotal() + $this->shippingCost(), 2);
    }

    public function isEmpty(): bool
    {
        return empty($this->get());
    }

    public function toOrderItems(): array
    {
        $items = [];
        foreach ($this->get() as $item) {
            $items[] = [
                'product_id'   => $item['product_id'],
                'product_name' => $item['name'],
                'product_sku'  => $item['sku'],
                'unit_price'   => $item['price'],
                'quantity'     => $item['quantity'],
                'total_price'  => $item['subtotal'],
                'product_image'=> $item['image'],
            ];
        }
        return $items;
    }

    /**
     * Generate unique order number: [CategoryID]-[ProductID]-[Timestamp]
     */
    public function generateOrderNumber(): string
    {
        $cart       = $this->get();
        $firstItem  = reset($cart);
        $categoryCode = $firstItem['category_code'] ?? '0000';
        $productId    = str_pad($firstItem['product_id'] ?? '0', 4, '0', STR_PAD_LEFT);
        $suffix       = strtoupper(substr(md5(uniqid()), 0, 6));
        return "{$categoryCode}-{$productId}-{$suffix}";
    }
}
