$(function() {
  $('FORM[name="quick_find"]')
    .submit(function() {
      $(this).children("input[type='submit']").prop("disabled", true).before('<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><span class="sr-only">Loading...</span>');
      $(this).children('input[name="keyword"]').keypress(function(e){
        if (e.which == 13) e.preventDefault();
      });
    });
  });
/** the following is the more modern way to do the keypress thing:
$('.noEnterSubmit').bind('keypress', false);
*/
