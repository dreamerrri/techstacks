@props(['maxHeight' => '60vh'])

<div class="overflow-x-auto overflow-y-auto hidden md:block isolate relative " style="max-height: {{ $maxHeight }}">
    <table class="table table-hover table-fixed w-full text-sm">
        <thead class="[&>tr>th]:sticky [&>tr>th]:top-0 [&>tr>th]:z-20 [&>tr>th]:bg-primary [&>tr>th]:text-primary-content [&>tr>th]:text-xs border-x border-base-300">
            <tr>
                {{ $head }}
            </tr>
        </thead>
       {{--         <tbody class="[&>tr]:outline [&>tr]:outline-2 [&>tr]:-outline-offset-1 [&>tr]:outline-transparent [&>tr]:hover:outline-primary [&>tr]:transition-colors">
--}}
       

        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>