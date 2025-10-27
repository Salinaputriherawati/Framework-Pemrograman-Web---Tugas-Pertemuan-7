<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::query();
        // Cek apakah ada parameter 'search' di request
        if ($request->has('search') && $request->search != '') {
            // Melakukan pencarian berdasarkan nama produk atau informasi
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', '%' . $search . '%');
            });
        }

        // Sorting 
        if ($request->has('sort_by') && $request->sort_by != '') {
            $order = $request->has('order') && $request->order == 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $order);
        }

        // Jika tidak ada parameter ‘search’, langsung ambil produk dengan paginasi
        $data = $query->paginate(2)->appends($request->query());
        return view("master-data.product-master.index-product", compact('data'));

        // $data = Product::paginate(perPage: 2);
        // return view("master-data.product-master.index-product", compact('data'));
        // $data = Product::all();
        // return view('layouts-percobaan.app');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("master-data.product-master.create-product");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // validasi input data
            $validasi_data = $request->validate([
                'product_name' => 'required|string|max:255',
                'unit'         => 'required|string|max:50',
                'type'         => 'required|string|max:50',
                'information'  => 'nullable|string',
                'qty'          => 'required|integer',
                'producer'     => 'required|string|max:255',
            ]);

            // Proses simpan data ke dalam database
            Product::create($validasi_data);

            return redirect()->route('product-index')->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create product!');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail(id: $id);
        return view(view: "master-data.product-master.detail-product", data: compact(var_name: 'product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        return view('master-data.product-master.edit-product', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'product_name' => 'required|string|max:255',
                'unit' => 'required|string|max:255',
                'type' => 'required|string|max:255',
                'information' => 'nullable|string',
                'qty' => 'required|integer|min:1',
                'producer' => 'required|string|max:255',
            ]);

            $product = Product::findOrFail($id);
            $product->update([
                'product_name' => $request->product_name,
                'unit' => $request->unit,
                'type' => $request->type,
                'information' => $request->information,
                'qty' => $request->qty,
                'producer' => $request->producer,
            ]);

            return redirect()->route('product-index')->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update product!');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // // buat ngehapus seluruh data di database
        // Product::truncate();
        // return redirect()->back()->with('error', 'Product tidak ditemukan.');

        // // buat ngehapus berdasar tipe, misal kue
        // $deleted = Product::where('type', 'kue')->delete();
        // if (deleted){
        //     return redirect()->back()->with('success', 'Semua Produk berhasil dihapus');
        // }

        // buat ngehapus berdasarkan id
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            return redirect()->back()->with('success', 'Product berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Product tidak ditemukan.');
    }
}
