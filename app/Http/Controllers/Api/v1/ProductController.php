<?php

namespace App\Http\Controllers\Api\v1;

use App\Domains\Product\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private ProductService $productService
    ) {}

    public function index(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->get('per_page', 15),
                'paginate' => true
            ];

            $products = $this->productService->getActiveProducts($filters);

            return new ProductCollection($products);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve products', 500);
        }
    }

    public function show($id)
    {
        try {
            $productData = $this->productService->getProductWithStock($id);

            return $this->successResponse([
                'product' => new ProductResource($productData['product']),
                'stock_info' => [
                    'in_stock' => $productData['in_stock'],
                    'stock_status' => $productData['stock_status']
                ]
            ], 'Product retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Product not found', 404);
        }
    }
}
