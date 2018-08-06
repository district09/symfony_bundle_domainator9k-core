<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Exception\TemplateException;

/**
 * Class TemplateService
 *
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TemplateService
{

    /**
     * The service for custom tokens.
     *
     * @var TokenService
     */
    protected $tokenService;

    /**
     * The replacements.
     *
     * @var array
     */
    protected $replacements;

    /**
     * Class constructor.
     *
     * @param TokenService $tokenService
     *   The token service.
     */
    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Replace all keys.
     *
     * @param string $text
     *   The text to replace in.
     * @param array $entities
     *   The template entities keyed by prefix.
     *
     * @return string
     *   The processed text.
     */
    public function replaceKeys($text, array $entities = array()): string
    {
        // Register the replacements.
        $this->resetReplacements();
        foreach ($entities as $type => $entity) {
            $this->registerReplacements($type, $entity);
        }

        // Replace the tokens.
        do {
            $result = preg_replace_callback('#
                \[\[
                    [ ]*
                    ([a-zA-Z][a-zA-Z0-9_]*)
                    :
                    ([a-zA-Z][a-zA-Z0-9_]*)
                    \(
                        [ ]*
                        (
                            [^,\s]+
                            (?:[ ]*,[ ]*[^,\s]+)*
                        )?
                        [ ]*
                    \)
                    [ ]*
                \]\]
                #x', [$this, 'doReplace'], $text);

            if ($result === $text) {
                break;
            }

            $text = $result;
        } while (true);

        return $text;
    }

    /**
     * Replace a key match.
     *
     * @param array $matches
     *   The replacement matches.
     *
     * @return string
     *   The replacement text.
     */
    protected function doReplace(array $matches): string
    {
        // Use readable variables names.
        $matches[] = null;
        list ($original, $type, $key, $params) = $matches;

        if (isset($this->replacements[$type][$key])) {
            // Get the replacement.
            $replacement = $this->replacements[$type][$key];

            // Prepare the parameters.
            if (!$replacement['params'] || $params === '') {
                $params = [];
            } else {
                $params = explode(',', str_replace(' ', '', $params));
                $count1 = count($params);
                $count2 = count($replacement['params']);

                // Ensure both arrays have the same number of parameters.
                if ($count1 > $count2) {
                    $params = array_slice($params, 0, $count2);
                } elseif ($count2 > $count1) {
                    $params = array_merge($params, array_fill(0, ($count2 - $count1), null));
                }

                // Create an associative array.
                $params = array_combine($replacement['params'], $params);
            }

            $result = $replacement['object'];

            foreach ($replacement['callbacks'] as $callback => $callbackParams) {
                // Get the parameters.
                foreach ($callbackParams as $name => &$value) {
                    if (array_key_exists($name, $params)) {
                        $value = $params[$name];
                    }
                }

                // Execute the callback.
                $result = call_user_func_array([$result, $callback], $callbackParams);
            }

            return (string) $result;
        }

        return $original;
    }

    /**
     * Reset the replacements.
     */
    protected function resetReplacements()
    {
        if ($this->replacements === null) {
            $this->replacements = [];
            $this->registerReplacements('token', $this->tokenService);
        } else {
            $this->replacements = [
                'token' => $this->replacements['token'],
            ];
        }
    }

    /**
     * Register new replacements.
     *
     * @param string $type
     *   The replacement type.
     * @param TemplateInterface|TokenService $object
     *   The object to use.
     * @param array $replacements
     *   Array of replacements, leave null to get them from the object.
     */
    protected function registerReplacements(string $type, $object, array $replacements = null)
    {
        // Initialize the replacements.
        if ($this->replacements === null) {
            $this->resetReplacements();
        }

        // Get the default replacements.
        if ($replacements === null) {
            if ($object instanceof TemplateInterface) {
                $replacements = $object::getTemplateReplacements();
            } elseif ($object instanceof TokenService) {
                $replacements = $object->getTemplateReplacements();
            } else {
                throw new TemplateException("The object doesn't specify default replacements.");
            }
        }

        $this->replacements[$type] = [];

        foreach ($replacements as $replacementKey => $replacementValueCallback) {
            // Extract the key and parameters.
            if (!preg_match('#^
                ([a-zA-Z][a-zA-Z0-9_]*)
                \(
                    [ ]*
                    (
                        [a-zA-Z][a-zA-Z0-9_]*
                        (?:[ ]*,[ ]*[a-zA-Z][a-zA-Z0-9_]*)*
                    )?
                    [ ]*
                \)
                $#x', $replacementKey, $matches)) {
                continue;
            }

            $key = $matches[1];

            // Prepare the parameters.
            if (isset($matches[2])) {
                $keyParams = explode(',', str_replace(' ', '', $matches[2]));
            } else {
                $keyParams = [];
            }

            // Extract the callbacks.
            $callbacks = [];
            $replacementValueCallback = explode('.', $replacementValueCallback);
            foreach ($replacementValueCallback as $callback) {
                // Extract the method and parameters.
                if (!preg_match('#^
                    ([a-zA-Z][a-zA-Z0-9_]*)
                    \(
                        [ ]*
                        (
                            [a-zA-Z][a-zA-Z0-9_]*
                            (?:[ ]*,[ ]*[a-zA-Z][a-zA-Z0-9_]*)*
                        )?
                        [ ]*
                    \)
                    $#x', $callback, $matches)) {
                    continue 2;
                }

                // Prepare the parameters.
                if (isset($matches[2])) {
                    $params = explode(',', str_replace(' ', '', $matches[2]));

                    if (array_diff($params, $keyParams)) {
                        throw new TemplateException('The replacement value callback uses unknown parameters.');
                    }

                    $params = array_fill_keys($params, null);
                } else {
                    $params = [];
                }

                $callbacks[$matches[1]] = $params;
            }

            // Add the replacement.
            $this->replacements[$type][$key] = [
                'params' => $keyParams,
                'callbacks' => $callbacks,
                'object' => $object,
            ];
        }
    }

}
