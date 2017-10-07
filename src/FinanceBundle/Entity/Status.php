<?php
/**
 * FinanceBundle
 *
 * @namespace
 */

namespace FinanceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Status
 *
 * An entity to save all updated statuses of a finance account.
 *
 * @ORM\Table(name="finance_status")
 * @ORM\Entity(repositoryClass="FinanceBundle\Repository\StatusRepository")
 */
class Status
{
    /**
     * @var int
     *
     * @ORM\Column(name="status_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $statusId;

    /**
     * @var FinanceAccount
     *
     * @ORM\ManyToOne(targetEntity="FinanceBundle\Entity\FinanceAccount", inversedBy="status")
     * @ORM\JoinColumn(name="finance_account_id", referencedColumnName="finance_account_id")
     */
    private $account;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * Status constructor.
     *
     * @param FinanceAccount $account
     * @param null|float $newValue
     */
    public function __construct(FinanceAccount $account, float $newValue = null)
    {
        $this->setAccount($account);
        $amount = $account->getAmount();

        if ($newValue) {
            $amount = $newValue;
        }

        $this->setAmount($amount);
        $this->setDate(new \DateTime());
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getStatusId(): int
    {
        return $this->statusId;
    }

    /**
     * Set account
     *
     * @param FinanceAccount $account
     *
     * @return Status
     */
    public function setAccount(FinanceAccount $account): Status
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return FinanceAccount
     */
    public function getAccount(): FinanceAccount
    {
        return $this->account;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Status
     */
    public function setAmount(float $amount): Status
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Status
     */
    public function setDate(\DateTime $date): Status
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }
}

