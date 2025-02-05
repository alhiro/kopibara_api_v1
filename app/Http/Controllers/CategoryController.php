<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::with(['parent'])->orderBy('created_at', 'DESC')->paginate(10);
        $parent = Category::getParent()->orderBy('name', 'ASC')->get();
        
        return response()->json([
            'data' => $category,
            'parent' => $parent,
            'status'=>true,
            'message'=>'Category show successfully'
        ]);

        // return view('categories.index', compact('category', 'parent'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:50|unique:categories'
        ]);

        // $request->request->add([
        //     'slug' => $request->get('name')
        // ]);
        // return Category::create($request);
        
        return Category::create([            
            'name' => $request['name'],
            'slug' => $request['slug'],
            'parent_id' => $request['parent_id']            
        ]);

        //return redirect(route('category.index'))->with(['success' => 'Kategori Baru Ditambahkan!']);
    }

    public function edit($id)
    {
        $category = Category::with('parent')->findOrFail($id);
        $parent = Category::getParent()->orderBy('name', 'ASC')->get();

        // return view('categories.edit', compact('category', 'parent'));

        return response()->json([      
            'data' => $category,
            'parent' => $parent,
            'status'=>true,
            'message'=>'Category selected successfully',
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:50|unique:categories,name,' . $id
        ]);

        $category = Category::find($id);
        $category->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'parent_id' => $request->parent_id
        ]);

        return response()->json([      
            'data' => $category,
            'status'=>true,
            'message'=>'Category updated successfully',
        ]);

        // return redirect(route('category.index'))->with(['success' => 'Kategori Diperbaharui!']);
    }

    public function destroy($id)
    {
        $category = Category::withCount(['child', 'product'])->find($id);
        if ($category->child_count == 0 && $category->product_count == 0) {
            $category->delete();

            return response()->json([      
                'data' => $category,
                'status'=>true,
                'message'=>'Category successfully delete',
            ]);
        } else {
            return response()->json([   
                'status'=>false,
                'message'=>'Category has parent. Can not successfully delete',
            ]);
        }
        
        // return redirect(route('category.index'))->with(['error' => 'Kategori Ini Memiliki Anak Kategori!']);
    }
}
