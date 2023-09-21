
# what does extension do
========================

Tested with TYPO3 LTS 12.4.6 \
works on following fields:  \

**sys_template** -> config and constants \ 
**pages** -> TSconfig \
**fe_users** -> TSconfig \
**fe_groups** -> TSconfig \
**be_users** -> TSconfig \
**be_groups** -> TSconfig \
\
Fixes filenames and there Content on a local DEV mashine \


## Important NOTICE 

1. you should have a database copy! \
2. you should be aware in case of many pages or fe_users, it may be slow \
3. run this wizard on production system only if you have tested it locally \
4. you should have experience to cun typo3 console command \
5. you should work with any vcs like git \
6. make a copy of your template folder for easier testing \


    ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates -vv


## Fixes file ending .ts .txt and .text etc to .typoscript in database with warning

    CONST UNWANTED_EXTENSIONS = ['ts', 'txt', 'text' , 't3' , 't3s' , 'tscript' , 'tsconfig' ] ;

    @import 'EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.ts'
    @import 'EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.txt'
    @import 'EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.text'
    

## Fixes  wrong "EXT:" or missing "EXT: syntax

not required, but helps to make extension to fiddle result easier 


    @import "FILE:EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript"
    @import "/typo3conf/ext/jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript"


## Fixes  INCLUDE_TYPOSCRIPT src=

    <INCLUDE_TYPOSCRIPT src="/typo3conf/ext/jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript"> 
    <INCLUDE_TYPOSCRIPT src='/typo3conf/ext/jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript'>


## Changes double quote " char to single quote '  

not required, but helps to make extension to fiddle result easier

     @import "EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript"

## Result in all cases:

    @import 'EXT:jve_template/Configuration/TypoScript/TSConfig/TSConfig.typoscript'

fixes also lines starting with # but keep coment status intakt.


## Warns if file in fileadmin and not in any EXT Folder:
   
     @import "/fileadmin/template/TSConfig/TSConfig.typoscript" 



## Fixes files In a given template Folder on a local test systems

    ./vendor/bin/typo3 jvelletti:updatefiles  -vv
    
enter path to your Template folder f.e. :

    /vendor/jvelletti/jve-upgradewizard/Configuration/TypoScript/

this extension comes along with some test files there. check the content to see some common error / outdated syntax  \

you are asked to confirm changes with "yes" \


you can also start the script with a given path: but sill will have to enter "yes"  \

(argument --force to skip confirmation is planned after sevaral tests that it is working correctly)

    ./vendor/bin/typo3 jvelletti:updatefiles --path=/vendor/jvelletti/jve-upgradewizard/Configuration/TypoScript/  -vv


then Console command searches for all files with definied Endings :

    CONST UNWANTED_EXTENSIONS = ['ts', 'txt', 'text' , 't3' , 't3s' , 'tscript' , 'tsconfig' ] ;

Renames these files if needed to ".typoscript" and fixes the content of this tiles in same way like database \
lines without "@import" and "INCLUDE_TYPOSCRIPT" are unchanged \


## Restrictions

1.  does not fix entries in fileadmin like  @import "/fileadmin/tscript.ts"


## Internal reminder for the extension maintainer:
To Update this extension in TER: \
change version Number to "x.y.z" in Documentation\ in Settings.cfg and Index.rst \
create Tag "x.y.z" \
git push --tags \

create new zip file: \
cd typo3conf/ext/jve_upgradewizard \
git archive -o "${PWD##*/}_x.y.z.zip" HEAD \

f.e.: \
git archive -o "${PWD##*/}_12.4.5.zip" HEAD \


Upload ZIP File to https://extensions.typo3.org/my-extensions \
git push \

setup packagist Webhook: \
https://packagist.org/api/update-package?username=jvelletti \

api Token from Profile: \
https://packagist.org/profile/ \

check: \
https://intercept.typo3.com/admin/docs/deployments \
https://packagist.org/packages/jvelletti/jve_upgradewizard \
https://extensions.typo3.org/extension/jve_upgradewizard/ \