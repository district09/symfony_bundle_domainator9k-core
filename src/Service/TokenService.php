<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Service;

use Camel\CaseTransformer;
use Camel\Format\SnakeCase;
use Camel\Format\StudlyCaps;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Token;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class TokenService
 * @package DigipolisGent\Domainator9k\CoreBundle\Service
 */
class TokenService
{

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var CaseTransformer
     */
    protected $caseTransformer;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Token::class);
        $this->caseTransformer = new CaseTransformer(new SnakeCase(), new StudlyCaps());
    }

    public function getTemplateReplacements(): array
    {
        $tokens = $this->repository->findAll();
        $replacements = [];
        foreach ($tokens as $token) {
            $replacements[$token->getName() . '()'] = 'get' . $this->caseTransformer->transform($token->getName()) . '()';
        }

        return $replacements;
    }

    public function __call(string $name, array $arguments)
    {
        if (strpos($name, 'get') !== 0) {
            throw new \BadMethodCallException('Call to undefined method ' . static::class . '::' . $name);
        }

        $replacements = $this->getTemplateReplacements();
        $tokenName = array_search($name . '()', $replacements);
        if ($tokenName === false) {
            throw new \BadMethodCallException('Call to undefined method ' . static::class . '::' . $name);
        }
        $tokenName = substr($tokenName, 0, -2);

        $token = $this->repository->findOneBy(['name' => $tokenName]);
        if (!$token) {
            throw new \BadMethodCallException('Token ' . $tokenName . ' not found');
        }

        return $token->getValue();
    }
}
