<?php
/**
 * FinanceBundle
 *
 * @namespace
 */
namespace FinanceBundle\Controller;

use AppBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FinanceController
 *
 * @package FinanceBundle
 */
class FinanceController extends AbstractController
{
    /**
     * Show all finance accounts of the user.
     *
     * @return Response
     *
     * @Route("/finance", name="finance_index")
     */
    public function indexAction()
    {
        $accountsArray = [];
        /** @var \FinanceBundle\Repository\FinanceAccountRepository $repo */
        $repo = $this->entityManager()->getRepository(\FinanceBundle\Entity\FinanceAccount::class);
        $accounts = $repo->findAll();

        /** @var \FinanceBundle\Entity\FinanceAccount $account */
        foreach ($accounts as $account) {
            $accountsArray[] = [
                'account' => $account,
                'movements' => $account->getMovements()->count(),
                'amount' => $account->getAmount()
            ];
        }

        return $this->render('@Finance/Finance/index.html.twig', ['accounts' => $accountsArray]);
    }

}
