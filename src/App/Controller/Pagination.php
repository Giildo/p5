<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;

trait Pagination
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param array $vars
     * @param int $nbItem
     * @param null|string $optionLimit
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function pagination(array $vars, int $nbItem, ?string $optionLimit = 'blog.limit.post'): array
    {
        $pagination = [];

        $pagination['limit'] = $this->container->get($optionLimit);

        $pagination['id'] = (int)$vars['id'];

        $pagination['pageNb'] = (int)ceil($nbItem / $pagination['limit']);
        $pagination['start'] = ($pagination['limit'] * ($pagination['id'] - 1));

        $pagination['next'] = ($pagination['id'] + 1 <= $pagination['pageNb']) ? $pagination['id'] + 1 : null;
        $pagination['previous'] = ($pagination['id'] - 1 >= 1) ? $pagination['id'] - 1 : null;

        return $pagination;
    }
}
