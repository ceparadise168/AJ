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
            var_dump($info);
            $zodiacSign = ZodiacSign::Where('uri_id', $info['uri_id'])->first();

            if (!$zodiacSign) {
                $zodiacSign = new ZodiacSign;

                $zodiacSign->uri_id = $info['uri_id'];
                $zodiacSign->name = $info['name'][0];
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
        $count = 0;

        while ($count <= 11) {
            try {
                $this->data = [];
                $this->data['uri_id'] = $count;

                $response = $client->request('GET', "daily_$count.php?iAstro=$count");
                $content = $response->getBody()->getContents();
        
                $crawler = new Crawler();
                $crawler->addHtmlContent($content);
                
                $target = $crawler->filterXPath('//div[contains(@class, "TODAY_CONTENT")]')->children();
        


                $target->each(function (Crawler $node) {
                    $node_text = $node->text();
                    $this->data[] = $node_text; 
                });

                $this->paserData($this->data);

                $count++;
            } catch (Exception $e) {  
                echo 'Caught exception: ',  $e->getMessage(),'<br>';  
            }  
        }
	}
    
    private function paserData($data)
    {
        $map = [
            'total' => '整體運勢',
            'love' => '愛情運勢',
            'job' => '事業運勢',
            'money' => '財運運勢'
        ];

        $info = [];
        $info['uri_id'] = $data['uri_id'];
        //preg_match("@\W{2}座@", $data[0], $m);

        $signName = substr($data[0], 6, 9);
        $info['name'] = $signName;

        unset($data[0]);        
          
        foreach ($map as $key => $value) {
            $idx = "$key" . '_rank';
            $idxx = "$key" . '_describe';

            foreach ($data as $dk => $d) {
                preg_match("@$value(★|☆)+@", $d, $r);

                if ($r && $r[0]) {                
                    $info[$idx] = substr_count($r[0], '★');
                    $ndk = $dk + 1;
                    $info[$idxx] = $data[$ndk];                   
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
                'timeout'  => 2.0,
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
