<?php

namespace DigipolisGent\Domainator9k\CoreBundle\EntityService;

use Ctrl\Common\EntityService\AbstractDoctrineService;
use DigipolisGent\Domainator9k\CoreBundle\Entity\Application;
use ZipStream\ZipStream;

/**
 * @codeCoverageIgnore
 *
 * @todo No tests are written for these methods because they might be removed or
 * will at least be heavily refactored.
 */
class ApplicationService extends AbstractDoctrineService
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return Application::class;
    }

    /**
     * Generates the drush alias files.
     *
     * @param Application $application
     * @param ServerService $serverService
     *
     * @return string
     *     The file content
     */
    public function generateDrushAliasFile(Application $application, ServerService $serverService)
    {
        //todo: make requiresdrush variable in appType config ?
        if (false !== strpos($application->getType()->getSlug(), 'drupal')) {
            throw new \InvalidArgumentException(sprintf(
                'Only Drupal applications can be used to generate drush alias files, %s given',
                $application->getType()->getSlug()
            ));
        }

        $serverFinder = $serverService->getFinder();

        // this assumes there is ! task server per env?
        $servers = $serverFinder->find(array('taskServer = true'))->getAll();

        $ips = [];
        foreach ($servers as $server) {
            $ips[$server->getEnvironment()] = $server->getIp();
        }

        $prod = $application->getProdAppEnvironment();

        $accountName = $prod->getServerSettings()->getUser();
        $appFolder = $application->getNameForFolder();

        $drush = <<<DRUSH
<?php
/**
 * Local alias
 * Set the root and site_path values to point to your local site
 */
\$aliases['local'] = array(
  'root' => '/path/to/drupal/root',
  'uri' => 'yoursite.localhost',
);

/**
 * Remote parent alias
 * Set up each entry to suit your site configuration
 */
\$common_remote = array (
  'root' => '/home/$accountName/apps/$appFolder/current',
  'remote-user' => '$accountName',
  'ssh-options' => '-o StrictHostKeyChecking=no',
  'command-specific' => array (
    'sql-sync' => array (
      'sanitize' => TRUE,
      'no-ordered-dump' => TRUE,
      'structure-tables-list' => array('batch', 'cache', 'cache_', '_cache',
        'cache', 'flood', 'search_dataset', 'search_index', 'search_total',
        'semaphore', 'sessions', 'watchdog'
      ),
    ),
  ),
);

DRUSH;

        foreach ($servers as $server) {
            $drush .= <<<DRUSH

/**
 * {$server->getEnvironment()} alias
 */
\$aliases['{$server->getEnvironment()}'] = array (
  'uri' => 'http://{$application->getAppEnvironment($server->getEnvironment())->getPreferredDomain()}',
  'remote-host' => '{$ips[$server->getEnvironment()]}',
) + \$common_remote;
DRUSH;
        }

        return $drush;
    }

    /**
     * Generates zip with drush alias files for all drupal applications.
     *
     * @param ServerService $serverService
     *
     * @return ZipStream
     */
    public function generateDrushAliasFilesZip(ServerService $serverService)
    {
        //todo: make requiresdrush variable in appType config ?
        /** @var Application[] $apps */
        $apps = $this->getEntityRepository()->createQueryBuilder('a')
            ->join('a.type', 'type')
            ->where('type.slug LIKE \'%drupal%\'')
            ->getQuery()
            ->getResult();

        if (!count($apps)) {
            throw new \RuntimeException('No Drupal applications found.');
        }

        // zip streamer outputs directly, catch in buffer
        ob_start();

        $zip = new ZipStream();
        foreach ($apps as $app) {
            $zip->addFile($app->getNameCanonical() . '.aliases.drushrc.php', $this->generateDrushAliasFile($app, $serverService));
        }

        $zip->finish();

        $zip = ob_get_contents();
        ob_end_clean();

        return $zip;
    }

    /**
     * Generates a csv for all apps with application, environment and domain.
     *
     * @param Application[] $apps
     *
     * @return string
     *     The file content
     */
    public function generateDomainCsv(array $apps)
    {
        // open handle
        $h = fopen('php://temp', 'w');

        // headers
        fputcsv($h, ['application', 'environment', 'domain']);

        // rows
        foreach ($apps as $app) {
            foreach ($app->getAppEnvironments() as $env) {
                fputcsv($h, [
                    $app->getName(),
                    $env->getName(),
                    $env->getPreferredDomain(),
                ]);
            }
        }

        // Get content and close handle.
        rewind($h);
        $csv = stream_get_contents($h);
        fclose($h);

        return $csv;
    }
}
