<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Container;

use App\Domains\Database\Services\DatabaseService;
use DI\Container;
use DI\ContainerBuilder;

class ContainerConfig
{
    public static function create(): Container
    {
        $builder = new ContainerBuilder();
        
        $builder->useAutowiring(true);
        
        $builder->addDefinitions([
            DatabaseService::class => function () {
                $service = new DatabaseService();
                $service->initializeTables();
                return $service;
            }
        ]);

        return $builder->build();
    }
}
