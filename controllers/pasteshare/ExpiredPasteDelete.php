<?php
/**
 * This file contains the command class to delete expired pastes for pasteshare
 * @license MIT
 */
namespace pasteshare;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class can delete expired pastes from the database for pasteshare
 *
 * @author Anthony Vitacco <anthony@littlegno.me>
 */
class ExpiredPasteDelete extends Command
{
    /** @var object An instance of our dependency container */
    private $deps;
    
    /**
     * Magic construct function
     *
     * @param object $deps The dependency container
     */
    public function __construct($deps)
    {
        $this->deps = $deps;
        parent::__construct();
    }
    
    /**
     * Configure the options for the command
     */
    protected function configure()
    {
        $this->setName("dropExpired")
        ->setDescription("Will drop all expired pastes from the database")
        ->addOption("dry-run", "d", null, "Dry run, do not actually delete from the database");
    }
    
    /**
     * What to do when then command is executed
     *
     * @param object $input The Input Interface instance
     * @param object $output The Output Interface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelperSet()->get("formatter");
        
        $timezone = new \DateTimeZone($this->deps["siteConfig"]->application->timezone);
        $currentDateTime = new \DateTime("now", $timezone);
        
        $dm = $this->deps["mongoDm"];
        
        /**
         * If we're in dry-run mode, we don't want to actually delete anything
         */
        if ($input->getOption("dry-run")) {
            $pastes = $dm->createQueryBuilder("pasteshare\Paste")
                ->eagerCursor(true)
                ->field("expires")->lte($currentDateTime)
                ->getQuery()
                ->execute();
            
            foreach ($pastes as $paste) {
                $output->writeln($formatter->formatSection("DELETE", $paste->uniqid));
            }
        } else {
            /**
             * If we need to print verbose/debug info we need to query the
             * database before we drop the entries so we have something to print
             */
            if ($output->isVerbose()) {
                $pastes = $dm->createQueryBuilder("pasteshare\Paste")
                    ->eagerCursor(true)
                    ->field("expires")->lte($currentDateTime)
                    ->getQuery()
                    ->execute();
                
                foreach ($pastes as $paste) {
                    $output->writeln($formatter->formatSection("DELETE", $paste->uniqid));
                    $dm->createQueryBuilder("pasteshare\Paste")
                        ->remove()
                        ->field("uniqid")->equals($paste->uniqid)
                        ->getQuery()
                        ->execute();
                }
            } else {
                /**
                 * We don't need to display anything so we should use the fewest
                 * number of queries possible.
                 */
                $dm->createQueryBuilder("pasteshare\Paste")
                ->remove()
                ->field("expires")->lte($currentDateTime)
                ->getQuery()
                ->execute();
            }
        }
    }
}
