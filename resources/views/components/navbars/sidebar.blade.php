@if (auth()->user()->isApplicant())
@include('components.navbars.applicant-sidebar')
@elseif (auth()->user()->isHod())
@include('components.navbars.hod-sidebar')

@endif
