@extends('components.app')
@section('content')

{{-- Title Header --}}
<div class="card bg-dark mb-5">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md d-flex align-items-center">
                <i class='bx bxs-dashboard text-white' style="font-size: 24px;">&nbsp;</i>
                <h4 class="text-white mb-0">Sales Funnel Management</h4>
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
            <label for="startDate" class="form-label"><small>Select Start Date</small></label>
            <input type="text" id="startDate" class="form-control form-control-sm" placeholder="Start Date">
        </div>
        <div class="form-group text-end">
            <label for="endDate" class="form-label"><small>Select End Date</small></label>
            <input type="text" id="endDate" class="form-control form-control-sm" placeholder="End Date">
        </div>
        <div class="form-group text-end @if(!in_array(Auth::user()->usertype->name, ['SuperAdmin', 'General Manager', 'Sales Admin Staff']))  d-none @endif">
            <label for="defaultSelect" class="form-label"><small>Filter Group</small></label>
            <select id="selectGroup" class="form-control form-select-sm">
            </select>
        </div>
        @if(!in_array(Auth::user()->usertype->name, ['Agent']))
        <div class="form-group text-end">
            <label for="defaultSelect" class="form-label"><small>Filter Agent</small></label>
            <select id="filterAgent" class="form-control form-select-sm">
            </select>
        </div>
        @endif
    </div>
</div>

{{-- Card Inquiries and Unit in Inquries Count --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="">
                                <label class="fs-4 fw-bold" style="color: #ff0055">Total Inquiries</label><br>
                                <small>Total number of inquiries</small>
                            </div>
                            <h1 class="fw-bold" id="inquiriesCountCard" style="color: #ff0055">0</h1>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="">
                                <label class="fs-4 fw-bold" style="color: #ff0055">Total Units Inquired</label><br>
                                <small>Total number of units inquired</small>
                            </div>
                            <h1 class="fw-bold" id="unitInquiredCountCard" style="color: #ff0055">0</h1>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtered Date Card --}}
    <div class="col-md">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-4" style="color: #ff0055;">Top MP/Agent Rankings</h5>
                <ul class="p-0 m-0">
                    <li class="d-flex align-items-center border-bottom mb-5">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-dark"><i class="icon-base bx bx-calendar"></i></span>
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
                            <span class="avatar-initial rounded bg-dark"><i class="icon-base bx bx-calendar-star"></i></span>
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
</div>


<div class="row mb-4">
    {{-- Total Inquiries Bar Graph --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 style="color: #ff0055;">Total Inquiries</h5>
                <div id="totalInquiriesBarGraph"></div>
            </div>
        </div>
    </div>
    {{-- Total Rservation Bar Graph --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 style="color: #ff0055;">Total Reservation</h5>
                <div id="totalReservationBarGraph"></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 style="color: #ff0055;">Total Units Inquired</h5>
                <div class="" id="unitInquiredLineGraph"></div>
            </div>
        </div>
    </div>
</div>

@endsection




@section('components.specific_page_scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        // Initialize flatpickr for date range picker
        // flatpickr("#date-range-picker", {
        //     mode: "range",
        //     dateFormat: "m/d/Y",
        //     onChange: function (selectedDates, dateStr, instance) {
        //         if (selectedDates.length === 2) {
        //             const startDate = selectedDates[0];
        //             const endDate = selectedDates[1];

        //             showLoader();

        //             if (selectedDates[1] <= selectedDates[0]) {
        //                 Swal.fire({
        //                     icon: 'warning',
        //                     title: 'Warning!',
        //                     text: 'Please select a valid date range.',
        //                 });
        //             } else {

        //                 fetchInquiriesData();
        //                 fetchInquiriesCount();
        //                 fetchReservationCount();
        //                 fetchVehicleQuantity();

        //             }

        //             // Update the month and year display
        //             const startMonth = startDate.toLocaleString('default', { month: 'short' });
        //             const endMonth = endDate.toLocaleString('default', { month: 'short' });
        //             const startYear = startDate.getFullYear();
        //             const endYear = endDate.getFullYear();

        //             if (startMonth === endMonth && startYear === endYear) {
        //                 document.getElementById('monthRange').textContent = startMonth;
        //             } else {
        //                 const monthRange = `${startMonth} - ${endMonth}`;
        //                 document.getElementById('monthRange').textContent = monthRange;
        //             }

        //             if (startYear === endYear) {
        //                 document.getElementById('year').textContent = startYear;
        //             } else {
        //                 document.getElementById('year').textContent = `${startYear} - ${endYear}`;
        //             }

        //             hideLoader();
        //         }else{


        //         }
        //     },
        //     onReady: function (selectedDates, dateStr, instance) {
        //         // Create a "Clear" button
        //         const clearButton = document.createElement("button");
        //         clearButton.innerHTML = "Clear";
        //         clearButton.classList.add("clear-btn");

        //         // Create a "Close" button
        //         const closeButton = document.createElement("button");
        //         closeButton.innerHTML = "Close";
        //         closeButton.classList.add("close-btn");

        //         // Append the buttons to the flatpickr calendar
        //         instance.calendarContainer.appendChild(clearButton);
        //         instance.calendarContainer.appendChild(closeButton);

        //         // Add event listener to clear the date and reload the tables
        //         clearButton.addEventListener("click", function () {
        //             instance.clear(); // Clear the date range

        //             fetchInquiriesData();
        //             fetchInquiriesCount();
        //             fetchReservationCount();
        //             fetchVehicleQuantity();


        //         });

        //         // Add event listener to close the calendar
        //         closeButton.addEventListener("click", function () {
        //             instance.close(); // Close the flatpickr calendar
        //         });
        //     }
        // });

        // Initialize flatpickr for startDate and endDate
        flatpickr("#startDate", {
            dateFormat: "m/d/Y",
            onChange: function (selectedDates, dateStr, instance) {
                validateDateRange();
            }
        });

        flatpickr("#endDate", {
            dateFormat: "m/d/Y",
            onChange: function (selectedDates, dateStr, instance) {
                validateDateRange();
            }
        });

        function validateDateRange() {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);

            // if (end <= start) {
            //     Swal.fire({
            //         icon: 'warning',
            //         title: 'Warning!',
            //         text: 'Please select a valid date range.',
            //     });
            // } else {
                showLoader();

                // Fetch data based on the selected date range
                fetchInquiriesData();
                fetchInquiriesCount();
                fetchReservationCount();
                fetchVehicleQuantity();

                // Update the month and year display
                const startMonth = start.toLocaleString('default', { month: 'short' });
                const endMonth = end.toLocaleString('default', { month: 'short' });
                const startYear = start.getFullYear();
                const endYear = end.getFullYear();

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
            // }
        }
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
                    // Initialize Select2
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


        $(document).ready(function () {
        // Event listeners for filter dropdowns
            $('#selectGroup').on('change', function() {
                getAgent();
            });
        });


        const currentDate = new Date();
        const currentMonth = currentDate.toLocaleString('default', { month: 'short' });
        const currentYear = currentDate.getFullYear();
        document.getElementById('monthRange').textContent = currentMonth;
        document.getElementById('year').textContent = currentYear;

        // Event listener for group selection
        document.getElementById('selectGroup').addEventListener('change', function () {
            const selectedGroup = this.options[this.selectedIndex].text;
            document.getElementById('group').textContent = selectedGroup || 'All Group';
        });

        // Event listener for group selection
        $('#selectGroup').on('change', function () {
            showLoader();

            fetchInquiriesData();
            fetchInquiriesCount();
            fetchReservationCount();
            fetchVehicleQuantity();

            hideLoader();

        });
        $('#filterAgent').on('change', function () {
            showLoader();

            fetchInquiriesData();
            fetchInquiriesCount();
            fetchReservationCount();
            fetchVehicleQuantity();

            hideLoader();

        });
    });


    function fetchInquiriesData() {
        $.ajax({
            url: '{{ route("dashboard.getInquiryCount") }}',
            type: 'GET',
            data: {
                start_date: $('#startDate').val(),
                end_date: $('#endDate').val(),
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                $('#inquiriesCountCard').text(response.inquiryCount);
                $('#unitInquiredCountCard').text(response.quantityPerUnit);
            }
        });
    }

    fetchInquiriesData();

    // Total Inquiries Bar Graph
    var InquiryCount = null;

    function fetchInquiriesCount() {
        $.ajax({
            url: '{{ route("dashboard.fetchInquiryCount") }}',
            type: 'GET',
            data: {
                start_date: $('#startDate').val(),
                end_date: $('#endDate').val(),
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                renderInquiryCount(response.monthlyData);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching inquiry count:', error);
            },
        });
    }

    function renderInquiryCount(monthlyData) {
        var options = {
          series: [{
            name: "Desktops",
            data: monthlyData,
        }],
          chart: {
          height: 350,
          type: 'bar',
          zoom: {
            enabled: false
          }
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
          offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ["#ff0055"] // Data label color
                },
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
        stroke: {
          curve: 'straight'
        },
        title: {
                text: '',
                floating: true,
                offsetY: 330,
                position: 'top',
                align: 'center',
                style: {
                    color: '#ff0055'
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
            }
        };

        if (InquiryCount) {
            InquiryCount.updateSeries([{
                name: "Desktops",
                data: monthlyData,
            }]);
        } else {
            InquiryCount = new ApexCharts(document.querySelector("#totalInquiriesBarGraph"), options);
            InquiryCount.render();
        }

    }

    fetchInquiriesCount();

    // Total Reservation Bar Graph

    var reservationCount = null;

    function fetchReservationCount() {
        $.ajax({
            url: '{{ route("dashboard.fetchReservationCount") }}',
            type: 'GET',
            data: {
                start_date: $('#startDate').val(),
                end_date: $('#endDate').val(),
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                renderReservationCount(response.monthlyData);
            }
        });
    }


    function renderReservationCount(monthlyData){

        var options = {
            series: [{
                name: "Desktops",
                data: monthlyData,
            }],
            chart: {
            height: 350,
            type: 'bar',
            zoom: {
                enabled: false
            }
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
            offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#ff0055"] // Data label color
                    },
            },
            colors: ['#ff0055'], // Set the base bar color
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
            stroke: {
            curve: 'straight'
            },
            title: {
                    text: '',
                    floating: true,
                    offsetY: 330,
                    position: 'top',
                    align: 'center',
                    style: {
                        color: '#ff0055'
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
                }
        };

        if (reservationCount) {
            reservationCount.updateSeries([{
                name: "Desktops",
                data: monthlyData,
            }]);
        } else {
            // Create a new chart instance
            reservationCount = new ApexCharts(document.querySelector("#totalReservationBarGraph"), options);
            reservationCount.render();
        }

    }

    fetchReservationCount();


    // Total Inquiries in Inquiries Bar Graph
    function fetchVehicleQuantity() {
        $.ajax({
            url: '{{ route("dashboard.fetchVehicleQuantity") }}',
            type: 'GET',
            data: {
                start_date: $('#startDate').val(),
                end_date: $('#endDate').val(),
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function(response) {
                renderVehicleQuantityChart(response.inquiryCount);
                // console.log(response);
            }
        });
    }

    var unitCount = null;

    function renderVehicleQuantityChart(data) {
        const units = data.map(item => item.unit);
        const quantities = data.map(item => item.total_quantity);

        var options = {
            series: [{
                name: "Desktops",
                data: quantities
            }],
            chart: {
            height: 350,
            type: 'bar',
            zoom: {
                enabled: false
            }
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
            offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#ff0055"] // Data label color
                    },
            },
            colors: ['#8a8c8e'], // Set the base bar color
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
            stroke: {
            curve: 'straight'
            },
            title: {
                    text: '',
                    floating: true,
                    offsetY: 330,
                    align: 'center',
                    style: {
                        color: '#ff0055'
                    }
                },
            xaxis: {
            categories: units,
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
            }
        };

        if (unitCount) {
            unitCount.updateSeries([{
                name: "Desktops",
                data: quantities
            }]);
            unitCount.destroy();

        }
            // Create a new chart instance
            unitCount = new ApexCharts(document.querySelector("#unitInquiredLineGraph"), options);
            unitCount.render();
    }

    fetchVehicleQuantity();

</script>

@endsection
