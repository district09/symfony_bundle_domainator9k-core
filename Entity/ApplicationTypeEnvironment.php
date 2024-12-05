<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ApplicationTypeEnvironment
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity
 */
#[ORM\Table(name: 'application_type_environment')]
#[ORM\Entity(repositoryClass: \DigipolisGent\Domainator9k\CoreBundle\Repository\ApplicationTypeEnvironmentRepository::class)]
class ApplicationTypeEnvironment
{

    use SettingImplementationTrait;
    use IdentifiableTrait;

    /**
     * @var ApplicationType
     */
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \ApplicationType::class, inversedBy: 'applicationTypeEnvironments')]
    protected $applicationType;

    /**
     * @var  Environment
     */
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Environment::class, inversedBy: 'applicationTypeEnvironments')]
    protected $environment;

    public static function getSettingImplementationName()
    {
        return 'application_type_environment';
    }

    /**
     * @return ApplicationType
     */
    public function getApplicationType()
    {
        return $this->applicationType;
    }

    /**
     * @param ApplicationType $applicationType
     */
    public function setApplicationType(ApplicationType $applicationType)
    {
        $this->applicationType = $applicationType;
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param Environment $environment
     */
    public function setEnvironment(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getEnvironmentName()
    {
        return $this->getEnvironment()->getName();
    }
}
