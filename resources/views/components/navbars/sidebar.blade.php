@if (auth()->user()->isApplicant())
@include('components.navbars.applicant-sidebar')
@endif

@if (auth()->user()->isHod())
@include('components.navbars.hod-sidebar')

@endif
@if (auth()->user()->isAdmin())
@include('components.navbars.admin-sidebar')

@endif
@if (auth()->user()->isCit())
@include('components.navbars.cit-sidebar')

@endif
