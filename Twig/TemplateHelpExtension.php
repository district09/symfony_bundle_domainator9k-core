<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Twig;

use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Service\TokenService;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TemplateHelpExtension extends AbstractExtension {

  protected $tokenService;

  public function __construct(TokenService $tokenService) {
      $this->tokenService = $tokenService;
  }

  public function getFunctions()
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

  public function templateHelp(Environment $environment, array $classes, $textarea)
  {

      $templates = [
          'token' => array_keys($this->tokenService->getTemplateReplacements()),
      ];
      foreach ($classes as $key => $class) {
          if (!is_a($class, TemplateInterface::class, true)) {
              new RuntimeError(sprintf('Class %s does not implement %s.', $class, TemplateInterface::class));
          }
          $templates[$key] = array_keys(call_user_func([$class, 'getTemplateReplacements']));
      }

      return $environment->render(
          '@DigipolisGentDomainator9kCore/Template/templatehelper.twig',
          [
              'templates' => $templates,
              'textarea' => $textarea,
          ]);
  }
}
