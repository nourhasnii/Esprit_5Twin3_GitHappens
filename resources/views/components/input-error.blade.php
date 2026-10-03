@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'field-validation-error text-sm text-red-500/90 space-y-1 mt-1.5']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
