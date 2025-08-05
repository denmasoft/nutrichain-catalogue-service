<?php
namespace ProductBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass="ProductBundle\Repository\ProductRepository")
 * @ORM\Table(name="products")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 * @UniqueEntity("sku", message="The SKU '{{ value }}' is already in use.")
 * @Serializer\ExclusionPolicy("all")
 */
class Product
{
    use SoftDeleteableEntity;
    /**
     * @ORM\Id @ORM\GeneratedValue(strategy="AUTO") 
     * @ORM\Column(type="integer")
     * @Serializer\Expose @Serializer\Groups({"list", "detail"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank(groups={"create"})
     * @Serializer\Expose 
     * @Serializer\Groups({"list", "detail", "create", "update"})
     */
    private $sku;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"create"})
     * @Serializer\Expose 
     * @Serializer\Groups({"list", "detail", "create", "update"})
     */
    private $name;
    
    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(groups={"create"})
     * @Serializer\Expose 
     * @Serializer\Groups({"detail", "create", "update"})
     */
    private $description;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     * @Assert\NotBlank(groups={"create"}) 
     * @Assert\Type(type="numeric") 
     * @Assert\GreaterThan(value=0)
     * @Serializer\Expose 
     * @Serializer\Type("float") 
     * @Serializer\Groups({"detail", "create", "update"})
     */
    private $weight;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(groups={"create"})
     * @Serializer\Expose 
     * @Serializer\Groups({"list", "detail", "create", "update"})
     */
    private $category;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Url()
     * @Serializer\Expose @Serializer\Groups({"detail", "create", "update"})
     */
    private $imageUrl;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    public function getId() { 
        return $this->id; 
    }

    public function getSku() { 
        return $this->sku; 
    }
    
    public function setSku($sku) { 
        $this->sku = $sku; 

        return $this; 
    }
    
    public function getName() { 
        return $this->name; 
    }
    
    public function setName($name) { 
        $this->name = $name; 

        return $this; 
    }

    public function getDescription() { 
        return $this->description; 
    }

    public function setDescription($description) { 
        $this->description = $description; 

        return $this; 
    }

    public function getWeight() { 
        return $this->weight; 
    }
    
    public function setWeight($weight) { 
        $this->weight = $weight; 

        return $this; 
    }
    
    public function getCategory() { 
        return $this->category; 
    }

    public function setCategory($category) { 
        $this->category = $category; 

        return $this; 
    }
    public function getImageUrl() { 
        return $this->imageUrl; 
    }

    public function setImageUrl($imageUrl) { 
        $this->imageUrl = $imageUrl;

        return $this; 
     }

     public function getCreatedAt() {
        return $this->createdAt;
     }

     public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
     }

     public function getUpdatedAt() {
        return $this->updatedAt;
     }

     public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;

        return $this;
     }
}
