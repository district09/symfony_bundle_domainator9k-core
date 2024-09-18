<?php


namespace DigipolisGent\Domainator9k\CoreBundle\Entity;

use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\IdentifiableTrait;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Traits\TemplateImplementationTrait;
use DigipolisGent\SettingBundle\Entity\Traits\SettingImplementationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractApplication
 * @package DigipolisGent\Domainator9k\CoreBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="abstract_application")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr",type="string")
 * @UniqueEntity(fields={"name"})]
 * @ORM\HasLifecycleCallbacks()
 */
abstract class AbstractApplication implements TemplateInterface
{

    use SettingImplementationTrait;
    use IdentifiableTrait;
    use TemplateImplementationTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Length(min="2", max="255")
     * @Assert\Regex(
     *     pattern="/^[a-z0-9\-]+$/",
     *     message="Name can only contain alphanumeric characters and dashes."
     * )
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="git_repo", type="string", nullable=false)
     * @Assert\Regex(
     *     pattern="/^(git@[a-z0-9-\.]*\.[a-z]*:|https:\/\/[a-z0-9-\.]*\.[a-z]*\/)[a-zA-Z0-9-]*\/[^\s.\\]*\.git$/",
     *     message="The git repository url must be in the git@host:respository.git or the https://host/repository.git format"
     * )
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
     */
    protected $applicationEnvironments;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted",type="boolean")
     */
    protected $deleted = false;

    /**
     * @var string
     *
     * @ORM\Column(name="application_type",type="string")
     */
    protected $applicationType;

    /**
     * @return string
     */
    abstract public static function getApplicationType(): string;

    /**
     * @return string
     */
    abstract public static function getFormType(): string;

    public static function getSettingImplementationName()
    {
        return 'application';
    }

    public function __construct()
    {
        $this->applicationEnvironments = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): ?string
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
    public function getNameCanonical(): ?string
    {
        $name = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $this->getName()));
        return substr($name, 0, 14);
    }

    /**
     * @return string
     */
    public function getGitRepo(): ?string
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
    public function isHasDatabase(): ?bool
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
    public function getApplicationEnvironments(): Collection
    {
        return $this->applicationEnvironments;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getApplicationEnvironmentByEnvironmentName(string $name): ?ApplicationEnvironment
    {
        foreach ($this->applicationEnvironments as $applicationEnvironment) {
            if ($applicationEnvironment->getEnvironment()->getName() == $name) {
                return $applicationEnvironment;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public static function additionalTemplateReplacements(): array
    {
        // Backward compatibility.
        return [
            'serverIps(dev_environment_name)' => 'getApplicationEnvironmentByEnvironmentName(dev_environment_name).getServerIps()',
        ];
    }

    /**
     * @return bool
     */
    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted = false)
    {
        $this->deleted = $deleted;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist(){
        $this->applicationType = $this::getApplicationType();
    }
}
