<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Param;
use App\Repository\ParamRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class ParamCollectionType extends AbstractType
{
    /**
     * @var ParamRepository
     */
    private $repository;

    public function __construct(ParamRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Param[] $params */
        $params = $this->repository->findAll();

        foreach ($params as $param) {

            $builder->add(
                $param->getCode(),
                TextareaType::class,
                [
                    'attr' => ['rows' => 3],
                    'data' => $param->getValue(),
                    'label' => $param->getDescription(),
                    'required' => true,
                ]
            );

        }
    }
}
