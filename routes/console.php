<?php

use App\Mail\AnniversaryMail;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Mail;
use App\Services\AnniversaryService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function(){
    $users = User::where('email_verified_at', '!=', null)->get();
    $wifeEmail = User::where('role', 'wife')->first()->email;
    $anniversary = AnniversaryService::getAnniversary();

    if($anniversary['showBanner']){
        foreach($users as $user){
            Mail::to($user->email)->queue(new AnniversaryMail($anniversary, $wifeEmail));
        }
    }
})->dailyAt('08:00');