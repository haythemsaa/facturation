<?php
namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category', 'stocks');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%");
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->is_active !== null) {
            $query->where('is_active', $request->is_active);
        }

        $products = $query->paginate($request->per_page ?? 15);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:products,code',
            'barcode' => 'nullable|string|unique:products,barcode',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'required|in:product,service',
            'unit' => 'required|in:piece,kg,liter,meter,box,pack',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'minimum_price' => 'nullable|numeric|min:0',
            'tva_rate' => 'required|in:19,13,7,0',
            'stock_alert_threshold' => 'nullable|numeric|min:0',
            'track_stock' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);

        return response()->json($product->load('category'), 201);
    }

    public function show(Product $product)
    {
        return response()->json($product->load('category', 'stocks.warehouse'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|unique:products,code,' . $product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $product->id,
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'type' => 'sometimes|in:product,service',
            'unit' => 'sometimes|in:piece,kg,liter,meter,box,pack',
            'purchase_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'minimum_price' => 'nullable|numeric|min:0',
            'tva_rate' => 'sometimes|in:19,13,7,0',
            'stock_alert_threshold' => 'nullable|numeric|min:0',
            'track_stock' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json($product->load('category'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Produit supprimé avec succès']);
    }
}
