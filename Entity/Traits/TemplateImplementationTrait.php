<?php

namespace DigipolisGent\Domainator9k\CoreBundle\Entity\Traits;

use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Trait IdentifiableTrait
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity\Traits
 */
trait TemplateImplementationTrait
{
    /**
     * {@inheritdoc}
     */
    public static function getTemplateReplacements(int $maxDepth = 3, array $skip = []): array
    {
        $reflection = ReflectionClass::createFromName(static::class);
        $skip[] = static::class;
        $methods = static::getRelevantMethods($reflection);
        $replacements = [];
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
     *
     * This function returns methods that are:
     *   - Not abstract or static
     *   - Start with -but are not equal to- 'get'.
     *   - Have a return type
     *   - Whose return type is scalar or implements TemplateInterface and is
     *     different from the current class (prevent loops).
     *   - Whose parameters are scalar (or non existant).
     *
     * @param \ReflectionClass $class
     *   The class to get the relevant methods of.
     *
     * @return \ReflectionMethod[]
     *   The relevant methods to build the templates.
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
            if (
                !$returnType->isBuiltin()
                && (
                    !class_exists($returnType)
                  || !is_a((string)$returnType, TemplateInterface::class, true)
                )
            ) {
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

    /**
     * Get all template replacements for a method.
     *
     * If this method's return type is scalar, it'll have one template. If the
     * return type implements TemplateInterface, it is chained (as a prefix) to
     * the templates of that return type.
     *
     * @param ReflectionMethod $method
     *   The method to get the templates for.
     * @param int $maxDepth
     *   The maximum depth to chain.
     * @param array $skip
     *   An array of classes to skip chaining for.
     *
     * @return array
     *   The templates generated for this method.
     */
    protected static function getTemplateReplacementsForMethod(ReflectionMethod $method, int $maxDepth, array $skip)
    {
        $returnType = $method->getReturnType();
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        $replacementParameters =  implode(',', $parameters);
        $replacementCallback = $method->getName() . '(' . $replacementParameters . ')';

        // Scalar return type, do not chain.
        if ($returnType->isBuiltin()) {
            // Strip off 'get' from the keyword and lowercase the first letter.
            $template = lcfirst(substr($method->getName(), 3)) . '(' . $replacementParameters . ')';
            return [$template => $replacementCallback];
        }

        // We've reached max depth or we should skip chaining for the return
        // type.
        if ($maxDepth <= 0 || in_array((string)$returnType, $skip)) {
            return [];
        }

        // Since method return type are usually more generic (interface,
        // abstract class) we also check if the return type is a parent class of
        // any of the classes to skip.
        foreach ($skip as $skipClass) {
            if (is_a($skipClass, (string) $returnType, true)) {
                return [];
            }
        }
        return static::getSubReplacementsForMethod($method, $maxDepth, $skip);
    }

    /**
     * Get all subtemplate replacements for a method.
     *
     * The methods return type should implement TemplateInterface. This return
     * type is chained (as a prefix) to the templates of that return type.
     *
     * @param ReflectionMethod $method
     *   The method to get the templates for.
     * @param int $maxDepth
     *   The maximum depth to chain.
     * @param array $skip
     *   An array of classes to skip chaining for.
     *
     * @return array
     *   The templates generated for this method.
     */
    protected static function getSubReplacementsForMethod(ReflectionMethod $method, int $maxDepth, array $skip)
    {
        $returnType = $method->getReturnType();
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = $parameter->getName();
        }
        $replacementParameters =  implode(',', $parameters);
        $replacementCallback = $method->getName() . '(' . $replacementParameters . ')';
        // Build the templates
        $replacements = [];
        $maxDepth--;
        $subs = call_user_func(array((string)$returnType, 'getTemplateReplacements'), $maxDepth, $skip);
        foreach ($subs as $subTemplate => $replacementSubCallback) {
            // Since we're chaining, we prepend the classname, with 'Abstract' or
            // 'Interface' stripped off, to the method for uniqueness.
            $template = lcfirst(
                str_replace(
                    ['Abstract', 'Interface'],
                    ['', ''],
                    ReflectionClass::createFromName((string)$returnType)->getShortName()
                )
            )
            // And we append the parameters of chained methods to the template.
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
