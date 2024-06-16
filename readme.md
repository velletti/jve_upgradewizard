
# what does extension do
========================

Version 12.4.18 | 16.6.2024 

Tested with TYPO3 LTS 12.4.16 under PHP 8.3 \
Tested with TYPO3 LTS 12.4.6 under PHP 8.1 \
Tested with TYPO3 LTS 11.4.31 under PHP 7.4 \

works on following database tables / fields:  \

**sys_template** -> config and constants \
**pages** -> TSconfig \
**fe_users** -> TSconfig \
**fe_groups** -> TSconfig \
**be_users** -> TSconfig \
**be_groups** -> TSconfig 

You have more Database tables with TSconfig ? 
feel free to add an issue or a pull request.

Fixes also filenames and there Content on a local DEV mashine in a given folder
renames if needed extenion of file from  

    'ts', 'txt', 'text' , 't3' , 't3s' , 'tscript' , 'tsconfig' 
to 

    'typoscript' 


## Important NOTICE 

1. you should have a database copy! 
2. you should be aware in case of many pages or fe_users, it may be slow 
3. run this wizard on production system only if you have tested it locally 
4. you should have experience to cun typo3 console command 
5. you should work with any vcs like git 
6. make a copy of your template folder for easier testing 
7. replace -vv against -vvv to get more verbose output in follwing commands


    ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates -vv

    ./vendor/bin/typo3 jvelletti:updatefiles --path=/test -vv



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

you are asked to confirm changes with "yes" 


you can also start the script with a given path: but sill will have to enter "yes"  

(argument --force to skip confirmation is planned after sevaral tests that it is working correctly)

    ./vendor/bin/typo3 jvelletti:updatefiles --path=/vendor/jvelletti/jve-upgradewizard/Configuration/TypoScript/  -vv


the Console command searches for all files with definied Endings :

    CONST UNWANTED_EXTENSIONS = ['ts', 'txt', 'text' , 't3' , 't3s' , 'tscript' , 'tsconfig' ] ;

Renames these files if needed to ".typoscript" and fixes the content of this tiles in same way like database entries \
lines without "@import" and "INCLUDE_TYPOSCRIPT" are unchanged \

Fixes since version 12.4.18 also typocript lines :

    page.includeJSFooter.main = /typo3conf/ext/ ... main.js
    page.includeCss.application = /typo3conf/ext/ ... application.css
    shortcutIcon = /typo3conf/ext/ .. icon.ico
    logo = /typo3conf/ext/ .. logo.png ( .gif /  .jpg )


## Restrictions

1. does not fix entries in folder "/fileadmin" like  @import "/fileadmin/tscript.ts"
2. does not fix CSS/javascript files itself, if background images or font path is loading from public folder of extension


# Best pratices

as maybe 3 steps are needed: **renaming** the files in filesystem and changing the name of imported file in **database**, \
and finally a Clear TYPO§ Cache,  the website will not be available for some time. \
If you need to avoid this and have no other option, try the following steps  

1. create a Feature branch for the migration f.e.: "migration-feature" 
2. create a copy of the template file Folder outside of the doc root f.e. "backup-migration"
3. run updateData command on template Files:
    
   ./vendor/bin/typo3 jvelletti:updatefiles --path=/vendor/your-vendor/your-template-ext/Configuration/TypoScript/  -vv
    
4. filenames with old extensions will be renamed by the script command
5. push this to "migration-feature" Branch
5. create a 'final-feature' branch from result.
6. run Upgradewizard manuall via cli on local dev mashine to update local your database entries and do needed tests
   
    ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates -vv 
  
   on success: 

7. switch back to "migration-feature" Branch
8. copy OLD files (with wrong file extensions or old IMPORT_TYPOSCRIPT syntaxt)  from "backup-migration"
9. push this to "migration-feature" Branch (so BOTH file versions exist: the WANTED New ones and OLD used via database)
10. pull this to the webserver
11. run Upgradewizard manuall via cli
    
     ./vendor/bin/typo3 upgrade:run jveUpgradewizard_upgradeTemplates -vv

12. As in step 10 you have both versions of files, it is not import if upgrade takes time.
13. As with step 11 your database now shuld only use New renamed files you can cleanup.
14. to remove the OLD template files, switch to 'final-feature' branch



## Internal reminder for the extension maintainer:
To Update this extension in TER: \
change version Number to "x.y.z" in Documentation\ in Settings.cfg and Index.rst \
create Tag "x.y.z" \
git push --tags 

create new zip file: \
cd vendor/jvelletti/jve-upgradewizard \
git archive -o "jve_upgradewizard_x.y.z.zip" HEAD 

f.e.: \
git archive -o "jve_upgradewizard_12.4.11.zip" HEAD 


Upload ZIP File to https://extensions.typo3.org/my-extensions \
git push 

setup packagist Webhook: \
https://packagist.org/api/update-package?username=jvelletti 

api Token from Profile: \
https://packagist.org/profile/ 

check: \
https://intercept.typo3.com/admin/docs/deployments \
https://packagist.org/packages/jvelletti/jve_upgradewizard \
https://extensions.typo3.org/extension/jve_upgradewizard/ 