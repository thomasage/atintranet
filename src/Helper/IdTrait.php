<?php
declare(strict_types=1);

namespace App\Helper;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Trait IdTrait
 * @package App\Helper
 */
trait IdTrait
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid", unique=true)
     */
    private $uuid;

    /**
     * IdTrait constructor.
     */
    public function __construct()
    {
        try {
            $this->uuid = Uuid::uuid4();
        } catch (\Exception $e) {
            exit('Unable to generate UUID');
        }
    }

    public function __clone()
    {
        $this->id = null;
        try {
            $this->uuid = Uuid::uuid4();
        } catch (\Exception $e) {
            exit('Unable to generate UUID');
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
