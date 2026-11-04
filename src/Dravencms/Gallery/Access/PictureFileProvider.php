<?php declare(strict_types=1);
namespace Dravencms\Gallery\Access;
use Salamek\Files\Access\{IFileAccessProvider, FileAccess};
use Dravencms\Model\Gallery\Repository\PictureRepository;
use Dravencms\File\Access\AdminFileAccess;

final class PictureFileProvider implements IFileAccessProvider
{
    public function __construct(private PictureRepository $repository, private AdminFileAccess $user) {}
    public static function getId(): string { return 'gallery.picture'; }
    public function resolve(string $reference): ?FileAccess
    {
        $id = filter_var($reference, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || !($entity = $this->repository->getOneById($id))) { return null; }
        $public = $entity->isActive() && $entity->getGallery()->isActive();
        if (!$public && !$this->user->isAllowed('gallery', 'edit')) { return null; }
        $attachment = $entity->getStructureFile();
        return $attachment ? new FileAccess($attachment->getFile(), $public, $attachment->getBasename()) : null;
    }
}
