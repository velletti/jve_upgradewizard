<?php
declare(strict_types=1);

namespace Jvelletti\JveUpgradewizard\Utility;

use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class IncludeFilesUtility
{

    CONST UNWANTED_EXTENSIONS = ['ts', 'txt', 'text' , 't3' , 't3s' , 'tscript' , 'tsconfig' ] ;
    CONST WANTED_EXTENSION = 'typoscript' ;
    CONST UNWANTED_PATH = ["/typo3conf/ext/" , "typo3conf/ext/","/EXT:" , "FILE: EXT:" , "FILE:EXT:"] ;

    public static function FixFileContent($filePath , ?SymfonyStyle $io=null, string $basePath='' ) {

        if ( file_exists( $filePath )) {
            $fileContent = file_get_contents( $filePath ) ;
            if( $fileContent ) {
                $repairedContent = self::checkContent( $fileContent , $io,  $basePath) ;
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
                    $line = self::fixIncludeMain( $line ,$io , $basePath) ;
                }
                $configLinesNew .= $line . "\n" ;
            }
        }
        return rtrim( $configLinesNew , "\n") ;
    }

    public static function fixIncludeMain($line , ?SymfonyStyle $io=null, string $basePath='' ) {


        if ( strpos( strtoupper($line) , "INCLUDE_TYPOSCRIPT") > 0
            ||  strpos( strtolower( $line ), "@import") > -1   )
        {
            return self::fixINCLUDE($line , $io,  $basePath) ;
        } elseif(
            str_ends_with( trim(strtolower( $line )) , ".js")
            ||
            str_ends_with( trim(strtolower( $line )) , ".css")
            ||
            str_ends_with( trim(strtolower( $line )) , ".png")
            ||
            str_ends_with( trim(strtolower( $line )) , ".ico")
            ||
            str_ends_with( trim(strtolower( $line )) , ".gif")
            ||
            str_ends_with( trim(strtolower( $line )) , ".jpg")
        )
        {
            return self::fixCssOrJsOrImg($line , $io,  $basePath) ;
        } else {

           return $line ;
        }
        return $result ;
    }

    public static function fixINCLUDE($line , ?SymfonyStyle $io=null, string $basePath='' ) {
        $isComment = '' ;
        if ( str_starts_with(trim($line), "#") || str_starts_with(trim($line), "/")) {
            $isComment = '# ' ;
        }

        $line = trim( $line) ;
        $line = str_replace('"', "'", $line);

        $temp = GeneralUtility::trimExplode( "'" , $line ) ;

        $result =  $line ;
        if (count($temp) > 1 ) {
            $pathTo = array_fill(0 , count(IncludeFilesUtility::UNWANTED_PATH) , "EXT:") ;
            $file = str_replace( IncludeFilesUtility::UNWANTED_PATH , $pathTo , $temp[1] ) ;

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

    public static function fixCssOrJsOrImg($line , ?SymfonyStyle $io=null, string $basePath='' ) {
        $isComment = '' ;
        if ( str_starts_with(trim($line), "#") || str_starts_with(trim($line), "/")) {
            $isComment = '# ' ;
        }

        $line = trim( $line) ;
        $line = str_replace('"', "'", $line);

        $temp = GeneralUtility::trimExplode( "=" , $line ) ;
        $result =  $line ;
        if (count($temp) > 1 ) {
            $io->writeln(   " repair :\n " . $line  . " \n " ) ;
            $pathTo = array_fill(0 , count(IncludeFilesUtility::UNWANTED_PATH) , "EXT:") ;
            $file = str_replace( IncludeFilesUtility::UNWANTED_PATH , $pathTo , $temp[1] ) ;

            $file = ltrim( $file , "\\") ;

            $result = $isComment . $temp[0]  . " = ". $file . "" ;
            if ( strpos( $result , "fileadmin/") > 0 ) {
                if ( $io ) {
                    if (  $isComment === '' ) {
                        $io->writeln(   " WARNING !! Typoscript Files in /fileadmin does not work anymore!!! :\n " . $fileNew  . " \n " ) ;
                    } else {
                        $io->writeln(   " One unused include of Typoscript Files in /fileadmin :\n " . $fileNew  . " \n " ) ;
                    }
                }
            }
            $io->writeln(   " to :\n " . $result  . " \n " ) ;
        }
        return $result ;
    }
}