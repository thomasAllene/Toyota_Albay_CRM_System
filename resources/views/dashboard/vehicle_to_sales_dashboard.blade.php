@extends('components.app')
@section('content')

{{-- Title Header --}}
<div class="card bg-dark mb-5">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md d-flex align-items-center">
                <i class='bx bxs-dashboard text-white' style="font-size: 24px;">&nbsp;</i>
                <h4 class="text-white mb-0">Vehicle to Sales</h4>
            </div>
        </div>
    </div>
</div>

{{-- Navlink Include --}}
@include('dashboard.dashboard_navlink')

{{-- Start Date - End Date Filter Group --}}
<div class="row mb-4">
    <div class="col-md d-flex justify-content-end gap-4">
        <div class="form-group text-end">
            <label for="defaultFormControlInput" class="form-label"><small>Select Start to End Date</small></label>
            <input type="text" id="date-range-picker" class="form-control form-control-sm" placeholder="Filter Date">
        </div>
    </div>
</div>

{{-- Card Deliveriesx Releases --}}
<div class="row mb-4">
    <div class="col-md">
        <div class="row">
            <div class="col-md">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="">
                                <label class="fs-4 fw-bold" style="color: #ff0055">Total Deliveries</label><br>
                                <small>Total number of Deliveries</small>
                            </div>
                            <h1 class="fw-bold" id="deliveriesCountCard" style="color: #ff0055">0</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-2">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                {{-- <h5 class="" style="color: #ff0055;">Daily Deliveries</h5> --}}
                <h6 class="">Month of: &nbsp; <b style="color: #ff0055;" id="deliveriesMonthLabel">Current</b></h6>
                <div id="dailyDeliveriesChart"></div>
            </div>
        </div>
    </div>
</div>

<div class="divider">
    <div class="divider-text"><i class='bx bxs-car'></i></div>
</div>

<div class="row mb-4 mt-2">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="">
                        <label class="fs-4 fw-bold" style="color: #ff0055">Total Releases</label><br>
                        <small>Total number of Releases (Posted & Released)</small>
                    </div>
                    <h1 class="fw-bold" id="releasesCountCard" style="color: #ff0055">0</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-2">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                {{-- <h5 class="" style="color: #ff0055;">Daily Deliveries</h5> --}}
                <h6 class="">Month of: &nbsp; <b style="color: #ff0055;" id="releasesMonthLabel">Current</b></h6>
                <div id="dailyReleasesChart"></div>
            </div>
        </div>
    </div>
</div>
@endsection



@section('components.specific_page_scripts')
<script>
    // Add this function to get current month and year
    function setCurrentMonthYear() {
        const currentDate = new Date();
        const monthYear = currentDate.toLocaleDateString('en-US', {
            month: 'long',
            year: 'numeric'
        });
        $('#deliveriesMonthLabel').text(monthYear);
        $('#releasesMonthLabel').text(monthYear);
    }

    // Initialize flatpickr for date range picker
    flatpickr("#date-range-picker", {
        mode: "range",
        dateFormat: "Y-m-d",
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                const startDate = selectedDates[0];
                const endDate = selectedDates[1];

                if (selectedDates[1] <= selectedDates[0]) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning!',
                        text: 'Please select a valid date range.',
                    });
                } else {
                    // Update month labels
                    const monthName = startDate.toLocaleDateString('en-US', {
                        month: 'long',
                        year: 'numeric'
                    });
                    $('#deliveriesMonthLabel').text(monthName);
                    $('#releasesMonthLabel').text(monthName);

                    getReleasedToday();
                    totalDeliveriesToday();
                    getDailyDeliveries();
                    getDailyReservation();
                }
            }
        },
        onReady: function (selectedDates, dateStr, instance) {
            // Create a "Clear" button
            const clearButton = document.createElement("button");
            clearButton.innerHTML = "Clear";
            clearButton.classList.add("clear-btn");

            // Create a "Close" button
            const closeButton = document.createElement("button");
            closeButton.innerHTML = "Close";
            closeButton.classList.add("close-btn");

            // Append the buttons to the flatpickr calendar
            instance.calendarContainer.appendChild(clearButton);
            instance.calendarContainer.appendChild(closeButton);

            // Add event listener to clear the date and reload the tables
            clearButton.addEventListener("click", function () {
                instance.clear(); // Clear the date range
                setCurrentMonthYear(); // Show current month and year instead of "Current"

                getReleasedToday();
                totalDeliveriesToday();
                getDailyDeliveries();
                getDailyReservation();
            });

            // Add event listener to close the calendar
            closeButton.addEventListener("click", function () {
                instance.close(); // Close the flatpickr calendar
            });
        }
    });

    // Set current month and year on page load
    setCurrentMonthYear();

    function getReleasedToday() {
        $.ajax({
            url: '{{ route("dashboard.vehicle-to-sales-dashboard.getReleasedToday") }}', // Adjust the route as necessary
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val(),
            },
            success: function(response) {
                if (response.releasedCount !== undefined) {
                    $('#releasesCountCard').text(response.releasedCount); // Update the count in the HTML
                }
            },
            error: function(xhr) {
                console.error('Error fetching transaction count:', xhr);
            }
        });
    }
    getReleasedToday();

    function totalDeliveriesToday() {
        $.ajax({
            url: '{{ route("dashboard.vehicle-to-sales-dashboard.totalDeliveriesToday") }}', // Adjust the route as necessary
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val(),
            },
            success: function(response) {
                if (response.deliveryCount !== undefined) {
                    $('#deliveriesCountCard').text(response.deliveryCount); // Update the count in the HTML
                }
            },
            error: function(xhr) {
                console.error('Error fetching transaction total:', xhr);
            }
        });
    }
    totalDeliveriesToday();

    function getDailyDeliveries(){
        $.ajax({
            url: "{{ route('dashboard.vehicle-to-sales-dashboard.getDailyDeliveries') }}", // Replace with actual route
            method: "GET",
            data: {
                date_range: $('#date-range-picker').val(),
            },
            success: function (data) {
                renderChart(data);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    var DailyDeliveries = null;

    function renderChart(deliveryData) {
            var options = {
            series: [{
                name: 'Deliveries',
                data: deliveryData
            }],
            chart: {
                height: 350,
                type: 'bar',
            },
            plotOptions: {
                bar: {
                    borderRadius: 10,
                    dataLabels: {
                        position: 'top',
                    },
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val;
                },
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ["#ff0055"]
                }
            },
            colors: ['#282830'],
            xaxis: {
                categories: Array.from({ length: deliveryData.length }, (_, i) => `D${i + 1}`),
                title: {
                    text: "DAILY DELIVERIES",
                    style: { fontSize: '14px', fontWeight: 'bold' }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return val;
                    }
                }
            }
        };

        if (DailyDeliveries) {
            DailyDeliveries.destroy();
        }

        DailyDeliveries = new ApexCharts(document.querySelector("#dailyDeliveriesChart"), options);
        DailyDeliveries.render();
    }

    getDailyDeliveries();

    function getDailyReservation(){
        $.ajax({
            url: "{{ route('dashboard.vehicle-to-sales-dashboard.getDailyReservation') }}", // Replace with actual route
            method: "GET",
            data: {
                date_range: $('#date-range-picker').val(),
            },
            success: function (data) {
                renderReservationChart(data);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    var DailyReservations = null;

    function renderReservationChart(reservationData) {
            var options = {
            series: [{
                name: 'Reservation',
                data: reservationData
            }],
            chart: {
                height: 350,
                type: 'bar',
            },
            plotOptions: {
                bar: {
                    borderRadius: 10,
                    dataLabels: {
                        position: 'top',
                    },
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val;
                },
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ["#ff0055"]
                }
            },
            colors: ['#282830'],
            xaxis: {
                categories: Array.from({ length: reservationData.length }, (_, i) => `D${i + 1}`),
                title: {
                    text: "DAILY RESERVATION",
                    style: { fontSize: '14px', fontWeight: 'bold' }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return val;
                    }
                }
            }
        };

        if (DailyReservations) {
            DailyReservations.destroy();
        }

        DailyReservations = new ApexCharts(document.querySelector("#dailyReleasesChart"), options);
        DailyReservations.render();
    }

    getDailyReservation();

</script>
@endsection
