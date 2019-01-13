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
 * @ORM\Table(name="app_project")
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    use IdTrait {
        IdTrait::__construct as protected IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="projects")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    private $client;

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
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="project")
     */
    private $tasks;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $externalReference;

    /**
     * @var Collection|ProjectRate[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectRate", mappedBy="project", orphanRemoval=true)
     * @ORM\OrderBy({"startedAt"="ASC"})
     */
    private $rates;

    /**
     * Project constructor.
     */
    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->active = true;
        $this->rates = new ArrayCollection();
        $this->tasks = new ArrayCollection();
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Project
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @return Project
     */
    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    /**
     * @param Task $task
     * @return Project
     */
    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setProject($this);
        }

        return $this;
    }

    /**
     * @param Task $task
     * @return Project
     */
    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            // set the owning side to null (unless already changed)
            if ($task->getProject() === $this) {
                $task->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProjectRate[]
     */
    public function getRates(): Collection
    {
        return $this->rates;
    }

    /**
     * @param ProjectRate $rate
     * @return Project
     */
    public function addRate(ProjectRate $rate): self
    {
        if (!$this->rates->contains($rate)) {
            $this->rates[] = $rate;
            $rate->setProject($this);
        }

        return $this;
    }

    /**
     * @param ProjectRate $rate
     * @return Project
     */
    public function removeRate(ProjectRate $rate): self
    {
        if ($this->rates->contains($rate)) {
            $this->rates->removeElement($rate);
            // set the owning side to null (unless already changed)
            if ($rate->getProject() === $this) {
                $rate->setProject(null);
            }
        }

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
     * @return Project
     */
    public function setExternalReference(?string $externalReference): self
    {
        $this->externalReference = $externalReference;

        return $this;
    }

    /**
     * @param \DateTimeInterface $date
     * @return ProjectRate|null
     */
    public function getRateOfDate(\DateTimeInterface $date): ?ProjectRate
    {
        foreach ($this->rates as $r => $rate) {
            if ($rate->getStartedAt()->format('Y-m-d') > $date->format('Y-m-d')) {
                if ($r > 0) {
                    return $this->rates[$r - 1];
                }
                break;
            }
        }

        return $rate ?? null;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return Project
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
