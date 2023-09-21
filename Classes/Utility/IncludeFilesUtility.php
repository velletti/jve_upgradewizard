<?php
declare(strict_types=1);

namespace Jvelletti\JveUpgradewizard\Utility;

use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class IncludeFilesUtility
{

    CONST UNWANTED_EXTENSIONS = ['ts', 'txt', 'text' , 't3' , 't3s' , 'tscript' , 'tsconfig' ] ;
    CONST WANTED_EXTENSION = 'typoscript' ;

    public static function FixFileContent($filePath , ?SymfonyStyle $io=null, string $basePath='' ) {

        if ( file_exists( $filePath )) {
            $fileContent = file_get_contents( $filePath ) ;
            if( $fileContent ) {
                $repairedContent = self::checkContent( $fileContent , $io=null,  $basePath) ;
                if ( $fileContent != $repairedContent ) {
                    if( file_put_contents( $filePath , $repairedContent )) {
                        return 1 ;
                    }
                }
            }
        }
        return 0 ;
    }


    public static function checkContent($content ,?SymfonyStyle $io=null, string $basePath='' ) {
        $configLines = GeneralUtility::trimExplode("\n" , $content ) ;
        $configLinesNew= '' ;
        if ( $configLines ) {
            foreach ($configLines as $line ) {
                if( trim(  $line )  != '' ) {
                    $line = self::fixINCLUDE( $line ,$io , $basePath) ;
                }
                $configLinesNew .= $line . "\n" ;
            }
        }
        return rtrim( $configLinesNew , "\n") ;
    }

    public static function fixINCLUDE($line , ?SymfonyStyle $io=null, string $basePath='' ) {
        $isComment = '' ;
        if ( str_starts_with(trim($line), "#") || str_starts_with(trim($line), "/")) {
            $isComment = '# ' ;
        }
        if ( strpos( strtoupper($line) , "INCLUDE_TYPOSCRIPT") > 0 ||  strpos( strtolower( $line ), "@import") > -1 ) {
            $line = trim( $line) ;
            $line = str_replace('"', "'", $line);
        } else {
           return $line ;
        }
        $temp = GeneralUtility::trimExplode( "'" , $line ) ;
        $result =  $line ;
        if (count($temp) > 1 ) {
            $file = str_replace( ["/typo3conf/ext/" , "typo3conf/ext/","/EXT:" , "FILE:EXT:"] , ["EXT:", "EXT:", "EXT:", "EXT:" ] , $temp[1] ) ;

            $file = ltrim( $file , "\\") ;
            foreach ( IncludeFilesUtility::UNWANTED_EXTENSIONS as $unwanted ) {
                $from[] = "." . $unwanted ;
            }
            $to = array_fill( 0 , count($from) , "." . IncludeFilesUtility::WANTED_EXTENSION) ;

            $fileNew = str_replace($from , $to , $file ) ;
            $result = $isComment . "@import '" . $fileNew . "'" ;
            if ( strpos( $result , "fileadmin/") > 0 ) {
                if ( $io ) {
                    if (  $isComment === '' ) {
                        $io->writeln(   " WARNING !! Typoscript Files in /fileadmin does not work anymore!!! :\n " . $fileNew  . " \n " ) ;
                    } else {
                        $io->writeln(   " One unused include of Typoscript Files in /fileadmin :\n " . $fileNew  . " \n " ) ;
                    }
                }
            }
        }
        return $result ;
    }
}