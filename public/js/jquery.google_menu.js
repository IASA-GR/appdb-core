(function ($) {
  $.fn.fixedMenu = function () {
    return this.each(function () {
      var menu = $(this);
      menu.find('ul li > a').bind('click', function () {
        //check whether the particular link has a dropdown
        if (!$(this).parent().hasClass('single-link') && !$(this).parent().hasClass('current')) {
          //hiding drop down menu when it is clicked again
          if ($(this).parent().hasClass('active')) {
            $(this).parent().removeClass('active');
          }
          else {
            //displaying the drop down menu
            $(this).parent().parent().find('.active').removeClass('active');
            $(this).parent().addClass('active');
          }
        }
        else {
          //hiding the drop down menu when some other link is clicked
          $(this).parent().parent().find('.active').removeClass('active');

        }
      });
    });
  };
})(jQuery);