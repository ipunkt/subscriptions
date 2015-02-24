@foreach ($benefits as $benefit)
	<li>@if (Lang::has('subscriptions.features.' . $benefit->feature()))
		@lang('subscriptions.features.' . $benefit->feature())
		@else
		{{ $benefit->feature() }}
		@endif
		<span class="pull-right">
			@if ($benefit->max() === null)
				&infin;
			@elseif($benefit->min() === 0)
				{{ $benefit->max() }}
			@else
				{{ $benefit->min() }} - {{ $benefit->max() }}
			@endif
		</span>
	</li>
@endforeach