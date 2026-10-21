{{--
  ONE FILE FOR ALL SECURED URLS (JS side).
  The URLs are generated from route NAMES, so when you change SECURE_PREFIX in .env
  nothing else needs to be edited.

  IMPORTANT: include this ONLY in layouts/pages that managers/admins see, e.g.
      @include('partials.js-routes')
  If you put it in a layout that cashiers/customers also load, the secret prefix
  will show up in their page source.

  Usage in JS:
      fetch(ROUTES.ATTENDANCE_CLEAR_ALL, { method: 'DELETE', ... })
      fetch(ROUTES.url(ROUTES.PAYROLL_DELETE_BATCH, batchId), { method: 'DELETE', ... })
--}}
<script>
window.ROUTES = {
    // My Account
    MY_ACCOUNT:            @json(route('my-account.index')),
    MY_ACCOUNT_PASSWORD:   @json(route('my-account.password')),

    // CMS
    CMS_INDEX:             @json(route('cms.index')),
    CMS_BOOKING_PRICING:   @json(route('cms.booking.pricing')),

    // System logs
    SYSTEM_LOGS:           @json(route('system-logs.index')),
    SYSTEM_LOGS_REVIEW:    @json(route('system-logs.review', ['id' => '__ID__'])),

    // Accounts
    ACCOUNTS:              @json(route('accounts.index')),
    ACCOUNTS_CREATE:       @json(route('accounts.create')),
    ACCOUNTS_STORE:        @json(route('accounts.store')),
    ACCOUNTS_UPDATE:       @json(route('accounts.update', ['id' => '__ID__'])),
    ACCOUNTS_DELETE:       @json(route('accounts.destroy', ['id' => '__ID__'])),

    // Reports
    REPORT_PRESENT:        @json(route('reports.present')),
    REPORT_HISTORICAL:     @json(route('reports.historical')),
    REPORT_BIR:            @json(route('reports.bir')),

    // Payroll / destructive actions
    PAYROLL_BULK_DELETE:   @json(route('payroll-history.bulkDelete')),
    PAYROLL_DELETE_BATCH:  @json(route('payroll-history.delete-batch', ['batch' => '__ID__'])),
    ATTENDANCE_CLEAR_ALL:  @json(route('attendance.clear-all')),
    EMPLOYEES_CLEAR_ALL:   @json(route('employees.clear-all')),

    // Replace the __ID__ placeholder with a real id
    url: function (template, id) {
        return template.replace('__ID__', encodeURIComponent(id));
    }
};
</script>