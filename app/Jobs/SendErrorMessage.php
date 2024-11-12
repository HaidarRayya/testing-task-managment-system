<?php

namespace App\Jobs;

use App\Mail\ErrorMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendErrorMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $errorMessage;
    /**
     * Create a new job instance.
     */
    public function __construct($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to("haedar.rayya@gmail.com")->send(new ErrorMessage($this->errorMessage));
    }
}