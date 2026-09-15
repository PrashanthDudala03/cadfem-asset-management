@extends('layouts/default')
{{-- Page title --}}
@section('title')
{{ trans('general.dashboard') }}
@parent
@stop


{{-- Page content --}}
@section('content')

<div style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; padding: 30px 0; margin: -15px -15px 0 -15px;">

@if ($snipeSettings->dashboard_message!='')
<div class="row px-4">
    <div class="col-md-12">
        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
            <div class="row">
                <div class="col-md-12">
                    {!!  Helper::parseEscapedMarkedown($snipeSettings->dashboard_message)  !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row px-4">

    <!-- KPI Cards -->
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('hardware.index') }}" style="text-decoration: none; color: inherit;">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #003366; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">{{ trans('general.assets') }}</p>
                <h2 style="margin: 12px 0 0 0; font-size: 32px; font-weight: 800; color: #003366;">{{ number_format(\App\Models\Asset::AssetsForShow()->count()) }}</h2>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('licenses.index') }}" style="text-decoration: none; color: inherit;">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #28a745; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">{{ trans('general.licenses') }}</p>
                <h2 style="margin: 12px 0 0 0; font-size: 32px; font-weight: 800; color: #28a745;">{{ number_format($counts['license']) }}</h2>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('accessories.index') }}" style="text-decoration: none; color: inherit;">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #ffc107; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">{{ trans('general.accessories') }}</p>
                <h2 style="margin: 12px 0 0 0; font-size: 32px; font-weight: 800; color: #ffc107;">{{ number_format($counts['accessory']) }}</h2>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('consumables.index') }}" style="text-decoration: none; color: inherit;">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #ff6c6c; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">{{ trans('general.consumables') }}</p>
                <h2 style="margin: 12px 0 0 0; font-size: 32px; font-weight: 800; color: #ff6c6c;">{{ number_format($counts['consumable']) }}</h2>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('components.index') }}" style="text-decoration: none; color: inherit;">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #6c5ce7; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">{{ trans('general.components') }}</p>
                <h2 style="margin: 12px 0 0 0; font-size: 32px; font-weight: 800; color: #6c5ce7;">{{ number_format($counts['component']) }}</h2>
            </div>
        </a>
    </div>

    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <a href="{{ route('users.index') }}" style="text-decoration: none; color: inherit;">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-left: 5px solid #00bcd4; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <p style="margin: 0; color: #999; font-size: 12px; font-weight: 600; text-transform: uppercase;">{{ trans('general.people') }}</p>
                <h2 style="margin: 12px 0 0 0; font-size: 32px; font-weight: 800; color: #00bcd4;">{{ number_format($counts['user']) }}</h2>
            </div>
        </a>
    </div>
</div>

@if ($counts['grand_total'] == 0)

    <div class="row px-4 mb-4">

        <div class="col-md-12">
            <div style="background: white; border-radius: 12px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center;">
                <div style="margin-bottom: 24px;">
                    <div style="font-size: 64px; margin-bottom: 16px;">📦</div>
                    <h2 style="color: #003366; font-weight: 700; margin: 0;">{{ trans('general.dashboard_info') }}</h2>
                </div>
                <p style="color: #666; font-size: 16px; margin-bottom: 32px;"><strong>{{ trans('general.dashboard_empty') }}</strong></p>
                <div style="background: #f0f0f0; height: 4px; border-radius: 2px; margin: 24px 0;">
                    <div style="background: linear-gradient(90deg, #ffc107 0%, #ffc107 60%, #f0f0f0 60%); height: 100%; border-radius: 2px;"></div>
                </div>

                <div class="row" style="margin-top: 32px;">
                    <div class="col-md-2">
                        @can('create', \App\Models\Asset::class)
                        <a style="display: inline-block; width: 100%; padding: 10px; background: #003366; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; margin-bottom: 12px;" href="{{ route('hardware.create') }}">{{ trans('general.new_asset') }}</a>
                        @endcan
                    </div>
                    <div class="col-md-2">
                        @can('create', \App\Models\License::class)
                            <a style="display: inline-block; width: 100%; padding: 10px; background: #28a745; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; margin-bottom: 12px;" href="{{ route('licenses.create') }}">{{ trans('general.new_license') }}</a>
                        @endcan
                    </div>
                    <div class="col-md-2">
                        @can('create', \App\Models\Accessory::class)
                            <a style="display: inline-block; width: 100%; padding: 10px; background: #ffc107; color: #333; border-radius: 8px; text-decoration: none; font-weight: 600; margin-bottom: 12px;" href="{{ route('accessories.create') }}">{{ trans('general.new_accessory') }}</a>
                        @endcan
                    </div>
                    <div class="col-md-2">
                        @can('create', \App\Models\Consumable::class)
                            <a style="display: inline-block; width: 100%; padding: 10px; background: #ff6c6c; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; margin-bottom: 12px;" href="{{ route('consumables.create') }}">{{ trans('general.new_consumable') }}</a>
                        @endcan
                    </div>
                    <div class="col-md-2">
                        @can('create', \App\Models\Component::class)
                            <a style="display: inline-block; width: 100%; padding: 10px; background: #6c5ce7; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; margin-bottom: 12px;" href="{{ route('components.create') }}">{{ trans('general.new_component') }}</a>
                        @endcan
                    </div>
                    <div class="col-md-2">
                        @can('create', \App\Models\User::class)
                            <a style="display: inline-block; width: 100%; padding: 10px; background: #00bcd4; color: white; border-radius: 8px; text-decoration: none; font-weight: 600; margin-bottom: 12px;" href="{{ route('users.create') }}">{{ trans('general.new_user') }}</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

@else

    <!-- recent activity + today calendar -->
    <div class="row dashboard-row-eq dashboard-row-compact px-4 mb-4">
  <div class="col-md-8">
    <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
      <h2 style="margin-top: 0; color: #333; font-weight: 700; margin-bottom: 20px;">{{ trans('general.recent_activity') }}</h2>
      <div>
        <div class="row">
          <div class="col-md-12">

                <table
                    data-cookie-id-table="dashActivityReport"
                    data-pagination="false"
                    data-side-pagination="server"
                    data-id-table="dashActivityReport"
                    data-sort-order="desc"
                    data-show-columns="false"
                    data-sort-name="created_at"
                    data-sticky-header="false"
                    data-empty-message="{{ trans('general.dashboard_activity_empty') }}"
                    id="dashActivityReport"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.activity.index', ['limit' => 25]) }}">
                    <thead>
                    <tr>
                        <th scope="col" data-field="icon" data-visible="true" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter"><span  class="sr-only">{{ trans('admin/hardware/table.icon') }}</span></th>
                        <th scope="col" class="col-sm-3" data-visible="true" data-field="created_at" data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                        <th scope="col" class="col-sm-2" data-visible="true" data-field="admin" data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
                        <th scope="col" class="col-sm-2" data-visible="true" data-field="action_type">{{ trans('general.action') }}</th>
                        <th scope="col" class="col-sm-3" data-visible="true" data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                        <th scope="col" class="col-sm-2" data-visible="true" data-field="target" data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                    </tr>
                    </thead>
                </table>
          </div>
        </div>
      </div>
        <div style="text-align: center; padding-top: 16px; border-top: 1px solid #f0f0f0;">
            <a href="{{ route('reports.activity') }}" style="display: inline-block; padding: 8px 16px; background: #003366; color: white; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px;">{{ trans('general.viewall') }}</a>
        </div>
    </div>
  </div>

        {{-- Today widget: agenda-style list of everything happening
             today across every HasCalendarEvents source. Uses the
             same reusable snipeit-calendar bundle as the main
             /calendar page, initialized with listDay (agenda list
             for a single day). --}}
        <div class="col-md-4">
            @can('view', \App\Models\Asset::class)
                <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <h2 style="margin-top: 0; color: #333; font-weight: 700; margin-bottom: 20px;">
                        <a href="{{ route('calendar.index') }}" style="color: #003366; text-decoration: none;">{{ trans('general.calendar_upcoming') }}</a>
                    </h2>
                    <div id="dashboard-today-calendar"></div>
                    <div id="dashboard-today-more" style="display:none; margin-top: 16px; text-align: center; padding-top: 16px; border-top: 1px solid #f0f0f0;">
                        <a href="{{ route('calendar.index') }}" style="display: inline-block; padding: 8px 16px; background: #003366; color: white; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px;" id="dashboard-today-more-link"></a>
                    </div>
                </div>
            @endcan
        </div>
    </div> <!--/row-->

    {{-- Row: pie chart + low-stock + overdue/pending. All three at
         col-md-4 so the row lines up cleanly regardless of box height,
         and each widget serves a distinct "what needs attention" role
         for the admin scanning the dashboard. `dashboard-row-compact`
         caps the box-body heights on this row so the pie/list/table
         trio doesn't dwarf the rest of the dashboard when Needs
         Attention or Low Stock grow long. --}}
    <div class="row dashboard-row-eq dashboard-row-compact px-4 mb-4">
        <div class="col-md-4">
        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <h2 style="margin-top: 0; color: #333; font-weight: 700; margin-bottom: 20px;">
                {{ (\App\Models\Setting::getSettings()->dash_chart_type == 'name') ? trans('general.assets_by_status') : trans('general.assets_by_status_type') }}
            </h2>
            <div style="position: relative; height: 300px;">
                <canvas id="statusPieChart"></canvas>
            </div>
        </div>
        </div>

        <div class="col-md-4">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <h2 style="margin-top: 0; color: #333; font-weight: 700; margin-bottom: 20px;">
                    {{ trans('general.dashboard_low_stock') }}
                        {{-- Info icon: explains the formula the alert
                             uses (remaining < min + alert_threshold)
                             so the widget doesn't look arbitrary to
                             someone who hasn't set a min_amt or
                             threshold. No link on the icon since
                             non-superuser admins see the dashboard
                             but can't reach the alert-threshold setting. --}}
                        <span data-tooltip="true"
                              title="{{ trans('general.dashboard_low_stock_help', ['threshold' => (int) $snipeSettings->alert_threshold]) }}"
                              style="cursor: help; margin-left: 8px; color: #999;">
                            ℹ️
                        </span>
                    </h2>
                <div>
                    {{-- Bs-table backed by /api/v1/low-stock which delegates
                         to Helper::checkLowInventory so this widget and the
                         top-nav alert bell can't drift. polymorphicItemFormatter
                         handles the per-type icon + drilldown link, and the
                         shared adjust-quantity button hangs off each row's
                         available_actions.adjust_quantity via the generic
                         actions column. --}}
                    <table
                        data-cookie-id-table="dashLowStock"
                        data-pagination="false"
                        data-side-pagination="server"
                        data-id-table="dashLowStock"
                        data-sticky-header="false"
                        data-search="false"
                        data-show-columns="false"
                        data-show-columns-toggle-all="false"
                        data-show-fullscreen="false"
                        data-show-print="false"
                        data-show-refresh="false"
                        data-show-export="false"
                        data-empty-message="{{ trans('general.dashboard_low_stock_empty') }}"
                        id="dashLowStock"
                        class="table table-striped snipe-table snipe-table--sticky-right-1"
                        data-url="{{ route('api.low-stock.index', ['limit' => 25]) }}">
                        <thead>
                            <tr>
                                <th scope="col" data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.name') }}</th>
                                <th scope="col" data-field="remaining" data-sortable="true" class="text-right">{{ trans('general.remaining') }}</th>
                                <th scope="col" data-field="min_amt" data-sortable="true" data-formatter="minAmtFormatter" class="text-right">{{ trans('general.min_amt') }}</th>
                                <th scope="col" data-field="available_actions" data-formatter="lowStockActionsFormatter" class="hidden-print text-right">
                                    <span class="sr-only">{{ trans('table.actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                {{-- Lazy Livewire component so the eight count queries
                     that back this widget don't sit on the dashboard's
                     critical render path. Rendered as a placeholder on
                     first paint; Livewire fires a follow-up XHR to hydrate
                     the real counts. Same pattern the top-nav AlertMenu
                     uses for its low-inventory + deprecation queries. --}}
                <livewire:needs-attention/>
            </div>
        </div>
</div> <!--/row-->
<div class="row px-4 mb-4">
    <div class="col-md-6">

		@if ((($snipeSettings->scope_locations_fmcs!='1') && ($snipeSettings->full_multiple_companies_support=='1')))
			 <!-- Companies -->
			<div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
				<h2 style="margin-top: 0; color: #333; font-weight: 700; margin-bottom: 20px;">{{ trans('general.companies') }}</h2>
				<div>
					<div class="row">
						<div class="col-md-12">
							<table
									data-cookie-id-table="dashCompanySummary"
									data-height="400"
                                    data-pagination="false"
									data-side-pagination="server"
									data-sort-order="desc"
                                    data-show-columns="false"
									data-sort-field="assets_count"
                                    data-sticky-header="false"
									id="dashCompanySummary"
									class="table table-striped snipe-table"
									data-url="{{ route('api.companies.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">

								<thead>
								<tr>
									<th scope="col" class="col-sm-3" data-visible="true" data-field="name" data-formatter="companiesLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
									<th scope="col" class="col-sm-1" data-visible="true" data-field="users_count" data-sortable="true">
                                        <x-icon type="users" />
										<span class="sr-only">{{ trans('general.people') }}</span>
									</th>
									<th scope="col" class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets" />
										<span class="sr-only">{{ trans('general.asset_count') }}</span>
									</th>
									<th scope="col" class="col-sm-1" data-visible="true" data-field="accessories_count" data-sortable="true">
                                        <x-icon type="accessories" />
										<span class="sr-only">{{ trans('general.accessories_count') }}</span>
									</th>
									<th scope="col" class="col-sm-1" data-visible="true" data-field="consumables_count" data-sortable="true">
                                        <x-icon type="consumables" />
										<span class="sr-only">{{ trans('general.consumables_count') }}</span>
									</th>
									<th scope="col" class="col-sm-1" data-visible="true" data-field="components_count" data-sortable="true">
                                        <x-icon type="components" />
										<span class="sr-only">{{ trans('general.components_count') }}</span>
									</th>
									<th scope="col" class="col-sm-1" data-visible="true" data-field="licenses_count" data-sortable="true">
                                        <x-icon type="licenses" />
										<span class="sr-only">{{ trans('general.licenses_count') }}</span>
									</th>
								</tr>
								</thead>
							</table>
						</div> <!-- /.col -->
						<div style="text-align: center; padding-top: 16px; border-top: 1px solid #f0f0f0;">
							<a href="{{ route('companies.index') }}" style="display: inline-block; padding: 8px 16px; background: #003366; color: white; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px;">{{ trans('general.viewall') }}</a>
						</div>
					</div> <!-- /.row -->

				</div>

		@else
			 <!-- Locations -->
			 <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
				<h2 style="margin-top: 0; color: #333; font-weight: 700; margin-bottom: 20px;">{{ trans('general.locations') }}</h2>
				<div>
					<div class="row">
						<div class="col-md-12">

							<table
									data-cookie-id-table="dashLocationSummary"
									data-height="400"
									data-side-pagination="server"
                                    data-pagination="false"
									data-sort-order="desc"
									data-sort-field="assets_count"
                                    data-sticky-header="false"
									id="dashLocationSummary"
                                    data-show-columns="false"
									class="table table-striped snipe-table"
									data-url="{{ route('api.locations.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
								<thead>
								<tr>
									<th scope="col" class="col-sm-3" data-visible="true" data-field="name" data-formatter="locationsLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
									
									<th scope="col" class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                        <x-icon type="assets" />
										<span class="sr-only">{{ trans('general.asset_count') }}</span>
									</th>
									<th scope="col" class="col-sm-1" data-visible="true" data-field="assigned_assets_count" data-sortable="true">
										
										{{ trans('general.assigned') }}
									</th>
									<th scope="col" class="col-sm-1" data-visible="true" data-field="users_count" data-sortable="true">
                                        <x-icon type="users" />
										<span class="sr-only">{{ trans('general.people') }}</span>
										
									</th>
									
								</tr>
								</thead>
							</table>
						</div> <!-- /.col -->
						<div style="text-align: center; padding-top: 16px; border-top: 1px solid #f0f0f0;">
							<a href="{{ route('locations.index') }}" style="display: inline-block; padding: 8px 16px; background: #003366; color: white; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px;">{{ trans('general.viewall') }}</a>
						</div>
					</div> <!-- /.row -->

				</div>

		@endif

    </div>
    <div class="col-md-6">

        <!-- Categories -->
        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <h2 style="margin-top: 0; color: #333; font-weight: 700; margin-bottom: 20px;">{{ trans('general.categories') }}</h2>
            <div>
                <div class="row">
                    <div class="col-md-12">

                        <table
                                data-cookie-id-table="dashCategorySummary"
                                data-height="400"
                                data-pagination="false"
                                data-side-pagination="server"
                                data-show-columns="false"
                                data-sort-order="desc"
                                data-sort-field="assets_count"
                                data-sticky-header="false"
                                id="dashCategorySummary"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.categories.index', ['sort' => 'assets_count', 'order' => 'asc']) }}">
                            <thead>
                            <tr>
                                <th scope="col" class="col-sm-3" data-visible="true" data-field="name" data-formatter="categoriesLinkFormatter" data-sortable="true">{{ trans('general.name') }}</th>
                                <th scope="col" class="col-sm-3" data-visible="true" data-field="category_type" data-sortable="true">
                                    {{ trans('general.type') }}
                                </th>
                                <th scope="col" class="col-sm-1" data-visible="true" data-field="assets_count" data-sortable="true">
                                    <x-icon type="assets" />
                                    <span class="sr-only">{{ trans('general.asset_count') }}</span>
                                </th>
                                <th scope="col" class="col-sm-1" data-visible="true" data-field="accessories_count" data-sortable="true">
                                    <x-icon type="licenses" />
                                    <span class="sr-only">{{ trans('general.accessories_count') }}</span>
                                </th>
                                <th scope="col" class="col-sm-1" data-visible="true" data-field="consumables_count" data-sortable="true">
                                    <x-icon type="consumables" />
                                    <span class="sr-only">{{ trans('general.consumables_count') }}</span>
                                </th>
                                <th scope="col" class="col-sm-1" data-visible="true" data-field="components_count" data-sortable="true">
                                    <x-icon type="components" />
                                    <span class="sr-only">{{ trans('general.components_count') }}</span>
                                </th>
                                <th scope="col" class="col-sm-1" data-visible="true" data-field="licenses_count" data-sortable="true">
                                    <x-icon type="licenses" />
                                    <span class="sr-only">{{ trans('general.licenses_count') }}</span>
                                </th>
                            </tr>
                            </thead>
                        </table>

                    </div> <!-- /.col -->
                    <div style="text-align: center; padding-top: 16px; border-top: 1px solid #f0f0f0;">
                        <a href="{{ route('categories.index') }}" style="display: inline-block; padding: 8px 16px; background: #003366; color: white; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 12px;">{{ trans('general.viewall') }}</a>
                    </div>
                </div> <!-- /.row -->

            </div>
        </div>
    </div>

</div>
@endif

    {{-- Adjust-quantity modal wiring for the low-stock widget's inline
         replenish button. Same shared modal used on the accessories /
         consumables / components index and view pages. Included whenever
         the viewer can update any of the three item types (one of those
         grants is what makes the button actually appear in the widget). --}}
    @if (Gate::allows('update', \App\Models\Consumable::class)
         || Gate::allows('update', \App\Models\Accessory::class)
         || Gate::allows('update', \App\Models\Component::class))
        <x-modals.adjust-quantity/>
    @endif

</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['simple_view' => true, 'nopages' => true])

        @can('view', \App\Models\Asset::class)
            {{-- Today widget for the dashboard. Reuses the main calendar
                 bundle; initializes into listDay view scoped to today. No
                 URL sync (widgets don't own the page URL), no filter
                 buttons (too tight for the dashboard column), no toolbar
                 (title suffices). Users who want to filter head over to
                 the full /calendar page. --}}
            <script src="{{ url(mix('js/dist/snipeit-calendar.js')) }}" nonce="{{ csrf_token() }}"></script>
            <script nonce="{{ csrf_token() }}">
                document.addEventListener('DOMContentLoaded', function () {
                    // Format list-view day-group labels. Compares the
                    // group's date to today's local Y-M-D so the
                    // "Today" / "Tomorrow" swap survives a browser
                    // timezone that's east/west of UTC. Anything beyond
                    // tomorrow falls back to FC's own locale-aware
                    // default via arg.text.
                    var upcomingDayFormat = function (arg) {
                        var d = arg.date;
                        var localYmd = d.year + '-' + String(d.month + 1).padStart(2, '0') + '-' + String(d.day).padStart(2, '0');
                        var today = new Date();
                        var todayYmd = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
                        var tomorrow = new Date(today.getTime() + 86400000);
                        var tomorrowYmd = tomorrow.getFullYear() + '-' + String(tomorrow.getMonth() + 1).padStart(2, '0') + '-' + String(tomorrow.getDate()).padStart(2, '0');
                        if (localYmd === todayYmd) return '{{ trans('general.calendar_today') }}';
                        if (localYmd === tomorrowYmd) return '{{ trans('general.calendar_tomorrow') }}';
                        return arg.text;
                    };

                    window.snipeitCalendar.init('dashboard-today-calendar', {
                        events: '{{ route('api.calendar.events') }}',
                        // Rolling 7-day list starting today (not listWeek,
                        // which starts on the week's Sunday/Monday and
                        // would show yesterday on a Tuesday). Keeps the
                        // widget useful when today itself is empty by
                        // pulling in tomorrow through 6 days out.
                        initialView: 'listUpcoming',
                        views: {
                            listUpcoming: {
                                type: 'list',
                                duration: {days: 7},
                            },
                        },
                        // Left side gets the "Today" / "Tomorrow" /
                        // weekday-default swap. Right side stays a full
                        // human-readable date ("August 14, 2026") so
                        // the viewer can see the actual calendar day
                        // even when the left label is relative.
                        listDayFormat: upcomingDayFormat,
                        listDaySideFormat: {
                            month: 'long',
                            day: 'numeric',
                            year: 'numeric',
                        },
                        headerToolbar: false,
                        direction: '{{ \App\Helpers\Helper::determineLanguageDirection() }}',
                        locale: '{{ str_replace('_', '-', app()->getLocale()) }}',
                        urlState: false,
                        limit: 10,
                        onFetchMeta: function (meta) {
                            var more = document.getElementById('dashboard-today-more');
                            var link = document.getElementById('dashboard-today-more-link');
                            if (!more || !link) {
                                return;
                            }
                            if (meta.truncated) {
                                var remaining = Math.max(0, meta.total - meta.returned);
                                link.textContent = '{{ trans('general.calendar_upcoming_more') }}'.replace(':count', String(remaining));
                                more.style.display = '';
                            }
                            else {
                                more.style.display = 'none';
                            }
                        },
                    });
                });
            </script>
        @endcan
@stop

@push('js')


        <script src="{{ url(mix('js/dist/Chart.min.js')) }}"></script>
<script nonce="{{ csrf_token() }}">
    // Theme-aware default text color for every Chart.js instance on
    // this page. Without this the shipped Chart.js default (#666)
    // reads as illegible on the dark-theme box background. Same
    // isDark() + defaultFontColor pattern the reports page uses.
    function isDark() {
        return document.documentElement.getAttribute('data-theme') === 'dark';
    }
    Chart.defaults.global.defaultFontColor = isDark() ? '#cccccc' : '#666666';

    // ---------------------------
    // - ASSET STATUS CHART -
    // ---------------------------
      var pieChartCanvas = $("#statusPieChart").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas);
      var ctx = document.getElementById("statusPieChart");
      var pieOptions = {
              // `responsive` + `maintainAspectRatio` are top-level
              // chart options in Chart.js, not legend options. Before
              // this fix they were nested under `legend`, which
              // Chart.js silently ignored — so the pie stayed at its
              // canvas height="260" attribute and didn't fill its
              // container. Setting maintainAspectRatio: false lets the
              // pie fill both dimensions of the .chart-responsive
              // wrapper the dashboard puts it in.
              responsive: true,
              maintainAspectRatio: false,
              legend: {
                  position: 'top',
              },
              tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        counts = data.datasets[0].data;
                        total = 0;
                        for(var i in counts) {
                            total += counts[i];
                        }
                        prefix = data.labels[tooltipItem.index] || '';
                        return prefix+" "+Math.round(counts[tooltipItem.index]/total*100)+"%";
                    }
                }
              }
          };

      $.ajax({
          type: 'GET',
          url: '{{ (\App\Models\Setting::getSettings()->dash_chart_type == 'name') ? route('api.statuslabels.assets.byname') : route('api.statuslabels.assets.bytype') }}',
          headers: {
              "X-Requested-With": 'XMLHttpRequest',
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
          },
          dataType: 'json',
          success: function (data) {
              var myPieChart = new Chart(ctx,{
                  type   : 'pie',
                  data   : data,
                  options: pieOptions
              });
          },
          error: function (data) {
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
