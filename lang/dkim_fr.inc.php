<?php

return [
    /*
 * This file is part of the YesWiki Extension dkim.
 *
 * Authors : see README.md file that was distributed with this source code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

    // handlers/AdminDkimHandler.php
    'DKIM_ACTIVATE_ERROR' => 'Une erreur est survenue pendant l\'activation/désactivation de la clé DKIM avec ce message : %{error} (fichier: %{file}, ligne: %{line})',
    'DKIM_DELETE_ERROR' => 'Une erreur est survenue pendant la suppression de la clé DKIM avec ce message : %{error} (fichier: %{file}, ligne: %{line})',
    'DKIM_ERROR' => 'Une erreur est survenue pendant l\'enregistrement de la clé DKIM avec ce message : %{error} (fichier: %{file}, ligne: %{line})',
    'DKIM_MISSING_ARGS' => 'Erreur : un des paramètres requis est vide. Veuillez bien compléter le domaine et le sélecteur.',
    'DKIM_NOT_ABLE_TO_GENERATE_KEYS' => 'Erreur : il n\'est pas possible de générer les clés avec ce wiki. Veuillez utiliser un autre outil pour générer les clés RSA.',
    'DKIM_KEY_NOT_IN_PEM_FORMAT' => 'la clé n\'a pas été fourni dans un format PEM',

    // templates/admin-dkim.twig
    'DKIM_ACTIVATION_STATUS' => 'Clé activée',
    'DKIM_DOMAIN' => 'Domaine pour la clé DKIM',
    'DKIM_KEYS_FORM_INFO' => 'La saisie directe des clés n\'est pas vérifiée. Vos clés doivent donc être correctes.',
    'DKIM_PRIVATE_KEY' => 'Clé privée DKIM',
    'DKIM_PUBLIC_KEY' => 'Clé publique DKIM',
    'DKIM_PUBLIC_KEY_NOT_SET' => 'La clé publique DKIM n\'est pas définie',
    'DKIM_PUBLIC_KEY_NOTUSABLE' => 'La clé publique DKIM est n\'est pas utlisé car pour ceci, le wiki doit avoir configuré le paramètre `contact_from` dans la page GererConfig et cette adresse e-mail doit avoir le même nom de domain que `%{domain}`',
    'DKIM_PUBLIC_KEY_SET' => 'La clé publique DKIM est : %{key}<br/>Paramètre DNS à utiliser pour le domaine %{domain}:<br/>TYPE <b>TXT</b><br/>NAME <b>%{selector}.%{domain}</b><br/>VALUE <b>"v=DKIM1;k=rsa;p=%{key}"</b><br/>N\'activer la clé qu\'une fois le DNS à jour !',
    'DKIM_HIDE_KEYS_BLOCK' => 'Masquer cette zone',
    'DKIM_SEE_KEYS_BLOCK' => 'Saisir manuellement les clés DKIM',
    'DKIM_SELECTOR' => 'Sélecteur pour le champ DKIM',
    'DKIM_UPDATE' => 'Mettre à jour',
];
