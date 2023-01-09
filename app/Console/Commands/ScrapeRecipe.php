<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Crawler\Crawler;
use App\Models\RecipeUrl;
use App\Models\Ingredient;
use Illuminate\Console\Command;

class ScrapeRecipe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:recipes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'scrapes recipes from cookpad';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $crawler = new Crawler();
        $mainUrl = $crawler->getMainUrl();
        $recipeUrl = new RecipeUrl();
        $recipeUrls = $recipeUrl->all();
        $recipeInfo = [];
        $ingredientsList = [];
        $bar = $this->output->createProgressBar(count($recipeUrls));
        foreach ($recipeUrls as $recipeUrl) {
            $recipeUrlId = $recipeUrl->id;
            $node = \Goutte::request('GET', $mainUrl . $recipeUrl->url)
            ->filter('div.recipe_show_wrapper');
            $recipeInfo[] = $crawler->scrapeRecipe($recipeUrlId, $node);
            $ingredientsList = array_merge($ingredientsList, $crawler->scrapeIngredients($recipeUrlId, $node));
            $bar->advance();
            sleep(1);
        }
        Recipe::upsert($recipeInfo, 'recipe_url_id');
        Ingredient::upsert($ingredientsList, 'recipe_url_id');
        $bar->finish();
        $this->info("\n" . 'Recipe Scraped Successfully.');
        return Command::SUCCESS;
    }
}
