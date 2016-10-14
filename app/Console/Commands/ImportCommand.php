<?php

namespace H2W\Console\Commands;

use H2W\API\HubSpot;
use H2W\Import;
use H2W\Listeners\LogEventListener;
use Illuminate\Console\Command;

/**
 * Class ImportCommand
 *
 * @package H2W\Console\Commands
 */
class ImportCommand extends Command
{
    /**
     * @var ImportCommand
     */
    private $import;
    /**
     * @var HubSpot
     */
    private $hubSpot;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "import:hubspot
                            {blogId? : Numeric ID of the blog to import}
                            {--dry-run : Simulate the import, don't actually save anything}
                            {--offset= : Skip importing the first [OFFSET] posts}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ImportCommand a blog from ImportCommand into WordPress';

    /**
     * Create a new command instance.
     *
     * @param Import  $import
     * @param HubSpot $hubSpot
     */
    public function __construct(Import $import, HubSpot $hubSpot)
    {
        parent::__construct();
        $this->import  = $import;
        $this->hubSpot = $hubSpot;

        /**
         * Convenient way to log events to the console and the log file
         */
        app('events')->listen('log.*', function ($data) {
            app(LogEventListener::class, ['command' => $this])->handle($data);
        });
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if (null === $blogId = $this->argument('blogId')) {
                $blogId = $this->choice('Enter Blog ID to import', $this->getBlogId(), 0);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return;
        }

        $this->import->handle($blogId, $this->output, $this->option('offset'), $this->option('dry-run'), $this->output->isVeryVerbose());
        $this->output->writeln('');
    }

    protected function getBlogId()
    {
        $this->hubSpot->setVerbose($this->output->isVeryVerbose());
        $blogs = $this->hubSpot->getBlogs();

        if ($blogs->count() === 0) {
            throw new \Exception('No blogs found! Check your HubSpot Configuration.');
        }

        $headers = ['ID', 'Name', 'Description'];

        $blogs->transform(function ($blog) {
            return [
                'id'          => (string) $blog->id,
                'name'        => $blog->name,
                'description' => $blog->description,
            ];
        });

        $this->table($headers, $blogs);

        return $blogs->pluck('id')->all();
    }
}
