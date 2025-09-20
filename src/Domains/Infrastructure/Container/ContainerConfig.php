<?php

declare(strict_types=1);

namespace App\Domains\Infrastructure\Container;

use DI\Container;
use DI\ContainerBuilder;

class ContainerConfig
{
    public static function create(): Container
    {
        $builder = new ContainerBuilder();
        
        $builder->useAutowiring(true);
        
        // $builder->addDefinitions([
        //     // Add custom definitions here if needed
        // ]);

        return $builder->build();
    }
}
