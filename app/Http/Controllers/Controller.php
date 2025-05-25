<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "2P",
    version: "1.0.0",
    description: "Tech Task",
    contact: new OA\Contact(
        name: "API Support",
        email: "2p@mail.com",
        url: "https://2p.com/support"
    ),
    license: new OA\License(
        name: "MIT",
        url: "https://opensource.org/licenses/MIT"
    )
)]
#[OA\Server(
    url: "/api/v1",
    description: "Testing API Server"
)]
#[OA\Server(
    url: "http://localhost:8000/api/v1",
    description: "Local Development Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Use Bearer token obtained from login endpoint"
)]
#[OA\Tag(
    name: "Authentication",
    description: "User authentication endpoints"
)]
#[OA\Tag(
    name: "Cart",
    description: "Shopping cart management operations"
)]
#[OA\Tag(
    name: "Products",
    description: "Product catalog endpoints"
)]
#[OA\Tag(
    name: "Checkout",
    description: "Order processing and payment endpoints"
)]
abstract class Controller
{}
