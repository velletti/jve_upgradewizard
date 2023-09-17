
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



## Internal reminder for the extension maintainer:
To Update this extension in TER:
change version Number to "x.y.z" in Documentation\ in Settings.cfg and Index.rst
create Tag "x.y.z"
git push --tags

create new zip file:
cd typo3conf/ext/jve_upgradewizard
git archive -o "${PWD##*/}_x.y.z.zip" HEAD

f.e.:
git archive -o "${PWD##*/}_12.4.3.zip" HEAD


Upload ZIP File to https://extensions.typo3.org/my-extensions
git push

setup packagist Webhook:
https://packagist.org/api/update-package?username=jvelletti

api Token from Profile:
https://packagist.org/profile/

check:
https://intercept.typo3.com/admin/docs/deployments
https://packagist.org/packages/jvelletti/jv-jve_upgradewizard
https://extensions.typo3.org/extension/jve_upgradewizard/