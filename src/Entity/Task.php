<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="app_task")
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task
{
    use IdTrait {
        IdTrait::__construct as protected IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="tasks")
     */
    private $project;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $start;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $stop;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $onSite;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $expected;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $externalReference;

    /**
     * Task constructor.
     */
    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->expected = true;
        $this->onSite = false;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return Task
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    /**
     * @param \DateTimeInterface $start
     *
     * @return Task
     */
    public function setStart(\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStop(): ?\DateTimeInterface
    {
        return $this->stop;
    }

    /**
     * @param \DateTimeInterface|null $stop
     *
     * @return Task
     */
    public function setStop(?\DateTimeInterface $stop): self
    {
        $this->stop = $stop;

        return $this;
    }

    /**
     * @return Project|null
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * @param Project|null $project
     *
     * @return Task
     */
    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return bool
     */
    public function getOnSite(): bool
    {
        return $this->onSite;
    }

    /**
     * @param bool $onSite
     *
     * @return Task
     */
    public function setOnSite(bool $onSite): self
    {
        $this->onSite = $onSite;

        return $this;
    }

    /**
     * @return bool
     */
    public function getExpected(): bool
    {
        return $this->expected;
    }

    /**
     * @param bool $expected
     *
     * @return Task
     */
    public function setExpected(bool $expected): self
    {
        $this->expected = $expected;

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
     *
     * @return Task
     */
    public function setExternalReference(?string $externalReference): self
    {
        $this->externalReference = $externalReference;

        return $this;
    }

    /**
     * @return int
     */
    public function getDuration(): int
    {
        return $this->stop->getTimestamp() - $this->start->getTimestamp();
    }
}
