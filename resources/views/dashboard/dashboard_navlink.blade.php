<style>
    .a-tag{
        color: #282830;
        text-decoration: none;
    }
    .a-tag:hover{
        color: #ff4772;
        border-bottom: 1px solid #ff4772;
    }

    .a-tag.active{
        color: #ff0055;
        border-bottom: 3px solid #ff0055;
    }
</style>


{{-- Nav Tabs --}}
<div class="row mb-4">
    <div class="col-md">
        <div class="d-flex justify-content-center border-bottom gap-5">
            <a href="/dashboard" class="a-tag text-decoration-none d-flex align-items-center {{ request()->is('dashboard') ? 'active' : '' }}">
                <i class='bx bx-right-top-arrow-circle fs-4'></i>
                <label  class="py-2 px-2">RELEASE STATS</label >
            </a>
            <a href="/sales-funnel-management-dashboard" class="a-tag text-decoration-none d-flex align-items-center {{ request()->is('sales-funnel-management-dashboard') ? 'active' : '' }}">
                <i class='bx bx-layer-plus fs-4'></i>
                <label class="py-2 px-2">SALES FUNNEL MANAGEMENT</label>
            </a>
            {{-- <a href="" class="text-decoration-none a-tag d-flex align-items-center">
                <i class='bx bx-transfer-alt fs-4'></i>
                <label class="py-2 px-2">PROFITABILITY</label>
            </a> --}}
            <a href="/vehicle-to-sales-dashboard" class="text-decoration-none a-tag d-flex align-items-center {{ request()->is('vehicle-to-sales-dashboard') ? 'active' : '' }}">
                <i class='bx bx-coin fs-4'></i>
                <label class="py-2 px-2">VEHICLE TO SALES</label>
            </a>
            <a href="/ranking-dashboard" class="text-decoration-none a-tag d-flex align-items-center {{ request()->is('ranking-dashboard') ? 'active' : '' }}">
                <i class='bx bx-coin fs-4'></i>
                <label class="py-2 px-2">RANKING</label>
            </a>
        </div>
    </div>
</div>
