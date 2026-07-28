@props([
    'name',
    'label' => null,
    'required' => false,
    'help' => null,
    'wrapperClass' => 'fieldset',
    'labelClass' => 'label text-xs font-semibold uppercase tracking-wider text-base-content/60',
    'errorClass' => 'label text-red-600 text-xs mt-1',
    'helpClass' => 'text-base-content/40 text-xs mt-1',
])

<div {{ $attributes->merge(['class' => $wrapperClass]) }}>
    @if ($label)
        <label class="{{ $labelClass }}">
            {{ $label }} @if ($required) <span class="text-red-600">*</span> @endif
        </label>
    @endif

    {{ $slot }}

    @if ($help)
        <p class="{{ $helpClass }}">{{ $help }}</p>
    @endif

    @error($name)
        <p class="{{ $errorClass }}">{{ $message }}</p>
    @enderror
</div>
