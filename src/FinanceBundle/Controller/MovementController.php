<?php

namespace FinanceBundle\Controller;

use AppBundle\Controller\AbstractController;
use FinanceBundle\Form\FinanceMovementForm;
use FinanceBundle\Entity\{
    FinanceAccount, FinanceMovement
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\{
    Request, Response, RedirectResponse
};

/**
 * Class MovementController
 *
 * @package FinanceBundle\Controller
 */
class MovementController extends AbstractController
{
    /**
     * Show all movements of a finance account.
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/finance/movements/{financeAccountId}", name="finance_movements")
     */
    public function indexAction(Request $request): Response
    {
        /** @var FinanceAccount $account */
        $account   = $this->getEntity();
        $show      = $request->get('show', 'all');
        $movements = $account->getMovements($show);

        return $this->render('@Finance/Account/finance.html.twig', [
            'account'   => $account,
            'movements' => $movements,
            'template'  => '@Finance/Movement/index.html.twig',
        ]);
    }

    /**
     * @param Request $request
     * @return Response|RedirectResponse
     *
     * @Route("finance/movements/{financeAccountId}/add", name="finance_movements_add")
     */
    public function addAction(Request $request)
    {
        /** @var FinanceAccount $account */
        $account = $this->getEntity();

        /**
         * @todo:
         * Not tested, because no movements available from earlier
         * than october.
         * Test this at the beginning of november.
         */
        $endOfMonth = new \DateTime();
        $interval   = new \DateInterval('P1M');
        $endOfMonth->add($interval);

        // When at the end of month or new month
        if (date('Y-m-d') >= $endOfMonth->format('Y-m-t')) {
            $amount = $account->getAmount();

            foreach ($account->getMovements() as $movement) {
                // Add the amount of all movements from the previous month
                // to the current amount of the account
                if ($movement->getDate()->format('Y-m-d') <= date('Y-m-01')) {
                    $amount += $movement->getAmount();
                }
            }

            // and update it
            $account->setAmount($amount);
            $this->entityManager()->flush($account);
        }

        $movement = new FinanceMovement($account);
        $movement
            ->setDescription('')
            ->setAmount(0.0)
            ->setDate(new \DateTime());

        $form = $this->createForm(FinanceMovementForm::class, $movement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager()->persist($movement);
            $this->entityManager()->flush();

            return $this->redirectToRoute('finance_movements', ['financeAccountId' => $account->getFinanceAccountId()]);
        }

        return $this->render('@Finance/Account/finance.html.twig', [
            'template' => '@Finance/Movement/add.html.twig',
            'account'  => $account,
            'form'     => $form->createView()
        ]);
    }

    /**
     * Edit (or delete) a movement.
     *
     * @param Request $request
     * @return Response|RedirectResponse
     *
     * @Route("/finance/movement/{financeMovementId}", name="finance_movements_edit")
     */
    public function editAction(Request $request)
    {
        /** @var FinanceMovement $movement */
        $movement = $this->getEntity();
        $account  = $movement->getAccount();

        $form = $this->createForm(FinanceMovementForm::class, $movement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->entityManager();

            if ($form->get('_remove')->isClicked()) {
                $em->remove($movement);
            } elseif (!$form->get('_editAmount')->isEmpty()) {
                $additionalAmount = (float)$form->get('_editAmount')->getData();
                $movement->updateAmount($additionalAmount);
            }

            $em->flush();

            return $this->redirectToRoute('finance_movements', ['financeAccountId' => $account->getFinanceAccountId()]);
        }

        return $this->render('@Finance/Account/finance.html.twig', [
            'account'  => $account,
            'template' => '@Finance/Movement/edit.html.twig',
            'movement' => $movement,
            'form'     => $form->createView()
        ]);
    }

    /**
     * @return Response
     *
     * @Route("/finance/strategize/{financeAccountId}", name="finance_movements_strategize")
     */
    public function strategizeAction(): Response
    {
        /** @var FinanceAccount $account */
        $account = $this->getEntity();
        $values  = [
            'account'  => $account,
            'template' => '@Finance/Movement/strategize.html.twig'
        ];

        return $this->render('@Finance/Account/finance.html.twig', $values);
    }

}
