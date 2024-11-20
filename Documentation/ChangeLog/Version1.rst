.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _changelog:

ChangeLog
=========

For details have a look at the Github commits:
https://github.com/velletti/jve_upgradewizard/commits/main

12.4.25 - fix version number in emconfig for TER
12.4.24 - do not replace. tsconfig WITH .tsconfigconfig (if file was already renamed to .tsconfig ! !
12.4.18 - repair also includeCss and includeJs and icons / logos
12.4.17 - repair also FILE: EXT:
12.4.16 - minor cahnges
12.4.15 - re added updateFilesCommand
12.4.14 - remove unfinished updateFilesCommand as exists in ssch typo3 rector
12.4.13 - fix namespace in updateFilesCommand
12.4.12 - Class name updateFilesCommand
12.4.11 - correct version Number in ext_emconf.php for TER
12.4.10 - fix error on PHP8.1 (and did more test in real Live instances)
12.4.8 - Update only documentation and point in rst file to readme.md
12.4.7 - add a console command to rename and fix local typoscipt and tsconfig files
         console command is not planned for production. use it only on lcal dev mashine
12.4.6 - fixes unneeded trailing comma in Composer.json
12.4.5 - Still connections with github, extension repository and composer packages
12.4.3 - connections with github, extension repository and composer packages
12.4.2 - some cleanup
12.4.1 - first running Version


Breaking changes
________________

   **none**


**Table of Contents**

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   ../Index
   ./ChangeLog
