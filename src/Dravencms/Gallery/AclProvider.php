<?php declare(strict_types=1);

namespace Dravencms\Gallery;

use Dravencms\User\Acl\{AclPermission, AclProviderInterface};

final class AclProvider implements AclProviderInterface
{
    public function getPermissions(): iterable
    {
        yield new AclPermission('gallery', 'edit', 'gallery.acl.permissions.edit.label', resourceLabel: 'gallery.acl.resource');
        yield new AclPermission('gallery', 'delete', 'gallery.acl.permissions.delete.label', resourceLabel: 'gallery.acl.resource');
    }
}
