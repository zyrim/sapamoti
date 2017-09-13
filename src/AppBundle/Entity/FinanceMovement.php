<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FinanceMovement
 *
 * @ORM\Table(name="finance_movement")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FinanceMovementRepository")
 */
class FinanceMovement
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $financeMovementId;

    /**
     * @var FinanceAccount
     *
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\FinanceAccount", inversedBy="movements")
     * @ORM\JoinColumn(name="finance_account_id", referencedColumnName="finance_account_id")
     */
    protected $account;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    protected $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    protected $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    protected $date;


    /**
     * Get id
     *
     * @return int
     */
    public function getFinanceMovementId(): int
    {
        return $this->financeMovementId;
    }

    /**
     * @return FinanceAccount
     */
    public function getAccount(): FinanceAccount
    {
        return $this->account;
    }

    /**
     * @param FinanceAccount $account
     *
     * @return FinanceMovement
     */
    public function setAccount(FinanceAccount $account): FinanceMovement
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return FinanceMovement
     */
    public function setAmount(float $amount): FinanceMovement
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
     * Set description
     *
     * @param string $description
     *
     * @return FinanceMovement
     */
    public function setDescription(string $description): FinanceMovement
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return FinanceMovement
     */
    public function setDate(\DateTime $date): FinanceMovement
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

