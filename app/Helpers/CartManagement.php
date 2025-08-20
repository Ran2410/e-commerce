<?php

namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Facades\Cookie;

class CartManagement
{
    // Add item to cart
    static public function addItemToCart($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();

        $existing_item = null;

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $existing_item = $key;
                break;
            }
        }

        if ($existing_item !== null) {
            $cart_items[$existing_item]['quantity']++;
            $cart_items[$existing_item]['total_amount'] = $cart_items[$existing_item]['quantity'] *
                $cart_items[$existing_item]['unit_amount'];
        } else {
            $product = Product::where('id', $product_id)->first(['id', 'name', 'price', 'images']);
            if ($product) {
                $cart_items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->images[0] ?? null,
                    'quantity' => 1,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price
                ];
            }
        }

        self::addCartItemsToCookie($cart_items);
        return $cart_items; // Return the full cart items array instead of just count
    }

    // Add item to cart with qty
    static public function addItemToCartWithQty($product_id, $qty = 1)
    {
        $cart_items = self::getCartItemsFromCookie();

        $existing_item = null;

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $existing_item = $key;
                break;
            }
        }

        if ($existing_item !== null) {
            $cart_items[$existing_item]['quantity'] = $qty;
            $cart_items[$existing_item]['total_amount'] = $cart_items[$existing_item]['quantity'] *
                $cart_items[$existing_item]['unit_amount'];
        } else {
            $product = Product::where('id', $product_id)->first(['id', 'name', 'price', 'images']);
            if ($product) {
                $cart_items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->images[0] ?? null,
                    'quantity' => $qty,
                    'unit_amount' => $product->price,
                    'total_amount' => $product->price * $qty
                ];
            }
        }

        self::addCartItemsToCookie($cart_items);
        return $cart_items;
    }

    // Remove item from cart
    static public function removeCartItem($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($cart_items[$key]);
            }
        }

        // Reset array keys to prevent JSON encoding issues
        $cart_items = array_values($cart_items);
        
        self::addCartItemsToCookie($cart_items);
        return $cart_items;
    }

    // Add cart to cookie
    static public function addCartItemsToCookie($cart_items)
    {
        Cookie::queue('cart_items', json_encode($cart_items), 60 * 24 * 30);
    }

    // Clear cart items from cookie
    static public function clearCartItems()
    {
        Cookie::queue(Cookie::forget('cart_items'));
        return []; // Return empty array for consistency
    }

    // Get all cart items from cookie (with improved validation)
    static public function getCartItemsFromCookie()
    {
        $cart_items = Cookie::get('cart_items');
        
        if (!$cart_items) {
            return [];
        }

        $cart_items = json_decode($cart_items, true);

        // Validate the decoded data is an array
        if (!is_array($cart_items)) {
            self::clearCartItems();
            return [];
        }

        // Migrate old cart items to add missing 'image' key if absent
        foreach ($cart_items as $key => $item) {
            if (!is_array($item)) {
                unset($cart_items[$key]);
                continue;
            }

            if (!array_key_exists('image', $item)) {
                $product = Product::where('id', $item['product_id'])->first(['images']);
                $cart_items[$key]['image'] = $product ? ($product->images[0] ?? null) : null;
            }
        }

        // Reset array keys and ensure all items are valid
        $cart_items = array_values(array_filter($cart_items, 'is_array'));
        
        // Re-save the updated cart to cookie to persist the fix
        self::addCartItemsToCookie($cart_items);

        return $cart_items;
    }

    // Increment item quantity
    static public function incrementQuantityToCart($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                $cart_items[$key]['quantity']++;
                $cart_items[$key]['total_amount'] = $cart_items[$key]['quantity'] * $cart_items[$key]['unit_amount'];
            }
        }

        self::addCartItemsToCookie($cart_items);
        return $cart_items;
    }

    // Decrement item quantity
    static public function decrementQuantityToCart($product_id)
    {
        $cart_items = self::getCartItemsFromCookie();

        foreach ($cart_items as $key => $item) {
            if ($item['product_id'] == $product_id) {
                if ($cart_items[$key]['quantity'] > 1) {
                    $cart_items[$key]['quantity']--;
                    $cart_items[$key]['total_amount'] = $cart_items[$key]['quantity'] * $cart_items[$key]['unit_amount'];
                } else {
                    // Option: Remove item if quantity becomes 0
                    unset($cart_items[$key]);
                    $cart_items = array_values($cart_items);
                }
            }
        }

        self::addCartItemsToCookie($cart_items);
        return $cart_items;
    }

    // Calculate grand total (with empty array check)
    static public function calculateGrandTotal($items)
    {
        if (!is_array($items) || empty($items)) {
            return 0;
        }
        
        return array_sum(array_column($items, 'total_amount'));
    }
}