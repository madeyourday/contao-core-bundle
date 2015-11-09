<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Image;

use Contao\BackendUser;
use Contao\CoreBundle\ContaoFrameworkInterface;
use Contao\CoreBundle\Event\ContaoCoreEvents;
use Contao\CoreBundle\Event\ImageSizesEvent;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Defines the image sizes service.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 * @author Kamil Kuzminski <https://github.com/qzminski>
 */
class ImageSizes
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param Connection               $connection      The connection
     * @param EventDispatcherInterface $eventDispatcher The event dispatcher
     * @param ContaoFrameworkInterface $framework       The Contao framework
     */
    public function __construct(
        Connection $connection,
        EventDispatcherInterface $eventDispatcher,
        ContaoFrameworkInterface $framework
    ) {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
        $this->framework = $framework;
    }

    /**
     * Returns the image sizes as options suitable for widgets.
     *
     * @return array The image sizes
     */
    public function getAllOptions()
    {
        $this->loadOptions();

        $event = new ImageSizesEvent($this->options);

        $this->eventDispatcher->dispatch(ContaoCoreEvents::IMAGE_SIZES_ALL, $event);

        return $event->getImageSizes();
    }

    /**
     * Returns the image sizes for the given user suitable for widgets.
     *
     * @param BackendUser $user The back end user
     *
     * @return array The image sizes
     */
    public function getOptionsForUser(BackendUser $user)
    {
        $this->loadOptions();

        $event = new ImageSizesEvent(
            $user->isAdmin ? $this->options : $this->filterOptions(deserialize($user->imageSizes, true)),
            $user
        );

        $this->eventDispatcher->dispatch(ContaoCoreEvents::IMAGE_SIZES_USER, $event);

        return $event->getImageSizes();
    }

    /**
     * Loads the options from the database.
     */
    private function loadOptions()
    {
        if (null !== $this->options) {
            return;
        }

        $this->options = $GLOBALS['TL_CROP'];

        // The framework is required to have the TL_CROP options available
        $this->framework->initialize();

        $rows = $this->connection->fetchAll(
            'SELECT id, name, width, height FROM tl_image_size ORDER BY pid, name'
        );

        foreach ($rows as $imageSize) {
            $this->options['image_sizes'][$imageSize['id']] = sprintf(
                '%s (%sx%s)',
                $imageSize['name'],
                $imageSize['width'],
                $imageSize['height']
            );
        }
    }

    /**
     * Filters the options by the given allowed sizes and returns the result.
     *
     * @param array $allowedSizes The allowed options
     *
     * @return array The filtered options
     */
    private function filterOptions(array $allowedSizes)
    {
        if (empty($allowedSizes)) {
            return [];
        }

        $filteredSizes = [];

        foreach ($this->options as $group => $sizes) {
            foreach ($sizes as $k => $v) {
                if ($group == 'image_sizes') {
                    if (in_array($k, $allowedSizes)) {
                        $filteredSizes[$group][$k] = $v;
                    }
                } elseif (in_array($v, $allowedSizes)) {
                    $filteredSizes[$group][] = $v;
                }
            }
        }

        return $filteredSizes;
    }
}
