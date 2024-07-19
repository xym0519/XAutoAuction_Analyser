<?php

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $url = 'example/%s/test';
        $querys = [1];
        $params = [
            'p1' => 'p1'
        ];

        $response = $this->post(sprintf($url, ...$querys), $params);
        self::print_($response);
        $this->assertEquals(201, $response->response->getStatusCode());
        $this->assertJson($response->response->content());
    }
}
