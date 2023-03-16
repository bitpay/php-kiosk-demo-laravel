@section('head_scripts')
    <style type="text/tailwindcss">
        @layer components {
            .grid-status-new {
                @apply bg-gray-100 text-gray-800;
            }

            .grid-status-paid {
                @apply bg-yellow-100 text-yellow-800;
            }

            .grid-status-confirmed {
                @apply bg-blue-100 text-blue-800;
            }

            .grid-status-complete {
                @apply bg-green-100 text-green-800;
            }

            .grid-status-expired, .grid-status-invalid {
                @apply bg-red-100 text-red-800;
            }
        }
    </style>
@stop
