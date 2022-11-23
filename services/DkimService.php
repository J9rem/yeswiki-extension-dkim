<?php

/*
 * This file is part of the YesWiki Extension dkim.
 *
 * Authors : see README.md file that was distributed with this source code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace YesWiki\Dkim\Service;

use PHPMailer\PHPMailer\PHPMailer;
use YesWiki\Core\Service\TripleStore;

class DkimService
{
    public const TRIPLE_PROPERTY = "https://yeswiki.net/vocabulary/dkim-key" ;

    protected $tripleStore;

    public function __construct(
        TripleStore $tripleStore,
    ) {
        $this->tripleStore = $tripleStore;
    }

    /**
     * return public info for dkim
     * @return array ['publicKey'=> string, 'domain'=> string, 'selector'=> string]
     */
    public function getInfo(): array
    {
        $publicKey = '';
        $domain = '';
        $selector = '';
        return compact(['publicKey','domain','selector']);
    }

    public function generateKey(string $domain, string $selector): array
    {
        return $this->getInfo();
    }

    /**
     * activate or inactivate the DKIM signature
     */
    public function setState(bool $activate): array
    {
        return $this->getInfo();
    }

    public function configDKIM(PHPMailer $mailer): ?PHPMailer
    {
        extract($this->getInfo());
        if (empty($publicKey)) {
            return $mailer;
        }
        $mailer->DKIM_domain = $domain;
        $mailer->DKIM_selector = $domain;
        // $mailer->DKIM_identity = '???';
        // $mailer->DKIM_private = '???'; // path to private key file
        $mailer->DKIM_private_string = '???'; // private key
        // $mailer->DKIM_passphrase = '???';  //if encrypted key
        return $mailer;
    }
}
