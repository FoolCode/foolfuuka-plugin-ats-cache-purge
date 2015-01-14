<?php

use Foolz\Foolframe\Model\Autoloader;
use Foolz\Foolframe\Model\Context;
use Foolz\Plugin\Event;

class HHVM_ATSCachePurge
{
    public function run()
    {
        Event::forge('Foolz\Plugin\Plugin::execute#foolz/foolfuuka-plugin-ats-cache-purge')
            ->setCall(function ($result) {
                /* @var Context $context */
                $context = $result->getParam('context');
                /** @var Autoloader $autoloader */
                $autoloader = $context->getService('autoloader');

                $autoloader->addClassMap([
                    'Foolz\Foolframe\Controller\Admin\Plugins\ATSCachePurge' => __DIR__ . '/classes/controller/admin.php',
                    'Foolz\Foolfuuka\Plugins\ATSCachePurge\Model\ATSCachePurge' => __DIR__ . '/classes/model/purge.php'
                ]);

                $context->getContainer()
                    ->register('foolfuuka-plugin.ats_cache_purge', 'Foolz\Foolfuuka\Plugins\ATSCachePurge\Model\ATSCachePurge')
                    ->addArgument($context);

                Event::forge('Foolz\Foolframe\Model\Context::handleWeb#obj.afterAuth')
                    ->setCall(function ($result) use ($context) {
                        // don't add the admin panels if the user is not an admin
                        if ($context->getService('auth')->hasAccess('maccess.admin')) {
                            $context->getRouteCollection()->add(
                                'foolfuuka.plugin.ats_cache_purge.admin', new \Symfony\Component\Routing\Route(
                                    '/admin/plugins/ats_cache_purge/{_suffix}',
                                    [
                                        '_suffix' => 'manage',
                                        '_controller' => 'Foolz\Foolframe\Controller\Admin\Plugins\ATSCachePurge::manage'
                                    ],
                                    [
                                        '_suffix' => '.*'
                                    ]
                                )
                            );

                            Event::forge('Foolz\Foolframe\Controller\Admin::before#var.sidebar')
                                ->setCall(function ($result) {
                                    $sidebar = $result->getParam('sidebar');
                                    $sidebar[]['plugins'] = [
                                        'content' => ['ats_cache_purge/manage' => ['level' => 'admin', 'name' => 'ATS Cache Purge', 'icon' => 'icon-leaf']]
                                    ];
                                    $result->setParam('sidebar', $sidebar);
                                });
                        }
                    });

                Event::forge('Foolz\Foolfuuka\Model\Media::delete#call.beforeMethod')
                    ->setCall(function ($result) use ($context) {
                        $context->getService('foolfuuka-plugin.ats_cache_purge')->beforeDeleteMedia($result);
                    });
            });

    }
}

(new HHVM_ATSCachePurge())->run();
