@extends('components.app')
@section('content')

{{-- Title Header --}}
<div class="card bg-dark mb-5">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md d-flex align-items-center">
                <i class='bx bxs-dashboard text-white' style="font-size: 24px;">&nbsp;</i>
                <h4 class="text-white mb-0">MP/Agent Ranking</h4>
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
        {{-- <div class="form-group text-end">
            <label for="defaultSelect" class="form-label"><small>Reset Filter</small></label><br>
            <button class="btn btn-sm btn-label-dark">Reset</button>
        </div> --}}
    </div>
</div>

{{-- Card & Table Graphs --}}
<div class="row mb-4">
    <div class="col-md">
        <h5>Top MP/Agent Rankings</h5>
        <div class="card" style="max-height: 640px; overflow-y: auto;">
            <div class="card-body">
                <div id="topAgentsContainer">
                    <!-- Top Agents will be displayed here -->
                </div>
            </div>
        </div>
    </div>
    <div class="col-md">
        <h5>Complete Ranking of MP/Agents</h5>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="rankingTable" class="table table-hover">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bar Chart --}}
<div class="row mb-4">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                <h5 style="color: #ff0055;">Top Performing MP/Agents by Units Released</h5>
                <div id="rankingBarChart"></div>
            </div>
        </div>
    </div>
</div>

@endsection



@section('components.specific_page_scripts')
<script>

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
                        fetchTopAgents();
                        rankingTable.ajax.reload(null, false);
                        fetchAgentData();
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
                    fetchAgentData();
                    fetchTopAgents();
                    rankingTable.ajax.reload(null, false);

                });

                // Add event listener to close the calendar
                closeButton.addEventListener("click", function () {
                    instance.close(); // Close the flatpickr calendar
                });
            }
        });

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

            // Event listener for group selection
            $('#selectGroup').on('change', function () {
                showLoader();

                fetchTopAgents();
                rankingTable.ajax.reload(null, false);
                fetchAgentData();

                hideLoader();

            });
            $('#filterAgent').on('change', function () {
                showLoader();

                fetchTopAgents();
                rankingTable.ajax.reload(null, false);
                fetchAgentData();

                hideLoader();

            });
        });


    function fetchTopAgents() {
        let url = '{{ route("dashboard.ranking-dashboard.topAgent") }}';

        $.ajax({
            url: url,
            method: 'GET',
            data: {
                date_range: $('#date-range-picker').val(),
                group: $('#selectGroup').val(),
                agent: $('#filterAgent').val()
            },
            success: function (response) {
                if (response.agents) {
                    const topAgents = response.agents;
                    let rank = 1;
                    let previousTotal = null;
                    let rankList = [];

                    topAgents.forEach((agent, index) => {
                        if (previousTotal !== null && agent.total !== previousTotal) {
                            rank++;
                        }
                        if (rank <= 3) {
                            rankList.push({
                                rank: rank,
                                agent: `${agent.agent.first_name} ${agent.agent.last_name}`,
                                total: agent.total
                            });
                        }
                        previousTotal = agent.total;
                    });

                    displayTopAgents(rankList);
                } else {
                    displayTopAgents([]);
                }
            },
            error: function () {
                displayTopAgents([]);
            }
        });
    }

    function displayTopAgents(rankList) {
        const topAgentsContainer = $('#topAgentsContainer');
        topAgentsContainer.empty();

        if (rankList.length > 0) {
            rankList.forEach(agent => {
                topAgentsContainer.append(`

                <div class="row mb-2 gy-3">
                    <div class="col-md-12">
                        <div class="card shadow-none border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class='bx bx-trophy fs-2' style="color: #ff0055"></i>
                                            <label class="fs-4 fw-bold" style="color: #ff0055">Top ${agent.rank} Agent</label><br>
                                        </div>
                                        <small>Agent with most released units</small>
                                    </div>
                                    <h3 class="fw-bold" id="top1Agent" style="color: #ff0055">${agent.agent}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                `);
            });
        } else {
            topAgentsContainer.append(`
                    <div class="col-md-12">
                        <div class="card shadow-none border">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class='bx bx-trophy fs-2' style="color: #ff0055"></i>
                                            <label class="fs-4 fw-bold" style="color: #ff0055">Top Agent</label><br>
                                        </div>
                                        <small>Agent with most released units</small>
                                    </div>
                                    <h3 class="fw-bold" id="top1Agent" style="color: #ff0055">No Data Found</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
    }

    fetchTopAgents();

        // Datatable Initilization
        const rankingTable = $('#rankingTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("dashboard.ranking-dashboard.topAgentList") }}',
                data: function(d) {
                    d.date_range = $('#date-range-picker').val();
                    d.group = $('#selectGroup').val();
                    d.agent = $('#filterAgent').val();
                },
            },
            pageLength: 10,
            paging: true,
            responsive: true,
            language: {
                search: "",
                searchPlaceholder: "Search..."
            },
            order: [[1, 'desc']], // Sort by the "total" column in descending order
            columns: [
                // { data: 'rank', title: 'Rank' },
                { data: 'agent', title: 'MP/Agent' },
                { data: 'total', title: 'Number of Released Units' },

            ],
            columnDefs: [

            ],
        });

        var AgentData = null;
        // Total Inquiries in Inquiries Bar Graph
        function fetchAgentData() {
            $.ajax({
                url: '{{ route("dashboard.ranking-dashboard.topAgentBarChart") }}',
                type: 'GET',
                data: {
                    date_range: $('#date-range-picker').val(),
                    group: $('#selectGroup').val(),
                    agent: $('#filterAgent').val()

                },
                success: function(response) {
                    if (response.agents && response.agents.length === 0) {
                        $('#rankingBarChart').html('<p>No transaction found</p>');
                    } else if (response.agents) {
                        const labels = response.agents.map(item => `${item.agent.first_name} ${item.agent.last_name}`);
                        const data = response.agents.map(item => item.total);
                        renderAgentDataChart(labels, data);
                    } else {
                        $('#rankingBarChart').html('<p>No transaction found</p>');
                    }
                }
            });
        }

        function renderAgentDataChart(labels, data){
             // Render the bar chart with the fetched data
            var options = {
            series: [{
                name: 'Released Units',
                data: data,
                }],
                chart: {
                height: 350,
                type: 'bar',
                },
                plotOptions: {
                bar: {
                    borderRadius: 10,
                    dataLabels: {
                    position: 'top', // top, center, bottom
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
                            colors: ["#ff0055"] // Data label color
                        }
                },

                xaxis: {
                categories: labels,
                position: 'bottom',
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
                    show: true,
                },
                labels: {
                    show: true,
                    formatter: function (val) {
                    return val;
                    }
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

            if (AgentData) {
                AgentData.updateSeries([{
                    name: 'Released Units',
                    data: data,
                }]);
                AgentData.destroy();
            }
            // } else {
            //     // Create a new chart instance
                AgentData = new ApexCharts(document.querySelector("#rankingBarChart"), options);
                AgentData.render();
            // }

        }
        fetchAgentData();
</script>
@endsection
