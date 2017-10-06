<?php
/**
 * AppBundle
 *
 * @namespace
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Doctrine\ORM\{
    EntityManager, EntityRepository
};
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractController
 *
 * Abstract class for all controllers to provide
 * more convenient ways for example to access the entitymanager,
 * or a currently requested entity by its id.
 *
 * @package AppBundle
 */
abstract class AbstractController extends Controller
{
    /**
     * Request instance used to receive
     * parameters.
     *
     * @var Request
     */
    protected $request;

    /**
     * A entity containing data
     * of the currently logged-in user.
     *
     * @var User
     */
    protected $user;

    /**
     * EntityManager used for all database interactions.
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * An array of EntityRepository objects
     * to store already used EntityRepositories.
     *
     * @var EntityRepository[]
     */
    protected $repositories;

    /**
     * An object mapped by doctrine
     * specified and retrieved by its primary key,
     * delivered per Request.
     *
     * @var object
     */
    protected $entity;

    /**
     * Lazy-load the EntityManager.
     *
     * @return EntityManager The currently active EntityManager
     */
    protected function entityManager(): EntityManager
    {
        if (!$this->em) {
            $this->em = $this->getDoctrine()->getManager();
        }

        return $this->em;
    }

    /**
     * Lazy-load a requested EntityRepository
     * and store it into the $repositories array
     * for later use.
     *
     * @param string $repositoryClass The requested entity name
     * @return EntityRepository The requested EntityRepository
     */
    protected function repository(string $repositoryClass): EntityRepository
    {
        if (!array_key_exists($repositoryClass, $this->repositories)) {
            $this->repositories[$repositoryClass] = $this->entityManager()->getRepository($repositoryClass);
        }

        return $this->repositories[$repositoryClass];
    }

    /**
     * When a request sends a parameter with a naming-convention
     * like {entityName}Id and an entity with a primary-key
     * called exactly like it, lying under a namespace
     * of {CurrentControllerBundle}\Entity and the parameter
     * contains an actual record-id, the record will be loaded
     * from its correct repository and the entity will be returned.
     *
     * @param int $id The id of of entity, if set in parameter-list of action
     * @return null|object The requested entity
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