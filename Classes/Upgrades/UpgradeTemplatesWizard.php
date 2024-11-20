<?php
declare(strict_types=1);

namespace Jvelletti\JveUpgradewizard\Upgrades;

use Jvelletti\JveUpgradewizard\Utility\IncludeFilesUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\RepeatableInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('jveUpgradewizard_upgradeTemplates')]
final class UpgradeTemplatesWizard implements UpgradeWizardInterface , RepeatableInterface
{

    public int $verboseLevel = 0 ;
    public bool $error = false ;
    public ?array $currentTemplate = [] ;
    public ?array $currentPage = [] ;

    public array $TSconfigs = [
        'be_groups' => 'title' ,
        'be_users' => "username" ,
        'fe_groups' => 'title',
        'fe_users' => "name",
        'pages'  => 'title'
    ] ;

    public array $otherConfig = [
       'tx_gridelements_backend_layout' =>  ['field' => 'config' , 'extension' => '.typoscript' , 'title' => 'title' ] ,
       'backend_layout' =>  ['field' => 'config' , 'extension' => '.typoscript' , 'title' => 'title' ] ,
    ] ;

    /**
     * Return the speaking name of this wizard
     */
    public function getTitle(): string
    {
        return 'Fix known issues when upgrading to LTS 12';
    }

    /**
     * Return the description for this wizard
     */
    public function getDescription(): string
    {
        return 'Change import syntax of typoscript / TSconfig and rename files extension to .typoscript';
    }

    /**
     * Execute the update of configured database tables and columns
     *
     * Called when a wizard reports that an update is necessary
     * remove sys_registry entry
     */
    public function executeUpdate(): bool
    {
        $this->verboseLevel = 16 ;

        if ( isset( $_SERVER['argv'][3]) ) {
            if (  $_SERVER['argv'][3] =="--no" ) {
                $this->verboseLevel = 0 ;
            }
            if (  $_SERVER['argv'][3] =="-vv" ) {
                $this->verboseLevel = 64 ;
            }
            if (  $_SERVER['argv'][3] =="-vvv" ) {
                $this->verboseLevel = 128 ;
            }
        };

        $startTime = time()   ;

        $objects = $this->getTemplates( ) ;
        $totalChanged = 0 ;
        $totalObjCount = 0 ;

        $changed = 0 ;
        $objCount = 0 ;
        $this->error = false ;
        $this->debugOutput( 0 ,  "  -----  typoscript in templates config and constants ---- " ) ;
        while ( $currentTemplate = $objects->fetchAssociative() ) {
            $this->currentTemplate = $currentTemplate ;

            try {
                $changed = $changed + $this->checkTemplate( $this->currentTemplate ) ;
                $objCount ++ ;
            } catch ( \Exception $e )  {
                $this->debugOutput( 0 ,  $e->getFile() . " - Line: " .  $e->getLine() .   $e->getMessage() ) ;
                $this->error = true ;
            }
        }
        $this->debugOutput( 31 ,  "Changed  " . $changed . " of ". $objCount . " Templates in database "  ) ;

        $totalChanged += $changed ;
        $totalObjCount += $objCount ;

        foreach ( $this->TSconfigs as $TSconfigTable => $titleField ) {
            $this->debugOutput( 31 ,  " " ) ;
            $this->debugOutput( 31 ,  "  -----  TSconfig in " . $TSconfigTable . " ---- " ) ;
            $objects = $this->getRows( $TSconfigTable , $titleField) ;
            if ( $objects  === null ) {
                continue ;
            }
            $changed = 0 ;
            $objCount = 0 ;

            while ( $currentPage = $objects->fetchAssociative() ) {
                $this->currentPage = $currentPage ;
                try {
                    $changed = $changed + $this->checkRow( $this->currentPage , $TSconfigTable , $titleField , 'TSconfig'  , IncludeFilesUtility::TSCONFIG_EXTENSION) ;
                    $objCount ++ ;
                } catch ( \Exception $e )  {
                    $this->debugOutput( 0 ,  $e->getFile() . " - Line: " .  $e->getLine() .   $e->getMessage() ) ;
                    $this->error = true ;
                }
            }
            $totalChanged += $changed ;
            $totalObjCount += $objCount ;

            $this->debugOutput( 31 ,  "Changed  " . $changed . " of ". $objCount . " rows in Table: '" . $TSconfigTable . "' "  ) ;
        }

        foreach ( $this->otherConfig as $otherConfigTable => $otherConfigValues ) {
            $this->debugOutput( 31 ,  " " ) ;
            $this->debugOutput( 31 ,  "  -----  " .   $otherConfigValues['field']  . "  in " . $otherConfigTable . " ---- " ) ;
            $objects = $this->getRows( $otherConfigTable , $otherConfigValues['title'] , $otherConfigValues['field'] ) ;
            if ( $objects  === null ) {
                continue ;
            }
            $changed = 0 ;
            $objCount = 0 ;

            while ( $currentPage = $objects->fetchAssociative() ) {
                $this->currentPage = $currentPage ;
                try {
                    $changed = $changed + $this->checkRow( $this->currentPage , $otherConfigTable ,  $otherConfigValues['title'] , $otherConfigValues['field'] , $otherConfigValues['extension']) ;
                    $objCount ++ ;
                } catch ( \Exception $e )  {
                    $this->debugOutput( 0 ,  $e->getFile() . " - Line: " .  $e->getLine() .   $e->getMessage() ) ;
                    $this->error = true ;
                }
            }
            $totalChanged += $changed ;
            $totalObjCount += $objCount ;

            $this->debugOutput( 31 ,  "Changed  " . $changed . " of ". $objCount . " rows in Table: '" . $TSconfigTable . "' "  ) ;
        }


        $this->debugOutput( 0 ,  "" ) ;
        $this->debugOutput( 15 , '---------------------------------------------------' ) ;
        $this->debugOutput( 15 ,  "Total Changed  " . $totalChanged . " of ". $totalObjCount . " | Done in " . date( "H:i:s" , time() - $startTime ) . " (HH:mm:ss) "  ) ;
        $this->debugOutput( 0 ,  "---------------------------------------------------" ) ;
        $this->debugOutput( 0 ,  "" ) ;

        if ( !$this->error ) {
            $registry = GeneralUtility::makeInstance(Registry::class);
            $registry->set('installUpdate', 'Jvelletti\JveUpgradewizard\Upgrades\UpgradeTemplatesWizard', 1 );
        }
        return ! $this->error ;
    }





    private function checkTemplate($template ) {
        $configLines = GeneralUtility::trimExplode("\n" , $template['config'] ) ;
        $this->debugOutput( 31 ,  "Template " . $template['uid'] . " on pid: "  . $template['pid'] . " " .  $template['title']) ;
        $this->debugOutput( 31 ,  "" ) ;
        $configLinesNew= '' ;
        $constantsLinesNew = '' ;
        if ( $configLines ) {

            foreach ($configLines as $line ) {
                if( trim(  $line )  != '' ) {
                    $this->debugOutput( 123 ,  "Line: " . $line ) ;
                    $line = $this->fixINCLUDE( $line , IncludeFilesUtility::WANTED_EXTENSION) ;
                }

                $configLinesNew .= $line . "\n" ;
            }
        }

        $constantsLines = GeneralUtility::trimExplode("\n" , $template['constants'] ) ;
        if ( $constantsLines ) {
            foreach ($constantsLines as $line ) {
                $this->debugOutput( 123 ,  "Line: " . $line ) ;
                $line = $this->fixINCLUDE( $line ) ;

                $constantsLinesNew .= $line . "\n" ;
            }
        }
        $configLinesNew = trim( $configLinesNew , "\n") ;
        $constantsLinesNew = trim( $constantsLinesNew , "\n") ;

        if (  $template['config'] != $configLinesNew ||  $template['constants'] != $constantsLinesNew ) {
            $template['config'] = $configLinesNew ;
            $template['constants'] = $constantsLinesNew ;
            return $this->updateTemplate($template) ;
        }
        return 0 ;

    }



    private function checkRow($row , $TSconfigTable , $titleField , $valueField , $wantedExtension) {
        $configLines = GeneralUtility::trimExplode("\n" , $row[$valueField] ) ;
        $configLinesNew= '' ;
        if ( $configLines && count( $configLines) > 0 && trim($configLines[0]) != ''  ) {
            $this->debugOutput( 31 ,  $valueField . " " . $row['uid'] . " on pid: "  . $row['pid'] . " " .  $row[$titleField]) ;
            $this->debugOutput( 31 ,  "" ) ;


            foreach ($configLines as $line ) {
                if( trim(  $line )  != '' ) {
                    $this->debugOutput(123, "Line: " . $line);
                    $line = $this->fixINCLUDE($line , $wantedExtension) ;
                }

                $configLinesNew .= $line . "\n" ;
            }
        }
        $configLinesNew = trim( $configLinesNew , "\n") ;

        if (  $row[$valueField]  != $configLinesNew  ) {
            $row[$valueField] = $configLinesNew ;
            return $this->updateRow($row , $TSconfigTable , $valueField) ;
        }
        return 0 ;

    }


    private function fixINCLUDE($line , $wantedExtension= IncludeFilesUtility::WANTED_EXTENSION ) {
        $isComment = '' ;
        if ( str_starts_with(trim($line), "#") || str_starts_with(trim($line), "/")) {
            $isComment = '# ' ;
        }
        if ( strpos( strtoupper($line) , "INCLUDE_TYPOSCRIPT") > 0
            ||  strpos( strtolower( $line ), "@import") > -1
            ||  str_ends_with( trim(strtolower( $line )) , ".js")
            ||  str_ends_with( trim(strtolower( $line )) , ".css")
           )
        {
            $line = trim( $line) ;
            $this->debugOutput( 123 ,  "has INCLUDE_TYPOSCRIPT or  @import or .js or .css " ) ;
            // replace any double Quotes   "   with single  ' 
            $line = str_replace('"', "'", $line);
        } else {
            return $line ;
        }
        $temp = GeneralUtility::trimExplode( "'" , $line ) ;
        $result =  $line ;
        if (count($temp) > 1 ) {

            $file = str_replace( ["/typo3conf/ext/" , "typo3conf/ext/","/EXT:" , "FILE:EXT:" , "FILE: EXT:"] , ["EXT:", "EXT:", "EXT:", "EXT:" , "EXT:" ] , $temp[1] ) ;

            $file = ltrim( $file , "\\") ;
            
            foreach ( IncludeFilesUtility::UNWANTED_EXTENSIONS as $unwanted ) {
                $from[] = "." . $unwanted . "'" ;
            }
            
            // from and to must END with '  to avoid replacing .tsconfig to .tsconfigconfig 
            
            $to = array_fill( 0 , count($from) ,  $wantedExtension. "'" ) ;

            $fileNew = str_replace($from , $to , $file ) ;
          
            $result = $isComment . "@import '" . $fileNew . "'" ;
            if ( $fileNew != $file ) {
                $this->debugOutput( 0 ,  "MAYBE You need to rename File ENDING to " . $wantedExtension . " of :\n " . $fileNew  . " \n " ) ;
            }
            if ( strpos( $result , "fileadmin/") > 0 ) {
                if (  $isComment === '' ) {
                    $this->error = true ;
                    $this->debugOutput( 0 ,  " WARNING !! Typoscript Files in /fileadmin does not work anymore!!! :\n " . $fileNew  . " \n " ) ;
                } else {
                    $this->debugOutput( 125 ,  " one unused include of Typoscript Files in /fileadmin :\n " . $fileNew  . " \n " ) ;
                }
            }
        }
        return $result ;
    }

    private function getTemplates() {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        $queryBuilder = $connectionPool->getConnectionForTable('sys_template')->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll()->add( GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder->select('uid', 'pid' , 'title','constants' , 'config' ) ->from('sys_template') ;
        return $queryBuilder->executeQuery() ;
    }

    private function updateTemplate( $data ) {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        $queryBuilder = $connectionPool->getConnectionForTable('sys_template')->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll()->add( GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder->update('sys_template')
            ->set( "config" ,  $data['config'])
            ->set( "constants" ,  $data['constants'])
        ;

        $expr = $queryBuilder->expr();
        $queryBuilder->where( $expr->eq('uid', $data['uid'] )) ;
        return $queryBuilder->executeStatement() ;
    }


    private function getRows( $table , $title , $field= 'TSconfig') {
        try {

            /** @var ConnectionPool $connectionPool */
            $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
            $queryBuilder = $connectionPool->getConnectionForTable($table)->createQueryBuilder();
            $expr = $queryBuilder->expr();
            $queryBuilder->getRestrictions()->removeAll()->add( GeneralUtility::makeInstance(DeletedRestriction::class));
            $queryBuilder->select('uid', 'pid' , $title ,$field ) ->from($table)
               ->where( $expr->neq($field , $queryBuilder->createNamedParameter('' ) ))
               ->andWhere( $expr->isNotNull($field)) ;

            return $queryBuilder->executeQuery() ;


        } catch ( \Exception $e ) {
            // not all tables may exist in all instances f.e. gridelements backend Layouts
           return null ;
        }
    }

    private function updateRow( $data , $table  , $field= 'TSconfig') {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( "TYPO3\\CMS\\Core\\Database\\ConnectionPool");
        $queryBuilder = $connectionPool->getConnectionForTable($table)->createQueryBuilder();
        $queryBuilder->getRestrictions()->removeAll()->add( GeneralUtility::makeInstance(DeletedRestriction::class));
        $expr = $queryBuilder->expr();
        $queryBuilder->update($table)
            ->set( $field ,  $data[$field])
            ->where( $expr->eq('uid', $data['uid'] )) ;

        return $queryBuilder->executeStatement() ;
    }



    private function debugOutput( $minVerbosity , $text ) {
        if ( $this->verboseLevel > $minVerbosity  ) {
            echo "\n" . $text ;
        }
    }


    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {
        return true;
    }

    /**
     * Returns an array of class names of prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return ['database up-to-date' , 'reference index updated'] ;
    }


    /**
     * Return the identifier for this wizard
     * This should be the same string as used in the ext_localconf class registration
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'jveUpgradewizard_upgradeTemplates' ;
    }
}