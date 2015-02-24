<?php
/**
 * Using example:

<ul class="list-unstyled">
@include('subscriptions::benefits.list', ['benefits' => $plan->benefits()])
</ul>

 * @view subscriptions::benefits.list
 * @param Collection|Benefit[] $benefits
 * @supports translation in subscriptions.features.[YOUR-FEATURE-ID]
 */
?>@foreach ($benefits as $benefit)
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