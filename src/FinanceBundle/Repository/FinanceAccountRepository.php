<?php

namespace FinanceBundle\Repository;

use FinanceBundle\Entity\FinanceAccount;

/**
 * Class FinanceAccountRepository
 *
 * @package FinanceBundle\Repository
 */
class FinanceAccountRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Remove a account and its movements.
     *
     * @param FinanceAccount $account
     */
    public function remove(FinanceAccount $account)
    {
        foreach ($account->getMovements() as $movement) {
            $this->getEntityManager()->remove($movement);
        }

        $this->getEntityManager()->remove($account);
    }
}
