<?php

namespace H2W\Console\Commands;

use H2W\Import\HubSpot;
use Illuminate\Console\Command;

/**
 * Class Import
 *
 * @package H2W\Console\Commands
 */
class Import extends Command
{
    /**
     * @var HubSpot
     */
    private $hubSpot;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:hubspot
                            {blogId : Numeric ID of the blog to import}
                            {--dry-run : Simulate the import, don\'t actually save anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a blog from HubSpot into WordPress';

    /**
     * Create a new command instance.
     *
     * @param HubSpot $hubSpot
     */
    public function __construct(HubSpot $hubSpot)
    {
        parent::__construct();
        $this->hubSpot = $hubSpot;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->hubSpot->import($this->argument('blogId'));
    }
}
