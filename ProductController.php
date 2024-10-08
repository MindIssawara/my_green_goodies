<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    //For displaying all products to users
    public function show()
    {
        $products = Product::paginate(8);
        return view('products.index', compact('products'));
    }

    public function showVegetable()
    {
        $products = DB::table('products as p')
        ->join('product_categories as pc', 'p.category_id', '=', 'pc.id')
        ->where('pc.id', 1)->paginate(8);
        return view('products.vegetables', compact('products'));
    }

    public function showFruit()
    {
        $products = DB::table('products as p')
        ->join('product_categories as pc', 'p.category_id', '=', 'pc.id')
        ->where('pc.id', 2)->paginate(8);
        return view('products.fruits', compact('products'));
    }


    public function show_detail(Product $product)
    {
        // Increment the popularity by 1
        $product->popularity += 1;

        // Save the updated product
        $product->save();
        return view('products.detail', compact('product'));
    }

    // Show form for creating a new product
    public function create()
    {
        $categories = ProductCategory::all(); // Load categories for the form
        return view('products.form', [
            'product' => null, // Indicate a new product
            'categories' => $categories,
        ]);
    }

    public function form()
    {
        $categories = ProductCategory::all();
        $allProducts = Product::all(); // Get all products
        return view('products.form', compact('categories', 'allProducts'));
    }

    // Show form for editing a product
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = ProductCategory::all(); // Load categories for the form
        return view('products.form', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    // Store or update the product
    public function store(Request $request)
    {
        // Validation rules
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Image is nullable
            'category_id' => 'required|exists:product_categories,id',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
        ]);

        // Check if this is an update or a new product
        $product = $request->id ? Product::findOrFail($request->id) : new Product;

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
            $product->image = $imagePath;
        }

        // Set product details
        $product->name = $request->name;
        $product->category_id = $request->category_id;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock_quantity = $request->stock_quantity;

        // Save the product
        $product->save();

        // Redirect to the index or to the create page
        return redirect()->route('products.form')->with('success', 'Product saved successfully.');
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products.form')->with('success', 'Product deleted successfully.');
    }
}