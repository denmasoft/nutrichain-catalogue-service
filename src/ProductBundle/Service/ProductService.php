<?php
namespace ProductBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;
use ProductBundle\DTO\ProductDTO;
use ProductBundle\Entity\Product;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductService
{
    private $em;
    private $validator;
    private $translator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->translator = $translator;
        $em->getFilters()->enable('softdeleteable');
    }

    public function findProductById(int $id): Product
    {
        $product = $this->em->getRepository(Product::class)->find($id);
        if (!$product) {
            $message = $this->translator->trans('product.not_found', ['%id%' => $id]);
            throw new NotFoundHttpException($message);
        }

        return $product;
    }

    public function findAllProducts(array $filters): array
    {
        return $this->em->getRepository(Product::class)->findByFilters($filters);
    }

    public function createProduct(ProductDTO $productDTO): Product
    {
        $errors = $this->validator->validate($productDTO);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        $product = new Product();
        $this->mapDtoToEntity($productDTO, $product);

        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }

    public function updateProduct(Product $product, ProductDTO $productDTO): Product
    {
        $errors = $this->validator->validate($productDTO);
        if (count($errors) > 0) {
            throw new \InvalidArgumentException((string) $errors);
        }

        $this->mapDtoToEntity($productDTO, $product);
        $this->em->flush();

        return $product;
    }

    public function deleteProduct(Product $product): void
    {
        $this->em->remove($product);
        $this->em->flush();
    }

    private function mapDtoToEntity(ProductDTO $dto, Product $product): void
    {
        $product->setName($dto->name);
        $product->setSku($dto->sku);
        $product->setWeight($dto->weight);
        $product->setDescription($dto->description);
    }
}
