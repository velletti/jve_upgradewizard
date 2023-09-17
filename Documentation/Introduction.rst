.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: Includes.txt

.. _start:


============
INTRODUCTION
============

    1. you should have a database copy!
    2. you should be aware in case of many pages or fe_users, it may be slow
    3. run this wizard on production system only if you have tested it locally

""""""""""""
Installation
""""""""""""

Install extension via composer

    composer req jvelletti/jve-upgradewizard

""""""""""""
Run wizard
""""""""""""

testing with needed info output:

    ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates -vv

default:

    ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates

suppress all output:

    ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates --no

include also debug output:

    ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates -vvv

"""""""""""""""
What does it do
"""""""""""""""

    ## Fixes file ending .ts .txt and .text to .typoscript in database with warning

        @import 'EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.ts'
        @import 'EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.txt'
        @import 'EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.text'


    ## Fixes  wrong "EXT:" or missing "EXT: syntax

    not required, but helps to make extension to fiddle result easier

        @import "EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.ts"
        @import "EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript"
        @import "FILE:EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript"
        @import "/typo3conf/ext/jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript"


    ## Fixes  INCLUDE_TYPOSCRIPT src=

        <INCLUDE_TYPOSCRIPT src="/typo3conf/ext/jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript">
        <INCLUDE_TYPOSCRIPT src='/typo3conf/ext/jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript'>


    ## Changes double quote " char to single quote '

    not required, but helps to make extension to fiddle result easier


    ## Result in all cases:

        @import 'EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript'

    fixes also lines starting with # but keep coment status intakt.


    ## Warns if file in fileadmin and not in any EXT Folder:

         @import "/fileadmin/template/TSConfig/TSConfig.typoscript"


""""""""""""
Restrictions
""""""""""""

   1. does not change Filenames locally
   2. does not fix entries in fileadmin like  @import "/fileadmin/tscript.ts"


To Dos
""""""

   maybe work on a option to fix also local filenames and includes in filenames.
   but as this is easy to solve by "refactor" in an IDE like PHP Storm,
   this is currently not on my prio list.



**Table of Contents**

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   ../Index
   ./ChangeLog

