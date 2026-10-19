<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InventoryAuthenticationTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function protectedEndpoints(): array
    {
        return [
            'dashboard' => ['GET', '/api/dashboard'],
            'products' => ['GET', '/api/products'],
            'create product' => ['POST', '/api/products'],
            'product details' => ['GET', '/api/products/1'],
            'update product' => ['PUT', '/api/products/1'],
            'delete product' => ['DELETE', '/api/products/1'],
            'product image' => ['GET', '/api/products/1/images/0'],
            'categories' => ['GET', '/api/categories'],
            'create category' => ['POST', '/api/categories'],
            'category details' => ['GET', '/api/categories/1'],
            'update category' => ['PUT', '/api/categories/1'],
            'delete category' => ['DELETE', '/api/categories/1'],
        ];
    }

    #[DataProvider('protectedEndpoints')]
    public function test_inventory_requires_authentication(string $method, string $path): void
    {
        $this->json($method, $path)->assertUnauthorized();
    }

    public function test_browser_requests_without_an_accept_header_receive_json_instead_of_a_redirect(): void
    {
        $this->get('/api/products')->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
        $this->get('/api/auth/me')->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
    }
}
