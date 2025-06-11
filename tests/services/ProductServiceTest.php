<?php
use PHPUnit\Framework\TestCase;
use services\ProductService;
use migrations\MigrationRunner;
use dto\product\ProductUpdateDTO;

require_once __DIR__ . '/../../api/services/ProductService.php';
require_once __DIR__ . '/../../api/migrations/MigrationRunner.php';

class ProductServiceTest extends TestCase
{
    private $productService;

    protected function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $runner = new MigrationRunner($pdo);
        $runner->run();
        $this->productService = new ProductService($pdo);

        // Cria um produto para os testes
        $this->productId = $this->productService->createProductWithVariations([
            'name' => 'Produto Teste',
            'price' => 10.5,
            'description' => 'Descrição',
            'quantity' => 5
        ]);
    }

    public function testGetAllProducts()
    {
        $products = $this->productService->getAllProducts();
        $this->assertIsArray($products);
        $this->assertNotEmpty($products);
    }

    public function testGetProductById()
    {
        $product = $this->productService->getProductById($this->productId);
        $this->assertIsArray($product);
        $this->assertEquals('Produto Teste', $product['name']);
    }

    public function testUpdateProduct()
    {
        $dto = new ProductUpdateDTO([
            'name' => 'Produto Editado',
            'price' => 20.0,
            'description' => 'Nova descrição'
        ]);
        $result = $this->productService->updateProduct($this->productId, $dto);
        $this->assertTrue($result);

        $product = $this->productService->getProductById($this->productId);
        $this->assertEquals('Produto Editado', $product['name']);
    }

    public function testDeleteProduct()
    {
        $result = $this->productService->deleteProduct($this->productId);
        $this->assertTrue($result);
    }

    public function testGetPaginatedProducts()
    {
        // Cria produtos extras
        $this->productService->createProductWithVariations([
            'name' => 'Produto 1',
            'price' => 10.0,
            'description' => 'Teste'
        ]);
        $this->productService->createProductWithVariations([
            'name' => 'Produto 2',
            'price' => 20.0,
            'description' => 'Teste'
        ]);
        $result = $this->productService->getPaginatedProducts(1, 2);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertEquals(2, $result['pagination']['per_page']);
        $this->assertEquals(1, $result['pagination']['current_page']);
        $this->assertIsArray($result['data']);
    }
}