<?php

namespace App\Crawler;

use App\Models\RecipeUrl;

final class Crawler
{
    /**
     *
     * @var string
     */
    private string $mainUrl = 'https://cookpad.com/';

    /**
     *
     * @var int
     */
    private int $categoryPageCount = 2;

    /**
     *
     * @return int
     */
    public function getCategoryPageCount(): int
    {
        return $this->categoryPageCount;
    }

    /**
     *
     * @return array
     */
    public function scrapeCategory(int $pageNum): array
    {
        $crawler = \Goutte::request('GET', 'https://cookpad.com/category/177?page=' . $pageNum);
        return $crawler->filter('div.recipe-preview')->each(function ($node) {
            $recipeUrl = $node->filter('a.recipe-title.font13')->attr('href');
            $recipeId = substr($recipeUrl, 8);
            return [
                'recipe_id' => $recipeId,
                'url' => $recipeUrl,
            ];
        });
    }

    /**
     *
     * @param RecipeUrl $recipeUrl
     * @param mixed $node
     *
     * @return array
     */
    public function scrapeRecipe(int $recipeUrlId, mixed $node): array
    {
        $title = $node->filter('h1.recipe-title')->text();
        $comment = $node->filter('div.description_text')->text();
        $servingNode = $node->filter('span.servings_for.yield');
        if (!$servingNode->count()) {
            $serving = '(1食分)';
        } else {
            $serving = $servingNode->text();
        }
        $reviewCountNode = $node->filter('span.tsukurepo_count');
        if (!$reviewCountNode->count()) {
            $reviewCount = 0;
        } else {
            $reviewCount = (int) $reviewCountNode->text();
        }
        return [
            'recipe_url_id' => $recipeUrlId,
            'title' => $title,
            'comment' => $comment,
            'serving' => $serving,
            'review_count' => $reviewCount,
        ];
    }

    /**
     *
     * @param int $recipe_url_id
     * @param mixed $node
     *
     * @return array
     */
    public function scrapeIngredients(int $recipe_url_id, mixed $node): array
    {
        $ingredientsNode = $node->filter('div.ingredient_row');
        $ingredients = $ingredientsNode->each(function ($row) use ($recipe_url_id) {
            if ($row->filter('div.ingredient_name')->count()) {
                return [
                    'recipe_url_id' => $recipe_url_id,
                    'ingredient_name' => $row->filter('div.ingredient_name > span.name')->text(),
                    'quantity' => $row->filter('div.ingredient_quantity.amount')->text(),
                ];
            }
        });
        return array_filter($ingredients, function ($i) {
            return $i !== null;
        });
    }

    /**
     * Get the value of mainUrl
     */
    public function getMainUrl()
    {
        return $this->mainUrl;
    }
}
