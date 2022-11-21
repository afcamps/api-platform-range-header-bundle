<?php


$apiPlatformBundleClass =  class_exists("ApiPlatform\\Core\\Bridge\\Symfony\\Bundle\\ApiPlatformBundle") ?
    ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle::class :
    ApiPlatform\Symfony\Bundle\ApiPlatformBundle::class;

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],
    $apiPlatformBundleClass => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
    Campings\Bundle\ApiPlatformRangeHeaderBundle\ApiPlatformRangeHeaderBundle::class => ['all' => true],
    FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['test' => true],
];
