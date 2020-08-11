<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Test extends MY_Controller 
{
    function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->helper('url');
        $this->client = new GuzzleHttp\Client();
    }

    /**
     * Client class object
     *
     * @var client Client
     */
    protected $client;

    public function index()
    {
        $dropletData =  $this->client->get(constant['url'].'/v2/images?private=true', [
            'headers' => $this->getHeaders()
            ]
        );

        $data['dropletData'] = json_decode($dropletData->getBody());
        print("<pre>".print_r($data['dropletData'],true)."</pre>"); exit;
        
    }

    /**
     * Get headers for the Guzzle client
     *
     * @return void
     */
    private function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization'=> constant['token']
        ];
    }
}