<?php declare(strict_types = 1);
/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

namespace Dravencms\Model\Gallery\Fixtures;

use Dravencms\Gallery\Gallery;
use Dravencms\Model\Admin\Entities\Menu;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Dravencms\Model\File\Entities\Structure;

class FileFixtures extends AbstractFixture
{


    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $structure = $manager->getRepository(Structure::class);

        if (!$found = $structure->findOneBy(['name' => 'Site items']))
        {
            $newStructure = new Structure(Gallery::PLUGIN_NAME);
            $manager->persist($newStructure);
            $manager->flush();
        }
    }

}