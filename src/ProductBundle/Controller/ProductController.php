<?php

namespace ProductBundle\Controller;

use ProductBundle\DTO\ProductDTO;
use ProductBundle\Entity\Product;
use ProductBundle\Form\ProductType;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Rest\RouteResource("Product")
 * @Rest\NamePrefix("api_")
 */
class ProductController extends FOSRestController
{

	/**
     * @ApiDoc(resource=true, description="List all products.")
     * @Rest\Get("/products")
     * @Rest\QueryParam(name="category", requirements="[a-z]+", nullable=true, description="Filter by category.")
     * @Rest\QueryParam(name="name", nullable=true, description="Filter by name.")
     * @Rest\View(serializerGroups={"list"})
     */
    public function cgetAction(Request $request)
    {
        $filters = [
            'name' => $request->query->get('name'),
            'category' => $request->query->get('category')
        ];
        $products = $this->get('app.product_service')->findAllProducts(array_filter($filters));
        return $this->view($products, Response::HTTP_OK);
    }

    /**
     * @ApiDoc(description="get a product.")
     * @Rest\Get("/products/{id}")
     * @Rest\View(serializerGroups={"detail"})
     */
    public function getAction(Product $product)
    {
        return $product;
    }

    /**
     * @ApiDoc(description="Add a product.", input={"class"="ProductBundle\Entity\Product", "groups"={"create"}})
     * @Rest\Post("/products")
     * @ParamConverter("productDTO", converter="fos_rest.request_body", options={"validator"={"groups"={"create"}}})
     * @Rest\View(statusCode=201, serializerGroups={"detail"})
     */
    public function postAction(ProductDTO $productDTO,
                               ConstraintViolationListInterface $errors)
    {
        if (count($errors) > 0) {
            return new View($errors, Response::HTTP_BAD_REQUEST);
        }

        $productService = $this->get('app.product_service');
        $product = $productService->createProduct($productDTO);

        return $this->view($product, 201);
    }

    /**
     * @ApiDoc(description="Updates a product (partial).", input={"class"="ProductBundle\Form\ProductType"})
     * @Rest\Patch("/products/{id}")
     * @Rest\View(serializerGroups={"detail"})
     */
    public function patchAction(Product $product,
                                ProductDTO $productDTO,
                                ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $updatedProduct = $this->get('app.product_service')->updateProduct($product, $productDTO);

        return $this->view($updatedProduct, Response::HTTP_OK);
    }

    /**
     * @ApiDoc(description="Updates a product.", input={"class"="ProductBundle\Form\ProductType"})
     * @Rest\PUT("/products/{id}")
     * @Rest\View(serializerGroups={"detail"})
     */
    public function putAction(Product $product,
                                ProductDTO $productDTO,
                                ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            return $this->view($validationErrors, Response::HTTP_BAD_REQUEST);
        }

        $updatedProduct = $this->get('app.product_service')->updateProduct($product, $productDTO);

        return $this->view($updatedProduct, Response::HTTP_OK);
    }

    /**
     * @ApiDoc(description="deletes a product.", input={"class"="ProductBundle\Form\Product"})
     * @Rest\DELETE("/products/{id}")
     * @Rest\View(serializerGroups={"detail"})
     */
    public function deleteAction(Product $product)
    {
        $this->get('app.product_service')->deleteProduct($product);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
