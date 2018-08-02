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
 * This controller is the main entry point
 * to the finance bundle.
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
    public function indexAction(): Response
    {
        /** @var \FinanceBundle\Repository\FinanceAccountRepository $repo */
        $repo     = $this->entityManager()->getRepository(\FinanceBundle\Entity\FinanceAccount::class);
        $accounts = $repo->findBy(['user' => $this->getUser()]);

        return $this->render('@Finance/Finance/index.html.twig', ['accounts' => $accounts]);
    }

}
