<?php

/**
 * Copyright (c) 2019 BitPay
 **/

declare(strict_types=1);

namespace App\Features\Shared\Configuration;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;

class BitPayYamlConfigurationFactory implements BitPayConfigurationFactoryInterface
{
    private const AVAILABLE_CONFIGURATION_FILES = [
        'application.yaml',
        'application-example.yaml'
    ];

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

        $configurationFiles = self::AVAILABLE_CONFIGURATION_FILES;
        if (config('application-file')) {
            $configurationFiles = [config('application-file')];
        }

        $data = null;
        foreach ($configurationFiles as $configurationFile) {
            try {
                $data = Yaml::parse(file_get_contents($directory . $configurationFile));
                break;
            } catch (\Exception $e) {
            }
        }

        if (!$data) {
            throw new \RuntimeException(sprintf(
                'Invalid configuration. Please create %s file',
                implode(' or ', $configurationFiles)
            ));
        }

        return $this->serializer->denormalize(
            $data['bitpay'],
            BitPayConfiguration::class,
            'yaml'
        );
    }
}
