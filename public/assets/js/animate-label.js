    
$(function(){
  $('.div-group.-animated .form-control').keyup(function(event) {
    if ($(this).val() != '') {
      $(this).parent('.div-group').addClass('-active');
    } else {
      $(this).parent('.div-group').removeClass('-active');
    }
  });

  $('.div-group.-animated .form-control').focusin(function(event) {
    $(this).parent('.div-group').addClass('-focus');
  });

  $('.div-group.-animated .form-control').focusout(function(event) {
    $(this).parent('.div-group').removeClass('-focus');
  });

  $('.select-wrapper select').change(function(event) {
    if ($(this).val() != '') {
      $(this).parent('.div-group').addClass('-active');
      $(".select-wrapper.-active label").css("display","block");
    } else {
      $(this).parent('.div-group').removeClass('-active');
      $(this).parent('.select-wrapper').children('label').css("display","none");
    }
  });

  if($(".select-wrapper select").val()==""){
    $(".select-wrapper label").css("display","none");
  }
  //$(".timepicker").parent().addClass('-active');
})


function addActiveClass(activeId){
  if ($("#"+activeId).val() != '') {
    $("#"+activeId).parent('.div-group').addClass('-active');
  } else {
    $("#"+activeId).parent('.div-group').removeClass('-active');
  }
}
