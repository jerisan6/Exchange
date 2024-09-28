<?php

namespace App\Notifications\Admin;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SellCryptoMailNotification extends Notification
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
            ->subject("Sell Crypto Via ". $data['data']->details->data->sender_wallet->name)
            ->line("Your sell crypto request successful via ".$data['data']->details->data->sender_wallet->name." , details of sell crypto:")
            ->line("Request Amount: " . $data['data']->amount.' '. $data['data']->details->data->sender_wallet->code)
            ->line("Fees & Charges: " . getAmount($data['data']->total_charge).' '. $data['data']->details->data->sender_wallet->code)
            ->line("Will Get: " . getAmount($data['data']->details->data->will_get,2).' '. $data['data']->details->data->payment_method->code)
            ->line("Total Payable Amount: " . getAmount($data['data']->total_payable,2).' '. $data['data']->details->data->sender_wallet->code)
            ->line("Transaction Id: " .$data['data']->trx_id)
            ->line("Status: ".$data['status'])
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
