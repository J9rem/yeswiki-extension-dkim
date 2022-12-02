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
use YesWiki\Security\Controller\SecurityController;

class UpdateHandler__ extends YesWikiHandler
{
    private const EMAIL_INC_PATH = 'includes/email.inc.php';

    public function run()
    {
        if ($this->getService(SecurityController::class)->isWikiHibernated()) {
            throw new \Exception(_t('WIKI_IN_HIBERNATION'));
        };
        if (!$this->wiki->UserIsAdmin()) {
            return null;
        }
        $output = '<strong>Extension dkim</strong><br />';
        $output .= "ℹ️ Update email.inc.php... ";

        
        if (!file_exists(self::EMAIL_INC_PATH)) {
            $output .= "❌ file missing<br/>";
        } else {
            $content = file_get_contents(self::EMAIL_INC_PATH);
            $anchor1 = 'function send_mail($mail_sender, $name_sender, $mail_receiver, $subject, $message_txt, $message_html = \'\')__s__{__s__//Create a new PHPMailer instance__s__$mail = new PHPMailer(true);';
            $formattedAnchor1 = str_replace('__s__','\\s*',preg_quote($anchor1,'/'));
            $anchor2 = 'try {';
            $formattedAnchor2 = preg_quote($anchor2,'/');

            $addon = <<<PHP

                if (\$GLOBALS['wiki']->services->has(\\YesWiki\\Dkim\\Service\\DkimService::class)){
                    \$GLOBALS['wiki']->services->get(\\YesWiki\\Dkim\\Service\\DkimService::class)
                        ->configDKIM(\$mail);
                }
            PHP;
            $formattedAddon = preg_quote($addon,'/');

            $patternBefore = "/$formattedAnchor1\s*$formattedAnchor2/";
            $patternReplace = "/($formattedAnchor1)(\s*$formattedAnchor2)/";
            $patternAfter = "/$formattedAnchor1\s*$formattedAddon\s*$formattedAnchor2/";

            if (preg_match($patternAfter, $content, $matches)) {
                $output .= "file already updated<br/>";
            } elseif (!preg_match($patternBefore, $content, $matches)) {
                $output .= "❌ error, content not waited in file !<br/>";
            } else {
                $output .= "updating file..";
                $newContent = preg_replace($patternReplace, "\$1$addon\$2", $content);
                if (is_null($newContent) || $newContent === $content) {
                    $output .= "❌ error, content not rightly updated !<br/>";
                } else {
                    file_put_contents(self::EMAIL_INC_PATH, $newContent);
                    $content = file_get_contents(self::EMAIL_INC_PATH);
                    if (!preg_match($patternAfter, $content, $matches)) {
                        $output .= "❌ error, not possible to write into file !<br/>";
                    } else {
                        $output .= '✅ Done !<br />';
                    }
                }
            }
        }

        // set output
        $this->output = str_replace(
            '<!-- end handler /update -->',
            $output.'<!-- end handler /update -->',
            $this->output
        );
        return null;
    }
}
