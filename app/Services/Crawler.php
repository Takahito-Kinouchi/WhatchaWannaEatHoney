<?php

namespace App\Services;

use App\Models\RecipeUrl;

final class Crawler
{
    /**
     *
     * @var string
     */
    private string $mainUrl = 'https://cookpad.com';

    /**
     *
     * @var int
     */
    private int $categoryPageCount = 10;

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
            return $this->mainUrl . $recipeUrl;
        });
    }

    /**
     *
     * @param RecipeUrl $recipeUrl
     * @param mixed $node
     *
     * @return array
     */
    public function scrapeRecipe(mixed $node): array
    {
        $title = $node->filter('h1.recipe-title')->text();
        $reviewCountNode = $node->filter('span.tsukurepo_count');
        if (!$reviewCountNode->count()) {
            $reviewCount = 0;
        } else {
            $reviewCount = (int) $reviewCountNode->text();
        }
        return [
            'title' => $title,
            'review_count' => $reviewCount,
        ];
    }

    /**
     *
     * @param mixed $node
     *
     * @return array
     */
    public function scrapeIngredients(mixed $node): array
    {
        $ingredientsNode = $node->filter('div.ingredient_row');
        $ingredientList = $ingredientsNode->each(function ($row) {
            if ($row->filter('div.ingredient_name')->count()) {
                return [
                    'name' => $row->filter('div.ingredient_name > span.name')->text(),
                ];
            }
        });
        return array_filter($ingredientList, function ($i) {
            return $i !== null;
        });
    }
}
