<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\Category;
use App\Customer;
use App\Province;
use App\Order;

class FrontController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(10);

        return response()->json([
            'data' => $products,
            'status' => true,
            'message' => 'Product show successfully'
        ]);
    }

    public function product()
    {
        $products = Product::orderBy('created_at', 'DESC')->paginate(12);

        return response()->json([
            'data' => $products,
            'status' => true,
            'message' => 'Product show successfully'
        ]);
    }

    public function categoryProduct($slug)
    {
        $products = Category::where('slug', $slug)->first()->product()->orderBy('created_at', 'DESC')->paginate(12);
        
        return response()->json([
            'data' => $products,
            'status'=>true,
            'message' => 'Category product show successfully'
        ]);        
    }    

    public function showall()
    {
        $category = Category::with(['product'])->orderBy('created_at', 'DESC')->paginate(10);
        
        return response()->json([
            'data' => $category,
            'status'=>true,
            'message'=>'Categories show with product successfully'
        ]);

    }

    public function show($slug)
    {
        $product = Product::with(['category'])->where('slug', $slug)->first();

        return response()->json([
            'data' => $product,
            'status'=>true,
            'message' => 'Product by category show successfully'
        ]);  
    }
}
