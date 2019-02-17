<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="app_search")
 * @ORM\Entity(repositoryClass="App\Repository\SearchRepository")
 * @UniqueEntity(fields={"route", "user"})
 */
class Search
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $route;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $page = 0;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $filter = [];

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $orderby = [];

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $resultsPerPage = 20;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * @param string $route
     * @return Search
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Search
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return Search
     */
    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     * @return Search
     */
    public function setFilter(array $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return array
     */
    public function getOrderby(): array
    {
        return $this->orderby;
    }

    /**
     * @param array $orderby
     * @return Search
     */
    public function setOrderby(array $orderby): self
    {
        $this->orderby = $orderby;

        return $this;
    }

    /**
     * @param int $results
     * @return int
     */
    public function getPages(int $results): int
    {
        return (int)ceil($results / $this->resultsPerPage);
    }

    /**
     * @return int
     */
    public function getResultsPerPage(): int
    {
        return $this->resultsPerPage;
    }

    /**
     * @param int $resultsPerPage
     * @return Search
     */
    public function setResultsPerPage(int $resultsPerPage): self
    {
        $this->resultsPerPage = $resultsPerPage;

        return $this;
    }
}
