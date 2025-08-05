<?php
namespace ProductBundle\DataFixtures\ORM;

use ProductBundle\Entity\Product;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadProductData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $productsData = [
            ['LE-F-001', 'Leche Fresca Entera', 'Leche de vaca pasteurizada, 1 litro.', 1.02, 'refrigerados', 'https://example.com/images/leche.jpg'],
            ['AR-S-001', 'Arroz Blanco Grano Largo', 'Paquete de 1kg de arroz blanco tipo 1.', 1.00, 'secos', 'https://example.com/images/arroz.jpg'],
            ['PO-C-001', 'Pechuga de Pollo Congelada', 'Bandeja con 500g de pechugas de pollo.', 0.50, 'congelados', 'https://example.com/images/pollo.jpg'],
            ['YO-F-002', 'Yogur Natural Griego', 'Yogur natural griego sin azúcar, pack de 4.', 0.48, 'refrigerados', 'https://example.com/images/yogur.jpg'],
            ['PA-S-002', 'Pasta Espagueti', 'Paquete de 500g de pasta de sémola.', 0.50, 'secos', 'https://example.com/images/pasta.jpg'],
            ['HE-C-002', 'Helado de Vainilla', 'Tarrina de 1 litro de helado cremoso.', 0.95, 'congelados', 'https://example.com/images/helado.jpg'],
            ['AT-S-003', 'Atún en Aceite de Oliva', 'Lata de 80g de atún claro.', 0.08, 'secos', 'https://example.com/images/atun.jpg'],
            ['QU-F-003', 'Queso Curado de Oveja', 'Cuña de 250g de queso curado.', 0.25, 'refrigerados', 'https://example.com/images/queso.jpg'],
            ['PI-C-003', 'Pizza Congelada 4 Quesos', 'Pizza de masa fina y crujiente.', 0.45, 'congelados', 'https://example.com/images/pizza.jpg'],
            ['GA-S-004', 'Galletas Integrales', 'Paquete de 300g de galletas con avena.', 0.30, 'secos', 'https://example.com/images/galletas.jpg']
        ];

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setSku($data[0]);
            $product->setName($data[1]);
            $product->setDescription($data[2]);
            $product->setWeight($data[3]);
            $product->setCategory($data[4]);
            $product->setImageUrl($data[5]);
            
            $manager->persist($product);
        }
        
        $manager->flush();
    }

    /**
     *
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
