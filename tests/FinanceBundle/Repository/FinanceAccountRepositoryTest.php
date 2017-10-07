<?php

use FinanceBundle\Repository\FinanceAccountRepository;
use FinanceBundle\Entity\{FinanceAccount, Status};

class FinanceAccountRepositoryTest extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    private $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var FinanceAccountRepository
     */
    private $repository;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->container  = static::$kernel->getContainer();
        $this->em         = $this->container->get('doctrine')->getManager();
        $this->repository = $this->em->getRepository(FinanceAccount::class);
    }

    public function testCreateAndUpdateAccount()
    {
        $testDate        = date('Y-m-d');
        $testAccountName = 'TESTACCOUNT' . $testDate;
        $testAmount      = 150;
        $account         = new FinanceAccount();
        $account->setName($testAccountName)
            ->setAmount($testAmount);

        $this->em->persist($account);
        $this->em->flush($account);

        // Check if a test account was successfully created
        $this->assertTrue(is_int($account->getFinanceAccountId()),
            'Failed to create test account object.');

        // Check if a Status entity has been created due peristence.
        $this->assertInstanceOf(Status::class, $account->getStatus()->first(),
            'Failed to create initial status object.');

        // Check if the initial Status entity holds the same amount as the account
        /** @var Status $status */
        $status = $account->getStatus()->first();
        $this->assertEquals($account->getAmount(), $status->getAmount(),
            'Values of account and its initial status object are not equal.');

        // Update account's amount
        $testAmount2 = 200;
        $account->setAmount($testAmount2);

        $this->em->flush($account);

        // Check if a new Status object has been added
        $this->assertCount(2, $account->getStatus(),
            'Failed to create status object due account update.');

        $status = $account->getStatus()->last();
        // Check if new Status has same value as $testAmount2
        $this->assertEquals($testAmount2, $status->getAmount(),
            'Value of new added status does not match.');

        // Everything went fine, now delete the account and its statuses.
        $this->em->remove($account);
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }
}
