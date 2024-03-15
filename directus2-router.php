<?php
namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Plugin\Directus2\Utils;
use Grav\Plugin\Directus2\DirectusUtility;

/**
 * Class DirectusRouterPlugin
 * @package Grav\Plugin
 */
class Directus2RouterPlugin extends Plugin
{
    protected $utils;
    protected $directusUtil;

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [
                ['autoload', 100000], // TODO: Remove when plugin requires Grav >=1.7
                ['onPluginsInitialized', 0]
            ],
            'onPageNotFound' => [
                ['onPageNotFound', 0]
            ],
        ];
    }

    /**
    * Composer autoload.
    *is
    * @return ClassLoader
    */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main events we are interested in
        $this->enable([
            //
        ]);
    }

    public function onPageNotFound()
    {

        $grav = $this->grav;
        $this->directusUtil = new DirectusUtility(
            $this->config["plugins.directus2"],
            $grav,
        );
        $this->utils = new Utils( $grav, $this->config["plugins.directus2"] );

        $requestedUri = $this->grav['uri']->path();
        $redirectUrl = '';
        $redirectStatusCode = false;
        $mapping = $this->config()['mapping'];

        $redirectData = $this->getRoute( $requestedUri );

        if(isset($redirectData['data']['0']))
        {
            $redirectUrl = $redirectData['data']['0'][$mapping['target_field']];
            $redirectStatusCode = $redirectData['data']['0'][$mapping['status_field']];
        }
        elseif ($this->config()['track_unknown'])
        {
            $postObj = [
                'status' => 'draft',
                $mapping['request_field'] => $requestedUri
            ];

            if (
                isset( $mapping['page_instance_field'] )
                && $mapping['page_instance_field'] !== null
            )
            {
                $postObj[ $mapping['page_instance_field'] ] = $this->config()['additionalFilters'][$mapping['page_instance_field'] . '.id']['value'];
            }

            try
            {
                $this->directusUtil->insert($mapping['table'], $postObj)->toArray();
            }
            catch (\Error $e)
            {
                // something bad happened…
                $this->utils->log( 'directus-redirect: Putting new route failed.' );
                $this->utils->log( 'Exception. Trace: ' . $e->getMessage() );
                $this->utils->log( 'Exception. File: ' . $e->getFile() . ', ' . $e->getLine() );
                $this->utils->respond( 500, 'putting route failed' );
                exit();
            }
        }


        if ($redirectUrl && $redirectStatusCode)
        {
            $this->redirect($redirectUrl, $redirectStatusCode);
        }
    }

    private function requestItem( $collection, $id = 0, $depth = 2, $filters = [] )
    {
        $requestUrl = $this->directusUtil->generateRequestUrl( $collection, $id, $depth, $filters );
        return $this->directusUtil->get( $requestUrl );
    }

    private function getRoute( $requestedUri = null )
    {
        // is the server reachable?
        $pingStatusCode = $this->directusUtil->get('/server/ping')->getStatusCode();

        if( $pingStatusCode === 200 )
        {
            $filter = [
                $this->config()['mapping']['request_field'] => [
                    'operator' => '_eq',
                    'value' => $requestedUri
                ]
            ];
            foreach ($this->config()['additionalFilters'] as $field => $filterItem)
            {
                $filter[$field] = $filterItem;
            }

            $redirectData = $this->requestItem(
                $this->config()['mapping']['table'],
                0,
                2,
                $filter
            )->toArray();

            return $redirectData;
        }
        else
        {
            $this->utils->log ('directus-redirect: ping to /server/ping not successful', $pingStatusCode );
            $this->utils->respond( 504, 'ping to /server/ping not successful' );
        }
    }

    private function redirect($url, $statusCode = 303)
    {
        header('Location: ' . $url, true, $statusCode);
        die();
    }
}
