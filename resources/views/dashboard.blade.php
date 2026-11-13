@extends('components.app')

@section('content')

{{-- Title Header --}}
<div class="card bg-dark shadow-none mb-4">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md d-flex align-items-center">
                <i class='bx bxs-dashboard text-white' style="font-size: 24px;">&nbsp;</i>
                <h4 class="text-white mb-0">Dashboard</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md">
                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center gap-1">
                        {{-- <i class='bx bx-calendar fs-4 text-warning'></i> --}}
                        <div id="liveDate" class="text-warning fs-6"></div>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        {{-- <i class='bx bx-time-five fs-4 text-warning'></i> --}}
                        <div id="liveTime" class="text-warning fs-6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <div class="card bg-label-warning shadow-none">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md d-flex justify-content-center">
                <div class="row"><i class='bx bxs-dashboard fs-1'></i></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md d-flex justify-content-center">
                <div class="row"><h1 class="text-warning">"Dashboard Under Development"</h1></div>
            </div>
        </div>
    </div>
</div> --}}

<!-- Centered align Cards -->
<div class="row g-6 mb-6">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-border-shadow-dark h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-dark"><i class="icon-base bx bxs-truck icon-lg"></i></span>
              </div>
              <h4 class="mb-0">42</h4>
            </div>
            <h5 class="mb-2">Units Released Today</h5>
            <p class="mb-0">
              {{-- <span class="text-heading fw-medium me-2">+18.2%</span> --}}
              <span class="text-secondary">January 13, 2024 (Monday)</span>
            </p>
          </div>
        </div>
    </div>
</div>
<!--/ Centered align Cards -->




@endsection


@section('components.specific_page_scripts')
<script>
    function updateTimeAndDate() {
    const now = new Date();

    // Format time (HH:MM:SS)
    const time = now.toLocaleTimeString();

    // Format date (e.g., Monday, December 16, 2024)
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const date = now.toLocaleDateString(undefined, dateOptions);

    // Update the DOM using jQuery
    $('#liveTime').text(time);
    $('#liveDate').text(date);
}

// Update time and date every second
setInterval(updateTimeAndDate, 1000);

// Initial call to display immediately
updateTimeAndDate();

</script>
@endsection


