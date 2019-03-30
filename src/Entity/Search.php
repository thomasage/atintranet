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
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
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
     *
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
     *
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
     *
     * @return Search
     */
    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param string|null $name
     *
     * @return mixed
     */
    public function getFilter(?string $name = null)
    {
        if (null !== $name) {
            return $this->filter[$name] ?? null;
        }

        return $this->filter;
    }

    /**
     * @param array $filter
     *
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
     *
     * @return Search
     */
    public function setOrderby(array $orderby): self
    {
        $this->orderby = $orderby;

        return $this;
    }

    /**
     * @param int $results
     *
     * @return int
     */
    public function getPages(int $results): int
    {
        return (int) ceil($results / $this->resultsPerPage);
    }

    /**
     * @return int|null
     */
    public function getResultsPerPage(): ?int
    {
        return $this->resultsPerPage;
    }

    /**
     * @param int|null $resultsPerPage
     *
     * @return Search
     */
    public function setResultsPerPage(?int $resultsPerPage): self
    {
        $this->resultsPerPage = $resultsPerPage;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return Search
     */
    public function addFilter(string $name, $value): self
    {
        if (null === $value) {
            return $this->removeFilter($name);
        }

        $this->filter[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Search
     */
    public function removeFilter(string $name): self
    {
        unset($this->filter[$name]);

        return $this;
    }

    /**
     * @param string $code
     * @param bool   $asc
     *
     * @return Search
     */
    public function addOrderby(string $code, bool $asc = true): self
    {
        $orderby = $this->orderby;
        $this->orderby = [$code => $asc];
        foreach ($orderby as $k => $v) {
            if ($k === $code) {
                continue;
            }
            $this->orderby[$k] = $v;
        }

        return $this;
    }
}
