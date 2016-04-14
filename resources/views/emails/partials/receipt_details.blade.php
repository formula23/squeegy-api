@foreach($order->order_details as $order_detail)
    <span>{{ $order_detail->name }} Wash<span style="float:right">${{ number_format($order_detail->amount/100, 2) }}</span></span><br />
@endforeach
