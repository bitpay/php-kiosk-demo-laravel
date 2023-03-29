<?php

declare(strict_types=1);

namespace App\Features\Shared\Configuration;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;

class BitPayYamlConfigurationFactory implements BitPayConfigurationFactoryInterface
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function create(): BitPayConfigurationInterface
    {
        $directory = __DIR__ . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR;
        ;
        $data = Yaml::parse(file_get_contents(
            $directory  . 'application.yaml'
        ));

        return  $this->serializer->denormalize(
            $data['bitpay'],
            BitPayConfiguration::class,
            'yaml'
        );
    }
}
