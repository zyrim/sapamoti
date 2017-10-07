<?php

namespace FinanceBundle\Controller;

use FinanceBundle\Entity\{
    FinanceAccount, FinanceMovement
};
use AppBundle\Form\FinanceMovementForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Controller\AbstractController as Controller;
use Symfony\Component\Form\Extension\Core\Type\{
    NumberType, SubmitType, TextType
};
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\{
    Request, Response, RedirectResponse
};

/**
 * Class AccountController
 *
 * This controller handles all pages responsible
 * for finance account administration.
 *
 * @package FinanceBundle\Controller
 */
class AccountController extends Controller
{
    /**
     * Show a finance account with all its movements.
     *
     * @param Request $request
     * @return RedirectResponse|Response
     *
     * @Route("/finance/account/show/{financeAccountId}", name="finance_account")
     */
    public function showAction(Request $request)
    {
        /** @var FinanceAccount $account */
        $account = $this->getEntity();
        $em      = $this->entityManager();

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
            $em->flush($account);
        }

        $values = [
            'account'   => $account,
            'fixed'     => $account->getFixedMovements(),
            'movements' => []
        ];

        foreach ($account->getMovements() as $movement) {
            if (!$movement->isFixed()) {
                $values['movements'][] = [
                    'description' => $movement->getDescription(),
                    'amount'      => $movement->getAmount()
                ];
            }
        }

        $movement = new FinanceMovement();
        $movement
            ->setAccount($account)
            ->setAmount(0.0)
            ->setDescription('')
            ->setDate(new \DateTime())
            ->setFixed(false);
        $form = $this->createForm(FinanceMovementForm::class, $movement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var FinanceMovement $movement */
            $movement = $form->getData();

            if ($movement->getAmount() == 0) {
                $form->get('_amount')->addError(new FormError('Es muss ein Betrag unter oder über 0 angegeben werden.'));
                $values['form'] = $form->createView();

                return $this->render('@Finance/Account/show.html.twig', $values);
            }

            $em->persist($movement);
            $em->flush();

            $account->addMovement($movement);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('finance_account', ['financeAccountId' => $account->getFinanceAccountId()]);
        }

        $values['form'] = $form->createView();

        return $this->render('@Finance/Account/show.html.twig', $values);
    }

    /**
     * Add a new finance account.
     *
     * @param Request $request
     * @return RedirectResponse|Response
     *
     * @Route("/finance/account/add", name="finance_account_add")
     */
    public function addAction(Request $request)
    {
        $em   = $this->entityManager();
        $form = $this->createFormBuilder()
            ->add('_name', TextType::class)
            ->add('_amount', NumberType::class)
            ->add('_save', SubmitType::class, ['label' => 'Account erstellen'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data    = $form->getData();
            $account = new FinanceAccount();
            $account
                ->setUser($this->getUser())
                ->setName($data['_name'])
                ->setAmount($data['_amount']);

            $em->persist($account);
            $em->flush();

            return $this->redirectToRoute('finance_account', ['financeAccountId' => $account->getFinanceAccountId()]);
        }

        return $this->render('@Finance/Account/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Edit (or delete) a finance account.
     *
     * @param Request $request
     * @return Response|RedirectResponse
     *
     * @Route("/finance/account/edit/{financeAccountId}", name="finance_account_edit")
     */
    public function editAction(Request $request)
    {
        /** @var FinanceAccount $account */
        $account = $this->getEntity();
        $em      = $this->entityManager();
        $form    = $this->createFormBuilder()
            ->add('_name', TextType::class, ['label' => 'Bezeichnung'])
            ->add('_amount', NumberType::class, ['label' => 'Aktuelle Summe'])
            ->add('_save', SubmitType::class, ['label' => 'Speichern'])
            ->add('_remove', SubmitType::class, [
                'label' => 'Löschen',
                'attr'  => [
                    'class' => 'btn btn-danger'
                ]
            ]);;
        $form->get('_name')->setData($account->getName());
        $form->get('_amount')->setData($account->getAmount(true));
        $form = $form->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('_remove')->isClicked()) {
                $em->remove($account);
                $em->flush();

                return $this->redirectToRoute('finance_index');
            }

            $data = $form->getData();
            $account->setName($data['_name'])->setAmount($data['_amount']);
            $em->flush($account);

            return $this->redirectToRoute('finance_account', ['financeAccountId' => $account->getFinanceAccountId()]);
        }

        return $this->render('@Finance/Account/finance.html.twig', [
            'account'  => $account,
            'template' => '@Finance/Account/edit.html.twig',
            'form'     => $form->createView()
        ]);
    }
}