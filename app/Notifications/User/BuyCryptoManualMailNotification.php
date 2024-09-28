<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BuyCryptoManualMailNotification extends Notification
{
    use Queueable;
    public $user;
    public $data;
    public $trx_id; 
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$data,$trx_id)
    {
        $this->user = $user;
        $this->data = $data;
        $this->trx_id = $trx_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $user                   = $this->user;
        $data                   = $this->data;
        $trx_id                 = $this->trx_id;
        
        $date = Carbon::now();
        $dateTime = $date->format('Y-m-d h:i:s A');
        return (new MailMessage)
            ->greeting("Hello ".$user->fullname." !")
            ->subject("Buy Crypto Via ". $data->data->wallet->type)
            ->line("Your buy crypto request successful via ".$data->data->wallet->name." , details of buy crypto:")
            ->line("Request Amount: " . $data->data->amount.' '. $data->data->wallet->code)
            ->line("Fees & Charges: " . getAmount($data->data->total_charge,2).' '. $data->data->wallet->code)
            ->line("Will Get: " . getAmount($data->data->will_get,2).' '. $data->data->wallet->code)
            ->line("Total Payable Amount: " . getAmount($data->data->payable_amount,2).' '. $data->data->wallet->code)
            ->line("Transaction Id: " .$trx_id)
            ->line("Date And Time: " .$dateTime)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
