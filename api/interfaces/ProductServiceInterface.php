<?php
namespace interfaces;

use dto\product\ProductCreateDTO;
use dto\product\ProductUpdateDTO;

interface ProductServiceInterface {
    public function getAllProducts();
    public function getProductById($id);
    public function getProductVariations($productId);
    public function getProductStock($productId);
    public function getVariationById($variationId);
    public function createProduct(ProductCreateDTO $dto);
    public function updateProduct($id, ProductUpdateDTO $dto);
    public function deleteProduct($id);
    public function decreaseStock($variationId, $quantity);
    public function increaseStock($variationId, $quantity);
    public function getPaginatedProducts($page = 1, $perPage = 10, $filters = []);
}