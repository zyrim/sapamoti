<?php


namespace FinanceBundle\Doctrine;


use Doctrine\ORM\Event\LifecycleEventArgs;
use FinanceBundle\Entity\FinanceAccount;
use FinanceBundle\Entity\Status;

class FinanceAccountListener
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // Only continue on FinanceAccount entities
        if (!$entity instanceof FinanceAccount) {
            return;
        }

        $entity->addStatus(new Status($entity));
    }
}