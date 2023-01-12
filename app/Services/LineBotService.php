<?php

namespace App\Services;

use LINE\LINEBot;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use Illuminate\Database\Eloquent\Collection;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LineBotService
{
    use HasFactory;

    public static function suggestRecipes(string $replyToken, Collection $recipeList)
    {
        $httpClient = new CurlHTTPClient(config('app.line_channel_access_token'));
        $lineBot = new LINEBot($httpClient, ['channelSecret' => config('app.line_channel_secret')]);

        $messages = new MultiMessageBuilder();
        $messages->add(new TextMessageBuilder('その食材リストで作れるおすすめレシピを' . $recipeList->count() . '件送ります!'));

        $recipeList->each(function (Recipe $recipe) use ($messages) {
            // $titleMessage = new TextMessageBuilder($recipe->title);
            // $messages->add($titleMessage);
            $urlMessage = new TextMessageBuilder($recipe->url);
            $messages->add($urlMessage);
        });

        $lineBot->replyMessage($replyToken, $messages);
    }
}
