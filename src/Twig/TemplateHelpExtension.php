<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Twig;

use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\TokenService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TemplateHelpExtension extends AbstractExtension
{

    const CACHE_LIFETIME = 60 * 60 * 24 * 7;
    const CACHE_TAG = 'template_help';
    protected $tokenService;
    protected TagAwareCacheInterface $cache;

    public function __construct(TokenService $tokenService, TagAwareCacheInterface $cache)
    {
        $this->tokenService = $tokenService;
        $this->cache = $cache;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'template_help',
                [
                    $this,
                    'templateHelp',
                ],
                [
                    'needs_environment' => true,
                    'is_safe' => [
                        'html',
                    ],
                ]
            ),
        ];
    }

    public function templateHelp(Environment $environment, array $classes, string $textareaSelector)
    {
        $templates = [
            'token' => array_keys($this->tokenService->getTemplateReplacements()),
        ];
        foreach ($classes as $key => $class) {
            if (!is_a($class, TemplateInterface::class, true)) {
                new RuntimeError(sprintf('Class %s does not implement %s.', $class, TemplateInterface::class));
            }
            $templates[$key] = $this->cache->get('template_help:' . $class, function(ItemInterface $item) use ($class): array {
                $item->tag(static::CACHE_TAG);
                $item->expiresAfter(static::CACHE_LIFETIME);

                return array_keys(call_user_func([$class, 'getTemplateReplacements']));
            });
        }

        return $environment->render(
            '@DigipolisGentDomainator9kCore/Template/templatehelper.twig',
            [
                'templates' => $templates,
                'textarea' => $textareaSelector,
            ]
        );
    }

}
