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

use Throwable;
use YesWiki\Core\YesWikiHandler;
use YesWiki\Dkim\Exception\MissingArguments;
use YesWiki\Dkim\Exception\NotAKey;
use YesWiki\Dkim\Service\DkimService;

class AdminDkimHandler extends YesWikiHandler
{
    protected $canGenerateKeys ;
    protected $dkimService;

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
        $this->dkimService = $this->getService(DkimService::class);

        $message = '';
        $this->canGenerateKeys = $this->dkimService->canAutogenerateKey();
        if (isset($_POST['delete']) || isset($_POST['activate']) || (isset($_POST['domain']) && isset($_POST['selector']))){
            $message= $this->processPost($_POST ?? []);
        }

        extract($this->dkimService->getInfo());

        if (!empty($publicKey)){
            try {
                $publicKey = $this->dkimService->extractKey($publicKey);
            } catch (NotAKey $th) {
                $publicKey = 'THIS IS NOT A KEY IN PEM FORMAT !';
                $message .= (empty($message)?'':'<br/>')._t('DKIM_KEY_NOT_IN_PEM_FORMAT');
            }
        }

        return $this->renderInSquelette('@dkim/admin-dkim.twig', [
            'activated' => $activated,
            'canGenerateKeys' => $this->canGenerateKeys,
            'domain' => !empty($domain) ? $domain : parse_url($this->params->get('base_url'), PHP_URL_HOST) ,
            'isUsable' => !empty($publicKey) ? $this->dkimService->isUsable($domain) : false,
            'message' => $message,
            'publicKey' => $publicKey,
            'selector' => !empty($selector) ? $selector : 'wiki',
        ]);
    }

    /**
     * process post from $_POST
     * @param array $post
     * @return string $message
     */
    protected function processPost(array $post): string
    {
        $message = '';

        
        if (isset($post['delete'])){
            $delete = filter_var($post['delete'],FILTER_VALIDATE_BOOL);
            if ($delete){
                try {
                    $this->dkimService->deleteAll();
                } catch (Throwable $th) {
                    $message = _t('DKIM_DELETE_ERROR',['error'=>$th->getMessage(),'file'=>basename($th->getFile()),'line'=>$th->getLine()]);
                }
            }
            return $message;
        }

        if (isset($post['activate'])){
            $activate = filter_var($post['activate'],FILTER_VALIDATE_BOOL);
            try {
                $data = $this->dkimService->setState($activate);
            } catch (Throwable $th) {
                $message = _t('DKIM_ACTIVATE_ERROR',['error'=>$th->getMessage(),'file'=>basename($th->getFile()),'line'=>$th->getLine()]);
            }
            return $message;
        }

        $domain = filter_var($post['domain'],FILTER_UNSAFE_RAW);
        $domain = (in_array($domain, [false,null], true) || !is_string($domain)) ? "" : $domain;

        $selector = filter_var($post['selector'],FILTER_UNSAFE_RAW);
        $selector = (in_array($selector, [false,null], true) || !is_string($selector)) ? "" : $selector;

        $privateKey = filter_var($post['privateKey'] ?? '',FILTER_UNSAFE_RAW);
        $privateKey = (in_array($privateKey, [false,null], true) || !is_string($privateKey)) ? "" : $privateKey;

        $publicKey = filter_var($post['publicKey'] ?? '',FILTER_UNSAFE_RAW);
        $publicKey = (in_array($publicKey, [false,null], true) || !is_string($publicKey)) ? "" : $publicKey;

        if (empty($domain)||empty($selector)){
            return _t('DKIM_MISSING_ARGS');
        }
        try {
            $this->dkimService->generateKey($domain,$selector,$privateKey,$publicKey);
        } catch (MissingArguments $th) {
            $message = _t('DKIM_NOT_ABLE_TO_GENERATE_KEYS');
            $this->canGenerateKeys = false;
        } catch (NotAKey $th) {
            $message = _t('DKIM_KEY_NOT_IN_PEM_FORMAT');
        } catch (Throwable $th) {
            $message = _t('DKIM_ERROR',['error'=>$th->getMessage(),'file'=>basename($th->getFile()),'line'=>$th->getLine()]);
        }

        return $message;
    }
}
