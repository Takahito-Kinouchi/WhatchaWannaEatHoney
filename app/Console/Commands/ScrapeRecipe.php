<?php
declare(strict_types=1);

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
        $bar->setFormat('very_verbose');

        $existingRecipeUrls = Recipe::query()->select('url')->get();
        foreach ($recipeUrls as $recipeUrl) {
            if ($existingRecipeUrls->contains($recipeUrl)) {
                $bar->advance();
                continue;
            }
            
            $node = \Goutte::request('GET', $recipeUrl)
                ->filter('div.recipe_show_wrapper');

            $scrapedRecipeDetail = $crawler->scrapeRecipe($node);

            if ($scrapedRecipeDetail['review_count'] <= 5) {
                $bar->advance();
                continue;
            }

            $recipe = new Recipe($scrapedRecipeDetail);
            $recipe->url = $recipeUrl;
            $recipe->save();

            $scrapedIngredientDetails = $crawler->scrapeIngredients($node);

            $recipe->setRelation('ingredients', $scrapedIngredientDetails);

            foreach ($scrapedIngredientDetails as $ingredientDetail) {
                $ingredient = new Ingredient($ingredientDetail);
                $ingredient->recipe_id = $recipe->id;
                $ingredient->save();
            }

            $bar->advance();
        }
        $bar->finish();
        $this->info("\n" . 'Recipe Scraped Successfully.');
        return Command::SUCCESS;
    }
}
