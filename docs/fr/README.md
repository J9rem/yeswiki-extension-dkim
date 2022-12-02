# Extension dkim

Cette extension ajoute le champ DKIM aux emails.

## Introduction

Le champ DKIM pour les e-mails permet d'ajouter une signature sur le contenu qui indique l'authenticité du message par rapport au nom de domaine. C'est une sécurité qui permet de réduire les e-mails envoyés par usurpations d'adresse.

 - [Article Wikipedia](https://fr.wikipedia.org/wiki/DomainKeys_Identified_Mail)
 - [Site officiel](https://www.dkim.org/)
 - [Spécification DKIM Service Overview](https://www.dkim.org/specs/rfc5585.html)

## Configuration

La configuration se fait via le handler `/admindkim` uniquement accessible aux administrateurs : [lien direct pour ce wiki](?GererSite/admindkim 'Page d\'administration DKIM :ignore').

!> **Si aucune clé n'a été créé, alors vous n'aurez pas accès à la clé publique**.

### Création d'une clé

Pour créer une clé, vous devez fournir **le nom de domaine** associé pour l'usage de cette clé, ainsi qu'un **sélecteur** pour l'en-tête [DNS](https://fr.wikipedia.org/wiki/Domain_Name_System) qui sera utilisé pour ce domaine.

Ensuite, cliquez sur mettre pour générer une nouvelle clé.

_La clé privée est alors sauvegardée dans la base de donnée. La clé publique est affichée avec le champ DNS correspondant._

### Configuration du [DNS](https://fr.wikipedia.org/wiki/Domain_Name_System)

!> **Si votre DNS n'est pas à jour avec les bons paramètres, les e-mails auront la mauvaise signature DKIM et pourraient ne pas être distribués.**

Recopier le paramètre DNS fourni par le handler [`/admindkim`](?GererSite/admindkim 'Page d\'administration DKIM :ignore') dans les paramètres DNS de votre hébergement.

### Activation/désactivation

Lorqu'une clé DKIM est enregistrée dans la base de données, il est possible d'activer ou non son usage grâce au bouton dédié dans le handler [`/admindkim`](?GererSite/admindkim 'Page d\'administration DKIM :ignore').

## Utilisation

Il n'y a rien de particuliers à faire car une fois l'extension installée, la clé générée et active et le champ DNS correctement enregistré dans les paramètres DNS de votre hébergement, les e-mails envoyés par le wiki auront la signature DKIM.

<div style="text-align:center;">

[Modifier cette page sur GitHub](https://github.com/J9rem/yeswiki-extension-dkim/edit/doc/docs/fr/README.md)

</div>