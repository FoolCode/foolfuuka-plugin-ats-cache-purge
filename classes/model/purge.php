<?php

namespace Foolz\Foolfuuka\Plugins\ATSCachePurge\Model;

use Foolz\Foolframe\Model\Context;
use Foolz\Foolframe\Model\Model;
use Foolz\Foolframe\Model\Preferences;
use Foolz\Foolfuuka\Model\Media;

class ATSCachePurge extends Model
{
    /**
     * @var Preferences
     */
    protected $preferences;

    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->preferences = $context->getService('preferences');
    }

    public function beforeDeleteMedia($result)
    {
        /** @var Media $post */
        $post = $result->getObject();
        $file = [];

        // purge full image
        try {
            $file['full'] = $post->getDir(false, true, true);
        } catch (\Foolz\Foolfuuka\Model\MediaException $e) {

        }

        // purge thumbnail
        try {
            $post->op = 0;
            $file['thumb-0'] = $post->getDir(true, true, true);
        } catch (\Foolz\Foolfuuka\Model\MediaException $e) {

        }

        try {
            $post->op = 1;
            $file['thumb-1'] = $post->getDir(true, true, true);
        } catch (\Foolz\Foolfuuka\Model\MediaException $e) {

        }

        $host = $this->preferences->get('foolfuuka.plugins.ats_cache_purge.hostname');
        $servers = $this->getServers();

        foreach ($servers as $server) {
            foreach ($file as $uri) {
                if (null === $uri) {
                    continue;
                }

                $curl = curl_init();
                $opts = [
                    CURLOPT_URL => $server.$uri,
                    CURLOPT_HTTPHEADER => ['Host: '.$host],
                    CURLOPT_CUSTOMREQUEST => 'PURGE',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FRESH_CONNECT => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                ];

                curl_setopt_array($curl, $opts);
                curl_exec($curl);
                curl_close($curl);
            }
        }

        return null;
    }

    public function getServers()
    {
        $servers = $this->preferences->get('foolfuuka.plugins.ats_cache_purge.servers');

        return array_filter(preg_split('/\r\n|\r|\n/', $servers));
    }
}
