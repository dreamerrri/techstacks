@props(['maxHeight' => '60vh'])

<div class="overflow-x-auto overflow-y-auto hidden md:block isolate relative " style="max-height: {{ $maxHeight }}">
    <table class="table table-hover table-fixed w-full text-sm table-borderless">
        <thead class="[&>tr>th]:sticky [&>tr>th]:top-0 [&>tr>th]:z-20 [&>tr>th]:bg-success [&>tr>th]:text-success-content [&>tr>th]:text-xs [&>tr>th]:border-b-2 [&>tr>th]:border-success-content/20">
            <tr>
                {{ $head }}
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>