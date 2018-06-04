<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity\Traits;

use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Trait IdentifiableTrait
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity\Traits
 */
trait TemplateImplementationTrait {
    /**
     * @return array
     */
    public static function getTemplateReplacements(int $maxDepth = 3, array $skip = []): array
    {
        $reflection = ReflectionClass::createFromName(static::class);
        $skip[] = static::class;
        $methods = static::getRelevantMethods($reflection);
        $replacements = [];
        $maxDepth--;
        foreach ($methods as $method) {
            $replacements += static::getTemplateReplacementsForMethod($method, $maxDepth, $skip);
        }
        if ($reflection->hasMethod('additionalTemplateReplacements')) {
            $replacements += static::additionalTemplateReplacements();
        }
        return $replacements;
    }

    /**
     * Get relevant methods for template replacements.
     * @param \ReflectionClass $class
     * @return \ReflectionMethod[]
     */
    protected static function getRelevantMethods(ReflectionClass $class)
    {
        // Get all public methods.
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        $relevantMethods = [];
        foreach ($methods as $method) {
            // That are not abstract or static.
            if ($method->isAbstract() || $method->isStatic()) {
                continue;
            }
            // That start with -but are not equal to- 'get'.
            $name = $method->getName();
            if ($name === 'get' || substr($name, 0, 3) !== 'get') {
                continue;
            }
            // That have a return type.
            if (!$method->hasReturnType()) {
                continue;
            }
            $returnType = $method->getReturnType();
            // Whose return type is scalar or implements TemplateInterface and
            // is different from the current class (prevent loops).
            if (!$returnType->isBuiltin() && (!class_exists($returnType) || !is_a((string)$returnType, TemplateInterface::class, true))) {
                continue;
            }
            // Whose parameters are scalar (or non existant).
            foreach ($method->getParameters() as $parameter) {
              $parameterType = $parameter->getType();
              if (!is_null($parameterType) && !$parameterType->isBuiltin()) {
                  continue 2;
              }
            }
            $relevantMethods[$method->getName()] = $method;
        }
        return $relevantMethods;
    }

    protected static function getTemplateReplacementsForMethod(ReflectionMethod $method, int $maxDepth, array $skip)
    {
        $returnType = $method->getReturnType();
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
          $parameters[] = $parameter->getName();
        }
        $replacementParameters =  implode(',', $parameters);
        $replacementCallback = $method->getName() . '(' . $replacementParameters . ')';
        if ($returnType->isBuiltin()) {
            $template = lcfirst(substr($method->getName(), 3)) . '(' . $replacementParameters . ')';
            return [$template => $replacementCallback];
        }
        if ($maxDepth < 0 || in_array((string)$returnType, $skip)) {
            return [];
        }
        $replacements = [];
        $subs = call_user_func(array((string)$returnType, 'getTemplateReplacements'), $maxDepth, $skip);
        foreach ($subs as $subTemplate => $replacementSubCallback) {
            $template = lcfirst(
                str_replace('Abstract', '', ReflectionClass::createFromName((string)$returnType)->getShortName())
            )
            . ucfirst(
                  str_replace(
                    ['(,', ',)'],
                    ['(', ')'],
                    preg_replace(
                        '/\((.*)\)/',
                        '(' . $replacementParameters . ',$1)',
                        $subTemplate
                    )
                  )
            );
            $replacements[$template] = $replacementCallback . '.' . $replacementSubCallback;
        }
        return $replacements;
    }
}
