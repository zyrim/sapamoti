<?php

namespace AppBundle\Controller;

use AppBundle\Entity\FinanceAccount;
use AppBundle\Entity\FinanceMovement;
use AppBundle\Entity\User;
use AppBundle\Form\FinanceMovementForm;
use AppBundle\Repository\FinanceAccountRepository;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class FinanceController
 *
 * @package AppBundle\Controller
 */
class FinanceController extends Controller
{
    /**
     * @var FinanceAccountRepository
     */
    protected $repository;

    /**
     * @var FinanceAccount
     */
    protected $account;

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        if (!$this->getUser() instanceof User) {
            return $this->redirectToRoute('security_login');
        }

        $accountsArray = [];
        /** @var FinanceAccountRepository $repo */
        $repo = $this->getRepository();
        $accounts = $repo->findAll();

        /** @var FinanceAccount $account */
        foreach ($accounts as $account) {
            $accountsArray[] = [
                'account' => $account,
                'movements' => $account->getMovements()->count(),
                'amount'  => $account->getAmount()
            ];
        }

        return $this->render('finance/index.html.twig', ['accounts' => $accountsArray]);
    }

    /**
     * @Route("/finance/add", name="addAccount")
     */
    public function addAccountAction(Request $request)
    {
        $account = new FinanceAccount();
        $account->setName('')
            ->setAmount(0.0);

        $form = $this->createFormBuilder($account)
            ->add('name', TextType::class)
            ->add('amount', NumberType::class)
            ->add('save', SubmitType::class, ['label' => 'Account erstellen'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $account = $form->getData();

            $this->getDoctrine()->getManager()->persist($account);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('finance/addaccount.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/finance/{id}", name="account")
     */
    public function accountAction(Request $request, $id = 0)
    {
        $account = $this->getAccount($id);
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
     * @Route("/finance/{id}/edit", name="finance_edit")
     */
    public function editAction(Request $request, int $id = 0)
    {
        $account = $this->getAccount($id);
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
     * @Route("/finance/{id}/movements", name="account_movements")
     */
    public function movementsAction(Request $request, int $id = 0)
    {
        $account = $this->getAccount($id);
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

        return $this->render('finance/finance.html.twig', [
            'account' => $account,
            'movements' => $movements,
            'template' => 'finance/movements.html.twig',
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