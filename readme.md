
# what does extension do
========================

Tested with TYPO3 LTS 12.4.6
works on following fields: 

**sys_template** -> config and constants 
**pages** -> TSconfig
**fe_users** -> TSconfig
**fe_groups** -> TSconfig
**be_users** -> TSconfig
**be_groups** -> TSconfig

## Important NOTICE 

1. you should have a database copy!
2. you should be aware in case of many pages or fe_users, it may be slow
3. you can test it with verbose output -vv (-v default) 
4. you can suppress output with --no or get more output with -vvv


    ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates -vv


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
