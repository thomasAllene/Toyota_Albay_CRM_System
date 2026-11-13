@extends('components.app')

@section('content')


{{-- Title Header --}}
<div class="card bg-dark shadow-none mb-5">
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

{{-- Navlink Include --}}
@include('dashboard.dashboard_navlink')

{{-- Start Date - End Date Filter Group --}}
<div class="row mb-4">
    <div class="col-md d-flex justify-content-end gap-4">
        <div class="form-group text-end">
            <label for="defaultFormControlInput" class="form-label"><small>Select Start to End Date</small></label>
            <input type="text" id="date-range-picker" class="form-control form-control-sm" placeholder="Filter Date">
        </div>
        {{-- @if(in_array(Auth::user()->usertype->name, ['SuperAdmin', 'General Manager', 'Sales Admin Staff'])) --}}
        <div class="form-group text-end @if(!in_array(Auth::user()->usertype->name, ['SuperAdmin', 'General Manager', 'Sales Admin Staff']))  d-none @endif">
            <label for="defaultSelect" class="form-label"><small>Filter Group</small></label>
            <select id="selectGroup" class="form-control form-select-sm">
            </select>
        </div>
        {{-- @endif --}}
        @if(!in_array(Auth::user()->usertype->name, ['Agent']))
        <div class="form-group text-end">
            <label for="defaultSelect" class="form-label"><small>Filter Agent</small></label>
            <select id="filterAgent" class="form-control form-select-sm">
            </select>
        </div>
        @endif

    </div>
</div>

<div class="row mb-4">
    {{-- Total Released Units Card --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-0" style="color: #ff0055;">Units Released <small>(As of This Month)</small></h5>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex flex-column align-items-center gap-1">
                        <h1 class="fw-bold" id="totalRelease" style="color: #ff0055;"></h1>
                    </div>
                </div>
                <ul class="p-0 m-0">
                    <li class="d-flex align-items-center border-bottom mb-5">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-dark"><i class="icon-base bx bx-group"></i></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                            <div class="me-2">
                                <h6 class="mb-0">GROUP</h6>
                                <small>Selected Group</small>
                            </div>
                        <div class="user-progress">
                            <h5 class="mb-0" style="color: #ff0055;" id="group">All Group</h5>
                        </div>
                        </div>
                    </li>
                    <li class="d-flex align-items-center border-bottom mb-5">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-dark"><i class="icon-base bx bx-calendar"></i></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0">MONTH</h6>
                            <small>Selected Month</small>
                        </div>
                        <div class="user-progress">
                            <h5 class="mb-0" style="color: #ff0055;" id="monthRange">Present</h5>
                        </div>
                    </li>
                    <li class="d-flex align-items-center border-bottom mb-5">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-dark"><i class="icon-base bx bx-calendar-star"></i></span>
                        </div>
                        <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0">YEAR</h6>
                            <small>Selected Year</small>
                        </div>
                        <div class="user-progress">
                            <h5 class="mb-0" style="color: #ff0055;" id="year">2025</h5>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Total Releases Bar Chart --}}
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="" style="color: #ff0055;">Monthly Released Units</h5>
                <div id="totalReleasesBarChart"></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    {{-- Transaction Type Pie Graph Container --}}
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="col-md">
                    <h5 class="mb-0" style="color: #ff0055;">Transaction Type</h5>
                    <div id="transactionTypePieGraph" class="d-flex justify-content-center h-100"></div>
                </div>
            </div>
        </div>
    </div>
    {{-- Bank Pie Graph Container --}}
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="col-md">
                    <h5 class="mb-0" style="color: #ff0055;">Bank</h5>
                    <div id="bankPieGraph" class="d-flex justify-content-center h-100"></div>
                </div>
            </div>
        </div>
    </div>
    {{-- Source Pie Graph Container --}}
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="col-md">
                    <h5 class="mb-0" style="color: #ff0055;">Source</h5>
                    <div id="sourePieGraph" class="d-flex justify-content-center h-100"></div>
                </div>
            </div>
        </div>
    </div>
    {{-- Gender Pie Graph Container --}}
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="col-md">
                    <h5 class="mb-0" style="color: #ff0055;">Gender</h5>
                    <div id="genderPieGraph" class="d-flex justify-content-center h-100"></div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('components.specific_page_scripts')
<script>

    // Loader
    function showLoader() {
        Swal.fire({
            title: 'Loading...',
            text: 'Please wait while we fetch the data.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    function hideLoader() {
        Swal.close();
    }

    const userTeamId = {{ Auth::user()->team_id ?? 'null' }};
    // Load the Groups
    function loadTeams(selectedTeamId = null) {
        $.ajax({
            url: '{{ route("teams.list") }}',
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Select Group</option>';
                data.forEach(function(team) {
                    options += `<option value="${team.id}">${team.name}</option>`;
                });
                $('#selectGroup').html(options);

                if (selectedTeamId) {
                    $('#selectGroup').val(selectedTeamId).trigger('change');
                }
            }
        });
    }
    loadTeams(userTeamId);

    function getAgent(){
        $.ajax({
            url: '{{ route('leads.getAgent') }}',
            type: 'GET',
            data : {
                team_id: $('#selectGroup').val()
            },
            dataType: 'json',
            success: function(data) {
                let agentSelect = $('#filterAgent');
                agentSelect.empty();
                agentSelect.append('<option value="">Filter Agent...</option>');
                data.forEach(function(item) {
                    agentSelect.append(`<option value="${item.id}">${item.first_name} ${item.last_name}</option>`);
                });

                // agentSelect.select2({
                //     placeholder: "Select an option",
                //     allowClear: true
                // });

            },
            error: function(error) {
                console.error('Error loading team:', error);
            }
        });
    }

    @if(in_array(Auth::user()->usertype->name, ['Agent', 'Group Manager']))
    $(document).ready(function () {
        getAgent();
    });
    @endif


    $(document).ready(function () {
      // Event listeners for filter dropdowns
        $('#selectGroup').on('change', function() {
            getAgent();
        });
    });


    document.addEventListener('DOMContentLoaded', function () {
        // Initialize flatpickr for date range picker
        flatpickr("#date-range-picker", {
            mode: "range",
            dateFormat: "m/d/Y",
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    const startDate = selectedDates[0];
                    const endDate = selectedDates[1];

                    showLoader();

                    if (selectedDates[1] <= selectedDates[0]) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Warning!',
                            text: 'Please select a valid date range.',
                        });
                    } else {


                        releasedCount();
                        fetchMonthlyReleasedCount();
                        fetchReleasePerTransType();
                        fetchBankData();
                        fetchSourceData();
                        fetchGenderData();
                    }

                    // Update the month and year display
                    const startMonth = startDate.toLocaleString('default', { month: 'short' });
                    const endMonth = endDate.toLocaleString('default', { month: 'short' });
                    const startYear = startDate.getFullYear();
                    const endYear = endDate.getFullYear();

                    if (startMonth === endMonth && startYear === endYear) {
                        document.getElementById('monthRange').textContent = startMonth;
                    } else {
                        const monthRange = `${startMonth} - ${endMonth}`;
                        document.getElementById('monthRange').textContent = monthRange;
                    }

                    if (startYear === endYear) {
                        document.getElementById('year').textContent = startYear;
                    } else {
                        document.getElementById('year').textContent = `${startYear} - ${endYear}`;
                    }

                    hideLoader();
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
                    releasedCount();
                    fetchMonthlyReleasedCount();
                    fetchReleasePerTransType();
                    fetchBankData();
                    fetchSourceData();
                    fetchGenderData();
                });

                // Add event listener to close the calendar
                closeButton.addEventListener("click", function () {
                    instance.close(); // Close the flatpickr calendar
                });
            }
        });

        const currentDate = new Date();
        const currentMonth = currentDate.toLocaleString('default', { month: 'short' });
        const currentYear = currentDate.getFullYear();
        document.getElementById('monthRange').textContent = currentMonth;
        document.getElementById('year').textContent = currentYear;

        // Event listener for group selection
        $('#selectGroup').on('change', function () {
            showLoader();

            const selectedGroup = this.options[this.selectedIndex].text;
            document.getElementById('group').textContent = selectedGroup || 'All Group';

            releasedCount();
            fetchMonthlyReleasedCount();
            fetchReleasePerTransType();
            fetchBankData();
            fetchSourceData();
            fetchGenderData();
            hideLoader();
        });

         // Event listener for group selection
         $('#filterAgent').on('change', function () {
            showLoader();
            releasedCount();
            fetchMonthlyReleasedCount();
            fetchReleasePerTransType();
            fetchBankData();
            fetchSourceData();
            fetchGenderData();
            hideLoader();
        });
    });


    function releasedCount() {
        $.ajax({
            url: '{{ route("api.released-data") }}', // Adjust the route as necessary
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val(),
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                if (response.releasedCount !== undefined) {
                    $('#totalRelease').text(response.releasedCount); // Update the count in the HTML
                }
            },
            error: function(xhr) {
                console.error('Error fetching transaction count:', xhr);
            }
        });
    }
    releasedCount();

    // Active Nav Tab
    $('.btn-group .btn').on('click', function (e) {
        e.preventDefault();

        // Clear the date range picker
        $('#date-range-picker').val(''); // Clear the date range input

        // Reload the table without resetting the paging
        //  inquiryTable.ajax.reload(null, false);

        // Get the route from the clicked button
        //  var route = $(this).data('route');
        //  inquiryTable.ajax.url(route).load();

        // Remove 'active' class from all buttons
        $('.btn-group .btn').removeClass('active');

        // Add 'active' class to the clicked button
        $(this).addClass('active');
    });

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

    // Bar Chart for Monthly Releases
    var releasedBarChart = null;

    // Fetch the monthly released count data
    function fetchMonthlyReleasedCount() {
        $.ajax({
            url: '{{ route("api.bar-chart-monthly-release") }}',
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val(),
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                renderBarChart(response.monthlyData);
            }
        });
    }

    // Render the bar chart with the fetched data
    function renderBarChart(monthlyData) {
        var options = {
            series: [{
                name: 'Releases',
                data: monthlyData
            }],
            chart: {
                height: 350,
                type: 'bar',
            },
            plotOptions: {
                bar: {
                    borderRadius: 5,
                    dataLabels: {
                        position: 'top', // top, center, bottom
                    },
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val; // Display the actual value
                },
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ["#ff0055"] // Data label color
                }
            },
            colors: ['#282830'], // Set the base bar color
            states: {
                hover: {
                    filter: {
                        type: 'lighten', // Lighten the color on hover
                        value: 0.2 // Adjust the amount of lightening
                    }
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: 'darken', // Darken the color on selection
                        value: 0.3 // Adjust the amount of darkening
                    }
                }
            },
            xaxis: {
                categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                position: 'top',
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                crosshairs: {
                    fill: {
                        type: 'gradient',
                        gradient: {
                            colorFrom: '#D8E3F0',
                            colorTo: '#BED1E6',
                            stops: [0, 100],
                            opacityFrom: 0.4,
                            opacityTo: 0.5,
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                }
            },
            yaxis: {
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false,
                },
                labels: {
                    show: true,
                    formatter: function (val) {
                        return Math.round(val); // Ensure the value is rounded to a whole number
                    }
                }
            },
            title: {
                text: '',
                floating: true,
                offsetY: 330,
                align: 'center',
                style: {
                    color: '#ff0055'
                }
            }
        };

        if (releasedBarChart) {
            releasedBarChart.updateSeries([{
                name: 'Releases',
                data: monthlyData
            }]);
        } else {
            releasedBarChart = new ApexCharts(document.querySelector("#totalReleasesBarChart"), options);
            releasedBarChart.render();
        }

    }

    // Call the function to fetch and render the bar chart
    fetchMonthlyReleasedCount();

    //End Bar Chart for Monthly Releases

    // Pie Chart for Transaction Type
    var transactionTypePieChart = null;

    function fetchReleasePerTransType() {
        $.ajax({
            url: '{{ route("api.pie-per-transaction-type") }}',
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val(), // Add the date range parameter
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                const labels = Object.keys(response);
                const data = Object.values(response);
                renderTransactionTypePieChart(labels, data);
            }
        });
    }

    function renderTransactionTypePieChart(labels, data) {
        var options = {
            series: data,
            chart: {
                width: 410,
                type: 'pie',
            },
            labels: labels,
            colors: ['#282830', '#ff0022', '#8a8c8e'], // Custom colors for the pie chart
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val; // Display whole numbers in the tooltip
                    }
                }
            },
            dataLabels: {
                formatter: function (val, opts) {
                    return opts.w.config.series[opts.seriesIndex]; // Display the actual value
                },
                style: {
                    fontSize: '24px', // Adjust the font size here
                    fontFamily: 'Arial, sans-serif', // Optional: Customize the font family
                    fontWeight: 'bold', // Optional: Customize the font weight
                }
            },
            legend: {
                show: true, // Show the legend
                position: 'bottom', // Position the legend at the bottom
                labels: {
                    useSeriesColors: true // Ensure legend uses the segment colors
                }
            },
        };


        if (transactionTypePieChart) {
            transactionTypePieChart.updateSeries(data);
        } else {
            transactionTypePieChart = new ApexCharts(document.querySelector("#transactionTypePieGraph"), options);
            transactionTypePieChart.render();
        }


    }

    // Fetch the release count per transaction type
    fetchReleasePerTransType();

    //END Pie Chart for Transaction Type


    // Fetch the bank
    var bankPieChart = null;
    function fetchBankData() {
        $.ajax({
            url: '{{ route("api.released-data-by-bank") }}',
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val(),
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                const labels = response.map(item => item.bank_name.bank_name); // Access the nested 'bank_name' field
                const data = response.map(item => item.count);
                renderBankPieChart(labels, data);
            }
        });
    }

    function renderBankPieChart(labels, data) {
        var options = {
            series: data,
            chart: {
                width: 410,
                type: 'pie',
            },
            labels: labels,
            colors: [
            '#282830', // Dark Gray (Toyota's professional tone)
            '#ff0022', // Toyota Red (primary brand color)
            '#5f5f72', // Light Gray (neutral accent)
            '#8a8c8e', // Medium Gray (complementary shade)
            '#660015', // Dark Red (deeper shade of Toyota Red)
            '#b6b4c3',  // Soft Gray (more visible than #f5f5f5)
        ],
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }],
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val; // Display whole numbers in the tooltip
                    }
                }
            },
            dataLabels: {
                style: {
                    fontSize: '24px', // Adjust the font size here
                    fontFamily: 'Arial, sans-serif', // Optional: Customize the font family
                    fontWeight: 'bold', // Optional: Customize the font weight
                },
                formatter: function (val, opts) {
                    return opts.w.config.series[opts.seriesIndex]; // Display the actual value
                }
            },
            legend: {
                show: true, // Show the legend
                position: 'bottom', // Position the legend at the bottom
                labels: {
                    useSeriesColors: true // Ensure legend uses the segment colors
                }
            },
        };

        if (bankPieChart) {
            bankPieChart.updateSeries(data);
        } else {
            bankPieChart = new ApexCharts(document.querySelector("#bankPieGraph"), options);
            bankPieChart.render();
        }

    }

    // Call the function to fetch and render the pie chart
    fetchBankData();

    //End Pie Chart for Bank


    // Pie Graph Source
    var sourcePieChart = null;

    function fetchSourceData() {
        $.ajax({
            url: '{{ route("api.released-data-by-source") }}',
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val(),
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                const labels = response.map(item => item.source); // Access the nested 'bank_name' field
                const data = response.map(item => item.count);
                renderSourcePieChart(labels, data);
            }
        });
    }

    function renderSourcePieChart(labels, data) {
        var options = {
            series: data,
            chart: {
                width: 410,
                type: 'pie',
            },
            labels: labels,
            colors: [
                '#282830', // Dark Gray (Toyota's professional tone)
                '#ff0022', // Toyota Red (primary brand color)
                '#5f5f72', // Light Gray (neutral accent)
                '#8a8c8e', // Medium Gray (complementary shade)
                '#660015', // Dark Red (deeper shade of Toyota Red)
                '#b6b4c3',  // Soft Gray (more visible than #f5f5f5)
            ],
            chart: {
                width: 405,
                type: 'donut',
            },
            dataLabels: {
                formatter: function (val, opts) {
                    // Display the actual value instead of percentage
                    return opts.w.config.series[opts.seriesIndex];
                },
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold',
                }
            },
            legend: {
                show: true, // Show the legend
                position: 'bottom', // Position the legend at the bottom
                labels: {
                    useSeriesColors: true // Ensure legend uses the segment colors
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        if (sourcePieChart) {
            sourcePieChart.updateSeries(data);
        } else {
            sourcePieChart = new ApexCharts(document.querySelector("#sourePieGraph"), options);
            sourcePieChart.render();
        }
    };


    fetchSourceData();

    //End Pie Graph Source

    // Pie Graph Gender
    var genderPieChart = null;

    function fetchGenderData() {
        $.ajax({
            url: '{{ route("api.released-data-by-gender") }}',
            type: 'GET',
            data: {
                date_range: $('#date-range-picker').val(), // Add the date range parameter
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                const labels = Object.keys(response);
                const data = Object.values(response);
                renderGenderPieChart(labels, data);
            }
        });
    }

    function renderGenderPieChart(labels, data) {
        var options = {
            series: data,
            chart: {
                width: 410,
                type: 'pie',
            },
            labels: labels,
            colors: [
                '#282830', // Dark Gray (Toyota's professional tone)
                '#ff0022', // Toyota Red (primary brand color)
            ],
            chart: {
                width: 400,
                type: 'donut',
            },
            dataLabels: {
                formatter: function (val, opts) {
                    // Display the actual value instead of percentage
                    return opts.w.config.series[opts.seriesIndex];
                },
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold',
                }
            },
            legend: {
                show: true, // Show the legend
                position: 'bottom', // Position the legend at the bottom
                labels: {
                    useSeriesColors: true // Ensure legend uses the segment colors
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        if (genderPieChart) {
            genderPieChart.updateSeries(data);
        } else {
            genderPieChart = new ApexCharts(document.querySelector("#genderPieGraph"), options);
            genderPieChart.render();
        }

    }

    fetchGenderData();

</script>
@endsection


