<?php
declare(strict_types=1);

namespace App\Services;

use LINE\LINEBot;
use App\Models\Recipe;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Database\Eloquent\Builder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// LineBotは１リプライトークンに対して5通までしかメッセージを送れない
final class LineBotService
{
    use HasFactory;

    /**
     *
     * @var CurlHTTPClient
     */
    private CurlHTTPClient $httpClient;

    /**
     *
     * @var LINEBot
     */
    private LINEBot $lineBot;

    public function __construct()
    {
        $this->httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $this->lineBot = new LINEBot($this->httpClient, ['channelSecret' => config('app.line_channel_secret')]);
    }

    /**
     *
     * @param string $replyToken
     * @param Collection $recipeList
     *
     * @return void
     */
    public function suggestRecipes(string $replyToken, Collection $recipeList): void
    {
        $messages = new MultiMessageBuilder();
        $messages->add(new TextMessageBuilder('その食材リストで作れるおすすめレシピを' . $recipeList->count() . '件送ります!'));

        foreach ($recipeList as $recipe) {
            $urlMessage = new TextMessageBuilder($recipe->url);
            $messages->add($urlMessage);
        }
        $this->lineBot->replyMessage($replyToken, $messages);
    }

    /**
     *
     * @param string $replyToken
     * @param Recipe $randomRecipe
     *
     * @return void
     */
    public function showRandomRecipe(string $replyToken, collection $randomRecipes): void
    {
        $messages = new MultiMessageBuilder();
        $messages->add(new TextMessageBuilder('ランダムなレシピを紹介します!'));

        foreach ($randomRecipes as $recipe) {
            $urlMessage = new TextMessageBuilder($recipe->url);
            $messages->add($urlMessage);
        }
        $this->lineBot->replyMessage($replyToken, $messages);
    }

    /**
     *
     * @param string $replyToken
     *
     * @return void
     */
    public function noRecipeMessage(string $replyToken): void
    {
        $this->lineBot->replyText($replyToken, '食材リストにマッチするレシピが見つかりませんでした...');
    }
}
