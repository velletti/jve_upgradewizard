.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: Includes.txt

.. _start:


============
INTRODUCTION
============

NO WARRANTY for this script!
feel free to add `an Issue on Git <https://github.com/velletti/jve_upgradewizard/issues>`_

    1. you should have a database copy!
    2. you should be aware in case of many pages or fe_users, it may be slow
    3. run this wizard on production system only if you have tested it locally
    4. you should have experience to cun typo3 console command
    5. you should work with any vcs like git
    6. make a copy of your template folder for easier testing


""""""""""""
Installation
""""""""""""

Install extension via composer

    composer req jvelletti/jve-upgradewizard

"""""""""""""""""""""""""""""""""""""""
Run upgrade wizard in local development
"""""""""""""""""""""""""""""""""""""""

testing with needed info output:

    ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates -vv

"""""""""""""""""""""""""""""""""""""""
Run console command in local development
"""""""""""""""""""""""""""""""""""""""

this part fixes files in your template Folder. This should be under git / vcs controll to see the changes afterwards

    ./vendor/bin/typo3 jvelletti:updatefiles  -vv


Details
"""""""

   see more details in ` readme.md on Git <https://github.com/velletti/jve_upgradewizard#readme>`_



**Table of Contents**

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   ../Index
   ./ChangeLog

