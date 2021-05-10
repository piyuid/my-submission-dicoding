<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

$pass_signature = true;

// Mengatur Piybot channel_access_token dan channel_secret
$channel_access_token = "zJgFHXcbz8Bi4ZxRozV37ajdmP+U6JX8Xphxs6vMT1VufvWpnmnpPYno1NDBjygqRU4rCfnPiH9sT8ThYC2zQNnCJ9GZJxPtY0CEJ11JF89F9OB99dfC9Bkw6T8msWpLUL76Ht4KYFeQgmZnsx699QdB04t89/1O/w1cDnyilFU=";
$channel_secret = "d2be779ac2009b2a9d7b8f167a0afdb0";

// inisiasi objek Piybots
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$app = AppFactory::create();
$app->setBasePath("/public");

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Piybot-Line");
    return $response;
});

// Route untuk webhook dari Line ke Heroku
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    // dapatkan header request body dan line signature
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');

    // log body dan signature
    file_put_contents('php://stderr', 'Body: ' . $body);

    if ($pass_signature === false) {
        // Kondisi melihat ada Line Signature di request header
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }

        // Jika proses line dev gagal.
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }

    $data = json_decode($body, true);
    if (is_array($data['events'])) {
        foreach ($data['events'] as $event) {
            if ($event['type'] == 'message') {
                //Perintah reply message
                if ($event['message']['type'] == 'text') {
                    if (strtolower($event['message']['text']) == 'user id') {

                        $result = $bot->replyText($event['replyToken'], $event['message']['text']);

                    } elseif (strtolower($event['message']['text']) == 'flex message') {

                        $flexTemplate = file_get_contents("../flex_message.json");
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    'type'     => 'flex',
                                    'altText'  => 'Test Flex Message',
                                    'contents' => json_decode($flexTemplate)
                                ]
                            ],
                        ]);

                    } else {
                        // Hasil Message yang sama.
                        $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                    }


                    // Code ini bisa menggunakan replyMessage () sebagai gantinya untuk mengirim pesan balasan
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);


                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                } //content api
                elseif (
                    $event['message']['type'] == 'image' or
                    $event['message']['type'] == 'video' or
                    $event['message']['type'] == 'audio' or
                    $event['message']['type'] == 'file'
                ) {
                    $contentURL = "https://piybot.herokuapp.com/public/content/" . $event['message']['id'];
                    $contentType = ucfirst($event['message']['type']);
                    $result = $bot->replyText($event['replyToken'],
                        $contentType . " yang Anda kirim bisa diakses dari link:\n " . $contentURL);

                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                } //group room
                elseif (
                    $event['source']['type'] == 'group' or
                    $event['source']['type'] == 'room'
                ) {
                    //message from group / room
                    if ($event['source']['userId']) {

                        $userId = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile = $getprofile->getJSONDecodedBody();
                        $greetings = new TextMessageBuilder("Halo, " . $profile['displayName']);

                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                        return $response
                            ->withHeader('Content-Type', 'application/json')
                            ->withStatus($result->getHTTPStatus());
                    }
                } else {
                    //message from single user
                    $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                    $response->getBody()->write((string)$result->getJSONDecodedBody());
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                }
            }
        }
        return $response->withStatus(200, 'for Webhook!'); //buat ngasih response 200 jika gagal verify ke pas webhook heroku di website developer line
    }
    return $response->withStatus(400, 'No event sent!');
});

$app->get('/content/{messageId}', function ($req, $response, $args) use ($bot) {
    // get message content

    $messageId = $args['messageId'];
    $result = $bot->getMessageContent($messageId);

    // set response
    $response->getBody()->write($result->getRawBody());

    return $response
        ->withHeader('Content-Type', $result->getHeader('Content-Type'))
        ->withStatus($result->getHTTPStatus());
});

$app->get('/pushmessage', function ($req, $response) use ($bot) {
    // send push message to user
    $userId = 'Isi dengan user ID Anda';
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
    $result = $bot->pushMessage($userId, $textMessageBuilder);

    $response->getBody()->write("Pesan push berhasil dikirim!");
    return $response
        //->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

$app->get('/multicast', function ($req, $response) use ($bot) {
    // list of users
    $userList = [
        'Isi dengan user ID Anda',
        'Isi dengan user ID teman1',
        'Isi dengan user ID teman2',
        'dst'
    ];

    // send multicast message to user
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan multicast');
    $result = $bot->multicast($userList, $textMessageBuilder);


    $response->getBody()->write("Pesan multicast berhasil dikirim!");
    return $response
        //->withHeader('Content-Type', 'application/json') //baris ini dapat dihilangkan karena hanya menampilkan pesan di browser
        ->withStatus($result->getHTTPStatus());
});

$app->get('/profile/{userId}', function ($req, $response, $args) use ($bot) {
    // get user profile
    $userId = $args['userId'];
    $result = $bot->getProfile($userId);

    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

$app->run();




