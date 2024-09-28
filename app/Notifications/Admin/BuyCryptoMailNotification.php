<?php

namespace App\Notifications\Admin;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BuyCryptoMailNotification extends Notification
{
    use Queueable;
    public $form_data;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($form_data)
    {
        $this->form_data  =  $form_data;
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
        $data       = $this->form_data;
       
        $date       = Carbon::now();
        $dateTime   = $date->format('Y-m-d h:i:s A');

        return (new MailMessage)
            ->greeting("Hello ".$data['data']->user->fullname." !")
            ->subject("Buy Crypto Via ". $data['data']->details->data->wallet->type)
            ->line("Your buy crypto request successful via ".$data['data']->details->data->wallet->name." , details of buy crypto:")
            ->line("Request Amount: " . $data['data']->details->data->amount.' '. $data['data']->details->data->wallet->code)
            ->line("Fees & Charges: " . getAmount($data['data']->details->data->total_charge).' '. $data['data']->details->data->wallet->code)
            ->line("Will Get: " . getAmount($data['data']->details->data->will_get,2).' '. $data['data']->details->data->wallet->code)
            ->line("Total Payable Amount: " . getAmount($data['data']->details->data->payable_amount,2).' '. $data['data']->details->data->wallet->code)
            ->line("Transaction Id: " .$data['data']->trx_id)
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
