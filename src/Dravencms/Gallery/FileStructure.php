<?php declare(strict_types=1);

namespace Dravencms\Gallery;

use Doctrine\ORM\EntityManagerInterface;
use Dravencms\Model\File\Entities\Structure;

final class FileStructure
{
    public function __construct(private EntityManagerInterface $manager) {}

    public function getOrCreate(): Structure
    {
        $existing = $this->manager->getRepository(Structure::class)->findOneBy(['name' => Gallery::PLUGIN_NAME, 'parent' => null]);
        if ($existing !== null) { return $existing; }
        return $this->manager->wrapInTransaction(function (): Structure {
            $structure = new Structure(Gallery::PLUGIN_NAME);
            $this->manager->persist($structure);
            return $structure;
        });
    }
}
