<?php declare(strict_types=1);
namespace Dravencms\Gallery;

use Dravencms\Admin\Dashboard\{DashboardDefinition, DashboardMetric, StatisticsComponent};
use Dravencms\Database\EntityManager;

final class DashboardComponent extends StatisticsComponent
{
    public function __construct(private EntityManager $entityManager) {}

    public function getDefinition(): DashboardDefinition
    {
        return new DashboardDefinition('gallery.overview', 'gallery.dashboard.title', 100,
            resource: 'gallery', privilege: 'edit', destination: ':Admin:Gallery:Gallery:default', icon: 'fa-picture-o');
    }

    protected function getMetrics(): iterable
    {
        $query = $this->entityManager->createQuery('SELECT COUNT(g.id) AS total, COALESCE(SUM(CASE WHEN g.isActive = true THEN 1 ELSE 0 END), 0) AS active FROM Dravencms\\Model\\Gallery\\Entities\\Gallery g');
        $stats = $query->getSingleResult();
        yield new DashboardMetric('gallery.dashboard.total', (int) $stats['total']);
        yield new DashboardMetric('gallery.dashboard.active', (int) $stats['active']);
    }
}
