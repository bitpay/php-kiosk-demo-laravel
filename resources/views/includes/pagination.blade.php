@php
    /** @var Illuminate\Contracts\Pagination\LengthAwarePaginator $lengthAwarePaginator **/
@endphp

<nav class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
     aria-label="Pagination">
    <div class="hidden sm:block">
        <p class="text-sm text-gray-700">
            <span class="font-medium">Showing {{ $lengthAwarePaginator->firstItem() }}</span>
            <span class="font-medium">to {{ $lengthAwarePaginator->lastItem() }}</span>
            <span class="font-medium">of {{ $lengthAwarePaginator->total() }} results</span>
        </p>
    </div>

    <div class="flex flex-1 justify-between sm:justify-end">
        <a href="{{ $lengthAwarePaginator->previousPageUrl() }}"
           class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
        <a href="{{ $lengthAwarePaginator->nextPageUrl() }}"
           class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
    </div>
</nav>
