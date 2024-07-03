@if (auth()->user()->isApplicant())
@include('components.navbars.applicant-sidebar')
@endif

@if (auth()->user()->isHod())
@include('components.navbars.hod-sidebar')

@endif
