<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ScrapeProductOptionsCommandTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_scraping_with_right_data()
    {
        Artisan::call('scrape:options');
        $response = Artisan::output();
        $this->assertJson($response);
        $results = json_decode($response,true);

        foreach ($results as $result) {
            // Assert data type and some expected values
            $this->assertNotNull($result['title']);
            $this->assertNotEmpty($result['title']);
            $this->assertTrue($result['price']>0);
            $this->assertIsString($result['description']);
            $this->assertIsNumeric($result['discount']);
        }

        // Assert 6 results expected
        $this->assertTrue(count($results) == 6);
    }
}
