<?php

namespace FinanceBundle\Entity;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $movements;

    /**
     * @var ArrayCollection|Status[]
     *
     * @ORM\OneToMany(targetEntity="FinanceBundle\Entity\Status", mappedBy="account", cascade={"persist", "remove"})
     */
    private $status;

    /**
     * FinanceAccount constructor.
     */
    public function __construct()
    {
        $this->setMovements(new ArrayCollection());
        $this->setStatus(new ArrayCollection());
    }

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
     * @deprecated Remove uses with parameter.
     *
     * @param bool $dontCalulate If set to true,
     * the current actual value without calculation is returned
     *
     * @param null|FinanceMovement $movement
     * @return float
     */
    public function getAmount(bool $dontCalulate = false): float
    {
        if (!$dontCalulate) {
            return round($this->amount, 2);
        }

        return $this->getTotalAmount();
    }

    /**
     * Return the current amount
     * added together with all movements amounts (if available).
     *
     * @return float
     */
    public function getTotalAmount()
    {
        $amount = $this->amount;

        if (!$this->getMovements()->count()) {
            return round($amount, 2);
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

        return round($amount, 2);
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
     * @param string $type Type to filter the movements
     * @return \Doctrine\Common\Collections\ArrayCollection|FinanceMovement[]
     */
    public function getMovements(string $type = 'all')
    {
        $movements = $this->movements;

        if ($type != 'all') {
            $movements = $this->movements->filter(function (FinanceMovement $movement) use ($type) {
                if ($type == FinanceMovement::MOVEMENT_PLUS) {
                    return $movement->getAmount() > 0;
                } elseif ($type == FinanceMovement::MOVEMENT_MINUS) {
                    return $movement->getAmount() < 0;
                }
            });
        }

        return $movements;
    }

    /**
     * @see ArrayCollection::count()
     */
    public function countMovements()
    {
        return $this->movements->count();
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

    /**
     * @param ArrayCollection|Status[] $status
     * @return FinanceAccount
     */
    public function setStatus($status): FinanceAccount
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param Status $status
     * @return FinanceAccount
     */
    public function addStatus(Status $status): FinanceAccount
    {
        if (!$this->status->contains($status)) {
            $this->status->add($status);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|Status[]
     */
    public function getStatus()
    {
        return $this->status;
    }
}

