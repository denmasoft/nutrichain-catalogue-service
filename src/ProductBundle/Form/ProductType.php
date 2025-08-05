<?php
namespace ProductBundle\Form;

use ProductBundle\DTO\ProductDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use ProductBundle\Entity\Product;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sku')
            ->add('name')
            ->add('description')
            ->add('weight');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductDTO::class,
            'csrf_protection' => false,
        ]);
    }

    public function getName()
    {
        return 'product_type';
    }
}