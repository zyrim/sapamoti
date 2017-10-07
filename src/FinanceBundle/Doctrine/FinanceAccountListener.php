<?php


namespace FinanceBundle\Doctrine;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
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

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        // Only continue on FinanceAccount entities
        if (!$entity instanceof FinanceAccount) {
            return;
        }

        if (!$args->hasChangedField('amount')) {
            return;
        }

        $newValue = $args->getNewValue('amount');
        $entity->addStatus(new Status($entity, $newValue));
    }
}