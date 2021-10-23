<?php declare(strict_types = 1);

namespace Dravencms\Gallery\DI;

use Dravencms\Gallery\Gallery;

use Nette\DI\CompilerExtension;
use Salamek\Cms\DI\CmsExtension;
/**
 * Class GalleryExtension
 * @package Dravencms\Gallery\DI
 */
class GalleryExtension extends CompilerExtension
{

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('gallery'))
            ->setFactory(Gallery::class);

        if (class_exists(CmsExtension::class)) {
            $this->loadCmsComponents();
        }

        $this->loadComponents();
        $this->loadModels();
        $this->loadConsole();
    }


    protected function loadCmsComponents()
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/cmsComponents.neon') as $i => $command) {
            $cli = $builder->addFactoryDefinition($this->prefix('cmsComponent.' . $i))
                ->addTag(CmsExtension::TAG_COMPONENT);
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/components.neon') as $i => $command) {
            $cli = $builder->addFactoryDefinition($this->prefix('components.' . $i));
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadModels(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/models.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('models.' . $i));
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadConsole(): void
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cli.' . $i))
                ->setAutowired(false);

            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }
}
