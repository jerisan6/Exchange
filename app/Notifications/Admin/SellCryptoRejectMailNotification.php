<?php

namespace App\Notifications\Admin;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SellCryptoRejectMailNotification extends Notification
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
        $this->form_data    = $form_data;
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
            ->subject("Exchange Crypto Via ". $data['data']->details->data->sender_wallet->name)
            ->line("Your exchange crypto request successful via ".$data['data']->details->data->sender_wallet->name." , details of exchange crypto:")
            ->line("Request Amount: " . $data['data']->amount.' '. $data['data']->details->data->sender_wallet->code)
            ->line("Fees & Charges: " . get_amount($data['data']->total_charge).' '. $data['data']->details->data->sender_wallet->code)
            ->line("Will Get: " . get_amount($data['data']->details->data->will_get).' '. $data['data']->details->data->payment_method->code)
            ->line("Total Payable Amount: " . get_amount($data['data']->total_payable).' '. $data['data']->details->data->sender_wallet->code)
            ->line("Transaction Id: " .$data['data']->trx_id)
            ->line("Status: " .$data['status'])
            ->line("Reject Reason: " .$data['data']->reject_reason)
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
