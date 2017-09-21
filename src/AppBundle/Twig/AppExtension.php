<?php

namespace AppBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AppExtension
 *
 * @package AppBundle\Twig
 */
class AppExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * @var RequestStack
     */
    protected $request;

    /**
     * AppExtension constructor.
     *
     * @param RequestStack $request
     */
    public function __construct(RequestStack $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('movement', [$this, 'movementFilter'])
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('routeMatch', [$this, 'routeMatch'])
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

    /**
     * @param string $route
     * @param string $class
     * @param array
     */
    public function routeMatch(string $route, string $class = 'active', array $query = [])
    {
        $request = $this->request->getCurrentRequest();
        $match = false;

        if ($request->get('_route') == $route) {
            if ($query) {
                $matches = 0;

                foreach ($query as $key => $value) {
                    if (
                        $request->query->has($key)
                        && $request->query->get($key) == $value
                    ) {
                        $matches++;
                    }
                }

                if ($matches == count($query)) {
                    $match = true;
                }
            } else {
                $match = true;
            }
        }

        if ($match) {
            echo $class;
        }
    }
}