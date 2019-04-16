<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Search;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class SearchManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function find(User $user, string $route): Search
    {
        $search = $this->em->getRepository(Search::class)->findOneBy(['user' => $user, 'route' => $route]);

        if (!$search instanceof Search) {
            $search = new Search();
            $search
                ->setRoute($route)
                ->setUser($user);
            $this->em->persist($search);
            $this->em->flush();
        }

        return $search;
    }

    public function handleRequest(Search $search, Request $request, FormInterface $form): bool
    {
        $reload = false;

        if ($request->query->has('empty')) {
            $search->setFilter([]);
            $this->em->flush();
            $reload = true;
        }

        if ($request->query->has('page')) {
            $search->setPage($request->query->getInt('page'));
            $this->em->flush();
            $reload = true;
        }

        if ($request->query->has('orderby')) {
            $orderby = $request->query->get('orderby');
            $reverse = $request->query->getBoolean('reverse');
            $search->addOrderby($orderby, $reverse);
            $this->em->flush();
            $reload = true;
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->getData() as $k => $v) {
                if (null === $v) {
                    $search->removeFilter($k);
                } else {
                    $search->addFilter($k, $v);
                }
            }
            $search->setPage(0);
            $this->em->flush();
            $reload = true;
        }

        if (!$reload) {
            foreach ($search->getFilter() as $k => $v) {
                if ($form->has($k)) {
                    $form->get($k)->setData($v);
                }
            }
        }

        return $reload;
    }
}
