<?php

namespace App\Http\Controllers\Api\v1;

use App\Application\Product\GetProductsUseCase;
use App\Application\Product\GetProductUseCase;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private GetProductsUseCase $getProductsUseCase,
        private GetProductUseCase $getProductUseCase
    ) {}

    public function index(Request $request)
    {
        try {
            $products = $this->getProductsUseCase->execute([
                'per_page' => $request->get('per_page', 15),
                'active_only' => true
            ]);

            return $this->successResponse($products, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve products', 500);
        }
    }

    public function show($id)
    {
        try {
            $product = $this->getProductUseCase->execute($id);
            return $this->successResponse($product, 'Product retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Product not found', 404);
        }
    }
}
