{{-- resources/views/components/sortable-th.blade.php

    Sortable <th> header cell. Toggles asc/desc on click via a query-string
    link (?sort=key&direction=asc|desc), and highlights the active sort
    direction's arrow in red.

    Props:
        sortKey  (string, required)  Query param value for `sort`, must
                                      match a sortable column key the
                                      controller expects (e.g. 'base_pay').
        label    (string, required)  Visible column label text.
        align    (string, optional)  Tailwind text alignment suffix:
                                      'left' | 'right' | 'center'.
                                      Default: 'left'.
        route    (string, required)  Named route to sort against
                                      (e.g. 'payroll.index').
        params   (array, optional)   Extra query params to preserve/merge
                                      into the sort link. Default:
                                      request()->except(['sort','direction','page']),
                                      i.e. current filters are kept automatically.
                                      Only pass this if a page needs custom
                                      merging beyond the current query string.

    Attributes: any extra HTML attributes (e.g. additional classes) merge
    onto the outer <th> via $attributes->merge().

    Note: reads current sort state from request('sort') / request('direction')
    directly, so no need to pass $s/$d in — just drop it into a table header
    on any page whose route accepts sort/direction query params.

    Example:
        <x-sortable-th sort-key="net_pay" label="Net Pay" align="right" route="payroll.index" />

         non-sortable columns don't need this component, use plain <th> 
        <th>Employee</th>
--}}

@props([
    'sortKey',
    'label',
    'align'  => 'left',
    'route',
    'params' => null,
])

@php
    $params  ??= request()->except(['sort', 'direction', 'page']);
    $s       = request('sort');
    $d       = request('direction', 'asc');
    $active  = $s === $sortKey;
    $nextDir = ($active && $d === 'asc') ? 'desc' : 'asc';
    $url     = route($route, array_merge($params, ['sort' => $sortKey, 'direction' => $nextDir]));
   $upCol   = ($active && $d === 'asc')  ? 'text-accent' : 'text-success-content';
$dnCol   = ($active && $d === 'desc') ? 'text-accent' : 'text-success-content';
@endphp

<th {{ $attributes->merge(['class' => 'text-' . $align]) }}>
    <a href="{{ $url }}" class="inline-flex items-center gap-1 tracking-wider text-success-content">
        {{ $label }}
        <span class="inline-flex flex-col leading-none">
            <i class="icon-[tabler--caret-up] text-[9px] {{ $upCol }}"></i>
            <i class="icon-[tabler--caret-down] text-[9px] {{ $dnCol }}"></i>
        </span>
    </a>
</th>