<?php

namespace App\Tests\Internal\Traits;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Container;

trait PurgeDatabaseTrait
{
    abstract protected function container(): Container;

    protected function purgeDatabase(): void
    {
        $em = $this->container()->get(EntityManagerInterface::class);
        $purger = new ORMPurger($em);
        $purger->purge();
    }

}
