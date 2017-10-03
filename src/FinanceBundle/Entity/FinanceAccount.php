<?php

namespace FinanceBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class FinanceAccount
 *
 * @package FinanceBundle
 *
 * @ORM\Table(name="finance_account")
 * @ORM\Entity(repositoryClass="FinanceBundle\Repository\FinanceAccountRepository")
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="financeAccounts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

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
     * @ORM\OneToMany(targetEntity="FinanceBundle\Entity\FinanceMovement", mappedBy="account")
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
     * Get user
     *
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return FinanceAccount
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
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
     * @param bool $dontCalulate If set to true,
     * the current actual value without calculation is returned
     *
     * @param null|FinanceMovement $movement
     * @return float
     */
    public function getAmount(bool $dontCalulate = false): float
    {
        $amount = $this->amount;

        if ($dontCalulate) {
            return $amount;
        }

        if (!$this->getMovements()->count()) {
            return $amount;
        }

        $fixed = [];

        foreach ($this->getMovements() as $movement) {
            if ($movement->isFixed()) {
                $fixed[] = $movement;
                continue;
            }

            $amount += $movement->getAmount();
        }

        // When in new month, add fixed movements.
        if (date('Y-m-d') >= date('Y-m-01')) {
            /** @var FinanceMovement $movement */
            foreach ($fixed as $movement) {
                // Positive movements shall only be edited if date is matching.
                if (
                    $movement->getAmount() < 0
                    || ($movement->getAmount() > 0
                    && date('Y-m-d') >= $movement->getDate()->format('Y-m-d'))
                ) {
                    $amount += $movement->getAmount();
                }
            }
        }

        return $amount;
    }

    /**
     * Calculate the value of all previous movements until the current.
     *
     * @param FinanceMovement $movement Movement before or until the amount should be calculated to
     * @param bool $before True = Dont include the movement's value. False = include it
     * @return float
     */
    public function getAmountUntil(FinanceMovement $movement, bool $before)
    {
        $amount = $this->amount;

        foreach ($this->getMovements() as $financeMovement) {
            if ($before && $movement->getFinanceMovementId() > $financeMovement->getFinanceMovementId()) {
                // Amount before the current movement
                $amount += $financeMovement->getAmount();
            } elseif (!$before && $movement->getFinanceMovementId() >= $financeMovement->getFinanceMovementId()) {
                // Amount including the current movement
                $amount += $financeMovement->getAmount();
            }
        }

        return $amount;
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

    /**
     * Get only fixed movements.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection|FinanceMovement[]
     */
    public function getFixedMovements()
    {
        return $this->getMovements()->filter(function (FinanceMovement $movement) {
            return $movement->isFixed();
        });
    }

    /**
     * Get the sum of all movements.
     *
     * @return float|int
     */
    public function getMovementsAmountSum()
    {
        $amount = 0.0;

        foreach ($this->getMovements() as $movement) {
            if (
                $movement->getAmount() < 0
                || ($movement->getAmount() > 0
                    && date('Y-m-d') >= $movement->getDate()->format('Y-m-d'))
            ) {
                $amount += $movement->getAmount();
            }
        }

        return $amount;
    }
}

