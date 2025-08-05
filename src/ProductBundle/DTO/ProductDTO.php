<?php

namespace ProductBundle\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=3, max=50)
     */
    public $sku;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=3, max=100)
     */
    public $name;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     * @Assert\GreaterThan(0)
     */
    public $weight;

    public $description;
}
