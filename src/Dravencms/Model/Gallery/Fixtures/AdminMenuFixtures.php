<?php
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Fixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Dravencms\Model\Admin\Entities\Menu;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class AdminMenuFixtures extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $menu = $manager->getRepository(Menu::class);

        $adminMenu = new Menu('Gallery', ':Admin:Gallery:Gallery', 'fa-picture-o', $this->getReference('user-acl-operation-gallery-edit'));

        if ($parent = $menu->findOneBy(['name' => 'Site items']))
        {
            $adminMenu->setParent($parent);
        }

        $manager->persist($adminMenu);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getDependencies()
    {
        return ['Dravencms\Model\Gallery\Fixtures\AclOperationFixtures', 'Dravencms\Model\Structure\Fixtures\AdminMenuFixtures'];
    }
}