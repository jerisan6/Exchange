(function ($) {
"user strict";

// preloader
$(window).on('load', function() {
  $(".preloader").delay(2000).animate({
    "opacity": "0"
  }, 400, function () {
      $(".preloader").css("display", "none");
  });
});

//Create Background Image
(function background() {
  let img = $('.bg_img');
  img.css('background-image', function () {
    var bg = ('url(' + $(this).data('background') + ')');
    return bg;
  });
})();

// nice-select
$(".nice-select").niceSelect();

// lightcase
$(window).on('load', function () {
  $("a[data-rel^=lightcase]").lightcase();
});

// header-fixed
var fixed_top = $(".header-section");
$(window).on("scroll", function(){
    if( $(window).scrollTop() > 200){  
        fixed_top.addClass("animated fadeInDown header-fixed");
    }
    else{
        fixed_top.removeClass("animated fadeInDown header-fixed");
    }
});

//Odometer
if ($(".statistics-item,.icon-box-items,.counter-single-items").length) {
  $(".statistics-item,.icon-box-items,.counter-single-items").each(function () {
    $(this).isInViewport(function (status) {
      if (status === "entered") {
        for (var i = 0; i < document.querySelectorAll(".odometer").length; i++) {
          var el = document.querySelectorAll('.odometer')[i];
          el.innerHTML = el.getAttribute("data-odometer-final");
        }
      }
    });
  });
}

// faq
$('.faq-wrapper .faq-title').on('click', function (e) {
  var element = $(this).parent('.faq-item');
  if (element.hasClass('open')) {
    element.removeClass('open');
    element.find('.faq-content').removeClass('open');
    element.find('.faq-content').slideUp(300, "swing");
  } else {
    element.addClass('open');
    element.children('.faq-content').slideDown(300, "swing");
    element.siblings('.faq-item').children('.faq-content').slideUp(300, "swing");
    element.siblings('.faq-item').removeClass('open');
    element.siblings('.faq-item').find('.faq-title').removeClass('open');
    element.siblings('.taq-item').find('.faq-content').slideUp(300, "swing");
  }
});

// switch-toggles
$(document).ready(function(){
  $.each($(".switch-toggles"),function(index,item) {
    var firstSwitch = $(item).find(".switch").first();
    var lastSwitch = $(item).find(".switch").last();
    if(firstSwitch.attr('data-value') == null) {
      $(item).find(".switch").first().attr("data-value",true);
      $(item).find(".switch").last().attr("data-value",false);
    }
    if($(item).hasClass("active")) {
      $(item).find('input').val(firstSwitch.attr("data-value"));
    }else {
      $(item).find('input').val(lastSwitch.attr("data-value"));
    }
  });
});

$('.switch-toggles .switch').on('click', function () {
  $(this).parents(".switch-toggles").toggleClass('active');
  $(this).parents(".switch-toggles").find("input").val($(this).attr("data-value"));
  
  let targetAttrVal = $(this).parent().attr("data-deactive");
  if($(this).parent().hasClass("active") == false) {
    $('[data-switcher='+targetAttrVal+']').removeClass("d-none").slideDown(400);
  }else {
    $('[data-switcher='+targetAttrVal+']').slideUp(400);
  }
});

// custom Select
$(document).on('click','.custom-select', function (e) {
  e.preventDefault();
  $(".custom-select-wrapper").removeClass("active");
  if($(this).siblings(".custom-select-wrapper").hasClass('active')) {
    $(this).siblings(".custom-select-wrapper").removeClass('active');
    $('.body-overlay').removeClass('active');
  }else {
    $(this).siblings(".custom-select-wrapper").addClass('active');
    $('.body-overlay').addClass('active');
  }
});
$('.body-overlay').on('click', function (e) {
  e.preventDefault();
  $('.custom-select-wrapper').removeClass('active');
  $('.body-overlay').removeClass('active');
});

$(document).on('click','.custom-option', function(){
  $(this).parent().find(".custom-option").removeClass("active");
  $(this).addClass('active');
  var flag = $(this).find("img").attr("src");
  var currencyCode = $(this).find(".custom-currency").text();
  var currencyCountry = $(this).find(".custom-country").text();
  $(this).parents(".custom-select-wrapper").siblings(".custom-select").find(".custom-select-inner").find(".custom-currency").text(currencyCode);
  $(this).parents(".custom-select-wrapper").siblings(".custom-select").find(".custom-select-inner").find("img").attr("src",flag);
  $(this).parents(".custom-select-wrapper").removeClass("active");
  $('.body-overlay').removeClass('active');
});

$(document).on('click','.custom-option', function(){
  $(this).parent().find(".custom-option").removeClass("active");
  $(this).addClass('active');
  var method = $(this).find(".title").text();
  $(this).parents(".custom-select-wrapper").siblings(".custom-select").find(".custom-select-inner").find(".method").text(method);
  $(this).parents(".custom-select-wrapper").removeClass("active");
  $('.body-overlay').removeClass('active');
});

// sidebar
$(".sidebar-menu-item > a").on("click", function () {
  var element = $(this).parent("li");
  if (element.hasClass("active")) {
    element.removeClass("active");
    element.children("ul").slideUp(500);
  }
  else {
    element.siblings("li").removeClass('active');
    element.addClass("active");
    element.siblings("li").find("ul").slideUp(500);
    element.children('ul').slideDown(500);
  }
});

// active menu JS
function splitSlash(data) {
  return data.split('/').pop();
}
function splitQuestion(data) {
  return data.split('?').shift().trim();
}
var pageNavLis = $('.sidebar-menu a');
var dividePath = splitSlash(window.location.href);
var divideGetData = splitQuestion(dividePath);
var currentPageUrl = divideGetData;

// find current sidevar element
$.each(pageNavLis,function(index,item){
    var anchoreTag = $(item);
    var anchoreTagHref = $(item).attr('href');
    var index = anchoreTagHref.indexOf('/');
    var getUri = "";
    if(index != -1) {
      // split with /
      getUri = splitSlash(anchoreTagHref);
      getUri = splitQuestion(getUri);
    }else {
      getUri = splitQuestion(anchoreTagHref);
    }
    if(getUri == currentPageUrl) {
      var thisElementParent = anchoreTag.parents('.sidebar-menu-item');
      (anchoreTag.hasClass('nav-link') == true) ? anchoreTag.addClass('active') : thisElementParent.addClass('active');
      (anchoreTag.parents('.sidebar-dropdown')) ? anchoreTag.parents('.sidebar-dropdown').addClass('active') : '';
      (thisElementParent.find('.sidebar-submenu')) ? thisElementParent.find('.sidebar-submenu').slideDown("slow") : '';
      return false;
    }
});

//sidebar Menu
$('.sidebar-menu-bar').on('click', function (e) {
  e.preventDefault();
  if($('.sidebar, .navbar-wrapper, .body-wrapper').hasClass('active')) {
    $('.sidebar, .navbar-wrapper, .body-wrapper').removeClass('active');
    $('.body-overlay').removeClass('show');
  }else {
    $('.sidebar, .navbar-wrapper, .body-wrapper').addClass('active');
    $('.body-overlay').addClass('show');
  }
});
$('#body-overlay').on('click', function (e) {
  e.preventDefault();
  $('.sidebar, .navbar-wrapper, .body-wrapper').removeClass('active');
  $('.body-overlay').removeClass('show');
});


// dashboard-list
$(document).on('click','.dashboard-list-item', function (e) {
  var element = $(this).parent('.dashboard-list-item-wrapper');
  if (element.hasClass('show')) {
    element.removeClass('show');
    element.find('.preview-list-wrapper').removeClass('show');
    element.find('.preview-list-wrapper').slideUp(300, "swing");
  } else {
    element.addClass('show');
    element.children('.preview-list-wrapper').slideDown(300, "swing");
    element.siblings('.dashboard-list-item-wrapper').children('.preview-list-wrapper').slideUp(300, "swing");
    element.siblings('.dashboard-list-item-wrapper').removeClass('show');
    element.siblings('.dashboard-list-item-wrapper').find('.dashboard-list-item').removeClass('show');
    element.siblings('.dashboard-list-item-wrapper').find('.preview-list-wrapper').slideUp(300, "swing");
  }
});

//info-btn
$(document).on('click', '.info-btn', function () {
  $('.support-profile-wrapper').addClass('active');
});
$(document).on('click', '.chat-cross-btn', function () {
  $('.support-profile-wrapper').removeClass('active');
});


//Notification
$('.notification-icon').on('click', function (e) {
  e.preventDefault();
  if($('.notification-wrapper').hasClass('active')) {
    $('.notification-wrapper').removeClass('active');
    $('.body-overlay').removeClass('active');
  }else {
    $('.notification-wrapper').addClass('active');
    $('.body-overlay').addClass('active');
  }
});
$('#body-overlay').on('click', function (e) {
  e.preventDefault();
  $('.notification-wrapper').removeClass('active');
  $('.body-overlay').removeClass('active');
});

//Profile Upload
function proPicURL(input) {
  if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
          var preview = $(input).parents('.preview-thumb').find('.profilePicPreview');
          $(preview).css('background-image', 'url(' + e.target.result + ')');
          $(preview).addClass('has-image');
          $(preview).hide();
          $(preview).fadeIn(650);
      }
      reader.readAsDataURL(input.files[0]);
  }
}
$(".profilePicUpload").on('change', function () {
  proPicURL(this);
});

$(".remove-image").on('click', function () {
  $(".profilePicPreview").css('background-image', 'none');
  $(".profilePicPreview").removeClass('has-image');
});

// password
$(document).ready(function() {
  $(".show_hide_password .show-pass").on('click', function(event) {
      event.preventDefault();
      if($(this).parent().find("input").attr("type") == "text"){
          $(this).parent().find("input").attr('type', 'password');
          $(this).find("i").addClass( "fa-eye-slash" );
          $(this).find("i").removeClass( "fa-eye" );
      }else if($(this).parent().find("input").attr("type") == "password"){
          $(this).parent().find("input").attr('type', 'text');
          $(this).find("i").removeClass( "fa-eye-slash" );
          $(this).find("i").addClass( "fa-eye" );
      }
  });
});
  

})(jQuery);


function openLoginModal() {
  if($('.account').hasClass('active')) {
    $('.account').removeClass('active');
    $('.body-overlay').removeClass('active');
  }else {
    $('.account').addClass('active');
    $('.body-overlay').addClass('active');
    $('.navbar-collapse').removeClass('show');
  }
}
/**
 * Function For Get All Country list by AJAX Request
 * @param {HTML DOM} targetElement
 * @param {Error Place Element} errorElement
 * @returns
 */
var allCountries = "";
function getAllCountries(hitUrl,targetElement = $(".country-select"),errorElement = $(".country-select").siblings(".select2")) {
  if(targetElement.length == 0) {
    return false;
  }
  var CSRF = $("meta[name=csrf-token]").attr("content");
  var data = {
    _token      : CSRF,
  };
  $.post(hitUrl,data,function() {
    // success
    $(errorElement).removeClass("is-invalid");
    $(targetElement).siblings(".invalid-feedback").remove();
  }).done(function(response){
    // Place States to States Field
    var options = "<option selected disabled>Select Country</option>";
    var selected_old_data = "";
    if($(targetElement).attr("data-old") != null) {
        selected_old_data = $(targetElement).attr("data-old");
    }
    $.each(response,function(index,item) {
        options += `<option value="${item.name}" data-id="${item.id}" data-mobile-code="${item.mobile_code}" ${selected_old_data == item.name ? "selected" : ""}>${item.name}</option>`;
    });

    allCountries = response;

    $(targetElement).html(options);
  }).fail(function(response) {
    var faildMessage = "Something went wrong! Please try again.";
    var faildElement = `<span class="invalid-feedback" role="alert">
                            <strong>${faildMessage}</strong>
                        </span>`;
    $(errorElement).addClass("is-invalid");
    if($(targetElement).siblings(".invalid-feedback").length != 0) {
        $(targetElement).siblings(".invalid-feedback").text(faildMessage);
    }else {
      errorElement.after(faildElement);
    }
  });
}
// getAllCountries();

/**
 * Function for open delete modal with method DELETE
 * @param {string} URL 
 * @param {string} target 
 * @param {string} message 
 * @returns 
 */
function openAlertModal(URL,target,message,actionBtnText = "Remove",method = "DELETE"){

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
                                  <button type="button" class="modal-close btn--base btn-for-modal">Close</button>
                                  <button type="submit" class="alert-submit-btn btn--base bg-danger btn-loading btn-for-modal">${actionBtnText}</button>
                              </div>    
                          </form>
                      </div>
                  </div>`,
      },

  );
}

/**
 * Function For Open Modal Instant by pushing HTML Element
 * @param {Object} data
 */
function openModalByContent(data = {
  content:"",
  animation: "mfp-move-horizontal",
  size: "medium",
}) {
  $.magnificPopup.open({
    removalDelay: 500,
    items: {
      src: `<div class="white-popup mfp-with-anim ${data.size ?? "medium"}">${data.content}</div>`, // can be a HTML string, jQuery object, or CSS selector
    },
    callbacks: {
      beforeOpen: function() {
        this.st.mainClass = data.animation ?? "mfp-move-horizontal";
      },
      open: function() {
        var modalCloseBtn = this.contentContainer.find(".modal-close");
        $(modalCloseBtn).click(function() {
          $.magnificPopup.close();
        });
      },
    },
    midClick: true,
  });
}

/**
 * Function for getting CSRF token for form submit in laravel
 * @returns string
 */
function laravelCsrf() {
  return $("head meta[name=csrf-token]").attr("content");
}

function placePhoneCode(code) {
  if(code != undefined) {
      code = code.replace("+","");
      code = "+" + code;
      $("input.phone-code").val(code);
      $("div.phone-code").html(code);
  }
}

// select-2 init
$('.select2-basic').select2();
$('.select2-multi-select').select2();
$(".select2-auto-tokenize").select2({
tags: true,
tokenSeparators: [',']
});

$('textarea').keydown(function (e) {
  const keyCode = e.which || e.keyCode;
  if (keyCode === 13 && !e.shiftKey) {
    e.preventDefault();
  }
});


$(document).on("keyup",".number-input",function(){
  var pattern = /^[0-9]*\.?[0-9]*$/;
  var value = $(this).val();
  var test = pattern.test(value);
  if(test == false) {
    var rightValue = value;
    if(value.length > 0) {
      for (let index = 0; index < value.length; index++){
        if(!$.isNumeric(rightValue)) {
          rightValue = rightValue.slice(0, -1);
        }
      }
    }
    $(this).val(rightValue);
  }
});


var timeOut;
function itemSearch(inputElement,tableElement,URL,minTextLength = 3) {
  $(inputElement).bind("keyup",function(){
    clearTimeout(timeOut);
    timeOut = setTimeout(executeItemSearch, 500,$(this),tableElement,URL,minTextLength);
  });
}
function executeItemSearch(inputElement,tableElement,URL,minTextLength) {
  $(tableElement).parent().find(".search-result-table").remove();
  var searchText = inputElement.val();
  if(searchText.length > minTextLength) {
    // console.log(searchText);
    $(tableElement).addClass("d-none");
    makeSearchItemXmlRequest(searchText,tableElement,URL);
  }else {
    $(tableElement).removeClass("d-none");
  }
}

function makeSearchItemXmlRequest(searchText,tableElement,URL) {
  var data = {
    _token      : laravelCsrf(),
    text        : searchText,
  };
  $.post(URL,data,function(response) {
    //response
  }).done(function(response){
    itemSearchResult(response,tableElement);
    // if($(tableElement).siblings(".search-result-table").length > 0) {
    //     $(tableElement).parent().find(".search-result-table").html(response);
    // }else{
    //     $(tableElement).after(`<div class="search-result-table"></div>`);
    //     $(tableElement).parent().find(".search-result-table").html(response);
    // }
  }).fail(function(response) {
    throwMessage('error',["Something went wrong! Please try again."]);
  });
}

function itemSearchResult(response,tableElement) {
  if(response == "") {
    throwMessage('error',["No data found!"]);
  }
  if($(tableElement).siblings(".search-result-table").length > 0) {
    $(tableElement).parent().find(".search-result-table").html(response);
  }else{
    $(tableElement).after(`<div class="search-result-table"></div>`);
    $(tableElement).parent().find(".search-result-table").html(response);
  }
}