<?php
namespace Tests\Functional;

class ApiTest extends BaseTestCase
{
    public function post($path, $accessKey, $options = array())
    {
        return $this->runApp('POST', $path, $accessKey, $options);
    }

    public function testAccessCreate()
    {
        $response = $this->post('/create', '321');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertContains('not authorised', (string)$response->getBody());
    }

    public function testAccessRead()
    {
        $response = $this->post('/read', '321');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertContains('not authorised', (string)$response->getBody());
    }

    public function testAccessUpdate()
    {
        $response = $this->post('/update', '321');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertContains('not authorised', (string)$response->getBody());
    }

    public function testAccessDelete()
    {
        $response = $this->post('/delete', '321');

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertContains('not authorised', (string)$response->getBody());
    }

    public function testUser() {
        // Test create operation
        $data = [
            'table' => 'user',
            'data' => [
                [
                    "column" => "username",
                    "value" => "TestUser",
                    "dataType" => "string",
                    "checkExistence" => true
                ],[
                    "column" => "phone_no",
                    "value" => rand(100,100000),
                    "dataType" => "number"
                ],[
                    "column" => "created",
                    "value" => date('c'),
                    "dataType" => "date"
                ],[
                    "column" => "location",
                    "value" => "36.124918,-115.3154292",
                    "dataType" => "geoPoint"
                ]
            ]
        ];

        $response = $this->post('/create', '123', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('id', (string)$response->getBody());
        $userId = json_decode($response->getBody())->id;

        // Test read operation
        $data = [
            "table" => "user",
            "limit" => "1",
            "descending" => "id",
            "select" => "id"
        ];

        $response = $this->post('/read', '123', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($userId, reset(json_decode($response->getBody()))->id);

        // Test update operation
        $data = [
            'table' => 'user',
            'id' => $userId,
            'data' => [
                [
                    "column" => "location",
                    "value" => "new location",
                    "dataType" => "geoPoint"
                ]
            ]
        ];

        $response = $this->post('/update', '123', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('new location', json_decode($response->getBody())->location);

        // Test delete operation
        $data = [
            "table" => "user",
            "id" => $userId
        ];

        $response = $this->post('/delete', '123', $data);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('true', (string)$response->getBody());
    }


}
