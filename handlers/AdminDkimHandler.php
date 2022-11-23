<?php

/*
 * This file is part of the YesWiki Extension dkim.
 *
 * Authors : see README.md file that was distributed with this source code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace YesWiki\Dkim;

use YesWiki\Core\YesWikiHandler;
use YesWiki\Dkim\Service\DkimService;

class AdminDkimHandler extends YesWikiHandler
{
    public function run()
    {
        if ($this->isWikiHibernated()) {
            return $this->getMessageWhenHibernated();
        }
        if (!$this->wiki->UserIsAdmin()) {
            return $this->renderInSquelette('@templates/alert-message.twig', [
                'type' => 'danger',
                'message' => get_class($this). ' : ' . _t('BAZ_NEED_ADMIN_RIGHTS')
            ]);
        }
        // get service
        $dkimService = $this->getService(DkimService::class);

        extract($dkimService->getInfo());

        return $this->renderInSquelette('@dkim/admin-dkim.twig', [
            'domain' => !empty($domain) ? !$domain : parse_url($this->params->get('base_url'), PHP_URL_HOST) ,
            'publicKey' => $publicKey,
            'selector' => $selector,
        ]);
    }
}
