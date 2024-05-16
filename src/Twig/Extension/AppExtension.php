<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    // public function getFilters(): array
    // {
    //     return [
    //         // If your filter generates SAFE HTML, you should add a third
    //         // parameter: ['is_safe' => ['html']]
    //         // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
    //         new TwigFilter('filter_name', [AppExtensionRuntime::class, 'doSomething']),
    //     ];
    // }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('function_name', [AppExtensionRuntime::class, 'doSomething']),
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('base64_encode', [$this, 'base64Encode']),
        ];
    }

    public function base64Encode($stream)
    {
        // Lire le contenu du flux de ressources
        $data = stream_get_contents($stream);

        // Encoder en base64
        $base64Data = base64_encode($data);

        return $base64Data;
    }

    // public function convertToBase64($stream)
    // {
    //     // Lire le contenu du flux de ressources
    //     $data = stream_get_contents($stream);

    //     // Encoder en base64
    //     $base64Data = base64_encode($data);

    //     return $base64Data;
    // }
}
