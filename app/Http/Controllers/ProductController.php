<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    // This function will render the products page
    public function index()
    {
        // Fetching all the products from the database
        $products = Product::orderBy('created_at', 'DESC')->get();

        return view('products.list', [
            'products' => $products
        ]);
    }

    // This function will render the create product page
    public function create()
    {
        return view('products.create');
    }

    // This function will insert the product into database
    public function insert(Request $request)
    {
        // Validation rule for product details
        $rules = [
            'name' => 'required|min:4',
            'code' => 'required|min:3',
            'price' => 'required|numeric'
        ];

        // Checking the imgae is inserted or nor
        if ($request->image != "") {
            $rules['image'] = 'image'; // Setting the ruke that will valid that the inserted data is image or not.
        }

        // Validating the product details inserted by the user
        $validator = Validator::make($request->all(), $rules);

        // Returning the validation failed message with user input data
        if ($validator->fails()) {
            return redirect()->route('products.create')->withInput()->withErrors($validator);
        }

        // Insert the product into database
        $product = new Product();
        $product->name = $request->name;
        $product->code = $request->code;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if ($request->image != "") {
            // Code to store image
            $image = $request->image;
            $ext = $image->getClientOriginalExtension(); // retrieving real extension of the image.
            $imageName = time() . '.' . $ext; // Prociding the unique image name

            // Store the image into a products directory under the public uploads directory
            $image->move(public_path('uploads/products'), $imageName);

            // Save image name to the database
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    // This function will render the product edit page
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', [
            'product' => $product
        ]);
    }

    // This function will update the product in database
    public function update($id, Request $request)
    {

        $product = Product::findOrFail($id);

        // Validation rule for product details
        $rules = [
            'name' => 'required|min:4',
            'code' => 'required|min:3',
            'price' => 'required|numeric'
        ];

        // Checking the imgae is inserted or not
        if ($request->image != "") {
            $rules['image'] = 'image'; // Setting the rule that will valid that the inserted data is image or not.
        }

        // Validating the product details inserted by the user
        $validator = Validator::make($request->all(), $rules);

        // Returning the validation failed message with user input data
        if ($validator->fails()) {
            return redirect()->route('products.edit', $product->id)->withInput()->withErrors($validator);
        }

        // Update the product into database
        $product->name = $request->name;
        $product->code = $request->code;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if ($request->image != "") {
            // Delete Old image from directory  if it exists
            File::delete(public_path('uploads/products/' . $product->image));

            // Code to store image
            $image = $request->image;
            $ext = $image->getClientOriginalExtension(); // retrieving real extension of the image.
            $imageName = time() . '.' . $ext; // Providing the unique image name

            // Store the image into a products directory under the public uploads directory
            $image->move(public_path('uploads/products'), $imageName);

            // Save image name to the database
            $product->image = $imageName;
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    // This function will delete the product from database
    public function delete($id)
    {
        $product = Product::findOrFail($id);

        // Delete the image from the directory under the public uploads
        File::delete(public_path('uploads/products/' . $product->image));

        // Delete product from the database
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully');
    }
}
