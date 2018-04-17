<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use Camel\CaseTransformer;
use Camel\Format\SnakeCase;
use Camel\Format\StudlyCaps;
use DigipolisGent\Domainator9k\CoreBundle\Entity\TemplateInterface;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Token;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Class TokenService
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TokenService implements TemplateInterface
{

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var CaseTransformer
     */
    protected $caseTransformer;

    public function __construct(EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(Token::class);
        $this->caseTransformer = new CaseTransformer(new SnakeCase(), new StudlyCaps());
    }

    public static function getTemplateReplacements(): array
    {
        $tokens = $this->repository->findAll();
        $replacements = [];
        foreach ($tokens as $token) {
            $replacements[$token->getName()] = 'get' . $this->caseTransformer->transform($token->getName()) . '()';
        }

        return $replacements;
    }

    public function __call(string $name, array $arguments)
    {
        if (strpos($name, 'get') !== 0) {
            throw new \BadMethodCallException('Call to undefined method ' . static::class . '::' . $name);
        }

        $replacements = static::getTemplateReplacements();
        $tokenName = array_search($name . '()', $replacements);
        if ($tokenName === false) {
            throw new \BadMethodCallException('Call to undefined method ' . static::class . '::' . $name);
        }

        $token = $this->repository->findOneBy(['name' => $tokenName]);
        if (!$token) {
            throw new \BadMethodCallException('Token ' . $tokenName . ' not found');
        }

        return $token->getValue();
    }
}
