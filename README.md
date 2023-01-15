# What do you wanna eat, honey?

## what this program can do

- scrape recipes and ingredients those recipes use, by artisan command "scrape:recipes".  
- connect to a LINE bot and read messages that bot receives.
- based on the received message (must be space separated ingredient names), suggest urls of recipes that use ingredients the received message contains.

### Artisan command: scraping recipes
To scrape recipes from cookpad, run an artisan command below.  
Normally you need to do this only once, in order to set up databases.  
**Do not run this command frequently. You must understand the concept of web scraping before running this command.**
```
php artisan scrape:recipes
```

### Setting up a line bot and its connection
check out [official line developers documents](https://developers.line.biz/en/docs/messaging-api/building-bot/) to set up a LINE bot.  
after setting up completion, put your channel ID, channel secret, channel token in your `.env` file as below.
```
LINE_MESSAGE_CHANNEL_ID={your channel ID}  
LINE_MESSAGE_CHANNEL_SECRET={your channel secret}  
LINE_MESSAGE_CHANNEL_TOKEN={your channel token}
```
- [ngrok](https://ngrok.com/) will be super useful when you want to test your line bot.

### Recipe suggesting
Once your LINE bot receives a list of ingredient names as a message, this program will find recipes with such ingredient list and get urls of them. LINE bot will send urls as a reply.  
If your LINE bot receives 'おまかせ' as a message, this program will get a random recipes and LINE bot replies their urls. 
- LINE bot uses a reply token included in a received message body. If you try to send more than 5 messages with the same reply token, messages won't be sent at all.

### Improvements to do in the future
- must find a way to handle orthographical variant of Japanese characters, especially Kanjis.

***
***This program uses Gautte as a web crawler, line-bot-sdk as LINE bot SDK.***