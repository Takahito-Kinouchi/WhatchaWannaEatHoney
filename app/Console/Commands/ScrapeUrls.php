<?php

namespace App\Console\Commands;

use App\Crawler\Crawler;
use App\Models\RecipeUrl;
use Illuminate\Console\Command;

class ScrapeUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'scrapes recipe urls from cookpad';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $crawler = new Crawler();
        $maxPage = $crawler->getCategoryPageCount();
        $recipeUrls = [];
        $bar = $this->output->createProgressBar($maxPage);
        for ($pageNum = 1; $pageNum <= $maxPage; $pageNum++) {
            $recipeUrls = array_merge($recipeUrls, $crawler->scrapeCategory($pageNum));
            sleep(1);
        }
        RecipeUrl::upsert($recipeUrls, 'recipe_id');
        $bar->finish();
        $this->info("\n" . 'Recipe Urls Scraped Successfully.');
        return Command::SUCCESS;
    }
}
