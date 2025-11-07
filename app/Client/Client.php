<?php

namespace App\Music\Client;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    protected $table = 'clients';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $client_id;

    /**
     * The client.
     *
     * @var string
     */
    protected $client;

    /**
     * The token.
     *
     * @var string
     */
    protected $token;

    /**
     * Token expiry date.
     *
     * @var datetime
     */
    protected $expires;

    /**
     * Created date.
     *
     * @var datetime
     */
    protected $created_at;

    /**
     * Updated date.
     *
     * @var datetime
     */
    protected $updated_at;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['expires'];

}
