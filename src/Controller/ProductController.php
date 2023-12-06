<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods:['GET'])]
    public function getAllProduct(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $productList = $productRepository->findAll();

        $jsonProductList = $serializer->serialize($productList, 'json');


        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/product/{id}', name: 'product', methods:['GET'])]
    public function getProductById(ProductRepository $productRepository, SerializerInterface $serializer, int $id): JsonResponse
    {
        $product = $productRepository->find($id);
        if($product){
            $jsonProductList = $serializer->serialize($product, 'json');
            return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        
    }
}
