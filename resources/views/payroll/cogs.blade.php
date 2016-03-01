@foreach($cogs as $cog)
    {{ $cog[0] }} | ${{ number_format($cog[2], 2) }} | ${{ number_format($cog[1], 2) }}
    @endforeach