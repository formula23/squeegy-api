<?php namespace App\Handlers\Events;

use App\Events\BadRating;


class EmailSupport {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  BadRating  $event
	 * @return void
	 */
	public function handle(BadRating $event)
	{
		$order = $event->order;

        $email_content = [
            'order_id' => $order->id,
            'name' => $order->customer->name,
            'email' => $order->customer->email,
            'rating' => $order->rating,
            'rating_comment' => $order->rating_comment,
        ];

        try {

            \Mail::send('emails.bad_rating', $email_content, function ($message) use ($order) {

				$message->from(config('squeegy.emails.from'), config('squeegy.emails.from_name'));

				$message->to(config('squeegy.emails.support'))->cc("dan@squeegyapp.com")
					->subject(trans('messages.emails.bad_rating.subject'));

            });

        } catch(\Exception $e) {
            \Bugsnag::notifyException($e);
        }

	}

}
