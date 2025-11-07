<?php

/**
 * CreateClient.php
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Console\Commands;

use App\Music\Client\Client as Client;
use Exception;
use Illuminate\Console\Command;

/**
 * Create client via the command line
 */
class CreateClient extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create-client
                            {--client= : Client name}
                            {--token= : Client token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create client in database';

    /**
     * Create a new command instance.
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
        $options = $this->options();

        if(isset($options['client'])):
            $client = $options['client'];
        else:
            $client = $this->ask('What is the name of the client?');
        endif;

        if(isset($options['token'])):
            $token = $options['token'];
        else:
            $token = $this->ask('What token should be used for the client?');
        endif;

        // Validate client.
        if(empty($client)):
            $this->error('Client is required');
            exit;
        else:
            $exists = Client::where('client', $client)->first();
            if($exists):
                $this->error('Client already exists');
                exit;
            endif;
        endif;

        // Validate token.
        if(empty($token)):
            $this->error('The token is required');
            exit;
        else:
            if(strlen($token) < 8):
                $this->error('Token should be at least 8 characters long');
                exit;
            endif;
            $encrypted_token = crypt($token, config('app.api_salt'));
            $exists = Client::where('token', $encrypted_token)->first();
            if($exists):
                $this->error('This token is already being used');
                exit;
            endif;
        endif;

        $this->createClient($client, $encrypted_token);
    }

    /**
     * Create a client.
     *
     * @param string $client_name     The client name
     * @param string $encrypted_token The client token
     *
     * @return void
     */
    public function createClient($client_name, $encrypted_token)
    {
        try {

            $client = [];
            $client['client'] = $client_name;
            $client['token'] = $encrypted_token;

            Client::create($client);

            $this->info('The client has been created successfully.');

        } catch (Exception $e) {
            $this->error("{{$e}}");
        }
    }

}
