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
     * @var FinanceAccount
     */
    protected $entity;

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
        $form->get('_amount')->setData($account->getAmount());
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