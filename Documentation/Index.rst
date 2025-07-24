.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: Includes.txt

.. _start:

=================================
Add/remove Frontend User to Group
=================================

:Extension Key:
  jve_upgradewizard

:Classification:
  jve_upgradewizard

:Version:
  12.4.33

:Language:
  en

:Description:
   Just needed during Upgrade from older TYPO3 version to LTS 12.4
   Fixes the ways included files have been added in database.

   Fixes filenames and there Content on a local DEV mashine


   Tested with TYPO3 LTS 11.4.31 on PHP 7.4
   Tested with TYPO3 LTS 12.4.6 on PHP 8.1

   Works on following fields:

   **sys_template** -> config and constants
   **pages** -> TSconfig
   **fe_users** -> TSconfig
   **fe_groups** -> TSconfig
   **be_users** -> TSconfig
   **be_groups** -> TSconfig




   Version: |version|

:Keywords:
  typoscript,tsconfig,rename,include,import,upgrade,jve

:Copyright:
  2023

:Author:
  Jörg Velletti

:Email:
  typo3@velletti.de

:License:
	This document is published under the Open Content License
	available from http://www.opencontent.org/opl.shtml

:Rendered:
	|today|

The content of this document is related to TYPO3,
a GNU/GPL CMS/Framework available from `www.typo3.org <http://www.typo3.org/>`_.

**Table of Contents**

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   Index
   Introduction
   ChangeLog/Changelog