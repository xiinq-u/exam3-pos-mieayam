<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::with('category')
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'product' => $product->load(
                'category',
                'productMaterials.material',
            ),
        ]);
    }

    public function updateRecipe(
        Request $request,
        Product $product,
    ): JsonResponse {
        $data = $request->validate([
            'materials' => ['required', 'array'],
            'materials.*.material_id' => [
                'required',
                'exists:materials,id',
                'distinct',
            ],
            'materials.*.quantity_per_unit' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        $product->productMaterials()->delete();

        foreach ($data['materials'] as $material) {
            ProductMaterial::create([
                'product_id' => $product->id,
                ...$material,
            ]);
        }

        $product->load('productMaterials.material');

        $cost = $product->productMaterials->sum(
            fn (ProductMaterial $recipe): float => (float) $recipe->quantity_per_unit *
                (float) $recipe->material->purchase_price,
        );

        return response()->json([
            'data' => $product,
            'cost_per_unit' => $cost,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'category_id' => [
                    'required',
                    'exists:categories,id',
                ],
                'price' => ['required', 'numeric', 'min:0'],
                'is_available' => ['sometimes', 'boolean'],
                'image' => ['nullable', 'image', 'max:2048'],
            ],
            [
                'image.image' => 'Foto produk harus berupa gambar. Disarankan memakai WebP atau JPG, rasio 1:1, dan resolusi sekitar 600 x 600 piksel.',
                'image.max' => 'Ukuran foto maksimal 2 MB. Untuk pemuatan lebih cepat, ukuran ideal maksimal 300 KB.',
            ],
        );

        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        $product = Product::create([
            ...$data,
            'is_available' => $data['is_available'] ?? true,
        ]);

        return response()->json([
            'data' => $product->load('category'),
        ], 201);
    }

    public function update(
        Request $request,
        Product $product,
    ): JsonResponse {
        $data = $request->validate(
            [
                'name' => ['sometimes', 'string', 'max:255'],
                'category_id' => [
                    'sometimes',
                    'exists:categories,id',
                ],
                'price' => ['sometimes', 'numeric', 'min:0'],
                'is_available' => ['sometimes', 'boolean'],
                'image' => ['nullable', 'image', 'max:2048'],
            ],
            [
                'image.image' => 'Foto produk harus berupa gambar. Disarankan memakai WebP atau JPG, rasio 1:1, dan resolusi sekitar 600 x 600 piksel.',
                'image.max' => 'Ukuran foto maksimal 2 MB. Untuk pemuatan lebih cepat, ukuran ideal maksimal 300 KB.',
            ],
        );

        if ($request->hasFile('image')) {
            if ($product->image !== null) {
                File::delete(
                    storage_path('app/public/'.$product->image),
                );
            }

            $data['image'] = $request
                ->file('image')
                ->store('products', 'public');
        }

        $product->update($data);

        return response()->json([
            'data' => $product->load('category'),
        ]);
    }

    public function destroy(Product $product): Response
    {
        if ($product->image !== null) {
            File::delete(
                storage_path('app/public/'.$product->image),
            );
        }

        Product::destroy($product->getKey());

        return response()->noContent();
    }
}
