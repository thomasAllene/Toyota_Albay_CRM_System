<!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/libs/hammer/hammer.js"></script>
    <script src="assets/vendor/libs/flatpickr/flatpickr.js"></script>
    <script src="assets/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script src="assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <script src="assets/vendor/libs/select2/select2.js"></script>
    <script src="assets/vendor/libs/toastr/toastr.js"></script>
    <script src="assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js" />
    // <script src="assets/vendor/libs/apex-charts/apexcharts.js"><script>


    <!-- CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>


    <!-- Custom JS -->
    // <script>
    //     function updateLeadsBadge(count) {
    //         let badge = $("#leads-badge");

    //         if (count > 0) {
    //             badge.text(count).show();  // Show badge with count
    //         } else {
    //             badge.hide();  // Hide badge if count is 0
    //         }
    //     }

    //     // Example Test Cases:
    //     updateLeadsBadge(4);  // Should show badge with "5"
    //     setTimeout(() => updateLeadsBadge(0), 3000);  // Hide badge after 3 seconds
    // </script>


<script>
        function updateLeadsBadge() {
            $.ajax({
                url: "{{ route('leads.countInquiry') }}",
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    let individualBadge = $("#leadsIndividualTabBadge");
                    if (response.inquiryIndividual > 0) {
                        individualBadge.text(response.inquiryIndividual).show();
                    } else {
                        individualBadge.hide();
                    }

                    let fleetBadge = $("#leadsFleetTabBadge");
                    if (response.inquiryFleet > 0) {
                        fleetBadge.text(response.inquiryFleet).show();
                    } else {
                        fleetBadge.hide();
                    }

                    let governmentBadge = $("#leadsGovernmentTabBadge");
                    if (response.inquiryGovernment > 0) {
                        governmentBadge.text(response.inquiryGovernment).show();
                    } else {
                        governmentBadge.hide();
                    }

                    let companyBadge = $("#leadsCompanyTabBadge");
                    if (response.inquiryCompany > 0) {
                        companyBadge.text(response.inquiryCompany).show();
                    } else {
                        companyBadge.hide();
                    }

                    let totalBadge = $("#sideNavLeadsBadge");
                    if (response.inquiryIndividual + response.inquiryFleet + response.inquiryGovernment + response.inquiryCompany > 0) {
                        totalBadge.show();
                    } else {
                        totalBadge.hide();
                    }
                    
                }
            });
        }

        updateLeadsBadge();

        function updateApplicationBadge(){
            $.ajax({
                url: "{{ route('application.count') }}",
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response){
                    console.log(response);
                    let pendingBadge = $("#applicationPendingTabBadge");
                    if(response.pending_application > 0){
                        pendingBadge.text(response.pending_application).show();
                    }else{
                        pendingBadge.hide();
                    }
                    let cashPOTabBadge = $("#applicationCashPOTabBadge");
                    if(response.poOrCash_application > 0){
                        cashPOTabBadge.text(response.poOrCash_application).show();
                    }else{
                        cashPOTabBadge.hide();
                    }

                    let approvedBadge = $("#applicationApprovedTabBadge");
                    if(response.approved_application > 0){
                        approvedBadge.text(response.approved_application).show();
                    }else{
                        approvedBadge.hide();
                    }   

                    let canceledBadge = $("#applicationDeniedTabBadge");
                    if(response.cancel_application > 0){
                        canceledBadge.text(response.cancel_application).show();
                    }else{
                        canceledBadge.hide();
                    }

                    let totalBadge = $("#sideNavApplicationBadge");
                    if(response.pending_application + response.poOrCash_application + response.approved_application + response.cancel_application > 0){
                        totalBadge.show();
                    }else{
                        totalBadge.hide();
                    }
                    
                    
                    
                    
                },
                error: function(xhr, status, error){
                    console.log(xhr);
                    console.log(status);
                    console.log(error);
                }
            });
        }

        updateApplicationBadge();

       function updateVehicleReservationBadge(){
        $.ajax({
            url: "{{ route('vehicle.reservation.getVehicleReservationCount') }}",
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response){
                console.log(response);
                let pendingBadge = $("#pendingTabBadge");
                if(response.pending_count > 0){
                    pendingBadge.text(response.pending_count).show();
                }else{
                    pendingBadge.hide();
                }
                let reservedBadge = $("#reservationTabBadge");
                if(response.reserved_count > 0){
                    reservedBadge.text(response.reserved_count).show();
                }else{
                    reservedBadge.hide();
                }

                let totalBadge = $("#sideNavReservationBadge");
                if(response.pending_count + response.reserved_count > 0){
                    totalBadge.show();
                }else{
                    totalBadge.hide();
                }
            },
            error: function(xhr, status, error){
                console.log(xhr);
            }
        });
       }

       updateVehicleReservationBadge();

       function updateVehicleReleaseBadge(){
        $.ajax({
            url: "{{ route('vehicle.releases.getVehicleReleaseCount') }}",
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response){
                console.log(response);
                let pendingBadge = $("#forReleaseTabBadge");
                if(response.pending_count > 0){
                    pendingBadge.text(response.pending_count).show();
                }else{
                    pendingBadge.hide();
                }

                let releasedBadge = $("#releasedTabBadge");
                if(response.released_count > 0){
                    releasedBadge.text(response.released_count).show();
                }else{
                    releasedBadge.hide();
                }

                let totalBadge = $("#sideNavReleasesBadge");
                if(response.pending_count + response.released_count > 0){
                    totalBadge.show();
                }else{
                    totalBadge.hide();
                }
                
            },
            error: function(xhr, status, error){
                console.log(xhr);
            }
        });
       }

         updateVehicleReleaseBadge();

       function updateDisputeBadge(){
        $.ajax({
            url: "{{ route('dispute.getDisputeCount') }}",
            type: "GET",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response){
                console.log(response);
                let totalBadge = $("#sideNavDisputeTabBadge");
                if(response.count > 0){
                    totalBadge.show();
                }else{
                    totalBadge.hide();
                }
            },
            error: function(xhr, status, error){
                console.log(xhr);
            }
        });
       }

       updateDisputeBadge();

       function updateDisputeStatus(){
        $.ajax({
            url: "{{ route('dispute.updateDisputeStatus') }}",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response){
                console.log(response);
            },
            error: function(xhr, status, error){
                console.log(xhr);
                console.log(status);
                console.log(error);
            }
        });
       }


</script>




