<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
use OpenApi\Annotations as OA;

// ...

/**
 *  @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "user",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers"),
 *      attributes = {"method": "GET" }
 * )
 *
 *  @Hateoas\Relation(
 *      "delete",
 *      href = @Hateoas\Route(
 *          "deleteUser",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getUsers"),
 *      attributes = {"method": "DELETE" }
 * )
 *  @Hateoas\Relation(
 *      "all",
 *      href = @Hateoas\Route(
 *          "users",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups ="getUsers"),
 *      attributes = {"methods": "GET" }
 * )
 *
 */

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    /**
     *   @OA\Property( type="integer")
     *        
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUsers"])]
    private ?int $id = null;
    /**
     *   @OA\Property( type="string")
     *        
     */
    #[ORM\Column(length: 100)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "Le prénom est obligatoire")]
    private ?string $firstName = null;
    /**
     *   @OA\Property( type="string")
     *        
     */
    #[ORM\Column(length: 100)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "Le nom obligatoire")]
    private ?string $lastName = null;
    /**
     *   @OA\Property( type="string")
     *        
     */
    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "L'email' est obligatoire")]
    private ?string $email = null;
    /**
     *   @OA\Property(
     *           type="string",
     *           format="date-time",
     *         )
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(["getUsers"])]
    private ?\DateTimeImmutable $createdAt;
    /**
     *   @OA\Property( type="integer")
     *        
     */
    #[ORM\ManyToOne(inversedBy: 'users', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "id de customer est obligatoire")]
    private ?Customer $customer = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
