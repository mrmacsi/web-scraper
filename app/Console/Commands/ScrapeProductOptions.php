<?php
namespace App\Console\Commands;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Console\Command;

class ScrapeProductOptions extends Command
{
    protected $signature = 'scrape:options';

    protected $description = 'Scrapes product options from the specified website and returns them in a JSON array';

    // Use dependency injection to inject the HTTP client as a service
    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    public function handle()
    {
        // Use the injected client to make the HTTP request
        $response = $this->client->get('https://wltest.dns-systems.net/');
        $html = $response->getBody()->getContents();

        $crawler = new Crawler($html);
        $options = $crawler->filter('.pricing-table .row-subscriptions .package')->each(function (Crawler $tr) {

            // Use the Crawler to traverse and query the HTML
            $packages = $tr->filter('.package-features ul li');
            $priceDiv = $packages->filter('.package-price');
            $price = (float)str_replace('£','',$packages->filter('.price-big')->text());

            // Check if price is yearly, if not times 12
            if (stripos($priceDiv->text(),'year')!==false){
                $annual = $price;
            } else {
                $annual = $price * 12;
            }

            // Check if discount exists
            $isDiscounted = $priceDiv->filter('p')->count() > 0;
            $discountAmount = 0;

            if ($isDiscounted){
                //get price with regex digits pattern
                preg_match('/£(\d+\.\d+)/', $priceDiv->filter('p')->text(), $matches);
                $discountAmount =  (float)$matches[1];
            }
            // Return the package options as an array
            return [
                'title' => $tr->filter('.header h3')->first()->text(),
                'description' => $packages->filter('.package-description')->text(),
                'price' => $annual,
                // Check if there is a discount available
                'discount' => $discountAmount,
            ];
        });

        // Sort the options by annual price in descending order
        $options = collect($options)->sortByDesc(function ($option) {
            return $option['price'];
        });

        // Output the options as a JSON encoded string
        $this->info(json_encode($options));
    }
}