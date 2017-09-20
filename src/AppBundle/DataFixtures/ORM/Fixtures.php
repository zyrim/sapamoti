<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class Fixtures
 *
 * @package AppBundle\DataFixtues\ORM
 */
class Fixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail('max_' . $i . '@mustermann.mail')
                ->setPlainPassword('cool');

            $manager->persist($user);
        }

        $manager->flush();
    }
}