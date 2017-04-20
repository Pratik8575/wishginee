<?php
namespace Wishginee\Foundation;

use Razorpay\Api\Api;

class Razorpay extends Api{

    /**
     * Razorpay constructor.
     */
    public function __construct()
    {
        parent::__construct(config('razorpay.config.key'), config('razorpay.config.secret'));
    }
}