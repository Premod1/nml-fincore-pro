@php
    $mode = config('fincore-pro.mode', 'embedded');
    $erpLayout = config('fincore-pro.erp_layout', 'layouts.app');
    $erpSection = config('fincore-pro.erp_section', 'content');
    $useStandalone = ($mode === 'standalone') || !view()->exists($erpLayout);
    $parentLayout = $useStandalone ? 'fincore-pro::layouts.standalone' : $erpLayout;
    $targetSection = $useStandalone ? 'fincore_standalone_body' : $erpSection;
@endphp

@extends($parentLayout)

@section($targetSection)
    @include('fincore-pro::partials.styles')

    <div class="fincore-scope">
        @if(config('fincore-pro.theme.show_sub_navbar', true))
            @include('fincore-pro::partials.sub-navbar')
        @endif

        @if(session('fincore_success'))
            <div class="fc-alert fc-alert-success">
                <span><strong>Success:</strong> {{ session('fincore_success') }}</span>
            </div>
        @endif

        @if(session('fincore_error'))
            <div class="fc-alert fc-alert-danger">
                <span><strong>Error:</strong> {{ session('fincore_error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="fc-alert fc-alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="fc-body">
            @yield('fincore_content')
        </div>
    </div>
@endsection
