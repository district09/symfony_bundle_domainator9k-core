<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractApplication
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr",type="string")
 * @UniqueEntity(fields={"name"})
 */
abstract class AbstractApplication implements TemplateInterface
{

    use SettingImplementationTrait;
    use IdentifiableTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="255")
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="git_repo", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $gitRepo;

    /**
     * @var bool
     *
     * @ORM\Column(name="has_database", type="boolean", options={"default"="1"})
     */
    protected $hasDatabase = true;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ApplicationEnvironment", mappedBy="application", cascade={"all"},fetch="EAGER")
     * @Assert\Valid()
     * @Assert\NotNull()
     */
    protected $applicationEnvironments;

    /**
     * @return string
     */
    abstract function getType();

    public function __construct()
    {
        $this->builds = new ArrayCollection();
        $this->applicationEnvironments = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getNameCanonical()
    {
        $name = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $this->getName()));
        return substr($name, 0, 12);
    }

    /**
     * @return string
     */
    public function getGitRepo()
    {
        return $this->gitRepo;
    }

    /**
     * @param string $gitRepo
     */
    public function setGitRepo(string $gitRepo)
    {
        $this->gitRepo = $gitRepo;
    }

    /**
     * @return bool
     */
    public function isHasDatabase(): bool
    {
        return $this->hasDatabase;
    }

    /**
     * @param bool $hasDatabase
     */
    public function setHasDatabase(bool $hasDatabase)
    {
        $this->hasDatabase = $hasDatabase;
    }

    /**
     * @param ArrayCollection $builds
     */
    public function setBuilds(ArrayCollection $builds)
    {
        $this->builds = $builds;
    }

    /**
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function addApplicationEnvironment(ApplicationEnvironment $applicationEnvironment)
    {
        $this->applicationEnvironments->add($applicationEnvironment);
        $applicationEnvironment->setApplication($this);
    }

    /**
     * @param ApplicationEnvironment $applicationEnvironment
     */
    public function removeApplicationEnvironment(ApplicationEnvironment $applicationEnvironment)
    {
        $this->applicationEnvironments->removeElement($applicationEnvironment);
    }

    /**
     * @return ArrayCollection
     */
    public function getApplicationEnvironments()
    {
        return $this->applicationEnvironments;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getApplicationEnvironmentByEnvironmentName(string $name)
    {
        foreach ($this->applicationEnvironments as $applicationEnvironment) {
            if ($applicationEnvironment->getEnvironment()->getName() == $name) {
                return $applicationEnvironment;
            }
        }

        return '';
    }

    /**
     * @return array
     */
    public static function getTemplateReplacements(): array
    {
        return [
            'nameCanonical()' => 'getNameCanonical()',
            'serverIps(dev_environment_name)' => 'getApplicationEnvironmentByEnvironmentName(dev_environment_name).getServerIps()',
        ];
    }
}