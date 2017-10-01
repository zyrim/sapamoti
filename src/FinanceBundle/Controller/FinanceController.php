<?php

namespace FinanceBundle\Controller;

use AppBundle\Entity\FinanceAccount;
use AppBundle\Entity\FinanceMovement;
use AppBundle\Entity\User;
use AppBundle\Form\FinanceMovementForm;
use AppBundle\Repository\FinanceAccountRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Controller\AbstractController as Controller;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FinanceController
 *
 * @package FinanceBundle\Controller
 */
class FinanceController extends Controller
{
    /**
     * @var FinanceAccount
     */
    protected $account;

    /**
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $accountsArray = [];
        /** @var FinanceAccountRepository $repo */
        $repo = $this->getRepository();
        $accounts = $repo->findAll();

        /** @var FinanceAccount $account */
        foreach ($accounts as $account) {
            $accountsArray[] = [
                'account' => $account,
                'movements' => $account->getMovements()->count(),
                'amount' => $account->getAmount()
            ];
        }

        return $this->render('@Finance/Finance/index.html.twig', ['accounts' => $accountsArray]);
    }

    /**
     * @Route("/finance/add", name="addAccount")
     */
    public function addAccountAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('_name', TextType::class)
            ->add('_amount', NumberType::class)
            ->add('_save', SubmitType::class, ['label' => 'Account erstellen'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $account = new FinanceAccount();
            $account
                ->setName($data['_name'])
                ->setAmount($data['_amount'])
            ;

            $this->getDoctrine()->getManager()->persist($account);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('finance/addaccount.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/finance/{financeAccountId}", name="account")
     */
    public function accountAction(Request $request, $financeAccountId = 0)
    {
        $account = $this->getAccount($financeAccountId);
        $values = [
            'account' => $account,
            'fixed' => $account->getFixedMovements(),
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
            ->setFixed(false)
        ;
        $form = $this->createForm(FinanceMovementForm::class, $movement);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $movement = $form->getData();

            $this->getDoctrine()->getManager()->persist($movement);
            $this->getDoctrine()->getManager()->flush();

            $account->addMovement($movement);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('account', ['id' => $id]);
        }

        $values['form'] = $form->createView();

        return $this->render('finance/account.html.twig', $values);
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @Route("/finance/{financeAccountId}/edit", name="finance_edit")
     */
    public function editAction(Request $request, int $financeAccountId = 0)
    {
        $account = $this->getAccount($financeAccountId);
        $form = $this->createFormBuilder()
            ->add('_name', TextType::class, ['label' => 'Bezeichnung'])
            ->add('_amount', NumberType::class, ['label' => 'Aktuelle Summe'])
            ->add('_save', SubmitType::class, ['label' => 'Speichern'])
        ;
        $form->get('_name')->setData($account->getName());
        $form->get('_amount')->setData($account->getAmount(true));
        $form = $form->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $account->setName($data['_name'])->setAmount($data['_amount']);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('account', ['id' => $account->getFinanceAccountId()]);
        }

        return $this->render('finance/finance.html.twig', [
            'account' => $account,
            'template' => 'finance/edit.html.twig',
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @Route("/finance/{financeAccountId}/movements", name="account_movements")
     */
    public function movementsAction(Request $request, int $financeAccountId = 0)
    {
        $account = $this->getAccount($financeAccountId);
        $movements = $account->getMovements();
        $show = $request->get('show', 'all');

        if ($show != 'all') {
            $movements = $movements->filter(function (FinanceMovement $movement) use ($show) {
                if ($show == 'plus') {
                    return $movement->getAmount() >= 0;
                } elseif ($show == 'minus') {
                    return $movement->getAmount() < 0;
                }
            });
        }

        return $this->render('@Finance/Finance/finance.html.twig', [
            'account' => $account,
            'movements' => $movements,
            'template' => '@Finance/Finance/movements.html.twig',
        ]);
    }

    /**
     * @Route("/finance/movement/{id}", name="account_movement_edit")
     */
    public function editMovementAction(Request $request, int $id = 0)
    {
        $movement = $this->getDoctrine()->getRepository(FinanceMovement::class)->find($id);

        if (!$movement instanceof FinanceMovement) {
            throw new \InvalidArgumentException('Invalid movement id ' . $id);
        }

        $account = $movement->getAccount();

        $form = $this->createForm(FinanceMovementForm::class, $movement)
            ->remove('_save')
            ->add('_save', SubmitType::class, ['label' => 'Speichern'])
            ->add('_remove', SubmitType::class, [
                'label' => 'Löschen',
                'attr' => [
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

            return $this->redirectToRoute('account_movements', ['id' => $account->getFinanceAccountId()]);
        }

        return $this->render('finance/finance.html.twig', [
            'account' => $account,
            'template' => 'finance/movement.html.twig',
            'form' => $form->createView()
        ]);
    }

    /**
     * @return FinanceAccountRepository
     */
    protected function getRepository(): FinanceAccountRepository
    {
        if (!$this->repository) {
            $this->repository = $this->getDoctrine()->getRepository(FinanceAccount::class);
        }

        return $this->repository;
    }

    /**
     * @param int $id
     *
     * @return FinanceAccount
     * @throws \Exception
     */
    protected function getAccount(int $id = 0): FinanceAccount
    {
        if (!$this->account) {
            $this->account = $this->getRepository()->find($id);

            if (!$this->account instanceof FinanceAccount) {
                throw new \Exception('Invalid account-id ' . $id);
            }
        }

        return $this->account;
    }
}