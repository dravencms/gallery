<?php declare(strict_types=1);

namespace Dravencms\Gallery;

use Dravencms\Admin\Menu\{AdminMenuItem, AdminMenuProviderInterface};

final class AdminMenuProvider implements AdminMenuProviderInterface
{
    public function getItems(): iterable
    {
        yield new AdminMenuItem('gallery.overview', 'gallery.menu.overview', destination: ':Admin:Gallery:Gallery:default', parent: 'content', icon: 'fa-picture-o', order: 50, resource: 'gallery', privilege: 'edit');
    }
}
