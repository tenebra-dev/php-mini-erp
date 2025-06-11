<?php
use PHPUnit\Framework\TestCase;
use services\OrderService;
use services\ProductService;
use migrations\MigrationRunner;

require_once __DIR__ . '/../../api/services/OrderService.php';
require_once __DIR__ . '/../../api/services/ProductService.php';
require_once __DIR__ . '/../../api/migrations/MigrationRunner.php';

class OrderServiceTest extends TestCase
{
    private $orderService;
    private $productService;
    private $productId;
    private $variationId;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];

        $_SESSION['cart'] = [
            'items' => [],
            'coupon' => null,
            'shipping' => 0,
            'subtotal' => 0,
            'total' => 0
        ];

        $pdo = new PDO('sqlite::memory:');
        $runner = new MigrationRunner($pdo);
        $runner->run();

        $this->orderService = new OrderService($pdo);
        $this->productService = new ProductService($pdo);

        // Cria um produto com variação
        $this->productId = $this->productService->createProductWithVariations([
            'name' => 'Produto Pedido',
            'price' => 50.0,
            'description' => 'Produto para pedido',
            'variations' => [
                [
                    'name' => 'Tamanho',
                    'value' => 'M',
                    'quantity' => 10
                ]
            ]
        ]);

        // Pegue o variation_id criado
        $variations = $this->productService->getProductVariations($this->productId);
        $this->variationId = $variations[0]['id'] ?? null;
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddToCart()
    {
        $result = $this->orderService->addToCart([
            'product_id' => $this->productId,
            'variation_id' => $this->variationId,
            'quantity' => 2
        ]);
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $_SESSION['cart']['items'][$this->productId . '_' . $this->variationId]['quantity']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testClearCart()
    {
        $this->orderService->addToCart([
            'product_id' => $this->productId,
            'variation_id' => $this->variationId, // Corrigido aqui
            'quantity' => 1
        ]);
        $result = $this->orderService->clearCart();
        $this->assertTrue($result['success']);
        $this->assertEmpty($_SESSION['cart']['items']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCreateOrder()
    {
        // Adiciona item ao carrinho
        $this->orderService->addToCart([
            'product_id' => $this->productId,
            'variation_id' => $this->variationId,
            'quantity' => 1
        ]);

        $orderData = [
            'customer_name' => 'Cliente Teste',
            'customer_email' => 'cliente@teste.com',
            'customer_cep' => '01001-000',
            'customer_address' => 'Rua Teste'
        ];

        ob_start(); // Suprime saída do "envio de e-mail"
        $result = $this->orderService->processCheckout($orderData);
        ob_end_clean();

        $this->assertIsArray($result);
        $this->assertTrue($result['success'], $result['message'] ?? 'Falha no checkout');
        $this->assertArrayHasKey('order_id', $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCheckout()
    {
        // Simula um produto e variação válidos
        $productId = $this->productService->createProductWithVariations([
            'name' => 'Produto Teste',
            'price' => 50.0,
            'description' => 'Produto para pedido',
            'variations' => [
                [
                    'name' => 'Tamanho',
                    'value' => 'M',
                    'quantity' => 10
                ]
            ]
        ]);
        $variations = $this->productService->getProductVariations($productId);
        $variationId = $variations[0]['id'] ?? null;

        // Simula o carrinho na sessão
        $_SESSION['cart'] = [
            'items' => [
                [
                    'product_id' => $productId,
                    'variation_id' => $variationId,
                    'quantity' => 2,
                    'unit_price' => 50.0,
                    'product_name' => 'Produto Teste',
                    'variation_name' => 'Tamanho M'
                ]
            ],
            'coupon' => null,
            'shipping' => 10.0,
            'subtotal' => 100.0,
            'discount' => 0.0,
            'total' => 110.0
        ];

        $orderData = [
            'customer_name' => 'Cliente Teste',
            'customer_email' => 'cliente@teste.com',
            'customer_cep' => '01001-000',
            'customer_address' => 'Rua Teste, 123',
            'customer_neighborhood' => 'Centro',
            'customer_city' => 'São Paulo',
            'customer_state' => 'SP'
        ];

        ob_start(); // Suprime saída do "envio de e-mail"
        $result = $this->orderService->processCheckout($orderData);
        ob_end_clean();

        $this->assertIsArray($result);
        $this->assertTrue($result['success'], $result['message'] ?? 'Falha no checkout');
        $this->assertArrayHasKey('order_id', $result);
    }
}