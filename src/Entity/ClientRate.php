<?php

declare(strict_types=1);

namespace App\Entity;

use App\Helper\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Table(name="app_client_rate")
 * @ORM\Entity(repositoryClass="App\Repository\ClientRateRepository")
 */
class ClientRate implements RateInterface
{
    use IdTrait {
        IdTrait::__construct as protected IdTraitConstruct;
    }
    use TimestampableEntity;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="rates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $hourlyRateOnSite;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $hourlyRateOffSite;

    /**
     * @ORM\Column(type="date")
     */
    private $startedAt;

    public function __construct()
    {
        $this->IdTraitConstruct();
        $this->startedAt = new \DateTime();
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getHourlyRateOnSite(): ?float
    {
        return $this->hourlyRateOnSite;
    }

    public function setHourlyRateOnSite(?float $hourlyRateOnSite): self
    {
        $this->hourlyRateOnSite = $hourlyRateOnSite;

        return $this;
    }

    public function getHourlyRateOffSite(): ?float
    {
        return $this->hourlyRateOffSite;
    }

    public function setHourlyRateOffSite(?float $hourlyRateOffSite): self
    {
        $this->hourlyRateOffSite = $hourlyRateOffSite;

        return $this;
    }

    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }
}
