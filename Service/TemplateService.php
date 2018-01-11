<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Service;


use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Exception\TemplateException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TemplateService
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TemplateService
{

    private $entityManager;

    /**
     * TemplateService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $text
     * @param array $entities
     * @return string
     */
    public function replaceKeys($text, array $entities = array()): string
    {
        $hasMatches = false;

        // Loop over all entities
        foreach ($entities as $entityPrefix => $entity) {
            if (!$entity instanceof TemplateInterface) {
                throw new TemplateException('This object doesn\'t implement the TemplateInterface');
            }

            foreach ($entity::getTemplateReplacements() as $templateReplacementKey => $templateReplacementValue) {

                // Define the replacement arguments
                $replacementArguments = [];

                preg_match('#\((.*?)\)#', $templateReplacementKey, $match);
                if (isset($match[1]) && $match[1] != '') {
                    $replacementArguments = explode(',', $match[1]);
                }

                // Complete the pattern and escape all existing special characters
                $pattern = '[[ ' . $entityPrefix . ':' . $templateReplacementKey . ' ]]';
                $pattern = str_replace(['(', ')', '[', ']'], ['\(', '\)', '\[', '\]'], $pattern);

                // Get all the arguments out of the pattern so we can match them with the real arguments
                foreach ($replacementArguments as $replacementArgument) {
                    $pattern = str_replace($replacementArgument, '([^)]*)', $pattern);
                }

                // Check if the pattern exists in our text
                $hasMatch = preg_match('/' . $pattern . '/', $text, $matches);

                // If we have a match for the pattern we substitute it
                if ($hasMatch) {
                    $hasMatches = true;

                    // The value can be called recursive
                    $passingValue = $entity;

                    // Get a key value pair of all arguments
                    foreach ($replacementArguments as $key => $value) {
                        $replacementArguments[$value] = $matches[$key + 1];
                    }

                    // Get all functions that should be executed
                    $functions = explode('.', $templateReplacementValue);

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
                    $text = preg_replace('/' . $pattern . '/', $passingValue, $text);
                }
            }
        }

        // Recursivly go trough this function until no matches are found
        if ($hasMatches) {
            $text = $this->replaceKeys($text, $entities);
        }

        return $text;
    }
}