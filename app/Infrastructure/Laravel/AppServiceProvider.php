<?php

/**
 * Copyright (c) 2019 BitPay
 **/

namespace App\Infrastructure\Laravel;

use App\Features\Invoice\CreateInvoice\Validator\CreateInvoiceValidator;
use App\Features\Invoice\CreateInvoice\Validator\DonationParamsValidator;
use App\Features\Invoice\CreateInvoice\Validator\PosParamsValidator;
use App\Features\Shared\Configuration\BitPayConfigurationFactoryInterface;
use App\Features\Shared\Configuration\BitPayConfigurationInterface;
use App\Features\Shared\Configuration\BitPayYamlConfigurationFactory;
use App\Features\Shared\Configuration\Mode;
use App\Features\Invoice\UpdateInvoice\BaseUpdateInvoiceValidator;
use App\Features\Invoice\UpdateInvoice\SendUpdateInvoiceEventStream;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceIpnValidator;
use App\Features\Invoice\UpdateInvoice\UpdateInvoiceValidator;
use App\Features\Shared\Logger;
use App\Features\Shared\SseConfiguration;
use App\Features\Shared\UrlProvider;
use App\Features\Shared\UuidFactory;
use App\Infrastructure\Mercure\SendMercureUpdateInvoiceEventStream;
use App\Infrastructure\Mercure\SseMercureConfiguration;
use App\Infrastructure\RamseyUuidFactory;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mercure\Hub;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Jwt\StaticTokenProvider;
use Symfony\Component\PropertyInfo\Extractor\ConstructorExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
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
            UrlProvider::class,
            LaravelUrlProvider::class
        );

        $this->app->bind(
            UuidFactory::class,
            RamseyUuidFactory::class
        );

        $this->app->bind(
            SendUpdateInvoiceEventStream::class,
            SendMercureUpdateInvoiceEventStream::class
        );

        $this->app->bind(
            Logger::class,
            LaravelLogger::class
        );

        $this->app->bind(
            HubInterface::class,
            function () {
                return new Hub(
                    env('MERCURE_PUBLISHER_PUBLISHER_URL'),
                    new StaticTokenProvider(env('MERCURE_PUBLISHER_JWT_KEY')),
                    null,
                    env('MERCURE_PUBLISHER_SUBSCRIBER_URL')
                );
            }
        );

        $this->app->bind(
            SseConfiguration::class,
            SseMercureConfiguration::class
        );

        $this->app->bind(
            UpdateInvoiceIpnValidator::class,
            function () {
                return new UpdateInvoiceIpnValidator(
                    $this->app->make(BaseUpdateInvoiceValidator::class),
                    $this->app->make(Logger::class)
                );
            }
        );

        $this->app->bind(
            UpdateInvoiceValidator::class,
            UpdateInvoiceIpnValidator::class
        );

        $this->app->bind(
            SerializerInterface::class,
            function () {
                $phpDocExtractor = new PhpDocExtractor();
                $typeExtractor   = new PropertyInfoExtractor(
                    typeExtractors: [ new ConstructorExtractor([$phpDocExtractor]), $phpDocExtractor,]
                );

                $normalizer = [
                    new BackedEnumNormalizer(),
                    new ObjectNormalizer(propertyTypeExtractor: $typeExtractor),
                    new ArrayDenormalizer(),
                ];
                return new Serializer($normalizer, [new YamlEncoder()]);
            }
        );

        $this->app->bind(
            DonationParamsValidator::class,
            function () {
                return new DonationParamsValidator($this->app->get(PosParamsValidator::class));
            }
        );

        $this->app->bind(
            CreateInvoiceValidator::class,
            function () {
                /** @var BitPayConfigurationInterface $configuration */
                $configuration = $this->app->get(BitPayConfigurationInterface::class);
                if ($configuration->getMode() === Mode::DONATION) {
                    return $this->app->get(DonationParamsValidator::class);
                }

                if ($configuration->getMode() === Mode::STANDARD) {
                    return $this->app->get(PosParamsValidator::class);
                }

                throw new \RuntimeException("Wrong MODE in configuration yaml file (application*.yaml");
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
