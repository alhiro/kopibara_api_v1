<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Product;
use App\Category;
use App\Jobs\ProductJob;
use App\Jobs\MarketplaceJob;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index()
    {
        $product = Product::with(['category'])->orderBy('created_at', 'DESC');
        if (request()->q != '') {
            $product = $product->where('name', 'LIKE', '%' . request()->q . '%');
        }
        $product = $product->paginate(10);

        return response()->json([
            'data' => $product,
            'status' => true,
            'message' => 'Product show all successfully'
        ]);        
    }

    public function create()
    {
        $category = Category::orderBy('name', 'DESC')->get();

        return response()->json([
            'data' => $category,
            'status' => true,
            'message' => 'Product category show successfully'
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kode' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required',
            'stock' => 'required|integer',
            'price' => 'required|integer',
            'end_price' => 'required|integer',
            'weight' => 'required|integer',
        ]);

        // if ($request->hasFile('image')) {
        //     $file = $request->file('image');
        //     $destinationPath = 'public/product';
        //     $productImage = date('YmdHis') . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
        //     $file->move($destinationPath, $productImage);
        // }

        $product = Product::create([
            'name' => $request->name,
            'slug' => $request->name,
            'kode' => $request->kode,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'image' => $request->image,
            'stock' => $request->stock,
            'price' => $request->price,
            'end_price' => $request->end_price,
            'weight' => $request->weight,
            'status' => $request->status
        ]);
        
        return response()->json([
            'data' => $product,
            'status' => true,
            'message' => 'Product successfully create'
        ]);
    }

    public function edit($id)
    {
        $product = Product::find($id);
        $category = Category::orderBy('name', 'DESC')->get();
        
        return response()->json([
            'data' => $product,
            'category' => $category,
            'status' => true,
            'message' => 'Product successfully select by category'
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'kode' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required',
            'stock' => 'required|integer',
            'price' => 'required|integer',
            'end_price' => 'required|integer',
            'weight' => 'required|integer',
        ]);

        $product = Product::find($id);
        
        // $productImage = $product->image;
        
        // if ($request->hasFile('image')) {
        //     $file = $request->file('image');
        //     $destinationPath = 'public/product';
        //     $productImage = date('YmdHis') . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
        //     $file->move($destinationPath, $productImage);

        //     File::delete('public/product/' . $product->image);
        // }

        $product->update([
            'kode' => $request->kode,
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'image' => $request->image,
            'stock' => $request->stock,
            'price' => $request->price,
            'end_price' => $request->end_price,
            'weight' => $request->weight,
            'status' => $request->status
           
        ]);

        return response()->json([
            'data' => $product,
            'status' => true,
            'message' => 'Product successfully change'
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $product = Product::find($id);
        if($product) {                
            $destinationPath = 'public/product/' . $product->image;
            File::delete($destinationPath);
            $product->delete();

            return response()->json([
                'data' => $product,
                'status' => true,
                'message' => 'Product successfully remove',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Product succesfully remove'
            ]);
        }     
    }
}