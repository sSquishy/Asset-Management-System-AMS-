@extends('layouts/default')
{{-- Page title --}}
@section('title')
    {{ trans('general.dashboard') }}
    @parent
@stop


{{-- Page content --}}
@section('content')

    @php
        $assets = \App\Models\Asset::AssetsForShow()->get();
    @endphp

    @if ($snipeSettings->dashboard_message != '')
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                {!! Helper::parseEscapedMarkedown($snipeSettings->dashboard_message) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <div class="row">

        <!-- panel -->
        <div class="col-lg-2 col-xs-6">
            <a href="{{ route('hardware.index') }}">
                <!-- small hardware box -->
                <div class="dashboard small-box bg-blue">
                    <div class="inner">
                        <h3>{{ number_format(\App\Models\Asset::AssetsForShow()->count()) }}</h3>
                        <p>{{ trans('general.assets') }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <x-icon type="assets" />
                    </div>
                    <span class="small-box-footer">
                        {{ trans('general.view_all') }}
                        <x-icon type="arrow-circle-right" />
                    </span>
                </div>
            </a>
        </div><!-- ./col -->

        <div class="col-lg-2 col-xs-6">
            <a href="{{ route('licenses.index') }}" aria-hidden="true">
                <!-- small license box -->
                <div class="dashboard small-box bg-blue">
                    <div class="inner">
                        <h3>{{ number_format($counts['license']) }}</h3>
                        <p>{{ trans('general.licenses') }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <x-icon type="licenses" />
                    </div>
                    <span class="small-box-footer">
                        {{ trans('general.view_all') }}
                        <x-icon type="arrow-circle-right" />
                    </span>
                </div>
            </a>
        </div><!-- ./col -->

        <div class="col-lg-2 col-xs-6">
            <!-- small accessories box -->
            <a href="{{ route('accessories.index') }}">
                <div class="dashboard small-box bg-blue">
                    <div class="inner">
                        <h3> {{ number_format($counts['accessory']) }}</h3>
                        <p>{{ trans('general.accessories') }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <x-icon type="accessories" />
                    </div>
                    <span class="small-box-footer">
                        {{ trans('general.view_all') }}
                        <x-icon type="arrow-circle-right" />
                    </span>
                </div>
            </a>
        </div><!-- ./col -->

        <div class="col-lg-2 col-xs-6">
            <!-- small consumables box -->
            <a href="{{ route('consumables.index') }}">
                <div class="dashboard small-box bg-blue">
                    <div class="inner">
                        <h3> {{ number_format($counts['consumable']) }}</h3>
                        <p>{{ trans('general.consumables') }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <x-icon type="consumables" />
                    </div>
                    <span class="small-box-footer">
                        {{ trans('general.view_all') }}
                        <x-icon type="arrow-circle-right" />
                    </span>
                </div>
            </a>
        </div><!-- ./col -->

        <div class="col-lg-2 col-xs-6">
            <!-- small components box -->
            <a href="{{ route('components.index') }}">
                <div class="dashboard small-box bg-blue">
                    <div class="inner">
                        <h3>{{ number_format($counts['component']) }}</h3>
                        <p>{{ trans('general.components') }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <x-icon type="components" />
                    </div>
                    <span class="small-box-footer">
                        {{ trans('general.view_all') }}
                        <x-icon type="arrow-circle-right" />
                    </span>
                </div>
            </a>
        </div><!-- ./col -->

        <div class="col-lg-2 col-xs-6">
            <!-- small users box -->
            <a href="{{ route('users.index') }}">
                <div class="dashboard small-box bg-blue">
                    <div class="inner">
                        <h3>{{ number_format($counts['user']) }}</h3>
                        <p>{{ trans('general.people') }}</p>
                    </div>
                    <div class="icon" aria-hidden="true">
                        <x-icon type="users" />
                    </div>
                    <span class="small-box-footer">
                        {{ trans('general.view_all') }}
                        <x-icon type="arrow-circle-right" />
                    </span>
                </div>
            </a>
        </div><!-- ./col -->

    </div>

    <!-- BEGIN: Dashboard KPI Row -->
    <div class="row" style="margin-bottom:10px;">
        <!-- BEGIN: Asset Depreciation Overview -->
        <div class="col-md-4" style="margin-bottom:12px;">
            @include('zaanalytics.asset_depreciation_overview.column')
        </div>
        <!-- END: Asset Depreciation Overview -->

        <!-- BEGIN: Warranty Expiration Forecast -->
        <div class="col-md-4" style="margin-bottom:12px;">
            @include('zaanalytics.warranty_expiration_forecast.column')
        </div>
        <!-- END: Warranty Expiration Forecast -->

        <!-- BEGIN: Assets with Most Failures -->
        <div class="col-md-4" style="margin-bottom:12px;">
            @include('zaanalytics.assets_most_failures.column')
        </div>
        <!-- END: Assets with Most Failures -->
    </div>
    <!-- END: Dashboard KPI Row -->

    <!-- BEGIN: Assets Predicted & Recommended For Replacement Row -->
    <div class="row" style="margin-top:10px;">
        <!-- BEGIN: Assets Predicted For Replacement -->
        <div class="col-md-8">
            @include('zaanalytics.assets_predicted_for_replacement.column')
        </div>
        <!-- END: Assets Predicted For Replacement -->
        <!-- BEGIN: Recommended Assets for Replacement -->
        <div class="col-md-4">
            @include('zaanalytics.recommended_assets_for_replacement.column')
        </div>
        <!-- END: Recommended Assets for Replacement -->
    </div>
    <!-- END: Assets Predicted & Recommended For Replacement Row -->

    @php
        // Assets nearing warranty expiration (within next N days)
        $warrantySoon = collect();
        $thresholdDays = 60; // consider "nearing" as within next 60 days
        foreach ($assets as $a) {
            if (!$a->purchase_date || empty($a->warranty_months)) {
                continue;
            }
            try {
                $expiry = \Carbon\Carbon::parse($a->purchase_date)->addMonths($a->warranty_months);
            } catch (\Exception $e) {
                continue;
            }
            $now = \Carbon\Carbon::now();
            if ($expiry->lt($now)) {
                continue;
            } // already expired
            $daysRemaining = $now->diffInDays($expiry);
            if ($daysRemaining <= $thresholdDays) {
                $assigned =
                    optional($a->assigned_user)->name ?:
                    optional($a->assigned_to)->name ?:
                    optional($a->user)->name ?:
                    '';
                $department = optional($a->location)->name ?: optional($a->department)->name ?: '';
                $supplier = optional($a->supplier)->name ?: '';
                $warrantySoon->push([
                    'asset_tag' => $a->asset_tag ?: '',
                    'name' => $a->name ?: 'Asset #' . $a->id,
                    'category' => optional($a->category)->name ?: optional($a->model)->name ?: '',
                    'assigned' => $assigned,
                    'department' => $department,
                    'warranty_date' => $expiry->toDateString(),
                    'days_remaining' => $daysRemaining,
                    'supplier' => $supplier,
                ]);
            }
        }
        $warrantySoon = $warrantySoon->sortBy('days_remaining')->values();
    @endphp

    <div class="row" style="margin-top:10px;">
        <div class="col-md-8">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title" style="font-weight:700">Asset Nearing Warranty Expiration</h2>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                            <x-icon type="minus" />
                            <span class="sr-only">{{ trans('general.collapse') }}</span>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="dashWarrantySoon" class="table table-striped snipe-table" data-toggle="table"
                            data-fixed-table-toolbar="true" data-cookie-id-table="dashWarrantySoon" data-height="500"
                            data-search="true" data-pagination="false">
                            <thead>
                                <tr>
                                    <th>Asset Tag</th>
                                    <th>Asset Name</th>
                                    <th>Category</th>
                                    <th>Assigned To / Department</th>
                                    <th>Warranty Date</th>
                                    <th>Days Remaining</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($warrantySoon->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-muted">No assets nearing warranty expiration.</td>
                                    </tr>
                                @else
                                    @foreach ($warrantySoon as $w)
                                        <tr>
                                            <td>{{ $w['asset_tag'] }}</td>
                                            <td>{{ $w['name'] }}</td>
                                            <td>{{ $w['category'] }}</td>
                                            <td>{{ $w['assigned'] ?: $w['department'] }}</td>
                                            <td>{{ $w['warranty_date'] }}</td>
                                            <td style="font-weight:700">{{ $w['days_remaining'] }}</td>
                                            <td>{{ $w['supplier'] }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center col-md-12" style="padding-top: 10px;">
                        <a href="{{ route('hardware.index') }}" class="btn btn-primary btn-sm"
                            style="width: 100%">{{ trans('general.viewall') }}</a>
                    </div>
                </div>
            </div>
        </div>
        @php
            // Vendors maintenance aggregation: top vendors by total cost (or jobs)
            $vendorRows = \App\Models\Maintenance::select(
                'supplier_id',
                \DB::raw('count(*) as jobs'),
                \DB::raw('coalesce(sum(cost),0) as total_cost'),
                \DB::raw(
                    'avg(case when completion_date is not null and start_date is not null then datediff(completion_date,start_date) end) as avg_duration',
                ),
            )
                ->whereNotNull('supplier_id')
                ->groupBy('supplier_id')
                ->get();

            $vendorIds = $vendorRows->pluck('supplier_id')->unique()->filter()->values()->all();
            $vendorsMap = \App\Models\Supplier::whereIn('id', $vendorIds)->get()->keyBy('id');

            $vendorItems = collect();
            foreach ($vendorRows as $vr) {
                $s = $vendorsMap->get($vr->supplier_id);
                if (!$s) {
                    continue;
                }
                $vendorItems->push([
                    'id' => $vr->supplier_id,
                    'name' => $s->name ?: 'Supplier #' . $vr->supplier_id,
                    'jobs' => (int) $vr->jobs,
                    'total_cost' => (float) $vr->total_cost,
                    'total_cost_formatted' => \App\Helpers\Helper::formatCurrencyOutput($vr->total_cost),
                    'avg_duration' => $vr->avg_duration !== null ? round($vr->avg_duration, 1) : null,
                ]);
            }
            $vendorItems = $vendorItems->sortByDesc('total_cost')->values()->take(8);
        @endphp

        @if ($vendorItems->isNotEmpty())
            <div class="col-md-4">
                @include('components.vendors-maintenance-card', ['items' => $vendorItems])
            </div>
        @endif

    </div>

    @if ($counts['grand_total'] == 0)

        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h2 class="box-title">{{ trans('general.dashboard_info') }}</h2>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">

                                <div class="progress">
                                    <div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="60"
                                        aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                        <span class="sr-only">{{ trans('general.60_percent_warning') }}</span>
                                    </div>
                                </div>


                                <p><strong>{{ trans('general.dashboard_empty') }}</strong></p>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                @can('create', \App\Models\Asset::class)
                                    <a class="btn bg-teal" style="width: 100%"
                                        href="{{ route('hardware.create') }}">{{ trans('general.new_asset') }}</a>
                                @endcan
                            </div>
                            <div class="col-md-2">
                                @can('create', \App\Models\License::class)
                                    <a class="btn bg-maroon" style="width: 100%"
                                        href="{{ route('licenses.create') }}">{{ trans('general.new_license') }}</a>
                                @endcan
                            </div>
                            <div class="col-md-2">
                                @can('create', \App\Models\Accessory::class)
                                    <a class="btn bg-orange" style="width: 100%"
                                        href="{{ route('accessories.create') }}">{{ trans('general.new_accessory') }}</a>
                                @endcan
                            </div>
                            <div class="col-md-2">
                                @can('create', \App\Models\Consumable::class)
                                    <a class="btn bg-purple" style="width: 100%"
                                        href="{{ route('consumables.create') }}">{{ trans('general.new_consumable') }}</a>
                                @endcan
                            </div>
                            <div class="col-md-2">
                                @can('create', \App\Models\Component::class)
                                    <a class="btn bg-yellow" style="width: 100%"
                                        href="{{ route('components.create') }}">{{ trans('general.new_component') }}</a>
                                @endcan
                            </div>
                            <div class="col-md-2">
                                @can('create', \App\Models\User::class)
                                    <a class="btn bg-light-blue" style="width: 100%"
                                        href="{{ route('users.create') }}">{{ trans('general.new_user') }}</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- recent activity -->
        <div class="row" style="padding-left:12px;padding-right:12px;">
            <div class="col-md-8">
                <div class="box">
                    <div class="box-header with-border bg-white">
                        <h2 class="box-title" style="font-weight:700">{{ trans('general.recent_activity') }}</h2>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                                <x-icon type="minus" />
                                <span class="sr-only">{{ trans('general.collapse') }}</span>
                            </button>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">

                                    <table data-cookie-id-table="dashActivityReport" data-height="500"
                                        data-pagination="false" data-side-pagination="server"
                                        data-id-table="dashActivityReport" data-sort-order="desc"
                                        data-sort-name="created_at" id="dashActivityReport"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.activity.index', ['limit' => 25]) }}">
                                        <thead>
                                            <tr>
                                                <th data-field="icon" data-visible="true" style="width: 40px;"
                                                    class="hidden-xs" data-formatter="iconFormatter"><span
                                                        class="sr-only">{{ trans('admin/hardware/table.icon') }}</span>
                                                </th>
                                                <th class="col-sm-3" data-visible="true" data-field="created_at"
                                                    data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                                                <th class="col-sm-2" data-visible="true" data-field="admin"
                                                    data-formatter="usersLinkObjFormatter">
                                                    {{ trans('general.created_by') }}</th>
                                                <th class="col-sm-2" data-visible="true" data-field="action_type">
                                                    {{ trans('general.action') }}</th>
                                                <th class="col-sm-3" data-visible="true" data-field="item"
                                                    data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}
                                                </th>
                                                <th class="col-sm-2" data-visible="true" data-field="target"
                                                    data-formatter="polymorphicItemFormatter">
                                                    {{ trans('general.target') }}</th>
                                            </tr>
                                        </thead>
                                    </table>



                                </div><!-- /.responsive -->
                            </div><!-- /.col -->
                            <div class="text-center col-md-12" style="padding-top: 10px;">
                                <a href="{{ route('reports.activity') }}" class="btn btn-primary btn-sm"
                                    style="width: 100%">{{ trans('general.viewall') }}</a>
                            </div>
                        </div><!-- /.row -->
                    </div><!-- ./box-body -->
                </div><!-- /.box -->
            </div>
            <div class="col-md-4">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title" style="font-weight:700">
                            {{ \App\Models\Setting::getSettings()->dash_chart_type == 'name' ? trans('general.assets_by_status') : trans('general.assets_by_status_type') }}
                        </h2>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                                <x-icon type="minus" />
                                <span class="sr-only">{{ trans('general.collapse') }}</span>
                            </button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="chart-responsive">
                                    <canvas id="statusPieChart" height="260"></canvas>
                                </div> <!-- ./chart-responsive -->
                            </div> <!-- /.col -->
                        </div> <!-- /.row -->
                    </div><!-- /.box-body -->
                </div> <!-- /.box -->
            </div>

        </div> <!--/row-->
        <div class="row" style="padding-left:12px;padding-right:12px;">
            <div class="col-md-6">

                @if ($snipeSettings->scope_locations_fmcs != '1' && $snipeSettings->full_multiple_companies_support == '1')
                    <!-- Companies -->
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h2 class="box-title" style="font-weight:700">{{ trans('general.companies') }}</h2>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <x-icon type="minus" />
                                    <span class="sr-only">{{ trans('general.collapse') }}</span>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table data-cookie-id-table="dashCompanySummary" data-height="400"
                                            data-pagination="false" data-side-pagination="server" data-sort-order="desc"
                                            data-sort-field="assets_count" id="dashCompanySummary"
                                            class="table table-striped snipe-table"
                                            data-url="{{ route('api.companies.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">

                                            <thead>
                                                <tr>
                                                    <th class="col-sm-3" data-visible="true" data-field="name"
                                                        data-formatter="companiesLinkFormatter" data-sortable="true">
                                                        {{ trans('general.name') }}</th>
                                                    <th class="col-sm-1" data-visible="true" data-field="users_count"
                                                        data-sortable="true">
                                                        <x-icon type="users" />
                                                        <span class="sr-only">{{ trans('general.people') }}</span>
                                                    </th>
                                                    <th class="col-sm-1" data-visible="true" data-field="assets_count"
                                                        data-sortable="true">
                                                        <x-icon type="assets" />
                                                        <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                                    </th>
                                                    <th class="col-sm-1" data-visible="true"
                                                        data-field="accessories_count" data-sortable="true">
                                                        <x-icon type="accessories" />
                                                        <span
                                                            class="sr-only">{{ trans('general.accessories_count') }}</span>
                                                    </th>
                                                    <th class="col-sm-1" data-visible="true"
                                                        data-field="consumables_count" data-sortable="true">
                                                        <x-icon type="consumables" />
                                                        <span
                                                            class="sr-only">{{ trans('general.consumables_count') }}</span>
                                                    </th>
                                                    <th class="col-sm-1" data-visible="true"
                                                        data-field="components_count" data-sortable="true">
                                                        <x-icon type="components" />
                                                        <span
                                                            class="sr-only">{{ trans('general.components_count') }}</span>
                                                    </th>
                                                    <th class="col-sm-1" data-visible="true" data-field="licenses_count"
                                                        data-sortable="true">
                                                        <x-icon type="licenses" />
                                                        <span class="sr-only">{{ trans('general.licenses_count') }}</span>
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div> <!-- /.col -->
                                <div class="text-center col-md-12" style="padding-top: 10px;">
                                    <a href="{{ route('companies.index') }}" class="btn btn-primary btn-sm"
                                        style="width: 100%">{{ trans('general.viewall') }}</a>
                                </div>
                            </div> <!-- /.row -->

                        </div><!-- /.box-body -->
                    </div> <!-- /.box -->
                @else
                    <!-- Locations -->
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h2 class="box-title">{{ trans('general.locations') }}</h2>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <x-icon type="minus" />
                                    <span class="sr-only">{{ trans('general.collapse') }}</span>
                                </button>
                            </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table data-cookie-id-table="dashLocationSummary" data-height="400"
                                            data-side-pagination="server" data-pagination="false" data-sort-order="desc"
                                            data-sort-field="assets_count" id="dashLocationSummary"
                                            class="table table-striped snipe-table"
                                            data-url="{{ route('api.locations.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
                                            <thead>
                                                <tr>
                                                    <th class="col-sm-3" data-visible="true" data-field="name"
                                                        data-formatter="locationsLinkFormatter" data-sortable="true">
                                                        {{ trans('general.name') }}</th>

                                                    <th class="col-sm-1" data-visible="true" data-field="assets_count"
                                                        data-sortable="true">
                                                        <x-icon type="assets" />
                                                        <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                                    </th>
                                                    <th class="col-sm-1" data-visible="true"
                                                        data-field="assigned_assets_count" data-sortable="true">

                                                        {{ trans('general.assigned') }}
                                                    </th>
                                                    <th class="col-sm-1" data-visible="true" data-field="users_count"
                                                        data-sortable="true">
                                                        <x-icon type="users" />
                                                        <span class="sr-only">{{ trans('general.people') }}</span>

                                                    </th>

                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div> <!-- /.col -->
                                <div class="text-center col-md-12" style="padding-top: 10px;">
                                    <a href="{{ route('locations.index') }}" class="btn btn-primary btn-sm"
                                        style="width: 100%">{{ trans('general.viewall') }}</a>
                                </div>
                            </div> <!-- /.row -->

                        </div><!-- /.box-body -->
                    </div> <!-- /.box -->
                @endif

            </div>
            <div class="col-md-6">

                <!-- Categories -->
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title" style="font-weight:700">{{ trans('general.asset') }}
                            {{ trans('general.categories') }}</h2>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <x-icon type="minus" />
                                <span class="sr-only">{{ trans('general.collapse') }}</span>
                            </button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table data-cookie-id-table="dashCategorySummary" data-height="400"
                                        data-pagination="false" data-side-pagination="server" data-sort-order="desc"
                                        data-sort-field="assets_count" id="dashCategorySummary"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.categories.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
                                        <thead>
                                            <tr>
                                                <th class="col-sm-3" data-visible="true" data-field="name"
                                                    data-formatter="categoriesLinkFormatter" data-sortable="true">
                                                    {{ trans('general.name') }}</th>
                                                <th class="col-sm-3" data-visible="true" data-field="category_type"
                                                    data-sortable="true">
                                                    {{ trans('general.type') }}
                                                </th>
                                                <th class="col-sm-1" data-visible="true" data-field="assets_count"
                                                    data-sortable="true">
                                                    <x-icon type="assets" />
                                                    <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                                </th>
                                                <th class="col-sm-1" data-visible="true" data-field="accessories_count"
                                                    data-sortable="true">
                                                    <x-icon type="licenses" />
                                                    <span class="sr-only">{{ trans('general.accessories_count') }}</span>
                                                </th>
                                                <th class="col-sm-1" data-visible="true" data-field="consumables_count"
                                                    data-sortable="true">
                                                    <x-icon type="consumables" />
                                                    <span class="sr-only">{{ trans('general.consumables_count') }}</span>
                                                </th>
                                                <th class="col-sm-1" data-visible="true" data-field="components_count"
                                                    data-sortable="true">
                                                    <x-icon type="components" />
                                                    <span class="sr-only">{{ trans('general.components_count') }}</span>
                                                </th>
                                                <th class="col-sm-1" data-visible="true" data-field="licenses_count"
                                                    data-sortable="true">
                                                    <x-icon type="licenses" />
                                                    <span class="sr-only">{{ trans('general.licenses_count') }}</span>
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div> <!-- /.col -->
                            <div class="text-center col-md-12" style="padding-top: 10px;">
                                <a href="{{ route('categories.index') }}" class="btn btn-primary btn-sm"
                                    style="width: 100%">{{ trans('general.viewall') }}</a>
                            </div>
                        </div> <!-- /.row -->

                    </div><!-- /.box-body -->
                </div> <!-- /.box -->
            </div>


    @endif


@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['simple_view' => true, 'nopages' => true])
@stop

@push('js')
    <script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
    <script nonce="{{ csrf_token() }}">
        // ---------------------------
        // - ASSET STATUS CHART -
        // ---------------------------
        var pieChartCanvas = $("#statusPieChart").get(0).getContext("2d");
        var pieChart = new Chart(pieChartCanvas);
        var ctx = document.getElementById("statusPieChart");
        var pieOptions = {
            legend: {
                position: 'top',
                responsive: true,
                maintainAspectRatio: true,
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        counts = data.datasets[0].data;
                        total = 0;
                        for (var i in counts) {
                            total += counts[i];
                        }
                        prefix = data.labels[tooltipItem.index] || '';
                        return prefix + " " + Math.round(counts[tooltipItem.index] / total * 100) + "%";
                    }
                }
            }
        };

        $.ajax({
            type: 'GET',
            url: '{{ \App\Models\Setting::getSettings()->dash_chart_type == 'name' ? route('api.statuslabels.assets.byname') : route('api.statuslabels.assets.bytype') }}',
            headers: {
                "X-Requested-With": 'XMLHttpRequest',
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(data) {
                var myPieChart = new Chart(ctx, {
                    type: 'pie',
                    data: data,
                    options: pieOptions
                });
            },
            error: function(data) {
                // window.location.reload(true);
            },
        });
        var last = document.getElementById('statusPieChart').clientWidth;
        addEventListener('resize', function() {
            var current = document.getElementById('statusPieChart').clientWidth;
            if (current != last) location.reload();
            last = current;
        });
    </script>
@endpush
