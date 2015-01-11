<?php

namespace Foolz\Foolframe\Controller\Admin\Plugins;

use Foolz\Foolframe\Model\Validation\ActiveConstraint\Trim;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ATSCachePurge extends \Foolz\Foolframe\Controller\Admin
{
    public function before()
    {
        parent::before();

        $this->param_manager->setParam('controller_title', 'ATS Cache Purge');
    }

    public function security()
    {
        return $this->getAuth()->hasAccess('maccess.admin');
    }

    function structure()
    {
        return [
            'open' => [
                'type' => 'open',
            ],
            'foolfuuka.plugins.ats_cache_purge.hostname' => [
                'type' => 'input',
                'preferences' => true,
                'label' => _i('Hostname'),
                'help' => _i(''),
                'class' => 'span8',
                'validation' => [new Trim()]
            ],
            'foolfuuka.plugins.ats_cache_purge.servers' => [
                'type' => 'textarea',
                'preferences' => true,
                'label' => _i('Servers'),
                'help' => _i(''),
                'class' => 'span8',
                'validation' => [new Trim()]
            ],
            'separator-2' => [
                'type' => 'separator-short'
            ],
            'submit' => [
                'type' => 'submit',
                'class' => 'btn-primary',
                'value' => _i('Submit')
            ],
            'close' => [
                'type' => 'close'
            ],
        ];
    }

    function action_manage()
    {
        $this->param_manager->setParam('method_title', 'Manage');

        $data['form'] = $this->structure();

        $this->preferences->submit_auto($this->getRequest(), $data['form'], $this->getPost());
        $this->builder->createPartial('body', 'form_creator')->getParamManager()->setParams($data);

        return new Response($this->builder->build());
    }
}
