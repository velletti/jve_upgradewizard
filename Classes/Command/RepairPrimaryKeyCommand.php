<?php
namespace Jvelletti\JveUpgradewizard\Command;

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
class RepairPrimaryKeyCommand extends Command {



    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Fix errors on primary keys in given MM database table')
            ->setHelp('Get list of Options: .' . LF . 'use the --help option.')
            ->addOption(
                'table',
                't',
                InputOption::VALUE_REQUIRED,
                "MM table that contains  uid_local and uid_foreign and throws Duplicate entry for key PRIMARY "
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

        $table = '' ;
        if ($input->getOption('table')) {
            $table = trim($input->getOption('table') )  ;
        } else {
            $io->writeln(  "\nOption table as Argument is mandatory \n "  );
            return 0;
        }
        try {
            $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)
               ->getQueryBuilderForTable($table);

            $duplicates = $queryBuilder
               ->select('uid_local', 'uid_foreign')
               ->from($table)
               ->groupBy('uid_local', 'uid_foreign')
               ->having('COUNT(*) > 1')
               ->execute()
               ->fetchAll();
            $total = count($duplicates) ;
            if ( $total) {
                $io->writeln("\nFound duplicate entries in table " . $table . ".\n");
                $progress = $io->createProgressBar($total) ;
                $progress->start();
                  foreach ($duplicates as $duplicate) {
                     $progress->advance();
                     $queryBuilder
                           ->delete($table)
                           ->where(
                              $queryBuilder->expr()->eq('uid_local', $duplicate['uid_local']),
                              $queryBuilder->expr()->eq('uid_foreign', $duplicate['uid_foreign'])
                           )
                           ->execute();
                  }
                  $progress->finish();
                  $io->writeln("\n\nDeleted " . $total . " duplicate entries in table " . $table . ".\n");
            } else {
                $io->writeln("\nNo duplicate entries found in table " . $table . ".\n");
            }
        } catch (Exception $e) {
            $io->writeln("\nTable " . $table . " does not exist.\n");
            return 0;
        }







        $io->writeln(  " " );
        return 0 ;
    }



}
