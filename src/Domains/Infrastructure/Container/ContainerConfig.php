<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Container;

use App\Domains\Database\Services\DatabaseService;
use App\Domains\User\Repositories\UserRepository;
use App\Domains\User\Services\UserService;
use App\Domains\Post\Repositories\PostRepository;
use App\Domains\Post\Services\PostService;
use DI\Container;
use DI\ContainerBuilder;

class ContainerConfig
{
    public static function create(): Container
    {
        $builder = new ContainerBuilder();
        
        $builder->useAutowiring(true);
        
        $builder->addDefinitions([
            // Database services
            DatabaseService::class => function () {
                $service = new DatabaseService();
                // Initialize tables on first connection
                $service->initializeTables();
                return $service;
            },
        ]);

        return $builder->build();
    }
}
