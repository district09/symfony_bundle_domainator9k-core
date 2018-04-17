<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Exception\TemplateException;

/**
 * Class TemplateService
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TemplateService
{

    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * @param string $text
     * @param array $entities
     * @return string
     */
    public function replaceKeys($text, array $entities = array()): string
    {
        $hasMatches = false;

        // Loop over user created tokens.
        foreach ($this->tokenService->getTemplateReplacements() as $templateReplacementKey => $templateReplacementValueCallback) {
            $text = $this->doReplacement(
                $text,
                [
                    'prefix' => 'token',
                    'entity' => $this->tokenService,
                    'key' => $templateReplacementKey,
                    'callback' => $templateReplacementValueCallback,
                ],
                $hasMatches
            );
        }

        // Loop over all entities.
        foreach ($entities as $entityPrefix => $entity) {
            if (!$entity instanceof TemplateInterface) {
                throw new TemplateException('This object doesn\'t implement the TemplateInterface');
            }

            foreach ($entity::getTemplateReplacements() as $templateReplacementKey => $templateReplacementValueCallback) {
                $text = $this->doReplacement(
                    $text,
                    [
                        'prefix' => $entityPrefix,
                        'entity' => $entity,
                        'key' => $templateReplacementKey,
                        'callback' => $templateReplacementValueCallback,
                    ],
                    $hasMatches
                );
            }
        }

        // Recursivly go trough this function until no matches are found
        if ($hasMatches) {
            $text = $this->replaceKeys($text, $entities);
        }

        return $text;
    }

    protected function doReplacement(string $text, array $data, &$hasMatches)
    {
        $entityPrefix = $data['prefix'];
        $entity = $data['entity'];
        $templateReplacementKey = $data['key'];
        $templateReplacementValueCallback = $data['callback'];
        // Define the replacement arguments
        $replacementArguments = [];
        $match = [];
        preg_match('#\((.*?)\)#', $templateReplacementKey, $match);
        if (isset($match[1]) && $match[1] != '') {
            $replacementArguments = explode(',', $match[1]);
        }

        // Complete the pattern and escape all existing special characters
        $pattern = '[[ ' . $entityPrefix . ':' . $templateReplacementKey . ' ]]';
        $pattern = str_replace(['(', ')', '[', ']'], ['\(', '\)', '\[', '\]'], $pattern);
        $replacePattern = $pattern;

        // Get all the arguments out of the pattern so we can match them with the real arguments
        foreach ($replacementArguments as $replacementArgument) {
            $pattern = str_replace($replacementArgument, '([^)]*)', $pattern);
        }

        // Check if the pattern exists in our text
        $matches = [];
        $hasMatch = preg_match('/' . $pattern . '/', $text, $matches);

        // If we have a match for the pattern we substitute it
        if (!$hasMatch) {
            return $text;
        }
        $hasMatches = true;

        // The value can be called recursive
        $passingValue = $entity;

        // Get a key value pair of all arguments
        foreach ($replacementArguments as $key => $value) {
            $replacementArguments[$value] = $matches[$key + 1];
            $replacePattern = str_replace($replacementArguments[$key], $matches[$key + 1 ], $replacePattern);
        }

        // Get all functions that should be executed
        $functions = explode('.', $templateReplacementValueCallback);

        // Execute these functions on the defined entity with the discovered arguments
        foreach ($functions as $function) {
            preg_match('/^([a-zA-Z]*)(\((.*)\))?/', $function, $result);

            $functionArguments = [];
            $methodName = $result[1];
            // Get the arguments and replace them by the real values if they are present
            if (isset($result[3]) && $result[3] != '') {
                $functionArguments = explode(',', $result[3]);
                foreach ($functionArguments as $key => $value) {
                    $functionArguments[$key] = $replacementArguments[$value];
                }
            }

            $passingValue = call_user_func_array(array($passingValue, $methodName), $functionArguments);
        }

        // Replace the pattern with the found value
        return preg_replace('/' . $replacePattern . '/', $passingValue, $text);
    }
}
