<?php declare(strict_types=1);
namespace Dravencms\Gallery\Access;
use Salamek\Files\Access\{IFileAccessProvider, FileAccess, BodyFiles};
use Dravencms\Model\Gallery\Repository\GalleryTranslationRepository;
use Nette\Security\User;

final class GalleryBodyProvider implements IFileAccessProvider
{
    public function __construct(private GalleryTranslationRepository $repository, private User $user, private BodyFiles $bodyFiles) {}
    public static function getId(): string { return 'gallery.body'; }
    public function resolve(string $reference): ?FileAccess
    {
        if (!preg_match('/^([1-9][0-9]*)-((?:[a-f0-9]{32}|[a-f0-9]{64}))$/D', $reference, $parts)) { return null; }
        $id = filter_var($parts[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false || !($entity = $this->repository->getOneById($id))) { return null; }
        $public = $entity->getGallery()->isActive();
        if (!$public && !$this->user->isAllowed('gallery', 'edit')) { return null; }
        $file = $this->bodyFiles->resolve($entity->getDescription(), $parts[2]);
        return $file ? new FileAccess($file, $public) : null;
    }
}
