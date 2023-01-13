<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Console\Command;

class CleanUpRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:recipes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'deletes recipes with less than 30 reviews';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $unPopularRecipes = Recipe::query()
            ->where('review_count', '<', 30);
        $unpopularRecipeIngredients = Ingredient::query()
            ->whereIn('recipe_id', $unPopularRecipes->select('id')
            ->get());
        $unpopularRecipeIngredients->delete();
        $unPopularRecipes->delete();
        return Command::SUCCESS;
    }
}
