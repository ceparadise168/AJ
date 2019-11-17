<?php

namespace App\Console\Commands;

use App\ZodiacSign;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;

class UpdateZodiac extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:update-zodiac';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Start Time
     *
     * @var \Datetime
     */
    private $stratTime;

    /**
     *  Client
     *
     * @var GuzzleHttp\Client;
     */
    private $client;

    /**
     * Start Time
     *
     * @var \Datetime
     */
    private $endTime;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->zodiacSignMap = ZodiacSign::$zodiacSignMap;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->start();
        $this->crawler();
        $this->update();
        $this->end();
    }

    private function update()
    {
        foreach ($this->infos as $info)
        {
            $zodiacSign = ZodiacSign::Where('sign_id', $info['sign_id'])->first();

            if (!$zodiacSign) {
                $zodiacSign = new ZodiacSign;
                $zodiacSign->sign_id = $info['sign_id'];
                $zodiacSign->name = $info['name'];
            }

            $zodiacSign->total_rank = $info['total_rank'];
            $zodiacSign->total_describe = $info['total_describe'];
            $zodiacSign->love_rank = $info['love_rank'];
            $zodiacSign->love_describe = $info['love_describe'];
            $zodiacSign->job_rank = $info['job_rank'];
            $zodiacSign->job_describe = $info['job_describe'];
            $zodiacSign->money_rank = $info['money_rank'];
            $zodiacSign->money_describe = $info['money_describe'];

            $zodiacSign->save();
        }
    }

    private function crawler()
    {
        $client = $this->getClient();

        foreach ($this->zodiacSignMap as $signId => $signName) {
            try {
                $this->data = [];
                $this->data['sign_id'] = $signId;

                $response = $client->request('GET', "daily_$signId.php?iAstro=$signId");
                $content = $response->getBody()->getContents();

                $crawler = new Crawler();
                $crawler->addHtmlContent($content);

                $target = $crawler->filterXPath('//div[contains(@class, "TODAY_CONTENT")]')->children();

                $target->each(function (Crawler $node) {
                    $node_text = $node->text();
                    $this->data[] = $node_text;
                });

                $this->paserData($this->data);
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(),'<br>';
            }
        }
    }

    private function paserData($data)
    {
        $targetMap = [
            'total' => '整體運勢',
            'love' => '愛情運勢',
            'job' => '事業運勢',
            'money' => '財運運勢'
        ];
        $signId = $data['sign_id'];

        $info = [];
        $info['sign_id'] = $signId;
        $info['name'] = $this->zodiacSignMap[$signId];

        unset($data[0]);

        foreach ($targetMap as $key => $value) {
            $idxRank = "$key" . '_rank';
            $idxDescribe = "$key" . '_describe';

            foreach ($data as $dataKey => $dataValue) {
                preg_match("@$value(★|☆)+@", $dataValue, $match);

                if ($match && $match[0]) {
                    $info[$idxRank] = substr_count($match[0], '★');
                    $nextKey = $dataKey + 1;
                    $info[$idxDescribe] = $data[$nextKey];
                }
            }
        }
        $this->infos[] = $info;
    }

    private function getClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => 'http://astro.click108.com.tw/',
                'timeout'  => 5.0,
            ]);
        }

        return $this->client;
    }

    private function start()
    {
        $this->startTime = new \DateTime;

        $this->info('Start Update ...');
    }

    private function end()
    {
        $this->endTime = new \Datetime;

        $costTime = $this->endTime->diff($this->startTime, true);

        $this->info('Execute time: ' . $costTime->format('%H:%I:%S'));
    }
}
