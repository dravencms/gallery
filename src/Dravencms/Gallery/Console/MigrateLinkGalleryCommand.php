<?php

declare(strict_types=1);

namespace Dravencms\Gallery\Console;

use Dravencms\Gallery\Gallery;
use Dravencms\Model\File\Entities\StructureFileLink;
use Dravencms\Model\Gallery\Repository\GalleryRepository;
use Dravencms\Model\Gallery\Repository\PictureRepository;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrateLinkGalleryCommand
 * @package Dravencms\Gallery\Console
 */
class MigrateLinkGalleryCommand extends Command
{
    /** @var EntityManager */
    private $entityManager;

    /** @var PictureRepository */
    private $pictureRepository;

    /**
     * CleanGalleryCommand constructor.
     * @param EntityManager $entityManager
     * @param GalleryRepository $galleryRepository
     */
    public function __construct(
        EntityManager $entityManager,
        PictureRepository $pictureRepository
    )
    {
        parent::__construct(null);

        $this->entityManager = $entityManager;
        $this->pictureRepository = $pictureRepository;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this->setName('gallery:gallery:migrate-link')
            ->setDescription('Migrate from direct fileStructure usage to fileStructureLink');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $migrated = 0;
            foreach ($this->pictureRepository->getAll() AS $picture) {
                if ($picture->getStructureFile() && !$picture->getStructureFileLink()) {
                    $structureFileLink = new StructureFileLink(Gallery::PLUGIN_NAME, $picture->getStructureFile(), true, true);
                    $picture->setStructureFileLink($structureFileLink);
                    $this->entityManager->persist($structureFileLink);
                    $this->entityManager->persist($picture);
                    $migrated++;
                }
            }
            
            $this->entityManager->flush();

            $cleared = 0;
            foreach ($this->pictureRepository->getAll() AS $picture) {
                if ($picture->getStructureFile() && $picture->getStructureFileLink()) {
                    $picture->setStructureFile(null);
                    $this->entityManager->persist($picture);
                    $cleared++;
                }
            }

            $this->entityManager->flush();
            
            $output->writeLn(sprintf('%s/%s pictures has been migrated!', $migrated, $cleared));
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}
