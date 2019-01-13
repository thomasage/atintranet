<?php
declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_client")
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 */
class Client
{
    use IdTrait {
        IdTrait::__construct as IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=10, unique=true, nullable=true)
     *
     * @Assert\Length(min=1, max=10)
     */
    private $code;

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
     * @®ar string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $externalReference;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $accountNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $vatNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $addressPrimary;

    /**
     * @var Collection|Invoice[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice", mappedBy="client")
     */
    private $invoices;

    /**
     * @var Collection|Project[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="client")
     * @ORM\OrderBy({"name"="ASC"})
     */
    private $projects;

    /**
     * Client constructor.
     */
    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->active = false;
        $this->invoices = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return Client
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Client
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalReference(): ?string
    {
        return $this->externalReference;
    }

    /**
     * @param string|null $externalReference
     * @return Client
     */
    public function setExternalReference(?string $externalReference): self
    {
        $this->externalReference = $externalReference;

        return $this;
    }

    /**
     * @return Address|null
     */
    public function getAddressPrimary(): ?Address
    {
        return $this->addressPrimary;
    }

    /**
     * @param Address $addressPrimary
     * @return Client
     */
    public function setAddressPrimary(Address $addressPrimary): self
    {
        $this->addressPrimary = $addressPrimary;

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    /**
     * @param Invoice $invoice
     * @return Client
     */
    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setClient($this);
        }

        return $this;
    }

    /**
     * @param Invoice $invoice
     * @return Client
     */
    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getClient() === $this) {
                $invoice->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return Client
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    /**
     * @param string|null $accountNumber
     * @return Client
     */
    public function setAccountNumber(?string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    /**
     * @param string|null $vatNumber
     * @return Client
     */
    public function setVatNumber(?string $vatNumber): self
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return Client
     */
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    /**
     * @param Project $project
     * @return Client
     */
    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setClient($this);
        }

        return $this;
    }

    /**
     * @param Project $project
     * @return Client
     */
    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getClient() === $this) {
                $project->setClient(null);
            }
        }

        return $this;
    }
}
