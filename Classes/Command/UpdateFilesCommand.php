<?php
namespace Allplan\AllplanContent\Command;

use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Input\InputOption;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateDataCommand
 * @author Jörg Velletti <typo3@velletti.de>
 * @package JVE\JvEvents\Command
 */
class UpdateDataCommand extends Command {

    /**
     * @var array
     */
    private $allowedTables = [] ;

    /**
     * @var array
     */
    private $extConf = [] ;



    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Updates the Data like Counter of the Configured database models')
            ->setHelp('Get list of Options: .' . LF . 'use the --help option.')
            ->addOption(
                'model',
                m,
                InputOption::VALUE_OPTIONAL,
                'enter name of Model name in lowercase, that should be updated. default is: tag'
            )
            ->addOption(
                'task',
                t,
                InputOption::VALUE_OPTIONAL,
                'Add task type : f.e. repairTagfor8644 . default is: count'
            )
            ->addOption(
                'rows',
                'r',
                InputOption::VALUE_OPTIONAL,
                'number of rows to be updates must be integer' ) ;



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
        /** @var  ExtensionConfiguration $extConf */
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class) ;
        try {
            $this->extConf = $extConf->get('allplan_content');
        } catch (Exception $e) {
            $this->extConf = [] ;
        }


        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $maxRows = 999999999999 ;
        if ($input->getOption('rows') ) {
            $maxRows = (int)$input->getOption('rows') ;
            $maxRows = min( $maxRows , 999999999999) ;
            $maxRows = max( $maxRows , 1) ;
            $io->writeln('max Rows to be updated was set to '. $maxRows );
        }

        $model = "tag" ;
        $task = "count" ;
        $table = "missing" ;
        if ($input->getOption('model')) {
            $model =  $input->getOption('model');

        }
        if ($input->getOption('task')) {
            $task =  $input->getOption('task');

        }

        switch ($task) {

            // this is default task
            case "count";
                switch ($model) {
                    case "tag";
                        $table = "tx_allplancontent_domain_model_" . $model ;
                        $this->updateTagsCommand($io , $table, $maxRows  ) ;
                        return 0 ;

                    case "manufactor";
                        $table = "tx_allplancontent_domain_model_" . $model ;
                        return $this->updateManufactorsCommand($io , $table, $maxRows  ) ;

                }
                $io->writeln('Entered Model name => table : ' . $table . ' is invalid. Only tableNames that are configured are allowed');
                $io->writeln(var_export( $this->allowedTables , true )) ;
                return 1 ;

            case "repairTagfor8644";
                return  $this->repairTagforUidCat($io , $maxRows , 8644 , 37 , 36 ) > 0 ? 0 : 1 ;

            case "repairTagfor8645";
                return  $this->repairTagforUidCat($io , $maxRows , 8645 , 36 , 37 ) > 0 ? 0 : 1 ;

        }
        $io->writeln('Entered task : ' . $task . ' is invalid. only default count or repairTagfor8644 works ');
        return 1 ; // error
    }


    /**
     * @param SymfonyStyle $io
     * @param $table
     * @param $maxRows
     */
    public function updateTagsCommand(SymfonyStyle $io , $table , $maxRows  ){
        $progress = false ;
        if( !$table ) { return ; }
		$defaultLand = $this->getExpressionBuilder($table)->in("sys_language_uid" , "0,-1" ) ;
		$onlyOneLang = $this->getExpressionBuilder($table)->in("l10n_parent" , "0" ) ;
		$rows = $this->getQueryBuilder($table)->select("*")->from($table)->where($defaultLand)->orWhere($onlyOneLang)->execute() ;

        $total = $rows->rowCount()  ;
        if( $total < $maxRows ) {
            $maxRows = $total ;

        }
        if( $io->getVerbosity() > 16 ) {
            $io->writeln( $table . " - rowCount: " . $total );
            $progress = $io->createProgressBar($total) ;
        }
        $i = 0 ;
        $debugOutput = "" ;
        $cfQb = $this->getQueryBuilder('tx_allplancontent_domain_model_contentfile') ;
        $cfExpr = $this->getExpressionBuilder('tx_allplancontent_domain_model_contentfile') ;

        while ( $row = $rows->fetchAssociative()) {

            $result = $cfQb->selectLiteral("count(uid)")->from('tx_allplancontent_domain_model_contentfile')
                ->where($cfExpr->inSet("tag" , $row["uid"]))
                ->andWhere($cfExpr->in("sys_language_uid" , "0,-1"))
                ->execute() ;
            $count = $result->fetchOne() ;
            $this->setCounter( $table , $row["uid"] ,  "counter" , $count ) ;
            $i++ ;

            if( $io->getVerbosity()  > 128 ) {
                $debugOutput .= " | Tag: " . $row['uid'] . " " . $row['label'] . " result: " . $count ;
            }
            if( $io->getVerbosity() > 16 ) {
                $progress->advance();
            }

            if( $i >= $maxRows ) {
                break ;
            }
            $pids[] = $row['pid'] ;

        }
        $pids = array_unique( $pids );
        if( $io->getVerbosity() > 16 ) {
            // @extensionScannerIgnoreLine
            $progress->finish();

            if( $io->getVerbosity()  > 128 ) {
                $io->writeln($debugOutput);
            }
            $io->writeln(" ") ;
            $io->writeln("Finished ( " . $table . " updated: "   . $i . "/" . $total .  " records) on PID: " . implode("," , $pids));
        }
	}

    /**
     * @param SymfonyStyle $io
     * @param $table
     * @param $maxRows
     */
    public function updateManufactorsCommand(SymfonyStyle $io , $table , $maxRows  ){
        $progress = false ;
        if( !$table ) { return ; }
        $defaultLand = $this->getExpressionBuilder($table)->in("sys_language_uid" , "0,-1" ) ;
        $onlyOneLang = $this->getExpressionBuilder($table)->in("l10n_parent" , "0" ) ;
        $rows = $this->getQueryBuilder($table)->select("*")->from($table)->where($defaultLand)->orWhere($onlyOneLang)->execute() ;

        $total = $rows->rowCount()  ;
        if ($total < 1) {
            $io->writeln("WARNING  - " . $table . " no rows found "  );
            return 1 ;
        }
        if( $total < $maxRows ) {
            $maxRows = $total ;

        }
        if( $io->getVerbosity() > 16 ) {
            $io->writeln( $table . " - rowCount: " . $total );
            $progress = $io->createProgressBar($total) ;
        }
        $i = 0 ;
        $debugOutput = "" ;
        $cfQb = $this->getQueryBuilder('tx_allplancontent_domain_model_contentfile') ;
        $cfExpr = $this->getExpressionBuilder('tx_allplancontent_domain_model_contentfile') ;

        $cfQbCond = $cfQb->selectLiteral("count(cf.uid) count")->from('tx_allplancontent_domain_model_contentfile' , "cf")
            ->leftJoin( "cf" ,"tx_allplancontent_domain_model_contentgroup" , "cg" , $cfExpr->eq('cg.uid' , 'cf.groupuid' ))

            ->where($cfExpr->in("cf.sys_language_uid" , "0,-1")) ;


        while ( $row = $rows->fetchAssociative()) {
            $cfQbCond2 = $cfQbCond ;
            $count = $cfQbCond2->andwhere($cfExpr->eq("cg.manufactoruid" , $row["uid"]))->execute()->fetchAssociative() ;

            $this->setCounter( $table , $row["uid"] ,  "counter" , $count['count']) ;
            $i++ ;

            if( $io->getVerbosity()  > 128 ) {
                $debugOutput .= " | Manufactor: " . $row['uid'] . " " . $row['label'] . " result: " . $count['count'] ;
            }
            if( $io->getVerbosity() > 16 ) {
                $progress->advance();
            }

            if( $i >= $maxRows ) {
                break ;
            }
            $pids[] = $row['pid'] ;

        }
        $pids = $pids ? $pids : [] ;
        $pids = array_unique( $pids );
        if( $io->getVerbosity() > 16 ) {
            // @extensionScannerIgnoreLine
            $progress->finish();

            if( $io->getVerbosity()  > 128 ) {
                $io->writeln($debugOutput);
            }
            $io->writeln(" ") ;
            $io->writeln("Finished ( " . $table . " updated: "   . $i . "/" . $total .  " records) on PID: " . implode("," , $pids));
        }
        return 0 ;
    }

    /**
     * @param SymfonyStyle $io
     * @param int $maxRows  Max Rows to work on .. default all
     * @param int $uidCat   8644 = Texture 512 * 512
     * @param int $removeTag  37 = Tag - medium
     * @param int $addTag     36 = Tag- large
     * @return int
     * @throws DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    private function repairTagforUidCat(SymfonyStyle $io , int $maxRows , int $uidCat, int $removeTag , int $addTag ){
        $tableMM = 'tx_allplancontent_contentfile_category_mm' ;
        $uidCatCond  = $this->getExpressionBuilder($tableMM)->eq("uid_foreign" , $uidCat ) ;

        $rows = $this->getQueryBuilder($tableMM)->select("uid_local ")->from($tableMM)
            ->where($uidCatCond) ;
        if ( $maxRows > 0 ) {
            $rows->setMaxResults($maxRows) ;
        }
        $rows = $rows->execute() ;

        $total = $rows->rowCount()  ;
        if ($total < 1) {
            $io->writeln("WARNING  - " . $tableMM . " no rows found "  );
            return 1 ;
        }
        $progress = false ;
        if( $io->getVerbosity() > 16 ) {
            $io->writeln( $tableMM . " - rowCount: " . $total );
            $io->writeln( '' );
            $progress = $io->createProgressBar($total) ;
        }
        $i = 0 ;
        while ( $row = $rows->fetchAssociative()) {

            $table = 'tx_allplancontent_domain_model_contentfile' ;
            $uidLocalCond  = $this->getExpressionBuilder($table)->eq("uid" , $row['uid_local'] ) ;
            $contentTag = $this->getQueryBuilder($table)->select("tag ")->from($table)->where($uidLocalCond)->setMaxResults(1)->execute()->fetchAssociative()  ;

            // now remove 37( $removeTag ) and add 36 ( $addTag )
            $oldTags = GeneralUtility::trimExplode("," , $contentTag['tag'] ) ;
            $newTags = [] ;
            foreach ($oldTags as $value ) {
                if ( $value != $addTag &&  $value != $removeTag ) {
                    $newTags[] = $value ;
                }
            }
            $newTags[] = $addTag ;

            $newTag = implode("," , $newTags ) ;
            if( $contentTag['tag'] != $newTag ) {
                $i++ ;
                $this->getQueryBuilder($table)->update($table)->set('tag' , $newTag )->where($uidLocalCond)->execute() ;
            }
            if( $io->getVerbosity() > 16 ) {
                $progress->advance();
            }
        }
        if( $io->getVerbosity() > 16 ) {
            $progress->finish();
            $io->writeln( '' );
        }
        $io->writeln( '' );
        $io->writeln("Updated " .   $i . " entries.") ;
        $io->writeln( '' );
        return $i ;
    }

    /**
     * @param string $table
     * @return QueryBuilder
     */
	private function getQueryBuilder(string $table): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */

        return $connectionPool->getConnectionForTable($table)->createQueryBuilder();
	}

    /**
     * @param string $table
     * @return ExpressionBuilder
     */
    private function getExpressionBuilder(string $table): ExpressionBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance( ConnectionPool::class);
        /** @var QueryBuilder $queryBuilder */

        return $connectionPool->getConnectionForTable($table)->getExpressionBuilder();
    }


    /**
     * @param string $table the Table name that should be updated
     * @param int $uid The UID or Parent Uid
     * @param string $counterField Name of the Counter field
     * @param int $counter the new Value
     */
    private function setCounter(string $table, int $uid, string $counterField, int $counter)
    {
        $qb = $this->getQueryBuilder($table) ;
        $qb->update($table)->set($counterField , $counter)
            ->where($qb->expr()->eq("uid" , $qb->createNamedParameter($uid , \PDO::PARAM_INT)))
            ->orWhere($qb->expr()->eq("l10n_parent" , $qb->createNamedParameter($uid , \PDO::PARAM_INT)))
            ->execute() ;

    }


}
