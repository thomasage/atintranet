<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="app_project_rate")
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRateRepository")
 */
class ProjectRate
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="rates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $hourlyRateOnSite;

    /**
     * @var float|null
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $hourlyRateOffSite;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="date")
     */
    private $startedAt;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Project|null
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * @param Project $project
     * @return ProjectRate
     */
    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getHourlyRateOnSite(): ?float
    {
        return $this->hourlyRateOnSite;
    }

    /**
     * @param float|null $hourlyRateOnSite
     * @return ProjectRate
     */
    public function setHourlyRateOnSite(?float $hourlyRateOnSite): self
    {
        $this->hourlyRateOnSite = $hourlyRateOnSite;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getHourlyRateOffSite(): ?float
    {
        return $this->hourlyRateOffSite;
    }

    /**
     * @param float|null $hourlyRateOffSite
     * @return ProjectRate
     */
    public function setHourlyRateOffSite(?float $hourlyRateOffSite): self
    {
        $this->hourlyRateOffSite = $hourlyRateOffSite;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTimeInterface $startedAt
     * @return ProjectRate
     */
    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }
}
