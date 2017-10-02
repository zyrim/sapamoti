<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Doctrine\ORM\{EntityManager, EntityRepository};
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractController
 *
 * @package AppBundle\Controller
 */
abstract class AbstractController extends Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var object
     */
    protected $entity;

    /**
     * @return EntityManager
     */
    protected function entityManager(): EntityManager
    {
        if (!$this->em) {
            $this->em = $this->getDoctrine()->getManager();
        }

        return $this->em;
    }

    /**
     * @param string $repositoryClass
     * @return EntityRepository
     */
    protected function repository(string $repositoryClass): EntityRepository
    {
        return $this->entityManager()->getRepository($repositoryClass);
    }

    /**
     * @param int $id
     * @return null|object
     */
    protected function getEntity(int $id = 0)
    {
        if (!$this->entity) {
            if (!$this->request) {
                /** @var Request $request */
                $this->request = $this->get('request_stack')->getCurrentRequest();
            }

            $class = explode('\\', get_class($this))[0] . '\\Entity\\';

            foreach ($this->request->attributes as $key => $value) {
                if (strpos($key, 'Id') !== false) {
                    $class .= ucfirst(substr($key, 0, strlen($key) - 2));

                    if (!$id) {
                        $id = $value;
                    }
                    break;
                }
            }

            if (class_exists($class)) {
                $entity = $this->repository($class)->find($id);

                if ($entity instanceof $class) {
                    $this->entity = $entity;
                }
            }
        }

        return $this->entity;
    }
}