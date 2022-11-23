# Extension dkim

This extension appends DKIM field in emails.

## Introduction

The emails' DKIM field appends a signature on content which indicates the authenticity of teh messagein relation to the used domain. This security process allows to reduce the number of messages sent with address usurpation.

 - [Wikipedia article](https://en.wikipedia.org/wiki/DomainKeys_Identified_Mail)
 - [Official website](https://www.dkim.org/)
 - [DKIM Service Overview Specification](https://www.dkim.org/specs/rfc5585.html)

## Configuration

The configuration is made through handler `/admindkim` only accessible to administrators : [direct link on this wiki](?GererSite/admindkim 'DKIM admin page :ignore').

!> **If no key was created, then there is no acces to a public key**.

### Create a key

To create a kay, you must give a **domain name** associated to the key, and a **selector** for [DNS](https://en.wikipedia.org/wiki/Domain_Name_System) that will be used for this domain.

Then, click on update to generate a new key.

_The private key is saved into the database. The public key is diplayed with the corresponding DNS field._

### Configuration of [DNS](https://en.wikipedia.org/wiki/Domain_Name_System)

!> **If your DNS is not up-to-date with the rigth parameters, e-mails will have bad DKIM signature and should be not distributed.**

Copy DNS parameter furnish by handler [`/admindkim`](?GererSite/admindkim 'DKIM admin page :ignore') into DNS parameters of your website.

### Activation/inactivation

When a DKIM key is saved into the database, it is possible to activate or not its usage by the dedicated button on handler [`/admindkim`](?GererSite/admindkim 'DKIM admin page :ignore').

## Usage

There is nothing to do because since the extension is installed, lthe key generated and activated and the DNS field rigthly registered into DNS parameters of your website, sent emails via the wiki will have DKIM signature.

<div style="text-align:center;">

[Modify this page on GitHub](https://github.com/J9rem/yeswiki-extension-dkim/edit/doc/docs/en/README.md)

</div>