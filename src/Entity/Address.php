<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_address")
 * @ORM\Entity(repositoryClass="App\Repository\AddressRepository")
 */
class Address implements \JsonSerializable
{
    use IdTrait {
        IdTrait::__construct as IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=255)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=1)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=10)
     */
    private $postcode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=255)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=2, max=2)
     */
    private $country;

    /**
     * Address constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->country = 'FR';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Address
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return Address
     */
    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * @return Address
     */
    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return Address
     */
    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return Address
     */
    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'name' => $this->name,
            'postcode' => $this->postcode,
        ];
    }
}
