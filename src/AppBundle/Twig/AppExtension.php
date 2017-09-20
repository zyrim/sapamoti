<?php

namespace AppBundle\Twig;

/**
 * Class AppExtension
 *
 * @package AppBundle\Twig
 */
class AppExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('movement', [$this, 'movementFilter'])
        ];
    }

    /**
     * @param float $number
     *
     * @return string
     */
    public function movementFilter(float $number)
    {
        $color = 'green';

        if ($number < 0) {
            $color = 'red';
        } elseif ($number == 0) {
            $color = 'black';
        }

        return '<span style="font-weight: bold; color: ' . $color. '">' . $number . '</span>';
    }
}