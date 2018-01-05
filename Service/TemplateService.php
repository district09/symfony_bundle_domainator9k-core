<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Service;


use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\TokenTemplateInterface;
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
    public function replaceKeys($entity, string $templateMethod, array $entities = array()): string
    {
        // Check if the entity implements the TemplateInterface
        if (!$entity instanceof TemplateInterface) {
            throw new TemplateException('This object doesn\'t implement the TemplateInterface');
        }

        // Check if the template property can be used for template token replacement
        $templateMethods = $entity::getTemplateMethods();
        if (!in_array($templateMethod, $templateMethods)) {
            throw new TemplateException('This object doesn\'t allow the property to be used for token replacement');
        }

        // Check if the all template entities are in place
        $templateEntities = $entity::getTemplateEntities();

        foreach ($templateEntities as $templateEntityKey => $templateEntityClass) {
            if (!array_key_exists($templateEntityKey, $entities)) {
                throw new TemplateException('Template key not found');
            }
        }

        // Get the text where the tokens will be replaced
        $text = call_user_func_array(array($entity, $templateMethod), []);;
        // Get the tokens that have to be replaced
        $templateReplacements = $entity::getTemplateReplacements();

        // Loop over the tokens
        foreach ($templateReplacements as $templateReplacementKey => $templateReplacementValue) {

            // Define the replacement arguments
            $replacementArguments = [];

            preg_match('#\((.*?)\)#', $templateReplacementKey, $match);
            if (isset($match[1]) && $match[1] != '') {
                $replacementArguments = explode(',', $match[1]);
            }

            // Complete the pattern and escape all existing special characters
            $pattern = '[[ ' . $templateReplacementKey . ' ]]';
            $pattern = str_replace(['(', ')', '[', ']'], ['\(', '\)', '\[', '\]'], $pattern);

            // Get all the arguments out of the pattern so we can match them with the real arguments
            foreach ($replacementArguments as $replacementArgument) {
                $pattern = str_replace($replacementArgument, '(.*)', $pattern);
            }

            // Check if the pattern exists in our text
            $hasMatch = preg_match('/' . $pattern . '/', $text, $matches);

            // If we have a match for the pattern we substitute it
            if ($hasMatch) {

                // Define the entity out of the key
                $entityKey = strtok($templateReplacementKey, ':');

                // The value can be called recursive
                $passingValue = $entities[$entityKey];

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

                    // We decide if the function should be handled recussivly or not by checking if it is an object
                    // and if it implements the TemplateInterface
                    $childTemplateMethods = [];

                    if (is_object($passingValue) && $passingValue instanceof TemplateInterface) {
                        $childTemplateMethods = $passingValue::getTemplateMethods();
                    }

                    if (in_array($methodName, $childTemplateMethods)) {
                        $passingValue = $this->replaceKeys($passingValue,$methodName,$entities);
                    }else{
                        $passingValue = call_user_func_array(array($passingValue, $methodName), $functionArguments);
                    }
                }

                // Replace the pattern with the found value
                $text = preg_replace('/' . $pattern . '/', $passingValue, $text);
            }
        }

        return $text;
    }
}