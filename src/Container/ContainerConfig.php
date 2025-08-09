<?php

declare(strict_types=1);

namespace App\Container;

use DI\Container;
use DI\ContainerBuilder;

class ContainerConfig
{
    public static function create(): Container
    {
        $builder = new ContainerBuilder();
        
        // Enable reflection-based autowiring
        $builder->useAutowiring(true);
        
        // Optional: Add specific definitions only when needed
        // $builder->addDefinitions([
        //     // Add custom definitions here if needed
        // ]);

        return $builder->build();
    }
}
