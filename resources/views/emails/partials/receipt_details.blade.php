@foreach($order->order_details as $order_detail)
    <span style="font-size:12px">{{ $order_detail->name }}&nbsp;<span style="float:right">${{ number_format($order_detail->amount/100, 2) }}</span></span><br />
@endforeach
