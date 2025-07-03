<?php
namespace Jvelletti\JveUpgradewizard\Command;

use Jvelletti\JveUpgradewizard\Utility\IncludeFilesUtility;
use Symfony\Component\Console\Input\InputOption;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateFilesCommand
 * @author Jörg Velletti <typo3@velletti.de>
 * @package JVE\JvEvents\Command
 */
class UpdateFilesCommand extends Command {



    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Fix included template scripts or TS config files on local dev in given folder')
            ->setHelp('Get list of Options: .' . LF . 'use the --help option.')
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_OPTIONAL,
                "local path to a template folder, that should be updated, starting from project root. \n
                --path=/vendor/your-vendor/your-extension/Configuration/TypoScript/\n
                f.e.: --path=/vendor/jvelletti/jve-upgradewizard/Configuration/TypoScript/
                \n"
            ) ;

    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        // Check if it's not running from the command line
        if (PHP_SAPI !== 'cli') {
            $io->writeln(  "\nThis script must be run from the command-line interface.\n "  );
            return 0;
        }
        $basePath = rtrim( Environment::getProjectPath() , "/" ) . "/";



        $path = '' ;
        if ($input->getOption('path')) {
            $path = trim( trim($input->getOption('path') ), "/" ) ;
        }
        $path = str_replace( $basePath , "" , $path) ;
        if (!is_dir( $basePath . $path)) {
            $io->writeln(  "\nThe Given Path is not accessable: "  .$basePath  . $path );
            return 0;
        }

        if ( $path == '' ) {
            $io->writeln(  "\n................................... "  );
            $io->writeln(  "Enter the path to TypoScript folder "  );
            $io->writeln(  "Must be a subfolder of: "  .$basePath   );
            $handle = fopen ("php://stdin","r");

            // remove spaces and "/" at beginning and end of input path
            $path = trim( trim(fgets($handle) ), "/" ) ;
            fclose($handle);
        }
        if (!is_dir( $basePath . $path)) {
            $io->writeln(  "\nThe Given Path is not accessable: "  .$basePath . $path );
            return 0;
        }
        if( $io->getVerbosity() > 32 ) {
            $io->writeln("The Given Path is: " . $basePath . $path);
        }

        $files = self::getFiles( $path , $io, $basePath   ) ;
        $io->writeln(  " " );

        $total = count($files ) ;
        $renamedFiles = 0 ;
        $changedFiles = 0 ;
        if ( $files && count($files ) > 0  ) {
            $io->writeln(  " Files: " . $total . "\n");

            $io->writeln(  "\n................................... "  );
            $io->writeln(  "Are you shure you want to fix the  TypoScript folder?"  );
            if ( $total > 3 ) {
                $io->writeln(  "WARNING: Found " . $total . " Files to work on !!! "  );
            }
            $io->writeln(  "yes / [no]  "  );
            $handle = fopen ("php://stdin","r");

            // remove spaces and "/" at beginning and end of input path
            $confirmed  =  strtolower( trim(fgets($handle) ) )  == "yes" ;
            fclose($handle);

            if ( !$confirmed ) {
                $io->writeln(  "\n................................... "  );
                $io->writeln(  "stopped be answer was not: yes"  );
                $io->writeln(  "\n................................... "  );
                return 0;
            }
            $progress = $io->createProgressBar($total) ;

            foreach ( $files as $file ) {
                if( IncludeFilesUtility::FixFileContent( $file , $io, $basePath  )) {
                    $changedFiles ++ ;
                    $io->writeln(   "\n Repaired File: " . str_replace( $basePath , "/" , $file )  . "" ) ;
                }

                $renamedFiles += self::renameFile($file , $io, $basePath );

                $progress->advance();
            }
            // @extensionScannerIgnoreLine
            $progress->finish();
        }




        $io->writeln(  " " );
        return 0 ;
    }


    public static function getFiles(string $templatePath , SymfonyStyle $io, string $basePath  ): array
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath . $templatePath ));
        $files = [];

        foreach ($rii as $file) {


            if (!$file->isDir()) {

                $info = pathinfo($file);
                $extension = isset($info['extension']) ? strtolower($info['extension']) : '';

                // Check if file has one of the specified extensions
                if (in_array($extension, IncludeFilesUtility::UNWANTED_EXTENSIONS ) || $extension == strtolower(IncludeFilesUtility::WANTED_EXTENSION) ) {
                    $files[] = $file->getPathname();
                    if( $io->getVerbosity() > 32 ) {
                        $io->writeln(" Add to queue: " .str_replace( $basePath , "/" ,  $file->getPathname()) ) ;
                    }
                } else {
                    if( $io->getVerbosity() > 32 ) {
                        $io->writeln(" Extension needs no change: " .  str_replace( $basePath , "/" ,  $file->getPathname()) );
                    }
                }
            } else {
                if( $io->getVerbosity() > 32 ) {
                    if( substr( $file , -2 , 2 ) != "..") {
                        $io->writeln(" Dir: " . str_replace( $basePath , "/" ,  $file->getPathname()) );
                    }
                }
            }
        }

        return $files;
    }

    /**
     * rename typoscript files with Old Extension names like .ts  to .typoscript
     *
     * @param string $filePath
     * @return int
     */
    public static function renameFile(string $filePath , SymfonyStyle $io, string $basePath): int
    {
        // Check if file exists
        if (!file_exists( $filePath)) {
            return 0 ;
        }

        $info = pathinfo($filePath);
        $extension = isset($info['extension']) ? strtolower($info['extension']) : '';

        // Check if file has one of the specified extensions
        if (in_array($extension, IncludeFilesUtility::UNWANTED_EXTENSIONS )) {
            $newFilePath = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.' . IncludeFilesUtility::WANTED_EXTENSION ;
            $newFilePath = str_replace( ".." , "." , $newFilePath ) ;
            if (strpos(strtolower($newFilePath) , "tsconfig")) {
                $newFilePath = str_replace( IncludeFilesUtility::WANTED_EXTENSION , IncludeFilesUtility::TSCONFIG_EXTENSION , $newFilePath ) ;
            }
            if ( $newFilePath !== $filePath ) {

                if (rename($filePath, $newFilePath)) {
                    if( $io->getVerbosity() > 16 ) {
                        $io->writeln("\n Renamed to: " . str_replace( $basePath , "/" ,  $newFilePath ) );
                    }
                    return 1 ;
                } else {
                    $io->writeln("\n Failed to rename file: " . str_replace( $basePath , "/" ,  $filePath ) );
                    return 0 ;
                }
            }
        }
        return 0 ;
    }

}
