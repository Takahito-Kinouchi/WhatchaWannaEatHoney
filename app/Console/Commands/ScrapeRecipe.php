<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\Crawler;
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

        $maxPage = $crawler->getCategoryPageCount();

        $recipeUrls = [];

        $bar = $this->output->createProgressBar($maxPage);

        for ($pageNum = 1; $pageNum <= $maxPage; $pageNum++) {
            $recipeUrls = array_merge($recipeUrls, $crawler->scrapeCategory($pageNum));
            $bar->advance();
        }
        $bar->finish();

        $this->info("\n" . 'Recipe Urls Scraped Successfully.');

        $bar = $this->output->createProgressBar(count($recipeUrls));

        foreach ($recipeUrls as $recipeUrl) {
            $node = \Goutte::request('GET', $recipeUrl)
                ->filter('div.recipe_show_wrapper');

            $scrapedRecipeDetails = $crawler->scrapeRecipe($node);
            $recipe = new Recipe($scrapedRecipeDetails);
            $recipe->url = $recipeUrl;

            $ingredientsList = collect($crawler->scrapeIngredients($node))->map(function ($ingredientDetails) {
                return new Ingredient($ingredientDetails);
            });
            $recipe->setRelation('ingredients', $ingredientsList);

            $recipe->save();
            $recipe->ingredients->each(function (Ingredient $ingredient) use ($recipe) {
                $ingredient->recipe_id = $recipe->id;
                $ingredient->save();
            });
            $bar->advance();
        }
        $bar->finish();
        $this->info("\n" . 'Recipe Scraped Successfully.');
        return Command::SUCCESS;
    }
}
