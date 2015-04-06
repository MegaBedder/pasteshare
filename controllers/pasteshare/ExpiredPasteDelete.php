<?php
namespace pasteshare;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExpiredPasteDelete extends Command
{
    /** @var object An instance of our dependency container */
    private $deps;
    
    /**
     *
     */
    public function __construct($deps)
    {
        $this->deps = $deps;
        parent::__construct();
    }
    
    /**
     *
     */
    protected function configure()
    {
        $this->setName("dropExpired")
        ->setDescription("Will drop all expired pastes from the database")
        ->addOption("dry-run", "d", null, "Dry run, do not actually delete from the database");
    }
    
    /**
     *
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
