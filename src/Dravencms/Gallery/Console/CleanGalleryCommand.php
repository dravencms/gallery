<?php

declare(strict_types=1);

namespace Dravencms\Gallery\Console;

use Dravencms\Model\Gallery\Repository\GalleryRepository;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CleanGalleryCommand
 * @package Dravencms\Gallery\Console
 */
class CleanGalleryCommand extends Command
{
    /** @var EntityManager */
    private $entityManager;

    /** @var GalleryRepository */
    private $galleryRepository;

    /**
     * CleanGalleryCommand constructor.
     * @param EntityManager $entityManager
     * @param GalleryRepository $galleryRepository
     */
    public function __construct(
        EntityManager $entityManager,
        GalleryRepository $galleryRepository
    )
    {
        parent::__construct(null);

        $this->entityManager = $entityManager;
        $this->galleryRepository = $galleryRepository;
    }

    /**
     *
     */
    protected function configure(): void
    {
        $this->setName('gallery:gallery:clean')
            ->setDescription('Keep only defined number of last created galleries');

        $this->addArgument('keep', InputArgument::REQUIRED, 'How many last galleries to keep?');
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
            $keepGalleries = intval($input->getArgument('keep'));

            $galleriesToDelete = $this->galleryRepository->getOverOffset($keepGalleries);
            foreach($galleriesToDelete AS $gallery)
            {
                foreach($gallery->getPictures() AS $picture){
                    $structureFileLink = $picture->getStructureFileLink();
                    if ($structureFileLink) {
                        $structureFileLink->setIsUsed(false);
                        $structureFileLink->setIsAutoclean(true);
                        $this->entityManager->persist($structureFileLink);
                    }
                    $this->entityManager->remove($picture);
                }
                $this->entityManager->remove($gallery);
            }

            $this->entityManager->flush();
            $output->writeLn('Galleries has been cleaned!');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}