<?php

namespace FinanceBundle\Controller;

use AppBundle\Controller\AbstractController;
use AppBundle\Form\FinanceMovementForm;
use FinanceBundle\Entity\{
    FinanceAccount, FinanceMovement
};
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    public function indexAction(Request $request)
    {
        /** @var FinanceAccount $account */
        $account   = $this->getEntity();
        $movements = $account->getMovements();
        $show      = $request->get('show', 'all');

        if ($show != 'all') {
            $movements = $movements->filter(function (FinanceMovement $movement) use ($show) {
                if ($show == FinanceMovement::MOVEMENT_PLUS) {
                    return $movement->getAmount() > 0;
                } elseif ($show == FinanceMovement::MOVEMENT_MINUS) {
                    return $movement->getAmount() < 0;
                }
            });
        }

        return $this->render('@Finance/Account/finance.html.twig', [
            'account'   => $account,
            'movements' => $movements,
            'template'  => '@Finance/Movement/index.html.twig',
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

        $form = $this->createForm(FinanceMovementForm::class, $movement)
            ->remove('_save')
            ->add('_save', SubmitType::class, ['label' => 'Speichern'])
            ->add('_remove', SubmitType::class, [
                'label' => 'Löschen',
                'attr'  => [
                    'class' => 'btn btn-danger'
                ]
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($form->get('_remove')->isClicked()) {
                $em->remove($movement);
            }

            $em->flush();

            return $this->redirectToRoute('finance_movements', ['financeAccountId' => $account->getFinanceAccountId()]);
        }

        return $this->render('@Finance/Account/finance.html.twig', [
            'account'  => $account,
            'template' => '@Finance/Account/edit.html.twig',
            'form'     => $form->createView()
        ]);
    }

    /**
     * @Route("/finance/strategize/{financeAccountId}", name="finance_movements_strategize")
     */
    public function strategizeAction()
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
