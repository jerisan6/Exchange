<!DOCTYPE html>
<html lang="{{ get_default_language_code() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $basic_settings->site_name }} {{ __($page_title) ?? '' }}</title>

    @include('partials.header-asset')
    
    @stack("css")
</head>
<body class="{{ get_default_language_dir() }}">


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Preloader
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

@include('frontend.partials.preloader')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Preloader
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Body Overlay
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<div id="body-overlay" class="body-overlay"></div>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Body Overlay
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Dashboard
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<div class="main-section-wrapper">
  <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      Start Dashboard
  ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
  <div class="page-wrapper">
    @include('user.partials.side-nav')
      <div class="main-wrapper">
          <div class="main-body-wrapper">
            @include('user.partials.top-nav')
            
            @yield('content')
           
          </div>
      </div>
  </div>
  <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      End Dashboard
  ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
  </div> 
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Dashboard
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


@include('partials.footer-asset')

@stack('script')
<script>
    $(".logout-btn").click(function(){
    var actionRoute =  "{{ setRoute('user.logout') }}";
    var target      = 1;
    var message     = `{{ __("Are you sure to") }} <strong>{{ __("Logout") }}</strong>?`;

    openAlertModal(actionRoute,target,message,"{{ __('Logout') }}","POST");
    /**
* Function for open delete modal with method DELETE
* @param {string} URL
* @param {string} target
* @param {string} message
* @returns
*/
function openAlertModal(URL,target,message,actionBtnText = "{{ __('Remove') }}",method = "DELETE"){
if(URL == "" || target == "") {
  return false;
}

if(message == "") {
  message = "Are you sure to delete ?";
}
var method = `<input type="hidden" name="_method" value="${method}">`;
openModalByContent(
  {
      content: `<div class="card modal-alert border-0">
                  <div class="card-body">
                      <form method="POST" action="${URL}">
                          <input type="hidden" name="_token" value="${laravelCsrf()}">
                          ${method}
                          <div class="head mb-3">
                              ${message}
                              <input type="hidden" name="target" value="${target}">
                          </div>
                          <div class="foot d-flex align-items-center justify-content-between">
                              <button type="button" class="modal-close btn--base btn-for-modal">{{ __("Close") }}</button>
                              <button type="submit" class="alert-submit-btn btn--base bg-danger btn-loading btn-for-modal">${actionBtnText}</button>
                          </div>
                      </form>
                  </div>
              </div>`,
  },

);
}
});
</script>


</body>
</html>