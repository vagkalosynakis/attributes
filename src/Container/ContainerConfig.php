<?php

declare(strict_types=1);

namespace App\Container;

use App\Controllers\HomeController;
use App\Controllers\PostController;
use DI\Container;
use DI\ContainerBuilder;

class ContainerConfig
{
    public static function create(): Container
    {
        $builder = new ContainerBuilder();
        
        $builder->addDefinitions([
            // Controllers
            HomeController::class => \DI\autowire(),
            PostController::class => \DI\autowire(),
            
            // You can add more service definitions here
            // For example, database connections, services, etc.
        ]);

        return $builder->build();
    }
}
