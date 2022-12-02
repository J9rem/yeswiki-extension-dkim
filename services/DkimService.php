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

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use YesWiki\Dkim\Exception\MissingArguments;
use YesWiki\Dkim\Exception\NotAKey;
use YesWiki\Core\Service\TripleStore;

class DkimService
{
    public const TRIPLE_PROPERTY = "https://yeswiki.net/vocabulary/dkim-key" ;
    public const PRIVATE_KEY_LENGTH = 2048;

    protected $params;
    protected $tripleStore;

    public function __construct(
        ParameterBagInterface $params,
        TripleStore $tripleStore
    ) {
        $this->params = $params;
        $this->tripleStore = $tripleStore;
    }

    /**
     * return public info for dkim
     * @return array ['publicKey'=> string, 'domain'=> string, 'selector'=> string, 'activated' => bool]
     */
    public function getInfo(): array
    {
        $publicKey = '';
        $domain = '';
        $selector = '';
        $activated = false;
        $data = $this->getFirstAvailableSignature();
        if (!empty($data)){
            list(
                'publicKey' =>$publicKey,
                'domain'=>$domain,
                'selector'=>$selector,
                'activated'=>$activated
            ) = $data;
        }
        return compact(['publicKey','domain','selector','activated']);
    }

    private function getFirstAvailableSignature(): array
    {
        $signatures = $this->tripleStore->getMatching(
            null,
            self::TRIPLE_PROPERTY,
            null,
            '=',
            '=',
            '='
        );
        if (!empty($signatures)){
            $firstSignature = array_shift($signatures);
            $dataDecoded = json_decode($firstSignature['value'],true);
            if (is_array($dataDecoded)){
                $keys = array_keys($dataDecoded);
                sort($keys);
                if ($keys == ['activated','domain','privateKey','publicKey','selector']){
                    return $dataDecoded;
                }
            }
        }
        return [];
    }

    public function canAutogenerateKey(): bool
    {
        return defined('PKCS7_TEXT') && function_exists('openssl_pkey_new') && function_exists('openssl_csr_get_public_key') && function_exists('openssl_pkey_export');
    }

    /**
     * @param string $domain
     * @param string $selector
     * @param string $privateKey
     * @param string $publicKey
     * @throws Exception
     * @throws MissingArguments
     * @throws NotAKey
     */
    public function generateKey(string $domain, string $selector, string $privateKey = '', string $publicKey = ''): array
    {
        if (empty($privateKey) || empty($publicKey)) {
            if (!$this->canAutogenerateKey()) {
                throw new MissingArguments('Cannot generate private key and keys not furnisehd !');
            } else {
                // Generate a new private (and public) key pair
                $privateKeyCert = openssl_pkey_new([
                    'private_key_bits' => self::PRIVATE_KEY_LENGTH,
                    'private_key_type' => OPENSSL_KEYTYPE_RSA
                ]);
                if ($privateKeyCert === false) {
                    throw new MissingArguments('Not able to generate a private key !');
                }
                if (!openssl_pkey_export($privateKeyCert, $privateKey)) {
                    throw new Exception('Not able to the export the private key !');
                }
                $publicKeyCert = openssl_csr_get_public_key($privateKey);
                if ($publicKeyCert === false) {
                    throw new MissingArguments('Not able to extract the public key !');
                }
                if (!openssl_pkey_export($publicKeyCert, $publicKey)) {
                    throw new Exception('Not able to the export the public key !');
                }
            }
        } else {
            $pub = $this->extractKey($publicKey);
            $private = $this->extractKey($privateKey);
        }

        $activated = false;
        $this->update(compact(['domain','selector','privateKey','publicKey','activated']));

        return $this->getInfo();
    }

    public function deleteAll()
    {
        $this->clearPreviousSignatures();
    }

    /**
     * clear previous registration of DKIM signature
     * @param string $domainToKeep
     * @throws Exception
     */
    private function clearPreviousSignatures(string $domainToKeep = '')
    {
        $signatures = $this->tripleStore->getMatching(
            null,
            self::TRIPLE_PROPERTY,
            null,
            '=',
            '=',
            '='
        );
        if (!empty($signatures) && is_array($signatures)){
            $firstSignatureFound = empty($domainToKeep);
            foreach($signatures as $signature){
                if (
                    $firstSignatureFound || 
                    (
                        $domainToKeep != $signature['resource'] &&
                        ($firstSignatureFound = true) // assign directly 
                    )
                    ){
                    $this->tripleStore->delete(
                        $signature['resource'],
                        $signature['property'],
                        $signature['value'],
                        '',
                        ''
                    );
                }
            }
            
            $signatures = $this->tripleStore->getMatching(
                null,
                self::TRIPLE_PROPERTY,
                null,
                '=',
                '=',
                '='
            );
            if (!empty($signatures) && (
                    empty($domainToKeep) ||
                    count($signatures) != 1
                )){
                throw new Exception('Not all signatures were deleted !');
            }
        }
    }

    /**
     * activate or inactivate the DKIM signature
     */
    public function setState(bool $activate): array
    {
        $data = $this->getFirstAvailableSignature();
        if (!empty($data)){
            $data['activated'] = $activate;
            $this->update($data);
        }
        return $this->getInfo();
    }

    private function update($data)
    {
        $this->clearPreviousSignatures($data['domain']);
        $previousData = $this->tripleStore->getOne(
            $data['domain'],
            self::TRIPLE_PROPERTY,
            '',
            ''
        );
        if (empty($previousData)){
            if ($this->tripleStore->create(
                $data['domain'],
                self::TRIPLE_PROPERTY,
                json_encode($data),
                '',
                ''
            ) != 0 ){
                throw new Exception("Error creating the signature into database");
            }
        } else {
            if ($this->tripleStore->update(
                $data['domain'],
                self::TRIPLE_PROPERTY,
                $previousData,
                json_encode($data),
                '',
                ''
            ) != 0 ){
                throw new Exception("Error updating the signature into database");
            }
        }
    }

    public function configDKIM(PHPMailer $mailer): ?PHPMailer
    {
        $data = $this->getFirstAvailableSignature();
        if (empty($data) || !$this->isUsable($data['domain'])){
            return $mailer;
        }
        $mailer->DKIM_domain = $data['domain'];
        $mailer->DKIM_selector = $data['selector'];
        $mailer->DKIM_private_string = $data['privateKey'];
        return $mailer;
    }

    public function isUsable(string $domain): bool
    {
        return !empty($domain) &&
            $this->params->has('contact_from') &&
            substr($this->params->get('contact_from'),-strlen("@$domain")) == "@$domain";
    }

    public function extractKey(string $rawKey): string
    {
        if (preg_match('/^\\s*-{5}BEGIN [A-Z ]+ KEY-{5}\s*([A-Za-z0-9+\/=\s]+)\s*-{5}END [A-Z ]+ KEY-{5}\s*$/',$rawKey,$matches)){
            return str_replace([' ',"\n","\r"],'',$matches[1]);
        } else {
            throw new NotAKey("This this not a key in PEM format");
        }
    }
}
