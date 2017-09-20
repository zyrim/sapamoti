<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class FinanceAccount
 *
 * @package AppBundle
 *
 * @ORM\Table(name="finance_account")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FinanceAccountRepository")
 */
class FinanceAccount
{
    /**
     * @var int
     *
     * @ORM\Column(name="finance_account_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $financeAccountId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=30)
     */
    private $name;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float", nullable=false)
     */
    private $amount;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|FinanceMovement[]
     *
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\FinanceMovement", mappedBy="account")
     * @ORM\OrderBy({"financeMovementId" = "DESC"})
     */
    private $movements;


    /**
     * Get id
     *
     * @return int
     */
    public function getFinanceAccountId(): int
    {
        return $this->financeAccountId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return FinanceAccount
     */
    public function setName(string $name): FinanceAccount
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return FinanceAccount
     */
    public function setAmount(float $amount): FinanceAccount
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Set movements
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|FinanceMovement[] $movements
     *
     * @return FinanceAccount
     */
    public function setMovements($movements): FinanceAccount
    {
        $this->movements = $movements;

        return $this;
    }

    /**
     * @param FinanceMovement $movement
     *
     * @return FinanceAccount
     */
    public function addMovement(FinanceMovement $movement): FinanceAccount
    {
        $this->movements->add($movement);

        return $this;
    }

    /**
     * Get movements
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|FinanceMovement[]
     */
    public function getMovements()
    {
        return $this->movements;
    }
}

