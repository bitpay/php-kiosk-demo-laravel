<?php

namespace App\Providers;

use App\Configuration\BitPayConfiguration;
use App\Configuration\BitPayConfigurationFactoryInterface;
use App\Configuration\BitPayConfigurationInterface;
use App\Configuration\BitPayYamlConfigurationFactory;
use BitPaySDK\Client;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\PropertyInfo\Extractor\ConstructorExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            BitPayConfigurationFactoryInterface::class,
            BitPayYamlConfigurationFactory::class
        );


        $this->app->bind(
            SerializerInterface::class,
            function() {
                $phpDocExtractor = new PhpDocExtractor();
                $typeExtractor   = new PropertyInfoExtractor(
                    typeExtractors: [ new ConstructorExtractor([$phpDocExtractor]), $phpDocExtractor,]
                );

                $normalizer = [
                    new ObjectNormalizer(propertyTypeExtractor: $typeExtractor),
                    new ArrayDenormalizer(),
                ];
                return new Serializer($normalizer, [new YamlEncoder()]);
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
